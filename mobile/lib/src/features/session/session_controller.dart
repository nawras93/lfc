import 'dart:async';

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/api/api_exception.dart';
import '../../providers.dart';
import '../auth/models/account.dart';

enum SessionStatus { unknown, authenticated, unauthenticated }

class SessionState {
  const SessionState({
    required this.status,
    this.account,
    this.token,
    this.isBusy = false,
  });

  const SessionState.unknown() : this(status: SessionStatus.unknown);

  final SessionStatus status;
  final Account? account;
  final String? token;
  final bool isBusy;

  SessionState copyWith({
    SessionStatus? status,
    Account? account,
    String? token,
    bool? isBusy,
    bool clearAccount = false,
    bool clearToken = false,
  }) {
    return SessionState(
      status: status ?? this.status,
      account: clearAccount ? null : (account ?? this.account),
      token: clearToken ? null : (token ?? this.token),
      isBusy: isBusy ?? this.isBusy,
    );
  }
}

class SessionController extends Notifier<SessionState> {
  StreamSubscription<void>? _unauthorizedSubscription;

  @override
  SessionState build() {
    final sessionEvents = ref.watch(sessionEventsProvider);

    _unauthorizedSubscription ??= sessionEvents.unauthorizedStream.listen((_) {
      state = const SessionState(status: SessionStatus.unauthenticated);
    });

    ref.onDispose(() {
      _unauthorizedSubscription?.cancel();
    });

    Future<void>.microtask(_bootstrap);

    return const SessionState.unknown();
  }

  Future<void> _bootstrap() async {
    final repository = ref.read(authRepositoryProvider);
    final token = await repository.readStoredToken();

    if (token == null) {
      state = const SessionState(status: SessionStatus.unauthenticated);
      return;
    }

    state = state.copyWith(isBusy: true, token: token);

    try {
      final account = await repository.getMe();
      state = SessionState(
        status: SessionStatus.authenticated,
        token: token,
        account: account,
      );
    } on ApiException catch (error) {
      if (error.kind == ApiErrorKind.unauthorized) {
        await clearSession();
        return;
      }
      state = const SessionState(status: SessionStatus.unauthenticated);
    }
  }

  Future<void> login({
    required String email,
    required String password,
  }) async {
    state = state.copyWith(isBusy: true);
    final repository = ref.read(authRepositoryProvider);

    try {
      final result = await repository.login(email: email, password: password);
      final account = await repository.getMe();
      state = SessionState(
        status: SessionStatus.authenticated,
        token: result.token,
        account: account,
      );
    } catch (error) {
      state = const SessionState(status: SessionStatus.unauthenticated);
      rethrow;
    }
  }

  Future<void> acceptInvite({
    required String token,
    required String password,
  }) async {
    state = state.copyWith(isBusy: true);
    final repository = ref.read(authRepositoryProvider);

    try {
      final result = await repository.acceptInvite(token: token, password: password);
      final account = await repository.getMe();
      state = SessionState(
        status: SessionStatus.authenticated,
        token: result.token,
        account: account,
      );
    } catch (error) {
      state = const SessionState(status: SessionStatus.unauthenticated);
      rethrow;
    }
  }

  Future<void> logout() async {
    state = state.copyWith(isBusy: true);
    await ref.read(authRepositoryProvider).logout();
    state = const SessionState(status: SessionStatus.unauthenticated);
  }

  Future<void> clearSession() async {
    await ref.read(authRepositoryProvider).clearToken();
    state = const SessionState(status: SessionStatus.unauthenticated);
  }
}
