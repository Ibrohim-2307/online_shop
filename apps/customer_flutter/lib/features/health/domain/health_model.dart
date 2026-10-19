class HealthModel {
  final bool success;
  final String message;
  final String status;
  final String service;
  final String frameworkVersion;
  final String phpVersion;
  final String databaseStatus;
  final String environment;
  final String timestamp;
  final String requestId;

  HealthModel({
    required this.success,
    required this.message,
    required this.status,
    required this.service,
    required this.frameworkVersion,
    required this.phpVersion,
    required this.databaseStatus,
    required this.environment,
    required this.timestamp,
    required this.requestId,
  });

  factory HealthModel.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as Map<String, dynamic>? ?? {};
    final framework = data['framework'] as Map<String, dynamic>? ?? {};
    final runtime = data['runtime'] as Map<String, dynamic>? ?? {};
    final database = data['database'] as Map<String, dynamic>? ?? {};

    return HealthModel(
      success: json['success'] as bool? ?? false,
      message: json['message'] as String? ?? '',
      status: data['status'] as String? ?? 'unknown',
      service: data['service'] as String? ?? 'Marketplace API',
      frameworkVersion: framework['version'] as String? ?? '',
      phpVersion: runtime['php_version'] as String? ?? '',
      databaseStatus: database['status'] as String? ?? '',
      environment: data['environment'] as String? ?? '',
      timestamp: data['timestamp'] as String? ?? '',
      requestId: json['request_id'] as String? ?? '',
    );
  }
}
