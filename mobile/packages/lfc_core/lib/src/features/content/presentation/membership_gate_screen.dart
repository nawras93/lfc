import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../providers.dart';
import '../../../theme/app_theme.dart';
import '../../membership/presentation/vvip_card_screen.dart';
import '../../session/session_controller.dart';
import 'discount_wallet_screen.dart';
import 'member_auth_screen.dart';

class MembershipGateScreen extends ConsumerWidget {
  const MembershipGateScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context)!;
    final session = ref.watch(sessionControllerProvider);

    return switch (session.status) {
      SessionStatus.authenticated => const _MembershipRouter(),
      SessionStatus.unknown => const Center(child: CircularProgressIndicator()),
      SessionStatus.unauthenticated => Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 420),
          child: Card(
            margin: const EdgeInsets.all(24),
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.lock_outline, size: 38, color: context.lfc.gold),
                  const SizedBox(height: 14),
                  Text(
                    l10n.membershipSignInPrompt,
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                  const SizedBox(height: 18),
                  FilledButton(
                    key: const Key('membership-sign-in-button'),
                    onPressed: () {
                      Navigator.of(context).push(
                        MaterialPageRoute<void>(
                          builder: (_) => const MemberAuthScreen(),
                        ),
                      );
                    },
                    child: Text(l10n.signInButton),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    };
  }
}

class _MembershipRouter extends ConsumerWidget {
  const _MembershipRouter();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final session = ref.watch(sessionControllerProvider);
    final account = session.account;
    final accountType = account?.accountType ?? '';

    if (accountType == 'vvip_member') {
      return const VvipCardScreen();
    }

    return const DiscountWalletScreen();
  }
}
