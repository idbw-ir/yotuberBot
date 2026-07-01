<?php
/**
 * ============================================
 * Admin Layout - قالب اصلی پنل مدیریت
 * ============================================
 * نسخه: 2.0.0
 * 
 * این فایل قالب اصلی تمام صفحات ادمین هست
 * همه صفحات از این layout استفاده می‌کنن
 * 
 * نحوه استفاده:
 * $pageTitle = 'عنوان صفحه';
 * $pageIcon = '📊';
 * ob_start();
 * // محتوای صفحه اینجا
 * $pageContent = ob_get_clean();
 * include __DIR__ . '/../../resources/views/layouts/admin.php';
 */

// مقادیر پیش‌فرض
$pageTitle = $pageTitle ?? 'داشبورد';
$pageIcon = $pageIcon ?? '📊';
$pageDescription = $pageDescription ?? '';
$extraCss = $extraCss ?? '';
$extraJs = $extraJs ?? '';
$currentAdmin = $currentAdmin ?? [];

// CSRF Token
$csrfToken = $_SESSION['_csrf_token'] ?? '';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    
    <title><?= htmlspecialchars($pageIcon . ' ' . $pageTitle) ?> - پنل مدیریت</title>
    
    <!-- ═══ Fonts ═══ -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    
    <!-- ═══ TailwindCSS ═══ -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'vazir': ['Vazirmatn', 'Tahoma', 'sans-serif']
                    },
                    colors: {
                        'brand': {
                            '50': '#f5f3ff',
                            '100': '#ede9fe',
                            '500': '#8b5cf6',
                            '600': '#7c3aed',
                            '700': '#6d28d9',
                            '900': '#4c1d95'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- ═══ Chart.js ═══ -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- ═══ Font Awesome ═══ -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- ═══ Custom CSS ═══ -->
    <link rel="stylesheet" href="/assets/css/app.css">
    
    <?= $extraCss ?>
    
    <style>
        * { font-family: 'Vazirmatn', Tahoma, sans-serif; }
        
        /* Glass Effect */
        .glass { 
            background: rgba(255,255,255,0.08); 
            backdrop-filter: blur(12px); 
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.15); 
        }
        
        .glass-strong { 
            background: rgba(255,255,255,0.12); 
            backdrop-filter: blur(20px);
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.3); }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fadeIn { animation: fadeIn 0.3s ease-out; }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .animate-slideIn { animation: slideIn 0.3s ease-out; }
        
        /* Sidebar */
        .sidebar-link {
            transition: all 0.2s ease;
        }
        
        .sidebar-link:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(-4px);
        }
        
        .sidebar-link.active {
            background: linear-gradient(135deg, rgba(139,92,246,0.3), rgba(59,130,246,0.3));
            border-right: 3px solid #8b5cf6;
        }
        
        /* Mobile Sidebar */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                right: -100%;
                top: 0;
                height: 100vh;
                z-index: 50;
                transition: right 0.3s ease;
            }
            
            .sidebar.open {
                right: 0;
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 40;
            }
            
            .sidebar-overlay.open {
                display: block;
            }
        }
        
        /* Focus States */
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #8b5cf6 !important;
            box-shadow: 0 0 0 3px rgba(139,92,246,0.2);
        }
        
        /* Button Loading */
        .btn-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.7;
        }
        
        .btn-loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin: -8px 0 0 -8px;
            border: 2px solid transparent;
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Toast Notification */
        .toast {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 100;
            min-width: 300px;
            animation: slideIn 0.3s ease-out;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-900 via-purple-900/50 to-indigo-900 min-h-screen">

<!-- ═══ Toast Container ═══ -->
<div id="toastContainer"></div>

<!-- ═══ Mobile Sidebar Overlay ═══ -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="flex min-h-screen">
    
    <!-- ═══ Sidebar ═══ -->
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <!-- ═══ Main Content ═══ -->
    <main class="flex-1 flex flex-col min-w-0">
        
        <!-- ═══ Top Header ═══ -->
        <header class="glass border-b border-white/10 px-4 md:px-6 py-3 sticky top-0 z-30">
            <div class="flex items-center justify-between gap-4">
                
                <!-- Right Side: Menu Button + Page Title -->
                <div class="flex items-center gap-3">
                    <!-- Mobile Menu Button -->
                    <button 
                        onclick="toggleSidebar()" 
                        class="md:hidden text-white hover:bg-white/10 p-2 rounded-lg transition"
                        aria-label="منو"
                    >
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <!-- Page Title -->
                    <div>
                        <h1 class="text-lg md:text-xl font-bold text-white flex items-center gap-2">
                            <span><?= $pageIcon ?></span>
                            <span><?= htmlspecialchars($pageTitle) ?></span>
                        </h1>
                        <?php if ($pageDescription): ?>
                        <p class="text-white/50 text-xs mt-0.5 hidden md:block"><?= htmlspecialchars($pageDescription) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Left Side: Actions -->
                <div class="flex items-center gap-2 md:gap-3">
                    
                    <!-- Notifications Badge -->
                    <?php if (!empty($unreadMessages)): ?>
                    <a href="/admin/chat.php" class="relative text-white hover:bg-white/10 p-2 rounded-lg transition" title="پیام‌های جدید">
                        <i class="fas fa-bell text-lg"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold">
                            <?= (int)$unreadMessages ?>
                        </span>
                    </a>
                    <?php endif; ?>
                    
                    <!-- Refresh Button -->
                    <button 
                        onclick="location.reload()" 
                        class="text-white hover:bg-white/10 p-2 rounded-lg transition"
                        title="بروزرسانی"
                    >
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    
                    <!-- Admin Profile -->
                    <div class="flex items-center gap-2 bg-white/10 rounded-lg px-3 py-1.5">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white font-bold text-sm">
                            <?= strtoupper(substr($currentAdmin['name'] ?? 'A', 0, 1)) ?>
                        </div>
                        <div class="hidden md:block">
                            <div class="text-white text-sm font-medium"><?= htmlspecialchars($currentAdmin['name'] ?? 'ادمین') ?></div>
                            <div class="text-white/50 text-xs"><?= htmlspecialchars($currentAdmin['role'] ?? 'admin') ?></div>
                        </div>
                    </div>
                    
                </div>
                
            </div>
        </header>
        
        <!-- ═══ Flash Messages ═══ -->
        <?php if (function_exists('displayFlashMessages')): ?>
            <?= displayFlashMessages() ?>
        <?php endif; ?>
        
        <!-- ═══ Page Content ═══ -->
        <div class="flex-1 p-4 md:p-6 animate-fadeIn">
            <?= $pageContent ?? '' ?>
        </div>
        
        <!-- ═══ Footer ═══ -->
        <footer class="border-t border-white/10 px-6 py-4 text-center">
            <p class="text-white/40 text-xs">
                🎬 Youtuber Bot v2.1.0 | ساخته شده با ❤️ | 
                <span class="hidden md:inline">PHP <?= PHP_VERSION ?> | <?= date('Y-m-d') ?></span>
            </p>
        </footer>
        
    </main>
    
</div>

<!-- ═══ Global JavaScript ═══ -->
<script>
// CSRF Token
const CSRF_TOKEN = '<?= htmlspecialchars($csrfToken) ?>';

// ═══ Sidebar Toggle ═══
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.toggle('open');
    overlay.classList.toggle('open');
}

// ═══ Toast Notifications ═══
function showToast(message, type = 'info', duration = 3000) {
    const container = document.getElementById('toastContainer');
    
    const colors = {
        success: 'bg-green-500/90 border-green-400',
        error: 'bg-red-500/90 border-red-400',
        warning: 'bg-yellow-500/90 border-yellow-400',
        info: 'bg-blue-500/90 border-blue-400'
    };
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    const toast = document.createElement('div');
    toast.className = `toast ${colors[type]} border text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-3`;
    toast.innerHTML = `
        <i class="fas ${icons[type]} text-xl"></i>
        <span class="flex-1">${message}</span>
        <button onclick="this.parentElement.remove()" class="text-white/70 hover:text-white">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s ease-out reverse';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// ═══ Confirm Action ═══
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// ═══ AJAX Helper ═══
async function apiRequest(url, options = {}) {
    const defaults = {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': CSRF_TOKEN,
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    };
    
    const config = { ...defaults, ...options };
    
    try {
        const response = await fetch(url, config);
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error?.message || 'خطا در درخواست');
        }
        
        return data;
    } catch (error) {
        console.error('API Error:', error);
        showToast(error.message, 'error');
        throw error;
    }
}

// ═══ Form Helper ═══
function setupForm(form) {
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"]');
        
        if (submitBtn && !submitBtn.classList.contains('btn-loading')) {
            submitBtn.classList.add('btn-loading');
            submitBtn.disabled = true;
            
            // بازگردانی بعد از 5 ثانیه
            setTimeout(() => {
                submitBtn.classList.remove('btn-loading');
                submitBtn.disabled = false;
            }, 5000);
        }
    });
}

// ═══ Auto-setup all forms ═══
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form').forEach(setupForm);
    
    // Auto-hide flash messages
    setTimeout(() => {
        document.querySelectorAll('.flash-message').forEach(msg => {
            msg.style.animation = 'fadeIn 0.3s ease-out reverse';
            setTimeout(() => msg.remove(), 300);
        });
    }, 5000);
});

// ═══ Copy to Clipboard ═══
function copyToClipboard(text, message = 'کپی شد!') {
    navigator.clipboard.writeText(text).then(() => {
        showToast(message, 'success');
    }).catch(() => {
        showToast('خطا در کپی', 'error');
    });
}

// ═══ Format Number ═══
function formatNumber(num) {
    return new Intl.NumberFormat('fa-IR').format(num);
}

// ═══ Format Currency ═══
function formatCurrency(amount) {
    return formatNumber(amount) + ' تومان';
}

// ═══ Time Ago ═══
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

<?= $extraJs ?>
</script>

</body>
</html>