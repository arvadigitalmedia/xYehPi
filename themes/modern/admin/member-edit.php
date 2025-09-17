<?php
/**
 * EPIC Hub Admin - Edit Member Page
 * Halaman untuk mengedit data member dengan fitur lengkap
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

$member_id = $data['member_id'] ?? null;
if (!$member_id) {
    epic_route_404();
    return;
}

// Get member data
$member = db()->selectOne(
    "SELECT * FROM epic_users WHERE id = ?",
    [$member_id]
);

if (!$member) {
    epic_route_404();
    return;
}

$success = null;
$error = null;
$errors = [];
$form_data = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize input
        $sponsor_id = trim($_POST['sponsor_id'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $whatsapp = trim($_POST['whatsapp'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        // Store form data for repopulation
        $form_data = [
            'sponsor_id' => $sponsor_id,
            'full_name' => $full_name,
            'email' => $email,
            'whatsapp' => $whatsapp,
            'status' => $status,
            'role' => $role
        ];
        
        // Validation
        if (empty($sponsor_id)) {
            $errors['sponsor_id'] = 'ID Sponsor wajib diisi';
        } elseif (strlen($sponsor_id) < 3) {
            $errors['sponsor_id'] = 'ID Sponsor minimal 3 karakter';
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
        
        // Check for duplicates (exclude current member)
        if (empty($errors)) {
            // Check email duplicate
            $email_duplicate = db()->selectValue(
                "SELECT id FROM epic_users WHERE email = ? AND id != ? LIMIT 1",
                [$email, $member_id]
            );
            
            if ($email_duplicate) {
                $errors['email'] = 'Email sudah digunakan oleh member lain';
            }
            
            // Check WhatsApp duplicate
            $phone_duplicate = db()->selectValue(
                "SELECT id FROM epic_users WHERE phone = ? AND id != ? LIMIT 1",
                [$whatsapp, $member_id]
            );
            
            if ($phone_duplicate) {
                $errors['whatsapp'] = 'Nomor WhatsApp sudah digunakan oleh member lain';
            }
            
            // General error if any duplicate found
            if ($email_duplicate || $phone_duplicate) {
                $errors['general'] = 'Nomor WhatsApp atau email sudah digunakan';
            }
        }
        
        // Check sponsor exists (if changed)
        if (empty($errors) && !empty($sponsor_code)) {
            $sponsor = db()->selectOne(
                "SELECT id, name FROM epic_users WHERE referral_code = ?",
                [$sponsor_code]
            );
            
            if (!$sponsor) {
                $errors['sponsor_code'] = 'Kode Sponsor tidak ditemukan';
            }
        }
        
        if (!empty($errors)) {
            $error = 'Terdapat kesalahan pada form. Mohon perbaiki dan coba lagi.';
        } else {
            // Normalize phone number
            $whatsapp = preg_replace('/^0/', '62', $whatsapp);
            $whatsapp = preg_replace('/^\+/', '', $whatsapp);
            
            // Begin transaction
            db()->beginTransaction();
            
            try {
                // Prepare update data
                $update_data = [
                    'name' => $full_name,
                    'email' => strtolower($email),
                    'phone' => $whatsapp,
                    'status' => $status,
                    'role' => $role,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Update sponsor if changed
                if (!empty($sponsor_code)) {
                    $sponsor = db()->selectOne(
                        "SELECT id FROM epic_users WHERE referral_code = ?",
                        [$sponsor_code]
                    );
                    if ($sponsor) {
                        // Update referrer relationship in epic_referrals table
                        db()->delete('epic_referrals', 'user_id = ?', [$member_id]);
                        db()->insert('epic_referrals', [
                            'user_id' => $member_id,
                            'referrer_id' => $sponsor['id'],
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
                
                // Update password if provided
                if (!empty($password)) {
                    $update_data['password'] = password_hash($password, PASSWORD_DEFAULT);
                }
                
                // Update member
                $result = db()->update('epic_users', $update_data, 'id = ?', [$member_id]);
                
                if (!$result) {
                    // Check if the member still exists
                    $member_exists = db()->selectValue(
                        "SELECT id FROM epic_users WHERE id = ?",
                        [$member_id]
                    );
                    
                    if (!$member_exists) {
                        throw new Exception('Member dengan ID tersebut tidak ditemukan atau telah dihapus');
                    }
                    
                    throw new Exception('Gagal mengupdate data member. Periksa kembali data yang Anda masukkan');
                }
                
                // Log activity
                db()->insert('epic_activity_logs', [
                    'user_id' => epic_get_current_user_id(),
                    'action' => 'member_updated',
                    'description' => "Updated member: {$full_name} (ID: {$member_id})",
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                db()->commit();
                
                $success_message = "Data member berhasil diupdate!";
                if (!empty($password)) {
                    $success_message .= "<br><strong>Password baru:</strong> {$password}";
                }
                
                $success = $success_message;
                
                // Refresh member data
                $member = db()->selectOne(
                    "SELECT * FROM epic_users WHERE id = ?",
                    [$member_id]
                );
                
                // Clear form data on success
                $form_data = [];
                
            } catch (Exception $e) {
                db()->rollback();
                throw $e;
            }
        }
        
    } catch (Exception $e) {
        // Log detailed error information
        error_log('Edit member error: ' . $e->getMessage());
        error_log('Edit member stack trace: ' . $e->getTraceAsString());
        error_log('Edit member data: ' . json_encode([
            'member_id' => $member_id,
            'form_data' => $form_data,
            'user_id' => epic_get_current_user_id()
        ]));
        
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
            $errors['sponsor_id'] = 'Sponsor ID tidak valid atau tidak ditemukan';
            $error = 'Sponsor ID yang Anda masukkan tidak valid atau tidak ditemukan.';
        } elseif (strpos($error_message, 'foreign key') !== false || strpos($error_message, 'constraint') !== false) {
            $error = 'Data yang Anda masukkan tidak sesuai dengan aturan sistem. Periksa kembali ID Sponsor.';
        } elseif (strpos($error_message, 'Data too long') !== false) {
            $error = 'Data yang Anda masukkan terlalu panjang. Periksa kembali panjang karakter pada setiap field.';
        } elseif (strpos($error_message, 'cannot be null') !== false || strpos($error_message, 'required') !== false) {
            $error = 'Semua field yang wajib diisi harus dilengkapi. Periksa kembali form Anda.';
        } else {
            $error = 'Terjadi kesalahan saat mengupdate member: ' . $error_message . '. Silakan periksa data yang Anda masukkan.';
        }
    }
}

// Populate form data with member data if not from POST
if (empty($form_data)) {
    $form_data = [
        'sponsor_id' => $member['sponsor_id'] ?? '',
        'full_name' => $member['name'] ?? '',
        'email' => $member['email'] ?? '',
        'whatsapp' => $member['phone'] ?? '',
        'status' => $member['status'] ?? '',
        'role' => $member['role'] ?? ''
    ];
}
?>

<!DOCTYPE html>
<html lang="id" x-data="{ sidebarCollapsed: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Member - Admin EPIC Hub</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= epic_url('themes/modern/admin/admin.css') ?>">
    <link rel="stylesheet" href="<?= epic_url('themes/modern/admin/components.css') ?>">
    
    <style>
        .admin-main {
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
        
        .form-help {
            font-size: var(--font-size-sm);
            color: var(--ink-400);
            margin-top: var(--spacing-1);
        }
        
        .member-info-card {
            background: var(--surface-2);
            border: 1px solid var(--surface-3);
            border-radius: var(--radius-md);
            padding: var(--spacing-4);
            margin-bottom: var(--spacing-6);
        }
        
        .member-info-title {
            font-weight: 600;
            color: var(--gold-400);
            margin-bottom: var(--spacing-3);
        }
        
        .member-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-3);
        }
        
        .member-info-item {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-1);
        }
        
        .member-info-label {
            font-size: var(--font-size-xs);
            color: var(--ink-400);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .member-info-value {
            font-weight: 500;
            color: var(--ink-200);
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
                        <a href="<?= epic_url('admin/dashboard-member/layout') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Layout</span>
                        </a>
                        <a href="<?= epic_url('admin/dashboard-member/widgets') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Widgets</span>
                        </a>
                        <a href="<?= epic_url('admin/dashboard-member/permissions') ?>" class="sidebar-submenu-item">
                            <span class="sidebar-submenu-text">Permissions</span>
                        </a>
                    </div>
                </div>
            </nav>
            
            <div class="sidebar-footer">
                <button class="sidebar-collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed">
                    <i data-feather="chevron-left" class="sidebar-collapse-icon" :class="{ 'rotated': sidebarCollapsed }"></i>
                </button>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Topbar -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button class="topbar-menu-btn" @click="sidebarCollapsed = !sidebarCollapsed">
                        <i data-feather="menu" width="20" height="20"></i>
                    </button>
                    <div class="topbar-breadcrumb">
                        <span class="breadcrumb-item">Admin</span>
                        <i data-feather="chevron-right" width="16" height="16" class="breadcrumb-separator"></i>
                        <span class="breadcrumb-item">Manage</span>
                        <i data-feather="chevron-right" width="16" height="16" class="breadcrumb-separator"></i>
                        <span class="breadcrumb-item">Member</span>
                        <i data-feather="chevron-right" width="16" height="16" class="breadcrumb-separator"></i>
                        <span class="breadcrumb-item active">Edit</span>
                    </div>
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
            
            <div class="page-header">
                <h1 class="page-title">
                    <i data-feather="edit-2" width="24" height="24"></i>
                    Edit Member: <?= htmlspecialchars($member['name']) ?>
                </h1>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i data-feather="check-circle" width="16" height="16" style="display: inline; margin-right: 8px;"></i>
                    <?= $success ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i data-feather="x-circle" width="16" height="16" style="display: inline; margin-right: 8px;"></i>
                    <?= $error ?>
                    <?php if (!empty($errors)): ?>
                        <div style="margin-top: 10px; font-size: 14px;">
                            <strong>Detail kesalahan:</strong>
                            <ul style="margin: 5px 0 0 20px; padding: 0;">
                                <?php foreach ($errors as $field => $message): ?>
                                    <?php if ($field !== 'general'): ?>
                                        <li><?= ucfirst(str_replace('_', ' ', $field)) ?>: <?= $message ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Member Info Card -->
            <div class="member-info-card">
                <div class="member-info-title">
                    <i data-feather="info" width="16" height="16"></i>
                    Informasi Member
                </div>
                <div class="member-info-grid">
                    <div class="member-info-item">
                        <div class="member-info-label">ID Member</div>
                        <div class="member-info-value"><?= $member['id'] ?></div>
                    </div>
                    <div class="member-info-item">
                        <div class="member-info-label">Referral Code</div>
                        <div class="member-info-value"><?= $member['referral_code'] ?? '-' ?></div>
                    </div>
                    <div class="member-info-item">
                        <div class="member-info-label">Bergabung</div>
                        <div class="member-info-value"><?= date('d M Y H:i', strtotime($member['created_at'])) ?></div>
                    </div>
                    <div class="member-info-item">
                        <div class="member-info-label">Last Login</div>
                        <div class="member-info-value"><?= $member['last_login_at'] ? date('d M Y H:i', strtotime($member['last_login_at'])) : 'Belum pernah login' ?></div>
                    </div>
                </div>
            </div>
            
            <div class="form-card">
                <form method="POST" action="<?= epic_url('admin/manage/member/edit/' . $member_id) ?>">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="sponsor_id" class="form-label required">ID Sponsor</label>
                            <input type="text" 
                                   id="sponsor_id" 
                                   name="sponsor_id" 
                                   class="form-input <?= isset($errors['sponsor_id']) ? 'error' : '' ?>" 
                                   placeholder="Masukkan ID sponsor atau referral code"
                                   value="<?= htmlspecialchars($form_data['sponsor_id'] ?? '') ?>"
                                   required>
                            <?php if (isset($errors['sponsor_id'])): ?>
                                <div class="form-error server-error"><?= $errors['sponsor_id'] ?></div>
                            <?php endif; ?>
                            <div class="form-help">ID atau referral code dari sponsor yang mengundang member ini</div>
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
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-input" 
                                   placeholder="Kosongkan jika tidak ingin mengubah password">
                            <div class="form-help">Kosongkan jika tidak ingin mengubah password</div>
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
                            <span>Update Member</span>
                        </button>
                    </div>
                </form>
            </div>
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
                            
                        case 'sponsor_id':
                            if (value.length < 3) {
                                isValid = false;
                                showFieldError(field, 'ID Sponsor minimal 3 karakter');
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