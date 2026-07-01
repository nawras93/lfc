import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers.dart';

class LocaleController extends Notifier<Locale> {
  @override
  Locale build() {
    Future<void>.microtask(_restore);

    return const Locale('en');
  }

  Future<void> setLocale(Locale locale) async {
    state = locale;
    await ref.read(localeStorageProvider).writeLocale(locale);
  }

  Future<void> toggle() async {
    final next = state.languageCode == 'ar'
        ? const Locale('en')
        : const Locale('ar');
    await setLocale(next);
  }

  Future<void> _restore() async {
    final saved = await ref.read(localeStorageProvider).readLocale();
    if (saved != null) {
      state = saved;
    }
  }
}
