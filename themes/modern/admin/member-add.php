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
        
        // Store form data for repopulation
        $form_data = [
            'sponsor_code' => $sponsor_code,
            'full_name' => $full_name,
            'email' => $email,
            'whatsapp' => $whatsapp,
            'status' => $status,
            'role' => $role
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
        
        // Check for duplicates
        if (empty($errors)) {
            // Check email duplicate
            $email_duplicate = db()->selectValue(
                "SELECT id FROM epic_users WHERE email = ? LIMIT 1",
                [$email]
            );
            
            if ($email_duplicate) {
                $errors['email'] = 'Email sudah digunakan oleh member lain';
            }
            
            // Check WhatsApp duplicate
            $phone_duplicate = db()->selectValue(
                "SELECT id FROM epic_users WHERE phone = ? LIMIT 1",
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
        
        // Check sponsor exists
        if (empty($errors)) {
            $sponsor = db()->selectOne(
                "SELECT id, name FROM epic_users WHERE referral_code = ?",
                [$sponsor_code]
            );
            
            if (!$sponsor) {
                $errors['sponsor_code'] = 'Kode Sponsor tidak ditemukan';
            }
        }
        
        // Generate auto-increment sponsor ID
        $auto_sponsor_id = null;
        if (empty($errors)) {
            $last_sponsor_id = db()->selectValue(
                "SELECT MAX(id) FROM epic_users"
            ) ?: 0;
            $auto_sponsor_id = $last_sponsor_id + 1;
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
                    "SELECT id FROM epic_users WHERE referral_code = ?",
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
                // Insert new member with auto-generated ID
                $member_id = db()->insert('epic_users', [
                    'id' => $auto_sponsor_id,
                    'name' => $full_name,
                    'email' => strtolower($email),
                    'phone' => $whatsapp,
                    'password' => $hashed_password,
                    'status' => $status,
                    'role' => $role,
                    'referral_code' => $referral_code,
                    'sponsor_id' => $sponsor['id'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
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

<!DOCTYPE html>
<html lang="id" x-data="{ sidebarCollapsed: false }">
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
        
        /* Sidebar functionality styles */
        .sidebar-nav-parent {
            cursor: pointer;
            user-select: none;
            transition: all var(--transition-fast);
        }
        
        .sidebar-nav-parent:hover {
            background-color: var(--surface-3);
        }
        
        .sidebar-nav-arrow {
            transition: transform var(--transition-fast);
        }
        
        .sidebar-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height var(--transition-normal);
        }
        
        .sidebar-submenu.show {
            max-height: 300px;
        }
        
        .sidebar-submenu-item:hover {
            background-color: var(--surface-4);
            padding-left: calc(var(--spacing-5) + 4px);
        }
        
        .sidebar-separator {
            height: 1px;
            background: var(--surface-3);
            margin: var(--spacing-4) var(--spacing-4);
        }
        
        .sidebar-logout {
            color: var(--danger-light) !important;
        }
        
        .sidebar-logout:hover {
            background-color: rgba(239, 68, 68, 0.1) !important;
            color: var(--danger) !important;
        }
        
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
        <aside class="admin-sidebar" :class="{ 'collapsed': sidebarCollapsed }">
            <div class="sidebar-header">
                <a href="<?= epic_url('admin') ?>" class="sidebar-logo">
                    <div class="sidebar-logo-icon">EH</div>
                    <span class="sidebar-logo-text">EPIC Hub</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <!-- 1. Home -->
                <a href="<?= epic_url('admin') ?>" class="sidebar-nav-item">
                    <i data-feather="home" class="sidebar-nav-icon"></i>
                    <span class="sidebar-nav-text">Home</span>
                </a>
                
                <!-- 2. Edit Profile -->
                <a href="<?= epic_url('admin/edit-profile') ?>" class="sidebar-nav-item">
                    <i data-feather="user" class="sidebar-nav-icon"></i>
                    <span class="sidebar-nav-text">Edit Profile</span>
                </a>
                
                <!-- 3. Manage -->
                <div class="sidebar-nav-group">
                    <div class="sidebar-nav-item sidebar-nav-parent expanded">
                        <i data-feather="settings" class="sidebar-nav-icon"></i>
                        <span class="sidebar-nav-text">Manage</span>
                        <i data-feather="chevron-down" class="sidebar-nav-arrow"></i>
                    </div>
                    <div class="sidebar-submenu show">
                        <a href="<?= epic_url('admin/manage/member') ?>" class="sidebar-submenu-item active">
                            <span class="sidebar-submenu-text">Member</span>
                        </a>
                        <a href="<?= epic_url('admin/manage/order') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Order</span>
                        </a>
                        <a href="<?= epic_url('admin/manage/product') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Product</span>
                        </a>
                        <a href="<?= epic_url('admin/manage/landing-page') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Landing Page</span>
                        </a>
                        <a href="<?= epic_url('admin/manage/payout') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Payout</span>
                        </a>
                        <a href="<?= epic_url('admin/manage/finance') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Finance</span>
                        </a>
                        <a href="<?= epic_url('admin/manage/update-price') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Update Price</span>
                        </a>
                    </div>
                </div>
                
                <!-- 4. Settings -->
                <a href="<?= epic_url('admin/settings/general') ?>" class="sidebar-nav-item">
                    <i data-feather="sliders" class="sidebar-nav-icon"></i>
                    <span class="sidebar-nav-text">Settings</span>
                </a>
                
                <!-- 5. Integrasi -->
                <a href="<?= epic_url('admin/integrasi/autoresponder-email') ?>" class="sidebar-nav-item">
                    <i data-feather="zap" class="sidebar-nav-icon"></i>
                    <span class="sidebar-nav-text">Integrasi</span>
                </a>
                
                <!-- 6. Dashboard Member -->
                <div class="sidebar-nav-group">
                    <div class="sidebar-nav-item sidebar-nav-parent">
                        <i data-feather="monitor" class="sidebar-nav-icon"></i>
                        <span class="sidebar-nav-text">Dashboard Member</span>
                        <i data-feather="chevron-down" class="sidebar-nav-arrow"></i>
                    </div>
                    <div class="sidebar-submenu">
                        <a href="<?= epic_url('admin/dashboard-member/prospek') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Prospek</span>
                        </a>
                        <a href="<?= epic_url('admin/dashboard-member/bonus-cash') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Bonus Cash</span>
                        </a>
                        <a href="<?= epic_url('admin/dashboard-member/akses-produk') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Akses Produk</span>
                        </a>
                        <a href="<?= epic_url('admin/dashboard-member/history-order') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">History Order</span>
                        </a>
                    </div>
                </div>
                
                <!-- 7. Blog -->
                <a href="<?= epic_url('admin/blog') ?>" class="sidebar-nav-item">
                    <i data-feather="edit-3" class="sidebar-nav-icon"></i>
                    <span class="sidebar-nav-text">Blog</span>
                </a>
                

                
                <!-- Separator -->
                <div class="sidebar-separator"></div>
                
                <!-- 9. Logout -->
                <a href="<?= epic_url('logout') ?>" class="sidebar-nav-item sidebar-logout" onclick="return confirm('Apakah Anda yakin ingin logout?')">
                    <i data-feather="log-out" class="sidebar-nav-icon"></i>
                    <span class="sidebar-nav-text">Logout</span>
                </a>
            </nav>
            
            <!-- Collapse Button -->
            <button class="sidebar-collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed">
                <i data-feather="chevron-left" x-show="!sidebarCollapsed"></i>
                <i data-feather="chevron-right" x-show="sidebarCollapsed"></i>
            </button>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Topbar -->
            <header class="admin-topbar">
                <div class="topbar-left">
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
                            <input type="text" 
                                   id="sponsor_code" 
                                   name="sponsor_code" 
                                   class="form-input <?= isset($errors['sponsor_code']) ? 'error' : '' ?>" 
                                   placeholder="Masukkan kode referral sponsor"
                                   value="<?= htmlspecialchars($form_data['sponsor_code'] ?? '') ?>"
                                   required>
                            <?php if (isset($errors['sponsor_code'])): ?>
                                <div class="form-error server-error"><?= $errors['sponsor_code'] ?></div>
                            <?php endif; ?>
                            <div class="form-help">Kode referral dari sponsor yang mengundang member ini (ID akan di-generate otomatis)</div>
                        </div>
                    
                    <div class="form-group">
                        <label for="full_name" class="form-label required">Nama Lengkap</label>
                        <input type="text" 
                               id="full_name" 
                               name="full_name" 
                               class="form-input <?= isset($errors['full_name']) ? 'error' : '' ?>" 
                               placeholder="Masukkan nama lengkap member"
                               value="<?= htmlspecialchars($form_data['full_name'] ?? '') ?>"
                               required>
                        <?php if (isset($errors['full_name'])): ?>
                            <div class="form-error server-error"><?= $errors['full_name'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label required">Email</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input <?= isset($errors['email']) ? 'error' : '' ?>" 
                               placeholder="contoh@email.com"
                               value="<?= htmlspecialchars($form_data['email'] ?? '') ?>"
                               required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="form-error server-error"><?= $errors['email'] ?></div>
                        <?php endif; ?>
                        <div class="form-help">Email akan digunakan untuk login dan komunikasi</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="whatsapp" class="form-label required">Nomor WhatsApp</label>
                        <input type="tel" 
                               id="whatsapp" 
                               name="whatsapp" 
                               class="form-input <?= isset($errors['whatsapp']) ? 'error' : '' ?>" 
                               placeholder="628xxxxxxxxxx"
                               value="<?= htmlspecialchars($form_data['whatsapp'] ?? '') ?>"
                               required>
                        <?php if (isset($errors['whatsapp'])): ?>
                            <div class="form-error server-error"><?= $errors['whatsapp'] ?></div>
                        <?php endif; ?>
                        <div class="form-help">Format: 628xxxxxxxxxx (tanpa spasi atau tanda hubung)</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label required">Status Member</label>
                        <select id="status" 
                                name="status" 
                                class="form-select <?= isset($errors['status']) ? 'error' : '' ?>" 
                                required>
                            <option value="">Pilih Status Member</option>
                            <option value="pending" <?= ($form_data['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending - Menunggu aktivasi</option>
                            <option value="free" <?= ($form_data['status'] ?? '') === 'free' ? 'selected' : '' ?>>Free Account - Akses terbatas</option>
                            <option value="epic" <?= ($form_data['status'] ?? '') === 'epic' ? 'selected' : '' ?>>EPIC Account - Akses penuh</option>
                        </select>
                        <?php if (isset($errors['status'])): ?>
                            <div class="form-error server-error"><?= $errors['status'] ?></div>
                        <?php endif; ?>
                        <div class="form-help">Status menentukan level akses member di sistem</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="role" class="form-label required">Role Pengguna</label>
                        <select id="role" 
                                name="role" 
                                class="form-select <?= isset($errors['role']) ? 'error' : '' ?>" 
                                required>
                            <option value="">Pilih Role Pengguna</option>
                            <option value="user" <?= ($form_data['role'] ?? '') === 'user' ? 'selected' : '' ?>>User - Member biasa</option>
                            <option value="super_admin" <?= ($form_data['role'] ?? '') === 'super_admin' ? 'selected' : '' ?>>Super Admin - Administrator</option>
                        </select>
                        <?php if (isset($errors['role'])): ?>
                            <div class="form-error server-error"><?= $errors['role'] ?></div>
                        <?php endif; ?>
                        <div class="form-help">Role menentukan hak akses dan fitur yang tersedia</div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="<?= epic_url('admin/manage/member') ?>" class="topbar-btn secondary">
                        <i data-feather="x" width="16" height="16"></i>
                        <span>Batal</span>
                    </a>
                    <button type="submit" class="topbar-btn">
                        <i data-feather="save" width="16" height="16"></i>
                        <span>Simpan Member</span>
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
                    submenu.classList.add('show');
                    parent.classList.add('expanded');
                    const arrow = parent.querySelector('.sidebar-nav-arrow');
                    if (arrow) {
                        arrow.style.transform = 'rotate(180deg)';
                    }
                }
            });
            
            // Initialize form validation
            initFormValidation();
        });
        
        function toggleSubmenu(element) {
            const submenu = element.nextElementSibling;
            const arrow = element.querySelector('.sidebar-nav-arrow');
            
            if (submenu && submenu.classList.contains('sidebar-submenu')) {
                // Close other submenus
                document.querySelectorAll('.sidebar-submenu.show').forEach(menu => {
                    if (menu !== submenu) {
                        menu.classList.remove('show');
                        const parentArrow = menu.previousElementSibling?.querySelector('.sidebar-nav-arrow');
                        if (parentArrow) {
                            parentArrow.style.transform = 'rotate(0deg)';
                        }
                        menu.previousElementSibling?.classList.remove('expanded');
                    }
                });
                
                // Toggle current submenu
                submenu.classList.toggle('show');
                element.classList.toggle('expanded');
                
                if (arrow) {
                    arrow.style.transform = submenu.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
                }
            }
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
    </script>
</body>
</html>