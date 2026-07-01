/**
 * ============================================
 * Youtuber Bot - Main JavaScript
 * ============================================
 * نسخه: 2.0.0
 * 
 * JavaScript های اصلی پروژه شامل:
 * - AJAX Helper
 * - Toast Notifications
 * - Form Validation
 * - UI Components
 * - Helper Functions
 */

// ═══════════════════════════════════════════
// 1. راه‌اندازی اولیه
// ═══════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function() {
    console.log('%c🎬 Youtuber Bot Admin Panel', 'font-size: 20px; font-weight: bold; color: #8b5cf6;');
    console.log('%cنسخه 2.0.0', 'font-size: 12px; color: #10b981;');
    
    // راه‌اندازی کامپوننت‌ها
    initToasts();
    initForms();
    initModals();
    initSidebar();
    initKeyboardShortcuts();
    initAutoResize();
    initLazyLoading();
    initTooltips();
    
    // نمایش Flash Messages
    showFlashMessages();
});

// ═══════════════════════════════════════════
// 2. Global Variables
// ═══════════════════════════════════════════

const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';
const BASE_URL = window.location.origin;
const API_URL = `${BASE_URL}/admin/api`;

// ═══════════════════════════════════════════
// 3. AJAX Helper
// ═══════════════════════════════════════════

/**
 * ارسال درخواست AJAX
 */
async function apiRequest(endpoint, options = {}) {
    const url = endpoint.startsWith('http') ? endpoint : `${API_URL}${endpoint}`;
    
    const defaults = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': CSRF_TOKEN
        },
        credentials: 'same-origin'
    };
    
    const config = { ...defaults, ...options };
    
    // اگر body object باشد، به JSON تبدیل کن
    if (config.body && typeof config.body === 'object' && !(config.body instanceof FormData)) {
        config.body = JSON.stringify(config.body);
    }
    
    try {
        const response = await fetch(url, config);
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error?.message || `HTTP Error: ${response.status}`);
        }
        
        return data;
        
    } catch (error) {
        console.error('API Error:', error);
        showToast(error.message || 'خطا در ارتباط با سرور', 'error');
        throw error;
    }
}

/**
 * shorthand برای GET
 */
async function apiGet(endpoint, params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const url = queryString ? `${endpoint}?${queryString}` : endpoint;
    return apiRequest(url, { method: 'GET' });
}

/**
 * shorthand برای POST
 */
async function apiPost(endpoint, data = {}) {
    return apiRequest(endpoint, {
        method: 'POST',
        body: data
    });
}

/**
 * shorthand برای PUT
 */
async function apiPut(endpoint, data = {}) {
    return apiRequest(endpoint, {
        method: 'PUT',
        body: data
    });
}

/**
 * shorthand برای DELETE
 */
async function apiDelete(endpoint) {
    return apiRequest(endpoint, { method: 'DELETE' });
}

// ═══════════════════════════════════════════
// 4. Toast Notifications
// ═══════════════════════════════════════════

let toastContainer = null;

function initToasts() {
    // ساخت container اگر وجود نداره
    if (!document.getElementById('toastContainer')) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[100] flex flex-col gap-2';
        document.body.appendChild(toastContainer);
    } else {
        toastContainer = document.getElementById('toastContainer');
    }
}

/**
 * نمایش Toast Notification
 */
function showToast(message, type = 'info', duration = 3000) {
    if (!toastContainer) initToasts();
    
    const colors = {
        success: 'bg-green-500/95 border-green-400',
        error: 'bg-red-500/95 border-red-400',
        warning: 'bg-yellow-500/95 border-yellow-400',
        info: 'bg-blue-500/95 border-blue-400'
    };
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    const toast = document.createElement('div');
    toast.className = `${colors[type]} border text-white px-5 py-3 rounded-lg shadow-2xl flex items-center gap-3 min-w-[300px] max-w-[500px] animate-slideInRight`;
    toast.innerHTML = `
        <i class="fas ${icons[type]} text-xl"></i>
        <span class="flex-1 text-sm">${message}</span>
        <button onclick="this.parentElement.remove()" class="text-white/70 hover:text-white transition">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    toastContainer.appendChild(toast);
    
    // حذف خودکار
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.animation = 'slideInRight 0.3s ease-out reverse';
            setTimeout(() => toast.remove(), 300);
        }
    }, duration);
    
    return toast;
}

// ═══════════════════════════════════════════
// 5. Flash Messages
// ═══════════════════════════════════════════

function showFlashMessages() {
    const flashMessages = document.querySelectorAll('.flash-message');
    
    flashMessages.forEach(msg => {
        setTimeout(() => {
            msg.style.animation = 'fadeIn 0.3s ease-out reverse';
            setTimeout(() => msg.remove(), 300);
        }, 5000);
    });
}

// ═══════════════════════════════════════════
// 6. Form Management
// ═══════════════════════════════════════════

function initForms() {
    // جلوگیری از Double Submit
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (submitBtn && !submitBtn.classList.contains('btn-loading')) {
                submitBtn.classList.add('btn-loading');
                submitBtn.disabled = true;
                
                // بازگردانی بعد از 5 ثانیه (در صورت خطا)
                setTimeout(() => {
                    if (submitBtn.classList.contains('btn-loading')) {
                        submitBtn.classList.remove('btn-loading');
                        submitBtn.disabled = false;
                    }
                }, 5000);
            }
        });
    });
    
    // Real-time Validation
    document.querySelectorAll('[required]').forEach(field => {
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

/**
 * اعتبارسنجی یک فیلد
 */
function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    // Required
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'این فیلد الزامی است';
    }
    
    // Minlength
    if (isValid && field.hasAttribute('minlength') && value.length < parseInt(field.getAttribute('minlength'))) {
        isValid = false;
        errorMessage = `حداقل ${field.getAttribute('minlength')} کاراکتر لازم است`;
    }
    
    // Maxlength
    if (isValid && field.hasAttribute('maxlength') && value.length > parseInt(field.getAttribute('maxlength'))) {
        isValid = false;
        errorMessage = `حداکثر ${field.getAttribute('maxlength')} کاراکتر مجاز است`;
    }
    
    // Pattern
    if (isValid && field.hasAttribute('pattern')) {
        const pattern = new RegExp(field.getAttribute('pattern'));
        if (!pattern.test(value)) {
            isValid = false;
            errorMessage = 'فرمت وارد شده معتبر نیست';
        }
    }
    
    // Type Validation
    if (isValid) {
        switch (field.type) {
            case 'email':
                if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    isValid = false;
                    errorMessage = 'ایمیل معتبر نیست';
                }
                break;
                
            case 'url':
                if (value) {
                    try {
                        new URL(value);
                    } catch {
                        isValid = false;
                        errorMessage = 'URL معتبر نیست';
                    }
                }
                break;
                
            case 'number':
                if (value && isNaN(value)) {
                    isValid = false;
                    errorMessage = 'باید عدد باشد';
                }
                break;
        }
    }
    
    // نمایش خطا
    const errorElement = field.parentElement.querySelector('.field-error');
    
    if (isValid) {
        field.classList.remove('border-red-500');
        field.classList.add('border-green-500');
        if (errorElement) errorElement.remove();
    } else {
        field.classList.remove('border-green-500');
        field.classList.add('border-red-500');
        
        if (!errorElement) {
            const error = document.createElement('p');
            error.className = 'field-error text-red-400 text-xs mt-1';
            error.textContent = errorMessage;
            field.parentElement.appendChild(error);
        } else {
            errorElement.textContent = errorMessage;
        }
    }
    
    return isValid;
}

/**
 * اعتبارسنجی کل فرم
 */
function validateForm(form) {
    const fields = form.querySelectorAll('[required], [pattern], [minlength], [maxlength]');
    let isValid = true;
    
    fields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

// ═══════════════════════════════════════════
// 7. Modal Management
// ═══════════════════════════════════════════

function initModals() {
    // بستن Modal با Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.fixed.inset-0:not(.hidden)');
            openModals.forEach(modal => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        }
    });
    
    // بستن Modal با کلیک خارج
    document.querySelectorAll('.fixed.inset-0').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
                this.classList.remove('flex');
            }
        });
    });
}

/**
 * باز کردن Modal
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * بستن Modal
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }
}

// ═══════════════════════════════════════════
// 8. Sidebar Management
// ═══════════════════════════════════════════

function initSidebar() {
    // تشخیص صفحه فعال
    const currentPath = window.location.pathname;
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    
    sidebarLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href.replace('/admin', ''))) {
            link.classList.add('active');
        }
    });
}

/**
 * Toggle Sidebar (موبایل)
 */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
    
    if (overlay) {
        overlay.classList.toggle('open');
    }
}

// ═══════════════════════════════════════════
// 9. Keyboard Shortcuts
// ═══════════════════════════════════════════

function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl+Enter: Submit فرم
        if (e.ctrlKey && e.key === 'Enter') {
            const activeForm = document.activeElement.closest('form');
            if (activeForm) {
                e.preventDefault();
                activeForm.submit();
            }
        }
        
        // Ctrl+S: ذخیره (جلوگیری از default)
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            const activeForm = document.activeElement.closest('form');
            if (activeForm) {
                activeForm.dispatchEvent(new Event('submit'));
                showToast('در حال ذخیره...', 'info');
            }
        }
        
        // Ctrl+R: Reload
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            location.reload();
        }
        
        // ?: نمایش Help
        if (e.key === '?' && !isInputFocused()) {
            showKeyboardHelp();
        }
    });
}

/**
 * بررسی فوکوس روی Input
 */
function isInputFocused() {
    const activeElement = document.activeElement;
    return activeElement.tagName === 'INPUT' || 
           activeElement.tagName === 'TEXTAREA' || 
           activeElement.isContentEditable;
}

/**
 * نمایش Help Keyboard Shortcuts
 */
function showKeyboardHelp() {
    const shortcuts = [
        { key: 'Ctrl+Enter', action: 'ارسال فرم' },
        { key: 'Ctrl+S', action: 'ذخیره' },
        { key: 'Ctrl+R', action: 'بروزرسانی صفحه' },
        { key: 'Escape', action: 'بستن Modal' },
        { key: '?', action: 'نمایش این راهنما' }
    ];
    
    let html = '<div class="space-y-2">';
    shortcuts.forEach(s => {
        html += `
            <div class="flex items-center justify-between bg-white/5 rounded p-2">
                <kbd class="bg-white/10 px-2 py-1 rounded text-xs font-mono">${s.key}</kbd>
                <span class="text-white/70 text-sm">${s.action}</span>
            </div>
        `;
    });
    html += '</div>';
    
    showToast('برای مشاهده کلیدهای میانبر، ? را فشار دهید', 'info', 5000);
}

// ═══════════════════════════════════════════
// 10. Auto-resize Textarea
// ═══════════════════════════════════════════

function initAutoResize() {
    document.querySelectorAll('textarea[auto-resize]').forEach(textarea => {
        textarea.addEventListener('input', function() {
            autoResizeTextarea(this);
        });
        
        // اجرای اولیه
        autoResizeTextarea(textarea);
    });
}

/**
 * تغییر ارتفاع خودکار Textarea
 */
function autoResizeTextarea(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 300) + 'px';
}

// ═══════════════════════════════════════════
// 11. Lazy Loading
// ═══════════════════════════════════════════

function initLazyLoading() {
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
}

// ═══════════════════════════════════════════
// 12. Tooltips
// ═══════════════════════════════════════════

function initTooltips() {
    document.querySelectorAll('[data-tooltip]').forEach(element => {
        element.addEventListener('mouseenter', function() {
            const text = this.getAttribute('data-tooltip');
            const tooltip = document.createElement('div');
            tooltip.className = 'fixed bg-black/90 text-white text-xs px-3 py-2 rounded-lg z-[200] pointer-events-none';
            tooltip.textContent = text;
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = `${rect.top - 40}px`;
            tooltip.style.left = `${rect.left + rect.width / 2}px`;
            tooltip.style.transform = 'translateX(-50%)';
            
            document.body.appendChild(tooltip);
            this._tooltip = tooltip;
        });
        
        element.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                this._tooltip = null;
            }
        });
    });
}

// ═══════════════════════════════════════════
// 13. Helper Functions
// ═══════════════════════════════════════════

/**
 * کپی به Clipboard
 */
async function copyToClipboard(text, message = 'کپی شد!') {
    try {
        await navigator.clipboard.writeText(text);
        showToast(message, 'success');
        return true;
    } catch (err) {
        // Fallback برای مرورگرهای قدیمی
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            document.execCommand('copy');
            showToast(message, 'success');
            return true;
        } catch (err) {
            showToast('خطا در کپی', 'error');
            return false;
        } finally {
            document.body.removeChild(textarea);
        }
    }
}

/**
 * فرمت اعداد با جداکننده
 */
function formatNumber(num) {
    if (num === null || num === undefined) return '0';
    return new Intl.NumberFormat('fa-IR').format(num);
}

/**
 * فرمت ارز (تومان)
 */
function formatCurrency(amount) {
    return formatNumber(amount) + ' تومان';
}

/**
 * تبدیل زمان به "x دقیقه پیش"
 */
function timeAgo(dateString) {
    const date = new Date(dateString);
    const seconds = Math.floor((new Date() - date) / 1000);
    
    const intervals = [
        { label: 'سال', seconds: 31536000 },
        { label: 'ماه', seconds: 2592000 },
        { label: 'هفته', seconds: 604800 },
        { label: 'روز', seconds: 86400 },
        { label: 'ساعت', seconds: 3600 },
        { label: 'دقیقه', seconds: 60 },
        { label: 'ثانیه', seconds: 1 }
    ];
    
    for (const interval of intervals) {
        const count = Math.floor(seconds / interval.seconds);
        if (count >= 1) {
            return `${count} ${interval.label} پیش`;
        }
    }
    
    return 'همین الان';
}

/**
 * فرمت تاریخ
 */
function formatDate(dateString, format = 'datetime') {
    const date = new Date(dateString);
    
    const formats = {
        date: date.toLocaleDateString('fa-IR'),
        time: date.toLocaleTimeString('fa-IR'),
        datetime: date.toLocaleString('fa-IR')
    };
    
    return formats[format] || formats.datetime;
}

/**
 * truncate متن
 */
function truncateText(text, maxLength = 100) {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

/**
 * Escape HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Debounce
 */
function debounce(func, wait) {
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
 * Throttle
 */
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// ═══════════════════════════════════════════
// 14. Confirm Actions
// ═══════════════════════════════════════════

/**
 * تأیید عملیات
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * تأیید حذف
 */
function confirmDelete(callback) {
    confirmAction('آیا مطمئن هستید که می‌خواهید این مورد را حذف کنید؟\n\nاین عمل غیرقابل بازگشت است!', callback);
}

// ═══════════════════════════════════════════
// 15. Loading States
// ═══════════════════════════════════════════

/**
 * نمایش Loading روی دکمه
 */
function showButtonLoading(button, text = 'در حال پردازش...') {
    button.classList.add('btn-loading');
    button.disabled = true;
    button.dataset.originalText = button.innerHTML;
    button.innerHTML = `<span>${text}</span>`;
}

/**
 * مخفی کردن Loading دکمه
 */
function hideButtonLoading(button) {
    button.classList.remove('btn-loading');
    button.disabled = false;
    if (button.dataset.originalText) {
        button.innerHTML = button.dataset.originalText;
    }
}

/**
 * نمایش Loading Overlay
 */
function showLoadingOverlay(message = 'در حال بارگذاری...') {
    const overlay = document.createElement('div');
    overlay.id = 'loadingOverlay';
    overlay.className = 'fixed inset-0 bg-black/70 backdrop-blur-sm z-[200] flex items-center justify-center';
    overlay.innerHTML = `
        <div class="glass rounded-2xl p-8 text-center">
            <div class="spinner spinner-lg mx-auto mb-4"></div>
            <p class="text-white text-lg">${message}</p>
        </div>
    `;
    document.body.appendChild(overlay);
}

/**
 * مخفی کردن Loading Overlay
 */
function hideLoadingOverlay() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.remove();
    }
}

// ═══════════════════════════════════════════
// 16. Search & Filter
// ═══════════════════════════════════════════

/**
 * جستجو در جدول
 */
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    
    if (!input || !table) return;
    
    input.addEventListener('input', debounce(function() {
        const filter = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    }, 300));
}

/**
 * فیلتر لیست
 */
function filterList(items, query, searchFields = []) {
    if (!query) return items;
    
    return items.filter(item => {
        return searchFields.some(field => {
            const value = item[field]?.toString().toLowerCase() || '';
            return value.includes(query.toLowerCase());
        });
    });
}

// ═══════════════════════════════════════════
// 17. Pagination
// ═══════════════════════════════════════════

/**
 * بروزرسانی URL با Pagination
 */
function updatePage(page) {
    const url = new URL(window.location);
    url.searchParams.set('page', page);
    window.location.href = url.toString();
}

// ═══════════════════════════════════════════
// 18. Export Functions
// ═══════════════════════════════════════════

/**
 * Export به CSV
 */
function exportToCSV(data, filename = 'export.csv') {
    const BOM = '\uFEFF'; // برای UTF-8
    const csv = BOM + data;
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
    
    showToast('فایل دانلود شد', 'success');
}

/**
 * Export به JSON
 */
function exportToJSON(data, filename = 'export.json') {
    const json = JSON.stringify(data, null, 2);
    const blob = new Blob([json], { type: 'application/json;charset=utf-8;' });
    const link = document.createElement('a');
    
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
    
    showToast('فایل دانلود شد', 'success');
}

// ═══════════════════════════════════════════
// 19. Chart Helpers
// ═══════════════════════════════════════════

/**
 * تنظیمات پیش‌فرض Chart.js
 */
const chartDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                color: '#fff',
                font: { family: 'Vazirmatn', size: 11 },
                usePointStyle: true,
                padding: 15
            }
        },
        tooltip: {
            backgroundColor: 'rgba(0,0,0,0.8)',
            titleFont: { family: 'Vazirmatn' },
            bodyFont: { family: 'Vazirmatn' },
            padding: 12,
            cornerRadius: 8
        }
    },
    scales: {
        y: {
            ticks: {
                color: 'rgba(255,255,255,0.6)',
                font: { family: 'Vazirmatn', size: 10 }
            },
            grid: { color: 'rgba(255,255,255,0.05)' }
        },
        x: {
            ticks: {
                color: 'rgba(255,255,255,0.6)',
                font: { family: 'Vazirmatn', size: 10 }
            },
            grid: { color: 'rgba(255,255,255,0.05)' }
        }
    }
};

// ═══════════════════════════════════════════
// 20. Utility Functions
// ═══════════════════════════════════════════

/**
 * تولید UUID
 */
function generateUUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        const r = Math.random() * 16 | 0;
        const v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

/**
 * تولید رشته تصادفی
 */
function randomString(length = 16) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}

/**
 * بررسی Email معتبر
 */
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * بررسی URL معتبر
 */
function isValidUrl(url) {
    try {
        new URL(url);
        return true;
    } catch {
        return false;
    }
}

/**
 * اسکرول به بالا
 */
function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * اسکرول به عنصر
 */
function scrollToElement(selector) {
    const element = document.querySelector(selector);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

/**
 * Fullscreen Toggle
 */
function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
    } else {
        document.exitFullscreen();
    }
}

// ═══════════════════════════════════════════
// 21. Console Warning
// ═══════════════════════════════════════════

console.log(
    '%c⚠️ توجه!',
    'font-size: 24px; font-weight: bold; color: #ef4444;'
);
console.log(
    '%cاین کنسول برای توسعه‌دهندگان است. اگر کسی از شما خواست چیزی اینجا کپی کنید، احتمالاً کلاهبرداری است.',
    'font-size: 14px; color: #f59e0b;'
);

// ═══════════════════════════════════════════
// 22. Export Global Functions
// ═══════════════════════════════════════════

window.App = {
    // AJAX
    apiRequest,
    apiGet,
    apiPost,
    apiPut,
    apiDelete,
    
    // Toast
    showToast,
    
    // Modal
    openModal,
    closeModal,
    
    // Sidebar
    toggleSidebar,
    
    // Helpers
    copyToClipboard,
    formatNumber,
    formatCurrency,
    timeAgo,
    formatDate,
    truncateText,
    escapeHtml,
    debounce,
    throttle,
    
    // Validation
    validateField,
    validateForm,
    isValidEmail,
    isValidUrl,
    
    // Actions
    confirmAction,
    confirmDelete,
    
    // Loading
    showButtonLoading,
    hideButtonLoading,
    showLoadingOverlay,
    hideLoadingOverlay,
    
    // Export
    exportToCSV,
    exportToJSON,
    
    // Utilities
    generateUUID,
    randomString,
    scrollToTop,
    scrollToElement,
    toggleFullscreen,
    
    // Config
    CSRF_TOKEN,
    BASE_URL,
    API_URL,
    chartDefaults
};

// ═══════════════════════════════════════════
// پایان فایل
// ═══════════════════════════════════════════