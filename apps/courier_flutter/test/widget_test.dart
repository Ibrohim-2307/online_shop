import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:courier_flutter/main.dart';

void main() {
  testWidgets('CourierApp renders deliveries list and navigation', (WidgetTester tester) async {
    await tester.pumpWidget(const ProviderScope(child: CourierApp()));
    await tester.pump();

    expect(find.text('Kuryer Yetkazmalari'), findsOneWidget);
    expect(find.text('Yetkazmalar'), findsOneWidget);
    expect(find.text('Profil'), findsOneWidget);
  });
}
