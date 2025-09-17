<?php
/**
 * Fix Status Enum in Users Table
 * Add 'epis' status to enum values using safe migration approach
 */

try {
    $pdo = new PDO('mysql:host=localhost;dbname=epic_hub', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "Connected to database successfully.\n";
    
    // First, check current status values
    echo "\n=== CHECKING CURRENT STATUS VALUES ===\n";
    $result = $pdo->query("SELECT status, COUNT(*) as count FROM epic_users GROUP BY status");
    $statuses = $result->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($statuses as $status) {
        echo "Status '{$status['status']}': {$status['count']} users\n";
    }
    
    // Step 1: Add temporary column with new enum
    echo "\n=== STEP 1: ADDING TEMPORARY COLUMN ===\n";
    try {
        $pdo->exec("ALTER TABLE epic_users ADD COLUMN status_new enum('pending','free','epic','epis','suspended','banned') NOT NULL DEFAULT 'pending'");
        echo "✅ Added temporary status_new column\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "⚠️ Temporary column already exists, continuing...\n";
        } else {
            throw $e;
        }
    }
    
    // Step 2: Migrate data from old column to new column
    echo "\n=== STEP 2: MIGRATING DATA ===\n";
    
    // Get all users and migrate their status
    $users = $pdo->query("SELECT id, name, status FROM epic_users")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        $new_status = 'free'; // Default
        
        switch ($user['status']) {
            case 'pending':
                $new_status = 'pending';
                break;
            case 'active':
            case 'premium':
                $new_status = 'epic';
                break;
            case 'suspended':
            case 'inactive':
                $new_status = 'suspended';
                break;
            case 'banned':
                $new_status = 'banned';
                break;
            case 'free':
                $new_status = 'free';
                break;
            case 'epic':
                $new_status = 'epic';
                break;
            case 'epis':
                $new_status = 'epis';
                break;
            default:
                $new_status = 'free';
                break;
        }
        
        $pdo->prepare("UPDATE epic_users SET status_new = ? WHERE id = ?")
            ->execute([$new_status, $user['id']]);
        
        echo "User {$user['id']} ({$user['name']}): '{$user['status']}' → '{$new_status}'\n";
    }
    
    // Step 3: Drop old column and rename new column
    echo "\n=== STEP 3: REPLACING OLD COLUMN ===\n";
    
    $pdo->exec("ALTER TABLE epic_users DROP COLUMN status");
    echo "✅ Dropped old status column\n";
    
    $pdo->exec("ALTER TABLE epic_users CHANGE status_new status enum('pending','free','epic','epis','suspended','banned') NOT NULL DEFAULT 'pending'");
    echo "✅ Renamed status_new to status\n";
    
    // Verify the change
    echo "\n=== VERIFICATION ===\n";
    $result = $pdo->query("SHOW COLUMNS FROM epic_users LIKE 'status'");
    $column = $result->fetch(PDO::FETCH_ASSOC);
    
    echo "Column Type: " . $column['Type'] . "\n";
    echo "Default: " . $column['Default'] . "\n";
    
    // Check final status distribution
    echo "\n=== FINAL STATUS DISTRIBUTION ===\n";
    $result = $pdo->query("SELECT status, COUNT(*) as count FROM epic_users GROUP BY status");
    $final_statuses = $result->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($final_statuses as $status) {
        echo "Status '{$status['status']}': {$status['count']} users\n";
    }
    
    echo "\n✅ Status enum migration completed successfully!\n";
    echo "EPIS status is now available for use.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    
    // Cleanup on error
    try {
        $pdo->exec("ALTER TABLE epic_users DROP COLUMN IF EXISTS status_new");
        echo "Cleaned up temporary column.\n";
    } catch (Exception $cleanup_error) {
        // Ignore cleanup errors
    }
    
    exit(1);
}

?>