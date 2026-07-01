<?php
/**
 * مرحله ۶: اتمام نصب
 * این مرحله تمام عملیات نصب رو انجام می‌ده
 */

$data = $_SESSION['installer_data'] ?? [];
$errors = [];
$success = true;
$webhookResult = ['success' => false];
$baleWebhookResult = ['success' => false];

// جلوگیری از اجرای مجدد
if (file_exists(__DIR__ . '/../../config/config.php') && file_exists(__DIR__ . '/../../install.lock')) {
    header('Location: /admin/');
    exit;
}

$driver = $data['db_driver'] ?? $data['db_type'] ?? 'mysql';

// ══════════════════════════════════════
// 1. تست اتصال دیتابیس
// ══════════════════════════════════════
if ($driver === 'bunny') {
    $dbResult = $installer->testBunnyConnection(
        $data['bunny_url'] ?? '',
        $data['bunny_token'] ?? ''
    );
} else {
    $dbResult = $installer->testDatabaseConnection(
        $data['db_host'] ?? 'localhost',
        $data['db_name'] ?? 'youtuber_bot',
        $data['db_user'] ?? 'root',
        $data['db_pass'] ?? ''
    );
}

if (!$dbResult['success']) {
    $errors[] = 'خطا در اتصال به دیتابیس: ' . $dbResult['error'];
    $success = false;
}

// ══════════════════════════════════════
// 2. ایمپورت اسکریپت دیتابیس
// ══════════════════════════════════════
if ($success) {
    $conn = $dbResult['pdo'] ?? $dbResult['bunny'];
    $importResult = $installer->importDatabase(
        $conn,
        $data['db_name'] ?? 'youtuber_bot',
        $driver
    );
    
    if (!$importResult['success']) {
        $errors[] = 'خطا در ایمپورت دیتابیس: ' . $importResult['error'];
        $success = false;
    }
}

// ══════════════════════════════════════
// 3. ساخت حساب ادمین
// ══════════════════════════════════════
if ($success) {
    $conn = $dbResult['pdo'] ?? $dbResult['bunny'];
    $adminResult = $installer->createAdmin(
        $conn,
        $data['db_name'] ?? 'youtuber_bot',
        $data['admin_username'] ?? 'admin',
        $data['admin_password'] ?? '',
        $driver
    );
    
    if (!$adminResult['success']) {
        $errors[] = 'خطا در ساخت حساب ادمین: ' . $adminResult['error'];
        $success = false;
    }
}

// ══════════════════════════════════════
// 4. نوشتن فایل کانفیگ
// ══════════════════════════════════════
if ($success) {
    $configResult = $installer->writeConfig($data);
    
    if (!$configResult['success']) {
        $errors[] = $configResult['error'];
        $success = false;
    }
}

// ══════════════════════════════════════
// 5. تنظیم وب‌هوک تلگرام
// ══════════════════════════════════════
if ($success && !empty($data['bot_token'])) {
    $webhookUrl = rtrim($data['site_url'] ?? '', '/') . '/webhook.php';
    $webhookSecret = bin2hex(random_bytes(32));
    
    $webhookResult = $installer->setWebhook(
        $data['bot_token'],
        $webhookUrl,
        $webhookSecret
    );
}

// ══════════════════════════════════════
// 6. تنظیم وب‌هوک بله
// ══════════════════════════════════════
if ($success && !empty($data['bale_bot_token'])) {
    $baleWebhookUrl = rtrim($data['site_url'] ?? '', '/') . '/webhook-bale.php';
    
    $baleWebhookResult = $installer->setBaleWebhook(
        $data['bale_bot_token'],
        $baleWebhookUrl
    );
}

// ══════════════════════════════════════
// 7. قفل کردن نصب
// ══════════════════════════════════════
if ($success) {
    $installer->lockInstallation();
}

// ══════════════════════════════════════
// 8. ذخیره اطلاعات برای نمایش
// ══════════════════════════════════════
$installedData = [
    'admin_username' => $data['admin_username'] ?? 'admin',
    'site_url' => rtrim($data['site_url'] ?? '', '/'),
    'webhook_status' => $webhookResult['success'],
    'bale_webhook_status' => $baleWebhookResult['success'],
    'telegram_enabled' => !empty($data['bot_token']),
    'bale_enabled' => !empty($data['bale_bot_token']),
    'database' => $data['db_name'] ?? 'youtuber_bot'
];

// پاک کردن session
$finalData = $installedData;
unset($_SESSION['installer_data']);
unset($_SESSION['db_tested']);
unset($_SESSION['bot_tested']);
unset($_SESSION['admin_created']);
?>

<?php if ($success): ?>

<div class="text-center mb-6">
    <div class="inline-block animate-bounce text-6xl mb-4">🎉</div>
    <h2 class="text-3xl font-bold text-white mb-2">تبریک! نصب با موفقیت انجام شد</h2>
    <p class="text-white/60">ربات شما آماده استفاده است</p>
</div>

<!-- ═══ وضعیت نصب ═══ -->
<div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4 mb-6">
    <h3 class="text-green-300 font-bold mb-3 text-sm">✅ مراحل نصب:</h3>
    <div class="space-y-2">
        <div class="flex items-center gap-2 text-green-300 text-sm">
            <span>✅</span> <span>دیتابیس با موفقیت ساخته شد</span>
        </div>
        <div class="flex items-center gap-2 text-green-300 text-sm">
            <span>✅</span> <span>جداول دیتابیس ایمپورت شدند</span>
        </div>
        <div class="flex items-center gap-2 text-green-300 text-sm">
            <span>✅</span> <span>حساب ادمین ایجاد شد</span>
        </div>
        <div class="flex items-center gap-2 text-green-300 text-sm">
            <span>✅</span> <span>فایل کانفیگ نوشته شد</span>
        </div>
        <?php if ($finalData['telegram_enabled']): ?>
        <?php if ($webhookResult['success']): ?>
        <div class="flex items-center gap-2 text-green-300 text-sm">
            <span>✅</span> <span>وب‌هوک تلگرام تنظیم شد</span>
        </div>
        <?php else: ?>
        <div class="flex items-center gap-2 text-yellow-300 text-sm">
            <span>⚠️</span> <span>وب‌هوک تلگرام تنظیم نشد (دستی تنظیم کنید)</span>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        
        <?php if ($finalData['bale_enabled']): ?>
        <?php if ($baleWebhookResult['success']): ?>
        <div class="flex items-center gap-2 text-green-300 text-sm">
            <span>✅</span> <span>وب‌هوک بله تنظیم شد</span>
        </div>
        <?php else: ?>
        <div class="flex items-center gap-2 text-yellow-300 text-sm">
            <span>⚠️</span> <span>وب‌هوک بله تنظیم نشد (دستی تنظیم کنید)</span>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <div class="flex items-center gap-2 text-green-300 text-sm">
            <span>✅</span> <span>نصب قفل شد</span>
        </div>
    </div>
</div>

<!-- ═══ اطلاعات ورود ═══ -->
<div class="bg-white/5 rounded-lg p-4 mb-6 border border-white/10">
    <h3 class="text-white font-bold mb-3 text-sm">🔐 اطلاعات ورود به پنل مدیریت:</h3>
    <div class="space-y-2 text-sm">
        <div class="flex justify-between items-center text-white">
            <span class="text-white/60">آدرس پنل:</span>
            <a href="<?= htmlspecialchars($finalData['site_url']) ?>/admin/" target="_blank" 
               class="text-blue-400 hover:underline font-mono text-xs" dir="ltr">
                <?= htmlspecialchars($finalData['site_url']) ?>/admin/
            </a>
        </div>
        <div class="flex justify-between items-center text-white">
            <span class="text-white/60">نام کاربری:</span>
            <code class="bg-white/10 px-2 py-1 rounded text-xs"><?= htmlspecialchars($finalData['admin_username']) ?></code>
        </div>
        <div class="flex justify-between items-center text-white">
            <span class="text-white/60">رمز عبور:</span>
            <span class="text-xs text-white/50">همانی که در مرحله ۴ وارد کردید</span>
        </div>
    </div>
</div>

<!-- ═══ اقدامات امنیتی ═══ -->
<div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-6">
    <h3 class="text-red-300 font-bold mb-2 text-sm">🔒 اقدامات امنیتی ضروری:</h3>
    <ul class="text-white/70 text-xs space-y-1 list-disc pr-5">
        <li>فایل‌های <code class="bg-white/10 px-1 rounded">install.php</code> و پوشه <code class="bg-white/10 px-1 rounded">installer/</code> را حذف کنید</li>
        <li>SSL Certificate را فعال کنید (اگر هنوز فعال نیست)</li>
        <li>مجوز پوشه <code class="bg-white/10 px-1 rounded">config/</code> را به <code class="bg-white/10 px-1 rounded">644</code> تغییر دهید</li>
        <li>از دیتابیس بکاپ منظم بگیرید</li>
        <?php if (!$webhookResult['success']): ?>
        <li>وب‌هوک تلگرام را دستی تنظیم کنید (دستور در پایین)</li>
        <?php endif; ?>
    </ul>
</div>

<?php if (!$webhookResult['success'] && $finalData['telegram_enabled']): ?>
<!-- ═══ دستور تنظیم وب‌هوک تلگرام ═══ -->
<div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4 mb-6">
    <h3 class="text-yellow-300 font-bold mb-2 text-sm">⚠️ تنظیم دستی وب‌هوک تلگرام:</h3>
    <p class="text-white/70 text-xs mb-2">این دستور را در ترمینال اجرا کنید:</p>
    <div class="bg-black/40 rounded p-3 text-xs font-mono text-green-400 overflow-x-auto" dir="ltr">
        curl -F "url=<?= htmlspecialchars($finalData['site_url']) ?>/webhook.php" -F "secret_token=YOUR_SECRET" \<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;https://api.telegram.org/bot&lt;YOUR_BOT_TOKEN&gt;/setWebhook
    </div>
</div>
<?php endif; ?>

<?php if (!$baleWebhookResult['success'] && $finalData['bale_enabled']): ?>
<!-- ═══ دستور تنظیم وب‌هوک بله ═══ -->
<div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4 mb-6">
    <h3 class="text-yellow-300 font-bold mb-2 text-sm">⚠️ تنظیم دستی وب‌هوک بله:</h3>
    <p class="text-white/70 text-xs mb-2">این دستور را در ترمینال اجرا کنید:</p>
    <div class="bg-black/40 rounded p-3 text-xs font-mono text-green-400 overflow-x-auto" dir="ltr">
        curl -F "url=<?= htmlspecialchars($finalData['site_url']) ?>/webhook-bale.php" \<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;https://tapi.bale.ai/bot&lt;YOUR_BOT_TOKEN&gt;/setWebhook
    </div>
</div>
<?php endif; ?>

<!-- ═══ دکمه‌های نهایی ═══ -->
<div class="flex gap-3">
    <a href="?action=cleanup" 
       onclick="return confirm('آیا فایل‌های نصب‌کننده حذف شوند؟\n\nاین عمل غیرقابل بازگشت است.')"
       class="flex-1 bg-red-500/30 border border-red-500/50 text-white text-center py-3 rounded-lg font-bold hover:bg-red-500/50 transition text-sm">
        🗑️ حذف فایل‌های نصب
    </a>
    <a href="<?= htmlspecialchars($finalData['site_url']) ?>/admin/" target="_blank"
       class="flex-1 bg-gradient-to-r from-green-500 to-emerald-500 text-white text-center py-3 rounded-lg font-bold hover:opacity-90 transition transform hover:scale-[1.02] text-sm">
        🚀 ورود به پنل مدیریت
    </a>
</div>

<div class="mt-6 text-center">
    <p class="text-white/40 text-xs">📖 برای اطلاعات بیشتر، فایل README.md را مطالعه کنید</p>
</div>

<?php else: ?>

<!-- ═══ حالت خطا ═══ -->
<div class="text-center mb-6">
    <div class="text-6xl mb-4">❌</div>
    <h2 class="text-3xl font-bold text-white mb-2">خطا در نصب</h2>
    <p class="text-white/60">متأسفانه نصب با خطا مواجه شد</p>
</div>

<div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-6">
    <h3 class="text-red-300 font-bold mb-3 text-sm">❌ خطاهای رخ داده:</h3>
    <ul class="space-y-2">
        <?php foreach ($errors as $err): ?>
        <li class="text-white/80 text-sm flex gap-2">
            <span>❌</span> <span><?= htmlspecialchars($err) ?></span>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 mb-6">
    <h3 class="text-blue-300 font-bold mb-2 text-sm">💡 راه‌حل‌های پیشنهادی:</h3>
    <ul class="text-white/70 text-xs space-y-1 list-disc pr-5">
        <li>اطلاعات دیتابیس را بررسی کنید</li>
        <li>مجوز پوشه‌ها را چک کنید (<code class="bg-white/10 px-1 rounded">chmod 775 storage/</code>)</li>
        <li>مطمئن شوید فایل <code class="bg-white/10 px-1 rounded">database/schema.sql</code> وجود دارد</li>
        <li>لاگ‌های سرور را بررسی کنید</li>
    </ul>
</div>

<a href="?step=1" class="block w-full bg-white/20 text-white text-center py-3 rounded-lg font-bold hover:bg-white/30 transition">
    🔄 شروع مجدد نصب
</a>

<?php endif; ?>