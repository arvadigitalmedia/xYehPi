-- =====================================================
-- EPIS Supervisor Assignment Migration (SQL Version)
-- Manual migration untuk cPanel tanpa SSH/Terminal
-- =====================================================

-- Step 1: Create default EPIS supervisor if none exists
-- Check if there are any EPIS supervisors
SET @epis_count = (SELECT COUNT(*) FROM users u 
                   JOIN epic_epis_accounts ea ON u.id = ea.user_id 
                   WHERE u.status = 'epis' AND ea.status = 'active');

-- Create default EPIS supervisor if needed
INSERT INTO users (
    name, email, password, referral_code, affiliate_code, 
    status, role, hierarchy_level, can_recruit_epic, 
    registration_source, created_at, updated_at
)
SELECT 
    'EPIS Supervisor Default',
    'epis.default@epichub.local',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'EPIS001',
    'EPIS001',
    'epis',
    'member',
    3,
    1,
    'admin_only',
    NOW(),
    NOW()
WHERE @epis_count = 0;

-- Get the ID of the created EPIS user
SET @default_epis_id = (SELECT id FROM users WHERE email = 'epis.default@epichub.local' LIMIT 1);

-- Create EPIS account for default supervisor if needed
INSERT INTO epic_epis_accounts (
    user_id, epis_code, territory_name, territory_description,
    max_epic_recruits, current_epic_count, recruitment_commission_rate,
    indirect_commission_rate, can_manage_benefits, can_view_epic_analytics,
    status, activated_at, created_at, updated_at
)
SELECT 
    @default_epis_id,
    'EPIS001',
    'Default Territory',
    'Default EPIS territory for member assignment',
    0, -- Unlimited
    0,
    10.00,
    5.00,
    1,
    1,
    'active',
    NOW(),
    NOW(),
    NOW()
WHERE @epis_count = 0 AND @default_epis_id IS NOT NULL;

-- Step 2: Get available EPIS supervisor for assignment
SET @available_epis_id = (
    SELECT u.id 
    FROM users u 
    JOIN epic_epis_accounts ea ON u.id = ea.user_id 
    WHERE u.status = 'epis' AND ea.status = 'active' 
    AND (ea.max_epic_recruits = 0 OR ea.current_epic_count < ea.max_epic_recruits)
    ORDER BY ea.current_epic_count ASC, u.created_at ASC
    LIMIT 1
);

-- Step 3: Assign EPIS supervisor to all members without supervisor
UPDATE users 
SET 
    epis_supervisor_id = @available_epis_id,
    updated_at = NOW()
WHERE 
    (epis_supervisor_id IS NULL OR epis_supervisor_id = 0) 
    AND role != 'super_admin' 
    AND status IN ('free', 'epic', 'pending')
    AND @available_epis_id IS NOT NULL;

-- Step 4: Update EPIS supervisor counts
UPDATE epic_epis_accounts ea
JOIN users u ON ea.user_id = u.id
SET 
    ea.current_epic_count = (
        SELECT COUNT(*) 
        FROM users 
        WHERE epis_supervisor_id = u.id 
        AND status IN ('free', 'epic')
    ),
    ea.updated_at = NOW()
WHERE u.status = 'epis' AND ea.status = 'active';

-- Step 5: Create or update settings for EPIS requirement
-- Check if setting exists
SET @setting_exists = (SELECT COUNT(*) FROM settings WHERE `key` = 'epis_registration_required');

-- Insert setting if not exists
INSERT INTO settings (`key`, `value`, `type`, `group`, `description`)
SELECT 
    'epis_registration_required',
    '1',
    'boolean',
    'registration',
    'Require EPIS supervisor assignment for new member registrations'
WHERE @setting_exists = 0;

-- Update setting if exists
UPDATE settings 
SET `value` = '1', updated_at = NOW()
WHERE `key` = 'epis_registration_required' AND @setting_exists > 0;

-- Step 6: Insert activity logs for assignments
INSERT INTO activity_log (user_id, action, description, created_at)
SELECT 
    u.id,
    'epis_supervisor_assigned',
    CONCAT('Assigned to EPIS supervisor: ', es.name, ' (', ea.epis_code, ') via SQL migration'),
    NOW()
FROM users u
JOIN users es ON u.epis_supervisor_id = es.id
JOIN epic_epis_accounts ea ON es.id = ea.user_id
WHERE u.epis_supervisor_id IS NOT NULL 
AND u.role != 'super_admin'
AND NOT EXISTS (
    SELECT 1 FROM activity_log 
    WHERE user_id = u.id 
    AND action = 'epis_supervisor_assigned' 
    AND description LIKE '%SQL migration%'
);

-- Step 7: Insert activity logs for EPIS supervisors
INSERT INTO activity_log (user_id, action, description, created_at)
SELECT 
    es.id,
    'new_member_assigned',
    CONCAT('New member assigned via SQL migration: ', u.name, ' (', u.email, ')'),
    NOW()
FROM users u
JOIN users es ON u.epis_supervisor_id = es.id
WHERE u.epis_supervisor_id IS NOT NULL 
AND u.role != 'super_admin'
AND NOT EXISTS (
    SELECT 1 FROM activity_log 
    WHERE user_id = es.id 
    AND action = 'new_member_assigned' 
    AND description LIKE CONCAT('%', u.email, '%')
    AND description LIKE '%SQL migration%'
);

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

-- Check total members and assignments
SELECT 
    'Migration Summary' as report_type,
    (
        SELECT COUNT(*) 
        FROM users 
        WHERE role != 'super_admin'
    ) as total_members,
    (
        SELECT COUNT(*) 
        FROM users 
        WHERE epis_supervisor_id IS NOT NULL 
        AND epis_supervisor_id > 0 
        AND role != 'super_admin'
    ) as members_with_supervisor,
    (
        SELECT COUNT(*) 
        FROM users u 
        JOIN epic_epis_accounts ea ON u.id = ea.user_id 
        WHERE u.status = 'epis' AND ea.status = 'active'
    ) as total_epis_supervisors;

-- Check EPIS supervisor loads
SELECT 
    u.name as supervisor_name,
    ea.epis_code,
    ea.territory_name,
    ea.current_epic_count as assigned_members,
    CASE 
        WHEN ea.max_epic_recruits = 0 THEN 'Unlimited'
        ELSE CONCAT(ea.current_epic_count, '/', ea.max_epic_recruits)
    END as capacity
FROM users u
JOIN epic_epis_accounts ea ON u.id = ea.user_id
WHERE u.status = 'epis' AND ea.status = 'active'
ORDER BY ea.current_epic_count DESC;

-- Check members without supervisor (should be 0)
SELECT 
    COUNT(*) as members_without_supervisor
FROM users 
WHERE (epis_supervisor_id IS NULL OR epis_supervisor_id = 0) 
AND role != 'super_admin' 
AND status IN ('free', 'epic', 'pending');

-- =====================================================
-- ROLLBACK SCRIPT (if needed)
-- =====================================================

/*
-- ROLLBACK: Remove all EPIS supervisor assignments
-- WARNING: Only run this if you need to undo the migration

-- Remove supervisor assignments
UPDATE users 
SET epis_supervisor_id = NULL, updated_at = NOW()
WHERE role != 'super_admin';

-- Reset EPIS counts
UPDATE epic_epis_accounts 
SET current_epic_count = 0, updated_at = NOW();

-- Remove setting
DELETE FROM settings WHERE `key` = 'epis_registration_required';

-- Remove activity logs
DELETE FROM activity_log 
WHERE action IN ('epis_supervisor_assigned', 'new_member_assigned') 
AND description LIKE '%SQL migration%';

-- Remove default EPIS supervisor (if created)
DELETE ea FROM epic_epis_accounts ea
JOIN users u ON ea.user_id = u.id
WHERE u.email = 'epis.default@epichub.local';

DELETE FROM users WHERE email = 'epis.default@epichub.local';
*/

-- =====================================================
-- END OF MIGRATION
-- =====================================================

SELECT 'EPIS Supervisor Migration Completed Successfully!' as status;