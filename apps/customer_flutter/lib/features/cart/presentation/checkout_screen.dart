import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/network/api_client.dart';
import '../data/cart_notifier.dart';

class CheckoutScreen extends ConsumerStatefulWidget {
  const CheckoutScreen({super.key});

  @override
  ConsumerState<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends ConsumerState<CheckoutScreen> {
  final _nameCtrl = TextEditingController(text: 'Aziz Xaridor');
  final _phoneCtrl = TextEditingController(text: '+998901234567');
  final _addressCtrl = TextEditingController(text: 'Toshkent sh., Chilonzor 9-mavze');
  final _notesCtrl = TextEditingController();

  bool _isSubmitting = false;

  Future<void> _submitOrder() async {
    final cart = ref.read(cartProvider);
    if (cart.items.isEmpty) return;

    setState(() => _isSubmitting = true);

    final payload = {
      'recipient_name': _nameCtrl.text.trim(),
      'phone': _phoneCtrl.text.trim(),
      'address': _addressCtrl.text.trim(),
      'notes': _notesCtrl.text.trim(),
      'items': cart.items.map((item) => {
        'product_variant_id': item.variant.id,
        'quantity': item.quantity,
      }).toList(),
    };

    try {
      final res = await ApiClient().post('/checkout', payload);
      final orderNumber = res['order_number'] ?? 'ORD-SUCCESS';

      ref.read(cartProvider.notifier).clearCart();

      if (mounted) {
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (_) => AlertDialog(
            title: const Row(
              children: [
                Icon(Icons.check_circle, color: AppColors.statusSuccess),
                SizedBox(width: 8),
                Text('Buyurtma qabul qilindi!'),
              ],
            ),
            content: Text('Buyurtma raqami: $orderNumber\nSiz bilan kuryer tez orada bog\'lanadi.'),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.pop(context); // dialog
                  Navigator.pop(context); // checkout screen
                },
                child: const Text('Bosh sahifaga qaytish'),
              ),
            ],
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Xatolik: ${e.toString()}'), backgroundColor: AppColors.statusError),
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = ref.watch(cartProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.background,
        elevation: 0,
        title: const Text(
          'Buyurtmani rasmiylashtirish',
          style: TextStyle(
            color: AppColors.textPrimary,
            fontWeight: FontWeight.w800,
            fontSize: 18,
          ),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Recipient Address Form
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Icon(Icons.local_shipping_outlined, color: AppColors.brandDark),
                    SizedBox(width: 8),
                    Text(
                      'Yetkazish manzili va ma\'lumotlar',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _nameCtrl,
                  decoration: const InputDecoration(
                    labelText: 'Qabul qiluvchi ismi',
                    border: OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _phoneCtrl,
                  keyboardType: TextInputType.phone,
                  decoration: const InputDecoration(
                    labelText: 'Telefon raqam',
                    border: OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _addressCtrl,
                  maxLines: 2,
                  decoration: const InputDecoration(
                    labelText: 'Aniq manzil',
                    border: OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _notesCtrl,
                  decoration: const InputDecoration(
                    labelText: 'Yetkazish bo\'yicha izoh (ixtiyoriy)',
                    border: OutlineInputBorder(),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 16),

          // Order summary
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Buyurtma xulosasi:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                const SizedBox(height: 12),
                ...cart.items.map((item) => Padding(
                      padding: const EdgeInsets.symmetric(vertical: 4),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              '${item.product.name} (${item.quantity.toInt()} ${item.product.unit})',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(fontSize: 13),
                            ),
                          ),
                          Text(
                            '${item.subtotal} UZS',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                        ],
                      ),
                    )),
                const Divider(height: 20),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Jami to\'lov:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                    Text(
                      '${cart.grandTotal} UZS',
                      style: const TextStyle(
                        fontWeight: FontWeight.w900,
                        fontSize: 18,
                        color: AppColors.brandDark,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),

          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.brandLime,
              foregroundColor: AppColors.brandDark,
              elevation: 0,
              padding: const EdgeInsets.symmetric(vertical: 16),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            onPressed: _isSubmitting ? null : _submitOrder,
            child: _isSubmitting
                ? const SizedBox(
                    height: 20,
                    width: 20,
                    child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.brandDark),
                  )
                : const Text(
                    'Buyurtmani tasdiqlash',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                  ),
          ),
        ],
      ),
    );
  }
}
