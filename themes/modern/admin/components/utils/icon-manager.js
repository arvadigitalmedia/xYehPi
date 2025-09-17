/**
 * EPIC Hub Admin Icon Manager
 * Handles SVG icon loading, caching, and optimization with sprite generation
 */

export class IconManager {
    constructor() {
        this.iconCache = new Map();
        this.spriteCache = new Map();
        this.loadingPromises = new Map();
        this.config = {
            iconPath: '/themes/modern/admin/icons',
            spritePath: '/themes/modern/admin/sprites',
            cacheExpiry: 24 * 60 * 60 * 1000, // 24 hours
            preloadCommon: true,
            generateSprites: true,
            compressionLevel: 'medium'
        };
        
        this.commonIcons = [
            'home', 'user', 'settings', 'sliders', 'bell', 'search',
            'chevron-down', 'chevron-left', 'chevron-right', 'chevron-up',
            'plus', 'minus', 'edit', 'trash', 'eye', 'eye-off',
            'check', 'x', 'alert-circle', 'info', 'help-circle',
            'download', 'upload', 'save', 'copy', 'external-link',
            'mail', 'phone', 'calendar', 'clock', 'map-pin',
            'file', 'folder', 'image', 'video', 'music',
            'star', 'heart', 'bookmark', 'tag', 'flag',
            'lock', 'unlock', 'shield', 'key', 'users',
            'grid', 'list', 'table', 'bar-chart', 'pie-chart',
            'refresh', 'loader', 'wifi', 'battery', 'signal'
        ];
        
        this.init();
    }

    /**
     * Initialize icon manager
     */
    async init() {
        this.loadCacheFromStorage();
        
        if (this.config.preloadCommon) {
            await this.preloadCommonIcons();
        }
        
        if (this.config.generateSprites) {
            await this.generateIconSprites();
        }
        
        this.setupFeatherIntegration();
        this.setupMutationObserver();
        
        console.log('Icon Manager initialized');
    }

    /**
     * Load icon cache from localStorage
     */
    loadCacheFromStorage() {
        try {
            const cached = localStorage.getItem('epic-icon-cache');
            if (cached) {
                const data = JSON.parse(cached);
                const now = Date.now();
                
                // Filter out expired entries
                Object.entries(data).forEach(([key, entry]) => {
                    if (now - entry.timestamp < this.config.cacheExpiry) {
                        this.iconCache.set(key, entry.svg);
                    }
                });
                
                console.log(`Loaded ${this.iconCache.size} icons from cache`);
            }
        } catch (error) {
            console.warn('Failed to load icon cache:', error);
        }
    }

    /**
     * Save icon cache to localStorage
     */
    saveCacheToStorage() {
        try {
            const data = {};
            const now = Date.now();
            
            this.iconCache.forEach((svg, key) => {
                data[key] = {
                    svg,
                    timestamp: now
                };
            });
            
            localStorage.setItem('epic-icon-cache', JSON.stringify(data));
        } catch (error) {
            console.warn('Failed to save icon cache:', error);
        }
    }

    /**
     * Preload common icons
     */
    async preloadCommonIcons() {
        console.log('Preloading common icons...');
        
        const promises = this.commonIcons.map(iconName => 
            this.loadIcon(iconName).catch(error => 
                console.warn(`Failed to preload icon: ${iconName}`, error)
            )
        );
        
        await Promise.allSettled(promises);
        this.saveCacheToStorage();
        
        console.log(`Preloaded ${this.iconCache.size} common icons`);
    }

    /**
     * Load single icon
     */
    async loadIcon(iconName) {
        // Return cached icon if available
        if (this.iconCache.has(iconName)) {
            return this.iconCache.get(iconName);
        }
        
        // Return existing loading promise if icon is being loaded
        if (this.loadingPromises.has(iconName)) {
            return this.loadingPromises.get(iconName);
        }
        
        // Create loading promise
        const loadingPromise = this.fetchIcon(iconName);
        this.loadingPromises.set(iconName, loadingPromise);
        
        try {
            const svg = await loadingPromise;
            this.iconCache.set(iconName, svg);
            return svg;
        } finally {
            this.loadingPromises.delete(iconName);
        }
    }

    /**
     * Fetch icon from server
     */
    async fetchIcon(iconName) {
        const url = `${this.config.iconPath}/${iconName}.svg`;
        
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            let svg = await response.text();
            
            // Optimize SVG
            svg = this.optimizeSvg(svg, iconName);
            
            return svg;
        } catch (error) {
            console.warn(`Failed to fetch icon: ${iconName}`, error);
            
            // Try fallback to Feather icon
            return this.getFallbackIcon(iconName);
        }
    }

    /**
     * Optimize SVG content
     */
    optimizeSvg(svg, iconName) {
        // Remove XML declaration and DOCTYPE
        svg = svg.replace(/<\?xml[^>]*>/, '');
        svg = svg.replace(/<!DOCTYPE[^>]*>/, '');
        
        // Remove comments
        svg = svg.replace(/<!--[\s\S]*?-->/g, '');
        
        // Remove unnecessary whitespace
        svg = svg.replace(/\s+/g, ' ').trim();
        
        // Add consistent attributes
        if (!svg.includes('class=')) {
            svg = svg.replace('<svg', `<svg class="icon icon-${iconName}"`);
        }
        
        // Ensure proper dimensions
        if (!svg.includes('width=') && !svg.includes('height=')) {
            svg = svg.replace('<svg', '<svg width="24" height="24"');
        }
        
        // Add aria-hidden for accessibility
        if (!svg.includes('aria-hidden')) {
            svg = svg.replace('<svg', '<svg aria-hidden="true"');
        }
        
        return svg;
    }

    /**
     * Get fallback icon (Feather icon or generic)
     */
    getFallbackIcon(iconName) {
        // Try to get Feather icon
        if (window.feather && window.feather.icons[iconName]) {
            return window.feather.icons[iconName].toSvg({
                class: `icon icon-${iconName}`,
                'aria-hidden': 'true'
            });
        }
        
        // Return generic icon
        return `<svg class="icon icon-${iconName}" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>`;
    }

    /**
     * Generate icon sprites for better performance
     */
    async generateIconSprites() {
        if (this.iconCache.size === 0) {
            return;
        }
        
        console.log('Generating icon sprites...');
        
        const spriteContent = this.createSpriteContent();
        this.injectSprite(spriteContent);
        
        // Cache sprite
        this.spriteCache.set('main', spriteContent);
        
        console.log('Icon sprites generated');
    }

    /**
     * Create sprite SVG content
     */
    createSpriteContent() {
        let spriteContent = '<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">';
        
        this.iconCache.forEach((svg, iconName) => {
            // Extract the inner content of the SVG
            const match = svg.match(/<svg[^>]*>([\s\S]*?)<\/svg>/);
            if (match) {
                const innerContent = match[1];
                spriteContent += `<symbol id="icon-${iconName}" viewBox="0 0 24 24">${innerContent}</symbol>`;
            }
        });
        
        spriteContent += '</svg>';
        return spriteContent;
    }

    /**
     * Inject sprite into DOM
     */
    injectSprite(spriteContent) {
        // Remove existing sprite
        const existingSprite = document.getElementById('epic-icon-sprite');
        if (existingSprite) {
            existingSprite.remove();
        }
        
        // Create new sprite element
        const spriteElement = document.createElement('div');
        spriteElement.id = 'epic-icon-sprite';
        spriteElement.innerHTML = spriteContent;
        
        // Insert at the beginning of body
        document.body.insertBefore(spriteElement, document.body.firstChild);
    }

    /**
     * Setup Feather icons integration
     */
    setupFeatherIntegration() {
        // Override feather.replace to use our cached icons
        if (window.feather) {
            const originalReplace = window.feather.replace;
            
            window.feather.replace = (options = {}) => {
                // First, try to replace with cached icons
                this.replaceFeatherIcons(options);
                
                // Then call original replace for any remaining icons
                originalReplace.call(window.feather, options);
            };
        }
    }

    /**
     * Replace Feather icons with cached versions
     */
    replaceFeatherIcons(options = {}) {
        const elements = document.querySelectorAll('[data-feather]');
        
        elements.forEach(element => {
            const iconName = element.getAttribute('data-feather');
            if (this.iconCache.has(iconName)) {
                const svg = this.iconCache.get(iconName);
                
                // Apply options to SVG
                let modifiedSvg = this.applySvgOptions(svg, options, element);
                
                // Replace element
                element.outerHTML = modifiedSvg;
            }
        });
    }

    /**
     * Apply options to SVG
     */
    applySvgOptions(svg, options, element) {
        let modifiedSvg = svg;
        
        // Apply width and height
        const width = element.getAttribute('width') || options.width || '24';
        const height = element.getAttribute('height') || options.height || '24';
        
        modifiedSvg = modifiedSvg.replace(/width="[^"]*"/, `width="${width}"`);
        modifiedSvg = modifiedSvg.replace(/height="[^"]*"/, `height="${height}"`);
        
        // Apply classes
        const elementClasses = element.className;
        const optionClasses = options.class || '';
        const allClasses = [elementClasses, optionClasses].filter(Boolean).join(' ');
        
        if (allClasses) {
            modifiedSvg = modifiedSvg.replace(/class="[^"]*"/, `class="${allClasses}"`);
        }
        
        // Apply other attributes from element
        Array.from(element.attributes).forEach(attr => {
            if (!['data-feather', 'width', 'height', 'class'].includes(attr.name)) {
                const attrRegex = new RegExp(`${attr.name}="[^"]*"`, 'g');
                if (modifiedSvg.match(attrRegex)) {
                    modifiedSvg = modifiedSvg.replace(attrRegex, `${attr.name}="${attr.value}"`);
                } else {
                    modifiedSvg = modifiedSvg.replace('<svg', `<svg ${attr.name}="${attr.value}"`);
                }
            }
        });
        
        return modifiedSvg;
    }

    /**
     * Setup mutation observer to handle dynamically added icons
     */
    setupMutationObserver() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        // Check if the node itself has data-feather
                        if (node.hasAttribute && node.hasAttribute('data-feather')) {
                            this.replaceIcon(node);
                        }
                        
                        // Check for data-feather in child elements
                        if (node.querySelectorAll) {
                            const featherElements = node.querySelectorAll('[data-feather]');
                            featherElements.forEach(element => this.replaceIcon(element));
                        }
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        this.mutationObserver = observer;
    }

    /**
     * Replace single icon element
     */
    async replaceIcon(element) {
        const iconName = element.getAttribute('data-feather');
        if (!iconName) return;
        
        try {
            const svg = await this.loadIcon(iconName);
            const modifiedSvg = this.applySvgOptions(svg, {}, element);
            element.outerHTML = modifiedSvg;
        } catch (error) {
            console.warn(`Failed to replace icon: ${iconName}`, error);
        }
    }

    /**
     * Get icon as SVG string
     */
    async getIcon(iconName, options = {}) {
        const svg = await this.loadIcon(iconName);
        return this.applySvgOptions(svg, options, { attributes: [] });
    }

    /**
     * Get icon as sprite reference
     */
    getIconSprite(iconName, options = {}) {
        const width = options.width || '24';
        const height = options.height || '24';
        const className = options.class || `icon icon-${iconName}`;
        
        return `<svg class="${className}" width="${width}" height="${height}" aria-hidden="true">
            <use href="#icon-${iconName}"></use>
        </svg>`;
    }

    /**
     * Preload specific icons
     */
    async preloadIcons(iconNames) {
        const promises = iconNames.map(iconName => 
            this.loadIcon(iconName).catch(error => 
                console.warn(`Failed to preload icon: ${iconName}`, error)
            )
        );
        
        await Promise.allSettled(promises);
        this.saveCacheToStorage();
        
        console.log(`Preloaded ${iconNames.length} icons`);
    }

    /**
     * Clear icon cache
     */
    clearCache() {
        this.iconCache.clear();
        this.spriteCache.clear();
        localStorage.removeItem('epic-icon-cache');
        
        console.log('Icon cache cleared');
    }

    /**
     * Get cache statistics
     */
    getStats() {
        const cacheSize = JSON.stringify(Array.from(this.iconCache.entries())).length;
        
        return {
            cachedIcons: this.iconCache.size,
            loadingIcons: this.loadingPromises.size,
            cacheSize: `${(cacheSize / 1024).toFixed(2)} KB`,
            sprites: this.spriteCache.size,
            commonIconsLoaded: this.commonIcons.filter(icon => this.iconCache.has(icon)).length
        };
    }

    /**
     * Update configuration
     */
    updateConfig(newConfig) {
        this.config = { ...this.config, ...newConfig };
        
        // Reinitialize if paths changed
        if (newConfig.iconPath || newConfig.spritePath) {
            this.clearCache();
            this.init();
        }
    }

    /**
     * Export icons for offline use
     */
    exportIcons() {
        const exportData = {
            icons: Object.fromEntries(this.iconCache),
            timestamp: Date.now(),
            version: '1.0.0'
        };
        
        const blob = new Blob([JSON.stringify(exportData, null, 2)], {
            type: 'application/json'
        });
        
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'epic-icons-export.json';
        a.click();
        
        URL.revokeObjectURL(url);
    }

    /**
     * Import icons from exported data
     */
    async importIcons(file) {
        try {
            const text = await file.text();
            const data = JSON.parse(text);
            
            if (data.icons) {
                Object.entries(data.icons).forEach(([name, svg]) => {
                    this.iconCache.set(name, svg);
                });
                
                this.saveCacheToStorage();
                
                if (this.config.generateSprites) {
                    await this.generateIconSprites();
                }
                
                console.log(`Imported ${Object.keys(data.icons).length} icons`);
            }
        } catch (error) {
            console.error('Failed to import icons:', error);
        }
    }

    /**
     * Destroy icon manager
     */
    destroy() {
        if (this.mutationObserver) {
            this.mutationObserver.disconnect();
        }
        
        this.saveCacheToStorage();
        this.iconCache.clear();
        this.spriteCache.clear();
        this.loadingPromises.clear();
        
        console.log('Icon Manager destroyed');
    }
}

// Export for use in other modules
export default IconManager;