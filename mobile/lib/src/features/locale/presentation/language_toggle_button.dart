import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../providers.dart';

/// Language switch shown as a flag: the flag represents the language you'll
/// switch **to** — Qatar (Arabic) while in English, USA (English) while in
/// Arabic — mirroring the old "AR"/"EN" toggle.
class LanguageToggleButton extends ConsumerWidget {
  const LanguageToggleButton({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context)!;
    final locale = ref.watch(localeControllerProvider);
    final isArabic = locale.languageCode == 'ar';
    final targetFlag = isArabic ? 'assets/flags/us.png' : 'assets/flags/qa.png';

    return IconButton(
      key: const Key('language-toggle'),
      tooltip: l10n.languageLabel,
      onPressed: () => ref.read(localeControllerProvider.notifier).toggle(),
      icon: Container(
        width: 28,
        height: 20,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(4),
          border: Border.all(
            color: Colors.white.withValues(alpha: 0.5),
            width: 0.5,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.2),
              blurRadius: 2,
              offset: const Offset(0, 1),
            ),
          ],
        ),
        clipBehavior: Clip.antiAlias,
        child: Image.asset(
          targetFlag,
          fit: BoxFit.cover,
          filterQuality: FilterQuality.medium,
        ),
      ),
    );
  }
}
