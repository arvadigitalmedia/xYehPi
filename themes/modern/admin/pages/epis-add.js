/**
 * EPIS Add Page JavaScript
 * Interaktivitas untuk halaman Create EPIS Account standalone
 * 
 * @version 1.1.0
 * @author EPIC Hub Team
 */

(function() {
    'use strict';
    
    // Initialize page when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeEpisAddPage();
    });
    
    /**
     * Initialize EPIS Add Page functionality
     */
    function initializeEpisAddPage() {
        // Initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
        
        // Initialize creation method toggle
        initializeCreationMethodToggle();
        
        // Initialize form validation
        initializeFormValidation();
        
        // Initialize form enhancements
        initializeFormEnhancements();
        
        // Initialize tooltips
        initializeTooltips();
        
        // Initialize commission form handling
        initializeCommissionForm();
        
        // Initialize card interactions
        initializeCardInteractions();
        
        console.log('EPIS Add page initialized successfully');
    }
    
    /**
     * Creation Method Toggle - Updated for dropdown
     */
    function initializeCreationMethodToggle() {
        const methodSelect = document.getElementById('creation_method');
        const existingUserSection = document.getElementById('existing-user-section');
        const manualInputSection = document.getElementById('manual-input-section');
        
        if (!methodSelect || !existingUserSection || !manualInputSection) return;
        
        methodSelect.addEventListener('change', function() {
            toggleCreationMethod(this.value);
            
            // Smooth scroll to next section after selection
            if (this.value !== '') {
                setTimeout(() => {
                    const targetSection = this.value === 'existing_user' ? existingUserSection : manualInputSection;
                    targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 300);
            }
        });
        
        // Initialize based on current selection
        toggleCreationMethod(methodSelect.value);
    }
    
    function toggleCreationMethod(method) {
        const existingUserSection = document.getElementById('existing-user-section');
        const manualInputSection = document.getElementById('manual-input-section');
        const userSelect = document.getElementById('user_id');
        const manualFields = manualInputSection.querySelectorAll('input[required]');
        
        if (method === 'existing_user') {
            existingUserSection.style.display = 'block';
            manualInputSection.style.display = 'none';
            
            // Set user_id as required
            userSelect.setAttribute('required', 'required');
            
            // Remove required from manual fields
            manualFields.forEach(field => {
                field.removeAttribute('required');
            });
        } else if (method === 'manual_input') {
            existingUserSection.style.display = 'none';
            manualInputSection.style.display = 'block';
            
            // Remove required from user_id
            userSelect.removeAttribute('required');
            
            // Set manual fields as required
            manualFields.forEach(field => {
                field.setAttribute('required', 'required');
            });
        }
    }
    
    /**
     * Initialize form validation
     */
    function initializeFormValidation() {
        const form = document.getElementById('createEpisForm');
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
        });
        
        // Real-time validation for existing fields
        const userSelect = document.getElementById('user_id');
        const territoryName = document.getElementById('territory_name');
        const maxRecruits = document.getElementById('max_epic_recruits');
        
        // Manual input fields
        const manualName = document.getElementById('manual_name');
        const manualEmail = document.getElementById('manual_email');
        const manualPassword = document.getElementById('manual_password');
        
        if (userSelect) {
            userSelect.addEventListener('change', function() {
                validateField(this, 'Please select an EPIC user to promote');
            });
        }
        
        if (territoryName) {
            territoryName.addEventListener('blur', function() {
                validateField(this, 'Territory name is required');
            });
        }
        
        if (maxRecruits) {
            maxRecruits.addEventListener('blur', function() {
                validateNumericField(this, 1, 1000000, 'Maximum recruits must be between 1 and 1,000,000');
            });
        }
        
        // Manual input validations
        if (manualName) {
            manualName.addEventListener('blur', function() {
                validateManualName(this);
            });
        }
        
        if (manualEmail) {
            manualEmail.addEventListener('blur', function() {
                validateManualEmail(this);
            });
        }
        
        if (manualPassword) {
            manualPassword.addEventListener('blur', function() {
                validateManualPassword(this);
            });
        }
    }
    
    /**
     * Validate manual input name
     */
    function validateManualName(field) {
        const value = field.value.trim();
        if (field.hasAttribute('required') && !value) {
            showFieldError(field, 'Full name is required');
            return false;
        }
        if (value && value.length < 2) {
            showFieldError(field, 'Name must be at least 2 characters');
            return false;
        }
        clearFieldError(field);
        return true;
    }
    
    /**
     * Validate manual input email
     */
    function validateManualEmail(field) {
        const value = field.value.trim();
        if (field.hasAttribute('required') && !value) {
            showFieldError(field, 'Email address is required');
            return false;
        }
        if (value && !isValidEmail(value)) {
            showFieldError(field, 'Please enter a valid email address');
            return false;
        }
        clearFieldError(field);
        return true;
    }
    
    /**
     * Validate manual input password
     */
    function validateManualPassword(field) {
        const value = field.value.trim();
        if (field.hasAttribute('required') && !value) {
            showFieldError(field, 'Password is required');
            return false;
        }
        if (value && value.length < 8) {
            showFieldError(field, 'Password must be at least 8 characters');
            return false;
        }
        clearFieldError(field);
        return true;
    }
    
    /**
     * Check if email is valid
     */
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    /**
     * Validate entire form
     */
    function validateForm() {
        let isValid = true;
        
        // Validate user selection
        const userSelect = document.getElementById('user_id');
        if (userSelect && !userSelect.value) {
            showFieldError(userSelect, 'Please select an EPIC user to promote');
            isValid = false;
        } else {
            clearFieldError(userSelect);
        }
        
        // Validate territory name
        const territoryName = document.getElementById('territory_name');
        if (territoryName && !territoryName.value.trim()) {
            showFieldError(territoryName, 'Territory name is required');
            isValid = false;
        } else {
            clearFieldError(territoryName);
        }
        
        // Validate numeric fields
        const maxRecruits = document.getElementById('max_epic_recruits');
        if (maxRecruits) {
            const value = parseInt(maxRecruits.value);
            if (isNaN(value) || value < 1 || value > 1000000) {
            showFieldError(maxRecruits, 'Maximum recruits must be between 1 and 1,000,000');
                isValid = false;
            } else {
                clearFieldError(maxRecruits);
            }
        }
        
        // Validate commission rates
        const recruitmentRate = document.getElementById('recruitment_commission_rate');
        if (recruitmentRate) {
            const value = parseFloat(recruitmentRate.value);
            if (isNaN(value) || value < 0 || value > 100) {
                showFieldError(recruitmentRate, 'Commission rate must be between 0% and 100%');
                isValid = false;
            } else {
                clearFieldError(recruitmentRate);
            }
        }
        
        const indirectRate = document.getElementById('indirect_commission_rate');
        if (indirectRate) {
            const value = parseFloat(indirectRate.value);
            if (isNaN(value) || value < 0 || value > 100) {
                showFieldError(indirectRate, 'Commission rate must be between 0% and 100%');
                isValid = false;
            } else {
                clearFieldError(indirectRate);
            }
        }
        
        return isValid;
    }
    
    /**
     * Validate individual field
     */
    function validateField(field, errorMessage) {
        if (!field.value.trim()) {
            showFieldError(field, errorMessage);
            return false;
        } else {
            clearFieldError(field);
            return true;
        }
    }
    
    /**
     * Validate numeric field
     */
    function validateNumericField(field, min, max, errorMessage) {
        const value = parseFloat(field.value);
        if (isNaN(value) || value < min || value > max) {
            showFieldError(field, errorMessage);
            return false;
        } else {
            clearFieldError(field);
            return true;
        }
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
        if (!field) return;
        
        field.classList.remove('error');
        
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
    }
    
    /**
     * Initialize form enhancements
     */
    function initializeFormEnhancements() {
        // Auto-format commission rates
        const commissionFields = document.querySelectorAll('input[type="number"][step="0.01"]');
        commissionFields.forEach(field => {
            field.addEventListener('blur', function() {
                if (this.value && !isNaN(this.value)) {
                    this.value = parseFloat(this.value).toFixed(2);
                }
            });
        });
        
        // Character counter for territory description
        const territoryDesc = document.getElementById('territory_description');
        if (territoryDesc) {
            const maxLength = 500;
            
            const counter = document.createElement('small');
            counter.className = 'char-counter';
            counter.style.float = 'right';
            counter.style.color = '#666';
            
            territoryDesc.parentNode.appendChild(counter);
            
            function updateCounter() {
                const remaining = maxLength - territoryDesc.value.length;
                counter.textContent = `${remaining} characters remaining`;
                
                if (remaining < 50) {
                    counter.style.color = '#e74c3c';
                } else if (remaining < 100) {
                    counter.style.color = '#f39c12';
                } else {
                    counter.style.color = '#666';
                }
            }
            
            territoryDesc.addEventListener('input', updateCounter);
            territoryDesc.setAttribute('maxlength', maxLength);
            updateCounter();
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
     * Show tooltip
     */
    function showTooltip(element, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        tooltip.style.position = 'absolute';
        tooltip.style.background = '#333';
        tooltip.style.color = '#fff';
        tooltip.style.padding = '8px 12px';
        tooltip.style.borderRadius = '4px';
        tooltip.style.fontSize = '12px';
        tooltip.style.zIndex = '1000';
        tooltip.style.pointerEvents = 'none';
        
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
        
        // Store reference for cleanup
        element._tooltip = tooltip;
    }
    
    /**
     * Hide tooltip
     */
    function hideTooltip() {
        const tooltips = document.querySelectorAll('.tooltip');
        tooltips.forEach(tooltip => tooltip.remove());
    }
    
    /**
     * Initialize Commission Form Handling
     */
    function initializeCommissionForm() {
        const commissionForm = document.getElementById('commissionForm');
        const cancelCommissionBtn = document.getElementById('cancelCommissionBtn');
        const directCommissionRate = document.getElementById('direct_commission_rate');
        const indirectCommissionRate = document.getElementById('indirect_commission_rate');
        
        if (!commissionForm) return;
        
        // Commission form validation
        if (directCommissionRate) {
            directCommissionRate.addEventListener('blur', function() {
                validateCommissionRate(this, 'Direct commission rate');
            });
        }
        
        if (indirectCommissionRate) {
            indirectCommissionRate.addEventListener('blur', function() {
                validateCommissionRate(this, 'Indirect commission rate');
            });
        }
        
        // Cancel button handling
        if (cancelCommissionBtn) {
            cancelCommissionBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
                    // Reset form to original values
                    commissionForm.reset();
                    showNotification('Changes cancelled', 'info');
                }
            });
        }
        
        // Form submission
        commissionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateCommissionForm()) {
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i data-feather="loader" width="16" height="16"></i> Saving...';
                submitBtn.disabled = true;
                
                // Simulate API call (replace with actual implementation)
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    showNotification('Commission settings saved successfully!', 'success');
                    feather.replace();
                }, 1500);
            }
        });
    }
    
    /**
     * Validate commission rate
     */
    function validateCommissionRate(field, fieldName) {
        const value = parseFloat(field.value);
        
        if (field.hasAttribute('required') && (!field.value || isNaN(value))) {
            showFieldError(field, `${fieldName} is required`);
            return false;
        }
        
        if (!isNaN(value) && (value < 0 || value > 100)) {
            showFieldError(field, `${fieldName} must be between 0 and 100`);
            return false;
        }
        
        clearFieldError(field);
        return true;
    }
    
    /**
     * Validate commission form
     */
    function validateCommissionForm() {
        const directRate = document.getElementById('direct_commission_rate');
        const indirectRate = document.getElementById('indirect_commission_rate');
        
        let isValid = true;
        
        if (directRate && !validateCommissionRate(directRate, 'Direct commission rate')) {
            isValid = false;
        }
        
        if (indirectRate && !validateCommissionRate(indirectRate, 'Indirect commission rate')) {
            isValid = false;
        }
        
        return isValid;
    }
    
    /**
     * Initialize Card Interactions
     */
    function initializeCardInteractions() {
        // Add visual feedback when cards are interacted with
        const cards = document.querySelectorAll('.form-card');
        
        cards.forEach(card => {
            // Add focus-within class for better visual feedback
            card.addEventListener('focusin', function() {
                this.classList.add('card-focused');
            });
            
            card.addEventListener('focusout', function() {
                this.classList.remove('card-focused');
            });
        });
        
        // Smooth scroll between cards when navigating
        const createAccountBtn = document.querySelector('#createEpisForm button[type="submit"]');
        const commissionCard = document.querySelector('#commissionForm').closest('.admin-content');
        
        if (createAccountBtn && commissionCard) {
            createAccountBtn.addEventListener('click', function(e) {
                // If form is valid, scroll to commission card
                if (validateForm()) {
                    setTimeout(() => {
                        commissionCard.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'start' 
                        });
                    }, 500);
                }
            });
        }
    }
    
    /**
     * Show notification
     */
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i data-feather="${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info'}" width="16" height="16"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        feather.replace();
        
        // Show notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        // Hide notification after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
    
})();