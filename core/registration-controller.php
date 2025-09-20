<?php
/**
 * EPIC Hub - Registration Controller
 * Handles all registration business logic separated from routing
 * 
 * @version 1.0.0
 * @author Arva Team
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

/**
 * Enhanced Registration Controller
 * Handles the complete registration flow with referral and EPIS integration
 */
class EpicRegistrationController {
    
    private $error = null;
    private $success = null;
    private $referral_info = null;
    private $referral_code = '';
    
    /**
     * Main registration handler
     */
    public function handle() {
        // Redirect if already logged in
        if (epic_is_logged_in()) {
            epic_redirect(epic_url('dashboard'));
        }
        
        // Process referral tracking
        $this->processReferralTracking();
        
        // Handle POST request (form submission)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleRegistrationSubmission();
        }
        
        // Render the registration template
        $this->renderTemplate();
    }
    
    /**
     * Process referral code tracking from URL or cookies
     */
    private function processReferralTracking() {
        // Include enhanced referral handler
        require_once EPIC_ROOT . '/core/enhanced-referral-handler.php';
        
        // Process referral code dari URL atau cookie
        $this->referral_code = $_GET['ref'] ?? $_GET['referral'] ?? '';
        
        if ($this->referral_code) {
            // Process referral dan set cookie tracking
            $referral_processing = epic_enhanced_referral_processing($this->referral_code);
            if ($referral_processing['success']) {
                $this->referral_info = $referral_processing;
                
                // Set cookie untuk tracking
                $cookie_data = [
                    'code' => $this->referral_code,
                    'referrer_name' => $referral_processing['referrer']['name'],
                    'scenario' => $referral_processing['scenario'],
                    'auto_integration' => $referral_processing['auto_integration']
                ];
                
                if (isset($referral_processing['epis_supervisor'])) {
                    $cookie_data['epis_supervisor'] = $referral_processing['epis_supervisor'];
                }
                
                epic_set_referral_epis_cookie($this->referral_code, $referral_processing['referrer']['name'], $cookie_data);
            }
        } else {
            // Check existing cookie
            $tracking = epic_get_referral_epis_tracking();
            if ($tracking) {
                $this->referral_code = $tracking['code'];
                $referral_processing = epic_enhanced_referral_processing($this->referral_code);
                if ($referral_processing['success']) {
                    $this->referral_info = $referral_processing;
                }
            }
        }
    }
    
    /**
     * Handle registration form submission
     */
    private function handleRegistrationSubmission() {
        try {
            // CSRF Protection dan validasi input
            require_once EPIC_ROOT . '/core/csrf-protection.php';
            
            // Validasi form dengan CSRF protection
            $validation = epic_validate_registration_form($_POST);
            
            if (!$validation['valid']) {
                $this->handleValidationErrors($validation['errors']);
                return;
            }
            
            // Enhanced rate limiting with email protection
            require_once EPIC_ROOT . '/core/rate-limiter.php';
            epic_check_enhanced_registration_rate_limit($validation['data']['email'] ?? null);
            
            // Process successful registration
            $this->processRegistration($validation['data']);
            
        } catch (Exception $e) {
            $this->error = $e->getMessage();
        }
    }
    
    /**
     * Handle validation errors
     */
    private function handleValidationErrors($errors) {
        // Handle CSRF error specifically
        if (isset($errors['csrf'])) {
            epic_csrf_error($errors['csrf']);
            exit;
        }
        
        // Set error messages for display
        foreach ($errors as $field => $message) {
            $_SESSION['error_' . $field] = $message;
        }
        $this->error = 'Terdapat kesalahan pada form. Silakan periksa kembali.';
    }
    
    /**
     * Process the actual registration
     */
    private function processRegistration($validated_data) {
        // Extract validated data
        $name = $validated_data['name'];
        $email = $validated_data['email'];
        $phone = $validated_data['phone'] ?? '';
        $password = $validated_data['password'];
        $confirm_password = $validated_data['confirm_password'];
        $referral_code = $validated_data['referral_code'] ?? $this->referral_code;
        $terms = isset($_POST['terms']);
        $marketing = isset($_POST['marketing']);
        
        // Validasi terms
        if (!$terms) {
            throw new Exception('Anda harus menyetujui Ketentuan Layanan dan Kebijakan Privasi.');
        }
        
        // Validasi password match
        if ($password !== $confirm_password) {
            throw new Exception('Password dan konfirmasi password tidak cocok.');
        }
        
        // Enhanced registration dengan auto-integration
        require_once EPIC_ROOT . '/core/enhanced-referral-handler.php';
        
        $user_data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'referral_code' => $referral_code,
            'marketing' => $marketing
        ];
        
        $registration_result = epic_enhanced_register_user($user_data);
        
        if (!$registration_result['success']) {
            throw new Exception($registration_result['error']);
        }
        
        $this->handleSuccessfulRegistration($registration_result);
    }
    
    /**
     * Handle successful registration
     */
    private function handleSuccessfulRegistration($registration_result) {
        $user_id = $registration_result['user_id'];
        $referral_result = $registration_result['referral_result'];
        
        // Set success message berdasarkan skenario
        $this->success = 'Akun berhasil dibuat! ';
        
        if ($referral_result && $referral_result['success']) {
            switch ($referral_result['scenario']) {
                case 'epic_to_epis':
                    $this->success .= 'Anda telah otomatis terhubung dengan EPIS Supervisor melalui referral EPIC Account.';
                    break;
                case 'epis_direct':
                    $this->success .= 'Anda telah otomatis terhubung langsung dengan EPIS Account yang mereferalkan.';
                    break;
                case 'epic_standalone':
                    $this->success .= 'Anda telah terhubung melalui referral EPIC Account.';
                    break;
                default:
                    $this->success .= 'Registrasi berhasil dengan referral tracking.';
            }
        } else {
            $this->success .= 'Silakan cek email untuk konfirmasi akun.';
        }
        
        // Clear referral cookie setelah registrasi berhasil
        epic_clear_referral_epis_cookie();
        
        // Redirect to email confirmation page
        $_SESSION['registration_success'] = $this->success;
        epic_redirect('email-confirmation');
    }
    
    /**
     * Render the registration template
     */
    private function renderTemplate() {
        // Prepare data untuk template
        $data = [
            'page_title' => 'Daftar Akun Baru - ' . epic_setting('site_name'),
            'error' => $this->error,
            'success' => $this->success,
            'referral_info' => $this->referral_info,
            'referral_code' => $this->referral_code
        ];
        
        // Load the registration view template
        require_once EPIC_THEME_DIR . '/modern/auth/register.php';
    }
    
    /**
     * Get current error message
     */
    public function getError() {
        return $this->error;
    }
    
    /**
     * Get current success message
     */
    public function getSuccess() {
        return $this->success;
    }
    
    /**
     * Get referral information
     */
    public function getReferralInfo() {
        return $this->referral_info;
    }
    
    /**
     * Get referral code
     */
    public function getReferralCode() {
        return $this->referral_code;
    }
}

/**
 * Static helper function for backward compatibility
 * This function can be called from the router
 */
function epic_handle_registration() {
    $controller = new EpicRegistrationController();
    $controller->handle();
}