import 'package:flutter/material.dart';
import '../constants/app_colors.dart';

/// Same mark as the website header.
class AppLogo extends StatelessWidget {
  const AppLogo({super.key});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 34,
          height: 34,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: AppColors.brandLime,
            borderRadius: BorderRadius.circular(10),
          ),
          child: const Text(
            'M',
            style: TextStyle(color: AppColors.brandDark, fontSize: 19, fontWeight: FontWeight.w900),
          ),
        ),
        const SizedBox(width: 9),
        const Text(
          'MARKET',
          style: TextStyle(
            color: AppColors.brandDark,
            fontSize: 20,
            fontWeight: FontWeight.w900,
            letterSpacing: 0.3,
          ),
        ),
      ],
    );
  }
}
