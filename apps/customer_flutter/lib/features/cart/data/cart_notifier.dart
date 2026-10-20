import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../catalog/domain/product_model.dart';
import '../domain/cart_model.dart';

final cartProvider = NotifierProvider<CartNotifier, CartState>(() {
  return CartNotifier();
});

class CartNotifier extends Notifier<CartState> {
  @override
  CartState build() {
    return CartState(items: []);
  }

  void addItem(ProductModel product, ProductVariant variant, double quantity) {
    final existingIndex = state.items.indexWhere((item) => item.variant.id == variant.id);

    final currentItems = List<CartItemModel>.from(state.items);

    if (existingIndex >= 0) {
      final item = currentItems[existingIndex];
      final oldPrice = item.unitPrice;
      final newQuantity = item.quantity + quantity;
      item.updateQuantity(newQuantity);

      String? notice;
      if (item.unitPrice != oldPrice) {
        notice = "${variant.name} uchun optom chegirma qo'llandi! Yangi narx: ${item.unitPrice} UZS";
      }

      state = state.copyWith(items: currentItems, priceChangeNotice: notice);
    } else {
      final unitPrice = variant.getPriceForQuantity(quantity);
      currentItems.add(
        CartItemModel(
          product: product,
          variant: variant,
          quantity: quantity,
          unitPrice: unitPrice,
        ),
      );
      state = state.copyWith(items: currentItems, priceChangeNotice: null);
    }
  }

  void updateQuantity(int variantId, double quantity) {
    if (quantity <= 0) {
      removeItem(variantId);
      return;
    }

    final currentItems = List<CartItemModel>.from(state.items);
    final index = currentItems.indexWhere((item) => item.variant.id == variantId);

    if (index >= 0) {
      final item = currentItems[index];
      final oldPrice = item.unitPrice;
      item.updateQuantity(quantity);

      String? notice;
      if (item.unitPrice < oldPrice) {
        notice = "Katta hajm uchun chegirmali narx: ${item.unitPrice} UZS!";
      } else if (item.unitPrice > oldPrice) {
        notice = "Miqdor kamaygani sababli narx yangilandi: ${item.unitPrice} UZS";
      }

      state = state.copyWith(items: currentItems, priceChangeNotice: notice);
    }
  }

  void removeItem(int variantId) {
    final updated = state.items.where((item) => item.variant.id != variantId).toList();
    state = state.copyWith(items: updated);
  }

  void clearCart() {
    state = CartState(items: []);
  }

  void markOfflineDraft(bool isOffline) {
    state = state.copyWith(isOfflineDraft: isOffline);
  }
}
