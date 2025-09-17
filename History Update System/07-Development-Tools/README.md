# Development Tools

## 📋 Overview
Kumpulan tools dan utilities untuk development, testing, dan debugging sistem EPIC Hub.

## 📁 Files in this folder

### User Creation Tools
- `create-test-user.php` - Membuat user test untuk development
- `create-premium-user.php` - Membuat user premium untuk testing
- `create-free-user.php` - Membuat user free untuk testing
- `create-epis-test-account.php` - Membuat akun EPIS untuk testing

### System Check Tools
- `check-admin.php` - Verifikasi konfigurasi admin
- `check-form-fields.php` - Validasi form fields database
- `check-landing-visits-table.php` - Cek tabel landing visits
- `check-orders-table.php` - Validasi struktur tabel orders

### Helper Tools
- `form-fields-helper.php` - Helper untuk manajemen form fields

## 🛠️ User Creation Tools

### 1. Test User Creator
**File**: `create-test-user.php`

**Purpose**: Membuat user test dengan data lengkap untuk development

**Features:**
- Generates realistic test data
- Creates complete user profile
- Sets up referral relationships
- Configures permissions

**Usage:**
```bash
# Via command line
php create-test-user.php

# Via browser
http://localhost/create-test-user.php
```

**Generated Data:**
```php
$test_user = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '+62812345678',
    'status' => 'epic',
    'referral_code' => 'TEST123',
    'affiliate_code' => 'AFF123'
];
```

### 2. Premium User Creator
**File**: `create-premium-user.php`

**Purpose**: Membuat user premium dengan semua fitur aktif

**Features:**
- Premium account status
- All features unlocked
- Sample transaction history
- Commission tracking setup

**Premium Features Enabled:**
- Advanced analytics
- Custom referral links
- Priority support
- Extended API access
- Custom branding

### 3. Free User Creator
**File**: `create-free-user.php`

**Purpose**: Membuat user free dengan limitasi standar

**Features:**
- Basic account features
- Limited access permissions
- Standard referral system
- Basic analytics

### 4. EPIS Test Account Creator
**File**: `create-epis-test-account.php`

**Purpose**: Membuat akun EPIS untuk testing hierarchy system

**Features:**
- EPIS supervisor privileges
- Territory management
- Team member assignment
- Commission structure setup

**EPIS Account Structure:**
```php
$epis_account = [
    'epis_code' => 'EPIS001',
    'territory_name' => 'Test Territory',
    'max_epic_recruits' => 50,
    'recruitment_commission_rate' => 10.00,
    'indirect_commission_rate' => 5.00
];
```

## 🔍 System Check Tools

### 1. Admin Configuration Checker
**File**: `check-admin.php`

**Validates:**
- Admin user existence
- Permission settings
- Configuration completeness
- Security settings

**Check Results:**
```
✅ Admin user exists
✅ Proper permissions set
✅ Security configurations valid
⚠️  Some optional settings missing
```

### 2. Form Fields Checker
**File**: `check-form-fields.php`

**Validates:**
- Database table structure
- Required fields presence
- Data type consistency
- Index optimization

**Validation Process:**
```php
// Check table structure
$required_fields = [
    'id', 'name', 'type', 'required', 
    'validation', 'options', 'created_at'
];

foreach ($required_fields as $field) {
    if (!column_exists('form_fields', $field)) {
        echo "❌ Missing field: {$field}\n";
    }
}
```

### 3. Landing Visits Table Checker
**File**: `check-landing-visits-table.php`

**Validates:**
- Table structure integrity
- Index performance
- Data consistency
- Migration status

**Performance Checks:**
- Query execution time
- Index usage analysis
- Data volume assessment
- Optimization recommendations

### 4. Orders Table Checker
**File**: `check-orders-table.php`

**Validates:**
- Order data integrity
- Status consistency
- Payment tracking
- Commission calculations

## 🔧 Helper Tools

### Form Fields Helper
**File**: `form-fields-helper.php`

**Functions:**
- Dynamic form generation
- Field validation
- Data sanitization
- Form submission handling

**Usage Examples:**
```php
// Generate form field
$field = generate_form_field([
    'name' => 'email',
    'type' => 'email',
    'required' => true,
    'validation' => 'email',
    'placeholder' => 'Enter your email'
]);

// Validate form data
$validation_result = validate_form_data($form_data, $field_definitions);

// Sanitize input
$clean_data = sanitize_form_input($raw_data);
```

## 🧪 Testing Workflows

### Development Environment Setup
```bash
# 1. Create test users
php create-test-user.php
php create-premium-user.php
php create-free-user.php
php create-epis-test-account.php

# 2. Verify system integrity
php check-admin.php
php check-form-fields.php
php check-landing-visits-table.php
php check-orders-table.php

# 3. Test functionality
# Login with different user types
# Test referral system
# Verify commission calculations
# Check EPIS hierarchy
```

### User Testing Scenarios

**Scenario 1: New User Registration**
1. Use free user for basic registration flow
2. Test email verification
3. Verify referral tracking
4. Check welcome sequence

**Scenario 2: Premium Features**
1. Use premium user for advanced features
2. Test analytics dashboard
3. Verify custom branding
4. Check API access

**Scenario 3: EPIS Management**
1. Use EPIS account for team management
2. Test member assignment
3. Verify commission distribution
4. Check territory management

## 📊 Data Generation

### Realistic Test Data
```php
// User profiles with realistic data
$test_profiles = [
    [
        'name' => 'Ahmad Wijaya',
        'email' => 'ahmad.wijaya@email.com',
        'phone' => '+628123456789',
        'city' => 'Jakarta',
        'profession' => 'Marketing Manager'
    ],
    [
        'name' => 'Siti Nurhaliza',
        'email' => 'siti.nurhaliza@email.com',
        'phone' => '+628234567890',
        'city' => 'Surabaya',
        'profession' => 'Business Owner'
    ]
];
```

### Transaction History
```php
// Generate sample transactions
$sample_transactions = [
    [
        'amount' => 150000,
        'type' => 'commission',
        'status' => 'completed',
        'date' => date('Y-m-d', strtotime('-7 days'))
    ],
    [
        'amount' => 75000,
        'type' => 'referral_bonus',
        'status' => 'pending',
        'date' => date('Y-m-d', strtotime('-3 days'))
    ]
];
```

## 🛡️ Security Considerations

### Development Only
- **Never use in production**
- **Delete after development**
- **Use secure test credentials**
- **Limit access permissions**

### Data Protection
```php
// Environment check
if (ENVIRONMENT === 'production') {
    die('Development tools not allowed in production');
}

// IP restriction
$allowed_ips = ['127.0.0.1', '::1', 'localhost'];
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    die('Access denied');
}
```

### Cleanup Procedures
```php
// Remove test data
function cleanup_test_data() {
    // Remove test users
    db()->delete('users', 'email LIKE ?', ['%@test.com']);
    
    // Remove test transactions
    db()->delete('transactions', 'description LIKE ?', ['%TEST%']);
    
    // Reset sequences
    db()->query('ALTER TABLE users AUTO_INCREMENT = 1');
}
```

## 📋 Development Checklist

### Before Development
- [ ] Run system checks
- [ ] Create test users
- [ ] Verify database structure
- [ ] Check permissions
- [ ] Validate configurations

### During Development
- [ ] Test with different user types
- [ ] Verify data integrity
- [ ] Check error handling
- [ ] Validate user flows
- [ ] Monitor performance

### After Development
- [ ] Clean up test data
- [ ] Remove development tools
- [ ] Verify production readiness
- [ ] Update documentation
- [ ] Archive development files

## 🔄 Maintenance

### Regular Tasks
- Update test data scenarios
- Refresh user profiles
- Validate system checks
- Update helper functions
- Review security measures

### Troubleshooting
```php
// Debug mode
define('DEBUG_MODE', true);

// Verbose logging
ini_set('log_errors', 1);
ini_set('error_log', 'debug.log');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

**Status**: ✅ ACTIVE DEVELOPMENT TOOLS
**Date**: September 17, 2025
**Environment**: Development Only
**Security**: IP restricted, environment checked
**Maintenance**: Regular cleanup required