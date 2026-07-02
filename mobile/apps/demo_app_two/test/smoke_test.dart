import 'package:demo_app_two/app.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('demo_app_two builds', (tester) async {
    await tester.pumpWidget(const DemoAppTwo());
    await tester.pump();

    expect(tester.takeException(), isNull);
  });
}
