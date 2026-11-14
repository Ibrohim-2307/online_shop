import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:customer_flutter/main.dart';

void main() {
  testWidgets('CustomerApp renders main marketplace navigation', (WidgetTester tester) async {
    await tester.pumpWidget(const ProviderScope(child: CustomerApp()));
    await tester.pump();

    expect(find.text('MARKET'), findsOneWidget);
    // Only the active tab shows its name; the others are icons with a screen-reader label
    expect(find.text('Asosiy'), findsOneWidget);
    for (final tab in ['Katalog', 'Savat', 'Buyurtma', 'Profil']) {
      expect(find.bySemanticsLabel(tab), findsOneWidget);
    }
  });
}
