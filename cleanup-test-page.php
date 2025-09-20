<?php
require_once __DIR__ . '/bootstrap.php';

echo "Cleaning up test data...\n";
$cleanup1 = db()->query("DELETE FROM epic_user_tokens WHERE id = ?", [24]);
$cleanup2 = db()->query("DELETE FROM epic_users WHERE id = ?", [34]);

if ($cleanup1 && $cleanup2) {
    echo "✅ Cleanup successful\n";
} else {
    echo "❌ Cleanup failed\n";
}
?>