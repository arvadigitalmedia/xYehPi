<?php
/**
 * EPIC Hub - Landing Page Controller
 * Handles landing page routing and sponsor data integration
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

/**
 * Main landing page route handler
 * Handles URLs like: /userid/landingpage or /userid/template-name
 */
function epic_route_landing($segments) {
    // Extract user ID and template from URL segments
    $user_identifier = $segments[0] ?? null;
    $template_name = $segments[1] ?? 'template-1';
    
    if (!$user_identifier) {
        epic_route_404();
        return;
    }
    
    // Get sponsor data
    $sponsor = epic_get_sponsor_data($user_identifier);
    
    if (!$sponsor) {
        epic_route_404();
        return;
    }
    
    // Get landing page configuration
    $landing_config = epic_get_landing_config($sponsor['id'], $template_name);
    
    // Get product data
    $product = epic_get_landing_product($sponsor['id'], $landing_config);
    
    // Prepare data for template
    $data = [
        'sponsor_id' => $sponsor['id'],
        'sponsor' => $sponsor,
        'product' => $product,
        'landing_config' => $landing_config,
        'template_name' => $template_name,
        'page_title' => ($product['name'] ?? 'Landing Page') . ' - ' . $sponsor['name']
    ];
    
    // Track landing page visit
    epic_track_landing_visit($sponsor['id'], $template_name, $_SERVER['HTTP_USER_AGENT'] ?? '', epic_get_client_ip());
    
    // Render landing page template
    epic_render_landing_template($template_name, $data);
}

/**
 * Get sponsor data by user identifier (ID, referral code, or username)
 */
function epic_get_sponsor_data($identifier) {
    // Try to get user by different identifiers
    $user = null;
    
    // Try by ID first
    if (is_numeric($identifier)) {
        $user = epic_get_user($identifier);
    }
    
    // Try by referral code
    if (!$user) {
        $user = epic_get_user_by_referral_code($identifier);
    }
    
    // Try by username/slug if exists
    if (!$user) {
        $user = db()->selectOne(
            "SELECT * FROM " . TABLE_USERS . " WHERE username = ? OR slug = ?",
            [$identifier, $identifier]
        );
    }
    
    if (!$user || $user['status'] !== 'active') {
        return null;
    }
    
    // Get additional sponsor profile data
    $sponsor_profile = epic_get_sponsor_profile($user['id']);
    
    return [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'phone' => $user['phone'] ?? $sponsor_profile['phone'] ?? '+6281234567890',
        'avatar' => $user['avatar'] ?? $sponsor_profile['avatar'] ?? epic_url('themes/modern/assets/default-avatar.png'),
        'title' => $sponsor_profile['title'] ?? 'Digital Marketing Specialist',
        'experience' => $sponsor_profile['experience'] ?? '5+ Years',
        'bio' => $sponsor_profile['bio'] ?? '',
        'social_links' => $sponsor_profile['social_links'] ?? [],
        'referral_code' => $user['referral_code'],
        'created_at' => $user['created_at']
    ];
}

/**
 * Get sponsor profile data
 */
function epic_get_sponsor_profile($user_id) {
    $profile = db()->selectOne(
        "SELECT * FROM epic_user_profiles WHERE user_id = ?",
        [$user_id]
    );
    
    if (!$profile) {
        return [
            'title' => 'Digital Marketing Specialist',
            'experience' => '5+ Years',
            'bio' => '',
            'phone' => '',
            'avatar' => '',
            'social_links' => []
        ];
    }
    
    return [
        'title' => $profile['title'] ?? 'Digital Marketing Specialist',
        'experience' => $profile['experience'] ?? '5+ Years',
        'bio' => $profile['bio'] ?? '',
        'phone' => $profile['phone'] ?? '',
        'avatar' => $profile['avatar'] ?? '',
        'social_links' => json_decode($profile['social_links'] ?? '[]', true)
    ];
}

/**
 * Get landing page configuration for user
 */
function epic_get_landing_config($user_id, $template_name) {
    $config = db()->selectOne(
        "SELECT * FROM epic_landing_configs WHERE user_id = ? AND template_name = ?",
        [$user_id, $template_name]
    );
    
    if (!$config) {
        // Return default configuration
        return [
            'template_name' => $template_name,
            'is_active' => true,
            'custom_settings' => [],
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
    
    return [
        'id' => $config['id'],
        'template_name' => $config['template_name'],
        'is_active' => (bool)$config['is_active'],
        'custom_settings' => json_decode($config['custom_settings'] ?? '[]', true),
        'created_at' => $config['created_at'],
        'updated_at' => $config['updated_at']
    ];
}

/**
 * Get product data for landing page
 */
function epic_get_landing_product($user_id, $landing_config) {
    // Get user's selected product or default product
    $product_id = $landing_config['custom_settings']['product_id'] ?? null;
    
    if ($product_id) {
        $product = epic_get_product($product_id);
        if ($product) {
            return epic_format_product_for_landing($product);
        }
    }
    
    // Return default product data
    return [
        'id' => null,
        'name' => 'Digital Marketing Mastery',
        'tagline' => 'Transform Your Business with Proven Digital Strategies',
        'description' => 'Complete digital marketing course with live mentoring and lifetime support.',
        'price' => 'Rp 2.997.000',
        'discount_price' => 'Rp 997.000',
        'discount_percentage' => '67%',
        'currency' => 'IDR',
        'features' => [
            'Complete Digital Marketing Course',
            'Live Mentoring Sessions',
            'Private Community Access',
            'Marketing Tools & Templates',
            'Lifetime Updates',
            '30-Day Money Back Guarantee'
        ],
        'testimonials' => epic_get_default_testimonials(),
        'bonuses' => [
            'Free 1-on-1 Consultation (Worth Rp 500.000)',
            'Exclusive Marketing Templates (Worth Rp 300.000)',
            'Lifetime Community Access (Worth Rp 200.000)'
        ],
        'images' => [
            epic_url('themes/modern/assets/product-hero.jpg'),
            epic_url('themes/modern/assets/product-features.jpg')
        ]
    ];
}

/**
 * Format product data for landing page display
 */
function epic_format_product_for_landing($product) {
    return [
        'id' => $product['id'],
        'name' => $product['name'],
        'tagline' => $product['tagline'] ?? $product['description'],
        'description' => $product['description'],
        'price' => epic_format_currency($product['price']),
        'discount_price' => epic_format_currency($product['discount_price'] ?? $product['price']),
        'discount_percentage' => epic_calculate_discount_percentage($product['price'], $product['discount_price'] ?? $product['price']),
        'currency' => 'IDR',
        'features' => json_decode($product['features'] ?? '[]', true),
        'testimonials' => epic_get_product_testimonials($product['id']),
        'bonuses' => json_decode($product['bonuses'] ?? '[]', true),
        'images' => json_decode($product['images'] ?? '[]', true)
    ];
}

/**
 * Get default testimonials
 */
function epic_get_default_testimonials() {
    return [
        [
            'name' => 'Budi Santoso',
            'role' => 'Online Business Owner',
            'content' => 'Dalam 3 bulan setelah mengikuti program ini, omzet bisnis online saya meningkat 300%. Materinya sangat praktis dan mudah diterapkan.',
            'rating' => 5,
            'avatar' => epic_url('themes/modern/assets/testimonial-1.jpg')
        ],
        [
            'name' => 'Sari Dewi',
            'role' => 'Digital Marketer',
            'content' => 'Program terbaik yang pernah saya ikuti! Mentor sangat berpengalaman dan selalu siap membantu. Highly recommended!',
            'rating' => 5,
            'avatar' => epic_url('themes/modern/assets/testimonial-2.jpg')
        ],
        [
            'name' => 'Ahmad Rahman',
            'role' => 'E-commerce Entrepreneur',
            'content' => 'ROI yang luar biasa! Investment terbaik untuk bisnis digital. Sekarang saya bisa generate leads berkualitas setiap hari.',
            'rating' => 5,
            'avatar' => epic_url('themes/modern/assets/testimonial-3.jpg')
        ]
    ];
}

/**
 * Get product testimonials
 */
function epic_get_product_testimonials($product_id) {
    $testimonials = db()->select(
        "SELECT * FROM epic_testimonials WHERE product_id = ? AND is_approved = 1 ORDER BY created_at DESC LIMIT 6",
        [$product_id]
    );
    
    if (empty($testimonials)) {
        return epic_get_default_testimonials();
    }
    
    return array_map(function($testimonial) {
        return [
            'name' => $testimonial['customer_name'],
            'role' => $testimonial['customer_role'] ?? 'Customer',
            'content' => $testimonial['content'],
            'rating' => (int)$testimonial['rating'],
            'avatar' => $testimonial['customer_avatar'] ?? epic_url('themes/modern/assets/default-avatar.png')
        ];
    }, $testimonials);
}

/**
 * Track landing page visit
 */
function epic_track_landing_visit($sponsor_id, $template_name, $user_agent, $ip_address) {
    $data = [
        'sponsor_id' => $sponsor_id,
        'template_name' => $template_name,
        'ip_address' => $ip_address,
        'user_agent' => $user_agent,
        'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
        'visited_at' => date('Y-m-d H:i:s')
    ];
    
    db()->insert('epic_landing_visits', $data);
}

/**
 * Render landing page template
 */
function epic_render_landing_template($template_name, $data) {
    $template_path = EPIC_THEME_DIR . '/modern/landing/' . $template_name . '.php';
    
    if (!file_exists($template_path)) {
        // Fallback to default template
        $template_path = EPIC_THEME_DIR . '/modern/landing/template-1.php';
    }
    
    if (!file_exists($template_path)) {
        epic_route_404();
        return;
    }
    
    // Set headers
    header('Content-Type: text/html; charset=UTF-8');
    
    // Include template
    include $template_path;
}

/**
 * Get available landing page templates
 */
function epic_get_available_templates() {
    $templates_dir = EPIC_THEME_DIR . '/modern/landing/';
    $templates = [];
    
    if (is_dir($templates_dir)) {
        $files = scandir($templates_dir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php' && strpos($file, 'template-') === 0) {
                $template_name = pathinfo($file, PATHINFO_FILENAME);
                $templates[] = [
                    'name' => $template_name,
                    'title' => epic_get_template_title($template_name),
                    'description' => epic_get_template_description($template_name),
                    'preview_image' => epic_url('themes/modern/assets/templates/' . $template_name . '-preview.jpg')
                ];
            }
        }
    }
    
    return $templates;
}

/**
 * Get template title
 */
function epic_get_template_title($template_name) {
    $titles = [
        'template-1' => 'Professional Sales Letter',
        'template-2' => 'Modern Product Showcase',
        'template-3' => 'Minimalist Landing Page',
        'template-4' => 'Video Sales Letter',
        'template-5' => 'E-commerce Style'
    ];
    
    return $titles[$template_name] ?? ucfirst(str_replace('-', ' ', $template_name));
}

/**
 * Get template description
 */
function epic_get_template_description($template_name) {
    $descriptions = [
        'template-1' => 'Professional sales letter with testimonials, countdown timer, and strong call-to-action',
        'template-2' => 'Modern design with product showcase and interactive elements',
        'template-3' => 'Clean and minimalist design focused on conversion',
        'template-4' => 'Video-first landing page with embedded sales video',
        'template-5' => 'E-commerce style with product gallery and reviews'
    ];
    
    return $descriptions[$template_name] ?? 'Professional landing page template';
}

/**
 * Calculate discount percentage
 */
function epic_calculate_discount_percentage($original_price, $discount_price) {
    if ($original_price <= 0 || $discount_price >= $original_price) {
        return '0%';
    }
    
    $discount = (($original_price - $discount_price) / $original_price) * 100;
    return round($discount) . '%';
}

/**
 * Get client IP address
 */
function epic_get_client_ip() {
    $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

/**
 * Admin functions for landing page management
 */

/**
 * Get user's landing page configurations
 */
function epic_get_user_landing_configs($user_id) {
    return db()->select(
        "SELECT * FROM epic_landing_configs WHERE user_id = ? ORDER BY created_at DESC",
        [$user_id]
    );
}

/**
 * Save landing page configuration
 */
function epic_save_landing_config($user_id, $template_name, $settings = []) {
    $existing = db()->selectOne(
        "SELECT id FROM epic_landing_configs WHERE user_id = ? AND template_name = ?",
        [$user_id, $template_name]
    );
    
    $data = [
        'user_id' => $user_id,
        'template_name' => $template_name,
        'custom_settings' => json_encode($settings),
        'is_active' => 1,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    if ($existing) {
        return db()->update('epic_landing_configs', $data, 'id = ?', [$existing['id']]);
    } else {
        $data['created_at'] = date('Y-m-d H:i:s');
        return db()->insert('epic_landing_configs', $data);
    }
}

/**
 * Get landing page analytics
 */
function epic_get_landing_analytics($user_id, $template_name = null, $days = 30) {
    $where_clause = "sponsor_id = ? AND visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
    $params = [$user_id, $days];
    
    if ($template_name) {
        $where_clause .= " AND template_name = ?";
        $params[] = $template_name;
    }
    
    // Get visit statistics
    $total_visits = db()->selectValue(
        "SELECT COUNT(*) FROM epic_landing_visits WHERE {$where_clause}",
        $params
    );
    
    $unique_visits = db()->selectValue(
        "SELECT COUNT(DISTINCT ip_address) FROM epic_landing_visits WHERE {$where_clause}",
        $params
    );
    
    // Get daily visits
    $daily_visits = db()->select(
        "SELECT DATE(visited_at) as date, COUNT(*) as visits 
         FROM epic_landing_visits 
         WHERE {$where_clause} 
         GROUP BY DATE(visited_at) 
         ORDER BY date DESC",
        $params
    );
    
    // Get top referrers
    $top_referrers = db()->select(
        "SELECT referrer, COUNT(*) as visits 
         FROM epic_landing_visits 
         WHERE {$where_clause} AND referrer != '' 
         GROUP BY referrer 
         ORDER BY visits DESC 
         LIMIT 10",
        $params
    );
    
    return [
        'total_visits' => (int)$total_visits,
        'unique_visits' => (int)$unique_visits,
        'daily_visits' => $daily_visits,
        'top_referrers' => $top_referrers,
        'period_days' => $days
    ];
}