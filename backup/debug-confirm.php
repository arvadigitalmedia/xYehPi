<?php
/**
 * Debug Konfirmasi Email - Standalone
 */

// Ambil token dari URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Token tidak ditemukan. Gunakan: debug-confirm.php?token=TOKEN");
}

echo "<h2>Debug Konfirmasi Email</h2>";
echo "<p>Token: " . htmlspecialchars($token) . "</p>";

try {
    // Koneksi database langsung
    $pdo = new PDO("mysql:host=localhost;dbname=bustanu1_ujicoba", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✓ Koneksi database berhasil</p>";
    
    // Cari token
    $stmt = $pdo->prepare("
        SELECT t.*, u.id as user_id, u.name, u.email, u.email_verified_at, u.status
        FROM epic_user_tokens t 
        JOIN epic_users u ON t.user_id = u.id 
        WHERE t.token = ? AND t.type = 'email_verification' AND t.expires_at > NOW() AND t.used_at IS NULL
    ");
    $stmt->execute([$token]);
    $confirmation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($confirmation) {
        echo "<p>✓ Token ditemukan untuk user: " . htmlspecialchars($confirmation['name']) . " (" . htmlspecialchars($confirmation['email']) . ")</p>";
        echo "<p>Status user saat ini: " . htmlspecialchars($confirmation['status']) . "</p>";
        echo "<p>Email verified at: " . ($confirmation['email_verified_at'] ? $confirmation['email_verified_at'] : 'Belum dikonfirmasi') . "</p>";
        
        if ($confirmation['email_verified_at']) {
            echo "<p>⚠️ Email sudah dikonfirmasi sebelumnya pada: " . $confirmation['email_verified_at'] . "</p>";
        } else {
            echo "<p>🔄 Melakukan konfirmasi email...</p>";
            
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
                
                // Verifikasi update
                $stmt = $pdo->prepare("SELECT email_verified_at, status FROM epic_users WHERE id = ?");
                $stmt->execute([$confirmation['user_id']]);
                $updated_user = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<p>Verifikasi - Status baru: " . $updated_user['status'] . ", Email verified: " . $updated_user['email_verified_at'] . "</p>";
            }
        }
    } else {
        echo "<p>✗ Token tidak ditemukan, expired, atau sudah digunakan</p>";
        
        // Debug: cek token yang ada
        $stmt = $pdo->prepare("SELECT token, type, expires_at, used_at, created_at FROM epic_user_tokens WHERE token = ?");
        $stmt->execute([$token]);
        $token_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($token_info) {
            echo "<p>Token ditemukan tapi tidak valid:</p>";
            echo "<pre>" . print_r($token_info, true) . "</pre>";
        } else {
            echo "<p>Token tidak ditemukan sama sekali di database</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<hr>
<p><a href="/register">Ke Halaman Registrasi</a> | <a href="/test-email-confirmation.php">Generate Token Baru</a></p>