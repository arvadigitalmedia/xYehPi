/**
 * EPIC Hub Admin Navigation Manager
 * Handles sidebar, topbar, and settings navigation with lazy loading and caching
 */

export class NavigationManager {
    constructor() {
        this.sidebarState = this.loadSidebarState();
        this.activeSubmenu = null;
        this.navigationCache = new Map();
        this.observers = new Map();
        this.initialized = false;
    }

    /**
     * Initialize navigation manager
     */
    init() {
        if (this.initialized) return;
        
        this.setupEventListeners();
        this.initializeLazyLoading();
        this.restoreNavigationState();
        this.preloadCriticalComponents();
        
        this.initialized = true;
        console.log('Navigation Manager initialized');
    }

    /**
     * Setup event listeners for navigation interactions
     */
    setupEventListeners() {
        // Sidebar toggle
        document.addEventListener('click', (e) => {
            if (e.target.matches('.sidebar-collapse-btn, .sidebar-collapse-btn *')) {
                this.toggleSidebar();
            }
        });

        // Submenu toggle
        document.addEventListener('click', (e) => {
            if (e.target.matches('.sidebar-nav-parent, .sidebar-nav-parent *')) {
                const parent = e.target.closest('.sidebar-nav-parent');
                if (parent) {
                    this.toggleSubmenu(parent);
                }
            }
        });

        // Settings navigation
        document.addEventListener('click', (e) => {
            if (e.target.matches('.settings-nav-item, .settings-nav-item *')) {
                const navItem = e.target.closest('.settings-nav-item');
                if (navItem && !navItem.classList.contains('active')) {
                    this.handleSettingsNavigation(navItem);
                }
            }
        });

        // Handle window resize for responsive behavior
        window.addEventListener('resize', this.debounce(() => {
            this.handleResize();
        }, 250));
    }

    /**
     * Initialize lazy loading for navigation components
     */
    initializeLazyLoading() {
        // Lazy load submenu content
        const submenus = document.querySelectorAll('.sidebar-submenu');
        submenus.forEach(submenu => {
            if (!submenu.dataset.loaded) {
                this.setupSubmenuLazyLoading(submenu);
            }
        });

        // Lazy load settings navigation content
        const settingsNav = document.querySelector('.settings-navigation');
        if (settingsNav && !settingsNav.dataset.loaded) {
            this.setupSettingsNavLazyLoading(settingsNav);
        }
    }

    /**
     * Setup lazy loading for submenu items
     */
    setupSubmenuLazyLoading(submenu) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadSubmenuContent(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            rootMargin: '50px'
        });

        observer.observe(submenu);
        this.observers.set(submenu, observer);
    }

    /**
     * Setup lazy loading for settings navigation
     */
    setupSettingsNavLazyLoading(settingsNav) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadSettingsNavContent(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        });

        observer.observe(settingsNav);
        this.observers.set(settingsNav, observer);
    }

    /**
     * Load submenu content dynamically
     */
    async loadSubmenuContent(submenu) {
        const cacheKey = `submenu-${submenu.dataset.menu || 'default'}`;
        
        if (this.navigationCache.has(cacheKey)) {
            return this.navigationCache.get(cacheKey);
        }

        try {
            // Simulate loading submenu items (replace with actual API call)
            const menuData = await this.fetchMenuData(submenu.dataset.menu);
            this.renderSubmenuItems(submenu, menuData);
            
            submenu.dataset.loaded = 'true';
            this.navigationCache.set(cacheKey, menuData);
            
            console.log(`Loaded submenu: ${cacheKey}`);
        } catch (error) {
            console.error('Failed to load submenu content:', error);
        }
    }

    /**
     * Load settings navigation content
     */
    async loadSettingsNavContent(settingsNav) {
        const cacheKey = 'settings-nav';
        
        if (this.navigationCache.has(cacheKey)) {
            return this.navigationCache.get(cacheKey);
        }

        try {
            // Load settings navigation data
            const navData = await this.fetchSettingsNavData();
            this.enhanceSettingsNavigation(settingsNav, navData);
            
            settingsNav.dataset.loaded = 'true';
            this.navigationCache.set(cacheKey, navData);
            
            console.log('Loaded settings navigation');
        } catch (error) {
            console.error('Failed to load settings navigation:', error);
        }
    }

    /**
     * Fetch menu data (placeholder - implement actual API call)
     */
    async fetchMenuData(menuType) {
        // Simulate API delay
        await new Promise(resolve => setTimeout(resolve, 100));
        
        // Return mock data (replace with actual API call)
        return {
            items: [
                { id: 1, name: 'Menu Item 1', url: '/admin/item1', icon: 'circle' },
                { id: 2, name: 'Menu Item 2', url: '/admin/item2', icon: 'circle' }
            ]
        };
    }

    /**
     * Fetch settings navigation data
     */
    async fetchSettingsNavData() {
        await new Promise(resolve => setTimeout(resolve, 50));
        
        return {
            permissions: ['general', 'form', 'email', 'whatsapp', 'payment', 'security'],
            currentSection: this.getCurrentSettingsSection()
        };
    }

    /**
     * Render submenu items dynamically
     */
    renderSubmenuItems(submenu, menuData) {
        if (!menuData.items || menuData.items.length === 0) return;

        const fragment = document.createDocumentFragment();
        
        menuData.items.forEach(item => {
            const link = document.createElement('a');
            link.href = item.url;
            link.className = 'sidebar-submenu-item';
            link.innerHTML = `<span class="sidebar-submenu-text">${item.name}</span>`;
            
            // Add active class if current page
            if (window.location.pathname === item.url) {
                link.classList.add('active');
            }
            
            fragment.appendChild(link);
        });

        submenu.appendChild(fragment);
    }

    /**
     * Enhance settings navigation with dynamic features
     */
    enhanceSettingsNavigation(settingsNav, navData) {
        const navItems = settingsNav.querySelectorAll('.settings-nav-item');
        
        navItems.forEach(item => {
            // Add loading states
            item.addEventListener('click', (e) => {
                if (!item.classList.contains('active')) {
                    this.showNavigationLoading(item);
                }
            });

            // Add keyboard navigation
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    item.click();
                }
            });
        });
    }

    /**
     * Toggle sidebar collapsed state
     */
    toggleSidebar() {
        this.sidebarState.collapsed = !this.sidebarState.collapsed;
        this.applySidebarState();
        this.saveSidebarState();
        
        // Trigger custom event
        window.dispatchEvent(new CustomEvent('sidebarToggle', {
            detail: { collapsed: this.sidebarState.collapsed }
        }));
    }

    /**
     * Toggle submenu visibility
     */
    toggleSubmenu(parentElement) {
        const submenu = parentElement.nextElementSibling;
        if (!submenu || !submenu.classList.contains('sidebar-submenu')) return;

        const isOpen = submenu.classList.contains('show');
        
        // Close other submenus
        document.querySelectorAll('.sidebar-submenu.show').forEach(menu => {
            if (menu !== submenu) {
                menu.classList.remove('show');
                menu.previousElementSibling.classList.remove('expanded');
            }
        });

        // Toggle current submenu
        if (isOpen) {
            submenu.classList.remove('show');
            parentElement.classList.remove('expanded');
            this.activeSubmenu = null;
        } else {
            submenu.classList.add('show');
            parentElement.classList.add('expanded');
            this.activeSubmenu = parentElement.dataset.menu;
            
            // Load content if not already loaded
            if (!submenu.dataset.loaded) {
                this.loadSubmenuContent(submenu);
            }
        }

        this.saveNavigationState();
    }

    /**
     * Handle settings navigation clicks
     */
    handleSettingsNavigation(navItem) {
        // Remove active class from all items
        document.querySelectorAll('.settings-nav-item.active').forEach(item => {
            item.classList.remove('active');
        });

        // Add active class to clicked item
        navItem.classList.add('active');
        
        // Show loading state
        this.showNavigationLoading(navItem);
        
        // Save state
        this.saveNavigationState();
    }

    /**
     * Show loading state for navigation item
     */
    showNavigationLoading(navItem) {
        const icon = navItem.querySelector('.settings-nav-icon');
        if (icon) {
            const originalIcon = icon.getAttribute('data-feather');
            icon.setAttribute('data-feather', 'loader');
            icon.classList.add('spin');
            
            // Restore original icon after navigation
            setTimeout(() => {
                icon.setAttribute('data-feather', originalIcon);
                icon.classList.remove('spin');
                if (window.feather) {
                    window.feather.replace();
                }
            }, 500);
        }
    }

    /**
     * Handle window resize
     */
    handleResize() {
        const width = window.innerWidth;
        
        // Auto-collapse sidebar on mobile
        if (width < 768 && !this.sidebarState.collapsed) {
            this.sidebarState.collapsed = true;
            this.applySidebarState();
        }
        
        // Auto-expand sidebar on desktop if it was auto-collapsed
        if (width >= 1024 && this.sidebarState.collapsed && this.sidebarState.autoCollapsed) {
            this.sidebarState.collapsed = false;
            this.sidebarState.autoCollapsed = false;
            this.applySidebarState();
        }
    }

    /**
     * Apply sidebar state to DOM
     */
    applySidebarState() {
        const sidebar = document.querySelector('.admin-sidebar');
        const main = document.querySelector('.admin-main');
        
        if (sidebar) {
            sidebar.classList.toggle('collapsed', this.sidebarState.collapsed);
        }
        
        if (main) {
            main.classList.toggle('sidebar-collapsed', this.sidebarState.collapsed);
        }
    }

    /**
     * Restore navigation state from storage
     */
    restoreNavigationState() {
        this.applySidebarState();
        
        // Restore active submenu
        if (this.activeSubmenu) {
            const submenuParent = document.querySelector(`[data-menu="${this.activeSubmenu}"]`);
            if (submenuParent) {
                this.toggleSubmenu(submenuParent);
            }
        }
    }

    /**
     * Preload critical navigation components
     */
    async preloadCriticalComponents() {
        const criticalMenus = ['settings', 'manage'];
        
        for (const menu of criticalMenus) {
            try {
                await this.fetchMenuData(menu);
            } catch (error) {
                console.warn(`Failed to preload menu: ${menu}`, error);
            }
        }
    }

    /**
     * Get current settings section from URL
     */
    getCurrentSettingsSection() {
        const path = window.location.pathname;
        const match = path.match(/\/admin\/settings\/([^/]+)/);
        return match ? match[1] : 'general';
    }

    /**
     * Load sidebar state from localStorage
     */
    loadSidebarState() {
        try {
            const stored = localStorage.getItem('epic-admin-sidebar-state');
            return stored ? JSON.parse(stored) : { collapsed: false, autoCollapsed: false };
        } catch (error) {
            console.warn('Failed to load sidebar state:', error);
            return { collapsed: false, autoCollapsed: false };
        }
    }

    /**
     * Save sidebar state to localStorage
     */
    saveSidebarState() {
        try {
            localStorage.setItem('epic-admin-sidebar-state', JSON.stringify(this.sidebarState));
        } catch (error) {
            console.warn('Failed to save sidebar state:', error);
        }
    }

    /**
     * Save navigation state
     */
    saveNavigationState() {
        try {
            const state = {
                activeSubmenu: this.activeSubmenu,
                currentPath: window.location.pathname,
                timestamp: Date.now()
            };
            
            localStorage.setItem('epic-admin-nav-state', JSON.stringify(state));
        } catch (error) {
            console.warn('Failed to save navigation state:', error);
        }
    }

    /**
     * Get sidebar state
     */
    getSidebarState() {
        return this.sidebarState.collapsed;
    }

    /**
     * Set sidebar state
     */
    setSidebarState(collapsed) {
        this.sidebarState.collapsed = collapsed;
        this.applySidebarState();
        this.saveSidebarState();
    }

    /**
     * Debounce utility function
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Cleanup observers and event listeners
     */
    destroy() {
        this.observers.forEach(observer => observer.disconnect());
        this.observers.clear();
        this.navigationCache.clear();
        this.initialized = false;
    }
}

// Export for use in other modules
export default NavigationManager;