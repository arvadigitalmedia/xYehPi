<?php
require_once 'bootstrap.php';
require_once 'form-fields-helper.php';

echo "=== FORM FIELDS ANALYSIS ===\n\n";

echo "Registration Fields:\n";
$fields = get_form_fields('registration');
foreach($fields as $field) {
    echo "- {$field['name']} - {$field['label']} (Required: " . ($field['is_required'] ? 'Yes' : 'No') . ")\n";
}

echo "\nTotal registration fields: " . count($fields) . "\n\n";

echo "Profile Fields:\n";
$profile_fields = get_form_fields('profile');
foreach($profile_fields as $field) {
    echo "- {$field['name']} - {$field['label']} (Required: " . ($field['is_required'] ? 'Yes' : 'No') . ")\n";
}

echo "\nTotal profile fields: " . count($profile_fields) . "\n\n";

echo "Network Fields:\n";
$network_fields = get_form_fields('network');
foreach($network_fields as $field) {
    echo "- {$field['name']} - {$field['label']} (Required: " . ($field['is_required'] ? 'Yes' : 'No') . ")\n";
}

echo "\nTotal network fields: " . count($network_fields) . "\n\n";

// Check for duplicates with standard form fields
$standard_fields = ['name', 'email', 'phone', 'password', 'confirm_password'];
echo "DUPLICATE ANALYSIS:\n";
echo "Standard form fields: " . implode(', ', $standard_fields) . "\n\n";

echo "Dynamic fields that duplicate standard fields:\n";
foreach($fields as $field) {
    if (in_array($field['name'], $standard_fields)) {
        echo "⚠️  DUPLICATE: {$field['name']} - {$field['label']}\n";
    }
}

echo "\nDone.\n";
?>