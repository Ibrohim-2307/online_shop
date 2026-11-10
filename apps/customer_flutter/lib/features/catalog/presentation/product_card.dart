import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/utils/formatters.dart';
import '../../../core/widgets/product_image.dart';
import '../../cart/data/cart_notifier.dart';
import '../domain/product_model.dart';
import 'product_detail_screen.dart';

const productGridDelegate = SliverGridDelegateWithFixedCrossAxisCount(
  crossAxisCount: 2,
  mainAxisExtent: 315,
  crossAxisSpacing: 12,
  mainAxisSpacing: 12,
);

class ProductCard extends ConsumerStatefulWidget {
  final ProductModel product;

  const ProductCard({super.key, required this.product});

  @override
  ConsumerState<ProductCard> createState() => _ProductCardState();
}

class _ProductCardState extends ConsumerState<ProductCard> {
  bool _isFavorite = false;

  int get _bulkDiscountPercent {
    if (widget.product.variants.isEmpty) return 0;
    final tiers = widget.product.variants.first.priceTiers;
    final base = widget.product.basePrice;
    if (tiers.isEmpty || base <= 0) return 0;
    final lowest = tiers.map((t) => t.unitPrice).reduce((a, b) => a < b ? a : b);
    return ((base - lowest) * 100 / base).floor();
  }

  int get _monthlyInstallment {
    final base = widget.product.basePrice;
    if (base <= 0) return 0;
    // 12-month installment estimate
    return (base / 12 * 1.15).round();
  }

  @override
  Widget build(BuildContext context) {
    final product = widget.product;
    final discount = _bulkDiscountPercent;
    final installment = _monthlyInstallment;
    final variant = product.variants.isNotEmpty ? product.variants.first : null;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE8ECE4)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => ProductDetailScreen(product: product)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Image Container with Badges
              Expanded(
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    ProductImage(url: product.imageUrl, iconSize: 42),

                    // Top Left: Bulk Discount / Tag
                    if (discount > 0 && product.tradeMode != 'retail_only')
                      Positioned(
                        left: 8,
                        top: 8,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3.5),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [AppColors.brandDark, AppColors.brandDeep],
                            ),
                            borderRadius: BorderRadius.circular(6),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.15),
                                blurRadius: 4,
                              ),
                            ],
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.bolt, size: 12, color: AppColors.brandLime),
                              const SizedBox(width: 2),
                              Text(
                                'Optomda −$discount%',
                                style: const TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w800,
                                  color: Colors.white,
                                ),
                              ),
                            ],
                          ),
                        ),
                      )
                    else if (product.tradeMode == 'both')
                      Positioned(
                        left: 8,
                        top: 8,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppColors.brandLime,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: const Text(
                            'Optom & Chakana',
                            style: TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.w800,
                              color: AppColors.brandDark,
                            ),
                          ),
                        ),
                      ),

                    // Top Right: Wishlist Heart
                    Positioned(
                      right: 8,
                      top: 8,
                      child: GestureDetector(
                        onTap: () {
                          setState(() => _isFavorite = !_isFavorite);
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(
                                _isFavorite ? 'Sevimlilarga qo\'shildi' : 'Sevimlilardan olib tashlandi',
                              ),
                              duration: const Duration(milliseconds: 900),
                            ),
                          );
                        },
                        child: Container(
                          width: 32,
                          height: 32,
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.9),
                            shape: BoxShape.circle,
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.08),
                                blurRadius: 4,
                              ),
                            ],
                          ),
                          child: Icon(
                            _isFavorite ? Icons.favorite : Icons.favorite_border,
                            size: 18,
                            color: _isFavorite ? AppColors.brandRose : AppColors.textSecondary,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              // Details section
              Padding(
                padding: const EdgeInsets.fromLTRB(10, 8, 10, 10),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Product Name
                    SizedBox(
                      height: 34,
                      child: Text(
                        product.name,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 13,
                          height: 1.25,
                          fontWeight: FontWeight.w700,
                          color: AppColors.textPrimary,
                        ),
                      ),
                    ),
                    const SizedBox(height: 5),

                    // Rating & Reviews
                    const Row(
                      children: [
                        Icon(Icons.star_rounded, size: 15, color: AppColors.brandGold),
                        SizedBox(width: 3),
                        Text(
                          '4.9',
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                        ),
                        SizedBox(width: 3),
                        Text(
                          '(48)',
                          style: TextStyle(fontSize: 11, color: AppColors.textMuted),
                        ),
                      ],
                    ),
                    const SizedBox(height: 5),

                    // Installment (Nasiya) Chip
                    if (installment > 0)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2.5),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFEF3C7),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.flash_on, size: 11, color: Color(0xFFB45309)),
                            const SizedBox(width: 2),
                            Text(
                              '${formatNumber(installment)} UZS / oy',
                              style: const TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.w700,
                                color: Color(0xFF92400E),
                              ),
                            ),
                          ],
                        ),
                      ),
                    const SizedBox(height: 6),

                    // Price and Quick Add to Cart row
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                '${formatNumber(product.basePrice)} UZS',
                                style: const TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.w900,
                                  color: AppColors.brandDark,
                                ),
                              ),
                              if (discount > 0)
                                Text(
                                  '${formatNumber((product.basePrice * 1.15).round())} UZS',
                                  style: const TextStyle(
                                    fontSize: 11,
                                    decoration: TextDecoration.lineThrough,
                                    color: AppColors.textMuted,
                                  ),
                                ),
                            ],
                          ),
                        ),

                        // Quick Add Button
                        GestureDetector(
                          onTap: () {
                            if (variant != null) {
                              ref.read(cartProvider.notifier).addItem(product, variant, product.minOrderQuantity);
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text('"${product.name}" savatchaga qo\'shildi'),
                                  backgroundColor: AppColors.brandDark,
                                  duration: const Duration(seconds: 1),
                                ),
                              );
                            }
                          },
                          child: Container(
                            width: 34,
                            height: 34,
                            decoration: BoxDecoration(
                              color: AppColors.brandLime,
                              borderRadius: BorderRadius.circular(10),
                              boxShadow: [
                                BoxShadow(
                                  color: AppColors.brandLime.withValues(alpha: 0.4),
                                  blurRadius: 6,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: const Icon(
                              Icons.add_shopping_cart,
                              size: 18,
                              color: AppColors.brandDark,
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
      ),
    );
  }
}
