-- =====================================================
-- EPIS Account System Implementation
-- Hierarchical Account Management for EPIC Hub
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- =====================================================
-- 1. UPDATE EXISTING USERS TABLE
-- =====================================================

-- Add EPIS status to existing enum and new fields for hierarchy
ALTER TABLE `epic_users` 
MODIFY COLUMN `status` enum('pending','free','epic','epis','suspended','banned') NOT NULL DEFAULT 'pending',
ADD COLUMN `epis_supervisor_id` bigint(20) UNSIGNED NULL AFTER `referral_code`,
ADD COLUMN `hierarchy_level` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Free, 2=EPIC, 3=EPIS' AFTER `epis_supervisor_id`,
ADD COLUMN `can_recruit_epic` boolean NOT NULL DEFAULT FALSE AFTER `hierarchy_level`,
ADD COLUMN `registration_source` enum('public','admin_only','epis_recruit') NOT NULL DEFAULT 'public' AFTER `can_recruit_epic`,
ADD COLUMN `supervisor_locked` boolean NOT NULL DEFAULT FALSE COMMENT 'Prevent supervisor change' AFTER `registration_source`;

-- Add foreign key for EPIS supervisor
ALTER TABLE `epic_users`
ADD CONSTRAINT `epic_users_epis_supervisor_foreign` 
FOREIGN KEY (`epis_supervisor_id`) REFERENCES `epic_users` (`id`) ON DELETE SET NULL;

-- Add indexes for performance
ALTER TABLE `epic_users`
ADD KEY `epic_users_epis_supervisor_id_index` (`epis_supervisor_id`),
ADD KEY `epic_users_hierarchy_level_index` (`hierarchy_level`),
ADD KEY `epic_users_registration_source_index` (`registration_source`);

-- =====================================================
-- 2. EPIS ACCOUNT MANAGEMENT TABLES
-- =====================================================

-- EPIS Account Details and Capabilities
CREATE TABLE `epic_epis_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `epis_code` varchar(20) NOT NULL COMMENT 'Unique EPIS identifier',
  `territory_name` varchar(100) NULL COMMENT 'Territory or region name',
  `territory_description` text NULL,
  `max_epic_recruits` int(11) NOT NULL DEFAULT 0 COMMENT '0 = unlimited',
  `current_epic_count` int(11) NOT NULL DEFAULT 0,
  `recruitment_commission_rate` decimal(5,2) NOT NULL DEFAULT 10.00 COMMENT 'Direct recruitment commission %',
  `indirect_commission_rate` decimal(5,2) NOT NULL DEFAULT 5.00 COMMENT 'Through EPIC referral commission %',
  `can_manage_benefits` boolean NOT NULL DEFAULT TRUE,
  `can_view_epic_analytics` boolean NOT NULL DEFAULT TRUE,
  `status` enum('active','suspended','terminated') NOT NULL DEFAULT 'active',
  `activated_at` timestamp NULL DEFAULT NULL,
  `activated_by` bigint(20) UNSIGNED NULL COMMENT 'Admin who activated',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_epis_accounts_user_id_unique` (`user_id`),
  UNIQUE KEY `epic_epis_accounts_epis_code_unique` (`epis_code`),
  KEY `epic_epis_accounts_status_index` (`status`),
  KEY `epic_epis_accounts_activated_by_index` (`activated_by`),
  CONSTRAINT `epic_epis_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_epis_accounts_activated_by_foreign` FOREIGN KEY (`activated_by`) REFERENCES `epic_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- EPIS-EPIC Relationships (Network Management)
CREATE TABLE `epic_epis_networks` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `epis_id` bigint(20) UNSIGNED NOT NULL,
  `epic_user_id` bigint(20) UNSIGNED NOT NULL,
  `recruitment_type` enum('direct','indirect') NOT NULL COMMENT 'direct=recruited by EPIS, indirect=through EPIC referral',
  `recruited_by_epic_id` bigint(20) UNSIGNED NULL COMMENT 'If indirect, which EPIC recruited them',
  `recruitment_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `commission_rate` decimal(5,2) NOT NULL COMMENT 'Commission rate for this relationship',
  `total_commissions_earned` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','inactive','transferred') NOT NULL DEFAULT 'active',
  `notes` text NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_epis_networks_epic_user_unique` (`epic_user_id`),
  KEY `epic_epis_networks_epis_id_index` (`epis_id`),
  KEY `epic_epis_networks_recruited_by_epic_id_index` (`recruited_by_epic_id`),
  KEY `epic_epis_networks_recruitment_type_index` (`recruitment_type`),
  KEY `epic_epis_networks_status_index` (`status`),
  CONSTRAINT `epic_epis_networks_epis_id_foreign` FOREIGN KEY (`epis_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_epis_networks_epic_user_id_foreign` FOREIGN KEY (`epic_user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_epis_networks_recruited_by_epic_id_foreign` FOREIGN KEY (`recruited_by_epic_id`) REFERENCES `epic_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- EPIS Benefits Management for EPIC Accounts
CREATE TABLE `epic_epis_benefits` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `epis_id` bigint(20) UNSIGNED NOT NULL,
  `benefit_type` enum('commission_bonus','product_access','training','support','custom') NOT NULL,
  `benefit_name` varchar(200) NOT NULL,
  `benefit_description` text NULL,
  `benefit_value` json NULL COMMENT 'Flexible benefit configuration',
  `target_type` enum('all_network','specific_epic','epic_level') NOT NULL DEFAULT 'all_network',
  `target_epic_ids` json NULL COMMENT 'Specific EPIC user IDs if target_type = specific_epic',
  `eligibility_criteria` json NULL COMMENT 'Criteria for benefit eligibility',
  `is_active` boolean NOT NULL DEFAULT TRUE,
  `start_date` date NULL,
  `end_date` date NULL,
  `usage_limit` int(11) NULL COMMENT 'Max times benefit can be used',
  `usage_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epic_epis_benefits_epis_id_index` (`epis_id`),
  KEY `epic_epis_benefits_benefit_type_index` (`benefit_type`),
  KEY `epic_epis_benefits_target_type_index` (`target_type`),
  KEY `epic_epis_benefits_is_active_index` (`is_active`),
  CONSTRAINT `epic_epis_benefits_epis_id_foreign` FOREIGN KEY (`epis_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- EPIS Benefit Usage Tracking
CREATE TABLE `epic_epis_benefit_usage` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `benefit_id` bigint(20) UNSIGNED NOT NULL,
  `epic_user_id` bigint(20) UNSIGNED NOT NULL,
  `usage_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usage_value` decimal(15,2) NULL COMMENT 'Value of benefit used',
  `usage_details` json NULL COMMENT 'Additional usage information',
  `status` enum('pending','approved','rejected','expired') NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epic_epis_benefit_usage_benefit_id_index` (`benefit_id`),
  KEY `epic_epis_benefit_usage_epic_user_id_index` (`epic_user_id`),
  KEY `epic_epis_benefit_usage_status_index` (`status`),
  KEY `epic_epis_benefit_usage_usage_date_index` (`usage_date`),
  CONSTRAINT `epic_epis_benefit_usage_benefit_id_foreign` FOREIGN KEY (`benefit_id`) REFERENCES `epic_epis_benefits` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_epis_benefit_usage_epic_user_id_foreign` FOREIGN KEY (`epic_user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_epis_benefit_usage_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `epic_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. COMMISSION SYSTEM ENHANCEMENT
-- =====================================================

-- Enhanced Commission Rules for Hierarchical System
CREATE TABLE `epic_commission_rules` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rule_name` varchar(100) NOT NULL,
  `rule_description` text NULL,
  `source_level` enum('free','epic','epis') NOT NULL COMMENT 'Who generates the commission',
  `target_level` enum('epic','epis') NOT NULL COMMENT 'Who receives the commission',
  `commission_type` enum('registration','sale','recurring','bonus') NOT NULL,
  `calculation_method` enum('percentage','fixed','tiered') NOT NULL DEFAULT 'percentage',
  `commission_value` decimal(10,2) NOT NULL,
  `max_commission` decimal(15,2) NULL COMMENT 'Maximum commission per transaction',
  `min_commission` decimal(15,2) NULL COMMENT 'Minimum commission per transaction',
  `conditions` json NULL COMMENT 'Additional conditions for commission',
  `is_active` boolean NOT NULL DEFAULT TRUE,
  `priority` int(11) NOT NULL DEFAULT 0 COMMENT 'Rule priority for conflicts',
  `effective_from` date NOT NULL,
  `effective_until` date NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epic_commission_rules_source_level_index` (`source_level`),
  KEY `epic_commission_rules_target_level_index` (`target_level`),
  KEY `epic_commission_rules_commission_type_index` (`commission_type`),
  KEY `epic_commission_rules_is_active_index` (`is_active`),
  KEY `epic_commission_rules_effective_from_index` (`effective_from`),
  CONSTRAINT `epic_commission_rules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Commission Distribution Tracking
CREATE TABLE `epic_commission_distributions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint(20) UNSIGNED NOT NULL,
  `rule_id` bigint(20) UNSIGNED NOT NULL,
  `source_user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'User who generated the commission',
  `recipient_user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'User who receives the commission',
  `recipient_level` enum('epic','epis') NOT NULL,
  `commission_amount` decimal(15,2) NOT NULL,
  `commission_percentage` decimal(5,2) NOT NULL,
  `original_amount` decimal(15,2) NOT NULL COMMENT 'Original transaction amount',
  `distribution_type` enum('direct','indirect','override') NOT NULL,
  `calculation_details` json NULL COMMENT 'Detailed calculation breakdown',
  `status` enum('pending','paid','cancelled','disputed') NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `notes` text NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epic_commission_distributions_transaction_id_index` (`transaction_id`),
  KEY `epic_commission_distributions_rule_id_index` (`rule_id`),
  KEY `epic_commission_distributions_source_user_id_index` (`source_user_id`),
  KEY `epic_commission_distributions_recipient_user_id_index` (`recipient_user_id`),
  KEY `epic_commission_distributions_status_index` (`status`),
  CONSTRAINT `epic_commission_distributions_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `epic_transactions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_commission_distributions_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `epic_commission_rules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_commission_distributions_source_user_id_foreign` FOREIGN KEY (`source_user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_commission_distributions_recipient_user_id_foreign` FOREIGN KEY (`recipient_user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. REGISTRATION SYSTEM ENHANCEMENT
-- =====================================================

-- Registration Invitations (for EPIS exclusive recruitment)
CREATE TABLE `epic_registration_invitations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invitation_code` varchar(50) NOT NULL,
  `invited_by` bigint(20) UNSIGNED NOT NULL,
  `invited_by_type` enum('admin','epis') NOT NULL,
  `target_level` enum('epic','epis') NOT NULL,
  `target_email` varchar(100) NULL,
  `target_phone` varchar(20) NULL,
  `invitation_message` text NULL,
  `epis_supervisor_id` bigint(20) UNSIGNED NULL COMMENT 'Pre-assigned EPIS supervisor',
  `max_uses` int(11) NOT NULL DEFAULT 1,
  `used_count` int(11) NOT NULL DEFAULT 0,
  `expires_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','expired','cancelled','completed') NOT NULL DEFAULT 'active',
  `metadata` json NULL COMMENT 'Additional invitation data',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epic_registration_invitations_code_unique` (`invitation_code`),
  KEY `epic_registration_invitations_invited_by_index` (`invited_by`),
  KEY `epic_registration_invitations_target_level_index` (`target_level`),
  KEY `epic_registration_invitations_status_index` (`status`),
  KEY `epic_registration_invitations_expires_at_index` (`expires_at`),
  CONSTRAINT `epic_registration_invitations_invited_by_foreign` FOREIGN KEY (`invited_by`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_registration_invitations_epis_supervisor_foreign` FOREIGN KEY (`epis_supervisor_id`) REFERENCES `epic_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registration Usage Tracking
CREATE TABLE `epic_registration_usage` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invitation_id` bigint(20) UNSIGNED NOT NULL,
  `registered_user_id` bigint(20) UNSIGNED NOT NULL,
  `registration_ip` varchar(45) NOT NULL,
  `registration_user_agent` text NULL,
  `registration_data` json NULL COMMENT 'Registration form data',
  `used_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `epic_registration_usage_invitation_id_index` (`invitation_id`),
  KEY `epic_registration_usage_registered_user_id_index` (`registered_user_id`),
  KEY `epic_registration_usage_used_at_index` (`used_at`),
  CONSTRAINT `epic_registration_usage_invitation_id_foreign` FOREIGN KEY (`invitation_id`) REFERENCES `epic_registration_invitations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epic_registration_usage_registered_user_id_foreign` FOREIGN KEY (`registered_user_id`) REFERENCES `epic_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. SYSTEM SETTINGS FOR EPIS
-- =====================================================

-- Insert default commission rules
INSERT INTO `epic_commission_rules` (`rule_name`, `rule_description`, `source_level`, `target_level`, `commission_type`, `calculation_method`, `commission_value`, `effective_from`, `created_by`) VALUES
('EPIS Direct EPIC Registration', 'Commission for EPIS when directly recruiting EPIC account', 'epic', 'epis', 'registration', 'percentage', 100.00, CURDATE(), 1),
('EPIS Indirect EPIC Registration', 'Commission for EPIS when EPIC recruits through their network', 'epic', 'epis', 'registration', 'percentage', 30.00, CURDATE(), 1),
('EPIC Referral Commission', 'Commission for EPIC when recruiting through EPIS network', 'epic', 'epic', 'registration', 'percentage', 70.00, CURDATE(), 1);

-- Insert EPIS-related settings
INSERT INTO `epic_settings` (`key`, `value`, `type`, `group`, `description`, `is_public`) VALUES
('epis_registration_enabled', '1', 'boolean', 'epis', 'Enable EPIS account registration system', 0),
('epis_max_epic_recruits_default', '0', 'integer', 'epis', 'Default maximum EPIC recruits for new EPIS (0 = unlimited)', 0),
('epis_direct_commission_rate', '100.00', 'string', 'epis', 'Default commission rate for direct EPIC recruitment (%)', 0),
('epis_indirect_commission_rate', '30.00', 'string', 'epis', 'Default commission rate for indirect EPIC recruitment (%)', 0),
('epis_supervisor_lock_enabled', '1', 'boolean', 'epis', 'Prevent EPIC accounts from changing EPIS supervisor', 0),
('epis_benefits_enabled', '1', 'boolean', 'epis', 'Enable EPIS benefits management system', 0);

-- =====================================================
-- 6. VIEWS FOR REPORTING
-- =====================================================

-- EPIS Network Overview
CREATE VIEW `epic_epis_network_stats` AS
SELECT 
    e.id as epis_user_id,
    e.name as epis_name,
    e.email as epis_email,
    ea.epis_code,
    ea.territory_name,
    ea.current_epic_count,
    ea.max_epic_recruits,
    COUNT(en.epic_user_id) as total_network_size,
    COUNT(CASE WHEN en.recruitment_type = 'direct' THEN 1 END) as direct_recruits,
    COUNT(CASE WHEN en.recruitment_type = 'indirect' THEN 1 END) as indirect_recruits,
    SUM(en.total_commissions_earned) as total_commissions,
    ea.status as epis_status,
    ea.activated_at
FROM epic_users e
JOIN epic_epis_accounts ea ON e.id = ea.user_id
LEFT JOIN epic_epis_networks en ON e.id = en.epis_id AND en.status = 'active'
WHERE e.status = 'epis'
GROUP BY e.id, ea.id;

-- Hierarchical User Structure
CREATE VIEW `epic_user_hierarchy` AS
SELECT 
    u.id,
    u.name,
    u.email,
    u.status,
    u.hierarchy_level,
    u.epis_supervisor_id,
    supervisor.name as supervisor_name,
    supervisor.email as supervisor_email,
    u.created_at,
    CASE 
        WHEN u.hierarchy_level = 1 THEN 'Free Account'
        WHEN u.hierarchy_level = 2 THEN 'EPIC Account'
        WHEN u.hierarchy_level = 3 THEN 'EPIS Account'
        ELSE 'Unknown'
    END as level_name
FROM epic_users u
LEFT JOIN epic_users supervisor ON u.epis_supervisor_id = supervisor.id
WHERE u.role = 'user'
ORDER BY u.hierarchy_level DESC, u.created_at ASC;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- =====================================================
-- MIGRATION NOTES
-- =====================================================
/*
1. This schema adds EPIS account functionality while maintaining backward compatibility
2. Existing EPIC accounts will need to be assigned EPIS supervisors
3. Commission rules can be configured through admin interface
4. Registration invitations provide controlled access to EPIS recruitment
5. Benefits system allows EPIS accounts to provide additional value to their network
6. All changes are tracked for audit purposes
7. Views provide easy reporting and analytics

Next Steps:
1. Run this migration on the database
2. Update application code to handle new hierarchy
3. Create admin interfaces for EPIS management
4. Implement registration flow with EPIS supervisor selection
5. Build commission calculation engine
6. Create EPIS dashboard and network management tools
*/