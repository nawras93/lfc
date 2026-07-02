import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:qr_flutter/qr_flutter.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../providers.dart';
import '../../../theme/app_theme.dart';
import '../models/scan_token.dart';

class QrScreen extends ConsumerStatefulWidget {
  const QrScreen({super.key});

  @override
  ConsumerState<QrScreen> createState() => _QrScreenState();
}

class _QrScreenState extends ConsumerState<QrScreen>
    with WidgetsBindingObserver {
  ScanToken? _token;
  Timer? _timer;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _refresh();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _timer?.cancel();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      _refresh();
    }
  }

  Future<void> _refresh() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final token = await ref
          .read(parentScanRepositoryProvider)
          .fetchParentToken();
      if (mounted) {
        setState(() {
          _token = token;
          _loading = false;
        });
        _timer?.cancel();
        _scheduleTick();
      }
    } catch (error) {
      if (mounted) {
        setState(() {
          _loading = false;
          _error = error.toString().replaceFirst('Exception: ', '');
        });
      }
    }
  }

  void _scheduleTick() {
    _timer = Timer.periodic(const Duration(seconds: 1), (_) {
      final token = _token;
      if (!mounted || token == null) {
        return;
      }

      if (DateTime.now().isAfter(token.expiresAt.toLocal())) {
        _refresh();
      } else {
        setState(() {});
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final theme = Theme.of(context);
    final palette = context.lfc;
    final token = _token;
    final remaining = token == null
        ? 0
        : token.expiresAt
              .toLocal()
              .difference(DateTime.now())
              .inSeconds
              .clamp(0, 999);

    if (_loading) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(32),
          child: CircularProgressIndicator(),
        ),
      );
    }

    if (_error != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                Icons.qr_code_2_outlined,
                size: 40,
                color: theme.colorScheme.onSurfaceVariant,
              ),
              const SizedBox(height: 14),
              Text(_error!, textAlign: TextAlign.center),
              const SizedBox(height: 18),
              FilledButton(onPressed: _refresh, child: Text(l10n.retryButton)),
            ],
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _refresh,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 28),
        children: [
          Text(
            l10n.qrScreenTitle,
            style: theme.textTheme.headlineSmall,
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 8),
          Text(
            l10n.qrScreenSubtitle,
            textAlign: TextAlign.center,
            style: theme.textTheme.bodyMedium?.copyWith(
              color: theme.colorScheme.onSurfaceVariant,
            ),
          ),
          const SizedBox(height: 20),
          // The pass itself — always on a light card so the QR scans reliably
          // even in dark mode.
          Center(
            child: Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(28),
                border: Border.all(
                  color: palette.gold.withValues(alpha: 0.55),
                  width: 1.5,
                ),
                boxShadow: [
                  BoxShadow(
                    color: LfcColors.navy900.withValues(alpha: 0.18),
                    blurRadius: 24,
                    offset: const Offset(0, 10),
                  ),
                ],
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (token != null)
                    QrImageView(
                      key: const Key('parent-qr'),
                      data: token.token,
                      size: 200,
                      backgroundColor: Colors.white,
                      eyeStyle: const QrEyeStyle(
                        eyeShape: QrEyeShape.square,
                        color: LfcColors.navy700,
                      ),
                      dataModuleStyle: const QrDataModuleStyle(
                        dataModuleShape: QrDataModuleShape.square,
                        color: LfcColors.navy800,
                      ),
                    ),
                  const SizedBox(height: 14),
                  _CountdownBar(
                    remaining: remaining,
                    label: l10n.qrExpiresIn(remaining),
                    palette: palette,
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 20),
          FilledButton(
            onPressed: _refresh,
            style: FilledButton.styleFrom(
              backgroundColor: theme.colorScheme.secondaryContainer,
              foregroundColor: theme.colorScheme.onSecondaryContainer,
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.refresh, size: 20),
                const SizedBox(width: 8),
                Text(l10n.refreshQrButton),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _CountdownBar extends StatelessWidget {
  const _CountdownBar({
    required this.remaining,
    required this.label,
    required this.palette,
  });

  final int remaining;
  final String label;
  final LfcPalette palette;

  @override
  Widget build(BuildContext context) {
    // Tokens refresh on a short rotating window; show progress out of 30s.
    final fraction = (remaining / 30).clamp(0.0, 1.0);
    return Column(
      children: [
        Text(
          label,
          key: const Key('qr-countdown'),
          style: TextStyle(
            fontFamily: 'Changa',
            fontWeight: FontWeight.w700,
            fontSize: 16,
            color: LfcColors.navy700,
          ),
        ),
        const SizedBox(height: 10),
        ClipRRect(
          borderRadius: BorderRadius.circular(999),
          child: LinearProgressIndicator(
            value: fraction,
            minHeight: 6,
            backgroundColor: LfcColors.navy100,
            valueColor: AlwaysStoppedAnimation<Color>(palette.gold),
          ),
        ),
      ],
    );
  }
}
