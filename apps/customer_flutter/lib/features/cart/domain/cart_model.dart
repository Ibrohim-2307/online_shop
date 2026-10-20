import '../../catalog/domain/product_model.dart';

class CartItemModel {
  final ProductModel product;
  final ProductVariant variant;
  double quantity;
  int unitPrice;

  CartItemModel({
    required this.product,
    required this.variant,
    required this.quantity,
    required this.unitPrice,
  });

  int get subtotal => (unitPrice * quantity).round();

  void updateQuantity(double newQty) {
    quantity = newQty;
    unitPrice = variant.getPriceForQuantity(newQty);
  }
}

class CartState {
  final List<CartItemModel> items;
  final bool isOfflineDraft;
  final String? priceChangeNotice;

  CartState({
    required this.items,
    this.isOfflineDraft = false,
    this.priceChangeNotice,
  });

  int get grandTotal => items.fold(0, (sum, item) => sum + item.subtotal);
  int get totalItemCount => items.fold(0, (sum, item) => sum + item.quantity.toInt());

  CartState copyWith({
    List<CartItemModel>? items,
    bool? isOfflineDraft,
    String? priceChangeNotice,
  }) {
    return CartState(
      items: items ?? this.items,
      isOfflineDraft: isOfflineDraft ?? this.isOfflineDraft,
      priceChangeNotice: priceChangeNotice,
    );
  }
}
