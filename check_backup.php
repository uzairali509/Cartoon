<?php
try {
    $pdo = new PDO('sqlite:C:\xampp\htdocs\cartoon-universe\includes\data\cartoon_universe.sqlite.backup');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $tables = $pdo->query('SELECT name FROM sqlite_master WHERE type="table"');
    while ($row = $tables->fetch()) {
        echo $row[0] . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}