import 'dart:convert';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:http/http.dart' as http;
import '../../../core/network/api_config.dart';
import '../domain/health_model.dart';

final healthRepositoryProvider = Provider<HealthRepository>((ref) {
  return HealthRepository();
});

final healthCheckProvider = FutureProvider<HealthModel>((ref) async {
  final repo = ref.watch(healthRepositoryProvider);
  return repo.fetchHealth();
});

class HealthRepository {
  Future<HealthModel> fetchHealth() async {
    final uri = Uri.parse('${ApiConfig.baseUrl}/health');
    final response = await http.get(uri).timeout(const Duration(seconds: 5));

    if (response.statusCode >= 200 && response.statusCode < 300) {
      final decoded = json.decode(response.body) as Map<String, dynamic>;
      return HealthModel.fromJson(decoded);
    } else {
      throw Exception('Server javobi xato: HTTP ${response.statusCode}');
    }
  }
}
