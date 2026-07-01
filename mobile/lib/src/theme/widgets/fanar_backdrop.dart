import 'package:flutter/material.dart';

/// A restrained diamond lattice echoing Lusail Stadium's triangular fanar-lamp
/// facade. Used as a faint texture behind the navy hero surfaces.
class FanarBackdrop extends StatelessWidget {
  const FanarBackdrop({
    super.key,
    required this.color,
    this.spacing = 26,
    this.strokeWidth = 1,
  });

  final Color color;
  final double spacing;
  final double strokeWidth;

  @override
  Widget build(BuildContext context) {
    return Positioned.fill(
      child: IgnorePointer(
        child: CustomPaint(
          painter: _FanarPainter(
            color: color,
            spacing: spacing,
            strokeWidth: strokeWidth,
          ),
        ),
      ),
    );
  }
}

class _FanarPainter extends CustomPainter {
  _FanarPainter({
    required this.color,
    required this.spacing,
    required this.strokeWidth,
  });

  final Color color;
  final double spacing;
  final double strokeWidth;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = strokeWidth;

    // Two families of 45° lines form a diamond lattice.
    for (double x = -size.height; x < size.width + size.height; x += spacing) {
      canvas.drawLine(
        Offset(x, 0),
        Offset(x + size.height, size.height),
        paint,
      );
      canvas.drawLine(
        Offset(x + size.height, 0),
        Offset(x, size.height),
        paint,
      );
    }
  }

  @override
  bool shouldRepaint(_FanarPainter oldDelegate) =>
      oldDelegate.color != color ||
      oldDelegate.spacing != spacing ||
      oldDelegate.strokeWidth != strokeWidth;
}
