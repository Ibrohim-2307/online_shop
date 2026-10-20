import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:customer_flutter/main.dart';

void main() {
  testWidgets('CustomerApp renders main marketplace navigation', (WidgetTester tester) async {
    await tester.pumpWidget(const ProviderScope(child: CustomerApp()));
    await tester.pump();

    expect(find.text('Marketplace'), findsOneWidget);
    expect(find.text('Asosiy'), findsOneWidget);
    expect(find.text('Katalog'), findsOneWidget);
    expect(find.text('Savatcha'), findsOneWidget);
    expect(find.text('Buyurtmalar'), findsOneWidget);
    expect(find.text('Profil'), findsOneWidget);
  });
}
