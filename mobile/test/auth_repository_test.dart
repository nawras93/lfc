import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/src/core/api/api_exception.dart';
import 'package:mobile/src/core/storage/token_storage.dart';
import 'package:mobile/src/features/auth/data/auth_repository.dart';

import 'helpers/fakes.dart';

void main() {
  group('AuthRepository', () {
    test('login stores the token', () async {
      final storage = MemorySecureStorage();
      final dio = Dio(BaseOptions(baseUrl: 'http://localhost:8000/api/v1'));
      dio.httpClientAdapter = FakeHttpClientAdapter((options) async {
        expect(options.path, '/auth/login');
        return jsonResponseBody({
          'token': 'demo-token',
          'parent': {
            'id': 1,
            'name': 'Parent One',
            'email': 'parent@example.com',
            'phone': null,
            'whatsapp': null,
            'is_vvip': false,
            'account_type': 'parent',
            'account_balance': 75,
          },
        });
      });

      final repository = AuthRepository(
        dio: dio,
        tokenStorage: TokenStorage(storage),
      );

      final response = await repository.login(
        email: 'parent@example.com',
        password: 'secret123',
      );

      expect(response.token, 'demo-token');
      expect(await storage.read(key: TokenStorage.tokenKey), 'demo-token');
    });

    test('getMe parses account data from wrapped payload', () async {
      final dio = Dio(BaseOptions(baseUrl: 'http://localhost:8000/api/v1'));
      dio.httpClientAdapter = FakeHttpClientAdapter((options) async {
        expect(options.path, '/me');
        return jsonResponseBody({
          'data': {
            'id': 9,
            'name': 'VVIP User',
            'email': 'vip@example.com',
            'phone': '123',
            'whatsapp': '456',
            'is_vvip': true,
            'account_type': 'vvip_client',
            'account_balance': 500,
          },
        });
      });

      final repository = AuthRepository(
        dio: dio,
        tokenStorage: TokenStorage(MemorySecureStorage()),
      );

      final account = await repository.getMe();

      expect(account.name, 'VVIP User');
      expect(account.isVvip, isTrue);
      expect(account.accountBalance, 500);
    });

    test('401 maps to unauthorized exception', () async {
      final dio = Dio(BaseOptions(baseUrl: 'http://localhost:8000/api/v1'));
      dio.httpClientAdapter = FakeHttpClientAdapter((_) async {
        return jsonResponseBody({
          'message': 'Unauthenticated.',
        }, statusCode: 401);
      });

      final repository = AuthRepository(
        dio: dio,
        tokenStorage: TokenStorage(MemorySecureStorage()),
      );

      await expectLater(
        repository.getMe(),
        throwsA(
          isA<ApiException>().having(
            (error) => error.kind,
            'kind',
            ApiErrorKind.unauthorized,
          ),
        ),
      );
    });

    test('422 surfaces field errors', () async {
      final dio = Dio(BaseOptions(baseUrl: 'http://localhost:8000/api/v1'));
      dio.httpClientAdapter = FakeHttpClientAdapter((_) async {
        return jsonResponseBody({
          'message': 'The given data was invalid.',
          'errors': {
            'email': ['The provided credentials are incorrect.'],
          },
        }, statusCode: 422);
      });

      final repository = AuthRepository(
        dio: dio,
        tokenStorage: TokenStorage(MemorySecureStorage()),
      );

      await expectLater(
        repository.login(email: 'bad@example.com', password: 'bad'),
        throwsA(
          isA<ApiException>()
              .having((error) => error.kind, 'kind', ApiErrorKind.validation)
              .having(
                (error) => error.firstErrorFor('email'),
                'email error',
                'The provided credentials are incorrect.',
              ),
        ),
      );
    });
  });
}
