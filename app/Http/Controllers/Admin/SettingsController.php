<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class SettingsController extends Controller
{
    /**
     * Export the database and download it.
     */
    public function downloadDatabase()
    {
        $connection = config('database.default');

        if ($connection === 'sqlite') {
            $databasePath = config('database.connections.sqlite.database');
            if (file_exists($databasePath)) {
                $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sqlite';
                return response()->download($databasePath, $filename);
            }
            return back()->with('error', 'SQLite database file not found.');
        }

        $databaseName = config('database.connections.mysql.database');
        $filename = 'backup_' . $databaseName . '_' . date('Y-m-d_H-i-s') . '.sql';
        
        $tempPath = storage_path('app/' . $filename);
        $handle = fopen($tempPath, 'w');
        
        if (!$handle) {
            return back()->with('error', 'Unable to create backup file.');
        }

        // Add header comments
        fwrite($handle, "-- FOMS BMS Database Backup\n");
        fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Database: " . $databaseName . "\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        // Fetch tables along with types from information_schema
        try {
            $tables = DB::select("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?", [$databaseName]);
        } catch (\Exception $e) {
            // Fallback to simple SHOW TABLES
            $showTables = DB::select("SHOW TABLES");
            $tables = [];
            foreach ($showTables as $t) {
                $tableName = array_values((array)$t)[0];
                $tables[] = (object)[
                    'TABLE_NAME' => $tableName,
                    'TABLE_TYPE' => 'BASE TABLE'
                ];
            }
        }

        $pdo = DB::connection()->getPdo();

        foreach ($tables as $table) {
            $tableName = $table->TABLE_NAME;
            $tableType = $table->TABLE_TYPE ?? 'BASE TABLE';
            
            if ($tableType === 'VIEW') {
                // Get CREATE VIEW statement
                try {
                    $createViewObj = DB::select("SHOW CREATE VIEW `$tableName`")[0];
                    $createStatement = $createViewObj->{'Create View'};
                    fwrite($handle, "DROP VIEW IF EXISTS `$tableName`;\n");
                    fwrite($handle, $createStatement . ";\n\n");
                } catch (\Exception $e) {
                    // Ignore errors if any view creation fails
                }
                continue;
            }

            // Get CREATE TABLE
            try {
                $createTableObj = DB::select("SHOW CREATE TABLE `$tableName`")[0];
                $createStatement = $createTableObj->{'Create Table'} ?? null;
                if ($createStatement) {
                    fwrite($handle, "DROP TABLE IF EXISTS `$tableName`;\n");
                    fwrite($handle, $createStatement . ";\n\n");
                }
            } catch (\Exception $e) {
                continue;
            }
            
            // Get data in chunks of 1000 rows
            try {
                $count = DB::table($tableName)->count();
            } catch (\Exception $e) {
                continue;
            }

            $chunkSize = 1000;
            
            for ($offset = 0; $offset < $count; $offset += $chunkSize) {
                $rows = DB::table($tableName)->offset($offset)->limit($chunkSize)->get();
                if ($rows->isEmpty()) {
                    continue;
                }
                
                $columns = array_keys((array)$rows->first());
                $insertQuery = "INSERT INTO `$tableName` (";
                $insertQuery .= implode(', ', array_map(fn($col) => "`$col`", $columns)) . ") VALUES \n";
                
                $valuesList = [];
                foreach ($rows as $row) {
                    $rowValues = [];
                    foreach ($columns as $column) {
                        $value = $row->{$column};
                        if (is_null($value)) {
                            $rowValues[] = 'NULL';
                        } else {
                            $rowValues[] = $pdo->quote($value);
                        }
                    }
                    $valuesList[] = "(" . implode(', ', $rowValues) . ")";
                }
                
                $insertQuery .= implode(",\n", $valuesList) . ";\n\n";
                fwrite($handle, $insertQuery);
            }
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        // Download and delete temporary file after sending
        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }
}
