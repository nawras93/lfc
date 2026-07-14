import 'package:demo_app_one/app.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:lfc_core/lfc_core.dart';

void main() {
  testWidgets('demo_app_one builds', (tester) async {
    await tester.pumpWidget(const DemoAppOne());
    await tester.pump();

    expect(tester.takeException(), isNull);
  });

  // The API scopes /staff/fixtures by X-App-Key. Drop this and app one falls back to
  // the route's app_one default — until someone changes that default, at which point
  // the scanner silently lists another app's matches.
  testWidgets('demo_app_one identifies itself as app_one', (tester) async {
    await tester.pumpWidget(const DemoAppOne());
    await tester.pump();

    final container = ProviderScope.containerOf(
      tester.element(find.byType(CoreApp)),
    );

    expect(container.read(appConfigProvider).appKey, AppKeys.appOne);
  });
}
