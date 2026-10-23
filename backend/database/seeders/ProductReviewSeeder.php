<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Database\Seeder;

class ProductReviewSeeder extends Seeder
{
    /**
     * Seed realistic Uzbek customer reviews and ratings.
     */
    public function run(): void
    {
        $reviewsPool = [
            [
                'author_name' => 'Dilshod Raximov',
                'rating' => 5,
                'comment' => 'Juda sifatli mahsulot! Qurilishimiz uchun katta hajmda buyurtma bergandik, aytilgan vaqtda kuryer olib keldi. Ayniqsa optom narxlari juda qulay.',
                'is_verified_purchase' => true,
                'likes_count' => 14,
            ],
            [
                'author_name' => 'Akrom Boboyev',
                'rating' => 5,
                'comment' => 'Original mahsulot, sertifikatlari ham bor ekan. Usta ham ko\'rib ma\'qulladi. Tavsiya qilaman!',
                'is_verified_purchase' => true,
                'likes_count' => 8,
            ],
            [
                'author_name' => 'Sherzod Mahmudov',
                'rating' => 5,
                'comment' => 'Yetkazib berish xizmati qoyilmaqom! Xaritada kuryer qayerda kelayotganini jonli kuzatib turdik. To\'lovni Click orqali qildim, chek darhol chiqdi.',
                'is_verified_purchase' => true,
                'likes_count' => 19,
            ],
            [
                'author_name' => 'Bobur Mirzayev',
                'rating' => 4,
                'comment' => 'Mahsulot a\'lo darajada, o\'ramlari butun keldi. Bir oz kechroq yetkazishdi, lekin ogohlantirishgani uchun e\'tirozim yo\'q. Rahmat!',
                'is_verified_purchase' => true,
                'likes_count' => 5,
            ],
            [
                'author_name' => 'Otabek Qodirov',
                'rating' => 5,
                'comment' => 'Ikkinchi marta xarid qilyapman. Sifat barqaror, narxi ham bozorga nisbatan 10-15% arzonroqqa tushdi.',
                'is_verified_purchase' => false,
                'likes_count' => 11,
            ],
            [
                'author_name' => 'Sanjar Aliyev',
                'rating' => 5,
                'comment' => 'Haqiqiy birinchi qo\'l ulgurji narxlar! Katta ob\'ekt qurilishi uchun juda mos platforma ekan.',
                'is_verified_purchase' => true,
                'likes_count' => 7,
            ],
        ];

        $products = Product::where('status', 'approved')->get();

        foreach ($products as $product) {
            // Check if product already has reviews
            if (ProductReview::where('product_id', $product->id)->exists()) {
                continue;
            }

            // Assign 3-4 random reviews to each product
            $sampleReviews = collect($reviewsPool)->shuffle()->take(rand(3, 5));

            foreach ($sampleReviews as $rev) {
                ProductReview::create([
                    'product_id' => $product->id,
                    'user_id' => null,
                    'author_name' => $rev['author_name'],
                    'rating' => $rev['rating'],
                    'comment' => $rev['comment'],
                    'is_verified_purchase' => $rev['is_verified_purchase'],
                    'is_approved' => true,
                    'likes_count' => $rev['likes_count'],
                    'created_at' => now()->subDays(rand(1, 20))->subHours(rand(1, 12)),
                ]);
            }
        }
    }
}
