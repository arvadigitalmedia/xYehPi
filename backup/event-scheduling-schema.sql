-- Event Scheduling System Database Schema
-- Sistem penjadwalan event tanpa integrasi Zoom
-- Created: 2025-09-18

-- Tabel kategori event scheduling
CREATE TABLE `epi_event_schedule_categories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'Nama kategori event',
  `description` text NULL COMMENT 'Deskripsi kategori',
  `access_levels` JSON NOT NULL COMMENT 'Level akses: ["free", "epic", "epis"]',
  `color` varchar(7) DEFAULT '#3B82F6' COMMENT 'Warna hex untuk UI',
  `icon` varchar(50) DEFAULT 'calendar' COMMENT 'Icon feather untuk UI',
  `is_active` boolean DEFAULT TRUE COMMENT 'Status aktif kategori',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT 'ID admin pembuat',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epi_event_schedule_categories_created_by_index` (`created_by`),
  KEY `epi_event_schedule_categories_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kategori untuk event scheduling';

-- Tabel event scheduling
CREATE TABLE `epi_event_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID kategori event',
  `title` varchar(200) NOT NULL COMMENT 'Judul event',
  `description` text NULL COMMENT 'Deskripsi event',
  `location` varchar(255) NULL COMMENT 'Lokasi event (online/offline)',
  `start_time` datetime NOT NULL COMMENT 'Waktu mulai event',
  `end_time` datetime NOT NULL COMMENT 'Waktu selesai event',
  `timezone` varchar(50) DEFAULT 'Asia/Jakarta' COMMENT 'Timezone event',
  `max_participants` int(11) DEFAULT NULL COMMENT 'Maksimal peserta, NULL = unlimited',
  `current_participants` int(11) DEFAULT 0 COMMENT 'Jumlah peserta saat ini',
  `registration_required` boolean DEFAULT FALSE COMMENT 'Apakah perlu registrasi',
  `registration_deadline` datetime NULL COMMENT 'Deadline registrasi',
  `access_levels` JSON NOT NULL COMMENT 'Level akses yang bisa melihat: ["free", "epic", "epis"]',
  `status` enum('draft','published','ongoing','completed','cancelled') DEFAULT 'draft' COMMENT 'Status event',
  `reminder_sent` boolean DEFAULT FALSE COMMENT 'Apakah reminder sudah dikirim',
  `event_url` text NULL COMMENT 'URL event jika online',
  `materials` JSON NULL COMMENT 'Materi tambahan (links, files)',
  `notes` text NULL COMMENT 'Catatan tambahan untuk admin',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT 'ID admin pembuat',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epi_event_schedules_category_id_index` (`category_id`),
  KEY `epi_event_schedules_start_time_index` (`start_time`),
  KEY `epi_event_schedules_status_index` (`status`),
  KEY `epi_event_schedules_created_by_index` (`created_by`),
  KEY `epi_event_schedules_access_levels_index` (`access_levels`),
  CONSTRAINT `epi_event_schedules_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `epi_event_schedule_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Event scheduling tanpa integrasi Zoom';

-- Tabel registrasi event scheduling
CREATE TABLE `epi_event_schedule_registrations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID event',
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID user yang registrasi',
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Tanggal registrasi',
  `status` enum('registered','attended','cancelled','no_show') DEFAULT 'registered' COMMENT 'Status registrasi',
  `notes` text NULL COMMENT 'Catatan registrasi',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epi_event_schedule_registrations_event_user_unique` (`event_id`, `user_id`),
  KEY `epi_event_schedule_registrations_user_id_index` (`user_id`),
  KEY `epi_event_schedule_registrations_status_index` (`status`),
  CONSTRAINT `epi_event_schedule_registrations_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `epi_event_schedules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registrasi peserta event scheduling';

-- Insert default categories
INSERT INTO `epi_event_schedule_categories` (`name`, `description`, `access_levels`, `color`, `icon`, `created_by`) VALUES
('Webinar Gratis', 'Webinar gratis untuk semua member', '["free", "epic", "epis"]', '#10B981', 'monitor', 1),
('Training EPIC', 'Training khusus untuk EPIC Account', '["epic", "epis"]', '#3B82F6', 'book-open', 1),
('Coaching EPIS', 'Coaching eksklusif untuk EPIS Account', '["epis"]', '#F59E0B', 'users', 1),
('Workshop', 'Workshop praktis untuk pengembangan skill', '["epic", "epis"]', '#8B5CF6', 'tool', 1),
('Seminar', 'Seminar edukasi dan motivasi', '["free", "epic", "epis"]', '#EF4444', 'mic', 1);

-- Insert sample events
INSERT INTO `epi_event_schedules` (`category_id`, `title`, `description`, `location`, `start_time`, `end_time`, `access_levels`, `registration_required`, `status`, `created_by`) VALUES
(1, 'Webinar: Memulai Bisnis Online', 'Webinar gratis tentang cara memulai bisnis online dari nol hingga sukses', 'Online via Google Meet', DATE_ADD(NOW(), INTERVAL 7 DAY), DATE_ADD(NOW(), INTERVAL 7 DAY) + INTERVAL 2 HOUR, '["free", "epic", "epis"]', 1, 'published', 1),
(2, 'Training: Digital Marketing Strategy', 'Training mendalam tentang strategi digital marketing untuk EPIC members', 'Online via Zoom', DATE_ADD(NOW(), INTERVAL 10 DAY), DATE_ADD(NOW(), INTERVAL 10 DAY) + INTERVAL 3 HOUR, '["epic", "epis"]', 1, 'published', 1),
(3, 'Coaching: Leadership untuk EPIS', 'Coaching eksklusif pengembangan kepemimpinan untuk EPIS supervisors', 'Online via Teams', DATE_ADD(NOW(), INTERVAL 14 DAY), DATE_ADD(NOW(), INTERVAL 14 DAY) + INTERVAL 2 HOUR, '["epis"]', 1, 'published', 1);

-- Indexes untuk optimasi query
CREATE INDEX `epi_event_schedules_datetime_status` ON `epi_event_schedules` (`start_time`, `status`);
CREATE INDEX `epi_event_schedules_access_status` ON `epi_event_schedules` (`status`, `access_levels`(100));
CREATE INDEX `epi_event_schedule_registrations_event_status` ON `epi_event_schedule_registrations` (`event_id`, `status`);

-- Views untuk kemudahan query
CREATE VIEW `epi_event_schedules_with_categories` AS
SELECT 
    e.*,
    c.name as category_name,
    c.color as category_color,
    c.icon as category_icon,
    c.access_levels as category_access_levels,
    (SELECT COUNT(*) FROM epi_event_schedule_registrations r WHERE r.event_id = e.id AND r.status = 'registered') as registered_count
FROM epi_event_schedules e
LEFT JOIN epi_event_schedule_categories c ON e.category_id = c.id;

-- Trigger untuk update current_participants
DELIMITER //
CREATE TRIGGER `update_event_participants_after_registration` 
AFTER INSERT ON `epi_event_schedule_registrations`
FOR EACH ROW
BEGIN
    UPDATE epi_event_schedules 
    SET current_participants = (
        SELECT COUNT(*) 
        FROM epi_event_schedule_registrations 
        WHERE event_id = NEW.event_id AND status = 'registered'
    )
    WHERE id = NEW.event_id;
END//

CREATE TRIGGER `update_event_participants_after_update` 
AFTER UPDATE ON `epi_event_schedule_registrations`
FOR EACH ROW
BEGIN
    UPDATE epi_event_schedules 
    SET current_participants = (
        SELECT COUNT(*) 
        FROM epi_event_schedule_registrations 
        WHERE event_id = NEW.event_id AND status = 'registered'
    )
    WHERE id = NEW.event_id;
END//

CREATE TRIGGER `update_event_participants_after_delete` 
AFTER DELETE ON `epi_event_schedule_registrations`
FOR EACH ROW
BEGIN
    UPDATE epi_event_schedules 
    SET current_participants = (
        SELECT COUNT(*) 
        FROM epi_event_schedule_registrations 
        WHERE event_id = OLD.event_id AND status = 'registered'
    )
    WHERE id = OLD.event_id;
END//
DELIMITER ;

-- Comments untuk dokumentasi
ALTER TABLE `epi_event_schedule_categories` COMMENT = 'Kategori untuk sistem event scheduling tanpa integrasi Zoom';
ALTER TABLE `epi_event_schedules` COMMENT = 'Event scheduling untuk informasi dan penjadwalan tanpa integrasi Zoom';
ALTER TABLE `epi_event_schedule_registrations` COMMENT = 'Registrasi peserta untuk event scheduling';