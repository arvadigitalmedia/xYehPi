<?php
/**
 * EPIC Hub Event Scheduling Core
 * Sistem penjadwalan event tanpa integrasi Zoom
 * 
 * @package EPIC Hub
 * @subpackage Event Scheduling
 * @version 1.0.0
 */

if (!defined('EPIC_LOADED')) {
    die('Direct access not allowed');
}

class EpicEventScheduling {
    private $db;
    
    public function __construct() {
        global $epic_db;
        $this->db = $epic_db;
        
        // Validate database connection
        if (!$this->db) {
            throw new Exception('Database connection not available');
        }
        
        // Test database connection
        try {
            $this->db->query('SELECT 1');
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get all event categories (shared with Zoom Integration)
     */
    public function getEventCategories($access_level = null) {
        try {
            $sql = "SELECT * FROM epic_event_categories WHERE is_active = 1";
            $params = [];
            
            if ($access_level) {
                $sql .= " AND JSON_CONTAINS(access_levels, ?)"; 
                $params[] = json_encode($access_level);
            }
            
            $sql .= " ORDER BY name ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Failed to get event categories: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get single event category (shared with Zoom Integration)
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
     * Create new event category (shared with Zoom Integration)
     */
    public function createEventCategory($data) {
        try {
            // Validate required fields
            if (empty($data['name'])) {
                error_log('Event category creation failed: Name is required');
                return false;
            }
            
            // Check if category name already exists
            $stmt = $this->db->prepare("SELECT id FROM epic_event_categories WHERE name = ? AND is_active = 1");
            $stmt->execute([$data['name']]);
            if ($stmt->fetch()) {
                error_log('Event category creation failed: Category name already exists');
                return false;
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO epic_event_categories (
                    name, description, access_levels, color, icon, created_by
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                trim($data['name']),
                trim($data['description'] ?? ''),
                json_encode($data['access_levels'] ?? ['free']),
                $data['color'] ?? '#3B82F6',
                $data['icon'] ?? 'calendar',
                $data['created_by'] ?? 1
            ]);
            
            if ($result) {
                error_log('Event category created successfully: ' . $data['name']);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log('Failed to create event category: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update event category (shared with Zoom Integration)
     */
    public function updateEventCategory($id, $data) {
        try {
            // Validate required fields
            if (empty($data['name'])) {
                error_log('Event category update failed: Name is required');
                return false;
            }
            
            // Check if category name already exists (excluding current category)
            $stmt = $this->db->prepare("SELECT id FROM epic_event_categories WHERE name = ? AND id != ? AND is_active = 1");
            $stmt->execute([$data['name'], $id]);
            if ($stmt->fetch()) {
                error_log('Event category update failed: Category name already exists');
                return false;
            }
            
            $stmt = $this->db->prepare("
                UPDATE epic_event_categories 
                SET name = ?, description = ?, access_levels = ?, color = ?, icon = ?, is_active = ?
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                trim($data['name']),
                trim($data['description'] ?? ''),
                json_encode($data['access_levels'] ?? ['free']),
                $data['color'] ?? '#3B82F6',
                $data['icon'] ?? 'calendar',
                $data['is_active'] ?? 1,
                $id
            ]);
            
            if ($result) {
                error_log('Event category updated successfully: ' . $data['name']);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log('Failed to update event category: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete event category (shared with Zoom Integration)
     */
    public function deleteEventCategory($id) {
        try {
            // Check if category has events in both systems
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM epi_event_schedules WHERE category_id = ?");
            $stmt->execute([$id]);
            $schedule_count = $stmt->fetchColumn();
            
            if ($schedule_count > 0) {
                error_log('Cannot delete category: Category has associated events');
                return false; // Cannot delete category with events
            }
            
            $stmt = $this->db->prepare("DELETE FROM epic_event_categories WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                error_log('Event category deleted successfully: ID ' . $id);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log('Failed to delete event category: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate access levels based on event type
     */
    public function validateEventAccessLevels($category_name, $access_levels) {
        $validation_rules = [
            'EPI Connect' => ['epis'],
            'EPI Insight' => ['epis', 'epic'],
            'Webinar EPI' => ['free', 'epis', 'epic']
        ];
        
        // Check if category has specific rules
        if (!isset($validation_rules[$category_name])) {
            return ['valid' => true, 'message' => ''];
        }
        
        $allowed_levels = $validation_rules[$category_name];
        $invalid_levels = array_diff($access_levels, $allowed_levels);
        
        if (!empty($invalid_levels)) {
            $allowed_text = implode(', ', array_map('strtoupper', $allowed_levels));
            $invalid_text = implode(', ', array_map('strtoupper', $invalid_levels));
            
            return [
                'valid' => false,
                'message' => "Event {$category_name} hanya dapat diakses oleh: {$allowed_text}. Level akses tidak valid: {$invalid_text}"
            ];
        }
        
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Get all events with pagination and filters
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
            
            if (!empty($filters['access_level'])) {
                $where_conditions[] = "JSON_CONTAINS(e.access_levels, ?)";
                $params[] = json_encode($filters['access_level']);
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
                FROM epi_event_schedules e
                LEFT JOIN epic_event_categories c ON e.category_id = c.id
                LEFT JOIN epic_users u ON e.created_by = u.id
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
                FROM epi_event_schedules e
                LEFT JOIN epic_event_categories c ON e.category_id = c.id
                {$where_clause}
            ";
            
            $count_params = array_slice($params, 0, -2); // Remove limit and offset
            $stmt = $this->db->prepare($count_sql);
            $stmt->execute($count_params);
            $total = $stmt->fetchColumn();
            
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
    public function getEventById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT e.*, c.name as category_name, c.color as category_color, c.icon as category_icon,
                       u.name as creator_name
                FROM epi_event_schedules e
                LEFT JOIN epic_event_categories c ON e.category_id = c.id
                LEFT JOIN epic_users u ON e.created_by = u.id
                WHERE e.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Failed to get event by ID: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get single event by ID
     */
    public function getEvent($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT e.*, c.name as category_name, c.color as category_color, c.icon as category_icon,
                       c.access_levels as category_access_levels, u.name as creator_name
                FROM epi_event_schedules e
                LEFT JOIN epic_event_categories c ON e.category_id = c.id
                LEFT JOIN epic_users u ON e.created_by = u.id
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
            // Detect draft mode from action parameter
            $is_draft = isset($data['action']) && $data['action'] === 'save_draft';
            
            // Validate required fields
            if (empty($data['title'])) {
                return ['success' => false, 'message' => 'Judul event wajib diisi'];
            }
            
            if (empty($data['category_id'])) {
                return ['success' => false, 'message' => 'Kategori event wajib dipilih'];
            }
            
            // For published events, validate time fields
            if (!$is_draft) {
                if (empty($data['start_time']) || empty($data['end_time'])) {
                    return ['success' => false, 'message' => 'Waktu mulai dan berakhir wajib diisi'];
                }
                
                // Validate date format and logic
                $start_time = strtotime($data['start_time']);
                $end_time = strtotime($data['end_time']);
                
                if ($start_time === false || $end_time === false) {
                    return ['success' => false, 'message' => 'Format tanggal tidak valid'];
                }
                
                if ($end_time <= $start_time) {
                    return ['success' => false, 'message' => 'Waktu berakhir harus setelah waktu mulai'];
                }
            } else {
                // For draft, validate time format if provided
                if (!empty($data['start_time'])) {
                    $start_time = strtotime($data['start_time']);
                    if ($start_time === false) {
                        return ['success' => false, 'message' => 'Format waktu mulai tidak valid'];
                    }
                }
                
                if (!empty($data['end_time'])) {
                    $end_time = strtotime($data['end_time']);
                    if ($end_time === false) {
                        return ['success' => false, 'message' => 'Format waktu berakhir tidak valid'];
                    }
                }
                
                // Check time logic if both provided
                if (!empty($data['start_time']) && !empty($data['end_time'])) {
                    $start_time = strtotime($data['start_time']);
                    $end_time = strtotime($data['end_time']);
                    if ($end_time <= $start_time) {
                        return ['success' => false, 'message' => 'Waktu berakhir harus setelah waktu mulai'];
                    }
                }
            }
            
            // Validate category exists and get category info for access level validation
            $category_info = null;
            if (!empty($data['category_id'])) {
                $stmt = $this->db->prepare("SELECT id, name FROM epic_event_categories WHERE id = ? AND is_active = 1");
                $stmt->execute([$data['category_id']]);
                $category_info = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$category_info) {
                    return ['success' => false, 'message' => 'Kategori tidak valid atau tidak aktif'];
                }
            }
            
            // Validate access levels based on event type (only for published events)
            if (!$is_draft && $category_info && isset($data['access_levels'])) {
                $access_levels_input = is_array($data['access_levels']) ? $data['access_levels'] : json_decode($data['access_levels'], true);
                if ($access_levels_input) {
                    $validation = $this->validateEventAccessLevels($category_info['name'], $access_levels_input);
                    if (!$validation['valid']) {
                        return ['success' => false, 'message' => $validation['message']];
                    }
                }
            }
            
            // Determine status
            $status = $is_draft ? 'draft' : 'published';
            
            // Set access levels from input or default
            $access_levels = ['free', 'epic', 'epis']; // Default to all levels
            if (isset($data['access_levels'])) {
                $input_levels = is_array($data['access_levels']) ? $data['access_levels'] : json_decode($data['access_levels'], true);
                if ($input_levels && is_array($input_levels)) {
                    $access_levels = $input_levels;
                }
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO epi_event_schedules (
                    category_id, title, description, location, start_time, end_time, timezone,
                    max_participants, registration_required, registration_deadline,
                    access_levels, status, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            // For draft, use default times if not provided
            $start_time = $data['start_time'] ?? null;
            $end_time = $data['end_time'] ?? null;
            
            if ($is_draft) {
                // Use default future times for draft if not provided
                if (empty($start_time)) {
                    $start_time = date('Y-m-d H:i:s', strtotime('+1 day'));
                }
                if (empty($end_time)) {
                    $end_time = date('Y-m-d H:i:s', strtotime('+1 day +2 hours'));
                }
            }
            
            // Prepare execute parameters
            $execute_params = [
                $data['category_id'] ?? null,
                trim($data['title'] ?? ''),
                trim($data['description'] ?? ''),
                trim($data['location'] ?? ''),
                $start_time,
                $end_time,
                $data['timezone'] ?? 'Asia/Jakarta',
                !empty($data['max_participants']) ? intval($data['max_participants']) : null,
                $data['registration_required'] ?? 0,
                $data['registration_deadline'] ?? null,
                json_encode($access_levels),
                $status,
                $data['created_by'] ?? 1
            ];
            
            // Log parameters for debugging
            error_log('Event creation attempt - Status: ' . $status . ', Params: ' . json_encode($execute_params));
            
            $result = $stmt->execute($execute_params);
            
            if ($result) {
                $event_id = $this->db->lastInsertId();
                $action = $is_draft ? 'Draft disimpan' : 'Event dibuat';
                error_log($action . ' berhasil: ' . ($data['title'] ?? 'Untitled') . ' (ID: ' . $event_id . ')');
                
                return [
                    'success' => true,
                    'event_id' => $event_id,
                    'message' => $action . ' berhasil'
                ];
            } else {
                $error_info = $this->db->errorInfo();
                error_log('Event creation failed - Database error: ' . $error_info[2]);
                return ['success' => false, 'message' => 'Gagal menyimpan ke database: ' . $error_info[2]];
            }
            
        } catch (PDOException $e) {
            $error_code = $e->getCode();
            $error_message = $e->getMessage();
            
            // Log detailed error
            error_log('PDO Error creating event: ' . $error_message);
            error_log('Event data: ' . json_encode($data));
            
            // User-friendly error messages
            if (strpos($error_message, 'Connection') !== false) {
                return ['success' => false, 'message' => 'Koneksi database bermasalah. Silakan coba lagi.'];
            } elseif (strpos($error_message, 'Duplicate') !== false) {
                return ['success' => false, 'message' => 'Event dengan judul yang sama sudah ada.'];
            } elseif (strpos($error_message, 'foreign key') !== false) {
                return ['success' => false, 'message' => 'Kategori yang dipilih tidak valid.'];
            } else {
                return ['success' => false, 'message' => 'Gagal menyimpan event. Silakan periksa data dan coba lagi.'];
            }
        } catch (Exception $e) {
            error_log('Failed to create event: ' . $e->getMessage());
            error_log('Event data: ' . json_encode($data));
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'];
        }
    }
    
    /**
     * Update event
     */
    public function updateEvent($data) {
        try {
            $id = $data['id'];
            
            // Validate required fields for published events
            if (isset($data['is_draft']) && !$data['is_draft']) {
                if (empty($data['title'])) {
                    return ['success' => false, 'message' => 'Judul event wajib diisi'];
                }
                if (empty($data['category_id'])) {
                    return ['success' => false, 'message' => 'Kategori event wajib dipilih'];
                }
                if (empty($data['start_time']) || empty($data['end_time'])) {
                    return ['success' => false, 'message' => 'Waktu mulai dan berakhir wajib diisi'];
                }
                
                // Get category info for validation
                $category = $this->getEventCategoryById($data['category_id']);
                if (!$category) {
                    return ['success' => false, 'message' => 'Kategori tidak valid'];
                }
                
                // Validate access levels for published events
                $validation_result = $this->validateEventAccessLevels($category['name'], $data['access_levels']);
                if (!$validation_result['valid']) {
                    return ['success' => false, 'message' => $validation_result['message']];
                }
            }
            
            // Set access levels from input or default
            $access_levels = ['free', 'epic', 'epis']; // Default to all levels
            if (isset($data['access_levels'])) {
                $input_levels = is_array($data['access_levels']) ? $data['access_levels'] : json_decode($data['access_levels'], true);
                if ($input_levels && is_array($input_levels)) {
                    $access_levels = $input_levels;
                }
            }
            
            // Determine status
            $status = isset($data['is_draft']) && $data['is_draft'] ? 'draft' : 'published';
            
            $stmt = $this->db->prepare("
                UPDATE epi_event_schedules 
                SET category_id = ?, title = ?, description = ?, location = ?, start_time = ?, end_time = ?, timezone = ?,
                    max_participants = ?, registration_required = ?, registration_deadline = ?,
                    access_levels = ?, status = ?, event_url = ?, notes = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $data['category_id'],
                $data['title'],
                $data['description'] ?? '',
                $data['location'] ?? '',
                $data['start_time'],
                $data['end_time'],
                $data['timezone'] ?? 'Asia/Jakarta',
                $data['max_participants'] ?? null,
                isset($data['registration_required']) ? 1 : 0,
                $data['registration_deadline'] ?? null,
                json_encode($access_levels),
                $status,
                $data['event_url'] ?? '',
                $data['notes'] ?? '',
                $id
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Event berhasil diupdate', 'event_id' => $id];
            } else {
                return ['success' => false, 'message' => 'Gagal mengupdate event'];
            }
            
        } catch (Exception $e) {
            error_log('Failed to update event: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()];
        }
    }
    
    /**
     * Delete event
     */
    public function deleteEvent($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM epi_event_schedules WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log('Failed to delete event: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Register user for event
     */
    public function registerUserForEvent($event_id, $user_id, $notes = '') {
        try {
            // Check if event exists and registration is required
            $event = $this->getEvent($event_id);
            if (!$event || !$event['registration_required']) {
                return false;
            }
            
            // Check if user already registered
            $stmt = $this->db->prepare("
                SELECT id FROM epi_event_schedule_registrations 
                WHERE event_id = ? AND user_id = ?
            ");
            $stmt->execute([$event_id, $user_id]);
            if ($stmt->fetch()) {
                return false; // Already registered
            }
            
            // Check max participants
            if ($event['max_participants'] && $event['current_participants'] >= $event['max_participants']) {
                return false; // Event full
            }
            
            // Register user
            $stmt = $this->db->prepare("
                INSERT INTO epi_event_schedule_registrations (event_id, user_id, notes)
                VALUES (?, ?, ?)
            ");
            
            return $stmt->execute([$event_id, $user_id, $notes]);
        } catch (Exception $e) {
            error_log('Failed to register user for event: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cancel user registration
     */
    public function cancelUserRegistration($event_id, $user_id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE epi_event_schedule_registrations 
                SET status = 'cancelled' 
                WHERE event_id = ? AND user_id = ?
            ");
            return $stmt->execute([$event_id, $user_id]);
        } catch (Exception $e) {
            error_log('Failed to cancel user registration: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user registrations
     */
    public function getUserRegistrations($user_id, $status = null) {
        try {
            $sql = "
                SELECT r.*, e.title, e.start_time, e.end_time, e.location, e.event_url,
                       c.name as category_name, c.color as category_color
                FROM epi_event_schedule_registrations r
                JOIN epi_event_schedules e ON r.event_id = e.id
                LEFT JOIN epic_event_categories c ON e.category_id = c.id
                WHERE r.user_id = ?
            ";
            
            $params = [$user_id];
            
            if ($status) {
                $sql .= " AND r.status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY e.start_time ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Failed to get user registrations: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get featured events for member dashboard
     */
    public function getFeaturedEvents($access_level = 'free', $limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT e.*, c.name as category_name, c.color as category_color, c.icon as category_icon
                FROM epi_event_schedules e
                LEFT JOIN epic_event_categories c ON e.category_id = c.id
                WHERE e.status = 'published' 
                AND e.start_time > NOW()
                AND JSON_CONTAINS(e.access_levels, ?)
                ORDER BY e.start_time ASC
                LIMIT ?
            ");
            
            $stmt->execute([json_encode($access_level), $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Failed to get featured events: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Format event date for display
     */
    public function formatEventDate($datetime, $timezone = 'Asia/Jakarta') {
        try {
            $date = new DateTime($datetime, new DateTimeZone($timezone));
            return [
                'date' => $date->format('d M Y'),
                'time' => $date->format('H:i'),
                'day' => $date->format('l'),
                'full' => $date->format('l, d M Y H:i') . ' WIB'
            ];
        } catch (Exception $e) {
            return [
                'date' => date('d M Y', strtotime($datetime)),
                'time' => date('H:i', strtotime($datetime)),
                'day' => date('l', strtotime($datetime)),
                'full' => date('l, d M Y H:i', strtotime($datetime)) . ' WIB'
            ];
        }
    }
}

// Initialize global instance
if (!isset($GLOBALS['epic_event_scheduling'])) {
    $GLOBALS['epic_event_scheduling'] = new EpicEventScheduling();
}

/**
 * Helper functions for easy access
 */
function epic_get_event_scheduling() {
    return $GLOBALS['epic_event_scheduling'];
}

function epic_get_event_schedule_categories($access_level = null) {
    return epic_get_event_scheduling()->getEventCategories($access_level);
}

function epic_get_event_schedules($page = 1, $limit = 20, $filters = []) {
    return epic_get_event_scheduling()->getEvents($page, $limit, $filters);
}

function epic_get_featured_event_schedules($access_level = 'free', $limit = 5) {
    return epic_get_event_scheduling()->getFeaturedEvents($access_level, $limit);
}

function epic_register_for_event_schedule($event_id, $user_id, $notes = '') {
    return epic_get_event_scheduling()->registerUserForEvent($event_id, $user_id, $notes);
}

function epic_cancel_event_schedule_registration($event_id, $user_id) {
    return epic_get_event_scheduling()->cancelUserRegistration($event_id, $user_id);
}

function epic_get_user_event_schedule_registrations($user_id, $status = null) {
    return epic_get_event_scheduling()->getUserRegistrations($user_id, $status);
}

function epic_format_event_schedule_date($datetime, $timezone = 'Asia/Jakarta') {
    return epic_get_event_scheduling()->formatEventDate($datetime, $timezone);
}

function epic_get_event_schedule_status_badge($status) {
    $badges = [
        'draft' => '<span class="badge badge-secondary">Draft</span>',
        'published' => '<span class="badge badge-success">Published</span>',
        'ongoing' => '<span class="badge badge-primary">Ongoing</span>',
        'completed' => '<span class="badge badge-info">Completed</span>',
        'cancelled' => '<span class="badge badge-danger">Cancelled</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}
?>