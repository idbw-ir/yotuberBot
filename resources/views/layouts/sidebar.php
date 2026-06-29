<?php
/**
 * ============================================
 * Admin Sidebar - سایدبار پنل مدیریت
 * ============================================
 * نسخه: 2.0.0
 * 
 * سایدبار مشترک تمام صفحات ادمین
 * شامل منوی ناوبری، اطلاعات ادمین و Badge ها
 */

// دریافت مسیر فعلی برای تشخیص صفحه فعال
$currentPath = $_SERVER['REQUEST_URI'] ?? '/admin/';
$currentPath = parse_url($currentPath, PHP_URL_PATH);
$currentPath = rtrim($currentPath, '/') ?: '/admin';

// اطلاعات ادمین (از layout اصلی ارسال می‌شه)
$currentAdmin = $currentAdmin ?? [
    'id' => $_SESSION['admin_id'] ?? 0,
    'username' => $_SESSION['admin_username'] ?? 'admin',
    'name' => $_SESSION['admin_name'] ?? 'ادمین',
    'role' => $_SESSION['admin_role'] ?? 'admin'
];

// تعداد پیام‌های خوانده نشده
$unreadMessages = $unreadMessages ?? 0;

// تعداد دونیت‌های در انتظار
try {
    $pendingDonations = \App\Core\Database::getInstance()->fetchColumn(
        "SELECT COUNT(*) FROM donations WHERE status = 'pending'"
    );
} catch (Exception $e) {
    $pendingDonations = 0;
}

// لیست منوها
$menuItems = [
    [
        'title' => 'داشبورد',
        'icon' => 'fa-chart-line',
        'emoji' => '📊',
        'url' => '/admin/',
        'paths' => ['/admin', '/admin/index.php'],
        'badge' => null
    ],
    [
        'title' => 'چت زنده',
        'icon' => 'fa-comments',
        'emoji' => '💬',
        'url' => '/admin/chat.php',
        'paths' => ['/admin/chat.php'],
        'badge' => $unreadMessages > 0 ? $unreadMessages : null,
        'badge_color' => 'bg-red-500'
    ],
    [
        'title' => 'کاربران',
        'icon' => 'fa-users',
        'emoji' => '👥',
        'url' => '/admin/users.php',
        'paths' => ['/admin/users.php'],
        'badge' => null
    ],
    [
        'title' => 'پیام‌ها',
        'icon' => 'fa-envelope',
        'emoji' => '📨',
        'url' => '/admin/messages.php',
        'paths' => ['/admin/messages.php'],
        'badge' => null
    ],
    [
        'title' => 'دونیت‌ها',
        'icon' => 'fa-hand-holding-usd',
        'emoji' => '💰',
        'url' => '/admin/donations.php',
        'paths' => ['/admin/donations.php'],
        'badge' => $pendingDonations > 0 ? $pendingDonations : null,
        'badge_color' => 'bg-yellow-500'
    ],
    [
        'title' => 'کلمات کلیدی',
        'icon' => 'fa-key',
        'emoji' => '🔑',
        'url' => '/admin/keywords.php',
        'paths' => ['/admin/keywords.php'],
        'badge' => null
    ],
    [
        'title' => 'ارسال دسته‌جمعی',
        'icon' => 'fa-bullhorn',
        'emoji' => '📢',
        'url' => '/admin/broadcast.php',
        'paths' => ['/admin/broadcast.php'],
        'badge' => null
    ],
    [
        'title' => 'آمار و گزارشات',
        'icon' => 'fa-chart-bar',
        'emoji' => '📈',
        'url' => '/admin/statistics.php',
        'paths' => ['/admin/statistics.php'],
        'badge' => null
    ],
    [
        'title' => 'تنظیمات',
        'icon' => 'fa-cog',
        'emoji' => '⚙️',
        'url' => '/admin/settings.php',
        'paths' => ['/admin/settings.php'],
        'badge' => null
    ],
];

// تشخیص صفحه فعال
function isActive($paths, $currentPath) {
    foreach ($paths as $path) {
        if ($currentPath === $path || $currentPath === rtrim($path, '/')) {
            return true;
        }
    }
    return false;
}
?>

<aside id="sidebar" class="sidebar w-64 bg-gray-900/50 border-l border-white/10 flex flex-col">
    
    <!-- ═══ Logo & Title ═══ -->
    <div class="p-4 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-2xl">
                🎬
            </div>
            <div>
                <h2 class="text-white font-bold text-lg">پنل مدیریت</h2>
                <p class="text-white/50 text-xs">Youtuber Bot v2.0</p>
            </div>
        </div>
    </div>
    
    <!-- ═══ Navigation Menu ═══ -->
    <nav class="flex-1 overflow-y-auto p-3 space-y-1">
        
        <?php foreach ($menuItems as $item): 
            $active = isActive($item['paths'], $currentPath);
        ?>
        <a 
            href="<?= htmlspecialchars($item['url']) ?>"
            class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/80 hover:text-white <?= $active ? 'active' : '' ?>"
            title="<?= htmlspecialchars($item['title']) ?>"
        >
            <i class="fas <?= $item['icon'] ?> w-5 text-center"></i>
            <span class="flex-1 text-sm font-medium"><?= htmlspecialchars($item['title']) ?></span>
            
            <?php if ($item['badge']): ?>
            <span class="<?= $item['badge_color'] ?? 'bg-red-500' ?> text-white text-xs rounded-full min-w-[20px] h-5 flex items-center justify-center px-1.5 font-bold">
                <?= $item['badge'] ?>
            </span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
        
    </nav>
    
    <!-- ═══ Admin Info ═══ -->
    <div class="border-t border-white/10 p-3">
        
        <!-- Admin Profile -->
        <div class="bg-white/5 rounded-lg p-3 mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white font-bold">
                    <?= strtoupper(substr($currentAdmin['name'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-white text-sm font-medium truncate">
                        <?= htmlspecialchars($currentAdmin['name'] ?? 'ادمین') ?>
                    </div>
                    <div class="text-white/50 text-xs flex items-center gap-1">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        <span><?= htmlspecialchars($currentAdmin['role'] ?? 'admin') ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="grid grid-cols-2 gap-2 mb-3">
            <a 
                href="/admin/profile.php" 
                class="bg-white/5 hover:bg-white/10 text-white/70 hover:text-white text-xs px-3 py-2 rounded-lg transition flex items-center justify-center gap-1"
            >
                <i class="fas fa-user-circle"></i>
                <span>پروفایل</span>
            </a>
            <a 
                href="/admin/logout.php" 
                onclick="return confirm('آیا مطمئن هستید که می‌خواهید خارج شوید؟')"
                class="bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 text-xs px-3 py-2 rounded-lg transition flex items-center justify-center gap-1"
            >
                <i class="fas fa-sign-out-alt"></i>
                <span>خروج</span>
            </a>
        </div>
        
        <!-- System Status -->
        <div class="bg-white/5 rounded-lg p-2.5">
            <div class="flex items-center justify-between text-xs">
                <span class="text-white/50">وضعیت سیستم</span>
                <span class="flex items-center gap-1 text-green-400">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    <span>آنلاین</span>
                </span>
            </div>
        </div>
        
    </div>
    
</aside>

<!-- ═══ Mobile Close Button ═══ -->
<button 
    onclick="toggleSidebar()" 
    class="md:hidden fixed top-4 left-4 z-50 bg-gray-900/90 text-white p-2 rounded-lg shadow-lg"
    id="sidebarCloseBtn"
    style="display: none;"
>
    <i class="fas fa-times text-xl"></i>
</button>

<script>
// نمایش/مخفی کردن دکمه بستن در موبایل
const sidebar = document.getElementById('sidebar');
const closeBtn = document.getElementById('sidebarCloseBtn');

const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.attributeName === 'class') {
            if (sidebar.classList.contains('open')) {
                closeBtn.style.display = 'block';
            } else {
                closeBtn.style.display = 'none';
            }
        }
    });
});

observer.observe(sidebar, { attributes: true });

// بستن Sidebar با Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && sidebar.classList.contains('open')) {
        toggleSidebar();
    }
});
</script>