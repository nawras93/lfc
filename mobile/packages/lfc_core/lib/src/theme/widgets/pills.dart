import 'package:flutter/material.dart';

import '../app_theme.dart';

/// Gold tier badge for VVIP surfaces (hero, offers, players).
class VvipPill extends StatelessWidget {
  const VvipPill({super.key, required this.label});

  final String label;

  @override
  Widget build(BuildContext context) {
    final palette = context.lfc;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        gradient: LinearGradient(colors: [palette.goldBright, palette.gold]),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          const Icon(
            Icons.workspace_premium,
            size: 14,
            color: LfcColors.navy800,
          ),
          const SizedBox(width: 4),
          Text(
            label,
            style: const TextStyle(
              fontFamily: 'Changa',
              fontWeight: FontWeight.w700,
              fontSize: 11,
              letterSpacing: 0.6,
              height: 1,
              leadingDistribution: TextLeadingDistribution.even,
              color: LfcColors.navy800,
            ),
          ),
        ],
      ),
    );
  }
}

/// Gold-outlined pill for a points value (reward cost, balances).
class PointsPill extends StatelessWidget {
  const PointsPill({super.key, required this.label, this.icon});

  final String label;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    final palette = context.lfc;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: palette.gold.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: palette.gold.withValues(alpha: 0.55)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 15, color: palette.gold),
            const SizedBox(width: 5),
          ],
          Text(
            label,
            style: TextStyle(
              fontFamily: 'Changa',
              fontWeight: FontWeight.w700,
              fontSize: 13.5,
              height: 1,
              leadingDistribution: TextLeadingDistribution.even,
              color: palette.gold,
            ),
          ),
        ],
      ),
    );
  }
}
