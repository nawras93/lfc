import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:qr_flutter/qr_flutter.dart';

import '../../../../l10n/app_localizations.dart';
import '../../../providers.dart';
import '../models/scan_token.dart';

class QrScreen extends ConsumerStatefulWidget {
  const QrScreen({super.key});

  @override
  ConsumerState<QrScreen> createState() => _QrScreenState();
}

class _QrScreenState extends ConsumerState<QrScreen> with WidgetsBindingObserver {
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
      final token = await ref.read(parentScanRepositoryProvider).fetchParentToken();
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
    final token = _token;
    final remaining = token == null
        ? 0
        : token.expiresAt.toLocal().difference(DateTime.now()).inSeconds.clamp(0, 999);

    if (_loading) {
      return Center(child: Text(l10n.loadingText));
    }

    if (_error != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(_error!, textAlign: TextAlign.center),
              const SizedBox(height: 16),
              FilledButton(onPressed: _refresh, child: Text(l10n.retryButton)),
            ],
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _refresh,
      child: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                children: [
                  Text(
                    l10n.qrScreenTitle,
                    style: Theme.of(context).textTheme.titleLarge,
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 12),
                  Text(
                    l10n.qrScreenSubtitle,
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 24),
                  if (token != null)
                    QrImageView(
                      key: const Key('parent-qr'),
                      data: token.token,
                      size: 240,
                      backgroundColor: Colors.white,
                    ),
                  const SizedBox(height: 24),
                  Text(
                    l10n.qrExpiresIn(remaining),
                    key: const Key('qr-countdown'),
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                  const SizedBox(height: 16),
                  FilledButton.tonalIcon(
                    onPressed: _refresh,
                    icon: const Icon(Icons.refresh),
                    label: Text(l10n.refreshQrButton),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
