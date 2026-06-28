<?php
$error = '';
$success = false;
$botInfo = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_bot'])) {
    $result = $installer->testBotToken($_POST['bot_token']);
    
    if ($result['success']) {
        $success = true;
        $botInfo = $result['bot'];
        $_SESSION['bot_tested'] = true;
    } else {
        $error = $result['error'];
    }
}

$data = $_SESSION['installer_data'] ?? [];
?>

<h2 class="text-2xl font-bold text-white mb-6">🤖 تنظیمات ربات تلگرام</h2>
<p class="text-white/70 mb-6">توکن ربات و آیدی ادمین را وارد کنید.</p>

<?php if ($error): ?>
<div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-4">
    <p class="text-red-300">❌ <?= htmlspecialchars($error) ?></p>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4 mb-4">
    <div class="flex items-center gap-3">
        <span class="text-4xl">✅</span>
        <div>
            <p class="text-green-300 font-bold">ربات با موفقیت شناسایی شد!</p>
            <p class="text-white/70 text-sm mt-1">
                @<?= htmlspecialchars($botInfo['username']) ?> - <?= htmlspecialchars($botInfo['first_name']) ?>
            </p>
        </div>
    </div>
</div>
<a href="?step=4" class="block w-full bg-gradient-to-r from-purple-500 to-blue-500 text-white text-center py-3 rounded-lg font-bold hover:opacity-90 transition">
    مرحله بعد: ساخت ادمین ←
</a>
<?php else: ?>

<form method="POST" class="space-y-4">
    <div>
        <label class="block text-white mb-2">توکن ربات (Bot Token)</label>
        <input type="text" name="bot_token" value="<?= htmlspecialchars($data['bot_token'] ?? '') ?>" 
               placeholder="1234567890:ABCdefGHIjklMNOpqrsTUVwxyz"
               required class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 font-mono text-sm" dir="ltr">
        <p class="text-xs text-white/50 mt-1">از <a href="https://t.me/BotFather" target="_blank" class="text-blue-400">@BotFather</a> دریافت کنید</p>
    </div>
    
    <div>
        <label class="block text-white mb-2">آیدی عددی ادمین (Admin ID)</label>
        <input type="text" name="admin_id" value="<?= htmlspecialchars($data['admin_id'] ?? '') ?>" 
               placeholder="123456789"
               required class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 font-mono" dir="ltr">
        <p class="text-xs text-white/50 mt-1">از <a href="https://t.me/userinfobot" target="_blank" class="text-blue-400">@userinfobot</a> دریافت کنید</p>
    </div>
    
    <div class="flex gap-3">
        <a href="?step=2" class="flex-1 bg-white/10 text-white text-center py-3 rounded-lg font-bold hover:bg-white/20 transition">
            → قبلی
        </a>
        <button type="submit" name="test_bot" class="flex-1 bg-gradient-to-r from-purple-500 to-blue-500 text-white py-3 rounded-lg font-bold hover:opacity-90 transition">
            تست توکن و ادامه ←
        </button>
    </div>
</form>

<div class="mt-6 bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4">
    <p class="text-yellow-300 text-sm">⚠️ <b>توجه:</b> توکن ربات خود را با کسی به اشتراک نگذارید. هر کسی با این توکن می‌تواند کنترل ربات شما را در دست بگیرد.</p>
</div>

<?php endif; ?>