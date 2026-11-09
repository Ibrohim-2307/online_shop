import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/network/api_client.dart';
import '../data/cart_notifier.dart';

class CheckoutScreen extends ConsumerStatefulWidget {
  final String? initialPaymentMethod;
  final int? initialNasiyaMonths;

  const CheckoutScreen({
    super.key,
    this.initialPaymentMethod,
    this.initialNasiyaMonths,
  });

  @override
  ConsumerState<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends ConsumerState<CheckoutScreen> {
  final _nameCtrl = TextEditingController(text: 'Aziz Xaridor');
  final _phoneCtrl = TextEditingController(text: '+998901234567');
  final _addressCtrl = TextEditingController(text: 'Toshkent sh., Chilonzor 9-mavze');
  final _notesCtrl = TextEditingController();

  late String _selectedPaymentMethod; // cash_on_delivery, nasiya, card
  late int _selectedNasiyaMonths; // 3, 6, 12

  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    _selectedPaymentMethod = widget.initialPaymentMethod ?? 'cash_on_delivery';
    _selectedNasiyaMonths = widget.initialNasiyaMonths ?? 6;
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
    _addressCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  String _formatCurrency(num amount) {
    final parts = amount.round().toString().split('');
    final buffer = StringBuffer();
    for (int i = 0; i < parts.length; i++) {
      if (i > 0 && (parts.length - i) % 3 == 0) {
        buffer.write(' ');
      }
      buffer.write(parts[i]);
    }
    return '${buffer.toString()} UZS';
  }

  Future<void> _submitOrder() async {
    final cart = ref.read(cartProvider);
    if (cart.items.isEmpty) return;

    if (_nameCtrl.text.trim().isEmpty || _phoneCtrl.text.trim().isEmpty || _addressCtrl.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Iltimos, ism, telefon va yetkazish manzilini to\'liq kiriting.'),
          backgroundColor: AppColors.statusError,
        ),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    final payload = {
      'shipping_address': {
        'recipient_name': _nameCtrl.text.trim(),
        'phone': _phoneCtrl.text.trim(),
        'region': 'Toshkent',
        'city': 'Toshkent shahri',
        'address_line': _addressCtrl.text.trim(),
      },
      'payment_method': _selectedPaymentMethod,
      if (_selectedPaymentMethod == 'nasiya') 'nasiya_months': _selectedNasiyaMonths,
      'notes': _notesCtrl.text.trim(),
      'items': cart.items.map((item) => {
        'variant_id': item.variant.id,
        'quantity': item.quantity,
      }).toList(),
    };

    try {
      final res = await ApiClient().post('/checkout', payload);
      final orderNumber = (res is Map<String, dynamic>)
          ? (res['order_number'] ?? 'ORD-SUCCESS')
          : 'ORD-SUCCESS';

      ref.read(cartProvider.notifier).clearCart();

      if (mounted) {
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (_) => AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            title: const Row(
              children: [
                Icon(Icons.check_circle, color: AppColors.statusSuccess, size: 28),
                SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Buyurtma qabul qilindi!',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17),
                  ),
                ),
              ],
            ),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Buyurtma raqami: $orderNumber',
                  style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: AppColors.brandDark),
                ),
                const SizedBox(height: 8),
                if (_selectedPaymentMethod == 'nasiya')
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF3C7),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: const Color(0xFFFDE68A)),
                    ),
                    child: Text(
                      'Muddatli to\'lov rasmiylashtirildi: $_selectedNasiyaMonths oyga (${_formatCurrency(cart.grandTotal / _selectedNasiyaMonths)} / oy)',
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF92400E)),
                    ),
                  )
                else
                  const Text(
                    'To\'lov usuli: Naqd pul (kuryer yetkazganda to\'lanadi)',
                    style: TextStyle(fontSize: 13, color: AppColors.textSecondary),
                  ),
                const SizedBox(height: 8),
                const Text(
                  'Kuryerimiz tez orada siz bilan bog\'lanadi va tovarlarni yetkazib beradi.',
                  style: TextStyle(fontSize: 13),
                ),
              ],
            ),
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
          SnackBar(
            content: Text('Xatolik: ${e.toString().replaceAll('Exception: ', '')}'),
            backgroundColor: AppColors.statusError,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  Widget _buildPaymentOption({
    required String id,
    required String title,
    required String subtitle,
    required IconData icon,
    Widget? extra,
  }) {
    final isSelected = _selectedPaymentMethod == id;
    return InkWell(
      onTap: () => setState(() => _selectedPaymentMethod = id),
      borderRadius: BorderRadius.circular(12),
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFFF0FDF4) : Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isSelected ? AppColors.brandDark : const Color(0xFFE2E8F0),
            width: isSelected ? 1.5 : 1,
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(
                  isSelected ? Icons.radio_button_checked : Icons.radio_button_off,
                  color: isSelected ? AppColors.brandDark : AppColors.textSecondary,
                  size: 20,
                ),
                const SizedBox(width: 10),
                Icon(icon, color: AppColors.brandDark, size: 22),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                      ),
                      Text(
                        subtitle,
                        style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            if (extra != null && isSelected) ...[
              const SizedBox(height: 12),
              extra,
            ],
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final cart = ref.watch(cartProvider);
    final monthlyNasiya = cart.grandTotal > 0 ? (cart.grandTotal / _selectedNasiyaMonths).round() : 0;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
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
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Icon(Icons.location_on_outlined, color: AppColors.brandDark),
                    SizedBox(width: 8),
                    Text(
                      'Yetkazish manzili va xaridor',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                TextField(
                  controller: _nameCtrl,
                  decoration: const InputDecoration(
                    labelText: 'Qabul qiluvchi ismi',
                    border: OutlineInputBorder(),
                    contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _phoneCtrl,
                  keyboardType: TextInputType.phone,
                  decoration: const InputDecoration(
                    labelText: 'Telefon raqam',
                    border: OutlineInputBorder(),
                    contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _addressCtrl,
                  maxLines: 2,
                  decoration: const InputDecoration(
                    labelText: 'Aniq yetkazish manzili (shahar, ko\'cha, uy/kvartira)',
                    border: OutlineInputBorder(),
                    contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _notesCtrl,
                  decoration: const InputDecoration(
                    labelText: 'Yetkazish bo\'yicha kuryerga izoh (ixtiyoriy)',
                    border: OutlineInputBorder(),
                    contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 16),

          // Payment Methods Section
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
                const Row(
                  children: [
                    Icon(Icons.payment_outlined, color: AppColors.brandDark),
                    SizedBox(width: 8),
                    Text(
                      'To\'lov usulini tanlang',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                    ),
                  ],
                ),
                const SizedBox(height: 14),

                // Cash on Delivery
                _buildPaymentOption(
                  id: 'cash_on_delivery',
                  title: 'Naqd pul / Qabul qilinganda',
                  subtitle: 'Tovarni qo\'lingizga olgandan keyin kuryerga to\'laysiz',
                  icon: Icons.payments_outlined,
                ),

                // Nasiya / Installment
                _buildPaymentOption(
                  id: 'nasiya',
                  title: 'Muddatli to\'lov (Nasiya)',
                  subtitle: '0% boshlang\'ich to\'lov, teng oylik to\'lovlar',
                  icon: Icons.bolt,
                  extra: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF3C7).withValues(alpha: 0.5),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFFDE68A)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Muddatni tanlang:',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                        ),
                        const SizedBox(height: 8),
                        Row(
                          children: [3, 6, 12].map((months) {
                            final isCur = _selectedNasiyaMonths == months;
                            final monthly = cart.grandTotal > 0 ? (cart.grandTotal / months).round() : 0;
                            return Expanded(
                              child: InkWell(
                                onTap: () => setState(() => _selectedNasiyaMonths = months),
                                child: Container(
                                  margin: const EdgeInsets.symmetric(horizontal: 4),
                                  padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
                                  decoration: BoxDecoration(
                                    color: isCur ? AppColors.brandDark : Colors.white,
                                    borderRadius: BorderRadius.circular(8),
                                    border: Border.all(
                                      color: isCur ? AppColors.brandDark : const Color(0xFFCBD5E1),
                                    ),
                                  ),
                                  child: Column(
                                    children: [
                                      Text(
                                        months == 6 ? '6 oy (XIT)' : '$months oy',
                                        style: TextStyle(
                                          fontWeight: FontWeight.bold,
                                          fontSize: 11,
                                          color: isCur ? AppColors.brandLime : AppColors.textPrimary,
                                        ),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        '${_formatCurrency(monthly).replaceAll(' UZS', '')}/oy',
                                        style: TextStyle(
                                          fontSize: 10,
                                          fontWeight: isCur ? FontWeight.w600 : FontWeight.normal,
                                          color: isCur ? Colors.white : AppColors.textSecondary,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            );
                          }).toList(),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Oylik to\'lov: ${_formatCurrency(monthlyNasiya)} / oy',
                          style: const TextStyle(
                            fontWeight: FontWeight.w800,
                            fontSize: 13,
                            color: Color(0xFF92400E),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),

                // Online Card (Click / Payme)
                _buildPaymentOption(
                  id: 'card',
                  title: 'Onlayn karta (Click / Payme)',
                  subtitle: 'Uzcard, Humo, Visa yoki Mastercard orqali tezkor to\'lov',
                  icon: Icons.credit_card_outlined,
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
              borderRadius: BorderRadius.circular(14),
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
                            _formatCurrency(item.subtotal),
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
                      _formatCurrency(cart.grandTotal),
                      style: const TextStyle(
                        fontWeight: FontWeight.w900,
                        fontSize: 18,
                        color: AppColors.brandDark,
                      ),
                    ),
                  ],
                ),
                if (_selectedPaymentMethod == 'nasiya')
                  Padding(
                    padding: const EdgeInsets.only(top: 6),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Nasiya ($_selectedNasiyaMonths oy):',
                          style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Color(0xFFB45309)),
                        ),
                        Text(
                          '${_formatCurrency(monthlyNasiya)} / oy',
                          style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 14, color: Color(0xFFB45309)),
                        ),
                      ],
                    ),
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
                : Text(
                    _selectedPaymentMethod == 'nasiya'
                        ? 'Nasiyaga buyurtma berish'
                        : 'Buyurtmani tasdiqlash',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                  ),
          ),
        ],
      ),
    );
  }
}
