import 'package:demo_app_three/app.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('demo_app_three builds', (tester) async {
    await tester.pumpWidget(const DemoAppThree());
    await tester.pump();

    expect(tester.takeException(), isNull);
  });
}
