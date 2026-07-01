<?php
/**
 * ============================================
 * Users Management - Ù…Ø¯ÛŒØ±ÛŒØª Ú©Ø§Ø±Ø¨Ø±Ø§Ù†
 * ============================================
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * 
 * Ù„ÛŒØ³Øª Ú©Ø§Ø±Ø¨Ø±Ø§Ù† Ø¨Ø§ ÙÛŒÙ„ØªØ±ØŒ Ø¬Ø³ØªØ¬Ùˆ Ùˆ Ø¹Ù…Ù„ÛŒØ§Øª
 * Ø§Ø² layout Ø§ØµÙ„ÛŒ (admin.php) Ø§Ø³ØªÙØ§Ø¯Ù‡ Ù…ÛŒâ€ŒÚ©Ù†Ù‡
 */

// Ù…ØªØºÛŒØ±Ù‡Ø§ÛŒ Ù…ÙˆØ±Ø¯ Ù†ÛŒØ§Ø² Ø§Ø² Controller:
// - $users (Ø¢Ø±Ø§ÛŒÙ‡ Ú©Ø§Ø±Ø¨Ø±Ø§Ù†)
// - $pagination (Ø§Ø·Ù„Ø§Ø¹Ø§Øª ØµÙØ­Ù‡â€ŒØ¨Ù†Ø¯ÛŒ)
// - $filters (ÙÛŒÙ„ØªØ±Ù‡Ø§ÛŒ ÙØ¹Ù„ÛŒ)
// - $stats (Ø¢Ù…Ø§Ø± Ú©Ø§Ø±Ø¨Ø±Ø§Ù†)

$users = $users ?? [];
$pagination = $pagination ?? ['total' => 0, 'current_page' => 1, 'total_pages' => 1];
$filters = $filters ?? ['search' => '', 'status' => '', 'sort' => 'joined_at', 'order' => 'DESC'];
$stats = $stats ?? ['total' => 0, 'vip' => 0, 'blocked' => 0, 'active' => 0];

// CSRF Token
$csrfToken = $_SESSION['_csrf_token'] ?? '';
?>

<!-- â•â•â• Ø¢Ù…Ø§Ø± Ø³Ø±ÛŒØ¹ â•â•â• -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">ðŸ‘¥</div>
        <div class="text-white/60 text-xs mb-1">Ú©Ù„ Ú©Ø§Ø±Ø¨Ø±Ø§Ù†</div>
        <div class="text-white text-2xl font-bold"><?= number_format($stats['total'] ?? 0) ?></div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">ðŸ‘‘</div>
        <div class="text-white/60 text-xs mb-1">Ú©Ø§Ø±Ø¨Ø±Ø§Ù† VIP</div>
        <div class="text-yellow-400 text-2xl font-bold"><?= number_format($stats['vip'] ?? 0) ?></div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">ðŸš«</div>
        <div class="text-white/60 text-xs mb-1">Ø¨Ù„Ø§Ú© Ø´Ø¯Ù‡</div>
        <div class="text-red-400 text-2xl font-bold"><?= number_format($stats['blocked'] ?? 0) ?></div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">ðŸŸ¢</div>
        <div class="text-white/60 text-xs mb-1">ÙØ¹Ø§Ù„ (Ù‡ÙØªÙ‡)</div>
        <div class="text-green-400 text-2xl font-bold"><?= number_format($stats['active'] ?? 0) ?></div>
    </div>
</div>

<!-- â•â•â• ÙÛŒÙ„ØªØ±Ù‡Ø§ Ùˆ Ø¬Ø³ØªØ¬Ùˆ â•â•â• -->
<div class="glass rounded-2xl p-5 mb-6">
    <form method="GET" action="/admin/users.php" class="space-y-4">
        
        <!-- Ø±Ø¯ÛŒÙ Ø§ÙˆÙ„: Ø¬Ø³ØªØ¬Ùˆ Ùˆ ÙÛŒÙ„ØªØ± ÙˆØ¶Ø¹ÛŒØª -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <!-- Ø¬Ø³ØªØ¬Ùˆ -->
            <div class="md:col-span-2">
                <label class="block text-white/70 text-sm mb-2">Ø¬Ø³ØªØ¬Ùˆ</label>
                <div class="relative">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40">
                        <i class="fas fa-search"></i>
                    </span>
                    <input 
                        type="text" 
                        name="search" 
                        value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                        placeholder="Ø¬Ø³ØªØ¬Ùˆ Ø¨Ø± Ø§Ø³Ø§Ø³ Ù†Ø§Ù…ØŒ ÛŒÙˆØ²Ø±Ù†ÛŒÙ… ÛŒØ§ Ø¢ÛŒØ¯ÛŒ..."
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 pr-10 pl-4 text-white placeholder-white/40 focus:border-purple-500 transition"
                    >
                </div>
            </div>
            
            <!-- ÙÛŒÙ„ØªØ± ÙˆØ¶Ø¹ÛŒØª -->
            <div>
                <label class="block text-white/70 text-sm mb-2">ÙˆØ¶Ø¹ÛŒØª</label>
                <select 
                    name="status" 
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                >
                    <option value="" <?= empty($filters['status']) ? 'selected' : '' ?>>Ù‡Ù…Ù‡</option>
                    <option value="vip" <?= ($filters['status'] ?? '') === 'vip' ? 'selected' : '' ?>>ðŸ‘‘ VIP</option>
                    <option value="blocked" <?= ($filters['status'] ?? '') === 'blocked' ? 'selected' : '' ?>>ðŸš« Ø¨Ù„Ø§Ú© Ø´Ø¯Ù‡</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>ðŸŸ¢ ÙØ¹Ø§Ù„</option>
                    <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>âšª ØºÛŒØ±ÙØ¹Ø§Ù„</option>
                </select>
            </div>
            
        </div>
        
        <!-- Ø±Ø¯ÛŒÙ Ø¯ÙˆÙ…: Ø¯Ú©Ù…Ù‡â€ŒÙ‡Ø§ -->
        <div class="flex flex-wrap gap-3">
            <button 
                type="submit" 
                class="bg-gradient-to-r from-purple-500 to-blue-500 text-white px-6 py-2.5 rounded-lg hover:opacity-90 transition flex items-center gap-2"
            >
                <i class="fas fa-filter"></i>
                <span>Ø§Ø¹Ù…Ø§Ù„ ÙÛŒÙ„ØªØ±</span>
            </button>
            
            <a 
                href="/admin/users.php" 
                class="bg-white/10 text-white px-6 py-2.5 rounded-lg hover:bg-white/20 transition flex items-center gap-2"
            >
                <i class="fas fa-times"></i>
                <span>Ù¾Ø§Ú© Ú©Ø±Ø¯Ù† ÙÛŒÙ„ØªØ±Ù‡Ø§</span>
            </a>
            
            <button 
                type="button" 
                onclick="exportUsers()" 
                class="bg-green-500/20 border border-green-500/50 text-green-300 px-6 py-2.5 rounded-lg hover:bg-green-500/30 transition flex items-center gap-2"
            >
                <i class="fas fa-download"></i>
                <span>Ø®Ø±ÙˆØ¬ÛŒ CSV</span>
            </button>
        </div>
        
    </form>
</div>

<!-- â•â•â• Ø¹Ù…Ù„ÛŒØ§Øª Ø¯Ø³ØªÙ‡â€ŒØ¬Ù…Ø¹ÛŒ â•â•â• -->
<?php if (!empty($users)): ?>
<div class="glass rounded-2xl p-4 mb-4 hidden" id="bulkActions">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <input 
                type="checkbox" 
                id="selectAll" 
                onchange="toggleSelectAll()"
                class="w-5 h-5 rounded border-white/20 bg-white/10 text-purple-500 focus:ring-purple-500"
            >
            <label for="selectAll" class="text-white text-sm cursor-pointer">Ø§Ù†ØªØ®Ø§Ø¨ Ù‡Ù…Ù‡</label>
            <span class="text-white/50 text-sm">(<span id="selectedCount">0</span> Ø§Ù†ØªØ®Ø§Ø¨ Ø´Ø¯Ù‡)</span>
        </div>
        
        <div class="flex gap-2">
            <button 
                onclick="bulkAction('make_vip')" 
                class="bg-yellow-500/20 border border-yellow-500/50 text-yellow-300 px-4 py-2 rounded-lg hover:bg-yellow-500/30 transition text-sm flex items-center gap-2"
            >
                <i class="fas fa-crown"></i>
                <span>VIP Ú©Ø±Ø¯Ù†</span>
            </button>
            <button 
                onclick="bulkAction('remove_vip')" 
                class="bg-white/10 text-white px-4 py-2 rounded-lg hover:bg-white/20 transition text-sm flex items-center gap-2"
            >
                <i class="fas fa-user-times"></i>
                <span>Ø­Ø°Ù VIP</span>
            </button>
            <button 
                onclick="bulkAction('block')" 
                class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-2 rounded-lg hover:bg-red-500/30 transition text-sm flex items-center gap-2"
            >
                <i class="fas fa-ban"></i>
                <span>Ø¨Ù„Ø§Ú©</span>
            </button>
            <button 
                onclick="bulkAction('unblock')" 
                class="bg-green-500/20 border border-green-500/50 text-green-300 px-4 py-2 rounded-lg hover:bg-green-500/30 transition text-sm flex items-center gap-2"
            >
                <i class="fas fa-check"></i>
                <span>Ø¢Ù†â€ŒØ¨Ù„Ø§Ú©</span>
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- â•â•â• Ø¬Ø¯ÙˆÙ„ Ú©Ø§Ø±Ø¨Ø±Ø§Ù† â•â•â• -->
<div class="glass rounded-2xl overflow-hidden">
    
    <?php if (empty($users)): ?>
    <!-- Empty State -->
    <div class="text-center py-16">
        <div class="text-6xl mb-4">ðŸ‘¥</div>
        <h3 class="text-white text-xl font-bold mb-2">Ù‡Ù†ÙˆØ² Ú©Ø§Ø±Ø¨Ø±ÛŒ Ø«Ø¨Øªâ€ŒÙ†Ø§Ù… Ù†Ú©Ø±Ø¯Ù‡</h3>
        <p class="text-white/50 text-sm mb-6">ÙˆÙ‚ØªÛŒ Ú©Ø§Ø±Ø¨Ø±Ø§Ù† Ø±Ø¨Ø§Øª Ø±Ùˆ Ø§Ø³ØªØ§Ø±Øª Ú©Ù†Ù†ØŒ Ø§ÛŒÙ†Ø¬Ø§ Ù†Ù…Ø§ÛŒØ´ Ø¯Ø§Ø¯Ù‡ Ù…ÛŒâ€ŒØ´Ù†</p>
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
                    <th class="text-right py-3 px-4 text-white/70 font-medium">
                        <input 
                            type="checkbox" 
                            class="user-checkbox w-4 h-4 rounded border-white/20 bg-white/10 text-purple-500"
                            style="display: none;"
                        >
                    </th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">
                        <a href="?sort=id&order=<?= ($filters['sort'] ?? '') === 'id' && ($filters['order'] ?? '') === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:text-white transition flex items-center gap-1">
                            <span>Ø¢ÛŒØ¯ÛŒ</span>
                            <?php if (($filters['sort'] ?? '') === 'id'): ?>
                            <i class="fas fa-sort-<?= ($filters['order'] ?? '') === 'ASC' ? 'up' : 'down' ?> text-xs"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">Ú©Ø§Ø±Ø¨Ø±</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden md:table-cell">
                        <a href="?sort=joined_at&order=<?= ($filters['sort'] ?? '') === 'joined_at' && ($filters['order'] ?? '') === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:text-white transition flex items-center gap-1">
                            <span>ØªØ§Ø±ÛŒØ® Ø¹Ø¶ÙˆÛŒØª</span>
                            <?php if (($filters['sort'] ?? '') === 'joined_at'): ?>
                            <i class="fas fa-sort-<?= ($filters['order'] ?? '') === 'ASC' ? 'up' : 'down' ?> text-xs"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden lg:table-cell">Ø¢Ø®Ø±ÛŒÙ† Ø¨Ø§Ø²Ø¯ÛŒØ¯</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden lg:table-cell">ÙˆØ¶Ø¹ÛŒØª</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">Ø¹Ù…Ù„ÛŒØ§Øª</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr class="border-b border-white/5 hover:bg-white/5 transition">
                    
                    <!-- Checkbox -->
                    <td class="py-3 px-4">
                        <input 
                            type="checkbox" 
                            value="<?= $user['id'] ?>"
                            class="user-checkbox w-4 h-4 rounded border-white/20 bg-white/10 text-purple-500 focus:ring-purple-500"
                            onchange="updateSelectedCount()"
                        >
                    </td>
                    
                    <!-- ID -->
                    <td class="py-3 px-4">
                        <code class="text-white/60 text-xs bg-white/10 px-2 py-1 rounded">
                            <?= $user['id'] ?>
                        </code>
                    </td>
                    
                    <!-- User Info -->
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                <?= strtoupper(substr($user['first_name'] ?? $user['username'] ?? '?', 0, 1)) ?>
                            </div>
                            <div class="min-w-0">
                                <div class="text-white font-medium truncate flex items-center gap-2">
                                    <span><?= htmlspecialchars($user['display_name'] ?? 'Ú©Ø§Ø±Ø¨Ø±') ?></span>
                                    <?php if (!empty($user['is_vip'])): ?>
                                    <span class="text-yellow-400" title="VIP">ðŸ‘‘</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($user['username'])): ?>
                                <a href="https://t.me/<?= htmlspecialchars($user['username']) ?>" target="_blank" class="text-blue-400 text-xs hover:underline truncate block">
                                    @<?= htmlspecialchars($user['username']) ?>
                                </a>
                                <?php else: ?>
                                <span class="text-white/40 text-xs">Ø¨Ø¯ÙˆÙ† ÛŒÙˆØ²Ø±Ù†ÛŒÙ…</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    
                    <!-- Joined Date -->
                    <td class="py-3 px-4 text-white/60 text-xs hidden md:table-cell">
                        <div><?= htmlspecialchars($user['joined_at'] ?? '-') ?></div>
                        <div class="text-white/40"><?= htmlspecialchars($user['joined_ago'] ?? '') ?></div>
                    </td>
                    
                    <!-- Last Seen -->
                    <td class="py-3 px-4 text-white/60 text-xs hidden lg:table-cell">
                        <?php if (!empty($user['last_seen'])): ?>
                        <div class="flex items-center gap-1">
                            <?php if (!empty($user['is_online'])): ?>
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($user['last_seen_ago'] ?? '-') ?></span>
                        </div>
                        <?php else: ?>
                        <span class="text-white/40">Ù‡Ø±Ú¯Ø²</span>
                        <?php endif; ?>
                    </td>
                    
                    <!-- Status -->
                    <td class="py-3 px-4 hidden lg:table-cell">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium
                            <?= !empty($user['blocked']) ? 'bg-red-500/20 text-red-300' : 
                               (!empty($user['is_vip']) ? 'bg-yellow-500/20 text-yellow-300' : 
                               ($user['status_color'] === 'green' ? 'bg-green-500/20 text-green-300' : 
                               'bg-gray-500/20 text-gray-300')) ?>">
                            <?= htmlspecialchars($user['status_icon'] ?? 'â“') ?>
                            <span><?= htmlspecialchars($user['status_text'] ?? 'Ù†Ø§Ù…Ø´Ø®Øµ') ?></span>
                        </span>
                    </td>
                    
                    <!-- Actions -->
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <a 
                                href="/admin/chat.php?id=<?= $user['id'] ?>" 
                                class="bg-blue-500/20 hover:bg-blue-500/30 text-blue-300 p-2 rounded-lg transition"
                                title="Ú†Øª Ø¨Ø§ Ú©Ø§Ø±Ø¨Ø±"
                            >
                                <i class="fas fa-comments text-sm"></i>
                            </a>
                            
                            <button 
                                onclick="toggleVip(<?= $user['id'] ?>, <?= $user['is_vip'] ? 0 : 1 ?>)"
                                class="<?= !empty($user['is_vip']) ? 'bg-yellow-500/20 hover:bg-yellow-500/30 text-yellow-300' : 'bg-white/10 hover:bg-white/20 text-white' ?> p-2 rounded-lg transition"
                                title="<?= !empty($user['is_vip']) ? 'Ø­Ø°Ù VIP' : 'VIP Ú©Ø±Ø¯Ù†' ?>"
                            >
                                <i class="fas fa-crown text-sm"></i>
                            </button>
                            
                            <button 
                                onclick="toggleBlock(<?= $user['id'] ?>, <?= $user['blocked'] ? 0 : 1 ?>)"
                                class="<?= !empty($user['blocked']) ? 'bg-green-500/20 hover:bg-green-500/30 text-green-300' : 'bg-red-500/20 hover:bg-red-500/30 text-red-300' ?> p-2 rounded-lg transition"
                                title="<?= !empty($user['blocked']) ? 'Ø¢Ù†â€ŒØ¨Ù„Ø§Ú©' : 'Ø¨Ù„Ø§Ú©' ?>"
                            >
                                <i class="fas fa-<?= !empty($user['blocked']) ? 'check' : 'ban' ?> text-sm"></i>
                            </button>
                            
                            <button 
                                onclick="deleteUser(<?= $user['id'] ?>)"
                                class="bg-red-500/20 hover:bg-red-500/30 text-red-300 p-2 rounded-lg transition"
                                title="Ø­Ø°Ù Ú©Ø§Ø±Ø¨Ø±"
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
                Ù†Ù…Ø§ÛŒØ´ <?= number_format($pagination['from'] ?? 0) ?> ØªØ§ <?= number_format($pagination['to'] ?? 0) ?> Ø§Ø² <?= number_format($pagination['total']) ?> Ú©Ø§Ø±Ø¨Ø±
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

<!-- â•â•â• JavaScript â•â•â• -->
<script>
// â•â•â• Select All â•â•â•
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    const bulkActions = document.getElementById('bulkActions');
    
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    
    updateSelectedCount();
    
    if (bulkActions) {
        bulkActions.style.display = selectAll.checked || getSelectedCount() > 0 ? 'block' : 'none';
    }
}

// â•â•â• Update Selected Count â•â•â•
function updateSelectedCount() {
    const count = getSelectedCount();
    const countEl = document.getElementById('selectedCount');
    const bulkActions = document.getElementById('bulkActions');
    
    if (countEl) {
        countEl.textContent = count;
    }
    
    if (bulkActions) {
        bulkActions.style.display = count > 0 ? 'block' : 'none';
    }
}

// â•â•â• Get Selected Count â•â•â•
function getSelectedCount() {
    return document.querySelectorAll('.user-checkbox:checked').length;
}

// â•â•â• Get Selected IDs â•â•â•
function getSelectedIds() {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// â•â•â• Bulk Action â•â•â•
async function bulkAction(action) {
    const ids = getSelectedIds();
    
    if (ids.length === 0) {
        showToast('Ù„Ø·ÙØ§Ù‹ Ø­Ø¯Ø§Ù‚Ù„ ÛŒÚ© Ú©Ø§Ø±Ø¨Ø± Ø§Ù†ØªØ®Ø§Ø¨ Ú©Ù†ÛŒØ¯', 'warning');
        return;
    }
    
    const actionTexts = {
        'make_vip': 'VIP Ú©Ø±Ø¯Ù†',
        'remove_vip': 'Ø­Ø°Ù VIP',
        'block': 'Ø¨Ù„Ø§Ú© Ú©Ø±Ø¯Ù†',
        'unblock': 'Ø¢Ù†â€ŒØ¨Ù„Ø§Ú© Ú©Ø±Ø¯Ù†'
    };
    
    if (!confirm(`Ø¢ÛŒØ§ Ù…Ø·Ù…Ø¦Ù† Ù‡Ø³ØªÛŒØ¯ Ú©Ù‡ Ù…ÛŒâ€ŒØ®ÙˆØ§Ù‡ÛŒØ¯ ${ids.length} Ú©Ø§Ø±Ø¨Ø± Ø±Ø§ ${actionTexts[action]} Ú©Ù†ÛŒØ¯ØŸ`)) {
        return;
    }
    
    try {
        const response = await fetch('/admin/api/users/bulk', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ ids, action })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(`${data.affected} Ú©Ø§Ø±Ø¨Ø± Ø¨Ø§ Ù…ÙˆÙÙ‚ÛŒØª ØªØºÛŒÛŒØ± Ú©Ø±Ø¯`, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Ø®Ø·Ø§ Ø¯Ø± Ø¹Ù…Ù„ÛŒØ§Øª', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Toggle VIP â•â•â•
async function toggleVip(userId, newValue) {
    if (!confirm(`Ø¢ÛŒØ§ Ù…Ø·Ù…Ø¦Ù† Ù‡Ø³ØªÛŒØ¯ØŸ`)) {
        return;
    }
    
    try {
        const response = await fetch('/admin/api/users/vip', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ user_id: userId, is_vip: newValue })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(newValue ? 'Ú©Ø§Ø±Ø¨Ø± VIP Ø´Ø¯' : 'VIP Ø­Ø°Ù Ø´Ø¯', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Toggle Block â•â•â•
async function toggleBlock(userId, newValue) {
    if (!confirm(`Ø¢ÛŒØ§ Ù…Ø·Ù…Ø¦Ù† Ù‡Ø³ØªÛŒØ¯ØŸ`)) {
        return;
    }
    
    try {
        const response = await fetch('/admin/api/users/block', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ user_id: userId, blocked: newValue })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(newValue ? 'Ú©Ø§Ø±Ø¨Ø± Ø¨Ù„Ø§Ú© Ø´Ø¯' : 'Ú©Ø§Ø±Ø¨Ø± Ø¢Ù†â€ŒØ¨Ù„Ø§Ú© Ø´Ø¯', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Delete User â•â•â•
async function deleteUser(userId) {
    if (!confirm('Ø¢ÛŒØ§ Ù…Ø·Ù…Ø¦Ù† Ù‡Ø³ØªÛŒØ¯ Ú©Ù‡ Ù…ÛŒâ€ŒØ®ÙˆØ§Ù‡ÛŒØ¯ Ø§ÛŒÙ† Ú©Ø§Ø±Ø¨Ø± Ø±Ø§ Ø­Ø°Ù Ú©Ù†ÛŒØ¯ØŸ\n\nØ§ÛŒÙ† Ø¹Ù…Ù„ ØºÛŒØ±Ù‚Ø§Ø¨Ù„ Ø¨Ø§Ø²Ú¯Ø´Øª Ø§Ø³Øª!')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/api/users/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ user_id: userId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Ú©Ø§Ø±Ø¨Ø± Ø­Ø°Ù Ø´Ø¯', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Export Users â•â•â•
function exportUsers() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = `/admin/api/users/export?${params.toString()}`;
    showToast('Ø¯Ø± Ø­Ø§Ù„ Ø¯Ø§Ù†Ù„ÙˆØ¯ ÙØ§ÛŒÙ„...', 'info');
}
</script>