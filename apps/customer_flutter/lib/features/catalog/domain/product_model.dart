import 'package:flutter/material.dart';
import 'category_visual.dart';

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
      minQuantity: (json['min_quantity'] as num?)?.toDouble() ?? 1.0,
      maxQuantity: json['max_quantity'] != null ? (json['max_quantity'] as num).toDouble() : null,
      unitPrice: (json['unit_price'] as num?)?.toInt() ?? 0,
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
    final tiers = rawTiers.map((t) => PriceTier.fromJson(t as Map<String, dynamic>)).toList();
    
    int resolvedPrice = 0;
    if (json['price'] != null) {
      resolvedPrice = (json['price'] as num).toInt();
    } else if (tiers.isNotEmpty) {
      resolvedPrice = tiers.first.unitPrice;
    }

    return ProductVariant(
      id: json['id'] as int? ?? 0,
      sku: json['sku'] as String? ?? 'SKU',
      name: json['name'] as String? ?? 'Standart',
      price: resolvedPrice,
      priceTiers: tiers,
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
  final String? imageUrl;
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
    this.imageUrl,
    required this.variants,
  });

  factory ProductModel.fromJson(Map<String, dynamic> json) {
    final rawVariants = json['variants'] as List<dynamic>? ?? [];
    final parsedVariants = rawVariants.map((v) => ProductVariant.fromJson(v as Map<String, dynamic>)).toList();

    return ProductModel(
      id: json['id'] as int? ?? 0,
      name: json['name'] as String? ?? 'Mahsulot',
      slug: json['slug'] as String? ?? '',
      unit: json['unit'] as String? ?? 'piece',
      minOrderQuantity: (json['min_order_quantity'] as num?)?.toDouble() ?? 1.0,
      unitStep: (json['unit_step'] as num?)?.toDouble() ?? 1.0,
      tradeMode: json['trade_mode'] as String? ?? 'both',
      sellerName: json['seller']?['name'] as String?,
      imageUrl: json['image_url'] as String?,
      variants: parsedVariants.isNotEmpty
          ? parsedVariants
          : [
              ProductVariant(
                id: 0,
                sku: 'DEFAULT',
                name: json['name'] as String? ?? 'Standart',
                price: 0,
                priceTiers: [],
              ),
            ],
    );
  }

  int get basePrice {
    if (variants.isEmpty) return 0;
    return variants.first.price;
  }
}

class CategoryModel {
  final int id;
  final String name;
  final String slug;

  CategoryModel({required this.id, required this.name, required this.slug});

  CategoryVisual get visual => CategoryVisual.forSlugOrName(slug, name);
  String get emoji => visual.emoji;
  IconData get icon => visual.icon;
  Color get color => visual.primaryColor;
  Color get bgColor => visual.bgColor;
  String get subtitle => visual.subtitle;

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    return CategoryModel(
      id: json['id'] as int? ?? 0,
      name: json['name'] as String? ?? 'Kategoriya',
      slug: json['slug'] as String? ?? '',
    );
  }
}
