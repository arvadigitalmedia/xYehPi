<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Email - <?= htmlspecialchars($data['site_name']) ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #d4af37 0%, #f4e4a6 100%);
            padding: 40px 30px;
            text-align: center;
            color: #1a1a1a;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #1e293b;
        }
        .message {
            font-size: 16px;
            margin-bottom: 30px;
            color: #475569;
        }
        .confirmation-button {
            text-align: center;
            margin: 40px 0;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #d4af37 0%, #f4e4a6 100%);
            color: #1a1a1a;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.15);
        }
        .info-box {
            background-color: #f1f5f9;
            border-left: 4px solid #d4af37;
            padding: 20px;
            margin: 30px 0;
            border-radius: 0 8px 8px 0;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            color: #1e293b;
            font-size: 16px;
        }
        .info-box p {
            margin: 0;
            font-size: 14px;
            color: #64748b;
        }
        .footer {
            background-color: #f8fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 0;
            font-size: 14px;
            color: #64748b;
        }
        .footer a {
            color: #d4af37;
            text-decoration: none;
        }
        .security-notice {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .security-notice p {
            margin: 0;
            font-size: 14px;
            color: #92400e;
        }
        @media (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            .header, .content, .footer {
                padding: 20px;
            }
            .btn {
                padding: 14px 24px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><?= htmlspecialchars($data['site_name']) ?></h1>
            <p>Konfirmasi Email Anda</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Halo, <?= htmlspecialchars($data['user_name']) ?>!
            </div>

            <div class="message">
                Terima kasih telah mendaftar di <strong><?= htmlspecialchars($data['site_name']) ?></strong>. 
                Untuk mengaktifkan akun Anda dan mulai menggunakan layanan kami, silakan konfirmasi alamat email Anda dengan mengklik tombol di bawah ini.
            </div>

            <div class="confirmation-button">
                <a href="<?= htmlspecialchars($data['confirmation_url']) ?>" class="btn">
                    Konfirmasi Email Saya
                </a>
            </div>

            <div class="info-box">
                <h3>Informasi Akun Anda:</h3>
                <p><strong>Email:</strong> <?= htmlspecialchars($data['user_email']) ?></p>
                <p><strong>Nama:</strong> <?= htmlspecialchars($data['user_name']) ?></p>
                <p><strong>Tanggal Registrasi:</strong> <?= date('d F Y, H:i') ?> WIB</p>
            </div>

            <div class="security-notice">
                <p>
                    <strong>Penting:</strong> Link konfirmasi ini akan kedaluwarsa dalam <?= $data['expires_hours'] ?> jam. 
                    Jika Anda tidak melakukan registrasi di <?= htmlspecialchars($data['site_name']) ?>, 
                    silakan abaikan email ini.
                </p>
            </div>

            <div class="message">
                Jika tombol di atas tidak berfungsi, Anda dapat menyalin dan menempelkan link berikut ke browser Anda:
                <br><br>
                <a href="<?= htmlspecialchars($data['confirmation_url']) ?>" style="color: #d4af37; word-break: break-all;">
                    <?= htmlspecialchars($data['confirmation_url']) ?>
                </a>
            </div>

            <div class="message">
                Setelah email dikonfirmasi, Anda dapat:
                <ul style="color: #475569; margin: 10px 0;">
                    <li>Mengakses dashboard pribadi Anda</li>
                    <li>Mengelola profil dan pengaturan akun</li>
                    <li>Menerima notifikasi penting</li>
                    <li>Menggunakan semua fitur yang tersedia</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                Email ini dikirim secara otomatis dari sistem <?= htmlspecialchars($data['site_name']) ?>.<br>
                Jika Anda memiliki pertanyaan, silakan kunjungi <a href="<?= htmlspecialchars($data['site_url']) ?>">website kami</a>.
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #94a3b8;">
                © <?= date('Y') ?> <?= htmlspecialchars($data['site_name']) ?>. Semua hak dilindungi.
            </p>
        </div>
    </div>
</body>
</html>