import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/utils/formatters.dart';
import '../../../../core/widgets/empty_state.dart';
import '../../../../core/widgets/product_image.dart';
import '../../auth/auth_modal.dart';
import '../../auth/auth_notifier.dart';
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
          ? const EmptyState(
              icon: Icons.shopping_cart_outlined,
              title: "Savatchangiz bo'sh",
              subtitle: "Katalogdan mahsulotlarni tanlang va qo'shing",
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
                    final notifier = ref.read(cartProvider.notifier);
                    Widget step(IconData icon, double delta) => InkWell(
                          customBorder: const CircleBorder(),
                          onTap: () => notifier.updateQuantity(item.variant.id, item.quantity + delta),
                          child: SizedBox(width: 36, height: 36, child: Icon(icon, size: 18, color: AppColors.brandDark)),
                        );

                    // Swipe left to take the product out of the cart
                    return Dismissible(
                      key: ValueKey('cart-${item.product.id}-${item.variant.id}'),
                      direction: DismissDirection.endToStart,
                      onDismissed: (_) => notifier.removeItem(item.variant.id),
                      background: Container(
                        alignment: Alignment.centerRight,
                        padding: const EdgeInsets.only(right: 24),
                        decoration: BoxDecoration(
                          color: AppColors.statusError.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(22),
                        ),
                        child: const Icon(Icons.delete_outline_rounded, color: AppColors.statusError),
                      ),
                      child: Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(22),
                          boxShadow: [
                            BoxShadow(
                              color: AppColors.brandDark.withValues(alpha: 0.05),
                              blurRadius: 16,
                              offset: const Offset(0, 6),
                            ),
                          ],
                        ),
                        child: Row(
                          children: [
                            ClipRRect(
                              borderRadius: BorderRadius.circular(16),
                              child: SizedBox(
                                width: 92,
                                height: 92,
                                child: ProductImage(url: item.product.imageUrl, iconSize: 30),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Expanded(
                                        child: Text(
                                          item.product.name,
                                          maxLines: 2,
                                          overflow: TextOverflow.ellipsis,
                                          style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13, height: 1.25),
                                        ),
                                      ),
                                      InkWell(
                                        customBorder: const CircleBorder(),
                                        onTap: () => notifier.removeItem(item.variant.id),
                                        child: const Padding(
                                          padding: EdgeInsets.all(4),
                                          child: Icon(Icons.close_rounded, size: 18, color: AppColors.textMuted),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    '${formatMoney(item.unitPrice)} / ${unitLabel(item.product.unit)}',
                                    style: const TextStyle(color: AppColors.textSecondary, fontSize: 11.5),
                                  ),
                                  const SizedBox(height: 8),
                                  Row(
                                    children: [
                                      Container(
                                        decoration: BoxDecoration(
                                          color: AppColors.surfaceMuted,
                                          borderRadius: BorderRadius.circular(18),
                                        ),
                                        child: Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            step(Icons.remove_rounded, -item.product.unitStep),
                                            Text(
                                              '${formatQuantity(item.quantity)} ${unitLabel(item.product.unit)}',
                                              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12.5),
                                            ),
                                            step(Icons.add_rounded, item.product.unitStep),
                                          ],
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: FittedBox(
                                          fit: BoxFit.scaleDown,
                                          alignment: Alignment.centerRight,
                                          child: Text(
                                            formatMoney(item.subtotal),
                                            style: const TextStyle(
                                              fontWeight: FontWeight.w900,
                                              fontSize: 15,
                                              letterSpacing: -0.2,
                                              color: AppColors.textPrimary,
                                            ),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
              ],
            ),
      bottomNavigationBar: cart.items.isEmpty
          ? null
          : Container(
              padding: const EdgeInsets.fromLTRB(20, 14, 16, 14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: const BorderRadius.vertical(top: Radius.circular(26)),
                boxShadow: [
                  BoxShadow(color: AppColors.brandDark.withValues(alpha: 0.08), blurRadius: 24, offset: const Offset(0, -6)),
                ],
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
                          formatMoney(cart.grandTotal),
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
                      backgroundColor: AppColors.brandDark,
                      foregroundColor: Colors.white,
                      elevation: 0,
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                      shape: const StadiumBorder(),
                    ),
                    onPressed: () async {
                      final auth = ref.read(authProvider);
                      if (!auth.isAuthenticated) {
                        final ok = await showAuthModal(
                          context,
                          title: "Xaridni rasmiylashtirish",
                          subtitle: "Buyurtma berish uchun tizimga kiring yoki ro'yxatdan o'ting. Savatchangiz to'liq saqlanadi.",
                        );
                        if (ok != true || !context.mounted) return;
                      }

                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const CheckoutScreen()),
                      );
                    },
                    child: const Text('Rasmiylashtirish', style: TextStyle(fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            ),
    );
  }
}
