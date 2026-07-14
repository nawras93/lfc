import 'package:flutter/widgets.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lfc_core/lfc_core.dart';

import 'brand.dart';
import 'config.dart';

class DemoAppOne extends StatelessWidget {
  const DemoAppOne({super.key});

  @override
  Widget build(BuildContext context) {
    return ProviderScope(
      overrides: [
        brandProvider.overrideWithValue(demoOneBrand),
        appConfigProvider.overrideWith(
          (ref) => AppConfig.fromEnvironment(
            defaultBaseUrl: demoOneApiBaseUrl,
            appKey: 'app_one',
          ),
        ),
      ],
      child: const CoreApp(),
    );
  }
}
