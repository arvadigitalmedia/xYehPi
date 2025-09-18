<?php
/**
 * EPIC Hub Zoom Integration Core Functions
 * Handles Zoom API integration and event management
 */

if (!defined('EPIC_LOADED')) {
    die('Direct access not allowed');
}

class EpicZoomIntegration {
    private $db;
    private $zoom_api_key;
    private $zoom_api_secret;
    private $zoom_account_id;
    
    public function __construct() {
        global $epic_db;
        $this->db = $epic_db;
        $this->loadZoomCredentials();
    }
    
    /**
     * Load Zoom API credentials from database
     */
    private function loadZoomCredentials() {
        try {
            // Check if database connection exists
            if (!$this->db) {
                error_log('Database connection not available for Zoom integration');
                return;
            }
            
            // Check if zoom settings table exists
            $stmt = $this->db->prepare("SHOW TABLES LIKE 'epic_zoom_settings'");
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                // Table doesn't exist yet, use default empty values
                $this->zoom_api_key = '';
                $this->zoom_api_secret = '';
                $this->zoom_account_id = '';
                return;
            }
            
            $stmt = $this->db->prepare("SELECT setting_key, setting_value FROM epic_zoom_settings WHERE setting_key IN ('zoom_api_key', 'zoom_api_secret', 'zoom_account_id')");
            $stmt->execute();
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            $this->zoom_api_key = $settings['zoom_api_key'] ?? '';
            $this->zoom_api_secret = $settings['zoom_api_secret'] ?? '';
            $this->zoom_account_id = $settings['zoom_account_id'] ?? '';
        } catch (Exception $e) {
            error_log('Failed to load Zoom credentials: ' . $e->getMessage());
            // Set default empty values on error
            $this->zoom_api_key = '';
            $this->zoom_api_secret = '';
            $this->zoom_account_id = '';
        }
    }
    
    /**
     * Get all event categories
     */
    public function getEventCategories($active_only = true) {
        try {
            // Check if database connection exists
            if (!$this->db) {
                return [];
            }
            
            // Check if table exists
            $stmt = $this->db->prepare("SHOW TABLES LIKE 'epic_event_categories'");
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                return [];
            }
            
            $sql = "SELECT * FROM epic_event_categories";
            if ($active_only) {
                $sql .= " WHERE is_active = 1";
            }
            $sql .= " ORDER BY name ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Failed to get event categories: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create new event category
     */
    public function createEventCategory($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO epic_event_categories (name, description, access_levels, color, icon, created_by) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $access_levels = is_array($data['access_levels']) ? json_encode($data['access_levels']) : $data['access_levels'];
            
            return $stmt->execute([
                $data['name'],
                $data['description'] ?? '',
                $access_levels,
                $data['color'] ?? '#3B82F6',
                $data['icon'] ?? 'calendar',
                $data['created_by'] ?? 1
            ]);
        } catch (Exception $e) {
            error_log('Failed to create event category: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update event category
     */
    public function updateEventCategory($id, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE epic_event_categories 
                SET name = ?, description = ?, access_levels = ?, color = ?, icon = ?, is_active = ?
                WHERE id = ?
            ");
            
            $access_levels = is_array($data['access_levels']) ? json_encode($data['access_levels']) : $data['access_levels'];
            
            return $stmt->execute([
                $data['name'],
                $data['description'] ?? '',
                $access_levels,
                $data['color'] ?? '#3B82F6',
                $data['icon'] ?? 'calendar',
                $data['is_active'] ?? 1,
                $id
            ]);
        } catch (Exception $e) {
            error_log('Failed to update event category: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete event category
     */
    public function deleteEventCategory($id) {
        try {
            // Check if category has events
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM epic_zoom_events WHERE category_id = ?");
            $stmt->execute([$id]);
            $event_count = $stmt->fetchColumn();
            
            if ($event_count > 0) {
                return ['success' => false, 'message' => 'Tidak dapat menghapus kategori yang masih memiliki event'];
            }
            
            $stmt = $this->db->prepare("DELETE FROM epic_event_categories WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            return ['success' => $result, 'message' => $result ? 'Kategori berhasil dihapus' : 'Gagal menghapus kategori'];
        } catch (Exception $e) {
            error_log('Failed to delete event category: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan saat menghapus kategori'];
        }
    }

    /**
     * Get single event category by ID
     */
    public function getEventCategory($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM epic_event_categories WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Failed to get event category: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all events with pagination
     */
    public function getEvents($page = 1, $limit = 20, $filters = []) {
        try {
            $offset = ($page - 1) * $limit;
            $where_conditions = [];
            $params = [];
            
            // Build WHERE conditions
            if (!empty($filters['category_id'])) {
                $where_conditions[] = "e.category_id = ?";
                $params[] = $filters['category_id'];
            }
            
            if (!empty($filters['status'])) {
                $where_conditions[] = "e.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['search'])) {
                $where_conditions[] = "(e.title LIKE ? OR e.description LIKE ?)";
                $search_term = '%' . $filters['search'] . '%';
                $params[] = $search_term;
                $params[] = $search_term;
            }
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            $sql = "
                SELECT e.*, c.name as category_name, c.color as category_color, c.icon as category_icon,
                       u.name as creator_name
                FROM epic_zoom_events e
                LEFT JOIN epic_event_categories c ON e.category_id = c.id
                LEFT JOIN users u ON e.created_by = u.id
                {$where_clause}
                ORDER BY e.created_at DESC, e.start_time DESC
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $count_sql = "
                SELECT COUNT(*) 
                FROM epic_zoom_events e
                LEFT JOIN epic_event_categories c ON e.category_id = c.id
                {$where_clause}
            ";
            
            $count_params = array_slice($params, 0, -2); // Remove limit and offset
            $stmt = $this->db->prepare($count_sql);
            $stmt->execute($count_params);
            $total = $stmt->fetchColumn();
            
            // Check if this is called from wrapper function that expects simple array
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            if (isset($backtrace[1]['function']) && $backtrace[1]['function'] === 'epic_get_zoom_events') {
                return $events; // Return simple array for wrapper function
            }
            
            return [
                'events' => $events,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ];
        } catch (Exception $e) {
            error_log('Failed to get events: ' . $e->getMessage());
            return ['events' => [], 'total' => 0, 'page' => 1, 'limit' => $limit, 'total_pages' => 0];
        }
    }
    
    /**
     * Get single event by ID
     */
    public function getEvent($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT e.*, c.name as category_name, c.color as category_color, c.icon as category_icon,
                       c.access_levels, u.name as creator_name
                FROM epic_zoom_events e
                JOIN epic_event_categories c ON e.category_id = c.id
                LEFT JOIN users u ON e.created_by = u.id
                WHERE e.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Failed to get event: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create new event
     */
    public function createEvent($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO epic_zoom_events (
                    category_id, title, description, start_time, end_time, timezone,
                    max_participants, registration_required, registration_deadline,
                    status, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $data['category_id'],
                $data['title'],
                $data['description'] ?? '',
                $data['start_time'],
                $data['end_time'],
                $data['timezone'] ?? 'Asia/Jakarta',
                $data['max_participants'] ?? null,
                $data['registration_required'] ?? 0,
                $data['registration_deadline'] ?? null,
                $data['status'] ?? 'draft',
                $data['created_by'] ?? 1
            ]);
        } catch (Exception $e) {
            error_log('Failed to create event: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update event
     */
    public function updateEvent($id, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE epic_zoom_events 
                SET category_id = ?, title = ?, description = ?, start_time = ?, end_time = ?,
                    timezone = ?, max_participants = ?, registration_required = ?,
                    registration_deadline = ?, status = ?
                WHERE id = ?
            ");
            
            return $stmt->execute([
                $data['category_id'],
                $data['title'],
                $data['description'] ?? '',
                $data['start_time'],
                $data['end_time'],
                $data['timezone'] ?? 'Asia/Jakarta',
                $data['max_participants'] ?? null,
                $data['registration_required'] ?? 0,
                $data['registration_deadline'] ?? null,
                $data['status'] ?? 'draft',
                $id
            ]);
        } catch (Exception $e) {
            error_log('Failed to update event: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete event
     */
    public function deleteEvent($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM epic_zoom_events WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            return ['success' => $result, 'message' => $result ? 'Event berhasil dihapus' : 'Gagal menghapus event'];
        } catch (Exception $e) {
            error_log('Failed to delete event: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan saat menghapus event'];
        }
    }
    
    /**
     * Get events accessible by user level
     */
    public function getEventsByUserLevel($user_level, $limit = 10, $upcoming_only = true) {
        try {
            $sql = "
                SELECT e.*, c.name as category_name, c.color as category_color, c.icon as category_icon
                FROM epic_zoom_events e
                JOIN epic_event_categories c ON e.category_id = c.id
                WHERE c.is_active = 1 
                    AND e.status IN ('published', 'ongoing')
                    AND JSON_CONTAINS(c.access_levels, ?)
            ";
            
            $params = [json_encode($user_level)];
            
            if ($upcoming_only) {
                $sql .= " AND e.start_time >= NOW()";
            }
            
            $sql .= " ORDER BY e.start_time ASC LIMIT ?";
            $params[] = $limit;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Failed to get events by user level: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if user can access event
     */
    public function canUserAccessEvent($event_id, $user_level) {
        try {
            $stmt = $this->db->prepare("
                SELECT JSON_CONTAINS(c.access_levels, ?) as can_access
                FROM epic_zoom_events e
                JOIN epic_event_categories c ON e.category_id = c.id
                WHERE e.id = ? AND c.is_active = 1
            ");
            $stmt->execute([json_encode($user_level), $event_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (bool)$result['can_access'] : false;
        } catch (Exception $e) {
            error_log('Failed to check user access: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Save Zoom settings
     */
    public function saveZoomSettings($settings) {
        try {
            foreach ($settings as $key => $value) {
                $stmt = $this->db->prepare("
                    INSERT INTO epic_zoom_settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                ");
                $stmt->execute([$key, $value]);
            }
            return true;
        } catch (Exception $e) {
            error_log('Failed to save zoom settings: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Zoom settings
     */
    public function getZoomSettings() {
        try {
            $stmt = $this->db->prepare("SELECT setting_key, setting_value FROM epic_zoom_settings");
            $stmt->execute();
            $settings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            return $settings;
        } catch (Exception $e) {
            error_log('Failed to get zoom settings: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate Zoom meeting (placeholder for actual Zoom API integration)
     */
    public function createZoomMeeting($event_data) {
        // This would integrate with actual Zoom API
        // For now, return mock data
        return [
            'meeting_id' => 'mock_' . time(),
            'join_url' => 'https://zoom.us/j/mock_' . time(),
            'password' => 'mock_pass',
            'start_url' => 'https://zoom.us/s/mock_' . time()
        ];
    }
}

// Initialize global instance
if (!isset($epic_zoom)) {
    $epic_zoom = new EpicZoomIntegration();
}

// =====================================================
// WRAPPER FUNCTIONS FOR COMPATIBILITY
// =====================================================

/**
 * Create zoom event (wrapper function)
 */
function epic_create_zoom_event($data) {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    return $epic_zoom->createEvent($data);
}

/**
 * Update zoom event (wrapper function)
 */
function epic_update_zoom_event($id, $data) {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    return $epic_zoom->updateEvent($id, $data);
}

/**
 * Delete zoom event (wrapper function)
 */
function epic_delete_zoom_event($id) {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    return $epic_zoom->deleteEvent($id);
}

/**
 * Get zoom event (wrapper function)
 */
function epic_get_zoom_event($id) {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    return $epic_zoom->getEvent($id);
}

/**
 * Get zoom events (wrapper function)
 */
function epic_get_zoom_events($filters = []) {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    
    // Handle different parameter formats
    if (is_array($filters)) {
        $page = $filters['page'] ?? 1;
        $limit = $filters['limit'] ?? 20;
        return $epic_zoom->getEvents($page, $limit, $filters);
    } else {
        // Legacy support for old signature
        return $epic_zoom->getEvents(1, 20, []);
    }
}

/**
 * Create zoom event category (wrapper function)
 */
function epic_create_zoom_category($data) {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    return $epic_zoom->createEventCategory($data);
}

/**
 * Update zoom event category (wrapper function)
 */
function epic_update_zoom_category($id, $data) {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    return $epic_zoom->updateEventCategory($id, $data);
}

/**
 * Delete zoom event category (wrapper function)
 */
function epic_delete_zoom_category($id) {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    return $epic_zoom->deleteEventCategory($id);
}

/**
 * Get zoom event categories (wrapper function)
 */
function epic_get_zoom_categories($access_level = null, $active_only = true) {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    
    $categories = $epic_zoom->getEventCategories($active_only);
    
    // Filter by access level if provided
    if ($access_level && is_array($categories)) {
        $categories = array_filter($categories, function($category) use ($access_level) {
            $access_levels = json_decode($category['access_levels'], true);
            return is_array($access_levels) && in_array($access_level, $access_levels);
        });
    }
    
    return $categories;
}

/**
 * Get zoom event category (wrapper function)
 */
function epic_get_zoom_category($id) {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    return $epic_zoom->getEventCategory($id);
}

/**
 * Check if user can access zoom event (wrapper function)
 */
function epic_can_access_zoom_event($event_id, $user_level) {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    return $epic_zoom->canUserAccessEvent($event_id, $user_level);
}

/**
 * Save zoom settings (wrapper function)
 */
function epic_save_zoom_settings($settings) {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    return $epic_zoom->saveZoomSettings($settings);
}

/**
 * Get zoom settings (wrapper function)
 */
function epic_get_zoom_settings() {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    return $epic_zoom->getZoomSettings();
}

/**
 * Save zoom category (wrapper function for create/update)
 */
function epic_save_zoom_category($data, $id = null) {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    
    if ($id) {
        // Update existing category
        return $epic_zoom->updateEventCategory($id, $data);
    } else {
        // Create new category
        $result = $epic_zoom->createEventCategory($data);
        if ($result) {
            // Get the last inserted ID from database connection
            global $epic_db;
            if ($epic_db) {
                return $epic_db->lastInsertId();
            }
            return true; // Fallback if can't get ID
        }
        return false;
    }
}

/**
 * Get zoom event status badge (helper function)
 */
function epic_get_zoom_event_status_badge($status) {
    $badges = [
        'draft' => '<span class="badge badge-secondary">Draft</span>',
        'published' => '<span class="badge badge-success">Published</span>',
        'ongoing' => '<span class="badge badge-primary">Ongoing</span>',
        'completed' => '<span class="badge badge-info">Completed</span>',
        'cancelled' => '<span class="badge badge-danger">Cancelled</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
}

/**
 * Register user for zoom event (wrapper function)
 */
function epic_register_zoom_event($event_id, $user_id = null) {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    
    if (!$user_id) {
        $user_id = epic_get_current_user_id();
    }
    
    try {
        // Check if user is already registered
        global $epic_db;
        if ($epic_db) {
            $stmt = $epic_db->prepare("SELECT id FROM epic_event_registrations WHERE event_id = ? AND user_id = ?");
            $stmt->execute([$event_id, $user_id]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Anda sudah terdaftar untuk event ini'];
            }
            
            // Register user
            $stmt = $epic_db->prepare("INSERT INTO epic_event_registrations (event_id, user_id, registration_date, status) VALUES (?, ?, NOW(), 'confirmed')");
            $result = $stmt->execute([$event_id, $user_id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Berhasil mendaftar event'];
            }
        }
        return ['success' => false, 'message' => 'Gagal mendaftar event'];
    } catch (Exception $e) {
        error_log('Failed to register zoom event: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Terjadi kesalahan saat mendaftar'];
    }
}

/**
 * Cancel zoom event registration (wrapper function)
 */
function epic_cancel_zoom_registration($event_id, $user_id = null) {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    
    if (!$user_id) {
        $user_id = epic_get_current_user_id();
    }
    
    try {
        global $epic_db;
        if ($epic_db) {
            $stmt = $epic_db->prepare("DELETE FROM epic_event_registrations WHERE event_id = ? AND user_id = ?");
            $result = $stmt->execute([$event_id, $user_id]);
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Berhasil membatalkan pendaftaran'];
            }
        }
        return ['success' => false, 'message' => 'Gagal membatalkan pendaftaran'];
    } catch (Exception $e) {
        error_log('Failed to cancel zoom registration: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Terjadi kesalahan saat membatalkan'];
    }
}

/**
 * Get user zoom registrations (wrapper function)
 */
function epic_get_user_zoom_registrations($user_id = null, $status = null, $limit = 10) {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    
    if (!$user_id) {
        $user_id = epic_get_current_user_id();
    }
    
    // Handle different parameter orders
    if (is_string($user_id) && is_numeric($status)) {
        // Called as epic_get_user_zoom_registrations($user_id, 'registered')
        $temp = $status;
        $status = $user_id;
        $user_id = epic_get_current_user_id();
        $limit = $temp;
    } elseif (is_string($status) && $status === 'registered') {
        // Status filter for 'registered' events
        $status = 'confirmed';
    }
    
    try {
        global $epic_db;
        if ($epic_db) {
            $sql = "
                SELECT e.*, c.name as category_name, c.color as category_color, c.icon as category_icon,
                       r.registration_date, r.status as registration_status
                FROM epic_event_registrations r
                JOIN epic_zoom_events e ON r.event_id = e.id
                JOIN epic_event_categories c ON e.category_id = c.id
                WHERE r.user_id = ?
            ";
            
            $params = [$user_id];
            
            if ($status) {
                $sql .= " AND r.status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY e.start_time ASC LIMIT ?";
            $params[] = $limit;
            
            $stmt = $epic_db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    } catch (Exception $e) {
        error_log('Failed to get user zoom registrations: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get featured zoom events (wrapper function)
 */
function epic_get_featured_zoom_events($access_level = null, $limit = 6) {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    
    try {
        global $epic_db;
        if ($epic_db) {
            $sql = "
                SELECT e.*, c.name as category_name, c.color as category_color, c.icon as category_icon,
                       c.access_levels
                FROM epic_zoom_events e
                JOIN epic_event_categories c ON e.category_id = c.id
                WHERE e.status = 'published' AND e.start_time >= NOW()
            ";
            
            $params = [];
            
            // Filter by access level if provided
            if ($access_level) {
                $sql .= " AND JSON_CONTAINS(c.access_levels, ?)";
                $params[] = json_encode($access_level);
            }
            
            $sql .= " ORDER BY e.start_time ASC LIMIT ?";
            $params[] = $limit;
            
            $stmt = $epic_db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    } catch (Exception $e) {
        error_log('Failed to get featured zoom events: ' . $e->getMessage());
        return [];
    }
}

/**
 * Format zoom event date (wrapper function)
 */
function epic_format_zoom_event_date($date, $format = 'full') {
    if (empty($date)) {
        return '-';
    }
    
    try {
        $timestamp = strtotime($date);
        if (!$timestamp) {
            return $date; // Return original if can't parse
        }
        
        switch ($format) {
            case 'short':
                return date('d M Y', $timestamp);
            case 'time':
                return date('H:i', $timestamp);
            case 'datetime':
                return date('d M Y H:i', $timestamp);
            case 'full':
            default:
                return date('d F Y, H:i', $timestamp) . ' WIB';
        }
    } catch (Exception $e) {
        error_log('Failed to format zoom event date: ' . $e->getMessage());
        return $date;
    }
}

/**
 * Create zoom meeting (wrapper function)
 */
function epic_create_zoom_meeting($event_data) {
    global $epic_zoom;
    if (!$epic_zoom) {
        $epic_zoom = new EpicZoomIntegration();
    }
    return $epic_zoom->createZoomMeeting($event_data);
}