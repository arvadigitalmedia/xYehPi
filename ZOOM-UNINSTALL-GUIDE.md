# 🗑️ Panduan Uninstall Zoom Integration - EPIC Hub

## 📋 Overview

Panduan ini menjelaskan cara menghapus Zoom Integration dari sistem EPIC Hub secara aman dan menyeluruh. Script uninstall akan menghapus semua komponen terkait Zoom tanpa mempengaruhi fungsionalitas utama sistem.

## ⚠️ Peringatan Penting

- **BACKUP WAJIB**: Pastikan backup database dan file sebelum menjalankan uninstall
- **TIDAK DAPAT DIBATALKAN**: Proses uninstall bersifat permanen
- **DOWNTIME**: Sistem mungkin perlu restart setelah uninstall

## 🎯 Komponen yang Akan Dihapus

### 1. File Core
- `core/zoom-integration.php`
- `install-zoom-integration.php` 
- `zoom-integration-schema.sql`

### 2. Tabel Database
- `epic_zoom_events`
- `epic_zoom_settings`
- `epic_event_registrations`

### 3. Direktori Theme
- `themes/modern/zoom/` (direktori kosong)

### 4. Environment Variables
- `ZOOM_API_KEY`
- `ZOOM_API_SECRET`
- `ZOOM_ACCOUNT_ID`

### 5. Referensi Code
- Include statements di `bootstrap.php`
- Referensi di `core/admin.php`
- Komentar dan dokumentasi terkait

## 🚀 Cara Penggunaan

### Step 1: Upload Script ke Server

Upload file berikut ke root directory website:
- `uninstall-zoom-integration.php`
- `verify-zoom-uninstall.php`

### Step 2: Jalankan Uninstall

**Untuk Localhost:**
```
http://localhost/test-bisnisemasperak/uninstall-zoom-integration.php?uninstall_key=epic_uninstall_zoom_2025
```

**Untuk Server Production:**
```
https://yourdomain.com/uninstall-zoom-integration.php?uninstall_key=epic_uninstall_zoom_2025&confirm=yes
```

### Step 3: Ikuti Proses Uninstall

1. **Review Komponen** - Periksa daftar yang akan dihapus
2. **Konfirmasi** - Klik "Mulai Uninstall" 
3. **Monitor Progress** - Tunggu hingga selesai
4. **Verifikasi** - Periksa hasil uninstall

### Step 4: Verifikasi Hasil

Jalankan script verifikasi:
```
https://yourdomain.com/verify-zoom-uninstall.php?verify_key=epic_verify_uninstall_2025
```

## 📊 Proses Uninstall Detail

### Step 1: Backup Database (Otomatis)
- Backup tabel zoom ke `backup/zoom-integration-backup-YYYY-MM-DD-HH-mm-ss.sql`
- Include struktur dan data tabel
- Backup aman untuk restore jika diperlukan

### Step 2: Drop Database Tables
```sql
DROP TABLE IF EXISTS `epic_zoom_events`;
DROP TABLE IF EXISTS `epic_zoom_settings`;
DROP TABLE IF EXISTS `epic_event_registrations`;
```

### Step 3: Remove Core Files
```bash
rm core/zoom-integration.php
rm install-zoom-integration.php
rm zoom-integration-schema.sql
```

### Step 4: Remove Theme Directories
```bash
rmdir themes/modern/zoom
```

### Step 5: Clean Environment File
Hapus dari `.env`:
```env
# Zoom Integration (Optional)
ZOOM_API_KEY=
ZOOM_API_SECRET=
ZOOM_ACCOUNT_ID=
```

### Step 6: Clean Code References
- Update `bootstrap.php`: Comment out zoom includes
- Update `core/admin.php`: Remove zoom functions
- Add "removed" comments

## 🔍 Verifikasi Uninstall

Script verifikasi akan mengecek:

### ✅ Database Checks
- Memastikan tabel zoom sudah terhapus
- Verifikasi tidak ada foreign key constraints

### ✅ File System Checks  
- Memastikan file core sudah terhapus
- Verifikasi direktori theme sudah dibersihkan

### ✅ Environment Checks
- Memastikan variabel zoom sudah dihapus dari .env
- Verifikasi konfigurasi bersih

### ✅ Code Reference Checks
- Memastikan include statements sudah dibersihkan
- Verifikasi tidak ada referensi aktif

### ✅ Backup Verification
- Memastikan file backup tersedia
- Verifikasi integritas backup

## 📈 Success Rate

- **90-100%**: Excellent - Uninstall sempurna
- **70-89%**: Good - Perlu cleanup manual minimal  
- **<70%**: Needs Attention - Perlu review manual

## 🛠️ Troubleshooting

### Error: "Permission denied"
```bash
# Set permission yang benar
chmod 755 .
chmod 644 *.php
chmod 777 backup/
```

### Error: "Database connection failed"
- Periksa kredensial database di `bootstrap.php`
- Pastikan database server berjalan
- Cek koneksi network

### Error: "File not found"
- File mungkin sudah dihapus sebelumnya
- Lanjutkan proses, ini normal

### Tabel Masih Ada Setelah Uninstall
```sql
-- Manual cleanup
DROP TABLE IF EXISTS `epic_zoom_events`;
DROP TABLE IF EXISTS `epic_zoom_settings`;
DROP TABLE IF EXISTS `epic_event_registrations`;
```

### Referensi Code Masih Ada
Edit manual file berikut:
- `bootstrap.php` - Comment out zoom includes
- `core/admin.php` - Remove zoom functions

## 🔄 Restore (Jika Diperlukan)

Jika perlu restore Zoom Integration:

### 1. Restore Database
```bash
# Import backup file
mysql -u username -p database_name < backup/zoom-integration-backup-YYYY-MM-DD-HH-mm-ss.sql
```

### 2. Restore Files
- Re-upload file `core/zoom-integration.php`
- Re-upload schema files
- Restore environment variables

### 3. Update Code References
- Uncomment includes di `bootstrap.php`
- Restore functions di `core/admin.php`

## 🧹 Cleanup Setelah Uninstall

### 1. Hapus Script Uninstall
```bash
rm uninstall-zoom-integration.php
rm verify-zoom-uninstall.php
rm ZOOM-UNINSTALL-GUIDE.md
```

### 2. Restart Services
```bash
# Restart web server
sudo systemctl restart apache2
# atau
sudo systemctl restart nginx
```

### 3. Clear Cache
```bash
# Clear PHP opcache jika ada
php -r "opcache_reset();"
```

### 4. Test Website
- Test halaman admin
- Test event scheduling (tanpa zoom)
- Test registrasi dan login
- Periksa error log

## 📝 Checklist Post-Uninstall

- [ ] Script uninstall berhasil dijalankan
- [ ] Verifikasi menunjukkan success rate >90%
- [ ] File backup tersimpan dengan aman
- [ ] Script uninstall sudah dihapus
- [ ] Web server sudah direstart
- [ ] Website berfungsi normal
- [ ] Error log bersih dari error zoom
- [ ] Event scheduling masih berfungsi (tanpa zoom)
- [ ] Admin panel dapat diakses
- [ ] Dokumentasi diupdate

## 🆘 Support

Jika mengalami masalah:

1. **Periksa Error Log**
   ```bash
   tail -f /var/log/apache2/error.log
   ```

2. **Jalankan Verifikasi Ulang**
   ```
   https://yourdomain.com/verify-zoom-uninstall.php?verify_key=epic_verify_uninstall_2025
   ```

3. **Manual Cleanup**
   - Hapus file tersisa secara manual
   - Drop tabel database manual
   - Edit file konfigurasi manual

4. **Restore dari Backup**
   - Jika sistem bermasalah, restore dari backup
   - Import database backup
   - Restore file backup

## 📊 Monitoring

Setelah uninstall, monitor:

- **Performance**: Pastikan tidak ada degradasi
- **Error Logs**: Periksa error terkait zoom
- **Functionality**: Test semua fitur utama
- **Database**: Monitor query performance

---

**Dibuat:** <?= date('d F Y') ?>  
**Versi:** 1.0.0  
**Status:** Ready for Production  
**Author:** Bustanu - EPIC Hub Development Team