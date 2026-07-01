import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../providers.dart';

class LanguageToggleButton extends ConsumerWidget {
  const LanguageToggleButton({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context)!;

    return IconButton(
      key: const Key('language-toggle'),
      tooltip: l10n.languageLabel,
      onPressed: () => ref.read(localeControllerProvider.notifier).toggle(),
      icon: Text(
        l10n.languageToggle,
        style: Theme.of(context).textTheme.labelLarge,
      ),
    );
  }
}
