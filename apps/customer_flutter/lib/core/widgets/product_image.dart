import 'package:flutter/material.dart';
import '../constants/app_colors.dart';
import '../network/api_config.dart';

/// Product photo from the server, with a calm placeholder while it loads or if it is missing.
class ProductImage extends StatelessWidget {
  final String? url;
  final double iconSize;

  const ProductImage({super.key, required this.url, this.iconSize = 36});

  String? get _source {
    final value = url;
    if (value == null || value.isEmpty) return null;
    return value.startsWith('http') ? value : '${ApiConfig.origin}$value';
  }

  Widget _placeholder() {
    return ColoredBox(
      color: AppColors.surfaceMuted,
      child: Center(
        child: Icon(
          Icons.inventory_2_outlined,
          size: iconSize,
          color: AppColors.brandDark.withValues(alpha: 0.35),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final source = _source;
    if (source == null) return _placeholder();

    return Image.network(
      source,
      fit: BoxFit.cover,
      webHtmlElementStrategy: WebHtmlElementStrategy.fallback,
      loadingBuilder: (context, child, progress) => progress == null ? child : _placeholder(),
      errorBuilder: (context, error, stackTrace) => _placeholder(),
    );
  }
}
