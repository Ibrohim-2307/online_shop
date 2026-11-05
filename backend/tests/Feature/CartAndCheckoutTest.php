<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryBalance;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\Seller;
use App\Models\Suborder;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Checkout\InventoryReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function createCustomer(string $phone = '+998901112233'): User
    {
        $role = Role::firstOrCreate(['name' => 'customer'], [
            'label' => 'Xaridor',
        ]);

        $user = User::create([
            'name' => 'Aziz Xaridor',
            'email' => 'customer_' . uniqid() . '@example.uz',
            'phone' => $phone,
            'password' => bcrypt('Secret123!'),
            'is_active' => true,
        ]);

        $user->roles()->attach($role->id);

        return $user;
    }

    protected function setupSellerWithProduct(
        string $sellerName,
        string $sellerSlug,
        string $productName,
        string $productSlug,
        string $sku,
        int $stock = 1000
    ): array {
        $seller = Seller::create([
            'name' => $sellerName,
            'slug' => $sellerSlug,
            'legal_name' => $sellerName . ' MCHJ',
            'tax_id' => '30' . rand(1000000, 9999999),
            'status' => 'active',
            'commission_rate' => 5.0,
            'rating' => 4.9,
        ]);

        $category = Category::firstOrCreate(['slug' => 'qurilish'], [
            'name' => 'Qurilish',
            'is_active' => true,
        ]);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => $productName,
            'slug' => $productSlug,
            'unit' => 'piece',
            'min_order_quantity' => 1.0,
            'unit_step' => 1.0,
            'trade_mode' => 'both',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'name' => $productName . ' Standart',
            'price' => 300000, // 300,000 UZS base
            'currency' => 'UZS',
        ]);

        // Tier: 1-299 units -> 300,000 UZS
        $variant->priceTiers()->create([
            'min_quantity' => 1.0,
            'max_quantity' => 299.0,
            'unit_price' => 300000,
            'currency' => 'UZS',
        ]);

        // Tier: 300-999 units -> 270,000 UZS
        $variant->priceTiers()->create([
            'min_quantity' => 300.0,
            'max_quantity' => 999.0,
            'unit_price' => 270000,
            'currency' => 'UZS',
        ]);

        // Tier: 1000+ units -> 250,000 UZS
        $variant->priceTiers()->create([
            'min_quantity' => 1000.0,
            'max_quantity' => null,
            'unit_price' => 250000,
            'currency' => 'UZS',
        ]);

        $warehouse = Warehouse::create([
            'seller_id' => $seller->id,
            'name' => $sellerName . ' Ombori',
            'is_active' => true,
        ]);

        $balance = InventoryBalance::create([
            'warehouse_id' => $warehouse->id,
            'product_variant_id' => $variant->id,
            'quantity' => (float) $stock,
            'reserved_quantity' => 0.0,
        ]);

        return [$seller, $product, $variant, $warehouse, $balance];
    }

    protected function createUserWithRole(string $roleName, string $email): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], ['label' => $roleName]);
        $user = User::create([
            'name' => ucfirst($roleName) . ' User',
            'email' => $email,
            'password' => bcrypt('Secret123!'),
            'is_active' => true,
        ]);
        $user->roles()->attach($role->id);

        return $user->fresh();
    }

    public function test_cod_fulfillment_flow_deducts_stock_credits_sellers_and_completes_only_when_all_parts_arrive(): void
    {
        [$sellerA, , $variantA, , $balanceA] = $this->setupSellerWithProduct('Seller A', 'seller-a', 'Tovar A', 'tovar-a', 'SKU-A', 100);
        [$sellerB, , $variantB, , $balanceB] = $this->setupSellerWithProduct('Seller B', 'seller-b', 'Tovar B', 'tovar-b', 'SKU-B', 100);

        $ownerA = $this->createUserWithRole('seller_owner', 'owner-a@example.uz');
        \App\Models\SellerMember::create(['seller_id' => $sellerA->id, 'user_id' => $ownerA->id, 'role' => 'owner', 'permissions' => ['*'], 'is_active' => true]);
        $courier = $this->createUserWithRole('courier', 'kuryer@example.uz');
        $customer = $this->createCustomer('+998907771234');

        $this->actingAs($customer)->postJson('/api/v1/cart/items', ['variant_id' => $variantA->id, 'quantity' => 3])->assertStatus(201);
        $this->actingAs($customer)->postJson('/api/v1/cart/items', ['variant_id' => $variantB->id, 'quantity' => 2])->assertStatus(201);
        $this->actingAs($customer)->post('/checkout', [
            'recipient_name' => 'Aziz', 'phone' => '+998907771234', 'region' => 'Toshkent',
            'city' => 'Toshkent', 'address_line' => 'Uy 5', 'payment_method' => 'cash_on_delivery',
        ])->assertSessionHasNoErrors();

        $order = Order::where('user_id', $customer->id)->latest()->firstOrFail();
        $subA = $order->suborders()->where('seller_id', $sellerA->id)->firstOrFail();
        $subB = $order->suborders()->where('seller_id', $sellerB->id)->firstOrFail();

        // Seller: "packed" stage is stored as a valid status; a non-courier can't be assigned
        $this->actingAs($ownerA)->post(route('profile.seller.orders.status', $subA->id), ['status' => 'preparing'])
            ->assertSessionHas('success');
        $this->assertEquals('preparing', $subA->fresh()->status);

        $this->actingAs($ownerA)->post(route('profile.seller.orders.status', $subA->id), ['status' => 'ready_for_shipping'])
            ->assertSessionHas('error');
        $this->actingAs($ownerA)->post(route('profile.seller.orders.status', $subA->id), ['status' => 'ready_for_shipping', 'courier_id' => $customer->id])
            ->assertSessionHas('error');
        $this->actingAs($ownerA)->post(route('profile.seller.orders.status', $subA->id), ['status' => 'ready_for_shipping', 'courier_id' => $courier->id])
            ->assertSessionHas('success');

        $shipmentA = $subA->shipments()->firstOrFail();
        $this->assertEquals($courier->id, $shipmentA->fresh()->courier_id);
        $this->assertEquals('assigned', $shipmentA->fresh()->status);

        // Courier picks up: stock leaves the warehouse exactly once
        $this->actingAs($courier)->post(route('profile.courier.shipments.status', $shipmentA->id), ['status' => 'picked_up'])->assertRedirect();
        $this->actingAs($courier)->post(route('profile.courier.shipments.status', $shipmentA->id), ['status' => 'in_transit'])->assertRedirect();
        $this->assertEquals(97.0, (float) $balanceA->fresh()->quantity);
        $this->assertEquals(0.0, (float) $balanceA->fresh()->reserved_quantity);
        $this->assertEquals('shipped', $subA->fresh()->status);

        // First part delivered: seller A credited, but order not complete and cash not fully collected yet
        $this->actingAs($courier)->post(route('profile.courier.shipments.status', $shipmentA->id), ['status' => 'delivered'])->assertRedirect();
        $this->assertEquals('delivered', $subA->fresh()->status);
        $this->assertEquals('processing', $order->fresh()->status);
        $this->assertEquals('pending', $order->payments()->latest()->first()->status);
        $this->assertEquals(1, \App\Models\SellerLedger::where('suborder_id', $subA->id)->where('entry_type', 'order_sale')->count());

        // Delivering twice does nothing more
        $this->actingAs($courier)->post(route('profile.courier.shipments.status', $shipmentA->id), ['status' => 'delivered'])->assertSessionHas('error');
        $this->assertEquals(1, \App\Models\SellerLedger::where('suborder_id', $subA->id)->where('entry_type', 'order_sale')->count());

        // Second part delivered: order completes and COD payment is paid
        $shipmentB = $subB->shipments()->firstOrFail();
        $shipmentB->update(['courier_id' => $courier->id, 'status' => 'assigned']);
        $this->actingAs($courier)->post(route('profile.courier.shipments.status', $shipmentB->id), ['status' => 'delivered'])->assertRedirect();

        $this->assertEquals('completed', $order->fresh()->status);
        $this->assertEquals('paid', $order->payments()->latest()->first()->status);
        $this->assertEquals(98.0, (float) $balanceB->fresh()->quantity);
        $this->assertEquals(1, \App\Models\SellerLedger::where('suborder_id', $subB->id)->where('entry_type', 'order_sale')->count());
    }

    public function test_seller_and_courier_cannot_work_on_cancelled_order(): void
    {
        [$seller, , $variant] = $this->setupSellerWithProduct('Seller C', 'seller-c', 'Tovar X', 'tovar-x', 'SKU-X', 10);
        $owner = $this->createUserWithRole('seller_owner', 'owner-c@example.uz');
        \App\Models\SellerMember::create(['seller_id' => $seller->id, 'user_id' => $owner->id, 'role' => 'owner', 'permissions' => ['*'], 'is_active' => true]);
        $courier = $this->createUserWithRole('courier', 'kuryer-c@example.uz');
        $customer = $this->createCustomer('+998907775555');

        $this->actingAs($customer)->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 1])->assertStatus(201);
        $this->actingAs($customer)->post('/checkout', [
            'recipient_name' => 'Aziz', 'phone' => '+998907775555', 'region' => 'Toshkent',
            'city' => 'Toshkent', 'address_line' => 'Uy 6', 'payment_method' => 'cash_on_delivery',
        ])->assertSessionHasNoErrors();
        $order = Order::where('user_id', $customer->id)->latest()->firstOrFail();
        $this->actingAs($customer)->post('/orders/' . $order->order_number . '/cancel')->assertSessionHas('success');

        $sub = $order->suborders()->firstOrFail();
        $this->actingAs($owner)->post(route('profile.seller.orders.status', $sub->id), ['status' => 'confirmed'])
            ->assertSessionHas('error');
        $this->assertEquals('cancelled', $sub->fresh()->status);

        $shipment = $sub->shipments()->firstOrFail();
        $shipment->update(['courier_id' => $courier->id]);
        $this->actingAs($courier)->post(route('profile.courier.shipments.status', $shipment->id), ['status' => 'picked_up'])
            ->assertSessionHas('error');
    }

    public function test_customer_sees_progress_can_reorder_and_request_return_after_delivery(): void
    {
        [$seller, , $variant] = $this->setupSellerWithProduct('Seller R', 'seller-r', 'Tovar R', 'tovar-r', 'SKU-R', 50);
        $customer = $this->createCustomer('+998907776666');

        $this->actingAs($customer)->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 2])->assertStatus(201);
        $this->actingAs($customer)->post('/checkout', [
            'recipient_name' => 'Aziz', 'phone' => '+998907776666', 'region' => 'Toshkent',
            'city' => 'Toshkent', 'address_line' => 'Uy 7', 'payment_method' => 'cash_on_delivery',
        ])->assertSessionHasNoErrors();
        $order = Order::where('user_id', $customer->id)->latest()->firstOrFail();
        $sub = $order->suborders()->firstOrFail();

        // Progress timeline shows; no return option before delivery
        $page = $this->actingAs($customer)->get('/orders/' . $order->order_number);
        $page->assertStatus(200)->assertSee('Qadoqlanmoqda')->assertSee('aria-current="step"', false)
            ->assertDontSee('Tovarni qaytarish');
        $this->actingAs($customer)->post('/orders/' . $order->order_number . '/returns', ['suborder_id' => $sub->id, 'reason' => 'Nuqsonli'])
            ->assertSessionHas('error');

        // Reorder puts the items back into the (now empty) cart
        $this->actingAs($customer)->post('/orders/' . $order->order_number . '/reorder')
            ->assertRedirect(route('cart'))->assertSessionHas('success');
        $this->assertEquals(2.0, (float) \App\Models\CartItem::whereHas('cart', fn($q) => $q->where('user_id', $customer->id)->where('status', 'active'))->sum('quantity'));

        // After delivery and payment the customer can ask for a return, once
        $sub->update(['status' => 'delivered']);
        $sub->shipments()->update(['status' => 'delivered', 'delivered_at' => now()]);
        $order->payments()->update(['status' => 'paid']);

        $this->actingAs($customer)->get('/orders/' . $order->order_number)->assertSee('Tovarni qaytarish');
        $this->actingAs($customer)->post('/orders/' . $order->order_number . '/returns', [
            'suborder_id' => $sub->id, 'reason' => 'Tovar nuqsonli yoki singan', 'details' => 'Qopi yirtilgan',
        ])->assertRedirect(route('orders.detail', $order->order_number));

        $refund = \App\Models\Refund::where('order_id', $order->id)->firstOrFail();
        $this->assertEquals('requested', $refund->status);
        $this->assertEquals((int) $sub->items_subtotal, (int) $refund->amount);
        $this->assertEquals('Tovar nuqsonli yoki singan: Qopi yirtilgan', $refund->reason);

        $this->actingAs($customer)->get('/orders/' . $order->order_number)
            ->assertSee("Qaytarish so'rovi ko'rib chiqilmoqda")
            ->assertDontSee('Tovarni qaytarish');
        $this->actingAs($customer)->post('/orders/' . $order->order_number . '/returns', ['suborder_id' => $sub->id, 'reason' => 'Yana'])
            ->assertSessionHas('error');
        $this->assertEquals(1, \App\Models\Refund::where('order_id', $order->id)->count());

        // Return window closes after 10 days
        $sub->shipments()->update(['delivered_at' => now()->subDays(11)]);
        $refund->update(['status' => 'rejected']);
        $this->actingAs($customer)->get('/orders/' . $order->order_number)->assertDontSee('Tovarni qaytarish');
    }

    public function test_admin_reviews_and_completes_customer_return_from_web_panel(): void
    {
        [$seller, , $variant, , $balance] = $this->setupSellerWithProduct('Seller Q', 'seller-q', 'Tovar Q', 'tovar-q', 'SKU-Q', 20);
        $customer = $this->createCustomer('+998907778888');
        $admin = $this->createUserWithRole('admin', 'admin-q@example.uz');
        $courier = $this->createUserWithRole('courier', 'kuryer-q@example.uz');

        $this->actingAs($customer)->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 2])->assertStatus(201);
        $this->actingAs($customer)->post('/checkout', [
            'recipient_name' => 'Aziz', 'phone' => '+998907778888', 'region' => 'Toshkent',
            'city' => 'Toshkent', 'address_line' => 'Uy 8', 'payment_method' => 'cash_on_delivery',
        ])->assertSessionHasNoErrors();
        $order = Order::where('user_id', $customer->id)->latest()->firstOrFail();
        $sub = $order->suborders()->firstOrFail();
        $shipment = $sub->shipments()->firstOrFail();
        $shipment->update(['courier_id' => $courier->id, 'status' => 'assigned']);
        $this->actingAs($courier)->post(route('profile.courier.shipments.status', $shipment->id), ['status' => 'delivered'])->assertRedirect();
        $this->assertEquals(18.0, (float) $balance->fresh()->quantity);

        $this->actingAs($customer)->post('/orders/' . $order->order_number . '/returns', [
            'suborder_id' => $sub->id, 'reason' => 'Boshqa tovar keldi',
        ])->assertSessionHas('success');
        $refund = \App\Models\Refund::where('order_id', $order->id)->firstOrFail();

        // Admin sees the open request with a badge
        $this->actingAs($admin)->get('/admin?tab=refunds')->assertStatus(200)
            ->assertSee($refund->refund_number)->assertSee('Boshqa tovar keldi')->assertSee("currentTab: 'refunds'", false);

        // Customers and couriers cannot act on returns
        $this->actingAs($customer)->post(route('admin.refunds.review', $refund->id), ['decision' => 'approve'])->assertStatus(403);

        // Rejecting needs a reason shown to the customer
        $this->actingAs($admin)->post(route('admin.refunds.review', $refund->id), ['decision' => 'reject'])
            ->assertSessionHasErrors('notes');
        $this->assertEquals('requested', $refund->fresh()->status);

        $this->actingAs($admin)->post(route('admin.refunds.review', $refund->id), ['decision' => 'approve', 'notes' => 'Kuryer olib ketadi'])
            ->assertRedirect(route('admin.dashboard', ['tab' => 'refunds']));
        $this->assertEquals('approved', $refund->fresh()->status);
        $this->actingAs($customer)->get('/orders/' . $order->order_number)->assertSee('Kuryer olib ketadi');

        // Completing restocks the goods, debits the seller and marks the payment refunded
        $this->actingAs($admin)->post(route('admin.refunds.complete', $refund->id))->assertSessionHas('success');
        $this->assertEquals('completed', $refund->fresh()->status);
        $this->assertEquals(20.0, (float) $balance->fresh()->quantity);
        // Goods are refunded, delivery is not, so the payment is partially refunded
        $this->assertEquals('partially_refunded', $order->payments()->latest()->first()->status);
        $this->assertEquals(0, app(\App\Services\Ledger\SellerLedgerService::class)->getSellerBalance($seller) + (int) $sub->commission_amount);

        // Completing twice changes nothing
        $this->actingAs($admin)->post(route('admin.refunds.complete', $refund->id))->assertSessionHas('success');
        $this->assertEquals(20.0, (float) $balance->fresh()->quantity);
    }

    public function test_courier_panel_shows_recipient_contact_notes_and_requires_failure_reason(): void
    {
        [$seller, , $variant] = $this->setupSellerWithProduct('Seller K', 'seller-k', 'Tovar K', 'tovar-k', 'SKU-K', 20);
        $customer = $this->createCustomer('+998901110000');
        $courier = $this->createUserWithRole('courier', 'kuryer-k@example.uz');

        // Ordered for someone else: the courier must call the recipient, not the account owner
        $this->actingAs($customer)->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 1])->assertStatus(201);
        $this->actingAs($customer)->post('/checkout', [
            'recipient_name' => 'Onam Karimova', 'phone' => '+998935557799', 'region' => 'Toshkent',
            'city' => 'Chilonzor', 'address_line' => "Bunyodkor 5-uy", 'notes' => "Domofon ishlamaydi, qo'ng'iroq qiling",
            'payment_method' => 'cash_on_delivery',
        ])->assertSessionHasNoErrors();
        $order = Order::where('user_id', $customer->id)->latest()->firstOrFail();
        $shipment = $order->suborders()->firstOrFail()->shipments()->firstOrFail();
        $shipment->update(['courier_id' => $courier->id, 'status' => 'assigned']);

        $this->actingAs($courier)->get(route('profile', ['tab' => 'courier_shipments']))
            ->assertStatus(200)
            ->assertSee('Onam Karimova')
            ->assertSee('tel:+998935557799', false)
            ->assertSee('yandex.uz/maps', false)
            ->assertSee('Domofon ishlamaydi')
            ->assertSee("Do'kondan oldim", false)
            ->assertSee('Undirilishi kerak');

        $this->actingAs($courier)->post(route('profile.courier.shipments.status', $shipment->id), ['status' => 'picked_up'])->assertSessionHas('success');
        $this->actingAs($courier)->post(route('profile.courier.shipments.status', $shipment->id), ['status' => 'failed'])
            ->assertSessionHasErrors('delay_reason');
        $this->actingAs($courier)->post(route('profile.courier.shipments.status', $shipment->id), ['status' => 'failed', 'delay_reason' => "Mijoz manzilda yo'q"])
            ->assertSessionHas('success');
        $this->assertEquals('failed', $shipment->fresh()->status);

        // A failed delivery can be retried
        $this->actingAs($courier)->get(route('profile', ['tab' => 'courier_shipments']))->assertSee("Qayta yo'lga chiqdim");
        $this->actingAs($courier)->post(route('profile.courier.shipments.status', $shipment->id), ['status' => 'in_transit'])->assertSessionHas('success');
        $this->actingAs($courier)->post(route('profile.courier.shipments.status', $shipment->id), ['status' => 'delivered'])->assertSessionHas('success');

        $this->actingAs($courier)->get(route('profile', ['tab' => 'courier_shipments']))
            ->assertSee("Hozircha faol yetkazishlar yo'q", false)
            ->assertSee('Yakunlangan va bekor qilingan (1)');
    }

    public function test_seller_edits_product_price_and_stock_and_sees_new_order_alert(): void
    {
        [$seller, $product, $variant, , $balance] = $this->setupSellerWithProduct('Seller E', 'seller-e', 'Tovar E', 'tovar-e', 'SKU-E', 10);
        $owner = $this->createUserWithRole('seller_owner', 'owner-e@example.uz');
        \App\Models\SellerMember::create(['seller_id' => $seller->id, 'user_id' => $owner->id, 'role' => 'owner', 'permissions' => ['*'], 'is_active' => true]);
        $customer = $this->createCustomer('+998907779999');

        // A cash-on-delivery order reserves 4 units and needs the seller's action
        $this->actingAs($customer)->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 4])->assertStatus(201);
        $this->actingAs($customer)->post('/checkout', [
            'recipient_name' => 'Aziz', 'phone' => '+998907779999', 'region' => 'Toshkent',
            'city' => 'Toshkent', 'address_line' => 'Uy 9', 'payment_method' => 'cash_on_delivery',
        ])->assertSessionHasNoErrors();

        $this->actingAs($owner)->get(route('profile', ['tab' => 'seller_products']))
            ->assertStatus(200)
            ->assertSee('1 ta yangi buyurtma tayyorlashni kutmoqda')
            ->assertSee("So'nggi 30 kun", false)
            ->assertSee('1 200 000 UZS')
            ->assertSee('+4 band', false)
            ->assertSee('6 piece sotuvda', false);

        // Stock cannot be set below what customers already reserved
        $this->actingAs($owner)->post(route('profile.seller.products.update', $product->id), [
            'name' => 'Tovar E', 'price' => 280000, 'stock_quantity' => 3,
        ])->assertSessionHas('error');
        $this->assertEquals(10.0, (float) $balance->fresh()->quantity);

        $this->actingAs($owner)->post(route('profile.seller.products.update', $product->id), [
            'name' => 'Tovar E Yangi', 'price' => 280000, 'stock_quantity' => 25, 'description' => 'Yangilangan tavsif',
        ])->assertSessionHas('success');

        $this->assertEquals('Tovar E Yangi', $product->fresh()->name);
        $this->assertEquals(25.0, (float) $balance->fresh()->quantity);
        $this->assertEquals(280000, (int) $variant->priceTiers()->orderBy('min_quantity')->first()->unit_price);
        $this->assertDatabaseHas('inventory_movements', ['product_variant_id' => $variant->id, 'type' => 'adjustment']);

        // SVG uploads are refused
        $this->actingAs($owner)->post(route('profile.seller.products.update', $product->id), [
            'name' => 'Tovar E Yangi', 'price' => 280000, 'stock_quantity' => 25,
            'image_file' => \Illuminate\Http\UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        ])->assertSessionHasErrors('image_file');

        // Another seller cannot edit this product
        [$otherSeller] = $this->setupSellerWithProduct('Seller F', 'seller-f', 'Tovar F', 'tovar-f', 'SKU-F', 5);
        $otherOwner = $this->createUserWithRole('seller_owner', 'owner-f@example.uz');
        \App\Models\SellerMember::create(['seller_id' => $otherSeller->id, 'user_id' => $otherOwner->id, 'role' => 'owner', 'permissions' => ['*'], 'is_active' => true]);
        $this->actingAs($otherOwner)->post(route('profile.seller.products.update', $product->id), [
            'name' => 'Hack', 'price' => 100, 'stock_quantity' => 0,
        ])->assertStatus(404);
    }

    public function test_unapproved_store_products_stay_hidden_and_storeless_seller_cannot_post_into_another_store(): void
    {
        [$existing] = $this->setupSellerWithProduct('First Store', 'first-store', 'Birinchi Tovar', 'birinchi', 'SKU-1ST', 5);
        $category = \App\Models\Category::firstOrFail();

        // Seller role but no store membership: must not land in "First Store"
        $storeless = $this->createUserWithRole('seller_owner', 'storeless@example.uz');
        $this->actingAs($storeless)->post(route('profile.seller.products.store'), [
            'name' => 'Begona Tovar', 'category_id' => $category->id, 'unit' => 'piece', 'trade_mode' => 'both',
            'price' => 5000, 'stock_quantity' => 3,
        ])->assertSessionHas('error');
        $this->assertDatabaseMissing('products', ['name' => 'Begona Tovar', 'seller_id' => $existing->id]);

        // A pending store can prepare products but they are not shown to shoppers
        $pending = \App\Models\Seller::create(['name' => 'Pending Store', 'slug' => 'pending-store', 'status' => 'pending']);
        $pendingOwner = $this->createUserWithRole('seller_owner', 'pending-owner@example.uz');
        \App\Models\SellerMember::create(['seller_id' => $pending->id, 'user_id' => $pendingOwner->id, 'role' => 'owner', 'permissions' => ['*'], 'is_active' => true]);
        $this->actingAs($pendingOwner)->post(route('profile.seller.products.store'), [
            'name' => 'Kutilayotgan Tovar', 'category_id' => $category->id, 'unit' => 'piece', 'trade_mode' => 'both',
            'price' => 7000, 'stock_quantity' => 3,
        ])->assertSessionHas('success');

        $pendingProduct = Product::where('name', 'Kutilayotgan Tovar')->firstOrFail();
        $this->assertEquals('pending', $pendingProduct->status);
        $this->app['auth']->forgetGuards();
        $this->get('/catalog?q=Kutilayotgan')->assertDontSee('Kutilayotgan Tovar');

        // Even an approved product disappears if its store is suspended
        $existing->update(['status' => 'suspended']);
        $this->get('/catalog')->assertDontSee('Birinchi Tovar');
        $this->get('/products/birinchi')->assertStatus(404);
    }

    public function test_test_payment_works_while_click_and_payme_are_not_connected_and_can_be_switched_off(): void
    {
        [, , $variant] = $this->setupSellerWithProduct('Seller T', 'seller-t', 'Tovar T', 'tovar-t', 'SKU-T', 10);
        $customer = $this->createCustomer('+998907770101');
        // Outside the "testing" environment Laravel enforces CSRF even in tests
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);

        $placeOrder = function () use ($customer, $variant) {
            $this->actingAs($customer)->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 1])->assertStatus(201);
            $this->actingAs($customer)->post('/checkout', [
                'recipient_name' => 'Aziz', 'phone' => '+998907770101', 'region' => 'Toshkent',
                'city' => 'Toshkent', 'address_line' => 'Uy 10', 'payment_method' => 'click',
            ])->assertSessionHasNoErrors()->assertSessionMissing('error');

            return Order::where('user_id', $customer->id)->latest('id')->firstOrFail();
        };

        // Real store (production, test payments switched off): no test payment
        $this->app['env'] = 'production';
        config(['app.demo_mode' => false, 'services.payment.test_mode' => 'off']);
        $order = $placeOrder();
        $this->actingAs($customer)->get('/payments/checkout/' . $order->order_number)->assertDontSee("Test to'lovini amalga oshirish", false);
        $this->actingAs($customer)->post('/payments/pay/' . $order->order_number, ['payment_method' => 'demo'])->assertSessionHas('error');
        $this->assertEquals('pending_payment', $order->fresh()->status);

        // Default "auto": Click/Payme not connected, so a production site offers the test payment
        config(['services.payment.test_mode' => 'auto', 'services.click.service_id' => null, 'services.payme.merchant_id' => null]);
        $this->assertTrue(app(\App\Services\Payment\PaymentManager::class)->isTestPaymentAllowed());
        $this->actingAs($customer)->get('/payments/checkout/' . $order->order_number)
            ->assertSee("Test to'lovini amalga oshirish", false)
            ->assertSee('HAQIQIY PUL YECHILMAYDI');
        $this->actingAs($customer)->post('/payments/pay/' . $order->order_number, ['payment_method' => 'demo'])
            ->assertRedirect(route('order.success', $order->order_number));
        $this->app['env'] = 'testing';

        $order->refresh();
        $this->assertEquals('processing', $order->status);
        $payment = $order->payments()->latest('id')->first();
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals('demo', $payment->payment_method);
        $this->actingAs($customer)->get('/orders/' . $order->order_number)->assertSee("To'langan (test)", false);
    }

    public function test_sold_out_product_freezes_automatically_and_returns_after_restock(): void
    {
        [$seller, $product, $variant, , $balance] = $this->setupSellerWithProduct('Seller Z', 'seller-z', 'Muzlaydigan Tovar', 'muzlaydigan', 'SKU-Z', 3);
        $owner = $this->createUserWithRole('seller_owner', 'owner-z@example.uz');
        \App\Models\SellerMember::create(['seller_id' => $seller->id, 'user_id' => $owner->id, 'role' => 'owner', 'permissions' => ['*'], 'is_active' => true]);

        $this->get('/catalog')->assertSee('Muzlaydigan Tovar');

        // Everything sold out: hidden from listings, product page says it is not for sale
        $balance->update(['quantity' => 0]);
        $this->get('/catalog')->assertDontSee('Muzlaydigan Tovar');
        $this->get('/products/muzlaydigan')->assertStatus(200)->assertSee("Vaqtincha sotuvda yo'q", false)->assertDontSee('@click="addToCart()"', false);
        $this->actingAs($owner)->get(route('profile', ['tab' => 'seller_products']))->assertSee('Qoldiq tugadi')->assertSee('Avtomatik muzlatildi');

        // Restocking brings it back by itself
        $this->actingAs($owner)->post(route('profile.seller.products.update', $product->id), [
            'name' => 'Muzlaydigan Tovar', 'price' => 300000, 'stock_quantity' => 7,
        ])->assertSessionHas('success');
        $this->app['auth']->forgetGuards();
        $this->get('/catalog')->assertSee('Muzlaydigan Tovar');

        // Manual freeze hides it too
        $this->actingAs($owner)->post(route('profile.seller.products.status', $product->id), ['toggle_active' => 1])->assertSessionHas('success');
        $this->app['auth']->forgetGuards();
        $this->get('/catalog')->assertDontSee('Muzlaydigan Tovar');
    }

    public function test_seller_deletes_product_but_not_while_customers_are_waiting_for_it(): void
    {
        [$seller, $product, $variant] = $this->setupSellerWithProduct('Seller D', 'seller-d', "O'chiriladigan Tovar", 'ochiriladigan', 'SKU-D', 10);
        $owner = $this->createUserWithRole('seller_owner', 'owner-d@example.uz');
        \App\Models\SellerMember::create(['seller_id' => $seller->id, 'user_id' => $owner->id, 'role' => 'owner', 'permissions' => ['*'], 'is_active' => true]);
        $customer = $this->createCustomer('+998907770202');

        $this->actingAs($customer)->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 2])->assertStatus(201);
        $this->actingAs($customer)->post('/checkout', [
            'recipient_name' => 'Aziz', 'phone' => '+998907770202', 'region' => 'Toshkent',
            'city' => 'Toshkent', 'address_line' => 'Uy 11', 'payment_method' => 'cash_on_delivery',
        ])->assertSessionHasNoErrors();
        $order = Order::where('user_id', $customer->id)->latest('id')->firstOrFail();

        // An open order still needs these goods
        $this->actingAs($owner)->post(route('profile.seller.products.status', $product->id), ['delete' => 1])->assertSessionHas('error');
        $this->assertNotNull(Product::find($product->id));

        // Once the order is cancelled (stock released) the seller can delete it
        $this->actingAs($customer)->post('/orders/' . $order->order_number . '/cancel')->assertSessionHas('success');
        $this->actingAs($owner)->post(route('profile.seller.products.status', $product->id), ['delete' => 1])->assertSessionHas('success');

        $this->assertNull(Product::find($product->id));
        $this->assertNotNull(Product::withTrashed()->find($product->id)); // kept for order history
        $this->app['auth']->forgetGuards();
        $this->get('/products/ochiriladigan')->assertStatus(404);
        $this->actingAs($customer)->get('/orders/' . $order->order_number)->assertStatus(200)->assertSee("O&#039;chiriladigan Tovar", false);
    }

    public function test_two_parallel_checkouts_cannot_double_sell_last_sku(): void
    {
        // Setup product with exactly 5 units in stock
        [$seller, $product, $variant] = $this->setupSellerWithProduct(
            'StroyTest', 'stroy-test', 'Limit Sement', 'limit-sement', 'LIMIT-SKU-1', 5
        );

        $customer1 = $this->createCustomer('+998901111111');
        $customer2 = $this->createCustomer('+998902222222');

        $payload = [
            'items' => [
                ['variant_id' => $variant->id, 'quantity' => 5],
            ],
            'shipping_address' => [
                'recipient_name' => 'Alisher',
                'phone' => '+998901111111',
                'region' => 'Toshkent',
                'city' => 'Chilonzor',
                'address_line' => 'Bunyodkor shoh ko\'chasi 1-uy',
            ],
        ];

        // 1. Customer 1 checkouts all 5 units -> Succeeds!
        $response1 = $this->actingAs($customer1, 'sanctum')->postJson('/api/v1/checkout', $payload);
        $response1->assertStatus(201);
        $this->assertEquals(1, Order::count());

        // 2. Customer 2 immediately attempts to purchase 5 units of same SKU -> Fails!
        $response2 = $this->actingAs($customer2, 'sanctum')->postJson('/api/v1/checkout', $payload);
        $response2->assertStatus(422);
        $response2->assertJsonPath('error.code', 'CHECKOUT_ERROR');

        // Only 1 order exists in database
        $this->assertEquals(1, Order::count());
    }

    public function test_idempotency_key_replay_returns_same_order(): void
    {
        [$seller, $product, $variant] = $this->setupSellerWithProduct(
            'StroyTest2', 'stroy-test-2', 'Gips', 'gips-test', 'GIPS-SKU-1', 100
        );

        $customer = $this->createCustomer();
        $idempotencyKey = 'IDEMPOTENCY-TEST-KEY-001';

        $payload = [
            'items' => [
                ['variant_id' => $variant->id, 'quantity' => 10],
            ],
            'shipping_address' => [
                'recipient_name' => 'Farrux',
                'phone' => '+998901112233',
                'region' => 'Toshkent',
                'city' => 'Yunusobod',
                'address_line' => 'Navoiy 14',
            ],
        ];

        // 1. Initial request creates order
        $resp1 = $this->actingAs($customer, 'sanctum')
            ->withHeader('Idempotency-Key', $idempotencyKey)
            ->postJson('/api/v1/checkout', $payload);

        $resp1->assertStatus(201);
        $orderId = $resp1->json('data.order_id');
        $orderNum = $resp1->json('data.order_number');

        // 2. Exact same request replayed with identical Idempotency-Key
        $resp2 = $this->actingAs($customer, 'sanctum')
            ->withHeader('Idempotency-Key', $idempotencyKey)
            ->postJson('/api/v1/checkout', $payload);

        $resp2->assertStatus(200);
        $this->assertTrue($resp2->json('data.is_replay'));
        $this->assertEquals($orderNum, $resp2->json('data.order_number'));

        // Strictly 1 order in database
        $this->assertEquals(1, Order::count());
    }

    public function test_idempotency_key_mismatch_payload_is_rejected(): void
    {
        [$seller, $product, $variant] = $this->setupSellerWithProduct(
            'StroyTest3', 'stroy-test-3', 'Bo\'yoq', 'boyoq-test', 'BOYOQ-SKU-1', 100
        );

        $customer = $this->createCustomer();
        $idempotencyKey = 'IDEMPOTENCY-TEST-KEY-002';

        $payloadA = [
            'items' => [
                ['variant_id' => $variant->id, 'quantity' => 2],
            ],
            'shipping_address' => [
                'recipient_name' => 'Farrux',
                'phone' => '+998901112233',
                'region' => 'Toshkent',
                'city' => 'Yunusobod',
                'address_line' => 'Navoiy 14',
            ],
        ];

        $resp1 = $this->actingAs($customer, 'sanctum')
            ->withHeader('Idempotency-Key', $idempotencyKey)
            ->postJson('/api/v1/checkout', $payloadA);
        $resp1->assertStatus(201);

        // Payload B with different quantity under SAME Idempotency-Key
        $payloadB = $payloadA;
        $payloadB['items'][0]['quantity'] = 10;

        $resp2 = $this->actingAs($customer, 'sanctum')
            ->withHeader('Idempotency-Key', $idempotencyKey)
            ->postJson('/api/v1/checkout', $payloadB);

        $resp2->assertStatus(422);
        $resp2->assertJsonPath('error.code', 'IDEMPOTENCY_PAYLOAD_MISMATCH');
    }

    public function test_multi_seller_order_splits_into_suborders_with_individual_shipping(): void
    {
        // Seller 1: Cement
        [$seller1, $p1, $v1] = $this->setupSellerWithProduct(
            'Seller One', 'seller-one', 'Sement', 'sement-1', 'SEM-1', 500
        );
        // Seller 2: Generator
        [$seller2, $p2, $v2] = $this->setupSellerWithProduct(
            'Seller Two', 'seller-two', 'Generator', 'gen-1', 'GEN-1', 50
        );

        $customer = $this->createCustomer();

        $payload = [
            'items' => [
                ['variant_id' => $v1->id, 'quantity' => 2],
                ['variant_id' => $v2->id, 'quantity' => 1],
            ],
            'shipping_address' => [
                'recipient_name' => 'Akbar',
                'phone' => '+998901112233',
                'region' => 'Toshkent',
                'city' => 'Yakkasaroy',
                'address_line' => 'Shota Rustaveli 25',
            ],
        ];

        $response = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/checkout', $payload);
        $response->assertStatus(201);

        $orderId = $response->json('data.order_id');
        $order = Order::with('suborders.items', 'suborders.shipments')->findOrFail($orderId);

        // 1 Parent order with 2 Suborders
        $this->assertCount(2, $order->suborders);

        $suborderSellers = $order->suborders->pluck('seller_id')->all();
        $this->assertContains($seller1->id, $suborderSellers);
        $this->assertContains($seller2->id, $suborderSellers);

        // Each suborder has its own shipment and tracking number
        foreach ($order->suborders as $suborder) {
            $this->assertNotEmpty($suborder->suborder_number);
            $this->assertCount(1, $suborder->shipments);
            $this->assertStringStartsWith('TRK-', $suborder->shipments->first()->tracking_number);
        }
    }

    public function test_tiered_pricing_recalculated_at_299_vs_300_boundary(): void
    {
        [$seller, $product, $variant] = $this->setupSellerWithProduct(
            'TierSeller', 'tier-seller', 'AllUnits Test', 'all-units-test', 'TIER-SKU', 5000
        );

        $customer = $this->createCustomer();

        // 1. Checkout with 299 units (Base price = 300,000 UZS)
        // Expected subtotal: 299 * 300,000 = 89,700,000 UZS
        $payload299 = [
            'items' => [['variant_id' => $variant->id, 'quantity' => 299]],
            'shipping_address' => [
                'recipient_name' => 'Dilshod',
                'phone' => '+998901112233',
                'region' => 'Toshkent',
                'city' => 'Mirobod',
                'address_line' => 'Nukus 80',
            ],
        ];

        $resp299 = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/checkout', $payload299);
        $resp299->assertStatus(201);
        $order299 = Order::findOrFail($resp299->json('data.order_id'));
        $this->assertEquals(89700000, $order299->items_total);

        // 2. Checkout with 300 units (Tier price = 270,000 UZS)
        // Expected subtotal: 300 * 270,000 = 81,000,000 UZS (< 89,700,000!)
        $payload300 = [
            'items' => [['variant_id' => $variant->id, 'quantity' => 300]],
            'shipping_address' => [
                'recipient_name' => 'Dilshod',
                'phone' => '+998901112233',
                'region' => 'Toshkent',
                'city' => 'Mirobod',
                'address_line' => 'Nukus 80',
            ],
        ];

        $resp300 = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/checkout', $payload300);
        $resp300->assertStatus(201);
        $order300 = Order::findOrFail($resp300->json('data.order_id'));
        $this->assertEquals(81000000, $order300->items_total);
        $this->assertTrue($order300->items_total < $order299->items_total);
    }

    public function test_expired_reservation_releases_stock_back_to_available_balance(): void
    {
        [$seller, $product, $variant, $warehouse, $balance] = $this->setupSellerWithProduct(
            'ExpireSeller', 'expire-seller', 'Gips Blok', 'gips-blok', 'GIPS-EXP', 100
        );

        $reservationService = app(InventoryReservationService::class);

        // Reserve 20 units
        $res = $reservationService->reserveVariantStock($variant, 20);
        $this->assertEquals(20.0, (float) $balance->fresh()->reserved_quantity);

        // Force expiry in past
        $res->update(['expires_at' => now()->subMinutes(5)]);

        // Release expired
        $releasedCount = $reservationService->releaseExpiredReservations();
        $this->assertEquals(1, $releasedCount);

        // Reserved balance returned to 0
        $this->assertEquals(0.0, (float) $balance->fresh()->reserved_quantity);
        $this->assertEquals('expired', $res->fresh()->status);

        // Idempotency: second run does not release again
        $secondRun = $reservationService->releaseExpiredReservations();
        $this->assertEquals(0, $secondRun);
        $this->assertEquals(0.0, (float) $balance->fresh()->reserved_quantity);
    }

    public function test_web_cart_and_checkout_flow(): void
    {
        [$seller, $product, $variant] = $this->setupSellerWithProduct(
            'WebSeller', 'web-seller', 'Armatura', 'armatura-web', 'ARM-WEB', 200
        );

        $customer = $this->createCustomer('+998909998877');

        // 1. Visit empty cart
        $cartResp = $this->actingAs($customer)->get('/cart');
        $cartResp->assertStatus(200);
        $cartResp->assertSee('Savatchangiz bo\'sh', false);

        // 2. Add item to cart via API
        $addResp = $this->actingAs($customer)->postJson('/api/v1/cart/items', [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);
        $addResp->assertStatus(201);

        // 3. Visit checkout page
        $checkoutGet = $this->actingAs($customer)->get('/checkout');
        $checkoutGet->assertStatus(200);
        $checkoutGet->assertSee('Buyurtmani rasmiylashtirish');
        $checkoutGet->assertSee('WebSeller');

        // 4. Submit web checkout
        $checkoutPost = $this->actingAs($customer)->post('/checkout', [
            'recipient_name' => 'Botir Web',
            'phone' => '+998909998877',
            'region' => 'Toshkent shahri',
            'city' => 'Shayxontohur tumani',
            'address_line' => 'Ko\'kcha darvoza 12',
            'notes' => 'Qo\'ng\'iroq qilib keling',
        ]);

        $checkoutPost->assertSessionHasNoErrors();
        $checkoutPost->assertRedirect();

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('Botir Web', $order->shipping_address['recipient_name']);

        // 5. Follow redirect to order success page
        $successResp = $this->actingAs($customer)->get('/orders/' . $order->order_number . '/success');
        $successResp->assertStatus(200);
        $successResp->assertSee($order->order_number);
        $successResp->assertSee('Buyurtmangiz qabul qilindi!');
    }

    public function test_guest_can_add_item_to_cart_via_web_and_view_it(): void
    {
        [$seller, $product, $variant] = $this->setupSellerWithProduct(
            'B2B Seller', 'b2b-seller', 'Gips', 'gips-test', 'GIPS-01', 500
        );

        // 1. Post to web /cart/items as guest
        $response = $this->postJson('/cart/items', [
            'variant_id' => $variant->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $cartCookie = $response->json('data.session_id');

        // 2. Visit /cart with the browser's cart cookie - it should show the item and NOT be empty!
        $cartResp = $this->withCookie('cart_session_id', $cartCookie)->get('/cart');
        $cartResp->assertStatus(200);
        $cartResp->assertDontSee('Savatchangiz hozircha bo\'sh', false);
        $cartResp->assertSee('Gips');
    }

    public function test_guest_cart_is_never_shown_to_another_visitor(): void
    {
        [$seller, $product, $variant] = $this->setupSellerWithProduct(
            'Private Seller', 'private-seller', 'Shaxsiy Tovar', 'shaxsiy-tovar', 'PRIV-01', 500
        );

        $this->withCookie('cart_session_id', 'visitor-a')->postJson('/cart/items', [
            'variant_id' => $variant->id,
            'quantity' => 3,
        ])->assertStatus(200);

        // A different browser must get its own empty cart, not visitor A's items
        $otherResp = $this->withCookie('cart_session_id', 'visitor-b')->get('/cart');
        $otherResp->assertStatus(200);
        $otherResp->assertDontSee('Shaxsiy Tovar');
    }

    public function test_guest_cannot_checkout_into_a_registered_users_account(): void
    {
        [$seller, $product, $variant] = $this->setupSellerWithProduct(
            'Guard Seller', 'guard-seller', 'Tovar', 'tovar-guard', 'GRD-01', 500
        );
        $owner = User::create([
            'name' => 'Real Owner',
            'email' => 'owner@example.uz',
            'phone' => '+998901112233',
            'password' => 'Secret123!',
            'is_active' => true,
        ]);

        $this->withCookie('cart_session_id', 'guest-x')->postJson('/cart/items', [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertStatus(200);

        $resp = $this->withCookie('cart_session_id', 'guest-x')->post('/checkout', [
            'recipient_name' => 'Impostor',
            'phone' => '+998901112233',
            'region' => 'Toshkent',
            'city' => 'Toshkent',
            'address_line' => 'Biror joy',
            'payment_method' => 'cash_on_delivery',
        ]);

        $resp->assertRedirect(route('login'));
        $this->assertEquals(0, Order::where('user_id', $owner->id)->count());
    }

    public function test_order_confirmation_page_is_private(): void
    {
        [$seller, $product, $variant] = $this->setupSellerWithProduct(
            'Priv Seller', 'priv-seller', 'Tovar P', 'tovar-p', 'PRV-01', 500
        );
        $customer = User::create([
            'name' => 'Buyer', 'email' => 'buyer-priv@example.uz', 'phone' => '+998907770011',
            'password' => 'Secret123!', 'is_active' => true,
        ]);
        $stranger = User::create([
            'name' => 'Stranger', 'email' => 'stranger@example.uz', 'phone' => '+998907770022',
            'password' => 'Secret123!', 'is_active' => true,
        ]);

        $this->actingAs($customer)->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 1])->assertStatus(201);
        $this->actingAs($customer)->post('/checkout', [
            'recipient_name' => 'Buyer', 'phone' => '+998907770011', 'region' => 'Toshkent',
            'city' => 'Toshkent', 'address_line' => 'Uy 1', 'payment_method' => 'cash_on_delivery',
        ])->assertSessionHasNoErrors();

        $order = Order::where('user_id', $customer->id)->latest()->firstOrFail();

        $this->actingAs($customer)->get('/orders/' . $order->order_number . '/success')->assertStatus(200);

        // The stranger uses a different browser: fresh session without this order remembered
        $this->flushSession();
        $this->actingAs($stranger)->get('/orders/' . $order->order_number . '/success')->assertStatus(404);
        $this->actingAs($stranger)->get('/orders/' . $order->order_number)->assertStatus(403);
        $this->actingAs($stranger)->get('/payments/checkout/' . $order->order_number)->assertStatus(404);
    }

    public function test_customer_can_cancel_unshipped_order_and_stock_is_released(): void
    {
        [$seller, $product, $variant] = $this->setupSellerWithProduct(
            'Cancel Seller', 'cancel-seller', 'Tovar C', 'tovar-c', 'CNL-01', 100
        );
        $customer = User::create([
            'name' => 'Canceller', 'email' => 'cancel@example.uz', 'phone' => '+998907770033',
            'password' => 'Secret123!', 'is_active' => true,
        ]);

        $this->actingAs($customer)->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 4])->assertStatus(201);
        $this->actingAs($customer)->post('/checkout', [
            'recipient_name' => 'Canceller', 'phone' => '+998907770033', 'region' => 'Toshkent',
            'city' => 'Toshkent', 'address_line' => 'Uy 2', 'payment_method' => 'cash_on_delivery',
        ])->assertSessionHasNoErrors();

        $order = Order::where('user_id', $customer->id)->latest()->firstOrFail();
        $this->assertEquals('processing', $order->status);
        $reservedBefore = (float) \App\Models\InventoryBalance::where('product_variant_id', $variant->id)->sum('reserved_quantity');
        $this->assertEquals(4.0, $reservedBefore);

        $this->actingAs($customer)->get('/orders/' . $order->order_number)
            ->assertStatus(200)
            ->assertSee('Buyurtmani bekor qilish');

        $this->actingAs($customer)->post('/orders/' . $order->order_number . '/cancel', ['reason' => "Fikrimni o'zgartirdim"])
            ->assertRedirect(route('orders.detail', $order->order_number));

        $this->assertEquals('cancelled', $order->fresh()->status);
        $reservedAfter = (float) \App\Models\InventoryBalance::where('product_variant_id', $variant->id)->sum('reserved_quantity');
        $this->assertEquals(0.0, $reservedAfter);

        // A cancelled order cannot be cancelled twice (stock is not released again)
        $this->actingAs($customer)->post('/orders/' . $order->order_number . '/cancel')
            ->assertSessionHas('error');
    }
}
