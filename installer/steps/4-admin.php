<?php
/**
 * مرحله ۴: ساخت حساب ادمین
 */

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['admin_username'] ?? '');
    $password = $_POST['admin_password'] ?? '';
    $passwordConfirm = $_POST['admin_password_confirm'] ?? '';
    
    // اعتبارسنجی
    if (empty($username) || strlen($username) < 3) {
        $error = 'نام کاربری باید حداقل 3 کاراکتر باشد';
    } elseif (strlen($username) > 50) {
        $error = 'نام کاربری نباید بیشتر از 50 کاراکتر باشد';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'نام کاربری فقط می‌تواند شامل حروف، اعداد و _ باشد';
    } elseif (strlen($password) < 8) {
        $error = 'رمز عبور باید حداقل 8 کاراکتر باشد';
    } elseif ($password !== $passwordConfirm) {
        $error = 'رمز عبور و تکرار آن یکسان نیستند';
    } else {
        $success = true;
        $_SESSION['admin_created'] = true;
    }
}

$data = $_SESSION['installer_data'] ?? [];
?>

<h2 class="text-2xl font-bold text-white mb-2">👤 ساخت حساب ادمین</h2>
<p class="text-white/60 mb-6 text-sm">نام کاربری و رمز عبور برای ورود به پنل مدیریت را مشخص کنید.</p>

<?php if ($error): ?>
<div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-4">
    <div class="flex items-start gap-2">
        <span class="text-xl">❌</span>
        <div>
            <p class="text-red-300 font-bold mb-1">خطا در اعتبارسنجی</p>
            <p class="text-white/70 text-sm"><?= htmlspecialchars($error) ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4 mb-6">
    <div class="flex items-center gap-2 mb-3">
        <span class="text-2xl">✅</span>
        <p class="text-green-300 font-bold">اطلاعات ادمین با موفقیت ذخیره شد!</p>
    </div>
    <div class="bg-black/30 rounded p-3 text-xs text-white/80">
        <p><span class="text-white/60">نام کاربری:</span> <?= htmlspecialchars($data['admin_username']) ?></p>
        <p class="text-white/50 mt-1">⚠️ رمز عبور نمایش داده نمی‌شود (به دلایل امنیتی)</p>
    </div>
</div>

<a href="?step=5" class="block w-full bg-gradient-to-r from-purple-500 to-blue-500 text-white text-center py-3 rounded-lg font-bold hover:opacity-90 transition transform hover:scale-[1.02]">
    مرحله بعد: تنظیمات سایت ←
</a>

<?php else: ?>

<form method="POST" class="space-y-4" id="adminForm">
    <div>
        <label class="block text-white mb-2 text-sm font-medium">
            نام کاربری <span class="text-red-400">*</span>
        </label>
        <input type="text" name="admin_username" 
               value="<?= htmlspecialchars($data['admin_username'] ?? 'admin') ?>" 
               required minlength="3" maxlength="50"
               pattern="[a-zA-Z0-9_]+"
               class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition"
               placeholder="admin">
        <p class="text-xs text-white/50 mt-1">فقط حروف انگلیسی، اعداد و _ (زیرخط)</p>
    </div>
    
    <div>
        <label class="block text-white mb-2 text-sm font-medium">
            رمز عبور <span class="text-red-400">*</span>
        </label>
        <input type="password" name="admin_password" id="password"
               required minlength="8"
               class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition"
               placeholder="حداقل 8 کاراکتر">
        <div class="mt-2 flex items-center gap-2">
            <input type="checkbox" id="showPass" class="w-4 h-4">
            <label for="showPass" class="text-xs text-white/60">نمایش رمز عبور</label>
        </div>
    </div>
    
    <div>
        <label class="block text-white mb-2 text-sm font-medium">
            تکرار رمز عبور <span class="text-red-400">*</span>
        </label>
        <input type="password" name="admin_password_confirm" id="passwordConfirm"
               required minlength="8"
               class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition"
               placeholder="تکرار رمز عبور">
        <p id="matchError" class="text-xs text-red-400 mt-1 hidden">رمز عبور و تکرار آن یکسان نیستند</p>
    </div>
    
    <div class="flex gap-3 pt-4">
        <a href="?step=3" class="flex-1 bg-white/10 text-white text-center py-3 rounded-lg font-bold hover:bg-white/20 transition">
            → مرحله قبل
        </a>
        <button type="submit" class="flex-1 bg-gradient-to-r from-purple-500 to-blue-500 text-white py-3 rounded-lg font-bold hover:opacity-90 transition transform hover:scale-[1.02]">
            مرحله بعد ←
        </button>
    </div>
</form>

<div class="mt-6 space-y-3">
    <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4">
        <p class="text-yellow-300 text-sm mb-2">🔒 <b>توصیه‌های امنیتی:</b></p>
        <ul class="text-white/70 text-xs space-y-1 list-disc pr-5">
            <li>از رمز عبور قوی استفاده کنید (حداقل 8 کاراکتر، ترکیب حروف و اعداد)</li>
            <li>از رمزهای ساده مثل <code class="bg-white/10 px-1 rounded">12345678</code> یا <code class="bg-white/10 px-1 rounded">password</code> استفاده نکنید</li>
            <li>بعد از نصب، می‌توانید رمز را از پنل مدیریت تغییر دهید</li>
        </ul>
    </div>
    
    <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4">
        <p class="text-blue-300 text-sm">💡 <b>نکته:</b> این اطلاعات برای ورود به پنل مدیریت استفاده می‌شود. آن را در جای امنی ذخیره کنید.</p>
    </div>
</div>

<script>
// نمایش/مخفی کردن رمز عبور
document.getElementById('showPass').addEventListener('change', function() {
    const passField = document.getElementById('password');
    passField.type = this.checked ? 'text' : 'password';
});

// بررسی تطابق رمز عبور
const password = document.getElementById('password');
const confirm = document.getElementById('passwordConfirm');
const matchError = document.getElementById('matchError');

function checkMatch() {
    if (confirm.value && password.value !== confirm.value) {
        matchError.classList.remove('hidden');
        confirm.setCustomValidity('رمز عبور و تکرار آن یکسان نیستند');
    } else {
        matchError.classList.add('hidden');
        confirm.setCustomValidity('');
    }
}

password.addEventListener('input', checkMatch);
confirm.addEventListener('input', checkMatch);
</script>

<?php endif; ?>