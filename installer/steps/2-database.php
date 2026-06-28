<?php
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

<h2 class="text-2xl font-bold text-white mb-6">🗄️ تنظیمات دیتابیس</h2>
<p class="text-white/70 mb-6">اطلاعات دیتابیس MySQL خود را وارد کنید. دیتابیس به صورت خودکار ساخته می‌شود.</p>

<?php if ($error): ?>
<div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-4">
    <p class="text-red-300">❌ <?= htmlspecialchars($error) ?></p>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4 mb-4">
    <p class="text-green-300">✅ اتصال به دیتابیس با موفقیت برقرار شد!</p>
</div>
<a href="?step=3" class="block w-full bg-gradient-to-r from-purple-500 to-blue-500 text-white text-center py-3 rounded-lg font-bold hover:opacity-90 transition">
    مرحله بعد: تنظیمات ربات ←
</a>
<?php else: ?>

<form method="POST" class="space-y-4">
    <div>
        <label class="block text-white mb-2">هاست دیتابیس</label>
        <input type="text" name="db_host" value="<?= htmlspecialchars($data['db_host'] ?? 'localhost') ?>" 
               required class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50">
    </div>
    
    <div>
        <label class="block text-white mb-2">نام دیتابیس</label>
        <input type="text" name="db_name" value="<?= htmlspecialchars($data['db_name'] ?? 'youtuber_bot') ?>" 
               required class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50">
        <p class="text-xs text-white/50 mt-1">اگر وجود نداشته باشد، ساخته می‌شود</p>
    </div>
    
    <div>
        <label class="block text-white mb-2">نام کاربری دیتابیس</label>
        <input type="text" name="db_user" value="<?= htmlspecialchars($data['db_user'] ?? 'root') ?>" 
               required class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50">
    </div>
    
    <div>
        <label class="block text-white mb-2">رمز عبور دیتابیس</label>
        <input type="password" name="db_pass" value="<?= htmlspecialchars($data['db_pass'] ?? '') ?>" 
               class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50">
    </div>
    
    <div class="flex gap-3">
        <a href="?step=1" class="flex-1 bg-white/10 text-white text-center py-3 rounded-lg font-bold hover:bg-white/20 transition">
            → قبلی
        </a>
        <button type="submit" name="test_db" class="flex-1 bg-gradient-to-r from-purple-500 to-blue-500 text-white py-3 rounded-lg font-bold hover:opacity-90 transition">
            تست اتصال و ادامه ←
        </button>
    </div>
</form>

<?php endif; ?>

<div class="mt-6 bg-blue-500/10 border border-blue-500/30 rounded-lg p-4">
    <p class="text-blue-300 text-sm">💡 <b>راهنمایی:</b> اگر از cPanel یا DirectAdmin استفاده می‌کنید، اطلاعات دیتابیس را از بخش MySQL Databases دریافت کنید.</p>
</div>