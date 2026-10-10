<?php
$pdo = new PDO('sqlite:C:\xampp\htdocs\cartoon-universe\includes\data\cartoon_universe.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
echo 'Connection OK';
$result = $pdo->query('PRAGMA integrity_check');
while ($row = $result->fetch()) {
    echo 'Integrity: ' . $row[0] . PHP_EOL;
}