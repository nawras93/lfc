import 'package:dio/dio.dart';

import '../../../core/api/api_exception.dart';
import '../models/offer_summary.dart';

class OffersRepository {
  OffersRepository(this._dio);

  final Dio _dio;

  Future<List<OfferSummary>> fetchOffers() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/offers');
      final data = response.data?['data'] as List<dynamic>? ?? const [];

      return data
          .whereType<Map<String, dynamic>>()
          .map(OfferSummary.fromJson)
          .toList();
    } catch (error) {
      throw ApiException.fromObject(error);
    }
  }
}
