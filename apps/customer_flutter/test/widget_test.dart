import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:customer_flutter/main.dart';

void main() {
  testWidgets('CustomerApp renders health screen without crash', (WidgetTester tester) async {
    await tester.pumpWidget(const ProviderScope(child: CustomerApp()));
    expect(find.text('Xaridor Ilovasi — Tizim Holati'), findsOneWidget);
    expect(find.text('Backend API Bog\'lanishi'), findsOneWidget);
  });
}
