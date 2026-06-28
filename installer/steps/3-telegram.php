<?php
/**
 * مرحله ۳: تنظیمات ربات تلگرام
 */

$error = '';
$success = false;
$botInfo = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_bot'])) {
    // اعتبارسنجی اولیه
    $token = trim($_POST['bot_token'] ?? '');
    $adminId = trim($_POST['admin_id'] ?? '');
    
    if (empty($token)) {
        $error = 'توکن ربات الزامی است';
    } elseif (empty($adminId) || !is_numeric($adminId)) {
        $error = 'آیدی عددی ادمین معتبر نیست';
    } else {
        $result = $installer->testBotToken($token);
        
        if ($result['success']) {
            $success = true;
            $botInfo = $result['bot'];
            $_SESSION['bot_tested'] = true;
        } else {
            $error = $result['error'];
        }
    }
}

$data = $_SESSION['installer_data'] ?? [];
?>

<h2 class="text-2xl font-bold text-white mb-2">🤖 تنظیمات ربات تلگرام</h2>
<p class="text-white/60 mb-6 text-sm">توکن ربات و آیدی عددی ادمین را وارد کنید. توکن به صورت خودکار تست می‌شود.</p>

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
    <div class="flex items-center gap-3 mb-3">
        <div class="w-12 h-12 bg-green-500/30 rounded-full flex items-center justify-center text-2xl">
            <?= $botInfo['username'][0] === '_' ? '🤖' : '🤖' ?>
        </div>
        <div>
            <p class="text-green-300 font-bold">ربات با موفقیت شناسایی شد!</p>
            <p class="text-white/70 text-sm"><?= htmlspecialchars($botInfo['first_name']) ?></p>
        </div>
    </div>
    
    <div class="bg-black/30 rounded p-3 text-xs text-white/80 space-y-1">
        <p><span class="text-white/60">یوزرنیم:</span> <span dir="ltr">@<?= htmlspecialchars($botInfo['username']) ?></span></p>
        <p><span class="text-white/60">آیدی:</span> <span dir="ltr"><?= htmlspecialchars($botInfo['id']) ?></span></p>
        <p><span class="text-white/60">وضعیت:</span> <?= $botInfo['can_join_groups'] ? 'می‌تواند به گروه بپیوندد' : 'نمی‌تواند به گروه بپیوندد' ?></p>
    </div>
</div>

<a href="?step=4" class="block w-full bg-gradient-to-r from-purple-500 to-blue-500 text-white text-center py-3 rounded-lg font-bold hover:opacity-90 transition transform hover:scale-[1.02]">
    مرحله بعد: ساخت حساب ادمین ←
</a>

<?php else: ?>

<form method="POST" class="space-y-4">
    <div>
        <label class="block text-white mb-2 text-sm font-medium">
            توکن ربات (Bot Token) <span class="text-red-400">*</span>
        </label>
        <input type="text" name="bot_token" 
               value="<?= htmlspecialchars($data['bot_token'] ?? '') ?>" 
               placeholder="1234567890:ABCdefGHIjklMNOpqrsTUVwxyz"
               required 
               class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition font-mono text-sm" 
               dir="ltr">
        <p class="text-xs text-white/50 mt-1">
            از <a href="https://t.me/BotFather" target="_blank" class="text-blue-400 hover:underline">@BotFather</a> دریافت کنید
        </p>
    </div>
    
    <div>
        <label class="block text-white mb-2 text-sm font-medium">
            آیدی عددی ادمین (Admin ID) <span class="text-red-400">*</span>
        </label>
        <input type="text" name="admin_id" 
               value="<?= htmlspecialchars($data['admin_id'] ?? '') ?>" 
               placeholder="123456789"
               required 
               pattern="[0-9]{6,15}"
               class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition font-mono" 
               dir="ltr">
        <p class="text-xs text-white/50 mt-1">
            از <a href="https://t.me/userinfobot" target="_blank" class="text-blue-400 hover:underline">@userinfobot</a> دریافت کنید
        </p>
    </div>
    
    <div class="flex gap-3 pt-4">
        <a href="?step=2" class="flex-1 bg-white/10 text-white text-center py-3 rounded-lg font-bold hover:bg-white/20 transition">
            → مرحله قبل
        </a>
        <button type="submit" name="test_bot" class="flex-1 bg-gradient-to-r from-purple-500 to-blue-500 text-white py-3 rounded-lg font-bold hover:opacity-90 transition transform hover:scale-[1.02]">
            تست توکن و ادامه ←
        </button>
    </div>
</form>

<div class="mt-6 space-y-3">
    <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4">
        <p class="text-yellow-300 text-sm">⚠️ <b>هشدار امنیتی:</b> توکن ربات خود را با کسی به اشتراک نگذارید. هر کسی با این توکن می‌تواند کنترل ربات شما را در دست بگیرد.</p>
    </div>
    
    <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4">
        <p class="text-blue-300 text-sm mb-2">💡 <b>راهنمای دریافت توکن:</b></p>
        <ol class="text-white/70 text-xs space-y-1 list-decimal pr-5">
            <li>در تلگرام به <code class="bg-white/10 px-1 rounded">@BotFather</code> پیام دهید</li>
            <li>دستور <code class="bg-white/10 px-1 rounded">/newbot</code> را ارسال کنید</li>
            <li>نام و یوزرنیم ربات را وارد کنید</li>
            <li>توکن دریافتی را کپی و در فیلد بالا وارد کنید</li>
        </ol>
    </div>
</div>

<?php endif; ?>