import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../providers.dart';
import '../staff_session_controller.dart';

/// Switches the app into staff mode, from either shell's sign-in screen.
///
/// Both shells route their `home` on [StaffSessionStatus], so flipping the status is
/// what swaps the screen. When this button sits on a *pushed* route (app two reaches
/// the auth screen from the membership gate), that route would otherwise stay on top
/// of the new home — so pop it.
class StaffEntryButton extends ConsumerWidget {
  const StaffEntryButton({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context)!;

    return OutlinedButton.icon(
      key: const Key('staff-entry'),
      onPressed: () {
        ref.read(staffSessionControllerProvider.notifier).showLogin();

        final navigator = Navigator.of(context);
        if (navigator.canPop()) {
          navigator.pop();
        }
      },
      icon: const Icon(Icons.qr_code_scanner),
      label: Text(l10n.staffScannerEntry),
    );
  }
}
