import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../providers.dart';
import '../../../theme/presentation/theme_toggle_button.dart';
import '../../../theme/widgets/brand_app_bar.dart';
import '../../locale/presentation/language_toggle_button.dart';
import 'home_news_screen.dart';
import 'matches_screen.dart';
import 'membership_gate_screen.dart';

class SupporterShell extends ConsumerStatefulWidget {
  const SupporterShell({super.key});

  @override
  ConsumerState<SupporterShell> createState() => _SupporterShellState();
}

class _SupporterShellState extends ConsumerState<SupporterShell> {
  int _index = 0;

  @override
  Widget build(BuildContext context) {
    ref.listen<Locale>(localeControllerProvider, (previous, next) {
      if (previous?.languageCode != next.languageCode) {
        ref.invalidate(newsProvider);
        ref.invalidate(fixturesProvider);
        ref.invalidate(resultsProvider);
        ref.invalidate(standingsProvider);
      }
    });

    final l10n = AppLocalizations.of(context)!;
    final labels = [
      l10n.homeNavLabel,
      l10n.matchesNavLabel,
      l10n.membershipNavLabel,
    ];

    return Scaffold(
      appBar: BrandAppBar(
        roleLabel: labels[_index],
        actions: const [ThemeToggleButton(), LanguageToggleButton()],
      ),
      body: SafeArea(
        top: false,
        child: IndexedStack(
          index: _index,
          children: const [
            HomeNewsScreen(),
            MatchesScreen(),
            MembershipGateScreen(),
          ],
        ),
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: (value) => setState(() => _index = value),
        destinations: [
          NavigationDestination(
            icon: const Icon(Icons.newspaper_outlined),
            selectedIcon: const Icon(Icons.newspaper),
            label: l10n.homeNavLabel,
          ),
          NavigationDestination(
            icon: const Icon(Icons.sports_soccer_outlined),
            selectedIcon: const Icon(Icons.sports_soccer),
            label: l10n.matchesNavLabel,
          ),
          NavigationDestination(
            icon: const Icon(Icons.badge_outlined),
            selectedIcon: const Icon(Icons.badge),
            label: l10n.membershipNavLabel,
          ),
        ],
      ),
    );
  }
}
