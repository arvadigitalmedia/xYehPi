# Landing Page Manager - Panduan Lengkap

## Overview
Sistem Landing Page Manager telah dimodifikasi untuk mendukung:
- Manajemen landing page oleh admin
- Tracking pengunjung dan analytics
- Cookie capture untuk referral tracking
- Dashboard member dengan link referral

## Struktur File yang Dimodifikasi

### 1. Admin Files
- `themes/modern/admin/landing-page-manager.php` - Halaman utama admin
- `themes/modern/admin/content/landing-page-manager-content.php` - Daftar landing pages
- `themes/modern/admin/content/landing-page-manager-add-content.php` - Form tambah/edit

### 2. Core Files
- `core/landing-page-tracker.php` - Sistem tracking dan analytics
- `api/track-event.php` - API endpoint untuk tracking events
- `index.php` - Routing utama (ditambahkan landing page routing)

### 3. Landing Page Display
- `themes/modern/landing/landing-page-display.php` - Template display landing page
- `themes/modern/member/landing-pages.php` - Dashboard member

## Database Schema

### Tabel `sa_page`
```sql
CREATE TABLE `sa_page` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_title` varchar(255) NOT NULL,
  `page_slug` varchar(255) NOT NULL UNIQUE,
  `page_description` text,
  `page_url` text NOT NULL,
  `page_method` enum('iframe','inject','redirect') DEFAULT 'iframe',
  `page_image` varchar(255) DEFAULT NULL,
  `page_status` enum('active','inactive') DEFAULT 'active',
  `page_loads` int(11) DEFAULT 0,
  `page_created` datetime DEFAULT CURRENT_TIMESTAMP,
  `page_updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`page_id`),
  KEY `page_slug` (`page_slug`),
  KEY `page_status` (`page_status`)
);
```

### Tabel `sa_page_visits`
```sql
CREATE TABLE `sa_page_visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `sponsor_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `referrer` varchar(500) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`),
  KEY `sponsor_id` (`sponsor_id`),
  KEY `created_at` (`created_at`)
);
```

### Tabel `sa_page_events`
```sql
CREATE TABLE `sa_page_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `sponsor_id` int(11) DEFAULT NULL,
  `event_type` varchar(50) NOT NULL,
  `event_data` text,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `session_id` varchar(255) DEFAULT NULL,
  `time_spent` int(11) DEFAULT NULL,
  `event_timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`),
  KEY `sponsor_id` (`sponsor_id`),
  KEY `event_type` (`event_type`)
);
```

## Cara Penggunaan

### 1. Admin - Menambah Landing Page
1. Login sebagai admin
2. Akses: `http://localhost/test-bisnisemasperak/admin/manage/landing-page-manager`
3. Klik "Tambah Landing Page"
4. Isi form:
   - **Judul**: Nama landing page
   - **Slug URL**: URL unik (auto-generate dari judul)
   - **Deskripsi**: Deskripsi singkat
   - **URL Landing Page**: URL halaman yang akan ditampilkan
   - **Metode**: Pilih iframe, inject, atau redirect
   - **Upload Gambar**: Preview image (opsional)
   - **Status**: Active/Inactive

### 2. Member - Menggunakan Landing Page
1. Login sebagai member
2. Akses dashboard member
3. Klik menu "Landing Pages"
4. Copy link referral yang tersedia
5. Share link dengan format: `http://localhost/test-bisnisemasperak/[slug]?ref=[KODE_MEMBER]`

### 3. Tracking & Analytics
- Setiap kunjungan tercatat otomatis
- Cookie referral disimpan untuk tracking sponsor
- Analytics tersedia di admin dashboard
- Member dapat melihat statistik kunjungan mereka

## URL Structure

### Landing Page URLs
```
http://localhost/test-bisnisemasperak/[page_slug]?ref=[REFERRAL_CODE]
```

Contoh:
```
http://localhost/test-bisnisemasperak/silvergram?ref=ADMIN001
http://localhost/test-bisnisemasperak/promo-special?ref=MEMBER123
```

### Admin URLs
```
http://localhost/test-bisnisemasperak/admin/manage/landing-page-manager
http://localhost/test-bisnisemasperak/admin/manage/landing-page-manager/add
http://localhost/test-bisnisemasperak/admin/manage/landing-page-manager/edit/[id]
```

### Member URLs
```
http://localhost/test-bisnisemasperak/member/landing-pages
```

## Fitur Tracking

### 1. Cookie Capture
- Referral code disimpan dalam cookie `sponsor_ref`
- Cookie berlaku 30 hari
- Digunakan untuk tracking konversi

### 2. Event Tracking
- `page_load`: Saat halaman dimuat
- `page_unload`: Saat halaman ditutup
- `sponsor_click`: Saat link sponsor diklik

### 3. Analytics Data
- Total kunjungan per halaman
- Kunjungan unik berdasarkan IP
- Waktu yang dihabiskan di halaman
- Konversi per sponsor/referral

## API Endpoints

### Track Event
```
POST /api/track-event.php
Content-Type: application/json

{
  "event": "page_load",
  "page_id": 1,
  "referral_code": "ADMIN001",
  "data": {
    "timestamp": 1640995200000
  }
}
```

## Security Features

1. **Input Sanitization**: Semua input di-sanitize
2. **SQL Injection Protection**: Menggunakan prepared statements
3. **XSS Protection**: Output di-escape
4. **CSRF Protection**: Token validation pada form
5. **Access Control**: Role-based access untuk admin/member

## Troubleshooting

### 1. Landing Page Tidak Muncul
- Pastikan status landing page "Active"
- Cek slug URL tidak duplikat
- Pastikan routing di `index.php` berfungsi

### 2. Tracking Tidak Berfungsi
- Cek koneksi database
- Pastikan tabel tracking sudah dibuat
- Cek JavaScript console untuk error

### 3. Cookie Tidak Tersimpan
- Pastikan domain dan path cookie benar
- Cek setting browser untuk cookie
- Pastikan HTTPS jika diperlukan

## Testing Checklist

- [ ] Admin dapat menambah landing page
- [ ] Admin dapat mengedit landing page
- [ ] Admin dapat menghapus landing page
- [ ] Landing page dapat diakses dengan URL slug
- [ ] Referral tracking berfungsi dengan parameter ?ref=
- [ ] Cookie sponsor tersimpan
- [ ] Analytics tercatat di database
- [ ] Member dapat melihat landing pages di dashboard
- [ ] Copy link referral berfungsi
- [ ] Statistics ditampilkan dengan benar

## Maintenance

### 1. Backup Database
Backup tabel berikut secara berkala:
- `sa_page`
- `sa_page_visits`
- `sa_page_events`

### 2. Cleanup Data
Jalankan query cleanup untuk data lama:
```sql
DELETE FROM sa_page_events WHERE event_timestamp < DATE_SUB(NOW(), INTERVAL 6 MONTH);
DELETE FROM sa_page_visits WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

### 3. Performance Optimization
- Index pada kolom yang sering di-query
- Partitioning untuk tabel besar
- Caching untuk data yang sering diakses

## Support

Untuk pertanyaan atau masalah, hubungi tim development dengan informasi:
- URL yang bermasalah
- Error message (jika ada)
- Steps to reproduce
- Browser dan versi yang digunakan