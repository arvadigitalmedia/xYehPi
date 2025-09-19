<?php
/**
 * Enhanced Referral Code Validation System
 * For EPIC Registration System
 */

if (!defined('EPIC_ROOT')) {
    die('Direct access not allowed');
}

/**
 * Enhanced referral code validation with normalization
 */
function epic_validate_referral_code($code, $strict_mode = false) {
    if (empty($code)) {
        return [
            'valid' => false,
            'error' => 'Kode referral tidak boleh kosong',
            'normalized_code' => null,
            'referrer' => null
        ];
    }
    
    // Normalize the code
    $normalized_code = epic_normalize_referral_code($code);
    
    if (empty($normalized_code)) {
        return [
            'valid' => false,
            'error' => 'Kode referral tidak valid setelah normalisasi',
            'normalized_code' => null,
            'referrer' => null
        ];
    }
    
    // Check code format
    if (!epic_is_valid_referral_format($normalized_code)) {
        return [
            'valid' => false,
            'error' => 'Format kode referral tidak valid',
            'normalized_code' => $normalized_code,
            'referrer' => null
        ];
    }
    
    // Find referrer using normalized code
    $referrer = epic_find_referrer_by_code($normalized_code);
    
    if (!$referrer) {
        return [
            'valid' => false,
            'error' => 'Kode referral tidak ditemukan',
            'normalized_code' => $normalized_code,
            'referrer' => null
        ];
    }
    
    // Check referrer status and eligibility
    $eligibility_check = epic_check_referrer_eligibility($referrer, $strict_mode);
    
    if (!$eligibility_check['eligible']) {
        return [
            'valid' => false,
            'error' => $eligibility_check['reason'],
            'normalized_code' => $normalized_code,
            'referrer' => $referrer
        ];
    }
    
    return [
        'valid' => true,
        'error' => null,
        'normalized_code' => $normalized_code,
        'referrer' => $referrer
    ];
}

/**
 * Normalize referral code (case-insensitive, trim whitespace, etc.)
 */
function epic_normalize_referral_code($code) {
    if (!is_string($code)) {
        return '';
    }
    
    // Trim whitespace
    $code = trim($code);
    
    // Convert to uppercase for consistency
    $code = strtoupper($code);
    
    // Remove any non-alphanumeric characters except allowed ones
    $code = preg_replace('/[^A-Z0-9\-_]/', '', $code);
    
    return $code;
}

/**
 * Check if referral code format is valid
 */
function epic_is_valid_referral_format($code) {
    // Basic format validation
    if (strlen($code) < 3 || strlen($code) > 20) {
        return false;
    }
    
    // Must contain at least one letter or number
    if (!preg_match('/[A-Z0-9]/', $code)) {
        return false;
    }
    
    // Check against blacklisted patterns
    $blacklisted_patterns = [
        'ADMIN', 'ROOT', 'TEST', 'NULL', 'UNDEFINED',
        'SYSTEM', 'API', 'WWW', 'FTP', 'MAIL'
    ];
    
    foreach ($blacklisted_patterns as $pattern) {
        if (strpos($code, $pattern) !== false) {
            return false;
        }
    }
    
    return true;
}

/**
 * Find referrer by normalized code with multiple lookup strategies
 */
function epic_find_referrer_by_code($normalized_code) {
    // Strategy 1: Direct lookup with normalized code
    $referrer = db()->select(TABLE_USERS, '*', 'UPPER(TRIM(referral_code)) = ?', [$normalized_code]);
    
    if ($referrer) {
        return $referrer;
    }
    
    // Strategy 2: Lookup with original case variations
    $case_variations = [
        $normalized_code,
        strtolower($normalized_code),
        ucfirst(strtolower($normalized_code))
    ];
    
    foreach ($case_variations as $variation) {
        $referrer = db()->select(TABLE_USERS, '*', 'referral_code = ?', [$variation]);
        if ($referrer) {
            return $referrer;
        }
    }
    
    // Strategy 3: Fuzzy matching for common typos (optional)
    if (epic_setting('referral_fuzzy_matching', '0') === '1') {
        $referrer = epic_fuzzy_match_referral_code($normalized_code);
        if ($referrer) {
            return $referrer;
        }
    }
    
    return null;
}

/**
 * Fuzzy matching for common referral code typos
 */
function epic_fuzzy_match_referral_code($code) {
    // Get all referral codes for fuzzy matching
    $all_codes = db()->query("SELECT id, name, email, referral_code FROM " . TABLE_USERS . " WHERE referral_code IS NOT NULL AND referral_code != ''");
    
    $best_match = null;
    $best_score = 0;
    $threshold = 0.8; // 80% similarity threshold
    
    foreach ($all_codes as $user) {
        $user_code = epic_normalize_referral_code($user['referral_code']);
        $similarity = similar_text($code, $user_code, $percent);
        
        if ($percent >= ($threshold * 100) && $percent > $best_score) {
            $best_score = $percent;
            $best_match = $user;
        }
    }
    
    return $best_match;
}

/**
 * Check referrer eligibility
 */
function epic_check_referrer_eligibility($referrer, $strict_mode = false) {
    // Check if user is active
    if ($referrer['status'] !== 'active' && $referrer['status'] !== 'epic') {
        return [
            'eligible' => false,
            'reason' => 'Referrer account is not active'
        ];
    }
    
    // Check if user is verified (if required)
    if (epic_setting('require_verified_referrer', '0') === '1' && !$referrer['verified']) {
        return [
            'eligible' => false,
            'reason' => 'Referrer account is not verified'
        ];
    }
    
    // Check referral limits (if enabled)
    if (epic_setting('referral_limit_enabled', '0') === '1') {
        $limit_check = epic_check_referral_limits($referrer['id']);
        if (!$limit_check['within_limit']) {
            return [
                'eligible' => false,
                'reason' => $limit_check['reason']
            ];
        }
    }
    
    // Strict mode additional checks
    if ($strict_mode) {
        // Check if referrer has completed profile
        if (empty($referrer['phone']) || empty($referrer['name'])) {
            return [
                'eligible' => false,
                'reason' => 'Referrer profile is incomplete'
            ];
        }
        
        // Check minimum account age
        $min_age_days = epic_setting('referrer_min_age_days', '7');
        $account_age = (time() - strtotime($referrer['created_at'])) / (24 * 60 * 60);
        
        if ($account_age < $min_age_days) {
            return [
                'eligible' => false,
                'reason' => "Referrer account must be at least {$min_age_days} days old"
            ];
        }
    }
    
    return [
        'eligible' => true,
        'reason' => null
    ];
}

/**
 * Check referral limits for a user
 */
function epic_check_referral_limits($referrer_id) {
    $daily_limit = (int)epic_setting('referral_daily_limit', '10');
    $monthly_limit = (int)epic_setting('referral_monthly_limit', '100');
    
    if ($daily_limit <= 0 && $monthly_limit <= 0) {
        return ['within_limit' => true, 'reason' => null];
    }
    
    $today = date('Y-m-d');
    $this_month = date('Y-m');
    
    // Check daily limit
    if ($daily_limit > 0) {
        $daily_count = db()->count(TABLE_USERS, 'referrer_id = ? AND DATE(created_at) = ?', [$referrer_id, $today]);
        
        if ($daily_count >= $daily_limit) {
            return [
                'within_limit' => false,
                'reason' => "Daily referral limit ({$daily_limit}) exceeded"
            ];
        }
    }
    
    // Check monthly limit
    if ($monthly_limit > 0) {
        $monthly_count = db()->count(TABLE_USERS, 'referrer_id = ? AND DATE_FORMAT(created_at, "%Y-%m") = ?', [$referrer_id, $this_month]);
        
        if ($monthly_count >= $monthly_limit) {
            return [
                'within_limit' => false,
                'reason' => "Monthly referral limit ({$monthly_limit}) exceeded"
            ];
        }
    }
    
    return ['within_limit' => true, 'reason' => null];
}

/**
 * Enhanced referrer info getter with validation
 */
function epic_get_validated_referrer_info($code) {
    $validation_result = epic_validate_referral_code($code);
    
    if (!$validation_result['valid']) {
        return null;
    }
    
    $referrer = $validation_result['referrer'];
    
    // Add additional info
    $referrer['normalized_code'] = $validation_result['normalized_code'];
    $referrer['validation_timestamp'] = time();
    
    // Get referrer stats
    $referrer['total_referrals'] = db()->count(TABLE_USERS, 'referrer_id = ?', [$referrer['id']]);
    $referrer['active_referrals'] = db()->count(TABLE_USERS, 'referrer_id = ? AND status IN ("active", "epic")', [$referrer['id']]);
    
    return $referrer;
}

/**
 * Batch validate multiple referral codes
 */
function epic_batch_validate_referral_codes($codes) {
    $results = [];
    
    foreach ($codes as $code) {
        $results[$code] = epic_validate_referral_code($code);
    }
    
    return $results;
}

/**
 * Get referral code suggestions for typos
 */
function epic_get_referral_code_suggestions($invalid_code, $limit = 5) {
    $normalized_code = epic_normalize_referral_code($invalid_code);
    
    if (strlen($normalized_code) < 2) {
        return [];
    }
    
    // Get similar codes using SOUNDEX and LIKE
    $suggestions = db()->query("
        SELECT referral_code, name 
        FROM " . TABLE_USERS . " 
        WHERE referral_code IS NOT NULL 
        AND referral_code != '' 
        AND (
            SOUNDEX(referral_code) = SOUNDEX(?) 
            OR referral_code LIKE ? 
            OR referral_code LIKE ?
        )
        AND status IN ('active', 'epic')
        LIMIT ?
    ", [
        $normalized_code,
        $normalized_code . '%',
        '%' . $normalized_code . '%',
        $limit
    ]);
    
    return $suggestions;
}

/**
 * Log referral validation attempts for analytics
 */
function epic_log_referral_validation($code, $result, $ip = null) {
    try {
        if (!$ip) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
        
        db()->insert('epi_referral_validations', [
            'original_code' => substr($code, 0, 50),
            'normalized_code' => substr($result['normalized_code'] ?? '', 0, 50),
            'is_valid' => $result['valid'] ? 1 : 0,
            'error_message' => substr($result['error'] ?? '', 0, 255),
            'referrer_id' => $result['referrer']['id'] ?? null,
            'ip_address' => $ip,
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        error_log("Failed to log referral validation: " . $e->getMessage());
    }
}

/**
 * Create referral validations table
 */
function epic_create_referral_validations_table() {
    $sql = "CREATE TABLE IF NOT EXISTS `epi_referral_validations` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `original_code` varchar(50),
        `normalized_code` varchar(50),
        `is_valid` tinyint(1) NOT NULL DEFAULT 0,
        `error_message` varchar(255),
        `referrer_id` int(11),
        `ip_address` varchar(45),
        `user_agent` varchar(500),
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_normalized_code` (`normalized_code`),
        KEY `idx_is_valid` (`is_valid`),
        KEY `idx_created_at` (`created_at`),
        KEY `idx_referrer_id` (`referrer_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    db()->query($sql);
}

// Create table on include
epic_create_referral_validations_table();