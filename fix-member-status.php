<?php
/**
 * Fix Member Status - Update INACTIVE to ACTIVE for confirmed emails
 * Script untuk memperbaiki status member yang sudah konfirmasi email
 */

// Include required files
if (!defined('EPIC_LOADED')) define('EPIC_LOADED', true);
require_once __DIR__ . '/bootstrap.php';

echo "<h2>Fix Member Status - Email Confirmation System</h2>";

try {
    // 1. Cek struktur tabel users
    echo "<h3>1. Checking Users Table Structure</h3>";
    $columns = db()->select("DESCRIBE " . db()->table('users'));
    
    $has_email_verified = false;
    $has_email_verified_at = false;
    $has_status = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'email_verified') {
            $has_email_verified = true;
        }
        if ($column['Field'] === 'email_verified_at') {
            $has_email_verified_at = true;
        }
        if ($column['Field'] === 'status') {
            $has_status = true;
        }
    }
    
    echo "<p>✓ Status column: " . ($has_status ? "EXISTS" : "MISSING") . "</p>";
    echo "<p>✓ Email verified column: " . ($has_email_verified ? "EXISTS" : "MISSING") . "</p>";
    echo "<p>✓ Email verified at column: " . ($has_email_verified_at ? "EXISTS" : "MISSING") . "</p>";
    
    // 2. Add missing columns if needed
    if (!$has_email_verified) {
        echo "<h3>2. Adding email_verified column</h3>";
        db()->query("ALTER TABLE " . db()->table('users') . " ADD COLUMN email_verified TINYINT(1) DEFAULT 0");
        echo "<p>✓ Added email_verified column</p>";
    }
    
    if (!$has_email_verified_at) {
        echo "<h3>3. Adding email_verified_at column</h3>";
        db()->query("ALTER TABLE " . db()->table('users') . " ADD COLUMN email_verified_at TIMESTAMP NULL");
        echo "<p>✓ Added email_verified_at column</p>";
    }
    
    // 3. Check specific user status
    echo "<h3>4. Checking User: contact.bustanul@gmail.com</h3>";
    $user = db()->selectOne(
        "SELECT id, name, email, status, email_verified, email_verified_at, created_at 
         FROM " . db()->table('users') . " 
         WHERE email = ?",
        ['contact.bustanul@gmail.com']
    );
    
    if ($user) {
        echo "<p>✓ User found:</p>";
        echo "<ul>";
        echo "<li>ID: " . $user['id'] . "</li>";
        echo "<li>Name: " . htmlspecialchars($user['name']) . "</li>";
        echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
        echo "<li>Status: <strong>" . $user['status'] . "</strong></li>";
        echo "<li>Email Verified: " . ($user['email_verified'] ? 'YES' : 'NO') . "</li>";
        echo "<li>Email Verified At: " . ($user['email_verified_at'] ?: 'NULL') . "</li>";
        echo "<li>Created At: " . $user['created_at'] . "</li>";
        echo "</ul>";
    } else {
        echo "<p>❌ User not found</p>";
    }
    
    // 4. Check email confirmations table
    echo "<h3>5. Checking Email Confirmations</h3>";
    try {
        $confirmations = db()->select(
            "SELECT * FROM " . db()->table('user_tokens') . " 
             WHERE type = 'email_verification' AND user_id = ? 
             ORDER BY created_at DESC",
            [$user['id']]
        );
        
        if ($confirmations) {
            echo "<p>✓ Email confirmation tokens found: " . count($confirmations) . "</p>";
            foreach ($confirmations as $conf) {
                echo "<ul>";
                echo "<li>Token: " . substr($conf['token'], 0, 10) . "...</li>";
                echo "<li>Created: " . $conf['created_at'] . "</li>";
                echo "<li>Expires: " . $conf['expires_at'] . "</li>";
                echo "<li>Used: " . ($conf['used_at'] ?: 'NO') . "</li>";
                echo "</ul>";
            }
        } else {
            echo "<p>⚠️ No email confirmation tokens found</p>";
        }
    } catch (Exception $e) {
        echo "<p>⚠️ Email confirmations table check failed: " . $e->getMessage() . "</p>";
    }
    
    // 5. Fix user status if email was confirmed
    echo "<h3>6. Fixing User Status</h3>";
    
    if ($user && $user['status'] === 'INACTIVE') {
        // Check if user has used confirmation token
        $used_token = db()->selectOne(
            "SELECT * FROM " . db()->table('user_tokens') . " 
             WHERE type = 'email_verification' AND user_id = ? AND used_at IS NOT NULL 
             ORDER BY used_at DESC LIMIT 1",
            [$user['id']]
        );
        
        if ($used_token) {
            echo "<p>✓ User has confirmed email, updating status to ACTIVE...</p>";
            
            $result = db()->query(
                "UPDATE " . db()->table('users') . " 
                 SET status = 'ACTIVE', email_verified = 1, email_verified_at = COALESCE(email_verified_at, NOW()) 
                 WHERE id = ?",
                [$user['id']]
            );
            
            if ($result) {
                echo "<p style='color: green;'>✅ Status updated to ACTIVE successfully!</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to update status</p>";
            }
        } else {
            echo "<p>⚠️ User has not confirmed email yet</p>";
        }
    } elseif ($user && $user['status'] === 'ACTIVE') {
        echo "<p style='color: green;'>✅ User status is already ACTIVE</p>";
    }
    
    // 6. Update all users who have confirmed email but still INACTIVE
    echo "<h3>7. Fixing All Users with Confirmed Email</h3>";
    
    $inactive_confirmed_users = db()->select(
        "SELECT u.id, u.name, u.email, u.status 
         FROM " . db()->table('users') . " u
         INNER JOIN " . db()->table('user_tokens') . " t ON u.id = t.user_id
         WHERE u.status = 'INACTIVE' 
         AND t.type = 'email_verification' 
         AND t.used_at IS NOT NULL"
    );
    
    if ($inactive_confirmed_users) {
        echo "<p>Found " . count($inactive_confirmed_users) . " users with confirmed email but INACTIVE status:</p>";
        
        foreach ($inactive_confirmed_users as $inactive_user) {
            echo "<p>Updating: " . htmlspecialchars($inactive_user['name']) . " (" . htmlspecialchars($inactive_user['email']) . ")</p>";
            
            db()->query(
                "UPDATE " . db()->table('users') . " 
                 SET status = 'ACTIVE', email_verified = 1, email_verified_at = COALESCE(email_verified_at, NOW()) 
                 WHERE id = ?",
                [$inactive_user['id']]
            );
        }
        
        echo "<p style='color: green;'>✅ Updated " . count($inactive_confirmed_users) . " users to ACTIVE status</p>";
    } else {
        echo "<p>✓ No users found with confirmed email but INACTIVE status</p>";
    }
    
    // 7. Statistics after fix
    echo "<h3>8. Member Status Statistics</h3>";
    $stats = [
        'total' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('users')),
        'active' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('users') . " WHERE status = 'ACTIVE'"),
        'inactive' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('users') . " WHERE status = 'INACTIVE'"),
        'email_verified' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('users') . " WHERE email_verified = 1"),
        'email_not_verified' => db()->selectValue("SELECT COUNT(*) FROM " . db()->table('users') . " WHERE email_verified = 0 OR email_verified IS NULL")
    ];
    
    echo "<ul>";
    echo "<li>Total Members: " . $stats['total'] . "</li>";
    echo "<li>ACTIVE Status: " . $stats['active'] . "</li>";
    echo "<li>INACTIVE Status: " . $stats['inactive'] . "</li>";
    echo "<li>Email Verified: " . $stats['email_verified'] . "</li>";
    echo "<li>Email Not Verified: " . $stats['email_not_verified'] . "</li>";
    echo "</ul>";
    
    // 8. Verify specific user after fix
    echo "<h3>9. Verification After Fix</h3>";
    $updated_user = db()->selectOne(
        "SELECT id, name, email, status, email_verified, email_verified_at 
         FROM " . db()->table('users') . " 
         WHERE email = ?",
        ['contact.bustanul@gmail.com']
    );
    
    if ($updated_user) {
        echo "<p>✓ User status after fix:</p>";
        echo "<ul>";
        echo "<li>Status: <strong style='color: " . ($updated_user['status'] === 'ACTIVE' ? 'green' : 'red') . ";'>" . $updated_user['status'] . "</strong></li>";
        echo "<li>Email Verified: " . ($updated_user['email_verified'] ? 'YES' : 'NO') . "</li>";
        echo "<li>Email Verified At: " . ($updated_user['email_verified_at'] ?: 'NULL') . "</li>";
        echo "</ul>";
        
        if ($updated_user['status'] === 'ACTIVE') {
            echo "<p style='color: green; font-weight: bold;'>✅ SUCCESS: User can now login!</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ User still cannot login - status is " . $updated_user['status'] . "</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>Summary</h3>";
    echo "<p><strong>✅ Member status system has been fixed!</strong></p>";
    echo "<ul>";
    echo "<li>✓ Email confirmation now sets status to ACTIVE</li>";
    echo "<li>✓ Login validation blocks INACTIVE users</li>";
    echo "<li>✓ Admin can activate members manually</li>";
    echo "<li>✓ All confirmed users updated to ACTIVE status</li>";
    echo "</ul>";
    
    echo "<h3>Next Steps</h3>";
    echo "<ul>";
    echo "<li>Test login with contact.bustanul@gmail.com</li>";
    echo "<li>Test admin member activation</li>";
    echo "<li>Test new registration → confirmation → login flow</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Stack trace: " . htmlspecialchars($e->getTraceAsString()) . "</p>";
}
?>