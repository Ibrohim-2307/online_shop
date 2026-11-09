<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Backup\DatabaseBackupService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Mockery\MockInterface;
use PDO;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    private string $backupDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->backupDir = storage_path('framework/testing/backups-' . uniqid());
        File::ensureDirectoryExists($this->backupDir);
        config(['services.backup.path' => $this->backupDir]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->backupDir);
        parent::tearDown();
    }

    private function fileDatabase(): string
    {
        $file = $this->backupDir . '/source.db';
        touch($file);
        config(['database.connections.backup_source' => ['driver' => 'sqlite', 'database' => $file, 'prefix' => '']]);
        DB::connection('backup_source')->statement('CREATE TABLE orders (id INTEGER PRIMARY KEY, number TEXT)');
        DB::connection('backup_source')->table('orders')->insert([['number' => 'ORD-1'], ['number' => 'ORD-2']]);

        return $file;
    }

    private function userWithRole(string $role, string $phone): User
    {
        $user = User::create(['name' => $role, 'email' => "{$role}@example.uz", 'phone' => $phone, 'password' => 'x12345678', 'is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    private function placeBackup(string $name): void
    {
        file_put_contents($this->backupDir . '/' . $name, 'zaxira');
    }

    public function test_backup_can_be_restored_with_all_its_data(): void
    {
        $this->fileDatabase();

        $backup = app(DatabaseBackupService::class)->create('daily', 'backup_source');

        $this->assertMatchesRegularExpression('/^daily-\d{4}-\d{2}-\d{2}-\d{6}\.sqlite\.gz$/', $backup['name']);

        // Unpack it the way a restore would and read the data back
        $restored = $this->backupDir . '/restored.db';
        file_put_contents($restored, gzdecode(file_get_contents($this->backupDir . '/' . $backup['name'])));
        $this->assertSame(2, (int) (new PDO('sqlite:' . $restored))->query('SELECT COUNT(*) FROM orders')->fetchColumn());

        // Nothing temporary is left next to the backups
        $this->assertSame([], glob($this->backupDir . '/.tmp-*') ?: []);
    }

    public function test_old_copies_are_removed_per_type_so_deploys_cannot_push_out_daily_ones(): void
    {
        $this->fileDatabase();
        foreach (range(1, 16) as $day) {
            $this->placeBackup(sprintf('daily-2026-01-%02d-030000.sqlite.gz', $day));
        }
        $this->placeBackup('manual-2026-01-01-120000.sqlite.gz');

        $service = app(DatabaseBackupService::class);
        $service->create('daily', 'backup_source');

        $this->assertCount(14, $service->all()->where('type', 'daily'));
        $this->assertFileDoesNotExist($this->backupDir . '/daily-2026-01-01-030000.sqlite.gz');
        $this->assertFileExists($this->backupDir . '/daily-2026-01-16-030000.sqlite.gz');
        $this->assertFileExists($this->backupDir . '/manual-2026-01-01-120000.sqlite.gz');
    }

    public function test_nightly_backup_is_scheduled_at_three_in_tashkent(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($event) => str_contains((string) $event->command, 'db:backup --type=daily'));

        $this->assertNotNull($event);
        $this->assertSame('0 3 * * *', $event->expression);
        $this->assertSame('Asia/Tashkent', $event->timezone);
    }

    public function test_command_reports_failure_instead_of_crashing_startup(): void
    {
        // The in-memory test database is not a file, so nothing can be copied
        $this->artisan('db:backup --type=predeploy')->assertExitCode(1);
    }

    public function test_admin_sees_and_downloads_backups_and_the_download_is_logged(): void
    {
        $name = 'daily-2026-11-09-030000.sqlite.gz';
        $this->placeBackup($name);
        $admin = $this->userWithRole('admin', '+998901112201');

        $this->actingAs($admin)->get('/admin?tab=backups')->assertStatus(200)->assertSee('Yuklab olish')->assertSee('Kunlik');

        $this->actingAs($admin)->get("/admin/backups/{$name}/download")->assertStatus(200)->assertDownload($name);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $admin->id, 'action' => 'admin.backup_downloaded']);
    }

    public function test_only_administrators_can_reach_backups(): void
    {
        $name = 'daily-2026-11-09-030000.sqlite.gz';
        $this->placeBackup($name);

        $moderator = $this->userWithRole('moderator', '+998901112202');
        $this->actingAs($moderator)->get("/admin/backups/{$name}/download")->assertStatus(403);
        $this->actingAs($moderator)->post('/admin/backups')->assertStatus(403);
        $this->actingAs($moderator)->get('/admin?tab=backups')->assertStatus(200)->assertDontSee('Zaxira nusxalar');

        $customer = $this->userWithRole('customer', '+998901112203');
        $this->actingAs($customer)->get("/admin/backups/{$name}/download")->assertStatus(403);

        $this->app['auth']->forgetGuards();
        $this->get("/admin/backups/{$name}/download")->assertStatus(403);
    }

    public function test_download_accepts_only_real_backup_names(): void
    {
        file_put_contents($this->backupDir . '/secret.sqlite.gz', 'x');
        $admin = $this->userWithRole('admin', '+998901112204');

        $this->actingAs($admin)->get('/admin/backups/secret.sqlite.gz/download')->assertStatus(404);
        $this->actingAs($admin)->get('/admin/backups/daily-2030-01-01-030000.sqlite.gz/download')->assertStatus(404);
    }

    public function test_admin_can_take_a_copy_on_demand(): void
    {
        $this->mock(DatabaseBackupService::class, function (MockInterface $mock) {
            $mock->shouldReceive('create')->once()->with('manual')->andReturn([
                'name' => 'manual-2026-11-09-120000.sqlite.gz', 'type' => 'manual', 'size' => 10, 'created_at' => now(),
            ]);
        });
        $admin = $this->userWithRole('admin', '+998901112205');

        $this->actingAs($admin)->post('/admin/backups')
            ->assertRedirect('/admin?tab=backups')
            ->assertSessionHas('success');
        $this->assertDatabaseHas('audit_logs', ['user_id' => $admin->id, 'action' => 'admin.backup_created']);
    }
}
