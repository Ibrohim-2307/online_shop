import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/constants/app_colors.dart';
import '../../cart/data/cart_notifier.dart';
import '../domain/product_model.dart';

class ProductDetailScreen extends ConsumerStatefulWidget {
  final ProductModel product;
  const ProductDetailScreen({super.key, required this.product});

  @override
  ConsumerState<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends ConsumerState<ProductDetailScreen> {
  late ProductVariant _selectedVariant;
  late double _quantity;

  @override
  void initState() {
    super.initState();
    _selectedVariant = widget.product.variants.isNotEmpty
        ? widget.product.variants.first
        : ProductVariant(id: 0, sku: 'DEFAULT', name: widget.product.name, price: 0, priceTiers: []);
    _quantity = widget.product.minOrderQuantity;
  }

  void _increment() {
    setState(() {
      _quantity += widget.product.unitStep;
    });
  }

  void _decrement() {
    if (_quantity - widget.product.unitStep >= widget.product.minOrderQuantity) {
      setState(() {
        _quantity -= widget.product.unitStep;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final activeUnitPrice = _selectedVariant.getPriceForQuantity(_quantity);
    final totalSum = (activeUnitPrice * _quantity).round();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.background,
        elevation: 0,
        title: Text(
          widget.product.name,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: const TextStyle(
            color: AppColors.textPrimary,
            fontWeight: FontWeight.bold,
            fontSize: 16,
          ),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Image preview card
          Container(
            height: 200,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: const Center(
              child: Icon(
                Icons.inventory_2_outlined,
                size: 70,
                color: AppColors.brandDark,
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Title & Trade mode badges
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: AppColors.brandLime,
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  widget.product.tradeMode == 'wholesale_only'
                      ? 'Faqat Optom'
                      : widget.product.tradeMode == 'retail_only'
                          ? 'Chakana'
                          : 'Optom & Chakana',
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: AppColors.brandDark,
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Text(
                'Birlik: ${widget.product.unit}',
                style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
              ),
            ],
          ),
          const SizedBox(height: 8),

          Text(
            widget.product.name,
            style: const TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.w800,
              color: AppColors.textPrimary,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Sotuvchi: ${widget.product.sellerName ?? "Rasmiy diler"}',
            style: const TextStyle(color: AppColors.textSecondary, fontSize: 13),
          ),

          const SizedBox(height: 20),

          // Variant Selector
          if (widget.product.variants.length > 1) ...[
            const Text(
              'Variantni tanlang (SKU):',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
            ),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              children: widget.product.variants.map((v) {
                final isSelected = v.id == _selectedVariant.id;
                return ChoiceChip(
                  label: Text('${v.name} (${v.sku})'),
                  selected: isSelected,
                  selectedColor: AppColors.brandLime,
                  onSelected: (selected) {
                    if (selected) setState(() => _selectedVariant = v);
                  },
                );
              }).toList(),
            ),
            const SizedBox(height: 16),
          ],

          // Tiered Pricing Ladder Box
          if (_selectedVariant.priceTiers.isNotEmpty) ...[
            Container(
              padding: const EdgeInsets.all(14),
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
                      Icon(Icons.discount_outlined, size: 18, color: AppColors.brandDark),
                      SizedBox(width: 6),
                      Text(
                        'Hajm bo\'yicha optom narxlar jadvali:',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  Table(
                    border: TableBorder.all(color: const Color(0xFFF1F5F9)),
                    children: [
                      const TableRow(
                        decoration: BoxDecoration(color: Color(0xFFF8FAFC)),
                        children: [
                          Padding(
                            padding: EdgeInsets.all(8),
                            child: Text(
                              'Miqdor oralig\'i',
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11),
                            ),
                          ),
                          Padding(
                            padding: EdgeInsets.all(8),
                            child: Text(
                              'Dona narxi',
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11),
                            ),
                          ),
                        ],
                      ),
                      ..._selectedVariant.priceTiers.map((t) {
                        final isApplied = _quantity >= t.minQuantity &&
                            (t.maxQuantity == null || _quantity <= t.maxQuantity!);
                        return TableRow(
                          decoration: BoxDecoration(
                            color: isApplied ? AppColors.brandLime.withValues(alpha: 0.2) : Colors.transparent,
                          ),
                          children: [
                            Padding(
                              padding: const EdgeInsets.all(8),
                              child: Text(
                                t.maxQuantity != null
                                    ? '${t.minQuantity.toInt()} - ${t.maxQuantity!.toInt()} ${widget.product.unit}'
                                    : '${t.minQuantity.toInt()}+ ${widget.product.unit}',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: isApplied ? FontWeight.bold : FontWeight.normal,
                                ),
                              ),
                            ),
                            Padding(
                              padding: const EdgeInsets.all(8),
                              child: Text(
                                '${t.unitPrice} UZS',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: isApplied ? FontWeight.bold : FontWeight.normal,
                                  color: isApplied ? AppColors.brandDark : AppColors.textPrimary,
                                ),
                              ),
                            ),
                          ],
                        );
                      }),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
          ],

          // Interactive Quantity Counter
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Miqdor:',
                      style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                    ),
                    Text(
                      'Qadam: ${widget.product.unitStep.toInt()} ${widget.product.unit}',
                      style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                    ),
                  ],
                ),
                Row(
                  children: [
                    IconButton(
                      icon: const Icon(Icons.remove_circle_outline),
                      color: AppColors.brandDark,
                      onPressed: _decrement,
                    ),
                    Text(
                      '${_quantity.toInt()} ${widget.product.unit}',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                    ),
                    IconButton(
                      icon: const Icon(Icons.add_circle_outline),
                      color: AppColors.brandDark,
                      onPressed: _increment,
                    ),
                  ],
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),
        ],
      ),
      bottomNavigationBar: Container(
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
                  const Text('Jami hisob:', style: TextStyle(color: AppColors.textSecondary, fontSize: 12)),
                  Text(
                    '$totalSum UZS',
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w900,
                      color: AppColors.brandDark,
                    ),
                  ),
                  Text(
                    '($activeUnitPrice UZS / ${widget.product.unit})',
                    style: const TextStyle(fontSize: 10, color: AppColors.textSecondary),
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
                ref.read(cartProvider.notifier).addItem(
                      widget.product,
                      _selectedVariant,
                      _quantity,
                    );
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text('${widget.product.name} savatchaga qo\'shildi!'),
                    backgroundColor: AppColors.brandDark,
                    duration: const Duration(seconds: 2),
                  ),
                );
              },
              child: const Row(
                children: [
                  Icon(Icons.shopping_bag_outlined, size: 18),
                  SizedBox(width: 8),
                  Text('Savatchaga', style: TextStyle(fontWeight: FontWeight.bold)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
