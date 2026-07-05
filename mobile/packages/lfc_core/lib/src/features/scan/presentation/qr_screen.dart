import 'package:flutter/material.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../theme/widgets/qr_pass_card.dart';

class QrScreen extends StatelessWidget {
  const QrScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    return QrPassCard(
      title: l10n.qrScreenTitle,
      subtitle: l10n.qrScreenSubtitle,
    );
  }
}
