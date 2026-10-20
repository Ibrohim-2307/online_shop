import 'package:flutter/material.dart';
import '../../../core/constants/app_colors.dart';
import '../../health/presentation/courier_health_screen.dart';
import '../../tracking/data/gps_queue_service.dart';

class CourierProfileScreen extends StatelessWidget {
  const CourierProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final gpsService = GpsQueueService();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.background,
        elevation: 0,
        title: const Text(
          'Kuryer Profili',
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
          // Courier profile card
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Row(
              children: [
                CircleAvatar(
                  radius: 30,
                  backgroundColor: AppColors.brandDark,
                  child: const Text(
                    'BK',
                    style: TextStyle(color: AppColors.brandLime, fontWeight: FontWeight.bold, fontSize: 18),
                  ),
                ),
                const SizedBox(width: 16),
                const Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Botir Kuryer',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                    ),
                    SizedBox(height: 4),
                    Text(
                      '+998 90 987 65 43',
                      style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                    ),
                    Text(
                      'Smena holati: Safarda (Aktiv)',
                      style: TextStyle(color: AppColors.statusSuccess, fontSize: 11, fontWeight: FontWeight.w600),
                    ),
                  ],
                ),
              ],
            ),
          ),

          const SizedBox(height: 20),

          // Menu actions
          Material(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
            child: Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                children: [
                  ListTile(
                    leading: const Icon(Icons.gps_fixed, color: AppColors.brandDark),
                    title: const Text('GPS Navbatini tozalash'),
                    subtitle: Text('Joriy navbat: ${gpsService.queuedCount} ta nuqta', style: const TextStyle(fontSize: 11)),
                    trailing: const Icon(Icons.refresh),
                    onTap: () {
                      gpsService.clearQueue();
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('GPS navbati tozalandi')),
                      );
                    },
                  ),
                  const Divider(height: 1),
                  ListTile(
                    leading: const Icon(Icons.dns_outlined, color: AppColors.brandDark),
                    title: const Text('Logistika Serveri Diagnostikasi'),
                    subtitle: const Text('API va MySQL ulanish tekshiruvi', style: TextStyle(fontSize: 11)),
                    trailing: const Icon(Icons.chevron_right),
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const CourierHealthScreen()),
                      );
                    },
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
