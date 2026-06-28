<?php
/**
 * مرحله ۱: بررسی پیش‌نیازها
 */

$requirements = $installer->checkRequirements();
$allPassed = true;
$warnings = 0;

foreach ($requirements as $req) {
    if (!$req['status']) {
        $allPassed = false;
        if (strpos($req['title'], 'پوشه') !== false) {
            $warnings++;
        }
    }
}
?>

<h2 class="text-2xl font-bold text-white mb-2">📋 بررسی پیش‌نیازها</h2>
<p class="text-white/60 mb-6 text-sm">قبل از شروع نصب، سرور شما باید پیش‌نیازهای زیر را داشته باشد:</p>

<div class="space-y-2 mb-6">
    <?php foreach ($requirements as $key => $req): ?>
    <div class="flex items-center justify-between bg-white/5 rounded-lg p-3 border border-white/10 hover:bg-white/10 transition">
        <div class="flex items-center gap-3">
            <span class="text-xl"><?= $req['status'] ? '✅' : (strpos($req['title'], 'پوشه') !== false ? '⚠️' : '❌') ?></span>
            <span class="text-white text-sm"><?= $req['title'] ?></span>
        </div>
        <span class="text-xs text-white/50 font-mono"><?= $req['current'] ?></span>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($allPassed): ?>
<div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4 mb-6">
    <div class="flex items-center gap-2">
        <span class="text-2xl">🎉</span>
        <p class="text-green-300 font-bold">تمام پیش‌نیازها با موفقیت بررسی شدند!</p>
    </div>
</div>

<a href="?step=2" class="block w-full bg-gradient-to-r from-purple-500 to-blue-500 text-white text-center py-3 rounded-lg font-bold hover:opacity-90 transition transform hover:scale-[1.02]">
    مرحله بعد: تنظیمات دیتابیس ←
</a>

<?php elseif ($warnings > 0 && count(array_filter($requirements, fn($r) => !$r['status'])) === $warnings): ?>
<div class="bg-yellow-500/20 border border-yellow-500/50 rounded-lg p-4 mb-4">
    <p class="text-yellow-300 font-bold mb-2">⚠️ برخی پوشه‌ها قابل نوشتن نیستند</p>
    <p class="text-white/70 text-sm mb-3">می‌توانید ادامه دهید، اما ممکن است در ذخیره فایل‌ها مشکل داشته باشید.</p>
    <div class="bg-black/30 rounded p-3 text-xs font-mono text-white/80 mb-3" dir="ltr">
        chmod -R 775 storage/<br>
        chown -R www-data:www-data storage/
    </div>
</div>

<a href="?step=2" class="block w-full bg-gradient-to-r from-yellow-500 to-orange-500 text-white text-center py-3 rounded-lg font-bold hover:opacity-90 transition mb-2">
    ادامه با وجود هشدار ←
</a>
<a href="?" class="block w-full bg-white/10 text-white text-center py-3 rounded-lg font-bold hover:bg-white/20 transition">
    🔄 بررسی مجدد
</a>

<?php else: ?>
<div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-4">
    <p class="text-red-300 font-bold mb-2">❌ برخی پیش‌نیازهای ضروری برآورده نشده‌اند!</p>
    <p class="text-white/70 text-sm mb-3">لطفاً قبل از ادامه، مشکلات بالا را برطرف کنید.</p>
    
    <div class="bg-black/30 rounded p-3 text-xs text-white/80 space-y-1">
        <p class="font-bold mb-2">💡 راهنمای نصب پیش‌نیازها (Ubuntu/Debian):</p>
        <div class="font-mono" dir="ltr">
            sudo apt update<br>
            sudo apt install php8.1-fpm php8.1-mysql php8.1-curl php8.1-mbstring php8.1-xml
        </div>
    </div>
</div>

<button onclick="location.reload()" class="block w-full bg-white/20 text-white text-center py-3 rounded-lg font-bold hover:bg-white/30 transition">
    🔄 بررسی مجدد
</button>
<?php endif; ?>

<div class="mt-6 bg-blue-500/10 border border-blue-500/30 rounded-lg p-3">
    <p class="text-blue-300 text-xs">💡 <b>نکته:</b> این بررسی فقط یک بار انجام می‌شود. پس از نصب، این صفحه دیگر قابل دسترسی نخواهد بود.</p>
</div>