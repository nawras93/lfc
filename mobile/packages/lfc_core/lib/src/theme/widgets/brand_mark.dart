import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../branding/brand.dart';
import '../app_theme.dart';

/// The Lusail lockup: the club's crest beside the wordmark. The mark stays Latin
/// in both locales, as brand lockups usually do.
class BrandMark extends ConsumerWidget {
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
  Widget build(BuildContext context, WidgetRef ref) {
    final brand = ref.watch(brandProvider);
    final palette = context.lfc;
    final scheme = Theme.of(context).colorScheme;
    final isDark = scheme.brightness == Brightness.dark;

    final titleColor = onHero ? palette.onHero : scheme.onSurface;
    final eyebrowColor = onHero ? palette.goldBright : palette.gold;
    final crestHeight = compact ? 34.0 : 52.0;

    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        _BrandLogo(
          image: brand.logo,
          height: crestHeight,
          onHero: onHero,
          isDark: isDark,
        ),
        SizedBox(width: compact ? 11 : 14),
        Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              brand.wordmark.title,
              style: TextStyle(
                fontFamily: 'Changa',
                fontWeight: FontWeight.w700,
                fontSize: compact ? 17 : 24,
                height: 1.0,
                letterSpacing: 1.5,
                color: titleColor,
              ),
            ),
            if (showSubline && brand.wordmark.subtitle != null) ...[
              const SizedBox(height: 2),
              Text(
                brand.wordmark.subtitle!,
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
class _BrandLogo extends StatelessWidget {
  const _BrandLogo({
    required this.image,
    required this.height,
    this.onHero = false,
    this.isDark = false,
  });

  final ImageProvider<Object> image;
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

    final imageWidget = Image(
      image: image,
      height: height,
      filterQuality: FilterQuality.medium,
    );

    if (tint == null) {
      return imageWidget;
    }

    // Tint via ColorFiltered rather than Image.colorBlendMode: on
    // Android/Impeller the latter paints the quad's antialiased edge and leaves
    // faint seams at the image's box edges. A layer filter avoids that.
    return ColorFiltered(
      colorFilter: ColorFilter.mode(tint, BlendMode.srcIn),
      child: imageWidget,
    );
  }
}
