import 'package:flutter_test/flutter_test.dart';
import 'package:lfc_core/src/core/api/api_exception.dart';

void main() {
  test('ApiException.toString returns the message', () {
    expect(
      ApiException(message: 'x', kind: ApiErrorKind.unknown).toString(),
      'x',
    );
  });
}
