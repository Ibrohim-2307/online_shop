<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ProductImageSeeder extends Seeder
{
    /**
     * Seed images for all products.
     */
    public function run(): void
    {
        if (!Schema::hasColumn('products', 'image_url')) {
            return;
        }

        $map = [
            'sement-bekobod-m500' => '/images/products/sement.jpg',
            'qurilish-sementi-m500' => '/images/products/sement.jpg',
            'sanoat-generator-300kw' => '/images/products/generator.jpg',
            'po-lat-armatura-a500c-d12mm' => '/images/products/armatura.jpg',
            'armatura-a500c' => '/images/products/armatura.jpg',
            'ichimlik-suvi-nestle-18-9l' => '/images/products/suv.jpg',
            'mineral-suv-15l' => '/images/products/suv.jpg',
            'gipsokarton-knauf-95mm' => '/images/products/gipsokarton.jpg',
            'pishiq-qizil-gisht-m125' => '/images/products/gisht.jpg',
            'shlakoblok-devorbop-20x20x40' => '/images/products/shlakoblok.jpg',
            'kafel-yelimi-knauf-perlfix-25kg' => '/images/products/kafel_yelimi.jpg',
            'akril-fasad-boyogi-mastercolor-20kg' => '/images/products/boyoq.jpg',
            'mis-elektr-kabeli-vvg-p-3x25' => '/images/products/kabel.jpg',
            'led-sanoat-projektori-200w-ip66' => '/images/products/proyektor.jpg',
            'bazalt-mineral-paxta-teploizol-50mm' => '/images/products/mineral_paxta.jpg',
            'polipropilen-quvur-pn20-d25mm-4m' => '/images/products/quvur.jpg',
            'samarqand-bugdoy-uni-oltin-don-50kg' => '/images/products/un.jpg',
            'paxta-yogi-zilol-5l-quti' => '/images/products/yog.jpg',
            'lazerli-nivelir-4d-16-liniyali-prolaser' => '/images/products/nivelir.jpg',
            'payvandlash-apparati-invertor-igbt-250a' => '/images/products/payvandlash.jpg',
            'keramogranit-plitasi-60x60-sm' => '/images/products/plitka.jpg',
            'polat-profil-truba-40x20x2mm-6m' => '/images/products/profil_truba.jpg',
            'porshenli-havo-kompressori-100l' => '/images/products/kompressor.jpg',
        ];

        foreach ($map as $slug => $imageUrl) {
            Product::where('slug', $slug)->update(['image_url' => $imageUrl]);
        }

        // For any remaining product without an image, set a clean default from available images
        $defaultImages = [
            'qurilish-materiallari' => '/images/products/sement.jpg',
            'sanoat-uskunalari' => '/images/products/generator.jpg',
            'oziq-ovqat-va-ichimliklar' => '/images/products/un.jpg',
            'elektr-jihozlari' => '/images/products/kabel.jpg',
            'boyoqlar-va-kimyo' => '/images/products/boyoq.jpg',
            'santexnika-va-quvurlar' => '/images/products/quvur.jpg',
            'asbob-uskunalar' => '/images/products/nivelir.jpg',
        ];

        $products = Product::whereNull('image_url')->orWhere('image_url', '')->with('category')->get();
        foreach ($products as $p) {
            $catSlug = $p->category->slug ?? 'qurilish-materiallari';
            $p->update(['image_url' => $defaultImages[$catSlug] ?? '/images/products/sement.jpg']);
        }
    }
}
