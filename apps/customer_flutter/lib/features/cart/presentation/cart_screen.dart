import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/constants/app_colors.dart';
import '../data/cart_notifier.dart';
import 'checkout_screen.dart';

class CartScreen extends ConsumerWidget {
  const CartScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final cart = ref.watch(cartProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.background,
        elevation: 0,
        title: const Text(
          'Savatcha',
          style: TextStyle(
            color: AppColors.textPrimary,
            fontWeight: FontWeight.w800,
            fontSize: 20,
          ),
        ),
        actions: [
          if (cart.items.isNotEmpty)
            IconButton(
              icon: const Icon(Icons.delete_outline, color: AppColors.textSecondary),
              onPressed: () => ref.read(cartProvider.notifier).clearCart(),
            ),
        ],
      ),
      body: cart.items.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.shopping_cart_outlined, size: 70, color: AppColors.textSecondary.withValues(alpha: 0.5)),
                  const SizedBox(height: 16),
                  const Text(
                    'Savatchangiz bo\'sh',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Katalogdan mahsulotlarni tanlang va qo\'shing',
                    style: TextStyle(color: AppColors.textSecondary),
                  ),
                ],
              ),
            )
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                // Price change / tiered threshold alert notice
                if (cart.priceChangeNotice != null)
                  Container(
                    margin: const EdgeInsets.only(bottom: 16),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.brandLime.withValues(alpha: 0.3),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: AppColors.brandDark.withValues(alpha: 0.2)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.info_outline, color: AppColors.brandDark, size: 20),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            cart.priceChangeNotice!,
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: AppColors.brandDark,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),

                // Offline draft banner notice
                if (cart.isOfflineDraft)
                  Container(
                    margin: const EdgeInsets.only(bottom: 16),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF3C7),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFF59E0B)),
                    ),
                    child: const Row(
                      children: [
                        Icon(Icons.wifi_off_outlined, color: Color(0xFFD97706), size: 20),
                        SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            'Oflayn rejim: Savatcha qoralamasi qurilmada saqlanmoqda. Buyurtma berish uchun internetga ulaning.',
                            style: TextStyle(fontSize: 12, color: Color(0xFF92400E)),
                          ),
                        ),
                      ],
                    ),
                  ),

                // List of cart items
                ListView.separated(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: cart.items.length,
                  separatorBuilder: (context, index) => const SizedBox(height: 12),
                  itemBuilder: (context, i) {
                    final item = cart.items[i];
                    return Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                width: 50,
                                height: 50,
                                decoration: BoxDecoration(
                                  color: const Color(0xFFF8FAFC),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: const Icon(Icons.inventory_2_outlined, color: AppColors.brandDark),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      item.product.name,
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                    ),
                                    Text(
                                      item.variant.name,
                                      style: const TextStyle(color: AppColors.textSecondary, fontSize: 11),
                                    ),
                                    Text(
                                      '${item.unitPrice} UZS / ${item.product.unit}',
                                      style: const TextStyle(
                                        color: AppColors.brandDark,
                                        fontWeight: FontWeight.bold,
                                        fontSize: 12,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              IconButton(
                                icon: const Icon(Icons.close, size: 18, color: AppColors.textSecondary),
                                onPressed: () {
                                  ref.read(cartProvider.notifier).removeItem(item.variant.id);
                                },
                              ),
                            ],
                          ),
                          const Divider(height: 16),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Row(
                                children: [
                                  IconButton(
                                    icon: const Icon(Icons.remove_circle_outline, size: 20),
                                    color: AppColors.brandDark,
                                    onPressed: () {
                                      ref.read(cartProvider.notifier).updateQuantity(
                                            item.variant.id,
                                            item.quantity - item.product.unitStep,
                                          );
                                    },
                                  ),
                                  Text(
                                    '${item.quantity.toInt()} ${item.product.unit}',
                                    style: const TextStyle(fontWeight: FontWeight.bold),
                                  ),
                                  IconButton(
                                    icon: const Icon(Icons.add_circle_outline, size: 20),
                                    color: AppColors.brandDark,
                                    onPressed: () {
                                      ref.read(cartProvider.notifier).updateQuantity(
                                            item.variant.id,
                                            item.quantity + item.product.unitStep,
                                          );
                                    },
                                  ),
                                ],
                              ),
                              Text(
                                '${item.subtotal} UZS',
                                style: const TextStyle(
                                  fontWeight: FontWeight.w900,
                                  fontSize: 15,
                                  color: AppColors.brandDark,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    );
                  },
                ),
              ],
            ),
      bottomNavigationBar: cart.items.isEmpty
          ? null
          : Container(
              padding: const EdgeInsets.all(16),
              decoration: const BoxDecoration(
                color: Colors.white,
                border: Border(top: BorderSide(color: Color(0xFFE2E8F0))),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Jami (${cart.totalItemCount} dona):', style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
                        Text(
                          '${cart.grandTotal} UZS',
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w900,
                            color: AppColors.brandDark,
                          ),
                        ),
                      ],
                    ),
                  ),
                  ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.brandLime,
                      foregroundColor: AppColors.brandDark,
                      elevation: 0,
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const CheckoutScreen()),
                      );
                    },
                    child: const Text('Checkoutga o\'tish', style: TextStyle(fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            ),
    );
  }
}
