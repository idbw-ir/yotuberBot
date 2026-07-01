<?php
/**
 * ============================================
 * صفحه اصلی (Home Page) - قابل شخصی‌سازی
 * ============================================
 * 
 * می‌توانید این فایل را با HTML، CSS، JavaScript
 * و PHP دلخواه خود شخصی‌سازی کنید.
 * 
 * متغیرهای قابل استفاده:
 *   $isLoggedIn  - آیا کاربر لاگین کرده است؟
 *   $adminName   - نام ادمین (اگر لاگین کرده)
 *   $adminUrl    - لینک به پنل مدیریت
 *   $customContent - محتوای سفارشی (می‌توانید در
 *                   بلاک CUSTOM CONTENT کد بزنید)
 */

// ──────────────────────────────────────
// تنظیمات اولیه
// ──────────────────────────────────────

$isLoggedIn = false;
$adminName = '';
$adminUrl = '/admin/';
$error = '';
$success = '';
$csrfToken = $_SESSION['_csrf_token'] ?? \App\Helpers\Security::generateCsrfToken();
$_SESSION['_csrf_token'] = $csrfToken;

try {
    $auth = \App\Admin\Auth::getInstance();
    $isLoggedIn = $auth->check();
    if ($isLoggedIn) {
        $adminName = $auth->name();
    }
} catch (Exception $e) {
    // دیتابیس در دسترس نیست
}

// ──────────────────────────────────────
// ══════════════════════════════════════
// ← بخش محتوای سفارشی شما (PHP) →
// ══════════════════════════════════════
// کد PHP دلخواه خود را اینجا بنویسید
// مثال: دریافت اطلاعات از دیتابیس، API و ...

/*
$customData = $db->fetchAll("SELECT * FROM ...");
*/
// ──────────────────────────────────────

?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ربات یوتیوب - مدیریت خودکار کانال یوتیوب</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        * { font-family: 'Vazirmatn', Tahoma, sans-serif; }
        .glass { background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.15); }
        .glass-dark { background: rgba(0,0,0,0.3); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); }

        /* ════════════════════════════════ */
        /* ← استایل‌های سفارشی شما (CSS) →  */
        /* ════════════════════════════════ */
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-indigo-900 min-h-screen">

    <!-- ═══ Navbar ═══ -->
    <nav class="glass-dark fixed top-0 left-0 right-0 z-50 px-6 py-3">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🎬</span>
                <span class="text-white font-bold text-lg">ربات یوتیوب</span>
            </div>
            <div class="flex items-center gap-4">
                <?php if ($isLoggedIn): ?>
                    <span class="text-white/70 text-sm">خوش آمدید، <?= htmlspecialchars($adminName) ?></span>
                    <a href="<?= $adminUrl ?>" class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-purple-700 transition">
                        پنل مدیریت
                    </a>
                <?php else: ?>
                    <a href="/admin/" class="text-white/70 hover:text-white transition text-sm">ورود به پنل</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- ═══ Hero Section ═══ -->
    <section class="pt-24 pb-16 px-4">
        <div class="max-w-6xl mx-auto text-center mt-12">
            <div class="inline-block bg-white/10 rounded-full p-6 mb-6">
                <span class="text-7xl">🎬</span>
            </div>
            <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">
                ربات مدیریت کانال یوتیوب
            </h1>
            <p class="text-lg text-white/60 max-w-2xl mx-auto mb-8">
                مدیریت هوشمند کانال یوتیوب با ربات تلگرام
            </p>

            <?php if (!$isLoggedIn): ?>
            <!-- ═══ Login Form ═══ -->
            <div class="glass rounded-2xl p-8 max-w-md mx-auto">
                <h2 class="text-xl font-bold text-white mb-6">ورود به پنل مدیریت</h2>

                <?php if ($error): ?>
                <div class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg mb-4 text-sm">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="/admin/login.php" class="space-y-4">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <div>
                        <input type="text" name="username" required
                            class="w-full bg-white/10 border border-white/20 rounded-lg py-3 px-4 text-white placeholder-white/40 focus:border-purple-500 transition"
                            placeholder="نام کاربری">
                    </div>
                    <div>
                        <input type="password" name="password" required
                            class="w-full bg-white/10 border border-white/20 rounded-lg py-3 px-4 text-white placeholder-white/40 focus:border-purple-500 transition"
                            placeholder="رمز عبور">
                    </div>
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-purple-500 to-blue-500 text-white font-bold py-3 rounded-lg hover:opacity-90 transition">
                        ورود به پنل
                    </button>
                </form>
            </div>
            <?php else: ?>
            <!-- ═══ Logged In Welcome ═══ -->
            <div class="glass rounded-2xl p-8 max-w-md mx-auto">
                <div class="text-5xl mb-4">👋</div>
                <h2 class="text-xl font-bold text-white mb-2">
                    خوش آمدید، <?= htmlspecialchars($adminName) ?>
                </h2>
                <p class="text-white/60 text-sm mb-6">به پنل مدیریت ربات یوتیوب خوش آمدید</p>
                <a href="<?= $adminUrl ?>"
                    class="inline-block bg-gradient-to-r from-purple-500 to-blue-500 text-white font-bold py-3 px-8 rounded-lg hover:opacity-90 transition">
                    ورود به داشبورد
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ═══ Features ═══ -->
    <section class="py-16 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="glass rounded-2xl p-6 text-center">
                    <div class="text-4xl mb-3">📤</div>
                    <h3 class="text-white font-bold mb-2">آپلود خودکار</h3>
                    <p class="text-white/50 text-sm">آپلود هوشمند ویدیو در یوتیوب به صورت خودکار</p>
                </div>
                <div class="glass rounded-2xl p-6 text-center">
                    <div class="text-4xl mb-3">📊</div>
                    <h3 class="text-white font-bold mb-2">آمار و تحلیل</h3>
                    <p class="text-white/50 text-sm">مشاهده آمار کانال و عملکرد ویدیوها</p>
                </div>
                <div class="glass rounded-2xl p-6 text-center">
                    <div class="text-4xl mb-3">🤖</div>
                    <h3 class="text-white font-bold mb-2">کنترل با تلگرام</h3>
                    <p class="text-white/50 text-sm">مدیریت کامل ربات از طریق تلگرام</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════════════════════════ -->
    <!-- ← محتوای سفارشی شما (HTML) →        -->
    <!-- ════════════════════════════════════ -->
    <?php if (false): /* برای فعال‌سازی، false را به true تغییر دهید */ ?>
    <section class="py-16 px-4">
        <div class="max-w-6xl mx-auto">
            <!--
            HTML دلخواه خود را اینجا قرار دهید.
            مثال:
            <div class="glass rounded-2xl p-8 text-center">
                <h2 class="text-2xl font-bold text-white mb-4">محتوای سفارشی</h2>
                <p class="text-white/60">این بخش را با محتوای دلخواه خود پر کنید.</p>
            </div>
            -->
        </div>
    </section>
    <?php endif; ?>

    <!-- ═══ Footer ═══ -->
    <footer class="py-8 px-4 mt-12">
        <div class="max-w-6xl mx-auto text-center text-white/30 text-sm">
            <p>Youtuber Bot v2.1.2 | ساخته شده با ❤️</p>
        </div>
    </footer>

    <!-- ════════════════════════════════════ -->
    <!-- ← اسکریپت‌های سفارشی شما (JS) →    -->
    <!-- ════════════════════════════════════ -->
    <script>
    // کد JavaScript دلخواه خود را اینجا بنویسید
    </script>

</body>
</html>
