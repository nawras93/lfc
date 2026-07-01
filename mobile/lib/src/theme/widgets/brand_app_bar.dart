import 'package:flutter/material.dart';

import '../app_theme.dart';
import 'brand_mark.dart';

/// One consistent header for every role. Shows the Lusail lockup on the leading
/// side, an optional context/role chip, and a trailing actions cluster.
class BrandAppBar extends StatelessWidget implements PreferredSizeWidget {
  const BrandAppBar({
    super.key,
    this.roleLabel,
    this.actions = const [],
    this.showBack = false,
  });

  /// Small context chip beside the wordmark, e.g. "Staff" or a screen name.
  final String? roleLabel;

  /// Trailing controls (theme/language toggles, logout, …).
  final List<Widget> actions;

  /// Show a back button instead of the brand lockup (secondary screens).
  final bool showBack;

  @override
  Size get preferredSize => const Size.fromHeight(64);

  @override
  Widget build(BuildContext context) {
    return AppBar(
      toolbarHeight: 64,
      automaticallyImplyLeading: showBack,
      titleSpacing: showBack ? 4 : 16,
      title: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Flexible(
            child: BrandMark(compact: true, showSubline: false),
          ),
          if (roleLabel != null) ...[
            const SizedBox(width: 10),
            _RoleChip(label: roleLabel!),
          ],
        ],
      ),
      actions: [...actions, const SizedBox(width: 4)],
    );
  }
}

class _RoleChip extends StatelessWidget {
  const _RoleChip({required this.label});

  final String label;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: scheme.primary.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: context.lfc.gold.withValues(alpha: 0.55)),
      ),
      child: Text(
        label.toUpperCase(),
        style: TextStyle(
          fontFamily: 'Tajawal',
          fontWeight: FontWeight.w700,
          fontSize: 10,
          letterSpacing: 1,
          color: scheme.primary,
        ),
      ),
    );
  }
}
