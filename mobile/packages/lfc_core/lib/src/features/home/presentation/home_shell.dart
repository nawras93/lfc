import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../providers.dart';
import '../../../theme/app_theme.dart';
import '../../../theme/presentation/theme_toggle_button.dart';
import '../../../theme/widgets/brand_app_bar.dart';
import '../../auth/models/account.dart';
import '../../locale/presentation/language_toggle_button.dart';
import '../../offers/presentation/offers_screen.dart';
import '../../players/presentation/players_screen.dart';
import '../../redemptions/presentation/rewards_screen.dart';
import '../../scan/presentation/qr_screen.dart';
import 'widgets/points_hero_card.dart';

class HomeShell extends ConsumerStatefulWidget {
  const HomeShell({super.key});

  @override
  ConsumerState<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends ConsumerState<HomeShell> {
  int _index = 0;

  @override
  Widget build(BuildContext context) {
    ref.listen<Locale>(localeControllerProvider, (previous, next) {
      if (previous?.languageCode != next.languageCode) {
        ref.invalidate(playersProvider);
      }
    });

    final l10n = AppLocalizations.of(context)!;
    final session = ref.watch(sessionControllerProvider);
    final account = session.account;
    final playersAsync = ref.watch(playersProvider);

    final isVvipClient = account?.accountType == 'vvip_client';
    final pointsLabel = isVvipClient
        ? l10n.balanceLabel
        : l10n.totalPointsLabel;
    final pointsValue = isVvipClient
        ? '${account!.accountBalance}'
        : playersAsync.maybeWhen(
            data: (players) =>
                '${players.fold<int>(0, (sum, p) => sum + p.pointsBalance)}',
            orElse: () => '…',
          );

    final tabs = [l10n.playersTab, l10n.qrTab, l10n.rewardsTab, l10n.offersTab];

    return Scaffold(
      appBar: BrandAppBar(
        actions: [
          const ThemeToggleButton(),
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
        top: false,
        child: account == null
            ? Center(child: Text(l10n.loadingText))
            : _Body(
                index: _index,
                account: account,
                pointsLabel: pointsLabel,
                pointsValue: pointsValue,
                isVvip: account.isVvip,
                sectionTitle: tabs[_index],
              ),
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: (value) => setState(() => _index = value),
        destinations: [
          NavigationDestination(
            icon: const Icon(Icons.groups_2_outlined),
            selectedIcon: const Icon(Icons.groups_2),
            label: l10n.playersTab,
          ),
          NavigationDestination(
            icon: const Icon(Icons.qr_code_2_outlined),
            selectedIcon: const Icon(Icons.qr_code_2),
            label: l10n.qrTab,
          ),
          NavigationDestination(
            icon: const Icon(Icons.redeem_outlined),
            selectedIcon: const Icon(Icons.redeem),
            label: l10n.rewardsTab,
          ),
          NavigationDestination(
            icon: const Icon(Icons.local_offer_outlined),
            selectedIcon: const Icon(Icons.local_offer),
            label: l10n.offersTab,
          ),
        ],
      ),
    );
  }
}

class _Body extends StatelessWidget {
  const _Body({
    required this.index,
    required this.account,
    required this.pointsLabel,
    required this.pointsValue,
    required this.isVvip,
    required this.sectionTitle,
  });

  final int index;
  final Account account;
  final String pointsLabel;
  final String pointsValue;
  final bool isVvip;
  final String sectionTitle;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    // The points hero is the home dashboard's centrepiece. On the functional
    // tabs (QR, rewards, offers) it would only steal vertical space, so those
    // screens get the full canvas. IndexedStack keeps every tab alive.
    final showHero = index == 0;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (showHero) ...[
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 4, 20, 0),
            child: PointsHeroCard(
              label: pointsLabel,
              value: pointsValue,
              unit: l10n.pointsUnit,
              greeting: l10n.welcomeLabel(account.name),
              isVvip: isVvip,
              tierLabel: isVvip ? l10n.vvipStatusLabel : l10n.memberLabel,
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(22, 22, 22, 10),
            child: Align(
              alignment: AlignmentDirectional.centerStart,
              child: _SectionHeading(title: sectionTitle),
            ),
          ),
        ] else
          const SizedBox(height: 8),
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

class _SectionHeading extends StatelessWidget {
  const _SectionHeading({required this.title});

  final String title;

  @override
  Widget build(BuildContext context) {
    final palette = context.lfc;
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 4,
          height: 20,
          decoration: BoxDecoration(
            color: palette.gold,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        const SizedBox(width: 10),
        Text(title, style: Theme.of(context).textTheme.headlineSmall),
      ],
    );
  }
}
