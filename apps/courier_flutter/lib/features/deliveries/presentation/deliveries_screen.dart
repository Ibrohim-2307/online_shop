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
  List<DeliveryModel> _deliveries = [];
  bool _isLoading = true;
  String _filter = 'active'; // active, completed, all

  @override
  void initState() {
    super.initState();
    _fetchDeliveries();
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
            ),
          ];
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _deliveries.where((d) {
      if (_filter == 'active') return d.isActive;
      if (_filter == 'completed') return d.isDelivered;
      return true;
    }).toList();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.background,
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
          // Filter tabs
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Row(
              children: [
                _buildFilterChip('Aktiv safarlar', 'active'),
                const SizedBox(width: 8),
                _buildFilterChip('Yetkazilganlar', 'completed'),
                const SizedBox(width: 8),
                _buildFilterChip('Barchasi', 'all'),
              ],
            ),
          ),

          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: AppColors.brandDark))
                : filtered.isEmpty
                    ? const Center(
                        child: Text(
                          'Biriktirilgan topshiriqlar yo\'q',
                          style: TextStyle(color: AppColors.textSecondary),
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
                            return InkWell(
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) => ActiveTripScreen(delivery: delivery),
                                  ),
                                ).then((_) => _fetchDeliveries());
                              },
                              child: Container(
                                padding: const EdgeInsets.all(16),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(14),
                                  border: Border.all(color: const Color(0xFFE2E8F0)),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          delivery.trackingNumber,
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: delivery.isDelivered
                                                ? AppColors.statusSuccess.withValues(alpha: 0.2)
                                                : AppColors.brandLime,
                                            borderRadius: BorderRadius.circular(6),
                                          ),
                                          child: Text(
                                            delivery.status.toUpperCase(),
                                            style: const TextStyle(
                                              fontSize: 10,
                                              fontWeight: FontWeight.bold,
                                              color: AppColors.brandDark,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 8),
                                    Text(
                                      delivery.recipientName,
                                      style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
                                    ),
                                    const SizedBox(height: 4),
                                    Row(
                                      children: [
                                        const Icon(Icons.location_on_outlined, size: 16, color: AppColors.textSecondary),
                                        const SizedBox(width: 4),
                                        Expanded(
                                          child: Text(
                                            delivery.address,
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                            style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const Divider(height: 20),
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          '${delivery.totalAmount} UZS',
                                          style: const TextStyle(
                                            fontSize: 15,
                                            fontWeight: FontWeight.w900,
                                            color: AppColors.brandDark,
                                          ),
                                        ),
                                        const Row(
                                          children: [
                                            Text(
                                              'Safarni ochish',
                                              style: TextStyle(
                                                fontSize: 12,
                                                fontWeight: FontWeight.bold,
                                                color: AppColors.brandDark,
                                              ),
                                            ),
                                            Icon(Icons.chevron_right, size: 16, color: AppColors.brandDark),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
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

  Widget _buildFilterChip(String label, String value) {
    final isSelected = _filter == value;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      selectedColor: AppColors.brandLime,
      backgroundColor: Colors.white,
      side: BorderSide(color: isSelected ? AppColors.brandDark : const Color(0xFFCBD5E1)),
      labelStyle: TextStyle(
        fontSize: 12,
        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
        color: AppColors.brandDark,
      ),
      onSelected: (selected) {
        if (selected) setState(() => _filter = value);
      },
    );
  }
}
