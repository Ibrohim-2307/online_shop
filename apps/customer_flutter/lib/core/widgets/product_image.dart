import 'package:flutter/material.dart';
import '../constants/app_colors.dart';
import '../network/api_config.dart';
import 'motion.dart';

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
      color: AppColors.surfaceMuted,
      alignment: Alignment.center,
      child: Icon(
        Icons.image_outlined,
        size: iconSize,
        color: AppColors.brandDark.withValues(alpha: 0.25),
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
      // Lets photos from the shop's server show in the web build as well
      webHtmlElementStrategy: WebHtmlElementStrategy.fallback,
      frameBuilder: (context, child, frame, wasSynchronouslyLoaded) {
        if (wasSynchronouslyLoaded) return child;
        return Stack(
          fit: StackFit.expand,
          children: [
            if (frame == null) const Shimmer(child: Bone(radius: 0)),
            AnimatedOpacity(
              opacity: frame == null ? 0 : 1,
              duration: const Duration(milliseconds: 280),
              curve: Curves.easeOut,
              child: child,
            ),
          ],
        );
      },
      errorBuilder: (context, error, stackTrace) => _placeholder(),
    );
  }
}
