import 'dart:async';
import '../../../core/network/api_client.dart';

class GpsPoint {
  final String eventId;
  final int shipmentId;
  final double latitude;
  final double longitude;
  final double accuracy;
  final double heading;
  final double speed;
  final DateTime recordedAt;

  GpsPoint({
    required this.eventId,
    required this.shipmentId,
    required this.latitude,
    required this.longitude,
    required this.accuracy,
    required this.heading,
    required this.speed,
    required this.recordedAt,
  });

  Map<String, dynamic> toJson() => {
        'event_id': eventId,
        'shipment_id': shipmentId,
        'latitude': latitude,
        'longitude': longitude,
        'accuracy': accuracy,
        'heading': heading,
        'speed': speed,
        'recorded_at': recordedAt.toIso8601String(),
      };
}

enum GpsTransmissionState { idle, activeTransmitting, queuedOffline, stopped }

class GpsQueueService {
  static final GpsQueueService _instance = GpsQueueService._internal();
  factory GpsQueueService() => _instance;
  GpsQueueService._internal();

  final List<GpsPoint> _queue = [];
  final int _maxQueueSize = 500;
  Timer? _flushTimer;
  Timer? _simulationGpsTimer;

  int? _activeShipmentId;
  bool _isTrackingActive = false;

  GpsTransmissionState _state = GpsTransmissionState.idle;
  final _stateController = StreamController<GpsTransmissionState>.broadcast();

  Stream<GpsTransmissionState> get stateStream => _stateController.stream;
  GpsTransmissionState get currentState => _state;
  int get queuedCount => _queue.length;

  void _setState(GpsTransmissionState newState) {
    _state = newState;
    _stateController.add(newState);
  }

  /// Start tracking for an assigned shipment
  void startTracking(int shipmentId, {double initialLat = 41.311158, double initialLng = 69.279737}) {
    _activeShipmentId = shipmentId;
    _isTrackingActive = true;
    _setState(GpsTransmissionState.activeTransmitting);

    // Initial simulated location ping
    enqueueLocation(
      shipmentId: shipmentId,
      lat: initialLat,
      lng: initialLng,
      accuracy: 5.0,
      heading: 45.0,
      speed: 15.0,
    );

    // Periodic flush timer every 10 seconds
    _flushTimer?.cancel();
    _flushTimer = Timer.periodic(const Duration(seconds: 10), (_) => flushQueue());

    // Simulated device GPS movement every 5 seconds (moving along road)
    double curLat = initialLat;
    double curLng = initialLng;
    _simulationGpsTimer?.cancel();
    _simulationGpsTimer = Timer.periodic(const Duration(seconds: 5), (timer) {
      if (!_isTrackingActive || _activeShipmentId == null) {
        timer.cancel();
        return;
      }
      curLat += 0.0002;
      curLng += 0.0003;
      enqueueLocation(
        shipmentId: _activeShipmentId!,
        lat: curLat,
        lng: curLng,
        accuracy: 4.0,
        heading: 45.0,
        speed: 25.0,
      );
    });
  }

  /// Stop tracking immediately (upon delivery completion or logout)
  void stopTracking() {
    _isTrackingActive = false;
    _activeShipmentId = null;
    _flushTimer?.cancel();
    _simulationGpsTimer?.cancel();
    _setState(GpsTransmissionState.stopped);
  }

  /// Enqueue a location point into bounded queue
  void enqueueLocation({
    required int shipmentId,
    required double lat,
    required double lng,
    required double accuracy,
    required double heading,
    required double speed,
    DateTime? recordedAt,
  }) {
    // If bounded queue is full, evict oldest element
    if (_queue.length >= _maxQueueSize) {
      _queue.removeAt(0);
    }

    final point = GpsPoint(
      eventId: 'evt_dev_${DateTime.now().microsecondsSinceEpoch}',
      shipmentId: shipmentId,
      latitude: lat,
      longitude: lng,
      accuracy: accuracy,
      heading: heading,
      speed: speed,
      recordedAt: recordedAt ?? DateTime.now(),
    );

    _queue.add(point);
  }

  /// Send queued points to server
  Future<bool> flushQueue() async {
    if (_queue.isEmpty) return true;

    // Drain snapshot of current queue (FIFO)
    final pointsToSend = List<GpsPoint>.from(_queue.take(25));

    try {
      final payload = {
        'locations': pointsToSend.map((p) => p.toJson()).toList(),
      };

      await CourierApiClient().post('/courier/location/batch', payload);

      // On successful receipt, remove sent points
      _queue.removeRange(0, pointsToSend.length);
      _setState(GpsTransmissionState.activeTransmitting);
      return true;
    } catch (_) {
      // Network is offline or request failed: retain in bounded queue for next retry
      _setState(GpsTransmissionState.queuedOffline);
      return false;
    }
  }

  void clearQueue() {
    _queue.clear();
  }
}
