import 'package:flutter/material.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/widgets/app_logo.dart';
import '../../../../core/widgets/empty_state.dart';
import '../data/catalog_repository.dart';
import '../domain/product_model.dart';
import 'catalog_screen.dart';
import 'product_card.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final CatalogRepository _repo = CatalogRepository();
  List<ProductModel> _products = [];
  List<CategoryModel> _categories = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    try {
      final prods = await _repo.getProducts();
      final cats = await _repo.getCategories();
      if (mounted) {
        setState(() {
          _products = prods;
          _categories = cats;
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _openCatalog({String? category, bool focusSearch = false}) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => CatalogScreen(initialCategory: category, autofocusSearch: focusSearch),
      ),
    );
  }

  IconData _categoryIcon(String slug) {
    if (slug.contains('santexnika') || slug.contains('quvur')) return Icons.plumbing;
    if (slug.contains('qurilish') || slug.contains('sement')) return Icons.construction;
    if (slug.contains('sanoat') || slug.contains('uskuna')) return Icons.precision_manufacturing_outlined;
    if (slug.contains('oziq') || slug.contains('ichimlik')) return Icons.restaurant_outlined;
    if (slug.contains('elektr') || slug.contains('texnika')) return Icons.devices_other_outlined;
    return Icons.category_outlined;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const AppLogo()),
      body: RefreshIndicator(
        onRefresh: _loadData,
        color: AppColors.brandDark,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
          children: [
            _buildSearchBar(),
            const SizedBox(height: 14),
            _buildHero(),
            const SizedBox(height: 22),
            if (_isLoading) ...[
              _sectionTitle('Ommabop mahsulotlar'),
              const SizedBox(height: 12),
              const ProductGridSkeleton(),
            ] else ...[
              if (_categories.isNotEmpty) ...[
                _sectionTitle('Kategoriyalar', action: 'Barchasi', onAction: _openCatalog),
                const SizedBox(height: 10),
                _buildCategories(),
                const SizedBox(height: 22),
              ],
              _sectionTitle('Ommabop mahsulotlar'),
              const SizedBox(height: 12),
              if (_products.isEmpty)
                const EmptyState(
                  icon: Icons.inventory_2_outlined,
                  title: 'Mahsulotlar topilmadi',
                  subtitle: "Pastga tortib sahifani yangilab ko'ring",
                )
              else
                GridView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  padding: EdgeInsets.zero,
                  gridDelegate: productGridDelegate,
                  itemCount: _products.length,
                  itemBuilder: (context, i) => ProductCard(product: _products[i]),
                ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildSearchBar() {
    return Material(
      color: AppColors.surface,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(14),
        side: const BorderSide(color: AppColors.border),
      ),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () => _openCatalog(focusSearch: true),
        child: const Padding(
          padding: EdgeInsets.symmetric(horizontal: 14, vertical: 13),
          child: Row(
            children: [
              Icon(Icons.search, size: 22, color: AppColors.textSecondary),
              SizedBox(width: 10),
              Text('Mahsulot qidirish', style: TextStyle(fontSize: 14, color: AppColors.textSecondary)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHero() {
    return Container(
      clipBehavior: Clip.antiAlias,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(20),
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [AppColors.brandDark, AppColors.brandDeep],
        ),
      ),
      child: Stack(
        children: [
          Positioned(
            right: -36,
            bottom: -48,
            child: Container(
              width: 170,
              height: 170,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: AppColors.brandLime.withValues(alpha: 0.14),
              ),
            ),
          ),
          Positioned(
            right: 18,
            top: 18,
            child: Icon(Icons.local_shipping_outlined, size: 44, color: AppColors.brandLime.withValues(alpha: 0.9)),
          ),
          Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.brandLime,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Text(
                    'Optom va chakana',
                    style: TextStyle(color: AppColors.brandDark, fontWeight: FontWeight.w800, fontSize: 11),
                  ),
                ),
                const SizedBox(height: 14),
                const Padding(
                  padding: EdgeInsets.only(right: 56),
                  child: Text(
                    "To'g'ridan-to'g'ri ishlab chiqaruvchilardan",
                    style: TextStyle(color: Colors.white, fontSize: 21, height: 1.2, fontWeight: FontWeight.w800),
                  ),
                ),
                const SizedBox(height: 6),
                const Text(
                  'Katta hajm uchun bosqichma-bosqich chegirmalar',
                  style: TextStyle(color: Colors.white70, fontSize: 13),
                ),
                const SizedBox(height: 16),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.brandLime,
                    foregroundColor: AppColors.brandDark,
                    minimumSize: const Size(0, 42),
                    padding: const EdgeInsets.symmetric(horizontal: 18),
                  ),
                  onPressed: _openCatalog,
                  child: const Text("Katalogni ko'rish"),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _sectionTitle(String title, {String? action, VoidCallback? onAction}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          title,
          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
        ),
        if (action != null) TextButton(onPressed: onAction, child: Text(action)),
      ],
    );
  }

  Widget _buildCategories() {
    return SizedBox(
      height: 104,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: _categories.length,
        separatorBuilder: (context, index) => const SizedBox(width: 10),
        itemBuilder: (context, i) {
          final cat = _categories[i];
          return InkWell(
            borderRadius: BorderRadius.circular(16),
            onTap: () => _openCatalog(category: cat.slug),
            child: SizedBox(
              width: 82,
              child: Column(
                children: [
                  Container(
                    width: 60,
                    height: 60,
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Icon(_categoryIcon(cat.slug), color: AppColors.brandDark, size: 27),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    cat.name,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 11, height: 1.2, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
