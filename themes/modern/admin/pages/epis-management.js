/**
 * EPIS Management Page JavaScript
 * Interactive functionality untuk halaman EPIS Account Management
 * 
 * @version 2.0.0
 * @author EPIC Hub Team
 */

(function() {
    'use strict';
    
    // Initialize page when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeEpisManagement();
    });
    
    /**
     * Initialize EPIS Management functionality
     */
    function initializeEpisManagement() {
        // Initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
        
        // Initialize modal event listeners
        initializeModalEvents();
        
        // Initialize form validation
        initializeFormValidation();
        
        // Initialize search functionality
        initializeSearchFunctionality();
        
        // Initialize tooltips
        initializeTooltips();
        
        console.log('EPIS Management page initialized successfully');
    }
    
    /**
     * Initialize modal event listeners
     */
    function initializeModalEvents() {
        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                hideAllModals();
            }
        });
        
        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideAllModals();
            }
        });
    }
    
    /**
     * Initialize form validation
     */
    function initializeFormValidation() {
        const forms = document.querySelectorAll('.modal-form');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!validateForm(this)) {
                    e.preventDefault();
                    return false;
                }
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    showButtonLoading(submitBtn);
                }
            });
        });
    }
    
    /**
     * Initialize search functionality
     */
    function initializeSearchFunctionality() {
        const searchInput = document.querySelector('.search-input');
        const filterSelect = document.querySelector('.filter-select');
        
        if (searchInput) {
            // Add debounced search
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    // Auto-submit search form after 500ms of no typing
                    const form = this.closest('form');
                    if (form) {
                        form.submit();
                    }
                }, 500);
            });
        }
        
        if (filterSelect) {
            filterSelect.addEventListener('change', function() {
                // Auto-submit when filter changes
                const form = this.closest('form');
                if (form) {
                    form.submit();
                }
            });
        }
    }
    
    /**
     * Initialize tooltips
     */
    function initializeTooltips() {
        const tooltipElements = document.querySelectorAll('[title]');
        
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', function() {
                showTooltip(this, this.getAttribute('title'));
            });
            
            element.addEventListener('mouseleave', function() {
                hideTooltip();
            });
        });
    }
    
    /**
     * Show create EPIS modal
     */
    window.showCreateModal = function() {
        const modal = document.getElementById('createEpisModal');
        if (modal) {
            modal.style.display = 'flex';
            
            // Focus first input
            const firstInput = modal.querySelector('input, select, textarea');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
            
            // Reset form
            const form = modal.querySelector('form');
            if (form) {
                form.reset();
            }
        }
    };
    
    /**
     * Hide create EPIS modal
     */
    window.hideCreateModal = function() {
        const modal = document.getElementById('createEpisModal');
        if (modal) {
            modal.style.display = 'none';
        }
    };
    
    /**
     * Show edit EPIS modal
     */
    window.showEditModal = function(episData) {
        const modal = document.getElementById('editEpisModal');
        if (!modal || !episData) return;
        
        try {
            // Populate form with existing data
            document.getElementById('edit_epis_user_id').value = episData.user_id || '';
            
            const episInfo = document.getElementById('edit_epis_info');
            if (episInfo) {
                episInfo.innerHTML = `
                    <strong>${escapeHtml(episData.name || '')}</strong><br>
                    <small>${escapeHtml(episData.email || '')} • ${escapeHtml(episData.epis_code || '')}</small>
                `;
            }
            
            document.getElementById('edit_territory_name').value = episData.territory_name || '';
            document.getElementById('edit_territory_description').value = episData.territory_description || '';
            document.getElementById('edit_max_epic_recruits').value = episData.max_epic_recruits || 0;
            document.getElementById('edit_recruitment_commission_rate').value = episData.recruitment_commission_rate || 10;
            document.getElementById('edit_indirect_commission_rate').value = episData.indirect_commission_rate || 5;
            
            modal.style.display = 'flex';
            
            // Focus first editable input
            const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        } catch (error) {
            console.error('Error populating edit modal:', error);
            showNotification('Error loading EPIS data', 'error');
        }
    };
    
    /**
     * Hide edit EPIS modal
     */
    window.hideEditModal = function() {
        const modal = document.getElementById('editEpisModal');
        if (modal) {
            modal.style.display = 'none';
        }
    };
    
    /**
     * Hide all modals
     */
    function hideAllModals() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.style.display = 'none';
        });
    }
    
    /**
     * Validate form before submission
     */
    function validateForm(form) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                showFieldError(field, 'This field is required');
                isValid = false;
            } else {
                clearFieldError(field);
            }
        });
        
        // Validate commission rates
        const commissionFields = form.querySelectorAll('input[name*="commission_rate"]');
        commissionFields.forEach(field => {
            const value = parseFloat(field.value);
            if (isNaN(value) || value < 0 || value > 100) {
                showFieldError(field, 'Commission rate must be between 0 and 100');
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    /**
     * Show field error
     */
    function showFieldError(field, message) {
        clearFieldError(field);
        
        field.classList.add('error');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
    }
    
    /**
     * Clear field error
     */
    function clearFieldError(field) {
        field.classList.remove('error');
        
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
    }
    
    /**
     * Show button loading state
     */
    function showButtonLoading(button) {
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = `
            <svg class="animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 12a9 9 0 11-6.219-8.56"/>
            </svg>
            Processing...
        `;
        
        // Restore button after form submission
        setTimeout(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        }, 3000);
    }
    
    /**
     * Show tooltip
     */
    function showTooltip(element, text) {
        hideTooltip(); // Hide any existing tooltip
        
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
        
        setTimeout(() => tooltip.classList.add('show'), 10);
    }
    
    /**
     * Hide tooltip
     */
    function hideTooltip() {
        const existingTooltip = document.querySelector('.tooltip');
        if (existingTooltip) {
            existingTooltip.remove();
        }
    }
    
    /**
     * Show notification
     */
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const iconMap = {
            success: 'check-circle',
            error: 'x-circle',
            warning: 'alert-triangle',
            info: 'info'
        };
        
        notification.innerHTML = `
            <div class="notification-content">
                <i data-feather="${iconMap[type] || 'info'}" width="20" height="20"></i>
                <span>${escapeHtml(message)}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                    <i data-feather="x" width="16" height="16"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Replace feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
        
        // Animate in
        setTimeout(() => notification.classList.add('show'), 10);
    }
    
    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Copy text to clipboard
     */
    window.copyToClipboard = function(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                showNotification('Text copied to clipboard!', 'success');
            }).catch(err => {
                console.error('Could not copy text: ', err);
                fallbackCopyToClipboard(text);
            });
        } else {
            fallbackCopyToClipboard(text);
        }
    };
    
    /**
     * Fallback copy to clipboard for older browsers
     */
    function fallbackCopyToClipboard(text) {
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
            showNotification('Text copied to clipboard!', 'success');
        } catch (err) {
            console.error('Fallback: Could not copy text: ', err);
            showNotification('Could not copy text', 'error');
        }
        
        document.body.removeChild(textArea);
    }
    
})();

/* Additional CSS for JavaScript-enhanced elements */
const additionalStyles = `
.field-error {
    color: var(--red-400);
    font-size: var(--font-size-xs);
    margin-top: var(--spacing-1);
    display: block;
}

.form-input.error,
.form-select.error,
.form-textarea.error {
    border-color: var(--red-500);
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.tooltip {
    position: absolute;
    background: var(--surface-1);
    color: var(--ink-200);
    padding: var(--spacing-2) var(--spacing-3);
    border-radius: var(--radius-md);
    font-size: var(--font-size-xs);
    border: 1px solid var(--ink-600);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1001;
    opacity: 0;
    transform: translateY(4px);
    transition: all var(--transition-fast);
    pointer-events: none;
    max-width: 200px;
    word-wrap: break-word;
}

.tooltip.show {
    opacity: 1;
    transform: translateY(0);
}

.tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -4px;
    border: 4px solid transparent;
    border-top-color: var(--ink-600);
}

.notification {
    position: fixed;
    top: var(--spacing-4);
    right: var(--spacing-4);
    background: var(--surface-1);
    border: 1px solid var(--ink-600);
    border-radius: var(--radius-lg);
    padding: var(--spacing-4);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    z-index: 1002;
    min-width: 300px;
    opacity: 0;
    transform: translateX(100%);
    transition: all var(--transition-normal);
}

.notification.show {
    opacity: 1;
    transform: translateX(0);
}

.notification-content {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
}

.notification-close {
    background: none;
    border: none;
    color: var(--ink-400);
    cursor: pointer;
    padding: var(--spacing-1);
    border-radius: var(--radius-sm);
    margin-left: auto;
    transition: color var(--transition-fast);
}

.notification-close:hover {
    color: var(--ink-200);
}

.notification-success {
    border-left: 4px solid var(--green-500);
}

.notification-error {
    border-left: 4px solid var(--red-500);
}

.notification-warning {
    border-left: 4px solid var(--amber-500);
}

.notification-info {
    border-left: 4px solid var(--blue-500);
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
`;

// Inject additional styles
if (document.head) {
    const styleSheet = document.createElement('style');
    styleSheet.textContent = additionalStyles;
    document.head.appendChild(styleSheet);
}