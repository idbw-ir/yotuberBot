<?php
/**
 * ============================================
 * Admin Login Page
 * ============================================
 * نسخه: 2.0.0
 * 
 * صفحه ورود ادمین با امنیت بالا
 * CSRF Protection, Rate Limiting, Remember Me
 */

// ──────────────────────────────────────
// 1. تنظیمات اولیه
// ──────────────────────────────────────

define('BASE_PATH', dirname(__DIR__, 2));
define('PUBLIC_PATH', dirname(__DIR__));

error_reporting(0);
ini_set('display_errors', 0);

date_default_timezone_set('Asia/Tehran');
mb_internal_encoding('UTF-8');

// ──────────────────────────────────────
// 2. بارگذاری Autoloader
// ──────────────────────────────────────

if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// ──────────────────────────────────────
// 3. شروع Session
// ──────────────────────────────────────

try {
    $session = \App\Core\Session::getInstance();
    $session->start();
    $csrfToken = $session->getCsrfToken();
    $_SESSION['_csrf_token'] = $csrfToken;
} catch (Exception $e) {
    $csrfToken = '';
}

// ──────────────────────────────────────
// 4. بررسی لاگین بودن
// ──────────────────────────────────────

$auth = null;
try {
    $auth = \App\Admin\Auth::getInstance();
    
    // اگر قبلاً لاگین کرده، هدایت به داشبورد
    if ($auth->check()) {
        header('Location: /admin/');
        exit;
    }
    
} catch (Exception $e) {
    // اگر دیتابیس در دسترس نیست، خطا را ذخیره کن
    $error = 'سیستم به دیتابیس متصل نیست. لطفاً تنظیمات دیتابیس را بررسی کنید.';
}

// ──────────────────────────────────────
// 5. پردازش فرم ورود
// ──────────────────────────────────────

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Verification
    if (!isset($_POST['_token']) || !\App\Helpers\Security::verifyCsrfToken($_POST['_token'], $_SESSION['_csrf_token'] ?? '')) {
        $error = 'خطای امنیتی: توکن CSRF نامعتبر است';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // اعتبارسنجی
        if (empty($username) || empty($password)) {
            $error = 'نام کاربری و رمز عبور الزامی است';
        } else {
            if (!$auth) {
                $error = 'سیستم به دیتابیس متصل نیست. لطفاً تنظیمات دیتابیس را بررسی کنید.';
            } else {
                try {
                    $result = $auth->attempt($username, $password, $remember);

                    if ($result['success']) {
                        $redirectUrl = $_SESSION['intended_url'] ?? '/admin/';
                        unset($_SESSION['intended_url']);
                        header("Location: {$redirectUrl}");
                        exit;
                    } else {
                        $error = $result['error'];
                        if (isset($result['retry_after'])) {
                            $error .= " (لطفاً بعد از {$result['retry_after']} ثانیه دوباره تلاش کنید)";
                        }
                    }
                } catch (Exception $e) {
                    $error = 'خطا در پردازش درخواست. لطفاً دوباره تلاش کنید.';
                    try {
                        $logger = \App\Core\Logger::getInstance();
                        $logger->error('Login error', [
                            'error' => $e->getMessage(),
                            'username' => $username,
                            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                        ]);
                    } catch (Exception $logError) {}
                }
            }
        }
    }
}

// ──────────────────────────────────────
// 6. دریافت پیام‌های URL
// ──────────────────────────────────────

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'session_expired':
            $error = 'Session شما منقضی شده است. لطفاً دوباره وارد شوید.';
            break;
        case 'unauthorized':
            $error = 'شما دسترسی به این صفحه را ندارید.';
            break;
        case 'logout':
            $success = 'شما با موفقیت خارج شدید.';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به پنل مدیریت</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        * { font-family: 'Vazirmatn', Tahoma, sans-serif; }
        .glass { background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.15); }
        input:focus { outline: none; border-color: #667eea !important; box-shadow: 0 0 0 3px rgba(102,126,234,0.3); }
        .animate-shake { animation: shake 0.5s; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-indigo-900 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">
    
    <!-- ═══ Logo & Title ═══ -->
    <div class="text-center mb-8">
        <div class="inline-block bg-white/10 rounded-full p-4 mb-4">
            <span class="text-5xl">🎬</span>
        </div>
        <h1 class="text-3xl font-bold text-white mb-2">پنل مدیریت</h1>
        <p class="text-white/60 text-sm">برای ورود، اطلاعات خود را وارد کنید</p>
    </div>
    
    <!-- ═══ Login Form ═══ -->
    <div class="glass rounded-2xl p-8 shadow-2xl">
        
        <!-- Flash Messages -->
        <?php if ($error): ?>
        <div class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg mb-4 flex items-center gap-2 animate-shake">
            <span class="text-xl">❌</span>
            <span class="text-sm"><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="bg-green-500/20 border border-green-500/50 text-green-300 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
            <span class="text-xl">✅</span>
            <span class="text-sm"><?= htmlspecialchars($success) ?></span>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="space-y-5">
            
            <!-- CSRF Token -->
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
            
            <!-- Username -->
            <div>
                <label class="block text-white text-sm font-medium mb-2">
                    نام کاربری <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40">👤</span>
                    <input 
                        type="text" 
                        name="username" 
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        required 
                        autofocus
                        autocomplete="username"
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-3 pr-10 pl-4 text-white placeholder-white/40 focus:border-purple-500 transition"
                        placeholder="نام کاربری خود را وارد کنید"
                    >
                </div>
            </div>
            
            <!-- Password -->
            <div>
                <label class="block text-white text-sm font-medium mb-2">
                    رمز عبور <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40">🔒</span>
                    <input 
                        type="password" 
                        name="password" 
                        id="password"
                        required
                        autocomplete="current-password"
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-3 pr-10 pl-10 text-white placeholder-white/40 focus:border-purple-500 transition"
                        placeholder="رمز عبور خود را وارد کنید"
                    >
                    <button 
                        type="button" 
                        onclick="togglePassword()"
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/60 transition"
                    >
                        👁️
                    </button>
                </div>
            </div>
            
            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="remember" 
                        class="w-4 h-4 rounded border-white/20 bg-white/10 text-purple-500 focus:ring-purple-500"
                    >
                    <span class="text-white/70 text-sm">مرا به خاطر بسپار</span>
                </label>
            </div>
            
            <!-- Submit Button -->
            <button 
                type="submit" 
                id="submitBtn"
                class="w-full bg-gradient-to-r from-purple-500 to-blue-500 text-white font-bold py-3 rounded-lg hover:opacity-90 transition transform hover:scale-[1.02] active:scale-[0.98]"
            >
                ورود به پنل مدیریت
            </button>
            
        </form>
        
        <!-- Help Text -->
        <div class="mt-6 pt-6 border-t border-white/10">
            <p class="text-white/50 text-xs text-center">
                🔒 اتصال امن با رمزنگاری SSL<br>
                در صورت فراموشی رمز عبور، با پشتیبانی تماس بگیرید
            </p>
        </div>
        
    </div>
    
    <!-- Footer -->
    <div class="text-center mt-6 text-white/40 text-xs">
        <p>Youtuber Bot v2.1.2 | ساخته شده با ❤️</p>
    </div>
    
</div>

<!-- ═══ Scripts ═══ -->
<script>
// Toggle Password Visibility
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
}

// Form Validation
document.querySelector('form').addEventListener('submit', function(e) {
    const username = document.querySelector('[name="username"]').value.trim();
    const password = document.querySelector('[name="password"]').value;
    
    if (!username || !password) {
        e.preventDefault();
        alert('لطفاً تمام فیلدها را پر کنید');
        return false;
    }
    
    // Disable button to prevent double submit
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="inline-block animate-spin">⏳</span> در حال ورود...';
});

// Auto-focus on error
<?php if ($error): ?>
document.querySelector('[name="username"]').focus();
<?php endif; ?>
</script>

</body>
</html>