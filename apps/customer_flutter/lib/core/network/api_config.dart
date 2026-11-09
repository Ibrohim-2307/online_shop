class ApiConfig {
  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://online-shop-l3td.onrender.com/api/v1',
  );

  /// Site address without the API path; product photos are served from here.
  static String get origin => Uri.parse(baseUrl).origin;
}
