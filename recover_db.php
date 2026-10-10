<?php
// Try to recover data from corrupted SQLite database
$corruptedDb = 'C:\xampp\htdocs\cartoon-universe\includes\data\cartoon_universe.sqlite';
$recoveredDb = 'C:\xampp\htdocs\cartoon-universe\includes\data\cartoon_universe_recovered.sqlite';

echo "Attempting to recover database...\n";

// First, try to dump what we can from the corrupted DB
$corrupted = new PDO('sqlite:C:\xampp\htdocs\cartoon-universe\includes\data\cartoon_universe.sqlite');
$corrupted->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Try to get list of tables
    $tables = $corrupted->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tableNames = $tables->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables found: " . implode(', ', $tableNames) . "\n";
    
    // Try to dump each table
    foreach ($tableNames as $table) {
        echo "Dumping table: $table\n";
        try {
            $data = $corrupted->query("SELECT * FROM $table");
            $rows = $data->fetchAll(PDO::FETCH_ASSOC);
            echo "  Found " . count($rows) . " rows\n";
        } catch (Exception $e) {
            echo "  Error reading table $table: " . $e->getMessage() . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Try to create a recovered database
echo "\nAttempting to create recovered database...\n";
try {
    $recovered = new PDO('sqlite:C:\xampp\htdocs\cartoon-universe\includes\data\cartoon_universe_recovered.sqlite');
    $recovered->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Recreate tables from the corrupted DB schema
    $tables = $corrupted->query("SELECT sql FROM sqlite_master WHERE type='table'");
    $schemas = $tables->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($schemas as $sql) {
        if ($sql) {
            $recovered->exec($sql);
            echo "Created table schema\n";
        }
    }
    
    // Copy data from each table
    $tables = $corrupted->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tableNames = $tables->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tableNames as $table) {
        if ($table === 'sqlite_sequence') continue;
        echo "Copying table: $table\n";
        try {
            $data = $corrupted->query("SELECT * FROM $table");
            $rows = $data->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($rows) > 0) {
                // Build insert statements
                $columns = array_keys($rows[0]);
                $placeholders = implode(',', array_fill(0, count($columns), '?'));
                $stmt = $recovered->prepare("INSERT INTO $table (" . implode(',', $columns) . ") VALUES ($placeholders)");
                
                foreach ($rows as $row) {
                    $stmt->execute(array_values($row));
                }
                echo "  Copied " . count($rows) . " rows\n";
            }
        } catch (Exception $e) {
            echo "  Error copying $table: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nRecovery complete!\n";
    
    // Verify the recovered database
    $check = $recovered->query("PRAGMA integrity_check");
    $result = $check->fetch();
    echo "Integrity check: " . $result[0] . "\n";
    
} catch (Exception $e) {
    echo "Recovery failed: " . $e->getMessage() . "\n";
}