import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/constants/app_colors.dart';
import '../../auth/auth_modal.dart';
import '../../auth/auth_notifier.dart';
import '../../health/presentation/health_screen.dart';

class ProfileScreen extends ConsumerStatefulWidget {
  const ProfileScreen({super.key});

  @override
  ConsumerState<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends ConsumerState<ProfileScreen> {
  final TextEditingController _loginCtrl = TextEditingController();
  final TextEditingController _passwordCtrl = TextEditingController();
  bool _obscurePassword = true;

  @override
  void dispose() {
    _loginCtrl.dispose();
    _passwordCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        title: const Text(
          'Profil va Boshqaruv',
          style: TextStyle(
            color: AppColors.textPrimary,
            fontWeight: FontWeight.w800,
            fontSize: 20,
          ),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (!auth.isAuthenticated)
            _buildLoginSection(auth)
          else ...[
            _buildUserProfileCard(auth),
            const SizedBox(height: 16),
            _buildRoleSwitcherCard(auth),
            const SizedBox(height: 16),
            _buildGeneralMenu(),
            const SizedBox(height: 20),
            _buildLogoutButton(),
          ],
        ],
      ),
    );
  }

  Widget _buildLoginSection(AuthState auth) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Tizimga kirish',
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: AppColors.brandDark),
          ),
          const SizedBox(height: 6),
          const Text(
            'Sotuvchi, Kuryer, Admin yoki Xaridor akkauntingiz bilan kiring:',
            style: TextStyle(fontSize: 13, color: AppColors.textSecondary),
          ),
          const SizedBox(height: 16),

          if (auth.error != null)
            Container(
              margin: const EdgeInsets.only(bottom: 14),
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.statusError.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: AppColors.statusError.withValues(alpha: 0.3)),
              ),
              child: Text(
                auth.error!,
                style: const TextStyle(color: AppColors.statusError, fontSize: 13, fontWeight: FontWeight.w500),
              ),
            ),

          TextField(
            controller: _loginCtrl,
            decoration: const InputDecoration(
              labelText: 'Email yoki Telefon',
              border: OutlineInputBorder(),
              prefixIcon: Icon(Icons.person_outline),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _passwordCtrl,
            obscureText: _obscurePassword,
            decoration: InputDecoration(
              labelText: 'Parol',
              border: const OutlineInputBorder(),
              prefixIcon: const Icon(Icons.lock_outline),
              suffixIcon: IconButton(
                icon: Icon(_obscurePassword ? Icons.visibility_off : Icons.visibility),
                onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
              ),
            ),
          ),
          const SizedBox(height: 16),

          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.brandDark,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              onPressed: auth.isLoading
                  ? null
                  : () async {
                      final loginVal = _loginCtrl.text.trim();
                      final passVal = _passwordCtrl.text.trim();
                      if (loginVal.isEmpty || passVal.isEmpty) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Login va parolni kiriting')),
                        );
                        return;
                      }
                      await ref.read(authProvider.notifier).login(loginVal, passVal);
                    },
              child: auth.isLoading
                  ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                  : const Text('Kirish', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
            ),
          ),

          const SizedBox(height: 8),
          Center(
            child: TextButton(
              onPressed: () => showAuthModal(context),
              child: const Text(
                "Hisobingiz yo'qmi? Ro'yxatdan o'ting",
                style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.brandEmerald, fontSize: 13),
              ),
            ),
          ),

          const SizedBox(height: 24),
          const Divider(),
          const SizedBox(height: 12),

          const Text(
            'Tezkor Demo Sinov (1-Bosishda Kirish):',
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.textSecondary),
          ),
          const SizedBox(height: 10),

          // 4 Quick Demo Buttons
          _demoLoginButton(
            title: 'Xaridor (Customer)',
            email: 'customer@market.uz',
            icon: Icons.shopping_basket_outlined,
            color: Colors.teal,
            roleType: 'customer',
          ),
          const SizedBox(height: 8),
          _demoLoginButton(
            title: 'Sotuvchi (Seller)',
            email: 'seller@market.uz',
            icon: Icons.storefront_outlined,
            color: Colors.indigo,
            roleType: 'seller',
          ),
          const SizedBox(height: 8),
          _demoLoginButton(
            title: 'Kuryer (Logistics)',
            email: 'courier@market.uz',
            icon: Icons.delivery_dining_outlined,
            color: Colors.deepOrange,
            roleType: 'courier',
          ),
          const SizedBox(height: 8),
          _demoLoginButton(
            title: 'Administrator (Superadmin)',
            email: 'admin@market.uz',
            icon: Icons.admin_panel_settings_outlined,
            color: AppColors.brandDark,
            roleType: 'admin',
          ),
        ],
      ),
    );
  }

  Widget _demoLoginButton({
    required String title,
    required String email,
    required IconData icon,
    required Color color,
    required String roleType,
  }) {
    return InkWell(
      onTap: () async {
        await ref.read(authProvider.notifier).quickDemoLogin(roleType);
      },
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withValues(alpha: 0.25)),
        ),
        child: Row(
          children: [
            Icon(icon, color: color, size: 22),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: TextStyle(fontWeight: FontWeight.bold, color: color, fontSize: 13)),
                  Text(email, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                ],
              ),
            ),
            Icon(Icons.arrow_forward_ios, size: 14, color: color),
          ],
        ),
      ),
    );
  }

  Widget _buildUserProfileCard(AuthState auth) {
    final user = auth.user;
    final initials = user != null && user.name.isNotEmpty
        ? user.name.split(' ').map((n) => n.isNotEmpty ? n[0] : '').take(2).join()
        : 'U';

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 28,
            backgroundColor: AppColors.brandDark,
            child: Text(
              initials,
              style: const TextStyle(color: AppColors.brandLime, fontWeight: FontWeight.bold, fontSize: 18),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  user?.name ?? 'Foydalanuvchi',
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                ),
                const SizedBox(height: 3),
                Text(
                  user?.email ?? '',
                  style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
                ),
                const SizedBox(height: 6),
                Wrap(
                  spacing: 4,
                  children: (user?.roles ?? ['customer']).map((r) {
                    return Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.brandLime.withValues(alpha: 0.3),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        r,
                        style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.brandDark),
                      ),
                    );
                  }).toList(),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRoleSwitcherCard(AuthState auth) {
    final user = auth.user;
    final currentRole = auth.activeRole;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Ish rejimi (Rolni almashtirish)',
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.brandDark),
          ),
          const SizedBox(height: 4),
          const Text(
            'Bitta ilova orqali xohlagan panelingizga 1 bosishda o\'ting:',
            style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
          ),
          const SizedBox(height: 12),

          // 1. Customer View
          _roleTile(
            title: 'Xaridor ko\'rinishi',
            subtitle: 'Katalog, savatcha, xaridlar va nasiya',
            icon: Icons.storefront_outlined,
            roleKey: 'customer',
            isActive: currentRole == 'customer',
          ),

          // 2. Seller View (if seller or admin)
          if (user?.isSeller == true || user?.isAdmin == true) ...[
            const SizedBox(height: 8),
            _roleTile(
              title: 'Sotuvchi paneli',
              subtitle: 'Do\'kon, tovarlar, ombor va tushumlar',
              icon: Icons.inventory_2_outlined,
              roleKey: 'seller',
              isActive: currentRole == 'seller',
            ),
          ],

          // 3. Courier View (if courier or admin)
          if (user?.isCourier == true || user?.isAdmin == true) ...[
            const SizedBox(height: 8),
            _roleTile(
              title: 'Kuryer paneli',
              subtitle: 'Yetkazib berish, marshrut va GPS kuzatuv',
              icon: Icons.delivery_dining_outlined,
              roleKey: 'courier',
              isActive: currentRole == 'courier',
            ),
          ],

          // 4. Admin View (if admin)
          if (user?.isAdmin == true) ...[
            const SizedBox(height: 8),
            _roleTile(
              title: 'Administrator paneli',
              subtitle: 'Do\'konlar moderatsiyasi, GMV va nazorat',
              icon: Icons.admin_panel_settings_outlined,
              roleKey: 'admin',
              isActive: currentRole == 'admin',
            ),
          ],
        ],
      ),
    );
  }

  Widget _roleTile({
    required String title,
    required String subtitle,
    required IconData icon,
    required String roleKey,
    required bool isActive,
  }) {
    return InkWell(
      onTap: () {
        ref.read(authProvider.notifier).switchActiveRole(roleKey);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Rejim almashtirildi: $title'),
            duration: const Duration(seconds: 1),
          ),
        );
      },
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: isActive ? AppColors.brandLime.withValues(alpha: 0.25) : Colors.grey.shade50,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: isActive ? AppColors.brandDark : const Color(0xFFE2E8F0),
            width: isActive ? 1.5 : 1,
          ),
        ),
        child: Row(
          children: [
            Icon(icon, color: isActive ? AppColors.brandDark : AppColors.textSecondary, size: 22),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: TextStyle(
                      fontWeight: isActive ? FontWeight.bold : FontWeight.w600,
                      fontSize: 13,
                      color: isActive ? AppColors.brandDark : AppColors.textPrimary,
                    ),
                  ),
                  Text(subtitle, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                ],
              ),
            ),
            if (isActive)
              const Icon(Icons.check_circle, color: AppColors.brandDark, size: 20)
            else
              const Icon(Icons.chevron_right, color: AppColors.textSecondary, size: 18),
          ],
        ),
      ),
    );
  }

  Widget _buildGeneralMenu() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        children: [
          ListTile(
            leading: const Icon(Icons.location_on_outlined, color: AppColors.brandDark),
            title: const Text('Yetkazish manzillari'),
            trailing: const Icon(Icons.chevron_right),
            onTap: () {},
          ),
          const Divider(height: 1),
          ListTile(
            leading: const Icon(Icons.assignment_return_outlined, color: AppColors.brandDark),
            title: const Text('Tovarni qaytarish (Refund)'),
            trailing: const Icon(Icons.chevron_right),
            onTap: () {},
          ),
          const Divider(height: 1),
          ListTile(
            leading: const Icon(Icons.support_agent_outlined, color: AppColors.brandDark),
            title: const Text('Qo\'llab-quvvatlash xizmati'),
            trailing: const Icon(Icons.chevron_right),
            onTap: () {},
          ),
          const Divider(height: 1),
          ListTile(
            leading: const Icon(Icons.dns_outlined, color: AppColors.brandDark),
            title: const Text('Server & Baza holati (Health)'),
            subtitle: const Text('API va MySQL diagnostikasi', style: TextStyle(fontSize: 11)),
            trailing: const Icon(Icons.chevron_right),
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const CustomerHealthScreen()),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildLogoutButton() {
    return OutlinedButton.icon(
      style: OutlinedButton.styleFrom(
        foregroundColor: AppColors.statusError,
        side: const BorderSide(color: AppColors.statusError),
        padding: const EdgeInsets.symmetric(vertical: 12),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
      icon: const Icon(Icons.logout, size: 20),
      label: const Text('Tizimdan chiqish (Logout)', style: TextStyle(fontWeight: FontWeight.bold)),
      onPressed: () {
        ref.read(authProvider.notifier).logout();
      },
    );
  }
}
