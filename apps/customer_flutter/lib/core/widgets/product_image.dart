import 'package:flutter/material.dart';
import '../constants/app_colors.dart';
import '../network/api_config.dart';

class ProductImage extends StatelessWidget {
  final String? url;
  final double iconSize;

  const ProductImage({super.key, required this.url, this.iconSize = 38});

  String? get _source {
    final value = url;
    if (value == null || value.isEmpty) return null;
    return value.startsWith('http') ? value : '${ApiConfig.origin}$value';
  }

  Widget _placeholder() {
    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            AppColors.surfaceMuted,
            AppColors.brandLime.withValues(alpha: 0.15),
          ],
        ),
      ),
      child: Center(
        child: Container(
          width: iconSize + 20,
          height: iconSize + 20,
          decoration: BoxDecoration(
            color: Colors.white.withValues(alpha: 0.8),
            shape: BoxShape.circle,
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.04),
                blurRadius: 8,
                offset: const Offset(0, 3),
              ),
            ],
          ),
          child: Icon(
            Icons.inventory_2_outlined,
            size: iconSize,
            color: AppColors.brandDark.withValues(alpha: 0.7),
          ),
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
      loadingBuilder: (context, child, progress) {
        if (progress == null) return child;
        return _placeholder();
      },
      errorBuilder: (context, error, stackTrace) => _placeholder(),
    );
  }
}
