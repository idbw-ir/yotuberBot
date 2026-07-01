<?php
/**
 * مرحله ۳: تنظیمات ربات و پروکسی
 * انتخاب پلتفرم (تلگرام، بله، یا هر دو)
 */

$error = '';
$success = false;
$botInfo = null;
$baleBotInfo = null;
$proxyTestResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['installer_data']['telegram_enabled'] = $_POST['telegram_enabled'] ?? '0';
    $_SESSION['installer_data']['bale_enabled'] = $_POST['bale_enabled'] ?? '0';
    $_SESSION['installer_data']['proxy_enabled'] = $_POST['proxy_enabled'] ?? '0';
    $_SESSION['installer_data']['proxy_type'] = $_POST['proxy_type'] ?? 'http';
    $_SESSION['installer_data']['proxy_host'] = trim($_POST['proxy_host'] ?? '');
    $_SESSION['installer_data']['proxy_port'] = (int)($_POST['proxy_port'] ?? 0);
    $_SESSION['installer_data']['proxy_username'] = trim($_POST['proxy_username'] ?? '');
    $_SESSION['installer_data']['proxy_password'] = $_POST['proxy_password'] ?? '';
    $_SESSION['installer_data']['proxy_dns'] = trim($_POST['proxy_dns'] ?? '');

    if (isset($_POST['test_proxy'])) {
        $proxyHost = $_SESSION['installer_data']['proxy_host'];
        $proxyPort = $_SESSION['installer_data']['proxy_port'];
        $proxyType = $_SESSION['installer_data']['proxy_type'];
        $proxyUser = $_SESSION['installer_data']['proxy_username'];
        $proxyPass = $_SESSION['installer_data']['proxy_password'];
        $proxyDns = $_SESSION['installer_data']['proxy_dns'];

        if (empty($proxyHost) || $proxyPort <= 0) {
            $proxyTestResult = ['success' => false, 'error' => 'آدرس و پورت پروکسی را وارد کنید'];
        } else {
            $_SESSION['installer_data']['proxy_enabled'] = '1';

            $ch = curl_init('https://api.telegram.org');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_PROXY => $proxyHost,
                CURLOPT_PROXYPORT => $proxyPort,
                CURLOPT_PROXYTYPE => (['http' => CURLPROXY_HTTP, 'https' => CURLPROXY_HTTPS, 'socks4' => CURLPROXY_SOCKS4, 'socks5' => CURLPROXY_SOCKS5])[$proxyType] ?? CURLPROXY_HTTP,
            ]);
            if (!empty($proxyUser)) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyUser . (!empty($proxyPass) ? ":{$proxyPass}" : ''));
            }
            if (!empty($proxyDns)) {
                curl_setopt($ch, CURLOPT_DNS_SERVERS, $proxyDns);
            }
            $res = curl_exec($ch);
            $curlErr = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($curlErr) {
                $proxyTestResult = ['success' => false, 'error' => "خطا: {$curlErr}"];
            } elseif ($httpCode >= 200 && $httpCode < 400) {
                $proxyTestResult = ['success' => true, 'message' => 'پروکسی با موفقیت کار می‌کند'];
            } else {
                $proxyTestResult = ['success' => false, 'error' => "کد پاسخ: {$httpCode}"];
            }
        }
    } elseif (isset($_POST['test_bot'])) {
        $telegramEnabled = $_SESSION['installer_data']['telegram_enabled'];
        $baleEnabled = $_SESSION['installer_data']['bale_enabled'];
        
        if ($telegramEnabled !== '1' && $baleEnabled !== '1') {
            $error = 'حداقل یکی از پلتفرم‌ها را انتخاب کنید';
        } else {
            $allOk = true;
            
            if ($telegramEnabled === '1') {
                $token = trim($_POST['bot_token'] ?? '');
                $adminId = trim($_POST['admin_id'] ?? '');
                $_SESSION['installer_data']['bot_token'] = $token;
                $_SESSION['installer_data']['admin_id'] = $adminId;
                
                if (empty($token)) {
                    $error = 'توکن ربات تلگرام الزامی است';
                    $allOk = false;
                } elseif (empty($adminId) || !is_numeric($adminId)) {
                    $error = 'آیدی عددی ادمین تلگرام معتبر نیست';
                    $allOk = false;
                } else {
                    $result = $installer->testBotToken($token);
                    if ($result['success']) {
                        $botInfo = $result['bot'];
                    } else {
                        $error = 'تلگرام: ' . $result['error'];
                        $allOk = false;
                    }
                }
            }
            
            if ($allOk && $baleEnabled === '1') {
                $baleToken = trim($_POST['bale_bot_token'] ?? '');
                $baleAdminId = trim($_POST['bale_admin_id'] ?? '');
                $_SESSION['installer_data']['bale_bot_token'] = $baleToken;
                $_SESSION['installer_data']['bale_admin_id'] = $baleAdminId;
                
                if (empty($baleToken)) {
                    $error = 'توکن ربات بله الزامی است';
                    $allOk = false;
                } elseif (empty($baleAdminId) || !is_numeric($baleAdminId)) {
                    $error = 'آیدی عددی ادمین بله معتبر نیست';
                    $allOk = false;
                } else {
                    $result = $installer->testBaleBotToken($baleToken);
                    if ($result['success']) {
                        $baleBotInfo = $result['bot'];
                    } else {
                        $error = 'بله: ' . $result['error'];
                        $allOk = false;
                    }
                }
            }
            
            if ($allOk) {
                $success = true;
                $_SESSION['bot_tested'] = true;
            }
        }
    }
}

$data = $_SESSION['installer_data'] ?? [];
?>

<h2 class="text-2xl font-bold text-white mb-2">🤖 تنظیمات ربات</h2>
<p class="text-white/60 mb-6 text-sm">پلتفرم مورد نظر را انتخاب کنید و توکن ربات و آیدی عددی ادمین را وارد کنید.</p>

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
    <div class="space-y-3">
        <?php if ($botInfo): ?>
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-green-500/30 rounded-full flex items-center justify-center text-2xl">🤖</div>
            <div>
                <p class="text-green-300 font-bold">ربات تلگرام با موفقیت شناسایی شد!</p>
                <p class="text-white/70 text-sm"><?= htmlspecialchars($botInfo['first_name']) ?> (@<?= htmlspecialchars($botInfo['username']) ?>)</p>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($baleBotInfo): ?>
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-green-500/30 rounded-full flex items-center justify-center text-2xl">🤖</div>
            <div>
                <p class="text-green-300 font-bold">ربات بله با موفقیت شناسایی شد!</p>
                <p class="text-white/70 text-sm"><?= htmlspecialchars($baleBotInfo['first_name']) ?> (@<?= htmlspecialchars($baleBotInfo['username']) ?>)</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<a href="?step=4" class="block w-full bg-gradient-to-r from-purple-500 to-blue-500 text-white text-center py-3 rounded-lg font-bold hover:opacity-90 transition transform hover:scale-[1.02]">
    مرحله بعد: ساخت حساب ادمین ←
</a>

<?php else: ?>

<form method="POST" class="space-y-4">
    <!-- ═══ انتخاب پلتفرم ═══ -->
    <div class="bg-white/5 rounded-lg p-4 border border-white/10">
        <h3 class="text-white font-bold mb-3 text-sm">🌐 پلتفرم‌های مورد نظر را انتخاب کنید</h3>
        
        <div class="space-y-3">
            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-lg border <?= ($data['telegram_enabled'] ?? '1') === '1' ? 'border-purple-500/50 bg-purple-500/10' : 'border-white/10 bg-white/5' ?>">
                <input type="checkbox" name="telegram_enabled" value="1" 
                       <?= (!isset($data['telegram_enabled']) || $data['telegram_enabled'] === '1') ? 'checked' : '' ?>
                       class="w-4 h-4 rounded border-white/20 bg-white/10 text-purple-500 focus:ring-purple-500"
                       onchange="toggleTelegramFields()">
                <div>
                    <span class="text-white font-medium">🤖 تلگرام</span>
                    <p class="text-white/50 text-xs">ربات تلگرام با Bot API استاندارد</p>
                </div>
            </label>
            
            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-lg border <?= ($data['bale_enabled'] ?? '') === '1' ? 'border-purple-500/50 bg-purple-500/10' : 'border-white/10 bg-white/5' ?>">
                <input type="checkbox" name="bale_enabled" value="1" 
                       <?= ($data['bale_enabled'] ?? '') === '1' ? 'checked' : '' ?>
                       class="w-4 h-4 rounded border-white/20 bg-white/10 text-purple-500 focus:ring-purple-500"
                       onchange="toggleBaleFields()">
                <div>
                    <span class="text-white font-medium">💬 بله</span>
                    <p class="text-white/50 text-xs">ربات پیام‌رسان بله (سازگار با Telegram Bot API)</p>
                </div>
            </label>
        </div>
    </div>

    <!-- ═══ فیلدهای تلگرام ═══ -->
    <div id="telegramFields">
        <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-3 mb-4">
            <p class="text-blue-300 text-sm font-medium">🤖 تنظیمات ربات تلگرام</p>
        </div>
        
        <div>
            <label class="block text-white mb-2 text-sm font-medium">
                توکن ربات تلگرام (Bot Token) <span class="text-red-400">*</span>
            </label>
            <input type="text" name="bot_token" 
                   value="<?= htmlspecialchars($data['bot_token'] ?? '') ?>" 
                   placeholder="1234567890:ABCdefGHIjklMNOpqrsTUVwxyz"
                   class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition font-mono text-sm" 
                   dir="ltr">
            <p class="text-xs text-white/50 mt-1">
                از <a href="https://t.me/BotFather" target="_blank" class="text-blue-400 hover:underline">@BotFather</a> دریافت کنید
            </p>
        </div>
        
        <div class="mt-4">
            <label class="block text-white mb-2 text-sm font-medium">
                آیدی عددی ادمین تلگرام (Admin ID) <span class="text-red-400">*</span>
            </label>
            <input type="text" name="admin_id" 
                   value="<?= htmlspecialchars($data['admin_id'] ?? '') ?>" 
                   placeholder="123456789"
                   pattern="[0-9]{6,15}"
                   class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition font-mono" 
                   dir="ltr">
            <p class="text-xs text-white/50 mt-1">
                از <a href="https://t.me/userinfobot" target="_blank" class="text-blue-400 hover:underline">@userinfobot</a> دریافت کنید
            </p>
        </div>
    </div>

    <!-- ═══ فیلدهای بله ═══ -->
    <div id="baleFields" class="<?= ($data['bale_enabled'] ?? '') === '1' ? '' : 'hidden' ?>">
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-lg p-3 mb-4">
            <p class="text-emerald-300 text-sm font-medium">💬 تنظیمات ربات بله</p>
        </div>
        
        <div>
            <label class="block text-white mb-2 text-sm font-medium">
                توکن ربات بله (Bot Token) <span class="text-red-400">*</span>
            </label>
            <input type="text" name="bale_bot_token" 
                   value="<?= htmlspecialchars($data['bale_bot_token'] ?? '') ?>" 
                   placeholder="1234567890:ABCdefGHIjklMNOpqrsTUVwxyz"
                   class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition font-mono text-sm" 
                   dir="ltr">
            <p class="text-xs text-white/50 mt-1">
                از ربات <a href="https://ble.ir/botfather" target="_blank" class="text-blue-400 hover:underline">@botfather</a> در بله دریافت کنید
            </p>
        </div>
        
        <div class="mt-4">
            <label class="block text-white mb-2 text-sm font-medium">
                آیدی عددی ادمین بله (Admin ID) <span class="text-red-400">*</span>
            </label>
            <input type="text" name="bale_admin_id" 
                   value="<?= htmlspecialchars($data['bale_admin_id'] ?? '') ?>" 
                   placeholder="123456789"
                   pattern="[0-9]{6,15}"
                   class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition font-mono" 
                   dir="ltr">
            <p class="text-xs text-white/50 mt-1">
                از <a href="https://ble.ir/userinfobot" target="_blank" class="text-blue-400 hover:underline">@userinfobot</a> در بله دریافت کنید
            </p>
        </div>
    </div>

    <!-- ═══ بخش پروکسی ═══ -->
    <div class="border-t border-white/10 pt-6 mt-6">
        <button type="button" onclick="toggleProxy()" class="flex items-center gap-2 text-white/70 hover:text-white transition text-sm mb-4">
            <span id="proxyArrow" class="transition-transform">▶</span>
            <span>🔌 تنظیمات پروکسی (برای دور زدن تحریم‌ها)</span>
        </button>

        <div id="proxySection" class="hidden space-y-4">
            <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 mb-4">
                <p class="text-blue-300 text-xs">
                    💡 اگر سرور شما در ایران است و تلگرام/بله در دسترس نیست، از این بخش استفاده کنید.
                </p>
            </div>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="proxy_enabled" value="1" 
                       <?= (!empty($data['proxy_enabled']) && $data['proxy_enabled'] === '1') ? 'checked' : '' ?>
                       class="w-4 h-4 rounded border-white/20 bg-white/10 text-purple-500 focus:ring-purple-500">
                <span class="text-white text-sm">فعال‌سازی پروکسی</span>
            </label>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-white mb-2 text-sm font-medium">نوع پروکسی</label>
                    <select name="proxy_type" class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white focus:border-purple-500 transition text-sm">
                        <option value="http" class="bg-gray-800" <?= ($data['proxy_type'] ?? 'http') === 'http' ? 'selected' : '' ?>>HTTP</option>
                        <option value="https" class="bg-gray-800" <?= ($data['proxy_type'] ?? '') === 'https' ? 'selected' : '' ?>>HTTPS</option>
                        <option value="socks5" class="bg-gray-800" <?= ($data['proxy_type'] ?? '') === 'socks5' ? 'selected' : '' ?>>SOCKS5</option>
                        <option value="socks4" class="bg-gray-800" <?= ($data['proxy_type'] ?? '') === 'socks4' ? 'selected' : '' ?>>SOCKS4</option>
                    </select>
                </div>
                <div>
                    <label class="block text-white mb-2 text-sm font-medium">DNS دلخواه (اختیاری)</label>
                    <input type="text" name="proxy_dns" 
                           value="<?= htmlspecialchars($data['proxy_dns'] ?? '') ?>"
                           placeholder="178.22.122.100,185.51.200.2"
                           class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition text-sm font-mono" 
                           dir="ltr">
                    <p class="text-xs text-white/50 mt-1">مثل Shecan, Radar, 403.online</p>
                </div>
                <div>
                    <label class="block text-white mb-2 text-sm font-medium">آدرس سرور پروکسی <span class="text-red-400">*</span></label>
                    <input type="text" name="proxy_host" 
                           value="<?= htmlspecialchars($data['proxy_host'] ?? '') ?>"
                           placeholder="127.0.0.1 یا proxy.example.com"
                           class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition text-sm font-mono" 
                           dir="ltr">
                </div>
                <div>
                    <label class="block text-white mb-2 text-sm font-medium">پورت <span class="text-red-400">*</span></label>
                    <input type="number" name="proxy_port" 
                           value="<?= htmlspecialchars($data['proxy_port'] ?? '') ?>"
                           placeholder="8080"
                           min="1" max="65535"
                           class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition text-sm font-mono" 
                           dir="ltr">
                </div>
                <div>
                    <label class="block text-white mb-2 text-sm font-medium">نام کاربری (اختیاری)</label>
                    <input type="text" name="proxy_username" 
                           value="<?= htmlspecialchars($data['proxy_username'] ?? '') ?>"
                           placeholder="در صورت نیاز"
                           class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition text-sm font-mono" 
                           dir="ltr">
                </div>
                <div>
                    <label class="block text-white mb-2 text-sm font-medium">رمز عبور (اختیاری)</label>
                    <input type="password" name="proxy_password" 
                           value="<?= htmlspecialchars($data['proxy_password'] ?? '') ?>"
                           placeholder="در صورت نیاز"
                           class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:border-purple-500 transition text-sm font-mono" 
                           dir="ltr">
                </div>
            </div>

            <button type="submit" name="test_proxy"
                    class="w-full bg-amber-600/80 text-white py-2 rounded-lg text-sm font-bold hover:bg-amber-600 transition">
                🔍 تست پروکسی
            </button>

            <?php if ($proxyTestResult): ?>
                <?php if ($proxyTestResult['success']): ?>
                <div class="bg-green-500/20 border border-green-500/50 text-green-300 px-4 py-3 rounded-lg text-sm">
                    ✅ <?= htmlspecialchars($proxyTestResult['message']) ?>
                </div>
                <?php else: ?>
                <div class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg text-sm">
                    ❌ <?= htmlspecialchars($proxyTestResult['error']) ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
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
        <p class="text-yellow-300 text-sm">⚠️ <b>هشدار امنیتی:</b> توکن ربات خود را با کسی به اشتراک نگذارید.</p>
    </div>
</div>

<script>
function toggleTelegramFields() {
    const checked = document.querySelector('[name="telegram_enabled"]').checked;
    const fields = document.getElementById('telegramFields');
    fields.style.opacity = checked ? '1' : '0.3';
    fields.querySelectorAll('input').forEach(el => el.disabled = !checked);
}

function toggleBaleFields() {
    const checked = document.querySelector('[name="bale_enabled"]').checked;
    document.getElementById('baleFields').classList.toggle('hidden', !checked);
}

function toggleProxy() {
    const section = document.getElementById('proxySection');
    const arrow = document.getElementById('proxyArrow');
    const isHidden = section.classList.contains('hidden');
    section.classList.toggle('hidden');
    arrow.textContent = isHidden ? '▼' : '▶';
}
</script>

<?php endif; ?>
