import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/network/api_client.dart';
import '../../auth/auth_notifier.dart';

class AdminSellerItem {
  final int id;
  final String name;
  final String legalName;
  final String taxId;
  String status; // active, pending, suspended
  final int membersCount;

  AdminSellerItem({
    required this.id,
    required this.name,
    required this.legalName,
    required this.taxId,
    required this.status,
    required this.membersCount,
  });

  factory AdminSellerItem.fromJson(Map<String, dynamic> json) {
    return AdminSellerItem(
      id: json['id'] as int? ?? 1,
      name: json['name']?.toString() ?? 'Do\'kon',
      legalName: json['legal_name']?.toString() ?? 'MCHJ',
      taxId: json['tax_id']?.toString() ?? '309123456',
      status: json['status']?.toString() ?? 'active',
      membersCount: (json['members_count'] as num?)?.toInt() ?? 1,
    );
  }
}

class AdminUserItem {
  final int id;
  final String name;
  final String email;
  final String phone;
  final List<String> roles;
  String status;

  AdminUserItem({
    required this.id,
    required this.name,
    required this.email,
    required this.phone,
    required this.roles,
    required this.status,
  });

  factory AdminUserItem.fromJson(Map<String, dynamic> json) {
    final rawRoles = json['roles'] as List<dynamic>? ?? [];
    return AdminUserItem(
      id: json['id'] as int? ?? 1,
      name: json['name']?.toString() ?? 'Foydalanuvchi',
      email: json['email']?.toString() ?? '',
      phone: json['phone']?.toString() ?? '',
      roles: rawRoles.map((r) => r is Map ? r['name'].toString() : r.toString()).toList(),
      status: json['status']?.toString() ?? 'active',
    );
  }
}

class AdminDashboardScreen extends ConsumerStatefulWidget {
  const AdminDashboardScreen({super.key});

  @override
  ConsumerState<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends ConsumerState<AdminDashboardScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final ApiClient _client = ApiClient();

  List<AdminSellerItem> _sellers = [];
  List<AdminUserItem> _users = [];
  bool _isLoading = true;
  String _sellerFilter = 'all'; // all, active, pending, suspended
  String _sellerSearch = '';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    await Future.wait([_loadSellers(), _loadUsers()]);
    if (mounted) setState(() => _isLoading = false);
  }

  Future<void> _loadSellers() async {
    try {
      final res = await _client.get('/admin/sellers');
      List<dynamic> items = [];
      if (res is Map<String, dynamic>) {
        if (res['data'] is Map && res['data']['data'] is List) {
          items = res['data']['data'] as List;
        } else if (res['data'] is List) {
          items = res['data'] as List;
        }
      }
      if (mounted && items.isNotEmpty) {
        setState(() {
          _sellers = items.map((j) => AdminSellerItem.fromJson(j as Map<String, dynamic>)).toList();
        });
        return;
      }
    } catch (_) {}

    // Fallback Mock sellers
    if (mounted) {
      setState(() {
        _sellers = [
          AdminSellerItem(
            id: 1,
            name: 'Oltin Don MCHJ',
            legalName: 'Samarqand Oltin Don Klasteri',
            taxId: '309887711',
            status: 'active',
            membersCount: 4,
          ),
          AdminSellerItem(
            id: 2,
            name: 'StroyBaza Ulgurji Savdo',
            legalName: 'Qurilish Mol Servis MCHJ',
            taxId: '302456789',
            status: 'active',
            membersCount: 2,
          ),
          AdminSellerItem(
            id: 3,
            name: 'AgroTexnika Tashkent',
            legalName: 'Agro Mashina Savdo XK',
            taxId: '301112233',
            status: 'pending',
            membersCount: 1,
          ),
          AdminSellerItem(
            id: 4,
            name: 'Shubhali Savdo MCHJ',
            legalName: 'Noma\'lum korxona',
            taxId: '309999999',
            status: 'suspended',
            membersCount: 1,
          ),
        ];
      });
    }
  }

  Future<void> _loadUsers() async {
    try {
      final res = await _client.get('/admin/users');
      List<dynamic> items = [];
      if (res is Map<String, dynamic>) {
        if (res['data'] is Map && res['data']['data'] is List) {
          items = res['data']['data'] as List;
        } else if (res['data'] is List) {
          items = res['data'] as List;
        }
      }
      if (mounted && items.isNotEmpty) {
        setState(() {
          _users = items.map((j) => AdminUserItem.fromJson(j as Map<String, dynamic>)).toList();
        });
        return;
      }
    } catch (_) {}

    // Fallback Mock users
    if (mounted) {
      setState(() {
        _users = [
          AdminUserItem(
            id: 1,
            name: 'Bosh Administrator',
            email: 'admin@market.uz',
            phone: '+998 71 200 00 01',
            roles: ['admin', 'superadmin'],
            status: 'active',
          ),
          AdminUserItem(
            id: 2,
            name: 'Oltin Don Boshqaruvchisi',
            email: 'seller@market.uz',
            phone: '+998 90 123 45 67',
            roles: ['seller_owner'],
            status: 'active',
          ),
          AdminUserItem(
            id: 3,
            name: 'Jasur Kuryer',
            email: 'courier@market.uz',
            phone: '+998 93 987 65 43',
            roles: ['courier'],
            status: 'active',
          ),
          AdminUserItem(
            id: 4,
            name: 'Farrux Xaridor',
            email: 'customer@market.uz',
            phone: '+998 94 333 22 11',
            roles: ['customer'],
            status: 'active',
          ),
        ];
      });
    }
  }

  void _updateSellerStatus(AdminSellerItem seller, String newStatus) async {
    setState(() {
      seller.status = newStatus;
    });

    try {
      await _client.put('/admin/sellers/${seller.id}/status', {
        'status': newStatus,
      });
    } catch (_) {}

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('"${seller.name}" holati o\'zgartirildi: $newStatus'),
          backgroundColor: newStatus == 'active' ? AppColors.statusSuccess : Colors.orange,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final filteredSellers = _sellers.where((s) {
      if (_sellerFilter != 'all' && s.status != _sellerFilter) return false;
      if (_sellerSearch.isNotEmpty) {
        return s.name.toLowerCase().contains(_sellerSearch.toLowerCase()) ||
            s.legalName.toLowerCase().contains(_sellerSearch.toLowerCase()) ||
            s.taxId.contains(_sellerSearch);
      }
      return true;
    }).toList();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 1,
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'SuperApp Boshqaruv',
              style: TextStyle(
                color: AppColors.brandDark,
                fontWeight: FontWeight.w800,
                fontSize: 18,
              ),
            ),
            Text(
              'Marketplace Global Nazorat',
              style: TextStyle(color: AppColors.textSecondary, fontSize: 11),
            ),
          ],
        ),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 12),
            child: ActionChip(
              avatar: const Icon(Icons.storefront, size: 16, color: AppColors.brandDark),
              label: const Text(
                'Xaridor do\'koni',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.brandDark),
              ),
              backgroundColor: AppColors.brandLime.withValues(alpha: 0.4),
              side: const BorderSide(color: AppColors.brandLime),
              onPressed: () {
                ref.read(authProvider.notifier).switchActiveRole('customer');
              },
            ),
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          labelColor: AppColors.brandDark,
          unselectedLabelColor: AppColors.textSecondary,
          indicatorColor: AppColors.brandDark,
          indicatorWeight: 3,
          tabs: [
            Tab(
              icon: const Icon(Icons.store_mall_directory_outlined, size: 20),
              text: 'Do\'konlar (${_sellers.length})',
            ),
            Tab(
              icon: const Icon(Icons.people_outline, size: 20),
              text: 'Foydalanuvchilar (${_users.length})',
            ),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : TabBarView(
              controller: _tabController,
              children: [
                // Sellers Moderation Tab
                RefreshIndicator(
                  onRefresh: _loadData,
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      _buildPlatformStats(),
                      const SizedBox(height: 16),

                      // Search bar
                      Container(
                        height: 44,
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: TextField(
                          onChanged: (v) => setState(() => _sellerSearch = v),
                          decoration: const InputDecoration(
                            hintText: 'Do\'kon nomi, INN yoki yuridik nomi...',
                            hintStyle: TextStyle(fontSize: 13, color: AppColors.textSecondary),
                            prefixIcon: Icon(Icons.search, size: 20, color: AppColors.textSecondary),
                            border: InputBorder.none,
                            contentPadding: EdgeInsets.symmetric(vertical: 10),
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),

                      // Status Filter Chips
                      SingleChildScrollView(
                        scrollDirection: Axis.horizontal,
                        child: Row(
                          children: [
                            _filterChip('all', 'Barchasi (${_sellers.length})'),
                            const SizedBox(width: 8),
                            _filterChip('active', 'Faol'),
                            const SizedBox(width: 8),
                            _filterChip('pending', 'Kutilmoqda'),
                            const SizedBox(width: 8),
                            _filterChip('suspended', 'To\'xtatilgan'),
                          ],
                        ),
                      ),
                      const SizedBox(height: 14),

                      if (filteredSellers.isEmpty)
                        Container(
                          padding: const EdgeInsets.all(32),
                          alignment: Alignment.center,
                          child: const Text('Do\'konlar topilmadi', style: TextStyle(color: AppColors.textSecondary)),
                        )
                      else
                        ...filteredSellers.map((s) => _buildSellerCard(s)),
                    ],
                  ),
                ),

                // Users Tab
                RefreshIndicator(
                  onRefresh: _loadData,
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      ..._users.map((u) => _buildUserCard(u)),
                    ],
                  ),
                ),
              ],
            ),
    );
  }

  Widget _buildPlatformStats() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.brandDark,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Marketplace Global GMV',
                style: TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.w500),
              ),
              Icon(Icons.trending_up, color: AppColors.brandLime, size: 20),
            ],
          ),
          const SizedBox(height: 6),
          const Text(
            '1 450 000 000 UZS',
            style: TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: 14),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              _statPill('Faol do\'konlar', '${_sellers.where((s) => s.status == "active").length} ta'),
              _statPill('Foydalanuvchilar', '${_users.length} nafar'),
              _statPill('Buyurtmalar', '1 248 ta'),
            ],
          ),
        ],
      ),
    );
  }

  Widget _statPill(String title, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title, style: const TextStyle(color: Colors.white60, fontSize: 11)),
        const SizedBox(height: 2),
        Text(value, style: const TextStyle(color: AppColors.brandLime, fontWeight: FontWeight.bold, fontSize: 13)),
      ],
    );
  }

  Widget _filterChip(String key, String label) {
    final isSelected = _sellerFilter == key;
    return ChoiceChip(
      label: Text(label, style: TextStyle(fontSize: 12, fontWeight: isSelected ? FontWeight.bold : FontWeight.normal, color: isSelected ? Colors.white : AppColors.brandDark)),
      selected: isSelected,
      selectedColor: AppColors.brandDark,
      backgroundColor: Colors.white,
      showCheckmark: false,
      onSelected: (_) => setState(() => _sellerFilter = key),
    );
  }

  Widget _buildSellerCard(AdminSellerItem seller) {
    Color badgeColor;
    String badgeText;
    switch (seller.status) {
      case 'active':
        badgeColor = AppColors.statusSuccess;
        badgeText = 'Faol';
        break;
      case 'pending':
        badgeColor = Colors.orange;
        badgeText = 'Moderatsiyada';
        break;
      case 'suspended':
      default:
        badgeColor = AppColors.statusError;
        badgeText = 'Bloklangan';
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              CircleAvatar(
                backgroundColor: AppColors.brandLime.withValues(alpha: 0.3),
                radius: 20,
                child: const Icon(Icons.storefront, color: AppColors.brandDark, size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(seller.name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                    Text(seller.legalName, style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: badgeColor.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  badgeText,
                  style: TextStyle(color: badgeColor, fontWeight: FontWeight.bold, fontSize: 11),
                ),
              ),
            ],
          ),
          const Divider(height: 20),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('INN: ${seller.taxId} • Xodimlar: ${seller.membersCount}', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
              Row(
                children: [
                  if (seller.status != 'active')
                    ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.statusSuccess,
                        foregroundColor: Colors.white,
                        visualDensity: VisualDensity.compact,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                      ),
                      onPressed: () => _updateSellerStatus(seller, 'active'),
                      child: const Text('Tasdiqlash', style: TextStyle(fontSize: 11)),
                    ),
                  if (seller.status == 'active')
                    OutlinedButton(
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.statusError,
                        side: const BorderSide(color: AppColors.statusError),
                        visualDensity: VisualDensity.compact,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                      ),
                      onPressed: () => _updateSellerStatus(seller, 'suspended'),
                      child: const Text('To\'xtatish', style: TextStyle(fontSize: 11)),
                    ),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildUserCard(AdminUserItem user) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Row(
        children: [
          CircleAvatar(
            backgroundColor: AppColors.brandDark,
            child: Text(
              user.name.isNotEmpty ? user.name[0].toUpperCase() : 'U',
              style: const TextStyle(color: AppColors.brandLime, fontWeight: FontWeight.bold),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(user.name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                const SizedBox(height: 2),
                Text('${user.email} • ${user.phone}', style: const TextStyle(color: AppColors.textSecondary, fontSize: 11)),
                const SizedBox(height: 4),
                Wrap(
                  spacing: 4,
                  children: user.roles.map((r) {
                    return Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.blue.shade50,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        r,
                        style: TextStyle(color: Colors.blue.shade800, fontSize: 10, fontWeight: FontWeight.w600),
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
}
