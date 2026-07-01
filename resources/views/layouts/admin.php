<?php
/**
 * ============================================
 * Admin Layout - Ù‚Ø§Ù„Ø¨ Ø§ØµÙ„ÛŒ Ù¾Ù†Ù„ Ù…Ø¯ÛŒØ±ÛŒØª
 * ============================================
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * 
 * Ø§ÛŒÙ† ÙØ§ÛŒÙ„ Ù‚Ø§Ù„Ø¨ Ø§ØµÙ„ÛŒ ØªÙ…Ø§Ù… ØµÙØ­Ø§Øª Ø§Ø¯Ù…ÛŒÙ† Ù‡Ø³Øª
 * Ù‡Ù…Ù‡ ØµÙØ­Ø§Øª Ø§Ø² Ø§ÛŒÙ† layout Ø§Ø³ØªÙØ§Ø¯Ù‡ Ù…ÛŒâ€ŒÚ©Ù†Ù†
 * 
 * Ù†Ø­ÙˆÙ‡ Ø§Ø³ØªÙØ§Ø¯Ù‡:
 * $pageTitle = 'Ø¹Ù†ÙˆØ§Ù† ØµÙØ­Ù‡';
 * $pageIcon = 'ðŸ“Š';
 * ob_start();
 * // Ù…Ø­ØªÙˆØ§ÛŒ ØµÙØ­Ù‡ Ø§ÛŒÙ†Ø¬Ø§
 * $pageContent = ob_get_clean();
 * include __DIR__ . '/../../resources/views/layouts/admin.php';
 */

// Ù…Ù‚Ø§Ø¯ÛŒØ± Ù¾ÛŒØ´â€ŒÙØ±Ø¶
$pageTitle = $pageTitle ?? 'Ø¯Ø§Ø´Ø¨ÙˆØ±Ø¯';
$pageIcon = $pageIcon ?? 'ðŸ“Š';
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
    
    <title><?= htmlspecialchars($pageIcon . ' ' . $pageTitle) ?> - Ù¾Ù†Ù„ Ù…Ø¯ÛŒØ±ÛŒØª</title>
    
    <!-- â•â•â• Fonts â•â•â• -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    
    <!-- â•â•â• TailwindCSS â•â•â• -->
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
    
    <!-- â•â•â• Chart.js â•â•â• -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- â•â•â• Font Awesome â•â•â• -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- â•â•â• Custom CSS â•â•â• -->
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

<!-- â•â•â• Toast Container â•â•â• -->
<div id="toastContainer"></div>

<!-- â•â•â• Mobile Sidebar Overlay â•â•â• -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="flex min-h-screen">
    
    <!-- â•â•â• Sidebar â•â•â• -->
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <!-- â•â•â• Main Content â•â•â• -->
    <main class="flex-1 flex flex-col min-w-0">
        
        <!-- â•â•â• Top Header â•â•â• -->
        <header class="glass border-b border-white/10 px-4 md:px-6 py-3 sticky top-0 z-30">
            <div class="flex items-center justify-between gap-4">
                
                <!-- Right Side: Menu Button + Page Title -->
                <div class="flex items-center gap-3">
                    <!-- Mobile Menu Button -->
                    <button 
                        onclick="toggleSidebar()" 
                        class="md:hidden text-white hover:bg-white/10 p-2 rounded-lg transition"
                        aria-label="Ù…Ù†Ùˆ"
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
                    <a href="/admin/chat.php" class="relative text-white hover:bg-white/10 p-2 rounded-lg transition" title="Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§ÛŒ Ø¬Ø¯ÛŒØ¯">
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
                        title="Ø¨Ø±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ"
                    >
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    
                    <!-- Admin Profile -->
                    <div class="flex items-center gap-2 bg-white/10 rounded-lg px-3 py-1.5">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white font-bold text-sm">
                            <?= strtoupper(substr($currentAdmin['name'] ?? 'A', 0, 1)) ?>
                        </div>
                        <div class="hidden md:block">
                            <div class="text-white text-sm font-medium"><?= htmlspecialchars($currentAdmin['name'] ?? 'Ø§Ø¯Ù…ÛŒÙ†') ?></div>
                            <div class="text-white/50 text-xs"><?= htmlspecialchars($currentAdmin['role'] ?? 'admin') ?></div>
                        </div>
                    </div>
                    
                </div>
                
            </div>
        </header>
        
        <!-- â•â•â• Flash Messages â•â•â• -->
        <?php if (function_exists('displayFlashMessages')): ?>
            <?= displayFlashMessages() ?>
        <?php endif; ?>
        
        <!-- â•â•â• Page Content â•â•â• -->
        <div class="flex-1 p-4 md:p-6 animate-fadeIn">
            <?= $pageContent ?? '' ?>
        </div>
        
        <!-- â•â•â• Footer â•â•â• -->
        <footer class="border-t border-white/10 px-6 py-4 text-center">
            <p class="text-white/40 text-xs">
                ðŸŽ¬ Youtuber Bot v2.1.0 | Ø³Ø§Ø®ØªÙ‡ Ø´Ø¯Ù‡ Ø¨Ø§ â¤ï¸ | 
                <span class="hidden md:inline">PHP <?= PHP_VERSION ?> | <?= date('Y-m-d') ?></span>
            </p>
        </footer>
        
    </main>
    
</div>

<!-- â•â•â• Global JavaScript â•â•â• -->
<script>
// CSRF Token
const CSRF_TOKEN = '<?= htmlspecialchars($csrfToken) ?>';

// â•â•â• Sidebar Toggle â•â•â•
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.toggle('open');
    overlay.classList.toggle('open');
}

// â•â•â• Toast Notifications â•â•â•
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

// â•â•â• Confirm Action â•â•â•
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// â•â•â• AJAX Helper â•â•â•
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
            throw new Error(data.error?.message || 'Ø®Ø·Ø§ Ø¯Ø± Ø¯Ø±Ø®ÙˆØ§Ø³Øª');
        }
        
        return data;
    } catch (error) {
        console.error('API Error:', error);
        showToast(error.message, 'error');
        throw error;
    }
}

// â•â•â• Form Helper â•â•â•
function setupForm(form) {
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"]');
        
        if (submitBtn && !submitBtn.classList.contains('btn-loading')) {
            submitBtn.classList.add('btn-loading');
            submitBtn.disabled = true;
            
            // Ø¨Ø§Ø²Ú¯Ø±Ø¯Ø§Ù†ÛŒ Ø¨Ø¹Ø¯ Ø§Ø² 5 Ø«Ø§Ù†ÛŒÙ‡
            setTimeout(() => {
                submitBtn.classList.remove('btn-loading');
                submitBtn.disabled = false;
            }, 5000);
        }
    });
}

// â•â•â• Auto-setup all forms â•â•â•
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

// â•â•â• Copy to Clipboard â•â•â•
function copyToClipboard(text, message = 'Ú©Ù¾ÛŒ Ø´Ø¯!') {
    navigator.clipboard.writeText(text).then(() => {
        showToast(message, 'success');
    }).catch(() => {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ú©Ù¾ÛŒ', 'error');
    });
}

// â•â•â• Format Number â•â•â•
function formatNumber(num) {
    return new Intl.NumberFormat('fa-IR').format(num);
}

// â•â•â• Format Currency â•â•â•
function formatCurrency(amount) {
    return formatNumber(amount) + ' ØªÙˆÙ…Ø§Ù†';
}

// â•â•â• Time Ago â•â•â•
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

<?= $extraJs ?>
</script>

</body>
</html>