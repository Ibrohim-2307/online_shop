import 'package:flutter/material.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/widgets/empty_state.dart';
import '../data/catalog_repository.dart';
import '../domain/product_model.dart';
import 'product_card.dart';

class CatalogScreen extends StatefulWidget {
  final String? initialCategory;
  final bool autofocusSearch;
  const CatalogScreen({super.key, this.initialCategory, this.autofocusSearch = false});

  @override
  State<CatalogScreen> createState() => _CatalogScreenState();
}

class _CatalogScreenState extends State<CatalogScreen> {
  final CatalogRepository _repo = CatalogRepository();
  final TextEditingController _searchCtrl = TextEditingController();

  List<ProductModel> _products = [];
  List<CategoryModel> _categories = [];
  bool _isLoading = true;

  String? _selectedCategory;
  String _selectedTradeMode = 'all'; // all, retail_only, wholesale_only

  @override
  void initState() {
    super.initState();
    _selectedCategory = widget.initialCategory;
    _fetchData();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _fetchData() async {
    setState(() => _isLoading = true);
    try {
      final cats = await _repo.getCategories();
      final prods = await _repo.getProducts(
        categorySlug: _selectedCategory == 'all' ? null : _selectedCategory,
        tradeMode: _selectedTradeMode == 'all' ? null : _selectedTradeMode,
        search: _searchCtrl.text.trim(),
      );
      if (mounted) {
        setState(() {
          _categories = cats;
          _products = prods;
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Katalog')),
      body: Column(
        children: [
          // Search
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 4, 16, 10),
            child: TextField(
              controller: _searchCtrl,
              autofocus: widget.autofocusSearch,
              textInputAction: TextInputAction.search,
              onSubmitted: (_) => _fetchData(),
              decoration: const InputDecoration(
                hintText: 'Mahsulot qidirish',
                prefixIcon: Icon(Icons.search, color: AppColors.textSecondary),
              ),
            ),
          ),

          // Categories horizontal scroll bar
          if (_categories.isNotEmpty)
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                children: [
                  _buildCategoryChip('Hammasi', null),
                  ..._categories.map((c) => Padding(
                        padding: const EdgeInsets.only(left: 8),
                        child: _buildCategoryChip(c.name, c.slug),
                      )),
                ],
              ),
            ),

          // Trade mode quick pills
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 4),
            child: Row(
              children: [
                _buildFilterChip('Barchasi', 'all'),
                const SizedBox(width: 8),
                _buildFilterChip('Optom', 'wholesale_only'),
                const SizedBox(width: 8),
                _buildFilterChip('Chakana', 'retail_only'),
              ],
            ),
          ),

          // Products grid
          Expanded(
            child: _isLoading
                ? const ProductGridSkeleton(scrollable: true)
                : _products.isEmpty
                    ? const EmptyState(
                        icon: Icons.search_off,
                        title: 'Hech narsa topilmadi',
                        subtitle: "Boshqa so'z yoki kategoriyani sinab ko'ring",
                      )
                    : RefreshIndicator(
                        onRefresh: _fetchData,
                        color: AppColors.brandDark,
                        child: GridView.builder(
                          padding: const EdgeInsets.all(16),
                          gridDelegate: productGridDelegate,
                          itemCount: _products.length,
                          itemBuilder: (context, i) => ProductCard(product: _products[i]),
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildCategoryChip(String label, String? slug) {
    final isSelected = _selectedCategory == slug || (slug == null && (_selectedCategory == null || _selectedCategory == 'all'));
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      side: BorderSide(color: isSelected ? AppColors.brandDark : AppColors.border),
      labelStyle: TextStyle(
        fontSize: 12.5,
        fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
        color: isSelected ? Colors.white : AppColors.textPrimary,
      ),
      onSelected: (selected) {
        setState(() {
          _selectedCategory = selected ? slug : null;
        });
        _fetchData();
      },
    );
  }

  Widget _buildFilterChip(String label, String value) {
    final isSelected = _selectedTradeMode == value;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      selectedColor: AppColors.brandLime,
      side: BorderSide(color: isSelected ? AppColors.brandLime : AppColors.border),
      labelStyle: TextStyle(
        fontSize: 12.5,
        fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
        color: AppColors.brandDark,
      ),
      onSelected: (selected) {
        if (selected) {
          setState(() => _selectedTradeMode = value);
          _fetchData();
        }
      },
    );
  }
}
