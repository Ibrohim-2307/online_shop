import 'package:flutter/material.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/utils/formatters.dart';
import '../../../core/widgets/product_image.dart';
import '../domain/product_model.dart';
import 'product_detail_screen.dart';

const productGridDelegate = SliverGridDelegateWithFixedCrossAxisCount(
  crossAxisCount: 2,
  mainAxisExtent: 268,
  crossAxisSpacing: 12,
  mainAxisSpacing: 12,
);

class ProductCard extends StatelessWidget {
  final ProductModel product;

  const ProductCard({super.key, required this.product});

  /// Biggest saving a bulk buyer gets, taken from the real price tiers.
  int get _bulkDiscountPercent {
    final tiers = product.variants.first.priceTiers;
    final base = product.basePrice;
    if (tiers.isEmpty || base <= 0) return 0;
    final lowest = tiers.map((t) => t.unitPrice).reduce((a, b) => a < b ? a : b);
    return ((base - lowest) * 100 / base).floor();
  }

  @override
  Widget build(BuildContext context) {
    final discount = _bulkDiscountPercent;

    return Material(
      color: AppColors.surface,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: const BorderSide(color: AppColors.border),
      ),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => ProductDetailScreen(product: product)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(
              child: Stack(
                fit: StackFit.expand,
                children: [
                  ProductImage(url: product.imageUrl, iconSize: 44),
                  if (discount > 0 && product.tradeMode != 'retail_only')
                    Positioned(
                      left: 8,
                      top: 8,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppColors.brandLime,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          'Optomda −$discount%',
                          style: const TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w800,
                            color: AppColors.brandDark,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(11, 10, 11, 11),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text.rich(
                    TextSpan(
                      children: [
                        TextSpan(
                          text: formatNumber(product.basePrice),
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
                        ),
                        const TextSpan(
                          text: ' UZS',
                          style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700),
                        ),
                      ],
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(color: AppColors.brandDark),
                  ),
                  const SizedBox(height: 4),
                  SizedBox(
                    height: 34,
                    child: Text(
                      product.name,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 12.5,
                        height: 1.3,
                        fontWeight: FontWeight.w600,
                        color: AppColors.textPrimary,
                      ),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '1 ${unitLabel(product.unit)} · ${product.sellerName ?? "Rasmiy do'kon"}',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
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

/// Grey shapes in the place of cards while products load.
class ProductGridSkeleton extends StatefulWidget {
  final bool scrollable;

  const ProductGridSkeleton({super.key, this.scrollable = false});

  @override
  State<ProductGridSkeleton> createState() => _ProductGridSkeletonState();
}

class _ProductGridSkeletonState extends State<ProductGridSkeleton> with SingleTickerProviderStateMixin {
  late final AnimationController _controller = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 900),
    lowerBound: 0.45,
  )..repeat(reverse: true);

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return FadeTransition(
      opacity: _controller,
      child: GridView.builder(
        shrinkWrap: !widget.scrollable,
        physics: widget.scrollable ? null : const NeverScrollableScrollPhysics(),
        padding: widget.scrollable ? const EdgeInsets.all(16) : EdgeInsets.zero,
        gridDelegate: productGridDelegate,
        itemCount: 6,
        itemBuilder: (context, index) => DecoratedBox(
          decoration: BoxDecoration(
            color: AppColors.surfaceMuted,
            borderRadius: BorderRadius.circular(16),
          ),
        ),
      ),
    );
  }
}
