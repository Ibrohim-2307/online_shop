import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'core/constants/app_colors.dart';
import 'features/deliveries/presentation/deliveries_screen.dart';
import 'features/profile/presentation/courier_profile_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const ProviderScope(child: CourierApp()));
}

class CourierApp extends StatelessWidget {
  const CourierApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Marketplace Kuryer',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        scaffoldBackgroundColor: AppColors.background,
        colorScheme: ColorScheme.fromSeed(
          seedColor: AppColors.brandLime,
          primary: AppColors.brandDark,
        ),
        useMaterial3: true,
      ),
      home: const MainCourierScreen(),
    );
  }
}

class MainCourierScreen extends StatefulWidget {
  const MainCourierScreen({super.key});

  @override
  State<MainCourierScreen> createState() => _MainCourierScreenState();
}

class _MainCourierScreenState extends State<MainCourierScreen> {
  int _currentIndex = 0;

  final List<Widget> _screens = const [
    DeliveriesScreen(),
    CourierProfileScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: _screens,
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        backgroundColor: Colors.white,
        indicatorColor: AppColors.brandLime,
        elevation: 2,
        onDestinationSelected: (index) {
          setState(() => _currentIndex = index);
        },
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.two_wheeler_outlined),
            selectedIcon: Icon(Icons.two_wheeler, color: AppColors.brandDark),
            label: 'Yetkazmalar',
          ),
          NavigationDestination(
            icon: Icon(Icons.person_outline),
            selectedIcon: Icon(Icons.person, color: AppColors.brandDark),
            label: 'Profil',
          ),
        ],
      ),
    );
  }
}
