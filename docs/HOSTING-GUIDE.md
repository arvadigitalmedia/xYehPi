# 🚀 EPIC Hub - Panduan Deploy & Testing Hosting cPanel

## 📋 Checklist Upload ke Hosting

### 1. **Persiapan File**
```bash
# File yang WAJIB diupload:
✅ bootstrap.php (sudah diupdate)
✅ config/config.php (sudah diupdate dengan deteksi environment)
✅ config/database.php
✅ core/zoom-integration.php (sudah diperbaiki error handling)
✅ setup-cpanel.php (tool setup khusus hosting)
✅ debug-hosting.php (tool debug)
✅ .env.example (template konfigurasi)

# File yang JANGAN diupload:
❌ .env (jika ada)
❌ config/local-config.php (jika ada)
❌ debug-hosting.php (hapus setelah testing)
```

### 2. **Langkah Upload**

#### **Step 1: Upload Files**
1. Compress semua file project ke ZIP
2. Upload via cPanel File Manager atau FTP
3. Extract di public_html atau subdirectory

#### **Step 2: Setup Database**
1. Buat database MySQL di cPanel
2. Catat informasi:
   - **Database Name**: `cpanel_username_dbname`
   - **Database User**: `cpanel_username_dbuser`
   - **Database Password**: `password_yang_dibuat`
   - **Database Host**: `localhost` (biasanya)

#### **Step 3: Konfigurasi Awal**
```bash
# Akses setup tool:
https://yourdomain.com/setup-cpanel.php?setup_key=epic_setup_2025

# Atau edit manual config/config.php:
# Ganti bagian database dengan info hosting
```

---

## 🔧 Testing & Troubleshooting

### **Test 1: Debug Tool**
```bash
# Akses debug tool:
https://yourdomain.com/debug-hosting.php?debug_key=epic_debug_2025

# Yang harus dicek:
✅ Environment Information
✅ File System Check (semua file ada)
✅ Configuration Check (DB credentials benar)
✅ Database Connection Test (koneksi berhasil)
✅ Bootstrap Test (loading berhasil)
✅ Zoom Integration Test (class bisa diinstansiasi)
```

### **Test 2: Manual Database Test**
```php
<?php
// Buat file test-db.php sementara:
$host = 'localhost';
$dbname = 'cpanel_username_dbname';
$username = 'cpanel_username_dbuser';
$password = 'your_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo "✅ Database connection successful!";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>
```

### **Test 3: Zoom Integration Test**
```bash
# Akses halaman yang menggunakan Zoom:
https://yourdomain.com/events.php
https://yourdomain.com/admin/zoom-settings.php

# Cek error log:
# cPanel > Error Logs > Main Domain
```

---

## 🚨 Troubleshooting Common Issues

### **Issue 1: "Database connection not available"**
```bash
# Solusi:
1. Cek kredensial database di config/config.php
2. Pastikan user database punya akses ke database
3. Cek apakah database host benar (localhost vs IP)
4. Jalankan debug tool untuk detail error
```

### **Issue 2: "Table doesn't exist"**
```bash
# Solusi:
1. Akses: https://yourdomain.com/install-zoom-integration.php
2. Atau import manual file: zoom-integration-schema.sql
3. Cek di cPanel phpMyAdmin apakah tabel sudah ada
```

### **Issue 3: "Permission denied"**
```bash
# Solusi:
1. Set permission folder: 755
2. Set permission file: 644
3. Folder uploads/: 777 (jika ada)
```

### **Issue 4: "Class not found"**
```bash
# Solusi:
1. Cek apakah bootstrap.php di-include
2. Cek path file di require_once
3. Pastikan semua file core/ terupload
```

---

## 📊 Monitoring & Maintenance

### **1. Error Monitoring**
```bash
# Lokasi error log hosting:
- cPanel > Error Logs
- /home/username/public_html/error_log
- /var/log/apache2/error.log (jika akses server)

# Filter error Zoom:
grep -i "zoom\|database" error_log | tail -20
```

### **2. Performance Check**
```bash
# Cek loading time:
https://yourdomain.com/debug-hosting.php?debug_key=epic_debug_2025

# Monitor database queries:
- Aktifkan slow query log di cPanel
- Cek query yang > 2 detik
```

### **3. Security**
```bash
# Hapus file debug setelah selesai:
rm debug-hosting.php
rm test-db.php (jika dibuat)

# Set permission ketat:
chmod 644 config/config.php
chmod 644 core/*.php
```

---

## 🎯 Final Checklist

### **Setelah Upload & Setup:**
- [ ] Database connection berhasil
- [ ] Tabel Zoom sudah ada (epic_zoom_*)
- [ ] Halaman events.php bisa diakses
- [ ] Admin zoom settings bisa diakses
- [ ] Error log bersih dari error Zoom
- [ ] File debug sudah dihapus

### **Jika Masih Error:**
1. **Cek error log terbaru**
2. **Jalankan debug tool**
3. **Verifikasi kredensial database**
4. **Test koneksi manual**
5. **Reinstall tabel jika perlu**

---

## 📞 Support

Jika masih mengalami masalah:
1. Screenshot error dari debug tool
2. Copy error log terbaru
3. Sertakan info hosting (PHP version, MySQL version)
4. Jelaskan langkah yang sudah dicoba

---

**🔥 Pro Tips:**
- Selalu backup database sebelum update
- Test di subdomain dulu sebelum production
- Monitor error log secara berkala
- Update kredensial database jika hosting berubah