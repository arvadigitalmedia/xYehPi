# EPIC Hub - Sistem Referral & Hierarchy Overview

## 📋 Ringkasan Sistem

EPIC Hub menggunakan sistem hierarki 3 level dengan sistem referral dan komisi yang terintegrasi:

1. **Free Account** (Level 1) - Akun gratis dasar
2. **EPIC Account** (Level 2) - Akun premium dengan fitur affiliate
3. **EPIS Account** (Level 3) - Akun supervisor dengan network management

---

## 🏗️ Struktur Database & Status

### Status User (`epic_users.status`)
- `pending` - Akun baru belum diverifikasi
- `free` - Akun gratis aktif
- `epic` - Akun EPIC premium
- `epis` - Akun EPIS supervisor
- `suspended` - Akun ditangguhkan
- `banned` - Akun diblokir

### Role User (`epic_users.role`)
- `user` - Pengguna biasa (semua level)
- `admin` - Administrator
- `super_admin` - Super Administrator

### Hierarchy Level (`epic_users.hierarchy_level`)
- `1` - Free Account
- `2` - EPIC Account  
- `3` - EPIS Account

---

## 👥 Level 1: FREE ACCOUNT

### Capabilities & Permissions
✅ **Yang Bisa Dilakukan:**
- Akses profil dasar (`profile_basic`)
- Membuat landing page sederhana (`landing_page_basic`)
- Support dasar (`support_basic`)
- Akses produk dasar (`basic_products`)
- Kelola order dasar (`basic_orders`)

❌ **Yang Tidak Bisa Dilakukan:**
- Sistem referral
- Komisi/bonus
- Analytics advanced
- Template premium
- Email automation

### Cara Upgrade
- Upgrade ke EPIC melalui pembayaran
- Bisa menggunakan kode referral EPIC/EPIS saat registrasi

---

## 🚀 Level 2: EPIC ACCOUNT

### Capabilities & Permissions
✅ **Yang Bisa Dilakukan:**
- Semua fitur Free Account +
- Sistem referral (`referral_system`)
- Landing pages premium (`landing_pages_premium`)
- Analytics advanced (`analytics_advanced`)
- Tracking komisi (`commission_tracking`)
- Priority support (`priority_support`)
- Custom domain (`custom_domain`)
- Email automation (`email_automation`)
- Conversion tracking (`conversion_tracking`)
- Akses semua template (`template_all`)

### Sistem Referral EPIC
- **Kode Referral:** Setiap EPIC mendapat kode unik
- **Target:** Bisa merekrut Free users menjadi EPIC
- **Komisi Registration:** 70% dari fee upgrade EPIC
- **Komisi Sale:** 10% dari setiap pembelian referral
- **EPIC Upgrade Bonus:** Rp 29.700 per referral yang upgrade

### Struktur Komisi
```
Referral beli Rp 100.000 → EPIC dapat Rp 10.000 (10%)
Referral upgrade ke EPIC → EPIC dapat 70% dari fee + Rp 29.700
```

### Supervisor System
- EPIC bisa memiliki EPIS supervisor
- Jika ada EPIS supervisor, komisi dibagi:
  - EPIC: 70% 
  - EPIS: 30%

---

## 👑 Level 3: EPIS ACCOUNT

### Capabilities & Permissions
✅ **Yang Bisa Dilakukan:**
- Semua fitur EPIC Account +
- Team management (`team_management`)
- Advanced analytics (`advanced_analytics`)
- Commission management (`commission_management`)
- EPIC recruitment (`epic_recruitment`)
- Territory management (`territory_management`)

### Sistem Network EPIS
- **Territory Management:** Kelola wilayah/area tertentu
- **EPIC Recruitment:** Bisa merekrut EPIC langsung atau tidak langsung
- **Network Supervision:** Supervisi network EPIC di bawahnya

### Struktur Komisi EPIS

#### 1. Direct Recruitment (Rekrut EPIC Langsung)
- **Rate:** 100% dari fee registration EPIC
- **Contoh:** EPIC baru bayar Rp 99.000 → EPIS dapat Rp 99.000

#### 2. Indirect Recruitment (EPIC merekrut melalui network)
- **Rate:** 30% dari komisi EPIC
- **Contoh:** EPIC dalam network merekrut → EPIS dapat 30% dari komisi EPIC

#### 3. Network Commission
- Mendapat komisi dari semua aktivitas EPIC dalam network
- Rate bervariasi berdasarkan jenis transaksi

### EPIS Account Features
- **Territory Code:** Kode unik untuk wilayah
- **Max Recruits:** Batas maksimal EPIC yang bisa direkrut (0 = unlimited)
- **Commission Rates:** 
  - Direct: 100%
  - Indirect: 30%
- **Network Analytics:** Laporan lengkap network performance

---

## 🔄 Alur Sistem Referral

### 1. Registrasi dengan Referral
```
User baru → Gunakan kode referral → Auto-assign sponsor → Upgrade → Komisi dibagikan
```

### 2. Cookie System
- Kode referral disimpan dalam cookie (7 hari)
- Auto-load saat kembali ke halaman registrasi
- Prioritas: URL parameter > Cookie > Default

### 3. Commission Distribution
```
Sale Rp 100.000:
├── EPIC (Direct): Rp 10.000 (10%)
└── EPIS (Supervisor): Rp 3.000 (30% dari EPIC)

EPIC Upgrade Rp 99.000:
├── EPIC (Recruiter): Rp 69.300 (70%) + Rp 29.700 (bonus)
└── EPIS (Supervisor): Rp 29.700 (30%)
```

---

## 📊 Database Tables Utama

### Core Tables
- `epic_users` - Data user dan hierarchy
- `epic_user_profiles` - Profil affiliate/sponsor
- `epic_transactions` - Tracking semua transaksi
- `epic_commission_distributions` - Distribusi komisi

### EPIS System Tables
- `epic_epis_accounts` - Data akun EPIS
- `epic_epis_networks` - Network EPIC-EPIS
- `epic_commission_rules` - Aturan komisi
- `epic_registration_invitations` - Undangan registrasi

---

## 🎯 Key Functions

### User Management
- `epic_get_user_access_level()` - Cek level akses user
- `epic_can_access_feature()` - Validasi permission fitur
- `epic_require_access_level()` - Enforce minimum level

### Referral System
- `epic_get_referrer_info()` - Info sponsor/referrer
- `epic_set_referral_cookie()` - Set cookie referral
- `epic_auto_assign_epis_from_referral()` - Auto-assign EPIS

### Commission System
- `epic_calculate_epis_commission()` - Hitung komisi EPIS
- `epic_calculate_sponsor_commission()` - Hitung komisi sponsor
- `epic_add_to_epis_network()` - Tambah ke network EPIS

---

## 🔐 Security & Validation

### Access Control
- Role-based permissions
- Feature-level access control
- Hierarchy-based restrictions

### Referral Validation
- Kode referral unik dan terenkripsi
- Validasi status referrer aktif
- Pencegahan self-referral
- Rate limiting pada API

### Commission Security
- Audit trail semua transaksi
- Validation rules untuk komisi
- Anti-fraud mechanisms

---

## 📈 Upgrade Paths

### Free → EPIC
1. Klik upgrade di dashboard
2. Pilih paket EPIC
3. Pembayaran
4. Auto-upgrade status
5. Aktivasi fitur EPIC

### EPIC → EPIS
1. Aplikasi melalui admin
2. Review kelayakan
3. Setup territory
4. Aktivasi akun EPIS
5. Transfer network (jika ada)

---

## 🎯 Expected User Behavior

### Free Users
- Explore platform dengan fitur terbatas
- Tertarik upgrade setelah lihat benefit EPIC
- Gunakan kode referral untuk bonus

### EPIC Users
- Aktif promosi dengan kode referral
- Focus pada recruitment dan sales
- Monitor komisi dan analytics
- Upgrade ke EPIS jika memenuhi syarat

### EPIS Users
- Manage territory dan network
- Recruit EPIC secara strategis
- Optimize commission distribution
- Provide support ke network

---

## 🚨 Important Notes

1. **Backward Compatibility:** Sistem kompatibel dengan SimpleAff Plus
2. **Migration Safe:** Upgrade tidak mengganggu data existing
3. **Scalable:** Bisa handle growth network yang besar
4. **Audit Ready:** Semua transaksi tercatat dan trackable
5. **Security First:** Multiple layer validation dan encryption

---

*Dokumentasi ini memberikan overview lengkap sistem referral EPIC Hub. Untuk implementasi teknis detail, lihat file-file core functions dan database schema.*