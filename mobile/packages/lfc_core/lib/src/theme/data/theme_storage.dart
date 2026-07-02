import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class ThemeStorage {
  ThemeStorage(this._storage);

  static const themeKey = 'selected_theme_mode';

  final FlutterSecureStorage _storage;

  Future<void> writeThemeMode(ThemeMode mode) {
    return _storage.write(key: themeKey, value: mode.name);
  }

  Future<ThemeMode?> readThemeMode() async {
    final value = await _storage.read(key: themeKey);
    return switch (value) {
      'light' => ThemeMode.light,
      'dark' => ThemeMode.dark,
      'system' => ThemeMode.system,
      _ => null,
    };
  }
}
