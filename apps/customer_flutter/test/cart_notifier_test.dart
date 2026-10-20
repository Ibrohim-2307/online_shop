import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:customer_flutter/features/cart/data/cart_notifier.dart';
import 'package:customer_flutter/features/catalog/domain/product_model.dart';

void main() {
  group('Customer Flutter CartNotifier Tests', () {
    test('Adding product variant computes initial subtotal and total', () {
      final container = ProviderContainer();
      addTearDown(container.dispose);

      final variant = ProductVariant(
        id: 10,
        sku: 'GISHT-10',
        name: 'Pishgan gisht',
        price: 1500,
        priceTiers: [
          PriceTier(minQuantity: 1.0, maxQuantity: 999.0, unitPrice: 1500),
          PriceTier(minQuantity: 1000.0, maxQuantity: null, unitPrice: 1200),
        ],
      );

      final product = ProductModel(
        id: 1,
        name: 'Gisht',
        slug: 'gisht',
        unit: 'piece',
        minOrderQuantity: 100.0,
        unitStep: 50.0,
        tradeMode: 'both',
        variants: [variant],
      );

      final notifier = container.read(cartProvider.notifier);
      notifier.addItem(product, variant, 200.0);

      final state1 = container.read(cartProvider);
      expect(state1.items.length, equals(1));
      expect(state1.grandTotal, equals(300000)); // 200 * 1500

      // Increase to 1000 units -> triggers wholesale tier 1200 UZS
      notifier.updateQuantity(10, 1000.0);

      final state2 = container.read(cartProvider);
      expect(state2.grandTotal, equals(1200000)); // 1000 * 1200
      expect(state2.priceChangeNotice, contains('Katta hajm uchun chegirmali narx'));
    });
  });
}
