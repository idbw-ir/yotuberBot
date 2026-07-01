/**
 * ============================================
 * Youtuber Bot - Main JavaScript
 * ============================================
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * 
 * JavaScript Ù‡Ø§ÛŒ Ø§ØµÙ„ÛŒ Ù¾Ø±ÙˆÚ˜Ù‡ Ø´Ø§Ù…Ù„:
 * - AJAX Helper
 * - Toast Notifications
 * - Form Validation
 * - UI Components
 * - Helper Functions
 */

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 1. Ø±Ø§Ù‡â€ŒØ§Ù†Ø¯Ø§Ø²ÛŒ Ø§ÙˆÙ„ÛŒÙ‡
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

document.addEventListener('DOMContentLoaded', function() {
    console.log('%cðŸŽ¬ Youtuber Bot Admin Panel', 'font-size: 20px; font-weight: bold; color: #8b5cf6;');
    console.log('%cÙ†Ø³Ø®Ù‡ 2.1.0', 'font-size: 12px; color: #10b981;');
    
    // Ø±Ø§Ù‡â€ŒØ§Ù†Ø¯Ø§Ø²ÛŒ Ú©Ø§Ù…Ù¾ÙˆÙ†Ù†Øªâ€ŒÙ‡Ø§
    initToasts();
    initForms();
    initModals();
    initSidebar();
    initKeyboardShortcuts();
    initAutoResize();
    initLazyLoading();
    initTooltips();
    
    // Ù†Ù…Ø§ÛŒØ´ Flash Messages
    showFlashMessages();
});

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 2. Global Variables
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';
const BASE_URL = window.location.origin;
const API_URL = `${BASE_URL}/admin/api`;

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 3. AJAX Helper
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

/**
 * Ø§Ø±Ø³Ø§Ù„ Ø¯Ø±Ø®ÙˆØ§Ø³Øª AJAX
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
    
    // Ø§Ú¯Ø± body object Ø¨Ø§Ø´Ø¯ØŒ Ø¨Ù‡ JSON ØªØ¨Ø¯ÛŒÙ„ Ú©Ù†
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
        showToast(error.message || 'Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
        throw error;
    }
}

/**
 * shorthand Ø¨Ø±Ø§ÛŒ GET
 */
async function apiGet(endpoint, params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const url = queryString ? `${endpoint}?${queryString}` : endpoint;
    return apiRequest(url, { method: 'GET' });
}

/**
 * shorthand Ø¨Ø±Ø§ÛŒ POST
 */
async function apiPost(endpoint, data = {}) {
    return apiRequest(endpoint, {
        method: 'POST',
        body: data
    });
}

/**
 * shorthand Ø¨Ø±Ø§ÛŒ PUT
 */
async function apiPut(endpoint, data = {}) {
    return apiRequest(endpoint, {
        method: 'PUT',
        body: data
    });
}

/**
 * shorthand Ø¨Ø±Ø§ÛŒ DELETE
 */
async function apiDelete(endpoint) {
    return apiRequest(endpoint, { method: 'DELETE' });
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 4. Toast Notifications
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

let toastContainer = null;

function initToasts() {
    // Ø³Ø§Ø®Øª container Ø§Ú¯Ø± ÙˆØ¬ÙˆØ¯ Ù†Ø¯Ø§Ø±Ù‡
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
 * Ù†Ù…Ø§ÛŒØ´ Toast Notification
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
    
    // Ø­Ø°Ù Ø®ÙˆØ¯Ú©Ø§Ø±
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.animation = 'slideInRight 0.3s ease-out reverse';
            setTimeout(() => toast.remove(), 300);
        }
    }, duration);
    
    return toast;
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 5. Flash Messages
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

function showFlashMessages() {
    const flashMessages = document.querySelectorAll('.flash-message');
    
    flashMessages.forEach(msg => {
        setTimeout(() => {
            msg.style.animation = 'fadeIn 0.3s ease-out reverse';
            setTimeout(() => msg.remove(), 300);
        }, 5000);
    });
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 6. Form Management
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

function initForms() {
    // Ø¬Ù„ÙˆÚ¯ÛŒØ±ÛŒ Ø§Ø² Double Submit
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (submitBtn && !submitBtn.classList.contains('btn-loading')) {
                submitBtn.classList.add('btn-loading');
                submitBtn.disabled = true;
                
                // Ø¨Ø§Ø²Ú¯Ø±Ø¯Ø§Ù†ÛŒ Ø¨Ø¹Ø¯ Ø§Ø² 5 Ø«Ø§Ù†ÛŒÙ‡ (Ø¯Ø± ØµÙˆØ±Øª Ø®Ø·Ø§)
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
 * Ø§Ø¹ØªØ¨Ø§Ø±Ø³Ù†Ø¬ÛŒ ÛŒÚ© ÙÛŒÙ„Ø¯
 */
function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    // Required
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'Ø§ÛŒÙ† ÙÛŒÙ„Ø¯ Ø§Ù„Ø²Ø§Ù…ÛŒ Ø§Ø³Øª';
    }
    
    // Minlength
    if (isValid && field.hasAttribute('minlength') && value.length < parseInt(field.getAttribute('minlength'))) {
        isValid = false;
        errorMessage = `Ø­Ø¯Ø§Ù‚Ù„ ${field.getAttribute('minlength')} Ú©Ø§Ø±Ø§Ú©ØªØ± Ù„Ø§Ø²Ù… Ø§Ø³Øª`;
    }
    
    // Maxlength
    if (isValid && field.hasAttribute('maxlength') && value.length > parseInt(field.getAttribute('maxlength'))) {
        isValid = false;
        errorMessage = `Ø­Ø¯Ø§Ú©Ø«Ø± ${field.getAttribute('maxlength')} Ú©Ø§Ø±Ø§Ú©ØªØ± Ù…Ø¬Ø§Ø² Ø§Ø³Øª`;
    }
    
    // Pattern
    if (isValid && field.hasAttribute('pattern')) {
        const pattern = new RegExp(field.getAttribute('pattern'));
        if (!pattern.test(value)) {
            isValid = false;
            errorMessage = 'ÙØ±Ù…Øª ÙˆØ§Ø±Ø¯ Ø´Ø¯Ù‡ Ù…Ø¹ØªØ¨Ø± Ù†ÛŒØ³Øª';
        }
    }
    
    // Type Validation
    if (isValid) {
        switch (field.type) {
            case 'email':
                if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    isValid = false;
                    errorMessage = 'Ø§ÛŒÙ…ÛŒÙ„ Ù…Ø¹ØªØ¨Ø± Ù†ÛŒØ³Øª';
                }
                break;
                
            case 'url':
                if (value) {
                    try {
                        new URL(value);
                    } catch {
                        isValid = false;
                        errorMessage = 'URL Ù…Ø¹ØªØ¨Ø± Ù†ÛŒØ³Øª';
                    }
                }
                break;
                
            case 'number':
                if (value && isNaN(value)) {
                    isValid = false;
                    errorMessage = 'Ø¨Ø§ÛŒØ¯ Ø¹Ø¯Ø¯ Ø¨Ø§Ø´Ø¯';
                }
                break;
        }
    }
    
    // Ù†Ù…Ø§ÛŒØ´ Ø®Ø·Ø§
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
 * Ø§Ø¹ØªØ¨Ø§Ø±Ø³Ù†Ø¬ÛŒ Ú©Ù„ ÙØ±Ù…
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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 7. Modal Management
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

function initModals() {
    // Ø¨Ø³ØªÙ† Modal Ø¨Ø§ Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.fixed.inset-0:not(.hidden)');
            openModals.forEach(modal => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        }
    });
    
    // Ø¨Ø³ØªÙ† Modal Ø¨Ø§ Ú©Ù„ÛŒÚ© Ø®Ø§Ø±Ø¬
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
 * Ø¨Ø§Ø² Ú©Ø±Ø¯Ù† Modal
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
 * Ø¨Ø³ØªÙ† Modal
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 8. Sidebar Management
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

function initSidebar() {
    // ØªØ´Ø®ÛŒØµ ØµÙØ­Ù‡ ÙØ¹Ø§Ù„
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
 * Toggle Sidebar (Ù…ÙˆØ¨Ø§ÛŒÙ„)
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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 9. Keyboard Shortcuts
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl+Enter: Submit ÙØ±Ù…
        if (e.ctrlKey && e.key === 'Enter') {
            const activeForm = document.activeElement.closest('form');
            if (activeForm) {
                e.preventDefault();
                activeForm.submit();
            }
        }
        
        // Ctrl+S: Ø°Ø®ÛŒØ±Ù‡ (Ø¬Ù„ÙˆÚ¯ÛŒØ±ÛŒ Ø§Ø² default)
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            const activeForm = document.activeElement.closest('form');
            if (activeForm) {
                activeForm.dispatchEvent(new Event('submit'));
                showToast('Ø¯Ø± Ø­Ø§Ù„ Ø°Ø®ÛŒØ±Ù‡...', 'info');
            }
        }
        
        // Ctrl+R: Reload
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            location.reload();
        }
        
        // ?: Ù†Ù…Ø§ÛŒØ´ Help
        if (e.key === '?' && !isInputFocused()) {
            showKeyboardHelp();
        }
    });
}

/**
 * Ø¨Ø±Ø±Ø³ÛŒ ÙÙˆÚ©ÙˆØ³ Ø±ÙˆÛŒ Input
 */
function isInputFocused() {
    const activeElement = document.activeElement;
    return activeElement.tagName === 'INPUT' || 
           activeElement.tagName === 'TEXTAREA' || 
           activeElement.isContentEditable;
}

/**
 * Ù†Ù…Ø§ÛŒØ´ Help Keyboard Shortcuts
 */
function showKeyboardHelp() {
    const shortcuts = [
        { key: 'Ctrl+Enter', action: 'Ø§Ø±Ø³Ø§Ù„ ÙØ±Ù…' },
        { key: 'Ctrl+S', action: 'Ø°Ø®ÛŒØ±Ù‡' },
        { key: 'Ctrl+R', action: 'Ø¨Ø±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ ØµÙØ­Ù‡' },
        { key: 'Escape', action: 'Ø¨Ø³ØªÙ† Modal' },
        { key: '?', action: 'Ù†Ù…Ø§ÛŒØ´ Ø§ÛŒÙ† Ø±Ø§Ù‡Ù†Ù…Ø§' }
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
    
    showToast('Ø¨Ø±Ø§ÛŒ Ù…Ø´Ø§Ù‡Ø¯Ù‡ Ú©Ù„ÛŒØ¯Ù‡Ø§ÛŒ Ù…ÛŒØ§Ù†Ø¨Ø±ØŒ ? Ø±Ø§ ÙØ´Ø§Ø± Ø¯Ù‡ÛŒØ¯', 'info', 5000);
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 10. Auto-resize Textarea
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

function initAutoResize() {
    document.querySelectorAll('textarea[auto-resize]').forEach(textarea => {
        textarea.addEventListener('input', function() {
            autoResizeTextarea(this);
        });
        
        // Ø§Ø¬Ø±Ø§ÛŒ Ø§ÙˆÙ„ÛŒÙ‡
        autoResizeTextarea(textarea);
    });
}

/**
 * ØªØºÛŒÛŒØ± Ø§Ø±ØªÙØ§Ø¹ Ø®ÙˆØ¯Ú©Ø§Ø± Textarea
 */
function autoResizeTextarea(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 300) + 'px';
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 11. Lazy Loading
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 12. Tooltips
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 13. Helper Functions
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

/**
 * Ú©Ù¾ÛŒ Ø¨Ù‡ Clipboard
 */
async function copyToClipboard(text, message = 'Ú©Ù¾ÛŒ Ø´Ø¯!') {
    try {
        await navigator.clipboard.writeText(text);
        showToast(message, 'success');
        return true;
    } catch (err) {
        // Fallback Ø¨Ø±Ø§ÛŒ Ù…Ø±ÙˆØ±Ú¯Ø±Ù‡Ø§ÛŒ Ù‚Ø¯ÛŒÙ…ÛŒ
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
            showToast('Ø®Ø·Ø§ Ø¯Ø± Ú©Ù¾ÛŒ', 'error');
            return false;
        } finally {
            document.body.removeChild(textarea);
        }
    }
}

/**
 * ÙØ±Ù…Øª Ø§Ø¹Ø¯Ø§Ø¯ Ø¨Ø§ Ø¬Ø¯Ø§Ú©Ù†Ù†Ø¯Ù‡
 */
function formatNumber(num) {
    if (num === null || num === undefined) return '0';
    return new Intl.NumberFormat('fa-IR').format(num);
}

/**
 * ÙØ±Ù…Øª Ø§Ø±Ø² (ØªÙˆÙ…Ø§Ù†)
 */
function formatCurrency(amount) {
    return formatNumber(amount) + ' ØªÙˆÙ…Ø§Ù†';
}

/**
 * ØªØ¨Ø¯ÛŒÙ„ Ø²Ù…Ø§Ù† Ø¨Ù‡ "x Ø¯Ù‚ÛŒÙ‚Ù‡ Ù¾ÛŒØ´"
 */
function timeAgo(dateString) {
    const date = new Date(dateString);
    const seconds = Math.floor((new Date() - date) / 1000);
    
    const intervals = [
        { label: 'Ø³Ø§Ù„', seconds: 31536000 },
        { label: 'Ù…Ø§Ù‡', seconds: 2592000 },
        { label: 'Ù‡ÙØªÙ‡', seconds: 604800 },
        { label: 'Ø±ÙˆØ²', seconds: 86400 },
        { label: 'Ø³Ø§Ø¹Øª', seconds: 3600 },
        { label: 'Ø¯Ù‚ÛŒÙ‚Ù‡', seconds: 60 },
        { label: 'Ø«Ø§Ù†ÛŒÙ‡', seconds: 1 }
    ];
    
    for (const interval of intervals) {
        const count = Math.floor(seconds / interval.seconds);
        if (count >= 1) {
            return `${count} ${interval.label} Ù¾ÛŒØ´`;
        }
    }
    
    return 'Ù‡Ù…ÛŒÙ† Ø§Ù„Ø§Ù†';
}

/**
 * ÙØ±Ù…Øª ØªØ§Ø±ÛŒØ®
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
 * truncate Ù…ØªÙ†
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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 14. Confirm Actions
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

/**
 * ØªØ£ÛŒÛŒØ¯ Ø¹Ù…Ù„ÛŒØ§Øª
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * ØªØ£ÛŒÛŒØ¯ Ø­Ø°Ù
 */
function confirmDelete(callback) {
    confirmAction('Ø¢ÛŒØ§ Ù…Ø·Ù…Ø¦Ù† Ù‡Ø³ØªÛŒØ¯ Ú©Ù‡ Ù…ÛŒâ€ŒØ®ÙˆØ§Ù‡ÛŒØ¯ Ø§ÛŒÙ† Ù…ÙˆØ±Ø¯ Ø±Ø§ Ø­Ø°Ù Ú©Ù†ÛŒØ¯ØŸ\n\nØ§ÛŒÙ† Ø¹Ù…Ù„ ØºÛŒØ±Ù‚Ø§Ø¨Ù„ Ø¨Ø§Ø²Ú¯Ø´Øª Ø§Ø³Øª!', callback);
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 15. Loading States
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

/**
 * Ù†Ù…Ø§ÛŒØ´ Loading Ø±ÙˆÛŒ Ø¯Ú©Ù…Ù‡
 */
function showButtonLoading(button, text = 'Ø¯Ø± Ø­Ø§Ù„ Ù¾Ø±Ø¯Ø§Ø²Ø´...') {
    button.classList.add('btn-loading');
    button.disabled = true;
    button.dataset.originalText = button.innerHTML;
    button.innerHTML = `<span>${text}</span>`;
}

/**
 * Ù…Ø®ÙÛŒ Ú©Ø±Ø¯Ù† Loading Ø¯Ú©Ù…Ù‡
 */
function hideButtonLoading(button) {
    button.classList.remove('btn-loading');
    button.disabled = false;
    if (button.dataset.originalText) {
        button.innerHTML = button.dataset.originalText;
    }
}

/**
 * Ù†Ù…Ø§ÛŒØ´ Loading Overlay
 */
function showLoadingOverlay(message = 'Ø¯Ø± Ø­Ø§Ù„ Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ...') {
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
 * Ù…Ø®ÙÛŒ Ú©Ø±Ø¯Ù† Loading Overlay
 */
function hideLoadingOverlay() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.remove();
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 16. Search & Filter
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

/**
 * Ø¬Ø³ØªØ¬Ùˆ Ø¯Ø± Ø¬Ø¯ÙˆÙ„
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
 * ÙÛŒÙ„ØªØ± Ù„ÛŒØ³Øª
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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 17. Pagination
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

/**
 * Ø¨Ø±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ URL Ø¨Ø§ Pagination
 */
function updatePage(page) {
    const url = new URL(window.location);
    url.searchParams.set('page', page);
    window.location.href = url.toString();
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 18. Export Functions
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

/**
 * Export Ø¨Ù‡ CSV
 */
function exportToCSV(data, filename = 'export.csv') {
    const BOM = '\uFEFF'; // Ø¨Ø±Ø§ÛŒ UTF-8
    const csv = BOM + data;
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
    
    showToast('ÙØ§ÛŒÙ„ Ø¯Ø§Ù†Ù„ÙˆØ¯ Ø´Ø¯', 'success');
}

/**
 * Export Ø¨Ù‡ JSON
 */
function exportToJSON(data, filename = 'export.json') {
    const json = JSON.stringify(data, null, 2);
    const blob = new Blob([json], { type: 'application/json;charset=utf-8;' });
    const link = document.createElement('a');
    
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
    
    showToast('ÙØ§ÛŒÙ„ Ø¯Ø§Ù†Ù„ÙˆØ¯ Ø´Ø¯', 'success');
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 19. Chart Helpers
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

/**
 * ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ù¾ÛŒØ´â€ŒÙØ±Ø¶ Chart.js
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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 20. Utility Functions
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

/**
 * ØªÙˆÙ„ÛŒØ¯ UUID
 */
function generateUUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        const r = Math.random() * 16 | 0;
        const v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

/**
 * ØªÙˆÙ„ÛŒØ¯ Ø±Ø´ØªÙ‡ ØªØµØ§Ø¯ÙÛŒ
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
 * Ø¨Ø±Ø±Ø³ÛŒ Email Ù…Ø¹ØªØ¨Ø±
 */
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * Ø¨Ø±Ø±Ø³ÛŒ URL Ù…Ø¹ØªØ¨Ø±
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
 * Ø§Ø³Ú©Ø±ÙˆÙ„ Ø¨Ù‡ Ø¨Ø§Ù„Ø§
 */
function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Ø§Ø³Ú©Ø±ÙˆÙ„ Ø¨Ù‡ Ø¹Ù†ØµØ±
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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 21. Console Warning
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

console.log(
    '%câš ï¸ ØªÙˆØ¬Ù‡!',
    'font-size: 24px; font-weight: bold; color: #ef4444;'
);
console.log(
    '%cØ§ÛŒÙ† Ú©Ù†Ø³ÙˆÙ„ Ø¨Ø±Ø§ÛŒ ØªÙˆØ³Ø¹Ù‡â€ŒØ¯Ù‡Ù†Ø¯Ú¯Ø§Ù† Ø§Ø³Øª. Ø§Ú¯Ø± Ú©Ø³ÛŒ Ø§Ø² Ø´Ù…Ø§ Ø®ÙˆØ§Ø³Øª Ú†ÛŒØ²ÛŒ Ø§ÛŒÙ†Ø¬Ø§ Ú©Ù¾ÛŒ Ú©Ù†ÛŒØ¯ØŒ Ø§Ø­ØªÙ…Ø§Ù„Ø§Ù‹ Ú©Ù„Ø§Ù‡Ø¨Ø±Ø¯Ø§Ø±ÛŒ Ø§Ø³Øª.',
    'font-size: 14px; color: #f59e0b;'
);

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 22. Export Global Functions
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// Ù¾Ø§ÛŒØ§Ù† ÙØ§ÛŒÙ„
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•