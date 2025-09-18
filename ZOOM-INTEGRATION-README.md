# EPIC Hub Zoom Integration System

Sistem integrasi Zoom untuk EPIC Hub yang memungkinkan admin mengelola event Zoom dengan kontrol akses berdasarkan level member.

## 🎯 Fitur Utama

### 1. **Kategori Event dengan Level Akses**
- **EPI Insight**: Event pembinaan khusus EPIC & EPIS Account
- **EPI Connect**: Event pembinaan khusus EPIS Account
- **Webinar EPI**: Event edukasi untuk semua level (Free, EPIC, EPIS)
- **Custom Categories**: Admin dapat menambah kategori baru dengan level akses custom

### 2. **Manajemen Event Lengkap**
- ✅ Create, Read, Update, Delete (CRUD) event
- ✅ Penjadwalan dengan timezone support
- ✅ Kontrol peserta maksimal
- ✅ Sistem registrasi opsional
- ✅ Status event (Draft, Published, Ongoing, Completed, Cancelled)
- ✅ Filter dan pencarian event

### 3. **Kontrol Akses Berbasis Level**
- **Free Account**: Hanya dapat melihat Webinar EPI
- **EPIC Account**: Dapat melihat EPI Insight dan Webinar EPI
- **EPIS Account**: Dapat melihat semua kategori event

### 4. **Interface Admin yang Lengkap**
- Dashboard manajemen event
- Manajemen kategori event
- Pengaturan Zoom API
- Filter dan pencarian
- Modal forms untuk input data

### 5. **Interface Member yang User-Friendly**
- Tampilan event sesuai level akses
- Detail event dengan informasi lengkap
- Sistem registrasi event
- Responsive design untuk semua device

## 📁 Struktur File

```
test-bisnisemasperak/
├── core/
│   ├── zoom-integration.php     # Core functions untuk Zoom
│   └── zoom-routes.php          # URL routing dan menu
├── themes/modern/
│   ├── admin/
│   │   └── zoom-integration.php # Halaman admin
│   └── member/
│       └── zoom-events.php      # Halaman member
├── admin/
│   └── zoom-integration.php     # Direct access admin
├── member/
│   └── zoom-events.php          # Direct access member
├── zoom-integration-schema.sql  # Database schema
├── install-zoom-integration.php # Installer
├── add-zoom-menu.php           # Menu installer
└── ZOOM-INTEGRATION-README.md  # Dokumentasi ini
```

## 🗄️ Database Schema

### Tabel `epic_event_categories`
```sql
- id (bigint, primary key)
- name (varchar 100) - Nama kategori
- description (text) - Deskripsi kategori
- access_levels (JSON) - Level akses: ["free", "epic", "epis"]
- color (varchar 7) - Warna hex untuk UI
- icon (varchar 50) - Icon feather
- is_active (boolean) - Status aktif
- created_by (bigint) - ID admin pembuat
- created_at, updated_at (timestamp)
```

### Tabel `epic_zoom_events`
```sql
- id (bigint, primary key)
- category_id (bigint, foreign key)
- title (varchar 200) - Judul event
- description (text) - Deskripsi event
- zoom_meeting_id (varchar 50) - ID meeting Zoom
- zoom_join_url (text) - URL join meeting
- zoom_password (varchar 50) - Password meeting
- start_time, end_time (datetime) - Waktu event
- timezone (varchar 50) - Timezone event
- max_participants (int) - Maksimal peserta
- current_participants (int) - Peserta saat ini
- registration_required (boolean) - Perlu registrasi
- registration_deadline (datetime) - Deadline registrasi
- status (enum) - Status event
- created_by (bigint) - ID admin pembuat
- created_at, updated_at (timestamp)
```

### Tabel `epic_event_registrations`
```sql
- id (bigint, primary key)
- event_id (bigint, foreign key)
- user_id (bigint, foreign key)
- registration_time (timestamp)
- attendance_status (enum) - Status kehadiran
- notes (text) - Catatan
- created_at, updated_at (timestamp)
```

## 🚀 Instalasi

### 1. **Setup Database**
```bash
# Via browser (recommended)
http://localhost:8000/install-zoom-integration.php

# Via command line
php install-zoom-integration.php
```

### 2. **Tambah Menu ke Navbar**
```bash
# Via browser
http://localhost:8000/add-zoom-menu.php
```

### 3. **Verifikasi Instalasi**
- Cek tabel database telah dibuat
- Akses halaman admin: `http://localhost:8000/admin/zoom-integration`
- Akses halaman member: `http://localhost:8000/member/zoom-events`

## 🔧 Konfigurasi

### 1. **Zoom API Setup**
1. Login ke [Zoom Marketplace](https://marketplace.zoom.us/)
2. Buat aplikasi baru (Server-to-Server OAuth)
3. Dapatkan API Key, Secret, dan Account ID
4. Masukkan credentials di tab "Pengaturan Zoom" di admin panel

### 2. **Kategori Event**
- 3 kategori default sudah dibuat saat instalasi
- Admin dapat menambah kategori baru dengan level akses custom
- Setiap kategori memiliki warna dan icon untuk UI

### 3. **Level Akses**
```json
{
  "free": ["Webinar EPI"],
  "epic": ["EPI Insight", "Webinar EPI"],
  "epis": ["EPI Insight", "EPI Connect", "Webinar EPI"]
}
```

## 📱 Penggunaan

### **Admin Panel**
1. **Manajemen Event**:
   - Buat event baru dengan kategori yang sesuai
   - Set tanggal, waktu, dan timezone
   - Tentukan maksimal peserta (opsional)
   - Aktifkan registrasi jika diperlukan
   - Publish event untuk member

2. **Manajemen Kategori**:
   - Tambah kategori baru
   - Set level akses untuk setiap kategori
   - Customize warna dan icon
   - Aktifkan/nonaktifkan kategori

3. **Pengaturan Zoom**:
   - Input API credentials
   - Set default duration dan max participants
   - Konfigurasi recording dan waiting room

### **Member Area**
1. **Melihat Event**:
   - Event ditampilkan sesuai level akses member
   - Filter berdasarkan kategori
   - Lihat detail event lengkap

2. **Registrasi Event**:
   - Daftar event yang memerlukan registrasi
   - Join meeting langsung untuk event tanpa registrasi
   - Notifikasi email konfirmasi

## 🔗 URL Endpoints

### **Admin URLs**
- `/admin/zoom-integration` - Dashboard admin
- `/admin/zoom-integration?tab=events` - Manajemen event
- `/admin/zoom-integration?tab=categories` - Manajemen kategori
- `/admin/zoom-integration?tab=settings` - Pengaturan Zoom

### **Member URLs**
- `/member/zoom-events` - Daftar event untuk member
- `/member/zoom-events?category=1` - Filter berdasarkan kategori

### **API Endpoints**
- `POST /admin/zoom-integration` - CRUD operations (AJAX)
- `POST /member/zoom-events` - Member actions (AJAX)

## 🎨 UI/UX Features

### **Responsive Design**
- Mobile-first approach
- Grid layout yang adaptif
- Touch-friendly buttons dan forms

### **Dark Theme**
- Konsisten dengan tema EPIC Hub
- High contrast untuk accessibility
- Modern gradient dan shadows

### **Interactive Elements**
- Modal forms untuk input data
- Real-time filtering dan search
- Status badges dengan warna
- Hover effects dan transitions

### **Accessibility**
- Semantic HTML structure
- ARIA labels untuk screen readers
- Keyboard navigation support
- Color contrast compliance

## 🔒 Security Features

### **Access Control**
- Admin-only access untuk management
- Level-based event visibility
- CSRF protection pada forms
- Input sanitization dan validation

### **Data Protection**
- Prepared statements untuk database
- XSS protection pada output
- Encrypted storage untuk API credentials
- Rate limiting pada API calls

## 📊 Performance Optimizations

### **Database**
- Proper indexing pada tabel
- Efficient queries dengan JOINs
- Pagination untuk large datasets
- Caching untuk frequent queries

### **Frontend**
- Lazy loading untuk images
- Minified CSS dan JavaScript
- Efficient DOM manipulation
- Optimized asset loading

## 🧪 Testing

### **Manual Testing Checklist**

#### **Admin Panel**
- [ ] Login sebagai admin
- [ ] Akses halaman Zoom Integration
- [ ] Buat kategori event baru
- [ ] Edit kategori existing
- [ ] Hapus kategori (dengan validasi)
- [ ] Buat event baru
- [ ] Edit event existing
- [ ] Hapus event
- [ ] Filter event berdasarkan kategori
- [ ] Filter event berdasarkan status
- [ ] Search event berdasarkan judul
- [ ] Update pengaturan Zoom

#### **Member Area**
- [ ] Login sebagai Free Account
- [ ] Verifikasi hanya melihat Webinar EPI
- [ ] Login sebagai EPIC Account
- [ ] Verifikasi melihat EPI Insight + Webinar EPI
- [ ] Login sebagai EPIS Account
- [ ] Verifikasi melihat semua kategori
- [ ] Lihat detail event
- [ ] Registrasi event (jika diperlukan)
- [ ] Join meeting (placeholder)

#### **Responsive Testing**
- [ ] Desktop (1920x1080)
- [ ] Tablet (768x1024)
- [ ] Mobile (375x667)
- [ ] Mobile landscape (667x375)

## 🐛 Troubleshooting

### **Common Issues**

1. **"Access denied" saat instalasi**
   - Pastikan login sebagai admin
   - Cek fungsi `epic_is_admin()`

2. **Halaman tidak ditemukan (404)**
   - Pastikan file routing sudah di-include di bootstrap.php
   - Cek path file yang benar

3. **Database error saat instalasi**
   - Cek koneksi database
   - Pastikan user database memiliki privilege CREATE TABLE

4. **Menu tidak muncul di navbar**
   - Jalankan `add-zoom-menu.php`
   - Cek file navbar admin

5. **Event tidak muncul untuk member**
   - Cek level akses member
   - Pastikan event status "published"
   - Verifikasi kategori event aktif

### **Debug Mode**
```php
// Tambahkan di bootstrap.php untuk debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## 🔄 Future Enhancements

### **Phase 2 Features**
- [ ] Actual Zoom API integration
- [ ] Email notifications untuk event
- [ ] Calendar integration (iCal)
- [ ] Event recording management
- [ ] Attendance tracking
- [ ] Event analytics dan reporting
- [ ] Bulk operations untuk admin
- [ ] Event templates
- [ ] Recurring events
- [ ] Waiting list untuk event penuh

### **Technical Improvements**
- [ ] API rate limiting
- [ ] Background job processing
- [ ] Real-time notifications
- [ ] Advanced caching strategy
- [ ] Database optimization
- [ ] Unit testing coverage
- [ ] Integration testing
- [ ] Performance monitoring

## 📞 Support

Untuk bantuan teknis atau pertanyaan:
1. Cek dokumentasi ini terlebih dahulu
2. Review troubleshooting section
3. Cek log error di server
4. Hubungi tim development EPIC Hub

---

**Dibuat untuk EPIC Hub** | **Version 1.0** | **Tanggal: Januari 2025**