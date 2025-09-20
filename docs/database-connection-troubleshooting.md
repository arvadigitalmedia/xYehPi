# Database Connection Troubleshooting Guide

## Error: "Database connection not available"

### 📋 **Deskripsi Error**
```
Database connection not available in /home/bisnisem/dev-test.bisnisemasperak.com/core/event-scheduling.php on line 24
```

Error ini terjadi ketika class `EpicEventScheduling` tidak dapat mengakses koneksi database global `$epic_db`.

### 🔍 **Penyebab Utama**

1. **Urutan Loading Salah**: File `event-scheduling.php` dimuat sebelum database diinisialisasi
2. **Global Variable Tidak Tersedia**: `$epic_db` belum diset saat class diinstansiasi
3. **Database Configuration Error**: Konfigurasi database tidak dimuat dengan benar
4. **Missing Dependencies**: Function `db()` tidak tersedia

### ✅ **Solusi yang Diterapkan**

#### 1. **Perbaikan Bootstrap Loading Order**
**File**: `bootstrap.php`

```php
// Load database configuration FIRST
if (file_exists(EPIC_CONFIG_DIR . '/database.php')) {
    require_once EPIC_CONFIG_DIR . '/database.php';
} else {
    die('Database configuration file not found. Please run the installer.');
}

// Ensure $epic_db is available globally
global $epic_db;
if (!isset($epic_db) || !$epic_db) {
    try {
        $epic_db = db()->getConnection();
    } catch (Exception $e) {
        error_log('Bootstrap: Failed to initialize database connection: ' . $e->getMessage());
        // Continue loading but log the error
    }
}

// Load Event Scheduling if available (AFTER database is initialized)
if (file_exists(EPIC_CORE_DIR . '/event-scheduling.php')) {
    try {
        require_once EPIC_CORE_DIR . '/event-scheduling.php';
    } catch (Exception $e) {
        error_log('Bootstrap: Failed to load event-scheduling.php: ' . $e->getMessage());
        // Continue loading but log the error
    }
}
```

#### 2. **Enhanced Error Handling di EpicEventScheduling**
**File**: `core/event-scheduling.php`

```php
public function __construct() {
    global $epic_db;
    $this->db = $epic_db;
    
    // Validate database connection with better error handling
    if (!$this->db) {
        // Try to initialize database connection if not available
        try {
            if (function_exists('db')) {
                $this->db = db()->getConnection();
                $epic_db = $this->db; // Update global variable
            } else {
                error_log('EpicEventScheduling: Database function not available');
                throw new Exception('Database connection not available - db() function not found');
            }
        } catch (Exception $e) {
            error_log('EpicEventScheduling: Failed to initialize database: ' . $e->getMessage());
            throw new Exception('Database connection not available: ' . $e->getMessage());
        }
    }
    
    // Test database connection
    try {
        $this->db->query('SELECT 1');
    } catch (PDOException $e) {
        error_log('EpicEventScheduling: Database connection test failed: ' . $e->getMessage());
        throw new Exception('Database connection failed: ' . $e->getMessage());
    } catch (Exception $e) {
        error_log('EpicEventScheduling: Database connection error: ' . $e->getMessage());
        throw new Exception('Database connection error: ' . $e->getMessage());
    }
}
```

### 🧪 **Verifikasi Fix**

Jalankan script test untuk memverifikasi perbaikan:

```bash
php test-event-scheduling-db-fix.php
```

**Expected Output:**
```
✅ Global $epic_db: TERSEDIA
✅ Database query test: BERHASIL
✅ Function db(): TERSEDIA
✅ EpicEventScheduling instantiation: BERHASIL
✅ getEventCategories(): BERHASIL
🎉 No Issues Found!
```

### 🚨 **Troubleshooting Lanjutan**

#### Jika Error Masih Terjadi:

1. **Check Database Configuration**
   ```bash
   php -r "require 'bootstrap.php'; var_dump(db()->getConnection());"
   ```

2. **Check Error Logs**
   ```bash
   tail -f /path/to/php/error.log
   ```

3. **Manual Database Test**
   ```php
   <?php
   require_once 'bootstrap.php';
   global $epic_db;
   var_dump($epic_db);
   ?>
   ```

4. **Check File Permissions**
   - Pastikan file `config/database.php` readable
   - Pastikan folder `core/` accessible

#### Common Issues:

| Error | Penyebab | Solusi |
|-------|----------|--------|
| `db() function not found` | Database config tidak dimuat | Check `config/database.php` |
| `PDO connection failed` | Kredensial database salah | Verify DB credentials |
| `Class not found` | Autoloader issue | Check `bootstrap.php` loading order |
| `Permission denied` | File permissions | `chmod 644` pada config files |

### 📁 **File yang Dimodifikasi**

1. **bootstrap.php** - Perbaikan urutan loading dan inisialisasi global `$epic_db`
2. **core/event-scheduling.php** - Enhanced error handling dan fallback mechanism
3. **test-event-scheduling-db-fix.php** - Script verifikasi (baru)

### 🔄 **Rollback Instructions**

Jika perlu rollback:

```bash
# Restore original bootstrap.php
git checkout HEAD~1 -- bootstrap.php

# Restore original event-scheduling.php  
git checkout HEAD~1 -- core/event-scheduling.php
```

### 📞 **Support**

Jika masalah masih berlanjut:

1. Check server error logs
2. Verify database credentials di `config/database.php`
3. Ensure MySQL service running
4. Test database connection manually

---

**Last Updated**: 2025-01-21  
**Version**: 1.0.0  
**Status**: ✅ RESOLVED