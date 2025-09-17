/**
 * EPIC Hub Member Area - Admin Theme Compatible
 * Simple JavaScript for member area using admin theme
 */

// Member App Alpine.js Component
function memberApp() {
    return {
        // Sidebar state
        sidebarCollapsed: false,
        
        // Mobile sidebar
        mobileSidebarOpen: false,
        
        // Notifications
        notifications: [],
        
        // Loading states
        loading: false,
        
        // Initialize the app
        init() {
            console.log('EPIC Hub Member Area - Admin Theme Loaded');
            
            // Initialize Feather Icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
            
            // Setup event listeners
            this.setupEventListeners();
            
            // Check for saved sidebar state
            this.loadSidebarState();
            
            // Initialize tooltips if available
            this.initTooltips();
        },
        
        // Setup global event listeners
        setupEventListeners() {
            // Handle window resize
            window.addEventListener('resize', () => {
                if (window.innerWidth > 1024) {
                    this.mobileSidebarOpen = false;
                }
            });
            
            // Handle escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.mobileSidebarOpen = false;
                }
            });
            
            // Handle clicks outside sidebar on mobile
            document.addEventListener('click', (e) => {
                if (this.mobileSidebarOpen && !e.target.closest('.admin-sidebar') && !e.target.closest('.mobile-menu-btn')) {
                    this.mobileSidebarOpen = false;
                }
            });
        },
        
        // Toggle sidebar collapse
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            this.saveSidebarState();
        },
        
        // Toggle mobile sidebar
        toggleMobileSidebar() {
            this.mobileSidebarOpen = !this.mobileSidebarOpen;
        },
        
        // Save sidebar state to localStorage
        saveSidebarState() {
            try {
                localStorage.setItem('member_sidebar_collapsed', this.sidebarCollapsed);
            } catch (e) {
                console.warn('Could not save sidebar state:', e);
            }
        },
        
        // Load sidebar state from localStorage
        loadSidebarState() {
            try {
                const saved = localStorage.getItem('member_sidebar_collapsed');
                if (saved !== null) {
                    this.sidebarCollapsed = saved === 'true';
                }
            } catch (e) {
                console.warn('Could not load sidebar state:', e);
            }
        },
        
        // Show notification
        showNotification(message, type = 'info', duration = 5000) {
            const id = Date.now();
            const notification = {
                id,
                message,
                type,
                visible: true
            };
            
            this.notifications.push(notification);
            
            // Auto remove after duration
            setTimeout(() => {
                this.removeNotification(id);
            }, duration);
        },
        
        // Remove notification
        removeNotification(id) {
            const index = this.notifications.findIndex(n => n.id === id);
            if (index > -1) {
                this.notifications.splice(index, 1);
            }
        },
        
        // Initialize tooltips
        initTooltips() {
            // Simple tooltip implementation
            const tooltipElements = document.querySelectorAll('[title]');
            tooltipElements.forEach(el => {
                el.addEventListener('mouseenter', this.showTooltip);
                el.addEventListener('mouseleave', this.hideTooltip);
            });
        },
        
        // Show tooltip
        showTooltip(e) {
            const title = e.target.getAttribute('title');
            if (!title) return;
            
            // Create tooltip element
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = title;
            tooltip.style.position = 'absolute';
            tooltip.style.zIndex = '9999';
            tooltip.style.background = '#1a1a1a';
            tooltip.style.color = 'white';
            tooltip.style.padding = '4px 8px';
            tooltip.style.borderRadius = '4px';
            tooltip.style.fontSize = '12px';
            tooltip.style.pointerEvents = 'none';
            
            document.body.appendChild(tooltip);
            
            // Position tooltip
            const rect = e.target.getBoundingClientRect();
            tooltip.style.left = rect.left + 'px';
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
            
            // Store reference
            e.target._tooltip = tooltip;
            
            // Remove title to prevent default tooltip
            e.target._originalTitle = title;
            e.target.removeAttribute('title');
        },
        
        // Hide tooltip
        hideTooltip(e) {
            if (e.target._tooltip) {
                document.body.removeChild(e.target._tooltip);
                delete e.target._tooltip;
            }
            
            // Restore title
            if (e.target._originalTitle) {
                e.target.setAttribute('title', e.target._originalTitle);
                delete e.target._originalTitle;
            }
        },
        
        // Handle form submissions
        async submitForm(formData, url, options = {}) {
            this.loading = true;
            
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    ...options
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.showNotification(result.message || 'Success!', 'success');
                } else {
                    this.showNotification(result.message || 'Error occurred', 'error');
                }
                
                return result;
            } catch (error) {
                console.error('Form submission error:', error);
                this.showNotification('Network error occurred', 'error');
                return { success: false, error };
            } finally {
                this.loading = false;
            }
        },
        
        // Utility: Format currency
        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        },
        
        // Utility: Format date
        formatDate(date) {
            return new Intl.DateTimeFormat('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }).format(new Date(date));
        },
        
        // Utility: Copy to clipboard
        async copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                this.showNotification('Copied to clipboard!', 'success', 2000);
            } catch (error) {
                console.error('Copy failed:', error);
                this.showNotification('Copy failed', 'error', 2000);
            }
        }
    };
}

// Global utilities
window.memberApp = memberApp;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather Icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    console.log('Member Area - Admin Theme JavaScript Loaded');
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { memberApp };
}