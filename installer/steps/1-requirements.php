<?php
$requirements = $installer->checkRequirements();
$allPassed = true;
foreach ($requirements as $req) {
    if (!$req['status']) $allPassed = false;
}
?>

<h2 class="text-2xl font-bold text-white mb-6">📋 بررسی پیش‌نیازها</h2>
<p class="text-white/70 mb-6">قبل از شروع نصب، سرور شما باید پیش‌نیازهای زیر را داشته باشد:</p>

<div class="space-y-3 mb-6">
    <?php foreach ($requirements as $key => $req): ?>
    <div class="flex items-center justify-between bg-white/5 rounded-lg p-4 border border-white/10">
        <div class="flex items-center gap-3">
            <span class="text-2xl"><?= $req['status'] ? '✅' : '❌' ?></span>
            <span class="text-white"><?= $req['title'] ?></span>
        </div>
        <span class="text-sm text-white/60"><?= $req['current'] ?></span>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($allPassed): ?>
<div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4 mb-6">
    <p class="text-green-300">✅ تمام پیش‌نیازها با موفقیت بررسی شدند!</p>
</div>
<a href="?step=2" class="block w-full bg-gradient-to-r from-purple-500 to-blue-500 text-white text-center py-3 rounded-lg font-bold hover:opacity-90 transition">
    مرحله بعد: تنظیمات دیتابیس ←
</a>
<?php else: ?>
<div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-6">
    <p class="text-red-300 font-bold mb-2">❌ برخی پیش‌نیازها برآورده نشده‌اند!</p>
    <p class="text-white/70 text-sm">لطفاً قبل از ادامه، مشکلات بالا را برطرف کنید.</p>
</div>
<button onclick="location.reload()" class="block w-full bg-white/20 text-white text-center py-3 rounded-lg font-bold hover:bg-white/30 transition">
    🔄 بررسی مجدد
</button>
<?php endif; ?>