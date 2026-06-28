<?php
$error = '';
$data = $_SESSION['installer_data'] ?? [];
?>

<h2 class="text-2xl font-bold text-white mb-6">👤 ساخت حساب ادمین</h2>
<p class="text-white/70 mb-6">نام کاربری و رمز عبور برای ورود به پنل مدیریت را مشخص کنید.</p>

<?php if ($error): ?>
<div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-4">
    <p class="text-red-300">❌ <?= htmlspecialchars($error) ?></p>
</div>
<?php endif; ?>

<form method="POST" class="space-y-4">
    <div>
        <label class="block text-white mb-2">نام کاربری</label>
        <input type="text" name="admin_username" value="<?= htmlspecialchars($data['admin_username'] ?? 'admin') ?>" 
               required minlength="3" maxlength="50"
               class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50">
        <p class="text-xs text-white/50 mt-1">حداقل 3 کاراکتر</p>
    </div>
    
    <div>
        <label class="block text-white mb-2">رمز عبور</label>
        <input type="password" name="admin_password" required minlength="8"
               class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50">
        <p class="text-xs text-white/50 mt-1">حداقل 8 کاراکتر</p>
    </div>
    
    <div>
        <label class="block text-white mb-2">تکرار رمز عبور</label>
        <input type="password" name="admin_password_confirm" required minlength="8"
               class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50">
    </div>
    
    <div class="flex gap-3">
        <a href="?step=3" class="flex-1 bg-white/10 text-white text-center py-3 rounded-lg font-bold hover:bg-white/20 transition">
            → قبلی
        </a>
        <button type="submit" class="flex-1 bg-gradient-to-r from-purple-500 to-blue-500 text-white py-3 rounded-lg font-bold hover:opacity-90 transition">
            مرحله بعد ←
        </button>
    </div>
</form>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const pass = document.querySelector('[name="admin_password"]').value;
    const confirm = document.querySelector('[name="admin_password_confirm"]').value;
    if (pass !== confirm) {
        e.preventDefault();
        alert('❌ رمز عبور و تکرار آن یکسان نیستند!');
    }
});
</script>