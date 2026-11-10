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
      final prods = await _repo.getProducts();
      if (mounted) {
        setState(() {
          _products = prods;
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
    final categories = [
      {'title': 'Qurilish', 'icon': Icons.construction, 'color': AppColors.brandEmerald, 'slug': 'qurilish-materiallari'},
      {'title': 'Sanoat', 'icon': Icons.precision_manufacturing_outlined, 'color': AppColors.brandIndigo, 'slug': 'sanoat-uskunalari'},
      {'title': 'Oziq-ovqat', 'icon': Icons.restaurant_outlined, 'color': AppColors.brandAmber, 'slug': 'oziq-ovqat-va-ichimliklar'},
      {'title': 'Texnika', 'icon': Icons.devices_other_outlined, 'color': AppColors.brandPurple, 'slug': 'elektronika-texnika'},
      {'title': 'Santexnika', 'icon': Icons.plumbing, 'color': AppColors.brandTeal, 'slug': 'santexnika-isitish'},
      {'title': 'Optom Baza', 'icon': Icons.warehouse_outlined, 'color': AppColors.brandRose, 'slug': null},
    ];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Toifalar',
              style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: AppColors.brandDark),
            ),
            GestureDetector(
              onTap: () => _openCatalog(),
              child: const Text(
                'Barchasi >',
                style: TextStyle(color: AppColors.textSecondary, fontSize: 12, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        SizedBox(
          height: 90,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: categories.length,
            separatorBuilder: (_, _) => const SizedBox(width: 14),
            itemBuilder: (context, i) {
              final cat = categories[i];
              final color = cat['color'] as Color;
              return GestureDetector(
                onTap: () => _openCatalog(category: cat['slug'] as String?),
                child: Column(
                  children: [
                    Container(
                      width: 58,
                      height: 58,
                      decoration: BoxDecoration(
                        color: color.withValues(alpha: 0.12),
                        shape: BoxShape.circle,
                        border: Border.all(color: color.withValues(alpha: 0.3), width: 1.5),
                      ),
                      child: Icon(cat['icon'] as IconData, color: color, size: 26),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      cat['title'] as String,
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
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
