<?php
/**
 * Setup WhatsApp Settings Table
 * Script untuk membuat tabel settings dan konfigurasi awal WhatsApp notification
 * 
 * @package EPIC Hub
 * @version 1.0.0
 */

require_once __DIR__ . '/bootstrap.php';

echo "<h1>🚀 Setup WhatsApp Settings Table</h1>";

try {
    // Check if settings table exists
    echo "<h3>📋 Step 1: Checking settings table</h3>";
    
    try {
        $result = db()->select('SELECT 1 FROM settings LIMIT 1');
        echo "<p style='color: green;'>✅ Settings table already exists</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>ℹ️ Settings table does not exist, creating...</p>";
        
        // Create settings table
        $create_settings_sql = "
        CREATE TABLE IF NOT EXISTS `settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `key` varchar(255) NOT NULL,
            `value` longtext,
            `type` varchar(50) DEFAULT 'string',
            `group` varchar(100) DEFAULT 'general',
            `description` text,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_key` (`key`),
            KEY `idx_group` (`group`),
            KEY `idx_type` (`type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        db()->query($create_settings_sql);
        echo "<p style='color: green;'>✅ Settings table created successfully</p>";
    }
    
    // Insert default WhatsApp settings
    echo "<h3>📱 Step 2: Setting up default WhatsApp configurations</h3>";
    
    $default_settings = [
        // Basic configuration
        'starsender_enabled' => '0',
        'starsender_api_key' => '',
        'starsender_test_phone' => '',
        
        // Registration messages - User
        'starsender_registration_free_message' => 'Halo [user_name]! 🎉\n\nSelamat bergabung di [site_name]!\n\nAkun Anda telah berhasil dibuat dengan level FREE. Silakan cek email Anda untuk konfirmasi dan aktivasi akun.\n\nTerima kasih!',
        
        'starsender_registration_epic_message' => 'Halo [user_name]! 🎉\n\nSelamat bergabung di [site_name]!\n\nAkun EPIC Anda telah berhasil dibuat. Silakan cek email Anda untuk konfirmasi dan aktivasi akun.\n\nSelamat menikmati fitur premium!\n\nTerima kasih!',
        
        'starsender_registration_epis_message' => 'Halo [user_name]! 🎉\n\nSelamat bergabung di [site_name]!\n\nAkun EPIS Anda telah berhasil dibuat. Silakan cek email Anda untuk konfirmasi dan aktivasi akun.\n\nSelamat menikmati fitur premium terlengkap!\n\nTerima kasih!',
        
        // Registration messages - Referral/Sponsor
        'starsender_registration_referral_message' => 'Halo [sponsor_name]! 🎉\n\nAda prospek baru yang mendaftar melalui link referral Anda!\n\n👤 Nama: [user_name]\n📧 Email: [user_email]\n📱 Phone: [user_phone]\n📅 Tanggal: [user_join_date]\n🏷️ Level: [user_level]\n\nSelamat! Terus promosikan link referral Anda untuk mendapatkan lebih banyak prospek.\n\nTerima kasih!',
        
        'starsender_registration_image' => '',
        'starsender_registration_button' => '',
        
        // Upgrade messages
        'starsender_upgrade_user_message' => 'Selamat [user_name]! 🎉

Upgrade akun Anda ke [new_level] berhasil!

✨ Fitur baru yang bisa Anda nikmati:
- Komisi lebih tinggi
- Dashboard premium
- Support prioritas

Login sekarang: [dashboard_url]',
        
        'starsender_upgrade_sponsor_message' => 'Halo [sponsor_name]! 💰

Member referral Anda telah upgrade:

👤 Nama: [user_name]
📈 Upgrade ke: [new_level]
💵 Komisi Anda: [commission_amount]

Cek dashboard untuk detail lengkap!',
        
        'starsender_upgrade_image' => '',
        'starsender_upgrade_button' => 'Cek Komisi',
        
        // Purchase messages
        'starsender_purchase_buyer_message' => 'Terima kasih [user_name]! 🛒

Pembelian Anda berhasil:

📦 Produk: [product_name]
💰 Total: [total_amount]
📅 Tanggal: [purchase_date]

Akses produk: [product_url]',
        
        'starsender_purchase_referral_message' => 'Halo [sponsor_name]! 💰

Ada pembelian dari referral Anda:

👤 Buyer: [user_name]
📦 Produk: [product_name]
💵 Komisi: [commission_amount]

Dashboard: [dashboard_url]',
        
        'starsender_purchase_image' => '',
        'starsender_purchase_button' => 'Lihat Detail',
        
        // Payout messages
        'starsender_payout_message' => 'Halo [user_name]! 💰

Pencairan komisi Anda berhasil diproses:

💵 Jumlah: [payout_amount]
🏦 Metode: [payout_method]
📅 Tanggal: [payout_date]
🆔 ID Transaksi: [transaction_id]

Dana akan masuk dalam 1-3 hari kerja.',
        
        'starsender_payout_image' => '',
        'starsender_payout_button' => 'Cek Status',
        
        // Closing EPIC Account messages
        'starsender_closing_epis_message' => 'Halo [supervisor_name]! 🎯

Ada closing EPIC Account baru:

👤 Member: [user_name]
📱 Phone: [user_phone]
💰 Nilai Closing: [closing_amount]
📅 Tanggal: [closing_date]

Segera follow up untuk proses selanjutnya!',
        
        'starsender_closing_image' => '',
        'starsender_closing_button' => 'Proses Closing'
    ];
    
    $inserted = 0;
    $updated = 0;
    
    foreach ($default_settings as $key => $value) {
        try {
            // Check if setting already exists
            $existing = db()->selectOne("SELECT id FROM settings WHERE `key` = ?", [$key]);
            
            if ($existing) {
                // Update existing setting
                db()->query(
                    "UPDATE settings SET `value` = ?, updated_at = CURRENT_TIMESTAMP WHERE `key` = ?",
                    [$value, $key]
                );
                $updated++;
            } else {
                // Insert new setting
                db()->query(
                    "INSERT INTO settings (`key`, `value`, `type`, `group`) VALUES (?, ?, 'text', 'whatsapp')",
                    [$key, $value]
                );
                $inserted++;
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error setting $key: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<p style='color: green;'>✅ WhatsApp settings configured:</p>";
    echo "<ul>";
    echo "<li>📝 $inserted new settings inserted</li>";
    echo "<li>🔄 $updated existing settings updated</li>";
    echo "</ul>";
    
    // Test database connection
    echo "<h3>🔍 Step 3: Testing configuration</h3>";
    
    $test_settings = db()->select("SELECT `key`, `value` FROM settings WHERE `key` LIKE 'starsender_%' LIMIT 5");
    
    if (count($test_settings) > 0) {
        echo "<p style='color: green;'>✅ Settings table working properly</p>";
        echo "<p>Sample settings found:</p>";
        echo "<ul>";
        foreach ($test_settings as $setting) {
            $preview = strlen($setting['value']) > 50 ? substr($setting['value'], 0, 50) . '...' : $setting['value'];
            echo "<li><strong>" . htmlspecialchars($setting['key']) . ":</strong> " . htmlspecialchars($preview) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ No settings found, something went wrong</p>";
    }
    
    echo "<h3>🎉 Setup Complete!</h3>";
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>✅ WhatsApp Notification Setup Berhasil!</h4>";
    echo "<p><strong>Langkah selanjutnya:</strong></p>";
    echo "<ol>";
    echo "<li>🔑 Dapatkan API Key dari <a href='https://starsender.online/dashboard' target='_blank'>Starsender Dashboard</a></li>";
    echo "<li>⚙️ Buka <a href='" . epic_url('admin/settings/whatsapp-notification') . "'>Admin Settings > WhatsApp Notification</a></li>";
    echo "<li>📝 Masukkan API Key dan konfigurasi pesan</li>";
    echo "<li>🧪 Test koneksi dan notifikasi</li>";
    echo "<li>✅ Aktifkan notifikasi WhatsApp</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error during setup</h3>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
}
?>