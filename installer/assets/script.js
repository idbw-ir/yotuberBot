/**
 * ============================================
 * Youtuber Bot Installer - JavaScript
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * ============================================
 */

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 1. Ø±Ø§Ù‡â€ŒØ§Ù†Ø¯Ø§Ø²ÛŒ Ø§ÙˆÙ„ÛŒÙ‡
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
document.addEventListener('DOMContentLoaded', function() {
    console.log('ðŸŽ¬ Youtuber Bot Installer v2.1.0');
    
    initFormValidation();
    initCopyButtons();
    initAnimations();
    initTooltips();
    initPasswordStrength();
});

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 2. Ø§Ø¹ØªØ¨Ø§Ø±Ø³Ù†Ø¬ÛŒ ÙØ±Ù…â€ŒÙ‡Ø§
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner spinner-sm inline-block"></span> Ø¯Ø± Ø­Ø§Ù„ Ù¾Ø±Ø¯Ø§Ø²Ø´...';
                
                // ÙØ¹Ø§Ù„â€ŒØ³Ø§Ø²ÛŒ Ù…Ø¬Ø¯Ø¯ Ø¨Ø¹Ø¯ Ø§Ø² 5 Ø«Ø§Ù†ÛŒÙ‡ (Ø¨Ø±Ø§ÛŒ Ø®Ø·Ø§)
                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = submitBtn.dataset.originalText || 'Ø§Ø¯Ø§Ù…Ù‡';
                    }
                }, 5000);
            }
        });
    });
    
    // Ø§Ø¹ØªØ¨Ø§Ø±Ø³Ù†Ø¬ÛŒ real-time Ø¨Ø±Ø§ÛŒ ÙÛŒÙ„Ø¯Ù‡Ø§ÛŒ Ø®Ø§Øµ
    const requiredFields = document.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });
        
        field.addEventListener('input', function() {
            if (this.classList.contains('border-red-500')) {
                validateField(this);
            }
        });
    });
}

function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    
    // Ø¨Ø±Ø±Ø³ÛŒ required
    if (field.hasAttribute('required') && !value) {
        isValid = false;
    }
    
    // Ø¨Ø±Ø±Ø³ÛŒ minlength
    if (field.hasAttribute('minlength') && value.length < parseInt(field.getAttribute('minlength'))) {
        isValid = false;
    }
    
    // Ø¨Ø±Ø±Ø³ÛŒ pattern
    if (field.hasAttribute('pattern')) {
        const pattern = new RegExp(field.getAttribute('pattern'));
        if (!pattern.test(value)) {
            isValid = false;
        }
    }
    
    // Ø¨Ø±Ø±Ø³ÛŒ type
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        isValid = emailRegex.test(value);
    }
    
    if (field.type === 'url' && value) {
        try {
            new URL(value);
            isValid = value.startsWith('https://');
        } catch {
            isValid = false;
        }
    }
    
    // Ù†Ù…Ø§ÛŒØ´ Ø®Ø·Ø§
    if (isValid) {
        field.classList.remove('border-red-500');
        field.classList.add('border-green-500');
    } else {
        field.classList.remove('border-green-500');
        field.classList.add('border-red-500');
    }
    
    return isValid;
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 3. Ø¯Ú©Ù…Ù‡â€ŒÙ‡Ø§ÛŒ Ú©Ù¾ÛŒ
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function initCopyButtons() {
    const copyButtons = document.querySelectorAll('[data-copy]');
    
    copyButtons.forEach(btn => {
        btn.addEventListener('click', async function() {
            const textToCopy = this.getAttribute('data-copy');
            const originalText = this.innerHTML;
            
            try {
                await navigator.clipboard.writeText(textToCopy);
                
                this.innerHTML = 'âœ… Ú©Ù¾ÛŒ Ø´Ø¯!';
                this.classList.add('bg-green-500/30');
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.classList.remove('bg-green-500/30');
                }, 2000);
            } catch (err) {
                console.error('Ø®Ø·Ø§ Ø¯Ø± Ú©Ù¾ÛŒ:', err);
                
                // Fallback Ø¨Ø±Ø§ÛŒ Ù…Ø±ÙˆØ±Ú¯Ø±Ù‡Ø§ÛŒ Ù‚Ø¯ÛŒÙ…ÛŒ
                const textarea = document.createElement('textarea');
                textarea.value = textToCopy;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                
                this.innerHTML = 'âœ… Ú©Ù¾ÛŒ Ø´Ø¯!';
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 2000);
            }
        });
    });
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 4. Ø§Ù†ÛŒÙ…ÛŒØ´Ù†â€ŒÙ‡Ø§ÛŒ ÙˆØ±ÙˆØ¯
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function initAnimations() {
    // Ø§Ù†ÛŒÙ…ÛŒØ´Ù† fade-in Ø¨Ø±Ø§ÛŒ Ø§Ù„Ù…Ø§Ù†â€ŒÙ‡Ø§
    const animatedElements = document.querySelectorAll('.animate-fadeInUp, .animate-fadeIn, .animate-slideInRight');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0) translateX(0)';
            }
        });
    }, { threshold: 0.1 });
    
    animatedElements.forEach(el => {
        observer.observe(el);
    });
    
    // Ø§Ù†ÛŒÙ…ÛŒØ´Ù† Ø¨Ø±Ø§ÛŒ Ú©Ø§Ø±Øªâ€ŒÙ‡Ø§ÛŒ ÙˆØ¶Ø¹ÛŒØª
    const statusCards = document.querySelectorAll('.status-card');
    statusCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('animate-fadeInUp');
    });
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 5. Tooltip Ù‡Ø§
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', function() {
            const text = this.getAttribute('data-tooltip');
            const tooltipEl = document.createElement('div');
            tooltipEl.className = 'fixed bg-black/90 text-white text-xs px-3 py-2 rounded-lg z-50 pointer-events-none';
            tooltipEl.textContent = text;
            tooltipEl.style.top = `${this.getBoundingClientRect().top - 40}px`;
            tooltipEl.style.left = `${this.getBoundingClientRect().left + this.offsetWidth / 2}px`;
            tooltipEl.style.transform = 'translateX(-50%)';
            
            document.body.appendChild(tooltipEl);
            
            this._tooltip = tooltipEl;
        });
        
        tooltip.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                this._tooltip = null;
            }
        });
    });
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 6. Ø¨Ø±Ø±Ø³ÛŒ Ù‚Ø¯Ø±Øª Ø±Ù…Ø² Ø¹Ø¨ÙˆØ±
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function initPasswordStrength() {
    const passwordFields = document.querySelectorAll('input[type="password"]');
    
    passwordFields.forEach(field => {
        field.addEventListener('input', function() {
            const strength = calculatePasswordStrength(this.value);
            updatePasswordStrengthUI(this, strength);
        });
    });
}

function calculatePasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    return strength;
}

function updatePasswordStrengthUI(field, strength) {
    const container = field.parentElement;
    let strengthBar = container.querySelector('.password-strength');
    
    if (!strengthBar) {
        strengthBar = document.createElement('div');
        strengthBar.className = 'password-strength mt-2';
        strengthBar.innerHTML = `
            <div class="flex gap-1 mb-1">
                <div class="h-1 flex-1 rounded bg-white/10"></div>
                <div class="h-1 flex-1 rounded bg-white/10"></div>
                <div class="h-1 flex-1 rounded bg-white/10"></div>
                <div class="h-1 flex-1 rounded bg-white/10"></div>
                <div class="h-1 flex-1 rounded bg-white/10"></div>
            </div>
            <div class="text-xs text-white/60"></div>
        `;
        container.appendChild(strengthBar);
    }
    
    const bars = strengthBar.querySelectorAll('div > div');
    const label = strengthBar.querySelector('div:last-child');
    
    const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500'];
    const labels = ['Ø®ÛŒÙ„ÛŒ Ø¶Ø¹ÛŒÙ', 'Ø¶Ø¹ÛŒÙ', 'Ù…ØªÙˆØ³Ø·', 'Ù‚ÙˆÛŒ', 'Ø®ÛŒÙ„ÛŒ Ù‚ÙˆÛŒ'];
    
    bars.forEach((bar, index) => {
        bar.className = 'h-1 flex-1 rounded';
        if (index < strength) {
            bar.classList.add(colors[strength - 1]);
        } else {
            bar.classList.add('bg-white/10');
        }
    });
    
    if (strength > 0) {
        label.textContent = labels[strength - 1];
        label.className = `text-xs ${colors[strength - 1].replace('bg-', 'text-')}`;
    } else {
        label.textContent = '';
    }
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 7. ØªØ§ÛŒÛŒØ¯ Ù‚Ø¨Ù„ Ø§Ø² Ø­Ø°Ù
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 8. Ù†Ù…Ø§ÛŒØ´/Ù…Ø®ÙÛŒ Ú©Ø±Ø¯Ù† Ø§Ù„Ù…Ø§Ù†â€ŒÙ‡Ø§
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function toggleElement(selector, show = null) {
    const element = document.querySelector(selector);
    if (!element) return;
    
    if (show === null) {
        element.classList.toggle('hidden');
    } else if (show) {
        element.classList.remove('hidden');
    } else {
        element.classList.add('hidden');
    }
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 9. Ø§Ø³Ú©Ø±ÙˆÙ„ Ù†Ø±Ù…
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function smoothScrollTo(selector) {
    const element = document.querySelector(selector);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 10. Ù†Ù…Ø§ÛŒØ´ Ù†ÙˆØªÛŒÙÛŒÚ©ÛŒØ´Ù†
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function showNotification(message, type = 'info', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 bg-${type}-500/90 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-slideInRight`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, duration);
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 11. Auto-resize textarea
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function autoResizeTextarea(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
}

document.addEventListener('input', function(e) {
    if (e.target.tagName === 'TEXTAREA') {
        autoResizeTextarea(e.target);
    }
});

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 12. Keyboard Shortcuts
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
document.addEventListener('keydown', function(e) {
    // Ctrl + Enter Ø¨Ø±Ø§ÛŒ submit ÙØ±Ù…
    if (e.ctrlKey && e.key === 'Enter') {
        const form = document.activeElement.closest('form');
        if (form) {
            form.submit();
        }
    }
    
    // Escape Ø¨Ø±Ø§ÛŒ Ø¨Ø³ØªÙ† modal Ù‡Ø§
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal:not(.hidden)');
        modals.forEach(modal => modal.classList.add('hidden'));
    }
});

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 13. Lazy Loading Ø¨Ø±Ø§ÛŒ ØªØµØ§ÙˆÛŒØ±
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });
    
    document.querySelectorAll('img.lazy').forEach(img => {
        imageObserver.observe(img);
    });
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 14. Prevent Double Submit
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
let isSubmitting = false;

document.addEventListener('submit', function(e) {
    if (isSubmitting) {
        e.preventDefault();
        return false;
    }
    
    const form = e.target;
    if (form.tagName === 'FORM') {
        isSubmitting = true;
        
        setTimeout(() => {
            isSubmitting = false;
        }, 3000);
    }
}, true);

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 15. Console Welcome Message
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
console.log('%cðŸŽ¬ Youtuber Bot Installer', 'font-size: 20px; font-weight: bold; color: #667eea;');
console.log('%cÙ†Ø³Ø®Ù‡ 2.1.0', 'font-size: 12px; color: #10b981;');
console.log('%câš ï¸ ØªÙˆØ¬Ù‡: Ø§ÛŒÙ† Ú©Ù†Ø³ÙˆÙ„ Ø¨Ø±Ø§ÛŒ ØªÙˆØ³Ø¹Ù‡â€ŒØ¯Ù‡Ù†Ø¯Ú¯Ø§Ù† Ø§Ø³Øª. Ø§Ú¯Ø± Ú©Ø³ÛŒ Ø§Ø² Ø´Ù…Ø§ Ø®ÙˆØ§Ø³Øª Ú†ÛŒØ²ÛŒ Ø§ÛŒÙ†Ø¬Ø§ Ú©Ù¾ÛŒ Ú©Ù†ÛŒØ¯ØŒ Ø§Ø­ØªÙ…Ø§Ù„Ø§Ù‹ Ú©Ù„Ø§Ù‡Ø¨Ø±Ø¯Ø§Ø±ÛŒ Ø§Ø³Øª.', 'font-size: 11px; color: #f59e0b;');

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 16. Export Ø¨Ø±Ø§ÛŒ Ø§Ø³ØªÙØ§Ø¯Ù‡ Ø®Ø§Ø±Ø¬ÛŒ
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
window.Installer = {
    showNotification,
    toggleElement,
    smoothScrollTo,
    confirmAction,
    validateField,
    calculatePasswordStrength
};