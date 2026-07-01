<?php
/**
 * ============================================
 * Admin Login Page
 * ============================================
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * 
 * ØµÙØ­Ù‡ ÙˆØ±ÙˆØ¯ Ø§Ø¯Ù…ÛŒÙ† Ø¨Ø§ Ø§Ù…Ù†ÛŒØª Ø¨Ø§Ù„Ø§
 * CSRF Protection, Rate Limiting, Remember Me
 */

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 1. ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ø§ÙˆÙ„ÛŒÙ‡
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

define('BASE_PATH', dirname(__DIR__, 2));
define('PUBLIC_PATH', dirname(__DIR__));

error_reporting(0);
ini_set('display_errors', 0);

date_default_timezone_set('Asia/Tehran');
mb_internal_encoding('UTF-8');

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 2. Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ Autoloader
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

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

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 3. Ø¨Ø±Ø±Ø³ÛŒ Ù„Ø§Ú¯ÛŒÙ† Ø¨ÙˆØ¯Ù†
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

try {
    $auth = \App\Admin\Auth::getInstance();
    
    // Ø§Ú¯Ø± Ù‚Ø¨Ù„Ø§Ù‹ Ù„Ø§Ú¯ÛŒÙ† Ú©Ø±Ø¯Ù‡ØŒ Ù‡Ø¯Ø§ÛŒØª Ø¨Ù‡ Ø¯Ø§Ø´Ø¨ÙˆØ±Ø¯
    if ($auth->check()) {
        header('Location: /admin/');
        exit;
    }
    
} catch (Exception $e) {
    // Ù†Ø§Ø¯ÛŒØ¯Ù‡ Ø¨Ú¯ÛŒØ±
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 4. Ù¾Ø±Ø¯Ø§Ø²Ø´ ÙØ±Ù… ÙˆØ±ÙˆØ¯
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Verification
    if (!isset($_POST['_token']) || !\App\Helpers\Security::verifyCsrfToken($_POST['_token'], $_SESSION['_csrf_token'] ?? '')) {
        $error = 'Ø®Ø·Ø§ÛŒ Ø§Ù…Ù†ÛŒØªÛŒ: ØªÙˆÚ©Ù† CSRF Ù†Ø§Ù…Ø¹ØªØ¨Ø± Ø§Ø³Øª';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Ø§Ø¹ØªØ¨Ø§Ø±Ø³Ù†Ø¬ÛŒ
        if (empty($username) || empty($password)) {
            $error = 'Ù†Ø§Ù… Ú©Ø§Ø±Ø¨Ø±ÛŒ Ùˆ Ø±Ù…Ø² Ø¹Ø¨ÙˆØ± Ø§Ù„Ø²Ø§Ù…ÛŒ Ø§Ø³Øª';
        } else {
            try {
                $result = $auth->attempt($username, $password, $remember);
                
                if ($result['success']) {
                    // Ù‡Ø¯Ø§ÛŒØª Ø¨Ù‡ Ø¯Ø§Ø´Ø¨ÙˆØ±Ø¯ ÛŒØ§ URL Ù…ÙˆØ±Ø¯ Ù†Ø¸Ø±
                    $redirectUrl = $_SESSION['intended_url'] ?? '/admin/';
                    unset($_SESSION['intended_url']);
                    
                    header("Location: {$redirectUrl}");
                    exit;
                } else {
                    $error = $result['error'];
                    
                    // Ø§Ú¯Ø± Rate Limit ÙØ¹Ø§Ù„ Ø´Ø¯Ù‡
                    if (isset($result['retry_after'])) {
                        $error .= " (Ù„Ø·ÙØ§Ù‹ Ø¨Ø¹Ø¯ Ø§Ø² {$result['retry_after']} Ø«Ø§Ù†ÛŒÙ‡ Ø¯ÙˆØ¨Ø§Ø±Ù‡ ØªÙ„Ø§Ø´ Ú©Ù†ÛŒØ¯)";
                    }
                }
                
            } catch (Exception $e) {
                $error = 'Ø®Ø·Ø§ Ø¯Ø± Ù¾Ø±Ø¯Ø§Ø²Ø´ Ø¯Ø±Ø®ÙˆØ§Ø³Øª. Ù„Ø·ÙØ§Ù‹ Ø¯ÙˆØ¨Ø§Ø±Ù‡ ØªÙ„Ø§Ø´ Ú©Ù†ÛŒØ¯.';
                
                // Ù„Ø§Ú¯ Ø®Ø·Ø§
                try {
                    $logger = \App\Core\Logger::getInstance();
                    $logger->error('Login error', [
                        'error' => $e->getMessage(),
                        'username' => $username,
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                    ]);
                } catch (Exception $logError) {
                    // Ù†Ø§Ø¯ÛŒØ¯Ù‡ Ø¨Ú¯ÛŒØ±
                }
            }
        }
    }
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 5. ØªÙˆÙ„ÛŒØ¯ CSRF Token
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

try {
    $session = \App\Core\Session::getInstance();
    $session->start();
    $csrfToken = \App\Helpers\Security::generateCsrfToken();
    $_SESSION['_csrf_token'] = $csrfToken;
} catch (Exception $e) {
    $csrfToken = '';
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 6. Ø¯Ø±ÛŒØ§ÙØª Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§ÛŒ URL
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'session_expired':
            $error = 'Session Ø´Ù…Ø§ Ù…Ù†Ù‚Ø¶ÛŒ Ø´Ø¯Ù‡ Ø§Ø³Øª. Ù„Ø·ÙØ§Ù‹ Ø¯ÙˆØ¨Ø§Ø±Ù‡ ÙˆØ§Ø±Ø¯ Ø´ÙˆÛŒØ¯.';
            break;
        case 'unauthorized':
            $error = 'Ø´Ù…Ø§ Ø¯Ø³ØªØ±Ø³ÛŒ Ø¨Ù‡ Ø§ÛŒÙ† ØµÙØ­Ù‡ Ø±Ø§ Ù†Ø¯Ø§Ø±ÛŒØ¯.';
            break;
        case 'logout':
            $success = 'Ø´Ù…Ø§ Ø¨Ø§ Ù…ÙˆÙÙ‚ÛŒØª Ø®Ø§Ø±Ø¬ Ø´Ø¯ÛŒØ¯.';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ÙˆØ±ÙˆØ¯ Ø¨Ù‡ Ù¾Ù†Ù„ Ù…Ø¯ÛŒØ±ÛŒØª</title>
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
    
    <!-- â•â•â• Logo & Title â•â•â• -->
    <div class="text-center mb-8">
        <div class="inline-block bg-white/10 rounded-full p-4 mb-4">
            <span class="text-5xl">ðŸŽ¬</span>
        </div>
        <h1 class="text-3xl font-bold text-white mb-2">Ù¾Ù†Ù„ Ù…Ø¯ÛŒØ±ÛŒØª</h1>
        <p class="text-white/60 text-sm">Ø¨Ø±Ø§ÛŒ ÙˆØ±ÙˆØ¯ØŒ Ø§Ø·Ù„Ø§Ø¹Ø§Øª Ø®ÙˆØ¯ Ø±Ø§ ÙˆØ§Ø±Ø¯ Ú©Ù†ÛŒØ¯</p>
    </div>
    
    <!-- â•â•â• Login Form â•â•â• -->
    <div class="glass rounded-2xl p-8 shadow-2xl">
        
        <!-- Flash Messages -->
        <?php if ($error): ?>
        <div class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg mb-4 flex items-center gap-2 animate-shake">
            <span class="text-xl">âŒ</span>
            <span class="text-sm"><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="bg-green-500/20 border border-green-500/50 text-green-300 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
            <span class="text-xl">âœ…</span>
            <span class="text-sm"><?= htmlspecialchars($success) ?></span>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="space-y-5">
            
            <!-- CSRF Token -->
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
            
            <!-- Username -->
            <div>
                <label class="block text-white text-sm font-medium mb-2">
                    Ù†Ø§Ù… Ú©Ø§Ø±Ø¨Ø±ÛŒ <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40">ðŸ‘¤</span>
                    <input 
                        type="text" 
                        name="username" 
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        required 
                        autofocus
                        autocomplete="username"
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-3 pr-10 pl-4 text-white placeholder-white/40 focus:border-purple-500 transition"
                        placeholder="Ù†Ø§Ù… Ú©Ø§Ø±Ø¨Ø±ÛŒ Ø®ÙˆØ¯ Ø±Ø§ ÙˆØ§Ø±Ø¯ Ú©Ù†ÛŒØ¯"
                    >
                </div>
            </div>
            
            <!-- Password -->
            <div>
                <label class="block text-white text-sm font-medium mb-2">
                    Ø±Ù…Ø² Ø¹Ø¨ÙˆØ± <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40">ðŸ”’</span>
                    <input 
                        type="password" 
                        name="password" 
                        id="password"
                        required
                        autocomplete="current-password"
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-3 pr-10 pl-10 text-white placeholder-white/40 focus:border-purple-500 transition"
                        placeholder="Ø±Ù…Ø² Ø¹Ø¨ÙˆØ± Ø®ÙˆØ¯ Ø±Ø§ ÙˆØ§Ø±Ø¯ Ú©Ù†ÛŒØ¯"
                    >
                    <button 
                        type="button" 
                        onclick="togglePassword()"
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/60 transition"
                    >
                        ðŸ‘ï¸
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
                    <span class="text-white/70 text-sm">Ù…Ø±Ø§ Ø¨Ù‡ Ø®Ø§Ø·Ø± Ø¨Ø³Ù¾Ø§Ø±</span>
                </label>
            </div>
            
            <!-- Submit Button -->
            <button 
                type="submit" 
                id="submitBtn"
                class="w-full bg-gradient-to-r from-purple-500 to-blue-500 text-white font-bold py-3 rounded-lg hover:opacity-90 transition transform hover:scale-[1.02] active:scale-[0.98]"
            >
                ÙˆØ±ÙˆØ¯ Ø¨Ù‡ Ù¾Ù†Ù„ Ù…Ø¯ÛŒØ±ÛŒØª
            </button>
            
        </form>
        
        <!-- Help Text -->
        <div class="mt-6 pt-6 border-t border-white/10">
            <p class="text-white/50 text-xs text-center">
                ðŸ”’ Ø§ØªØµØ§Ù„ Ø§Ù…Ù† Ø¨Ø§ Ø±Ù…Ø²Ù†Ú¯Ø§Ø±ÛŒ SSL<br>
                Ø¯Ø± ØµÙˆØ±Øª ÙØ±Ø§Ù…ÙˆØ´ÛŒ Ø±Ù…Ø² Ø¹Ø¨ÙˆØ±ØŒ Ø¨Ø§ Ù¾Ø´ØªÛŒØ¨Ø§Ù†ÛŒ ØªÙ…Ø§Ø³ Ø¨Ú¯ÛŒØ±ÛŒØ¯
            </p>
        </div>
        
    </div>
    
    <!-- Footer -->
    <div class="text-center mt-6 text-white/40 text-xs">
        <p>Youtuber Bot v2.1.0 | Ø³Ø§Ø®ØªÙ‡ Ø´Ø¯Ù‡ Ø¨Ø§ â¤ï¸</p>
    </div>
    
</div>

<!-- â•â•â• Scripts â•â•â• -->
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
        alert('Ù„Ø·ÙØ§Ù‹ ØªÙ…Ø§Ù… ÙÛŒÙ„Ø¯Ù‡Ø§ Ø±Ø§ Ù¾Ø± Ú©Ù†ÛŒØ¯');
        return false;
    }
    
    // Disable button to prevent double submit
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="inline-block animate-spin">â³</span> Ø¯Ø± Ø­Ø§Ù„ ÙˆØ±ÙˆØ¯...';
});

// Auto-focus on error
<?php if ($error): ?>
document.querySelector('[name="username"]').focus();
<?php endif; ?>
</script>

</body>
</html>