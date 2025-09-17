<?php
/**
 * Create Premium Test User for EPIC Account Testing
 * Script untuk membuat user premium dengan akses penuh ke semua fitur
 */

require_once __DIR__ . '/bootstrap.php';

try {
    // Premium test user data
    $premium_user = [
        'name' => 'Premium Test User',
        'email' => 'premium@epichub.com',
        'password' => 'premium123',
        'phone' => '+6281234567891',
        'status' => 'premium',
        'role' => 'user'
    ];
    
    // Check if user already exists
    $existing_user = epic_get_user_by_email($premium_user['email']);
    if ($existing_user) {
        echo "Premium user already exists with ID: " . $existing_user['id'] . "\n";
        echo "Email: " . $existing_user['email'] . "\n";
        echo "Name: " . $existing_user['name'] . "\n";
        echo "Status: " . $existing_user['status'] . "\n";
        echo "Role: " . $existing_user['role'] . "\n";
        echo "\n=== PREMIUM LOGIN CREDENTIALS ===\n";
        echo "Email: premium@epichub.com\n";
        echo "Password: premium123\n";
        echo "Login URL: http://localhost:8000/login\n";
        exit;
    }
    
    // Generate UUID and referral code
    $uuid = epic_generate_uuid();
    $referral_code = epic_generate_referral_code();
    
    // Hash password
    $hashed_password = password_hash($premium_user['password'], PASSWORD_DEFAULT);
    
    // Prepare user data for database
    $user_data = [
        'uuid' => $uuid,
        'name' => $premium_user['name'],
        'email' => $premium_user['email'],
        'password' => $hashed_password,
        'phone' => $premium_user['phone'],
        'referral_code' => $referral_code,
        'status' => $premium_user['status'],
        'role' => $premium_user['role'],
        'email_verified_at' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Insert user into database
    $user_id = db()->insert('users', $user_data);
    
    if ($user_id) {
        echo "✅ Premium test user created successfully!\n";
        echo "User ID: " . $user_id . "\n";
        echo "UUID: " . $uuid . "\n";
        echo "Referral Code: " . $referral_code . "\n";
        echo "\n=== PREMIUM LOGIN CREDENTIALS ===\n";
        echo "Email: " . $premium_user['email'] . "\n";
        echo "Password: " . $premium_user['password'] . "\n";
        echo "\n=== ACCESS URLS ===\n";
        echo "Login: http://localhost:8000/login\n";
        echo "Member Area: http://localhost:8000/dashboard/member\n";
        echo "\n=== USER DETAILS ===\n";
        echo "Name: " . $premium_user['name'] . "\n";
        echo "Status: " . $premium_user['status'] . " (EPIC Account)\n";
        echo "Role: " . $premium_user['role'] . "\n";
        echo "Phone: " . $premium_user['phone'] . "\n";
        echo "Email Verified: Yes\n";
        echo "\n=== PREMIUM FEATURES AVAILABLE ===\n";
        echo "✅ Home Dashboard (Full Statistics)\n";
        echo "✅ Edit Profil (Complete Profile Management)\n";
        echo "✅ Prospek (Full CRM Features)\n";
        echo "✅ Bonus Cash (Commission & Referral System)\n";
        echo "✅ Akses Produk (All Premium Products)\n";
        echo "✅ History Order (Complete Order Management)\n";
        echo "\n=== PREMIUM BENEFITS ===\n";
        echo "🔥 Unlimited Access to All Features\n";
        echo "🔥 Premium Product Downloads\n";
        echo "🔥 Advanced Analytics & Reports\n";
        echo "🔥 Referral Commission System\n";
        echo "🔥 Priority Support\n";
        echo "🔥 Custom Landing Pages\n";
        
        // Create sample premium data
        echo "\n=== CREATING PREMIUM SAMPLE DATA ===\n";
        
        // Add some sample transactions for premium user
        try {
            // Sample commission transaction
            $transaction_data = [
                'user_id' => $user_id,
                'type' => 'commission',
                'amount_in' => 50000,
                'amount_out' => 0,
                'balance' => 50000,
                'status' => 'completed',
                'description' => 'Referral Commission - Sample Data',
                'reference_id' => 'REF-' . time(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Check if transactions table exists
            $tables = db()->select("SHOW TABLES LIKE 'epic_transactions'");
            if (!empty($tables)) {
                $transaction_id = db()->insert('transactions', $transaction_data);
                if ($transaction_id) {
                    echo "✅ Sample commission transaction created (Rp 50.000)\n";
                }
            }
            
            // Sample referral data
            $referral_data = [
                'user_id' => $user_id,
                'referrer_id' => null,
                'total_referrals' => 5,
                'total_earnings' => 250000,
                'this_month_referrals' => 2,
                'this_month_earnings' => 100000,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Check if referrals table exists
            $tables = db()->select("SHOW TABLES LIKE 'epic_referrals'");
            if (!empty($tables)) {
                $referral_id = db()->insert('referrals', $referral_data);
                if ($referral_id) {
                    echo "✅ Sample referral data created (5 referrals, Rp 250.000 earnings)\n";
                }
            }
            
        } catch (Exception $e) {
            echo "⚠️ Note: Some sample data creation failed (table might not exist): " . $e->getMessage() . "\n";
        }
        
        echo "\n🎉 Premium test user setup completed!\n";
        echo "\n=== TESTING COMPARISON ===\n";
        echo "Free Account: testmember@epichub.com / testmember123\n";
        echo "Premium Account: premium@epichub.com / premium123\n";
        echo "\nLogin dengan kedua akun untuk membandingkan fitur Free vs Premium!\n";
        
    } else {
        echo "❌ Failed to create premium test user\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error creating premium test user: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

?>