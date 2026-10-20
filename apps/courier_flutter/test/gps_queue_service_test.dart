import 'package:flutter_test/flutter_test.dart';
import 'package:courier_flutter/features/tracking/data/gps_queue_service.dart';

void main() {
  group('Courier Flutter GpsQueueService Tests', () {
    late GpsQueueService service;

    setUp(() {
      service = GpsQueueService();
      service.clearQueue();
    });

    test('Enqueueing location appends point to bounded queue', () {
      expect(service.queuedCount, equals(0));

      service.enqueueLocation(
        shipmentId: 101,
        lat: 41.311158,
        lng: 69.279737,
        accuracy: 4.5,
        heading: 90.0,
        speed: 15.0,
      );

      expect(service.queuedCount, equals(1));
    });

    test('Bounded queue prevents overflow and evicts oldest items', () {
      // Enqueue 505 items into bounded queue of max 500
      for (int i = 0; i < 505; i++) {
        service.enqueueLocation(
          shipmentId: 101,
          lat: 41.0 + (i * 0.0001),
          lng: 69.0 + (i * 0.0001),
          accuracy: 5.0,
          heading: 0.0,
          speed: 10.0,
        );
      }

      // Max size is capped at 500
      expect(service.queuedCount, equals(500));
    });

    test('Stopping tracking updates state to stopped', () {
      service.startTracking(101);
      expect(service.currentState, equals(GpsTransmissionState.activeTransmitting));

      service.stopTracking();
      expect(service.currentState, equals(GpsTransmissionState.stopped));
    });
  });
}
