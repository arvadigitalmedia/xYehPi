<?php
/**
 * Test Routing Confirm Email
 * Debug routing confirm-email tanpa dependency bermasalah
 */

echo "<h2>Test Routing Confirm Email</h2>";

// Simulasi routing
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
echo "<p>Request URI: " . htmlspecialchars($request_uri) . "</p>";

// Parse segments
$path = parse_url($request_uri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));
echo "<p>Segments: " . implode(' | ', $segments) . "</p>";

if (isset($segments[0]) && $segments[0] === 'confirm-email' && isset($segments[1])) {
    $token = $segments[1];
    echo "<p>Token dari URL: " . htmlspecialchars($token) . "</p>";
    
    try {
        // Test koneksi database
        $pdo = new PDO("mysql:host=localhost;dbname=bustanu1_ujicoba", 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p>✓ Koneksi database berhasil</p>";
        
        // Cari token
        $stmt = $pdo->prepare("SELECT t.*, u.id as user_id, u.name, u.email, u.email_verified_at 
                               FROM epic_user_tokens t 
                               JOIN epic_users u ON t.user_id = u.id 
                               WHERE t.token = ? AND t.type = 'email_verification' AND t.expires_at > NOW() AND t.used_at IS NULL");
        $stmt->execute([$token]);
        $confirmation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($confirmation) {
            echo "<p>✓ Token ditemukan untuk user: " . htmlspecialchars($confirmation['name']) . "</p>";
            
            if ($confirmation['email_verified_at']) {
                echo "<p>⚠️ Email sudah dikonfirmasi sebelumnya</p>";
            } else {
                // Update user
                $stmt = $pdo->prepare("UPDATE epic_users SET email_verified_at = NOW(), status = 'free' WHERE id = ?");
                $result1 = $stmt->execute([$confirmation['user_id']]);
                echo "<p>Update user: " . ($result1 ? '✓ berhasil' : '✗ gagal') . "</p>";
                
                // Update token
                $stmt = $pdo->prepare("UPDATE epic_user_tokens SET used_at = NOW() WHERE id = ?");
                $result2 = $stmt->execute([$confirmation['id']]);
                echo "<p>Update token: " . ($result2 ? '✓ berhasil' : '✗ gagal') . "</p>";
                
                if ($result1 && $result2) {
                    echo "<p>🎉 <strong>Konfirmasi email berhasil!</strong></p>";
                    echo "<p><a href='/login?confirmed=1'>Lanjut ke Login</a></p>";
                }
            }
        } else {
            echo "<p>✗ Token tidak ditemukan atau expired</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p>Format URL tidak sesuai. Gunakan: /confirm-email/TOKEN</p>";
}
?>