<?php
/**
 * ============================================
 * Admin Dashboard
 * ============================================
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * 
 * ØµÙØ­Ù‡ Ø§ØµÙ„ÛŒ Ù¾Ù†Ù„ Ù…Ø¯ÛŒØ±ÛŒØª
 * Ù†Ù…Ø§ÛŒØ´ Ø¢Ù…Ø§Ø± Ú©Ù„ÛŒØŒ Ù†Ù…ÙˆØ¯Ø§Ø±Ù‡Ø§ Ùˆ ÙØ¹Ø§Ù„ÛŒØªâ€ŒÙ‡Ø§ÛŒ Ø§Ø®ÛŒØ±
 */

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 1. ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ø§ÙˆÙ„ÛŒÙ‡
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

define('BASE_PATH', dirname(__DIR__, 2));
define('PUBLIC_PATH', dirname(__DIR__));

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

date_default_timezone_set('Asia/Tehran');
mb_internal_encoding('UTF-8');

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 2. Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ Autoloader
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 3. Ø§Ø­Ø±Ø§Ø² Ù‡ÙˆÛŒØª
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

try {
    $auth = \App\Admin\Auth::getInstance();
    $auth->requireLogin('/admin/login.php');
    
    // Ø§Ø·Ù„Ø§Ø¹Ø§Øª Ø§Ø¯Ù…ÛŒÙ† ÙØ¹Ù„ÛŒ
    $currentAdmin = [
        'id' => $auth->id(),
        'username' => $auth->username(),
        'name' => $auth->name(),
        'role' => $auth->role()
    ];
    
} catch (Exception $e) {
    header('Location: /admin/login.php?error=session_expired');
    exit;
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 4. Ø¯Ø±ÛŒØ§ÙØª Ø¢Ù…Ø§Ø±
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

try {
    $dashboard = \App\Admin\Dashboard::getInstance();
    $stats = $dashboard->getFullStats();
    $cards = $dashboard->getDashboardCards();
    
    // Ù†Ù…ÙˆØ¯Ø§Ø±Ù‡Ø§
    $donationChart = $dashboard->getDonationChartData(30);
    $userChart = $dashboard->getUserChartData(30);
    
    // Ø¨Ø±ØªØ±ÛŒÙ†â€ŒÙ‡Ø§
    $topDonors = $dashboard->getTopDonors(5);
    $recentMessages = $dashboard->getRecentMessages(10);
    $recentDonations = $dashboard->getRecentDonations(5);
    
    // Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§ÛŒ Ø®ÙˆØ§Ù†Ø¯Ù‡ Ù†Ø´Ø¯Ù‡
    $chat = \App\Admin\Chat::getInstance();
    $unreadCount = $chat->getUnreadCount();
    
} catch (Exception $e) {
    // Ø¯Ø± ØµÙˆØ±Øª Ø®Ø·Ø§ØŒ Ù…Ù‚Ø§Ø¯ÛŒØ± Ù¾ÛŒØ´â€ŒÙØ±Ø¶
    $stats = ['users' => [], 'messages' => [], 'donations' => [], 'growth' => []];
    $cards = [];
    $donationChart = ['labels' => [], 'datasets' => []];
    $userChart = ['labels' => [], 'datasets' => []];
    $topDonors = [];
    $recentMessages = [];
    $recentDonations = [];
    $unreadCount = 0;
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 5. Ø¨Ø±Ø±Ø³ÛŒ Refresh Cache
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

if (isset($_GET['refresh_cache'])) {
    try {
        $dashboard->clearCache();
        header('Location: /admin/?cache_cleared=1');
        exit;
    } catch (Exception $e) {
        // Ù†Ø§Ø¯ÛŒØ¯Ù‡ Ø¨Ú¯ÛŒØ±
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ø¯Ø§Ø´Ø¨ÙˆØ±Ø¯ Ù…Ø¯ÛŒØ±ÛŒØª - <?= htmlspecialchars($currentAdmin['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        * { font-family: 'Vazirmatn', Tahoma, sans-serif; }
        .glass { background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.15); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.3); }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-indigo-900 min-h-screen">

<div class="flex">
    
    <!-- â•â•â• Sidebar â•â•â• -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    
    <!-- â•â•â• Main Content â•â•â• -->
    <main class="flex-1 p-6">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white">ðŸ“Š Ø¯Ø§Ø´Ø¨ÙˆØ±Ø¯</h1>
                <p class="text-white/60 text-sm mt-1">Ø®ÙˆØ´ Ø¢Ù…Ø¯ÛŒØ¯ØŒ <?= htmlspecialchars($currentAdmin['name']) ?></p>
            </div>
            <div class="flex items-center gap-3">
                <?php if ($unreadCount > 0): ?>
                <a href="/admin/chat.php" class="relative bg-yellow-500/20 border border-yellow-500/50 text-yellow-300 px-4 py-2 rounded-lg hover:bg-yellow-500/30 transition">
                    ðŸ’¬ Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§ÛŒ Ø¬Ø¯ÛŒØ¯
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                        <?= $unreadCount ?>
                    </span>
                </a>
                <?php endif; ?>
                <a href="?refresh_cache=1" class="bg-white/10 text-white px-4 py-2 rounded-lg hover:bg-white/20 transition text-sm">
                    ðŸ”„ Ø¨Ø±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ Ú©Ø´
                </a>
            </div>
        </div>
        
        <!-- Flash Messages -->
        <?php if (isset($_GET['cache_cleared'])): ?>
        <div class="bg-green-500/20 border border-green-500/50 text-green-300 px-4 py-3 rounded-lg mb-4">
            âœ… Ú©Ø´ Ø¨Ø§ Ù…ÙˆÙÙ‚ÛŒØª Ù¾Ø§Ú© Ø´Ø¯
        </div>
        <?php endif; ?>
        
        <!-- â•â•â• Ú©Ø§Ø±Øªâ€ŒÙ‡Ø§ÛŒ Ø¢Ù…Ø§Ø±ÛŒ â•â•â• -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <?php foreach ($cards as $card): ?>
            <div class="card-hover bg-gradient-to-br <?= $card['color'] ?> rounded-2xl p-5 shadow-lg">
                <div class="flex justify-between items-start mb-3">
                    <span class="text-3xl"><?= $card['icon'] ?></span>
                    <?php if (isset($card['change'])): ?>
                    <span class="text-xs bg-white/20 px-2 py-1 rounded-full">
                        <?= $card['change'] >= 0 ? 'â†‘' : 'â†“' ?> <?= abs($card['change']) ?>%
                    </span>
                    <?php endif; ?>
                </div>
                <div class="text-white/80 text-sm mb-1"><?= $card['title'] ?></div>
                <div class="text-white text-2xl font-bold"><?= $card['value'] ?></div>
                <div class="text-white/60 text-xs mt-2"><?= $card['subtitle'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- â•â•â• Ù†Ù…ÙˆØ¯Ø§Ø±Ù‡Ø§ â•â•â• -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            
            <!-- Ù†Ù…ÙˆØ¯Ø§Ø± Ø¯ÙˆÙ†ÛŒØªâ€ŒÙ‡Ø§ -->
            <div class="glass rounded-2xl p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-white font-bold text-lg">ðŸ’° Ù†Ù…ÙˆØ¯Ø§Ø± Ø¯ÙˆÙ†ÛŒØªâ€ŒÙ‡Ø§ (30 Ø±ÙˆØ² Ø§Ø®ÛŒØ±)</h3>
                </div>
                <canvas id="donationChart" height="200"></canvas>
            </div>
            
            <!-- Ù†Ù…ÙˆØ¯Ø§Ø± Ú©Ø§Ø±Ø¨Ø±Ø§Ù† -->
            <div class="glass rounded-2xl p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-white font-bold text-lg">ðŸ‘¥ Ø±Ø´Ø¯ Ú©Ø§Ø±Ø¨Ø±Ø§Ù†</h3>
                </div>
                <canvas id="userChart" height="200"></canvas>
            </div>
            
        </div>
        
        <!-- â•â•â• Ø¨Ø±ØªØ±ÛŒÙ†â€ŒÙ‡Ø§ Ùˆ ÙØ¹Ø§Ù„ÛŒØªâ€ŒÙ‡Ø§ÛŒ Ø§Ø®ÛŒØ± â•â•â• -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Ø¨Ø±ØªØ±ÛŒÙ† Ø­Ø§Ù…ÛŒØ§Ù† -->
            <div class="glass rounded-2xl p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-white font-bold text-lg">ðŸ† Ø¨Ø±ØªØ±ÛŒÙ† Ø­Ø§Ù…ÛŒØ§Ù†</h3>
                    <a href="/admin/donations.php" class="text-blue-400 text-sm hover:underline">Ù…Ø´Ø§Ù‡Ø¯Ù‡ Ù‡Ù…Ù‡</a>
                </div>
                
                <?php if (empty($topDonors)): ?>
                <p class="text-white/50 text-center py-8">Ù‡Ù†ÙˆØ² Ø¯ÙˆÙ†ÛŒØªÛŒ Ø«Ø¨Øª Ù†Ø´Ø¯Ù‡</p>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($topDonors as $i => $donor): ?>
                    <div class="flex items-center justify-between bg-white/5 rounded-lg p-3 hover:bg-white/10 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center text-white font-bold text-sm">
                                <?= $i + 1 ?>
                            </div>
                            <div>
                                <div class="text-white text-sm font-medium"><?= htmlspecialchars($donor['display_name']) ?></div>
                                <div class="text-white/50 text-xs"><?= $donor['donation_count'] ?> Ø¯ÙˆÙ†ÛŒØª</div>
                            </div>
                        </div>
                        <div class="text-green-400 font-bold text-sm">
                            <?= number_format($donor['total_amount']) ?> Øª
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Ø¢Ø®Ø±ÛŒÙ† Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§ -->
            <div class="glass rounded-2xl p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-white font-bold text-lg">ðŸ’¬ Ø¢Ø®Ø±ÛŒÙ† Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§</h3>
                    <a href="/admin/messages.php" class="text-blue-400 text-sm hover:underline">Ù…Ø´Ø§Ù‡Ø¯Ù‡ Ù‡Ù…Ù‡</a>
                </div>
                
                <?php if (empty($recentMessages)): ?>
                <p class="text-white/50 text-center py-8">Ù‡Ù†ÙˆØ² Ù¾ÛŒØ§Ù…ÛŒ Ø¯Ø±ÛŒØ§ÙØª Ù†Ø´Ø¯Ù‡</p>
                <?php else: ?>
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    <?php foreach ($recentMessages as $msg): ?>
                    <a href="/admin/chat.php?id=<?= $msg['user_id'] ?>" class="block bg-white/5 rounded-lg p-3 hover:bg-white/10 transition">
                        <div class="flex justify-between items-start mb-1">
                            <span class="text-white text-sm font-medium"><?= htmlspecialchars($msg['user_display_name']) ?></span>
                            <span class="text-white/40 text-xs"><?= $msg['time_ago'] ?></span>
                        </div>
                        <p class="text-white/70 text-sm truncate"><?= htmlspecialchars($msg['text_preview']) ?></p>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
        </div>
        
        <!-- â•â•â• Ø¢Ø®Ø±ÛŒÙ† Ø¯ÙˆÙ†ÛŒØªâ€ŒÙ‡Ø§ â•â•â• -->
        <div class="glass rounded-2xl p-5 mt-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-white font-bold text-lg">ðŸ’³ Ø¢Ø®Ø±ÛŒÙ† Ø¯ÙˆÙ†ÛŒØªâ€ŒÙ‡Ø§</h3>
                <a href="/admin/donations.php" class="text-blue-400 text-sm hover:underline">Ù…Ø´Ø§Ù‡Ø¯Ù‡ Ù‡Ù…Ù‡</a>
            </div>
            
            <?php if (empty($recentDonations)): ?>
            <p class="text-white/50 text-center py-8">Ù‡Ù†ÙˆØ² Ø¯ÙˆÙ†ÛŒØªÛŒ Ø«Ø¨Øª Ù†Ø´Ø¯Ù‡</p>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-white/60 border-b border-white/10">
                            <th class="text-right py-2 px-3">Ú©Ø§Ø±Ø¨Ø±</th>
                            <th class="text-right py-2 px-3">Ù…Ø¨Ù„Øº</th>
                            <th class="text-right py-2 px-3">Ø¯Ø±Ú¯Ø§Ù‡</th>
                            <th class="text-right py-2 px-3">Ø²Ù…Ø§Ù†</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentDonations as $d): ?>
                        <tr class="border-b border-white/5 hover:bg-white/5">
                            <td class="py-3 px-3 text-white"><?= htmlspecialchars($d['user_display_name']) ?></td>
                            <td class="py-3 px-3 text-green-400 font-bold"><?= $d['amount_formatted'] ?> Øª</td>
                            <td class="py-3 px-3 text-white/70"><?= $d['gateway_icon'] ?> <?= htmlspecialchars($d['gateway'] ?? 'Ù†Ø§Ù…Ø´Ø®Øµ') ?></td>
                            <td class="py-3 px-3 text-white/50"><?= $d['time_ago'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <div class="text-center text-white/40 text-xs mt-8">
            <p>Youtuber Bot v2.1.0 | Ø³Ø§Ø®ØªÙ‡ Ø´Ø¯Ù‡ Ø¨Ø§ â¤ï¸</p>
        </div>
        
    </main>
    
</div>

<!-- â•â•â• Chart.js Scripts â•â•â• -->
<script>
// Ù†Ù…ÙˆØ¯Ø§Ø± Ø¯ÙˆÙ†ÛŒØªâ€ŒÙ‡Ø§
const donationCtx = document.getElementById('donationChart').getContext('2d');
new Chart(donationCtx, {
    type: 'line',
    data: <?= json_encode($donationChart) ?>,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { labels: { color: '#fff', font: { family: 'Vazirmatn' } } }
        },
        scales: {
            y: { ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.1)' } },
            x: { ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.1)' } }
        }
    }
});

// Ù†Ù…ÙˆØ¯Ø§Ø± Ú©Ø§Ø±Ø¨Ø±Ø§Ù†
const userCtx = document.getElementById('userChart').getContext('2d');
new Chart(userCtx, {
    type: 'line',
    data: <?= json_encode($userChart) ?>,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { labels: { color: '#fff', font: { family: 'Vazirmatn' } } }
        },
        scales: {
            y: { ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.1)' } },
            x: { ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.1)' } }
        }
    }
});
</script>

</body>
</html>