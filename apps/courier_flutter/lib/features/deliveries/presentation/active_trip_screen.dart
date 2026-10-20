import 'package:flutter/material.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/network/api_client.dart';
import '../../tracking/data/gps_queue_service.dart';
import '../domain/delivery_model.dart';

class ActiveTripScreen extends StatefulWidget {
  final DeliveryModel delivery;
  const ActiveTripScreen({super.key, required this.delivery});

  @override
  State<ActiveTripScreen> createState() => _ActiveTripScreenState();
}

class _ActiveTripScreenState extends State<ActiveTripScreen> {
  final GpsQueueService _gpsService = GpsQueueService();
  late String _currentStatus;
  bool _isActionLoading = false;

  @override
  void initState() {
    super.initState();
    _currentStatus = widget.delivery.status;

    // Automatically ensure GPS tracking is on during active delivery
    if (widget.delivery.isActive && !widget.delivery.isDelivered) {
      _gpsService.startTracking(widget.delivery.id);
    }
  }

  Future<void> _updateStatus(String status, {String? delayReason}) async {
    setState(() => _isActionLoading = true);
    final payload = <String, dynamic>{'status': status};
    if (delayReason != null) {
      payload['delay_reason'] = delayReason;
    }
    try {
      await CourierApiClient().post('/logistics/shipments/${widget.delivery.id}/status', payload);

      setState(() {
        _currentStatus = status;
        _isActionLoading = false;
      });

      if (status == 'delivered') {
        _gpsService.stopTracking();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Yetkazish muvaffaqiyatli yakunlandi!'), backgroundColor: AppColors.statusSuccess),
          );
          Navigator.pop(context);
        }
      }
    } catch (_) {
      // Offline fallback state change
      setState(() {
        _currentStatus = status;
        _isActionLoading = false;
      });
      if (status == 'delivered') {
        _gpsService.stopTracking();
        if (mounted) Navigator.pop(context);
      }
    }
  }

  void _showDelayDialog() {
    final reasonCtrl = TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Kechikish sababini ko\'rsating'),
        content: TextField(
          controller: reasonCtrl,
          maxLines: 2,
          decoration: const InputDecoration(
            hintText: 'Masalan: Tirbandlik, ob-havo, mashina buzilishi...',
            border: OutlineInputBorder(),
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Bekor qilish')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.brandDark, foregroundColor: Colors.white),
            onPressed: () {
              if (reasonCtrl.text.trim().isNotEmpty) {
                Navigator.pop(ctx);
                _updateStatus('delayed', delayReason: reasonCtrl.text.trim());
              }
            },
            child: const Text('Tasdiqlash'),
          ),
        ],
      ),
    );
  }

  void _showOtpCompletionDialog() {
    final otpCtrl = TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.verified_user_outlined, color: AppColors.brandDark),
            SizedBox(width: 8),
            Text('Topshirish kodi (OTP)'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Xaridor telefoniga yuborilgan 4 xonali tasdiqlash kodini kiriting:',
              style: TextStyle(fontSize: 13, color: AppColors.textSecondary),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: otpCtrl,
              keyboardType: TextInputType.number,
              maxLength: 4,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, letterSpacing: 8),
              decoration: const InputDecoration(
                hintText: '••••',
                counterText: '',
                border: OutlineInputBorder(),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Bekor qilish')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.brandLime,
              foregroundColor: AppColors.brandDark,
            ),
            onPressed: () {
              if (otpCtrl.text.trim().length >= 4) {
                Navigator.pop(ctx);
                _updateStatus('delivered');
              }
            },
            child: const Text('Topshirishni yakunlash', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.background,
        elevation: 0,
        title: Text(
          widget.delivery.trackingNumber,
          style: const TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.bold, fontSize: 16),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // GPS live transmission indicator
          StreamBuilder<GpsTransmissionState>(
            stream: _gpsService.stateStream,
            initialData: _gpsService.currentState,
            builder: (context, snapshot) {
              final state = snapshot.data ?? GpsTransmissionState.idle;
              Color bg;
              String text;
              IconData icon;

              switch (state) {
                case GpsTransmissionState.activeTransmitting:
                  bg = AppColors.statusSuccess;
                  text = 'GPS: Serverga jonli uzatilmoqda';
                  icon = Icons.gps_fixed;
                  break;
                case GpsTransmissionState.queuedOffline:
                  bg = Colors.amber.shade800;
                  text = 'GPS: Oflayn navbatda (${_gpsService.queuedCount} ta nuqta)';
                  icon = Icons.wifi_off;
                  break;
                case GpsTransmissionState.stopped:
                  bg = Colors.grey;
                  text = 'GPS: Safar yakunlangan, uzatish to\'xtatildi';
                  icon = Icons.gps_off;
                  break;
                case GpsTransmissionState.idle:
                  bg = AppColors.brandDark;
                  text = 'GPS: Kutilmoqda';
                  icon = Icons.location_searching;
                  break;
              }

              return Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  color: bg.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: bg.withValues(alpha: 0.4)),
                ),
                child: Row(
                  children: [
                    Icon(icon, color: bg, size: 20),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        text,
                        style: TextStyle(fontWeight: FontWeight.bold, color: bg, fontSize: 13),
                      ),
                    ),
                  ],
                ),
              );
            },
          ),

          const SizedBox(height: 16),

          // Recipient Card
          Container(
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
                    const Text('Xaridor ma\'lumotlari:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppColors.brandLime,
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        _currentStatus.toUpperCase(),
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: AppColors.brandDark),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Text(widget.delivery.recipientName, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
                const SizedBox(height: 4),
                Text(widget.delivery.phone, style: const TextStyle(color: AppColors.textSecondary)),
                const SizedBox(height: 8),
                Row(
                  children: [
                    const Icon(Icons.location_on, color: AppColors.brandDark, size: 18),
                    const SizedBox(width: 6),
                    Expanded(
                      child: Text(
                        widget.delivery.address,
                        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          const SizedBox(height: 20),

          // Trip Step Actions
          const Text('Safar Bosqichlari:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
          const SizedBox(height: 10),

          // Action 1: Qabul qilib olish (picked_up)
          ElevatedButton.icon(
            style: ElevatedButton.styleFrom(
              backgroundColor: _currentStatus == 'assigned' ? AppColors.brandDark : Colors.white,
              foregroundColor: _currentStatus == 'assigned' ? Colors.white : AppColors.brandDark,
              side: const BorderSide(color: AppColors.brandDark),
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            icon: const Icon(Icons.inventory_outlined),
            label: const Text('Tovarni ombordan qabul qilish'),
            onPressed: (_currentStatus == 'assigned' && !_isActionLoading)
                ? () => _updateStatus('picked_up')
                : null,
          ),

          const SizedBox(height: 10),

          // Action 2: Yo'lga chiqish (in_transit)
          ElevatedButton.icon(
            style: ElevatedButton.styleFrom(
              backgroundColor: _currentStatus == 'picked_up' ? AppColors.brandDark : Colors.white,
              foregroundColor: _currentStatus == 'picked_up' ? Colors.white : AppColors.brandDark,
              side: const BorderSide(color: AppColors.brandDark),
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            icon: const Icon(Icons.navigation_outlined),
            label: const Text('Yo\'lga chiqish (Aktiv harakat)'),
            onPressed: ((_currentStatus == 'picked_up' || _currentStatus == 'delayed') && !_isActionLoading)
                ? () => _updateStatus('in_transit')
                : null,
          ),

          const SizedBox(height: 10),

          // Action 3: Kechikishni qayd etish (delayed)
          OutlinedButton.icon(
            style: OutlinedButton.styleFrom(
              foregroundColor: Colors.red.shade800,
              side: BorderSide(color: Colors.red.shade400),
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            icon: const Icon(Icons.timer_outlined),
            label: const Text('Kechikish sababini yuborish'),
            onPressed: (_currentStatus == 'in_transit' && !_isActionLoading) ? _showDelayDialog : null,
          ),

          const SizedBox(height: 24),

          // Action 4: Topshirish va OTP (delivered)
          ElevatedButton.icon(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.brandLime,
              foregroundColor: AppColors.brandDark,
              elevation: 0,
              padding: const EdgeInsets.symmetric(vertical: 16),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            icon: const Icon(Icons.check_circle_outline, size: 22),
            label: const Text(
              'Xaridorga topshirish (OTP tasdiq)',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            ),
            onPressed: (_currentStatus != 'delivered' && !_isActionLoading)
                ? _showOtpCompletionDialog
                : null,
          ),
        ],
      ),
    );
  }
}
