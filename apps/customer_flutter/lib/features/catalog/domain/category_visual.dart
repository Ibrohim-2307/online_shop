import 'package:flutter/material.dart';

/// Professional styling metadata for categories.
/// Provides ambient tinted color palettes, crisp icons, and tasteful emoji accents.
class CategoryVisual {
  final String emoji;
  final IconData icon;
  final Color primaryColor;
  final Color bgColor;
  final String subtitle;

  const CategoryVisual({
    required this.emoji,
    required this.icon,
    required this.primaryColor,
    required this.bgColor,
    required this.subtitle,
  });

  /// Map any category slug or name to its curated visual presentation.
  static CategoryVisual forSlugOrName(String slug, [String name = '']) {
    final s = slug.toLowerCase().trim();
    final n = name.toLowerCase().trim();

    // 1. Qurilish materiallari
    if (s.contains('qurilish-material') || s.contains('sement') || s.contains('gisht') || n.contains('qurilish material')) {
      return const CategoryVisual(
        emoji: '🧱',
        icon: Icons.foundation_rounded,
        primaryColor: Color(0xFFD97706),
        bgColor: Color(0xFFFEF3C7),
        subtitle: 'Sement, g\'isht, profil',
      );
    }

    // 2. Sanoat uskunalari
    if (s.contains('sanoat') || s.contains('mashina') || (s.contains('uskuna') && !s.contains('asbob')) || n.contains('sanoat')) {
      return const CategoryVisual(
        emoji: '⚙️',
        icon: Icons.precision_manufacturing_rounded,
        primaryColor: Color(0xFF2563EB),
        bgColor: Color(0xFFDBEAFE),
        subtitle: 'Dastgohlar, stanoklar',
      );
    }

    // 3. Oziq-ovqat va ichimliklar
    if (s.contains('oziq-ovqat') || s.contains('ichimlik') || s.contains('don') || n.contains('oziq')) {
      return const CategoryVisual(
        emoji: '🌾',
        icon: Icons.restaurant_rounded,
        primaryColor: Color(0xFF059669),
        bgColor: Color(0xFFD1FAE5),
        subtitle: 'Don, un, oziq-ovqat',
      );
    }

    // 4. Elektr va yoritish jihozlari
    if (s.contains('elektr') || s.contains('yoritish') || s.contains('texnika') || s.contains('elektronika') || n.contains('elektr')) {
      return const CategoryVisual(
        emoji: '💡',
        icon: Icons.lightbulb_rounded,
        primaryColor: Color(0xFFCA8A04),
        bgColor: Color(0xFFFEF9C3),
        subtitle: 'Kabel, avtomat, chiroq',
      );
    }

    // 5. Bo'yoqlar va kimyo
    if (s.contains('boyoq') || s.contains('kimyo') || n.contains('bo\'yoq') || n.contains('boyoq')) {
      return const CategoryVisual(
        emoji: '🎨',
        icon: Icons.format_paint_rounded,
        primaryColor: Color(0xFF7C3AED),
        bgColor: Color(0xFFEDE9FE),
        subtitle: 'Emulsiya, lak, kimyo',
      );
    }

    // 6. Santexnika va quvurlar
    if (s.contains('santexnika') || s.contains('quvur') || s.contains('suv') || s.contains('isitish') || n.contains('santexnika')) {
      return const CategoryVisual(
        emoji: '🚰',
        icon: Icons.plumbing_rounded,
        primaryColor: Color(0xFF0891B2),
        bgColor: Color(0xFFCFFAFE),
        subtitle: 'Quvurlar, kran, nasos',
      );
    }

    // 7. Qurilish asbob-uskunalari
    if (s.contains('asbob') || s.contains('instrument') || n.contains('asbob')) {
      return const CategoryVisual(
        emoji: '🛠️',
        icon: Icons.handyman_rounded,
        primaryColor: Color(0xFFEA580C),
        bgColor: Color(0xFFFFEDD5),
        subtitle: 'Drel, laqqa, o\'lchov',
      );
    }

    // 8. Avtomobil jihozlari
    if (s.contains('avto') || n.contains('avto')) {
      return const CategoryVisual(
        emoji: '🚗',
        icon: Icons.directions_car_rounded,
        primaryColor: Color(0xFFE11D48),
        bgColor: Color(0xFFFFE4E6),
        subtitle: 'Moy, ehtiyot qismlar',
      );
    }

    // 9. Mebel
    if (s.contains('mebel') || n.contains('mebel')) {
      return const CategoryVisual(
        emoji: '🪑',
        icon: Icons.chair_rounded,
        primaryColor: Color(0xFF4F46E5),
        bgColor: Color(0xFFE0E7FF),
        subtitle: 'Ofis va ustaxona',
      );
    }

    // Default / All
    return const CategoryVisual(
      emoji: '📦',
      icon: Icons.category_rounded,
      primaryColor: Color(0xFF123C2F),
      bgColor: Color(0xFFE6F4EA),
      subtitle: 'Sifatli mahsulotlar',
    );
  }
}
