<?php
/**
 * ============================================
 * تنظیمات سیستم - Settings Page
 * ============================================
 */

define('BASE_PATH', dirname(__DIR__, 2));
define('PUBLIC_PATH', dirname(__DIR__));

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

date_default_timezone_set('Asia/Tehran');
mb_internal_encoding('UTF-8');

if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

$auth = \App\Admin\Auth::getInstance();
$auth->requireLogin('/admin/login.php');

$currentAdmin = [
    'id' => $auth->id(),
    'username' => $auth->username(),
    'name' => $auth->name(),
    'role' => $auth->role()
];

$settings = \App\Admin\Settings::getInstance();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted = $_POST['settings'] ?? [];

    if (isset($_POST['save_proxy'])) {
        $proxyFields = [
            'proxy_enabled' => ['type' => 'boolean'],
            'proxy_type' => ['type' => 'string'],
            'proxy_host' => ['type' => 'string'],
            'proxy_port' => ['type' => 'integer'],
            'proxy_username' => ['type' => 'string'],
            'proxy_password' => ['type' => 'string'],
            'proxy_dns' => ['type' => 'string'],
        ];

        $errors = [];
        foreach ($proxyFields as $key => $config) {
            $value = $posted[$key] ?? '';
            if ($key === 'proxy_enabled') {
                $value = isset($posted['proxy_enabled']) ? '1' : '0';
            }
            $result = $settings->set($key, $value, ['type' => $config['type'], 'category' => 'proxy']);
            if (!$result['success']) {
                $errors[] = $result['error'];
            }
        }

        if (empty($errors)) {
            // بروزرسانی فایل کانفیگ
            $configFile = BASE_PATH . '/config/config.php';
            if (file_exists($configFile)) {
                $configData = require $configFile;
                $configData['proxy'] = [
                    'enabled' => isset($posted['proxy_enabled']) ? true : false,
                    'type' => $posted['proxy_type'] ?? 'http',
                    'host' => $posted['proxy_host'] ?? '',
                    'port' => (int)($posted['proxy_port'] ?? 0),
                    'username' => $posted['proxy_username'] ?? '',
                    'password' => $posted['proxy_password'] ?? '',
                    'dns' => $posted['proxy_dns'] ?? '',
                ];
                file_put_contents($configFile, '<?php return ' . var_export($configData, true) . ';');
            }

            $message = 'تنظیمات پروکسی با موفقیت ذخیره شد';
            $messageType = 'success';
        } else {
            $message = 'خطا در ذخیره: ' . implode('، ', $errors);
            $messageType = 'error';
        }
    }
}

$proxyEnabled = $settings->get('proxy_enabled', '0');
$proxyType = $settings->get('proxy_type', 'http');
$proxyHost = $settings->get('proxy_host', '');
$proxyPort = $settings->get('proxy_port', '');
$proxyUsername = $settings->get('proxy_username', '');
$proxyPassword = $settings->get('proxy_password', '');
$proxyDns = $settings->get('proxy_dns', '');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تنظیمات - پنل مدیریت</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        * { font-family: 'Vazirmatn', Tahoma, sans-serif; }
        .glass { background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.15); }
        .glass-card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); }
        input, select { transition: all 0.2s; }
        input:focus, select:focus { outline: none; border-color: #667eea !important; box-shadow: 0 0 0 3px rgba(102,126,234,0.3); }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-indigo-900 min-h-screen">

<div class="flex">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white">⚙️ تنظیمات سیستم</h1>
                <p class="text-white/60 text-sm mt-1">مدیریت تنظیمات پروکسی و سایر بخش‌ها</p>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="mb-6 px-4 py-3 rounded-lg text-sm <?= $messageType === 'success' ? 'bg-green-500/20 border border-green-500/50 text-green-300' : 'bg-red-500/20 border border-red-500/50 text-red-300' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <!-- ═══ بخش پروکسی ═══ -->
        <div class="glass rounded-2xl p-6 mb-6">
            <div class="flex items-center gap-3 mb-6">
                <span class="text-2xl">🔌</span>
                <div>
                    <h2 class="text-xl font-bold text-white">تنظیمات پروکسی</h2>
                    <p class="text-white/50 text-sm">برای دور زدن تحریم‌ها و اتصال به تلگرام از طریق پروکسی</p>
                </div>
            </div>

            <form method="POST">
                <div class="glass-card rounded-xl p-5 space-y-5">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="settings[proxy_enabled]" value="1"
                               <?= $proxyEnabled === '1' ? 'checked' : '' ?>
                               class="w-5 h-5 rounded border-white/20 bg-white/10 text-purple-500 focus:ring-purple-500">
                        <span class="text-white font-medium">فعال‌سازی پروکسی</span>
                    </label>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-white/80 text-sm font-medium mb-2">نوع پروکسی</label>
                            <select name="settings[proxy_type]" class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white text-sm">
                                <option value="http" class="bg-gray-800" <?= $proxyType === 'http' ? 'selected' : '' ?>>HTTP</option>
                                <option value="https" class="bg-gray-800" <?= $proxyType === 'https' ? 'selected' : '' ?>>HTTPS</option>
                                <option value="socks5" class="bg-gray-800" <?= $proxyType === 'socks5' ? 'selected' : '' ?>>SOCKS5</option>
                                <option value="socks4" class="bg-gray-800" <?= $proxyType === 'socks4' ? 'selected' : '' ?>>SOCKS4</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-white/80 text-sm font-medium mb-2">آدرس سرور</label>
                            <input type="text" name="settings[proxy_host]" value="<?= htmlspecialchars($proxyHost) ?>"
                                   placeholder="proxy.example.com" dir="ltr"
                                   class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-white/80 text-sm font-medium mb-2">پورت</label>
                            <input type="number" name="settings[proxy_port]" value="<?= htmlspecialchars($proxyPort) ?>"
                                   placeholder="8080" min="1" max="65535" dir="ltr"
                                   class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-white/80 text-sm font-medium mb-2">نام کاربری (اختیاری)</label>
                            <input type="text" name="settings[proxy_username]" value="<?= htmlspecialchars($proxyUsername) ?>"
                                   placeholder="در صورت نیاز" dir="ltr"
                                   class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-white/80 text-sm font-medium mb-2">رمز عبور (اختیاری)</label>
                            <input type="password" name="settings[proxy_password]" value="<?= htmlspecialchars($proxyPassword) ?>"
                                   placeholder="در صورت نیاز" dir="ltr"
                                   class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-white/80 text-sm font-medium mb-2">DNS دلخواه (اختیاری)</label>
                            <input type="text" name="settings[proxy_dns]" value="<?= htmlspecialchars($proxyDns) ?>"
                                   placeholder="178.22.122.100,185.51.200.2" dir="ltr"
                                   class="w-full p-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 text-sm font-mono">
                            <p class="text-white/40 text-xs mt-1">مثل Shecan, Radar, 403.online</p>
                        </div>
                    </div>

                    <button type="submit" name="save_proxy"
                            class="bg-gradient-to-r from-purple-500 to-blue-500 text-white px-6 py-3 rounded-lg font-bold hover:opacity-90 transition">
                        💾 ذخیره تنظیمات پروکسی
                    </button>
                </div>
            </form>
        </div>

        <!-- ═══ وضعیت اتصال ═══ -->
        <div class="glass rounded-2xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-2xl">📡</span>
                <h2 class="text-xl font-bold text-white">وضعیت اتصال</h2>
            </div>
            <div class="glass-card rounded-xl p-5">
                <?php
                $proxyActive = $proxyEnabled === '1' && !empty($proxyHost);
                if ($proxyActive):
                    $ch = curl_init('https://api.telegram.org');
                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_TIMEOUT => 8,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_PROXY => $proxyHost,
                        CURLOPT_PROXYPORT => (int)$proxyPort,
                        CURLOPT_PROXYTYPE => (['http' => CURLPROXY_HTTP, 'https' => CURLPROXY_HTTPS, 'socks5' => CURLPROXY_SOCKS5, 'socks4' => CURLPROXY_SOCKS4])[$proxyType] ?? CURLPROXY_HTTP,
                    ]);
                    if (!empty($proxyUsername)) {
                        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyUsername . (!empty($proxyPassword) ? ":{$proxyPassword}" : ''));
                    }
                    if (!empty($proxyDns)) {
                        curl_setopt($ch, CURLOPT_DNS_SERVERS, $proxyDns);
                    }
                    $res = curl_exec($ch);
                    $err = curl_error($ch);
                    curl_close($ch);

                    if ($err): ?>
                        <div class="flex items-center gap-3 text-red-300">
                            <span class="text-2xl">❌</span>
                            <div>
                                <p class="font-bold">عدم اتصال به تلگرام</p>
                                <p class="text-sm text-white/60"><?= htmlspecialchars($err) ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-3 text-green-300">
                            <span class="text-2xl">✅</span>
                            <div>
                                <p class="font-bold">اتصال به تلگرام برقرار است</p>
                                <p class="text-sm text-white/60">پروکسی به درستی کار می‌کند</p>
                            </div>
                        </div>
                    <?php endif;
                else: ?>
                    <div class="flex items-center gap-3 text-white/60">
                        <span class="text-2xl">⏸️</span>
                        <div>
                            <p class="font-bold">پروکسی غیرفعال است</p>
                            <p class="text-sm">پروکسی را فعال و تنظیم کنید تا وضعیت اتصال نمایش داده شود</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>
