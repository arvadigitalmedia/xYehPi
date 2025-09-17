-- EPIC Hub Zoom Integration Database Schema
-- Schema untuk sistem penjadwalan event Zoom dengan level akses

-- Tabel kategori event
CREATE TABLE `epic_event_categories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text NULL,
  `access_levels` JSON NOT NULL COMMENT 'Array level akun yang dapat akses: ["free", "epic", "epis"]',
  `color` varchar(7) DEFAULT '#3B82F6' COMMENT 'Warna hex untuk kategori',
  `icon` varchar(50) DEFAULT 'calendar' COMMENT 'Icon feather untuk kategori',
  `is_active` boolean NOT NULL DEFAULT TRUE,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epic_event_categories_created_by_index` (`created_by`),
  KEY `epic_event_categories_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel event Zoom
CREATE TABLE `epic_zoom_events` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NULL,
  `zoom_meeting_id` varchar(50) NULL COMMENT 'ID meeting Zoom',
  `zoom_join_url` text NULL COMMENT 'URL join Zoom meeting',
  `zoom_password` varchar(50) NULL COMMENT 'Password meeting Zoom',
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `timezone` varchar(50) DEFAULT 'Asia/Jakarta',
  `max_participants` int(11) DEFAULT NULL COMMENT 'Maksimal peserta, NULL = unlimited',
  `current_participants` int(11) DEFAULT 0 COMMENT 'Jumlah peserta saat ini',
  `registration_required` boolean DEFAULT FALSE COMMENT 'Apakah perlu registrasi',
  `registration_deadline` datetime NULL COMMENT 'Deadline registrasi',
  `status` enum('draft','published','ongoing','completed','cancelled') DEFAULT 'draft',
  `reminder_sent` boolean DEFAULT FALSE COMMENT 'Apakah reminder sudah dikirim',
  `recording_url` text NULL COMMENT 'URL recording jika ada',
  `materials` JSON NULL COMMENT 'Materi tambahan (links, files)',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epic_zoom_events_category_id_index` (`category_id`),
  KEY `epic_zoom_events_start_time_index` (`start_time`),
  KEY `epic_zoom_events_status_index` (`status`),
  KEY `epic_zoom_events_created_by_index` (`created_by`),
  CONSTRAINT `epic_zoom_events_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `epic_event_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel registrasi event (jika diperlukan)
CREATE TABLE `epic_event_registrations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `registration_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `attendance_status` enum('registered','attended','absent','cancelled') DEFAULT 'registered',
  `notes` text NULL COMMENT 'Catatan tambahan',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_event_registrations_event_user_unique` (`event_id`, `user_id`),
  KEY `epic_event_registrations_user_id_index` (`user_id`),
  CONSTRAINT `epic_event_registrations_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `epic_zoom_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_event_registrations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert kategori default
INSERT INTO `epic_event_categories` (`name`, `description`, `access_levels`, `color`, `icon`, `created_by`) VALUES
('EPI Insight', 'Event pembinaan via Zoom khusus untuk EPIC Account dan EPIS Account', '["epic", "epis"]', '#10B981', 'users', 1),
('EPI Connect', 'Event pembinaan via Zoom khusus untuk EPIS Account', '["epis"]', '#F59E0B', 'user-check', 1),
('Webinar EPI', 'Event edukasi untuk Free Account dan semua level lainnya', '["free", "epic", "epis"]', '#3B82F6', 'video', 1);

-- Insert contoh event
INSERT INTO `epic_zoom_events` (`category_id`, `title`, `description`, `start_time`, `end_time`, `status`, `created_by`) VALUES
(1, 'EPI Insight: Strategi Marketing Digital 2025', 'Pembahasan mendalam tentang strategi marketing digital terbaru untuk meningkatkan konversi dan ROI.', '2025-01-20 19:00:00', '2025-01-20 21:00:00', 'published', 1),
(2, 'EPI Connect: Leadership untuk EPIS', 'Sesi khusus pengembangan kepemimpinan untuk para EPIS supervisor dalam mengelola tim.', '2025-01-22 20:00:00', '2025-01-22 22:00:00', 'published', 1),
(3, 'Webinar EPI: Pengenalan Bisnis Online', 'Webinar gratis untuk pemula yang ingin memulai bisnis online dengan strategi yang tepat.', '2025-01-25 19:30:00', '2025-01-25 21:00:00', 'published', 1);

-- Indexes untuk performa
CREATE INDEX `epic_zoom_events_upcoming` ON `epic_zoom_events` (`start_time`, `status`) WHERE `status` IN ('published', 'ongoing');
CREATE INDEX `epic_event_categories_active` ON `epic_event_categories` (`is_active`) WHERE `is_active` = TRUE;

-- View untuk event dengan kategori
CREATE VIEW `epic_events_with_categories` AS
SELECT 
    e.*,
    c.name as category_name,
    c.description as category_description,
    c.access_levels,
    c.color as category_color,
    c.icon as category_icon,
    u.name as creator_name
FROM `epic_zoom_events` e
JOIN `epic_event_categories` c ON e.category_id = c.id
JOIN `users` u ON e.created_by = u.id
WHERE c.is_active = TRUE;

-- Stored procedure untuk mendapatkan event berdasarkan level user
DELIMITER //
CREATE PROCEDURE GetEventsByUserLevel(
    IN user_level VARCHAR(10),
    IN limit_count INT DEFAULT 10,
    IN offset_count INT DEFAULT 0
)
BEGIN
    SELECT 
        e.*,
        c.name as category_name,
        c.color as category_color,
        c.icon as category_icon
    FROM epic_zoom_events e
    JOIN epic_event_categories c ON e.category_id = c.id
    WHERE c.is_active = TRUE 
        AND e.status IN ('published', 'ongoing')
        AND JSON_CONTAINS(c.access_levels, JSON_QUOTE(user_level))
        AND e.start_time >= NOW()
    ORDER BY e.start_time ASC
    LIMIT limit_count OFFSET offset_count;
END //
DELIMITER ;

-- Function untuk check akses user ke event
DELIMITER //
CREATE FUNCTION CanUserAccessEvent(
    event_id BIGINT,
    user_level VARCHAR(10)
) RETURNS BOOLEAN
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE can_access BOOLEAN DEFAULT FALSE;
    
    SELECT 
        JSON_CONTAINS(c.access_levels, JSON_QUOTE(user_level))
    INTO can_access
    FROM epic_zoom_events e
    JOIN epic_event_categories c ON e.category_id = c.id
    WHERE e.id = event_id AND c.is_active = TRUE;
    
    RETURN COALESCE(can_access, FALSE);
END //
DELIMITER ;

-- Trigger untuk update participant count
DELIMITER //
CREATE TRIGGER update_participant_count_after_registration
AFTER INSERT ON epic_event_registrations
FOR EACH ROW
BEGIN
    UPDATE epic_zoom_events 
    SET current_participants = (
        SELECT COUNT(*) 
        FROM epic_event_registrations 
        WHERE event_id = NEW.event_id 
            AND attendance_status IN ('registered', 'attended')
    )
    WHERE id = NEW.event_id;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER update_participant_count_after_update
AFTER UPDATE ON epic_event_registrations
FOR EACH ROW
BEGIN
    UPDATE epic_zoom_events 
    SET current_participants = (
        SELECT COUNT(*) 
        FROM epic_event_registrations 
        WHERE event_id = NEW.event_id 
            AND attendance_status IN ('registered', 'attended')
    )
    WHERE id = NEW.event_id;
END //
DELIMITER ;

-- Rollback script (untuk development)
/*
DROP TRIGGER IF EXISTS update_participant_count_after_update;
DROP TRIGGER IF EXISTS update_participant_count_after_registration;
DROP FUNCTION IF EXISTS CanUserAccessEvent;
DROP PROCEDURE IF EXISTS GetEventsByUserLevel;
DROP VIEW IF EXISTS epic_events_with_categories;
DROP TABLE IF EXISTS epic_event_registrations;
DROP TABLE IF EXISTS epic_zoom_events;
DROP TABLE IF EXISTS epic_event_categories;
*/