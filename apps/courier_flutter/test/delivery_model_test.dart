import 'package:flutter_test/flutter_test.dart';
import 'package:courier_flutter/features/deliveries/domain/delivery_model.dart';

void main() {
  group('Courier DeliveryModel Tests', () {
    test('Parses JSON correctly and evaluates active status', () {
      final json = {
        'id': 15,
        'tracking_number': 'TRK-20261020-099',
        'status': 'in_transit',
        'suborder': {
          'items': [{'id': 1}, {'id': 2}],
          'total_amount': 250000,
          'order': {
            'shipping_address': {
              'recipient_name': 'Vali Karimov',
              'phone': '+998901234567',
              'address': 'Chilonzor 1-mavze',
            },
          },
        },
      };

      final delivery = DeliveryModel.fromJson(json);

      expect(delivery.id, equals(15));
      expect(delivery.trackingNumber, equals('TRK-20261020-099'));
      expect(delivery.recipientName, equals('Vali Karimov'));
      expect(delivery.isActive, isTrue);
      expect(delivery.isDelivered, isFalse);
      expect(delivery.itemsCount, equals(2));
      expect(delivery.totalAmount, equals(250000));
    });

    test('Evaluates delivered status', () {
      final delivery = DeliveryModel(
        id: 20,
        trackingNumber: 'TRK-DONE',
        recipientName: 'Ali',
        phone: '+998901112233',
        address: 'Shayxontohur',
        status: 'delivered',
        itemsCount: 1,
        totalAmount: 50000,
      );

      expect(delivery.isActive, isFalse);
      expect(delivery.isDelivered, isTrue);
    });
  });
}
