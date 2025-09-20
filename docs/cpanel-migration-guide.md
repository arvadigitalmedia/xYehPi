# Panduan Migration Event Scheduling History di cPanel

## Persiapan
1. **Backup Database** terlebih dahulu melalui cPanel > phpMyAdmin
2. **Download file migration**: `event-scheduling-history-migration.sql`
3. **Akses phpMyAdmin** melalui cPanel

## Langkah Eksekusi di cPanel

### 1. Upload & Jalankan Migration
```bash
# Via phpMyAdmin:
1. Login ke phpMyAdmin
2. Pilih database proyek Anda
3. Klik tab "SQL"
4. Copy-paste isi file event-scheduling-history-migration.sql
5. Klik "Go" untuk eksekusi
```

### 2. Verifikasi Hasil
```sql
-- Cek tabel history sudah terbuat
SHOW TABLES LIKE '%history%';

-- Cek struktur tabel
DESCRIBE epi_event_schedule_history;
DESCRIBE epi_event_schedule_registration_history;

-- Cek trigger sudah aktif
SHOW TRIGGERS LIKE 'epi_event_schedules';

-- Cek stored procedure
SHOW PROCEDURE STATUS WHERE Name LIKE '%history%';
```

### 3. Test Functionality
```sql
-- Test insert event baru (akan otomatis masuk history)
INSERT INTO epi_event_schedules (title, description, category_id, start_time, end_time, status) 
VALUES ('Test Event', 'Test Description', 1, '2024-02-01 10:00:00', '2024-02-01 12:00:00', 'active');

-- Cek history tercatat
SELECT * FROM epi_event_schedule_history ORDER BY changed_at DESC LIMIT 5;

-- Test stored procedure
CALL GetEventHistory(NULL, NULL, NULL, 10, 0);
```

## Rollback (Jika Diperlukan)
```sql
-- Hapus stored procedures
DROP PROCEDURE IF EXISTS GetEventHistory;
DROP PROCEDURE IF EXISTS GetRegistrationHistory;

-- Hapus triggers
DROP TRIGGER IF EXISTS epi_event_schedules_history_trigger;
DROP TRIGGER IF EXISTS epi_event_schedule_registrations_history_trigger;

-- Hapus tabel history
DROP TABLE IF EXISTS epi_event_schedule_registration_history;
DROP TABLE IF EXISTS epi_event_schedule_history;
```

## Penggunaan di PHP

### Contoh Query History Event
```php
<?php
// Ambil riwayat event tertentu
$stmt = $pdo->prepare("CALL GetEventHistory(?, NULL, NULL, 20, 0)");
$stmt->execute([$event_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tampilkan riwayat
foreach ($history as $record) {
    echo "Perubahan: {$record['action']} pada {$record['changed_at']}<br>";
    echo "Oleh: {$record['changed_by']}<br>";
    echo "Data: " . json_decode($record['old_data'], true)['title'] . "<br><br>";
}
?>
```

### Contoh Query History Registrasi
```php
<?php
// Ambil riwayat registrasi event
$stmt = $pdo->prepare("CALL GetRegistrationHistory(?, NULL, NULL, 20, 0)");
$stmt->execute([$event_id]);
$reg_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
```

## Monitoring & Maintenance

### Pembersihan Data Lama
```sql
-- Hapus history lebih dari 1 tahun (opsional)
DELETE FROM epi_event_schedule_history 
WHERE changed_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

DELETE FROM epi_event_schedule_registration_history 
WHERE changed_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

### Cek Performa
```sql
-- Cek ukuran tabel history
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.tables 
WHERE table_name LIKE '%history%';
```

## Troubleshooting

### Error: "Table already exists"
```sql
-- Cek apakah tabel sudah ada
SHOW TABLES LIKE '%history%';
-- Jika ada, hapus dulu atau skip bagian CREATE TABLE
```

### Error: "Trigger already exists"
```sql
-- Hapus trigger lama
DROP TRIGGER IF EXISTS epi_event_schedules_history_trigger;
-- Lalu jalankan ulang CREATE TRIGGER
```

### Error: "Procedure already exists"
```sql
-- Hapus procedure lama
DROP PROCEDURE IF EXISTS GetEventHistory;
-- Lalu jalankan ulang CREATE PROCEDURE
```

## Catatan Keamanan
- **Backup database** sebelum migration
- **Test di staging** terlebih dahulu jika memungkinkan
- **Monitor performa** setelah implementasi
- **Atur retention policy** untuk data history

## Support
Jika mengalami masalah:
1. Cek error log di cPanel > Error Logs
2. Verifikasi versi MySQL (minimal 5.7)
3. Pastikan user database memiliki privilege CREATE, ALTER, TRIGGER