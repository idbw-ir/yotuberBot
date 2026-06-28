<?php
/**
 * مرحله ۵: تنظیمات سایت
 */

// تشخیص خودکار URL سایت
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$defaultUrl = $protocol . '://' . $host . ($scriptDir === '/' ? '' : $scriptDir);
$defaultUrl = rtrim($defaultUrl, '/\\');

$data = $_SESSION['installer_data'] ?? [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteName = trim($_POST['site_name'] ?? '');
    $siteUrl = trim($_POST['site_url'] ?? '');
    
    if (empty($siteName)) {
        $error = 'نام سایت الزامی است';
    } elseif (empty($siteUrl) || !filter_var($siteUrl, FILTER_VALIDATE_URL)) {
        $error = 'آدرس سایت معتبر نیست';
    } elseif (strpos($siteUrl, 'http://') === 0) {
        $error = 'آدرس سایت باید با https شروع شود (الزامی برای تلگرام)';
    } else {
        $success = true;
    }
}
?>

<h2 class="text-2xl font-bold text-white mb-2">⚙️ تنظیمات سایت</h2>
<p class="text-white/60 mb-6 text-sm">اطلاعات عمومی سایت و تنظیمات اختیاری هوش مصنوعی را وارد کنید.</p>

<?php if (!empty($error)): ?>
<div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-4">
    <div class="flex items-start gap-2">
        <span class="text-xl">❌</span>
        <p class="text-red-300 text-sm"><?= htmlspecialchars($error) ?></p>
    </div>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4 mb-6">
    <div class="flex items-center gap-2 mb-3">
        <span class="text-2xl">✅</span>
        <p class="text-green-300 font-bold">تنظیمات با موفقیت ذخیره شد!</p>
    </div>
    <div class="bg-black/30 rounded p-3 text-xs text-white/80 space-y-1">
        <p><span class="text-white/60">نام سایت:</span> <?= htmlspecialchars($data['site_name']) ?></p>
        <p><span class="text-white/60">آدرس:</span> <span dir="ltr"><?= htmlspecialchars($data['site_url']) ?></span></p>
        <p><span class="text-white/60">هوش مصنوعی:</span> <?= isset($data['ai_enabled']) ? 'فعال 🧠' : 'غیرفعال' ?></p>
    </div>
</div>

<a href="?step=6" class="block w-full bg-gradient-to-r from-green-500 to-emerald-500 text-white text-center py-3 rounded-lg font-bold hover:opacity-90 transition transform hover:scale-[1.02]">
    🚀 شروع نصب نهایی ←
</a>

<?php else: ?>

<form method="POST" class="space-y-4">
    <!-- نام سایت -->
    <div>
        <label class="block text-white mb-2 text-sm font-medium">
            نام سایت <span class="text-red-400">*</span>
        </label>
        <input type="text" name="site_name" 
               value="<?= htmlspecialchars($data['site_name'] ?? 'ربات یوتیوبر') ?>" 
               required maxlength="100"
               class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition">
    </div>
    
    <!-- آدرس سایت -->
    <div>
        <label class="block text-white mb-2 text-sm font-medium">
            آدرس کامل سایت (URL) <span class="text-red-400">*</span>
        </label>
        <input type="url" name="site_url" 
               value="<?= htmlspecialchars($data['site_url'] ?? $defaultUrl) ?>" 
               required
               class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition font-mono text-sm" 
               dir="ltr"
               placeholder="https://bot.yourdomain.com">
        <p class="text-xs text-white/50 mt-1">
            ⚠️ حتماً با <code class="bg-white/10 px-1 rounded">https</code> شروع شود (الزامی برای تلگرام)
        </p>
    </div>
    
    <!-- تنظیمات هوش مصنوعی -->
    <div class="bg-white/5 rounded-lg p-4 border border-white/10">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-white font-bold text-sm">🧠 هوش مصنوعی (اختیاری)</h3>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="ai_enabled" value="true" 
                       class="sr-only peer" id="aiToggle">
                <div class="w-11 h-6 bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-500"></div>
            </label>
        </div>
        
        <div id="aiSettings" class="space-y-3 hidden">
            <div>
                <label class="block text-white/80 mb-2 text-xs">OpenAI API Key</label>
                <input type="text" name="ai_api_key" 
                       value="<?= htmlspecialchars($data['ai_api_key'] ?? '') ?>" 
                       placeholder="sk-..."
                       class="w-full p-2.5 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition text-sm" 
                       dir="ltr">
                <p class="text-xs text-white/50 mt-1">
                    از <a href="https://platform.openai.com/api-keys" target="_blank" class="text-blue-400 hover:underline">OpenAI Platform</a> دریافت کنید
                </p>
            </div>
        </div>
        
        <p class="text-xs text-white/50 mt-3">💡 با فعال‌سازی این گزینه، ربات می‌تواند به صورت هوشمند به پیام‌های کاربران پاسخ دهد</p>
    </div>
    
    <!-- دکمه‌ها -->
    <div class="flex gap-3 pt-4">
        <a href="?step=4" class="flex-1 bg-white/10 text-white text-center py-3 rounded-lg font-bold hover:bg-white/20 transition">
            → مرحله قبل
        </a>
        <button type="submit" class="flex-1 bg-gradient-to-r from-purple-500 to-blue-500 text-white py-3 rounded-lg font-bold hover:opacity-90 transition transform hover:scale-[1.02]">
            ذخیره و ادامه ←
        </button>
    </div>
</form>

<div class="mt-6 space-y-3">
    <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4">
        <p class="text-blue-300 text-sm mb-2">💡 <b>راهنمای URL:</b></p>
        <ul class="text-white/70 text-xs space-y-1 list-disc pr-5">
            <li>اگر روی دامنه اصلی نصب می‌کنید: <code class="bg-white/10 px-1 rounded" dir="ltr">https://yourdomain.com</code></li>
            <li>اگر روی ساب‌دامین نصب می‌کنید: <code class="bg-white/10 px-1 rounded" dir="ltr">https://bot.yourdomain.com</code></li>
            <li>اگر در پوشه نصب می‌کنید: <code class="bg-white/10 px-1 rounded" dir="ltr">https://yourdomain.com/bot</code></li>
        </ul>
    </div>
    
    <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4">
        <p class="text-yellow-300 text-sm">⚠️ <b>توجه:</b> پس از این مرحله، نصب شروع می‌شود و فایل‌های نصب‌کننده حذف خواهند شد. لطفاً اطلاعات را بررسی کنید.</p>
    </div>
</div>

<script>
// نمایش/مخفی تنظیمات هوش مصنوعی
document.getElementById('aiToggle').addEventListener('change', function() {
    document.getElementById('aiSettings').classList.toggle('hidden', !this.checked);
});

// اگر از قبل فعال بوده
<?php if (isset($data['ai_enabled'])): ?>
document.getElementById('aiToggle').checked = true;
document.getElementById('aiSettings').classList.remove('hidden');
<?php endif; ?>
</script>

<?php endif; ?>