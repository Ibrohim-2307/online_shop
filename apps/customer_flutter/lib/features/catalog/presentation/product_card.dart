import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/utils/formatters.dart';
import '../../../core/widgets/motion.dart';
import '../../../core/widgets/product_image.dart';
import '../../cart/data/cart_notifier.dart';
import '../domain/product_model.dart';
import 'product_detail_screen.dart';

const productGridDelegate = SliverGridDelegateWithFixedCrossAxisCount(
  crossAxisCount: 2,
  mainAxisExtent: 292,
  crossAxisSpacing: 12,
  mainAxisSpacing: 12,
);

/// Largest real saving a buyer gets from the seller's bulk price tiers, in percent.
int bulkDiscountPercent(ProductModel product) {
  if (product.variants.isEmpty) return 0;
  final tiers = product.variants.first.priceTiers;
  final base = product.basePrice;
  if (tiers.isEmpty || base <= 0) return 0;
  final lowest = tiers.map((t) => t.unitPrice).reduce((a, b) => a < b ? a : b);
  return ((base - lowest) * 100 / base).floor();
}

int lowestTierPrice(ProductModel product) {
  final tiers = product.variants.isEmpty ? const <PriceTier>[] : product.variants.first.priceTiers;
  if (tiers.isEmpty) return product.basePrice;
  return tiers.map((t) => t.unitPrice).reduce((a, b) => a < b ? a : b);
}

class ProductCard extends ConsumerStatefulWidget {
  final ProductModel product;

  /// Keeps the photo's fly-to-detail animation unique when one product is shown in two lists.
  final String heroPrefix;

  const ProductCard({super.key, required this.product, this.heroPrefix = 'grid'});

  @override
  ConsumerState<ProductCard> createState() => _ProductCardState();
}

class _ProductCardState extends ConsumerState<ProductCard> {
  bool _isFavorite = false;

  String get _heroTag => '${widget.heroPrefix}-product-${widget.product.id}';

  void _open() {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => ProductDetailScreen(product: widget.product, heroTag: _heroTag),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final product = widget.product;
    final discount = product.tradeMode == 'retail_only' ? 0 : bulkDiscountPercent(product);
    final variant = product.variants.isNotEmpty ? product.variants.first : null;

    final inCart = variant == null
        ? 0.0
        : ref.watch(cartProvider.select((cart) {
            for (final item in cart.items) {
              if (item.variant.id == variant.id && item.product.id == product.id) return item.quantity;
            }
            return 0.0;
          }));

    return Pressable(
      scale: 0.975,
      onTap: _open,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(22),
          boxShadow: [
            BoxShadow(
              color: AppColors.brandDark.withValues(alpha: 0.06),
              blurRadius: 18,
              offset: const Offset(0, 8),
            ),
          ],
        ),
        padding: const EdgeInsets.all(6),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(
              child: ClipRRect(
                borderRadius: BorderRadius.circular(17),
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    Hero(tag: _heroTag, child: ProductImage(url: product.imageUrl, iconSize: 40)),
                    if (discount > 0)
                      Positioned(
                        left: 8,
                        top: 8,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppColors.brandLime,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            'Optomda −$discount%',
                            style: const TextStyle(
                              fontSize: 10.5,
                              fontWeight: FontWeight.w800,
                              color: AppColors.brandDark,
                            ),
                          ),
                        ),
                      ),
                    Positioned(
                      right: 6,
                      top: 6,
                      child: Pressable(
                        scale: 0.85,
                        haptic: true,
                        onTap: () => setState(() => _isFavorite = !_isFavorite),
                        child: Container(
                          width: 34,
                          height: 34,
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.92),
                            shape: BoxShape.circle,
                          ),
                          child: AnimatedSwitcher(
                            duration: const Duration(milliseconds: 220),
                            transitionBuilder: (child, anim) => ScaleTransition(scale: anim, child: child),
                            child: Icon(
                              _isFavorite ? Icons.favorite_rounded : Icons.favorite_border_rounded,
                              key: ValueKey(_isFavorite),
                              size: 18,
                              color: _isFavorite ? AppColors.brandRose : AppColors.textSecondary,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(8, 10, 6, 6),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if ((product.sellerName ?? '').isNotEmpty)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 3),
                      child: Text(
                        product.sellerName!,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w600, color: AppColors.textMuted),
                      ),
                    ),
                  SizedBox(
                    height: 34,
                    child: Text(
                      product.name,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 13,
                        height: 1.28,
                        fontWeight: FontWeight.w600,
                        color: AppColors.textPrimary,
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            FittedBox(
                              fit: BoxFit.scaleDown,
                              alignment: Alignment.centerLeft,
                              child: Text.rich(
                                TextSpan(
                                  text: formatNumber(product.basePrice),
                                  style: const TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w900,
                                    letterSpacing: -0.3,
                                    color: AppColors.textPrimary,
                                  ),
                                  children: const [
                                    TextSpan(
                                      text: ' UZS',
                                      style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: AppColors.textSecondary),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                            const SizedBox(height: 1),
                            Text(
                              discount > 0
                                  ? 'optom ${formatNumber(lowestTierPrice(product))} dan'
                                  : '1 ${unitLabel(product.unit)} uchun',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                fontSize: 10.5,
                                fontWeight: FontWeight.w600,
                                color: discount > 0 ? AppColors.brandEmerald : AppColors.textMuted,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 6),
                      if (variant != null) _CartControl(product: product, variant: variant, quantity: inCart),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// "+" while the product is not in the cart; a − n + stepper once it is.
class _CartControl extends ConsumerWidget {
  final ProductModel product;
  final ProductVariant variant;
  final double quantity;

  const _CartControl({required this.product, required this.variant, required this.quantity});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final cart = ref.read(cartProvider.notifier);
    final inCart = quantity > 0;

    void change(double next) {
      HapticFeedback.selectionClick();
      // Below the seller's minimum the product leaves the cart
      cart.updateQuantity(variant.id, next < product.minOrderQuantity ? 0 : next);
    }

    return AnimatedSize(
      duration: const Duration(milliseconds: 200),
      curve: Curves.easeOutCubic,
      alignment: Alignment.centerRight,
      child: inCart
          ? Container(
              height: 36,
              decoration: BoxDecoration(color: AppColors.brandDark, borderRadius: BorderRadius.circular(18)),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  _StepButton(icon: Icons.remove_rounded, onTap: () => change(quantity - product.unitStep)),
                  ConstrainedBox(
                    constraints: const BoxConstraints(minWidth: 18),
                    child: Text(
                      formatQuantity(quantity),
                      textAlign: TextAlign.center,
                      style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w800),
                    ),
                  ),
                  _StepButton(icon: Icons.add_rounded, onTap: () => change(quantity + product.unitStep)),
                ],
              ),
            )
          : Pressable(
              scale: 0.88,
              onTap: () {
                HapticFeedback.lightImpact();
                cart.addItem(product, variant, product.minOrderQuantity);
              },
              child: Container(
                width: 36,
                height: 36,
                decoration: const BoxDecoration(color: AppColors.brandDark, shape: BoxShape.circle),
                child: const Icon(Icons.add_rounded, size: 22, color: AppColors.brandLime),
              ),
            ),
    );
  }
}

class _StepButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;
  const _StepButton({required this.icon, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      behavior: HitTestBehavior.opaque,
      onTap: onTap,
      child: SizedBox(width: 32, height: 36, child: Icon(icon, size: 18, color: AppColors.brandLime)),
    );
  }
}

/// Placeholder grid shown while products load.
class ProductGridSkeleton extends StatelessWidget {
  final int count;
  const ProductGridSkeleton({super.key, this.count = 4});

  @override
  Widget build(BuildContext context) {
    return Shimmer(
      child: GridView.builder(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        padding: EdgeInsets.zero,
        gridDelegate: productGridDelegate,
        itemCount: count,
        itemBuilder: (context, i) => const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(child: Bone(width: double.infinity, radius: 20)),
            SizedBox(height: 10),
            Bone(width: 120, height: 12),
            SizedBox(height: 6),
            Bone(width: 80, height: 12),
            SizedBox(height: 12),
            Bone(width: 100, height: 18),
            SizedBox(height: 6),
          ],
        ),
      ),
    );
  }
}
