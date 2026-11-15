<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Role;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControlWebTest extends TestCase
{
    use RefreshDatabase;

    protected function user(string $roleName, array $attributes = []): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], ['label' => ucfirst($roleName)]);
        $user = User::create(array_merge([
            'name' => ucfirst($roleName) . ' ' . uniqid(),
            'email' => uniqid() . '@example.uz',
            'phone' => '+99890' . random_int(1000000, 9999999),
            'password' => bcrypt('Secret123!'),
            'is_active' => true,
        ], $attributes));
        $user->roles()->attach($role->id);

        return $user->fresh();
    }

    protected function order(User $customer, string $number, string $status = 'processing'): Order
    {
        return Order::create([
            'user_id' => $customer->id,
            'order_number' => $number,
            'status' => $status,
            'currency' => 'UZS',
            'items_total' => 100000,
            'shipping_total' => 0,
            'grand_total' => 100000,
            'shipping_address' => ['recipient_name' => 'Ali', 'phone' => '+998901234567', 'address' => 'Chilonzor'],
        ]);
    }

    protected function product(string $name): Product
    {
        $seller = Seller::firstOrCreate(['slug' => 'admin-test-store'], [
            'name' => 'Admin Test Do\'koni', 'legal_name' => 'Test MCHJ', 'status' => 'active', 'commission_rate' => 5, 'rating' => 5,
        ]);
        $category = Category::firstOrCreate(['slug' => 'test-toifa'], ['name' => 'Test toifa', 'is_active' => true]);

        return Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . uniqid(),
            'unit' => 'piece',
            'min_order_quantity' => 1,
            'unit_step' => 1,
            'trade_mode' => 'both',
            'status' => 'approved',
            'is_active' => true,
        ]);
    }

    public function test_admin_sees_every_order_page_by_page_and_can_search(): void
    {
        $admin = $this->user('admin');
        $customer = $this->user('customer', ['name' => 'Dilshod Karimov', 'phone' => '+998977776655']);
        for ($i = 1; $i <= 25; $i++) {
            $this->order($customer, sprintf('ORD-PAGE-%03d', $i));
        }

        // 25 orders, 20 per page: the oldest ones are on page 2, and the link keeps the orders tab
        $page1 = $this->actingAs($admin)->get(route('admin.dashboard', ['tab' => 'orders']))->assertOk();
        $page1->assertSee('Topildi: 25 ta');
        $page1->assertSee('orders_page=2', false);
        $page1->assertSee('tab=orders', false);

        $this->actingAs($admin)->get(route('admin.dashboard', ['tab' => 'orders', 'orders_page' => 2]))
            ->assertOk()
            ->assertSee('ORD-PAGE-001');

        $this->actingAs($admin)->get(route('admin.dashboard', ['tab' => 'orders', 'orders_q' => '7776655']))
            ->assertOk()
            ->assertSee('Topildi: 25 ta');

        $this->actingAs($admin)->get(route('admin.dashboard', ['tab' => 'orders', 'orders_q' => 'ORD-PAGE-013']))
            ->assertOk()
            ->assertSee('Topildi: 1 ta');
    }

    public function test_paging_one_list_does_not_page_the_others(): void
    {
        $admin = $this->user('admin');
        for ($i = 0; $i < 20; $i++) {
            $this->user('customer');
        }

        $html = $this->actingAs($admin)->get(route('admin.dashboard', ['tab' => 'users']))->assertOk()->getContent();

        $this->assertStringContainsString('users_page=2', $html);
        $this->assertStringNotContainsString('?page=2', $html);
    }

    public function test_admin_cancels_unpaid_order_but_not_paid_one(): void
    {
        $admin = $this->user('admin');
        $customer = $this->user('customer');
        $unpaid = $this->order($customer, 'ORD-UNPAID');
        $paid = $this->order($customer, 'ORD-PAID');
        Payment::create([
            'order_id' => $paid->id, 'payment_method' => 'click', 'transaction_id' => 'TX-' . uniqid(),
            'amount' => 100000, 'currency' => 'UZS', 'status' => 'paid', 'paid_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.orders.cancel', $unpaid->id), ['reason' => 'Mijoz telefonda rad etdi'])
            ->assertSessionHas('success');
        $this->assertSame('cancelled', $unpaid->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.order_cancelled', 'user_id' => $admin->id]);

        $this->actingAs($admin)->post(route('admin.orders.cancel', $paid->id), ['reason' => 'Sinov'])
            ->assertSessionHas('error');
        $this->assertSame('processing', $paid->fresh()->status);
    }

    public function test_cancelling_needs_a_reason_and_an_admin(): void
    {
        $customer = $this->user('customer');
        $order = $this->order($customer, 'ORD-REASON');

        $this->actingAs($this->user('admin'))->post(route('admin.orders.cancel', $order->id), ['reason' => ''])
            ->assertSessionHasErrors('reason');
        $this->actingAs($this->user('moderator'))->post(route('admin.orders.cancel', $order->id), ['reason' => 'Sabab bor'])
            ->assertForbidden();
        $this->actingAs($customer)->post(route('admin.orders.cancel', $order->id), ['reason' => 'Sabab bor'])
            ->assertForbidden();

        $this->assertSame('processing', $order->fresh()->status);
    }

    public function test_admin_edits_and_deletes_a_product(): void
    {
        $admin = $this->user('admin');
        $product = $this->product('Eski nom');

        $this->actingAs($admin)->post(route('admin.products.update', $product->id), [
            'name' => 'Yangi nom',
            'category_id' => $product->category_id,
            'description' => 'Aniq tavsif',
            'image_url' => '/images/products/sement.jpg',
        ])->assertSessionHas('success');

        $product->refresh();
        $this->assertSame('Yangi nom', $product->name);
        $this->assertSame('/images/products/sement.jpg', $product->image_url);

        $this->actingAs($admin)->get(route('admin.dashboard', ['tab' => 'products', 'products_q' => 'Yangi']))
            ->assertOk()
            ->assertSee('Yangi nom');

        $this->actingAs($admin)->delete(route('admin.products.delete', $product->id))->assertSessionHas('success');
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_product_image_must_be_a_web_address_and_moderators_cannot_edit(): void
    {
        $product = $this->product('Himoyalangan');

        $this->actingAs($this->user('admin'))->post(route('admin.products.update', $product->id), [
            'name' => 'Himoyalangan', 'category_id' => $product->category_id, 'image_url' => 'javascript:alert(1)',
        ])->assertSessionHasErrors('image_url');

        $this->actingAs($this->user('moderator'))->post(route('admin.products.update', $product->id), [
            'name' => 'Buzilgan', 'category_id' => $product->category_id,
        ])->assertForbidden();
        $this->actingAs($this->user('moderator'))->delete(route('admin.products.delete', $product->id))->assertForbidden();

        $this->assertSame('Himoyalangan', $product->fresh()->name);
    }

    public function test_admin_can_create_user_with_role(): void
    {
        $admin = $this->user('admin');
        Role::firstOrCreate(['name' => 'seller_owner'], ['label' => 'Do\'kon egasi']);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Yangi Sotuvchi Aka',
            'email' => 'new_seller@market.uz',
            'phone' => '+998901234999',
            'password' => 'Password123!',
            'role' => 'seller_owner',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'users']));
        $response->assertSessionHas('success');

        $newUser = User::where('email', 'new_seller@market.uz')->first();
        $this->assertNotNull($newUser);
        $this->assertSame('Yangi Sotuvchi Aka', $newUser->name);
        $this->assertTrue($newUser->hasRole('seller_owner'));
    }

    public function test_admin_can_update_user_details(): void
    {
        $admin = $this->user('admin');
        $customer = $this->user('customer', ['name' => 'Eski Ism', 'email' => 'eski@market.uz', 'phone' => '+998901110000']);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $customer->id), [
            'name' => 'Yangilangan Ism',
            'email' => 'yangilangan@market.uz',
            'phone' => '+998902220000',
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'users']));
        $response->assertSessionHas('success');

        $customer->refresh();
        $this->assertSame('Yangilangan Ism', $customer->name);
        $this->assertSame('yangilangan@market.uz', $customer->email);
        $this->assertSame('+998902220000', $customer->phone);
    }

    public function test_admin_can_delete_user(): void
    {
        $admin = $this->user('admin');
        $customer = $this->user('customer');

        $response = $this->actingAs($admin)->delete(route('admin.users.delete', $customer->id));

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'users']));
        $response->assertSessionHas('success');

        $this->assertNull(User::find($customer->id));
    }

    public function test_admin_can_update_order_status(): void
    {
        $admin = $this->user('admin');
        $customer = $this->user('customer');
        $order = $this->order($customer, 'ORD-STATUS-TEST-1', 'pending_payment');

        $response = $this->actingAs($admin)->post(route('admin.orders.status', $order->id), [
            'status' => 'processing',
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'orders']));
        $response->assertSessionHas('success');
        $this->assertSame('processing', $order->fresh()->status);
    }

    public function test_admin_can_update_platform_settings(): void
    {
        $admin = $this->user('admin');

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'site_name' => 'Marketplace Yangi Nomi',
            'site_tagline' => 'Eng zo\'r bozor',
            'contact_phone' => '+998 71 999-99-99',
            'contact_email' => 'support@market.uz',
            'office_address' => 'Toshkent sh.',
            'default_commission_rate' => 7.5,
            'standard_delivery_fee' => 30000,
            'free_delivery_threshold' => 500000,
            'registration_enabled' => '1',
            'maintenance_mode' => '0',
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'settings']));
        $response->assertSessionHas('success');

        $this->assertSame('Marketplace Yangi Nomi', \App\Models\PlatformSetting::get('site_name'));
        $this->assertSame('7.5', \App\Models\PlatformSetting::get('default_commission_rate'));
    }

    public function test_admin_dashboard_renders_settings_and_user_crud_buttons(): void
    {
        $admin = $this->user('admin');

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['tab' => 'users']));
        $response->assertOk();
        $response->assertSee('Yangi Foydalanuvchi');
        $response->assertSee('Tahrirlash');
        $response->assertSee('Sozlamalar');
    }
}
