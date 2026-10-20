import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'api_config.dart';

class ApiClient {
  static final ApiClient _instance = ApiClient._internal();
  factory ApiClient() => _instance;
  ApiClient._internal();

  String? _authToken;

  void setToken(String? token) {
    _authToken = token;
  }

  String get baseUrl => ApiConfig.baseUrl;

  Map<String, String> _buildHeaders() {
    final headers = <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
    if (_authToken != null) {
      headers['Authorization'] = 'Bearer $_authToken';
    }
    return headers;
  }

  Future<dynamic> get(String endpoint) async {
    final uri = Uri.parse('$baseUrl$endpoint');
    try {
      final response = await http.get(uri, headers: _buildHeaders()).timeout(
        const Duration(seconds: 12),
      );
      return _processResponse(response);
    } on SocketException {
      throw Exception('Serverga ulanish imkoni bo\'lmadi. Internetni tekshiring.');
    }
  }

  Future<dynamic> post(String endpoint, Map<String, dynamic> body) async {
    final uri = Uri.parse('$baseUrl$endpoint');
    try {
      final response = await http
          .post(uri, headers: _buildHeaders(), body: jsonEncode(body))
          .timeout(const Duration(seconds: 12));
      return _processResponse(response);
    } on SocketException {
      throw Exception('Serverga ulanish imkoni bo\'lmadi. Internetni tekshiring.');
    }
  }

  dynamic _processResponse(http.Response response) {
    final body = jsonDecode(utf8.decode(response.bodyBytes));
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return body['data'] ?? body;
    } else {
      final errorMsg = body['error']?['message'] ?? body['message'] ?? 'Xatolik yuz berdi (${response.statusCode})';
      throw Exception(errorMsg);
    }
  }
}
