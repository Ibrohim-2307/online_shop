import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'core/constants/app_colors.dart';
import 'core/theme/app_theme.dart';
import 'features/admin/presentation/admin_dashboard_screen.dart';
import 'features/auth/auth_notifier.dart';
import 'features/cart/data/cart_notifier.dart';
import 'features/cart/presentation/cart_screen.dart';
import 'features/catalog/presentation/catalog_screen.dart';
import 'features/catalog/presentation/home_screen.dart';
import 'features/courier/presentation/courier_dashboard_screen.dart';
import 'features/orders/presentation/orders_screen.dart';
import 'features/profile/presentation/profile_screen.dart';
import 'features/seller/presentation/seller_dashboard_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const ProviderScope(child: CustomerApp()));
}

class CustomerApp extends StatelessWidget {
  const CustomerApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Marketplace SuperApp',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light,
      home: const RootScreen(),
    );
  }
}

typedef SuperApp = CustomerApp;

class RootScreen extends ConsumerWidget {
  const RootScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final auth = ref.watch(authProvider);

    switch (auth.activeRole) {
      case 'seller':
        return const SellerDashboardScreen();
      case 'courier':
        return const CourierDashboardScreen();
      case 'admin':
        return const AdminDashboardScreen();
      case 'customer':
      default:
        return const MainNavigationScreen();
    }
  }
}

class MainNavigationScreen extends ConsumerStatefulWidget {
  const MainNavigationScreen({super.key});

  @override
  ConsumerState<MainNavigationScreen> createState() => _MainNavigationScreenState();
}

class _MainNavigationScreenState extends ConsumerState<MainNavigationScreen> {
  int _currentIndex = 0;

  final List<Widget> _screens = const [
    HomeScreen(),
    CatalogScreen(),
    CartScreen(),
    OrdersScreen(),
    ProfileScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    final cart = ref.watch(cartProvider);
    final auth = ref.watch(authProvider);
    final user = auth.user;

    // Determine if the user has non-customer management roles
    String? jumpRole;
    String? jumpLabel;
    IconData? jumpIcon;
    if (auth.isAuthenticated && user != null) {
      if (user.isAdmin) {
        jumpRole = 'admin';
        jumpLabel = 'Admin paneli';
        jumpIcon = Icons.admin_panel_settings;
      } else if (user.isSeller) {
        jumpRole = 'seller';
        jumpLabel = 'Sotuvchi paneli';
        jumpIcon = Icons.storefront;
      } else if (user.isCourier) {
        jumpRole = 'courier';
        jumpLabel = 'Kuryer paneli';
        jumpIcon = Icons.delivery_dining;
      }
    }

    return Scaffold(
      // Tabs keep their state; the one being opened fades and slides up slightly
      body: Stack(
        children: [
          for (var i = 0; i < _screens.length; i++)
            Offstage(
              offstage: i != _currentIndex,
              child: TickerMode(
                enabled: i == _currentIndex,
                child: i == _currentIndex
                    ? TweenAnimationBuilder<double>(
                        key: ValueKey(i),
                        tween: Tween(begin: 0, end: 1),
                        duration: const Duration(milliseconds: 240),
                        curve: Curves.easeOutCubic,
                        child: _screens[i],
                        builder: (context, v, child) => Opacity(
                          opacity: v,
                          child: Transform.translate(offset: Offset(0, 10 * (1 - v)), child: child),
                        ),
                      )
                    : _screens[i],
              ),
            ),
        ],
      ),
      floatingActionButton: (jumpRole != null && _currentIndex == 0)
          ? FloatingActionButton.extended(
              backgroundColor: AppColors.brandDark,
              foregroundColor: AppColors.brandLime,
              icon: Icon(jumpIcon, size: 20),
              label: Text(jumpLabel!, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              onPressed: () {
                ref.read(authProvider.notifier).switchActiveRole(jumpRole!);
              },
            )
          : null,
      bottomNavigationBar: _BottomBar(
        index: _currentIndex,
        cartCount: cart.items.length,
        onSelect: (index) {
          if (index != _currentIndex) HapticFeedback.selectionClick();
          setState(() => _currentIndex = index);
        },
      ),
    );
  }
}

/// Bottom menu: the active tab grows into a dark pill with its name; the rest stay icons.
class _BottomBar extends StatelessWidget {
  final int index;
  final int cartCount;
  final ValueChanged<int> onSelect;

  const _BottomBar({required this.index, required this.cartCount, required this.onSelect});

  static const _items = [
    (Icons.home_outlined, Icons.home_rounded, 'Asosiy'),
    (Icons.grid_view_outlined, Icons.grid_view_rounded, 'Katalog'),
    (Icons.shopping_bag_outlined, Icons.shopping_bag_rounded, 'Savat'),
    (Icons.receipt_long_outlined, Icons.receipt_long_rounded, 'Buyurtma'),
    (Icons.person_outline_rounded, Icons.person_rounded, 'Profil'),
  ];

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(26)),
        boxShadow: [
          BoxShadow(color: AppColors.brandDark.withValues(alpha: 0.08), blurRadius: 24, offset: const Offset(0, -6)),
        ],
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(10, 10, 10, 8),
          child: Row(
            children: [
              for (var i = 0; i < _items.length; i++)
                Expanded(
                  flex: i == index ? 18 : 10,
                  child: Semantics(
                    button: true,
                    selected: i == index,
                    label: _items[i].$3,
                    child: GestureDetector(
                      behavior: HitTestBehavior.opaque,
                      onTap: () => onSelect(i),
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 260),
                        curve: Curves.easeOutCubic,
                        height: 48,
                        margin: const EdgeInsets.symmetric(horizontal: 3),
                        decoration: BoxDecoration(
                          color: i == index ? AppColors.brandDark : Colors.transparent,
                          borderRadius: BorderRadius.circular(24),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Badge(
                              isLabelVisible: i == 2 && cartCount > 0,
                              label: Text('$cartCount'),
                              backgroundColor: AppColors.brandRose,
                              child: Icon(
                                i == index ? _items[i].$2 : _items[i].$1,
                                size: 23,
                                color: i == index ? AppColors.brandLime : AppColors.textSecondary,
                              ),
                            ),
                            if (i == index)
                              Flexible(
                                child: Padding(
                                  padding: const EdgeInsets.only(left: 6),
                                  child: Text(
                                    _items[i].$3,
                                    maxLines: 1,
                                    overflow: TextOverflow.fade,
                                    softWrap: false,
                                    style: const TextStyle(color: Colors.white, fontSize: 12.5, fontWeight: FontWeight.w800),
                                  ),
                                ),
                              ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}
