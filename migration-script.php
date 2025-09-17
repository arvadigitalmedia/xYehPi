<?php
/**
 * EPIC Hub Migration Script
 * Migrates data from SimpleAff Plus (sa_*) to EPIC Hub (epic_*)
 * 
 * Features:
 * - Safe migration with backup
 * - Rollback capability
 * - Progress tracking
 * - Error handling
 * - Data validation
 */

require_once 'config/config.php';
require_once 'core/functions.php';

class EpicMigration {
    private $con;
    private $backup_prefix = 'backup_';
    private $migration_log = [];
    private $start_time;
    
    public function __construct() {
        global $con;
        $this->con = $con;
        $this->start_time = microtime(true);
        $this->log('Migration started at ' . date('Y-m-d H:i:s'));
    }
    
    /**
     * Main migration process
     */
    public function migrate() {
        try {
            $this->log('=== EPIC Hub Migration Process ===');
            
            // Step 1: Backup existing data
            $this->backupExistingData();
            
            // Step 2: Create new EPIC schema
            $this->createEpicSchema();
            
            // Step 3: Migrate data
            $this->migrateUsers();
            $this->migrateReferrals();
            $this->migrateProducts();
            $this->migrateOrders();
            $this->migrateTransactions();
            $this->migrateSettings();
            $this->migrateFormFields();
            $this->migrateCategories();
            $this->migrateArticles();
            $this->migrateAnalytics();
            
            // Step 4: Validate migration
            $this->validateMigration();
            
            // Step 5: Create initial affiliate links
            $this->createInitialAffiliateLinks();
            
            $this->log('Migration completed successfully!');
            $this->printSummary();
            
        } catch (Exception $e) {
            $this->log('ERROR: ' . $e->getMessage());
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Backup existing data
     */
    private function backupExistingData() {
        $this->log('Creating backup of existing data...');
        
        $tables = [
            'sa_member', 'sa_sponsor', 'sa_page', 'sa_order', 
            'sa_laporan', 'sa_setting', 'sa_form', 'sa_kategori', 
            'sa_artikel', 'sa_visitor'
        ];
        
        foreach ($tables as $table) {
            if ($this->tableExists($table)) {
                $backup_table = $this->backup_prefix . $table;
                $this->query("DROP TABLE IF EXISTS `{$backup_table}`");
                $this->query("CREATE TABLE `{$backup_table}` LIKE `{$table}`");
                $this->query("INSERT INTO `{$backup_table}` SELECT * FROM `{$table}`");
                $this->log("Backed up {$table} to {$backup_table}");
            }
        }
    }
    
    /**
     * Create EPIC schema
     */
    private function createEpicSchema() {
        $this->log('Creating EPIC Hub database schema...');
        
        $schema_file = __DIR__ . '/epic-database-schema.sql';
        if (!file_exists($schema_file)) {
            throw new Exception('Schema file not found: ' . $schema_file);
        }
        
        $sql = file_get_contents($schema_file);
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !preg_match('/^(--|#)/', $statement)) {
                try {
                    $this->query($statement);
                } catch (Exception $e) {
                    // Log but continue for non-critical statements
                    $this->log('Warning: ' . $e->getMessage());
                }
            }
        }
        
        $this->log('EPIC schema created successfully');
    }
    
    /**
     * Migrate users from sa_member to epic_users
     */
    private function migrateUsers() {
        $this->log('Migrating users...');
        
        $users = $this->select("SELECT * FROM sa_member ORDER BY mem_id");
        $migrated = 0;
        
        foreach ($users as $user) {
            $uuid = $this->generateUUID();
            $referral_code = $user['mem_kodeaff'] ?: $this->generateReferralCode();
            
            // Map status
            $status_map = [
                '0' => 'pending',
                '1' => 'active', 
                '2' => 'premium'
            ];
            $status = $status_map[$user['mem_status']] ?? 'pending';
            
            // Map role
            $role_map = [
                '1' => 'user',
                '2' => 'staff',
                '9' => 'admin'
            ];
            $role = $role_map[$user['mem_role']] ?? 'user';
            
            $additional_data = null;
            if (!empty($user['mem_datalain'])) {
                $additional_data = json_encode(unserialize($user['mem_datalain']) ?: []);
            }
            
            $sql = "INSERT INTO epic_users (
                        uuid, name, email, password, phone, referral_code, 
                        status, role, last_login_at, email_confirmation_token,
                        additional_data, created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )";
            
            $params = [
                $uuid,
                $user['mem_nama'],
                $user['mem_email'],
                $user['mem_password'],
                $user['mem_whatsapp'],
                $referral_code,
                $status,
                $role,
                $user['mem_lastlogin'],
                $user['mem_confirm'],
                $additional_data,
                $user['mem_tgldaftar'],
                $user['mem_tgldaftar']
            ];
            
            $this->execute($sql, $params);
            
            // Store mapping for later use
            $this->user_mapping[$user['mem_id']] = $this->con->insert_id;
            $migrated++;
        }
        
        $this->log("Migrated {$migrated} users");
    }
    
    /**
     * Migrate referrals from sa_sponsor to epic_referrals
     */
    private function migrateReferrals() {
        $this->log('Migrating referrals...');
        
        $sponsors = $this->select("SELECT * FROM sa_sponsor ORDER BY sp_id");
        $migrated = 0;
        
        foreach ($sponsors as $sponsor) {
            $user_id = $this->user_mapping[$sponsor['sp_mem_id']] ?? null;
            $referrer_id = $this->user_mapping[$sponsor['sp_sponsor_id']] ?? null;
            
            if ($user_id) {
                $sql = "INSERT INTO epic_referrals (
                            user_id, referrer_id, referral_date, status
                        ) VALUES (?, ?, NOW(), 'active')";
                
                $this->execute($sql, [$user_id, $referrer_id]);
                $migrated++;
            }
        }
        
        $this->log("Migrated {$migrated} referrals");
    }
    
    /**
     * Migrate products from sa_page to epic_products
     */
    private function migrateProducts() {
        $this->log('Migrating products...');
        
        $products = $this->select("SELECT * FROM sa_page ORDER BY page_id");
        $migrated = 0;
        
        foreach ($products as $product) {
            $uuid = $this->generateUUID();
            
            // Determine commission
            $commission_type = 'percentage';
            $commission_value = 10.00; // Default
            
            if (!empty($product['pro_komisi'])) {
                $komisi = unserialize($product['pro_komisi']);
                if (is_array($komisi) && isset($komisi['premium'][1])) {
                    $commission_value = floatval($komisi['premium'][1]);
                }
            }
            
            // Map landing page type
            $landing_type_map = [
                '1' => 'iframe',
                '2' => 'redirect'
            ];
            $landing_type = $landing_type_map[$product['page_method']] ?? 'iframe';
            
            $sql = "INSERT INTO epic_products (
                        uuid, name, slug, description, price, commission_type,
                        commission_value, image, landing_page_type, landing_page_url,
                        download_file, content, status, created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
                    )";
            
            $status = ($product['pro_status'] == '1') ? 'active' : 'inactive';
            
            $params = [
                $uuid,
                $product['page_judul'],
                $product['page_url'],
                $product['page_diskripsi'],
                floatval($product['pro_harga'] ?? 0),
                $commission_type,
                $commission_value,
                $product['pro_img'],
                $landing_type,
                $product['page_iframe'],
                $product['pro_file'],
                $product['page_fr'],
                $status
            ];
            
            $this->execute($sql, $params);
            
            // Store mapping
            $this->product_mapping[$product['page_id']] = $this->con->insert_id;
            $migrated++;
        }
        
        $this->log("Migrated {$migrated} products");
    }
    
    /**
     * Migrate orders from sa_order to epic_orders
     */
    private function migrateOrders() {
        $this->log('Migrating orders...');
        
        $orders = $this->select("SELECT * FROM sa_order ORDER BY order_id");
        $migrated = 0;
        
        foreach ($orders as $order) {
            $uuid = $this->generateUUID();
            $user_id = $this->user_mapping[$order['order_idmember']] ?? null;
            $referrer_id = $this->user_mapping[$order['order_idsponsor']] ?? null;
            $product_id = $this->product_mapping[$order['order_idproduk']] ?? null;
            $staff_id = $this->user_mapping[$order['order_idstaff']] ?? null;
            
            if ($user_id && $product_id) {
                // Map status
                $status_map = [
                    '0' => 'pending',
                    '1' => 'paid'
                ];
                $status = $status_map[$order['order_status']] ?? 'pending';
                
                $order_number = 'ORD-' . str_pad($order['order_id'], 6, '0', STR_PAD_LEFT);
                
                $sql = "INSERT INTO epic_orders (
                            uuid, order_number, user_id, referrer_id, product_id,
                            amount, unique_amount, status, payment_reference,
                            staff_id, paid_at, expired_at, created_at, updated_at
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                        )";
                
                $params = [
                    $uuid,
                    $order_number,
                    $user_id,
                    $referrer_id,
                    $product_id,
                    floatval($order['order_harga']),
                    floatval($order['order_hargaunik']),
                    $status,
                    $order['order_trx'],
                    $staff_id,
                    $order['order_tglbayar'],
                    $order['order_tglexpired'],
                    $order['order_tglorder'],
                    $order['order_tglorder']
                ];
                
                $this->execute($sql, $params);
                
                // Store mapping
                $this->order_mapping[$order['order_id']] = $this->con->insert_id;
                $migrated++;
            }
        }
        
        $this->log("Migrated {$migrated} orders");
    }
    
    /**
     * Migrate transactions from sa_laporan to epic_transactions
     */
    private function migrateTransactions() {
        $this->log('Migrating transactions...');
        
        $transactions = $this->select("SELECT * FROM sa_laporan ORDER BY lap_id");
        $migrated = 0;
        
        foreach ($transactions as $transaction) {
            $order_id = $this->order_mapping[$transaction['lap_idorder']] ?? null;
            $user_id = $this->user_mapping[$transaction['lap_idmember']] ?? null;
            $referrer_id = $this->user_mapping[$transaction['lap_idsponsor']] ?? null;
            
            if ($user_id) {
                // Map type based on lap_code
                $type_map = [
                    '1' => 'sale',
                    '2' => 'commission'
                ];
                $type = $type_map[$transaction['lap_code']] ?? 'sale';
                
                $sql = "INSERT INTO epic_transactions (
                            order_id, user_id, referrer_id, type, amount_in,
                            amount_out, status, description, reference,
                            created_at, updated_at
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?, 'completed', ?, ?, ?, ?
                        )";
                
                $params = [
                    $order_id,
                    $user_id,
                    $referrer_id,
                    $type,
                    floatval($transaction['lap_masuk']),
                    floatval($transaction['lap_keluar']),
                    $transaction['lap_keterangan'],
                    'MIGRATED-' . $transaction['lap_id'],
                    $transaction['lap_tanggal'],
                    $transaction['lap_tanggal']
                ];
                
                $this->execute($sql, $params);
                $migrated++;
            }
        }
        
        $this->log("Migrated {$migrated} transactions");
    }
    
    /**
     * Migrate settings from sa_setting to epic_settings
     */
    private function migrateSettings() {
        $this->log('Migrating settings...');
        
        $settings = $this->select("SELECT * FROM sa_setting ORDER BY set_id");
        $migrated = 0;
        
        foreach ($settings as $setting) {
            $sql = "INSERT INTO epic_settings (
                        `key`, `value`, `type`, `group`, `description`
                    ) VALUES (?, ?, 'string', 'migrated', 'Migrated from SimpleAff Plus')";
            
            $this->execute($sql, [
                $setting['set_label'],
                $setting['set_value']
            ]);
            $migrated++;
        }
        
        $this->log("Migrated {$migrated} settings");
    }
    
    /**
     * Migrate form fields from sa_form to epic_form_fields
     */
    private function migrateFormFields() {
        $this->log('Migrating form fields...');
        
        $fields = $this->select("SELECT * FROM sa_form ORDER BY ff_sort");
        $migrated = 0;
        
        foreach ($fields as $field) {
            $options = null;
            if (!empty($field['ff_options'])) {
                $options = json_encode(explode(',', $field['ff_options']));
            }
            
            $sql = "INSERT INTO epic_form_fields (
                        name, label, type, description, options, is_required,
                        show_in_profile, show_in_registration, show_in_network,
                        sort_order, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
            
            $params = [
                $field['ff_field'],
                $field['ff_label'],
                $field['ff_type'],
                $field['ff_keterangan'],
                $options,
                ($field['ff_required'] == '1'),
                ($field['ff_profil'] == '1'),
                ($field['ff_registrasi'] == '1'),
                ($field['ff_network'] == '1'),
                intval($field['ff_sort'])
            ];
            
            $this->execute($sql, $params);
            $migrated++;
        }
        
        $this->log("Migrated {$migrated} form fields");
    }
    
    /**
     * Migrate categories from sa_kategori to epic_categories
     */
    private function migrateCategories() {
        if (!$this->tableExists('sa_kategori')) {
            $this->log('Skipping categories migration - table does not exist');
            return;
        }
        
        $this->log('Migrating categories...');
        
        $categories = $this->select("SELECT * FROM sa_kategori ORDER BY kat_id");
        $migrated = 0;
        
        foreach ($categories as $category) {
            $parent_id = null;
            if (!empty($category['kat_parent_id'])) {
                $parent_id = $this->category_mapping[$category['kat_parent_id']] ?? null;
            }
            
            $sql = "INSERT INTO epic_categories (
                        parent_id, name, slug, status, created_at, updated_at
                    ) VALUES (?, ?, ?, 'active', NOW(), NOW())";
            
            $this->execute($sql, [
                $parent_id,
                $category['kat_nama'],
                $category['kat_slug']
            ]);
            
            $this->category_mapping[$category['kat_id']] = $this->con->insert_id;
            $migrated++;
        }
        
        $this->log("Migrated {$migrated} categories");
    }
    
    /**
     * Migrate articles from sa_artikel to epic_articles
     */
    private function migrateArticles() {
        if (!$this->tableExists('sa_artikel')) {
            $this->log('Skipping articles migration - table does not exist');
            return;
        }
        
        $this->log('Migrating articles...');
        
        $articles = $this->select("SELECT * FROM sa_artikel ORDER BY art_id");
        $migrated = 0;
        
        foreach ($articles as $article) {
            $uuid = $this->generateUUID();
            $category_id = $this->category_mapping[$article['art_kat_id']] ?? null;
            $product_id = $this->product_mapping[$article['art_product']] ?? null;
            $author_id = $this->user_mapping[$article['art_writer']] ?? 1;
            
            // Map status
            $status = ($article['art_status'] == '1') ? 'published' : 'draft';
            
            // Map visibility
            $visibility_map = [
                '0' => 'public',
                '1' => 'members',
                '2' => 'premium'
            ];
            $visibility = $visibility_map[$article['art_role']] ?? 'public';
            
            $sql = "INSERT INTO epic_articles (
                        uuid, category_id, product_id, author_id, title, slug,
                        content, featured_image, status, visibility,
                        published_at, created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )";
            
            $params = [
                $uuid,
                $category_id,
                $product_id,
                $author_id,
                $article['art_judul'],
                $article['art_slug'],
                $article['art_konten'],
                $article['art_img'],
                $status,
                $visibility,
                $article['art_tglpublish'],
                $article['art_tglpublish'],
                $article['art_tglpublish']
            ];
            
            $this->execute($sql, $params);
            $migrated++;
        }
        
        $this->log("Migrated {$migrated} articles");
    }
    
    /**
     * Migrate analytics from sa_visitor to epic_analytics
     */
    private function migrateAnalytics() {
        if (!$this->tableExists('sa_visitor')) {
            $this->log('Skipping analytics migration - table does not exist');
            return;
        }
        
        $this->log('Migrating analytics...');
        
        $visitors = $this->select("SELECT * FROM sa_visitor ORDER BY id");
        $migrated = 0;
        
        foreach ($visitors as $visitor) {
            $user_id = $this->user_mapping[$visitor['id_sponsor']] ?? null;
            
            $sql = "INSERT INTO epic_analytics (
                        user_id, session_id, page_url, ip_address,
                        visited_at
                    ) VALUES (?, ?, '/', '127.0.0.1', ?)";
            
            $this->execute($sql, [
                $user_id,
                'MIGRATED-' . $visitor['id'],
                $visitor['visit_date']
            ]);
            $migrated++;
        }
        
        $this->log("Migrated {$migrated} analytics records");
    }
    
    /**
     * Create initial affiliate links for existing users
     */
    private function createInitialAffiliateLinks() {
        $this->log('Creating initial affiliate links...');
        
        $users = $this->select("SELECT id, referral_code FROM epic_users WHERE referral_code IS NOT NULL");
        $products = $this->select("SELECT id, slug FROM epic_products WHERE status = 'active'");
        
        $created = 0;
        
        foreach ($users as $user) {
            foreach ($products as $product) {
                $link_code = $user['referral_code'] . '-' . $product['slug'];
                $original_url = '/order/' . $product['slug'] . '?ref=' . $user['referral_code'];
                
                $sql = "INSERT INTO epic_affiliate_links (
                            user_id, product_id, link_code, original_url,
                            status, created_at, updated_at
                        ) VALUES (?, ?, ?, ?, 'active', NOW(), NOW())";
                
                $this->execute($sql, [
                    $user['id'],
                    $product['id'],
                    $link_code,
                    $original_url
                ]);
                $created++;
            }
        }
        
        $this->log("Created {$created} affiliate links");
    }
    
    /**
     * Validate migration
     */
    private function validateMigration() {
        $this->log('Validating migration...');
        
        $validations = [
            'Users' => [
                'old' => "SELECT COUNT(*) FROM sa_member",
                'new' => "SELECT COUNT(*) FROM epic_users"
            ],
            'Products' => [
                'old' => "SELECT COUNT(*) FROM sa_page",
                'new' => "SELECT COUNT(*) FROM epic_products"
            ],
            'Orders' => [
                'old' => "SELECT COUNT(*) FROM sa_order",
                'new' => "SELECT COUNT(*) FROM epic_orders"
            ]
        ];
        
        foreach ($validations as $name => $queries) {
            $old_count = $this->selectValue($queries['old']);
            $new_count = $this->selectValue($queries['new']);
            
            if ($old_count == $new_count) {
                $this->log("✓ {$name}: {$old_count} records migrated successfully");
            } else {
                $this->log("⚠ {$name}: Expected {$old_count}, got {$new_count}");
            }
        }
    }
    
    /**
     * Rollback migration
     */
    public function rollback() {
        $this->log('Rolling back migration...');
        
        // Drop EPIC tables
        $epic_tables = [
            'epic_activity_log', 'epic_notifications', 'epic_analytics',
            'epic_form_fields', 'epic_settings', 'epic_articles',
            'epic_categories', 'epic_link_clicks', 'epic_affiliate_links',
            'epic_transactions', 'epic_orders', 'epic_products',
            'epic_referrals', 'epic_users'
        ];
        
        foreach ($epic_tables as $table) {
            $this->query("DROP TABLE IF EXISTS `{$table}`");
        }
        
        // Restore from backup
        $backup_tables = [
            'sa_member', 'sa_sponsor', 'sa_page', 'sa_order',
            'sa_laporan', 'sa_setting', 'sa_form', 'sa_kategori',
            'sa_artikel', 'sa_visitor'
        ];
        
        foreach ($backup_tables as $table) {
            $backup_table = $this->backup_prefix . $table;
            if ($this->tableExists($backup_table)) {
                $this->query("DROP TABLE IF EXISTS `{$table}`");
                $this->query("CREATE TABLE `{$table}` LIKE `{$backup_table}`");
                $this->query("INSERT INTO `{$table}` SELECT * FROM `{$backup_table}`");
                $this->log("Restored {$table} from backup");
            }
        }
        
        $this->log('Rollback completed');
    }
    
    /**
     * Print migration summary
     */
    private function printSummary() {
        $duration = round(microtime(true) - $this->start_time, 2);
        
        echo "\n=== MIGRATION SUMMARY ===\n";
        echo "Duration: {$duration} seconds\n";
        echo "Log entries: " . count($this->migration_log) . "\n";
        
        foreach ($this->migration_log as $entry) {
            echo $entry . "\n";
        }
        
        echo "\n=== NEXT STEPS ===\n";
        echo "1. Update application code to use epic_* tables\n";
        echo "2. Test all functionality thoroughly\n";
        echo "3. Remove backup tables when confident\n";
        echo "4. Update documentation\n";
    }
    
    // Helper methods
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $this->migration_log[] = "[{$timestamp}] {$message}";
        echo "[{$timestamp}] {$message}\n";
    }
    
    private function query($sql) {
        $result = mysqli_query($this->con, $sql);
        if (!$result) {
            throw new Exception('Query failed: ' . mysqli_error($this->con) . " SQL: {$sql}");
        }
        return $result;
    }
    
    private function execute($sql, $params = []) {
        $stmt = mysqli_prepare($this->con, $sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . mysqli_error($this->con));
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        $result = mysqli_stmt_execute($stmt);
        if (!$result) {
            throw new Exception('Execute failed: ' . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
        return $result;
    }
    
    private function select($sql) {
        $result = $this->query($sql);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    private function selectValue($sql) {
        $result = $this->query($sql);
        $row = mysqli_fetch_array($result, MYSQLI_NUM);
        return $row[0] ?? 0;
    }
    
    private function tableExists($table) {
        $result = $this->query("SHOW TABLES LIKE '{$table}'");
        return mysqli_num_rows($result) > 0;
    }
    
    private function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    private function generateReferralCode() {
        return 'REF' . strtoupper(substr(md5(uniqid()), 0, 6));
    }
    
    // Mapping arrays to store old ID -> new ID relationships
    private $user_mapping = [];
    private $product_mapping = [];
    private $order_mapping = [];
    private $category_mapping = [];
}

// Usage
if (php_sapi_name() === 'cli' || isset($_GET['migrate'])) {
    try {
        $migration = new EpicMigration();
        
        if (isset($_GET['rollback'])) {
            $migration->rollback();
        } else {
            $migration->migrate();
        }
        
    } catch (Exception $e) {
        echo "Migration failed: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "<h1>EPIC Hub Migration</h1>";
    echo "<p><a href='?migrate=1'>Start Migration</a></p>";
    echo "<p><a href='?rollback=1' onclick='return confirm(\"Are you sure?\")'>Rollback Migration</a></p>";
}
?>