-- =====================================================
-- EPIC Hub Event Scheduling History Migration
-- Menambahkan sistem riwayat untuk event scheduling management
-- Version: 1.0
-- Date: 2025-01-14
-- =====================================================

-- Disable foreign key checks untuk migration
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- =====================================================
-- 1. TABEL RIWAYAT EVENT SCHEDULING
-- =====================================================

-- Tabel untuk menyimpan riwayat perubahan event scheduling
CREATE TABLE `epi_event_schedule_history` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID event yang diubah',
  `action_type` enum('created','updated','deleted','status_changed','published','cancelled') NOT NULL COMMENT 'Jenis aksi yang dilakukan',
  `old_data` JSON NULL COMMENT 'Data sebelum perubahan (untuk update/delete)',
  `new_data` JSON NULL COMMENT 'Data setelah perubahan (untuk create/update)',
  `changed_fields` JSON NULL COMMENT 'Field yang berubah: ["title", "start_time", "status"]',
  `changed_by` bigint(20) UNSIGNED NOT NULL COMMENT 'ID user yang melakukan perubahan',
  `changed_by_name` varchar(100) NOT NULL COMMENT 'Nama user yang melakukan perubahan',
  `changed_by_role` varchar(50) NOT NULL COMMENT 'Role user yang melakukan perubahan',
  `ip_address` varchar(45) NULL COMMENT 'IP address user',
  `user_agent` text NULL COMMENT 'User agent browser',
  `reason` text NULL COMMENT 'Alasan perubahan (opsional)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epi_event_schedule_history_event_id_index` (`event_id`),
  KEY `epi_event_schedule_history_action_type_index` (`action_type`),
  KEY `epi_event_schedule_history_changed_by_index` (`changed_by`),
  KEY `epi_event_schedule_history_created_at_index` (`created_at`),
  CONSTRAINT `epi_event_schedule_history_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `epi_event_schedules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Riwayat perubahan event scheduling';

-- =====================================================
-- 2. TABEL RIWAYAT REGISTRASI EVENT
-- =====================================================

-- Tabel untuk menyimpan riwayat perubahan registrasi event
CREATE TABLE `epi_event_schedule_registration_history` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `registration_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID registrasi yang diubah',
  `event_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID event terkait',
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID user yang registrasi',
  `action_type` enum('registered','cancelled','attended','no_show','status_changed') NOT NULL COMMENT 'Jenis aksi registrasi',
  `old_status` varchar(50) NULL COMMENT 'Status sebelum perubahan',
  `new_status` varchar(50) NULL COMMENT 'Status setelah perubahan',
  `registration_data` JSON NULL COMMENT 'Data registrasi lengkap',
  `changed_by` bigint(20) UNSIGNED NULL COMMENT 'ID user yang melakukan perubahan (NULL jika user sendiri)',
  `changed_by_name` varchar(100) NULL COMMENT 'Nama user yang melakukan perubahan',
  `ip_address` varchar(45) NULL COMMENT 'IP address saat registrasi/perubahan',
  `user_agent` text NULL COMMENT 'User agent browser',
  `notes` text NULL COMMENT 'Catatan tambahan',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epi_event_schedule_registration_history_registration_id_index` (`registration_id`),
  KEY `epi_event_schedule_registration_history_event_id_index` (`event_id`),
  KEY `epi_event_schedule_registration_history_user_id_index` (`user_id`),
  KEY `epi_event_schedule_registration_history_action_type_index` (`action_type`),
  KEY `epi_event_schedule_registration_history_created_at_index` (`created_at`),
  CONSTRAINT `epi_event_schedule_registration_history_registration_id_foreign` FOREIGN KEY (`registration_id`) REFERENCES `epi_event_schedule_registrations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epi_event_schedule_registration_history_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `epi_event_schedules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Riwayat perubahan registrasi event';

-- =====================================================
-- 3. TRIGGER UNTUK AUTO-INSERT HISTORY
-- =====================================================

-- Trigger untuk event scheduling - INSERT
DELIMITER //
CREATE TRIGGER `epi_event_schedule_history_after_insert` 
AFTER INSERT ON `epi_event_schedules`
FOR EACH ROW
BEGIN
    DECLARE user_name VARCHAR(100) DEFAULT 'System';
    DECLARE user_role VARCHAR(50) DEFAULT 'system';
    
    -- Get user info
    SELECT name, role INTO user_name, user_role 
    FROM epic_users WHERE id = NEW.created_by LIMIT 1;
    
    INSERT INTO epi_event_schedule_history (
        event_id, action_type, new_data, changed_by, changed_by_name, changed_by_role, created_at
    ) VALUES (
        NEW.id,
        'created',
        JSON_OBJECT(
            'title', NEW.title,
            'description', NEW.description,
            'location', NEW.location,
            'start_time', NEW.start_time,
            'end_time', NEW.end_time,
            'status', NEW.status,
            'access_levels', NEW.access_levels,
            'max_participants', NEW.max_participants,
            'registration_required', NEW.registration_required
        ),
        NEW.created_by,
        user_name,
        user_role,
        NOW()
    );
END//

-- Trigger untuk event scheduling - UPDATE
CREATE TRIGGER `epi_event_schedule_history_after_update` 
AFTER UPDATE ON `epi_event_schedules`
FOR EACH ROW
BEGIN
    DECLARE user_name VARCHAR(100) DEFAULT 'System';
    DECLARE user_role VARCHAR(50) DEFAULT 'system';
    DECLARE changed_fields_json JSON DEFAULT JSON_ARRAY();
    
    -- Get user info (menggunakan created_by karena tidak ada updated_by)
    SELECT name, role INTO user_name, user_role 
    FROM epic_users WHERE id = NEW.created_by LIMIT 1;
    
    -- Build changed fields array
    IF OLD.title != NEW.title THEN
        SET changed_fields_json = JSON_ARRAY_APPEND(changed_fields_json, '$', 'title');
    END IF;
    IF OLD.description != NEW.description THEN
        SET changed_fields_json = JSON_ARRAY_APPEND(changed_fields_json, '$', 'description');
    END IF;
    IF OLD.location != NEW.location THEN
        SET changed_fields_json = JSON_ARRAY_APPEND(changed_fields_json, '$', 'location');
    END IF;
    IF OLD.start_time != NEW.start_time THEN
        SET changed_fields_json = JSON_ARRAY_APPEND(changed_fields_json, '$', 'start_time');
    END IF;
    IF OLD.end_time != NEW.end_time THEN
        SET changed_fields_json = JSON_ARRAY_APPEND(changed_fields_json, '$', 'end_time');
    END IF;
    IF OLD.status != NEW.status THEN
        SET changed_fields_json = JSON_ARRAY_APPEND(changed_fields_json, '$', 'status');
    END IF;
    IF OLD.max_participants != NEW.max_participants THEN
        SET changed_fields_json = JSON_ARRAY_APPEND(changed_fields_json, '$', 'max_participants');
    END IF;
    
    -- Determine action type
    SET @action_type = CASE 
        WHEN OLD.status != NEW.status THEN 'status_changed'
        ELSE 'updated'
    END;
    
    INSERT INTO epi_event_schedule_history (
        event_id, action_type, old_data, new_data, changed_fields, 
        changed_by, changed_by_name, changed_by_role, created_at
    ) VALUES (
        NEW.id,
        @action_type,
        JSON_OBJECT(
            'title', OLD.title,
            'description', OLD.description,
            'location', OLD.location,
            'start_time', OLD.start_time,
            'end_time', OLD.end_time,
            'status', OLD.status,
            'access_levels', OLD.access_levels,
            'max_participants', OLD.max_participants,
            'registration_required', OLD.registration_required
        ),
        JSON_OBJECT(
            'title', NEW.title,
            'description', NEW.description,
            'location', NEW.location,
            'start_time', NEW.start_time,
            'end_time', NEW.end_time,
            'status', NEW.status,
            'access_levels', NEW.access_levels,
            'max_participants', NEW.max_participants,
            'registration_required', NEW.registration_required
        ),
        changed_fields_json,
        NEW.created_by,
        user_name,
        user_role,
        NOW()
    );
END//

-- Trigger untuk registrasi event - INSERT
CREATE TRIGGER `epi_event_schedule_registration_history_after_insert` 
AFTER INSERT ON `epi_event_schedule_registrations`
FOR EACH ROW
BEGIN
    DECLARE user_name VARCHAR(100) DEFAULT 'Unknown';
    
    -- Get user info
    SELECT name INTO user_name 
    FROM epic_users WHERE id = NEW.user_id LIMIT 1;
    
    INSERT INTO epi_event_schedule_registration_history (
        registration_id, event_id, user_id, action_type, new_status, 
        registration_data, changed_by, changed_by_name, created_at
    ) VALUES (
        NEW.id,
        NEW.event_id,
        NEW.user_id,
        'registered',
        NEW.status,
        JSON_OBJECT(
            'registration_date', NEW.registration_date,
            'status', NEW.status,
            'notes', NEW.notes
        ),
        NEW.user_id,
        user_name,
        NOW()
    );
END//

-- Trigger untuk registrasi event - UPDATE
CREATE TRIGGER `epi_event_schedule_registration_history_after_update` 
AFTER UPDATE ON `epi_event_schedule_registrations`
FOR EACH ROW
BEGIN
    DECLARE user_name VARCHAR(100) DEFAULT 'System';
    
    -- Get user info
    SELECT name INTO user_name 
    FROM epic_users WHERE id = NEW.user_id LIMIT 1;
    
    INSERT INTO epi_event_schedule_registration_history (
        registration_id, event_id, user_id, action_type, old_status, new_status,
        registration_data, changed_by, changed_by_name, created_at
    ) VALUES (
        NEW.id,
        NEW.event_id,
        NEW.user_id,
        'status_changed',
        OLD.status,
        NEW.status,
        JSON_OBJECT(
            'registration_date', NEW.registration_date,
            'status', NEW.status,
            'notes', NEW.notes,
            'old_status', OLD.status
        ),
        NEW.user_id,
        user_name,
        NOW()
    );
END//
DELIMITER ;

-- =====================================================
-- 4. STORED PROCEDURES UNTUK QUERY HISTORY
-- =====================================================

-- Stored procedure untuk mendapatkan riwayat event
DELIMITER //
CREATE PROCEDURE `GetEventScheduleHistory`(
    IN p_event_id BIGINT,
    IN p_limit INT DEFAULT 50,
    IN p_offset INT DEFAULT 0
)
BEGIN
    SELECT 
        h.*,
        e.title as event_title,
        c.name as category_name
    FROM epi_event_schedule_history h
    LEFT JOIN epi_event_schedules e ON h.event_id = e.id
    LEFT JOIN epi_event_schedule_categories c ON e.category_id = c.id
    WHERE h.event_id = p_event_id
    ORDER BY h.created_at DESC
    LIMIT p_limit OFFSET p_offset;
END//

-- Stored procedure untuk mendapatkan riwayat registrasi
CREATE PROCEDURE `GetRegistrationHistory`(
    IN p_event_id BIGINT DEFAULT NULL,
    IN p_user_id BIGINT DEFAULT NULL,
    IN p_limit INT DEFAULT 50,
    IN p_offset INT DEFAULT 0
)
BEGIN
    SELECT 
        rh.*,
        e.title as event_title,
        u.name as user_name,
        u.email as user_email
    FROM epi_event_schedule_registration_history rh
    LEFT JOIN epi_event_schedules e ON rh.event_id = e.id
    LEFT JOIN epic_users u ON rh.user_id = u.id
    WHERE 
        (p_event_id IS NULL OR rh.event_id = p_event_id) AND
        (p_user_id IS NULL OR rh.user_id = p_user_id)
    ORDER BY rh.created_at DESC
    LIMIT p_limit OFFSET p_offset;
END//

-- Stored procedure untuk statistik riwayat
CREATE PROCEDURE `GetEventHistoryStats`(
    IN p_start_date DATE DEFAULT NULL,
    IN p_end_date DATE DEFAULT NULL
)
BEGIN
    SELECT 
        action_type,
        COUNT(*) as total_actions,
        DATE(created_at) as action_date
    FROM epi_event_schedule_history
    WHERE 
        (p_start_date IS NULL OR DATE(created_at) >= p_start_date) AND
        (p_end_date IS NULL OR DATE(created_at) <= p_end_date)
    GROUP BY action_type, DATE(created_at)
    ORDER BY action_date DESC, action_type;
END//
DELIMITER ;

-- =====================================================
-- 5. VIEWS UNTUK KEMUDAHAN QUERY
-- =====================================================

-- View untuk riwayat event dengan detail lengkap
CREATE VIEW `epi_event_schedule_history_detailed` AS
SELECT 
    h.*,
    e.title as event_title,
    e.start_time as event_start_time,
    e.status as current_status,
    c.name as category_name,
    c.color as category_color,
    u.name as changed_by_user_name,
    u.email as changed_by_user_email
FROM epi_event_schedule_history h
LEFT JOIN epi_event_schedules e ON h.event_id = e.id
LEFT JOIN epi_event_schedule_categories c ON e.category_id = c.id
LEFT JOIN epic_users u ON h.changed_by = u.id;

-- View untuk riwayat registrasi dengan detail lengkap
CREATE VIEW `epi_event_schedule_registration_history_detailed` AS
SELECT 
    rh.*,
    e.title as event_title,
    e.start_time as event_start_time,
    u.name as user_name,
    u.email as user_email,
    cu.name as changed_by_user_name,
    cu.email as changed_by_user_email
FROM epi_event_schedule_registration_history rh
LEFT JOIN epi_event_schedules e ON rh.event_id = e.id
LEFT JOIN epic_users u ON rh.user_id = u.id
LEFT JOIN epic_users cu ON rh.changed_by = cu.id;

-- =====================================================
-- 6. INDEXES UNTUK OPTIMASI PERFORMA
-- =====================================================

-- Composite indexes untuk query yang sering digunakan
CREATE INDEX `epi_event_schedule_history_event_action_date` ON `epi_event_schedule_history` (`event_id`, `action_type`, `created_at`);
CREATE INDEX `epi_event_schedule_history_user_date` ON `epi_event_schedule_history` (`changed_by`, `created_at`);
CREATE INDEX `epi_event_schedule_registration_history_user_event_date` ON `epi_event_schedule_registration_history` (`user_id`, `event_id`, `created_at`);

-- =====================================================
-- 7. SAMPLE DATA UNTUK TESTING
-- =====================================================

-- Insert sample history data (opsional, untuk testing)
-- INSERT INTO epi_event_schedule_history (event_id, action_type, new_data, changed_by, changed_by_name, changed_by_role) 
-- SELECT id, 'created', JSON_OBJECT('title', title, 'status', status), created_by, 'Admin', 'super_admin' 
-- FROM epi_event_schedules LIMIT 5;

-- =====================================================
-- 8. CLEANUP & MAINTENANCE
-- =====================================================

-- Event untuk cleanup history lama (opsional)
-- CREATE EVENT IF NOT EXISTS `cleanup_old_event_history`
-- ON SCHEDULE EVERY 1 MONTH
-- DO
--   DELETE FROM epi_event_schedule_history 
--   WHERE created_at < DATE_SUB(NOW(), INTERVAL 12 MONTH);

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- MIGRATION COMPLETED
-- =====================================================

-- Tampilkan informasi migration
SELECT 'Event Scheduling History Migration Completed Successfully!' as status,
       NOW() as completed_at,
       'Tables: epi_event_schedule_history, epi_event_schedule_registration_history' as tables_created,
       'Triggers: 4 triggers created for auto-history' as triggers_created,
       'Procedures: 3 stored procedures created' as procedures_created,
       'Views: 2 views created for detailed queries' as views_created;