/// 4200000 -> "4 200 000"
String formatNumber(num amount) {
  final digits = amount.round().abs().toString();
  final buffer = StringBuffer(amount < 0 ? '-' : '');
  for (int i = 0; i < digits.length; i++) {
    if (i > 0 && (digits.length - i) % 3 == 0) buffer.write(' ');
    buffer.write(digits[i]);
  }
  return buffer.toString();
}

/// 4200000 -> "4 200 000 UZS"
String formatMoney(num amount) => '${formatNumber(amount)} UZS';

/// 2.0 -> "2", 2.5 -> "2.5"
String formatQuantity(double quantity) =>
    quantity == quantity.roundToDouble() ? quantity.toInt().toString() : quantity.toString();

/// Units are stored in English on the server; customers read Uzbek.
String unitLabel(String unit) {
  const labels = {
    'piece': 'dona',
    'box': 'quti',
    'bag': 'qop',
    'ton': 'tonna',
    'kg': 'kg',
    'meter': 'metr',
    'liter': 'litr',
  };
  return labels[unit] ?? unit;
}
