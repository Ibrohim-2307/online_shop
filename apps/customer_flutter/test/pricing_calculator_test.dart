import 'package:flutter_test/flutter_test.dart';
import 'package:customer_flutter/features/catalog/domain/product_model.dart';

void main() {
  group('Customer Flutter Tiered Pricing Tests', () {
    test('Calculates base price when below minimum tier threshold', () {
      final variant = ProductVariant(
        id: 1,
        sku: 'SEM-M500',
        name: 'Sement M500',
        price: 300000,
        priceTiers: [
          PriceTier(minQuantity: 1.0, maxQuantity: 299.0, unitPrice: 300000),
          PriceTier(minQuantity: 300.0, maxQuantity: 999.0, unitPrice: 270000),
          PriceTier(minQuantity: 1000.0, maxQuantity: null, unitPrice: 250000),
        ],
      );

      expect(variant.getPriceForQuantity(1.0), equals(300000));
      expect(variant.getPriceForQuantity(299.0), equals(300000));
    });

    test('Recalculates tiered discount dynamically at 300 units', () {
      final variant = ProductVariant(
        id: 1,
        sku: 'SEM-M500',
        name: 'Sement M500',
        price: 300000,
        priceTiers: [
          PriceTier(minQuantity: 1.0, maxQuantity: 299.0, unitPrice: 300000),
          PriceTier(minQuantity: 300.0, maxQuantity: 999.0, unitPrice: 270000),
          PriceTier(minQuantity: 1000.0, maxQuantity: null, unitPrice: 250000),
        ],
      );

      expect(variant.getPriceForQuantity(300.0), equals(270000));
      expect(variant.getPriceForQuantity(999.0), equals(270000));
    });

    test('Applies deepest wholesale discount at 1000+ units', () {
      final variant = ProductVariant(
        id: 1,
        sku: 'SEM-M500',
        name: 'Sement M500',
        price: 300000,
        priceTiers: [
          PriceTier(minQuantity: 1.0, maxQuantity: 299.0, unitPrice: 300000),
          PriceTier(minQuantity: 300.0, maxQuantity: 999.0, unitPrice: 270000),
          PriceTier(minQuantity: 1000.0, maxQuantity: null, unitPrice: 250000),
        ],
      );

      expect(variant.getPriceForQuantity(1000.0), equals(250000));
      expect(variant.getPriceForQuantity(5000.0), equals(250000));
    });
  });
}
