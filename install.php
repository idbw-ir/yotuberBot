<?php
/**
 * ============================================
 * 🎬 Youtuber Bot Installer - نسخه 2.0.0
 * ============================================
 * فایل اصلی نصب‌کننده (Setup Wizard)
 */

// ──────────────────────────────────────
// 1. جلوگیری از نصب مجدد (امن)
// ──────────────────────────────────────
$isInstalled = file_exists(__DIR__ . '/config/config.php');
$isLocked = file_exists(__DIR__ . '/install.lock');

if ($isInstalled || $isLocked) {
    http_response_code(403);
    die('<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8">
        <title>نصب شده</title><script src="https://cdn.tailwindcss.com"></script></head>
        <body class="bg-gray-900 min-h-screen flex items-center justify-center">
        <div class="bg-gray-800 rounded-2xl p-8 max-w-md text-center border border-gray-700">
            <div class="text-5xl mb-4">🔒</div>
            <h1 class="text-2xl font-bold text-white mb-3">پروژه قبلاً نصب شده است</h1>
            <p class="text-gray-400 mb-4">برای نصب مجدد، فایل‌های <code class="bg-gray-700 px-2 py-1 rounded text-sm">config.php</code> و <code class="bg-gray-700 px-2 py-1 rounded text-sm">install.lock</code> را حذف کنید.</p>
            <a href="/admin/" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">ورود به پنل مدیریت</a>
        </div></body></html>');
}

// ──────────────────────────────────────
// 2. شروع Session و بارگذاری خودکار
// ──────────────────────────────────────
session_start();

spl_autoload_register(function ($class) {
    $file = __DIR__ . '/installer/classes/' . $class . '.php';
    if (file_exists($file)) require_once $file;
});

// ──────────────────────────────────────
// 3. مدیریت حذف فایل‌های نصب
// ──────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'cleanup') {
    $installer = new Installer(__DIR__);
    if ($installer->removeInstaller()) {
        header('Location: /admin/');
        exit;
    }
    die('خطا در حذف فایل‌ها. لطفاً دستی حذف کنید.');
}

// ──────────────────────────────────────
// 4. مدیریت مراحل
// ──────────────────────────────────────
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

// ──────────────────────────────────────
// 5. عنوان‌ها و آیکون‌های مراحل
// ──────────────────────────────────────
$stepTitles = [
    1 => 'پیش‌نیازها',
    2 => 'دیتابیس',
    3 => 'ربات تلگرام',
    4 => 'حساب ادمین',
    5 => 'تنظیمات',
    6 => 'اتمام'
];
$stepIcons = ['📋', '🗄️', '🤖', '👤', '⚙️', '✅'];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نصب ربات یوتیوبر - مرحله <?= $step ?>: <?= $stepTitles[$step] ?></title>
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
    
    <!-- ═══ هدر ═══ -->
    <div class="text-center mb-6">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-1">🎬 نصب ربات تلگرام یوتیوبر</h1>
        <p class="text-white/60 text-sm">نسخه 2.0.0 | نصب آسان در ۶ مرحله</p>
    </div>

    <!-- ═══ نوار مراحل ═══ -->
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
                    <a href="?step=<?= $i ?>" class="w-10 h-10 md:w-11 md:h-11 rounded-full <?= $class ?> flex items-center justify-center text-white text-base transition-all hover:scale-110">✓</a>
                <?php else: ?>
                    <div class="w-10 h-10 md:w-11 md:h-11 rounded-full <?= $class ?> flex items-center justify-center text-white text-base transition-all"><?= $i === $step ? $stepIcons[$i-1] : $i ?></div>
                <?php endif; ?>
                <span class="text-[10px] md:text-xs text-white/70 mt-1 whitespace-nowrap"><?= $stepTitles[$i] ?></span>
            </div>
        <?php endfor; ?>
    </div>

    <!-- ═══ محتوای مرحله ═══ -->
    <div class="glass rounded-2xl p-6 md:p-8 shadow-2xl">
        <?php 
        $stepFile = __DIR__ . "/installer/steps/{$step}-{$currentStep['file']}.php";
        if (file_exists($stepFile)) {
            include $stepFile;
        } else {
            echo '<div class="text-center text-red-400">❌ فایل مرحله یافت نشد</div>';
        }
        ?>
    </div>

    <!-- ═══ فوتر ═══ -->
    <div class="text-center mt-6 text-white/40 text-xs">
        <p>Youtuber Bot v2.0.0 | ساخته شده با ❤️</p>
    </div>

</div>

</body>
</html>