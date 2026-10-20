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
  List<dynamic> _orders = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchOrders();
  }

  Future<void> _fetchOrders() async {
    try {
      final res = await ApiClient().get('/orders');
      if (mounted) {
        setState(() {
          _orders = (res is Map<String, dynamic> && res.containsKey('data'))
              ? res['data'] as List<dynamic>
              : (res is List ? res : []);
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.background,
        elevation: 0,
        title: const Text(
          'Buyurtmalarim',
          style: TextStyle(
            color: AppColors.textPrimary,
            fontWeight: FontWeight.w800,
            fontSize: 20,
          ),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.brandDark))
          : _orders.isEmpty
              ? const Center(
                  child: Text(
                    'Hozircha buyurtmalar yo\'q',
                    style: TextStyle(color: AppColors.textSecondary),
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _fetchOrders,
                  color: AppColors.brandDark,
                  child: ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: _orders.length,
                    separatorBuilder: (context, index) => const SizedBox(height: 12),
                    itemBuilder: (context, i) {
                      final order = _orders[i] as Map<String, dynamic>;
                      final orderNumber = order['order_number'] ?? 'ORD';
                      final status = order['status'] ?? 'pending';
                      final grandTotal = order['grand_total'] ?? 0;
                      final suborders = (order['suborders'] as List<dynamic>?) ?? [];

                      return Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  orderNumber,
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: status == 'completed'
                                        ? AppColors.statusSuccess.withValues(alpha: 0.2)
                                        : AppColors.brandLime,
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text(
                                    status.toString().toUpperCase(),
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 11,
                                      color: AppColors.brandDark,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text(
                              'Summa: $grandTotal UZS',
                              style: const TextStyle(
                                fontWeight: FontWeight.w800,
                                fontSize: 16,
                                color: AppColors.brandDark,
                              ),
                            ),
                            const Divider(height: 16),
                            ...suborders.map((sub) {
                              final shipments = (sub['shipments'] as List<dynamic>?) ?? [];
                              return Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Do\'kon: ${sub['seller']?['name'] ?? "Sotuvchi"}',
                                    style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
                                  ),
                                  if (shipments.isNotEmpty)
                                    Padding(
                                      padding: const EdgeInsets.only(top: 6),
                                      child: OutlinedButton.icon(
                                        style: OutlinedButton.styleFrom(
                                          foregroundColor: AppColors.brandDark,
                                          side: const BorderSide(color: AppColors.brandDark),
                                        ),
                                        icon: const Icon(Icons.location_on_outlined, size: 16),
                                        label: const Text('Yetkazishni kuzatish (GPS)', style: TextStyle(fontSize: 12)),
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
                                ],
                              );
                            }),
                          ],
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}
