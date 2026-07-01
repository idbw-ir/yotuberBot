<?php
/**
 * ============================================
 * Admin Sidebar - Ø³Ø§ÛŒØ¯Ø¨Ø§Ø± Ù¾Ù†Ù„ Ù…Ø¯ÛŒØ±ÛŒØª
 * ============================================
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * 
 * Ø³Ø§ÛŒØ¯Ø¨Ø§Ø± Ù…Ø´ØªØ±Ú© ØªÙ…Ø§Ù… ØµÙØ­Ø§Øª Ø§Ø¯Ù…ÛŒÙ†
 * Ø´Ø§Ù…Ù„ Ù…Ù†ÙˆÛŒ Ù†Ø§ÙˆØ¨Ø±ÛŒØŒ Ø§Ø·Ù„Ø§Ø¹Ø§Øª Ø§Ø¯Ù…ÛŒÙ† Ùˆ Badge Ù‡Ø§
 */

// Ø¯Ø±ÛŒØ§ÙØª Ù…Ø³ÛŒØ± ÙØ¹Ù„ÛŒ Ø¨Ø±Ø§ÛŒ ØªØ´Ø®ÛŒØµ ØµÙØ­Ù‡ ÙØ¹Ø§Ù„
$currentPath = $_SERVER['REQUEST_URI'] ?? '/admin/';
$currentPath = parse_url($currentPath, PHP_URL_PATH);
$currentPath = rtrim($currentPath, '/') ?: '/admin';

// Ø§Ø·Ù„Ø§Ø¹Ø§Øª Ø§Ø¯Ù…ÛŒÙ† (Ø§Ø² layout Ø§ØµÙ„ÛŒ Ø§Ø±Ø³Ø§Ù„ Ù…ÛŒâ€ŒØ´Ù‡)
$currentAdmin = $currentAdmin ?? [
    'id' => $_SESSION['admin_id'] ?? 0,
    'username' => $_SESSION['admin_username'] ?? 'admin',
    'name' => $_SESSION['admin_name'] ?? 'Ø§Ø¯Ù…ÛŒÙ†',
    'role' => $_SESSION['admin_role'] ?? 'admin'
];

// ØªØ¹Ø¯Ø§Ø¯ Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§ÛŒ Ø®ÙˆØ§Ù†Ø¯Ù‡ Ù†Ø´Ø¯Ù‡
$unreadMessages = $unreadMessages ?? 0;

// ØªØ¹Ø¯Ø§Ø¯ Ø¯ÙˆÙ†ÛŒØªâ€ŒÙ‡Ø§ÛŒ Ø¯Ø± Ø§Ù†ØªØ¸Ø§Ø±
try {
    $pendingDonations = \App\Core\Database::getInstance()->fetchColumn(
        "SELECT COUNT(*) FROM donations WHERE status = 'pending'"
    );
} catch (Exception $e) {
    $pendingDonations = 0;
}

// Ù„ÛŒØ³Øª Ù…Ù†ÙˆÙ‡Ø§
$menuItems = [
    [
        'title' => 'Ø¯Ø§Ø´Ø¨ÙˆØ±Ø¯',
        'icon' => 'fa-chart-line',
        'emoji' => 'ðŸ“Š',
        'url' => '/admin/',
        'paths' => ['/admin', '/admin/index.php'],
        'badge' => null
    ],
    [
        'title' => 'Ú†Øª Ø²Ù†Ø¯Ù‡',
        'icon' => 'fa-comments',
        'emoji' => 'ðŸ’¬',
        'url' => '/admin/chat.php',
        'paths' => ['/admin/chat.php'],
        'badge' => $unreadMessages > 0 ? $unreadMessages : null,
        'badge_color' => 'bg-red-500'
    ],
    [
        'title' => 'Ú©Ø§Ø±Ø¨Ø±Ø§Ù†',
        'icon' => 'fa-users',
        'emoji' => 'ðŸ‘¥',
        'url' => '/admin/users.php',
        'paths' => ['/admin/users.php'],
        'badge' => null
    ],
    [
        'title' => 'Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§',
        'icon' => 'fa-envelope',
        'emoji' => 'ðŸ“¨',
        'url' => '/admin/messages.php',
        'paths' => ['/admin/messages.php'],
        'badge' => null
    ],
    [
        'title' => 'Ø¯ÙˆÙ†ÛŒØªâ€ŒÙ‡Ø§',
        'icon' => 'fa-hand-holding-usd',
        'emoji' => 'ðŸ’°',
        'url' => '/admin/donations.php',
        'paths' => ['/admin/donations.php'],
        'badge' => $pendingDonations > 0 ? $pendingDonations : null,
        'badge_color' => 'bg-yellow-500'
    ],
    [
        'title' => 'Ú©Ù„Ù…Ø§Øª Ú©Ù„ÛŒØ¯ÛŒ',
        'icon' => 'fa-key',
        'emoji' => 'ðŸ”‘',
        'url' => '/admin/keywords.php',
        'paths' => ['/admin/keywords.php'],
        'badge' => null
    ],
    [
        'title' => 'Ø§Ø±Ø³Ø§Ù„ Ø¯Ø³ØªÙ‡â€ŒØ¬Ù…Ø¹ÛŒ',
        'icon' => 'fa-bullhorn',
        'emoji' => 'ðŸ“¢',
        'url' => '/admin/broadcast.php',
        'paths' => ['/admin/broadcast.php'],
        'badge' => null
    ],
    [
        'title' => 'Ø¢Ù…Ø§Ø± Ùˆ Ú¯Ø²Ø§Ø±Ø´Ø§Øª',
        'icon' => 'fa-chart-bar',
        'emoji' => 'ðŸ“ˆ',
        'url' => '/admin/statistics.php',
        'paths' => ['/admin/statistics.php'],
        'badge' => null
    ],
    [
        'title' => 'ØªÙ†Ø¸ÛŒÙ…Ø§Øª',
        'icon' => 'fa-cog',
        'emoji' => 'âš™ï¸',
        'url' => '/admin/settings.php',
        'paths' => ['/admin/settings.php'],
        'badge' => null
    ],
];

// ØªØ´Ø®ÛŒØµ ØµÙØ­Ù‡ ÙØ¹Ø§Ù„
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
    
    <!-- â•â•â• Logo & Title â•â•â• -->
    <div class="p-4 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-2xl">
                ðŸŽ¬
            </div>
            <div>
                <h2 class="text-white font-bold text-lg">Ù¾Ù†Ù„ Ù…Ø¯ÛŒØ±ÛŒØª</h2>
                <p class="text-white/50 text-xs">Youtuber Bot v2.0</p>
            </div>
        </div>
    </div>
    
    <!-- â•â•â• Navigation Menu â•â•â• -->
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
    
    <!-- â•â•â• Admin Info â•â•â• -->
    <div class="border-t border-white/10 p-3">
        
        <!-- Admin Profile -->
        <div class="bg-white/5 rounded-lg p-3 mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white font-bold">
                    <?= strtoupper(substr($currentAdmin['name'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-white text-sm font-medium truncate">
                        <?= htmlspecialchars($currentAdmin['name'] ?? 'Ø§Ø¯Ù…ÛŒÙ†') ?>
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
                <span>Ù¾Ø±ÙˆÙØ§ÛŒÙ„</span>
            </a>
            <a 
                href="/admin/logout.php" 
                onclick="return confirm('Ø¢ÛŒØ§ Ù…Ø·Ù…Ø¦Ù† Ù‡Ø³ØªÛŒØ¯ Ú©Ù‡ Ù…ÛŒâ€ŒØ®ÙˆØ§Ù‡ÛŒØ¯ Ø®Ø§Ø±Ø¬ Ø´ÙˆÛŒØ¯ØŸ')"
                class="bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 text-xs px-3 py-2 rounded-lg transition flex items-center justify-center gap-1"
            >
                <i class="fas fa-sign-out-alt"></i>
                <span>Ø®Ø±ÙˆØ¬</span>
            </a>
        </div>
        
        <!-- System Status -->
        <div class="bg-white/5 rounded-lg p-2.5">
            <div class="flex items-center justify-between text-xs">
                <span class="text-white/50">ÙˆØ¶Ø¹ÛŒØª Ø³ÛŒØ³ØªÙ…</span>
                <span class="flex items-center gap-1 text-green-400">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    <span>Ø¢Ù†Ù„Ø§ÛŒÙ†</span>
                </span>
            </div>
        </div>
        
    </div>
    
</aside>

<!-- â•â•â• Mobile Close Button â•â•â• -->
<button 
    onclick="toggleSidebar()" 
    class="md:hidden fixed top-4 left-4 z-50 bg-gray-900/90 text-white p-2 rounded-lg shadow-lg"
    id="sidebarCloseBtn"
    style="display: none;"
>
    <i class="fas fa-times text-xl"></i>
</button>

<script>
// Ù†Ù…Ø§ÛŒØ´/Ù…Ø®ÙÛŒ Ú©Ø±Ø¯Ù† Ø¯Ú©Ù…Ù‡ Ø¨Ø³ØªÙ† Ø¯Ø± Ù…ÙˆØ¨Ø§ÛŒÙ„
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

// Ø¨Ø³ØªÙ† Sidebar Ø¨Ø§ Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && sidebar.classList.contains('open')) {
        toggleSidebar();
    }
});
</script>