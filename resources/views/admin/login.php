<?php
/**
 * ============================================
 * Admin Login Page - ØµÙØ­Ù‡ ÙˆØ±ÙˆØ¯ Ø§Ø¯Ù…ÛŒÙ†
 * ============================================
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * 
 * ØµÙØ­Ù‡ ÙˆØ±ÙˆØ¯ Ù…Ø³ØªÙ‚Ù„ (Ø¨Ø¯ÙˆÙ† layout Ø§ØµÙ„ÛŒ)
 * Ø¨Ø§ Ø·Ø±Ø§Ø­ÛŒ Ù…Ø¯Ø±Ù† Ùˆ Ø§Ù…Ù†ÛŒØª Ø¨Ø§Ù„Ø§
 */

// Ø¯Ø±ÛŒØ§ÙØª Ø®Ø·Ø§ Ùˆ Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§
$error = $error ?? '';
$success = $success ?? '';

// CSRF Token
$csrfToken = $_SESSION['_csrf_token'] ?? '';

// Ø§Ø·Ù„Ø§Ø¹Ø§Øª Ù‚Ø¨Ù„ÛŒ (Ø¨Ø±Ø§ÛŒ Ù¾Ø± Ú©Ø±Ø¯Ù† Ù…Ø¬Ø¯Ø¯ ÙØ±Ù…)
$oldUsername = $_SESSION['old_username'] ?? '';
unset($_SESSION['old_username']);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">
    <title>ÙˆØ±ÙˆØ¯ Ø¨Ù‡ Ù¾Ù†Ù„ Ù…Ø¯ÛŒØ±ÛŒØª</title>
    
    <!-- â•â•â• Fonts â•â•â• -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    
    <!-- â•â•â• TailwindCSS â•â•â• -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- â•â•â• Font Awesome â•â•â• -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { font-family: 'Vazirmatn', Tahoma, sans-serif; }
        
        /* Animated Background */
        .animated-bg {
            background: linear-gradient(-45deg, #1e293b, #581c87, #312e81, #1e1b4b);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Glass Effect */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Input Focus */
        input:focus {
            outline: none;
            border-color: #8b5cf6 !important;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
        }
        
        /* Button Loading */
        .btn-loading {
            position: relative;
            pointer-events: none;
        }
        
        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin: -10px 0 0 -10px;
            border: 3px solid transparent;
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Shake Animation */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .shake {
            animation: shake 0.5s;
        }
        
        /* Floating Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .float {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>

<body class="animated-bg min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        
        <!-- â•â•â• Logo & Title â•â•â• -->
        <div class="text-center mb-8">
            <div class="inline-block float">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-4xl shadow-2xl mb-4">
                    ðŸŽ¬
                </div>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Ù¾Ù†Ù„ Ù…Ø¯ÛŒØ±ÛŒØª</h1>
            <p class="text-white/60 text-sm">Ø¨Ø±Ø§ÛŒ ÙˆØ±ÙˆØ¯ØŒ Ø§Ø·Ù„Ø§Ø¹Ø§Øª Ø®ÙˆØ¯ Ø±Ø§ ÙˆØ§Ø±Ø¯ Ú©Ù†ÛŒØ¯</p>
        </div>
        
        <!-- â•â•â• Login Form â•â•â• -->
        <div class="glass rounded-2xl p-8 shadow-2xl">
            
            <!-- Flash Messages -->
            <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg mb-4 flex items-center gap-2 shake">
                <i class="fas fa-exclamation-circle text-xl"></i>
                <span class="text-sm"><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="bg-green-500/20 border border-green-500/50 text-green-300 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
                <i class="fas fa-check-circle text-xl"></i>
                <span class="text-sm"><?= htmlspecialchars($success) ?></span>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="/admin/login.php" id="loginForm" class="space-y-5">
                
                <!-- CSRF Token -->
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                
                <!-- Username -->
                <div>
                    <label class="block text-white/80 text-sm font-medium mb-2">
                        <i class="fas fa-user ml-1"></i>
                        <span>Ù†Ø§Ù… Ú©Ø§Ø±Ø¨Ø±ÛŒ</span>
                        <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="text" 
                            name="username" 
                            id="username"
                            value="<?= htmlspecialchars($oldUsername) ?>"
                            required 
                            autofocus
                            autocomplete="username"
                            class="w-full bg-white/10 border border-white/20 rounded-lg py-3 px-4 pr-11 text-white placeholder-white/40 transition"
                            placeholder="Ù†Ø§Ù… Ú©Ø§Ø±Ø¨Ø±ÛŒ Ø®ÙˆØ¯ Ø±Ø§ ÙˆØ§Ø±Ø¯ Ú©Ù†ÛŒØ¯"
                        >
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40">
                            <i class="fas fa-user"></i>
                        </span>
                    </div>
                </div>
                
                <!-- Password -->
                <div>
                    <label class="block text-white/80 text-sm font-medium mb-2">
                        <i class="fas fa-lock ml-1"></i>
                        <span>Ø±Ù…Ø² Ø¹Ø¨ÙˆØ±</span>
                        <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            required
                            autocomplete="current-password"
                            class="w-full bg-white/10 border border-white/20 rounded-lg py-3 px-4 pr-11 pl-11 text-white placeholder-white/40 transition"
                            placeholder="Ø±Ù…Ø² Ø¹Ø¨ÙˆØ± Ø®ÙˆØ¯ Ø±Ø§ ÙˆØ§Ø±Ø¯ Ú©Ù†ÛŒØ¯"
                        >
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40">
                            <i class="fas fa-lock"></i>
                        </span>
                        <button 
                            type="button" 
                            onclick="togglePassword()"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/60 transition"
                            title="Ù†Ù…Ø§ÛŒØ´/Ù…Ø®ÙÛŒ Ø±Ù…Ø² Ø¹Ø¨ÙˆØ±"
                        >
                            <i class="fas fa-eye" id="passwordToggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            id="remember"
                            class="w-4 h-4 rounded border-white/20 bg-white/10 text-purple-500 focus:ring-purple-500"
                        >
                        <span class="text-white/70 text-sm">Ù…Ø±Ø§ Ø¨Ù‡ Ø®Ø§Ø·Ø± Ø¨Ø³Ù¾Ø§Ø±</span>
                    </label>
                    
                    <a 
                        href="/admin/forgot-password.php" 
                        class="text-purple-400 text-sm hover:text-purple-300 transition"
                    >
                        ÙØ±Ø§Ù…ÙˆØ´ÛŒ Ø±Ù…Ø²ØŸ
                    </a>
                </div>
                
                <!-- Submit Button -->
                <button 
                    type="submit" 
                    id="submitBtn"
                    class="w-full bg-gradient-to-r from-purple-500 to-blue-500 text-white font-bold py-3 rounded-lg hover:opacity-90 transition transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2"
                >
                    <i class="fas fa-sign-in-alt"></i>
                    <span>ÙˆØ±ÙˆØ¯ Ø¨Ù‡ Ù¾Ù†Ù„</span>
                </button>
                
            </form>
            
            <!-- Help Text -->
            <div class="mt-6 pt-6 border-t border-white/10">
                <div class="flex items-center justify-center gap-2 text-white/50 text-xs">
                    <i class="fas fa-shield-alt"></i>
                    <span>Ø§ØªØµØ§Ù„ Ø§Ù…Ù† Ø¨Ø§ Ø±Ù…Ø²Ù†Ú¯Ø§Ø±ÛŒ SSL</span>
                </div>
                <p class="text-white/40 text-xs text-center mt-2">
                    Ø¯Ø± ØµÙˆØ±Øª ÙØ±Ø§Ù…ÙˆØ´ÛŒ Ø±Ù…Ø² Ø¹Ø¨ÙˆØ±ØŒ Ø¨Ø§ Ù¾Ø´ØªÛŒØ¨Ø§Ù†ÛŒ ØªÙ…Ø§Ø³ Ø¨Ú¯ÛŒØ±ÛŒØ¯
                </p>
            </div>
            
        </div>
        
        <!-- â•â•â• Footer â•â•â• -->
        <div class="text-center mt-6 text-white/40 text-xs">
            <p>Youtuber Bot v2.1.0 | Ø³Ø§Ø®ØªÙ‡ Ø´Ø¯Ù‡ Ø¨Ø§ â¤ï¸</p>
        </div>
        
    </div>
    
    <!-- â•â•â• JavaScript â•â•â• -->
    <script>
    // â•â•â• Toggle Password Visibility â•â•â•
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('passwordToggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
    
    // â•â•â• Form Validation â•â•â•
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        
        if (!username || !password) {
            e.preventDefault();
            showToast('Ù„Ø·ÙØ§Ù‹ ØªÙ…Ø§Ù… ÙÛŒÙ„Ø¯Ù‡Ø§ Ø±Ø§ Ù¾Ø± Ú©Ù†ÛŒØ¯', 'warning');
            return false;
        }
        
        // Disable button and show loading
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.classList.add('btn-loading');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>Ø¯Ø± Ø­Ø§Ù„ ÙˆØ±ÙˆØ¯...</span>';
        
        // Re-enable after 5 seconds (in case of error)
        setTimeout(() => {
            if (submitBtn.classList.contains('btn-loading')) {
                submitBtn.classList.remove('btn-loading');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i><span>ÙˆØ±ÙˆØ¯ Ø¨Ù‡ Ù¾Ù†Ù„</span>';
            }
        }, 5000);
    });
    
    // â•â•â• Toast Notification â•â•â•
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 left-1/2 -translate-x-1/2 z-50 px-6 py-3 rounded-lg shadow-lg text-white text-sm flex items-center gap-2 animate-slideIn`;
        
        const colors = {
            success: 'bg-green-500/90',
            error: 'bg-red-500/90',
            warning: 'bg-yellow-500/90',
            info: 'bg-blue-500/90'
        };
        
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        
        toast.className += ` ${colors[type]}`;
        toast.innerHTML = `
            <i class="fas ${icons[type]}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideIn 0.3s ease-out reverse';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // â•â•â• Auto-focus on error â•â•â•
    <?php if ($error): ?>
    document.getElementById('username').focus();
    <?php endif; ?>
    
    // â•â•â• Keyboard Shortcuts â•â•â•
    document.addEventListener('keydown', function(e) {
        // Ctrl+Enter to submit
        if (e.ctrlKey && e.key === 'Enter') {
            document.getElementById('loginForm').submit();
        }
    });
    </script>
    
</body>
</html>