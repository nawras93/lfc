import 'package:flutter_test/flutter_test.dart';
import 'package:lfc_core/src/features/scan/models/scan_result.dart';

void main() {
  test('parses member discount scan payload', () {
    final result = ScanResult.fromJson(const {
      'scan_id': 12,
      'discount_added_percent': 0.5,
      'discount_percent': 3.0,
      'discount_cap_percent': 10.0,
    });

    expect(result.scanId, 12);
    expect(result.credited, isEmpty);
    expect(result.totalPoints, 0);
    expect(result.discountAddedPercent, 0.5);
    expect(result.discountPercent, 3.0);
    expect(result.discountCapPercent, 10.0);
    expect(result.isDiscountScan, isTrue);
  });

  test('parses player points scan payload without discount fields', () {
    final result = ScanResult.fromJson(const {
      'scan_id': 9,
      'credited': [
        {'player_id': 1, 'player_name': 'Ahmed Ali', 'points': 25},
      ],
      'total_points': 25,
    });

    expect(result.scanId, 9);
    expect(result.credited, hasLength(1));
    expect(result.totalPoints, 25);
    expect(result.discountAddedPercent, isNull);
    expect(result.discountPercent, isNull);
    expect(result.discountCapPercent, isNull);
    expect(result.isDiscountScan, isFalse);
  });
}
