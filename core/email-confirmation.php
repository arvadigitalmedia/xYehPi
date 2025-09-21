<?php
/**
 * EPIC HUB - Email Confirmation Functions
 * Handles email verification and confirmation processes
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

/**
 * Generate email confirmation token
 * 
 * @param int $user_id User ID
 * @return string Confirmation token
 */
function epic_generate_confirmation_token($user_id) {
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Store token in database
    db()->insert('email_confirmations', [
        'user_id' => $user_id,
        'token' => $token,
        'expires_at' => $expires_at,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    return $token;
}

/**
 * Send confirmation email to user
 * 
 * @param array $user User data
 * @return array Result with success status and message
 */
function epic_send_confirmation_email($user) {
    try {
        // Generate confirmation token
        $token = epic_generate_confirmation_token($user['id']);
        $confirmation_url = epic_url('confirm-email/' . $token);
        
        // Prepare email data
        $email_data = [
            'to' => $user['email'],
            'to_name' => $user['name'],
            'subject' => 'Konfirmasi Email - ' . epic_setting('site_name'),
            'template' => 'email-confirmation',
            'data' => [
                'user_name' => $user['name'],
                'user_email' => $user['email'],
                'confirmation_url' => $confirmation_url,
                'site_name' => epic_setting('site_name'),
                'site_url' => epic_url(),
                'expires_hours' => 24
            ]
        ];
        
        // Render email template
        ob_start();
        epic_render_template('emails/' . $email_data['template'], $email_data['data']);
        $message = ob_get_clean();
        
        // Send email
        $result = epic_send_email(
            $email_data['to'], 
            $email_data['subject'], 
            $message, 
            $email_data['to_name']
        );
        
        if ($result['success']) {
            // Log successful email send
            epic_log_activity($user['id'], 'email_confirmation_sent', 'Email confirmation sent to ' . $user['email']);
            
            return [
                'success' => true,
                'message' => 'Email konfirmasi berhasil dikirim'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal mengirim email konfirmasi: ' . $result['message']
            ];
        }
        
    } catch (Exception $e) {
        error_log('Send confirmation email error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan sistem'
        ];
    }
}

/**
 * Confirm email using token
 * 
 * @param string $token Confirmation token
 * @return array Result with success status and message
 */
function epic_confirm_email_token($token) {
    try {
        // Find valid token
        $confirmation = db()->selectOne(
            "SELECT t.*, u.id as user_id, u.name, u.email, u.email_verified_at 
             FROM epic_user_tokens t 
             JOIN epic_users u ON t.user_id = u.id 
             WHERE t.token = ? AND t.type = 'email_verification' AND t.expires_at > NOW() AND t.used_at IS NULL",
            [$token]
        );
        
        if (!$confirmation) {
            return [
                'success' => false,
                'message' => 'Token konfirmasi tidak valid atau sudah kedaluwarsa'
            ];
        }
        
        // Check if email already verified
        if ($confirmation['email_verified_at']) {
            return [
                'success' => false,
                'message' => 'Email sudah dikonfirmasi sebelumnya'
            ];
        }
        
        // Mark email as verified and set status to ACTIVE
        db()->query(
            "UPDATE epic_users SET email_verified_at = NOW(), status = 'ACTIVE' WHERE id = ?",
            [$confirmation['user_id']]
        );
        
        // Mark token as used
        db()->query(
            "UPDATE epic_user_tokens SET used_at = NOW() WHERE id = ?",
            [$confirmation['id']]
        );
        
        // Log successful confirmation
        epic_log_activity($confirmation['user_id'], 'email_confirmed', 'Email address confirmed successfully');
        
        // Send welcome email after successful confirmation
        try {
            // Simple welcome email without complex dependencies
            $welcome_subject = 'Selamat Datang!';
            $welcome_message = "Halo {$confirmation['name']},\n\nSelamat datang! Email Anda telah berhasil dikonfirmasi.\n\nTerima kasih.";
            
            if (function_exists('epic_send_email')) {
                epic_send_email($confirmation['email'], $welcome_subject, $welcome_message);
                epic_log_activity($confirmation['user_id'], 'welcome_email_sent', 'Welcome email sent to ' . $confirmation['email']);
            }
        } catch (Exception $e) {
            // Log error but don't fail the confirmation process
            error_log("Welcome email failed: " . $e->getMessage());
        }
        
        return [
            'success' => true,
            'message' => 'Email berhasil dikonfirmasi',
            'user_id' => $confirmation['user_id']
        ];
        
    } catch (Exception $e) {
        error_log('Confirm email token error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan sistem'
        ];
    }
}

/**
 * Send welcome email after confirmation
 * 
 * @param array $user User data
 * @return array Result with success status
 */
function epic_send_welcome_email($user) {
    try {
        $email_data = [
            'to' => $user['email'],
            'to_name' => $user['name'],
            'subject' => 'Selamat Datang di ' . epic_setting('site_name'),
            'template' => 'welcome',
            'data' => [
                'user_name' => $user['name'],
                'site_name' => epic_setting('site_name'),
                'site_url' => epic_url(),
                'login_url' => epic_url('login'),
                'dashboard_url' => epic_url('dashboard')
            ]
        ];
        
        $result = epic_send_email(
            $email_data['to'], 
            $email_data['subject'], 
            epic_render_email_template($email_data['template'], $email_data['data']),
            epic_setting('site_name')
        );
        
        if ($result['success']) {
            epic_log_activity($user['id'], 'welcome_email_sent', 'Welcome email sent to ' . $user['email']);
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log('Send welcome email error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Gagal mengirim email selamat datang'
        ];
    }
}

/**
 * Clean up expired confirmation tokens
 * 
 * @return int Number of cleaned tokens
 */
function epic_cleanup_expired_confirmations() {
    try {
        $result = db()->query(
            "DELETE FROM email_confirmations WHERE expires_at < NOW()"
        );
        
        $cleaned_count = $result->rowCount();
        
        if ($cleaned_count > 0) {
            error_log("Cleaned up {$cleaned_count} expired email confirmation tokens");
        }
        
        return $cleaned_count;
        
    } catch (Exception $e) {
        error_log('Cleanup expired confirmations error: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Check if user needs email confirmation
 * 
 * @param int $user_id User ID
 * @return bool True if confirmation needed
 */
function epic_user_needs_email_confirmation($user_id) {
    try {
        $user = db()->selectOne(
            "SELECT email_verified_at FROM users WHERE id = ?",
            [$user_id]
        );
        
        return $user && !$user['email_verified_at'];
        
    } catch (Exception $e) {
        error_log('Check email confirmation error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get confirmation statistics
 * 
 * @param string $start_date Start date (Y-m-d)
 * @param string $end_date End date (Y-m-d)
 * @return array Statistics
 */
function epic_get_confirmation_stats($start_date = null, $end_date = null) {
    try {
        $start_date = $start_date ?: date('Y-m-d', strtotime('-30 days'));
        $end_date = $end_date ?: date('Y-m-d');
        
        // Total confirmations sent
        $sent = db()->selectValue(
            "SELECT COUNT(*) FROM email_confirmations 
             WHERE DATE(created_at) BETWEEN ? AND ?",
            [$start_date, $end_date]
        ) ?: 0;
        
        // Total confirmations completed
        $confirmed = db()->selectValue(
            "SELECT COUNT(*) FROM email_confirmations 
             WHERE used_at IS NOT NULL AND DATE(used_at) BETWEEN ? AND ?",
            [$start_date, $end_date]
        ) ?: 0;
        
        // Confirmation rate
        $rate = $sent > 0 ? round(($confirmed / $sent) * 100, 2) : 0;
        
        return [
            'sent' => $sent,
            'confirmed' => $confirmed,
            'rate' => $rate,
            'period' => [
                'start' => $start_date,
                'end' => $end_date
            ]
        ];
        
    } catch (Exception $e) {
        error_log('Get confirmation stats error: ' . $e->getMessage());
        return [
            'sent' => 0,
            'confirmed' => 0,
            'rate' => 0,
            'period' => [
                'start' => $start_date,
                'end' => $end_date
            ]
        ];
    }
}

/**
 * Create email confirmation table if not exists
 */
function epic_create_email_confirmation_table() {
    try {
        $sql = "CREATE TABLE IF NOT EXISTS `email_confirmations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `token` varchar(64) NOT NULL,
            `expires_at` datetime NOT NULL,
            `used_at` datetime NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `token` (`token`),
            KEY `user_id` (`user_id`),
            KEY `expires_at` (`expires_at`),
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        db()->query($sql);
        
        return true;
        
    } catch (Exception $e) {
        error_log('Create email confirmation table error: ' . $e->getMessage());
        return false;
    }
}

// Auto-create table if not exists
epic_create_email_confirmation_table();