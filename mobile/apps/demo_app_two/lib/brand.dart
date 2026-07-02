import 'package:flutter/material.dart';
import 'package:lfc_core/lfc_core.dart';

const demoTwoBrand = Brand(
  appTitle: 'Demo Two',
  palette: BrandPalette(
    primary: Color(0xFF284F73),
    primaryMid: Color(0xFF3A678F),
    primaryOnDark: Color(0xFF537EA4),
    primaryStrong: Color(0xFF1D3B56),
    primarySoft: Color(0xFF7FA5BF),
    primaryTint: Color(0xFFD8E6F0),
    gold: Color(0xFFC48C4D),
    goldDeep: Color(0xFF8E5F26),
    goldBright: Color(0xFFE4B97E),
    heroStart: Color(0xFF173149),
    heroEnd: Color(0xFF284F73),
    success: Color(0xFF2E7D5B),
    successBright: Color(0xFF7FD0A8),
  ),
  wordmark: BrandWordmark(
    title: 'DEMO TWO',
    subtitle: 'MOBILE EXPERIENCE',
  ),
  logo: AssetImage('assets/images/lusail_crest.png'),
  heroLogo: AssetImage('assets/images/lusail_logo.png'),
);
