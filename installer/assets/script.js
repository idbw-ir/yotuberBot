/**
 * ============================================
 * Youtuber Bot Installer - JavaScript
 * نسخه: 2.0.0
 * ============================================
 */

// ──────────────────────────────────────
// 1. راه‌اندازی اولیه
// ──────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎬 Youtuber Bot Installer v2.0.0');
    
    initFormValidation();
    initCopyButtons();
    initAnimations();
    initTooltips();
    initPasswordStrength();
});

// ──────────────────────────────────────
// 2. اعتبارسنجی فرم‌ها
// ──────────────────────────────────────
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner spinner-sm inline-block"></span> در حال پردازش...';
                
                // فعال‌سازی مجدد بعد از 5 ثانیه (برای خطا)
                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = submitBtn.dataset.originalText || 'ادامه';
                    }
                }, 5000);
            }
        });
    });
    
    // اعتبارسنجی real-time برای فیلدهای خاص
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
    
    // بررسی required
    if (field.hasAttribute('required') && !value) {
        isValid = false;
    }
    
    // بررسی minlength
    if (field.hasAttribute('minlength') && value.length < parseInt(field.getAttribute('minlength'))) {
        isValid = false;
    }
    
    // بررسی pattern
    if (field.hasAttribute('pattern')) {
        const pattern = new RegExp(field.getAttribute('pattern'));
        if (!pattern.test(value)) {
            isValid = false;
        }
    }
    
    // بررسی type
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
    
    // نمایش خطا
    if (isValid) {
        field.classList.remove('border-red-500');
        field.classList.add('border-green-500');
    } else {
        field.classList.remove('border-green-500');
        field.classList.add('border-red-500');
    }
    
    return isValid;
}

// ──────────────────────────────────────
// 3. دکمه‌های کپی
// ──────────────────────────────────────
function initCopyButtons() {
    const copyButtons = document.querySelectorAll('[data-copy]');
    
    copyButtons.forEach(btn => {
        btn.addEventListener('click', async function() {
            const textToCopy = this.getAttribute('data-copy');
            const originalText = this.innerHTML;
            
            try {
                await navigator.clipboard.writeText(textToCopy);
                
                this.innerHTML = '✅ کپی شد!';
                this.classList.add('bg-green-500/30');
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.classList.remove('bg-green-500/30');
                }, 2000);
            } catch (err) {
                console.error('خطا در کپی:', err);
                
                // Fallback برای مرورگرهای قدیمی
                const textarea = document.createElement('textarea');
                textarea.value = textToCopy;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                
                this.innerHTML = '✅ کپی شد!';
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 2000);
            }
        });
    });
}

// ──────────────────────────────────────
// 4. انیمیشن‌های ورود
// ──────────────────────────────────────
function initAnimations() {
    // انیمیشن fade-in برای المان‌ها
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
    
    // انیمیشن برای کارت‌های وضعیت
    const statusCards = document.querySelectorAll('.status-card');
    statusCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('animate-fadeInUp');
    });
}

// ──────────────────────────────────────
// 5. Tooltip ها
// ──────────────────────────────────────
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

// ──────────────────────────────────────
// 6. بررسی قدرت رمز عبور
// ──────────────────────────────────────
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
    const labels = ['خیلی ضعیف', 'ضعیف', 'متوسط', 'قوی', 'خیلی قوی'];
    
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

// ──────────────────────────────────────
// 7. تایید قبل از حذف
// ──────────────────────────────────────
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// ──────────────────────────────────────
// 8. نمایش/مخفی کردن المان‌ها
// ──────────────────────────────────────
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

// ──────────────────────────────────────
// 9. اسکرول نرم
// ──────────────────────────────────────
function smoothScrollTo(selector) {
    const element = document.querySelector(selector);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// ──────────────────────────────────────
// 10. نمایش نوتیفیکیشن
// ──────────────────────────────────────
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

// ──────────────────────────────────────
// 11. Auto-resize textarea
// ──────────────────────────────────────
function autoResizeTextarea(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
}

document.addEventListener('input', function(e) {
    if (e.target.tagName === 'TEXTAREA') {
        autoResizeTextarea(e.target);
    }
});

// ──────────────────────────────────────
// 12. Keyboard Shortcuts
// ──────────────────────────────────────
document.addEventListener('keydown', function(e) {
    // Ctrl + Enter برای submit فرم
    if (e.ctrlKey && e.key === 'Enter') {
        const form = document.activeElement.closest('form');
        if (form) {
            form.submit();
        }
    }
    
    // Escape برای بستن modal ها
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal:not(.hidden)');
        modals.forEach(modal => modal.classList.add('hidden'));
    }
});

// ──────────────────────────────────────
// 13. Lazy Loading برای تصاویر
// ──────────────────────────────────────
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

// ──────────────────────────────────────
// 14. Prevent Double Submit
// ──────────────────────────────────────
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

// ──────────────────────────────────────
// 15. Console Welcome Message
// ──────────────────────────────────────
console.log('%c🎬 Youtuber Bot Installer', 'font-size: 20px; font-weight: bold; color: #667eea;');
console.log('%cنسخه 2.0.0', 'font-size: 12px; color: #10b981;');
console.log('%c⚠️ توجه: این کنسول برای توسعه‌دهندگان است. اگر کسی از شما خواست چیزی اینجا کپی کنید، احتمالاً کلاهبرداری است.', 'font-size: 11px; color: #f59e0b;');

// ──────────────────────────────────────
// 16. Export برای استفاده خارجی
// ──────────────────────────────────────
window.Installer = {
    showNotification,
    toggleElement,
    smoothScrollTo,
    confirmAction,
    validateField,
    calculatePasswordStrength
};