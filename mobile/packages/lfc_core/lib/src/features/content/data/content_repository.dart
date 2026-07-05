import 'package:dio/dio.dart';

import '../../../core/api/api_exception.dart';
import '../../../core/api/api_response.dart';
import '../models/match_summary.dart';
import '../models/news_article.dart';
import '../models/news_summary.dart';
import '../models/standing_row.dart';

class ContentRepository {
  ContentRepository(this._dio);

  final Dio _dio;

  Future<List<NewsSummary>> fetchNews() async {
    return _fetchList('/content/news', NewsSummary.fromJson);
  }

  Future<NewsArticle> fetchNewsArticle(int id) async {
    try {
      final response = await _dio.get<Map<String, dynamic>>(
        '/content/news/$id',
      );
      return NewsArticle.fromJson(
        unwrapData(response.data ?? const <String, dynamic>{}),
      );
    } catch (error) {
      throw ApiException.fromObject(error);
    }
  }

  Future<List<MatchSummary>> fetchFixtures() async {
    return _fetchList('/content/fixtures', MatchSummary.fromJson);
  }

  Future<List<MatchSummary>> fetchResults() async {
    return _fetchList('/content/results', MatchSummary.fromJson);
  }

  Future<List<StandingRow>> fetchStandings() async {
    return _fetchList('/content/standings', StandingRow.fromJson);
  }

  Future<List<T>> _fetchList<T>(
    String path,
    T Function(Map<String, dynamic>) fromJson,
  ) async {
    try {
      final response = await _dio.get<Map<String, dynamic>>(path);
      final data = response.data?['data'] as List<dynamic>? ?? const [];

      return data.whereType<Map<String, dynamic>>().map(fromJson).toList();
    } catch (error) {
      throw ApiException.fromObject(error);
    }
  }
}
