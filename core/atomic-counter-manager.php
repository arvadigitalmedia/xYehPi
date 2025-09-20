<?php
/**
 * EPIC Atomic Counter Manager
 * Mengelola operasi counter EPIS secara atomic dan consistent
 * 
 * @author TRAE AI Assistant
 * @version 1.0.0
 */

require_once __DIR__ . '/../bootstrap.php';

class EpicAtomicCounterManager {
    private $db;
    private $logger;
    
    public function __construct() {
        $this->db = db();
        $this->logger = null; // Use simple logging for now
    }
    
    /**
     * Increment EPIS counter atomically
     */
    public function incrementEpisCounter($epis_id, $amount = 1, $context = []) {
        return $this->updateCounterAtomic($epis_id, $amount, 'increment', $context);
    }
    
    /**
     * Decrement EPIS counter atomically
     */
    public function decrementEpisCounter($epis_id, $amount = 1, $context = []) {
        return $this->updateCounterAtomic($epis_id, -$amount, 'decrement', $context);
    }
    
    /**
     * Set EPIS counter to specific value atomically
     */
    public function setEpisCounter($epis_id, $new_value, $context = []) {
        return $this->updateCounterAtomic($epis_id, null, 'set', $context, $new_value);
    }
    
    /**
     * Reset EPIS counter to zero atomically
     */
    public function resetEpisCounter($epis_id, $context = []) {
        return $this->updateCounterAtomic($epis_id, null, 'reset', $context, 0);
    }
    
    /**
     * Atomic counter update with transaction and locking
     */
    private function updateCounterAtomic($epis_id, $amount, $operation, $context = [], $new_value = null) {
        $this->db->beginTransaction();
        
        try {
            // Lock the EPIS account row for update
            $epis_account = $this->db->select(
                "SELECT id, epis_code, current_epic_count, max_epic_recruits, status 
                 FROM epic_epis_accounts 
                 WHERE id = ? FOR UPDATE",
                [$epis_id]
            );
            
            if (empty($epis_account)) {
                throw new Exception("EPIS account not found: ID $epis_id");
            }
            
            $account = $epis_account[0];
            
            // Check if account is active
            if ($account['status'] !== 'active') {
                throw new Exception("Cannot update counter for inactive EPIS: {$account['epis_code']} (status: {$account['status']})");
            }
            
            $old_count = (int)$account['current_epic_count'];
            $max_recruits = (int)$account['max_epic_recruits'];
            
            // Calculate new counter value
            switch ($operation) {
                case 'increment':
                    $new_count = $old_count + $amount;
                    break;
                case 'decrement':
                    $new_count = $old_count + $amount; // amount is already negative
                    break;
                case 'set':
                case 'reset':
                    $new_count = $new_value;
                    break;
                default:
                    throw new Exception("Invalid operation: $operation");
            }
            
            // Validate new counter value
            if ($new_count < 0) {
                throw new Exception("Counter cannot be negative. Current: $old_count, Attempted: $new_count");
            }
            
            if ($max_recruits > 0 && $new_count > $max_recruits) {
                throw new Exception("Counter cannot exceed max recruits limit. Max: $max_recruits, Attempted: $new_count");
            }
            
            // Update the counter
            $updated = $this->db->update(
                'epic_epis_accounts',
                ['current_epic_count' => $new_count],
                'id = ?',
                [$epis_id]
            );
            
            if (!$updated) {
                throw new Exception("Failed to update counter for EPIS ID $epis_id");
            }
            
            // Log the operation
            $this->logCounterOperation($epis_id, $account['epis_code'], $operation, $old_count, $new_count, $context);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'epis_id' => $epis_id,
                'epis_code' => $account['epis_code'],
                'operation' => $operation,
                'old_count' => $old_count,
                'new_count' => $new_count,
                'amount' => $amount,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            
            $this->log('error', "Atomic counter operation failed", [
                'epis_id' => $epis_id,
                'operation' => $operation,
                'amount' => $amount,
                'error' => $e->getMessage(),
                'context' => $context
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'epis_id' => $epis_id,
                'operation' => $operation,
                'amount' => $amount,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    /**
     * Batch update multiple EPIS counters atomically
     */
    public function batchUpdateCounters($updates) {
        $this->db->beginTransaction();
        $results = [];
        
        try {
            foreach ($updates as $update) {
                $epis_id = $update['epis_id'];
                $operation = $update['operation'];
                $amount = $update['amount'] ?? 1;
                $context = $update['context'] ?? [];
                $new_value = $update['new_value'] ?? null;
                
                // Perform individual atomic update (without separate transaction)
                $result = $this->updateCounterAtomicBatch($epis_id, $amount, $operation, $context, $new_value);
                $results[] = $result;
                
                if (!$result['success']) {
                    throw new Exception("Batch update failed at EPIS ID $epis_id: " . $result['error']);
                }
            }
            
            $this->db->commit();
            
            $this->log('info', "Batch counter update completed", [
                'total_updates' => count($updates),
                'successful' => count(array_filter($results, fn($r) => $r['success']))
            ]);
            
            return [
                'success' => true,
                'total_updates' => count($updates),
                'results' => $results,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            
            $this->log('error', "Batch counter update failed", [
                'total_updates' => count($updates),
                'error' => $e->getMessage(),
                'partial_results' => $results
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'total_updates' => count($updates),
                'partial_results' => $results,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    /**
     * Atomic counter update for batch operations (no separate transaction)
     */
    private function updateCounterAtomicBatch($epis_id, $amount, $operation, $context = [], $new_value = null) {
        try {
            // Lock the EPIS account row for update
            $epis_account = $this->db->select(
                "SELECT id, epis_code, current_epic_count, max_epic_recruits, status 
                 FROM epic_epis_accounts 
                 WHERE id = ? FOR UPDATE",
                [$epis_id]
            );
            
            if (empty($epis_account)) {
                throw new Exception("EPIS account not found: ID $epis_id");
            }
            
            $account = $epis_account[0];
            
            if ($account['status'] !== 'active') {
                throw new Exception("Cannot update counter for inactive EPIS: {$account['epis_code']}");
            }
            
            $old_count = (int)$account['current_epic_count'];
            $max_recruits = (int)$account['max_epic_recruits'];
            
            // Calculate new counter value
            switch ($operation) {
                case 'increment':
                    $new_count = $old_count + $amount;
                    break;
                case 'decrement':
                    $new_count = $old_count + $amount; // amount is already negative
                    break;
                case 'set':
                case 'reset':
                    $new_count = $new_value;
                    break;
                default:
                    throw new Exception("Invalid operation: $operation");
            }
            
            // Validate new counter value
            if ($new_count < 0) {
                throw new Exception("Counter cannot be negative. Current: $old_count, Attempted: $new_count");
            }
            
            if ($max_recruits > 0 && $new_count > $max_recruits) {
                throw new Exception("Counter exceeds max recruits. Max: $max_recruits, Attempted: $new_count");
            }
            
            // Update the counter
            $updated = $this->db->update(
                    'epic_epis_accounts',
                    ['current_epic_count' => $new_count],
                    'id = ?',
                    [$epis_id]
                );
            
            if (!$updated) {
                throw new Exception("Failed to update counter for EPIS ID $epis_id");
            }
            
            return [
                'success' => true,
                'epis_id' => $epis_id,
                'epis_code' => $account['epis_code'],
                'operation' => $operation,
                'old_count' => $old_count,
                'new_count' => $new_count,
                'amount' => $amount
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'epis_id' => $epis_id,
                'operation' => $operation,
                'amount' => $amount
            ];
        }
    }
    
    /**
     * Get current counter value safely
     */
    public function getCurrentCount($epis_id) {
        try {
            $result = $this->db->select(
                "SELECT current_epic_count FROM epic_epis_accounts WHERE id = ?",
                [$epis_id]
            );
            
            if (empty($result)) {
                return null;
            }
            
            return (int)$result[0]['current_epic_count'];
            
        } catch (Exception $e) {
            $this->log('error', "Failed to get current count", [
                'epis_id' => $epis_id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Validate counter integrity for specific EPIS
     */
    public function validateCounterIntegrity($epis_id) {
        try {
            $account = $this->db->select(
                "SELECT id, epis_code, current_epic_count, max_epic_recruits, status 
                 FROM epic_epis_accounts 
                 WHERE id = ?",
                [$epis_id]
            );
            
            if (empty($account)) {
                return [
                    'valid' => false,
                    'error' => 'EPIS account not found'
                ];
            }
            
            $acc = $account[0];
            $issues = [];
            
            // Check for negative counter
            if ($acc['current_epic_count'] < 0) {
                $issues[] = "Negative counter: {$acc['current_epic_count']}";
            }
            
            // Check if exceeds max recruits
            if ($acc['max_epic_recruits'] > 0 && $acc['current_epic_count'] > $acc['max_epic_recruits']) {
                $issues[] = "Counter {$acc['current_epic_count']} exceeds max recruits {$acc['max_epic_recruits']}";
            }
            
            return [
                'valid' => empty($issues),
                'epis_id' => $epis_id,
                'epis_code' => $acc['epis_code'],
                'current_count' => $acc['current_epic_count'],
                'max_recruits' => $acc['max_epic_recruits'],
                'status' => $acc['status'],
                'issues' => $issues
            ];
            
        } catch (Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Log counter operation
     */
    private function logCounterOperation($epis_id, $epis_code, $operation, $old_count, $new_count, $context) {
        $this->log('info', "Counter operation: $operation", [
            'epis_id' => $epis_id,
            'epis_code' => $epis_code,
            'operation' => $operation,
            'old_count' => $old_count,
            'new_count' => $new_count,
            'change' => $new_count - $old_count,
            'context' => $context
        ]);
    }
    
    /**
     * Simple logging method
     */
    private function log($level, $message, $context = []) {
        try {
            $this->db->insert('epic_monitoring_logs', [
                'component' => 'atomic-counter',
                'level' => $level,
                'message' => $message,
                'context' => json_encode($context),
                'check_timestamp' => date('Y-m-d H:i:s'),
                'status' => 'logged',
                'summary' => substr($message, 0, 100),
                'details' => json_encode([
                    'level' => $level,
                    'message' => $message,
                    'context' => $context,
                    'timestamp' => date('Y-m-d H:i:s')
                ])
            ]);
        } catch (Exception $e) {
            // Fallback to error_log if database logging fails
            error_log("EPIC Counter Log [$level]: $message - " . json_encode($context));
        }
    }
}

// CLI testing interface
if (php_sapi_name() === 'cli') {
    echo "=== EPIC ATOMIC COUNTER MANAGER TEST ===\n";
    
    $manager = new EpicAtomicCounterManager();
    
    // Get first EPIS account for testing
    $db = db();
    $test_epis = $db->select("SELECT id, epis_code, current_epic_count FROM epic_epis_accounts WHERE status = 'active' LIMIT 1");
    
    if (empty($test_epis)) {
        echo "❌ No active EPIS accounts found for testing\n";
        exit(1);
    }
    
    $epis = $test_epis[0];
    echo "Testing with EPIS: {$epis['epis_code']} (ID: {$epis['id']})\n";
    echo "Current count: {$epis['current_epic_count']}\n\n";
    
    // Test 1: Increment counter
    echo "1. Testing increment operation...\n";
    $result = $manager->incrementEpisCounter($epis['id'], 1, ['test' => 'increment_test']);
    echo "Result: " . ($result['success'] ? '✓' : '❌') . " " . 
         ($result['success'] ? "Count: {$result['old_count']} → {$result['new_count']}" : "Error: {$result['error']}") . "\n\n";
    
    // Test 2: Decrement counter
    echo "2. Testing decrement operation...\n";
    $result = $manager->decrementEpisCounter($epis['id'], 1, ['test' => 'decrement_test']);
    echo "Result: " . ($result['success'] ? '✓' : '❌') . " " . 
         ($result['success'] ? "Count: {$result['old_count']} → {$result['new_count']}" : "Error: {$result['error']}") . "\n\n";
    
    // Test 3: Validate integrity
    echo "3. Testing integrity validation...\n";
    $validation = $manager->validateCounterIntegrity($epis['id']);
    echo "Result: " . ($validation['valid'] ? '✓' : '❌') . " " . 
         ($validation['valid'] ? "Counter integrity is valid" : "Issues: " . implode(', ', $validation['issues'])) . "\n\n";
    
    // Test 4: Batch update
    echo "4. Testing batch update...\n";
    $batch_updates = [
        [
            'epis_id' => $epis['id'],
            'operation' => 'increment',
            'amount' => 2,
            'context' => ['test' => 'batch_test_1']
        ]
    ];
    
    $batch_result = $manager->batchUpdateCounters($batch_updates);
    echo "Result: " . ($batch_result['success'] ? '✓' : '❌') . " " . 
         ($batch_result['success'] ? "Batch completed: {$batch_result['total_updates']} updates" : "Error: {$batch_result['error']}") . "\n\n";
    
    // Final count
    $final_count = $manager->getCurrentCount($epis['id']);
    echo "Final count: $final_count\n";
    echo "✅ Atomic counter manager test completed\n";
}

// Function for external use
function epic_atomic_counter_manager() {
    return new EpicAtomicCounterManager();
}