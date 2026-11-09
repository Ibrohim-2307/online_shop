class ApiConfig {
  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://online-shop-l3td.onrender.com/api/v1',
  );
}
