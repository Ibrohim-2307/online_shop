<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryBalance;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Refund;
use App\Models\Role;
use App\Models\Seller;
use App\Models\SellerLedger;
use App\Models\Suborder;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Ledger\SellerLedgerService;
use App\Services\Payment\DemoPaymentAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAndLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected function setupUserWithRole(string $roleName, string $email): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], [
            'label' => ucfirst($roleName),
        ]);

        $user = User::create([
            'name' => ucfirst($roleName) . ' User',
            'email' => $email,
            'phone' => '+99890' . rand(1000000, 9999999),
            'password' => bcrypt('Secret123!'),
            'is_active' => true,
        ]);

        $user->roles()->attach($role->id);

        return $user;
    }

    protected function setupOrder(User $customer, int $itemsSubtotal = 300000, int $shippingCost = 25000, float $commissionRate = 5.0): array
    {
        $seller = Seller::create([
            'name' => 'Akfa Qurilish Do\'koni',
            'slug' => 'akfa-qurilish-' . uniqid(),
            'legal_name' => 'Akfa Qurilish MCHJ',
            'tax_id' => '30' . rand(1000000, 9999999),
            'status' => 'active',
            'commission_rate' => $commissionRate,
            'rating' => 4.9,
        ]);

        $warehouse = Warehouse::create([
            'seller_id' => $seller->id,
            'name' => 'Toshkent Asosiy Ombor',
            'code' => 'WH-TOSHKENT-' . uniqid(),
            'address' => 'Toshkent sh., Chilonzor 1-mavze',
            'is_active' => true,
        ]);

        $category = Category::firstOrCreate(['slug' => 'qurilish-test'], [
            'name' => 'Qurilish Materiallari',
            'is_active' => true,
        ]);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Sement M500',
            'slug' => 'sement-m500-' . uniqid(),
            'unit' => 'piece',
            'min_order_quantity' => 1.0,
            'unit_step' => 1.0,
            'trade_mode' => 'both',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SEM-M500-' . uniqid(),
            'name' => 'Sement M500 50kg',
            'price' => $itemsSubtotal,
            'currency' => 'UZS',
        ]);

        $balance = InventoryBalance::create([
            'warehouse_id' => $warehouse->id,
            'product_variant_id' => $variant->id,
            'quantity' => 500.0,
            'safety_stock' => 10.0,
        ]);

        $grandTotal = $itemsSubtotal + $shippingCost;
        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => $orderNumber,
            'status' => 'pending_payment',
            'items_total' => $itemsSubtotal,
            'shipping_total' => $shippingCost,
            'grand_total' => $grandTotal,
            'currency' => 'UZS',
            'shipping_address' => [
                'recipient_name' => 'Aziz Xaridor',
                'phone' => '+998901234567',
                'address' => 'Navoiy ko\'chasi, 12-uy',
            ],
        ]);

        $suborderNumber = 'SUB-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
        $commissionAmount = (int) round(($itemsSubtotal * $commissionRate) / 100);

        $suborder = Suborder::create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'suborder_number' => $suborderNumber,
            'status' => 'pending',
            'items_subtotal' => $itemsSubtotal,
            'shipping_cost' => $shippingCost,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'total_amount' => $grandTotal,
        ]);

        $orderItem = OrderItem::create([
            'suborder_id' => $suborder->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_sku' => $variant->sku,
            'unit' => 'piece',
            'quantity' => 1.0,
            'unit_price' => $itemsSubtotal,
            'subtotal' => $itemsSubtotal,
        ]);

        $reservation = InventoryReservation::create([
            'order_id' => $order->id,
            'warehouse_id' => $warehouse->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1.0,
            'status' => 'reserved',
            'expires_at' => now()->addMinutes(15),
        ]);

        return compact('seller', 'warehouse', 'product', 'variant', 'balance', 'order', 'suborder', 'orderItem', 'reservation');
    }

    public function test_demo_payment_initiation_generates_valid_session(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust1@example.uz');
        $setup = $this->setupOrder($customer);
        $order = $setup['order'];

        $response = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/payments/initiate', [
            'order_id' => $order->id,
            'payment_method' => 'demo_sandbox',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.amount', $order->grand_total);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'amount' => $order->grand_total,
            'status' => 'pending',
        ]);
    }

    public function test_payment_redirect_never_marks_order_as_paid(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust2@example.uz');
        $setup = $this->setupOrder($customer);
        $order = $setup['order'];

        // Customer initiates payment and receives redirect URL
        $response = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/payments/initiate', [
            'order_id' => $order->id,
            'payment_method' => 'demo_sandbox',
        ]);

        $paymentUrl = $response->json('data.payment_url');
        $this->assertNotEmpty($paymentUrl);

        // Verification: order and payment MUST still be pending, never paid by simply having a redirect
        $this->assertEquals('pending_payment', $order->fresh()->status);
        $this->assertEquals('pending', Payment::where('order_id', $order->id)->first()->status);
    }

    public function test_webhook_requires_valid_cryptographic_hmac_signature(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust3@example.uz');
        $setup = $this->setupOrder($customer);
        $order = $setup['order'];

        $initRes = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/payments/initiate', [
            'order_id' => $order->id,
        ]);
        $transactionId = $initRes->json('data.transaction_id');

        // Send webhook with invalid/fake signature
        $payload = [
            'event_id' => 'evt_' . uniqid(),
            'transaction_id' => $transactionId,
            'order_id' => $order->id,
            'amount' => $order->grand_total,
            'status' => 'success',
        ];

        $response = $this->postJson('/api/v1/payments/webhook', $payload, [
            'X-Payment-Signature' => 'fake_invalid_tampered_hmac_signature_hash',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'WEBHOOK_VERIFICATION_FAILED');

        $this->assertEquals('pending_payment', $order->fresh()->status);
    }

    public function test_valid_signed_webhook_settles_payment_commits_reservations_and_credits_ledger(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust4@example.uz');
        $setup = $this->setupOrder($customer, 300000, 25000, 5.0); // 300k items, 25k ship, 15k commission
        $order = $setup['order'];
        $seller = $setup['seller'];

        $initRes = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/payments/initiate', [
            'order_id' => $order->id,
        ]);
        $transactionId = $initRes->json('data.transaction_id');

        $secretKey = config('services.payment.demo_secret', 'marketplace-sandbox-hmac-secret-key-2026');
        $eventId = 'evt_valid_' . uniqid();
        $signature = hash_hmac('sha256', "{$eventId}:{$transactionId}:{$order->id}:{$order->grand_total}", $secretKey);

        $payload = [
            'event_id' => $eventId,
            'transaction_id' => $transactionId,
            'order_id' => $order->id,
            'amount' => $order->grand_total,
            'status' => 'success',
        ];

        $response = $this->postJson('/api/v1/payments/webhook', $payload, [
            'X-Payment-Signature' => $signature,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'success');

        // Check payment marked paid
        $this->assertDatabaseHas('payments', [
            'transaction_id' => $transactionId,
            'status' => 'paid',
        ]);

        // Check order status updated
        $this->assertEquals('processing', $order->fresh()->status);

        // Check reservations committed
        $this->assertEquals('committed', $setup['reservation']->fresh()->status);

        // Check webhook event recorded
        $this->assertDatabaseHas('webhook_events', [
            'event_id' => $eventId,
            'status' => 'processed',
        ]);

        // Check double-entry seller ledger:
        // 1. Credit 300,000 UZS (order sale)
        // 2. Debit 15,000 UZS (5% platform commission)
        $this->assertDatabaseHas('seller_ledgers', [
            'seller_id' => $seller->id,
            'type' => 'credit',
            'entry_type' => 'order_sale',
            'amount' => 300000,
        ]);

        $this->assertDatabaseHas('seller_ledgers', [
            'seller_id' => $seller->id,
            'type' => 'debit',
            'entry_type' => 'commission_fee',
            'amount' => 15000,
        ]);

        $ledgerService = app(SellerLedgerService::class);
        $netBalance = $ledgerService->getSellerBalance($seller);
        $this->assertEquals(285000, $netBalance); // 300k - 15k = 285k
    }

    public function test_webhook_is_strictly_idempotent_and_prevents_duplicate_settlement(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust5@example.uz');
        $setup = $this->setupOrder($customer);
        $order = $setup['order'];
        $seller = $setup['seller'];

        $initRes = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/payments/initiate', [
            'order_id' => $order->id,
        ]);
        $transactionId = $initRes->json('data.transaction_id');

        $secretKey = config('services.payment.demo_secret', 'marketplace-sandbox-hmac-secret-key-2026');
        $eventId = 'evt_duplicate_' . uniqid();
        $signature = hash_hmac('sha256', "{$eventId}:{$transactionId}:{$order->id}:{$order->grand_total}", $secretKey);

        $payload = [
            'event_id' => $eventId,
            'transaction_id' => $transactionId,
            'order_id' => $order->id,
            'amount' => $order->grand_total,
            'status' => 'success',
        ];

        // 1st request
        $res1 = $this->postJson('/api/v1/payments/webhook', $payload, [
            'X-Payment-Signature' => $signature,
        ]);
        $res1->assertStatus(200)->assertJsonPath('data.status', 'success');

        // 2nd duplicate request
        $res2 = $this->postJson('/api/v1/payments/webhook', $payload, [
            'X-Payment-Signature' => $signature,
        ]);
        $res2->assertStatus(200)->assertJsonPath('data.status', 'already_processed');

        // Verify ledger entries are not duplicated
        $creditsCount = SellerLedger::where('seller_id', $seller->id)->where('type', 'credit')->count();
        $this->assertEquals(1, $creditsCount);
    }

    public function test_ledger_balance_equality_across_sellers_and_platform(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust6@example.uz');
        $setup = $this->setupOrder($customer, 500000, 30000, 10.0);
        $order = $setup['order']->fresh(['suborders']);

        $ledgerService = app(SellerLedgerService::class);
        $isEqual = $ledgerService->verifyOrderBalanceEquality($order);

        $this->assertTrue($isEqual);
    }

    public function test_customer_can_request_refund_and_prevents_over_refund(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust7@example.uz');
        $setup = $this->setupOrder($customer, 100000, 20000, 5.0); // Grand total: 120,000 UZS
        $order = $setup['order'];

        // Mark payment paid
        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'demo_sandbox',
            'transaction_id' => 'TXN-PAID-' . uniqid(),
            'amount' => $order->grand_total,
            'currency' => 'UZS',
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Attempt over-refund (130,000 > 120,000 grand total)
        $overResponse = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/refunds', [
            'order_id' => $order->id,
            'amount' => 130000,
            'reason' => 'Mahsulot sifatsiz chiqdi, qaytarmoqchiman',
        ]);

        $overResponse->assertStatus(422)
            ->assertJsonPath('error.code', 'REFUND_REQUEST_FAILED');

        // Valid partial refund request (50,000 UZS)
        $validResponse = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/refunds', [
            'order_id' => $order->id,
            'amount' => 50000,
            'reason' => 'Bitta quti shikastlangan holatda yetib keldi',
        ]);

        $validResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.amount', 50000)
            ->assertJsonPath('data.status', 'requested');
    }

    public function test_moderator_reviews_and_operator_completes_refund_with_inventory_restock(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust8@example.uz');
        $moderator = $this->setupUserWithRole('moderator', 'mod1@example.uz');
        $operator = $this->setupUserWithRole('admin', 'admin1@example.uz');

        $setup = $this->setupOrder($customer, 200000, 20000, 5.0);
        $order = $setup['order'];
        $seller = $setup['seller'];
        $variant = $setup['variant'];
        $balance = $setup['balance'];

        // Initial inventory quantity
        $initialStock = (float) $balance->quantity;

        // Payment and initial ledger credit
        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'demo_sandbox',
            'transaction_id' => 'TXN-' . uniqid(),
            'amount' => $order->grand_total,
            'currency' => 'UZS',
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        app(SellerLedgerService::class)->recordOrderSale($setup['suborder']);

        // 1. Customer requests refund for 200,000 UZS
        $refundRes = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/refunds', [
            'order_id' => $order->id,
            'amount' => 200000,
            'reason' => 'Qurilish to\'xtatildi, tovar qaytariladi',
        ]);
        $refundId = $refundRes->json('data.id');

        // 2. Moderator approves refund
        $reviewRes = $this->actingAs($moderator, 'sanctum')->postJson("/api/v1/refunds/{$refundId}/review", [
            'approve' => true,
            'notes' => 'Qaytarish hujjati tasdiqlandi, tovar omborga yo\'naltirildi.',
        ]);
        $reviewRes->assertStatus(200)->assertJsonPath('data.status', 'approved');

        // 3. Warehouse operator restocks goods and completes refund
        $restockRes = $this->actingAs($operator, 'sanctum')->postJson("/api/v1/refunds/{$refundId}/restock");
        $restockRes->assertStatus(200)->assertJsonPath('data.status', 'completed');

        // Verification 1: Inventory balance restored (+1.0 unit)
        $newStock = (float) $balance->fresh()->quantity;
        $this->assertEquals($initialStock + 1.0, $newStock);

        // Verification 2: Inventory movement record created
        $this->assertDatabaseHas('inventory_movements', [
            'product_variant_id' => $variant->id,
            'type' => 'inflow',
            'reference_type' => 'Refund',
            'reference_id' => $refundId,
        ]);

        // Verification 3: Ledger debit deducted from seller
        $this->assertDatabaseHas('seller_ledgers', [
            'seller_id' => $seller->id,
            'type' => 'debit',
            'entry_type' => 'refund_deduction',
            'amount' => 200000,
        ]);

        // Verification 4: Idempotent call to restock again fails safely
        $secondRestock = $this->actingAs($operator, 'sanctum')->postJson("/api/v1/refunds/{$refundId}/restock");
        $secondRestock->assertStatus(200)->assertJsonPath('data.status', 'already_completed');
    }

    public function test_payout_hold_and_dispute_block(): void
    {
        $customer = $this->setupUserWithRole('customer', 'cust9@example.uz');
        $setup = $this->setupOrder($customer, 1000000, 50000, 5.0);
        $seller = $setup['seller'];

        $ledgerService = app(SellerLedgerService::class);
        $ledgerService->recordOrderSale($setup['suborder']);

        // 1. Immediately after sale: 7-day hold blocks payout
        $canPayout = $ledgerService->canRequestPayout($seller, 500000);
        $this->assertFalse($canPayout['allowed']);
        $this->assertStringContainsString('Hold period', $canPayout['reason']);

        // 2. Simulate sale created 8 days ago
        SellerLedger::where('seller_id', $seller->id)->update(['created_at' => now()->subDays(8)]);

        $canPayoutOlder = $ledgerService->canRequestPayout($seller, 500000);
        $this->assertTrue($canPayoutOlder['allowed']);

        // 3. Active dispute blocks payout
        Refund::create([
            'order_id' => $setup['order']->id,
            'suborder_id' => $setup['suborder']->id,
            'user_id' => $customer->id,
            'refund_number' => 'REF-DISP-' . uniqid(),
            'amount' => 200000,
            'reason' => 'Nizo ochildi',
            'status' => 'requested',
            'is_inventory_restocked' => false,
        ]);

        $canPayoutWithDispute = $ledgerService->canRequestPayout($seller, 500000);
        $this->assertFalse($canPayoutWithDispute['allowed']);
        $this->assertStringContainsString('Dispute block', $canPayoutWithDispute['reason']);
    }
}
