<?php
/**
 * EPIC Hub Admin - Add New Event Scheduling
 * Halaman standalone untuk menambah event scheduling baru
 */

// Bootstrap sistem terlebih dahulu
require_once __DIR__ . '/../bootstrap.php';

// Include routing helper for consistent error handling
require_once __DIR__ . '/../themes/modern/admin/routing-helper.php';

// Initialize admin page with proper validation
$init_result = epic_init_admin_page('admin', 'admin/event-scheduling-add');
$user = $init_result['user'];

// Load Event Scheduling core
require_once EPIC_PATH . '/core/event-scheduling.php';

// Initialize Event Scheduling class
$epic_event_scheduling = new EpicEventScheduling();

// Check if this is edit mode
$edit_mode = false;
$edit_event = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    try {
        $edit_event = $epic_event_scheduling->getEventById($edit_id);
        if ($edit_event) {
            $edit_mode = true;
        }
    } catch (Exception $e) {
        $error = 'Gagal memuat data event: ' . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $error = '';
    $success = '';
    
    try {
        // Determine action type
        $action = $_POST['action'];
        $is_draft = ($action === 'save_draft');
        
        // Validate required fields (less strict for draft)
        if (!$is_draft) {
            if (empty($_POST['title'])) {
                throw new Exception('Judul event wajib diisi');
            }
            
            if (empty($_POST['category_id'])) {
                throw new Exception('Kategori event wajib dipilih');
            }
            
            if (empty($_POST['start_time'])) {
                throw new Exception('Waktu mulai wajib diisi');
            }
            
            if (empty($_POST['end_time'])) {
                throw new Exception('Waktu berakhir wajib diisi');
            }
            
            // Validate time format and logic
            if (strtotime($_POST['start_time']) >= strtotime($_POST['end_time'])) {
                throw new Exception('Waktu mulai harus lebih awal dari waktu berakhir');
            }
        }
        
        // Sanitize and prepare data
        $event_data = [
            'title' => epic_sanitize($_POST['title'] ?? ''),
            'description' => epic_sanitize($_POST['description'] ?? ''),
            'category_id' => !empty($_POST['category_id']) ? intval($_POST['category_id']) : null,
            'start_time' => $_POST['start_time'] ?? null,
            'end_time' => $_POST['end_time'] ?? null,
            'location' => epic_sanitize($_POST['location'] ?? ''),
            'max_participants' => intval($_POST['max_participants'] ?? 0),
            'registration_deadline' => $_POST['registration_deadline'] ?? null,
            'is_public' => isset($_POST['is_public']) ? 1 : 0,
            'requires_approval' => isset($_POST['requires_approval']) ? 1 : 0,
            'is_draft' => $is_draft ? 1 : 0,
            'access_levels' => $_POST['access_levels'] ?? ['free', 'epic', 'epis'],
            'created_by' => $user['id']
        ];
        
        // Check if this is edit mode
        if (isset($_POST['event_id']) && !empty($_POST['event_id'])) {
            // Update existing event
            $event_data['id'] = intval($_POST['event_id']);
            $result = $epic_event_scheduling->updateEvent($event_data);
        } else {
            // Create new event
            $result = $epic_event_scheduling->createEvent($event_data);
        }
        
        if ($result['success']) {
            if ($is_draft) {
                $success = 'Draft berhasil disimpan dengan ID: ' . $result['event_id'];
            } else {
                $success = 'Event berhasil dibuat dengan ID: ' . $result['event_id'];
            }
        } else {
            $error = $result['message'] ?? ($is_draft ? 'Gagal menyimpan draft' : 'Gagal membuat event');
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get categories for dropdown
try {
    $categories = $epic_event_scheduling->getEventCategories();
    if (empty($categories)) {
        $error = 'Tidak ada kategori event yang tersedia. Silakan buat kategori terlebih dahulu.';
    }
} catch (Exception $e) {
    $error = 'Gagal memuat kategori event: ' . $e->getMessage();
    $categories = [];
}

// Use admin layout system
require_once __DIR__ . '/../themes/modern/admin/layout-helper.php';

// Prepare layout data
$layout_data = [
    'page_title' => ($edit_mode ? 'Edit Event' : 'Add New Event') . ' - EPIC Hub Admin',
    'header_title' => $edit_mode ? 'Edit Event' : 'Add New Event',
    'current_page' => 'manage',
    'breadcrumb' => [
        ['text' => 'Admin', 'url' => epic_url('admin')],
        ['text' => 'Manage', 'url' => '#'],
        ['text' => 'Event Scheduling', 'url' => epic_url('admin/event-scheduling')],
        ['text' => $edit_mode ? 'Edit Event' : 'Add New Event']
    ],
    'categories' => $categories,
    'edit_mode' => $edit_mode,
    'edit_event' => $edit_event,
    'error' => $error ?? '',
    'success' => $success ?? ''
];

// Render admin page
try {
    epic_render_admin_page(__DIR__ . '/../themes/modern/admin/content/event-scheduling-add-content.php', $layout_data);
} catch (Exception $e) {
    // Fallback error display
    echo '<div style="padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin: 20px;">';
    echo '<h3>Error</h3>';
    echo '<p>Gagal memuat halaman: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><a href="' . epic_url('admin/event-scheduling') . '">Kembali ke Event Scheduling</a></p>';
    echo '</div>';
}
?>