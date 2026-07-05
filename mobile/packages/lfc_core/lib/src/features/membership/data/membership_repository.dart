import 'package:dio/dio.dart';

import '../../../core/api/api_exception.dart';
import '../models/membership_card.dart';

class MembershipRepository {
  MembershipRepository(this._dio);

  final Dio _dio;

  Future<MembershipCard?> fetchMembership() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/me/benefits');
      final data = response.data?['data'];
      if (data == null) {
        return null;
      }

      return MembershipCard.fromJson(data as Map<String, dynamic>);
    } catch (error) {
      throw ApiException.fromObject(error);
    }
  }
}
