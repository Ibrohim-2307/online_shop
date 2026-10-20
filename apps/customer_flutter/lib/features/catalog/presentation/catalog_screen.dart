import 'package:flutter/material.dart';
import '../../../../core/constants/app_colors.dart';
import '../data/catalog_repository.dart';
import '../domain/product_model.dart';
import 'product_detail_screen.dart';

class CatalogScreen extends StatefulWidget {
  final String? initialCategory;
  const CatalogScreen({super.key, this.initialCategory});

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
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.background,
        elevation: 0,
        title: const Text(
          'Katalog',
          style: TextStyle(
            color: AppColors.textPrimary,
            fontWeight: FontWeight.w800,
            fontSize: 20,
          ),
        ),
      ),
      body: Column(
        children: [
          // Search & Trade mode filter bar
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: TextField(
              controller: _searchCtrl,
              onSubmitted: (_) => _fetchData(),
              decoration: InputDecoration(
                hintText: 'Mahsulot yoki SKU qidirish...',
                prefixIcon: const Icon(Icons.search, color: AppColors.textSecondary),
                suffixIcon: IconButton(
                  icon: const Icon(Icons.filter_list, color: AppColors.brandDark),
                  onPressed: _showFilterModal,
                ),
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 16),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                ),
              ),
            ),
          ),

          // Trade mode quick pills
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
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

          const SizedBox(height: 8),

          // Products list/grid
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: AppColors.brandDark))
                : _products.isEmpty
                    ? const Center(
                        child: Text(
                          'Hech qanday mahsulot topilmadi',
                          style: TextStyle(color: AppColors.textSecondary),
                        ),
                      )
                    : ListView.separated(
                        padding: const EdgeInsets.all(16),
                        itemCount: _products.length,
                        separatorBuilder: (context, index) => const SizedBox(height: 12),
                        itemBuilder: (context, i) {
                          final prod = _products[i];
                          return InkWell(
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => ProductDetailScreen(product: prod),
                                ),
                              );
                            },
                            child: Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: const Color(0xFFE2E8F0)),
                              ),
                              child: Row(
                                children: [
                                  Container(
                                    width: 70,
                                    height: 70,
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFF8FAFC),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: const Icon(
                                      Icons.inventory_2_outlined,
                                      color: AppColors.brandDark,
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          prod.name,
                                          maxLines: 2,
                                          style: const TextStyle(
                                            fontWeight: FontWeight.bold,
                                            fontSize: 14,
                                          ),
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          'Sotuvchi: ${prod.sellerName ?? "Rasmiy do'kon"}',
                                          style: const TextStyle(
                                            color: AppColors.textSecondary,
                                            fontSize: 12,
                                          ),
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          '${prod.basePrice} UZS / ${prod.unit}',
                                          style: const TextStyle(
                                            color: AppColors.brandDark,
                                            fontWeight: FontWeight.w800,
                                            fontSize: 14,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  const Icon(Icons.chevron_right, color: AppColors.textSecondary),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String label, String value) {
    final isSelected = _selectedTradeMode == value;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      selectedColor: AppColors.brandLime,
      backgroundColor: Colors.white,
      side: BorderSide(
        color: isSelected ? AppColors.brandDark : const Color(0xFFCBD5E1),
      ),
      labelStyle: TextStyle(
        fontSize: 12,
        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
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

  void _showFilterModal() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (_) {
        return Container(
          padding: const EdgeInsets.all(20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Kategoriya bo\'yicha saralash',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
              ),
              const SizedBox(height: 12),
              Wrap(
                spacing: 8,
                children: _categories.map((c) {
                  return FilterChip(
                    label: Text(c.name),
                    selected: _selectedCategory == c.slug,
                    onSelected: (selected) {
                      setState(() {
                        _selectedCategory = selected ? c.slug : null;
                      });
                      Navigator.pop(context);
                      _fetchData();
                    },
                  );
                }).toList(),
              ),
            ],
          ),
        );
      },
    );
  }
}
