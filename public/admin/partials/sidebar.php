<aside class="w-64 bg-gray-900/50 min-h-screen p-4 border-l border-white/10">
    <div class="text-center mb-6">
        <h2 class="text-xl font-bold text-white">🎬 پنل مدیریت</h2>
        <p class="text-white/50 text-xs mt-1">v2.0.0</p>
    </div>
    
    <nav class="space-y-1">
        <a href="/admin/" class="block px-4 py-2 rounded-lg text-white hover:bg-white/10 transition">📊 داشبورد</a>
        <a href="/admin/users.php" class="block px-4 py-2 rounded-lg text-white hover:bg-white/10 transition">👥 کاربران</a>
        <a href="/admin/chat.php" class="block px-4 py-2 rounded-lg text-white hover:bg-white/10 transition">💬 چت</a>
        <a href="/admin/messages.php" class="block px-4 py-2 rounded-lg text-white hover:bg-white/10 transition">📨 پیام‌ها</a>
        <a href="/admin/donations.php" class="block px-4 py-2 rounded-lg text-white hover:bg-white/10 transition">💰 دونیت‌ها</a>
        <a href="/admin/keywords.php" class="block px-4 py-2 rounded-lg text-white hover:bg-white/10 transition">🔑 کلمات کلیدی</a>
        <a href="/admin/broadcast.php" class="block px-4 py-2 rounded-lg text-white hover:bg-white/10 transition">📢 ارسال دسته‌جمعی</a>
        <a href="/admin/statistics.php" class="block px-4 py-2 rounded-lg text-white hover:bg-white/10 transition">📈 آمار</a>
        <a href="/admin/settings.php" class="block px-4 py-2 rounded-lg text-white hover:bg-white/10 transition">⚙️ تنظیمات</a>
        
        <hr class="border-white/10 my-3">
        
        <a href="/admin/logout.php" class="block px-4 py-2 rounded-lg text-red-400 hover:bg-red-500/10 transition">🚪 خروج</a>
    </nav>
    
    <div class="mt-6 pt-4 border-t border-white/10">
        <div class="flex items-center gap-2 text-white/60 text-xs">
            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
            <span><?= htmlspecialchars($currentAdmin['name'] ?? '') ?></span>
        </div>
    </div>
</aside>