<?php
define('EPIC_DIRECT_ACCESS', true);
require_once 'bootstrap.php';

echo "<h2>Struktur Tabel epic_users</h2>\n";

try {
    $result = db()->select('SHOW CREATE TABLE epic_users');
    echo "<pre>" . htmlspecialchars($result[0]['Create Table']) . "</pre>\n";
    
    echo "<h2>Struktur Tabel epic_epis_accounts</h2>\n";
    $result2 = db()->select('SHOW CREATE TABLE epic_epis_accounts');
    echo "<pre>" . htmlspecialchars($result2[0]['Create Table']) . "</pre>\n";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>\n";
}
?>