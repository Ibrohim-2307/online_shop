import 'dart:async';
import 'package:flutter/material.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/network/api_client.dart';

class TrackingScreen extends StatefulWidget {
  final String trackingNumber;
  const TrackingScreen({super.key, required this.trackingNumber});

  @override
  State<TrackingScreen> createState() => _TrackingScreenState();
}

class _TrackingScreenState extends State<TrackingScreen> {
  Timer? _pollTimer;
  Map<String, dynamic>? _trackingData;
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _fetchTracking();
    // Poll every 12 seconds while screen is open
    _pollTimer = Timer.periodic(const Duration(seconds: 12), (_) => _fetchTracking(silent: true));
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    super.dispose();
  }

  Future<void> _fetchTracking({bool silent = false}) async {
    if (!silent) setState(() => _isLoading = true);
    try {
      final res = await ApiClient().get('/tracking/${widget.trackingNumber}');
      if (mounted) {
        setState(() {
          _trackingData = res as Map<String, dynamic>;
          _isLoading = false;
          _errorMessage = null;
        });

        // If delivered or cancelled, stop polling
        if (_trackingData?['tracking_closed'] == true) {
          _pollTimer?.cancel();
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = e.toString();
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final status = _trackingData?['status'] ?? 'Kutilmoqda';
    final latestLoc = _trackingData?['latest_location'] as Map<String, dynamic>?;
    final isStale = latestLoc?['is_stale'] == true;
    final delayReason = _trackingData?['delay_reason'] as String?;
    final timeline = (_trackingData?['timeline'] as List<dynamic>?) ?? [];

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.background,
        elevation: 0,
        title: Text(
          'Yetkazish #${widget.trackingNumber}',
          style: const TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.bold, fontSize: 16),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.brandDark))
          : _errorMessage != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.error_outline, size: 50, color: AppColors.statusError),
                        const SizedBox(height: 12),
                        Text(_errorMessage!, textAlign: TextAlign.center),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: () => _fetchTracking(),
                          child: const Text('Qayta urinish'),
                        ),
                      ],
                    ),
                  ),
                )
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    // Status Header Card
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
                              const Text('Holat:', style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: status == 'delivered'
                                      ? AppColors.statusSuccess.withValues(alpha: 0.15)
                                      : AppColors.brandLime,
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  status.toUpperCase(),
                                  style: const TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 12,
                                    color: AppColors.brandDark,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Kuryer: ${_trackingData?['courier_name'] ?? "Biriktirilmoqda"}',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                          ),
                          if (delayReason != null) ...[
                            const SizedBox(height: 8),
                            Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFEF2F2),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                'Kechikish sababi: $delayReason',
                                style: const TextStyle(color: Color(0xFF991B1B), fontSize: 12),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),

                    const SizedBox(height: 16),

                    // Live GPS Coordinates Card
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
                              const Row(
                                children: [
                                  Icon(Icons.gps_fixed, color: AppColors.brandDark, size: 18),
                                  SizedBox(width: 6),
                                  Text('Jonli GPS Ko\'rsatkich:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                ],
                              ),
                              if (isStale)
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: Colors.amber.shade100,
                                    borderRadius: BorderRadius.circular(4),
                                  ),
                                  child: const Text('Signal eskirgan', style: TextStyle(fontSize: 10, color: Colors.brown)),
                                ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          if (latestLoc != null) ...[
                            Text('Kenglik (Lat): ${latestLoc['latitude']}'),
                            Text('Uzunlik (Lng): ${latestLoc['longitude']}'),
                            Text('Tezlik: ${latestLoc['speed'] ?? 0} km/soat'),
                            Text('Vaqt: ${latestLoc['recorded_at']}', style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                          ] else
                            const Text(
                              'Hozircha kuryerdan GPS signali kelmadi.',
                              style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                            ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 16),

                    // Delivery Timeline
                    const Text('Harakatlar Tarixi (Timeline):', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                    const SizedBox(height: 8),

                    if (timeline.isEmpty)
                      const Text('Tarix mavjud emas', style: TextStyle(color: AppColors.textSecondary))
                    else
                      ...timeline.map((ev) => Container(
                            margin: const EdgeInsets.only(bottom: 8),
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(color: const Color(0xFFF1F5F9)),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.check_circle_outline, color: AppColors.brandDark, size: 18),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    ev['notes'] ?? ev['event_type'],
                                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500),
                                  ),
                                ),
                              ],
                            ),
                          )),
                  ],
                ),
    );
  }
}
