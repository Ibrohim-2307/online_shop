import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:customer_flutter/features/auth/auth_notifier.dart';

void main() {
  group('Unified SuperApp Auth & Role Switching Tests', () {
    test('Initial auth state has default customer role and unauthenticated', () {
      final container = ProviderContainer();
      addTearDown(container.dispose);

      final state = container.read(authProvider);
      expect(state.isAuthenticated, false);
      expect(state.activeRole, 'customer');
      expect(state.user, null);
    });

    test('UserModel correctly detects role privileges', () {
      final adminUser = UserModel(
        id: 1,
        name: 'Admin',
        email: 'admin@market.uz',
        phone: '+998712000001',
        roles: ['admin'],
      );
      expect(adminUser.isAdmin, true);
      expect(adminUser.isSeller, false);
      expect(adminUser.isCourier, false);

      final sellerUser = UserModel(
        id: 2,
        name: 'Seller',
        email: 'seller@market.uz',
        phone: '+998901234567',
        roles: ['seller_owner'],
        sellerId: 10,
        storeName: 'Oltin Don',
      );
      expect(sellerUser.isAdmin, false);
      expect(sellerUser.isSeller, true);
      expect(sellerUser.isCourier, false);

      final courierUser = UserModel(
        id: 3,
        name: 'Courier',
        email: 'courier@market.uz',
        phone: '+998939876543',
        roles: ['courier'],
      );
      expect(courierUser.isAdmin, false);
      expect(courierUser.isSeller, false);
      expect(courierUser.isCourier, true);
    });

    test('Role switching updates activeRole state seamlessly', () {
      final container = ProviderContainer();
      addTearDown(container.dispose);

      final notifier = container.read(authProvider.notifier);

      notifier.switchActiveRole('seller');
      expect(container.read(authProvider).activeRole, 'seller');

      notifier.switchActiveRole('courier');
      expect(container.read(authProvider).activeRole, 'courier');

      notifier.switchActiveRole('admin');
      expect(container.read(authProvider).activeRole, 'admin');

      notifier.switchActiveRole('customer');
      expect(container.read(authProvider).activeRole, 'customer');
    });

    test('Logout clears user and resets to default state', () {
      final container = ProviderContainer();
      addTearDown(container.dispose);

      final notifier = container.read(authProvider.notifier);
      notifier.switchActiveRole('seller');
      notifier.logout();

      final state = container.read(authProvider);
      expect(state.isAuthenticated, false);
      expect(state.user, null);
      expect(state.activeRole, 'customer');
    });
  });
}
