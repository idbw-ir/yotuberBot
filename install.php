<?php
/**
 * ============================================
 * ðŸŽ¬ Youtuber Bot Installer - Ù†Ø³Ø®Ù‡ 2.1.0
 * ============================================
 * ÙØ§ÛŒÙ„ Ø§ØµÙ„ÛŒ Ù†ØµØ¨â€ŒÚ©Ù†Ù†Ø¯Ù‡ (Setup Wizard)
 */

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 1. Ø¬Ù„ÙˆÚ¯ÛŒØ±ÛŒ Ø§Ø² Ù†ØµØ¨ Ù…Ø¬Ø¯Ø¯ (Ø§Ù…Ù†)
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$isInstalled = file_exists(__DIR__ . '/config/config.php');
$isLocked = file_exists(__DIR__ . '/install.lock');

if ($isInstalled || $isLocked) {
    http_response_code(403);
    die('<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8">
        <title>Ù†ØµØ¨ Ø´Ø¯Ù‡</title><script src="https://cdn.tailwindcss.com"></script></head>
        <body class="bg-gray-900 min-h-screen flex items-center justify-center">
        <div class="bg-gray-800 rounded-2xl p-8 max-w-md text-center border border-gray-700">
            <div class="text-5xl mb-4">ðŸ”’</div>
            <h1 class="text-2xl font-bold text-white mb-3">Ù¾Ø±ÙˆÚ˜Ù‡ Ù‚Ø¨Ù„Ø§Ù‹ Ù†ØµØ¨ Ø´Ø¯Ù‡ Ø§Ø³Øª</h1>
            <p class="text-gray-400 mb-4">Ø¨Ø±Ø§ÛŒ Ù†ØµØ¨ Ù…Ø¬Ø¯Ø¯ØŒ ÙØ§ÛŒÙ„â€ŒÙ‡Ø§ÛŒ <code class="bg-gray-700 px-2 py-1 rounded text-sm">config.php</code> Ùˆ <code class="bg-gray-700 px-2 py-1 rounded text-sm">install.lock</code> Ø±Ø§ Ø­Ø°Ù Ú©Ù†ÛŒØ¯.</p>
            <a href="/admin/" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">ÙˆØ±ÙˆØ¯ Ø¨Ù‡ Ù¾Ù†Ù„ Ù…Ø¯ÛŒØ±ÛŒØª</a>
        </div></body></html>');
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 2. Ø´Ø±ÙˆØ¹ Session Ùˆ Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ Ø®ÙˆØ¯Ú©Ø§Ø±
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
session_start();

spl_autoload_register(function ($class) {
    $file = __DIR__ . '/installer/classes/' . $class . '.php';
    if (file_exists($file)) require_once $file;
    
    // App\Core classes (Ù…Ø«Ù„ DatabaseBunny)
    if (strpos($class, 'App\\') === 0) {
        $path = __DIR__ . '/app/' . str_replace('App\\', '', $class) . '.php';
        if (file_exists($path)) require_once $path;
    }
});

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 3. Ù…Ø¯ÛŒØ±ÛŒØª Ø­Ø°Ù ÙØ§ÛŒÙ„â€ŒÙ‡Ø§ÛŒ Ù†ØµØ¨
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
if (isset($_GET['action']) && $_GET['action'] === 'cleanup') {
    $installer = new Installer(__DIR__);
    if ($installer->removeInstaller()) {
        header('Location: /admin/');
        exit;
    }
    die('Ø®Ø·Ø§ Ø¯Ø± Ø­Ø°Ù ÙØ§ÛŒÙ„â€ŒÙ‡Ø§. Ù„Ø·ÙØ§Ù‹ Ø¯Ø³ØªÛŒ Ø­Ø°Ù Ú©Ù†ÛŒØ¯.');
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 4. Ù…Ø¯ÛŒØ±ÛŒØª Ù…Ø±Ø§Ø­Ù„
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$step = max(1, min(6, $step));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['installer_data'] = array_merge(
        $_SESSION['installer_data'] ?? [],
        $_POST
    );
}

$installer = new Installer(__DIR__);
$currentStep = $installer->getStep($step);

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 5. Ø¹Ù†ÙˆØ§Ù†â€ŒÙ‡Ø§ Ùˆ Ø¢ÛŒÚ©ÙˆÙ†â€ŒÙ‡Ø§ÛŒ Ù…Ø±Ø§Ø­Ù„
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$stepTitles = [
    1 => 'Ù¾ÛŒØ´â€ŒÙ†ÛŒØ§Ø²Ù‡Ø§',
    2 => 'Ø¯ÛŒØªØ§Ø¨ÛŒØ³',
    3 => 'Ø±Ø¨Ø§Øª ØªÙ„Ú¯Ø±Ø§Ù…',
    4 => 'Ø­Ø³Ø§Ø¨ Ø§Ø¯Ù…ÛŒÙ†',
    5 => 'ØªÙ†Ø¸ÛŒÙ…Ø§Øª',
    6 => 'Ø§ØªÙ…Ø§Ù…'
];
$stepIcons = ['ðŸ“‹', 'ðŸ—„ï¸', 'ðŸ¤–', 'ðŸ‘¤', 'âš™ï¸', 'âœ…'];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ù†ØµØ¨ Ø±Ø¨Ø§Øª ÛŒÙˆØªÛŒÙˆØ¨Ø± - Ù…Ø±Ø­Ù„Ù‡ <?= $step ?>: <?= $stepTitles[$step] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        * { font-family: 'Vazirmatn', Tahoma, sans-serif; }
        .glass { background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.15); }
        .step-active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 0 20px rgba(102,126,234,0.4); }
        .step-done { background: #10b981; }
        .step-pending { background: rgba(255,255,255,0.15); }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #667eea !important; box-shadow: 0 0 0 3px rgba(102,126,234,0.3); }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-indigo-900 min-h-screen">

<div class="container mx-auto px-4 py-6 max-w-3xl">
    
    <!-- â•â•â• Ù‡Ø¯Ø± â•â•â• -->
    <div class="text-center mb-6">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-1">ðŸŽ¬ Ù†ØµØ¨ Ø±Ø¨Ø§Øª ØªÙ„Ú¯Ø±Ø§Ù… ÛŒÙˆØªÛŒÙˆØ¨Ø±</h1>
        <p class="text-white/60 text-sm">Ù†Ø³Ø®Ù‡ 2.1.0 | Ù†ØµØ¨ Ø¢Ø³Ø§Ù† Ø¯Ø± Û¶ Ù…Ø±Ø­Ù„Ù‡</p>
    </div>

    <!-- â•â•â• Ù†ÙˆØ§Ø± Ù…Ø±Ø§Ø­Ù„ â•â•â• -->
    <div class="flex items-center mb-6 glass rounded-2xl p-3 md:p-4 overflow-x-auto">
        <?php for ($i = 1; $i <= 6; $i++): 
            $class = $i < $step ? 'step-done' : ($i === $step ? 'step-active' : 'step-pending');
            $clickable = $i < $step;
        ?>
            <?php if ($i > 1): ?>
                <div class="flex-1 h-0.5 min-w-[20px] <?= $i <= $step ? 'bg-green-500' : 'bg-white/10' ?>"></div>
            <?php endif; ?>
            <div class="flex flex-col items-center">
                <?php if ($clickable): ?>
                    <a href="?step=<?= $i ?>" class="w-10 h-10 md:w-11 md:h-11 rounded-full <?= $class ?> flex items-center justify-center text-white text-base transition-all hover:scale-110">âœ“</a>
                <?php else: ?>
                    <div class="w-10 h-10 md:w-11 md:h-11 rounded-full <?= $class ?> flex items-center justify-center text-white text-base transition-all"><?= $i === $step ? $stepIcons[$i-1] : $i ?></div>
                <?php endif; ?>
                <span class="text-[10px] md:text-xs text-white/70 mt-1 whitespace-nowrap"><?= $stepTitles[$i] ?></span>
            </div>
        <?php endfor; ?>
    </div>

    <!-- â•â•â• Ù…Ø­ØªÙˆØ§ÛŒ Ù…Ø±Ø­Ù„Ù‡ â•â•â• -->
    <div class="glass rounded-2xl p-6 md:p-8 shadow-2xl">
        <?php 
        $stepFile = __DIR__ . "/installer/steps/{$step}-{$currentStep['file']}.php";
        if (file_exists($stepFile)) {
            include $stepFile;
        } else {
            echo '<div class="text-center text-red-400">âŒ ÙØ§ÛŒÙ„ Ù…Ø±Ø­Ù„Ù‡ ÛŒØ§ÙØª Ù†Ø´Ø¯</div>';
        }
        ?>
    </div>

    <!-- â•â•â• ÙÙˆØªØ± â•â•â• -->
    <div class="text-center mt-6 text-white/40 text-xs">
        <p>Youtuber Bot v2.1.0 | Ø³Ø§Ø®ØªÙ‡ Ø´Ø¯Ù‡ Ø¨Ø§ â¤ï¸</p>
    </div>

</div>

</body>
</html>