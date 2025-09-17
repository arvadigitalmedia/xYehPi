<?php
/**
 * EPIC Hub LMS Integration
 * Integration layer between LMS products and member interface
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

/**
 * Get LMS products for member interface
 * Replaces the dummy data in products.php
 */
function get_lms_products_for_member($user_access_level = 'free', $limit = null) {
    try {
        $where = "p.status = 'active'";
        $params = [];
        
        // Filter by access level
        if ($user_access_level !== 'admin') {
            $where .= " AND JSON_CONTAINS(p.access_level, ?)";
            $params[] = json_encode($user_access_level);
        }
        
        $limit_clause = $limit ? "LIMIT {$limit}" : '';
        
        $products = db()->select(
            "SELECT p.id,
                    p.name,
                    p.type,
                    p.slug,
                    p.description,
                    p.short_description,
                    p.price,
                    p.duration,
                    p.difficulty_level,
                    p.total_modules as modules,
                    p.estimated_hours,
                    p.certificate_enabled,
                    p.access_level,
                    p.learning_objectives,
                    p.rating,
                    p.total_reviews,
                    p.enrollment_count,
                    p.featured,
                    p.image,
                    c.name as category_name,
                    c.color as category_color,
                    u.name as instructor_name
             FROM epic_products p
             LEFT JOIN epic_product_categories c ON p.category_id = c.id
             LEFT JOIN epic_users u ON p.instructor_id = u.id
             WHERE {$where}
             ORDER BY p.featured DESC, p.created_at DESC
             {$limit_clause}",
            $params
        ) ?: [];
        
        // Process products for member interface
        foreach ($products as &$product) {
            $product['access_level'] = json_decode($product['access_level'], true) ?: [];
            $product['learning_objectives'] = json_decode($product['learning_objectives'], true) ?: [];
            
            // Convert to format expected by member interface
            $product['level_access'] = $product['access_level'];
            $product['status'] = 'available'; // All active products are available
            
            // Add default image if not set
            if (empty($product['image'])) {
                $product['image'] = '/themes/modern/assets/images/default-course.jpg';
            }
        }
        
        return $products;
        
    } catch (Exception $e) {
        error_log('LMS Integration Error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get user's purchased products
 */
function get_user_purchased_products($user_id) {
    try {
        $purchased = db()->select(
            "SELECT DISTINCT p.id
             FROM epic_products p
             INNER JOIN epic_orders o ON p.id = o.product_id
             WHERE o.user_id = ? AND o.status = 'paid'",
            [$user_id]
        ) ?: [];
        
        return array_column($purchased, 'id');
        
    } catch (Exception $e) {
        error_log('LMS Integration Error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get user's learning statistics
 */
function get_user_learning_stats($user_id) {
    try {
        // Get purchased products count
        $purchased_count = db()->selectOne(
            "SELECT COUNT(DISTINCT p.id) as count
             FROM epic_products p
             INNER JOIN epic_orders o ON p.id = o.product_id
             WHERE o.user_id = ? AND o.status = 'paid'",
            [$user_id]
        )['count'] ?? 0;
        
        // Get completed products count
        $completed_count = db()->selectOne(
            "SELECT COUNT(DISTINCT up.product_id) as count
             FROM epic_user_progress up
             INNER JOIN epic_orders o ON up.product_id = o.product_id
             WHERE up.user_id = ? AND o.user_id = ? AND o.status = 'paid' 
             AND up.module_id IS NULL AND up.status = 'completed'",
            [$user_id, $user_id]
        )['count'] ?? 0;
        
        // Get total learning hours
        $total_hours = db()->selectOne(
            "SELECT COALESCE(SUM(up.time_spent), 0) / 3600 as hours
             FROM epic_user_progress up
             INNER JOIN epic_orders o ON up.product_id = o.product_id
             WHERE up.user_id = ? AND o.user_id = ? AND o.status = 'paid'",
            [$user_id, $user_id]
        )['hours'] ?? 0;
        
        // Get certificates count
        $certificates_count = db()->selectOne(
            "SELECT COUNT(*) as count
             FROM epic_user_certificates
             WHERE user_id = ? AND status = 'active'",
            [$user_id]
        )['count'] ?? 0;
        
        return [
            'total_purchased' => intval($purchased_count),
            'total_completed' => intval($completed_count),
            'total_hours' => round(floatval($total_hours), 1),
            'total_certificates' => intval($certificates_count)
        ];
        
    } catch (Exception $e) {
        error_log('LMS Integration Error: ' . $e->getMessage());
        return [
            'total_purchased' => 0,
            'total_completed' => 0,
            'total_hours' => 0,
            'total_certificates' => 0
        ];
    }
}

/**
 * Get user's progress for specific products
 */
function get_user_products_progress($user_id, $product_ids = []) {
    try {
        $where = 'up.user_id = ?';
        $params = [$user_id];
        
        if (!empty($product_ids)) {
            $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
            $where .= " AND up.product_id IN ({$placeholders})";
            $params = array_merge($params, $product_ids);
        }
        
        $progress = db()->select(
            "SELECT up.product_id,
                    up.module_id,
                    up.progress_percentage,
                    up.time_spent,
                    up.status,
                    up.completed_at,
                    up.last_accessed_at,
                    p.name as product_name,
                    p.total_modules,
                    m.title as module_title
             FROM epic_user_progress up
             LEFT JOIN epic_products p ON up.product_id = p.id
             LEFT JOIN epic_product_modules m ON up.module_id = m.id
             WHERE {$where}
             ORDER BY up.last_accessed_at DESC",
            $params
        ) ?: [];
        
        // Organize progress by product
        $organized = [];
        foreach ($progress as $item) {
            $product_id = $item['product_id'];
            if (!isset($organized[$product_id])) {
                $organized[$product_id] = [
                    'product_name' => $item['product_name'],
                    'total_modules' => intval($item['total_modules']),
                    'overall_progress' => 0,
                    'overall_status' => 'not_started',
                    'modules' => [],
                    'last_accessed' => null
                ];
            }
            
            if ($item['module_id']) {
                // Module-specific progress
                $organized[$product_id]['modules'][$item['module_id']] = [
                    'title' => $item['module_title'],
                    'progress' => floatval($item['progress_percentage']),
                    'status' => $item['status'],
                    'time_spent' => intval($item['time_spent']),
                    'completed_at' => $item['completed_at'],
                    'last_accessed_at' => $item['last_accessed_at']
                ];
            } else {
                // Overall course progress
                $organized[$product_id]['overall_progress'] = floatval($item['progress_percentage']);
                $organized[$product_id]['overall_status'] = $item['status'];
                $organized[$product_id]['last_accessed'] = $item['last_accessed_at'];
            }
        }
        
        return $organized;
        
    } catch (Exception $e) {
        error_log('LMS Integration Error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get product modules for member interface
 */
function get_product_modules($product_id, $user_id = null) {
    try {
        $modules = db()->select(
            "SELECT id,
                    title,
                    description,
                    content_type,
                    video_url,
                    video_duration,
                    file_url,
                    sort_order,
                    is_preview,
                    estimated_duration,
                    status
             FROM epic_product_modules
             WHERE product_id = ? AND status = 'published'
             ORDER BY sort_order, created_at",
            [$product_id]
        ) ?: [];
        
        // Add progress information if user is provided
        if ($user_id) {
            $progress_data = get_user_products_progress($user_id, [$product_id]);
            $product_progress = $progress_data[$product_id] ?? [];
            
            foreach ($modules as &$module) {
                $module_progress = $product_progress['modules'][$module['id']] ?? [];
                $module['user_progress'] = $module_progress['progress'] ?? 0;
                $module['user_status'] = $module_progress['status'] ?? 'not_started';
                $module['time_spent'] = $module_progress['time_spent'] ?? 0;
            }
        }
        
        return $modules;
        
    } catch (Exception $e) {
        error_log('LMS Integration Error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Check if user has access to a product
 */
function user_has_product_access($user_id, $product_id, $user_access_level = 'free') {
    try {
        // Check if product exists and is active
        $product = db()->selectOne(
            "SELECT access_level, status FROM epic_products WHERE id = ?",
            [$product_id]
        );
        
        if (!$product || $product['status'] !== 'active') {
            return false;
        }
        
        // Check access level
        $access_levels = json_decode($product['access_level'], true) ?: [];
        if (!in_array($user_access_level, $access_levels)) {
            return false;
        }
        
        // Check if user has purchased the product (for paid products)
        $is_purchased = db()->selectOne(
            "SELECT COUNT(*) as count FROM epic_orders 
             WHERE user_id = ? AND product_id = ? AND status = 'paid'",
            [$user_id, $product_id]
        )['count'] > 0;
        
        // For free access level, no purchase required
        if (in_array('free', $access_levels)) {
            return true;
        }
        
        return $is_purchased;
        
    } catch (Exception $e) {
        error_log('LMS Integration Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update user progress
 */
function update_user_learning_progress($user_id, $product_id, $module_id = null, $progress_data = []) {
    try {
        // Check if user has access
        if (!user_has_product_access($user_id, $product_id)) {
            return false;
        }
        
        // Prepare progress data
        $data = [
            'progress_percentage' => min(100, max(0, floatval($progress_data['progress'] ?? 0))),
            'time_spent' => intval($progress_data['time_spent'] ?? 0),
            'status' => $progress_data['status'] ?? 'in_progress',
            'last_accessed_at' => date('Y-m-d H:i:s')
        ];
        
        if ($data['progress_percentage'] >= 100) {
            $data['status'] = 'completed';
            $data['completed_at'] = date('Y-m-d H:i:s');
        }
        
        // Check if progress record exists
        $existing = db()->selectOne(
            "SELECT id FROM epic_user_progress 
             WHERE user_id = ? AND product_id = ? AND module_id = ?",
            [$user_id, $product_id, $module_id]
        );
        
        if ($existing) {
            // Update existing progress
            db()->update('epic_user_progress', $data, ['id' => $existing['id']]);
        } else {
            // Create new progress record
            $data['uuid'] = generate_uuid();
            $data['user_id'] = $user_id;
            $data['product_id'] = $product_id;
            $data['module_id'] = $module_id;
            db()->insert('epic_user_progress', $data);
        }
        
        // Update overall course progress if this was a module completion
        if ($module_id && $data['status'] === 'completed') {
            update_overall_course_progress($user_id, $product_id);
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log('LMS Integration Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update overall course progress based on module completions
 */
function update_overall_course_progress($user_id, $product_id) {
    try {
        // Get total published modules
        $total_modules = db()->selectOne(
            "SELECT COUNT(*) as count FROM epic_product_modules 
             WHERE product_id = ? AND status = 'published'",
            [$product_id]
        )['count'] ?? 0;
        
        if ($total_modules === 0) {
            return false;
        }
        
        // Get completed modules
        $completed_modules = db()->selectOne(
            "SELECT COUNT(*) as count FROM epic_user_progress 
             WHERE user_id = ? AND product_id = ? AND module_id IS NOT NULL AND status = 'completed'",
            [$user_id, $product_id]
        )['count'] ?? 0;
        
        // Calculate overall progress
        $overall_progress = round(($completed_modules / $total_modules) * 100, 2);
        $overall_status = $completed_modules >= $total_modules ? 'completed' : 'in_progress';
        
        // Update overall progress
        $existing = db()->selectOne(
            "SELECT id FROM epic_user_progress 
             WHERE user_id = ? AND product_id = ? AND module_id IS NULL",
            [$user_id, $product_id]
        );
        
        $data = [
            'progress_percentage' => $overall_progress,
            'status' => $overall_status,
            'last_accessed_at' => date('Y-m-d H:i:s')
        ];
        
        if ($overall_status === 'completed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
            
            // Issue certificate if enabled
            $product = db()->selectOne(
                "SELECT certificate_enabled FROM epic_products WHERE id = ?",
                [$product_id]
            );
            
            if ($product && $product['certificate_enabled']) {
                issue_user_certificate($user_id, $product_id);
            }
        }
        
        if ($existing) {
            db()->update('epic_user_progress', $data, ['id' => $existing['id']]);
        } else {
            $data['uuid'] = generate_uuid();
            $data['user_id'] = $user_id;
            $data['product_id'] = $product_id;
            $data['module_id'] = null;
            db()->insert('epic_user_progress', $data);
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log('LMS Integration Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Issue certificate to user
 */
function issue_user_certificate($user_id, $product_id) {
    try {
        // Check if certificate already exists
        $existing = db()->selectOne(
            "SELECT id FROM epic_user_certificates 
             WHERE user_id = ? AND product_id = ?",
            [$user_id, $product_id]
        );
        
        if ($existing) {
            return $existing['id'];
        }
        
        // Generate certificate number
        $certificate_number = 'EPIC-' . date('Y') . '-' . str_pad($product_id, 4, '0', STR_PAD_LEFT) . '-' . str_pad($user_id, 6, '0', STR_PAD_LEFT);
        
        // Get product and user info
        $product = db()->selectOne(
            "SELECT name FROM epic_products WHERE id = ?",
            [$product_id]
        );
        
        $user = db()->selectOne(
            "SELECT name FROM epic_users WHERE id = ?",
            [$user_id]
        );
        
        // Insert certificate
        $certificate_id = db()->insert('epic_user_certificates', [
            'uuid' => generate_uuid(),
            'user_id' => $user_id,
            'product_id' => $product_id,
            'certificate_number' => $certificate_number,
            'completion_percentage' => 100.00,
            'issued_at' => date('Y-m-d H:i:s'),
            'certificate_data' => json_encode([
                'product_name' => $product['name'] ?? 'Unknown Course',
                'user_name' => $user['name'] ?? 'Unknown User',
                'issued_date' => date('Y-m-d'),
                'completion_date' => date('Y-m-d'),
                'grade' => 'Completed'
            ])
        ]);
        
        return $certificate_id;
        
    } catch (Exception $e) {
        error_log('LMS Integration Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get user certificates
 */
function get_user_certificates($user_id) {
    try {
        $certificates = db()->select(
            "SELECT c.*, p.name as product_name
             FROM epic_user_certificates c
             LEFT JOIN epic_products p ON c.product_id = p.id
             WHERE c.user_id = ? AND c.status = 'active'
             ORDER BY c.issued_at DESC",
            [$user_id]
        ) ?: [];
        
        foreach ($certificates as &$certificate) {
            $certificate['certificate_data'] = json_decode($certificate['certificate_data'], true) ?: [];
        }
        
        return $certificates;
        
    } catch (Exception $e) {
        error_log('LMS Integration Error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Generate UUID helper function
 */
function generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

/**
 * Sync member data with LMS (called automatically)
 */
function sync_member_lms_data() {
    try {
        // This function can be called periodically to ensure data consistency
        // Update product statistics
        db()->query(
            "UPDATE epic_products p SET 
             enrollment_count = (
                 SELECT COUNT(*) FROM epic_orders o 
                 WHERE o.product_id = p.id AND o.status = 'paid'
             ),
             total_modules = (
                 SELECT COUNT(*) FROM epic_product_modules m 
                 WHERE m.product_id = p.id AND m.status = 'published'
             )"
        );
        
        return true;
        
    } catch (Exception $e) {
        error_log('LMS Sync Error: ' . $e->getMessage());
        return false;
    }
}

// Auto-sync on include (can be disabled if needed)
if (defined('EPIC_AUTO_SYNC_LMS') && EPIC_AUTO_SYNC_LMS) {
    sync_member_lms_data();
}
?>