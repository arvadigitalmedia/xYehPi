<?php
/**
 * Advanced Landing Page Template
 * Supports multiple methods: iframe, inject URL, redirect
 */

if (!defined('EPIC_INIT')) {
    die('Direct access not allowed');
}

// Extract data
$landing_page = $data['landing_page'];
$sponsor = $data['sponsor'];
$username = $data['username'];
$landing_url = $data['landing_url'];
$method = $data['method'];
$find_replace_data = $data['find_replace_data'];
$register_url = $data['register_url'];

// Set referral cookie for registration
setcookie('epic_referral', $username, time() + (30 * 24 * 60 * 60), '/', '', false, true);
setcookie('epic_sponsor_name', $sponsor['name'], time() + (30 * 24 * 60 * 60), '/', '', false, true);

// Handle different methods
switch ($method) {
    case 3: // Redirect URL
        // Add referral parameters to landing URL
        $parsed_url = parse_url($landing_url);
        parse_str($parsed_url['query'] ?? '', $query_params);
        
        // Add referral tracking parameters
        $query_params['ref'] = $username;
        $query_params['sponsor'] = $sponsor['name'];
        $query_params['sponsor_id'] = $sponsor['id'];
        
        // Rebuild URL with referral parameters
        $final_landing_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . 
                            ($parsed_url['path'] ?? '') . 
                            '?' . http_build_query($query_params);
        
        // Redirect immediately
        header('Location: ' . $final_landing_url);
        exit;
        break;
        
    case 2: // Inject URL
        // Get content from URL and apply find & replace
        $content = @file_get_contents($landing_url);
        
        if ($content === false) {
            // Fallback to iframe if content fetch fails
            $method = 1;
            break;
        }
        
        // Apply find & replace
        if (!empty($find_replace_data)) {
            foreach ($find_replace_data as $fr) {
                if (!empty($fr['find']) && isset($fr['replace'])) {
                    $content = str_replace($fr['find'], $fr['replace'], $content);
                }
            }
        }
        
        // Add referral tracking to all links
        $content = preg_replace_callback(
            '/<a\s+([^>]*href=["\']?)([^"\'>]+)(["\'][^>]*)>/i',
            function($matches) use ($username, $sponsor) {
                $url = $matches[2];
                
                // Skip if it's already a full URL or anchor
                if (strpos($url, 'http') === 0 || strpos($url, '#') === 0 || strpos($url, 'mailto:') === 0) {
                    return $matches[0];
                }
                
                // Add referral parameters
                $separator = strpos($url, '?') !== false ? '&' : '?';
                $url .= $separator . 'ref=' . urlencode($username) . '&sponsor=' . urlencode($sponsor['name']);
                
                return '<a ' . $matches[1] . $url . $matches[3] . '>';
            },
            $content
        );
        
        // Add sponsor box and tracking scripts
        $sponsor_box = generateSponsorBox($sponsor, $username, $register_url);
        $tracking_script = generateTrackingScript($landing_page['id'], $sponsor['id'], $username);
        
        // Inject sponsor box and tracking before closing body tag
        $content = str_replace('</body>', $sponsor_box . $tracking_script . '</body>', $content);
        
        // Output the modified content
        echo $content;
        exit;
        break;
        
    default: // iframe (method 1)
        // Continue to iframe template below
        break;
}

// Add referral parameters to landing URL for iframe
$parsed_url = parse_url($landing_url);
parse_str($parsed_url['query'] ?? '', $query_params);

// Add referral tracking parameters
$query_params['ref'] = $username;
$query_params['sponsor'] = $sponsor['name'];
$query_params['sponsor_id'] = $sponsor['id'];

// Rebuild URL with referral parameters
$final_landing_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . 
                    ($parsed_url['path'] ?? '') . 
                    '?' . http_build_query($query_params);

// Generate sponsor box HTML
function generateSponsorBox($sponsor, $username, $register_url) {
    return '
    <!-- Sponsor Information Box -->
    <div class="epic-sponsor-box" id="epicSponsorBox" style="
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(26, 26, 26, 0.95);
        border: 1px solid #d4af37;
        border-radius: 12px;
        padding: 16px;
        max-width: 300px;
        backdrop-filter: blur(10px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        z-index: 10000;
        transition: all 0.3s ease;
        font-family: Inter, sans-serif;
        color: #f5f5f5;
        opacity: 0;
        transform: translateY(20px);
        pointer-events: none;
    ">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
            <div style="
                width: 48px;
                height: 48px;
                border-radius: 50%;
                background: linear-gradient(135deg, #1e40af, #d4af37);
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
                font-size: 18px;
                color: white;
            ">
                ' . (empty($sponsor['profile_photo']) ? strtoupper(substr($sponsor['name'], 0, 2)) : 
                    '<img src="' . epic_url('uploads/profiles/' . $sponsor['profile_photo']) . '" 
                         alt="' . htmlspecialchars($sponsor['name']) . '" 
                         style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">') . '
            </div>
            <div>
                <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 2px 0;">' . htmlspecialchars($sponsor['name']) . '</h3>
                <p style="font-size: 12px; color: #a3a3a3; margin: 0;">Sponsor Anda</p>
            </div>
        </div>
        
        <div style="display: flex; gap: 8px;">
            <a href="' . htmlspecialchars($register_url) . '" style="
                flex: 1;
                padding: 8px 12px;
                background: #d4af37;
                color: #1a1a1a;
                text-decoration: none;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 500;
                text-align: center;
                transition: all 0.2s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 4px;
            " onmouseover="this.style.background=\'#b8941f\'; this.style.transform=\'translateY(-1px)\'" 
               onmouseout="this.style.background=\'#d4af37\'; this.style.transform=\'translateY(0)\'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <line x1="19" y1="8" x2="19" y2="14"></line>
                    <line x1="22" y1="11" x2="16" y2="11"></line>
                </svg>
                Daftar Sekarang
            </a>
            <button onclick="toggleEpicSponsorBox()" style="
                padding: 8px;
                background: #404040;
                color: #e5e5e5;
                border: 1px solid #a3a3a3;
                border-radius: 6px;
                cursor: pointer;
                transition: all 0.2s ease;
            " onmouseover="this.style.background=\'#2d2d2d\'; this.style.borderColor=\'#d4d4d4\'" 
               onmouseout="this.style.background=\'#404040\'; this.style.borderColor=\'#a3a3a3\'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>
    
    <style>
        @media (max-width: 768px) {
            .epic-sponsor-box {
                bottom: 10px !important;
                right: 10px !important;
                left: 10px !important;
                max-width: none !important;
                padding: 12px !important;
            }
        }
        
        .epic-sponsor-box.epic-show {
            opacity: 1 !important;
            transform: translateY(0) !important;
            pointer-events: auto !important;
        }
        
        .epic-sponsor-box.epic-hidden {
            opacity: 0 !important;
            transform: translateY(20px) !important;
            pointer-events: none !important;
        }
    </style>';
}

// Generate tracking script
function generateTrackingScript($landing_page_id, $sponsor_id, $username) {
    return '
    <script>
        // Show sponsor box after delay
        setTimeout(function() {
            var sponsorBox = document.getElementById("epicSponsorBox");
            if (sponsorBox) {
                sponsorBox.classList.add("epic-show");
            }
        }, 2000);
        
        // Toggle sponsor box visibility
        function toggleEpicSponsorBox() {
            var sponsorBox = document.getElementById("epicSponsorBox");
            if (sponsorBox) {
                sponsorBox.classList.toggle("epic-hidden");
            }
        }
        
        // Track page interactions
        document.addEventListener("click", function(e) {
            // Track clicks for analytics
            if (window.gtag) {
                gtag("event", "click", {
                    "event_category": "landing_page",
                    "event_label": "user_interaction",
                    "referral_code": "' . $username . '"
                });
            }
        });
        
        // Track visit duration
        var visitStartTime = Date.now();
        
        window.addEventListener("beforeunload", function() {
            var visitDuration = Date.now() - visitStartTime;
            
            // Send visit duration to analytics if available
            if (navigator.sendBeacon) {
                navigator.sendBeacon("' . epic_url('api/track-visit') . '", JSON.stringify({
                    landing_page_id: ' . $landing_page_id . ',
                    sponsor_id: ' . $sponsor_id . ',
                    duration: visitDuration,
                    referral_code: "' . $username . '"
                }));
            }
        });
    </script>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($landing_page['page_title']) ?></title>
    
    <?php if ($landing_page['page_description']): ?>
        <meta name="description" content="<?= htmlspecialchars($landing_page['page_description']) ?>">
    <?php endif; ?>
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= epic_url($username . '/' . $landing_page['page_slug']) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($landing_page['page_title']) ?>">
    <?php if ($landing_page['page_description']): ?>
        <meta property="og:description" content="<?= htmlspecialchars($landing_page['page_description']) ?>">
    <?php endif; ?>
    <?php if ($landing_page['page_image']): ?>
        <meta property="og:image" content="<?= epic_url('uploads/landing-pages/' . $landing_page['page_image']) ?>">
    <?php endif; ?>
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= epic_url($username . '/' . $landing_page['page_slug']) ?>">
    <meta property="twitter:title" content="<?= htmlspecialchars($landing_page['page_title']) ?>">
    <?php if ($landing_page['page_description']): ?>
        <meta property="twitter:description" content="<?= htmlspecialchars($landing_page['page_description']) ?>">
    <?php endif; ?>
    <?php if ($landing_page['page_image']): ?>
        <meta property="twitter:image" content="<?= epic_url('uploads/landing-pages/' . $landing_page['page_image']) ?>">
    <?php endif; ?>
    
    <!-- Favicon -->
    <?php 
    $site_favicon = epic_setting('site_favicon');
    if ($site_favicon && file_exists(EPIC_ROOT . '/uploads/logos/' . $site_favicon)): 
    ?>
        <link rel="icon" type="image/x-icon" href="<?= epic_url('uploads/logos/' . $site_favicon) ?>">
    <?php else: ?>
        <link rel="icon" type="image/x-icon" href="<?= epic_url('themes/modern/assets/favicon.ico') ?>">
    <?php endif; ?>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #1e40af;
            --primary-dark: #1e3a8a;
            --gold: #d4af37;
            --success: #059669;
            --danger: #dc2626;
            --surface-1: #1a1a1a;
            --surface-2: #2d2d2d;
            --surface-3: #404040;
            --ink-100: #f5f5f5;
            --ink-200: #e5e5e5;
            --ink-300: #d4d4d4;
            --ink-400: #a3a3a3;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--surface-1);
            color: var(--ink-100);
            overflow: hidden;
        }
        
        .landing-container {
            position: relative;
            width: 100vw;
            height: 100vh;
        }
        
        .landing-iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }
        
        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--surface-1);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
            transition: opacity 0.3s ease;
        }
        
        .loading-spinner {
            width: 48px;
            height: 48px;
            border: 3px solid var(--surface-3);
            border-top: 3px solid var(--gold);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-text {
            margin-top: 16px;
            color: var(--ink-400);
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loadingOverlay">
            <div>
                <div class="loading-spinner"></div>
                <div class="loading-text">Loading landing page...</div>
            </div>
        </div>
        
        <!-- Main Landing Page Iframe -->
        <iframe 
            src="<?= htmlspecialchars($final_landing_url) ?>" 
            class="landing-iframe" 
            id="landingIframe"
            onload="hideLoading()"
            title="<?= htmlspecialchars($landing_page['page_title']) ?>">
        </iframe>
        
        <?= generateSponsorBox($sponsor, $username, $register_url) ?>
    </div>
    
    <script>
        // Hide loading overlay when iframe loads
        function hideLoading() {
            const loadingOverlay = document.getElementById('loadingOverlay');
            const sponsorBox = document.getElementById('epicSponsorBox');
            
            if (loadingOverlay) {
                loadingOverlay.style.opacity = '0';
                setTimeout(() => {
                    loadingOverlay.style.display = 'none';
                }, 300);
            }
            
            // Show sponsor box after a delay
            if (sponsorBox) {
                setTimeout(() => {
                    sponsorBox.classList.add('epic-show');
                }, 2000);
            }
        }
        
        // Toggle sponsor box visibility
        function toggleEpicSponsorBox() {
            const sponsorBox = document.getElementById('epicSponsorBox');
            if (sponsorBox) {
                sponsorBox.classList.toggle('epic-hidden');
            }
        }
        
        // Track page interactions
        document.addEventListener('click', function(e) {
            // Track clicks for analytics
            if (window.gtag) {
                gtag('event', 'click', {
                    'event_category': 'landing_page',
                    'event_label': 'user_interaction',
                    'referral_code': '<?= htmlspecialchars($username) ?>'
                });
            }
        });
        
        // Handle iframe communication if needed
        window.addEventListener('message', function(event) {
            // Handle messages from iframe if the landing page supports it
            if (event.data && event.data.type === 'landing_page_action') {
                switch (event.data.action) {
                    case 'register':
                        window.location.href = '<?= htmlspecialchars($register_url) ?>';
                        break;
                    case 'hide_sponsor':
                        toggleEpicSponsorBox();
                        break;
                }
            }
        });
        
        // Auto-hide loading after timeout (fallback)
        setTimeout(function() {
            hideLoading();
        }, 10000);
        
        // Track visit duration
        let visitStartTime = Date.now();
        
        window.addEventListener('beforeunload', function() {
            let visitDuration = Date.now() - visitStartTime;
            
            // Send visit duration to analytics if available
            if (navigator.sendBeacon) {
                navigator.sendBeacon('<?= epic_url('api/track-visit') ?>', JSON.stringify({
                    landing_page_id: <?= $landing_page['id'] ?>,
                    sponsor_id: <?= $sponsor['id'] ?>,
                    duration: visitDuration,
                    referral_code: '<?= htmlspecialchars($username) ?>'
                }));
            }
        });
    </script>
</body>
</html>