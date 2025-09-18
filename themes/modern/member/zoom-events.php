<?php
/**
 * EPIC Hub Member - Zoom Events
 * Halaman member untuk melihat dan mendaftar event Zoom
 */

if (!defined('EPIC_LOADED')) {
    die('Direct access not allowed');
}

// Check member access
if (!function_exists('epic_is_logged_in') || !epic_is_logged_in()) {
    if (function_exists('epic_redirect')) {
        epic_redirect('login');
    } else {
        header('Location: /login');
        exit;
    }
}

// Load Zoom integration core
require_once EPIC_PATH . '/core/zoom-integration.php';
global $epic_zoom;

// Get current user info
$user = function_exists('epic_get_current_user') ? epic_get_current_user() : null;
$user_level = (function_exists('epic_get_user_access_level') && $user) ? epic_get_user_access_level($user['id']) : 'free';

// Initialize zoom integration if not available
if (!$epic_zoom) {
    try {
        $epic_zoom = new EpicZoomIntegration();
    } catch (Exception $e) {
        error_log('Failed to initialize Zoom integration: ' . $e->getMessage());
        $epic_zoom = null;
    }
}

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'register_event':
            $event_id = intval($_POST['event_id']);
            
            // Check if zoom integration is available
            if (!$epic_zoom) {
                echo json_encode(['success' => false, 'message' => 'Zoom integration tidak tersedia']);
                exit;
            }
            
            // Check if user can access this event
            if (!method_exists($epic_zoom, 'canUserAccessEvent') || !$epic_zoom->canUserAccessEvent($event_id, $user_level)) {
                echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki akses ke event ini']);
                exit;
            }
            
            // Register user for event (implement registration logic)
            // For now, just return success
            echo json_encode(['success' => true, 'message' => 'Berhasil mendaftar event']);
            exit;
            
        case 'get_event_details':
            $event_id = intval($_POST['event_id']);
            
            if (!$epic_zoom) {
                echo json_encode(['success' => false, 'message' => 'Zoom integration tidak tersedia']);
                exit;
            }
            
            $event = method_exists($epic_zoom, 'getEvent') ? $epic_zoom->getEvent($event_id) : null;
            
            if ($event && (method_exists($epic_zoom, 'canUserAccessEvent') ? $epic_zoom->canUserAccessEvent($event_id, $user_level) : true)) {
                echo json_encode(['success' => true, 'event' => $event]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Event tidak ditemukan atau tidak dapat diakses']);
            }
            exit;
    }
}

// Get events accessible by user
$upcoming_events = [];
$categories = [];
$accessible_categories = [];

if ($epic_zoom) {
    try {
        $upcoming_events = method_exists($epic_zoom, 'getEventsByUserLevel') ? $epic_zoom->getEventsByUserLevel($user_level, 20, true) : [];
        $categories = method_exists($epic_zoom, 'getEventCategories') ? $epic_zoom->getEventCategories() : [];
        
        // Filter categories by user access
        $accessible_categories = array_filter($categories, function($category) use ($user_level) {
            $access_levels = json_decode($category['access_levels'], true);
            return is_array($access_levels) && in_array($user_level, $access_levels);
        });
    } catch (Exception $e) {
        error_log('Error loading zoom events data: ' . $e->getMessage());
        $upcoming_events = [];
        $categories = [];
        $accessible_categories = [];
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Zoom - EPIC Hub</title>
    <link rel="stylesheet" href="<?= epic_url('themes/modern/member/css/member.css') ?>">
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        :root {
            --primary: #3B82F6;
            --primary-dark: #2563EB;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --surface: #1F2937;
            --surface-light: #374151;
            --surface-dark: #111827;
            --text: #F9FAFB;
            --text-muted: #9CA3AF;
            --border: #4B5563;
            --gold: #F59E0B;
        }
        
        .zoom-events-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--text);
            margin-bottom: 1rem;
        }
        
        .page-subtitle {
            font-size: 1.125rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .user-level-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, var(--gold), #FBBF24);
            color: var(--surface-dark);
            border-radius: 9999px;
            font-weight: 600;
            margin-bottom: 2rem;
        }
        
        .categories-section {
            margin-bottom: 3rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .category-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 1.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .category-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border-color: var(--primary);
        }
        
        .category-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .category-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .category-info h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text);
            margin: 0 0 0.25rem 0;
        }
        
        .category-info p {
            color: var(--text-muted);
            margin: 0;
            font-size: 0.875rem;
        }
        
        .category-description {
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .category-access {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--success);
        }
        
        .events-section {
            margin-bottom: 3rem;
        }
        
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        
        .event-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 1rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .event-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        
        .event-category {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }
        
        .event-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }
        
        .event-description {
            color: var(--text-muted);
            line-height: 1.6;
            font-size: 0.875rem;
        }
        
        .event-details {
            padding: 1.5rem;
        }
        
        .event-meta {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-muted);
            font-size: 0.875rem;
        }
        
        .meta-icon {
            width: 16px;
            height: 16px;
            color: var(--primary);
        }
        
        .event-actions {
            display: flex;
            gap: 0.75rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            font-size: 0.875rem;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-secondary {
            background: var(--surface-light);
            color: var(--text);
            border: 1px solid var(--border);
        }
        
        .btn-secondary:hover {
            background: var(--surface-dark);
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-disabled {
            background: var(--surface-light);
            color: var(--text-muted);
            cursor: not-allowed;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-published {
            background: rgba(16, 185, 129, 0.2);
            color: #10B981;
        }
        
        .status-ongoing {
            background: rgba(245, 158, 11, 0.2);
            color: #F59E0B;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }
        
        .empty-state-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            color: var(--text-muted);
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: var(--surface);
            border-radius: 1rem;
            padding: 2rem;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text);
            margin: 0;
        }
        
        .close-modal {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .zoom-events-container {
                padding: 1rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .categories-grid,
            .events-grid {
                grid-template-columns: 1fr;
            }
            
            .event-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="zoom-events-container">
        <div class="page-header">
            <h1 class="page-title">
                <i data-feather="video" width="40" height="40"></i>
                Event Zoom EPIC Hub
            </h1>
            <p class="page-subtitle">
                Ikuti berbagai event pembinaan dan edukasi melalui platform Zoom. 
                Tingkatkan skill dan knowledge Anda bersama komunitas EPIC Hub.
            </p>
            <div class="user-level-badge">
                <i data-feather="user" width="16" height="16"></i>
                <?= strtoupper($user_level) ?> Account
            </div>
        </div>
        
        <?php if (!empty($accessible_categories)): ?>
            <div class="categories-section">
                <h2 class="section-title">
                    <i data-feather="folder" width="24" height="24"></i>
                    Kategori Event yang Dapat Anda Akses
                </h2>
                
                <div class="categories-grid">
                    <?php foreach ($accessible_categories as $category): ?>
                        <div class="category-card">
                            <div class="category-header">
                                <div class="category-icon" style="background: <?= $category['color'] ?>">
                                    <i data-feather="<?= $category['icon'] ?>" width="24" height="24"></i>
                                </div>
                                <div class="category-info">
                                    <h3><?= htmlspecialchars($category['name']) ?></h3>
                                    <p>Kategori Event</p>
                                </div>
                            </div>
                            <div class="category-description">
                                <?= htmlspecialchars($category['description']) ?>
                            </div>
                            <div class="category-access">
                                <i data-feather="check-circle" width="16" height="16"></i>
                                Anda memiliki akses ke kategori ini
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="events-section">
            <h2 class="section-title">
                <i data-feather="calendar" width="24" height="24"></i>
                Event Mendatang
            </h2>
            
            <?php if (!empty($upcoming_events)): ?>
                <div class="events-grid">
                    <?php foreach ($upcoming_events as $event): ?>
                        <div class="event-card">
                            <div class="event-header">
                                <div class="event-category" style="background: <?= $event['category_color'] ?>20; color: <?= $event['category_color'] ?>">
                                    <i data-feather="<?= $event['category_icon'] ?>" width="14" height="14"></i>
                                    <?= htmlspecialchars($event['category_name']) ?>
                                </div>
                                <h3 class="event-title"><?= htmlspecialchars($event['title']) ?></h3>
                                <?php if ($event['description']): ?>
                                    <p class="event-description"><?= htmlspecialchars(substr($event['description'], 0, 150)) ?>...</p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="event-details">
                                <div class="event-meta">
                                    <div class="meta-item">
                                        <i data-feather="calendar" class="meta-icon"></i>
                                        <?= date('d F Y', strtotime($event['start_time'])) ?>
                                    </div>
                                    <div class="meta-item">
                                        <i data-feather="clock" class="meta-icon"></i>
                                        <?= date('H:i', strtotime($event['start_time'])) ?> - <?= date('H:i', strtotime($event['end_time'])) ?> WIB
                                    </div>
                                    <?php if ($event['max_participants']): ?>
                                        <div class="meta-item">
                                            <i data-feather="users" class="meta-icon"></i>
                                            <?= $event['current_participants'] ?> / <?= $event['max_participants'] ?> peserta
                                        </div>
                                    <?php endif; ?>
                                    <div class="meta-item">
                                        <i data-feather="info" class="meta-icon"></i>
                                        <span class="status-badge status-<?= $event['status'] ?>">
                                            <?= ucfirst($event['status']) ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="event-actions">
                                    <button class="btn btn-primary" onclick="showEventDetails(<?= $event['id'] ?>)">
                                        <i data-feather="eye" width="16" height="16"></i>
                                        Lihat Detail
                                    </button>
                                    
                                    <?php if ($event['status'] === 'published'): ?>
                                        <?php if ($event['registration_required']): ?>
                                            <button class="btn btn-success" onclick="registerEvent(<?= $event['id'] ?>)">
                                                <i data-feather="user-plus" width="16" height="16"></i>
                                                Daftar
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-success" onclick="joinEvent(<?= $event['id'] ?>)">
                                                <i data-feather="video" width="16" height="16"></i>
                                                Join Meeting
                                            </button>
                                        <?php endif; ?>
                                    <?php elseif ($event['status'] === 'ongoing'): ?>
                                        <button class="btn btn-warning" onclick="joinEvent(<?= $event['id'] ?>)">
                                            <i data-feather="video" width="16" height="16"></i>
                                            Join Sekarang
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-disabled" disabled>
                                            <i data-feather="clock" width="16" height="16"></i>
                                            Belum Tersedia
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i data-feather="calendar-x" class="empty-state-icon"></i>
                    <h3>Belum Ada Event Mendatang</h3>
                    <p>Saat ini belum ada event yang tersedia untuk level akun Anda. Silakan cek kembali nanti.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Event Details Modal -->
    <div id="eventModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Detail Event</h3>
                <button class="close-modal" onclick="closeModal('eventModal')">
                    <i data-feather="x" width="20" height="20"></i>
                </button>
            </div>
            
            <div id="eventModalContent">
                <!-- Event details will be loaded here -->
            </div>
        </div>
    </div>
    
    <script>
        // Initialize Feather Icons
        feather.replace();
        
        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        // Show event details
        function showEventDetails(eventId) {
            const formData = new FormData();
            formData.set('action', 'get_event_details');
            formData.set('event_id', eventId);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayEventDetails(data.event);
                    openModal('eventModal');
                } else {
                    alert('Gagal memuat detail event: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memuat detail event');
            });
        }
        
        // Display event details in modal
        function displayEventDetails(event) {
            const content = document.getElementById('eventModalContent');
            content.innerHTML = `
                <div class="event-category" style="background: ${event.category_color}20; color: ${event.category_color}; margin-bottom: 1rem;">
                    <i data-feather="${event.category_icon}" width="14" height="14"></i>
                    ${event.category_name}
                </div>
                
                <h3 style="color: var(--text); margin-bottom: 1rem;">${event.title}</h3>
                
                ${event.description ? `<p style="color: var(--text-muted); line-height: 1.6; margin-bottom: 1.5rem;">${event.description}</p>` : ''}
                
                <div style="display: grid; gap: 1rem; margin-bottom: 1.5rem;">
                    <div class="meta-item">
                        <i data-feather="calendar" class="meta-icon"></i>
                        ${new Date(event.start_time).toLocaleDateString('id-ID', { 
                            weekday: 'long', 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric' 
                        })}
                    </div>
                    <div class="meta-item">
                        <i data-feather="clock" class="meta-icon"></i>
                        ${new Date(event.start_time).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })} - 
                        ${new Date(event.end_time).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })} ${event.timezone}
                    </div>
                    ${event.max_participants ? `
                        <div class="meta-item">
                            <i data-feather="users" class="meta-icon"></i>
                            Maksimal ${event.max_participants} peserta
                        </div>
                    ` : ''}
                    ${event.registration_required ? `
                        <div class="meta-item">
                            <i data-feather="user-check" class="meta-icon"></i>
                            Memerlukan registrasi
                        </div>
                    ` : ''}
                    ${event.registration_deadline ? `
                        <div class="meta-item">
                            <i data-feather="clock" class="meta-icon"></i>
                            Deadline registrasi: ${new Date(event.registration_deadline).toLocaleDateString('id-ID')}
                        </div>
                    ` : ''}
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button class="btn btn-secondary" onclick="closeModal('eventModal')">Tutup</button>
                    ${event.status === 'published' ? `
                        <button class="btn btn-primary" onclick="${event.registration_required ? 'registerEvent' : 'joinEvent'}(${event.id})">
                            <i data-feather="${event.registration_required ? 'user-plus' : 'video'}" width="16" height="16"></i>
                            ${event.registration_required ? 'Daftar Event' : 'Join Meeting'}
                        </button>
                    ` : ''}
                </div>
            `;
            
            // Re-initialize feather icons
            feather.replace();
        }
        
        // Register for event
        function registerEvent(eventId) {
            if (confirm('Apakah Anda yakin ingin mendaftar event ini?')) {
                const formData = new FormData();
                formData.set('action', 'register_event');
                formData.set('event_id', eventId);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Berhasil mendaftar event! Anda akan menerima email konfirmasi.');
                        location.reload();
                    } else {
                        alert('Gagal mendaftar event: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mendaftar event');
                });
            }
        }
        
        // Join event (placeholder)
        function joinEvent(eventId) {
            alert('Fitur join meeting akan segera tersedia. Anda akan menerima link meeting melalui email.');
        }
        
        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>