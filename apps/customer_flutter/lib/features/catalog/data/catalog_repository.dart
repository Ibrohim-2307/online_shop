import '../../../../core/network/api_client.dart';
import '../domain/product_model.dart';

class CatalogRepository {
  final ApiClient _client = ApiClient();

  // In-memory cache for offline viewing
  static List<ProductModel> _cachedProducts = [];
  static List<CategoryModel> _cachedCategories = [];

  Future<List<ProductModel>> getProducts({String? categorySlug, String? tradeMode, String? search}) async {
    try {
      final queryParams = <String>[];
      if (categorySlug != null) queryParams.add('category=$categorySlug');
      if (tradeMode != null) queryParams.add('trade_mode=$tradeMode');
      if (search != null && search.isNotEmpty) queryParams.add('q=$search');

      final queryString = queryParams.isNotEmpty ? '?${queryParams.join('&')}' : '';
      final response = await _client.get('/catalog$queryString');

      List<dynamic> items = [];
      if (response is Map<String, dynamic> && response.containsKey('data')) {
        items = response['data'] as List<dynamic>;
      } else if (response is List) {
        items = response;
      }

      final products = items.map((json) => ProductModel.fromJson(json as Map<String, dynamic>)).toList();
      _cachedProducts = products;
      return products;
    } catch (e) {
      if (_cachedProducts.isNotEmpty) {
        return _cachedProducts;
      }
      rethrow;
    }
  }

  Future<List<CategoryModel>> getCategories() async {
    try {
      final response = await _client.get('/categories');
      final list = (response as List<dynamic>).map((c) => CategoryModel.fromJson(c as Map<String, dynamic>)).toList();
      _cachedCategories = list;
      return list;
    } catch (e) {
      if (_cachedCategories.isNotEmpty) {
        return _cachedCategories;
      }
      return [
        CategoryModel(id: 1, name: 'Barchasi', slug: 'all'),
        CategoryModel(id: 2, name: 'Qurilish', slug: 'qurilish'),
        CategoryModel(id: 3, name: 'Sement & Ohak', slug: 'sement-ohak'),
      ];
    }
  }

  bool get hasOfflineCache => _cachedProducts.isNotEmpty;
}
