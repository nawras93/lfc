import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../providers.dart';
import '../../auth/models/account.dart';
import '../../locale/presentation/language_toggle_button.dart';
import '../../offers/presentation/offers_screen.dart';
import '../../players/presentation/players_screen.dart';
import '../../redemptions/presentation/rewards_screen.dart';
import '../../scan/presentation/qr_screen.dart';

class HomeShell extends ConsumerStatefulWidget {
  const HomeShell({super.key});

  @override
  ConsumerState<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends ConsumerState<HomeShell> {
  int _index = 0;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final session = ref.watch(sessionControllerProvider);
    final account = session.account;

    final titles = [
      l10n.playersTab,
      l10n.qrTab,
      l10n.rewardsTab,
      l10n.offersTab,
    ];

    return Scaffold(
      appBar: AppBar(
        title: Text(titles[_index]),
        actions: [
          const LanguageToggleButton(),
          IconButton(
            key: const Key('logout-button'),
            tooltip: l10n.logoutButton,
            onPressed: session.isBusy
                ? null
                : () => ref.read(sessionControllerProvider.notifier).logout(),
            icon: const Icon(Icons.logout),
          ),
        ],
      ),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: account == null
              ? Center(child: Text(l10n.loadingText))
              : _Body(index: _index, account: account),
        ),
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: (value) => setState(() => _index = value),
        destinations: [
          NavigationDestination(
            icon: const Icon(Icons.groups_2_outlined),
            label: l10n.playersTab,
          ),
          NavigationDestination(
            icon: const Icon(Icons.qr_code_2),
            label: l10n.qrTab,
          ),
          NavigationDestination(
            icon: const Icon(Icons.redeem_outlined),
            label: l10n.rewardsTab,
          ),
          NavigationDestination(
            icon: const Icon(Icons.local_offer_outlined),
            label: l10n.offersTab,
          ),
        ],
      ),
    );
  }
}

class _Body extends StatelessWidget {
  const _Body({required this.index, required this.account});

  final int index;
  final Account account;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Card(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Wrap(
              spacing: 16,
              runSpacing: 12,
              alignment: WrapAlignment.spaceBetween,
              crossAxisAlignment: WrapCrossAlignment.center,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      l10n.welcomeLabel(account.name),
                      style: Theme.of(context).textTheme.headlineSmall,
                    ),
                    const SizedBox(height: 4),
                    Text(account.email),
                  ],
                ),
                Wrap(
                  spacing: 12,
                  runSpacing: 12,
                  children: [
                    _InfoChip(
                      label: l10n.accountTypeLabel,
                      value: account.accountType ?? l10n.notAvailableValue,
                    ),
                    _InfoChip(
                      label: l10n.vvipStatusLabel,
                      value: account.isVvip ? l10n.yesText : l10n.noText,
                    ),
                    _InfoChip(
                      label: l10n.balanceLabel,
                      value: '${account.accountBalance} ${l10n.pointsUnit}',
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 16),
        Expanded(
          child: IndexedStack(
            index: index,
            children: [
              PlayersScreen(account: account),
              const QrScreen(),
              RewardsScreen(account: account),
              const OffersScreen(),
            ],
          ),
        ),
      ],
    );
  }
}

class _InfoChip extends StatelessWidget {
  const _InfoChip({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surfaceContainerHighest,
        borderRadius: BorderRadius.circular(18),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(label, style: Theme.of(context).textTheme.bodySmall),
            Text(value, style: Theme.of(context).textTheme.titleMedium),
          ],
        ),
      ),
    );
  }
}
