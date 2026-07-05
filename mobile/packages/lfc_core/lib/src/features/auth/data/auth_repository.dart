import 'package:dio/dio.dart';

import '../../../core/api/api_exception.dart';
import '../../../core/api/api_response.dart';
import '../../../core/storage/token_storage.dart';
import '../models/account.dart';
import '../models/login_response.dart';
import '../models/staff_login_response.dart';

class AuthRepository {
  AuthRepository({required this.dio, required this.tokenStorage});

  final Dio dio;
  final TokenStorage tokenStorage;

  Future<LoginResponse> login({
    required String email,
    required String password,
  }) async {
    try {
      final response = await dio.post<Map<String, dynamic>>(
        '/auth/login',
        data: {'email': email, 'password': password},
      );
      final payload = response.data ?? const <String, dynamic>{};
      final result = LoginResponse.fromJson(payload);
      await tokenStorage.writeToken(result.token);
      return result;
    } catch (error) {
      throw ApiException.fromObject(error);
    }
  }

  Future<LoginResponse> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
  }) async {
    try {
      final response = await dio.post<Map<String, dynamic>>(
        '/auth/register',
        data: {
          'name': name,
          'email': email,
          'password': password,
          'password_confirmation': passwordConfirmation,
          if (phone != null && phone.isNotEmpty) 'phone': phone,
        },
      );
      final payload = response.data ?? const <String, dynamic>{};
      final result = LoginResponse.fromJson(payload);
      await tokenStorage.writeToken(result.token);
      return result;
    } catch (error) {
      throw ApiException.fromObject(error);
    }
  }

  Future<LoginResponse> acceptInvite({
    required String token,
    required String password,
  }) async {
    try {
      final response = await dio.post<Map<String, dynamic>>(
        '/auth/accept-invite',
        data: {'token': token, 'password': password},
      );
      final payload = response.data ?? const <String, dynamic>{};
      final result = LoginResponse.fromJson(payload);
      await tokenStorage.writeToken(result.token);
      return result;
    } catch (error) {
      throw ApiException.fromObject(error);
    }
  }

  Future<Account> getMe() async {
    try {
      final response = await dio.get<Map<String, dynamic>>('/me');
      final payload = unwrapData(response.data ?? const <String, dynamic>{});
      return Account.fromJson(payload);
    } catch (error) {
      throw ApiException.fromObject(error);
    }
  }

  Future<StaffLoginResponse> staffLogin({
    required String email,
    required String password,
  }) async {
    try {
      final response = await dio.post<Map<String, dynamic>>(
        '/staff/login',
        data: {'email': email, 'password': password},
      );
      return StaffLoginResponse.fromJson(
        response.data ?? const <String, dynamic>{},
      );
    } catch (error) {
      throw ApiException.fromObject(error);
    }
  }

  Future<void> logout() async {
    try {
      await dio.post<void>('/auth/logout');
    } catch (error) {
      final apiError = ApiException.fromObject(error);
      if (apiError.kind != ApiErrorKind.unauthorized) {
        rethrow;
      }
    } finally {
      await clearToken();
    }
  }

  Future<String?> readStoredToken() => tokenStorage.readToken();

  Future<void> clearToken() => tokenStorage.clearToken();
}
