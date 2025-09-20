<?php
/**
 * EPIS Counter Monitoring System
 * Monitors and validates EPIS counter integrity
 * Prevents and detects counter mismatches
 */

require_once __DIR__ . '/../bootstrap.php';

class EpicEpisCounterMonitor {
    private $db;
    private $logger;
    private $alerts = [];
    
    public function __construct() {
        $this->db = db();
        $this->logger = null; // Use simple logging for now
    }
    
    /**
     * Simple logging method
     */
    private function log($level, $message, $context = []) {
        // Simple logging to error log
        $logMessage = "[EPIS-MONITOR] [$level] $message";
        if (!empty($context)) {
            $logMessage .= " Context: " . json_encode($context);
        }
        error_log($logMessage);
        
        // Also log to monitoring table
        try {
            $this->db->insert('epic_monitoring_logs', [
                'component' => 'epis-counter-monitor',
                'level' => $level,
                'message' => $message,
                'context' => json_encode($context),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Failed to log to monitoring table: " . $e->getMessage());
        }
    }
    
    /**
     * Run comprehensive EPIS counter integrity check
     */
    public function runIntegrityCheck($auto_fix = false) {
        $this->log('info', 'Starting EPIS counter integrity check', [
            'auto_fix' => $auto_fix,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        $results = [
            'total_epis' => 0,
            'mismatches' => [],
            'fixed' => [],
            'errors' => [],
            'summary' => []
        ];
        
        try {
            // Get all EPIS accounts
            $epis_accounts = $this->db->select(
                "SELECT ea.*, u.name, u.email 
                 FROM epic_epis_accounts ea 
                 LEFT JOIN epic_users u ON ea.user_id = u.id 
                 WHERE ea.status = 'active'"
            );
            
            $results['total_epis'] = count($epis_accounts);
            
            foreach ($epis_accounts as $epis) {
                $check_result = $this->checkEpisCounter($epis, $auto_fix);
                
                if ($check_result['has_mismatch']) {
                    $results['mismatches'][] = $check_result;
                    
                    if ($auto_fix && $check_result['fixed']) {
                        $results['fixed'][] = $check_result;
                    }
                }
                
                if (!empty($check_result['errors'])) {
                    $results['errors'] = array_merge($results['errors'], $check_result['errors']);
                }
            }
            
            // Generate summary
            $results['summary'] = $this->generateSummary($results);
            
            // Send alerts if needed
            if (!empty($results['mismatches'])) {
                $this->sendMismatchAlert($results);
            }
            
            $this->log('info', 'EPIS counter integrity check completed', $results['summary']);
            
        } catch (Exception $e) {
            $this->log('error', 'EPIS counter integrity check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $results['errors'][] = $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Check individual EPIS counter
     */
    private function checkEpisCounter($epis, $auto_fix = false) {
        $result = [
            'epis_id' => $epis['id'],
            'epis_name' => $epis['name'],
            'epis_email' => $epis['email'],
            'current_counter' => $epis['current_epic_count'],
            'actual_count' => 0,
            'difference' => 0,
            'has_mismatch' => false,
            'fixed' => false,
            'errors' => []
        ];
        
        try {
            // Count actual members supervised by this EPIS
            $actual_count = $this->db->selectValue(
                "SELECT COUNT(*) FROM epic_users WHERE epis_supervisor_id = ?",
                [$epis['user_id']]
            ) ?: 0;
            
            $result['actual_count'] = $actual_count;
            $result['difference'] = $epis['current_epic_count'] - $actual_count;
            $result['has_mismatch'] = ($result['difference'] != 0);
            
            if ($result['has_mismatch']) {
                $this->log('warning', 'EPIS counter mismatch detected', [
                    'epis_id' => $epis['id'],
                    'epis_name' => $epis['name'],
                    'counter' => $epis['current_epic_count'],
                    'actual' => $actual_count,
                    'difference' => $result['difference']
                ]);
                
                if ($auto_fix) {
                    $result['fixed'] = $this->fixEpisCounter($epis['id'], $actual_count);
                }
            }
            
        } catch (Exception $e) {
            $result['errors'][] = $e->getMessage();
            $this->log('error', 'Error checking EPIS counter', [
                'epis_id' => $epis['id'],
                'error' => $e->getMessage()
            ]);
        }
        
        return $result;
    }
    
    /**
     * Fix EPIS counter mismatch
     */
    private function fixEpisCounter($epis_id, $correct_count) {
        try {
            $updated = $this->db->update('epic_epis_accounts', [
                'current_epic_count' => $correct_count,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$epis_id]);
            
            if ($updated) {
                $this->log('info', 'EPIS counter fixed', [
                    'epis_id' => $epis_id,
                    'new_count' => $correct_count
                ]);
                return true;
            }
            
        } catch (Exception $e) {
            $this->log('error', 'Failed to fix EPIS counter', [
                'epis_id' => $epis_id,
                'error' => $e->getMessage()
            ]);
        }
        
        return false;
    }
    
    /**
     * Generate summary report
     */
    private function generateSummary($results) {
        return [
            'total_epis_checked' => $results['total_epis'],
            'mismatches_found' => count($results['mismatches']),
            'auto_fixed' => count($results['fixed']),
            'errors_encountered' => count($results['errors']),
            'integrity_status' => empty($results['mismatches']) ? 'HEALTHY' : 'ISSUES_FOUND',
            'check_timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Send mismatch alert
     */
    private function sendMismatchAlert($results) {
        $alert_data = [
            'type' => 'EPIS_COUNTER_MISMATCH',
            'severity' => 'HIGH',
            'summary' => $results['summary'],
            'mismatches' => $results['mismatches'],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Log alert
        $this->log('critical', 'EPIS counter mismatch alert', $alert_data);
        
        // Store alert for admin dashboard
        try {
            $this->db->insert('epic_system_alerts', [
                'alert_type' => 'epis_counter_mismatch',
                'severity' => 'high',
                'title' => 'EPIS Counter Mismatch Detected',
                'message' => sprintf(
                    '%d EPIS counter mismatches found. %d total EPIS checked.',
                    count($results['mismatches']),
                    $results['total_epis']
                ),
                'metadata' => json_encode($alert_data),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            $this->log('error', 'Failed to store system alert', [
                'error' => $e->getMessage()
            ]);
        }
        
        $this->alerts[] = $alert_data;
    }
    
    /**
     * Get recent alerts
     */
    public function getRecentAlerts($limit = 10) {
        try {
            return $this->db->select(
                "SELECT * FROM epic_system_alerts 
                 WHERE alert_type = 'epis_counter_mismatch' 
                 ORDER BY created_at DESC 
                 LIMIT ?",
                [$limit]
            );
        } catch (Exception $e) {
            $this->log('error', 'Failed to get recent alerts', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Schedule automated monitoring
     */
    public function scheduleMonitoring() {
        // This would be called by cron job
        $results = $this->runIntegrityCheck(true); // Auto-fix enabled
        
        return [
            'scheduled_at' => date('Y-m-d H:i:s'),
            'results' => $results['summary']
        ];
    }
}

// Global functions for easy access
function epic_check_epis_counters($auto_fix = false) {
    $monitor = new EpicEpisCounterMonitor();
    return $monitor->runIntegrityCheck($auto_fix);
}

function epic_schedule_epis_monitoring() {
    $monitor = new EpicEpisCounterMonitor();
    return $monitor->scheduleMonitoring();
}

function epic_get_epis_alerts($limit = 10) {
    $monitor = new EpicEpisCounterMonitor();
    return $monitor->getRecentAlerts($limit);
}

// CLI execution
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    echo "=== EPIS COUNTER MONITORING SYSTEM ===\n";
    
    $auto_fix = in_array('--fix', $argv);
    $schedule = in_array('--schedule', $argv);
    
    if ($schedule) {
        echo "Running scheduled monitoring...\n";
        $results = epic_schedule_epis_monitoring();
        echo "Scheduled monitoring completed: " . json_encode($results, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "Running integrity check" . ($auto_fix ? " with auto-fix" : "") . "...\n";
        $results = epic_check_epis_counters($auto_fix);
        
        echo "\n=== RESULTS ===\n";
        echo "Total EPIS checked: " . $results['summary']['total_epis_checked'] . "\n";
        echo "Mismatches found: " . $results['summary']['mismatches_found'] . "\n";
        echo "Auto-fixed: " . $results['summary']['auto_fixed'] . "\n";
        echo "Errors: " . $results['summary']['errors_encountered'] . "\n";
        echo "Status: " . $results['summary']['integrity_status'] . "\n";
        
        if (!empty($results['mismatches'])) {
            echo "\n=== MISMATCHES ===\n";
            foreach ($results['mismatches'] as $mismatch) {
                echo sprintf(
                    "EPIS %d (%s): Counter=%d, Actual=%d, Diff=%d%s\n",
                    $mismatch['epis_id'],
                    $mismatch['epis_name'],
                    $mismatch['current_counter'],
                    $mismatch['actual_count'],
                    $mismatch['difference'],
                    $mismatch['fixed'] ? ' [FIXED]' : ''
                );
            }
        }
    }
}
?>