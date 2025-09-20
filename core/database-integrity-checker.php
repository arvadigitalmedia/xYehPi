<?php
/**
 * EPIC Database Integrity Checker
 * Comprehensive integrity check untuk semua tabel EPIS
 * 
 * @author TRAE AI Assistant
 * @version 1.0.0
 */

require_once __DIR__ . '/../bootstrap.php';

class EpicDatabaseIntegrityChecker {
    private $db;
    private $results = [];
    private $errors = [];
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Run comprehensive database integrity check
     */
    public function runFullCheck($auto_fix = false) {
        echo "=== EPIC DATABASE INTEGRITY CHECK ===\n";
        echo "Checking all EPIS-related tables for data integrity...\n\n";
        
        $this->results = [
            'timestamp' => date('Y-m-d H:i:s'),
            'auto_fix' => $auto_fix,
            'checks' => []
        ];
        
        try {
            // 1. Check EPIS accounts integrity
            $this->checkEpisAccountsIntegrity($auto_fix);
            
            // 2. Check user-EPIS relationships
            $this->checkUserEpisRelationships($auto_fix);
            
            // 3. Check EPIS counter consistency
            $this->checkEpisCounterConsistency($auto_fix);
            
            // 4. Check orphaned records
            $this->checkOrphanedRecords($auto_fix);
            
            // 5. Check data constraints
            $this->checkDataConstraints($auto_fix);
            
            // 6. Check referential integrity
            $this->checkReferentialIntegrity($auto_fix);
            
            $this->generateReport();
            
        } catch (Exception $e) {
            $this->errors[] = "Critical error during integrity check: " . $e->getMessage();
            echo "❌ Critical error: " . $e->getMessage() . "\n";
        }
        
        return $this->results;
    }
    
    /**
     * Check EPIS accounts table integrity
     */
    private function checkEpisAccountsIntegrity($auto_fix = false) {
        echo "1. Checking EPIS accounts integrity...\n";
        
        $check = [
            'name' => 'epis_accounts_integrity',
            'status' => 'passed',
            'issues' => [],
            'fixes_applied' => []
        ];
        
        try {
            // Check for duplicate EPIS codes
            $duplicates = $this->db->select(
                "SELECT epis_code, COUNT(*) as count 
                 FROM epic_epis_accounts 
                 GROUP BY epis_code 
                 HAVING count > 1"
            );
            
            if (!empty($duplicates)) {
                $check['status'] = 'failed';
                foreach ($duplicates as $dup) {
                    $check['issues'][] = "Duplicate EPIS code: {$dup['epis_code']} ({$dup['count']} times)";
                }
                
                if ($auto_fix) {
                    // Keep the oldest record, remove duplicates
                    foreach ($duplicates as $dup) {
                        $this->db->query(
                            "DELETE FROM epic_epis_accounts 
                             WHERE epis_code = ? AND id NOT IN (
                                 SELECT min_id FROM (
                                     SELECT MIN(id) as min_id 
                                     FROM epic_epis_accounts 
                                     WHERE epis_code = ?
                                 ) as temp
                             )",
                            [$dup['epis_code'], $dup['epis_code']]
                        );
                        $check['fixes_applied'][] = "Removed duplicate EPIS code: {$dup['epis_code']}";
                    }
                }
            }
            
            // Check for invalid status values
            $invalid_status = $this->db->select(
                "SELECT id, status FROM epic_epis_accounts 
                 WHERE status NOT IN ('active', 'inactive', 'suspended')"
            );
            
            if (!empty($invalid_status)) {
                $check['status'] = 'failed';
                foreach ($invalid_status as $invalid) {
                    $check['issues'][] = "Invalid status '{$invalid['status']}' for EPIS ID {$invalid['id']}";
                }
                
                if ($auto_fix) {
                    $this->db->query(
                        "UPDATE epic_epis_accounts 
                         SET status = 'active' 
                         WHERE status NOT IN ('active', 'inactive', 'suspended')"
                    );
                    $check['fixes_applied'][] = "Fixed invalid status values to 'active'";
                }
            }
            
            // Check for negative counter values
            $negative_counters = $this->db->select(
                "SELECT id, epis_code, current_epic_count 
                 FROM epic_epis_accounts 
                 WHERE current_epic_count < 0"
            );
            
            if (!empty($negative_counters)) {
                $check['status'] = 'failed';
                foreach ($negative_counters as $negative) {
                    $check['issues'][] = "Negative counter {$negative['current_epic_count']} for EPIS {$negative['epis_code']}";
                }
                
                if ($auto_fix) {
                    $this->db->query(
                        "UPDATE epic_epis_accounts 
                         SET current_epic_count = 0 
                         WHERE current_epic_count < 0"
                    );
                    $check['fixes_applied'][] = "Reset negative counters to 0";
                }
            }
            
        } catch (Exception $e) {
            $check['status'] = 'error';
            $check['issues'][] = "Error checking EPIS accounts: " . $e->getMessage();
        }
        
        $this->results['checks'][] = $check;
        echo "   " . ($check['status'] === 'passed' ? '✓' : '❌') . " EPIS accounts integrity\n";
    }
    
    /**
     * Check user-EPIS relationships
     */
    private function checkUserEpisRelationships($auto_fix = false) {
        echo "2. Checking user-EPIS relationships...\n";
        
        $check = [
            'name' => 'user_epis_relationships',
            'status' => 'passed',
            'issues' => [],
            'fixes_applied' => []
        ];
        
        try {
            // Check for EPIS accounts without valid users
            $orphaned_epis = $this->db->select(
                "SELECT ea.id, ea.epis_code, ea.user_id 
                 FROM epic_epis_accounts ea 
                 LEFT JOIN epic_users u ON ea.user_id = u.id 
                 WHERE u.id IS NULL"
            );
            
            if (!empty($orphaned_epis)) {
                $check['status'] = 'failed';
                foreach ($orphaned_epis as $orphan) {
                    $check['issues'][] = "EPIS {$orphan['epis_code']} references non-existent user ID {$orphan['user_id']}";
                }
                
                if ($auto_fix) {
                    // Mark as inactive instead of deleting
                    $orphan_ids = array_column($orphaned_epis, 'id');
                    $this->db->query(
                        "UPDATE epic_epis_accounts 
                         SET status = 'inactive' 
                         WHERE id IN (" . str_repeat('?,', count($orphan_ids) - 1) . "?)",
                        $orphan_ids
                    );
                    $check['fixes_applied'][] = "Marked " . count($orphaned_epis) . " orphaned EPIS as inactive";
                }
            }
            
            // Check for users without EPIS accounts (should have at least one)
            $users_without_epis = $this->db->select(
                "SELECT u.id, u.name, u.email 
                 FROM epic_users u 
                 LEFT JOIN epic_epis_accounts ea ON u.id = ea.user_id 
                 WHERE ea.user_id IS NULL AND u.status = 'active'"
            );
            
            if (!empty($users_without_epis)) {
                $check['status'] = 'warning';
                foreach ($users_without_epis as $user) {
                    $check['issues'][] = "Active user {$user['name']} ({$user['email']}) has no EPIS accounts";
                }
                // Note: This might be intentional, so we don't auto-fix
            }
            
        } catch (Exception $e) {
            $check['status'] = 'error';
            $check['issues'][] = "Error checking user-EPIS relationships: " . $e->getMessage();
        }
        
        $this->results['checks'][] = $check;
        echo "   " . ($check['status'] === 'passed' ? '✓' : ($check['status'] === 'warning' ? '⚠️' : '❌')) . " User-EPIS relationships\n";
    }
    
    /**
     * Check EPIS counter consistency
     */
    private function checkEpisCounterConsistency($auto_fix = false) {
        echo "3. Checking EPIS counter consistency...\n";
        
        $check = [
            'name' => 'epis_counter_consistency',
            'status' => 'passed',
            'issues' => [],
            'fixes_applied' => []
        ];
        
        try {
            // Check for unrealistic counter values
            $unrealistic_counters = $this->db->select(
                "SELECT id, epis_code, current_epic_count 
                 FROM epic_epis_accounts 
                 WHERE current_epic_count > 10000"
            );
            
            if (!empty($unrealistic_counters)) {
                $check['status'] = 'warning';
                foreach ($unrealistic_counters as $unrealistic) {
                    $check['issues'][] = "Unusually high counter {$unrealistic['current_epic_count']} for EPIS {$unrealistic['epis_code']}";
                }
            }
            
            // Check for counters exceeding max_epic_recruits
            $exceeded_limits = $this->db->select(
                "SELECT id, epis_code, current_epic_count, max_epic_recruits 
                 FROM epic_epis_accounts 
                 WHERE current_epic_count > max_epic_recruits AND max_epic_recruits > 0"
            );
            
            if (!empty($exceeded_limits)) {
                $check['status'] = 'failed';
                foreach ($exceeded_limits as $exceeded) {
                    $check['issues'][] = "EPIS {$exceeded['epis_code']}: count={$exceeded['current_epic_count']} exceeds limit={$exceeded['max_epic_recruits']}";
                }
                
                if ($auto_fix) {
                    foreach ($exceeded_limits as $exceeded) {
                        $this->db->update('epic_epis_accounts', 
                            ['current_epic_count' => $exceeded['max_epic_recruits']], 
                            ['id' => $exceeded['id']]
                        );
                    }
                    $check['fixes_applied'][] = "Capped " . count($exceeded_limits) . " counters to their limits";
                }
            }
            
        } catch (Exception $e) {
            $check['status'] = 'error';
            $check['issues'][] = "Error checking counter consistency: " . $e->getMessage();
        }
        
        $this->results['checks'][] = $check;
        echo "   " . ($check['status'] === 'passed' ? '✓' : '❌') . " EPIS counter consistency\n";
    }
    
    /**
     * Check for orphaned records
     */
    private function checkOrphanedRecords($auto_fix = false) {
        echo "4. Checking for orphaned records...\n";
        
        $check = [
            'name' => 'orphaned_records',
            'status' => 'passed',
            'issues' => [],
            'fixes_applied' => []
        ];
        
        try {
            // Check for orphaned activity logs
            if ($this->tableExists('epic_activity_logs')) {
                $orphaned_logs = $this->db->select(
                    "SELECT COUNT(*) as count 
                     FROM epic_activity_logs al 
                     LEFT JOIN epic_users u ON al.user_id = u.id 
                     WHERE u.id IS NULL"
                );
                
                if ($orphaned_logs[0]['count'] > 0) {
                    $check['status'] = 'warning';
                    $check['issues'][] = "Found {$orphaned_logs[0]['count']} orphaned activity logs";
                    
                    if ($auto_fix) {
                        $this->db->query(
                            "DELETE al FROM epic_activity_logs al 
                             LEFT JOIN epic_users u ON al.user_id = u.id 
                             WHERE u.id IS NULL"
                        );
                        $check['fixes_applied'][] = "Removed {$orphaned_logs[0]['count']} orphaned activity logs";
                    }
                }
            }
            
            // Check for orphaned system alerts
            if ($this->tableExists('epic_system_alerts')) {
                $old_alerts = $this->db->select(
                    "SELECT COUNT(*) as count 
                     FROM epic_system_alerts 
                     WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY) 
                     AND status = 'resolved'"
                );
                
                if ($old_alerts[0]['count'] > 0) {
                    $check['status'] = 'info';
                    $check['issues'][] = "Found {$old_alerts[0]['count']} old resolved alerts (>30 days)";
                    
                    if ($auto_fix) {
                        $this->db->query(
                            "DELETE FROM epic_system_alerts 
                             WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY) 
                             AND status = 'resolved'"
                        );
                        $check['fixes_applied'][] = "Cleaned up {$old_alerts[0]['count']} old resolved alerts";
                    }
                }
            }
            
        } catch (Exception $e) {
            $check['status'] = 'error';
            $check['issues'][] = "Error checking orphaned records: " . $e->getMessage();
        }
        
        $this->results['checks'][] = $check;
        echo "   " . ($check['status'] === 'passed' ? '✓' : ($check['status'] === 'warning' ? '⚠️' : '❌')) . " Orphaned records\n";
    }
    
    /**
     * Check data constraints
     */
    private function checkDataConstraints($auto_fix = false) {
        echo "5. Checking data constraints...\n";
        
        $check = [
            'name' => 'data_constraints',
            'status' => 'passed',
            'issues' => [],
            'fixes_applied' => []
        ];
        
        try {
            // Check email format in users table
            $invalid_emails = $this->db->select(
                "SELECT id, email FROM epic_users 
                 WHERE email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$'"
            );
            
            if (!empty($invalid_emails)) {
                $check['status'] = 'failed';
                foreach ($invalid_emails as $invalid) {
                    $check['issues'][] = "Invalid email format: {$invalid['email']} (User ID: {$invalid['id']})";
                }
                // Note: Email format issues require manual review, no auto-fix
            }
            
            // Check for empty required fields
            $empty_names = $this->db->select(
                "SELECT id, email FROM epic_users 
                 WHERE name IS NULL OR name = '' OR TRIM(name) = ''"
            );
            
            if (!empty($empty_names)) {
                $check['status'] = 'failed';
                foreach ($empty_names as $empty) {
                    $check['issues'][] = "Empty name for user: {$empty['email']} (ID: {$empty['id']})";
                }
                
                if ($auto_fix) {
                    $this->db->query(
                        "UPDATE epic_users 
                         SET name = CONCAT('User ', id) 
                         WHERE name IS NULL OR name = '' OR TRIM(name) = ''"
                    );
                    $check['fixes_applied'][] = "Generated names for " . count($empty_names) . " users";
                }
            }
            
        } catch (Exception $e) {
            $check['status'] = 'error';
            $check['issues'][] = "Error checking data constraints: " . $e->getMessage();
        }
        
        $this->results['checks'][] = $check;
        echo "   " . ($check['status'] === 'passed' ? '✓' : '❌') . " Data constraints\n";
    }
    
    /**
     * Check referential integrity
     */
    private function checkReferentialIntegrity($auto_fix = false) {
        echo "6. Checking referential integrity...\n";
        
        $check = [
            'name' => 'referential_integrity',
            'status' => 'passed',
            'issues' => [],
            'fixes_applied' => []
        ];
        
        try {
            // Check EPIS accounts with invalid user_id references
            $invalid_user_refs = $this->db->select(
                "SELECT ea.id, ea.epis_code, ea.user_id 
                 FROM epic_epis_accounts ea 
                 LEFT JOIN epic_users u ON ea.user_id = u.id 
                 WHERE ea.user_id IS NOT NULL AND u.id IS NULL"
            );
            
            if (!empty($invalid_user_refs)) {
                $check['status'] = 'failed';
                foreach ($invalid_user_refs as $invalid) {
                    $check['issues'][] = "EPIS {$invalid['epis_code']} references non-existent user ID {$invalid['user_id']}";
                }
                
                if ($auto_fix) {
                    // Set status to inactive instead of deleting
                    $invalid_ids = array_column($invalid_user_refs, 'id');
                    $this->db->query(
                        "UPDATE epic_epis_accounts 
                         SET status = 'terminated' 
                         WHERE id IN (" . str_repeat('?,', count($invalid_ids) - 1) . "?)",
                        $invalid_ids
                    );
                    $check['fixes_applied'][] = "Terminated " . count($invalid_user_refs) . " EPIS accounts with invalid user references";
                }
            }
            
            // Check for activated_by references to non-existent users
            $invalid_activators = $this->db->select(
                "SELECT ea.id, ea.epis_code, ea.activated_by 
                 FROM epic_epis_accounts ea 
                 LEFT JOIN epic_users u ON ea.activated_by = u.id 
                 WHERE ea.activated_by IS NOT NULL AND u.id IS NULL"
            );
            
            if (!empty($invalid_activators)) {
                $check['status'] = 'warning';
                foreach ($invalid_activators as $invalid) {
                    $check['issues'][] = "EPIS {$invalid['epis_code']} activated_by references non-existent user ID {$invalid['activated_by']}";
                }
                
                if ($auto_fix) {
                    $this->db->query(
                        "UPDATE epic_epis_accounts 
                         SET activated_by = NULL 
                         WHERE activated_by NOT IN (SELECT id FROM epic_users)"
                    );
                    $check['fixes_applied'][] = "Cleared invalid activated_by references for " . count($invalid_activators) . " EPIS accounts";
                }
            }
            
        } catch (Exception $e) {
            $check['status'] = 'error';
            $check['issues'][] = "Error checking referential integrity: " . $e->getMessage();
        }
        
        $this->results['checks'][] = $check;
        echo "   " . ($check['status'] === 'passed' ? '✓' : '❌') . " Referential integrity\n";
    }
    
    /**
     * Generate comprehensive report
     */
    private function generateReport() {
        echo "\n=== INTEGRITY CHECK REPORT ===\n";
        echo "Timestamp: " . $this->results['timestamp'] . "\n";
        echo "Auto-fix enabled: " . ($this->results['auto_fix'] ? 'Yes' : 'No') . "\n\n";
        
        $total_checks = count($this->results['checks']);
        $passed = 0;
        $warnings = 0;
        $failed = 0;
        $errors = 0;
        
        foreach ($this->results['checks'] as $check) {
            switch ($check['status']) {
                case 'passed': $passed++; break;
                case 'warning': $warnings++; break;
                case 'failed': $failed++; break;
                case 'error': $errors++; break;
            }
        }
        
        echo "Summary:\n";
        echo "- Total checks: $total_checks\n";
        echo "- Passed: $passed\n";
        echo "- Warnings: $warnings\n";
        echo "- Failed: $failed\n";
        echo "- Errors: $errors\n\n";
        
        if ($failed > 0 || $errors > 0) {
            echo "❌ Database integrity issues found!\n";
            if (!$this->results['auto_fix']) {
                echo "Run with --fix parameter to automatically fix issues.\n";
            }
        } else if ($warnings > 0) {
            echo "⚠️ Database integrity mostly good with some warnings.\n";
        } else {
            echo "✅ Database integrity is excellent!\n";
        }
        
        // Detailed report
        echo "\nDetailed Report:\n";
        foreach ($this->results['checks'] as $check) {
            if (!empty($check['issues']) || !empty($check['fixes_applied'])) {
                echo "\n" . strtoupper($check['name']) . ":\n";
                
                if (!empty($check['issues'])) {
                    echo "Issues found:\n";
                    foreach ($check['issues'] as $issue) {
                        echo "  - $issue\n";
                    }
                }
                
                if (!empty($check['fixes_applied'])) {
                    echo "Fixes applied:\n";
                    foreach ($check['fixes_applied'] as $fix) {
                        echo "  ✓ $fix\n";
                    }
                }
            }
        }
    }
    
    /**
     * Check if table exists
     */
    private function tableExists($table_name) {
        try {
            $result = $this->db->select("SHOW TABLES LIKE ?", [$table_name]);
            return !empty($result);
        } catch (Exception $e) {
            return false;
        }
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $auto_fix = in_array('--fix', $argv) || in_array('-f', $argv);
    
    $checker = new EpicDatabaseIntegrityChecker();
    $results = $checker->runFullCheck($auto_fix);
    
    // Exit with appropriate code
    $has_errors = false;
    foreach ($results['checks'] as $check) {
        if ($check['status'] === 'failed' || $check['status'] === 'error') {
            $has_errors = true;
            break;
        }
    }
    
    exit($has_errors ? 1 : 0);
}

// Function for external use
function epic_check_database_integrity($auto_fix = false) {
    $checker = new EpicDatabaseIntegrityChecker();
    return $checker->runFullCheck($auto_fix);
}