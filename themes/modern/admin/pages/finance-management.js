/**
 * Finance Management Page JavaScript
 * Interactive functionality untuk halaman Finance Management
 * 
 * @version 2.0.0
 * @author EPIC Hub Team
 */

(function() {
    'use strict';
    
    // Initialize page when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeFinanceManagement();
    });
    
    /**
     * Initialize Finance Management functionality
     */
    function initializeFinanceManagement() {
        // Initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
        
        // Initialize form functionality
        initializeFormFunctionality();
        
        // Initialize search functionality
        initializeSearchFunctionality();
        
        // Initialize export functionality
        initializeExportFunctionality();
        
        // Initialize modal functionality
        initializeModalFunctionality();
        
        // Initialize tooltips
        initializeTooltips();
        
        console.log('Finance Management page initialized successfully');
    }
    
    /**
     * Initialize form functionality
     */
    function initializeFormFunctionality() {
        // Auto-submit form when month changes
        const monthInput = document.getElementById('month');
        if (monthInput) {
            monthInput.addEventListener('change', function() {
                // Keep search query when changing month
                const form = this.closest('form');
                if (form) {
                    showLoadingState();
                    form.submit();
                }
            });
        }
        
        // Enhanced search functionality with debounce
        const searchInput = document.getElementById('search');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    // Auto-submit search form after 500ms of no typing
                    const form = this.closest('form');
                    if (form) {
                        showLoadingState();
                        form.submit();
                    }
                }, 500);
            });
            
            // Clear search on Escape key
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    const form = this.closest('form');
                    if (form) {
                        showLoadingState();
                        form.submit();
                    }
                }
            });
        }
    }
    
    /**
     * Initialize search functionality
     */
    function initializeSearchFunctionality() {
        // Highlight search terms in results
        const searchQuery = getUrlParameter('search');
        if (searchQuery && searchQuery.trim()) {
            highlightSearchTerms(searchQuery.trim());
        }
        
        // Add search suggestions
        addSearchSuggestions();
    }
    
    /**
     * Initialize export functionality
     */
    function initializeExportFunctionality() {
        // Export data functionality will be implemented here
        console.log('Export functionality initialized');
    }
    
    /**
     * Initialize modal functionality
     */
    function initializeModalFunctionality() {
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
     * Export finance data
     */
    window.exportFinanceData = function() {
        const selectedMonth = document.getElementById('month')?.value || '';
        const searchQuery = document.getElementById('search')?.value || '';
        
        // Build export URL with current filters
        const params = new URLSearchParams();
        if (selectedMonth) params.append('month', selectedMonth);
        if (searchQuery) params.append('search', searchQuery);
        params.append('export', 'csv');
        
        const exportUrl = window.location.pathname + '?' + params.toString();
        
        // Show loading notification
        showNotification('Preparing export...', 'info');
        
        // Create temporary link and trigger download
        const link = document.createElement('a');
        link.href = exportUrl;
        link.download = `finance-report-${selectedMonth || 'all'}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Show success notification
        setTimeout(() => {
            showNotification('Export completed successfully!', 'success');
        }, 1000);
    };
    
    /**
     * Show add transaction modal
     */
    window.showAddTransactionModal = function() {
        // Create modal if it doesn't exist
        let modal = document.getElementById('addTransactionModal');
        if (!modal) {
            modal = createAddTransactionModal();
            document.body.appendChild(modal);
        }
        
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
    };
    
    /**
     * Hide add transaction modal
     */
    window.hideAddTransactionModal = function() {
        const modal = document.getElementById('addTransactionModal');
        if (modal) {
            modal.style.display = 'none';
        }
    };
    
    /**
     * Create add transaction modal
     */
    function createAddTransactionModal() {
        const modal = document.createElement('div');
        modal.id = 'addTransactionModal';
        modal.className = 'modal';
        modal.style.display = 'none';
        
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Add New Transaction</h3>
                    <button type="button" class="modal-close" onclick="hideAddTransactionModal()">
                        <i data-feather="x" width="20" height="20"></i>
                    </button>
                </div>
                
                <form method="POST" class="modal-form" onsubmit="return handleAddTransaction(this)">
                    <input type="hidden" name="action" value="add_transaction">
                    
                    <div class="form-group">
                        <label for="transaction_type" class="form-label">Transaction Type</label>
                        <select name="transaction_type" id="transaction_type" class="form-select" required>
                            <option value="">Select type...</option>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" name="amount" id="amount" class="form-input" 
                                   step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="transaction_date" class="form-label">Date</label>
                            <input type="date" name="transaction_date" id="transaction_date" 
                                   class="form-input" value="${new Date().toISOString().split('T')[0]}" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-textarea" 
                                  placeholder="Enter transaction description..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" name="category" id="category" class="form-input" 
                               placeholder="e.g., Sales, Marketing, Office Supplies">
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="hideAddTransactionModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="plus" width="16" height="16"></i>
                            Add Transaction
                        </button>
                    </div>
                </form>
            </div>
        `;
        
        return modal;
    }
    
    /**
     * Handle add transaction form submission
     */
    window.handleAddTransaction = function(form) {
        // Validate form
        if (!validateTransactionForm(form)) {
            return false;
        }
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            showButtonLoading(submitBtn);
        }
        
        // Form will submit normally
        return true;
    };
    
    /**
     * Validate transaction form
     */
    function validateTransactionForm(form) {
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
        
        // Validate amount
        const amountField = form.querySelector('#amount');
        if (amountField) {
            const amount = parseFloat(amountField.value);
            if (isNaN(amount) || amount <= 0) {
                showFieldError(amountField, 'Amount must be greater than 0');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    /**
     * Show loading state
     */
    function showLoadingState() {
        // Add loading overlay to table
        const tableContainer = document.querySelector('.table-container');
        if (tableContainer && !tableContainer.querySelector('.loading-overlay')) {
            const overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            overlay.innerHTML = `
                <div class="loading-spinner">
                    <svg class="animate-spin" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12a9 9 0 11-6.219-8.56"/>
                    </svg>
                    <span>Loading...</span>
                </div>
            `;
            tableContainer.style.position = 'relative';
            tableContainer.appendChild(overlay);
        }
    }
    
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
     * Highlight search terms in results
     */
    function highlightSearchTerms(searchQuery) {
        const tableRows = document.querySelectorAll('.finance-table tbody tr');
        const terms = searchQuery.toLowerCase().split(' ').filter(term => term.length > 0);
        
        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => {
                let cellText = cell.textContent;
                let highlightedText = cellText;
                
                terms.forEach(term => {
                    const regex = new RegExp(`(${escapeRegExp(term)})`, 'gi');
                    highlightedText = highlightedText.replace(regex, '<mark>$1</mark>');
                });
                
                if (highlightedText !== cellText) {
                    cell.innerHTML = highlightedText;
                }
            });
        });
    }
    
    /**
     * Add search suggestions
     */
    function addSearchSuggestions() {
        const searchInput = document.getElementById('search');
        if (!searchInput) return;
        
        // Common search terms based on transaction data
        const suggestions = [
            'commission', 'payment', 'refund', 'bonus', 'salary',
            'marketing', 'office', 'travel', 'equipment', 'software'
        ];
        
        // Add datalist for suggestions
        const datalist = document.createElement('datalist');
        datalist.id = 'search-suggestions';
        
        suggestions.forEach(suggestion => {
            const option = document.createElement('option');
            option.value = suggestion;
            datalist.appendChild(option);
        });
        
        searchInput.setAttribute('list', 'search-suggestions');
        searchInput.parentNode.appendChild(datalist);
    }
    
    /**
     * Get URL parameter
     */
    function getUrlParameter(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
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
     * Escape RegExp special characters
     */
    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
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

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
}

.loading-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--spacing-3);
    color: var(--ink-200);
    font-size: var(--font-size-sm);
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

mark {
    background: var(--amber-400);
    color: var(--ink-900);
    padding: 1px 2px;
    border-radius: 2px;
}
`;

// Inject additional styles
if (document.head) {
    const styleSheet = document.createElement('style');
    styleSheet.textContent = additionalStyles;
    document.head.appendChild(styleSheet);
}