/**
 * Enhanced Frontend Validation System
 * For EPIC Registration System
 */

class EpicFormValidator {
    constructor(formSelector, options = {}) {
        this.form = document.querySelector(formSelector);
        this.options = {
            realTimeValidation: true,
            showLoadingStates: true,
            debounceDelay: 500,
            apiEndpoint: '/api/check-referral.php',
            ...options
        };
        
        this.validators = new Map();
        this.debounceTimers = new Map();
        this.loadingStates = new Map();
        
        this.init();
    }
    
    init() {
        if (!this.form) {
            console.warn('Form not found:', this.formSelector);
            return;
        }
        
        this.setupValidators();
        this.bindEvents();
        this.setupSubmitHandler();
    }
    
    setupValidators() {
        // Email validator
        this.addValidator('email', {
            validate: (value) => this.validateEmail(value),
            message: 'Masukkan alamat email yang valid'
        });
        
        // Password validator
        this.addValidator('password', {
            validate: (value) => this.validatePassword(value),
            message: 'Password minimal 8 karakter'
        });
        
        // Password confirmation validator
        this.addValidator('confirm_password', {
            validate: (value) => this.validatePasswordConfirmation(value),
            message: 'Konfirmasi password tidak cocok'
        });
        
        // Name validator
        this.addValidator('name', {
            validate: (value) => this.validateName(value),
            message: 'Nama lengkap minimal 2 karakter'
        });
        
        // Phone validator
        this.addValidator('phone', {
            validate: (value) => this.validatePhone(value),
            message: 'Nomor telepon tidak valid'
        });
        
        // Referral code validator (async)
        this.addValidator('referral_code', {
            validate: (value) => this.validateReferralCode(value),
            message: 'Kode referral tidak valid',
            async: true
        });
    }
    
    addValidator(fieldName, config) {
        this.validators.set(fieldName, config);
    }
    
    bindEvents() {
        const inputs = this.form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            if (this.options.realTimeValidation) {
                // Real-time validation on input
                input.addEventListener('input', (e) => {
                    this.debounceValidation(e.target);
                });
                
                // Immediate validation on blur
                input.addEventListener('blur', (e) => {
                    this.validateField(e.target);
                });
            }
            
            // Clear validation on focus
            input.addEventListener('focus', (e) => {
                this.clearFieldValidation(e.target);
            });
        });
    }
    
    debounceValidation(field) {
        const fieldName = field.name;
        
        // Clear existing timer
        if (this.debounceTimers.has(fieldName)) {
            clearTimeout(this.debounceTimers.get(fieldName));
        }
        
        // Set new timer
        const timer = setTimeout(() => {
            this.validateField(field);
        }, this.options.debounceDelay);
        
        this.debounceTimers.set(fieldName, timer);
    }
    
    async validateField(field) {
        const fieldName = field.name;
        const value = field.value.trim();
        const validator = this.validators.get(fieldName);
        
        if (!validator) {
            return true;
        }
        
        // Show loading state for async validators
        if (validator.async && this.options.showLoadingStates) {
            this.showLoadingState(field);
        }
        
        try {
            const isValid = await validator.validate(value);
            
            if (isValid) {
                this.showFieldSuccess(field);
                return true;
            } else {
                this.showFieldError(field, validator.message);
                return false;
            }
        } catch (error) {
            console.error('Validation error:', error);
            this.showFieldError(field, 'Terjadi kesalahan validasi');
            return false;
        } finally {
            if (validator.async && this.options.showLoadingStates) {
                this.hideLoadingState(field);
            }
        }
    }
    
    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    validatePassword(password) {
        return password.length >= 8;
    }
    
    validatePasswordConfirmation(confirmPassword) {
        const passwordField = this.form.querySelector('input[name="password"]');
        return passwordField && passwordField.value === confirmPassword;
    }
    
    validateName(name) {
        return name.length >= 2;
    }
    
    validatePhone(phone) {
        // Indonesian phone number validation
        const phoneRegex = /^(\+62|62|0)[0-9]{9,13}$/;
        return !phone || phoneRegex.test(phone.replace(/[\s\-]/g, ''));
    }
    
    async validateReferralCode(code) {
        if (!code) {
            return true; // Optional field
        }
        
        try {
            const response = await fetch(this.options.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ referral_code: code })
            });
            
            const data = await response.json();
            
            if (data.valid) {
                // Show referrer info if available
                this.showReferrerInfo(data.referrer);
                return true;
            } else {
                // Show suggestions if available
                if (data.suggestions && data.suggestions.length > 0) {
                    this.showReferralSuggestions(data.suggestions);
                }
                return false;
            }
        } catch (error) {
            console.error('Referral validation error:', error);
            throw error;
        }
    }
    
    showLoadingState(field) {
        const container = this.getFieldContainer(field);
        const loadingEl = container.querySelector('.validation-loading');
        
        if (!loadingEl) {
            const loading = document.createElement('div');
            loading.className = 'validation-loading';
            loading.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memvalidasi...';
            container.appendChild(loading);
        }
        
        this.loadingStates.set(field.name, true);
    }
    
    hideLoadingState(field) {
        const container = this.getFieldContainer(field);
        const loadingEl = container.querySelector('.validation-loading');
        
        if (loadingEl) {
            loadingEl.remove();
        }
        
        this.loadingStates.delete(field.name);
    }
    
    showFieldSuccess(field) {
        const container = this.getFieldContainer(field);
        
        // Remove existing validation messages
        this.clearFieldValidation(field);
        
        // Add success class
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        
        // Add success icon
        const successEl = document.createElement('div');
        successEl.className = 'validation-success';
        successEl.innerHTML = '<i class="fas fa-check text-success"></i>';
        container.appendChild(successEl);
    }
    
    showFieldError(field, message) {
        const container = this.getFieldContainer(field);
        
        // Remove existing validation messages
        this.clearFieldValidation(field);
        
        // Add error class
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        
        // Add error message
        const errorEl = document.createElement('div');
        errorEl.className = 'validation-error invalid-feedback';
        errorEl.textContent = message;
        container.appendChild(errorEl);
    }
    
    clearFieldValidation(field) {
        const container = this.getFieldContainer(field);
        
        // Remove validation classes
        field.classList.remove('is-valid', 'is-invalid');
        
        // Remove validation messages
        const validationEls = container.querySelectorAll('.validation-error, .validation-success, .validation-loading, .referrer-info, .referral-suggestions');
        validationEls.forEach(el => el.remove());
    }
    
    // Alias for compatibility
    clearFieldError(field) {
        return this.clearFieldValidation(field);
    }
    
    getFieldContainer(field) {
        return field.closest('.form-group') || field.closest('.mb-3') || field.parentElement;
    }
    
    showReferrerInfo(referrer) {
        const referralField = this.form.querySelector('input[name="referral_code"]');
        if (!referralField) return;
        
        const container = this.getFieldContainer(referralField);
        
        // Remove existing referrer info
        const existingInfo = container.querySelector('.referrer-info');
        if (existingInfo) {
            existingInfo.remove();
        }
        
        // Add referrer info
        const infoEl = document.createElement('div');
        infoEl.className = 'referrer-info alert alert-success mt-2';
        infoEl.innerHTML = `
            <i class="fas fa-user-check"></i>
            <strong>Referrer:</strong> ${referrer.name}
            ${referrer.total_referrals ? `<br><small>Total referral: ${referrer.total_referrals}</small>` : ''}
        `;
        container.appendChild(infoEl);
    }
    
    showReferralSuggestions(suggestions) {
        const referralField = this.form.querySelector('input[name="referral_code"]');
        if (!referralField) return;
        
        const container = this.getFieldContainer(referralField);
        
        // Remove existing suggestions
        const existingSuggestions = container.querySelector('.referral-suggestions');
        if (existingSuggestions) {
            existingSuggestions.remove();
        }
        
        // Add suggestions
        const suggestionsEl = document.createElement('div');
        suggestionsEl.className = 'referral-suggestions alert alert-warning mt-2';
        
        let suggestionsHtml = '<i class="fas fa-lightbulb"></i> <strong>Mungkin maksud Anda:</strong><br>';
        suggestions.forEach(suggestion => {
            suggestionsHtml += `<button type="button" class="btn btn-sm btn-outline-primary me-1 mt-1 suggestion-btn" data-code="${suggestion.referral_code}">${suggestion.referral_code}</button>`;
        });
        
        suggestionsEl.innerHTML = suggestionsHtml;
        container.appendChild(suggestionsEl);
        
        // Bind suggestion click events
        suggestionsEl.querySelectorAll('.suggestion-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                referralField.value = e.target.dataset.code;
                this.validateField(referralField);
            });
        });
    }
    
    setupSubmitHandler() {
        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Show form loading state
            this.showFormLoading();
            
            try {
                // Validate all fields
                const isValid = await this.validateForm();
                
                if (isValid) {
                    // Submit form
                    this.submitForm();
                } else {
                    this.showFormError('Mohon perbaiki kesalahan pada form');
                }
            } catch (error) {
                console.error('Form validation error:', error);
                this.showFormError('Terjadi kesalahan validasi form');
            } finally {
                this.hideFormLoading();
            }
        });
    }
    
    async validateForm() {
        const inputs = this.form.querySelectorAll('input, select, textarea');
        const validationPromises = [];
        
        inputs.forEach(input => {
            if (this.validators.has(input.name)) {
                validationPromises.push(this.validateField(input));
            }
        });
        
        const results = await Promise.all(validationPromises);
        return results.every(result => result === true);
    }
    
    showFormLoading() {
        const submitBtn = this.form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        }
    }
    
    hideFormLoading() {
        const submitBtn = this.form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = submitBtn.dataset.originalText || 'Daftar';
        }
    }
    
    showFormError(message) {
        // Remove existing error
        const existingError = this.form.querySelector('.form-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Add error message
        const errorEl = document.createElement('div');
        errorEl.className = 'form-error alert alert-danger';
        errorEl.textContent = message;
        
        this.form.insertBefore(errorEl, this.form.firstChild);
        
        // Scroll to error
        errorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    submitForm() {
        // Create hidden input to bypass client-side validation
        const bypassInput = document.createElement('input');
        bypassInput.type = 'hidden';
        bypassInput.name = 'client_validated';
        bypassInput.value = '1';
        this.form.appendChild(bypassInput);
        
        // Submit form
        this.form.submit();
    }
}

// Auto-initialize for registration forms
document.addEventListener('DOMContentLoaded', function() {
    // Initialize for registration forms
    const registrationForms = document.querySelectorAll('form[action*="register"], form#registration-form, form.registration-form');
    
    registrationForms.forEach(form => {
        new EpicFormValidator(`#${form.id}` || 'form', {
            realTimeValidation: true,
            showLoadingStates: true,
            debounceDelay: 500
        });
    });
    
    // Store original button text
    const submitBtns = document.querySelectorAll('button[type="submit"]');
    submitBtns.forEach(btn => {
        btn.dataset.originalText = btn.innerHTML;
    });
});

// Export for manual initialization
window.EpicFormValidator = EpicFormValidator;