<?php
/**
 * EPIC Hub Admin - Add Member Page
 * Halaman terpisah untuk menambah member dengan fitur lengkap
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Check admin access
$user = epic_current_user();
if (!$user || !in_array($user['role'], ['admin', 'super_admin'])) {
    epic_route_403();
    return;
}

$success = null;
$error = null;
$form_data = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize input
        $sponsor_code = trim($_POST['sponsor_code'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $whatsapp = trim($_POST['whatsapp'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $epis_supervisor = trim($_POST['epis_supervisor'] ?? '');
        
        // Store form data for repopulation
        $form_data = [
            'sponsor_code' => $sponsor_code,
            'full_name' => $full_name,
            'email' => $email,
            'whatsapp' => $whatsapp,
            'status' => $status,
            'role' => $role,
            'epis_supervisor' => $epis_supervisor
        ];
        
        // Validation
        $errors = [];
        
        if (empty($sponsor_code)) {
            $errors['sponsor_code'] = 'Kode Sponsor wajib diisi';
        } elseif (strlen($sponsor_code) < 3) {
            $errors['sponsor_code'] = 'Kode Sponsor minimal 3 karakter';
        }
        
        if (empty($full_name)) {
            $errors['full_name'] = 'Nama lengkap wajib diisi';
        } elseif (strlen($full_name) < 2) {
            $errors['full_name'] = 'Nama lengkap minimal 2 karakter';
        }
        
        if (empty($email)) {
            $errors['email'] = 'Email wajib diisi';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid';
        }
        
        if (empty($whatsapp)) {
            $errors['whatsapp'] = 'Nomor WhatsApp wajib diisi';
        } elseif (!preg_match('/^(\+?62|0)[0-9]{9,13}$/', $whatsapp)) {
            $errors['whatsapp'] = 'Format nomor WhatsApp tidak valid (contoh: 628xxxxxxxxxx)';
        }
        
        if (empty($status)) {
            $errors['status'] = 'Status member wajib dipilih';
        } elseif (!in_array($status, ['pending', 'free', 'epic'])) {
            $errors['status'] = 'Status member tidak valid';
        }
        
        if (empty($role)) {
            $errors['role'] = 'Role pengguna wajib dipilih';
        } elseif (!in_array($role, ['user', 'super_admin'])) {
            $errors['role'] = 'Role pengguna tidak valid';
        }
        
        // Validate Sponsor Code (now required for all registrations)
        if (empty($sponsor_code)) {
            $errors['sponsor_code'] = 'Kode Sponsor wajib diisi';
        } elseif (strlen($sponsor_code) < 3) {
            $errors['sponsor_code'] = 'Kode Sponsor minimal 3 karakter';
        }
        
        // EPIS Supervisor will be auto-populated from sponsor data, no manual validation needed
        
        // Check for duplicates
        if (empty($errors)) {
            // Check email duplicate
            $email_duplicate = db()->selectValue(
                "SELECT id FROM users WHERE email = ? LIMIT 1",
                [$email]
            );
            
            if ($email_duplicate) {
                $errors['email'] = 'Email sudah digunakan oleh member lain';
            }
            
            // Check WhatsApp duplicate
            $phone_duplicate = db()->selectValue(
                "SELECT id FROM users WHERE phone = ? LIMIT 1",
                [$whatsapp]
            );
            
            if ($phone_duplicate) {
                $errors['whatsapp'] = 'Nomor WhatsApp sudah digunakan oleh member lain';
            }
            
            // General error if any duplicate found
            if ($email_duplicate || $phone_duplicate) {
                $errors['general'] = 'Nomor WhatsApp atau email sudah digunakan';
            }
        }
        
        // Check sponsor exists and get EPIS Supervisor data
        $sponsor_data = null;
        if (empty($errors)) {
            $sponsor_data = db()->selectOne(
                "SELECT u.id, u.name, u.email, u.referral_code, u.status,
                        supervisor.id as epis_supervisor_id,
                        supervisor.name as epis_supervisor_name,
                        supervisor.email as epis_supervisor_email
                 FROM " . db()->table('users') . " u
                 LEFT JOIN " . db()->table('users') . " supervisor ON u.epis_supervisor_id = supervisor.id
                 WHERE u.referral_code = ? AND u.status IN ('active', 'epic', 'epis')",
                [$sponsor_code]
            );
            
            if (!$sponsor_data) {
                $errors['sponsor_code'] = 'Kode Sponsor tidak ditemukan atau tidak aktif';
            }
        }
        
        // Generate auto-increment sponsor ID
        $auto_sponsor_id = null;
        if (empty($errors)) {
            $last_id = db()->selectValue(
                "SELECT MAX(id) FROM users"
            ) ?: 0;
            $auto_sponsor_id = $last_id + 1;
        }
        
        if (!empty($errors)) {
            $error = 'Terdapat kesalahan pada form. Mohon perbaiki dan coba lagi.';
        } else {
            // Generate referral code
            $base = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $full_name), 0, 3));
            if (strlen($base) < 3) {
                $base = str_pad($base, 3, 'X');
            }
            
            $attempts = 0;
            do {
                $referral_code = $base . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $exists = db()->selectValue(
                    "SELECT id FROM users WHERE referral_code = ?",
                    [$referral_code]
                );
                $attempts++;
            } while ($exists && $attempts < 10);
            
            if ($exists) {
                $referral_code = $base . substr(time(), -4);
            }
            
            // Generate password
            $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Normalize phone number
            $whatsapp = preg_replace('/^0/', '62', $whatsapp);
            $whatsapp = preg_replace('/^\+/', '', $whatsapp);
            
            // Begin transaction
            db()->beginTransaction();
            
            try {
                // Prepare insert data
                $insert_data = [
                    'id' => $auto_sponsor_id,
                    'name' => $full_name,
                    'email' => strtolower($email),
                    'phone' => $whatsapp,
                    'password' => $hashed_password,
                    'status' => $status,
                    'role' => $role,
                    'referral_code' => $referral_code,
                    'sponsor_id' => $sponsor_data['id'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Auto-populate EPIS Supervisor from sponsor data
                if (!empty($sponsor_data['epis_supervisor_id'])) {
                    $insert_data['epis_supervisor_id'] = $sponsor_data['epis_supervisor_id'];
                    $insert_data['epis_supervisor_name'] = $sponsor_data['epis_supervisor_name'];
                }
                
                // Insert new member with auto-generated ID
                $member_id = db()->insert('users', $insert_data);
                
                // Log activity
                db()->insert('epic_activity_logs', [
                    'user_id' => epic_get_current_user_id(),
                    'action' => 'member_added',
                    'description' => "Added new member: {$full_name} (ID: {$member_id})",
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                db()->commit();
                
                $success = "Member berhasil ditambahkan! <br>"
                         . "<strong>Nama:</strong> {$full_name}<br>"
                         . "<strong>Email:</strong> {$email}<br>"
                         . "<strong>Password:</strong> {$password}<br>"
                         . "<strong>Referral Code:</strong> {$referral_code}";
                
                // Clear form data on success
                $form_data = [];
                
            } catch (Exception $e) {
                db()->rollback();
                throw $e;
            }
        }
        
    } catch (Exception $e) {
        error_log('Add member error: ' . $e->getMessage());
        
        // Provide more specific error messages
        $error_message = $e->getMessage();
        
        if (strpos($error_message, 'Duplicate entry') !== false) {
            if (strpos($error_message, 'email') !== false) {
                $errors['email'] = 'Email sudah digunakan oleh member lain';
                $error = 'Email yang Anda masukkan sudah digunakan oleh member lain.';
            } elseif (strpos($error_message, 'phone') !== false) {
                $errors['whatsapp'] = 'Nomor WhatsApp sudah digunakan oleh member lain';
                $error = 'Nomor WhatsApp yang Anda masukkan sudah digunakan oleh member lain.';
            } else {
                $error = 'Data yang Anda masukkan sudah digunakan oleh member lain.';
            }
        } elseif (strpos($error_message, 'sponsor') !== false || strpos($error_message, 'Sponsor') !== false) {
            $errors['sponsor_code'] = 'Kode Sponsor tidak valid atau tidak ditemukan';
            $error = 'Kode Sponsor yang Anda masukkan tidak valid atau tidak ditemukan.';
        } elseif (strpos($error_message, 'foreign key') !== false || strpos($error_message, 'constraint') !== false) {
            $error = 'Data yang Anda masukkan tidak sesuai dengan aturan sistem. Periksa kembali Kode Sponsor.';
        } elseif (strpos($error_message, 'Data too long') !== false) {
            $error = 'Data yang Anda masukkan terlalu panjang. Periksa kembali panjang karakter pada setiap field.';
        } elseif (strpos($error_message, 'cannot be null') !== false || strpos($error_message, 'required') !== false) {
            $error = 'Semua field yang wajib diisi harus dilengkapi. Periksa kembali form Anda.';
        } elseif (strpos($error_message, 'auto_sponsor_id') !== false || strpos($error_message, 'PRIMARY') !== false) {
            $error = 'Terjadi konflik ID member. Silakan coba lagi dalam beberapa saat.';
        } else {
            $error = 'Terjadi kesalahan saat menambahkan member: ' . $error_message . '. Silakan periksa data yang Anda masukkan.';
        }
    }
}
?>

<?php
// Set current page untuk sidebar navigation
$current_page = 'member-add';
$current_url = $_SERVER['REQUEST_URI'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Member - Admin EPIC Hub</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= epic_url('themes/modern/admin/admin.css') ?>">
    <link rel="stylesheet" href="<?= epic_url('themes/modern/admin/components.css') ?>">
    
    <style>
        .admin-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left var(--transition-normal);
            min-height: 100vh;
            background-color: var(--ink-900);
        }
        
        .admin-sidebar.collapsed + .admin-main {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        .admin-content {
            padding: var(--spacing-6);
        }
        
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--spacing-6);
            padding-bottom: var(--spacing-4);
            border-bottom: 1px solid var(--surface-3);
        }
        
        .page-title {
            display: flex;
            align-items: center;
            gap: var(--spacing-3);
            font-size: var(--font-size-xl);
            font-weight: 600;
            color: var(--gold-400);
            margin: 0;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-2);
            padding: var(--spacing-3) var(--spacing-4);
            background: var(--surface-2);
            color: var(--ink-300);
            text-decoration: none;
            border-radius: var(--radius-md);
            border: 1px solid var(--surface-4);
            transition: all var(--transition-fast);
        }
        
        .back-button:hover {
            background: var(--surface-3);
            color: var(--gold-400);
            border-color: var(--gold-400);
            transform: translateY(-1px);
        }
        
        .form-card {
            background: linear-gradient(135deg, var(--surface-1) 0%, var(--surface-2) 100%);
            border: 1px solid var(--surface-3);
            border-radius: var(--radius-lg);
            padding: var(--spacing-8);
            box-shadow: var(--shadow-lg);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-6);
            margin-bottom: var(--spacing-6);
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-2);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--ink-200);
            font-size: var(--font-size-sm);
        }
        
        .form-label.required::after {
            content: ' *';
            color: var(--danger);
        }
        
        .form-input, .form-select {
            background: var(--surface-1);
            border: 1px solid var(--surface-4);
            border-radius: var(--radius-md);
            padding: var(--spacing-4) var(--spacing-5);
            color: var(--ink-100);
            font-size: var(--font-size-base);
            transition: all var(--transition-fast);
        }
        
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--gold-400);
            box-shadow: 0 0 0 3px rgba(207, 168, 78, 0.1);
        }
        
        .form-input.error, .form-select.error {
            border-color: var(--danger);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }
        
        .form-error {
            color: var(--danger);
            font-size: var(--font-size-sm);
            margin-top: var(--spacing-1);
        }
        

        
        /* Form actions styling to match topbar buttons */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: var(--spacing-3);
            padding-top: var(--spacing-6);
            border-top: 1px solid var(--surface-3);
        }
        
        .form-actions .topbar-btn {
            min-width: 140px;
            justify-content: center;
        }
        
        .alert {
            padding: var(--spacing-4);
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-6);
            border: 1px solid;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border-color: var(--success);
            color: var(--success-light);
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border-color: var(--danger);
            color: var(--danger-light);
        }
        
        .error-header {
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
            font-weight: 600;
            margin-bottom: var(--spacing-3);
        }
        
        .error-details {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-2);
        }
        
        .error-item {
            padding: var(--spacing-2) var(--spacing-3);
            background: rgba(239, 68, 68, 0.05);
            border-radius: var(--radius-sm);
            border-left: 3px solid var(--danger);
        }
        
        .error-message {
            padding: var(--spacing-2) 0;
        }
        
        .form-help {
            font-size: var(--font-size-sm);
            color: var(--ink-400);
            margin-top: var(--spacing-1);
        }
        
        /* Locked field styling */
        .locked-field {
            background-color: var(--surface-2) !important;
            color: var(--ink-500) !important;
            cursor: not-allowed !important;
            opacity: 0.6;
        }
        
        .locked-field:focus {
            box-shadow: none !important;
            border-color: var(--surface-4) !important;
        }
        
        /* Auto-filled field styling */
        .auto-filled {
            background-color: var(--surface-1) !important;
            color: var(--ink-300) !important;
            cursor: default !important;
            border-color: var(--surface-4) !important;
        }
        
        /* Sponsor validation styling */
        .sponsor-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .sponsor-validation-status {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
        }
        
        .validation-icon {
            width: 20px;
            height: 20px;
        }
        
        .validation-icon.loading {
            color: var(--ink-400);
            animation: spin 1s linear infinite;
        }
        
        .validation-icon.success {
            color: var(--success);
        }
        
        .validation-icon.error {
            color: var(--danger);
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Sponsor info box */
        .sponsor-info {
            margin-top: var(--spacing-3);
            padding: var(--spacing-3);
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success);
            border-radius: var(--radius-md);
        }
        
        .sponsor-details {
            font-size: var(--font-size-sm);
            color: var(--success-light);
        }
        
        .sponsor-details strong {
            color: var(--success);
        }
        
        /* Submit button disabled state */
        #submit-btn:disabled {
            background-color: var(--surface-3) !important;
            color: var(--ink-500) !important;
            cursor: not-allowed !important;
            opacity: 0.6;
        }
        
        #submit-btn:disabled:hover {
            background-color: var(--surface-3) !important;
            transform: none !important;
        }
        
        /* Sidebar styles sudah ada di komponen global */
        
        /* Breadcrumb links styling */
        .topbar-breadcrumb a {
            color: var(--ink-300);
            text-decoration: none;
            transition: color var(--transition-fast);
        }
        
        .topbar-breadcrumb a:hover {
            color: var(--gold-400);
        }
        
        @media (max-width: 768px) {
            .admin-main {
                padding: var(--spacing-4);
            }
            
            .form-card {
                padding: var(--spacing-6);
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: var(--spacing-4);
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-3);
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .admin-sidebar {
                transform: translateX(-100%);
            }
            
            .admin-sidebar.collapsed {
                transform: translateX(0);
            }
        }
    </style>
    <!-- Scripts -->
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="admin-body">
    <div class="admin-layout">
        <!-- Sidebar -->
        <?php include __DIR__ . '/components/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Topbar -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <!-- Mobile Menu Toggle -->
                    <button class="mobile-menu-toggle" aria-label="Toggle mobile menu">
                        <i data-feather="menu" width="20" height="20"></i>
                    </button>
                    
                    <h1 class="topbar-title">Tambah Member</h1>
                    <nav class="topbar-breadcrumb">
                        <a href="<?= epic_url('admin') ?>">Admin</a>
                        <span class="breadcrumb-separator">/</span>
                        <a href="#">Manage</a>
                        <span class="breadcrumb-separator">/</span>
                        <a href="<?= epic_url('admin/manage/member') ?>">Member</a>
                        <span class="breadcrumb-separator">/</span>
                        <span>Tambah</span>
                    </nav>
                </div>
                
                <div class="topbar-right">
                    <div class="topbar-actions">
                        <button type="button" class="topbar-btn secondary" onclick="window.location.href='<?= epic_url('admin/manage/member') ?>'">
                            <i data-feather="arrow-left" width="16" height="16"></i>
                            <span>Kembali</span>
                        </button>
                        <button type="button" class="topbar-btn" onclick="document.querySelector('form').reset()">
                            <i data-feather="refresh-cw" width="16" height="16"></i>
                            <span>Reset Form</span>
                        </button>
                    </div>
                    
                    <div class="topbar-notifications">
                        <i data-feather="bell" width="20" height="20"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    
                    <div class="topbar-avatar" onclick="window.location.href='<?= epic_url('admin/edit-profile') ?>'" style="cursor: pointer;" title="Edit Profile">
                        <?php if (!empty($user['profile_photo'])): ?>
                            <img src="<?= epic_url('uploads/profiles/' . $user['profile_photo']) ?>" alt="Profile" class="avatar-image">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <?= strtoupper(substr($user['name'], 0, 2)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </header>
            
            <!-- Content Area -->
            <div class="admin-content">
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i data-feather="check-circle" width="16" height="16" style="display: inline; margin-right: 8px;"></i>
                <?= $success ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <div class="error-header">
                    <i data-feather="x-circle" width="16" height="16"></i>
                    <span>Terjadi Kesalahan</span>
                </div>
                <?php if (!empty($errors) && count($errors) > 1): ?>
                    <div class="error-details">
                        <?php foreach ($errors as $field => $message): ?>
                            <?php if ($field !== 'general'): ?>
                                <div class="error-item">
                                    <strong><?= ucfirst(str_replace('_', ' ', $field)) ?>:</strong> <?= $message ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="error-message"><?= $error ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="form-card">
            <form method="POST" action="<?= epic_url('admin/manage/member/add') ?>">
                <div class="form-grid">
                    <div class="form-group">
                            <label for="sponsor_code" class="form-label required">Kode Sponsor</label>
                            <div class="sponsor-input-group">
                                <input type="text" 
                                       id="sponsor_code" 
                                       name="sponsor_code" 
                                       class="form-input <?= isset($errors['sponsor_code']) ? 'error' : '' ?>" 
                                       placeholder="Masukkan kode referral sponsor"
                                       value="<?= htmlspecialchars($form_data['sponsor_code'] ?? '') ?>"
                                       required>
                                <div class="sponsor-validation-status" id="sponsor-validation-status">
                                    <i data-feather="loader" class="validation-icon loading" style="display: none;"></i>
                                    <i data-feather="check-circle" class="validation-icon success" style="display: none;"></i>
                                    <i data-feather="x-circle" class="validation-icon error" style="display: none;"></i>
                                </div>
                            </div>
                            <?php if (isset($errors['sponsor_code'])): ?>
                                <div class="form-error server-error"><?= $errors['sponsor_code'] ?></div>
                            <?php endif; ?>
                            <div class="form-help">Kode referral dari sponsor yang mengundang member ini (wajib diisi terlebih dahulu)</div>
                            <div class="sponsor-info" id="sponsor-info" style="display: none;">
                                <div class="sponsor-details">
                                    <strong>Sponsor:</strong> <span id="sponsor-name"></span><br>
                                    <strong>Email:</strong> <span id="sponsor-email"></span><br>
                                    <strong>Status:</strong> <span id="sponsor-status"></span>
                                </div>
                            </div>
                        </div>
                    
                    <div class="form-group">
                        <label for="full_name" class="form-label required">Nama Lengkap</label>
                        <input type="text" 
                               id="full_name" 
                               name="full_name" 
                               class="form-input locked-field <?= isset($errors['full_name']) ? 'error' : '' ?>" 
                               placeholder="Masukkan nama lengkap member"
                               value="<?= htmlspecialchars($form_data['full_name'] ?? '') ?>"
                               disabled
                               required>
                        <?php if (isset($errors['full_name'])): ?>
                            <div class="form-error server-error"><?= $errors['full_name'] ?></div>
                        <?php endif; ?>
                        <div class="form-help">Nama lengkap sesuai identitas resmi (akan aktif setelah kode sponsor valid)</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label required">Email</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input locked-field <?= isset($errors['email']) ? 'error' : '' ?>" 
                               placeholder="contoh@email.com"
                               value="<?= htmlspecialchars($form_data['email'] ?? '') ?>"
                               disabled
                               required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="form-error server-error"><?= $errors['email'] ?></div>
                        <?php endif; ?>
                        <div class="form-help">Email akan digunakan untuk login dan komunikasi (akan aktif setelah kode sponsor valid)</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="whatsapp" class="form-label required">Nomor WhatsApp</label>
                        <input type="tel" 
                               id="whatsapp" 
                               name="whatsapp" 
                               class="form-input locked-field <?= isset($errors['whatsapp']) ? 'error' : '' ?>" 
                               placeholder="628xxxxxxxxxx"
                               value="<?= htmlspecialchars($form_data['whatsapp'] ?? '') ?>"
                               disabled
                               required>
                        <?php if (isset($errors['whatsapp'])): ?>
                            <div class="form-error server-error"><?= $errors['whatsapp'] ?></div>
                        <?php endif; ?>
                        <div class="form-help">Format: 628xxxxxxxxxx (akan aktif setelah kode sponsor valid)</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label required">Status Member</label>
                        <select id="status" 
                                name="status" 
                                class="form-select locked-field <?= isset($errors['status']) ? 'error' : '' ?>" 
                                disabled
                                required>
                            <option value="">Pilih Status Member</option>
                            <option value="pending" <?= ($form_data['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending - Menunggu aktivasi</option>
                            <option value="free" <?= ($form_data['status'] ?? '') === 'free' ? 'selected' : '' ?>>Free Account - Akses terbatas</option>
                            <option value="epic" <?= ($form_data['status'] ?? '') === 'epic' ? 'selected' : '' ?>>EPIC Account - Akses penuh</option>
                        </select>
                        <?php if (isset($errors['status'])): ?>
                            <div class="form-error server-error"><?= $errors['status'] ?></div>
                        <?php endif; ?>
                        <div class="form-help">Status menentukan level akses member di sistem (akan aktif setelah kode sponsor valid)</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="role" class="form-label required">Role Pengguna</label>
                        <select id="role" 
                                name="role" 
                                class="form-select locked-field <?= isset($errors['role']) ? 'error' : '' ?>" 
                                disabled
                                required>
                            <option value="">Pilih Role Pengguna</option>
                            <option value="user" <?= ($form_data['role'] ?? '') === 'user' ? 'selected' : '' ?>>User - Member biasa</option>
                            <option value="super_admin" <?= ($form_data['role'] ?? '') === 'super_admin' ? 'selected' : '' ?>>Super Admin - Administrator</option>
                        </select>
                        <?php if (isset($errors['role'])): ?>
                            <div class="form-error server-error"><?= $errors['role'] ?></div>
                        <?php endif; ?>
                        <div class="form-help">Role menentukan hak akses dan fitur yang tersedia (akan aktif setelah kode sponsor valid)</div>
                    </div>
                    
                    <div class="form-group epis-supervisor-group" id="epis-supervisor-group" style="display: none;">
                        <label for="epis_supervisor_name" class="form-label">EPIS Supervisor</label>
                        <input type="text" 
                               id="epis_supervisor_name" 
                               name="epis_supervisor_name" 
                               class="form-input auto-filled <?= isset($errors['epis_supervisor_name']) ? 'error' : '' ?>" 
                               placeholder="Akan diisi otomatis berdasarkan sponsor"
                               value="<?= htmlspecialchars($form_data['epis_supervisor_name'] ?? '') ?>"
                               readonly>
                        <?php if (isset($errors['epis_supervisor_name'])): ?>
                            <div class="form-error server-error"><?= $errors['epis_supervisor_name'] ?></div>
                        <?php endif; ?>
                        <div class="form-help epis-help">EPIS Supervisor akan diisi otomatis berdasarkan sponsor yang dipilih</div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="<?= epic_url('admin/manage/member') ?>" class="topbar-btn secondary">
                        <i data-feather="x" width="16" height="16"></i>
                        <span>Batal</span>
                    </a>
                    <button type="submit" id="submit-btn" class="topbar-btn" disabled>
                        <i data-feather="save" width="16" height="16"></i>
                        <span id="submit-text">Menunggu Validasi Sponsor</span>
                    </button>
                </div>
            </form>
        </div>
        
        </div> <!-- End admin-content -->
        </main>
    </div>
    <script>
        // Initialize all functionality when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather icons
            if (window.feather) {
                feather.replace();
            }
            
            // Add click event listeners to all sidebar nav parents
            document.querySelectorAll('.sidebar-nav-parent').forEach(parent => {
                parent.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleSubmenu(this);
                });
            });
            
            // Initialize expanded submenu for current page (Manage)
            const manageNavGroups = document.querySelectorAll('.sidebar-nav-group');
            manageNavGroups.forEach(group => {
                const parent = group.querySelector('.sidebar-nav-parent');
                const submenu = group.querySelector('.sidebar-submenu');
                const parentText = parent?.querySelector('.sidebar-nav-text')?.textContent;
                
                if (parentText === 'Manage' && submenu) {
                    submenu.classList.add('expanded');
                    parent.classList.add('expanded');
                    const arrow = parent.querySelector('.sidebar-nav-arrow');
                    if (arrow) {
                        arrow.style.transform = 'rotate(180deg)';
                    }
                }
            });
            
            // Initialize form validation
            initFormValidation();
            
            // Initialize sponsor validation system
            initSponsorValidation();
            
            // Initialize EPIS Supervisor field behavior
            initEpisSupervisorField();
        });
        
        function toggleSubmenu(element) {
            const submenu = element.nextElementSibling;
            const arrow = element.querySelector('.sidebar-nav-arrow');
            
            if (submenu && submenu.classList.contains('sidebar-submenu')) {
                // Close other submenus
                document.querySelectorAll('.sidebar-submenu.expanded').forEach(menu => {
                    if (menu !== submenu) {
                        menu.classList.remove('expanded');
                        const parentArrow = menu.previousElementSibling?.querySelector('.sidebar-nav-arrow');
                        if (parentArrow) {
                            parentArrow.style.transform = 'rotate(0deg)';
                        }
                        menu.previousElementSibling?.classList.remove('expanded');
                    }
                });
                
                // Toggle current submenu
                submenu.classList.toggle('expanded');
                element.classList.toggle('expanded');
                
                if (arrow) {
                    arrow.style.transform = submenu.classList.contains('expanded') ? 'rotate(180deg)' : 'rotate(0deg)';
                }
            }
        }
        
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const body = document.body;
            const collapseBtn = document.querySelector('.sidebar-collapse-btn');
            const leftIcon = collapseBtn?.querySelector('.collapse-icon-left');
            const rightIcon = collapseBtn?.querySelector('.collapse-icon-right');
            
            if (sidebar && body) {
                sidebar.classList.toggle('collapsed');
                body.classList.toggle('sidebar-collapsed');
                
                // Toggle collapse button icons
                if (leftIcon && rightIcon) {
                    if (sidebar.classList.contains('collapsed')) {
                        leftIcon.style.display = 'none';
                        rightIcon.style.display = 'block';
                    } else {
                        leftIcon.style.display = 'block';
                        rightIcon.style.display = 'none';
                    }
                }
            }
        }
        
        // Initialize sponsor validation system
        function initSponsorValidation() {
            const sponsorCodeInput = document.getElementById('sponsor_code');
            const sponsorValidationStatus = document.getElementById('sponsor-validation-status');
            const sponsorInfo = document.getElementById('sponsor-info');
            const submitBtn = document.getElementById('submit-btn');
            const submitText = document.getElementById('submit-text');
            
            // Get all locked fields
            const lockedFields = document.querySelectorAll('.locked-field');
            const episSupervisorGroup = document.getElementById('epis-supervisor-group');
            const episSupervisorInput = document.getElementById('epis_supervisor_name');
            
            let validationTimeout;
            let currentSponsorData = null;
            
            // Function to show validation status
            function showValidationStatus(type) {
                const icons = sponsorValidationStatus.querySelectorAll('.validation-icon');
                icons.forEach(icon => icon.style.display = 'none');
                
                const targetIcon = sponsorValidationStatus.querySelector(`.validation-icon.${type}`);
                if (targetIcon) {
                    targetIcon.style.display = 'block';
                }
            }
            
            // Function to unlock fields
            function unlockFields() {
                lockedFields.forEach(field => {
                    field.disabled = false;
                    field.classList.remove('locked-field');
                });
                
                submitBtn.disabled = false;
                submitText.textContent = 'Simpan Member';
            }
            
            // Function to lock fields
            function lockFields() {
                lockedFields.forEach(field => {
                    field.disabled = true;
                    field.classList.add('locked-field');
                });
                
                submitBtn.disabled = true;
                submitText.textContent = 'Menunggu Validasi Sponsor';
                
                // Hide EPIS Supervisor field
                episSupervisorGroup.style.display = 'none';
                episSupervisorInput.value = '';
            }
            
            // Function to validate sponsor code
            async function validateSponsorCode(code) {
                if (!code || code.length < 3) {
                    showValidationStatus('error');
                    sponsorInfo.style.display = 'none';
                    lockFields();
                    return;
                }
                
                showValidationStatus('loading');
                
                try {
                    const response = await fetch('<?= epic_url("api/check-referral.php") ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ referral_code: code })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success && data.data && data.data.sponsor) {
                        // Sponsor valid
                        showValidationStatus('success');
                        currentSponsorData = data.data.sponsor;
                        
                        // Show sponsor info
                        document.getElementById('sponsor-name').textContent = data.data.sponsor.name || 'N/A';
                        document.getElementById('sponsor-email').textContent = data.data.sponsor.email || 'N/A';
                        document.getElementById('sponsor-status').textContent = data.data.sponsor.status || 'N/A';
                        sponsorInfo.style.display = 'block';
                        
                        // Auto-populate EPIS Supervisor if available
                        if (data.data.epis_supervisor && data.data.epis_supervisor.name) {
                            episSupervisorInput.value = data.data.epis_supervisor.name;
                            episSupervisorGroup.style.display = 'block';
                        } else {
                            episSupervisorInput.value = '';
                            episSupervisorGroup.style.display = 'none';
                        }
                        
                        // Unlock other fields
                        unlockFields();
                        
                    } else {
                        // Sponsor tidak valid
                        showValidationStatus('error');
                        sponsorInfo.style.display = 'none';
                        currentSponsorData = null;
                        lockFields();
                    }
                    
                } catch (error) {
                    console.error('Error validating sponsor:', error);
                    showValidationStatus('error');
                    sponsorInfo.style.display = 'none';
                    currentSponsorData = null;
                    lockFields();
                }
            }
            
            // Event listener for sponsor code input
            sponsorCodeInput.addEventListener('input', function() {
                const code = this.value.trim();
                
                // Clear previous timeout
                if (validationTimeout) {
                    clearTimeout(validationTimeout);
                }
                
                // Set new timeout for validation (debounce)
                validationTimeout = setTimeout(() => {
                    validateSponsorCode(code);
                }, 500);
            });
            
            // Initial state - lock all fields
            lockFields();
            
            // If there's already a value in sponsor code, validate it
            if (sponsorCodeInput.value.trim()) {
                validateSponsorCode(sponsorCodeInput.value.trim());
            }
        }
        
        // Initialize EPIS Supervisor field behavior
        function initEpisSupervisorField() {
            const statusSelect = document.getElementById('status');
            const episSupervisorGroup = document.getElementById('epis-supervisor-group');
            const episSupervisorInput = document.getElementById('epis_supervisor_name');
            
            function updateEpisSupervisorField() {
                const selectedStatus = statusSelect.value;
                
                // EPIS Supervisor field is now auto-populated based on sponsor
                // Only show if sponsor has EPIS Supervisor data
                if (selectedStatus === 'epic' && episSupervisorInput.value) {
                    episSupervisorGroup.style.display = 'block';
                } else {
                    episSupervisorGroup.style.display = 'none';
                }
            }
            
            // Update field on status change
            statusSelect.addEventListener('change', updateEpisSupervisorField);
            
            // Initialize field state
            updateEpisSupervisorField();
        }
        
        // Initialize form validation
         function initFormValidation() {
             const form = document.querySelector('form');
             const inputs = form.querySelectorAll('.form-input, .form-select');
             
             // Auto-hide success message after 10 seconds
             const successAlert = document.querySelector('.alert-success');
             if (successAlert) {
                 setTimeout(() => {
                     successAlert.style.opacity = '0';
                     setTimeout(() => {
                         successAlert.remove();
                     }, 300);
                 }, 10000);
             }
            
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('error')) {
                        validateField(this);
                    }
                });
            });
            
            function validateField(field) {
                const value = field.value.trim();
                const fieldName = field.name;
                let isValid = true;
                
                // Remove existing error state
                field.classList.remove('error');
                const existingError = field.parentNode.querySelector('.form-error');
                if (existingError && !existingError.classList.contains('server-error')) {
                    existingError.remove();
                }
                
                // Required field validation
                if (field.hasAttribute('required') && !value) {
                    isValid = false;
                    showFieldError(field, 'Field ini wajib diisi');
                    return;
                }
                
                // Specific field validations
                if (value) {
                    switch (fieldName) {
                        case 'email':
                            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (!emailRegex.test(value)) {
                                isValid = false;
                                showFieldError(field, 'Format email tidak valid');
                            }
                            break;
                            
                        case 'whatsapp':
                            const phoneRegex = /^(\+?62|0)[0-9]{9,13}$/;
                            if (!phoneRegex.test(value)) {
                                isValid = false;
                                showFieldError(field, 'Format nomor WhatsApp tidak valid');
                            }
                            break;
                            
                        case 'sponsor_code':
                            if (value.length < 3) {
                                isValid = false;
                                showFieldError(field, 'Kode Sponsor minimal 3 karakter');
                            }
                            break;
                            
                        case 'full_name':
                            if (value.length < 2) {
                                isValid = false;
                                showFieldError(field, 'Nama lengkap minimal 2 karakter');
                            }
                            break;
                            
                        case 'epis_supervisor':
                            const statusField = document.getElementById('status');
                            const isEpicAccount = statusField && statusField.value === 'epic';
                            
                            if (isEpicAccount && !value) {
                                isValid = false;
                                showFieldError(field, 'EPIS Supervisor wajib diisi untuk EPIC Account');
                            } else if (value && value.length < 3) {
                                isValid = false;
                                showFieldError(field, 'EPIS Supervisor minimal 3 karakter');
                            } else if (value && value.length > 100) {
                                isValid = false;
                                showFieldError(field, 'EPIS Supervisor maksimal 100 karakter');
                            }
                            break;
                    }
                }
            }
            
            function showFieldError(field, message) {
                field.classList.add('error');
                
                // Only add error message if there isn't already a server-side error
                const existingServerError = field.parentNode.querySelector('.form-error.server-error');
                if (!existingServerError) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'form-error';
                    errorDiv.textContent = message;
                    field.parentNode.appendChild(errorDiv);
                 }
             }
         }
         
         // Initialize all functions when DOM is loaded
         document.addEventListener('DOMContentLoaded', function() {
             initSponsorValidation();
             initEpisSupervisorField();
         });
    </script>
</body>
</html>