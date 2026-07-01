import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../core/api/api_exception.dart';
import '../../../providers.dart';
import '../../locale/presentation/language_toggle_button.dart';
import '../../scan/models/fixture_summary.dart';
import '../../scan/models/scan_result.dart';

class StaffScannerScreen extends ConsumerStatefulWidget {
  const StaffScannerScreen({super.key});

  @override
  ConsumerState<StaffScannerScreen> createState() => _StaffScannerScreenState();
}

class _StaffScannerScreenState extends ConsumerState<StaffScannerScreen> {
  late Future<List<FixtureSummary>> _fixturesFuture;
  final _tokenController = TextEditingController();
  int? _selectedFixtureId;
  ScanResult? _result;
  String? _error;
  bool _submitting = false;
  bool _cameraEnabled = false;
  bool _handlingDetection = false;

  @override
  void initState() {
    super.initState();
    _fixturesFuture = _loadFixtures();
  }

  @override
  void dispose() {
    _tokenController.dispose();
    super.dispose();
  }

  Future<List<FixtureSummary>> _loadFixtures() async {
    final fixtures = await ref.read(staffScanRepositoryProvider).fetchOpenFixtures();
    if (_selectedFixtureId == null && fixtures.isNotEmpty) {
      _selectedFixtureId = fixtures.first.id;
    }
    return fixtures;
  }

  Future<void> _submitToken(String token) async {
    final l10n = AppLocalizations.of(context)!;

    if (_selectedFixtureId == null) {
      setState(() => _error = l10n.selectFixtureHint);
      return;
    }

    setState(() {
      _submitting = true;
      _error = null;
      _result = null;
    });

    try {
      final result = await ref.read(staffScanRepositoryProvider).submitScan(
            fixtureId: _selectedFixtureId!,
            token: token,
          );

      if (mounted) {
        setState(() => _result = result);
      }
    } on ApiException catch (error) {
      if (mounted) {
        setState(() => _error = _friendlyError(l10n, error));
      }
    } finally {
      if (mounted) {
        setState(() {
          _submitting = false;
          _handlingDetection = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final session = ref.watch(staffSessionControllerProvider);
    final locale = Localizations.localeOf(context).toLanguageTag();
    final formatter = DateFormat.yMMMd(locale).add_jm();

    return Scaffold(
      appBar: AppBar(
        title: Text(l10n.staffScannerTitle),
        actions: [
          const LanguageToggleButton(),
          IconButton(
            onPressed: () => ref.read(staffSessionControllerProvider.notifier).logout(),
            icon: const Icon(Icons.logout),
            tooltip: l10n.logoutButton,
          ),
        ],
      ),
      body: FutureBuilder<List<FixtureSummary>>(
        future: _fixturesFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState != ConnectionState.done) {
            return Center(child: Text(l10n.loadingText));
          }

          if (snapshot.hasError) {
            return Center(child: Text(snapshot.error.toString()));
          }

          final fixtures = snapshot.data ?? const <FixtureSummary>[];

          return RefreshIndicator(
            onRefresh: () async {
              final next = _loadFixtures();
              setState(() => _fixturesFuture = next);
              await next;
            },
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          session.user?.name ?? '',
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                        const SizedBox(height: 8),
                        DropdownButtonFormField<int>(
                          key: const Key('staff-fixture-select'),
                          initialValue: _selectedFixtureId,
                          decoration: InputDecoration(labelText: l10n.fixtureLabel),
                          items: fixtures
                              .map(
                                (fixture) => DropdownMenuItem<int>(
                                  value: fixture.id,
                                  child: Text('${fixture.teamName ?? ''} vs ${fixture.opponent}'),
                                ),
                              )
                              .toList(),
                          onChanged: (value) => setState(() => _selectedFixtureId = value),
                        ),
                        if (_selectedFixture(fixtures) case final fixture?)
                          Padding(
                            padding: const EdgeInsets.only(top: 12),
                            child: Text(
                              [
                                if (fixture.venue?.isNotEmpty == true) fixture.venue!,
                                if (fixture.kickoffAt != null)
                                  formatter.format(fixture.kickoffAt!.toLocal()),
                              ].join(' • '),
                            ),
                          ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        Text(
                          l10n.manualTokenTitle,
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                        const SizedBox(height: 12),
                        TextField(
                          key: const Key('manual-token-field'),
                          controller: _tokenController,
                          minLines: 2,
                          maxLines: 4,
                          decoration: InputDecoration(
                            labelText: l10n.tokenLabel,
                            hintText: l10n.manualTokenHint,
                          ),
                        ),
                        const SizedBox(height: 12),
                        FilledButton(
                          key: const Key('manual-scan-submit'),
                          onPressed: _submitting
                              ? null
                              : () => _submitToken(_tokenController.text.trim()),
                          child: Text(l10n.submitScanButton),
                        ),
                        const SizedBox(height: 16),
                        OutlinedButton.icon(
                          onPressed: () => setState(() => _cameraEnabled = !_cameraEnabled),
                          icon: const Icon(Icons.qr_code_scanner),
                          label: Text(
                            _cameraEnabled ? l10n.hideCameraButton : l10n.openCameraButton,
                          ),
                        ),
                        if (_cameraEnabled) ...[
                          const SizedBox(height: 16),
                          SizedBox(
                            height: 280,
                            child: ClipRRect(
                              borderRadius: BorderRadius.circular(20),
                              child: MobileScanner(
                                onDetect: (capture) {
                                  if (_handlingDetection || _submitting) {
                                    return;
                                  }

                                  final code = capture.barcodes.first.rawValue;
                                  if (code == null || code.isEmpty) {
                                    return;
                                  }

                                  _handlingDetection = true;
                                  _tokenController.text = code;
                                  _submitToken(code);
                                },
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                ),
                if (_error != null) ...[
                  const SizedBox(height: 16),
                  Text(
                    _error!,
                    style: TextStyle(color: Theme.of(context).colorScheme.error),
                  ),
                ],
                if (_result != null) ...[
                  const SizedBox(height: 16),
                  Card(
                    key: const Key('staff-scan-result'),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            l10n.scanSuccessTitle,
                            style: Theme.of(context).textTheme.titleMedium,
                          ),
                          const SizedBox(height: 8),
                          ..._result!.credited.map(
                            (credit) => Padding(
                              padding: const EdgeInsets.only(bottom: 8),
                              child: Text('${credit.playerName}: ${credit.points}'),
                            ),
                          ),
                          const Divider(),
                          Text('${l10n.totalPointsLabel}: ${_result!.totalPoints}'),
                        ],
                      ),
                    ),
                  ),
                ],
              ],
            ),
          );
        },
      ),
    );
  }

  FixtureSummary? _selectedFixture(List<FixtureSummary> fixtures) {
    for (final fixture in fixtures) {
      if (fixture.id == _selectedFixtureId) {
        return fixture;
      }
    }
    return null;
  }

  String _friendlyError(AppLocalizations l10n, ApiException error) {
    return switch (error.statusCode) {
      403 => l10n.staffForbiddenError,
      409 => l10n.scanAlreadyUsedError,
      422 => _scanValidationError(l10n, error.message),
      _ => error.message.isEmpty ? l10n.genericError : error.message,
    };
  }

  String _scanValidationError(AppLocalizations l10n, String message) {
    final lower = message.toLowerCase();

    if (lower.contains('expired qr') || lower.contains('invalid or expired')) {
      return l10n.scanInvalidQrError;
    }
    if (lower.contains('not open for scanning')) {
      return l10n.scanFixtureClosedError;
    }
    if (lower.contains('no linked player')) {
      return l10n.scanNoLinkedPlayerError;
    }
    return l10n.scanValidationError;
  }
}
