<?php
/**
 * EPIC Input Sanitization Audit & Fix Script
 * Mengaudit dan memperbaiki kerentanan input sanitization
 * 
 * @author TRAE AI Assistant
 * @version 1.0.0
 */

require_once 'bootstrap.php';

class EpicInputSanitizationAuditor {
    private $vulnerabilities = [];
    private $fixes_applied = 0;
    private $scan_paths = [
        'core/',
        'api/',
        'themes/modern/',
        'admin/',
        'plugins/'
    ];
    
    public function runAudit() {
        echo "=== EPIC INPUT SANITIZATION AUDIT ===\n";
        echo "Scanning for input sanitization vulnerabilities...\n\n";
        
        $this->scanForVulnerabilities();
        $this->generateReport();
        $this->applyFixes();
        
        echo "\n=== AUDIT COMPLETE ===\n";
        echo "Vulnerabilities found: " . count($this->vulnerabilities) . "\n";
        echo "Fixes applied: " . $this->fixes_applied . "\n";
    }
    
    private function scanForVulnerabilities() {
        foreach ($this->scan_paths as $path) {
            if (is_dir($path)) {
                $this->scanDirectory($path);
            }
        }
    }
    
    private function scanDirectory($dir) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $this->scanFile($file->getPathname());
            }
        }
    }
    
    private function scanFile($filepath) {
        $content = file_get_contents($filepath);
        $lines = explode("\n", $content);
        
        foreach ($lines as $line_num => $line) {
            $line_num++; // 1-indexed
            
            // Check for direct $_POST, $_GET, $_REQUEST usage without sanitization
            if (preg_match('/\$_(POST|GET|REQUEST|COOKIE)\[/', $line) && 
                !preg_match('/(htmlspecialchars|filter_var|trim|strip_tags|mysqli_real_escape_string|addslashes)/', $line)) {
                
                $this->vulnerabilities[] = [
                    'type' => 'unsanitized_input',
                    'file' => $filepath,
                    'line' => $line_num,
                    'code' => trim($line),
                    'severity' => 'high'
                ];
            }
            
            // Check for SQL injection vulnerabilities
            if (preg_match('/\$.*query.*=.*["\'].*\$/', $line) && 
                !preg_match('/(prepare|bind_param|\?)/', $line)) {
                
                $this->vulnerabilities[] = [
                    'type' => 'sql_injection',
                    'file' => $filepath,
                    'line' => $line_num,
                    'code' => trim($line),
                    'severity' => 'critical'
                ];
            }
            
            // Check for XSS vulnerabilities
            if (preg_match('/echo.*\$_(POST|GET|REQUEST)/', $line) && 
                !preg_match('/htmlspecialchars/', $line)) {
                
                $this->vulnerabilities[] = [
                    'type' => 'xss',
                    'file' => $filepath,
                    'line' => $line_num,
                    'code' => trim($line),
                    'severity' => 'high'
                ];
            }
            
            // Check for file upload vulnerabilities
            if (preg_match('/\$_FILES.*\[.*name.*\]/', $line) && 
                !preg_match('/(pathinfo|basename|filter_var)/', $line)) {
                
                $this->vulnerabilities[] = [
                    'type' => 'file_upload',
                    'file' => $filepath,
                    'line' => $line_num,
                    'code' => trim($line),
                    'severity' => 'medium'
                ];
            }
        }
    }
    
    private function generateReport() {
        echo "VULNERABILITY REPORT:\n";
        echo str_repeat("=", 50) . "\n";
        
        $severity_counts = ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0];
        
        foreach ($this->vulnerabilities as $vuln) {
            $severity_counts[$vuln['severity']]++;
            
            echo "\n[{$vuln['severity']}] {$vuln['type']}\n";
            echo "File: {$vuln['file']}:{$vuln['line']}\n";
            echo "Code: {$vuln['code']}\n";
            echo str_repeat("-", 30) . "\n";
        }
        
        echo "\nSUMMARY BY SEVERITY:\n";
        foreach ($severity_counts as $severity => $count) {
            if ($count > 0) {
                echo "- " . ucfirst($severity) . ": {$count}\n";
            }
        }
    }
    
    private function applyFixes() {
        echo "\nAPPLYING AUTOMATIC FIXES:\n";
        echo str_repeat("=", 30) . "\n";
        
        // Create enhanced sanitization functions
        $this->createSanitizationFunctions();
        
        // Apply common fixes
        $this->fixCommonVulnerabilities();
        
        echo "\nFixes applied: {$this->fixes_applied}\n";
        echo "Manual review required for remaining vulnerabilities.\n";
    }
    
    private function createSanitizationFunctions() {
        $sanitization_file = 'core/input-sanitizer.php';
        
        if (!file_exists($sanitization_file)) {
            $content = '<?php
/**
 * EPIC Input Sanitization Functions
 * Enhanced input sanitization and validation
 */

/**
 * Sanitize string input
 */
function epic_sanitize_string($input, $max_length = 255) {
    if (!is_string($input)) return "";
    
    $sanitized = trim($input);
    $sanitized = strip_tags($sanitized);
    $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, "UTF-8");
    
    if ($max_length > 0) {
        $sanitized = substr($sanitized, 0, $max_length);
    }
    
    return $sanitized;
}

/**
 * Sanitize email input
 */
function epic_sanitize_email($email) {
    $email = trim($email);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    return strtolower($email);
}

/**
 * Sanitize phone number
 */
function epic_sanitize_phone($phone) {
    $phone = preg_replace("/[^0-9+\-\(\)\s]/", "", $phone);
    $phone = trim($phone);
    
    return $phone;
}

/**
 * Sanitize integer input
 */
function epic_sanitize_int($input, $min = null, $max = null) {
    $value = filter_var($input, FILTER_VALIDATE_INT);
    
    if ($value === false) {
        return 0;
    }
    
    if ($min !== null && $value < $min) {
        return $min;
    }
    
    if ($max !== null && $value > $max) {
        return $max;
    }
    
    return $value;
}

/**
 * Sanitize filename for uploads
 */
function epic_sanitize_filename($filename) {
    $filename = basename($filename);
    $filename = preg_replace("/[^a-zA-Z0-9\-_\.]/", "", $filename);
    $filename = trim($filename, ".");
    
    if (empty($filename)) {
        $filename = "file_" . time();
    }
    
    return $filename;
}

/**
 * Sanitize URL input
 */
function epic_sanitize_url($url) {
    $url = trim($url);
    $url = filter_var($url, FILTER_SANITIZE_URL);
    
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    return $url;
}

/**
 * Sanitize HTML content (for rich text)
 */
function epic_sanitize_html($html) {
    $allowed_tags = "<p><br><strong><em><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6>";
    
    $html = strip_tags($html, $allowed_tags);
    $html = htmlspecialchars_decode($html);
    $html = htmlspecialchars($html, ENT_QUOTES, "UTF-8");
    
    return $html;
}

/**
 * Comprehensive input sanitization
 */
function epic_sanitize_input($input, $type = "string", $options = []) {
    switch ($type) {
        case "email":
            return epic_sanitize_email($input);
        case "phone":
            return epic_sanitize_phone($input);
        case "int":
            return epic_sanitize_int($input, $options["min"] ?? null, $options["max"] ?? null);
        case "filename":
            return epic_sanitize_filename($input);
        case "url":
            return epic_sanitize_url($input);
        case "html":
            return epic_sanitize_html($input);
        case "string":
        default:
            return epic_sanitize_string($input, $options["max_length"] ?? 255);
    }
}
';
            
            file_put_contents($sanitization_file, $content);
            $this->fixes_applied++;
            echo "✓ Created enhanced sanitization functions\n";
        }
    }
    
    private function fixCommonVulnerabilities() {
        // This would contain specific fixes for identified vulnerabilities
        // For now, we'll just report what needs manual fixing
        
        $critical_files = [];
        $high_priority_files = [];
        
        foreach ($this->vulnerabilities as $vuln) {
            if ($vuln['severity'] === 'critical') {
                $critical_files[] = $vuln['file'];
            } elseif ($vuln['severity'] === 'high') {
                $high_priority_files[] = $vuln['file'];
            }
        }
        
        $critical_files = array_unique($critical_files);
        $high_priority_files = array_unique($high_priority_files);
        
        if (!empty($critical_files)) {
            echo "\n⚠️  CRITICAL FILES REQUIRING IMMEDIATE ATTENTION:\n";
            foreach ($critical_files as $file) {
                echo "- {$file}\n";
            }
        }
        
        if (!empty($high_priority_files)) {
            echo "\n⚠️  HIGH PRIORITY FILES:\n";
            foreach ($high_priority_files as $file) {
                echo "- {$file}\n";
            }
        }
    }
}

// Run the audit
try {
    $auditor = new EpicInputSanitizationAuditor();
    $auditor->runAudit();
} catch (Exception $e) {
    echo "Error during audit: " . $e->getMessage() . "\n";
    exit(1);
}