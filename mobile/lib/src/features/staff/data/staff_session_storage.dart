import 'dart:convert';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../../auth/models/staff_user.dart';

class StaffSessionStorage {
  StaffSessionStorage(this._storage);

  static const _tokenKey = 'staff_auth_token';
  static const _userKey = 'staff_user';

  final FlutterSecureStorage _storage;

  Future<void> writeSession({
    required String token,
    required StaffUser user,
  }) async {
    final payload = <String, Object>{
      'id': user.id,
      'name': user.name,
      'email': user.email,
    };

    await _storage.write(key: _tokenKey, value: token);
    await _storage.write(key: _userKey, value: jsonEncode(payload));
  }

  Future<String?> readToken() => _storage.read(key: _tokenKey);

  Future<StaffUser?> readUser() async {
    final raw = await _storage.read(key: _userKey);
    if (raw == null || raw.isEmpty) {
      return null;
    }

    final decoded = jsonDecode(raw);
    if (decoded is! Map<String, dynamic>) {
      return null;
    }

    return StaffUser.fromJson(decoded);
  }

  Future<void> clear() async {
    await _storage.delete(key: _tokenKey);
    await _storage.delete(key: _userKey);
  }
}
