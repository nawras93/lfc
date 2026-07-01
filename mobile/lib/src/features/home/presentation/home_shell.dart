import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../providers.dart';

class HomeShell extends ConsumerWidget {
  const HomeShell({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context)!;
    final session = ref.watch(sessionControllerProvider);
    final account = session.account;

    return Scaffold(
      appBar: AppBar(
        title: Text(l10n.homeTitle),
        actions: [
          IconButton(
            key: const Key('language-toggle'),
            tooltip: l10n.languageLabel,
            onPressed: () => ref.read(localeControllerProvider.notifier).toggle(),
            icon: Text(
              l10n.languageToggle,
              style: Theme.of(context).textTheme.labelLarge,
            ),
          ),
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
              : Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Text(
                      l10n.welcomeLabel(account.name),
                      style: Theme.of(context).textTheme.headlineSmall,
                    ),
                    const SizedBox(height: 24),
                    _InfoTile(
                      label: l10n.accountTypeLabel,
                      value: account.accountType ?? l10n.notAvailableValue,
                    ),
                    const SizedBox(height: 12),
                    _InfoTile(
                      label: l10n.vvipStatusLabel,
                      value: account.isVvip ? l10n.yesText : l10n.noText,
                    ),
                    const SizedBox(height: 12),
                    _InfoTile(
                      label: l10n.balanceLabel,
                      value: account.accountBalance.toString(),
                    ),
                  ],
                ),
        ),
      ),
    );
  }
}

class _InfoTile extends StatelessWidget {
  const _InfoTile({
    required this.label,
    required this.value,
  });

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: ListTile(
        title: Text(label),
        subtitle: Text(value),
      ),
    );
  }
}
