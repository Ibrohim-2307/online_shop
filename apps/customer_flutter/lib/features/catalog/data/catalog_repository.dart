import '../../../../core/network/api_client.dart';
import '../domain/product_model.dart';

class CatalogRepository {
  final ApiClient _client = ApiClient();

  static List<ProductModel> _cachedProducts = [];
  static List<CategoryModel> _cachedCategories = [];

  Future<List<ProductModel>> getProducts({String? categorySlug, String? tradeMode, String? search}) async {
    try {
      final queryParams = <String>[];
      if (categorySlug != null && categorySlug != 'all') queryParams.add('category=$categorySlug');
      if (tradeMode != null && tradeMode != 'all') queryParams.add('trade_mode=$tradeMode');
      if (search != null && search.isNotEmpty) queryParams.add('search=$search');

      final queryString = queryParams.isNotEmpty ? '?${queryParams.join('&')}' : '';
      final response = await _client.get('/products$queryString');

      List<dynamic> items = [];
      if (response is Map<String, dynamic>) {
        if (response['data'] is List) {
          items = response['data'] as List<dynamic>;
        } else if (response['data'] is Map && response['data']['data'] is List) {
          items = response['data']['data'] as List<dynamic>;
        }
      } else if (response is List) {
        items = response;
      }

      if (items.isNotEmpty) {
        final products = items.map((json) => ProductModel.fromJson(json as Map<String, dynamic>)).toList();
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
      } else if (response is Map && response['data'] is List) {
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
    ];
  }

  bool get hasOfflineCache => _cachedProducts.isNotEmpty;
}
