import 'package:dio/dio.dart';

import '../../../core/api/api_exception.dart';
import '../../../core/api/api_response.dart';
import '../models/player_summary.dart';
import '../models/point_history_entry.dart';

class PlayerRepository {
  PlayerRepository(this._dio);

  final Dio _dio;

  Future<List<PlayerSummary>> fetchPlayers() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/players');
      final data = response.data?['data'] as List<dynamic>? ?? const [];

      return data
          .whereType<Map<String, dynamic>>()
          .map(PlayerSummary.fromJson)
          .toList();
    } catch (error) {
      throw ApiException.fromObject(error);
    }
  }

  Future<PlayerSummary> fetchPlayer(int playerId) async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/players/$playerId');
      return PlayerSummary.fromJson(unwrapData(response.data ?? const <String, dynamic>{}));
    } catch (error) {
      throw ApiException.fromObject(error);
    }
  }

  Future<List<PointHistoryEntry>> fetchPlayerTransactions(int playerId) async {
    return _fetchTransactions('/players/$playerId/transactions');
  }

  Future<List<PointHistoryEntry>> fetchAccountTransactions() async {
    return _fetchTransactions('/me/transactions');
  }

  Future<List<PointHistoryEntry>> _fetchTransactions(String path) async {
    try {
      final response = await _dio.get<Map<String, dynamic>>(path);
      final data = response.data?['data'] as List<dynamic>? ?? const [];

      return data
          .whereType<Map<String, dynamic>>()
          .map(PointHistoryEntry.fromJson)
          .toList();
    } catch (error) {
      throw ApiException.fromObject(error);
    }
  }
}
