import '../../../../core/network/api_client.dart';
import '../domain/product_model.dart';

class CatalogRepository {
  final ApiClient _client = ApiClient();

  List<ProductModel> _cachedProducts = [];
  List<CategoryModel> _cachedCategories = [];

  Future<List<ProductModel>> getProducts({
    String? category,
    String? categorySlug,
    String? tradeMode,
    String? search,
  }) async {
    final queryParams = <String, String>{};
    final cat = categorySlug ?? category;
    if (cat != null && cat.isNotEmpty) queryParams['category'] = cat;
    if (tradeMode != null && tradeMode.isNotEmpty) queryParams['trade_mode'] = tradeMode;
    if (search != null && search.isNotEmpty) queryParams['search'] = search;

    final queryString = queryParams.isNotEmpty
        ? '?${queryParams.entries.map((e) => '${e.key}=${Uri.encodeComponent(e.value)}').join('&')}'
        : '';

    try {
      final response = await _client.get('/products$queryString');

      List<dynamic> items = [];
      if (response is Map<String, dynamic>) {
        if (response.containsKey('data')) {
          final data = response['data'];
          if (data is Map<String, dynamic> && data.containsKey('data')) {
            items = data['data'] as List<dynamic>;
          } else if (data is List) {
            items = data;
          }
        }
      } else if (response is List) {
        items = response;
      }

      if (items.isNotEmpty) {
        final products = items.map((p) => ProductModel.fromJson(p as Map<String, dynamic>)).toList();
        _cachedProducts = products;
        return products;
      }

      return _cachedProducts.isNotEmpty ? _cachedProducts : _getFallbackProducts();
    } catch (_) {
      return _cachedProducts.isNotEmpty ? _cachedProducts : _getFallbackProducts();
    }
  }

  Future<List<CategoryModel>> getCategories() async {
    try {
      final response = await _client.get('/categories');
      List<dynamic> cats = [];
      if (response is List) {
        cats = response;
      } else if (response is Map<String, dynamic> && response.containsKey('data') && response['data'] is List) {
        cats = response['data'] as List;
      }

      if (cats.isNotEmpty) {
        final list = cats.map((c) => CategoryModel.fromJson(c as Map<String, dynamic>)).toList();
        _cachedCategories = list;
        return list;
      }

      return _cachedCategories.isNotEmpty ? _cachedCategories : _getFallbackCategories();
    } catch (_) {
      return _cachedCategories.isNotEmpty ? _cachedCategories : _getFallbackCategories();
    }
  }

  List<CategoryModel> _getFallbackCategories() {
    return [
      CategoryModel(id: 1, name: 'Qurilish materiallari', slug: 'qurilish-materiallari'),
      CategoryModel(id: 2, name: 'Sanoat uskunalari', slug: 'sanoat-uskunalari'),
      CategoryModel(id: 3, name: 'Oziq-ovqat va ichimliklar', slug: 'oziq-ovqat-va-ichimliklar'),
      CategoryModel(id: 4, name: 'Elektronika & Texnika', slug: 'elektronika-texnika'),
      CategoryModel(id: 5, name: 'Santexnika & Isitish', slug: 'santexnika-isitish'),
      CategoryModel(id: 6, name: 'Avtomobil jihozlari', slug: 'avtomobil-jihozlari'),
    ];
  }

  List<ProductModel> _getFallbackProducts() {
    return [
      ProductModel(
        id: 1,
        name: 'Qurilish Sementi M-500 (50kg)',
        slug: 'sement-m500',
        unit: 'piece',
        minOrderQuantity: 1,
        unitStep: 1,
        tradeMode: 'both',
        sellerName: 'StroyBaza Ulgurji Savdo',
        imageUrl: 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=600&auto=format&fit=crop&q=80',
        variants: [
          ProductVariant(
            id: 1,
            sku: 'CEMENT-M500-BAG',
            name: '50 kg qop',
            price: 300000,
            priceTiers: [
              PriceTier(minQuantity: 1, maxQuantity: 299, unitPrice: 300000),
              PriceTier(minQuantity: 300, maxQuantity: 999, unitPrice: 270000),
              PriceTier(minQuantity: 1000, maxQuantity: null, unitPrice: 250000),
            ],
          ),
        ],
      ),
      ProductModel(
        id: 2,
        name: 'Samarqand Bug\'doy Uni 1-nav (50kg)',
        slug: 'bugdoy-uni-1nav',
        unit: 'piece',
        minOrderQuantity: 2,
        unitStep: 1,
        tradeMode: 'both',
        sellerName: 'Oltin Don MCHJ',
        imageUrl: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600&auto=format&fit=crop&q=80',
        variants: [
          ProductVariant(
            id: 2,
            sku: 'FLOUR-50KG-BAG',
            name: '50 kg qop',
            price: 310000,
            priceTiers: [
              PriceTier(minQuantity: 2, maxQuantity: 99, unitPrice: 310000),
              PriceTier(minQuantity: 100, maxQuantity: null, unitPrice: 285000),
            ],
          ),
        ],
      ),
      ProductModel(
        id: 3,
        name: 'Tog\' Chashmasi Mineral Suvi 1.5L (12 talik blok)',
        slug: 'mineral-suv-15l',
        unit: 'box',
        minOrderQuantity: 5,
        unitStep: 1,
        tradeMode: 'both',
        sellerName: 'Aqua Pure Savdo',
        imageUrl: 'https://images.unsplash.com/photo-1548839140-29a749e1bc4e?w=600&auto=format&fit=crop&q=80',
        variants: [
          ProductVariant(
            id: 3,
            sku: 'WATER-15L-BOX12',
            name: '12 talik blok',
            price: 48000,
            priceTiers: [
              PriceTier(minQuantity: 5, maxQuantity: 49, unitPrice: 48000),
              PriceTier(minQuantity: 50, maxQuantity: null, unitPrice: 42000),
            ],
          ),
        ],
      ),
      ProductModel(
        id: 4,
        name: 'Bosch Akkumulyatorli Drel-shurupovyort 18V',
        slug: 'bosch-drel-18v',
        unit: 'piece',
        minOrderQuantity: 1,
        unitStep: 1,
        tradeMode: 'both',
        sellerName: 'Profi Uskunalar MCHJ',
        imageUrl: 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=600&auto=format&fit=crop&q=80',
        variants: [
          ProductVariant(
            id: 4,
            sku: 'BOSCH-DREL-18V',
            name: 'To\'liq komplekt + 2 akkumulyator',
            price: 850000,
            priceTiers: [
              PriceTier(minQuantity: 1, maxQuantity: 4, unitPrice: 850000),
              PriceTier(minQuantity: 5, maxQuantity: null, unitPrice: 760000),
            ],
          ),
        ],
      ),
      ProductModel(
        id: 5,
        name: 'Kalsiy Karbonat Extra Oq Kukun 25kg',
        slug: 'kalsiy-karbonat-25kg',
        unit: 'piece',
        minOrderQuantity: 5,
        unitStep: 1,
        tradeMode: 'both',
        sellerName: 'Qurilish Mol Servis',
        imageUrl: 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=600&auto=format&fit=crop&q=80',
        variants: [
          ProductVariant(
            id: 5,
            sku: 'CALCIUM-CARB-25KG',
            name: '25 kg qop',
            price: 75000,
            priceTiers: [
              PriceTier(minQuantity: 5, maxQuantity: 49, unitPrice: 75000),
              PriceTier(minQuantity: 50, maxQuantity: null, unitPrice: 65000),
            ],
          ),
        ],
      ),
      ProductModel(
        id: 6,
        name: 'Quyultirilgan Sut "Slavyanka" 380g (30 dona quti)',
        slug: 'quyultirilgan-sut-slavyanka',
        unit: 'box',
        minOrderQuantity: 1,
        unitStep: 1,
        tradeMode: 'both',
        sellerName: 'Oltin Don MCHJ',
        imageUrl: 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=600&auto=format&fit=crop&q=80',
        variants: [
          ProductVariant(
            id: 6,
            sku: 'MILK-SLAV-BOX30',
            name: '30 talik quti',
            price: 450000,
            priceTiers: [
              PriceTier(minQuantity: 1, maxQuantity: 9, unitPrice: 450000),
              PriceTier(minQuantity: 10, maxQuantity: null, unitPrice: 395000),
            ],
          ),
        ],
      ),
      ProductModel(
        id: 7,
        name: 'Sof Paxta Yog\'i "Ziyo" 5L (4 talik blok)',
        slug: 'paxta-yogi-ziyo-5l',
        unit: 'box',
        minOrderQuantity: 1,
        unitStep: 1,
        tradeMode: 'both',
        sellerName: 'Agro Savdo Fayz',
        imageUrl: 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&auto=format&fit=crop&q=80',
        variants: [
          ProductVariant(
            id: 7,
            sku: 'OIL-ZIYO-5L-BOX4',
            name: '4 dona x 5L',
            price: 360000,
            priceTiers: [
              PriceTier(minQuantity: 1, maxQuantity: 9, unitPrice: 360000),
              PriceTier(minQuantity: 10, maxQuantity: null, unitPrice: 320000),
            ],
          ),
        ],
      ),
      ProductModel(
        id: 8,
        name: 'Pishgan Qizil G\'isht (1000 dona poddon)',
        slug: 'pishgan-gisht-1000',
        unit: 'piece',
        minOrderQuantity: 1,
        unitStep: 1,
        tradeMode: 'both',
        sellerName: 'StroyBaza Ulgurji Savdo',
        imageUrl: 'https://images.unsplash.com/photo-1584463699039-4bb4bc6587d4?w=600&auto=format&fit=crop&q=80',
        variants: [
          ProductVariant(
            id: 8,
            sku: 'BRICK-RED-1000',
            name: 'Poddon (1000 dona)',
            price: 1400000,
            priceTiers: [
              PriceTier(minQuantity: 1, maxQuantity: 4, unitPrice: 1400000),
              PriceTier(minQuantity: 5, maxQuantity: null, unitPrice: 1250000),
            ],
          ),
        ],
      ),
    ];
  }

  bool get hasOfflineCache => _cachedProducts.isNotEmpty;
}
