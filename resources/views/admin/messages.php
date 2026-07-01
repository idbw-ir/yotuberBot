<?php
/**
 * ============================================
 * Messages Management - Ù…Ø¯ÛŒØ±ÛŒØª Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§
 * ============================================
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * 
 * Ù„ÛŒØ³Øª Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§ Ø¨Ø§ ÙÛŒÙ„ØªØ±ØŒ Ø¬Ø³ØªØ¬Ùˆ Ùˆ Ù…Ø´Ø§Ù‡Ø¯Ù‡ Ø¬Ø²Ø¦ÛŒØ§Øª
 * Ø§Ø² layout Ø§ØµÙ„ÛŒ (admin.php) Ø§Ø³ØªÙØ§Ø¯Ù‡ Ù…ÛŒâ€ŒÚ©Ù†Ù‡
 */

// Ù…ØªØºÛŒØ±Ù‡Ø§ÛŒ Ù…ÙˆØ±Ø¯ Ù†ÛŒØ§Ø² Ø§Ø² Controller:
// - $messages (Ø¢Ø±Ø§ÛŒÙ‡ Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§)
// - $pagination (Ø§Ø·Ù„Ø§Ø¹Ø§Øª ØµÙØ­Ù‡â€ŒØ¨Ù†Ø¯ÛŒ)
// - $filters (ÙÛŒÙ„ØªØ±Ù‡Ø§ÛŒ ÙØ¹Ù„ÛŒ)
// - $stats (Ø¢Ù…Ø§Ø± Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§)

$messages = $messages ?? [];
$pagination = $pagination ?? ['total' => 0, 'current_page' => 1, 'total_pages' => 1];
$filters = $filters ?? ['search' => '', 'direction' => '', 'type' => '', 'sort' => 'created_at', 'order' => 'DESC'];
$stats = $stats ?? ['total' => 0, 'incoming' => 0, 'outgoing' => 0, 'today' => 0, 'unread' => 0];

// CSRF Token
$csrfToken = $_SESSION['_csrf_token'] ?? '';
?>

<!-- â•â•â• Ø¢Ù…Ø§Ø± Ø³Ø±ÛŒØ¹ â•â•â• -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">ðŸ“¨</div>
        <div class="text-white/60 text-xs mb-1">Ú©Ù„ Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§</div>
        <div class="text-white text-2xl font-bold"><?= number_format($stats['total'] ?? 0) ?></div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">ðŸ“¥</div>
        <div class="text-white/60 text-xs mb-1">Ø¯Ø±ÛŒØ§ÙØªÛŒ</div>
        <div class="text-green-400 text-2xl font-bold"><?= number_format($stats['incoming'] ?? 0) ?></div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">ðŸ“¤</div>
        <div class="text-white/60 text-xs mb-1">Ø§Ø±Ø³Ø§Ù„ÛŒ</div>
        <div class="text-blue-400 text-2xl font-bold"><?= number_format($stats['outgoing'] ?? 0) ?></div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">ðŸ“…</div>
        <div class="text-white/60 text-xs mb-1">Ø§Ù…Ø±ÙˆØ²</div>
        <div class="text-purple-400 text-2xl font-bold"><?= number_format($stats['today'] ?? 0) ?></div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">ðŸ””</div>
        <div class="text-white/60 text-xs mb-1">Ø®ÙˆØ§Ù†Ø¯Ù‡ Ù†Ø´Ø¯Ù‡</div>
        <div class="text-red-400 text-2xl font-bold"><?= number_format($stats['unread'] ?? 0) ?></div>
    </div>
</div>

<!-- â•â•â• ÙÛŒÙ„ØªØ±Ù‡Ø§ Ùˆ Ø¬Ø³ØªØ¬Ùˆ â•â•â• -->
<div class="glass rounded-2xl p-5 mb-6">
    <form method="GET" action="/admin/messages.php" class="space-y-4">
        
        <!-- Ø±Ø¯ÛŒÙ Ø§ÙˆÙ„: Ø¬Ø³ØªØ¬Ùˆ -->
        <div>
            <label class="block text-white/70 text-sm mb-2">Ø¬Ø³ØªØ¬Ùˆ Ø¯Ø± Ù…ØªÙ† Ù¾ÛŒØ§Ù…</label>
            <div class="relative">
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40">
                    <i class="fas fa-search"></i>
                </span>
                <input 
                    type="text" 
                    name="search" 
                    value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                    placeholder="Ø¬Ø³ØªØ¬Ùˆ Ø¯Ø± Ù…ØªÙ† Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§..."
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 pr-10 pl-4 text-white placeholder-white/40 focus:border-purple-500 transition"
                >
            </div>
        </div>
        
        <!-- Ø±Ø¯ÛŒÙ Ø¯ÙˆÙ…: ÙÛŒÙ„ØªØ±Ù‡Ø§ -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <!-- ÙÛŒÙ„ØªØ± Ø¬Ù‡Øª -->
            <div>
                <label class="block text-white/70 text-sm mb-2">Ø¬Ù‡Øª Ù¾ÛŒØ§Ù…</label>
                <select 
                    name="direction" 
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                >
                    <option value="" <?= empty($filters['direction']) ? 'selected' : '' ?>>Ù‡Ù…Ù‡</option>
                    <option value="in" <?= ($filters['direction'] ?? '') === 'in' ? 'selected' : '' ?>>ðŸ“¥ Ø¯Ø±ÛŒØ§ÙØªÛŒ</option>
                    <option value="out" <?= ($filters['direction'] ?? '') === 'out' ? 'selected' : '' ?>>ðŸ“¤ Ø§Ø±Ø³Ø§Ù„ÛŒ</option>
                </select>
            </div>
            
            <!-- ÙÛŒÙ„ØªØ± Ù†ÙˆØ¹ -->
            <div>
                <label class="block text-white/70 text-sm mb-2">Ù†ÙˆØ¹ Ù¾ÛŒØ§Ù…</label>
                <select 
                    name="type" 
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                >
                    <option value="" <?= empty($filters['type']) ? 'selected' : '' ?>>Ù‡Ù…Ù‡</option>
                    <option value="text" <?= ($filters['type'] ?? '') === 'text' ? 'selected' : '' ?>>ðŸ’¬ Ù…ØªÙ†ÛŒ</option>
                    <option value="photo" <?= ($filters['type'] ?? '') === 'photo' ? 'selected' : '' ?>>ðŸ–¼ï¸ Ø¹Ú©Ø³</option>
                    <option value="video" <?= ($filters['type'] ?? '') === 'video' ? 'selected' : '' ?>>ðŸŽ¥ ÙˆÛŒØ¯Ø¦Ùˆ</option>
                    <option value="document" <?= ($filters['type'] ?? '') === 'document' ? 'selected' : '' ?>>ðŸ“„ ÙØ§ÛŒÙ„</option>
                    <option value="audio" <?= ($filters['type'] ?? '') === 'audio' ? 'selected' : '' ?>>ðŸŽµ ØµØ¯Ø§</option>
                    <option value="voice" <?= ($filters['type'] ?? '') === 'voice' ? 'selected' : '' ?>>ðŸŽ¤ ÙˆÛŒØ³</option>
                    <option value="location" <?= ($filters['type'] ?? '') === 'location' ? 'selected' : '' ?>>ðŸ“ Ù…ÙˆÙ‚Ø¹ÛŒØª</option>
                    <option value="contact" <?= ($filters['type'] ?? '') === 'contact' ? 'selected' : '' ?>>ðŸ“± ØªÙ…Ø§Ø³</option>
                    <option value="sticker" <?= ($filters['type'] ?? '') === 'sticker' ? 'selected' : '' ?>>ðŸŽ­ Ø§Ø³ØªÛŒÚ©Ø±</option>
                </select>
            </div>
            
            <!-- ÙÛŒÙ„ØªØ± ØªØ§Ø±ÛŒØ® -->
            <div>
                <label class="block text-white/70 text-sm mb-2">Ù…Ø±ØªØ¨â€ŒØ³Ø§Ø²ÛŒ</label>
                <select 
                    name="sort" 
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                >
                    <option value="created_at" <?= ($filters['sort'] ?? '') === 'created_at' ? 'selected' : '' ?>>ØªØ§Ø±ÛŒØ® Ø§ÛŒØ¬Ø§Ø¯</option>
                    <option value="id" <?= ($filters['sort'] ?? '') === 'id' ? 'selected' : '' ?>>Ø¢ÛŒØ¯ÛŒ</option>
                    <option value="user_id" <?= ($filters['sort'] ?? '') === 'user_id' ? 'selected' : '' ?>>Ú©Ø§Ø±Ø¨Ø±</option>
                </select>
            </div>
            
        </div>
        
        <!-- Ø±Ø¯ÛŒÙ Ø³ÙˆÙ…: Ø¯Ú©Ù…Ù‡â€ŒÙ‡Ø§ -->
        <div class="flex flex-wrap gap-3">
            <button 
                type="submit" 
                class="bg-gradient-to-r from-purple-500 to-blue-500 text-white px-6 py-2.5 rounded-lg hover:opacity-90 transition flex items-center gap-2"
            >
                <i class="fas fa-filter"></i>
                <span>Ø§Ø¹Ù…Ø§Ù„ ÙÛŒÙ„ØªØ±</span>
            </button>
            
            <a 
                href="/admin/messages.php" 
                class="bg-white/10 text-white px-6 py-2.5 rounded-lg hover:bg-white/20 transition flex items-center gap-2"
            >
                <i class="fas fa-times"></i>
                <span>Ù¾Ø§Ú© Ú©Ø±Ø¯Ù† ÙÛŒÙ„ØªØ±Ù‡Ø§</span>
            </a>
            
            <button 
                type="button" 
                onclick="exportMessages()" 
                class="bg-green-500/20 border border-green-500/50 text-green-300 px-6 py-2.5 rounded-lg hover:bg-green-500/30 transition flex items-center gap-2"
            >
                <i class="fas fa-download"></i>
                <span>Ø®Ø±ÙˆØ¬ÛŒ CSV</span>
            </button>
        </div>
        
    </form>
</div>

<!-- â•â•â• Ù„ÛŒØ³Øª Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§ â•â•â• -->
<div class="glass rounded-2xl overflow-hidden">
    
    <?php if (empty($messages)): ?>
    <!-- Empty State -->
    <div class="text-center py-16">
        <div class="text-6xl mb-4">ðŸ“¨</div>
        <h3 class="text-white text-xl font-bold mb-2">Ù‡Ù†ÙˆØ² Ù¾ÛŒØ§Ù…ÛŒ Ø¯Ø±ÛŒØ§ÙØª Ù†Ø´Ø¯Ù‡</h3>
        <p class="text-white/50 text-sm mb-6">ÙˆÙ‚ØªÛŒ Ú©Ø§Ø±Ø¨Ø±Ø§Ù† Ø¨Ù‡ Ø±Ø¨Ø§Øª Ù¾ÛŒØ§Ù… Ø¨Ø¯Ù†ØŒ Ø§ÛŒÙ†Ø¬Ø§ Ù†Ù…Ø§ÛŒØ´ Ø¯Ø§Ø¯Ù‡ Ù…ÛŒâ€ŒØ´Ù†</p>
        <a href="/admin/" class="inline-block bg-purple-500/20 border border-purple-500/50 text-purple-300 px-6 py-2.5 rounded-lg hover:bg-purple-500/30 transition">
            Ø¨Ø§Ø²Ú¯Ø´Øª Ø¨Ù‡ Ø¯Ø§Ø´Ø¨ÙˆØ±Ø¯
        </a>
    </div>
    <?php else: ?>
    
    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-white/5 border-b border-white/10">
                    <th class="text-right py-3 px-4 text-white/70 font-medium">Ø¢ÛŒØ¯ÛŒ</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">Ú©Ø§Ø±Ø¨Ø±</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">Ø¬Ù‡Øª</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">Ù†ÙˆØ¹</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">Ù…ØªÙ† Ù¾ÛŒØ§Ù…</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden md:table-cell">
                        <a href="?sort=created_at&order=<?= ($filters['sort'] ?? '') === 'created_at' && ($filters['order'] ?? '') === 'ASC' ? 'DESC' : 'ASC' ?>&<?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="hover:text-white transition flex items-center gap-1">
                            <span>ØªØ§Ø±ÛŒØ®</span>
                            <?php if (($filters['sort'] ?? '') === 'created_at'): ?>
                            <i class="fas fa-sort-<?= ($filters['order'] ?? '') === 'ASC' ? 'up' : 'down' ?> text-xs"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">Ø¹Ù…Ù„ÛŒØ§Øª</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                <tr class="border-b border-white/5 hover:bg-white/5 transition">
                    
                    <!-- ID -->
                    <td class="py-3 px-4">
                        <code class="text-white/60 text-xs bg-white/10 px-2 py-1 rounded">
                            #<?= $msg['id'] ?>
                        </code>
                    </td>
                    
                    <!-- User -->
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                <?= strtoupper(substr($msg['first_name'] ?? $msg['username'] ?? '?', 0, 1)) ?>
                            </div>
                            <div class="min-w-0">
                                <div class="text-white text-sm font-medium truncate">
                                    <?= htmlspecialchars($msg['user_display_name'] ?? 'Ú©Ø§Ø±Ø¨Ø±') ?>
                                </div>
                                <?php if (!empty($msg['username'])): ?>
                                <a href="https://t.me/<?= htmlspecialchars($msg['username']) ?>" target="_blank" class="text-blue-400 text-xs hover:underline truncate block">
                                    @<?= htmlspecialchars($msg['username']) ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    
                    <!-- Direction -->
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium
                            <?= $msg['direction'] === 'in' ? 'bg-green-500/20 text-green-300' : 'bg-blue-500/20 text-blue-300' ?>">
                            <span><?= $msg['direction_icon'] ?? 'ðŸ“' ?></span>
                            <span><?= htmlspecialchars($msg['direction_text'] ?? 'Ù†Ø§Ù…Ø´Ø®Øµ') ?></span>
                        </span>
                    </td>
                    
                    <!-- Type -->
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-white/10 text-white">
                            <span><?= $msg['type_icon'] ?? 'ðŸ’¬' ?></span>
                            <span class="hidden sm:inline"><?= htmlspecialchars($msg['message_type'] ?? 'text') ?></span>
                        </span>
                    </td>
                    
                    <!-- Text -->
                    <td class="py-3 px-4">
                        <div class="text-white/80 text-sm truncate max-w-xs" title="<?= htmlspecialchars($msg['text'] ?? '') ?>">
                            <?= htmlspecialchars($msg['text_preview'] ?? $msg['text'] ?? '-') ?>
                        </div>
                    </td>
                    
                    <!-- Date -->
                    <td class="py-3 px-4 text-white/60 text-xs hidden md:table-cell">
                        <div><?= htmlspecialchars($msg['created_at'] ?? '-') ?></div>
                        <div class="text-white/40"><?= htmlspecialchars($msg['time_ago'] ?? '') ?></div>
                    </td>
                    
                    <!-- Actions -->
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <button 
                                onclick="viewMessage(<?= $msg['id'] ?>)"
                                class="bg-blue-500/20 hover:bg-blue-500/30 text-blue-300 p-2 rounded-lg transition"
                                title="Ù…Ø´Ø§Ù‡Ø¯Ù‡ Ø¬Ø²Ø¦ÛŒØ§Øª"
                            >
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                            
                            <a 
                                href="/admin/chat.php?id=<?= $msg['user_id'] ?>"
                                class="bg-purple-500/20 hover:bg-purple-500/30 text-purple-300 p-2 rounded-lg transition"
                                title="Ú†Øª Ø¨Ø§ Ú©Ø§Ø±Ø¨Ø±"
                            >
                                <i class="fas fa-comments text-sm"></i>
                            </a>
                            
                            <button 
                                onclick="deleteMessage(<?= $msg['id'] ?>)"
                                class="bg-red-500/20 hover:bg-red-500/30 text-red-300 p-2 rounded-lg transition"
                                title="Ø­Ø°Ù Ù¾ÛŒØ§Ù…"
                            >
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </div>
                    </td>
                    
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- â•â•â• Pagination â•â•â• -->
    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="border-t border-white/10 p-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            
            <!-- Info -->
            <div class="text-white/60 text-sm">
                Ù†Ù…Ø§ÛŒØ´ <?= number_format($pagination['from'] ?? 0) ?> ØªØ§ <?= number_format($pagination['to'] ?? 0) ?> Ø§Ø² <?= number_format($pagination['total']) ?> Ù¾ÛŒØ§Ù…
            </div>
            
            <!-- Pagination Buttons -->
            <div class="flex items-center gap-2">
                
                <!-- Previous -->
                <?php if ($pagination['current_page'] > 1): ?>
                <a 
                    href="?page=<?= $pagination['current_page'] - 1 ?>&<?= http_build_query($filters) ?>"
                    class="bg-white/10 hover:bg-white/20 text-white px-3 py-2 rounded-lg transition flex items-center gap-1"
                >
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="hidden sm:inline">Ù‚Ø¨Ù„ÛŒ</span>
                </a>
                <?php else: ?>
                <span class="bg-white/5 text-white/30 px-3 py-2 rounded-lg flex items-center gap-1 cursor-not-allowed">
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="hidden sm:inline">Ù‚Ø¨Ù„ÛŒ</span>
                </span>
                <?php endif; ?>
                
                <!-- Page Numbers -->
                <?php
                $startPage = max(1, $pagination['current_page'] - 2);
                $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                
                if ($startPage > 1): ?>
                <a href="?page=1&<?= http_build_query($filters) ?>" class="bg-white/10 hover:bg-white/20 text-white px-3 py-2 rounded-lg transition">1</a>
                <?php if ($startPage > 2): ?>
                <span class="text-white/40 px-2">...</span>
                <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a 
                    href="?page=<?= $i ?>&<?= http_build_query($filters) ?>"
                    class="<?= $i === $pagination['current_page'] ? 'bg-purple-500 text-white' : 'bg-white/10 hover:bg-white/20 text-white' ?> px-3 py-2 rounded-lg transition"
                >
                    <?= $i ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($endPage < $pagination['total_pages']): ?>
                <?php if ($endPage < $pagination['total_pages'] - 1): ?>
                <span class="text-white/40 px-2">...</span>
                <?php endif; ?>
                <a href="?page=<?= $pagination['total_pages'] ?>&<?= http_build_query($filters) ?>" class="bg-white/10 hover:bg-white/20 text-white px-3 py-2 rounded-lg transition">
                    <?= $pagination['total_pages'] ?>
                </a>
                <?php endif; ?>
                
                <!-- Next -->
                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <a 
                    href="?page=<?= $pagination['current_page'] + 1 ?>&<?= http_build_query($filters) ?>"
                    class="bg-white/10 hover:bg-white/20 text-white px-3 py-2 rounded-lg transition flex items-center gap-1"
                >
                    <span class="hidden sm:inline">Ø¨Ø¹Ø¯ÛŒ</span>
                    <i class="fas fa-chevron-left text-xs"></i>
                </a>
                <?php else: ?>
                <span class="bg-white/5 text-white/30 px-3 py-2 rounded-lg flex items-center gap-1 cursor-not-allowed">
                    <span class="hidden sm:inline">Ø¨Ø¹Ø¯ÛŒ</span>
                    <i class="fas fa-chevron-left text-xs"></i>
                </span>
                <?php endif; ?>
                
            </div>
            
        </div>
    </div>
    <?php endif; ?>
    
    <?php endif; ?>
    
</div>

<!-- â•â•â• Modal for Message Details â•â•â• -->
<div id="messageModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="glass rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        
        <!-- Modal Header -->
        <div class="p-5 border-b border-white/10 flex items-center justify-between">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                <i class="fas fa-envelope"></i>
                <span>Ø¬Ø²Ø¦ÛŒØ§Øª Ù¾ÛŒØ§Ù…</span>
            </h3>
            <button 
                onclick="closeMessageModal()"
                class="text-white/60 hover:text-white p-2 rounded-lg hover:bg-white/10 transition"
            >
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Content -->
        <div id="messageModalContent" class="p-5">
            <!-- Content will be loaded here -->
        </div>
        
    </div>
</div>

<!-- â•â•â• JavaScript â•â•â• -->
<script>
// â•â•â• View Message Details â•â•â•
async function viewMessage(messageId) {
    try {
        const response = await fetch(`/admin/api/messages/${messageId}`);
        const data = await response.json();
        
        if (data.success) {
            const msg = data.data;
            
            const content = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-white/60 text-xs">Ø¢ÛŒØ¯ÛŒ Ù¾ÛŒØ§Ù…</label>
                            <div class="text-white font-mono">#${msg.id}</div>
                        </div>
                        <div>
                            <label class="text-white/60 text-xs">Ø¢ÛŒØ¯ÛŒ Ú©Ø§Ø±Ø¨Ø±</label>
                            <div class="text-white font-mono">${msg.user_id}</div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-white/60 text-xs">Ú©Ø§Ø±Ø¨Ø±</label>
                        <div class="text-white font-medium">${msg.user_display_name || 'Ú©Ø§Ø±Ø¨Ø±'}</div>
                        ${msg.username ? `<a href="https://t.me/${msg.username}" target="_blank" class="text-blue-400 text-sm hover:underline">@${msg.username}</a>` : ''}
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-white/60 text-xs">Ø¬Ù‡Øª</label>
                            <div class="text-white">${msg.direction_icon} ${msg.direction_text}</div>
                        </div>
                        <div>
                            <label class="text-white/60 text-xs">Ù†ÙˆØ¹</label>
                            <div class="text-white">${msg.type_icon} ${msg.message_type}</div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-white/60 text-xs">ØªØ§Ø±ÛŒØ® Ø§ÛŒØ¬Ø§Ø¯</label>
                        <div class="text-white">${msg.created_at}</div>
                        <div class="text-white/50 text-sm">${msg.time_ago}</div>
                    </div>
                    
                    <div>
                        <label class="text-white/60 text-xs mb-2 block">Ù…ØªÙ† Ù¾ÛŒØ§Ù…</label>
                        <div class="bg-white/5 rounded-lg p-4 text-white whitespace-pre-wrap break-words">
                            ${msg.text ? msg.text.replace(/\n/g, '<br>') : '<span class="text-white/40">Ø¨Ø¯ÙˆÙ† Ù…ØªÙ†</span>'}
                        </div>
                    </div>
                    
                    <div class="flex gap-3 pt-4">
                        <a href="/admin/chat.php?id=${msg.user_id}" class="flex-1 bg-purple-500/20 border border-purple-500/50 text-purple-300 px-4 py-2.5 rounded-lg hover:bg-purple-500/30 transition text-center">
                            <i class="fas fa-comments"></i>
                            <span>Ú†Øª Ø¨Ø§ Ú©Ø§Ø±Ø¨Ø±</span>
                        </a>
                        <button onclick="deleteMessage(${msg.id}, true)" class="flex-1 bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-2.5 rounded-lg hover:bg-red-500/30 transition">
                            <i class="fas fa-trash"></i>
                            <span>Ø­Ø°Ù Ù¾ÛŒØ§Ù…</span>
                        </button>
                    </div>
                </div>
            `;
            
            document.getElementById('messageModalContent').innerHTML = content;
            document.getElementById('messageModal').classList.remove('hidden');
            document.getElementById('messageModal').classList.add('flex');
        } else {
            showToast(data.error || 'Ø®Ø·Ø§ Ø¯Ø± Ø¯Ø±ÛŒØ§ÙØª Ø§Ø·Ù„Ø§Ø¹Ø§Øª', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Close Modal â•â•â•
function closeMessageModal() {
    document.getElementById('messageModal').classList.add('hidden');
    document.getElementById('messageModal').classList.remove('flex');
}

// â•â•â• Delete Message â•â•â•
async function deleteMessage(messageId, fromModal = false) {
    if (!confirm('Ø¢ÛŒØ§ Ù…Ø·Ù…Ø¦Ù† Ù‡Ø³ØªÛŒØ¯ Ú©Ù‡ Ù…ÛŒâ€ŒØ®ÙˆØ§Ù‡ÛŒØ¯ Ø§ÛŒÙ† Ù¾ÛŒØ§Ù… Ø±Ø§ Ø­Ø°Ù Ú©Ù†ÛŒØ¯ØŸ')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/api/messages/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ message_id: messageId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Ù¾ÛŒØ§Ù… Ø­Ø°Ù Ø´Ø¯', 'success');
            
            if (fromModal) {
                closeMessageModal();
            }
            
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Export Messages â•â•â•
function exportMessages() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = `/admin/api/messages/export?${params.toString()}`;
    showToast('Ø¯Ø± Ø­Ø§Ù„ Ø¯Ø§Ù†Ù„ÙˆØ¯ ÙØ§ÛŒÙ„...', 'info');
}

// â•â•â• Close Modal on Escape â•â•â•
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMessageModal();
    }
});

// â•â•â• Close Modal on Outside Click â•â•â•
document.getElementById('messageModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeMessageModal();
    }
});
</script>