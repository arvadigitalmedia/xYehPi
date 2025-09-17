/**
 * EPIC Hub Admin Lazy Loader
 * Handles lazy loading of components, images, and content with intersection observer
 */

export class LazyLoader {
    constructor() {
        this.observers = new Map();
        this.loadedComponents = new Set();
        this.loadingComponents = new Set();
        this.componentRegistry = new Map();
        this.config = {
            rootMargin: '50px',
            threshold: 0.1,
            imageRootMargin: '100px',
            componentRootMargin: '200px'
        };
        
        this.init();
    }

    /**
     * Initialize lazy loader
     */
    init() {
        this.setupImageLazyLoading();
        this.setupComponentLazyLoading();
        this.setupContentLazyLoading();
        this.registerDefaultComponents();
        
        console.log('Lazy Loader initialized');
    }

    /**
     * Setup lazy loading for images
     */
    setupImageLazyLoading() {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadImage(entry.target);
                    imageObserver.unobserve(entry.target);
                }
            });
        }, {
            rootMargin: this.config.imageRootMargin,
            threshold: this.config.threshold
        });

        // Observe existing lazy images
        document.querySelectorAll('img[data-src], picture[data-src]').forEach(img => {
            imageObserver.observe(img);
        });

        this.observers.set('images', imageObserver);
    }

    /**
     * Setup lazy loading for components
     */
    setupComponentLazyLoading() {
        const componentObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadComponent(entry.target);
                    componentObserver.unobserve(entry.target);
                }
            });
        }, {
            rootMargin: this.config.componentRootMargin,
            threshold: this.config.threshold
        });

        // Observe existing lazy components
        document.querySelectorAll('[data-lazy-component]').forEach(element => {
            componentObserver.observe(element);
        });

        this.observers.set('components', componentObserver);
    }

    /**
     * Setup lazy loading for content sections
     */
    setupContentLazyLoading() {
        const contentObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadContent(entry.target);
                    contentObserver.unobserve(entry.target);
                }
            });
        }, {
            rootMargin: this.config.rootMargin,
            threshold: this.config.threshold
        });

        // Observe existing lazy content
        document.querySelectorAll('[data-lazy-content]').forEach(element => {
            contentObserver.observe(element);
        });

        this.observers.set('content', contentObserver);
    }

    /**
     * Load image with fallback support
     */
    async loadImage(element) {
        const isImg = element.tagName === 'IMG';
        const isPicture = element.tagName === 'PICTURE';
        
        try {
            if (isImg) {
                await this.loadSingleImage(element);
            } else if (isPicture) {
                await this.loadPictureElement(element);
            }
            
            element.classList.add('loaded');
            element.classList.remove('loading');
        } catch (error) {
            console.warn('Failed to load image:', error);
            this.handleImageError(element);
        }
    }

    /**
     * Load single image element
     */
    async loadSingleImage(img) {
        return new Promise((resolve, reject) => {
            const src = img.dataset.src;
            if (!src) {
                reject(new Error('No data-src attribute found'));
                return;
            }

            img.classList.add('loading');
            
            const tempImg = new Image();
            tempImg.onload = () => {
                img.src = src;
                img.removeAttribute('data-src');
                resolve();
            };
            tempImg.onerror = () => {
                reject(new Error(`Failed to load image: ${src}`));
            };
            tempImg.src = src;
        });
    }

    /**
     * Load picture element with multiple sources
     */
    async loadPictureElement(picture) {
        const sources = picture.querySelectorAll('source[data-srcset]');
        const img = picture.querySelector('img[data-src]');
        
        if (!img) {
            throw new Error('No img element found in picture');
        }

        picture.classList.add('loading');

        // Load source elements
        sources.forEach(source => {
            const srcset = source.dataset.srcset;
            if (srcset) {
                source.srcset = srcset;
                source.removeAttribute('data-srcset');
            }
        });

        // Load main image
        await this.loadSingleImage(img);
    }

    /**
     * Handle image loading errors
     */
    handleImageError(element) {
        element.classList.add('error');
        element.classList.remove('loading');
        
        // Set fallback image if available
        const fallback = element.dataset.fallback;
        if (fallback && element.tagName === 'IMG') {
            element.src = fallback;
        }
    }

    /**
     * Load component dynamically
     */
    async loadComponent(element) {
        const componentName = element.dataset.lazyComponent;
        const componentOptions = element.dataset.componentOptions;
        
        if (!componentName) {
            console.warn('No component name specified for lazy loading');
            return;
        }

        if (this.loadingComponents.has(componentName)) {
            // Component is already loading, wait for it
            await this.waitForComponent(componentName);
            return;
        }

        if (this.loadedComponents.has(componentName)) {
            // Component already loaded, just initialize
            this.initializeComponent(element, componentName, componentOptions);
            return;
        }

        try {
            this.loadingComponents.add(componentName);
            element.classList.add('loading');
            
            await this.fetchComponent(componentName);
            this.initializeComponent(element, componentName, componentOptions);
            
            this.loadedComponents.add(componentName);
            element.classList.add('loaded');
            element.classList.remove('loading');
            
            console.log(`Loaded component: ${componentName}`);
        } catch (error) {
            console.error(`Failed to load component: ${componentName}`, error);
            element.classList.add('error');
            element.classList.remove('loading');
        } finally {
            this.loadingComponents.delete(componentName);
        }
    }

    /**
     * Fetch component from server or registry
     */
    async fetchComponent(componentName) {
        // Check if component is registered
        if (this.componentRegistry.has(componentName)) {
            return this.componentRegistry.get(componentName);
        }

        // Try to import component module
        try {
            const module = await import(`/themes/modern/admin/components/${componentName}.js`);
            const component = module.default || module[componentName];
            
            if (component) {
                this.componentRegistry.set(componentName, component);
                return component;
            }
        } catch (importError) {
            console.warn(`Failed to import component module: ${componentName}`, importError);
        }

        // Fallback to fetch component HTML/JS
        const response = await fetch(`/themes/modern/admin/components/${componentName}.html`);
        if (!response.ok) {
            throw new Error(`Failed to fetch component: ${response.statusText}`);
        }

        const html = await response.text();
        this.componentRegistry.set(componentName, { html });
        return { html };
    }

    /**
     * Initialize component in element
     */
    initializeComponent(element, componentName, options) {
        const component = this.componentRegistry.get(componentName);
        if (!component) {
            console.warn(`Component not found in registry: ${componentName}`);
            return;
        }

        try {
            const parsedOptions = options ? JSON.parse(options) : {};
            
            if (component.html) {
                // HTML-based component
                element.innerHTML = component.html;
                
                // Initialize any scripts in the component
                const scripts = element.querySelectorAll('script');
                scripts.forEach(script => {
                    const newScript = document.createElement('script');
                    newScript.textContent = script.textContent;
                    script.parentNode.replaceChild(newScript, script);
                });
            } else if (typeof component === 'function') {
                // Function-based component
                component(element, parsedOptions);
            } else if (component.init) {
                // Object-based component with init method
                component.init(element, parsedOptions);
            }
            
            // Trigger custom event
            element.dispatchEvent(new CustomEvent('componentLoaded', {
                detail: { componentName, options: parsedOptions }
            }));
        } catch (error) {
            console.error(`Failed to initialize component: ${componentName}`, error);
        }
    }

    /**
     * Wait for component to finish loading
     */
    async waitForComponent(componentName) {
        return new Promise((resolve) => {
            const checkLoaded = () => {
                if (this.loadedComponents.has(componentName) || !this.loadingComponents.has(componentName)) {
                    resolve();
                } else {
                    setTimeout(checkLoaded, 100);
                }
            };
            checkLoaded();
        });
    }

    /**
     * Load content dynamically
     */
    async loadContent(element) {
        const contentUrl = element.dataset.lazyContent;
        const contentType = element.dataset.contentType || 'html';
        
        if (!contentUrl) {
            console.warn('No content URL specified for lazy loading');
            return;
        }

        try {
            element.classList.add('loading');
            
            const response = await fetch(contentUrl);
            if (!response.ok) {
                throw new Error(`Failed to fetch content: ${response.statusText}`);
            }

            let content;
            switch (contentType) {
                case 'json':
                    content = await response.json();
                    this.renderJsonContent(element, content);
                    break;
                case 'text':
                    content = await response.text();
                    element.textContent = content;
                    break;
                case 'html':
                default:
                    content = await response.text();
                    element.innerHTML = content;
                    break;
            }
            
            element.classList.add('loaded');
            element.classList.remove('loading');
            
            // Re-observe any new lazy elements in the loaded content
            this.observeNewElements(element);
            
            console.log(`Loaded content from: ${contentUrl}`);
        } catch (error) {
            console.error(`Failed to load content from: ${contentUrl}`, error);
            element.classList.add('error');
            element.classList.remove('loading');
            element.innerHTML = '<div class="error-message">Failed to load content</div>';
        }
    }

    /**
     * Render JSON content using template
     */
    renderJsonContent(element, data) {
        const template = element.dataset.template;
        if (!template) {
            element.textContent = JSON.stringify(data, null, 2);
            return;
        }

        try {
            // Simple template rendering (replace {{key}} with data[key])
            let html = template;
            const regex = /\{\{([^}]+)\}\}/g;
            html = html.replace(regex, (match, key) => {
                const value = this.getNestedValue(data, key.trim());
                return value !== undefined ? value : match;
            });
            
            element.innerHTML = html;
        } catch (error) {
            console.error('Failed to render JSON content:', error);
            element.textContent = JSON.stringify(data, null, 2);
        }
    }

    /**
     * Get nested value from object using dot notation
     */
    getNestedValue(obj, path) {
        return path.split('.').reduce((current, key) => {
            return current && current[key] !== undefined ? current[key] : undefined;
        }, obj);
    }

    /**
     * Observe new elements that were added to the DOM
     */
    observeNewElements(container) {
        // Observe new lazy images
        const newImages = container.querySelectorAll('img[data-src], picture[data-src]');
        const imageObserver = this.observers.get('images');
        if (imageObserver) {
            newImages.forEach(img => imageObserver.observe(img));
        }

        // Observe new lazy components
        const newComponents = container.querySelectorAll('[data-lazy-component]');
        const componentObserver = this.observers.get('components');
        if (componentObserver) {
            newComponents.forEach(element => componentObserver.observe(element));
        }

        // Observe new lazy content
        const newContent = container.querySelectorAll('[data-lazy-content]');
        const contentObserver = this.observers.get('content');
        if (contentObserver) {
            newContent.forEach(element => contentObserver.observe(element));
        }
    }

    /**
     * Register default components
     */
    registerDefaultComponents() {
        // Register common admin components
        this.registerComponent('data-table', {
            init: (element, options) => {
                // Initialize data table component
                console.log('Initializing data table', options);
            }
        });

        this.registerComponent('chart', {
            init: (element, options) => {
                // Initialize chart component
                console.log('Initializing chart', options);
            }
        });

        this.registerComponent('modal', {
            init: (element, options) => {
                // Initialize modal component
                console.log('Initializing modal', options);
            }
        });
    }

    /**
     * Register a component
     */
    registerComponent(name, component) {
        this.componentRegistry.set(name, component);
    }

    /**
     * Preload components
     */
    async preloadComponents(componentNames) {
        const promises = componentNames.map(async (name) => {
            try {
                await this.fetchComponent(name);
                this.loadedComponents.add(name);
                console.log(`Preloaded component: ${name}`);
            } catch (error) {
                console.warn(`Failed to preload component: ${name}`, error);
            }
        });

        await Promise.allSettled(promises);
    }

    /**
     * Force load all visible lazy elements
     */
    loadAllVisible() {
        // Load all visible images
        document.querySelectorAll('img[data-src], picture[data-src]').forEach(element => {
            const rect = element.getBoundingClientRect();
            if (rect.top < window.innerHeight && rect.bottom > 0) {
                this.loadImage(element);
            }
        });

        // Load all visible components
        document.querySelectorAll('[data-lazy-component]').forEach(element => {
            const rect = element.getBoundingClientRect();
            if (rect.top < window.innerHeight && rect.bottom > 0) {
                this.loadComponent(element);
            }
        });

        // Load all visible content
        document.querySelectorAll('[data-lazy-content]').forEach(element => {
            const rect = element.getBoundingClientRect();
            if (rect.top < window.innerHeight && rect.bottom > 0) {
                this.loadContent(element);
            }
        });
    }

    /**
     * Update configuration
     */
    updateConfig(newConfig) {
        this.config = { ...this.config, ...newConfig };
        
        // Recreate observers with new config
        this.destroy();
        this.init();
    }

    /**
     * Get loading statistics
     */
    getStats() {
        return {
            loadedComponents: this.loadedComponents.size,
            loadingComponents: this.loadingComponents.size,
            registeredComponents: this.componentRegistry.size,
            activeObservers: this.observers.size
        };
    }

    /**
     * Destroy lazy loader
     */
    destroy() {
        // Disconnect all observers
        this.observers.forEach(observer => observer.disconnect());
        this.observers.clear();
        
        console.log('Lazy Loader destroyed');
    }
}

// Export for use in other modules
export default LazyLoader;