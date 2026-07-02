import 'package:flutter/material.dart';

import '../../../../theme/app_theme.dart';

/// Circular initials badge for a player, ringed in Lusail gold.
class PlayerAvatar extends StatelessWidget {
  const PlayerAvatar({super.key, required this.name, this.size = 46});

  final String name;
  final double size;

  @override
  Widget build(BuildContext context) {
    final palette = context.lfc;
    final scheme = Theme.of(context).colorScheme;

    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: scheme.primary.withValues(alpha: 0.12),
        border: Border.all(color: palette.gold.withValues(alpha: 0.6), width: 1.5),
      ),
      alignment: Alignment.center,
      child: Text(
        _initials(name),
        style: TextStyle(
          fontFamily: 'Changa',
          fontWeight: FontWeight.w700,
          fontSize: size * 0.36,
          color: scheme.primary,
        ),
      ),
    );
  }

  static String _initials(String name) {
    final parts = name.trim().split(RegExp(r'\s+')).where((p) => p.isNotEmpty);
    if (parts.isEmpty) {
      return '?';
    }
    if (parts.length == 1) {
      final p = parts.first;
      return p.characters.take(2).toString().toUpperCase();
    }
    return (parts.first.characters.first + parts.last.characters.first)
        .toUpperCase();
  }
}
