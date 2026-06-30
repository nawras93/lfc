import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class LocaleController extends Notifier<Locale> {
  @override
  Locale build() => const Locale('en');

  void toggle() {
    state = state.languageCode == 'ar' ? const Locale('en') : const Locale('ar');
  }
}
