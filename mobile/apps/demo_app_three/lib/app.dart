import 'package:flutter/widgets.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lfc_core/lfc_core.dart';

import 'brand.dart';
import 'config.dart';

class DemoAppThree extends StatelessWidget {
  const DemoAppThree({super.key});

  @override
  Widget build(BuildContext context) {
    return ProviderScope(
      overrides: [
        brandProvider.overrideWithValue(demoThreeBrand),
        appConfigProvider.overrideWith(
          (ref) =>
              AppConfig.fromEnvironment(defaultBaseUrl: demoThreeApiBaseUrl),
        ),
      ],
      child: const CoreApp(),
    );
  }
}
