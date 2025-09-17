<?php
/**
 * EPIC Hub Form Fields Helper
 * Helper functions for dynamic form fields management
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

/**
 * Get form fields for specific context
 */
function get_form_fields($context = 'registration', $status = 'active') {
    $context_column = '';
    switch ($context) {
        case 'registration':
            $context_column = 'show_in_registration';
            break;
        case 'profile':
            $context_column = 'show_in_profile';
            break;
        case 'network':
            $context_column = 'show_in_network';
            break;
        default:
            return [];
    }
    
    return db()->select(
        "SELECT * FROM " . TABLE_FORM_FIELDS . " 
         WHERE status = ? AND {$context_column} = 1 
         ORDER BY sort_order ASC, id ASC",
        [$status]
    ) ?: [];
}

/**
 * Render form field HTML for registration
 */
function render_form_field($field, $value = '', $attributes = []) {
    $field_id = 'field_' . $field['name'];
    $field_name = $field['name'];
    $field_label = htmlspecialchars($field['label']);
    $field_type = $field['type'];
    $is_required = $field['is_required'];
    $placeholder = htmlspecialchars($field['placeholder'] ?? '');
    $options = !empty($field['options']) ? json_decode($field['options'], true) : [];
    
    // Build attributes string
    $attr_string = '';
    foreach ($attributes as $key => $val) {
        $attr_string .= ' ' . $key . '="' . htmlspecialchars($val) . '"';
    }
    
    // Add required attribute if needed
    if ($is_required) {
        $attr_string .= ' required';
    }
    
    $html = '<div class="form-field-container">';
    
    // Label
    $html .= '<label for="' . $field_id . '" class="block text-sm font-medium text-white text-opacity-90 mb-2">';
    $html .= $field_label;
    if ($is_required) {
        $html .= ' <span class="text-red-400">*</span>';
    }
    $html .= '</label>';
    
    // Field input based on type
    switch ($field_type) {
        case 'text':
        case 'email':
        case 'password':
        case 'number':
        case 'date':
            $html .= '<div class="relative">';
            $html .= '<input type="' . $field_type . '" ';
            $html .= 'id="' . $field_id . '" ';
            $html .= 'name="' . $field_name . '" ';
            $html .= 'value="' . htmlspecialchars($value) . '" ';
            if ($placeholder) {
                $html .= 'placeholder="' . $placeholder . '" ';
            }
            $html .= 'class="w-full px-4 py-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white placeholder-white placeholder-opacity-50 input-focus transition-all duration-300" ';
            $html .= $attr_string . '>';
            $html .= '</div>';
            break;
            
        case 'textarea':
            $html .= '<textarea ';
            $html .= 'id="' . $field_id . '" ';
            $html .= 'name="' . $field_name . '" ';
            if ($placeholder) {
                $html .= 'placeholder="' . $placeholder . '" ';
            }
            $html .= 'rows="4" ';
            $html .= 'class="w-full px-4 py-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white placeholder-white placeholder-opacity-50 input-focus transition-all duration-300 resize-vertical" ';
            $html .= $attr_string . '>';
            $html .= htmlspecialchars($value);
            $html .= '</textarea>';
            break;
            
        case 'select':
            $html .= '<select ';
            $html .= 'id="' . $field_id . '" ';
            $html .= 'name="' . $field_name . '" ';
            $html .= 'class="w-full px-4 py-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white input-focus transition-all duration-300" ';
            $html .= $attr_string . '>';
            
            if ($placeholder) {
                $html .= '<option value="">' . $placeholder . '</option>';
            }
            
            if (!empty($options)) {
                foreach ($options as $option) {
                    $option_value = is_array($option) ? $option['value'] : $option;
                    $option_label = is_array($option) ? $option['label'] : $option;
                    $selected = ($value == $option_value) ? ' selected' : '';
                    $html .= '<option value="' . htmlspecialchars($option_value) . '"' . $selected . '>';
                    $html .= htmlspecialchars($option_label);
                    $html .= '</option>';
                }
            }
            $html .= '</select>';
            break;
            
        case 'checkbox':
            $html .= '<div class="flex items-center">';
            $html .= '<input type="checkbox" ';
            $html .= 'id="' . $field_id . '" ';
            $html .= 'name="' . $field_name . '" ';
            $html .= 'value="1" ';
            if ($value) {
                $html .= 'checked ';
            }
            $html .= 'class="w-4 h-4 text-blue-600 bg-white bg-opacity-10 border-white border-opacity-20 rounded focus:ring-blue-500 focus:ring-2" ';
            $html .= $attr_string . '>';
            $html .= '<label for="' . $field_id . '" class="ml-2 text-sm text-white text-opacity-90">';
            if ($placeholder) {
                $html .= $placeholder;
            }
            $html .= '</label>';
            $html .= '</div>';
            break;
            
        case 'radio':
            if (!empty($options)) {
                $html .= '<div class="space-y-2">';
                foreach ($options as $index => $option) {
                    $option_value = is_array($option) ? $option['value'] : $option;
                    $option_label = is_array($option) ? $option['label'] : $option;
                    $radio_id = $field_id . '_' . $index;
                    $checked = ($value == $option_value) ? ' checked' : '';
                    
                    $html .= '<div class="flex items-center">';
                    $html .= '<input type="radio" ';
                    $html .= 'id="' . $radio_id . '" ';
                    $html .= 'name="' . $field_name . '" ';
                    $html .= 'value="' . htmlspecialchars($option_value) . '" ';
                    $html .= 'class="w-4 h-4 text-blue-600 bg-white bg-opacity-10 border-white border-opacity-20 focus:ring-blue-500 focus:ring-2" ';
                    $html .= $checked . $attr_string . '>';
                    $html .= '<label for="' . $radio_id . '" class="ml-2 text-sm text-white text-opacity-90">';
                    $html .= htmlspecialchars($option_label);
                    $html .= '</label>';
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
            break;
            
        case 'file':
            $html .= '<input type="file" ';
            $html .= 'id="' . $field_id . '" ';
            $html .= 'name="' . $field_name . '" ';
            $html .= 'class="w-full px-4 py-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" ';
            $html .= $attr_string . '>';
            break;
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Render form field HTML for profile (compact style)
 */
function render_profile_field($field, $value = '', $attributes = []) {
    $field_id = 'profile_' . $field['name'];
    $field_name = $field['name'];
    $field_type = $field['type'];
    $is_required = $field['is_required'];
    $placeholder = htmlspecialchars($field['placeholder'] ?? '');
    $options = !empty($field['options']) ? json_decode($field['options'], true) : [];
    
    // Build attributes string
    $attr_string = '';
    foreach ($attributes as $key => $val) {
        $attr_string .= ' ' . $key . '="' . htmlspecialchars($val) . '"';
    }
    
    // Add required attribute if needed
    if ($is_required) {
        $attr_string .= ' required';
    }
    
    $html = '';
    
    // Field input based on type
    switch ($field_type) {
        case 'text':
        case 'email':
        case 'password':
        case 'number':
        case 'date':
            $html .= '<input type="' . $field_type . '" ';
            $html .= 'id="' . $field_id . '" ';
            $html .= 'name="' . $field_name . '" ';
            $html .= 'value="' . htmlspecialchars($value) . '" ';
            if ($placeholder) {
                $html .= 'placeholder="' . $placeholder . '" ';
            }
            $html .= 'class="form-input-compact" ';
            $html .= $attr_string . '>';
            break;
            
        case 'textarea':
            $html .= '<textarea ';
            $html .= 'id="' . $field_id . '" ';
            $html .= 'name="' . $field_name . '" ';
            if ($placeholder) {
                $html .= 'placeholder="' . $placeholder . '" ';
            }
            $html .= 'rows="3" ';
            $html .= 'class="form-textarea-compact" ';
            $html .= $attr_string . '>';
            $html .= htmlspecialchars($value);
            $html .= '</textarea>';
            break;
            
        case 'select':
            $html .= '<select ';
            $html .= 'id="' . $field_id . '" ';
            $html .= 'name="' . $field_name . '" ';
            $html .= 'class="form-select-compact" ';
            $html .= $attr_string . '>';
            
            if ($placeholder) {
                $html .= '<option value="">' . $placeholder . '</option>';
            }
            
            if (!empty($options)) {
                foreach ($options as $option) {
                    $option_value = is_array($option) ? $option['value'] : $option;
                    $option_label = is_array($option) ? $option['label'] : $option;
                    $selected = ($value == $option_value) ? ' selected' : '';
                    $html .= '<option value="' . htmlspecialchars($option_value) . '"' . $selected . '>';
                    $html .= htmlspecialchars($option_label);
                    $html .= '</option>';
                }
            }
            $html .= '</select>';
            break;
            
        case 'checkbox':
            $html .= '<div class="checkbox-container-compact">';
            $html .= '<input type="checkbox" ';
            $html .= 'id="' . $field_id . '" ';
            $html .= 'name="' . $field_name . '" ';
            $html .= 'value="1" ';
            if ($value) {
                $html .= 'checked ';
            }
            $html .= 'class="form-checkbox-compact" ';
            $html .= $attr_string . '>';
            $html .= '<label for="' . $field_id . '" class="checkbox-label-compact">';
            if ($placeholder) {
                $html .= $placeholder;
            }
            $html .= '</label>';
            $html .= '</div>';
            break;
            
        case 'radio':
            if (!empty($options)) {
                $html .= '<div class="radio-group-compact">';
                foreach ($options as $index => $option) {
                    $option_value = is_array($option) ? $option['value'] : $option;
                    $option_label = is_array($option) ? $option['label'] : $option;
                    $radio_id = $field_id . '_' . $index;
                    $checked = ($value == $option_value) ? ' checked' : '';
                    
                    $html .= '<div class="radio-item-compact">';
                    $html .= '<input type="radio" ';
                    $html .= 'id="' . $radio_id . '" ';
                    $html .= 'name="' . $field_name . '" ';
                    $html .= 'value="' . htmlspecialchars($option_value) . '" ';
                    $html .= 'class="form-radio-compact" ';
                    $html .= $checked . $attr_string . '>';
                    $html .= '<label for="' . $radio_id . '" class="radio-label-compact">';
                    $html .= htmlspecialchars($option_label);
                    $html .= '</label>';
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
            break;
            
        case 'file':
            $html .= '<input type="file" ';
            $html .= 'id="' . $field_id . '" ';
            $html .= 'name="' . $field_name . '" ';
            $html .= 'class="form-file-compact" ';
            $html .= $attr_string . '>';
            break;
    }
    
    return $html;
}

/**
 * Validate form field value
 */
function validate_form_field($field, $value) {
    $errors = [];
    
    // Check required fields
    if ($field['is_required'] && empty($value)) {
        $errors[] = $field['label'] . ' is required.';
        return $errors;
    }
    
    // Skip validation if value is empty and field is not required
    if (empty($value)) {
        return $errors;
    }
    
    // Type-specific validation
    switch ($field['type']) {
        case 'email':
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[] = $field['label'] . ' must be a valid email address.';
            }
            break;
            
        case 'number':
            if (!is_numeric($value)) {
                $errors[] = $field['label'] . ' must be a valid number.';
            }
            break;
            
        case 'date':
            if (!strtotime($value)) {
                $errors[] = $field['label'] . ' must be a valid date.';
            }
            break;
    }
    
    // Custom validation rules
    if (!empty($field['validation_rules'])) {
        $rules = json_decode($field['validation_rules'], true);
        if (is_array($rules)) {
            foreach ($rules as $rule) {
                switch ($rule) {
                    case 'min_length_8':
                        if (strlen($value) < 8) {
                            $errors[] = $field['label'] . ' must be at least 8 characters long.';
                        }
                        break;
                    case 'max_length_100':
                        if (strlen($value) > 100) {
                            $errors[] = $field['label'] . ' must not exceed 100 characters.';
                        }
                        break;
                    case 'phone_format':
                        if (!preg_match('/^[\+]?[0-9\s\-\(\)]+$/', $value)) {
                            $errors[] = $field['label'] . ' must be a valid phone number.';
                        }
                        break;
                }
            }
        }
    }
    
    return $errors;
}

/**
 * Save form field values to user profile
 */
function save_form_field_values($user_id, $field_values) {
    if (empty($field_values) || !$user_id) {
        return false;
    }
    
    try {
        foreach ($field_values as $field_name => $value) {
            // Check if field exists in form_fields table
            $field = db()->selectOne(
                "SELECT id FROM " . TABLE_FORM_FIELDS . " WHERE name = ? AND status = 'active'",
                [$field_name]
            );
            
            if ($field) {
                // Save to user_profile_data table (create if not exists)
                db()->query(
                    "INSERT INTO epic_user_profile_data (user_id, field_name, field_value, created_at) 
                     VALUES (?, ?, ?, NOW()) 
                     ON DUPLICATE KEY UPDATE field_value = VALUES(field_value), updated_at = NOW()",
                    [$user_id, $field_name, $value]
                );
            }
        }
        return true;
    } catch (Exception $e) {
        error_log('Error saving form field values: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get user form field values
 */
function get_user_form_field_values($user_id, $context = 'profile') {
    if (!$user_id) {
        return [];
    }
    
    $context_column = '';
    switch ($context) {
        case 'profile':
            $context_column = 'show_in_profile';
            break;
        case 'network':
            $context_column = 'show_in_network';
            break;
        default:
            $context_column = 'show_in_profile';
    }
    
    $results = db()->select(
        "SELECT ff.name, ff.label, ff.type, upd.field_value 
         FROM " . TABLE_FORM_FIELDS . " ff 
         LEFT JOIN epic_user_profile_data upd ON ff.name = upd.field_name AND upd.user_id = ? 
         WHERE ff.status = 'active' AND ff.{$context_column} = 1 
         ORDER BY ff.sort_order ASC, ff.id ASC",
        [$user_id]
    );
    
    $field_values = [];
    if ($results) {
        foreach ($results as $row) {
            $field_values[$row['name']] = [
                'label' => $row['label'],
                'type' => $row['type'],
                'value' => $row['field_value']
            ];
        }
    }
    
    return $field_values;
}

/**
 * Create user profile data table if not exists
 */
function ensure_user_profile_data_table() {
    $sql = "
        CREATE TABLE IF NOT EXISTS `epic_user_profile_data` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) UNSIGNED NOT NULL,
            `field_name` varchar(100) NOT NULL,
            `field_value` text NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_field_unique` (`user_id`, `field_name`),
            KEY `user_profile_data_user_id_index` (`user_id`),
            KEY `user_profile_data_field_name_index` (`field_name`),
            CONSTRAINT `user_profile_data_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    try {
        db()->query($sql);
        return true;
    } catch (Exception $e) {
        error_log('Error creating user_profile_data table: ' . $e->getMessage());
        return false;
    }
}

// Ensure table exists when this file is included
ensure_user_profile_data_table();
?>