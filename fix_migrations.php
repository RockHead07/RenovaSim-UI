<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
$existingTables = array_map(function($t) {
    $vars = get_object_vars($t);
    return array_values($vars)[0];
}, $tables);

echo "Existing tables: " . implode(', ', $existingTables) . "\n";

$pendingMigrations = DB::select("SELECT * FROM migrations");
$migratedFiles = array_column($pendingMigrations, 'migration');

$migrationFiles = scandir('database/migrations');

foreach ($migrationFiles as $file) {
    if (strpos($file, '.php') === false) continue;
    $migrationName = str_replace('.php', '', $file);
    
    // Check if already migrated
    if (in_array($migrationName, $migratedFiles)) continue;
    
    // Try to guess table name from migration file
    $tableName = '';
    if (preg_match('/create_(.*)_table/', $migrationName, $matches)) {
        $tableName = $matches[1];
    } elseif (preg_match('/update_(.*)_table/', $migrationName, $matches)) {
        $tableName = $matches[1];
    } elseif (preg_match('/add_.*_to_(.*)_table/', $migrationName, $matches)) {
        $tableName = $matches[1];
    }
    
    if ($tableName && in_array($tableName, $existingTables)) {
        echo "Marking {$migrationName} as migrated because table {$tableName} exists.\n";
        DB::table('migrations')->insert([
            'migration' => $migrationName,
            'batch' => 3
        ]);
    }
}
echo "Done.\n";
