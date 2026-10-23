<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\Suborder;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    protected function createProductFixture(): Product
    {
        $seller = Seller::create([
            'name' => 'StroyBaza Grand',
            'slug' => 'stroybaza-grand',
            'legal_name' => 'StroyBaza Grand MCHJ',
            'tax_id' => '308123456',
            'status' => 'active',
            'rating' => 4.90,
        ]);

        $category = Category::create([
            'name' => 'Qurilish materiallari',
            'slug' => 'qurilish-materiallari',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Qurilish Sementi M-500',
            'slug' => 'sement-m500',
            'description' => 'Yuqori sifatli portland sement M-500.',
            'trade_mode' => 'both',
            'unit' => 'piece',
            'unit_step' => 1.000,
            'min_order_quantity' => 1.000,
            'status' => 'approved',
            'is_active' => true,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SEM-500-50KG',
            'name' => '50 kg qop',
            'retail_price' => 78000,
            'wholesale_price' => 72000,
            'is_active' => true,
        ]);

        return $product;
    }

    public function test_guest_can_submit_product_review(): void
    {
        $product = $this->createProductFixture();

        $response = $this->post(route('product.reviews.store', $product->slug), [
            'author_name' => 'Akmal Rahimov',
            'rating' => 5,
            'comment' => 'Mahsulot sifati juda zo\'r, o\'z vaqtida yetkazib berildi.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success_review');

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'author_name' => 'Akmal Rahimov',
            'rating' => 5,
            'is_verified_purchase' => false,
            'is_approved' => true,
        ]);
    }

    public function test_review_validation_rules(): void
    {
        $product = $this->createProductFixture();

        $response = $this->post(route('product.reviews.store', $product->slug), [
            'author_name' => '',
            'rating' => 6, // invalid > 5
            'comment' => 'Hi', // too short (<3)
        ]);

        $response->assertSessionHasErrors(['author_name', 'rating', 'comment']);
        $this->assertEquals(0, ProductReview::count());
    }

    public function test_authenticated_customer_with_completed_order_gets_verified_purchase_tag(): void
    {
        $product = $this->createProductFixture();
        $user = User::factory()->create([
            'name' => 'Sardorbek Alimov',
            'email' => 'sardor@example.com',
        ]);

        // Create an order & order item for this user and product
        $order = Order::create([
            'order_number' => 'ORD-REV-001',
            'user_id' => $user->id,
            'status' => 'completed',
            'currency' => 'UZS',
            'items_total' => 156000,
            'shipping_total' => 0,
            'grand_total' => 156000,
            'shipping_address' => ['city' => 'Toshkent', 'address' => 'Chilonzor 9'],
        ]);

        $suborder = Suborder::create([
            'order_id' => $order->id,
            'seller_id' => $product->seller_id,
            'suborder_number' => 'ORD-REV-001-1',
            'status' => 'delivered',
            'items_subtotal' => 156000,
            'shipping_cost' => 0,
            'total_amount' => 156000,
        ]);

        $variant = $product->variants->first();

        OrderItem::create([
            'suborder_id' => $suborder->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_sku' => $variant->sku,
            'unit' => 'piece',
            'quantity' => 2,
            'unit_price' => 78000,
            'subtotal' => 156000,
        ]);

        // Submit review as authenticated user
        $response = $this->actingAs($user)->post(route('product.reviews.store', $product->slug), [
            'author_name' => $user->name,
            'rating' => 5,
            'comment' => 'Haqiqatan ham original mahsulot, usta ham ma\'qulladi!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success_review');

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'is_verified_purchase' => true,
        ]);
    }

    public function test_product_average_rating_and_breakdown_calculation(): void
    {
        $product = $this->createProductFixture();

        // Create 3 reviews: 5, 4, 3 stars
        ProductReview::create([
            'product_id' => $product->id,
            'author_name' => 'User 1',
            'rating' => 5,
            'comment' => 'Ajoyib sifat!',
            'is_approved' => true,
        ]);
        ProductReview::create([
            'product_id' => $product->id,
            'author_name' => 'User 2',
            'rating' => 4,
            'comment' => 'Yaxshi, narxiga arziydi.',
            'is_approved' => true,
        ]);
        ProductReview::create([
            'product_id' => $product->id,
            'author_name' => 'User 3',
            'rating' => 3,
            'comment' => 'O\'rtacha, qadoqda ozgina shikast bor edi.',
            'is_approved' => true,
        ]);

        // Average = (5 + 4 + 3) / 3 = 4.0
        $this->assertEquals(3, $product->reviews_count);
        $this->assertEquals(4.0, $product->average_rating);

        $breakdown = $product->rating_breakdown;
        $this->assertEquals(1, $breakdown[5]['count']);
        $this->assertEquals(33, $breakdown[5]['percentage']);
        $this->assertEquals(1, $breakdown[4]['count']);
        $this->assertEquals(33, $breakdown[4]['percentage']);
        $this->assertEquals(1, $breakdown[3]['count']);
        $this->assertEquals(33, $breakdown[3]['percentage']);
        $this->assertEquals(0, $breakdown[2]['count']);
        $this->assertEquals(0, $breakdown[1]['count']);
    }

    public function test_product_detail_page_displays_reviews_and_form(): void
    {
        $product = $this->createProductFixture();

        ProductReview::create([
            'product_id' => $product->id,
            'author_name' => 'Jamshid Usmonov',
            'rating' => 5,
            'comment' => 'Menga juda yoqdi, yana zakaz beraman!',
            'is_verified_purchase' => true,
            'is_approved' => true,
        ]);

        $response = $this->get(route('product.show', $product->slug));
        $response->assertOk();
        $response->assertSee('Jamshid Usmonov');
        $response->assertSee('Menga juda yoqdi, yana zakaz beraman!');
        $response->assertSee('Tasdiqlangan xarid'); // Verified purchase badge text
        $response->assertSee('Mijozlar Sharhlari');
    }
}
