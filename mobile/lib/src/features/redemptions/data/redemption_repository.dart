import 'package:dio/dio.dart';

import '../../../core/api/api_exception.dart';
import '../../../core/api/api_response.dart';
import '../models/redemption_history_item.dart';
import '../models/redemption_item_summary.dart';
import '../models/redemption_voucher.dart';

class RedemptionRepository {
  RedemptionRepository(this._dio);

  final Dio _dio;

  Future<List<RedemptionItemSummary>> fetchItems() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>(
        '/redemption-items',
      );
      final data = response.data?['data'] as List<dynamic>? ?? const [];

      return data
          .whereType<Map<String, dynamic>>()
          .map(RedemptionItemSummary.fromJson)
          .toList();
    } catch (error) {
      throw ApiException.fromObject(error);
    }
  }

  Future<RedemptionVoucher> redeem({
    required int redemptionItemId,
    int? playerId,
  }) async {
    try {
      final payload = <String, dynamic>{'redemption_item_id': redemptionItemId};
      if (playerId != null) {
        payload['player_id'] = playerId;
      }

      final response = await _dio.post<Map<String, dynamic>>(
        '/redemptions',
        data: payload,
      );
      return RedemptionVoucher.fromJson(
        unwrapData(response.data ?? const <String, dynamic>{}),
      );
    } catch (error) {
      throw ApiException.fromObject(error);
    }
  }

  Future<List<RedemptionHistoryItem>> fetchHistory() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/redemptions');
      final data = response.data?['data'] as List<dynamic>? ?? const [];

      return data
          .whereType<Map<String, dynamic>>()
          .map(RedemptionHistoryItem.fromJson)
          .toList();
    } catch (error) {
      throw ApiException.fromObject(error);
    }
  }
}
