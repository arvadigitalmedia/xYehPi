<?php
require_once 'bootstrap.php';

echo "Checking epic_users table structure:\n";
$result = db()->query('DESCRIBE epic_users');
while ($row = $result->fetch()) {
    echo $row['Field'] . ' - ' . $row['Type'] . ' - Null: ' . $row['Null'] . ' - Default: ' . ($row['Default'] ?? 'NULL') . "\n";
}
?>