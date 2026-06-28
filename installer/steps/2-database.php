<?php
/**
 * مرحله ۲: تنظیمات دیتابیس
 */

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_db'])) {
    $result = $installer->testDatabaseConnection(
        $_POST['db_host'],
        $_POST['db_name'],
        $_POST['db_user'],
        $_POST['db_pass']
    );
    
    if ($result['success']) {
        $success = true;
        $_SESSION['db_tested'] = true;
    } else {
        $error = $result['error'];
    }
}

$data = $_SESSION['installer_data'] ?? [];
?>

<h2 class="text-2xl font-bold text-white mb-2">🗄️ تنظیمات دیتابیس</h2>
<p class="text-white/60 mb-6 text-sm">اطلاعات دیتابیس MySQL خود را وارد کنید. دیتابیس به صورت خودکار ساخته می‌شود.</p>

<?php if ($error): ?>
<div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-4">
    <div class="flex items-start gap-2">
        <span class="text-xl">❌</span>
        <div>
            <p class="text-red-300 font-bold mb-1">خطا در اتصال به دیتابیس</p>
            <p class="text-white/70 text-sm"><?= htmlspecialchars($error) ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4 mb-6">
    <div class="flex items-center gap-2 mb-3">
        <span class="text-2xl">✅</span>
        <p class="text-green-300 font-bold">اتصال به دیتابیس با موفقیت برقرار شد!</p>
    </div>
    <div class="bg-black/30 rounded p-3 text-xs text-white/80 space-y-1">
        <p><span class="text-white/60">هاست:</span> <?= htmlspecialchars($data['db_host']) ?></p>
        <p><span class="text-white/60">دیتابیس:</span> <?= htmlspecialchars($data['db_name']) ?></p>
        <p><span class="text-white/60">کاربر:</span> <?= htmlspecialchars($data['db_user']) ?></p>
    </div>
</div>

<a href="?step=3" class="block w-full bg-gradient-to-r from-purple-500 to-blue-500 text-white text-center py-3 rounded-lg font-bold hover:opacity-90 transition transform hover:scale-[1.02]">
    مرحله بعد: تنظیمات ربات تلگرام ←
</a>

<?php else: ?>

<form method="POST" class="space-y-4">
    <div>
        <label class="block text-white mb-2 text-sm font-medium">
            هاست دیتابیس <span class="text-red-400">*</span>
        </label>
        <input type="text" name="db_host" value="<?= htmlspecialchars($data['db_host'] ?? 'localhost') ?>" 
               required class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition" dir="ltr">
        <p class="text-xs text-white/50 mt-1">معمولاً <code class="bg-white/10 px-1 rounded">localhost</code> یا <code class="bg-white/10 px-1 rounded">127.0.0.1</code></p>
    </div>
    
    <div>
        <label class="block text-white mb-2 text-sm font-medium">
            نام دیتابیس <span class="text-red-400">*</span>
        </label>
        <input type="text" name="db_name" value="<?= htmlspecialchars($data['db_name'] ?? 'youtuber_bot') ?>" 
               required class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition" dir="ltr">
        <p class="text-xs text-white/50 mt-1">اگر وجود نداشته باشد، به صورت خودکار ساخته می‌شود</p>
    </div>
    
    <div>
        <label class="block text-white mb-2 text-sm font-medium">
            نام کاربری دیتابیس <span class="text-red-400">*</span>
        </label>
        <input type="text" name="db_user" value="<?= htmlspecialchars($data['db_user'] ?? 'root') ?>" 
               required class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition" dir="ltr">
    </div>
    
    <div>
        <label class="block text-white mb-2 text-sm font-medium">
            رمز عبور دیتابیس
        </label>
        <input type="password" name="db_pass" value="<?= htmlspecialchars($data['db_pass'] ?? '') ?>" 
               class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition" dir="ltr">
        <p class="text-xs text-white/50 mt-1">اگر رمز ندارید، خالی بگذارید</p>
    </div>
    
    <div class="flex gap-3 pt-4">
        <a href="?step=1" class="flex-1 bg-white/10 text-white text-center py-3 rounded-lg font-bold hover:bg-white/20 transition">
            → مرحله قبل
        </a>
        <button type="submit" name="test_db" class="flex-1 bg-gradient-to-r from-purple-500 to-blue-500 text-white py-3 rounded-lg font-bold hover:opacity-90 transition transform hover:scale-[1.02]">
            تست اتصال و ادامه ←
        </button>
    </div>
</form>

<div class="mt-6 bg-blue-500/10 border border-blue-500/30 rounded-lg p-4">
    <p class="text-blue-300 text-sm mb-2">💡 <b>راهنمایی:</b></p>
    <ul class="text-white/70 text-xs space-y-1 list-disc pr-5">
        <li>اگر از <b>cPanel</b> استفاده می‌کنید، از بخش "MySQL Databases" اطلاعات را دریافت کنید</li>
        <li>اگر از <b>DirectAdmin</b> استفاده می‌کنید، از بخش "MySQL Management" اطلاعات را دریافت کنید</li>
        <li>برای سرور محلی (localhost)، معمولاً کاربر <code class="bg-white/10 px-1 rounded">root</code> و بدون رمز است</li>
    </ul>
</div>

<?php endif; ?>