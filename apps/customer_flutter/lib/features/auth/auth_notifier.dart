import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/network/api_client.dart';

class UserModel {
  final int id;
  final String name;
  final String email;
  final String phone;
  final List<String> roles;
  final int? sellerId;
  final String? storeName;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.phone,
    required this.roles,
    this.sellerId,
    this.storeName,
  });

  bool get isAdmin => roles.contains('admin') || roles.contains('superadmin');
  bool get isSeller => roles.any((r) => r.startsWith('seller_')) || roles.contains('seller');
  bool get isCourier => roles.contains('courier') || roles.contains('logistics_operator');

  factory UserModel.fromJson(Map<String, dynamic> json) {
    final rawRoles = json['roles'] as List<dynamic>? ?? [];
    final seller = json['seller'] as Map<String, dynamic>?;

    return UserModel(
      id: json['id'] as int? ?? 1,
      name: json['name'] as String? ?? 'Foydalanuvchi',
      email: json['email'] as String? ?? '',
      phone: json['phone'] as String? ?? '',
      roles: rawRoles.map((r) => r.toString()).toList(),
      sellerId: seller?['id'] as int?,
      storeName: seller?['name'] as String?,
    );
  }
}

class AuthState {
  final UserModel? user;
  final bool isAuthenticated;
  final String activeRole; // 'customer', 'seller', 'courier', 'admin'
  final bool isLoading;
  final String? error;

  const AuthState({
    this.user,
    this.isAuthenticated = false,
    this.activeRole = 'customer',
    this.isLoading = false,
    this.error,
  });

  AuthState copyWith({
    UserModel? user,
    bool? isAuthenticated,
    String? activeRole,
    bool? isLoading,
    String? error,
  }) {
    return AuthState(
      user: user ?? this.user,
      isAuthenticated: isAuthenticated ?? this.isAuthenticated,
      activeRole: activeRole ?? this.activeRole,
      isLoading: isLoading ?? this.isLoading,
      error: error,
    );
  }
}

class AuthNotifier extends Notifier<AuthState> {
  final ApiClient _client = ApiClient();

  @override
  AuthState build() {
    return const AuthState();
  }

  Future<bool> login(String login, String password) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final res = await _client.post('/auth/login', {
        'login': login.trim(),
        'password': password.trim(),
      });

      // Handle Admin MFA challenge if enabled
      if (res is Map<String, dynamic> && res['mfa_required'] == true) {
        final mfaToken = res['mfa_token'] as String;
        final demoCode = res['demo_code'] as String? ?? '123456';

        final mfaRes = await _client.post('/auth/mfa/verify', {
          'mfa_token': mfaToken,
          'code': demoCode,
        });

        return _processAuthResponse(mfaRes);
      }

      return _processAuthResponse(res);
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString().replaceAll('Exception: ', ''),
      );
      return false;
    }
  }

  bool _processAuthResponse(dynamic res) {
    if (res is Map<String, dynamic>) {
      final token = res['token'] as String?;
      final userMap = res['user'] as Map<String, dynamic>?;

      if (token != null) {
        _client.setToken(token);
      }

      UserModel user;
      if (userMap != null) {
        user = UserModel.fromJson(userMap);
      } else {
        user = UserModel(
          id: 1,
          name: 'Foydalanuvchi',
          email: '',
          phone: '',
          roles: ['customer'],
        );
      }

      // Determine default active view from highest role
      String defaultRole = 'customer';
      if (user.isAdmin) {
        defaultRole = 'admin';
      } else if (user.isSeller) {
        defaultRole = 'seller';
      } else if (user.isCourier) {
        defaultRole = 'courier';
      }

      state = state.copyWith(
        user: user,
        isAuthenticated: true,
        activeRole: defaultRole,
        isLoading: false,
        error: null,
      );
      return true;
    }
    state = state.copyWith(isLoading: false, error: 'Kutilmagan server javobi');
    return false;
  }

  Future<bool> quickDemoLogin(String roleType) async {
    String email;
    switch (roleType) {
      case 'admin':
        email = 'admin@market.uz';
        break;
      case 'seller':
        email = 'seller@market.uz';
        break;
      case 'courier':
        email = 'courier@market.uz';
        break;
      case 'customer':
      default:
        email = 'customer@market.uz';
        break;
    }

    final success = await login(email, 'admin123');
    if (success) {
      state = state.copyWith(activeRole: roleType);
    }
    return success;
  }

  void switchActiveRole(String newRole) {
    state = state.copyWith(activeRole: newRole);
  }

  void logout() {
    _client.setToken(null);
    state = const AuthState();
  }
}

final authProvider = NotifierProvider<AuthNotifier, AuthState>(() {
  return AuthNotifier();
});
