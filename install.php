<?php
/**
 * Youtuber Bot Installer
 * نسخه: 2.0.0
 */

// جلوگیری از اجرای مجدد
if (file_exists(__DIR__ . '/config/config.php') && !isset($_GET['force'])) {
    die('<div style="direction:rtl;text-align:center;padding:50px;font-family:Tahoma;">
        <h1>⚠️ پروژه قبلاً نصب شده است!</h1>
        <p>برای نصب مجدد، فایل <code>config/config.php</code> را حذف کنید یا 
        <a href="?force=1">اینجا کلیک کنید</a></p>
    </div>');
}

// شروع session
session_start();

// بارگذاری خودکار
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/installer/classes/' . $class . '.php';
    if (file_exists($file)) require_once $file;
});

// مرحله فعلی
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$step = max(1, min(6, $step));

// ذخیره داده‌ها در session
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['installer_data'] = array_merge(
        $_SESSION['installer_data'] ?? [],
        $_POST
    );
}

// پردازش مرحله
$installer = new Installer(__DIR__);
$currentStep = $installer->getStep($step);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نصب ربات تلگرام یوتیوبر - مرحله <?= $step ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        * { font-family: 'Vazirmatn', Tahoma, sans-serif; }
        .glass { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); }
        .step-active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .step-done { background: #10b981; }
        .step-pending { background: rgba(255,255,255,0.2); }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 min-h-screen">

<div class="container mx-auto px-4 py-8 max-w-4xl">
    
    <!-- هدر -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">🎬 نصب ربات تلگرام یوتیوبر</h1>
        <p class="text-white/70">نسخه 2.0.0 - نصب آسان در 6 مرحله</p>
    </div>

    <!-- نوار مراحل -->
    <div class="flex justify-between items-center mb-8 glass rounded-2xl p-4">
        <?php for ($i = 1; $i <= 6; $i++): 
            $class = $i < $step ? 'step-done' : ($i === $step ? 'step-active' : 'step-pending');
            $icons = ['📋', '🗄️', '🤖', '👤', '⚙️', '✅'];
        ?>
        <div class="flex flex-col items-center flex-1">
            <div class="w-12 h-12 rounded-full <?= $class ?> flex items-center justify-center text-white text-xl mb-2 transition-all">
                <?= $i < $step ? '✓' : $icons[$i-1] ?>
            </div>
            <span class="text-xs text-white/80 hidden md:block">مرحله <?= $i ?></span>
        </div>
        <?php if ($i < 6): ?>
            <div class="flex-1 h-1 <?= $i < $step ? 'bg-green-500' : 'bg-white/20' ?> mx-2"></div>
        <?php endif; ?>
        <?php endfor; ?>
    </div>

    <!-- محتوای مرحله -->
    <div class="glass rounded-2xl p-8 shadow-2xl border border-white/20">
        <?php include __DIR__ . "/installer/steps/{$step}-" . $currentStep['file'] . ".php"; ?>
    </div>

    <!-- فوتر -->
    <div class="text-center mt-8 text-white/60 text-sm">
        <p>ساخته شده با ❤️ برای یوتیوبرهای فارسی‌زبان</p>
    </div>

</div>

</body>
</html>