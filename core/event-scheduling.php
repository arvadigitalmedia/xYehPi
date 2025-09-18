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
            
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM epic_zoom_events WHERE category_id = ?");
            $stmt->execute([$id]);
            $zoom_count = $stmt->fetchColumn();
            
            if ($schedule_count > 0 || $zoom_count > 0) {
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
    public function getEvent($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT e.*, c.name as category_name, c.color as category_color, c.icon as category_icon,
                       c.access_levels as category_access_levels, u.name as creator_name
                FROM epi_event_schedules e
                LEFT JOIN epic_event_categories c ON e.category_id = c.id
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
            // Validate required fields
            if (empty($data['title'])) {
                error_log('Event creation failed: Title is required');
                return false;
            }
            
            if (empty($data['category_id'])) {
                error_log('Event creation failed: Category is required');
                return false;
            }
            
            if (empty($data['start_time']) || empty($data['end_time'])) {
                error_log('Event creation failed: Start time and end time are required');
                return false;
            }
            
            // Validate category exists
            $stmt = $this->db->prepare("SELECT id FROM epic_event_categories WHERE id = ? AND is_active = 1");
            $stmt->execute([$data['category_id']]);
            if (!$stmt->fetch()) {
                error_log('Event creation failed: Invalid or inactive category ID: ' . $data['category_id']);
                return false;
            }
            
            // Validate date format and logic
            $start_time = strtotime($data['start_time']);
            $end_time = strtotime($data['end_time']);
            
            if ($start_time === false || $end_time === false) {
                error_log('Event creation failed: Invalid date format');
                return false;
            }
            
            if ($end_time <= $start_time) {
                error_log('Event creation failed: End time must be after start time');
                return false;
            }
            
            // Validate access levels
            if (empty($data['access_levels']) || !is_array($data['access_levels'])) {
                error_log('Event creation failed: At least one access level must be specified');
                return false;
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO epi_event_schedules (
                    category_id, title, description, location, start_time, end_time, timezone,
                    max_participants, registration_required, registration_deadline,
                    access_levels, status, event_url, notes, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                intval($data['category_id']),
                trim($data['title']),
                trim($data['description'] ?? ''),
                trim($data['location'] ?? ''),
                $data['start_time'],
                $data['end_time'],
                $data['timezone'] ?? 'Asia/Jakarta',
                !empty($data['max_participants']) ? intval($data['max_participants']) : null,
                $data['registration_required'] ?? 0,
                $data['registration_deadline'] ?? null,
                json_encode($data['access_levels']),
                $data['status'] ?? 'draft',
                trim($data['event_url'] ?? ''),
                trim($data['notes'] ?? ''),
                $data['created_by'] ?? 1
            ]);
            
            if ($result) {
                $event_id = $this->db->lastInsertId();
                error_log('Event created successfully: ' . $data['title'] . ' (ID: ' . $event_id . ')');
            } else {
                $error_info = $this->db->errorInfo();
                error_log('Event creation failed - Database error: ' . $error_info[2]);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log('Failed to create event: ' . $e->getMessage());
            error_log('Event data: ' . json_encode($data));
            return false;
        }
    }
    
    /**
     * Update event
     */
    public function updateEvent($id, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE epi_event_schedules 
                SET category_id = ?, title = ?, description = ?, location = ?, start_time = ?, end_time = ?, timezone = ?,
                    max_participants = ?, registration_required = ?, registration_deadline = ?,
                    access_levels = ?, status = ?, event_url = ?, notes = ?
                WHERE id = ?
            ");
            
            return $stmt->execute([
                $data['category_id'],
                $data['title'],
                $data['description'] ?? '',
                $data['location'] ?? '',
                $data['start_time'],
                $data['end_time'],
                $data['timezone'] ?? 'Asia/Jakarta',
                $data['max_participants'] ?? null,
                $data['registration_required'] ?? 0,
                $data['registration_deadline'] ?? null,
                json_encode($data['access_levels'] ?? ['free']),
                $data['status'] ?? 'draft',
                $data['event_url'] ?? '',
                $data['notes'] ?? '',
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