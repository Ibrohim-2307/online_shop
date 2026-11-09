<?php

namespace App\Services\Backup;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PDO;
use RuntimeException;

class DatabaseBackupService
{
    /** How many copies of each kind to keep; deploys must not push out the daily copies. */
    public const RETENTION = ['daily' => 14, 'predeploy' => 5, 'manual' => 5];

    public const FILENAME_PATTERN = '(?:daily|predeploy|manual)-\d{4}-\d{2}-\d{2}-\d{6}\.sqlite\.gz';

    /**
     * Make a verified, compressed copy of the live SQLite database.
     *
     * @return array{name: string, type: string, size: int, created_at: \Illuminate\Support\Carbon}
     */
    public function create(string $type = 'manual', ?string $connectionName = null): array
    {
        if (!isset(self::RETENTION[$type])) {
            throw new InvalidArgumentException("Noma'lum zaxira turi: {$type}");
        }

        $connection = DB::connection($connectionName);
        $database = (string) $connection->getDatabaseName();

        if ($connection->getDriverName() !== 'sqlite' || !is_file($database)) {
            throw new RuntimeException('Zaxira nusxa faqat fayldagi SQLite baza uchun ishlaydi.');
        }
        if (filesize($database) === 0) {
            throw new RuntimeException('Baza hali bo\'sh, zaxira olinmadi.');
        }

        $directory = $this->directory();
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $name = $type . '-' . now('Asia/Tashkent')->format('Y-m-d-His') . '.sqlite.gz';
        $copy = $directory . '/.tmp-' . Str::random(12);
        $compressed = $copy . '.gz';

        try {
            // VACUUM INTO gives a consistent copy while customers keep placing orders
            $connection->statement('VACUUM INTO ?', [$copy]);
            $this->assertHealthy($copy);
            $this->compress($copy, $compressed);

            if (!rename($compressed, $directory . '/' . $name)) {
                throw new RuntimeException('Zaxira faylini saqlab bo\'lmadi.');
            }
        } finally {
            foreach ([$copy, $compressed] as $temporary) {
                if (is_file($temporary)) {
                    unlink($temporary);
                }
            }
        }

        $this->prune($type);

        return $this->describe($directory . '/' . $name);
    }

    /**
     * All backups, newest first.
     */
    public function all(): Collection
    {
        return collect(glob($this->directory() . '/*.sqlite.gz') ?: [])
            ->filter(fn (string $file) => $this->isBackupName(basename($file)))
            ->map(fn (string $file) => $this->describe($file))
            ->sortByDesc(fn (array $backup) => $backup['created_at']->getTimestamp())
            ->values();
    }

    /**
     * Absolute path of a backup, or null if there is no such backup.
     */
    public function path(string $name): ?string
    {
        if (!$this->isBackupName($name)) {
            return null;
        }

        $path = $this->directory() . '/' . $name;

        return is_file($path) ? $path : null;
    }

    protected function directory(): string
    {
        return rtrim((string) config('services.backup.path'), '/\\');
    }

    protected function isBackupName(string $name): bool
    {
        return preg_match('/\A' . self::FILENAME_PATTERN . '\z/', $name) === 1;
    }

    protected function assertHealthy(string $file): void
    {
        $result = (new PDO('sqlite:' . $file))->query('PRAGMA integrity_check')->fetchColumn();

        if ($result !== 'ok') {
            throw new RuntimeException('Zaxira nusxa tekshiruvdan o\'tmadi.');
        }
    }

    protected function compress(string $source, string $target): void
    {
        $input = fopen($source, 'rb');
        $output = gzopen($target, 'wb6');

        try {
            while (!feof($input)) {
                gzwrite($output, fread($input, 1024 * 1024));
            }
        } finally {
            fclose($input);
            gzclose($output);
        }
    }

    protected function prune(string $type): void
    {
        $this->all()
            ->where('type', $type)
            ->sortByDesc('name')
            ->slice(self::RETENTION[$type])
            ->each(fn (array $backup) => unlink($this->directory() . '/' . $backup['name']));
    }

    /**
     * @return array{name: string, type: string, size: int, created_at: \Illuminate\Support\Carbon}
     */
    protected function describe(string $file): array
    {
        return [
            'name' => basename($file),
            'type' => Str::before(basename($file), '-'),
            'size' => filesize($file),
            'created_at' => now()->setTimestamp(filemtime($file)),
        ];
    }
}
