import 'package:flutter/widgets.dart';
import 'package:lfc_core/lfc_core.dart';

const testBrand = Brand(
  appTitle: 'LFC Test',
  palette: BrandPalette.lusail,
  wordmark: BrandWordmark(title: 'TEST', subtitle: 'MOBILE APP'),
  logo: AssetImage('assets/flags/qa.png', package: 'lfc_core'),
  heroLogo: AssetImage('assets/flags/gb.png', package: 'lfc_core'),
);
