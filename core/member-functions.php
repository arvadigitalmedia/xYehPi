<?php
/**
 * EPIC Hub - Member Functions
 * Fungsi-fungsi khusus untuk member area
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

/**
 * Get member landing page limits based on access level
 * 
 * @param array $user User data (optional, uses current user if not provided)
 * @return array Landing page limits
 */
function epic_get_member_landing_page_limits($user = null) {
    if (!$user) {
        $user = epic_current_user();
    }
    
    if (!$user) {
        return [
            'max_pages' => 0,
            'templates' => [],
            'analytics' => false,
            'custom_domain' => false,
            'advanced_features' => false
        ];
    }
    
    // Get access level
    $access_level = epic_get_member_access_level($user);
    
    // Define limits based on access level
    $limits = [
        'free' => [
            'max_pages' => 1,
            'templates' => ['basic'],
            'analytics' => false,
            'custom_domain' => false,
            'advanced_features' => false
        ],
        'epic' => [
            'max_pages' => 10,
            'templates' => ['basic', 'premium'],
            'analytics' => true,
            'custom_domain' => false,
            'advanced_features' => true
        ],
        'epis' => [
            'max_pages' => 50,
            'templates' => ['basic', 'premium', 'exclusive'],
            'analytics' => true,
            'custom_domain' => true,
            'advanced_features' => true
        ]
    ];
    
    return $limits[$access_level] ?? $limits['free'];
}

/**
 * Check if member can create more landing pages
 * 
 * @param array $user User data (optional, uses current user if not provided)
 * @return bool
 */
function epic_member_can_create_landing_page($user = null) {
    if (!$user) {
        $user = epic_current_user();
    }
    
    if (!$user) {
        return false;
    }
    
    // Get current page count
    $current_pages = db()->selectValue(
        "SELECT COUNT(*) FROM " . db()->table('landing_pages') . " WHERE user_id = ?",
        [$user['id']]
    ) ?: 0;
    
    // Get limits
    $limits = epic_get_member_landing_page_limits($user);
    
    return $current_pages < $limits['max_pages'];
}

/**
 * Get member landing page usage statistics
 * 
 * @param array $user User data (optional, uses current user if not provided)
 * @return array Usage statistics
 */
function epic_get_member_landing_page_usage($user = null) {
    if (!$user) {
        $user = epic_current_user();
    }
    
    if (!$user) {
        return [
            'current_pages' => 0,
            'max_pages' => 0,
            'remaining_pages' => 0,
            'usage_percentage' => 0
        ];
    }
    
    // Get current page count
    $current_pages = db()->selectValue(
        "SELECT COUNT(*) FROM " . db()->table('landing_pages') . " WHERE user_id = ?",
        [$user['id']]
    ) ?: 0;
    
    // Get limits
    $limits = epic_get_member_landing_page_limits($user);
    $max_pages = $limits['max_pages'];
    
    $remaining_pages = max(0, $max_pages - $current_pages);
    $usage_percentage = $max_pages > 0 ? round(($current_pages / $max_pages) * 100, 1) : 0;
    
    return [
        'current_pages' => $current_pages,
        'max_pages' => $max_pages,
        'remaining_pages' => $remaining_pages,
        'usage_percentage' => $usage_percentage
    ];
}

/**
 * Get available landing page templates for member
 * 
 * @param array $user User data (optional, uses current user if not provided)
 * @return array Available templates
 */
function epic_get_member_available_templates($user = null) {
    if (!$user) {
        $user = epic_current_user();
    }
    
    if (!$user) {
        return [];
    }
    
    $limits = epic_get_member_landing_page_limits($user);
    $available_templates = $limits['templates'];
    
    // Define template details
    $templates = [
        'basic' => [
            'name' => 'Basic Template',
            'description' => 'Template sederhana untuk landing page',
            'preview' => 'basic-preview.jpg',
            'features' => ['Responsive Design', 'Contact Form', 'Basic Analytics']
        ],
        'premium' => [
            'name' => 'Premium Template',
            'description' => 'Template premium dengan fitur lengkap',
            'preview' => 'premium-preview.jpg',
            'features' => ['Advanced Design', 'Multiple Sections', 'Enhanced Analytics', 'A/B Testing']
        ],
        'exclusive' => [
            'name' => 'Exclusive Template',
            'description' => 'Template eksklusif untuk EPIS',
            'preview' => 'exclusive-preview.jpg',
            'features' => ['Custom Design', 'Advanced Features', 'Priority Support', 'Custom Domain']
        ]
    ];
    
    $result = [];
    foreach ($available_templates as $template_key) {
        if (isset($templates[$template_key])) {
            $result[$template_key] = $templates[$template_key];
        }
    }
    
    return $result;
}