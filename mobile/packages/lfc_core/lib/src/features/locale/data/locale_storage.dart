import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class LocaleStorage {
  LocaleStorage(this._storage);

  static const localeKey = 'selected_locale';

  final FlutterSecureStorage _storage;

  Future<void> writeLocale(Locale locale) {
    return _storage.write(key: localeKey, value: locale.languageCode);
  }

  Future<Locale?> readLocale() async {
    final code = await _storage.read(key: localeKey);
    if (code == null || code.isEmpty) {
      return null;
    }

    return Locale(code);
  }
}
