<?php
require_once 'bootstrap.php';

echo "<h2>Monitoring Tables Check</h2>";

try {
    $tables = ['epi_registration_metrics', 'epi_registration_errors', 'epi_performance_logs'];
    
    foreach ($tables as $table) {
        echo "<h3>Table: $table</h3>";
        
        $result = db()->getConnection()->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "<p>✅ Table exists</p>";
            
            $columns = db()->getConnection()->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
            echo "<table border='1'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
                echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>❌ Table does not exist</p>";
        }
        echo "<hr>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>