import 'package:flutter/material.dart';
import 'package:lfc_core/lfc_core.dart';

const demoThreeBrand = Brand(
  appTitle: 'Demo Three',
  palette: BrandPalette(
    primary: Color(0xFF4B3A6A),
    primaryMid: Color(0xFF624D86),
    primaryOnDark: Color(0xFF7A65A1),
    primaryStrong: Color(0xFF322347),
    primarySoft: Color(0xFFA89BC4),
    primaryTint: Color(0xFFE8E1F1),
    gold: Color(0xFFCC9A45),
    goldDeep: Color(0xFF8F6822),
    goldBright: Color(0xFFE8C57A),
    heroStart: Color(0xFF322347),
    heroEnd: Color(0xFF4B3A6A),
    success: Color(0xFF2E7D5B),
    successBright: Color(0xFF7FD0A8),
  ),
  wordmark: BrandWordmark(
    title: 'DEMO THREE',
    subtitle: 'MOBILE EXPERIENCE',
  ),
  logo: AssetImage('assets/images/lusail_crest.png'),
  heroLogo: AssetImage('assets/images/lusail_logo.png'),
);
