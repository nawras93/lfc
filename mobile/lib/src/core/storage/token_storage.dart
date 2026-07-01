import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class TokenStorage {
  TokenStorage(this._storage);

  static const tokenKey = 'auth_token';

  final FlutterSecureStorage _storage;

  Future<void> writeToken(String token) =>
      _storage.write(key: tokenKey, value: token);

  Future<String?> readToken() => _storage.read(key: tokenKey);

  Future<void> clearToken() => _storage.delete(key: tokenKey);
}
