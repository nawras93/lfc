import 'package:dio/dio.dart';

enum ApiErrorKind { unauthorized, validation, network, server, unknown }

class ApiException implements Exception {
  ApiException({
    required this.message,
    required this.kind,
    this.statusCode,
    this.fieldErrors = const {},
  });

  factory ApiException.fromObject(Object error) {
    if (error is ApiException) {
      return error;
    }

    if (error is DioException) {
      if (error.error is ApiException) {
        return error.error! as ApiException;
      }

      return ApiException.fromDioException(error);
    }

    return ApiException(
      message: 'Something went wrong. Please try again.',
      kind: ApiErrorKind.unknown,
    );
  }

  factory ApiException.fromDioException(DioException error) {
    final responseData = error.response?.data;
    final message =
        responseData is Map<String, dynamic> &&
            responseData['message'] is String
        ? responseData['message'] as String
        : error.message ?? 'Something went wrong. Please try again.';
    final statusCode = error.response?.statusCode;
    final fieldErrors = <String, List<String>>{};

    if (responseData is Map<String, dynamic> &&
        responseData['errors'] is Map<String, dynamic>) {
      final rawErrors = responseData['errors'] as Map<String, dynamic>;
      for (final entry in rawErrors.entries) {
        final value = entry.value;
        if (value is List) {
          fieldErrors[entry.key] = value
              .map((item) => item.toString())
              .toList();
        }
      }
    }

    if (statusCode == 401) {
      return ApiException(
        message: message,
        kind: ApiErrorKind.unauthorized,
        statusCode: statusCode,
      );
    }

    if (statusCode == 422) {
      return ApiException(
        message: message,
        kind: ApiErrorKind.validation,
        statusCode: statusCode,
        fieldErrors: fieldErrors,
      );
    }

    if (statusCode != null && statusCode >= 500) {
      return ApiException(
        message: message,
        kind: ApiErrorKind.server,
        statusCode: statusCode,
      );
    }

    if (error.type == DioExceptionType.connectionError ||
        error.type == DioExceptionType.connectionTimeout ||
        error.type == DioExceptionType.receiveTimeout ||
        error.type == DioExceptionType.sendTimeout) {
      return ApiException(
        message: message,
        kind: ApiErrorKind.network,
        statusCode: statusCode,
      );
    }

    return ApiException(
      message: message,
      kind: ApiErrorKind.unknown,
      statusCode: statusCode,
      fieldErrors: fieldErrors,
    );
  }

  final String message;
  final ApiErrorKind kind;
  final int? statusCode;
  final Map<String, List<String>> fieldErrors;

  String? firstErrorFor(String field) => fieldErrors[field]?.first;

  @override
  String toString() => message;
}
