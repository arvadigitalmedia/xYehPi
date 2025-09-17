<?php
/**
 * EPIC Hub Performance Optimizer
 * Comprehensive performance optimization system
 */

class PerformanceOptimizer {
    private $config;
    private $cache_dir;
    private $optimized_assets = [];
    
    public function __construct() {
        $this->cache_dir = EPIC_ROOT . '/cache/performance/';
        $this->ensureCacheDirectory();
        $this->loadConfig();
    }
    
    private function ensureCacheDirectory() {
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }
    
    private function loadConfig() {
        $this->config = [
            'image_optimization' => [
                'webp_quality' => 85,
                'lazy_loading' => true,
                'responsive_images' => true,
                'aspect_ratio_preservation' => true
            ],
            'css_optimization' => [
                'inline_critical' => true,
                'preload_stylesheets' => true,
                'minify' => true,
                'remove_unused' => true
            ],
            'js_optimization' => [
                'defer_scripts' => true,
                'use_modules' => true,
                'minify' => true,
                'remove_polyfills' => true
            ],
            'cache_control' => [
                'max_age_assets' => 31536000, // 1 year
                'max_age_html' => 3600, // 1 hour
                'enable_etag' => true
            ],
            'preload_resources' => [
                'fonts' => true,
                'hero_images' => true,
                'critical_css' => true
            ]
        ];
    }
    
    /**
     * Optimize images to WebP format with lazy loading
     */
    public function optimizeImages($html) {
        // Convert images to WebP with srcset and lazy loading
        $pattern = '/<img([^>]*?)src=["\']([^"\'>]+)["\']([^>]*?)>/i';
        
        return preg_replace_callback($pattern, function($matches) {
            $attributes = $matches[1] . $matches[3];
            $src = $matches[2];
            
            // Generate WebP version
            $webp_src = $this->convertToWebP($src);
            
            // Extract dimensions if available
            $width = $this->extractAttribute($attributes, 'width');
            $height = $this->extractAttribute($attributes, 'height');
            
            // Generate responsive srcset
            $srcset = $this->generateSrcset($webp_src);
            
            // Build optimized img tag
            $optimized = '<img';
            $optimized .= ' src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 ' . ($width ?: '800') . ' ' . ($height ?: '600') . '\'%3E%3C/svg%3E"';
            $optimized .= ' data-src="' . $webp_src . '"';
            $optimized .= ' srcset="' . $srcset . '"';
            $optimized .= ' loading="lazy"';
            $optimized .= ' decoding="async"';
            
            if ($width && $height) {
                $optimized .= ' width="' . $width . '" height="' . $height . '"';
                $optimized .= ' style="aspect-ratio: ' . $width . '/' . $height . ';"';
            }
            
            $optimized .= ' ' . $attributes . '>';
            
            return $optimized;
        }, $html);
    }
    
    /**
     * Inline critical CSS and preload stylesheets
     */
    public function optimizeCSS($html) {
        // Extract and inline critical CSS
        $critical_css = $this->extractCriticalCSS();
        
        // Replace CSS links with preload + async loading
        $pattern = '/<link([^>]*?)rel=["\']stylesheet["\']([^>]*?)href=["\']([^"\'>]+)["\']([^>]*?)>/i';
        
        $html = preg_replace_callback($pattern, function($matches) {
            $href = $matches[3];
            $attributes = $matches[1] . $matches[2] . $matches[4];
            
            // Preload stylesheet
            $preload = '<link rel="preload" href="' . $href . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';
            $preload .= '<noscript><link rel="stylesheet" href="' . $href . '"></noscript>';
            
            return $preload;
        }, $html);
        
        // Inject critical CSS
        if ($critical_css) {
            $critical_style = '<style>' . $this->minifyCSS($critical_css) . '</style>';
            $html = str_replace('</head>', $critical_style . '</head>', $html);
        }
        
        return $html;
    }
    
    /**
     * Optimize JavaScript loading
     */
    public function optimizeJS($html) {
        // Move scripts to end of body with defer/module attributes
        $pattern = '/<script([^>]*?)src=["\']([^"\'>]+)["\']([^>]*?)><\/script>/i';
        
        $scripts = [];
        $html = preg_replace_callback($pattern, function($matches) use (&$scripts) {
            $src = $matches[2];
            $attributes = $matches[1] . $matches[3];
            
            // Add modern script attributes
            $optimized_script = '<script type="module" src="' . $src . '" defer';
            $optimized_script .= ' ' . $attributes . '></script>';
            
            $scripts[] = $optimized_script;
            return ''; // Remove from current position
        }, $html);
        
        // Add scripts before closing body tag
        if (!empty($scripts)) {
            $script_block = implode("\n", $scripts);
            $html = str_replace('</body>', $script_block . "\n</body>", $html);
        }
        
        return $html;
    }
    
    /**
     * Add resource preloading
     */
    public function addPreloading($html) {
        $preloads = [];
        
        // Preload fonts
        $fonts = $this->findFonts();
        foreach ($fonts as $font) {
            $preloads[] = '<link rel="preload" href="' . $font . '" as="font" type="font/woff2" crossorigin>';
        }
        
        // Preload hero images
        $hero_images = $this->findHeroImages($html);
        foreach ($hero_images as $image) {
            $preloads[] = '<link rel="preload" href="' . $image . '" as="image">';
        }
        
        if (!empty($preloads)) {
            $preload_block = implode("\n", $preloads);
            $html = str_replace('</head>', $preload_block . "\n</head>", $html);
        }
        
        return $html;
    }
    
    /**
     * Add performance monitoring script
     */
    public function addPerformanceMonitoring($html) {
        $monitoring_script = '
<script type="module">
// Performance monitoring and optimization
class PerformanceMonitor {
    constructor() {
        this.initLazyLoading();
        this.initEventOptimization();
        this.initIdleCallback();
    }
    
    initLazyLoading() {
        if (\'IntersectionObserver\' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove(\'lazy\');
                        observer.unobserve(img);
                    }
                });
            });
            
            document.querySelectorAll(\'img[data-src]\').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }
    
    initEventOptimization() {
        // Debounced scroll handler
        let scrollTimeout;
        const debouncedScroll = () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                // Scroll handling logic
            }, 16); // ~60fps
        };
        
        // Passive event listeners
        window.addEventListener(\'scroll\', debouncedScroll, { passive: true });
        window.addEventListener(\'touchstart\', () => {}, { passive: true });
        window.addEventListener(\'touchmove\', () => {}, { passive: true });
        
        // Debounced resize handler
        let resizeTimeout;
        window.addEventListener(\'resize\', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                // Resize handling logic
            }, 250);
        }, { passive: true });
    }
    
    initIdleCallback() {
        if (\'requestIdleCallback\' in window) {
            requestIdleCallback(() => {
                // Initialize non-critical features
                this.initNonCriticalFeatures();
            });
        } else {
            setTimeout(() => {
                this.initNonCriticalFeatures();
            }, 1000);
        }
    }
    
    initNonCriticalFeatures() {
        // Load analytics, social widgets, etc.
        console.log(\'Non-critical features initialized\');
    }
}

// Initialize when DOM is ready
if (document.readyState === \'loading\') {
    document.addEventListener(\'DOMContentLoaded\', () => new PerformanceMonitor());
} else {
    new PerformanceMonitor();
}
</script>';
        
        return str_replace('</body>', $monitoring_script . "\n</body>", $html);
    }
    
    // Helper methods
    private function convertToWebP($src) {
        // Implementation for WebP conversion
        $path_info = pathinfo($src);
        return $path_info['dirname'] . '/' . $path_info['filename'] . '.webp';
    }
    
    private function generateSrcset($src) {
        // Generate responsive srcset
        $base = pathinfo($src, PATHINFO_FILENAME);
        $ext = pathinfo($src, PATHINFO_EXTENSION);
        $dir = pathinfo($src, PATHINFO_DIRNAME);
        
        return $src . ' 1x, ' . $dir . '/' . $base . '@2x.' . $ext . ' 2x';
    }
    
    private function extractAttribute($attributes, $name) {
        preg_match('/' . $name . '=["\']([^"\'>]+)["\']/', $attributes, $matches);
        return $matches[1] ?? null;
    }
    
    private function extractCriticalCSS() {
        // Extract above-the-fold CSS
        $critical_files = [
            EPIC_ROOT . '/themes/modern/admin/admin.css',
            EPIC_ROOT . '/themes/modern/member/epic-components.css'
        ];
        
        $critical_css = '';
        foreach ($critical_files as $file) {
            if (file_exists($file)) {
                $css = file_get_contents($file);
                // Extract only critical selectors (first 50KB)
                $critical_css .= substr($css, 0, 51200);
            }
        }
        
        return $critical_css;
    }
    
    private function minifyCSS($css) {
        // Basic CSS minification
        $css = preg_replace('/\/\*[^*]*\*+([^\/*][^*]*\*+)*\//', '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = str_replace(['; ', ' {', '{ ', ' }', '} ', ': '], [';', '{', '{', '}', '}', ':'], $css);
        return trim($css);
    }
    
    private function findFonts() {
        // Find WOFF2 fonts in the project
        $fonts = [];
        $font_dirs = [
            EPIC_ROOT . '/themes/modern/fonts/',
            EPIC_ROOT . '/assets/fonts/'
        ];
        
        foreach ($font_dirs as $dir) {
            if (is_dir($dir)) {
                $files = glob($dir . '*.woff2');
                foreach ($files as $file) {
                    $fonts[] = str_replace(EPIC_ROOT, '', $file);
                }
            }
        }
        
        return $fonts;
    }
    
    private function findHeroImages($html) {
        // Find hero/banner images
        $hero_images = [];
        preg_match_all('/class=["\'][^"\'>]*(?:hero|banner|featured)[^"\'>]*["\'][^>]*src=["\']([^"\'>]+)["\']/', $html, $matches);
        
        if (!empty($matches[1])) {
            $hero_images = array_slice($matches[1], 0, 3); // Limit to first 3
        }
        
        return $hero_images;
    }
    
    /**
     * Apply all optimizations
     */
    public function optimize($html) {
        $html = $this->optimizeImages($html);
        $html = $this->optimizeCSS($html);
        $html = $this->optimizeJS($html);
        $html = $this->addPreloading($html);
        $html = $this->addPerformanceMonitoring($html);
        
        return $html;
    }
}

// Global function for easy access
function optimize_performance($html) {
    static $optimizer = null;
    if ($optimizer === null) {
        $optimizer = new PerformanceOptimizer();
    }
    return $optimizer->optimize($html);
}
?>