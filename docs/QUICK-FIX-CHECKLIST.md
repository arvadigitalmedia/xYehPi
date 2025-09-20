# ⚡ QUICK FIX CHECKLIST - SISTEM REGISTRASI

> **Estimasi Waktu**: 2-4 jam  
> **Skill Level**: Intermediate PHP/MySQL  
> **Risk Level**: Low-Medium  

## 🎯 **FIXES YANG BISA DILAKUKAN HARI INI**

### ✅ **1. DATABASE CONSTRAINTS (15 menit)**

```sql
-- BACKUP DATABASE DULU!
-- mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

-- Tambah unique constraints
ALTER TABLE epic_users ADD UNIQUE KEY unique_email (email);
ALTER TABLE epic_users ADD UNIQUE KEY unique_referral_code (referral_code);

-- Tambah indexes untuk performance
ALTER TABLE epic_users ADD INDEX idx_status (status);
ALTER TABLE epic_users ADD INDEX idx_created_at (created_at);
ALTER TABLE epic_epis_accounts ADD INDEX idx_status_count (status, current_epic_count);

-- Verify
SHOW INDEX FROM epic_users;
SHOW INDEX FROM epic_epis_accounts;
```

**Test Command:**
```bash
# Test duplicate email prevention
curl -X POST http://localhost/test-bisnisemasperak/register \
  -d "email=test@example.com&name=Test1&password=123456"
curl -X POST http://localhost/test-bisnisemasperak/register \
  -d "email=test@example.com&name=Test2&password=123456"
# Second request should fail
```

---

### ✅ **2. ATOMIC EPIS COUNTER UPDATE (10 menit)**

**File**: `History Update System/01-EPIS-Supervisor-System/functions.php.backup`

```php
// FIND (around line 340):
db()->update(TABLE_USERS, [
    'epis_supervisor_id' => $epis_supervisor_id,
    'updated_at' => date('Y-m-d H:i:s')
], 'id = ?', [$user_id]);

// Update EPIS supervisor count
db()->query(
    "UPDATE epic_epis_accounts SET current_epic_count = current_epic_count + 1, updated_at = NOW() WHERE user_id = ?",
    [$epis_supervisor_id]
);

// REPLACE WITH:
// Update user and EPIS count atomically
db()->beginTransaction();
try {
    db()->update(TABLE_USERS, [
        'epis_supervisor_id' => $epis_supervisor_id,
        'updated_at' => date('Y-m-d H:i:s')
    ], 'id = ?', [$user_id]);
    
    $affected = db()->query(
        "UPDATE epic_epis_accounts SET current_epic_count = current_epic_count + 1, updated_at = NOW() WHERE user_id = ?",
        [$epis_supervisor_id]
    );
    
    if ($affected === 0) {
        throw new Exception("EPIS supervisor not found or inactive");
    }
    
    db()->commit();
} catch (Exception $e) {
    db()->rollback();
    throw new Exception("Failed to assign EPIS supervisor: " . $e->getMessage());
}
```

---

### ✅ **3. ENHANCED ERROR LOGGING (20 menit)**

**File**: `core/error-handler.php` (create if not exists)

```php
<?php
/**
 * Enhanced Error Logging for Registration System
 */

if (!function_exists('epic_log_registration_error')) {
    function epic_log_registration_error($error, $context = [], $level = 'ERROR') {
        $log_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'error' => is_string($error) ? $error : $error->getMessage(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 200),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'context' => $context,
            'memory_usage' => memory_get_usage(true),
            'request_id' => uniqid('req_', true)
        ];
        
        $log_line = '[' . $log_data['timestamp'] . '] ' . $log_data['level'] . ': ' . 
                   $log_data['error'] . ' | IP: ' . $log_data['ip'] . 
                   ' | ReqID: ' . $log_data['request_id'] . 
                   ' | Context: ' . json_encode($log_data['context']);
        
        error_log($log_line);
        
        // Store in database for monitoring (optional)
        try {
            db()->insert('epic_error_logs', [
                'level' => $log_data['level'],
                'message' => $log_data['error'],
                'context' => json_encode($log_data),
                'ip_address' => $log_data['ip'],
                'request_id' => $log_data['request_id'],
                'created_at' => $log_data['timestamp']
            ]);
        } catch (Exception $e) {
            // Fallback to file logging only
            error_log('Failed to log to database: ' . $e->getMessage());
        }
    }
}

if (!function_exists('epic_log_registration_success')) {
    function epic_log_registration_success($user_id, $context = []) {
        epic_log_registration_error("Registration successful for user ID: {$user_id}", $context, 'INFO');
    }
}
?>
```

**Update Registration Controller** (`core/registration-controller.php`):

```php
// ADD at top after includes:
require_once EPIC_ROOT . '/core/error-handler.php';

// FIND (around line 110):
} catch (Exception $e) {
    $this->error = $e->getMessage();
}

// REPLACE WITH:
} catch (Exception $e) {
    epic_log_registration_error($e, [
        'post_data' => array_diff_key($_POST, ['password' => '', 'confirm_password' => '']),
        'referral_code' => $this->referral_code,
        'step' => 'form_submission'
    ]);
    
    // Generic user message
    $this->error = 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.';
}

// FIND successful registration (around line 175):
$this->handleSuccessfulRegistration($registration_result);

// ADD AFTER:
epic_log_registration_success($registration_result['user_id'], [
    'referral_code' => $referral_code,
    'epis_supervisor_id' => $registration_result['epis_supervisor_id'] ?? null
]);
```

---

### ✅ **4. IMPROVED RATE LIMITING (30 menit)**

**File**: `core/rate-limiter.php`

```php
// ADD new function:
if (!function_exists('epic_check_enhanced_registration_rate_limit')) {
    function epic_check_enhanced_registration_rate_limit() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $email = $_POST['email'] ?? '';
        
        // IP-based rate limiting (5 attempts per hour)
        $ip_key = "rate_limit:registration:ip:{$ip}";
        $ip_attempts = epic_cache_get($ip_key, 0);
        
        if ($ip_attempts >= 5) {
            epic_log_registration_error("Rate limit exceeded for IP: {$ip}", ['attempts' => $ip_attempts], 'WARNING');
            throw new Exception('Terlalu banyak percobaan registrasi dari IP ini. Coba lagi dalam 1 jam.');
        }
        
        // Email-based rate limiting (3 attempts per hour)
        if (!empty($email)) {
            $email_key = "rate_limit:registration:email:" . md5(strtolower($email));
            $email_attempts = epic_cache_get($email_key, 0);
            
            if ($email_attempts >= 3) {
                epic_log_registration_error("Rate limit exceeded for email: {$email}", ['attempts' => $email_attempts], 'WARNING');
                throw new Exception('Terlalu banyak percobaan registrasi dengan email ini. Coba lagi dalam 1 jam.');
            }
            
            // Increment email counter
            epic_cache_set($email_key, $email_attempts + 1, 3600);
        }
        
        // Increment IP counter
        epic_cache_set($ip_key, $ip_attempts + 1, 3600);
    }
}

// Simple cache functions if not exists
if (!function_exists('epic_cache_get')) {
    function epic_cache_get($key, $default = null) {
        // Simple file-based cache
        $cache_file = EPIC_ROOT . '/cache/' . md5($key) . '.cache';
        
        if (!file_exists($cache_file)) {
            return $default;
        }
        
        $data = json_decode(file_get_contents($cache_file), true);
        
        if ($data && $data['expires'] > time()) {
            return $data['value'];
        }
        
        // Expired, delete file
        @unlink($cache_file);
        return $default;
    }
}

if (!function_exists('epic_cache_set')) {
    function epic_cache_set($key, $value, $ttl = 3600) {
        $cache_dir = EPIC_ROOT . '/cache';
        if (!is_dir($cache_dir)) {
            mkdir($cache_dir, 0755, true);
        }
        
        $cache_file = $cache_dir . '/' . md5($key) . '.cache';
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        file_put_contents($cache_file, json_encode($data));
    }
}
```

**Update Registration Controller**:

```php
// FIND (around line 95):
epic_check_registration_rate_limit();

// REPLACE WITH:
epic_check_enhanced_registration_rate_limit();
```

---

### ✅ **5. INPUT SANITIZATION AUDIT (25 menit)**

**File**: `core/csrf-protection.php`

```php
// FIND epic_sanitize_input function and ENHANCE:
function epic_sanitize_input($value, $type = 'string') {
    if (is_array($value)) {
        return array_map(function($item) use ($type) {
            return epic_sanitize_input($item, $type);
        }, $value);
    }
    
    // Remove null bytes
    $value = str_replace("\0", '', $value);
    
    switch ($type) {
        case 'email':
            $value = filter_var(trim($value), FILTER_SANITIZE_EMAIL);
            $value = strtolower($value);
            break;
            
        case 'phone':
            // Remove all non-numeric except + and spaces
            $value = preg_replace('/[^0-9+\s-]/', '', trim($value));
            break;
            
        case 'name':
            // Allow letters, spaces, apostrophes, hyphens, dots
            $value = preg_replace('/[^a-zA-Z\s\'\-\.]/', '', trim($value));
            $value = preg_replace('/\s+/', ' ', $value); // Multiple spaces to single
            break;
            
        case 'referral_code':
            $value = strtoupper(preg_replace('/[^A-Z0-9]/', '', trim($value)));
            break;
            
        case 'string':
        default:
            $value = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            break;
    }
    
    return $value;
}
```

---

## 🧪 **TESTING COMMANDS**

### **1. Test Database Constraints**
```bash
# Test duplicate email
mysql -u root -p epic_database -e "
INSERT INTO epic_users (name, email, password_hash, referral_code) 
VALUES ('Test1', 'test@example.com', 'hash1', 'TEST001');
INSERT INTO epic_users (name, email, password_hash, referral_code) 
VALUES ('Test2', 'test@example.com', 'hash2', 'TEST002');
"
# Should fail on second insert
```

### **2. Test Rate Limiting**
```bash
# Rapid fire requests
for i in {1..6}; do
  curl -X POST http://localhost/test-bisnisemasperak/register \
    -d "name=Test$i&email=test$i@example.com&password=123456" \
    -w "Response $i: %{http_code}\n"
done
# Should block after 5 attempts
```

### **3. Test Error Logging**
```bash
# Check error log
tail -f /path/to/php/error.log | grep "REGISTRATION"

# Check cache directory
ls -la cache/
```

---

## 📊 **VERIFICATION CHECKLIST**

- [ ] Database constraints applied successfully
- [ ] Duplicate email registration blocked
- [ ] EPIS counter updates atomically
- [ ] Error logging working (check logs)
- [ ] Rate limiting active (test with curl)
- [ ] Input sanitization enhanced
- [ ] No new errors in PHP error log
- [ ] Registration still works for valid data
- [ ] Performance not degraded (test registration time)

---

## 🔄 **ROLLBACK PLAN**

```sql
-- If something goes wrong, rollback database changes:
ALTER TABLE epic_users DROP INDEX unique_email;
ALTER TABLE epic_users DROP INDEX unique_referral_code;
ALTER TABLE epic_users DROP INDEX idx_status;
ALTER TABLE epic_users DROP INDEX idx_created_at;
ALTER TABLE epic_epis_accounts DROP INDEX idx_status_count;
```

```bash
# Restore code from git
git checkout HEAD -- core/registration-controller.php
git checkout HEAD -- core/rate-limiter.php
git checkout HEAD -- core/csrf-protection.php

# Remove new files
rm -f core/error-handler.php
rm -rf cache/
```

---

> **⚠️ PENTING**: Test semua perubahan di development environment dulu sebelum apply ke production!

**Estimasi Total Waktu**: 2-4 jam  
**Risk Level**: Low (mostly additions, minimal core changes)  
**Rollback Time**: 15 menit