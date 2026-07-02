import 'package:demo_app_one/app.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('demo_app_one builds', (tester) async {
    await tester.pumpWidget(const DemoAppOne());
    await tester.pump();

    expect(tester.takeException(), isNull);
  });
}
