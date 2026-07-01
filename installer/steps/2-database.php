<?php
/**
 * مرحله ۲: تنظیمات دیتابیس
 * پشتیبانی از MySQL و Bunny Database
 */

$error = '';
$success = false;

$data = $_SESSION['installer_data'] ?? [];
$dbType = $data['db_type'] ?? 'mysql';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_db'])) {
    $dbType = $_POST['db_type'] ?? 'mysql';
    
    if ($dbType === 'bunny') {
        $result = $installer->testBunnyConnection(
            $_POST['bunny_url'],
            $_POST['bunny_token']
        );
    } else {
        $result = $installer->testDatabaseConnection(
            $_POST['db_host'],
            $_POST['db_name'],
            $_POST['db_user'],
            $_POST['db_pass']
        );
    }
    
    if ($result['success']) {
        $success = true;
        $_SESSION['db_tested'] = true;
    } else {
        $error = $result['error'];
    }
}

// page title
$pageTitle = $dbType === 'bunny' ? 'Bunny Database (اتصال با توکن)' : 'MySQL';
$pageDesc = $dbType === 'bunny'
    ? 'اطلاعات Bunny Database خود را از سایت bunny.net وارد کنید.'
    : 'اطلاعات دیتابیس MySQL خود را وارد کنید. دیتابیس به صورت خودکار ساخته می‌شود.';
?>

<h2 class="text-2xl font-bold text-white mb-2">🗄️ تنظیمات دیتابیس</h2>
<p class="text-white/60 mb-6 text-sm"><?= $pageDesc ?></p>

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
        <?php if ($dbType === 'bunny'): ?>
        <p><span class="text-white/60">نوع:</span> Bunny Database (Turso/libSQL)</p>
        <?php else: ?>
        <p><span class="text-white/60">هاست:</span> <?= htmlspecialchars($data['db_host']) ?></p>
        <p><span class="text-white/60">دیتابیس:</span> <?= htmlspecialchars($data['db_name']) ?></p>
        <?php endif; ?>
    </div>
</div>

<a href="?step=3" class="block w-full bg-gradient-to-r from-purple-500 to-blue-500 text-white text-center py-3 rounded-lg font-bold hover:opacity-90 transition transform hover:scale-[1.02]">
    مرحله بعد: تنظیمات ربات تلگرام ←
</a>

<?php else: ?>

<form method="POST" class="space-y-4" id="dbForm">
    <!-- انتخاب نوع دیتابیس -->
    <div>
        <label class="block text-white mb-2 text-sm font-medium">نوع دیتابیس</label>
        <div class="flex gap-3">
            <label class="flex-1 cursor-pointer">
                <input type="radio" name="db_type" value="mysql" <?= $dbType === 'mysql' ? 'checked' : '' ?>
                       onchange="toggleDbType()" class="hidden peer">
                <div class="p-4 rounded-xl border-2 <?= $dbType === 'mysql' ? 'border-purple-500 bg-purple-500/20' : 'border-white/20 bg-white/5' ?> text-center peer-checked:border-purple-500 peer-checked:bg-purple-500/20">
                    <div class="text-3xl mb-1">🐬</div>
                    <div class="text-white font-bold text-sm">MySQL</div>
                    <div class="text-white/50 text-xs">هاست معمولی / لوکال</div>
                </div>
            </label>
            <label class="flex-1 cursor-pointer">
                <input type="radio" name="db_type" value="bunny" <?= $dbType === 'bunny' ? 'checked' : '' ?>
                       onchange="toggleDbType()" class="hidden peer">
                <div class="p-4 rounded-xl border-2 <?= $dbType === 'bunny' ? 'border-orange-500 bg-orange-500/20' : 'border-white/20 bg-white/5' ?> text-center peer-checked:border-orange-500 peer-checked:bg-orange-500/20">
                    <div class="text-3xl mb-1">🐰</div>
                    <div class="text-white font-bold text-sm">Bunny Database</div>
                    <div class="text-white/50 text-xs">اتصال با توکن (Turso)</div>
                </div>
            </label>
        </div>
    </div>

    <!-- فرم MySQL -->
    <div id="mysqlFields" class="space-y-4" style="display: <?= $dbType === 'mysql' ? 'block' : 'none' ?>">
        <div>
            <label class="block text-white mb-2 text-sm font-medium">هاست دیتابیس <span class="text-red-400">*</span></label>
            <input type="text" name="db_host" value="<?= htmlspecialchars($data['db_host'] ?? 'localhost') ?>"
                   class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition" dir="ltr">
            <p class="text-xs text-white/50 mt-1">معمولاً <code class="bg-white/10 px-1 rounded">localhost</code></p>
        </div>
        <div>
            <label class="block text-white mb-2 text-sm font-medium">نام دیتابیس <span class="text-red-400">*</span></label>
            <input type="text" name="db_name" value="<?= htmlspecialchars($data['db_name'] ?? 'youtuber_bot') ?>"
                   class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition" dir="ltr">
        </div>
        <div>
            <label class="block text-white mb-2 text-sm font-medium">نام کاربری <span class="text-red-400">*</span></label>
            <input type="text" name="db_user" value="<?= htmlspecialchars($data['db_user'] ?? 'root') ?>"
                   class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition" dir="ltr">
        </div>
        <div>
            <label class="block text-white mb-2 text-sm font-medium">رمز عبور</label>
            <input type="password" name="db_pass" value="<?= htmlspecialchars($data['db_pass'] ?? '') ?>"
                   class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition" dir="ltr">
            <p class="text-xs text-white/50 mt-1">اگر رمز ندارید، خالی بگذارید</p>
        </div>
    </div>

    <!-- فرم Bunny -->
    <div id="bunnyFields" class="space-y-4" style="display: <?= $dbType === 'bunny' ? 'block' : 'none' ?>">
        <div>
            <label class="block text-white mb-2 text-sm font-medium">Bunny Database URL <span class="text-red-400">*</span></label>
            <input type="url" name="bunny_url" value="<?= htmlspecialchars($data['bunny_url'] ?? '') ?>"
                   placeholder="libsql://xxxx-xxxx.lite.bunnydb.net"
                   class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition font-mono text-xs" dir="ltr">
            <p class="text-xs text-white/50 mt-1">آدرس دیتابیس از bunny.net</p>
        </div>
        <div>
            <label class="block text-white mb-2 text-sm font-medium">Auth Token <span class="text-red-400">*</span></label>
            <textarea name="bunny_token" rows="3"
                      class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition font-mono text-xs" dir="ltr"><?= htmlspecialchars($data['bunny_token'] ?? '') ?></textarea>
            <p class="text-xs text-white/50 mt-1">توکن احراز هویت از bunny.net</p>
        </div>
        <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-3">
            <p class="text-blue-300 text-xs">💡 Bunny Database یک دیتابیس ابری بر پایه Turso/libSQL است. برای استفاده نیاز به حساب bunny.net دارید.</p>
        </div>
    </div>

    <div class="flex gap-3 pt-4">
        <a href="?step=1" class="flex-1 bg-white/10 text-white text-center py-3 rounded-lg font-bold hover:bg-white/20 transition">→ مرحله قبل</a>
        <button type="submit" name="test_db" class="flex-1 bg-gradient-to-r from-purple-500 to-blue-500 text-white py-3 rounded-lg font-bold hover:opacity-90 transition transform hover:scale-[1.02]">تست اتصال و ادامه ←</button>
    </div>
</form>

<script>
function toggleDbType() {
    const type = document.querySelector('input[name="db_type"]:checked').value;
    document.getElementById('mysqlFields').style.display = type === 'mysql' ? 'block' : 'none';
    document.getElementById('bunnyFields').style.display = type === 'bunny' ? 'block' : 'none';
}
</script>

<div class="mt-6 bg-blue-500/10 border border-blue-500/30 rounded-lg p-4">
    <p class="text-blue-300 text-sm mb-2">💡 <b>راهنمایی:</b></p>
    <ul class="text-white/70 text-xs space-y-1 list-disc pr-5">
        <li><b>MySQL</b>: برای هاست معمولی یا سرور محلی (cPanel، DirectAdmin، localhost)</li>
        <li><b>Bunny Database</b>: برای دیتابیس ابری bunny.net (اتصال با توکن، نیازی به MySQL ندارد)</li>
    </ul>
</div>

<?php endif; ?>