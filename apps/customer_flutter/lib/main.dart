import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'core/constants/app_colors.dart';
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
      theme: ThemeData(
        scaffoldBackgroundColor: AppColors.background,
        colorScheme: ColorScheme.fromSeed(
          seedColor: AppColors.brandLime,
          primary: AppColors.brandDark,
        ),
        useMaterial3: true,
      ),
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
      body: IndexedStack(
        index: _currentIndex,
        children: _screens,
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
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        backgroundColor: Colors.white,
        indicatorColor: AppColors.brandLime,
        elevation: 2,
        onDestinationSelected: (index) {
          setState(() => _currentIndex = index);
        },
        destinations: [
          const NavigationDestination(
            icon: Icon(Icons.home_outlined),
            selectedIcon: Icon(Icons.home, color: AppColors.brandDark),
            label: 'Asosiy',
          ),
          const NavigationDestination(
            icon: Icon(Icons.category_outlined),
            selectedIcon: Icon(Icons.category, color: AppColors.brandDark),
            label: 'Katalog',
          ),
          NavigationDestination(
            icon: Badge(
              isLabelVisible: cart.items.isNotEmpty,
              label: Text(cart.totalItemCount.toString()),
              backgroundColor: AppColors.brandDark,
              child: const Icon(Icons.shopping_cart_outlined),
            ),
            selectedIcon: Badge(
              isLabelVisible: cart.items.isNotEmpty,
              label: Text(cart.totalItemCount.toString()),
              backgroundColor: AppColors.brandDark,
              child: const Icon(Icons.shopping_cart, color: AppColors.brandDark),
            ),
            label: 'Savatcha',
          ),
          const NavigationDestination(
            icon: Icon(Icons.receipt_long_outlined),
            selectedIcon: Icon(Icons.receipt_long, color: AppColors.brandDark),
            label: 'Buyurtmalar',
          ),
          const NavigationDestination(
            icon: Icon(Icons.person_outline),
            selectedIcon: Icon(Icons.person, color: AppColors.brandDark),
            label: 'Profil',
          ),
        ],
      ),
    );
  }
}
