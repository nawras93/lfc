import 'package:flutter/widgets.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lfc_core/lfc_core.dart';

import 'brand.dart';
import 'config.dart';

class DemoAppTwo extends StatelessWidget {
  const DemoAppTwo({super.key});

  @override
  Widget build(BuildContext context) {
    return ProviderScope(
      overrides: [
        brandProvider.overrideWithValue(demoTwoBrand),
        appConfigProvider.overrideWith(
          (ref) => AppConfig.fromEnvironment(
            defaultBaseUrl: demoTwoApiBaseUrl,
            appKey: 'app_two',
          ),
        ),
      ],
      child: const SupporterApp(),
    );
  }
}
