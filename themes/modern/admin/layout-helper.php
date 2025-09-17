<?php
/**
 * EPIC Hub Admin Layout Helper
 * Helper functions untuk menggunakan layout global
 * 
 * @version 2.0.0
 * @author EPIC Hub Team
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

/**
 * Render halaman admin dengan layout global
 * 
 * @param string $content_file Path ke file content
 * @param array $data Data untuk halaman
 */
function epic_render_admin_page($content_file, $data = []) {
    // Pastikan user sudah login dan memiliki akses admin
    $user = epic_current_user();
    if (!$user || !in_array($user['role'], ['admin', 'super_admin'])) {
        epic_route_403();
        return;
    }
    
    // Set content file path
    $data['content_file'] = $content_file;
    $data['user'] = $user;
    
    // Include layout utama
    include __DIR__ . '/layout.php';
}

/**
 * Render halaman admin dengan content string
 * 
 * @param string $content HTML content
 * @param array $data Data untuk halaman
 */
function epic_render_admin_content($content, $data = []) {
    // Pastikan user sudah login dan memiliki akses admin
    $user = epic_current_user();
    if (!$user || !in_array($user['role'], ['admin', 'super_admin'])) {
        epic_route_403();
        return;
    }
    
    // Set content
    $data['content'] = $content;
    $data['user'] = $user;
    
    // Include layout utama
    include __DIR__ . '/layout.php';
}

/**
 * Generate breadcrumb otomatis berdasarkan URL
 * 
 * @param array $custom_breadcrumb Custom breadcrumb (optional)
 * @return array
 */
function epic_generate_breadcrumb($custom_breadcrumb = null) {
    if ($custom_breadcrumb !== null) {
        return $custom_breadcrumb;
    }
    
    $breadcrumb = [
        ['text' => 'Admin', 'url' => epic_url('admin')]
    ];
    
    $current_url = $_SERVER['REQUEST_URI'];
    $url_parts = explode('/', trim($current_url, '/'));
    
    if (count($url_parts) > 1) {
        $path = '';
        for ($i = 1; $i < count($url_parts); $i++) {
            $path .= '/' . $url_parts[$i];
            $text = ucwords(str_replace('-', ' ', $url_parts[$i]));
            
            // Special cases for better breadcrumb text
            $text = str_replace([
                'Landing Page Manager',
                'Edit Profile'
            ], [
                'Landing Page Manager',
                'Edit Profile'
            ], $text);
            
            // Don't add link for the last item (current page)
            if ($i === count($url_parts) - 1) {
                $breadcrumb[] = ['text' => $text];
            } else {
                $breadcrumb[] = ['text' => $text, 'url' => epic_url($path)];
            }
        }
    }
    
    return $breadcrumb;
}

/**
 * Create page action button
 * 
 * @param string $type 'button' atau 'link'
 * @param string $text Button text
 * @param array $options Additional options
 * @return array
 */
function epic_create_page_action($type, $text, $options = []) {
    $action = [
        'type' => $type,
        'text' => $text
    ];
    
    // Merge dengan options
    return array_merge($action, $options);
}

/**
 * Shortcut untuk membuat link action
 * 
 * @param string $text Button text
 * @param string $url URL tujuan
 * @param string $icon Feather icon name
 * @param string $class CSS class tambahan
 * @return array
 */
function epic_link_action($text, $url, $icon = null, $class = '') {
    return epic_create_page_action('link', $text, [
        'url' => $url,
        'icon' => $icon,
        'class' => $class
    ]);
}

/**
 * Shortcut untuk membuat button action
 * 
 * @param string $text Button text
 * @param string $onclick JavaScript onclick
 * @param string $icon Feather icon name
 * @param string $class CSS class tambahan
 * @param string $button_type HTML button type
 * @return array
 */
function epic_button_action($text, $onclick = null, $icon = null, $class = '', $button_type = 'button') {
    return epic_create_page_action('button', $text, [
        'onclick' => $onclick,
        'icon' => $icon,
        'class' => $class,
        'button_type' => $button_type
    ]);
}

/**
 * Shortcut untuk membuat submit button action
 * 
 * @param string $text Button text
 * @param string $form Form ID (optional)
 * @param string $icon Feather icon name
 * @param string $class CSS class tambahan
 * @return array
 */
function epic_submit_action($text, $form = null, $icon = 'save', $class = '') {
    return epic_create_page_action('button', $text, [
        'form' => $form,
        'icon' => $icon,
        'class' => $class,
        'button_type' => 'submit'
    ]);
}

/**
 * Get current page identifier untuk menentukan active menu
 * 
 * @return string
 */
function epic_get_current_page() {
    $current_url = $_SERVER['REQUEST_URI'];
    
    // Remove query parameters
    $current_url = strtok($current_url, '?');
    
    // Determine page based on URL patterns
    if (preg_match('/\/admin\/?$/', $current_url)) {
        return 'dashboard';
    } elseif (strpos($current_url, '/admin/manage/landing-page-manager') !== false) {
        return 'landing-page-manager';
    } elseif (strpos($current_url, '/admin/manage/') !== false) {
        return 'manage';
    } elseif (strpos($current_url, '/admin/settings/') !== false) {
        return 'settings';
    } elseif (strpos($current_url, '/admin/edit-profile') !== false) {
        return 'edit-profile';
    }
    
    return 'dashboard';
}

/**
 * Add CSS file ke layout
 * 
 * @param array $css_files Array of CSS file paths
 * @return array
 */
function epic_add_css($css_files) {
    if (!is_array($css_files)) {
        $css_files = [$css_files];
    }
    
    return ['additional_css' => $css_files];
}

/**
 * Add JavaScript file ke layout
 * 
 * @param array $js_files Array of JS file paths
 * @return array
 */
function epic_add_js($js_files) {
    if (!is_array($js_files)) {
        $js_files = [$js_files];
    }
    
    return ['additional_js' => $js_files];
}

/**
 * Add inline CSS ke layout
 * 
 * @param string $css CSS content
 * @return array
 */
function epic_add_inline_css($css) {
    return ['inline_css' => $css];
}

/**
 * Add inline JavaScript ke layout
 * 
 * @param string $js JavaScript content
 * @return array
 */
function epic_add_inline_js($js) {
    return ['inline_js' => $js];
}