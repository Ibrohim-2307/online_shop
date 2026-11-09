import 'package:flutter/material.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/network/api_client.dart';
import '../domain/delivery_model.dart';
import 'active_trip_screen.dart';

class DeliveriesScreen extends StatefulWidget {
  const DeliveriesScreen({super.key});

  @override
  State<DeliveriesScreen> createState() => _DeliveriesScreenState();
}

class _DeliveriesScreenState extends State<DeliveriesScreen> {
  final TextEditingController _searchCtrl = TextEditingController();
  List<DeliveryModel> _deliveries = [];
  bool _isLoading = true;
  String _filter = 'today'; // today, pickup, in_transit, cod, completed, all

  @override
  void initState() {
    super.initState();
    _fetchDeliveries();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _fetchDeliveries() async {
    setState(() => _isLoading = true);
    try {
      final res = await CourierApiClient().get('/logistics/shipments');
      List<dynamic> items = [];
      if (res is Map<String, dynamic> && res.containsKey('data')) {
        items = res['data'] as List<dynamic>;
      } else if (res is List) {
        items = res;
      }

      if (mounted) {
        setState(() {
          _deliveries = items.map((j) => DeliveryModel.fromJson(j as Map<String, dynamic>)).toList();
          _isLoading = false;
        });
      }
    } catch (_) {
      // Mock fallback for offline / demo mode
      if (mounted) {
        setState(() {
          _deliveries = [
            DeliveryModel(
              id: 1,
              trackingNumber: 'TRK-20261020-001',
              recipientName: 'Aziz Xaridor',
              phone: '+998 90 123 45 67',
              address: 'Toshkent sh., Yunusobod 4-mavze, 12-uy',
              status: 'in_transit',
              itemsCount: 2,
              totalAmount: 325000,
              storeName: 'Techno Store',
              paymentMethod: 'cash_on_delivery',
            ),
            DeliveryModel(
              id: 2,
              trackingNumber: 'TRK-20261020-002',
              recipientName: 'Jasur Bek',
              phone: '+998 93 987 65 43',
              address: 'Toshkent sh., Chilonzor 7-mavze, 5-uy',
              status: 'assigned',
              itemsCount: 1,
              totalAmount: 120000,
              storeName: 'Smart House',
              paymentMethod: 'cash_on_delivery',
            ),
            DeliveryModel(
              id: 3,
              trackingNumber: 'TRK-20261020-003',
              recipientName: 'Gulbahor Karimova',
              phone: '+998 97 555 44 33',
              address: 'Toshkent sh., Mirzo Ulug\'bek, 44-uy',
              status: 'delivered',
              itemsCount: 3,
              totalAmount: 840000,
              storeName: 'Oltin Don',
              paymentMethod: 'card',
            ),
          ];
          _isLoading = false;
        });
      }
    }
  }

  List<DeliveryModel> get _filteredDeliveries {
    final query = _searchCtrl.text.trim().toLowerCase();

    return _deliveries.where((d) {
      // 1. Status / Category Filter Tab
      if (_filter == 'today') {
        if (!d.isActive) return false;
      } else if (_filter == 'pickup') {
        if (d.status != 'assigned') return false;
      } else if (_filter == 'in_transit') {
        if (!['picked_up', 'in_transit'].contains(d.status)) return false;
      } else if (_filter == 'cod') {
        if (!d.isCod || !d.isActive) return false;
      } else if (_filter == 'completed') {
        if (!d.isDelivered) return false;
      }

      // 2. Search Query Filter
      if (query.isNotEmpty) {
        final matchesTrk = d.trackingNumber.toLowerCase().contains(query);
        final matchesName = d.recipientName.toLowerCase().contains(query);
        final matchesPhone = d.phone.toLowerCase().contains(query);
        final matchesAddr = d.address.toLowerCase().contains(query);
        final matchesStore = (d.storeName ?? '').toLowerCase().contains(query);

        if (!matchesTrk && !matchesName && !matchesPhone && !matchesAddr && !matchesStore) {
          return false;
        }
      }

      return true;
    }).toList();
  }

  int _countFor(String tab) {
    switch (tab) {
      case 'today':
        return _deliveries.where((d) => d.isActive).length;
      case 'pickup':
        return _deliveries.where((d) => d.status == 'assigned').length;
      case 'in_transit':
        return _deliveries.where((d) => ['picked_up', 'in_transit'].contains(d.status)).length;
      case 'cod':
        return _deliveries.where((d) => d.isCod && d.isActive).length;
      case 'completed':
        return _deliveries.where((d) => d.isDelivered).length;
      default:
        return _deliveries.length;
    }
  }

  String _formatCurrency(num amount) {
    final parts = amount.round().toString().split('');
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
    final isSelected = _filter == value;
    final count = _countFor(value);

    return InkWell(
      onTap: () => setState(() => _filter = value),
      borderRadius: BorderRadius.circular(20),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.brandDark : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected ? AppColors.brandDark : const Color(0xFFE2E8F0),
          ),
          boxShadow: isSelected
              ? [BoxShadow(color: AppColors.brandDark.withValues(alpha: 0.15), blurRadius: 4, offset: const Offset(0, 2))]
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
            const SizedBox(width: 5),
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                color: isSelected ? Colors.white : AppColors.textPrimary,
              ),
            ),
            if (count > 0) ...[
              const SizedBox(width: 5),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                decoration: BoxDecoration(
                  color: isSelected ? AppColors.brandLime : const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  '$count',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                    color: isSelected ? AppColors.brandDark : AppColors.textSecondary,
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _filteredDeliveries;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        title: const Row(
          children: [
            Icon(Icons.two_wheeler, color: AppColors.brandDark),
            SizedBox(width: 8),
            Text(
              'Kuryer Yetkazmalari',
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
                // Search Input
                TextField(
                  controller: _searchCtrl,
                  onChanged: (_) => setState(() {}),
                  decoration: InputDecoration(
                    hintText: 'Trek raqami, xaridor yoki manzil...',
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

                // Horizontal Categorized Tabs
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _buildFilterChip('Bugun topshirish', 'today', Icons.local_shipping),
                      const SizedBox(width: 8),
                      _buildFilterChip('Do\'kondan olish', 'pickup', Icons.storefront),
                      const SizedBox(width: 8),
                      _buildFilterChip('Qo\'limda', 'in_transit', Icons.inventory),
                      const SizedBox(width: 8),
                      _buildFilterChip('Naqd pul (COD)', 'cod', Icons.payments_outlined),
                      const SizedBox(width: 8),
                      _buildFilterChip('Yetkazilgan', 'completed', Icons.check_circle_outline),
                      const SizedBox(width: 8),
                      _buildFilterChip('Barchasi', 'all', Icons.all_inbox),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Deliveries list
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: AppColors.brandDark))
                : filtered.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.two_wheeler_outlined,
                              size: 56,
                              color: AppColors.textSecondary.withValues(alpha: 0.5),
                            ),
                            const SizedBox(height: 12),
                            Text(
                              _searchCtrl.text.isNotEmpty || _filter != 'all'
                                  ? 'Ushbu bo\'limda topshiriq topilmadi'
                                  : 'Biriktirilgan topshiriqlar yo\'q',
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 15,
                                color: AppColors.textPrimary,
                              ),
                            ),
                            const SizedBox(height: 4),
                            const Text(
                              'Yangi buyurtmalar kelishi bilan ro\'yxatda paydo bo\'ladi',
                              style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                            ),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: _fetchDeliveries,
                        color: AppColors.brandDark,
                        child: ListView.separated(
                          padding: const EdgeInsets.all(16),
                          itemCount: filtered.length,
                          separatorBuilder: (context, index) => const SizedBox(height: 12),
                          itemBuilder: (context, i) {
                            final delivery = filtered[i];

                            Color statusBg = AppColors.brandLime;
                            Color statusFg = AppColors.brandDark;
                            String statusText = delivery.status.toUpperCase();

                            if (delivery.isDelivered) {
                              statusBg = AppColors.statusSuccess.withValues(alpha: 0.15);
                              statusFg = AppColors.statusSuccess;
                              statusText = 'YETKAZILDI';
                            } else if (delivery.status == 'in_transit') {
                              statusBg = const Color(0xFFFEF3C7);
                              statusFg = const Color(0xFFB45309);
                              statusText = 'YO\'LDA';
                            } else if (delivery.status == 'assigned') {
                              statusBg = const Color(0xFFEFF6FF);
                              statusFg = const Color(0xFF1D4ED8);
                              statusText = 'DO\'KONDAN OLISH';
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
                                      Text(
                                        delivery.trackingNumber,
                                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: statusBg,
                                          borderRadius: BorderRadius.circular(6),
                                        ),
                                        child: Text(
                                          statusText,
                                          style: TextStyle(
                                            fontSize: 10,
                                            fontWeight: FontWeight.bold,
                                            color: statusFg,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 10),

                                  // Recipient & phone
                                  Row(
                                    children: [
                                      const Icon(Icons.person_outline, size: 16, color: AppColors.brandDark),
                                      const SizedBox(width: 6),
                                      Expanded(
                                        child: Text(
                                          delivery.recipientName,
                                          style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
                                        ),
                                      ),
                                      Text(
                                        delivery.phone,
                                        style: const TextStyle(
                                          fontWeight: FontWeight.w600,
                                          fontSize: 12,
                                          color: AppColors.brandDark,
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 6),

                                  // Address
                                  Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Icon(Icons.location_on_outlined, size: 16, color: AppColors.textSecondary),
                                      const SizedBox(width: 6),
                                      Expanded(
                                        child: Text(
                                          delivery.address,
                                          style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                                        ),
                                      ),
                                    ],
                                  ),

                                  if (delivery.storeName != null) ...[
                                    const SizedBox(height: 6),
                                    Row(
                                      children: [
                                        const Icon(Icons.storefront_outlined, size: 16, color: AppColors.textSecondary),
                                        const SizedBox(width: 6),
                                        Text(
                                          'Do\'kon: ${delivery.storeName}',
                                          style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                                        ),
                                      ],
                                    ),
                                  ],

                                  const Divider(height: 20),

                                  // COD or Paid Badge & Amount
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      if (delivery.isCod && !delivery.isDelivered)
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                          decoration: BoxDecoration(
                                            color: const Color(0xFFFEF3C7),
                                            borderRadius: BorderRadius.circular(6),
                                            border: Border.all(color: const Color(0xFFFDE68A)),
                                          ),
                                          child: Row(
                                            children: [
                                              const Icon(Icons.payments, size: 14, color: Color(0xFFB45309)),
                                              const SizedBox(width: 4),
                                              Text(
                                                'Naqd pul (COD): ${_formatCurrency(delivery.totalAmount)}',
                                                style: const TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 11,
                                                  color: Color(0xFF92400E),
                                                ),
                                              ),
                                            ],
                                          ),
                                        )
                                      else
                                        Text(
                                          'Summa: ${_formatCurrency(delivery.totalAmount)}',
                                          style: const TextStyle(
                                            fontWeight: FontWeight.bold,
                                            fontSize: 13,
                                            color: AppColors.brandDark,
                                          ),
                                        ),
                                      Text(
                                        '${delivery.itemsCount} dona tovar',
                                        style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                                      ),
                                    ],
                                  ),

                                  const SizedBox(height: 12),

                                  // Action Button
                                  SizedBox(
                                    width: double.infinity,
                                    child: ElevatedButton.icon(
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: delivery.isDelivered ? Colors.white : AppColors.brandLime,
                                        foregroundColor: AppColors.brandDark,
                                        elevation: 0,
                                        side: delivery.isDelivered
                                            ? const BorderSide(color: Color(0xFFE2E8F0))
                                            : null,
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                        padding: const EdgeInsets.symmetric(vertical: 11),
                                      ),
                                      icon: Icon(
                                        delivery.isDelivered
                                            ? Icons.check_circle_outline
                                            : Icons.navigation_outlined,
                                        size: 18,
                                      ),
                                      label: Text(
                                        delivery.isDelivered
                                            ? 'Tafsilotlar'
                                            : (delivery.status == 'assigned'
                                                ? 'Do\'kondan qabul qilish / Safar'
                                                : 'Mijozga topshirish / Safar'),
                                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                      ),
                                      onPressed: () {
                                        Navigator.push(
                                          context,
                                          MaterialPageRoute(
                                            builder: (_) => ActiveTripScreen(delivery: delivery),
                                          ),
                                        ).then((_) => _fetchDeliveries());
                                      },
                                    ),
                                  ),
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
