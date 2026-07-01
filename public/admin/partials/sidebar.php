<aside class="w-64 bg-gray-900/50 min-h-screen p-4 border-l border-white/10">
    <div class="text-center mb-6">
        <h2 class="text-xl font-bold text-white">ðŸŽ¬ Ù¾Ù†Ù„ Ù…Ø¯ÛŒØ±ÛŒØª</h2>
        <p class="text-white/50 text-xs mt-1">v2.1.0</p>
    </div>
    
    <nav class="space-y-1">
        <a href="/admin/" class="block px-4 py-2 rounded-lg text-white hover:bg-white/10 transition">ðŸ“Š Ø¯Ø§Ø´Ø¨ÙˆØ±Ø¯</a>
        <a href="/admin/users.php" class="block px-4 py-2 rounded-lg text-white hover:bg-white/10 transition">ðŸ‘¥ Ú©Ø§Ø±Ø¨Ø±Ø§Ù†</a>
        <a href="/admin/chat.php" class="block px-4 py-2 rounded-lg text-white hover:bg-white/10 transition">ðŸ’¬ Ú†Øª</a>
        <a href="/admin/messages.php" class="block px-4 py-2 rounded-lg text-white hover:bg-white/10 transition">ðŸ“¨ Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§</a>
        <a href="/admin/donations.php" class="block px-4 py-2 rounded-lg text-white hover:bg-white/10 transition">ðŸ’° Ø¯ÙˆÙ†ÛŒØªâ€ŒÙ‡Ø§</a>
        <a href="/admin/keywords.php" class="block px-4 py-2 rounded-lg text-white hover:bg-white/10 transition">ðŸ”‘ Ú©Ù„Ù…Ø§Øª Ú©Ù„ÛŒØ¯ÛŒ</a>
        <a href="/admin/broadcast.php" class="block px-4 py-2 rounded-lg text-white hover:bg-white/10 transition">ðŸ“¢ Ø§Ø±Ø³Ø§Ù„ Ø¯Ø³ØªÙ‡â€ŒØ¬Ù…Ø¹ÛŒ</a>
        <a href="/admin/statistics.php" class="block px-4 py-2 rounded-lg text-white hover:bg-white/10 transition">ðŸ“ˆ Ø¢Ù…Ø§Ø±</a>
        <a href="/admin/settings.php" class="block px-4 py-2 rounded-lg text-white hover:bg-white/10 transition">âš™ï¸ ØªÙ†Ø¸ÛŒÙ…Ø§Øª</a>
        
        <hr class="border-white/10 my-3">
        
        <a href="/admin/logout.php" class="block px-4 py-2 rounded-lg text-red-400 hover:bg-red-500/10 transition">ðŸšª Ø®Ø±ÙˆØ¬</a>
    </nav>
    
    <div class="mt-6 pt-4 border-t border-white/10">
        <div class="flex items-center gap-2 text-white/60 text-xs">
            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
            <span><?= htmlspecialchars($currentAdmin['name'] ?? '') ?></span>
        </div>
    </div>
</aside>