<?php
/**
 * Create Missing EPIS Tables
 * Create the EPIS tables that were not created properly
 */

try {
    $pdo = new PDO('mysql:host=localhost;dbname=epic_hub', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "Connected to database successfully.\n";
    
    // Create epic_epis_accounts table
    echo "\n=== CREATING EPIS ACCOUNTS TABLE ===\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS `epic_epis_accounts` (
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
      KEY `epic_epis_accounts_activated_by_index` (`activated_by`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ Created epic_epis_accounts table\n";
    
    // Create epic_epis_networks table
    echo "\n=== CREATING EPIS NETWORKS TABLE ===\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS `epic_epis_networks` (
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
      KEY `epic_epis_networks_status_index` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ Created epic_epis_networks table\n";
    
    // Create epic_commission_rules table
    echo "\n=== CREATING COMMISSION RULES TABLE ===\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS `epic_commission_rules` (
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
      KEY `epic_commission_rules_effective_from_index` (`effective_from`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ Created epic_commission_rules table\n";
    
    // Create epic_registration_invitations table
    echo "\n=== CREATING REGISTRATION INVITATIONS TABLE ===\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS `epic_registration_invitations` (
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
      KEY `epic_registration_invitations_expires_at_index` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ Created epic_registration_invitations table\n";
    
    // Verify tables were created
    echo "\n=== VERIFICATION ===\n";
    $tables = ['epic_epis_accounts', 'epic_epis_networks', 'epic_commission_rules', 'epic_registration_invitations'];
    
    foreach ($tables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '{$table}'");
        if ($result->rowCount() > 0) {
            echo "✅ Table {$table} exists\n";
        } else {
            echo "❌ Table {$table} not found\n";
        }
    }
    
    echo "\n✅ All EPIS tables created successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

?>