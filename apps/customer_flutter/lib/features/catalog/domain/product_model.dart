class PriceTier {
  final double minQuantity;
  final double? maxQuantity;
  final int unitPrice;

  PriceTier({
    required this.minQuantity,
    this.maxQuantity,
    required this.unitPrice,
  });

  factory PriceTier.fromJson(Map<String, dynamic> json) {
    return PriceTier(
      minQuantity: (json['min_quantity'] as num).toDouble(),
      maxQuantity: json['max_quantity'] != null ? (json['max_quantity'] as num).toDouble() : null,
      unitPrice: json['unit_price'] as int,
    );
  }
}

class ProductVariant {
  final int id;
  final String sku;
  final String name;
  final int price;
  final List<PriceTier> priceTiers;

  ProductVariant({
    required this.id,
    required this.sku,
    required this.name,
    required this.price,
    required this.priceTiers,
  });

  factory ProductVariant.fromJson(Map<String, dynamic> json) {
    final rawTiers = json['price_tiers'] as List<dynamic>? ?? [];
    return ProductVariant(
      id: json['id'] as int,
      sku: json['sku'] as String,
      name: json['name'] as String,
      price: json['price'] as int,
      priceTiers: rawTiers.map((t) => PriceTier.fromJson(t as Map<String, dynamic>)).toList(),
    );
  }

  /// Calculates quote based on tiered all-units pricing rule
  int getPriceForQuantity(double quantity) {
    for (final tier in priceTiers) {
      if (quantity >= tier.minQuantity) {
        if (tier.maxQuantity == null || quantity <= tier.maxQuantity!) {
          return tier.unitPrice;
        }
      }
    }
    return price;
  }
}

class ProductModel {
  final int id;
  final String name;
  final String slug;
  final String unit;
  final double minOrderQuantity;
  final double unitStep;
  final String tradeMode; // retail_only, wholesale_only, both
  final String? sellerName;
  final List<ProductVariant> variants;

  ProductModel({
    required this.id,
    required this.name,
    required this.slug,
    required this.unit,
    required this.minOrderQuantity,
    required this.unitStep,
    required this.tradeMode,
    this.sellerName,
    required this.variants,
  });

  factory ProductModel.fromJson(Map<String, dynamic> json) {
    final rawVariants = json['variants'] as List<dynamic>? ?? [];
    return ProductModel(
      id: json['id'] as int,
      name: json['name'] as String,
      slug: json['slug'] as String,
      unit: json['unit'] as String? ?? 'piece',
      minOrderQuantity: (json['min_order_quantity'] as num?)?.toDouble() ?? 1.0,
      unitStep: (json['unit_step'] as num?)?.toDouble() ?? 1.0,
      tradeMode: json['trade_mode'] as String? ?? 'both',
      sellerName: json['seller']?['name'] as String?,
      variants: rawVariants.map((v) => ProductVariant.fromJson(v as Map<String, dynamic>)).toList(),
    );
  }

  int get basePrice => variants.isNotEmpty ? variants.first.price : 0;
}

class CategoryModel {
  final int id;
  final String name;
  final String slug;

  CategoryModel({required this.id, required this.name, required this.slug});

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    return CategoryModel(
      id: json['id'] as int,
      name: json['name'] as String,
      slug: json['slug'] as String,
    );
  }
}
