import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../providers.dart';

/// Holds the active [ThemeMode]. Defaults to the dark "matchday" theme and
/// persists the parent's choice, mirroring [LocaleController].
class ThemeController extends Notifier<ThemeMode> {
  @override
  ThemeMode build() {
    Future<void>.microtask(_restore);
    return ThemeMode.dark;
  }

  Future<void> setMode(ThemeMode mode) async {
    state = mode;
    await ref.read(themeStorageProvider).writeThemeMode(mode);
  }

  /// Flips between the dark and light themes.
  Future<void> toggle() async {
    await setMode(state == ThemeMode.dark ? ThemeMode.light : ThemeMode.dark);
  }

  Future<void> _restore() async {
    final saved = await ref.read(themeStorageProvider).readThemeMode();
    if (saved != null) {
      state = saved;
    }
  }
}
