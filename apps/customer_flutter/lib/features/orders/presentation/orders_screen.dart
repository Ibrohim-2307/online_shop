import 'package:flutter/material.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/network/api_client.dart';
import '../../tracking/presentation/tracking_screen.dart';

class OrdersScreen extends StatefulWidget {
  const OrdersScreen({super.key});

  @override
  State<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends State<OrdersScreen> {
  final TextEditingController _searchCtrl = TextEditingController();
  List<dynamic> _allOrders = [];
  bool _isLoading = true;
  String _selectedStatus = 'all'; // all, in_transit, completed, cancelled

  @override
  void initState() {
    super.initState();
    _fetchOrders();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _fetchOrders() async {
    setState(() => _isLoading = true);
    try {
      final res = await ApiClient().get('/orders');
      if (mounted) {
        setState(() {
          _allOrders = (res is Map<String, dynamic> && res.containsKey('data'))
              ? res['data'] as List<dynamic>
              : (res is List ? res : []);
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  List<dynamic> get _filteredOrders {
    final query = _searchCtrl.text.trim().toLowerCase();

    return _allOrders.filter((order) {
      final orderMap = order is Map<String, dynamic> ? order : <String, dynamic>{};
      final orderNumber = (orderMap['order_number'] ?? '').toString().toLowerCase();
      final status = (orderMap['status'] ?? '').toString().toLowerCase();
      final suborders = (orderMap['suborders'] as List<dynamic>?) ?? [];

      // 1. Status Filter Tab
      if (_selectedStatus == 'in_transit') {
        final hasInTransitShipment = suborders.any((s) {
          final shipments = (s['shipments'] as List<dynamic>?) ?? [];
          return shipments.any((sh) => sh['status'] == 'in_transit');
        });
        final matchesStatus = ['processing', 'pending', 'pending_payment', 'in_transit'].contains(status) ||
            hasInTransitShipment;
        if (!matchesStatus) return false;
      } else if (_selectedStatus == 'completed') {
        if (!['completed', 'delivered'].contains(status)) return false;
      } else if (_selectedStatus == 'cancelled') {
        if (status != 'cancelled') return false;
      }

      // 2. Search Query Filter
      if (query.isNotEmpty) {
        final matchesOrderNum = orderNumber.contains(query);
        final matchesSeller = suborders.any((s) {
          final sellerName = (s['seller']?['name'] ?? '').toString().toLowerCase();
          return sellerName.contains(query);
        });
        final matchesItem = suborders.any((s) {
          final items = (s['items'] as List<dynamic>?) ?? [];
          return items.any((it) {
            final name = (it['product_name'] ?? it['name'] ?? '').toString().toLowerCase();
            return name.contains(query);
          });
        });

        if (!matchesOrderNum && !matchesSeller && !matchesItem) {
          return false;
        }
      }

      return true;
    }).toList();
  }

  String _formatCurrency(dynamic amount) {
    if (amount == null) return '0 UZS';
    final numVal = num.tryParse(amount.toString()) ?? 0;
    final parts = numVal.toStringAsFixed(0).split('');
    final buffer = StringBuffer();
    for (int i = 0; i < parts.length; i++) {
      if (i > 0 && (parts.length - i) % 3 == 0) {
        buffer.write(' ');
      }
      buffer.write(parts[i]);
    }
    return '${buffer.toString()} UZS';
  }

  Widget _buildFilterChip(String label, String value, IconData icon) {
    final isSelected = _selectedStatus == value;
    return InkWell(
      onTap: () => setState(() => _selectedStatus = value),
      borderRadius: BorderRadius.circular(20),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.brandDark : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected ? AppColors.brandDark : const Color(0xFFE2E8F0),
          ),
          boxShadow: isSelected
              ? [BoxShadow(color: AppColors.brandDark.withValues(alpha: 0.2), blurRadius: 4, offset: const Offset(0, 2))]
              : null,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 14,
              color: isSelected ? AppColors.brandLime : AppColors.textSecondary,
            ),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                color: isSelected ? Colors.white : AppColors.textPrimary,
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _filteredOrders;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        title: const Row(
          children: [
            Icon(Icons.receipt_long_outlined, color: AppColors.brandDark),
            SizedBox(width: 8),
            Text(
              'Mening Buyurtmalarim',
              style: TextStyle(
                color: AppColors.textPrimary,
                fontWeight: FontWeight.w800,
                fontSize: 18,
              ),
            ),
          ],
        ),
      ),
      body: Column(
        children: [
          // Search & Filter header container
          Container(
            color: Colors.white,
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
            child: Column(
              children: [
                // Search TextField
                TextField(
                  controller: _searchCtrl,
                  onChanged: (_) => setState(() {}),
                  decoration: InputDecoration(
                    hintText: 'Buyurtma raqami yoki mahsulot nomi...',
                    hintStyle: const TextStyle(fontSize: 13, color: AppColors.textSecondary),
                    prefixIcon: const Icon(Icons.search, size: 20, color: AppColors.textSecondary),
                    suffixIcon: _searchCtrl.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear, size: 18, color: AppColors.textSecondary),
                            onPressed: () {
                              _searchCtrl.clear();
                              setState(() {});
                            },
                          )
                        : null,
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                    contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 14),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                    ),
                  ),
                ),
                const SizedBox(height: 10),

                // Horizontal Filter Chips
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _buildFilterChip('Barchasi', 'all', Icons.all_inbox),
                      const SizedBox(width: 8),
                      _buildFilterChip('Yo\'lda / Kutilmoqda', 'in_transit', Icons.local_shipping_outlined),
                      const SizedBox(width: 8),
                      _buildFilterChip('Yetkazilgan', 'completed', Icons.check_circle_outline),
                      const SizedBox(width: 8),
                      _buildFilterChip('Bekor qilingan', 'cancelled', Icons.cancel_outlined),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Main list
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: AppColors.brandDark))
                : filtered.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.inventory_2_outlined,
                              size: 56,
                              color: AppColors.textSecondary.withValues(alpha: 0.5),
                            ),
                            const SizedBox(height: 12),
                            Text(
                              _searchCtrl.text.isNotEmpty || _selectedStatus != 'all'
                                  ? 'Mos keladigan buyurtma topilmadi'
                                  : 'Hozircha buyurtmalar yo\'q',
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 15,
                                color: AppColors.textPrimary,
                              ),
                            ),
                            const SizedBox(height: 4),
                            const Text(
                              'Katalogdan o\'zingizga yoqqan tovarlarni tanlang',
                              style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                            ),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: _fetchOrders,
                        color: AppColors.brandDark,
                        child: ListView.separated(
                          padding: const EdgeInsets.all(16),
                          itemCount: filtered.length,
                          separatorBuilder: (context, index) => const SizedBox(height: 12),
                          itemBuilder: (context, i) {
                            final order = filtered[i] as Map<String, dynamic>;
                            final orderNumber = order['order_number'] ?? 'ORD';
                            final status = (order['status'] ?? 'pending').toString().toLowerCase();
                            final grandTotal = order['grand_total'] ?? 0;
                            final suborders = (order['suborders'] as List<dynamic>?) ?? [];
                            final createdAt = order['created_at']?.toString().split('T').first;

                            // Badge styling
                            Color badgeBg = AppColors.brandLime;
                            Color badgeFg = AppColors.brandDark;
                            String badgeText = status.toUpperCase();

                            if (status == 'completed' || status == 'delivered') {
                              badgeBg = AppColors.statusSuccess.withValues(alpha: 0.15);
                              badgeFg = AppColors.statusSuccess;
                              badgeText = 'YETKAZILDI';
                            } else if (status == 'in_transit' || status == 'processing') {
                              badgeBg = const Color(0xFFEFF6FF);
                              badgeFg = const Color(0xFF1D4ED8);
                              badgeText = 'YO\'LDA';
                            } else if (status == 'cancelled') {
                              badgeBg = AppColors.statusError.withValues(alpha: 0.15);
                              badgeFg = AppColors.statusError;
                              badgeText = 'BEKOR QILINGAN';
                            }

                            return Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(14),
                                border: Border.all(color: const Color(0xFFE2E8F0)),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withValues(alpha: 0.02),
                                    blurRadius: 8,
                                    offset: const Offset(0, 2),
                                  ),
                                ],
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            orderNumber,
                                            style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15),
                                          ),
                                          if (createdAt != null)
                                            Text(
                                              createdAt,
                                              style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                                            ),
                                        ],
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                        decoration: BoxDecoration(
                                          color: badgeBg,
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Text(
                                          badgeText,
                                          style: TextStyle(
                                            fontWeight: FontWeight.bold,
                                            fontSize: 11,
                                            color: badgeFg,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 12),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      const Text(
                                        'Jami summa:',
                                        style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                                      ),
                                      Text(
                                        _formatCurrency(grandTotal),
                                        style: const TextStyle(
                                          fontWeight: FontWeight.w900,
                                          fontSize: 16,
                                          color: AppColors.brandDark,
                                        ),
                                      ),
                                    ],
                                  ),
                                  const Divider(height: 20),

                                  // Suborders and items
                                  ...suborders.map((sub) {
                                    final shipments = (sub['shipments'] as List<dynamic>?) ?? [];
                                    final items = (sub['items'] as List<dynamic>?) ?? [];
                                    final sellerName = sub['seller']?['name'] ?? "Hamkor do'kon";

                                    return Container(
                                      margin: const EdgeInsets.only(bottom: 8),
                                      padding: const EdgeInsets.all(10),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFF8FAFC),
                                        borderRadius: BorderRadius.circular(10),
                                      ),
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Row(
                                            children: [
                                              const Icon(Icons.storefront, size: 14, color: AppColors.textSecondary),
                                              const SizedBox(width: 6),
                                              Expanded(
                                                child: Text(
                                                  'Do\'kon: $sellerName',
                                                  style: const TextStyle(
                                                    fontWeight: FontWeight.bold,
                                                    fontSize: 12,
                                                    color: AppColors.textPrimary,
                                                  ),
                                                ),
                                              ),
                                            ],
                                          ),
                                          if (items.isNotEmpty) ...[
                                            const SizedBox(height: 6),
                                            ...items.map((it) {
                                              final itName = it['product_name'] ?? it['name'] ?? 'Mahsulot';
                                              final itQty = it['quantity'] ?? 1;
                                              return Padding(
                                                padding: const EdgeInsets.symmetric(vertical: 2),
                                                child: Text(
                                                  '• $itName × $itQty',
                                                  style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                                                ),
                                              );
                                            }),
                                          ],
                                          if (shipments.isNotEmpty)
                                            Padding(
                                              padding: const EdgeInsets.only(top: 8),
                                              child: SizedBox(
                                                width: double.infinity,
                                                child: ElevatedButton.icon(
                                                  style: ElevatedButton.styleFrom(
                                                    backgroundColor: Colors.white,
                                                    foregroundColor: AppColors.brandDark,
                                                    elevation: 0,
                                                    side: const BorderSide(color: AppColors.brandDark),
                                                    shape: RoundedRectangleBorder(
                                                      borderRadius: BorderRadius.circular(8),
                                                    ),
                                                  ),
                                                  icon: const Icon(Icons.location_on, size: 16),
                                                  label: const Text(
                                                    'Yetkazishni kuzatish (GPS)',
                                                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                                                  ),
                                                  onPressed: () {
                                                    final trkNumber = shipments.first['tracking_number'];
                                                    Navigator.push(
                                                      context,
                                                      MaterialPageRoute(
                                                        builder: (_) => TrackingScreen(trackingNumber: trkNumber),
                                                      ),
                                                    );
                                                  },
                                                ),
                                              ),
                                            ),
                                        ],
                                      ),
                                    );
                                  }),
                                ],
                              ),
                            );
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }
}

extension _IterableFilter on List<dynamic> {
  Iterable<dynamic> filter(bool Function(dynamic) predicate) sync* {
    for (final element in this) {
      if (predicate(element)) yield element;
    }
  }
}
