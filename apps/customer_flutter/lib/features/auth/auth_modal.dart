import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import 'auth_notifier.dart';

Future<bool?> showAuthModal(
  BuildContext context, {
  String? title,
  String? subtitle,
}) {
  return showModalBottomSheet<bool>(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.transparent,
    builder: (ctx) => _AuthModalSheet(
      title: title ?? "Xaridni davom ettirish",
      subtitle: subtitle ??
          "Buyurtmani rasmiylashtirish uchun tizimga kiring yoki ro'yxatdan o'ting. Savatchangiz to'liq saqlanadi.",
    ),
  );
}

class _AuthModalSheet extends ConsumerStatefulWidget {
  final String title;
  final String subtitle;

  const _AuthModalSheet({
    required this.title,
    required this.subtitle,
  });

  @override
  ConsumerState<_AuthModalSheet> createState() => _AuthModalSheetState();
}

class _AuthModalSheetState extends ConsumerState<_AuthModalSheet>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  // Register controllers
  final _regNameCtrl = TextEditingController();
  final _regPhoneCtrl = TextEditingController(text: '+998');
  final _regPassCtrl = TextEditingController();

  // Login controllers
  final _loginCtrl = TextEditingController();
  final _loginPassCtrl = TextEditingController();

  bool _obscurePassword = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    _regNameCtrl.dispose();
    _regPhoneCtrl.dispose();
    _regPassCtrl.dispose();
    _loginCtrl.dispose();
    _loginPassCtrl.dispose();
    super.dispose();
  }

  Future<void> _submitRegister() async {
    final name = _regNameCtrl.text.trim();
    final phone = _regPhoneCtrl.text.trim();
    final password = _regPassCtrl.text.trim();

    if (name.isEmpty || phone.length < 9 || password.length < 6) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text("Iltimos, ism, telefon raqami va parolni to'liq kiriting (kamida 6 belgi)."),
          backgroundColor: AppColors.statusError,
        ),
      );
      return;
    }

    final success = await ref.read(authProvider.notifier).register(
          name: name,
          phone: phone,
          password: password,
        );

    if (success && mounted) {
      Navigator.pop(context, true);
    }
  }

  Future<void> _submitLogin() async {
    final login = _loginCtrl.text.trim();
    final password = _loginPassCtrl.text.trim();

    if (login.isEmpty || password.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text("Telefon raqami (yoki email) va parolni kiriting."),
          backgroundColor: AppColors.statusError,
        ),
      );
      return;
    }

    final success = await ref.read(authProvider.notifier).login(login, password);

    if (success && mounted) {
      Navigator.pop(context, true);
    }
  }

  Future<void> _quickDemoCustomer() async {
    final success = await ref.read(authProvider.notifier).quickDemoLogin('customer');
    if (success && mounted) {
      Navigator.pop(context, true);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authProvider);
    final bottomInset = MediaQuery.of(context).viewInsets.bottom;

    return Container(
      padding: EdgeInsets.only(
        left: 20,
        right: 20,
        top: 12,
        bottom: bottomInset + 20,
      ),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Drag handle
            Center(
              child: Container(
                width: 44,
                height: 5,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Header banner
            Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: AppColors.brandEmerald.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: const Icon(
                    Icons.shopping_bag_outlined,
                    color: AppColors.brandEmerald,
                    size: 24,
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        widget.title,
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w900,
                          color: AppColors.brandDark,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        widget.subtitle,
                        style: const TextStyle(
                          fontSize: 12,
                          color: AppColors.textSecondary,
                          height: 1.3,
                        ),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close, color: AppColors.textSecondary),
                  onPressed: () => Navigator.pop(context, false),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Error notice if any
            if (auth.error != null)
              Container(
                margin: const EdgeInsets.only(bottom: 14),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.statusError.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.statusError.withValues(alpha: 0.3)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.error_outline, color: AppColors.statusError, size: 20),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        auth.error!,
                        style: const TextStyle(
                          color: AppColors.statusError,
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
              ),

            // Tabs: Ro'yxatdan o'tish / Kirish
            Container(
              decoration: BoxDecoration(
                color: const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(12),
              ),
              child: TabBar(
                controller: _tabController,
                indicator: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(10),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.05),
                      blurRadius: 4,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                labelColor: AppColors.brandDark,
                unselectedLabelColor: AppColors.textSecondary,
                labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                tabs: const [
                  Tab(text: "Ro'yxatdan o'tish"),
                  Tab(text: "Tizimga kirish"),
                ],
              ),
            ),
            const SizedBox(height: 18),

            // Tab View contents
            SizedBox(
              height: 250,
              child: TabBarView(
                controller: _tabController,
                children: [
                  // Tab 1: Registration
                  _buildRegisterForm(auth),

                  // Tab 2: Login
                  _buildLoginForm(auth),
                ],
              ),
            ),

            const SizedBox(height: 12),

            // Divider: yoki
            Row(
              children: [
                Expanded(child: Divider(color: Colors.grey.shade300)),
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 12),
                  child: Text(
                    "yoki",
                    style: TextStyle(color: AppColors.textSecondary, fontSize: 11),
                  ),
                ),
                Expanded(child: Divider(color: Colors.grey.shade300)),
              ],
            ),
            const SizedBox(height: 12),

            // Quick 1-tap demo customer button
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppColors.brandEmerald,
                  side: const BorderSide(color: AppColors.brandEmerald, width: 1.5),
                  padding: const EdgeInsets.symmetric(vertical: 13),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  backgroundColor: AppColors.brandEmerald.withValues(alpha: 0.05),
                ),
                icon: const Icon(Icons.bolt, color: AppColors.brandEmerald, size: 20),
                label: const Text(
                  "⚡ 1-bosishda tezkor kirish (Xaridor)",
                  style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                ),
                onPressed: auth.isLoading ? null : _quickDemoCustomer,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRegisterForm(AuthState auth) {
    return Column(
      children: [
        TextField(
          controller: _regNameCtrl,
          textInputAction: TextInputAction.next,
          decoration: InputDecoration(
            labelText: "Ism va familiyangiz",
            hintText: "Masalan: Aziz Rahimov",
            prefixIcon: const Icon(Icons.person_outline, size: 20),
            filled: true,
            fillColor: const Color(0xFFF8FAFC),
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
          ),
        ),
        const SizedBox(height: 10),
        TextField(
          controller: _regPhoneCtrl,
          keyboardType: TextInputType.phone,
          textInputAction: TextInputAction.next,
          decoration: InputDecoration(
            labelText: "Telefon raqami",
            hintText: "+998901234567",
            prefixIcon: const Icon(Icons.phone_outlined, size: 20),
            filled: true,
            fillColor: const Color(0xFFF8FAFC),
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
          ),
        ),
        const SizedBox(height: 10),
        TextField(
          controller: _regPassCtrl,
          obscureText: _obscurePassword,
          decoration: InputDecoration(
            labelText: "Parol (kamida 6 belgi)",
            prefixIcon: const Icon(Icons.lock_outline, size: 20),
            suffixIcon: IconButton(
              icon: Icon(_obscurePassword ? Icons.visibility_off : Icons.visibility, size: 20),
              onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
            ),
            filled: true,
            fillColor: const Color(0xFFF8FAFC),
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
          ),
        ),
        const Spacer(),
        SizedBox(
          width: double.infinity,
          child: ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.brandLime,
              foregroundColor: AppColors.brandDark,
              elevation: 0,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            onPressed: auth.isLoading ? null : _submitRegister,
            child: auth.isLoading
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(color: AppColors.brandDark, strokeWidth: 2),
                  )
                : const Text(
                    "Ro'yxatdan o'tish va davom etish",
                    style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14),
                  ),
          ),
        ),
      ],
    );
  }

  Widget _buildLoginForm(AuthState auth) {
    return Column(
      children: [
        TextField(
          controller: _loginCtrl,
          textInputAction: TextInputAction.next,
          decoration: InputDecoration(
            labelText: "Telefon yoki Email",
            hintText: "customer@market.uz yoki +99890...",
            prefixIcon: const Icon(Icons.alternate_email, size: 20),
            filled: true,
            fillColor: const Color(0xFFF8FAFC),
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
          ),
        ),
        const SizedBox(height: 10),
        TextField(
          controller: _loginPassCtrl,
          obscureText: _obscurePassword,
          decoration: InputDecoration(
            labelText: "Parol",
            prefixIcon: const Icon(Icons.lock_outline, size: 20),
            suffixIcon: IconButton(
              icon: Icon(_obscurePassword ? Icons.visibility_off : Icons.visibility, size: 20),
              onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
            ),
            filled: true,
            fillColor: const Color(0xFFF8FAFC),
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
          ),
        ),
        const Spacer(),
        SizedBox(
          width: double.infinity,
          child: ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.brandDark,
              foregroundColor: Colors.white,
              elevation: 0,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            onPressed: auth.isLoading ? null : _submitLogin,
            child: auth.isLoading
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                  )
                : const Text(
                    "Kirish va davom etish",
                    style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14),
                  ),
          ),
        ),
      ],
    );
  }
}
