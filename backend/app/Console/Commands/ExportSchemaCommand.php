<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExportSchemaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schema:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export current database structure to database/exports/schema.sql';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $database = config('database.connections.mysql.database');
        $tables = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
        $columnName = 'Tables_in_' . $database;

        $exportDir = base_path('../database/exports');
        if (!File::isDirectory($exportDir)) {
            File::makeDirectory($exportDir, 0755, true);
        }

        $sql = "-- =========================================================\n";
        $sql .= "-- Marketplace MySQL Schema Export\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: {$database}\n";
        $sql .= "-- =========================================================\n\n";
        $sql .= "SET NAMES utf8mb4;\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $tableObj) {
            $table = $tableObj->$columnName;
            $createTable = DB::selectOne("SHOW CREATE TABLE `{$table}`");
            $createStatement = $createTable->{'Create Table'};

            $sql .= "-- ---------------------------------------------------------\n";
            $sql .= "-- Table structure for `{$table}`\n";
            $sql .= "-- ---------------------------------------------------------\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createStatement . ";\n\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        $targetFile = $exportDir . '/schema.sql';
        File::put($targetFile, $sql);

        $this->info("Schema successfully exported to: {$targetFile}");

        return Command::SUCCESS;
    }
}
