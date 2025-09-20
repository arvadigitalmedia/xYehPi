<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Email - <?= htmlspecialchars($site_name) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .confirmation-button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .confirmation-button:hover {
            background-color: #0056b3;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1><?= htmlspecialchars($site_name) ?></h1>
            <p>Konfirmasi Email Anda</p>
        </div>
        
        <div class="content">
            <h2>Halo, <?= htmlspecialchars($user_name) ?>!</h2>
            
            <p>Terima kasih telah mendaftar di <?= htmlspecialchars($site_name) ?>. Untuk mengaktifkan akun Anda, silakan konfirmasi email Anda dengan mengklik tombol di bawah ini:</p>
            
            <div style="text-align: center;">
                <a href="<?= htmlspecialchars($confirmation_url) ?>" class="confirmation-button">
                    Konfirmasi Email Saya
                </a>
            </div>
            
            <p>Atau salin dan tempel link berikut di browser Anda:</p>
            <p style="word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
                <?= htmlspecialchars($confirmation_url) ?>
            </p>
            
            <div class="warning">
                <strong>Penting:</strong> Link konfirmasi ini akan kedaluwarsa dalam <?= $expires_hours ?> jam. Jika Anda tidak mengkonfirmasi email dalam waktu tersebut, Anda perlu mendaftar ulang.
            </div>
            
            <p>Jika Anda tidak mendaftar di <?= htmlspecialchars($site_name) ?>, silakan abaikan email ini.</p>
        </div>
        
        <div class="footer">
            <p>Email ini dikirim secara otomatis dari <?= htmlspecialchars($site_name) ?></p>
            <p>Jangan membalas email ini. Jika Anda membutuhkan bantuan, silakan hubungi support kami.</p>
            <p><a href="<?= htmlspecialchars($site_url) ?>"><?= htmlspecialchars($site_url) ?></a></p>
        </div>
    </div>
</body>
</html>