<?php
define('EPIC_DIRECT_ACCESS', true);
require_once 'bootstrap.php';

echo "<h2>Data Users yang Ada</h2>\n";

try {
    $users = db()->select('SELECT * FROM epic_users LIMIT 10');
    echo "<p>Total users: " . count($users) . "</p>\n";
    
    if (count($users) > 0) {
        echo "<table border='1'>\n";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Role</th><th>Created</th></tr>\n";
        foreach($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['name']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['status']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>{$user['created_at']}</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "<p>Tidak ada data users.</p>\n";
    }
    
    // Cek juga data EPIS accounts
    echo "<h2>Data EPIS Accounts yang Ada</h2>\n";
    $epis = db()->select('SELECT * FROM epic_epis_accounts LIMIT 10');
    echo "<p>Total EPIS accounts: " . count($epis) . "</p>\n";
    
    if (count($epis) > 0) {
        echo "<table border='1'>\n";
        echo "<tr><th>ID</th><th>User ID</th><th>EPIS Code</th><th>Territory</th><th>Status</th></tr>\n";
        foreach($epis as $account) {
            echo "<tr>";
            echo "<td>{$account['id']}</td>";
            echo "<td>{$account['user_id']}</td>";
            echo "<td>{$account['epis_code']}</td>";
            echo "<td>{$account['territory_name']}</td>";
            echo "<td>{$account['status']}</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "<p>Tidak ada data EPIS accounts.</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>\n";
}
?>