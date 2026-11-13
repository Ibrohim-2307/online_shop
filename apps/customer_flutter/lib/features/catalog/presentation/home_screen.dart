import 'dart:async';
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

  final PageController _bannerController = PageController();
  int _currentBannerIndex = 0;
  Timer? _bannerTimer;

  final List<Map<String, dynamic>> _banners = [
    {
      'title': 'Katta Bahorgi Aksiyalar',
      'subtitle': 'Qurilish va ta\'mirlash mollari uchun 35% gacha chegirmalar',
      'tag': 'AKSIYA',
      'tagColor': AppColors.brandLime,
      'tagTextColor': AppColors.brandDark,
      'colors': [const Color(0xFF0F382C), const Color(0xFF051C15)],
      'icon': Icons.local_offer_outlined,
      'btnText': 'Xarid qilish',
    },
    {
      'title': '0% Boshlang\'ich Nasiya',
      'subtitle': '3, 6 va 12 oyga teng bo\'lib to\'lash imkoniyati',
      'tag': 'MUDDATLI TO\'LOV',
      'tagColor': const Color(0xFFF59E0B),
      'tagTextColor': Colors.white,
      'colors': [const Color(0xFF7C2D12), const Color(0xFF431407)],
      'icon': Icons.flash_on,
      'btnText': 'Hisoblash',
    },
    {
      'title': 'Zavoddan Ulgurji Narxlar',
      'subtitle': 'To\'g\'ridan-to\'g\'ri ishlab chiqaruvchilardan eng past optom narxlar',
      'tag': 'OPTOM BAZA',
      'tagColor': const Color(0xFF818CF8),
      'tagTextColor': Colors.white,
      'colors': [const Color(0xFF312E81), const Color(0xFF1E1B4B)],
      'icon': Icons.precision_manufacturing_outlined,
      'btnText': 'Barchasini ko\'rish',
    },
  ];

  @override
  void initState() {
    super.initState();
    _loadData();
    _startBannerTimer();
  }

  @override
  void dispose() {
    _bannerTimer?.cancel();
    _bannerController.dispose();
    super.dispose();
  }

  void _startBannerTimer() {
    _bannerTimer = Timer.periodic(const Duration(seconds: 4), (_) {
      if (!mounted) return;
      if (_bannerController.hasClients) {
        final next = (_currentBannerIndex + 1) % _banners.length;
        _bannerController.animateToPage(
          next,
          duration: const Duration(milliseconds: 600),
          curve: Curves.easeInOutCubic,
        );
      }
    });
  }

  Future<void> _loadData() async {
    try {
      final results = await Future.wait([
        _repo.getProducts(),
        _repo.getCategories(),
      ]);
      if (mounted) {
        setState(() {
          _products = results[0] as List<ProductModel>;
          _categories = results[1] as List<CategoryModel>;
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: _buildAppBar(),
      body: RefreshIndicator(
        onRefresh: _loadData,
        color: AppColors.brandDark,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 6, 16, 30),
          children: [
            // Search Bar
            _buildSearchBar(),
            const SizedBox(height: 16),

            // Hero Promotion Carousel
            _buildHeroCarousel(),
            const SizedBox(height: 20),

            // Category Story Bubbles
            _buildCategoryStories(),
            const SizedBox(height: 20),

            // Trust features row
            _buildTrustRow(),
            const SizedBox(height: 22),

            // Hot Deals / Flash Sale Section
            _buildFlashSaleHeader(),
            const SizedBox(height: 12),

            // Products Grid
            if (_isLoading)
              const Center(
                child: Padding(
                  padding: EdgeInsets.all(40),
                  child: CircularProgressIndicator(color: AppColors.brandDark),
                ),
              )
            else if (_products.isEmpty)
              const EmptyState(
                icon: Icons.inventory_2_outlined,
                title: 'Mahsulotlar topilmadi',
                subtitle: 'Sahifani pastga tortib yangilab ko\'ring',
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
        ),
      ),
    );
  }

  PreferredSizeWidget _buildAppBar() {
    return AppBar(
      backgroundColor: Colors.white,
      elevation: 0.5,
      title: const Row(
        children: [
          AppLogo(),
          SizedBox(width: 8),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Icon(Icons.location_on, size: 12, color: AppColors.brandEmerald),
                  SizedBox(width: 2),
                  Text(
                    'Toshkent sh.',
                    style: TextStyle(color: AppColors.textSecondary, fontSize: 10, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ],
          ),
        ],
      ),
      actions: [
        IconButton(
          icon: Stack(
            children: [
              const Icon(Icons.notifications_none_outlined, color: AppColors.brandDark, size: 24),
              Positioned(
                right: 0,
                top: 0,
                child: Container(
                  width: 8,
                  height: 8,
                  decoration: const BoxDecoration(
                    color: AppColors.brandRose,
                    shape: BoxShape.circle,
                  ),
                ),
              ),
            ],
          ),
          onPressed: () {},
        ),
        const SizedBox(width: 4),
      ],
    );
  }

  Widget _buildSearchBar() {
    return Material(
      color: Colors.white,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(14),
        side: const BorderSide(color: Color(0xFFE2E8F0)),
      ),
      elevation: 1,
      shadowColor: Colors.black.withValues(alpha: 0.04),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () => _openCatalog(focusSearch: true),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          child: Row(
            children: [
              const Icon(Icons.search, size: 22, color: AppColors.textSecondary),
              const SizedBox(width: 10),
              const Expanded(
                child: Text(
                  'Mahsulot yoki do\'kon nomi bo\'yicha qidirish...',
                  style: TextStyle(fontSize: 13, color: AppColors.textSecondary),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: AppColors.brandLime.withValues(alpha: 0.3),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(Icons.tune, size: 16, color: AppColors.brandDark),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeroCarousel() {
    return Column(
      children: [
        SizedBox(
          height: 155,
          child: PageView.builder(
            controller: _bannerController,
            onPageChanged: (i) => setState(() => _currentBannerIndex = i),
            itemCount: _banners.length,
            itemBuilder: (context, i) {
              final b = _banners[i];
              return Container(
                margin: const EdgeInsets.symmetric(horizontal: 2),
                clipBehavior: Clip.antiAlias,
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(20),
                  gradient: LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: b['colors'] as List<Color>,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: (b['colors'][0] as Color).withValues(alpha: 0.25),
                      blurRadius: 12,
                      offset: const Offset(0, 5),
                    ),
                  ],
                ),
                child: Stack(
                  children: [
                    Positioned(
                      right: -25,
                      bottom: -35,
                      child: Container(
                        width: 140,
                        height: 140,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: Colors.white.withValues(alpha: 0.08),
                        ),
                      ),
                    ),
                    Positioned(
                      right: 18,
                      top: 18,
                      child: Icon(b['icon'] as IconData, size: 48, color: Colors.white.withValues(alpha: 0.3)),
                    ),
                    Padding(
                      padding: const EdgeInsets.all(18),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: b['tagColor'] as Color,
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              b['tag'] as String,
                              style: TextStyle(
                                color: b['tagTextColor'] as Color,
                                fontWeight: FontWeight.w800,
                                fontSize: 10,
                              ),
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            b['title'] as String,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 18,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                          const SizedBox(height: 4),
                          SizedBox(
                            width: 240,
                            child: Text(
                              b['subtitle'] as String,
                              maxLines: 2,
                              style: const TextStyle(color: Colors.white70, fontSize: 11),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        ),
        const SizedBox(height: 10),

        // Dots Indicator
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: List.generate(_banners.length, (index) {
            final isSelected = _currentBannerIndex == index;
            return AnimatedContainer(
              duration: const Duration(milliseconds: 300),
              margin: const EdgeInsets.symmetric(horizontal: 3),
              width: isSelected ? 20 : 6,
              height: 6,
              decoration: BoxDecoration(
                color: isSelected ? AppColors.brandDark : const Color(0xFFCBD5E1),
                borderRadius: BorderRadius.circular(3),
              ),
            );
          }),
        ),
      ],
    );
  }

  Widget _buildCategoryStories() {
    final displayCategories = _categories.isNotEmpty 
        ? _categories 
        : [
            CategoryModel(id: 1, name: 'Qurilish mollari', slug: 'qurilish-materiallari'),
            CategoryModel(id: 2, name: 'Sanoat uskunalari', slug: 'sanoat-uskunalari'),
            CategoryModel(id: 3, name: 'Oziq-ovqat & Ichimlik', slug: 'oziq-ovqat-va-ichimliklar'),
            CategoryModel(id: 4, name: 'Elektr & Yoritish', slug: 'elektr-jihozlari'),
            CategoryModel(id: 5, name: 'Bo\'yoqlar & Kimyo', slug: 'boyoqlar-va-kimyo'),
            CategoryModel(id: 6, name: 'Santexnika & Quvur', slug: 'santexnika-va-quvurlar'),
            CategoryModel(id: 7, name: 'Asbob-uskunalar', slug: 'asbob-uskunalar'),
          ];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Row(
              children: [
                Text(
                  'Toifalar',
                  style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: AppColors.brandDark),
                ),
                SizedBox(width: 6),
                Text(
                  '• Rasmiy katalog',
                  style: TextStyle(fontWeight: FontWeight.w500, fontSize: 11, color: AppColors.textSecondary),
                ),
              ],
            ),
            GestureDetector(
              onTap: () => _openCatalog(),
              child: const Text(
                'Barchasi >',
                style: TextStyle(color: AppColors.brandEmerald, fontSize: 12, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        SizedBox(
          height: 104,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: displayCategories.length + 1,
            separatorBuilder: (_, _) => const SizedBox(width: 12),
            itemBuilder: (context, i) {
              if (i == displayCategories.length) {
                // "All Categories" Pod
                return GestureDetector(
                  onTap: () => _openCatalog(),
                  child: Column(
                    children: [
                      Container(
                        width: 62,
                        height: 62,
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                            colors: [AppColors.brandLime.withValues(alpha: 0.35), Colors.white],
                          ),
                          borderRadius: BorderRadius.circular(18),
                          border: Border.all(color: AppColors.brandDark.withValues(alpha: 0.25), width: 1.2),
                          boxShadow: [
                            BoxShadow(
                              color: AppColors.brandDark.withValues(alpha: 0.08),
                              blurRadius: 8,
                              offset: const Offset(0, 3),
                            ),
                          ],
                        ),
                        child: Stack(
                          children: [
                            const Center(
                              child: Icon(Icons.grid_view_rounded, color: AppColors.brandDark, size: 28),
                            ),
                            Positioned(
                              top: 4,
                              right: 4,
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1.5),
                                decoration: BoxDecoration(
                                  color: Colors.white.withValues(alpha: 0.95),
                                  borderRadius: BorderRadius.circular(6),
                                  border: Border.all(color: AppColors.brandDark.withValues(alpha: 0.2), width: 0.8),
                                ),
                                child: const Text('✨', style: TextStyle(fontSize: 11)),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 6),
                      const SizedBox(
                        width: 72,
                        child: Text(
                          'Barchasi',
                          maxLines: 2,
                          textAlign: TextAlign.center,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                            color: AppColors.brandDark,
                            height: 1.15,
                          ),
                        ),
                      ),
                    ],
                  ),
                );
              }

              final cat = displayCategories[i];
              return GestureDetector(
                onTap: () => _openCatalog(category: cat.slug),
                child: Column(
                  children: [
                    Container(
                      width: 62,
                      height: 62,
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                          colors: [cat.bgColor, Colors.white],
                        ),
                        borderRadius: BorderRadius.circular(18),
                        border: Border.all(color: cat.color.withValues(alpha: 0.28), width: 1.2),
                        boxShadow: [
                          BoxShadow(
                            color: cat.color.withValues(alpha: 0.12),
                            blurRadius: 8,
                            offset: const Offset(0, 3),
                          ),
                        ],
                      ),
                      child: Stack(
                        children: [
                          Center(
                            child: Icon(cat.icon, color: cat.color, size: 28),
                          ),
                          Positioned(
                            top: 4,
                            right: 4,
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1.5),
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.95),
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(color: cat.color.withValues(alpha: 0.2), width: 0.8),
                              ),
                              child: Text(cat.emoji, style: const TextStyle(fontSize: 11)),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 6),
                    SizedBox(
                      width: 72,
                      child: Text(
                        cat.name,
                        maxLines: 2,
                        textAlign: TextAlign.center,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: AppColors.textPrimary,
                          height: 1.15,
                        ),
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildTrustRow() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: const Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          _TrustItem(icon: Icons.verified_user_outlined, label: 'Kafolatlangan sifat', color: AppColors.brandEmerald),
          _TrustItem(icon: Icons.local_shipping_outlined, label: 'Tezkor yetkazish', color: AppColors.brandIndigo),
          _TrustItem(icon: Icons.lock_outline, label: 'Xavfsiz to\'lov', color: AppColors.brandAmber),
        ],
      ),
    );
  }

  Widget _buildFlashSaleHeader() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        const Row(
          children: [
            Icon(Icons.whatshot, color: AppColors.brandOrange, size: 22),
            SizedBox(width: 4),
            Text(
              'Ommabop Mahsulotlar',
              style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16, color: AppColors.brandDark),
            ),
          ],
        ),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
          decoration: BoxDecoration(
            color: AppColors.brandLime.withValues(alpha: 0.35),
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: AppColors.brandLime),
          ),
          child: const Text(
            'Zavod narxida',
            style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.brandDark),
          ),
        ),
      ],
    );
  }
}

class _TrustItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;

  const _TrustItem({required this.icon, required this.label, required this.color});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 16, color: color),
        const SizedBox(width: 5),
        Text(
          label,
          style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
        ),
      ],
    );
  }
}
