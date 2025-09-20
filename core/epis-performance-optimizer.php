<?php
/**
 * EPIS Performance Optimizer
 * Script untuk optimasi performa database dan query EPIS
 */

require_once __DIR__ . '/../bootstrap.php';

class EpicPerformanceOptimizer {
    private $db;
    private $results = [];
    
    public function __construct() {
        $this->db = db();
    }
    
    public function runOptimization($auto_apply = false) {
        echo "=== EPIS PERFORMANCE OPTIMIZER ===\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
        echo "Auto-apply: " . ($auto_apply ? 'YES' : 'NO') . "\n\n";
        
        $this->analyzeTableSizes();
        $this->checkIndexes();
        $this->optimizeQueries();
        $this->analyzeSlowQueries();
        $this->checkTableFragmentation();
        
        if ($auto_apply) {
            $this->applyOptimizations();
        }
        
        $this->generateReport();
        
        return $this->results;
    }
    
    private function analyzeTableSizes() {
        echo "1. Analyzing table sizes...\n";
        
        try {
            $tables = $this->db->select("
                SELECT 
                    table_name,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                    table_rows,
                    ROUND((data_length / 1024 / 1024), 2) AS data_mb,
                    ROUND((index_length / 1024 / 1024), 2) AS index_mb
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name LIKE 'epic_%'
                ORDER BY (data_length + index_length) DESC
            ");
            
            $this->results['table_sizes'] = $tables;
            
            foreach ($tables as $table) {
                echo "   {$table['table_name']}: {$table['size_mb']} MB ({$table['table_rows']} rows)\n";
                echo "     Data: {$table['data_mb']} MB, Index: {$table['index_mb']} MB\n";
            }
            
            echo "   ✓ Table analysis completed\n\n";
            
        } catch (Exception $e) {
            echo "   ❌ Error analyzing tables: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function checkIndexes() {
        echo "2. Checking index optimization...\n";
        
        $recommendations = [];
        
        try {
            // Check for missing indexes on frequently queried columns
            $missing_indexes = [
                'epic_users' => ['status', 'last_login_at', 'created_at'],
                'epic_epis_accounts' => ['status', 'user_id', 'current_epic_count'],
                'epic_monitoring_logs' => ['level', 'action_type', 'created_at'],
            ];
            
            foreach ($missing_indexes as $table => $columns) {
                // Get existing indexes
                $existing = $this->db->select("SHOW INDEX FROM $table");
                $existing_columns = array_column($existing, 'Column_name');
                
                foreach ($columns as $column) {
                    if (!in_array($column, $existing_columns)) {
                        $recommendations[] = [
                            'type' => 'missing_index',
                            'table' => $table,
                            'column' => $column,
                            'sql' => "ALTER TABLE $table ADD INDEX idx_{$column} ($column)"
                        ];
                        echo "   ⚠️  Missing index: $table.$column\n";
                    }
                }
            }
            
            // Check for unused indexes
            $all_tables = ['epic_users', 'epic_epis_accounts', 'epic_monitoring_logs'];
            foreach ($all_tables as $table) {
                $indexes = $this->db->select("
                    SELECT DISTINCT index_name, column_name 
                    FROM information_schema.statistics 
                    WHERE table_schema = DATABASE() 
                    AND table_name = '$table'
                    AND index_name != 'PRIMARY'
                ");
                
                foreach ($indexes as $index) {
                    // Simple heuristic: if index name contains 'temp' or 'old', it might be unused
                    if (strpos($index['index_name'], 'temp') !== false || 
                        strpos($index['index_name'], 'old') !== false) {
                        $recommendations[] = [
                            'type' => 'unused_index',
                            'table' => $table,
                            'index' => $index['index_name'],
                            'sql' => "ALTER TABLE $table DROP INDEX {$index['index_name']}"
                        ];
                        echo "   ⚠️  Potentially unused index: $table.{$index['index_name']}\n";
                    }
                }
            }
            
            $this->results['index_recommendations'] = $recommendations;
            echo "   ✓ Index analysis completed (" . count($recommendations) . " recommendations)\n\n";
            
        } catch (Exception $e) {
            echo "   ❌ Error checking indexes: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function optimizeQueries() {
        echo "3. Analyzing query patterns...\n";
        
        $optimizations = [];
        
        try {
            // Test common queries and suggest optimizations
            $test_queries = [
                'active_epis_count' => [
                    'query' => "SELECT COUNT(*) FROM epic_epis_accounts WHERE status = 'active'",
                    'optimization' => 'Consider adding composite index on (status, current_epic_count)'
                ],
                'user_epis_lookup' => [
                    'query' => "SELECT * FROM epic_epis_accounts WHERE user_id = 1",
                    'optimization' => 'Index on user_id exists, query should be fast'
                ],
                'recent_logs' => [
                    'query' => "SELECT * FROM epic_monitoring_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY created_at DESC LIMIT 100",
                    'optimization' => 'Consider composite index on (created_at, level) for filtered queries'
                ]
            ];
            
            foreach ($test_queries as $name => $test) {
                $start_time = microtime(true);
                $this->db->select($test['query']);
                $execution_time = (microtime(true) - $start_time) * 1000;
                
                $optimizations[] = [
                    'query_name' => $name,
                    'execution_time_ms' => round($execution_time, 2),
                    'optimization' => $test['optimization'],
                    'status' => $execution_time < 100 ? 'good' : ($execution_time < 500 ? 'warning' : 'slow')
                ];
                
                $status_icon = $execution_time < 100 ? '✓' : ($execution_time < 500 ? '⚠️' : '❌');
                echo "   $status_icon $name: " . round($execution_time, 2) . "ms\n";
            }
            
            $this->results['query_optimizations'] = $optimizations;
            echo "   ✓ Query analysis completed\n\n";
            
        } catch (Exception $e) {
            echo "   ❌ Error analyzing queries: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function analyzeSlowQueries() {
        echo "4. Checking for slow query patterns...\n";
        
        try {
            // Check if slow query log is enabled
            $slow_log_result = $this->db->selectOne("SHOW VARIABLES LIKE 'slow_query_log'");
            
            if (!$slow_log_result || $slow_log_result['Value'] !== 'ON') {
                echo "   ⚠️  Slow query log is not enabled\n";
                echo "   Recommendation: Enable with SET GLOBAL slow_query_log = 'ON'\n";
            } else {
                echo "   ✓ Slow query log is enabled\n";
            }
            
            // Check long_query_time setting
            $long_query_result = $this->db->selectOne("SHOW VARIABLES LIKE 'long_query_time'");
            if ($long_query_result && floatval($long_query_result['Value']) > 2) {
                echo "   ⚠️  long_query_time is set to {$long_query_result['Value']}s (consider lowering to 1-2s)\n";
            }
            
            echo "   ✓ Slow query analysis completed\n\n";
            
        } catch (Exception $e) {
            echo "   ❌ Error checking slow queries: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function checkTableFragmentation() {
        echo "5. Checking table fragmentation...\n";
        
        try {
            $fragmented_tables = $this->db->select("
                SELECT 
                    table_name,
                    ROUND((data_free / 1024 / 1024), 2) AS fragmentation_mb,
                    ROUND((data_free / (data_length + index_length + data_free)) * 100, 2) AS fragmentation_pct
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name LIKE 'epic_%'
                AND data_free > 0
                ORDER BY data_free DESC
            ");
            
            $this->results['fragmentation'] = $fragmented_tables;
            
            foreach ($fragmented_tables as $table) {
                if ($table['fragmentation_pct'] > 10) {
                    echo "   ⚠️  {$table['table_name']}: {$table['fragmentation_pct']}% fragmented ({$table['fragmentation_mb']} MB)\n";
                    echo "     Recommendation: OPTIMIZE TABLE {$table['table_name']}\n";
                } else {
                    echo "   ✓ {$table['table_name']}: {$table['fragmentation_pct']}% fragmented (acceptable)\n";
                }
            }
            
            echo "   ✓ Fragmentation analysis completed\n\n";
            
        } catch (Exception $e) {
            echo "   ❌ Error checking fragmentation: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function applyOptimizations() {
        echo "6. Applying optimizations...\n";
        
        try {
            // Apply recommended indexes
            if (isset($this->results['index_recommendations'])) {
                foreach ($this->results['index_recommendations'] as $rec) {
                    if ($rec['type'] === 'missing_index') {
                        try {
                            $this->db->query($rec['sql']);
                            echo "   ✓ Applied: {$rec['sql']}\n";
                        } catch (Exception $e) {
                            if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                                echo "   ℹ️  Index already exists: {$rec['table']}.{$rec['column']}\n";
                            } else {
                                echo "   ❌ Failed to apply: {$rec['sql']} - " . $e->getMessage() . "\n";
                            }
                        }
                    }
                }
            }
            
            // Optimize fragmented tables
            if (isset($this->results['fragmentation'])) {
                foreach ($this->results['fragmentation'] as $table) {
                    if ($table['fragmentation_pct'] > 10) {
                        try {
                            $this->db->query("OPTIMIZE TABLE {$table['table_name']}");
                            echo "   ✓ Optimized table: {$table['table_name']}\n";
                        } catch (Exception $e) {
                            echo "   ❌ Failed to optimize: {$table['table_name']} - " . $e->getMessage() . "\n";
                        }
                    }
                }
            }
            
            echo "   ✓ Optimizations applied\n\n";
            
        } catch (Exception $e) {
            echo "   ❌ Error applying optimizations: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function generateReport() {
        echo "=== OPTIMIZATION REPORT ===\n";
        
        // Summary
        $total_size = array_sum(array_column($this->results['table_sizes'] ?? [], 'size_mb'));
        $total_recommendations = count($this->results['index_recommendations'] ?? []);
        
        echo "Database Size: " . round($total_size, 2) . " MB\n";
        echo "Index Recommendations: $total_recommendations\n";
        
        // Performance summary
        if (isset($this->results['query_optimizations'])) {
            $slow_queries = array_filter($this->results['query_optimizations'], function($q) {
                return $q['status'] === 'slow';
            });
            echo "Slow Queries: " . count($slow_queries) . "\n";
        }
        
        echo "\n=== RECOMMENDATIONS ===\n";
        
        // Top recommendations
        $recommendations = [
            "1. Add missing indexes for frequently queried columns",
            "2. Monitor slow query log and optimize queries > 1s",
            "3. Consider partitioning large tables (>1M rows)",
            "4. Implement query result caching for dashboard metrics",
            "5. Regular OPTIMIZE TABLE for tables with >10% fragmentation"
        ];
        
        foreach ($recommendations as $rec) {
            echo "$rec\n";
        }
        
        echo "\n✅ Performance optimization completed!\n";
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $auto_apply = in_array('--apply', $argv);
    $optimizer = new EpicPerformanceOptimizer();
    $optimizer->runOptimization($auto_apply);
}
?>