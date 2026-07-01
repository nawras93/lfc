import 'dart:async';

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/api/api_exception.dart';
import '../../providers.dart';
import '../auth/models/staff_user.dart';

enum StaffSessionStatus { hidden, login, authenticated }

class StaffSessionState {
  const StaffSessionState({
    required this.status,
    this.user,
    this.token,
    this.isBusy = false,
  });

  const StaffSessionState.hidden() : this(status: StaffSessionStatus.hidden);

  final StaffSessionStatus status;
  final StaffUser? user;
  final String? token;
  final bool isBusy;

  StaffSessionState copyWith({
    StaffSessionStatus? status,
    StaffUser? user,
    String? token,
    bool? isBusy,
    bool clearUser = false,
    bool clearToken = false,
  }) {
    return StaffSessionState(
      status: status ?? this.status,
      user: clearUser ? null : (user ?? this.user),
      token: clearToken ? null : (token ?? this.token),
      isBusy: isBusy ?? this.isBusy,
    );
  }
}

class StaffSessionController extends Notifier<StaffSessionState> {
  StreamSubscription<void>? _unauthorizedSubscription;

  @override
  StaffSessionState build() {
    final events = ref.watch(staffSessionEventsProvider);

    _unauthorizedSubscription ??= events.unauthorizedStream.listen((_) {
      state = const StaffSessionState(status: StaffSessionStatus.login);
    });

    ref.onDispose(() {
      _unauthorizedSubscription?.cancel();
    });

    Future<void>.microtask(_bootstrap);

    return const StaffSessionState.hidden();
  }

  Future<void> _bootstrap() async {
    final storage = ref.read(staffSessionStorageProvider);
    final token = await storage.readToken();
    final user = await storage.readUser();

    if (token != null && user != null) {
      state = StaffSessionState(
        status: StaffSessionStatus.authenticated,
        token: token,
        user: user,
      );
    }
  }

  void showLogin() {
    state = state.copyWith(status: StaffSessionStatus.login, isBusy: false);
  }

  void hideLogin() {
    state = const StaffSessionState.hidden();
  }

  Future<void> login({
    required String email,
    required String password,
  }) async {
    state = state.copyWith(status: StaffSessionStatus.login, isBusy: true);

    final repository = ref.read(authRepositoryProvider);
    final storage = ref.read(staffSessionStorageProvider);

    try {
      final response = await repository.staffLogin(email: email, password: password);
      await storage.writeSession(token: response.token, user: response.user);
      state = StaffSessionState(
        status: StaffSessionStatus.authenticated,
        token: response.token,
        user: response.user,
      );
    } on ApiException {
      state = state.copyWith(status: StaffSessionStatus.login, isBusy: false);
      rethrow;
    }
  }

  Future<void> logout() async {
    await ref.read(staffSessionStorageProvider).clear();
    state = const StaffSessionState.hidden();
  }
}
