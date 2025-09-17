<?php
/**
 * EPIS Account Migration Script
 * Run database migration for EPIS Account system
 */

try {
    // Database connection
    $pdo = new PDO('mysql:host=localhost;dbname=epic_hub', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "Connected to database successfully.\n";
    
    // Disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "Foreign key checks disabled.\n";
    
    $executed = 0;
    $errors = 0;
    
    // 1. Modify users table
    echo "\n=== MODIFYING USERS TABLE ===\n";
    
    try {
        // Add new status 'epis' to enum
        $pdo->exec("ALTER TABLE `epic_users` MODIFY COLUMN `status` enum('pending','free','epic','epis','suspended','banned') NOT NULL DEFAULT 'pending'");
        echo "✅ Updated status enum to include 'epis'\n";
        $executed++;
    } catch (Exception $e) {
        echo "❌ Error updating status enum: " . $e->getMessage() . "\n";
        $errors++;
    }
    
    try {
        $pdo->exec("ALTER TABLE `epic_users` ADD COLUMN `epis_supervisor_id` bigint(20) UNSIGNED NULL AFTER `referral_code`");
        echo "✅ Added epis_supervisor_id column\n";
        $executed++;
    } catch (Exception $e) {
        echo "❌ Error adding epis_supervisor_id: " . $e->getMessage() . "\n";
        $errors++;
    }
    
    try {
        $pdo->exec("ALTER TABLE `epic_users` ADD COLUMN `hierarchy_level` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Free, 2=EPIC, 3=EPIS' AFTER `epis_supervisor_id`");
        echo "✅ Added hierarchy_level column\n";
        $executed++;
    } catch (Exception $e) {
        echo "❌ Error adding hierarchy_level: " . $e->getMessage() . "\n";
        $errors++;
    }
    
    try {
        $pdo->exec("ALTER TABLE `epic_users` ADD COLUMN `can_recruit_epic` boolean NOT NULL DEFAULT FALSE AFTER `hierarchy_level`");
        echo "✅ Added can_recruit_epic column\n";
        $executed++;
    } catch (Exception $e) {
        echo "❌ Error adding can_recruit_epic: " . $e->getMessage() . "\n";
        $errors++;
    }
    
    try {
        $pdo->exec("ALTER TABLE `epic_users` ADD COLUMN `registration_source` enum('public','admin_only','epis_recruit') NOT NULL DEFAULT 'public' AFTER `can_recruit_epic`");
        echo "✅ Added registration_source column\n";
        $executed++;
    } catch (Exception $e) {
        echo "❌ Error adding registration_source: " . $e->getMessage() . "\n";
        $errors++;
    }
    
    try {
        $pdo->exec("ALTER TABLE `epic_users` ADD COLUMN `supervisor_locked` boolean NOT NULL DEFAULT FALSE COMMENT 'Prevent supervisor change' AFTER `registration_source`");
        echo "✅ Added supervisor_locked column\n";
        $executed++;
    } catch (Exception $e) {
        echo "❌ Error adding supervisor_locked: " . $e->getMessage() . "\n";
        $errors++;
    }
    
    // 2. Create EPIS accounts table
    echo "\n=== CREATING EPIS TABLES ===\n";
    
    try {
        $sql = "CREATE TABLE `epic_epis_accounts` (
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
        $executed++;
    } catch (Exception $e) {
        echo "❌ Error creating epic_epis_accounts: " . $e->getMessage() . "\n";
        $errors++;
    }
    
    try {
        $sql = "CREATE TABLE `epic_epis_networks` (
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
        $executed++;
    } catch (Exception $e) {
        echo "❌ Error creating epic_epis_networks: " . $e->getMessage() . "\n";
        $errors++;
    }
    
    try {
        $sql = "CREATE TABLE `epic_commission_rules` (
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
        $executed++;
    } catch (Exception $e) {
        echo "❌ Error creating epic_commission_rules: " . $e->getMessage() . "\n";
        $errors++;
    }
    
    try {
        $sql = "CREATE TABLE `epic_registration_invitations` (
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
        $executed++;
    } catch (Exception $e) {
        echo "❌ Error creating epic_registration_invitations: " . $e->getMessage() . "\n";
        $errors++;
    }
    
    // 3. Insert default settings
    echo "\n=== INSERTING DEFAULT SETTINGS ===\n";
    
    try {
        $settings = [
            ['epis_registration_enabled', '1', 'boolean', 'epis', 'Enable EPIS account registration system', 0],
            ['epis_max_epic_recruits_default', '0', 'integer', 'epis', 'Default maximum EPIC recruits for new EPIS (0 = unlimited)', 0],
            ['epis_direct_commission_rate', '100.00', 'string', 'epis', 'Default commission rate for direct EPIC recruitment (%)', 0],
            ['epis_indirect_commission_rate', '30.00', 'string', 'epis', 'Default commission rate for indirect EPIC recruitment (%)', 0],
            ['epis_supervisor_lock_enabled', '1', 'boolean', 'epis', 'Prevent EPIC accounts from changing EPIS supervisor', 0],
            ['epis_benefits_enabled', '1', 'boolean', 'epis', 'Enable EPIS benefits management system', 0]
        ];
        
        foreach ($settings as $setting) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO `epic_settings` (`key`, `value`, `type`, `group`, `description`, `is_public`) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute($setting);
        }
        echo "✅ Inserted EPIS default settings\n";
        $executed++;
    } catch (Exception $e) {
        echo "❌ Error inserting settings: " . $e->getMessage() . "\n";
        $errors++;
    }
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "\nForeign key checks re-enabled.\n";
    
    echo "\n=== MIGRATION SUMMARY ===\n";
    echo "Successfully executed: {$executed} operations\n";
    echo "Errors encountered: {$errors} operations\n";
    
    if ($errors === 0) {
        echo "\n🎉 EPIS Account schema migration completed successfully!\n";
    } else {
        echo "\n⚠️ Migration completed with some errors. Please review the output above.\n";
    }
    
    // Verification
    echo "\n=== VERIFICATION ===\n";
    $tables_to_check = [
        'epic_epis_accounts',
        'epic_epis_networks', 
        'epic_commission_rules',
        'epic_registration_invitations'
    ];
    
    foreach ($tables_to_check as $table) {
        try {
            $result = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($result->rowCount() > 0) {
                echo "✅ Table {$table} exists\n";
            } else {
                echo "❌ Table {$table} not found\n";
            }
        } catch (Exception $e) {
            echo "❌ Error checking table {$table}: " . $e->getMessage() . "\n";
        }
    }
    
    // Check users table modifications
    try {
        $result = $pdo->query("DESCRIBE epic_users");
        $columns = $result->fetchAll(PDO::FETCH_COLUMN);
        
        $new_columns = ['epis_supervisor_id', 'hierarchy_level', 'can_recruit_epic', 'registration_source', 'supervisor_locked'];
        $found_columns = 0;
        
        foreach ($new_columns as $column) {
            if (in_array($column, $columns)) {
                $found_columns++;
                echo "✅ Column epic_users.{$column} added\n";
            } else {
                echo "❌ Column epic_users.{$column} not found\n";
            }
        }
        
        echo "\nUsers table modification: {$found_columns}/{" . count($new_columns) . "} columns added\n";
        
    } catch (Exception $e) {
        echo "❌ Error checking users table modifications: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

?>