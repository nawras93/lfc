import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:lfc_core/src/features/membership/presentation/membership_helpers.dart';

void main() {
  test('membershipAccentColor parses valid hex and falls back safely', () {
    const fallback = Colors.blue;

    expect(membershipAccentColor('#C8A24A', fallback), const Color(0xFFC8A24A));
    expect(membershipAccentColor('C8A24A', fallback), const Color(0xFFC8A24A));
    expect(membershipAccentColor(null, fallback), fallback);
    expect(membershipAccentColor('garbage', fallback), fallback);
  });
}
