-- EPIC Hub Mailketing Integration Database Migration
-- Membuat tabel untuk logging dan statistik Mailketing

-- Tabel untuk logging webhook events dari Mailketing
CREATE TABLE IF NOT EXISTS `epi_mailketing_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `event_type` varchar(50) NOT NULL,
    `email` varchar(255) DEFAULT NULL,
    `data` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_event_type` (`event_type`),
    KEY `idx_email` (`email`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel untuk statistik email per user
CREATE TABLE IF NOT EXISTS `epi_email_stats` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `email` varchar(255) NOT NULL,
    `sent_count` int(11) DEFAULT 0,
    `opened_count` int(11) DEFAULT 0,
    `clicked_count` int(11) DEFAULT 0,
    `bounced_count` int(11) DEFAULT 0,
    `last_sent` timestamp NULL DEFAULT NULL,
    `last_opened` timestamp NULL DEFAULT NULL,
    `last_clicked` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_email` (`email`),
    KEY `idx_email` (`email`),
    KEY `idx_last_sent` (`last_sent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel untuk menyimpan konfigurasi Mailketing lists
CREATE TABLE IF NOT EXISTS `epi_mailketing_lists` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `list_id` int(11) NOT NULL,
    `list_name` varchar(255) NOT NULL,
    `subscriber_count` int(11) DEFAULT 0,
    `is_active` tinyint(1) DEFAULT 1,
    `last_sync` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_list_id` (`list_id`),
    KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel untuk tracking email campaigns
CREATE TABLE IF NOT EXISTS `epi_email_campaigns` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `campaign_name` varchar(255) NOT NULL,
    `subject` varchar(255) NOT NULL,
    `template` text,
    `list_id` int(11) DEFAULT NULL,
    `sent_count` int(11) DEFAULT 0,
    `opened_count` int(11) DEFAULT 0,
    `clicked_count` int(11) DEFAULT 0,
    `bounced_count` int(11) DEFAULT 0,
    `status` enum('draft','sending','sent','failed') DEFAULT 'draft',
    `sent_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_status` (`status`),
    KEY `idx_sent_at` (`sent_at`),
    KEY `idx_list_id` (`list_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default email templates jika belum ada
INSERT IGNORE INTO `epic_settings` (`key`, `value`, `type`, `group`, `description`) VALUES
('mailketing_enabled', '1', 'boolean', 'mailketing', 'Aktifkan Mailketing API'),
('mailketing_api_token', '', 'string', 'mailketing', 'API Token dari Mailketing'),
('mailketing_from_name', 'EPIC Hub', 'string', 'mailketing', 'Nama pengirim email'),
('mailketing_from_email', '', 'string', 'mailketing', 'Email pengirim'),
('mailketing_default_list_id', '1', 'integer', 'mailketing', 'Default List ID untuk subscriber baru'),
('welcome_email_enabled', '1', 'boolean', 'email_templates', 'Aktifkan welcome email'),
('welcome_email_subject', 'Selamat Datang di EPIC Hub!', 'string', 'email_templates', 'Subject welcome email'),
('welcome_email_template', 'Halo {name},\n\nTerima kasih telah bergabung dengan EPIC Hub. Silakan klik link berikut untuk konfirmasi email Anda:\n\n{confirmation_link}\n\nSalam,\nTim EPIC Hub', 'string', 'email_templates', 'Template welcome email'),
('order_confirmation_enabled', '1', 'boolean', 'email_templates', 'Aktifkan order confirmation email'),
('order_confirmation_subject', 'Konfirmasi Pesanan - EPIC Hub', 'string', 'email_templates', 'Subject order confirmation email'),
('order_confirmation_template', 'Halo {name},\n\nPesanan Anda telah dikonfirmasi:\n\nID Pesanan: {order_id}\nProduk: {product_name}\nTotal: {amount}\n\nTerima kasih atas kepercayaan Anda.\n\nSalam,\nTim EPIC Hub', 'string', 'email_templates', 'Template order confirmation email'),
('password_reset_enabled', '1', 'boolean', 'email_templates', 'Aktifkan password reset email'),
('password_reset_subject', 'Reset Password - EPIC Hub', 'string', 'email_templates', 'Subject password reset email'),
('password_reset_template', 'Halo {name},\n\nKlik link berikut untuk reset password Anda:\n\n{reset_link}\n\nLink ini akan expire dalam {expire_time}.\n\nJika Anda tidak meminta reset password, abaikan email ini.\n\nSalam,\nTim EPIC Hub', 'string', 'email_templates', 'Template password reset email'),
('mailketing_webhook_url', '', 'string', 'mailketing', 'URL webhook untuk menerima notifikasi dari Mailketing');

-- Create indexes untuk performa yang lebih baik
CREATE INDEX IF NOT EXISTS `idx_epic_users_email_status` ON `epic_users` (`email`, `status`);
CREATE INDEX IF NOT EXISTS `idx_epic_users_role` ON `epic_users` (`role`);

-- Update existing epic_users table untuk menambah kolom mailketing_subscriber_id jika belum ada
ALTER TABLE `epic_users` 
ADD COLUMN IF NOT EXISTS `mailketing_subscriber_id` int(11) DEFAULT NULL AFTER `email`,
ADD INDEX IF NOT EXISTS `idx_mailketing_subscriber_id` (`mailketing_subscriber_id`);

-- Trigger untuk otomatis membuat email stats entry saat user baru
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `tr_epic_users_after_insert` 
AFTER INSERT ON `epic_users` 
FOR EACH ROW 
BEGIN
    IF NEW.email IS NOT NULL AND NEW.email != '' THEN
        INSERT IGNORE INTO `epi_email_stats` (`email`) VALUES (NEW.email);
    END IF;
END$$
DELIMITER ;

-- Trigger untuk update email stats saat email user berubah
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `tr_epic_users_after_update` 
AFTER UPDATE ON `epic_users` 
FOR EACH ROW 
BEGIN
    IF NEW.email IS NOT NULL AND NEW.email != '' AND NEW.email != OLD.email THEN
        INSERT IGNORE INTO `epi_email_stats` (`email`) VALUES (NEW.email);
    END IF;
END$$
DELIMITER ;

-- View untuk dashboard statistics
CREATE OR REPLACE VIEW `v_mailketing_dashboard_stats` AS
SELECT 
    (SELECT COUNT(*) FROM epic_users WHERE status = 'active' AND email IS NOT NULL AND email != '') as total_users,
    (SELECT COUNT(*) FROM epi_mailketing_lists WHERE is_active = 1) as total_lists,
    (SELECT SUM(sent_count) FROM epi_email_stats) as total_emails_sent,
    (SELECT SUM(opened_count) FROM epi_email_stats) as total_emails_opened,
    (SELECT SUM(clicked_count) FROM epi_email_stats) as total_emails_clicked,
    (SELECT SUM(bounced_count) FROM epi_email_stats) as total_emails_bounced,
    (SELECT COUNT(*) FROM epi_mailketing_logs WHERE DATE(created_at) = CURDATE()) as today_events;

-- Stored procedure untuk cleanup old logs (optional)
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `sp_cleanup_mailketing_logs`(IN days_to_keep INT)
BEGIN
    DELETE FROM epi_mailketing_logs 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
    
    SELECT ROW_COUNT() as deleted_rows;
END$$
DELIMITER ;

-- Insert sample data untuk testing (optional)
-- INSERT INTO epi_mailketing_lists (list_id, list_name, subscriber_count) VALUES
-- (1, 'Main List', 0),
-- (2, 'VIP Customers', 0),
-- (3, 'Newsletter Subscribers', 0);

COMMIT;