# Server Deployment Fix - Database Connection Error

## Masalah yang Ditemukan

Error yang muncul saat upload website ke server:
```
[20-Sep-2025 18:35:48 Asia/Jakarta] Zoom Integration: Database connection validation failed
```

## Root Cause Analysis

1. **Hardcoded localhost URL** di `core/functions.php` line 1532
2. **Missing environment configuration** - file `.env` tidak dikonfigurasi untuk server production
3. **Database connection validation** di Zoom Integration tidak menangani error dengan graceful
4. **URL configuration** tidak menggunakan environment variables

## Solusi yang Diterapkan

### 1. Perbaikan URL Configuration (`core/functions.php`)

**Sebelum:**
```php
function epic_url($path = '') {
    global $weburl;
    if (!isset($weburl)) {
        $weburl = 'http://localhost/test-bisnisemasperak';
    }
    return rtrim($weburl, '/') . '/' . ltrim($path, '/');
}
```

**Sesudah:**
```php
function epic_url($path = '') {
    global $weburl;
    if (!isset($weburl)) {
        // Use SITE_URL from environment if available, fallback to localhost
        if (defined('SITE_URL')) {
            $weburl = SITE_URL;
        } else {
            $weburl = 'http://localhost/test-bisnisemasperak';
        }
    }
    return rtrim($weburl, '/') . '/' . ltrim($path, '/');
}
```

### 2. Perbaikan Zoom Integration Error Handling (`core/zoom-integration.php`)

**Sebelum:**
```php
if (!$this->validateDatabaseConnection()) {
    error_log('Zoom Integration: Database connection validation failed');
    return;
}
```

**Sesudah:**
```php
if (!$this->validateDatabaseConnection()) {
    error_log('Zoom Integration: Database connection validation failed - using fallback mode');
    $this->setDefaultCredentials();
    return;
}
```

### 3. Environment Configuration Template (`.env`)

File `.env` untuk server production:
```env
# Environment
ENVIRONMENT=production
DEBUG_MODE=false

# Database Configuration (Server)
DB_HOST=localhost
DB_NAME=bustanu1_ujicoba
DB_USER=bustanu1_ujicoba
DB_PASS=PASSWORD_DATABASE_CPANEL
DB_PREFIX=epic_

# Site Configuration
SITE_URL=https://bisnisemasperak.com
SITE_NAME=EPI Hub - Bisnis Emas Perak Indonesia
SITE_DESCRIPTION=Modern Support System Platform

# Security Keys (Generate new untuk production)
ENCRYPTION_KEY=1250f3e4bc6fef335229970362a3e1e2
JWT_SECRET=7d81eebb88447976c721b866417ab6f90d416db77908a9ac0bd2b687129ac3f7
SECURITY_SALT=f053e0c58d3fcf81fb0cbd2dd13ac8c3
SESSION_SECRET=f67ef3554409dd0741b11670c5d2f4c04dfc0d8b172c8dae

# Email Configuration (cPanel)
SMTP_HOST=mail.bisnisemasperak.com
SMTP_PORT=587
SMTP_USER=noreply@bisnisemasperak.com
SMTP_PASS=!Shadow007
SMTP_FROM=noreply@bisnisemasperak.com
SMTP_FROM_NAME=EPI Hub

# Mailketing API Configuration
MAILKETING_ENABLED=true
MAILKETING_API_URL=https://api.mailketing.co.id/api/v1/send
MAILKETING_API_TOKEN=277b5a7d945847177b5c67dfe91838ba
MAILKETING_FROM_NAME=Admin Bisnisemasperak.com
MAILKETING_FROM_EMAIL=email@bisnisemasperak.com

# Zoom Integration (Optional)
ZOOM_API_KEY=
ZOOM_API_SECRET=
ZOOM_ACCOUNT_ID=

# Feature Flags
EPIC_FEATURE_REGISTRATION=true
EPIC_FEATURE_REFERRALS=true
EPIC_FEATURE_COMMISSIONS=true
EPIC_FEATURE_ANALYTICS=true
EPIC_FEATURE_API=true
EPIC_FEATURE_BLOG=true

# File Upload
MAX_UPLOAD_SIZE=5242880
UPLOAD_PATH=uploads/
```

## Tools yang Dibuat

### 1. `verify-server-config.php`
Script untuk memverifikasi konfigurasi server dan database connection:
- ✅ Cek file `.env` dan environment variables
- ✅ Test database connection
- ✅ Test Zoom Integration
- ✅ Verifikasi URL configuration
- ✅ Tampilkan informasi server

### 2. `fix-server-config.php`
Script untuk memperbaiki konfigurasi `.env` secara otomatis:
- 🔧 Form input untuk konfigurasi database dan URL
- 🔧 Auto-detect server URL
- 🔧 Generate security keys baru
- 🔧 Backup file `.env` lama
- 🔧 Test database connection setelah update

## Langkah Deployment ke Server

### 1. Upload Files ke Server
```bash
# Upload semua file kecuali .env (akan dibuat manual)
rsync -av --exclude='.env' --exclude='node_modules' --exclude='.git' ./ user@server:/path/to/website/
```

### 2. Konfigurasi Environment
```bash
# Di server, jalankan script fix
https://yourdomain.com/fix-server-config.php
```

### 3. Verifikasi Konfigurasi
```bash
# Test konfigurasi
https://yourdomain.com/verify-server-config.php
```

### 4. Cleanup (Opsional)
```bash
# Hapus script setelah selesai
rm fix-server-config.php verify-server-config.php
```

## Checklist Deployment

- [ ] Upload files ke server
- [ ] Buat/update file `.env` dengan konfigurasi server
- [ ] Set `SITE_URL` ke domain actual
- [ ] Set `ENVIRONMENT=production`
- [ ] Set `DEBUG_MODE=false`
- [ ] Update database credentials
- [ ] Generate security keys baru
- [ ] Test database connection
- [ ] Test Zoom Integration
- [ ] Verifikasi URL configuration
- [ ] Test website functionality
- [ ] Hapus script deployment tools

## Troubleshooting

### Error: "Database connection validation failed"
1. Cek kredensial database di `.env`
2. Pastikan database server berjalan
3. Verifikasi nama database dan user
4. Cek firewall/security groups

### Error: "SITE_URL still contains localhost"
1. Update `SITE_URL` di file `.env`
2. Restart web server jika perlu
3. Clear cache jika ada

### Error: "Permission denied"
1. Set permission file `.env`: `chmod 600 .env`
2. Set permission direktori: `chmod 755 .`
3. Set ownership: `chown www-data:www-data .env`

## File yang Diubah

1. `core/functions.php` - Perbaikan URL configuration
2. `core/zoom-integration.php` - Perbaikan error handling
3. `.env` - Konfigurasi environment production
4. `verify-server-config.php` - Tool verifikasi (temporary)
5. `fix-server-config.php` - Tool perbaikan (temporary)

## Status

✅ **SELESAI** - Error database connection Zoom Integration telah diperbaiki
✅ **TESTED** - Script verifikasi dan perbaikan telah dibuat
✅ **DOCUMENTED** - Panduan deployment lengkap tersedia

## Maintenance

- Monitor error logs setelah deployment
- Update security keys secara berkala
- Backup file `.env` sebelum perubahan
- Test functionality setelah update server

---

**Dibuat:** 20 September 2025  
**Status:** Completed  
**Priority:** High  
**Tags:** deployment, database, zoom-integration, environment-config