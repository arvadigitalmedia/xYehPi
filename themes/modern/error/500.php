<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internal Server Error - EPIC Hub</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            text-align: center;
            color: white;
            max-width: 500px;
            padding: 2rem;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 700;
            margin: 0;
            opacity: 0.8;
        }
        .error-title {
            font-size: 2rem;
            margin: 1rem 0;
            font-weight: 600;
        }
        .error-message {
            font-size: 1.1rem;
            margin: 1.5rem 0;
            opacity: 0.9;
            line-height: 1.6;
        }
        .back-button {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 2rem;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">500</h1>
        <h2 class="error-title">Internal Server Error</h2>
        <p class="error-message">
            Maaf, terjadi kesalahan pada server. Tim kami sedang memperbaiki masalah ini.
            Silakan coba lagi dalam beberapa saat.
        </p>
        <a href="<?= epic_url() ?>" class="back-button">Kembali ke Beranda</a>
    </div>
</body>
</html>