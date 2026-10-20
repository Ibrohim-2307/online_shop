class DeliveryModel {
  final int id;
  final String trackingNumber;
  final String recipientName;
  final String phone;
  final String address;
  final String status; // assigned, picked_up, in_transit, delayed, delivered, failed
  final String? delayReason;
  final int itemsCount;
  final int totalAmount;

  DeliveryModel({
    required this.id,
    required this.trackingNumber,
    required this.recipientName,
    required this.phone,
    required this.address,
    required this.status,
    this.delayReason,
    required this.itemsCount,
    required this.totalAmount,
  });

  factory DeliveryModel.fromJson(Map<String, dynamic> json) {
    final suborder = json['suborder'] as Map<String, dynamic>? ?? {};
    final order = suborder['order'] as Map<String, dynamic>? ?? {};
    final shippingAddr = (order['shipping_address'] as Map<String, dynamic>?) ?? {};

    return DeliveryModel(
      id: json['id'] as int,
      trackingNumber: json['tracking_number'] as String? ?? 'TRK-UNKNOWN',
      recipientName: shippingAddr['recipient_name'] as String? ?? 'Noma\'lum xaridor',
      phone: shippingAddr['phone'] as String? ?? '+998900000000',
      address: shippingAddr['address'] as String? ?? 'Yetkazish manzili ko\'rsatilmagan',
      status: json['status'] as String? ?? 'assigned',
      delayReason: json['delay_reason'] as String?,
      itemsCount: (suborder['items'] as List<dynamic>?)?.length ?? 1,
      totalAmount: (suborder['total_amount'] as num?)?.toInt() ?? 0,
    );
  }

  bool get isActive => ['assigned', 'picked_up', 'in_transit', 'delayed'].contains(status);
  bool get isDelivered => status == 'delivered';
}
