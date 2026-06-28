<?php
$data = $_SESSION['installer_data'] ?? [];
$errors = [];
$success = true;

// 1. ایمپورت دیتابیس
$dbResult = $installer->testDatabaseConnection(
    $data['db_host'], $data['db_name'], $data['db_user'], $data['db_pass']
);

if (!$dbResult['success']) {
    $errors[] = 'خطا در اتصال به دیتابیس: ' . $dbResult['error'];
    $success = false;
} else {
    $importResult = $installer->importDatabase($dbResult['pdo'], $data['db_name']);
    if (!$importResult['success']) {
        $errors[] = 'خطا در ایمپورت دیتابیس: ' . $importResult['error'];
        $success = false;
    }
}

// 2. ساخت ادمین
if ($success) {
    $adminResult = $installer->createAdmin(
        $dbResult['pdo'],
        $data['db_name'],
        $data['admin_username'],
        $data['admin_password']
    );
    if (!$adminResult['success']) {
        $errors[] = 'خطا در ساخت ادمین: ' . $adminResult['error'];
        $success = false;
    }
}

// 3. نوشتن فایل کانفیگ
if ($success) {
    $configResult = $installer->writeConfig($data);
    if (!$configResult['success']) {
        $errors[] = $configResult['error'];
        $success = false;
    }
}

// 4. تنظیم وب‌هوک
$webhookResult = ['success' => false];
if ($success) {
    $webhookUrl = rtrim($data['site_url'], '/') . '/webhook.php';
    $webhookResult = $installer->setWebhook(
        $data['bot_token'],
        $webhookUrl,
        bin2hex(random_bytes(32))
    );
}

// 5. قفل کردن نصب
if ($success) {
    $installer->lockInstallation();
}

// پاک کردن session
$installedData = [
    'admin_username' => $data['admin_username'],
    'site_url' => $data['site_url'],
    'webhook_status' => $webhookResult['success']
];
session_destroy();
?>

<?php if ($success): ?>

<div class="text-center mb-6">
    <div class="text-6xl mb-4">🎉</div>
    <h2 class="text-3xl font-bold text-white mb-2">تبریک! نصب با موفقیت انجام شد</h2>
    <p class="text-white/70">ربات تلگرام شما آماده استفاده است</p>
</div>

<div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4 mb-6 space-y-2">
    <div class="flex items-center gap-2 text-green-300">
        <span>✅</span> <span>دیتابیس با موفقیت ساخته شد</span>
    </div>
    <div class="flex items-center gap-2 text-green-300">
        <span>✅</span> <span>حساب ادمین ایجاد شد</span>
    </div>
    <div class="flex items-center gap-2 text-green-300">
        <span>✅</span> <span>فایل کانفیگ نوشته شد</span>
    </div>
    <?php if ($webhookResult['success']): ?>
    <div class="flex items-center gap-2 text-green-300">
        <span>✅</span> <span>وب‌هوک تلگرام تنظیم شد</span>
    </div>
    <?php else: ?>
    <div class="flex items-center gap-2 text-yellow-300">
        <span>⚠️</span> <span>وب‌هوک تنظیم نشد (دستی تنظیم کنید)</span>
    </div>
    <?php endif; ?>
</div>

<div class="bg-white/5 rounded-lg p-4 mb-6 border border-white/10">
    <h3 class="text-white font-bold mb-3">🔐 اطلاعات ورود شما:</h3>
    <div class="space-y-2 text-sm">
        <div class="flex justify-between text-white">
            <span>آدرس پنل مدیریت:</span>
            <a href="<?= htmlspecialchars($installedData['site_url']) ?>/admin/" target="_blank" class="text-blue-400 font-mono" dir="ltr">
                <?= htmlspecialchars($installedData['site_url']) ?>/admin/
            </a>
        </div>
        <div class="flex justify-between text-white">
            <span>نام کاربری:</span>
            <code class="bg-white/10 px-2 py-1 rounded"><?= htmlspecialchars($installedData['admin_username']) ?></code>
        </div>
    </div>
</div>

<div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-6">
    <p class="text-red-300 font-bold mb-2">🔒 اقدامات امنیتی ضروری:</p>
    <ul class="text-white/80 text-sm space-y-1 list-disc pr-5">
        <li>فایل <code>install.php</code> و پوشه <code>installer/</code> را حذف کنید</li>
        <li>SSL Certificate را فعال کنید (الزامی)</li>
        <li>مجوز پوشه <code>config/</code> را به 644 تغییر دهید</li>
        <li>از دیتابیس بکاپ منظم بگیرید</li>
    </ul>
</div>

<div class="flex gap-3">
    <button onclick="if(confirm('آیا فایل‌های نصب‌کننده حذف شوند؟')) window.location='install.php?action=cleanup'" 
            class="flex-1 bg-red-500/30 border border-red-500/50 text-white py-3 rounded-lg font-bold hover:bg-red-500/50 transition">
        🗑️ حذف خودکار فایل‌های نصب
    </button>
    <a href="<?= htmlspecialchars($installedData['site_url']) ?>/admin/" target="_blank"
       class="flex-1 bg-gradient-to-r from-green-500 to-emerald-500 text-white text-center py-3 rounded-lg font-bold hover:opacity-90 transition">
        🚀 ورود به پنل مدیریت
    </a>
</div>

<?php else: ?>

<div class="text-center mb-6">
    <div class="text-6xl mb-4">❌</div>
    <h2 class="text-3xl font-bold text-white mb-2">خطا در نصب</h2>
    <p class="text-white/70">متأسفانه نصب با خطا مواجه شد</p>
</div>

<div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-6">
    <h3 class="text-red-300 font-bold mb-3">خطاهای رخ داده:</h3>
    <ul class="space-y-2">
        <?php foreach ($errors as $err): ?>
        <li class="text-white/80 text-sm flex gap-2">
            <span>❌</span> <span><?= htmlspecialchars($err) ?></span>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

<a href="?step=1" class="block w-full bg-white/20 text-white text-center py-3 rounded-lg font-bold hover:bg-white/30 transition">
    🔄 شروع مجدد
</a>

<?php endif; ?>