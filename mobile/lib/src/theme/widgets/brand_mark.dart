import 'package:flutter/material.dart';

import '../app_theme.dart';

/// Asset paths for the official Lusail SC crest (navy sidra frond on transparent).
class LfcAssets {
  LfcAssets._();
  static const crest = 'assets/images/lusail_crest.png';
  static const logo = 'assets/images/lusail_logo.png';
}

/// The Lusail lockup: the club's crest beside the wordmark. The mark stays Latin
/// in both locales, as brand lockups usually do.
class BrandMark extends StatelessWidget {
  const BrandMark({
    super.key,
    this.compact = false,
    this.onHero = false,
    this.showSubline = true,
  });

  /// Smaller sizing for app bars.
  final bool compact;

  /// Render for placement on the navy hero surface (light-on-dark).
  final bool onHero;

  /// Whether to show the "FOOTBALL ACADEMY" eyebrow under the wordmark.
  final bool showSubline;

  @override
  Widget build(BuildContext context) {
    final palette = context.lfc;
    final scheme = Theme.of(context).colorScheme;
    final isDark = scheme.brightness == Brightness.dark;

    final titleColor = onHero ? palette.onHero : scheme.onSurface;
    final eyebrowColor = onHero ? palette.goldBright : palette.gold;
    final crestHeight = compact ? 34.0 : 52.0;

    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        LfcCrest(height: crestHeight, onHero: onHero, isDark: isDark),
        SizedBox(width: compact ? 11 : 14),
        Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'LUSAIL',
              style: TextStyle(
                fontFamily: 'Changa',
                fontWeight: FontWeight.w700,
                fontSize: compact ? 17 : 24,
                height: 1.0,
                letterSpacing: 1.5,
                color: titleColor,
              ),
            ),
            if (showSubline) ...[
              const SizedBox(height: 2),
              Text(
                'FOOTBALL ACADEMY',
                style: TextStyle(
                  fontFamily: 'Tajawal',
                  fontWeight: FontWeight.w700,
                  fontSize: compact ? 8 : 10,
                  height: 1.0,
                  letterSpacing: compact ? 2 : 3.4,
                  color: eyebrowColor,
                ),
              ),
            ],
          ],
        ),
      ],
    );
  }
}

/// The club crest image, recoloured for the surface it sits on. The source is a
/// single-tone navy shape on transparency, so a `srcIn` tint keeps the soft
/// edges while swapping the colour.
class LfcCrest extends StatelessWidget {
  const LfcCrest({
    super.key,
    required this.height,
    this.onHero = false,
    this.isDark = false,
  });

  final double height;
  final bool onHero;
  final bool isDark;

  @override
  Widget build(BuildContext context) {
    // On the navy hero or the dark theme the navy crest would disappear, so tint
    // it light; on light surfaces keep the brand navy.
    final Color? tint = onHero
        ? context.lfc.onHero
        : (isDark ? context.lfc.onHero : null);

    return Image.asset(
      LfcAssets.crest,
      height: height,
      color: tint,
      colorBlendMode: tint == null ? null : BlendMode.srcIn,
      filterQuality: FilterQuality.medium,
    );
  }
}
