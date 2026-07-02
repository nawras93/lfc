import 'package:flutter/material.dart';

import '../../../../theme/app_theme.dart';
import '../../../../theme/widgets/fanar_backdrop.dart';
import '../../../../theme/widgets/pills.dart';

/// The signature "matchday scoreboard" — a navy hero (in both themes) that
/// frames the member's points as the single most important thing on screen.
class PointsHeroCard extends StatelessWidget {
  const PointsHeroCard({
    super.key,
    required this.label,
    required this.value,
    required this.unit,
    required this.greeting,
    this.tierLabel,
    this.isVvip = false,
  });

  /// e.g. "Total points" / "Account balance".
  final String label;

  /// The number, already formatted (no unit).
  final String value;

  /// e.g. "pts".
  final String unit;

  /// e.g. "Welcome, Sara".
  final String greeting;

  /// Small tier label shown when not VVIP (e.g. "Member").
  final String? tierLabel;

  final bool isVvip;

  @override
  Widget build(BuildContext context) {
    final palette = context.lfc;

    return DecoratedBox(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(24),
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: palette.heroGradient,
        ),
        boxShadow: [
          BoxShadow(
            color: LfcColors.navy900.withValues(alpha: 0.5),
            blurRadius: 24,
            offset: const Offset(0, 12),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(24),
        child: Stack(
          children: [
            FanarBackdrop(color: palette.heroPattern),
            // Soft gold glow anchored to the top corner.
            Positioned(
              top: -70,
              right: -50,
              child: Container(
                width: 190,
                height: 190,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: RadialGradient(
                    colors: [
                      palette.gold.withValues(alpha: 0.28),
                      palette.gold.withValues(alpha: 0.0),
                    ],
                  ),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(22, 20, 22, 22),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          greeting,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontFamily: 'Tajawal',
                            fontWeight: FontWeight.w500,
                            fontSize: 13.5,
                            color: palette.onHeroMuted,
                          ),
                        ),
                      ),
                      if (isVvip)
                        VvipPill(label: tierLabel ?? '')
                      else if (tierLabel != null)
                        _MemberTag(label: tierLabel!, palette: palette),
                    ],
                  ),
                  const SizedBox(height: 22),
                  Text(
                    label.toUpperCase(),
                    style: TextStyle(
                      fontFamily: 'Tajawal',
                      fontWeight: FontWeight.w700,
                      fontSize: 11.5,
                      letterSpacing: 1.6,
                      color: palette.goldBright,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.baseline,
                    textBaseline: TextBaseline.alphabetic,
                    children: [
                      Flexible(
                        child: Text(
                          value,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontFamily: 'Changa',
                            fontWeight: FontWeight.w700,
                            fontSize: 52,
                            height: 1.0,
                            letterSpacing: -1,
                            color: palette.onHero,
                            fontFeatures: const [FontFeature.tabularFigures()],
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Padding(
                        padding: const EdgeInsets.only(bottom: 6),
                        child: Text(
                          unit,
                          style: TextStyle(
                            fontFamily: 'Tajawal',
                            fontWeight: FontWeight.w500,
                            fontSize: 16,
                            color: palette.onHeroMuted,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _MemberTag extends StatelessWidget {
  const _MemberTag({required this.label, required this.palette});

  final String label;
  final LfcPalette palette;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: palette.onHeroMuted.withValues(alpha: 0.35)),
      ),
      child: Text(
        label.toUpperCase(),
        style: TextStyle(
          fontFamily: 'Tajawal',
          fontWeight: FontWeight.w700,
          fontSize: 10,
          letterSpacing: 1.2,
          color: palette.onHeroMuted,
        ),
      ),
    );
  }
}
