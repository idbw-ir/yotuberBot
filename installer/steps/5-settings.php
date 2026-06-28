<?php
$data = $_SESSION['installer_data'] ?? [];

// تشخیص خودکار URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$defaultUrl = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$defaultUrl = rtrim($defaultUrl, '/\\');
?>

<h2 class="text-2xl font-bold text-white mb-6">⚙️ تنظیمات سایت</h2>
<p class="text-white/70 mb-6">اطلاعات عمومی سایت و تنظیمات اختیاری را وارد کنید.</p>

<form method="POST" class="space-y-4">
    <div>
        <label class="block text-white mb-2">نام سایت</label>
        <input type="text" name="site_name" value="<?= htmlspecialchars($data['site_name'] ?? 'ربات یوتیوبر') ?>" 
               required class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50">
    </div>
    
    <div>
        <label class="block text-white mb-2">آدرس سایت (URL)</label>
        <input type="url" name="site_url" value="<?= htmlspecialchars($data['site_url'] ?? $defaultUrl) ?>" 
               required class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50" dir="ltr">
        <p class="text-xs text-white/50 mt-1">⚠️ حتماً با <b>https</b> شروع شود (الزامی برای تلگرام)</p>
    </div>
    
    <div class="bg-white/5 rounded-lg p-4 border border-white/10">
        <h3 class="text-white font-bold mb-3">🧠 هوش مصنوعی (اختیاری)</h3>
        
        <label class="flex items-center gap-2 mb-3">
            <input type="checkbox" name="ai_enabled" value="true" class="w-5 h-5">
            <span class="text-white">فعال‌سازی هوش مصنوعی</span>
        </label>
        
        <div class="space-y-3">
            <input type="text" name="ai_api_key" placeholder="OpenAI API Key"
                   class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 text-sm" dir="ltr">
        </div>
    </div>
    
    <div class="flex gap-3">
        <a href="?step=4" class="flex-1 bg-white/10 text-white text-center py-3 rounded-lg font-bold hover:bg-white/20 transition">
            → قبلی
        </a>
        <button type="submit" class="flex-1 bg-gradient-to-r from-purple-500 to-blue-500 text-white py-3 rounded-lg font-bold hover:opacity-90 transition">
            شروع نصب ←
        </button>
    </div>
</form>   