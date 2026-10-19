import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:courier_flutter/main.dart';

void main() {
  testWidgets('CourierApp renders health screen without crash', (WidgetTester tester) async {
    await tester.pumpWidget(const ProviderScope(child: CourierApp()));
    expect(find.text('Kuryer Ilovasi — Tizim Holati'), findsOneWidget);
    expect(find.text('Logistika API Bog\'lanishi'), findsOneWidget);
  });
}
