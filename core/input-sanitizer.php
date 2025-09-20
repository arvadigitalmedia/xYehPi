<?php
/**
 * EPIC Input Sanitization Functions
 * Enhanced input sanitization and validation
 */

/**
 * Sanitize string input
 */
function epic_sanitize_string($input, $max_length = 255) {
    if (!is_string($input)) return "";
    
    $sanitized = trim($input);
    $sanitized = strip_tags($sanitized);
    $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, "UTF-8");
    
    if ($max_length > 0) {
        $sanitized = substr($sanitized, 0, $max_length);
    }
    
    return $sanitized;
}

/**
 * Sanitize email input
 */
function epic_sanitize_email($email) {
    $email = trim($email);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    return strtolower($email);
}

/**
 * Sanitize phone number
 */
function epic_sanitize_phone($phone) {
    $phone = preg_replace("/[^0-9+\-\(\)\s]/", "", $phone);
    $phone = trim($phone);
    
    return $phone;
}

/**
 * Sanitize integer input
 */
function epic_sanitize_int($input, $min = null, $max = null) {
    $value = filter_var($input, FILTER_VALIDATE_INT);
    
    if ($value === false) {
        return 0;
    }
    
    if ($min !== null && $value < $min) {
        return $min;
    }
    
    if ($max !== null && $value > $max) {
        return $max;
    }
    
    return $value;
}

/**
 * Sanitize filename for uploads
 */
function epic_sanitize_filename($filename) {
    $filename = basename($filename);
    $filename = preg_replace("/[^a-zA-Z0-9\-_\.]/", "", $filename);
    $filename = trim($filename, ".");
    
    if (empty($filename)) {
        $filename = "file_" . time();
    }
    
    return $filename;
}

/**
 * Sanitize URL input
 */
function epic_sanitize_url($url) {
    $url = trim($url);
    $url = filter_var($url, FILTER_SANITIZE_URL);
    
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    return $url;
}

/**
 * Sanitize HTML content (for rich text)
 */
function epic_sanitize_html($html) {
    $allowed_tags = "<p><br><strong><em><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6>";
    
    $html = strip_tags($html, $allowed_tags);
    $html = htmlspecialchars_decode($html);
    $html = htmlspecialchars($html, ENT_QUOTES, "UTF-8");
    
    return $html;
}

/**
 * Comprehensive input sanitization
 */
function epic_sanitize_input($input, $type = "string", $options = []) {
    switch ($type) {
        case "email":
            return epic_sanitize_email($input);
        case "phone":
            return epic_sanitize_phone($input);
        case "int":
            return epic_sanitize_int($input, $options["min"] ?? null, $options["max"] ?? null);
        case "filename":
            return epic_sanitize_filename($input);
        case "url":
            return epic_sanitize_url($input);
        case "html":
            return epic_sanitize_html($input);
        case "string":
        default:
            return epic_sanitize_string($input, $options["max_length"] ?? 255);
    }
}
