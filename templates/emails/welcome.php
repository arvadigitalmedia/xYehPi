<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang - <?= htmlspecialchars($data['site_name']) ?></title>
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
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
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
        .action-buttons {
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
            margin: 0 10px 10px 0;
        }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.15);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #64748b 0%, #94a3b8 100%);
            color: #ffffff;
        }
        .welcome-box {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1px solid #22c55e;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
            text-align: center;
        }
        .welcome-box h2 {
            margin: 0 0 15px 0;
            color: #15803d;
            font-size: 24px;
        }
        .welcome-box p {
            margin: 0;
            color: #166534;
            font-size: 16px;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .feature-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        .feature-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #d4af37 0%, #f4e4a6 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
        }
        .feature-card h3 {
            margin: 0 0 10px 0;
            color: #1e293b;
            font-size: 16px;
        }
        .feature-card p {
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
                display: block;
                margin: 0 0 10px 0;
            }
            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🎉 Selamat Datang!</h1>
            <p>Email Anda telah berhasil dikonfirmasi</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="welcome-box">
                <h2>Halo, <?= htmlspecialchars($data['user_name']) ?>!</h2>
                <p>
                    Selamat! Akun Anda di <strong><?= htmlspecialchars($data['site_name']) ?></strong> 
                    telah aktif dan siap digunakan.
                </p>
            </div>

            <div class="message">
                Terima kasih telah bergabung dengan komunitas kami. Kami sangat senang memiliki Anda sebagai bagian dari keluarga besar <?= htmlspecialchars($data['site_name']) ?>.
            </div>

            <div class="action-buttons">
                <a href="<?= htmlspecialchars($data['dashboard_url']) ?>" class="btn">
                    Masuk ke Dashboard
                </a>
                <a href="<?= htmlspecialchars($data['site_url']) ?>" class="btn btn-secondary">
                    Jelajahi Website
                </a>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Dashboard Pribadi</h3>
                    <p>Kelola profil, pengaturan, dan aktivitas Anda dalam satu tempat yang mudah diakses.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔔</div>
                    <h3>Notifikasi Real-time</h3>
                    <p>Dapatkan update terbaru dan informasi penting langsung ke email Anda.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <h3>Fitur Lengkap</h3>
                    <p>Akses semua fitur premium dan tools yang tersedia untuk memaksimalkan pengalaman Anda.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🛡️</div>
                    <h3>Keamanan Terjamin</h3>
                    <p>Data dan privasi Anda dilindungi dengan standar keamanan tingkat enterprise.</p>
                </div>
            </div>

            <div class="message">
                <strong>Langkah Selanjutnya:</strong>
                <ol style="color: #475569; margin: 15px 0; padding-left: 20px;">
                    <li>Lengkapi profil Anda di dashboard</li>
                    <li>Jelajahi fitur-fitur yang tersedia</li>
                    <li>Bergabung dengan komunitas kami</li>
                    <li>Mulai memanfaatkan semua layanan yang ada</li>
                </ol>
            </div>

            <div style="background-color: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 20px; margin: 30px 0;">
                <p style="margin: 0; font-size: 14px; color: #92400e;">
                    <strong>💡 Tips:</strong> Bookmark halaman dashboard Anda untuk akses yang lebih mudah. 
                    Jika Anda memiliki pertanyaan atau membutuhkan bantuan, jangan ragu untuk menghubungi tim support kami.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                Terima kasih telah mempercayai <?= htmlspecialchars($data['site_name']) ?>.<br>
                Jika Anda memiliki pertanyaan, silakan kunjungi <a href="<?= htmlspecialchars($data['site_url']) ?>">website kami</a> 
                atau hubungi tim support.
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #94a3b8;">
                © <?= date('Y') ?> <?= htmlspecialchars($data['site_name']) ?>. Semua hak dilindungi.
            </p>
        </div>
    </div>
</body>
</html>