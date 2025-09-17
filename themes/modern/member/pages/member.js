/**
 * EPIC Hub Member Area JavaScript
 * Main JavaScript untuk member area functionality
 * 
 * @version 2.0.0
 * @author EPIC Hub Team
 */

// Alpine.js Member App
function memberApp() {
    return {
        // App State
        loading: false,
        sidebarOpen: false,
        notifications: [],
        
        // Initialize app
        init() {
            this.initFeatherIcons();
            this.initCopyButtons();
            this.initNotifications();
            this.initMobileHandlers();
            
            // Listen for sidebar toggle events
            this.$watch('sidebarOpen', (value) => {
                if (value) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            });
        },
        
        // Initialize Feather Icons
        initFeatherIcons() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        },
        
        // Initialize copy buttons
        initCopyButtons() {
            document.addEventListener('click', (e) => {
                if (e.target.matches('.copy-btn') || e.target.closest('.copy-btn')) {
                    const btn = e.target.matches('.copy-btn') ? e.target : e.target.closest('.copy-btn');
                    this.copyToClipboard(btn);
                }
            });
        },
        
        // Copy to clipboard functionality
        async copyToClipboard(button) {
            const textToCopy = button.dataset.copy || button.getAttribute('data-copy');
            
            if (!textToCopy) {
                console.warn('No text to copy found');
                return;
            }
            
            try {
                // Modern clipboard API
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(textToCopy);
                } else {
                    // Fallback for older browsers
                    this.fallbackCopyToClipboard(textToCopy);
                }
                
                // Visual feedback
                this.showCopySuccess(button);
                
                // Show toast notification
                this.showToast('URL berhasil disalin!', 'success');
                
            } catch (err) {
                console.error('Failed to copy text: ', err);
                this.showToast('Gagal menyalin URL', 'error');
            }
        },
        
        // Fallback copy method
        fallbackCopyToClipboard(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
            } catch (err) {
                console.error('Fallback copy failed: ', err);
                throw err;
            } finally {
                document.body.removeChild(textArea);
            }
        },
        
        // Show copy success visual feedback
        showCopySuccess(button) {
            const originalText = button.innerHTML;
            const originalClass = button.className;
            
            // Change button appearance
            button.innerHTML = '<i data-feather="check" width="16" height="16"></i> Copied!';
            button.classList.add('copied');
            
            // Replace feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
            
            // Reset after 2 seconds
            setTimeout(() => {
                button.innerHTML = originalText;
                button.className = originalClass;
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            }, 2000);
        },
        
        // Initialize notifications
        initNotifications() {
            // Load notifications from localStorage or API
            this.loadNotifications();
        },
        
        // Load notifications
        loadNotifications() {
            // This would typically load from an API
            // For now, we'll use dummy data
            this.notifications = [
                {
                    id: 1,
                    title: 'Komisi Baru',
                    message: 'Anda mendapat komisi Rp 50.000',
                    type: 'success',
                    time: '2 jam yang lalu',
                    read: false
                },
                {
                    id: 2,
                    title: 'Referral Baru',
                    message: 'John Doe bergabung melalui link Anda',
                    type: 'info',
                    time: '1 hari yang lalu',
                    read: true
                }
            ];
        },
        
        // Mark notification as read
        markAsRead(notificationId) {
            const notification = this.notifications.find(n => n.id === notificationId);
            if (notification) {
                notification.read = true;
                this.saveNotifications();
            }
        },
        
        // Mark all notifications as read
        markAllAsRead() {
            this.notifications.forEach(n => n.read = true);
            this.saveNotifications();
            this.showToast('Semua notifikasi ditandai sebagai dibaca', 'success');
        },
        
        // Save notifications to localStorage
        saveNotifications() {
            localStorage.setItem('member_notifications', JSON.stringify(this.notifications));
        },
        
        // Get unread notification count
        get unreadCount() {
            return this.notifications.filter(n => !n.read).length;
        },
        
        // Initialize mobile handlers
        initMobileHandlers() {
            // Handle mobile menu toggle
            document.addEventListener('click', (e) => {
                if (e.target.matches('.mobile-menu-toggle') || e.target.closest('.mobile-menu-toggle')) {
                    this.sidebarOpen = !this.sidebarOpen;
                }
            });
            
            // Close sidebar when clicking overlay
            document.addEventListener('click', (e) => {
                if (e.target.matches('.sidebar-overlay')) {
                    this.sidebarOpen = false;
                }
            });
            
            // Handle escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.sidebarOpen) {
                    this.sidebarOpen = false;
                }
            });
        },
        
        // Show toast notification
        showToast(message, type = 'info', duration = 3000) {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <div class="toast-content">
                    <div class="toast-icon">
                        <i data-feather="${this.getToastIcon(type)}" width="16" height="16"></i>
                    </div>
                    <div class="toast-message">${message}</div>
                    <button class="toast-close" onclick="this.parentElement.parentElement.remove()">
                        <i data-feather="x" width="14" height="14"></i>
                    </button>
                </div>
            `;
            
            // Add toast styles if not already present
            this.ensureToastStyles();
            
            // Add to page
            document.body.appendChild(toast);
            
            // Replace feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
            
            // Animate in
            setTimeout(() => {
                toast.classList.add('toast-show');
            }, 100);
            
            // Auto remove
            setTimeout(() => {
                toast.classList.add('toast-hide');
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.remove();
                    }
                }, 300);
            }, duration);
        },
        
        // Get toast icon based on type
        getToastIcon(type) {
            const icons = {
                success: 'check-circle',
                error: 'alert-circle',
                warning: 'alert-triangle',
                info: 'info'
            };
            return icons[type] || 'info';
        },
        
        // Ensure toast styles are present
        ensureToastStyles() {
            if (document.getElementById('toast-styles')) {
                return;
            }
            
            const styles = document.createElement('style');
            styles.id = 'toast-styles';
            styles.textContent = `
                .toast {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    min-width: 300px;
                    max-width: 400px;
                    background: white;
                    border-radius: 0.5rem;
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
                    border: 1px solid #e2e8f0;
                    transform: translateX(100%);
                    transition: all 0.3s ease;
                    opacity: 0;
                }
                
                .toast.toast-show {
                    transform: translateX(0);
                    opacity: 1;
                }
                
                .toast.toast-hide {
                    transform: translateX(100%);
                    opacity: 0;
                }
                
                .toast-content {
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                    padding: 1rem;
                }
                
                .toast-icon {
                    flex-shrink: 0;
                }
                
                .toast-success .toast-icon {
                    color: #059669;
                }
                
                .toast-error .toast-icon {
                    color: #dc2626;
                }
                
                .toast-warning .toast-icon {
                    color: #d97706;
                }
                
                .toast-info .toast-icon {
                    color: #2563eb;
                }
                
                .toast-message {
                    flex: 1;
                    font-size: 0.875rem;
                    color: #374151;
                }
                
                .toast-close {
                    background: none;
                    border: none;
                    color: #9ca3af;
                    cursor: pointer;
                    padding: 0.25rem;
                    border-radius: 0.25rem;
                    transition: all 0.2s;
                    flex-shrink: 0;
                }
                
                .toast-close:hover {
                    background: #f3f4f6;
                    color: #6b7280;
                }
                
                @media (max-width: 480px) {
                    .toast {
                        right: 10px;
                        left: 10px;
                        min-width: auto;
                        max-width: none;
                    }
                }
            `;
            
            document.head.appendChild(styles);
        },
        
        // Format currency
        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        },
        
        // Format number
        formatNumber(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        },
        
        // Format date
        formatDate(date) {
            return new Intl.DateTimeFormat('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }).format(new Date(date));
        },
        
        // Format relative time
        formatRelativeTime(date) {
            const now = new Date();
            const target = new Date(date);
            const diffInSeconds = Math.floor((now - target) / 1000);
            
            if (diffInSeconds < 60) {
                return 'Baru saja';
            } else if (diffInSeconds < 3600) {
                const minutes = Math.floor(diffInSeconds / 60);
                return `${minutes} menit yang lalu`;
            } else if (diffInSeconds < 86400) {
                const hours = Math.floor(diffInSeconds / 3600);
                return `${hours} jam yang lalu`;
            } else if (diffInSeconds < 2592000) {
                const days = Math.floor(diffInSeconds / 86400);
                return `${days} hari yang lalu`;
            } else {
                return this.formatDate(date);
            }
        },
        
        // Show loading state
        showLoading() {
            this.loading = true;
        },
        
        // Hide loading state
        hideLoading() {
            this.loading = false;
        },
        
        // Confirm dialog
        confirm(message, callback) {
            if (window.confirm(message)) {
                callback();
            }
        },
        
        // Redirect with loading
        redirect(url) {
            this.showLoading();
            window.location.href = url;
        }
    };
}

// Utility functions
window.memberUtils = {
    // Copy text to clipboard
    async copyText(text) {
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
            } else {
                // Fallback
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
            }
            return true;
        } catch (err) {
            console.error('Copy failed:', err);
            return false;
        }
    },
    
    // Format currency
    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    },
    
    // Debounce function
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
    },
    
    // Throttle function
    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips if needed
    // Initialize any other components
    
    console.log('EPIC Hub Member Area initialized');
});

// Handle page visibility changes
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
        // Refresh data when page becomes visible
        // This could be used to update notifications, stats, etc.
    }
});

// Handle online/offline status
window.addEventListener('online', function() {
    console.log('Connection restored');
    // Handle reconnection
});

window.addEventListener('offline', function() {
    console.log('Connection lost');
    // Handle offline state
});