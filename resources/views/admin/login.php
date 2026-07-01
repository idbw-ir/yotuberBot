<?php
/**
 * ============================================
 * Admin Login Page - صفحه ورود ادمین
 * ============================================
 * نسخه: 2.0.0
 * 
 * صفحه ورود مستقل (بدون layout اصلی)
 * با طراحی مدرن و امنیت بالا
 */

// دریافت خطا و پیام‌ها
$error = $error ?? '';
$success = $success ?? '';

// CSRF Token
$csrfToken = $_SESSION['_csrf_token'] ?? '';

// اطلاعات قبلی (برای پر کردن مجدد فرم)
$oldUsername = $_SESSION['old_username'] ?? '';
unset($_SESSION['old_username']);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">
    <title>ورود به پنل مدیریت</title>
    
    <!-- ═══ Fonts ═══ -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    
    <!-- ═══ TailwindCSS ═══ -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- ═══ Font Awesome ═══ -->
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
        
        <!-- ═══ Logo & Title ═══ -->
        <div class="text-center mb-8">
            <div class="inline-block float">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-4xl shadow-2xl mb-4">
                    🎬
                </div>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">پنل مدیریت</h1>
            <p class="text-white/60 text-sm">برای ورود، اطلاعات خود را وارد کنید</p>
        </div>
        
        <!-- ═══ Login Form ═══ -->
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
                        <span>نام کاربری</span>
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
                            placeholder="نام کاربری خود را وارد کنید"
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
                        <span>رمز عبور</span>
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
                            placeholder="رمز عبور خود را وارد کنید"
                        >
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40">
                            <i class="fas fa-lock"></i>
                        </span>
                        <button 
                            type="button" 
                            onclick="togglePassword()"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/60 transition"
                            title="نمایش/مخفی رمز عبور"
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
                        <span class="text-white/70 text-sm">مرا به خاطر بسپار</span>
                    </label>
                    
                    <a 
                        href="/admin/forgot-password.php" 
                        class="text-purple-400 text-sm hover:text-purple-300 transition"
                    >
                        فراموشی رمز؟
                    </a>
                </div>
                
                <!-- Submit Button -->
                <button 
                    type="submit" 
                    id="submitBtn"
                    class="w-full bg-gradient-to-r from-purple-500 to-blue-500 text-white font-bold py-3 rounded-lg hover:opacity-90 transition transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2"
                >
                    <i class="fas fa-sign-in-alt"></i>
                    <span>ورود به پنل</span>
                </button>
                
            </form>
            
            <!-- Help Text -->
            <div class="mt-6 pt-6 border-t border-white/10">
                <div class="flex items-center justify-center gap-2 text-white/50 text-xs">
                    <i class="fas fa-shield-alt"></i>
                    <span>اتصال امن با رمزنگاری SSL</span>
                </div>
                <p class="text-white/40 text-xs text-center mt-2">
                    در صورت فراموشی رمز عبور، با پشتیبانی تماس بگیرید
                </p>
            </div>
            
        </div>
        
        <!-- ═══ Footer ═══ -->
        <div class="text-center mt-6 text-white/40 text-xs">
            <p>Youtuber Bot v2.0.0 | ساخته شده با ❤️</p>
        </div>
        
    </div>
    
    <!-- ═══ JavaScript ═══ -->
    <script>
    // ═══ Toggle Password Visibility ═══
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
    
    // ═══ Form Validation ═══
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        
        if (!username || !password) {
            e.preventDefault();
            showToast('لطفاً تمام فیلدها را پر کنید', 'warning');
            return false;
        }
        
        // Disable button and show loading
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.classList.add('btn-loading');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>در حال ورود...</span>';
        
        // Re-enable after 5 seconds (in case of error)
        setTimeout(() => {
            if (submitBtn.classList.contains('btn-loading')) {
                submitBtn.classList.remove('btn-loading');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i><span>ورود به پنل</span>';
            }
        }, 5000);
    });
    
    // ═══ Toast Notification ═══
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
    
    // ═══ Auto-focus on error ═══
    <?php if ($error): ?>
    document.getElementById('username').focus();
    <?php endif; ?>
    
    // ═══ Keyboard Shortcuts ═══
    document.addEventListener('keydown', function(e) {
        // Ctrl+Enter to submit
        if (e.ctrlKey && e.key === 'Enter') {
            document.getElementById('loginForm').submit();
        }
    });
    </script>
    
</body>
</html>