/**
 * EPIC Hub Admin - Optimized JavaScript
 * Modern ES6+ with performance optimizations
 */

// Performance monitoring
class PerformanceMonitor {
    constructor() {
        this.metrics = {
            loadTime: 0,
            domReady: 0,
            firstPaint: 0,
            firstContentfulPaint: 0,
            largestContentfulPaint: 0,
            cumulativeLayoutShift: 0
        };
        
        this.init();
    }
    
    init() {
        // Measure page load performance
        if ('performance' in window) {
            window.addEventListener('load', () => {
                this.measurePerformance();
            });
        }
        
        // Observe layout shifts
        if ('PerformanceObserver' in window) {
            this.observeLayoutShifts();
            this.observePaintMetrics();
        }
    }
    
    measurePerformance() {
        const navigation = performance.getEntriesByType('navigation')[0];
        if (navigation) {
            this.metrics.loadTime = navigation.loadEventEnd - navigation.loadEventStart;
            this.metrics.domReady = navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart;
        }
    }
    
    observeLayoutShifts() {
        const observer = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                if (!entry.hadRecentInput) {
                    this.metrics.cumulativeLayoutShift += entry.value;
                }
            }
        });
        
        observer.observe({ type: 'layout-shift', buffered: true });
    }
    
    observePaintMetrics() {
        const observer = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                if (entry.name === 'first-paint') {
                    this.metrics.firstPaint = entry.startTime;
                } else if (entry.name === 'first-contentful-paint') {
                    this.metrics.firstContentfulPaint = entry.startTime;
                } else if (entry.entryType === 'largest-contentful-paint') {
                    this.metrics.largestContentfulPaint = entry.startTime;
                }
            }
        });
        
        observer.observe({ type: 'paint', buffered: true });
        observer.observe({ type: 'largest-contentful-paint', buffered: true });
    }
    
    getMetrics() {
        return this.metrics;
    }
}

// Optimized image lazy loading
class LazyImageLoader {
    constructor() {
        this.imageObserver = null;
        this.init();
    }
    
    init() {
        if ('IntersectionObserver' in window) {
            this.imageObserver = new IntersectionObserver(
                this.handleIntersection.bind(this),
                {
                    rootMargin: '50px 0px',
                    threshold: 0.01
                }
            );
            
            this.observeImages();
        } else {
            // Fallback for older browsers
            this.loadAllImages();
        }
    }
    
    observeImages() {
        const lazyImages = document.querySelectorAll('img[data-src], img[loading="lazy"]');
        lazyImages.forEach(img => {
            this.imageObserver.observe(img);
        });
    }
    
    handleIntersection(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                this.loadImage(entry.target);
                this.imageObserver.unobserve(entry.target);
            }
        });
    }
    
    loadImage(img) {
        if (img.dataset.src) {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        }
        
        img.classList.add('loaded');
        
        // Handle srcset for responsive images
        if (img.dataset.srcset) {
            img.srcset = img.dataset.srcset;
            img.removeAttribute('data-srcset');
        }
    }
    
    loadAllImages() {
        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => this.loadImage(img));
    }
}

// Debounced event handlers for performance
class EventOptimizer {
    constructor() {
        this.scrollTimeout = null;
        this.resizeTimeout = null;
        this.init();
    }
    
    init() {
        // Optimized scroll handler
        window.addEventListener('scroll', this.handleScroll.bind(this), { passive: true });
        
        // Optimized resize handler
        window.addEventListener('resize', this.handleResize.bind(this), { passive: true });
        
        // Touch event optimizations
        document.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: true });
        document.addEventListener('touchmove', this.handleTouchMove.bind(this), { passive: true });
    }
    
    handleScroll() {
        if (this.scrollTimeout) {
            clearTimeout(this.scrollTimeout);
        }
        
        this.scrollTimeout = setTimeout(() => {
            this.onScroll();
        }, 16); // ~60fps
    }
    
    handleResize() {
        if (this.resizeTimeout) {
            clearTimeout(this.resizeTimeout);
        }
        
        this.resizeTimeout = setTimeout(() => {
            this.onResize();
        }, 250);
    }
    
    handleTouchStart(e) {
        // Touch start optimizations
    }
    
    handleTouchMove(e) {
        // Touch move optimizations
    }
    
    onScroll() {
        // Custom scroll logic here
        this.updateScrollPosition();
    }
    
    onResize() {
        // Custom resize logic here
        this.updateViewportSize();
    }
    
    updateScrollPosition() {
        const scrollY = window.pageYOffset;
        document.documentElement.style.setProperty('--scroll-y', `${scrollY}px`);
    }
    
    updateViewportSize() {
        const vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }
}

// Modern admin functionality
class AdminInterface {
    constructor() {
        this.sidebar = null;
        this.mobileMenuOpen = false;
        this.init();
    }
    
    init() {
        this.initSidebar();
        this.initModals();
        this.initForms();
        this.initTables();
        this.initNotifications();
        this.initKeyboardShortcuts();
    }
    
    initSidebar() {
        this.sidebar = document.querySelector('.admin-sidebar');
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        
        if (mobileToggle) {
            mobileToggle.addEventListener('click', this.toggleMobileMenu.bind(this));
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (this.mobileMenuOpen && !this.sidebar.contains(e.target) && !e.target.closest('.mobile-menu-toggle')) {
                this.closeMobileMenu();
            }
        });
        
        // Close mobile menu when clicking backdrop
        document.addEventListener('click', (e) => {
            if (this.mobileMenuOpen && e.target === document.body) {
                this.closeMobileMenu();
            }
        });
    }
    
    toggleMobileMenu() {
        this.mobileMenuOpen = !this.mobileMenuOpen;
        this.sidebar.classList.toggle('mobile-open', this.mobileMenuOpen);
        document.body.classList.toggle('mobile-menu-open', this.mobileMenuOpen);
    }
    
    closeMobileMenu() {
        this.mobileMenuOpen = false;
        this.sidebar.classList.remove('mobile-open');
        document.body.classList.remove('mobile-menu-open');
    }
    
    initModals() {
        // Modal functionality with focus management
        const modalTriggers = document.querySelectorAll('[data-modal-target]');
        const modals = document.querySelectorAll('.modal');
        
        modalTriggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = trigger.dataset.modalTarget;
                const modal = document.getElementById(targetId);
                if (modal) {
                    this.openModal(modal);
                }
            });
        });
        
        modals.forEach(modal => {
            const closeButtons = modal.querySelectorAll('[data-modal-close]');
            closeButtons.forEach(btn => {
                btn.addEventListener('click', () => this.closeModal(modal));
            });
            
            // Close on backdrop click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal(modal);
                }
            });
        });
        
        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal.open');
                if (openModal) {
                    this.closeModal(openModal);
                }
            }
        });
    }
    
    openModal(modal) {
        modal.classList.add('open');
        document.body.classList.add('modal-open');
        
        // Focus management
        const firstFocusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (firstFocusable) {
            firstFocusable.focus();
        }
    }
    
    closeModal(modal) {
        modal.classList.remove('open');
        document.body.classList.remove('modal-open');
    }
    
    initForms() {
        // Enhanced form validation and UX
        const forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(form => {
            form.addEventListener('submit', this.handleFormSubmit.bind(this));
            
            // Real-time validation
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('blur', () => this.validateField(input));
                input.addEventListener('input', () => this.clearFieldError(input));
            });
        });
    }
    
    handleFormSubmit(e) {
        const form = e.target;
        const isValid = this.validateForm(form);
        
        if (!isValid) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
        }
    }
    
    validateForm(form) {
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    validateField(field) {
        const value = field.value.trim();
        const isRequired = field.hasAttribute('required');
        const type = field.type;
        
        let isValid = true;
        let errorMessage = '';
        
        if (isRequired && !value) {
            isValid = false;
            errorMessage = 'This field is required';
        } else if (value) {
            switch (type) {
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address';
                    }
                    break;
                case 'url':
                    try {
                        new URL(value);
                    } catch {
                        isValid = false;
                        errorMessage = 'Please enter a valid URL';
                    }
                    break;
            }
        }
        
        this.showFieldError(field, isValid ? '' : errorMessage);
        return isValid;
    }
    
    showFieldError(field, message) {
        const errorElement = field.parentNode.querySelector('.field-error');
        
        if (message) {
            field.classList.add('error');
            if (errorElement) {
                errorElement.textContent = message;
            } else {
                const error = document.createElement('div');
                error.className = 'field-error';
                error.textContent = message;
                field.parentNode.appendChild(error);
            }
        } else {
            field.classList.remove('error');
            if (errorElement) {
                errorElement.remove();
            }
        }
    }
    
    clearFieldError(field) {
        field.classList.remove('error');
        const errorElement = field.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    }
    
    initTables() {
        // Enhanced table functionality
        const tables = document.querySelectorAll('.data-table');
        
        tables.forEach(table => {
            this.initTableSorting(table);
            this.initTableFiltering(table);
        });
    }
    
    initTableSorting(table) {
        const headers = table.querySelectorAll('th[data-sortable]');
        
        headers.forEach(header => {
            header.addEventListener('click', () => {
                const column = header.dataset.sortable;
                const currentSort = header.dataset.sort || 'asc';
                const newSort = currentSort === 'asc' ? 'desc' : 'asc';
                
                this.sortTable(table, column, newSort);
                
                // Update header states
                headers.forEach(h => h.removeAttribute('data-sort'));
                header.dataset.sort = newSort;
            });
        });
    }
    
    sortTable(table, column, direction) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            const aValue = a.querySelector(`[data-column="${column}"]`)?.textContent || '';
            const bValue = b.querySelector(`[data-column="${column}"]`)?.textContent || '';
            
            const comparison = aValue.localeCompare(bValue, undefined, { numeric: true });
            return direction === 'asc' ? comparison : -comparison;
        });
        
        rows.forEach(row => tbody.appendChild(row));
    }
    
    initTableFiltering(table) {
        const filterInput = table.parentNode.querySelector('.table-filter');
        if (!filterInput) return;
        
        filterInput.addEventListener('input', (e) => {
            const filter = e.target.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
    
    initNotifications() {
        // Auto-hide notifications
        const notifications = document.querySelectorAll('.notification[data-auto-hide]');
        
        notifications.forEach(notification => {
            const delay = parseInt(notification.dataset.autoHide) || 5000;
            setTimeout(() => {
                notification.classList.add('fade-out');
                setTimeout(() => notification.remove(), 300);
            }, delay);
        });
    }
    
    initKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K for search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.querySelector('.global-search');
                if (searchInput) {
                    searchInput.focus();
                }
            }
            
            // Escape to close modals/dropdowns
            if (e.key === 'Escape') {
                const openDropdown = document.querySelector('.dropdown.open');
                if (openDropdown) {
                    openDropdown.classList.remove('open');
                }
            }
        });
    }
}

// Initialize everything when DOM is ready
class AppInitializer {
    constructor() {
        this.performanceMonitor = null;
        this.lazyImageLoader = null;
        this.eventOptimizer = null;
        this.adminInterface = null;
        
        this.init();
    }
    
    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', this.onDOMReady.bind(this));
        } else {
            this.onDOMReady();
        }
    }
    
    onDOMReady() {
        // Initialize core systems
        this.performanceMonitor = new PerformanceMonitor();
        this.lazyImageLoader = new LazyImageLoader();
        this.eventOptimizer = new EventOptimizer();
        this.adminInterface = new AdminInterface();
        
        // Initialize non-critical features after idle
        this.initNonCriticalFeatures();
    }
    
    initNonCriticalFeatures() {
        if ('requestIdleCallback' in window) {
            requestIdleCallback(() => {
                this.loadNonCriticalFeatures();
            });
        } else {
            setTimeout(() => {
                this.loadNonCriticalFeatures();
            }, 1000);
        }
    }
    
    loadNonCriticalFeatures() {
        // Load analytics, social widgets, etc.
        console.log('Non-critical features loaded');
        
        // Show performance metrics in development
        if (window.location.hostname === 'localhost') {
            this.showPerformanceMetrics();
        }
    }
    
    showPerformanceMetrics() {
        const metrics = this.performanceMonitor.getMetrics();
        console.table(metrics);
    }
}

// Start the application
const app = new AppInitializer();

// Export for global access if needed
window.EpicAdmin = {
    app,
    PerformanceMonitor,
    LazyImageLoader,
    EventOptimizer,
    AdminInterface
};

class AdminApp {
    constructor() {
        this.navigationManager = null;
        this.cacheManager = null;
        this.lazyLoader = null;
        this.iconManager = null;
        this.performanceMonitor = null;
        this.initialized = false;
        
        // Configuration
        this.config = {
            enablePerformanceMonitoring: true,
            enableServiceWorker: true,
            cacheStrategy: 'aggressive',
            lazyLoadingThreshold: 0.1,
            iconPreloadStrategy: 'common'
        };
        
        // Performance metrics
        this.metrics = {
            initStartTime: performance.now(),
            componentsLoaded: 0,
            totalLoadTime: 0
        };
    }

    /**
     * Initialize the admin application
     */
    async init() {
        if (this.initialized) {
            console.warn('AdminApp already initialized');
            return;
        }

        console.log('Initializing EPIC Hub Admin Application...');
        
        try {
            // Initialize performance monitoring first
            if (this.config.enablePerformanceMonitoring) {
                this.initPerformanceMonitoring();
            }

            // Initialize core managers
            await this.initCoreManagers();
            
            // Initialize Alpine.js components
            this.initAlpineComponents();
            
            // Setup global event listeners
            this.setupGlobalEventListeners();
            
            // Initialize service worker
            if (this.config.enableServiceWorker) {
                await this.initServiceWorker();
            }
            
            // Start Alpine.js
            if (window.Alpine) {
                window.Alpine.start();
            }
            
            this.initialized = true;
            this.metrics.totalLoadTime = performance.now() - this.metrics.initStartTime;
            
            console.log(`Admin Application initialized in ${this.metrics.totalLoadTime.toFixed(2)}ms`);
            
            // Dispatch initialization complete event
            window.dispatchEvent(new CustomEvent('adminAppInitialized', {
                detail: {
                    loadTime: this.metrics.totalLoadTime,
                    componentsLoaded: this.metrics.componentsLoaded
                }
            }));
            
        } catch (error) {
            console.error('Failed to initialize Admin Application:', error);
            this.handleInitializationError(error);
        }
    }

    /**
     * Initialize core managers
     */
    async initCoreManagers() {
        const startTime = performance.now();
        
        // Initialize managers in optimal order
        console.log('Initializing core managers...');
        
        // 1. Cache Manager (needed by others)
        this.cacheManager = new CacheManager();
        this.metrics.componentsLoaded++;
        
        // 2. Icon Manager (can use cache)
        this.iconManager = new IconManager();
        await this.iconManager.init();
        this.metrics.componentsLoaded++;
        
        // 3. Lazy Loader (can use cache and icons)
        this.lazyLoader = new LazyLoader();
        this.metrics.componentsLoaded++;
        
        // 4. Navigation Manager (uses all above)
        this.navigationManager = new NavigationManager();
        this.navigationManager.init();
        this.metrics.componentsLoaded++;
        
        const loadTime = performance.now() - startTime;
        console.log(`Core managers initialized in ${loadTime.toFixed(2)}ms`);
    }

    /**
     * Initialize Alpine.js components
     */
    initAlpineComponents() {
        if (!window.Alpine) {
            console.warn('Alpine.js not found, skipping component initialization');
            return;
        }

        console.log('Initializing Alpine.js components...');
        
        // Main admin app component
        window.Alpine.data('adminApp', () => ({
            // State
            sidebarCollapsed: this.navigationManager?.getSidebarState() || false,
            notifications: [],
            user: null,
            loading: false,
            
            // Computed
            get notificationCount() {
                return this.notifications.filter(n => !n.read).length;
            },
            
            get userInitials() {
                return this.user?.name ? this.user.name.split(' ').map(n => n[0]).join('').toUpperCase() : 'U';
            },
            
            get userAvatar() {
                return this.user?.avatar || null;
            },
            
            // Methods
            async init() {
                await this.loadUserData();
                await this.loadNotifications();
                this.setupKeyboardShortcuts();
            },
            
            toggleSidebar() {
                this.sidebarCollapsed = !this.sidebarCollapsed;
                this.navigationManager?.setSidebarState(this.sidebarCollapsed);
            },
            
            async loadUserData() {
                try {
                    this.user = await this.cacheManager?.getCachedData(
                        'current-user',
                        () => fetch('/api/admin/user/current').then(r => r.json()),
                        { ttl: 5 * 60 * 1000, useStorage: true }
                    );
                } catch (error) {
                    console.error('Failed to load user data:', error);
                }
            },
            
            async loadNotifications() {
                try {
                    this.notifications = await this.cacheManager?.getCachedData(
                        'notifications',
                        () => fetch('/api/admin/notifications').then(r => r.json()),
                        { ttl: 2 * 60 * 1000 }
                    ) || [];
                } catch (error) {
                    console.error('Failed to load notifications:', error);
                }
            },
            
            setupKeyboardShortcuts() {
                document.addEventListener('keydown', (e) => {
                    // Ctrl/Cmd + B: Toggle sidebar
                    if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                        e.preventDefault();
                        this.toggleSidebar();
                    }
                    
                    // Ctrl/Cmd + K: Focus search (if exists)
                    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                        e.preventDefault();
                        const searchInput = document.querySelector('[data-search-input]');
                        if (searchInput) {
                            searchInput.focus();
                        }
                    }
                });
            },
            
            // Expose managers to Alpine components
            get cacheManager() {
                return window.adminApp?.cacheManager;
            },
            
            get navigationManager() {
                return window.adminApp?.navigationManager;
            },
            
            get lazyLoader() {
                return window.adminApp?.lazyLoader;
            },
            
            get iconManager() {
                return window.adminApp?.iconManager;
            }
        }));
    }

    /**
     * Setup global event listeners
     */
    setupGlobalEventListeners() {
        // Handle online/offline status
        window.addEventListener('online', () => {
            console.log('Connection restored');
            this.showNotification('Connection restored', 'success');
        });
        
        window.addEventListener('offline', () => {
            console.log('Connection lost');
            this.showNotification('Connection lost - working offline', 'warning');
        });
        
        // Handle visibility change
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                // Page became visible, refresh critical data
                this.refreshCriticalData();
            }
        });
    }

    /**
     * Initialize service worker
     */
    async initServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/sw.js');
                console.log('Service Worker registered:', registration);
            } catch (error) {
                console.warn('Service Worker registration failed:', error);
            }
        }
    }

    /**
     * Initialize performance monitoring
     */
    initPerformanceMonitoring() {
        this.performanceMonitor = {
            metrics: new Map(),
            
            startTiming: (name) => {
                this.metrics.set(name, performance.now());
            },
            
            endTiming: (name) => {
                const startTime = this.metrics.get(name);
                if (startTime) {
                    const duration = performance.now() - startTime;
                    console.log(`⏱️ ${name}: ${duration.toFixed(2)}ms`);
                    return duration;
                }
                return 0;
            }
        };
        
        // Expose to window for debugging
        window.performanceMonitor = this.performanceMonitor;
    }

    /**
     * Show notification to user
     */
    showNotification(message, type = 'info', options = {}) {
        console.log(`${type.toUpperCase()}: ${message}`);
    }

    /**
     * Refresh critical data
     */
    async refreshCriticalData() {
        try {
            // Clear cache for critical data
            this.cacheManager?.clear('current-user');
            this.cacheManager?.clear('notifications');
            
            // Trigger refresh in Alpine components
            window.dispatchEvent(new CustomEvent('refreshCriticalData'));
        } catch (error) {
            console.error('Failed to refresh critical data:', error);
        }
    }

    /**
     * Handle initialization error
     */
    handleInitializationError(error) {
        console.error('Initialization error:', error);
    }

    /**
     * Get application statistics
     */
    getStats() {
        return {
            initialized: this.initialized,
            loadTime: this.metrics.totalLoadTime,
            componentsLoaded: this.metrics.componentsLoaded,
            cache: this.cacheManager?.getStats(),
            icons: this.iconManager?.getStats(),
            lazy: this.lazyLoader?.getStats(),
            navigation: {
                sidebarCollapsed: this.navigationManager?.getSidebarState()
            }
        };
    }

    /**
     * Destroy application
     */
    destroy() {
        console.log('Destroying Admin Application...');
        
        // Destroy managers
        this.navigationManager?.destroy();
        this.cacheManager?.destroy();
        this.lazyLoader?.destroy();
        this.iconManager?.destroy();
        
        // Clear references
        this.navigationManager = null;
        this.cacheManager = null;
        this.lazyLoader = null;
        this.iconManager = null;
        
        this.initialized = false;
        
        console.log('Admin Application destroyed');
    }
}

// Fallback for non-module environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdminApp;
} else {
    // Initialize application when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeApp);
    } else {
        initializeApp();
    }

    async function initializeApp() {
        // Create global admin app instance
        window.adminApp = new AdminApp();
        
        // Initialize the application
        await window.adminApp.init();
        
        // Expose for debugging
        window.adminAppDebug = {
            getStats: () => window.adminApp.getStats(),
            clearCache: () => window.adminApp.cacheManager?.clearAll(),
            reloadIcons: () => window.adminApp.iconManager?.clearCache(),
            destroy: () => window.adminApp.destroy()
        };
    }
}

// Export for module usage
if (typeof window !== 'undefined') {
    window.AdminApp = AdminApp;
}