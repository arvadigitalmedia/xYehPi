-- Add sponsor_id column to epic_users table
-- Script untuk menambahkan kolom sponsor_id jika belum ada

-- Cek apakah kolom sponsor_id sudah ada
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'epic_hub' 
  AND TABLE_NAME = 'epic_users' 
  AND COLUMN_NAME = 'sponsor_id';

-- Jika kolom belum ada, jalankan ALTER TABLE berikut:
ALTER TABLE epic_users 
ADD COLUMN sponsor_id INT(11) NULL DEFAULT NULL AFTER epis_supervisor_id,
ADD INDEX idx_sponsor_id (sponsor_id);

-- Verifikasi kolom sudah ditambahkan
DESCRIBE epic_users;

-- Update sample data untuk testing (opsional)
-- UPDATE epic_users SET sponsor_id = 1 WHERE email = 'contact.bustanul@gmail.com';

-- Cek data setelah update
SELECT id, name, email, sponsor_id, epis_supervisor_id, referral_code 
FROM epic_users 
WHERE email = 'contact.bustanul@gmail.com';