<?php
/**
 * EPIC Hub Image Optimizer
 * Converts images to WebP format and optimizes for performance
 */

class ImageOptimizer {
    private $upload_dir;
    private $webp_quality;
    private $supported_formats;
    
    public function __construct() {
        $this->upload_dir = EPIC_ROOT . '/uploads/';
        $this->webp_quality = 85;
        $this->supported_formats = ['jpg', 'jpeg', 'png', 'gif'];
    }
    
    /**
     * Convert image to WebP format
     */
    public function convertToWebP($source_path, $output_path = null) {
        if (!file_exists($source_path)) {
            return false;
        }
        
        $path_info = pathinfo($source_path);
        $extension = strtolower($path_info['extension']);
        
        if (!in_array($extension, $this->supported_formats)) {
            return false;
        }
        
        if (!$output_path) {
            $output_path = $path_info['dirname'] . '/' . $path_info['filename'] . '.webp';
        }
        
        // Check if WebP already exists and is newer
        if (file_exists($output_path) && filemtime($output_path) > filemtime($source_path)) {
            return $output_path;
        }
        
        $image = null;
        
        // Create image resource based on format
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($source_path);
                break;
            case 'png':
                $image = imagecreatefrompng($source_path);
                // Preserve transparency
                imagealphablending($image, false);
                imagesavealpha($image, true);
                break;
            case 'gif':
                $image = imagecreatefromgif($source_path);
                break;
        }
        
        if (!$image) {
            return false;
        }
        
        // Convert to WebP
        $success = imagewebp($image, $output_path, $this->webp_quality);
        imagedestroy($image);
        
        return $success ? $output_path : false;
    }
    
    /**
     * Generate responsive image sizes
     */
    public function generateResponsiveSizes($source_path, $sizes = [400, 800, 1200]) {
        if (!file_exists($source_path)) {
            return [];
        }
        
        $path_info = pathinfo($source_path);
        $generated_sizes = [];
        
        // Get original dimensions
        list($original_width, $original_height) = getimagesize($source_path);
        
        foreach ($sizes as $width) {
            // Skip if requested size is larger than original
            if ($width > $original_width) {
                continue;
            }
            
            $height = intval(($original_height * $width) / $original_width);
            $output_path = $path_info['dirname'] . '/' . $path_info['filename'] . '-' . $width . 'w.' . $path_info['extension'];
            $webp_path = $path_info['dirname'] . '/' . $path_info['filename'] . '-' . $width . 'w.webp';
            
            // Resize image
            if ($this->resizeImage($source_path, $output_path, $width, $height)) {
                $generated_sizes[$width] = [
                    'original' => $output_path,
                    'webp' => $this->convertToWebP($output_path, $webp_path),
                    'width' => $width,
                    'height' => $height
                ];
            }
        }
        
        return $generated_sizes;
    }
    
    /**
     * Resize image to specified dimensions
     */
    private function resizeImage($source_path, $output_path, $new_width, $new_height) {
        $path_info = pathinfo($source_path);
        $extension = strtolower($path_info['extension']);
        
        // Get original dimensions
        list($original_width, $original_height) = getimagesize($source_path);
        
        // Create image resource
        $source_image = null;
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $source_image = imagecreatefromjpeg($source_path);
                break;
            case 'png':
                $source_image = imagecreatefrompng($source_path);
                break;
            case 'gif':
                $source_image = imagecreatefromgif($source_path);
                break;
        }
        
        if (!$source_image) {
            return false;
        }
        
        // Create new image
        $new_image = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency for PNG
        if ($extension === 'png') {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
        }
        
        // Resize
        imagecopyresampled(
            $new_image, $source_image,
            0, 0, 0, 0,
            $new_width, $new_height,
            $original_width, $original_height
        );
        
        // Save resized image
        $success = false;
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $success = imagejpeg($new_image, $output_path, 90);
                break;
            case 'png':
                $success = imagepng($new_image, $output_path, 8);
                break;
            case 'gif':
                $success = imagegif($new_image, $output_path);
                break;
        }
        
        imagedestroy($source_image);
        imagedestroy($new_image);
        
        return $success;
    }
    
    /**
     * Optimize existing images in upload directory
     */
    public function optimizeExistingImages($directory = null) {
        if (!$directory) {
            $directory = $this->upload_dir;
        }
        
        $optimized = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $extension = strtolower($file->getExtension());
                if (in_array($extension, $this->supported_formats)) {
                    $source_path = $file->getPathname();
                    $webp_path = $this->convertToWebP($source_path);
                    
                    if ($webp_path) {
                        $optimized[] = [
                            'original' => $source_path,
                            'webp' => $webp_path,
                            'size_reduction' => $this->calculateSizeReduction($source_path, $webp_path)
                        ];
                        
                        // Generate responsive sizes
                        $responsive_sizes = $this->generateResponsiveSizes($source_path);
                        if (!empty($responsive_sizes)) {
                            $optimized[count($optimized) - 1]['responsive'] = $responsive_sizes;
                        }
                    }
                }
            }
        }
        
        return $optimized;
    }
    
    /**
     * Calculate size reduction percentage
     */
    private function calculateSizeReduction($original_path, $webp_path) {
        $original_size = filesize($original_path);
        $webp_size = filesize($webp_path);
        
        if ($original_size === 0) {
            return 0;
        }
        
        return round((($original_size - $webp_size) / $original_size) * 100, 2);
    }
    
    /**
     * Generate HTML picture element with WebP support
     */
    public function generatePictureElement($image_path, $alt_text = '', $attributes = []) {
        $path_info = pathinfo($image_path);
        $webp_path = $path_info['dirname'] . '/' . $path_info['filename'] . '.webp';
        
        // Get image dimensions
        list($width, $height) = getimagesize($image_path);
        
        // Build attributes
        $attr_string = '';
        foreach ($attributes as $key => $value) {
            $attr_string .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        
        // Generate responsive sizes if they exist
        $responsive_webp = [];
        $responsive_original = [];
        
        foreach ([400, 800, 1200] as $size) {
            $responsive_webp_path = $path_info['dirname'] . '/' . $path_info['filename'] . '-' . $size . 'w.webp';
            $responsive_original_path = $path_info['dirname'] . '/' . $path_info['filename'] . '-' . $size . 'w.' . $path_info['extension'];
            
            if (file_exists($responsive_webp_path)) {
                $responsive_webp[] = $responsive_webp_path . ' ' . $size . 'w';
            }
            
            if (file_exists($responsive_original_path)) {
                $responsive_original[] = $responsive_original_path . ' ' . $size . 'w';
            }
        }
        
        $html = '<picture>';
        
        // WebP source with responsive sizes
        if (file_exists($webp_path)) {
            $webp_srcset = !empty($responsive_webp) ? implode(', ', $responsive_webp) : $webp_path;
            $html .= '<source srcset="' . $webp_srcset . '" type="image/webp">';
        }
        
        // Original format source with responsive sizes
        $original_srcset = !empty($responsive_original) ? implode(', ', $responsive_original) : $image_path;
        $html .= '<source srcset="' . $original_srcset . '" type="image/' . $path_info['extension'] . '">';
        
        // Fallback img tag
        $html .= '<img src="' . $image_path . '" alt="' . htmlspecialchars($alt_text) . '"';
        $html .= ' width="' . $width . '" height="' . $height . '"';
        $html .= ' loading="lazy" decoding="async"';
        $html .= $attr_string;
        $html .= '>';
        
        $html .= '</picture>';
        
        return $html;
    }
    
    /**
     * Clean up old image versions
     */
    public function cleanupOldVersions($keep_days = 30) {
        $cutoff_time = time() - ($keep_days * 24 * 60 * 60);
        $deleted = [];
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->upload_dir)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getMTime() < $cutoff_time) {
                $filename = $file->getFilename();
                
                // Only delete generated responsive versions and WebP files
                if (preg_match('/-\d+w\.(webp|jpg|jpeg|png|gif)$/', $filename) || 
                    preg_match('/\.webp$/', $filename)) {
                    
                    if (unlink($file->getPathname())) {
                        $deleted[] = $file->getPathname();
                    }
                }
            }
        }
        
        return $deleted;
    }
}

// Global helper functions
function optimize_image($image_path) {
    static $optimizer = null;
    if ($optimizer === null) {
        $optimizer = new ImageOptimizer();
    }
    return $optimizer->convertToWebP($image_path);
}

function generate_responsive_image($image_path, $alt_text = '', $attributes = []) {
    static $optimizer = null;
    if ($optimizer === null) {
        $optimizer = new ImageOptimizer();
    }
    return $optimizer->generatePictureElement($image_path, $alt_text, $attributes);
}

// CLI script for batch optimization
if (php_sapi_name() === 'cli' && isset($argv[1]) && $argv[1] === 'optimize') {
    echo "Starting image optimization...\n";
    
    $optimizer = new ImageOptimizer();
    $results = $optimizer->optimizeExistingImages();
    
    echo "Optimized " . count($results) . " images:\n";
    
    $total_savings = 0;
    foreach ($results as $result) {
        echo "- " . basename($result['original']) . " -> " . $result['size_reduction'] . "% reduction\n";
        $total_savings += $result['size_reduction'];
    }
    
    if (count($results) > 0) {
        echo "Average size reduction: " . round($total_savings / count($results), 2) . "%\n";
    }
    
    echo "Optimization complete!\n";
}
?>