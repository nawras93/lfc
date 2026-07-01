import 'package:dio/dio.dart';

import '../../../core/api/api_exception.dart';
import '../models/fixture_summary.dart';
import '../models/scan_result.dart';
import '../models/scan_token.dart';

class ScanRepository {
  ScanRepository(this._dio);

  final Dio _dio;

  Future<ScanToken> fetchParentToken() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/scan-token');
      return ScanToken.fromJson(response.data ?? const <String, dynamic>{});
    } catch (error) {
      throw ApiException.fromObject(error);
    }
  }

  Future<List<FixtureSummary>> fetchOpenFixtures() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/staff/fixtures');
      final data = response.data?['data'] as List<dynamic>? ?? const [];

      return data
          .whereType<Map<String, dynamic>>()
          .map(FixtureSummary.fromJson)
          .toList();
    } catch (error) {
      throw ApiException.fromObject(error);
    }
  }

  Future<ScanResult> submitScan({
    required int fixtureId,
    required String token,
  }) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(
        '/scan',
        data: {
          'fixture_id': fixtureId,
          'token': token,
        },
      );
      return ScanResult.fromJson(response.data ?? const <String, dynamic>{});
    } catch (error) {
      throw ApiException.fromObject(error);
    }
  }
}
