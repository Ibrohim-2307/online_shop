<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    /**
     * Test API v1 health endpoint returns healthy status and standard structure.
     */
    public function test_api_v1_health_endpoint_returns_success(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertHeader('X-Request-Id')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'status',
                    'service',
                    'api_version',
                    'framework' => ['name', 'version'],
                    'runtime' => ['php_version', 'os'],
                    'database' => ['driver', 'status', 'latency_ms'],
                    'environment',
                    'timestamp',
                ],
                'request_id',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'healthy',
                    'database' => [
                        'status' => 'connected',
                    ],
                ],
            ]);

        $this->assertNotEmpty($response->json('request_id'));
        $this->assertEquals($response->headers->get('X-Request-Id'), $response->json('request_id'));
    }

    /**
     * Test custom X-Request-Id is preserved.
     */
    public function test_custom_request_id_is_preserved(): void
    {
        $customId = 'req-custom-test-12345';

        $response = $this->withHeaders([
            'X-Request-Id' => $customId,
        ])->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertHeader('X-Request-Id', $customId)
            ->assertJson([
                'request_id' => $customId,
            ]);
    }

    /**
     * Test web health view returns HTTP 200.
     */
    public function test_web_health_dashboard_returns_ok(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200)
            ->assertSee('Server Salomatligi (Health Check)')
            ->assertSee('PHP Versiyasi');
    }

    /**
     * Test unknown API route returns standardized JSON error response.
     */
    public function test_unknown_api_route_returns_standardized_json_error(): void
    {
        $response = $this->getJson('/api/v1/non-existent-endpoint');

        $response->assertStatus(404)
            ->assertHeader('X-Request-Id')
            ->assertJsonStructure([
                'success',
                'error' => [
                    'code',
                    'message',
                    'details',
                ],
                'request_id',
            ])
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                ],
            ]);
    }
}
