import 'package:flutter/material.dart';

import '../../../theme/app_theme.dart';

Color membershipAccentColor(String? value, Color fallback) {
  if (value == null) {
    return fallback;
  }

  final normalized = value.trim().replaceFirst('#', '');
  if (!RegExp(r'^[0-9a-fA-F]{6}$').hasMatch(normalized)) {
    return fallback;
  }

  return Color(int.parse('FF$normalized', radix: 16));
}

IconData membershipBenefitIcon(String? value) {
  switch ((value ?? '').trim().toLowerCase()) {
    case 'star':
      return Icons.star_outline;
    case 'ticket':
      return Icons.confirmation_number_outlined;
    case 'parking':
      return Icons.local_parking_outlined;
    case 'lounge':
      return Icons.weekend_outlined;
    case 'gift':
      return Icons.card_giftcard_outlined;
    case 'food':
      return Icons.restaurant_outlined;
    default:
      return Icons.star_outline;
  }
}

List<Color> membershipHeroGradient(BuildContext context, Color accentColor) {
  final palette = context.lfc;
  return [
    palette.heroGradient.first,
    Color.alphaBlend(
      accentColor.withValues(alpha: 0.2),
      palette.heroGradient.last,
    ),
  ];
}
