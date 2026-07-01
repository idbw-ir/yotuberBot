<?php
/**
 * ============================================
 * Donations Management - Ù…Ø¯ÛŒØ±ÛŒØª Ø¯ÙˆÙ†ÛŒØªâ€ŒÙ‡Ø§
 * ============================================
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * 
 * Ù„ÛŒØ³Øª Ø¯ÙˆÙ†ÛŒØªâ€ŒÙ‡Ø§ Ø¨Ø§ ÙÛŒÙ„ØªØ±ØŒ Ø¬Ø³ØªØ¬Ùˆ Ùˆ Ø¹Ù…Ù„ÛŒØ§Øª
 * Ø§Ø² layout Ø§ØµÙ„ÛŒ (admin.php) Ø§Ø³ØªÙØ§Ø¯Ù‡ Ù…ÛŒâ€ŒÚ©Ù†Ù‡
 */

// Ù…ØªØºÛŒØ±Ù‡Ø§ÛŒ Ù…ÙˆØ±Ø¯ Ù†ÛŒØ§Ø² Ø§Ø² Controller:
// - $donations (Ø¢Ø±Ø§ÛŒÙ‡ Ø¯ÙˆÙ†ÛŒØªâ€ŒÙ‡Ø§)
// - $pagination (Ø§Ø·Ù„Ø§Ø¹Ø§Øª ØµÙØ­Ù‡â€ŒØ¨Ù†Ø¯ÛŒ)
// - $filters (ÙÛŒÙ„ØªØ±Ù‡Ø§ÛŒ ÙØ¹Ù„ÛŒ)
// - $stats (Ø¢Ù…Ø§Ø± Ø¯ÙˆÙ†ÛŒØªâ€ŒÙ‡Ø§)

$donations = $donations ?? [];
$pagination = $pagination ?? ['total' => 0, 'current_page' => 1, 'total_pages' => 1];
$filters = $filters ?? ['status' => '', 'gateway' => '', 'min_amount' => '', 'max_amount' => '', 'sort' => 'created_at', 'order' => 'DESC'];
$stats = $stats ?? ['total_amount' => 0, 'total_count' => 0, 'today_amount' => 0, 'month_amount' => 0, 'pending' => 0, 'average' => 0];

// CSRF Token
$csrfToken = $_SESSION['_csrf_token'] ?? '';
?>

<!-- â•â•â• Ø¢Ù…Ø§Ø± Ø³Ø±ÛŒØ¹ â•â•â• -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">ðŸ’°</div>
        <div class="text-white/60 text-xs mb-1">Ù…Ø¬Ù…ÙˆØ¹ Ø¯ÙˆÙ†ÛŒØª</div>
        <div class="text-green-400 text-xl font-bold"><?= number_format($stats['total_amount'] ?? 0) ?></div>
        <div class="text-white/40 text-xs">ØªÙˆÙ…Ø§Ù†</div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">ðŸ’³</div>
        <div class="text-white/60 text-xs mb-1">ØªØ¹Ø¯Ø§Ø¯ Ú©Ù„</div>
        <div class="text-white text-xl font-bold"><?= number_format($stats['total_count'] ?? 0) ?></div>
        <div class="text-white/40 text-xs">Ø¯ÙˆÙ†ÛŒØª</div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">ðŸ“…</div>
        <div class="text-white/60 text-xs mb-1">Ø§Ù…Ø±ÙˆØ²</div>
        <div class="text-purple-400 text-xl font-bold"><?= number_format($stats['today_amount'] ?? 0) ?></div>
        <div class="text-white/40 text-xs">ØªÙˆÙ…Ø§Ù†</div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">ðŸ“Š</div>
        <div class="text-white/60 text-xs mb-1">Ø§ÛŒÙ† Ù…Ø§Ù‡</div>
        <div class="text-blue-400 text-xl font-bold"><?= number_format($stats['month_amount'] ?? 0) ?></div>
        <div class="text-white/40 text-xs">ØªÙˆÙ…Ø§Ù†</div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">â³</div>
        <div class="text-white/60 text-xs mb-1">Ø¯Ø± Ø§Ù†ØªØ¸Ø§Ø±</div>
        <div class="text-yellow-400 text-xl font-bold"><?= number_format($stats['pending'] ?? 0) ?></div>
        <div class="text-white/40 text-xs">Ø¯ÙˆÙ†ÛŒØª</div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">ðŸ’Ž</div>
        <div class="text-white/60 text-xs mb-1">Ù…ÛŒØ§Ù†Ú¯ÛŒÙ†</div>
        <div class="text-white text-xl font-bold"><?= number_format($stats['average'] ?? 0) ?></div>
        <div class="text-white/40 text-xs">ØªÙˆÙ…Ø§Ù†</div>
    </div>
</div>

<!-- â•â•â• ÙÛŒÙ„ØªØ±Ù‡Ø§ Ùˆ Ø¬Ø³ØªØ¬Ùˆ â•â•â• -->
<div class="glass rounded-2xl p-5 mb-6">
    <form method="GET" action="/admin/donations.php" class="space-y-4">
        
        <!-- Ø±Ø¯ÛŒÙ Ø§ÙˆÙ„: ÙˆØ¶Ø¹ÛŒØª Ùˆ Ø¯Ø±Ú¯Ø§Ù‡ -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- ÙÛŒÙ„ØªØ± ÙˆØ¶Ø¹ÛŒØª -->
            <div>
                <label class="block text-white/70 text-sm mb-2">ÙˆØ¶Ø¹ÛŒØª</label>
                <select 
                    name="status" 
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                >
                    <option value="" <?= empty($filters['status']) ? 'selected' : '' ?>>Ù‡Ù…Ù‡</option>
                    <option value="success" <?= ($filters['status'] ?? '') === 'success' ? 'selected' : '' ?>>âœ… Ù…ÙˆÙÙ‚</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>â³ Ø¯Ø± Ø§Ù†ØªØ¸Ø§Ø±</option>
                    <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>âŒ Ù†Ø§Ù…ÙˆÙÙ‚</option>
                </select>
            </div>
            
            <!-- ÙÛŒÙ„ØªØ± Ø¯Ø±Ú¯Ø§Ù‡ -->
            <div>
                <label class="block text-white/70 text-sm mb-2">Ø¯Ø±Ú¯Ø§Ù‡ Ù¾Ø±Ø¯Ø§Ø®Øª</label>
                <select 
                    name="gateway" 
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                >
                    <option value="" <?= empty($filters['gateway']) ? 'selected' : '' ?>>Ù‡Ù…Ù‡</option>
                    <option value="zarinpal" <?= ($filters['gateway'] ?? '') === 'zarinpal' ? 'selected' : '' ?>>ðŸ’³ Ø²Ø±ÛŒÙ†â€ŒÙ¾Ø§Ù„</option>
                    <option value="idpay" <?= ($filters['gateway'] ?? '') === 'idpay' ? 'selected' : '' ?>>ðŸ’° IDPay</option>
                    <option value="nextpay" <?= ($filters['gateway'] ?? '') === 'nextpay' ? 'selected' : '' ?>>ðŸ¦ NextPay</option>
                    <option value="nowpayments" <?= ($filters['gateway'] ?? '') === 'nowpayments' ? 'selected' : '' ?>>â‚¿ NowPayments</option>
                    <option value="manual" <?= ($filters['gateway'] ?? '') === 'manual' ? 'selected' : '' ?>>âœ‹ Ø¯Ø³ØªÛŒ</option>
                </select>
            </div>
            
        </div>
        
        <!-- Ø±Ø¯ÛŒÙ Ø¯ÙˆÙ…: Ø¨Ø§Ø²Ù‡ Ù…Ø¨Ù„Øº -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Ø­Ø¯Ø§Ù‚Ù„ Ù…Ø¨Ù„Øº -->
            <div>
                <label class="block text-white/70 text-sm mb-2">Ø­Ø¯Ø§Ù‚Ù„ Ù…Ø¨Ù„Øº (ØªÙˆÙ…Ø§Ù†)</label>
                <input 
                    type="number" 
                    name="min_amount" 
                    value="<?= htmlspecialchars($filters['min_amount'] ?? '') ?>"
                    placeholder="Ù…Ø«Ù„Ø§Ù‹: 10000"
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition"
                    min="0"
                >
            </div>
            
            <!-- Ø­Ø¯Ø§Ú©Ø«Ø± Ù…Ø¨Ù„Øº -->
            <div>
                <label class="block text-white/70 text-sm mb-2">Ø­Ø¯Ø§Ú©Ø«Ø± Ù…Ø¨Ù„Øº (ØªÙˆÙ…Ø§Ù†)</label>
                <input 
                    type="number" 
                    name="max_amount" 
                    value="<?= htmlspecialchars($filters['max_amount'] ?? '') ?>"
                    placeholder="Ù…Ø«Ù„Ø§Ù‹: 1000000"
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition"
                    min="0"
                >
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
                href="/admin/donations.php" 
                class="bg-white/10 text-white px-6 py-2.5 rounded-lg hover:bg-white/20 transition flex items-center gap-2"
            >
                <i class="fas fa-times"></i>
                <span>Ù¾Ø§Ú© Ú©Ø±Ø¯Ù† ÙÛŒÙ„ØªØ±Ù‡Ø§</span>
            </a>
            
            <button 
                type="button" 
                onclick="exportDonations()" 
                class="bg-green-500/20 border border-green-500/50 text-green-300 px-6 py-2.5 rounded-lg hover:bg-green-500/30 transition flex items-center gap-2"
            >
                <i class="fas fa-download"></i>
                <span>Ø®Ø±ÙˆØ¬ÛŒ CSV</span>
            </button>
            
            <button 
                type="button" 
                onclick="showFinancialReport()" 
                class="bg-blue-500/20 border border-blue-500/50 text-blue-300 px-6 py-2.5 rounded-lg hover:bg-blue-500/30 transition flex items-center gap-2"
            >
                <i class="fas fa-file-invoice"></i>
                <span>Ú¯Ø²Ø§Ø±Ø´ Ù…Ø§Ù„ÛŒ</span>
            </button>
        </div>
        
    </form>
</div>

<!-- â•â•â• Ù„ÛŒØ³Øª Ø¯ÙˆÙ†ÛŒØªâ€ŒÙ‡Ø§ â•â•â• -->
<div class="glass rounded-2xl overflow-hidden">
    
    <?php if (empty($donations)): ?>
    <!-- Empty State -->
    <div class="text-center py-16">
        <div class="text-6xl mb-4">ðŸ’¸</div>
        <h3 class="text-white text-xl font-bold mb-2">Ù‡Ù†ÙˆØ² Ø¯ÙˆÙ†ÛŒØªÛŒ Ø«Ø¨Øª Ù†Ø´Ø¯Ù‡</h3>
        <p class="text-white/50 text-sm mb-6">ÙˆÙ‚ØªÛŒ Ú©Ø§Ø±Ø¨Ø±Ø§Ù† Ø­Ù…Ø§ÛŒØª Ù…Ø§Ù„ÛŒ Ú©Ù†Ù†ØŒ Ø§ÛŒÙ†Ø¬Ø§ Ù†Ù…Ø§ÛŒØ´ Ø¯Ø§Ø¯Ù‡ Ù…ÛŒâ€ŒØ´Ù†</p>
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
                    <th class="text-right py-3 px-4 text-white/70 font-medium">
                        <a href="?sort=amount&order=<?= ($filters['sort'] ?? '') === 'amount' && ($filters['order'] ?? '') === 'ASC' ? 'DESC' : 'ASC' ?>&<?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="hover:text-white transition flex items-center gap-1">
                            <span>Ù…Ø¨Ù„Øº</span>
                            <?php if (($filters['sort'] ?? '') === 'amount'): ?>
                            <i class="fas fa-sort-<?= ($filters['order'] ?? '') === 'ASC' ? 'up' : 'down' ?> text-xs"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden md:table-cell">Ø¯Ø±Ú¯Ø§Ù‡</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">
                        <a href="?sort=status&order=<?= ($filters['sort'] ?? '') === 'status' && ($filters['order'] ?? '') === 'ASC' ? 'DESC' : 'ASC' ?>&<?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="hover:text-white transition flex items-center gap-1">
                            <span>ÙˆØ¶Ø¹ÛŒØª</span>
                            <?php if (($filters['sort'] ?? '') === 'status'): ?>
                            <i class="fas fa-sort-<?= ($filters['order'] ?? '') === 'ASC' ? 'up' : 'down' ?> text-xs"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden lg:table-cell">
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
                <?php foreach ($donations as $d): ?>
                <tr class="border-b border-white/5 hover:bg-white/5 transition">
                    
                    <!-- ID -->
                    <td class="py-3 px-4">
                        <code class="text-white/60 text-xs bg-white/10 px-2 py-1 rounded">
                            #<?= $d['id'] ?>
                        </code>
                    </td>
                    
                    <!-- User -->
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                <?= strtoupper(substr($d['first_name'] ?? $d['username'] ?? '?', 0, 1)) ?>
                            </div>
                            <div class="min-w-0">
                                <div class="text-white text-sm font-medium truncate">
                                    <?= htmlspecialchars($d['user_display_name'] ?? 'Ú©Ø§Ø±Ø¨Ø±') ?>
                                </div>
                                <?php if (!empty($d['username'])): ?>
                                <a href="https://t.me/<?= htmlspecialchars($d['username']) ?>" target="_blank" class="text-blue-400 text-xs hover:underline truncate block">
                                    @<?= htmlspecialchars($d['username']) ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    
                    <!-- Amount -->
                    <td class="py-3 px-4">
                        <div class="text-green-400 font-bold">
                            <?= htmlspecialchars($d['amount_formatted'] ?? number_format($d['amount'] ?? 0)) ?>
                        </div>
                        <div class="text-white/40 text-xs">ØªÙˆÙ…Ø§Ù†</div>
                    </td>
                    
                    <!-- Gateway -->
                    <td class="py-3 px-4 hidden md:table-cell">
                        <div class="flex items-center gap-2">
                            <span class="text-xl"><?= htmlspecialchars($d['gateway_icon'] ?? 'ðŸ’µ') ?></span>
                            <span class="text-white/70 text-xs"><?= htmlspecialchars($d['gateway'] ?? 'Ù†Ø§Ù…Ø´Ø®Øµ') ?></span>
                        </div>
                    </td>
                    
                    <!-- Status -->
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium
                            <?= ($d['status'] ?? '') === 'success' ? 'bg-green-500/20 text-green-300' : 
                               (($d['status'] ?? '') === 'pending' ? 'bg-yellow-500/20 text-yellow-300' : 
                               'bg-red-500/20 text-red-300') ?>">
                            <span><?= htmlspecialchars($d['status_icon'] ?? 'â“') ?></span>
                            <span><?= htmlspecialchars($d['status_text'] ?? 'Ù†Ø§Ù…Ø´Ø®Øµ') ?></span>
                        </span>
                    </td>
                    
                    <!-- Date -->
                    <td class="py-3 px-4 text-white/60 text-xs hidden lg:table-cell">
                        <div><?= htmlspecialchars($d['created_at'] ?? '-') ?></div>
                        <div class="text-white/40"><?= htmlspecialchars($d['time_ago'] ?? '') ?></div>
                    </td>
                    
                    <!-- Actions -->
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <button 
                                onclick="viewDonation(<?= $d['id'] ?>)"
                                class="bg-blue-500/20 hover:bg-blue-500/30 text-blue-300 p-2 rounded-lg transition"
                                title="Ù…Ø´Ø§Ù‡Ø¯Ù‡ Ø¬Ø²Ø¦ÛŒØ§Øª"
                            >
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                            
                            <?php if (($d['status'] ?? '') === 'pending'): ?>
                            <button 
                                onclick="approveDonation(<?= $d['id'] ?>)"
                                class="bg-green-500/20 hover:bg-green-500/30 text-green-300 p-2 rounded-lg transition"
                                title="ØªØ£ÛŒÛŒØ¯"
                            >
                                <i class="fas fa-check text-sm"></i>
                            </button>
                            <button 
                                onclick="rejectDonation(<?= $d['id'] ?>)"
                                class="bg-red-500/20 hover:bg-red-500/30 text-red-300 p-2 rounded-lg transition"
                                title="Ø±Ø¯"
                            >
                                <i class="fas fa-times text-sm"></i>
                            </button>
                            <?php endif; ?>
                            
                            <button 
                                onclick="deleteDonation(<?= $d['id'] ?>)"
                                class="bg-red-500/20 hover:bg-red-500/30 text-red-300 p-2 rounded-lg transition"
                                title="Ø­Ø°Ù"
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
                Ù†Ù…Ø§ÛŒØ´ <?= number_format($pagination['from'] ?? 0) ?> ØªØ§ <?= number_format($pagination['to'] ?? 0) ?> Ø§Ø² <?= number_format($pagination['total']) ?> Ø¯ÙˆÙ†ÛŒØª
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

<!-- â•â•â• Modal for Donation Details â•â•â• -->
<div id="donationModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="glass rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        
        <!-- Modal Header -->
        <div class="p-5 border-b border-white/10 flex items-center justify-between">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                <i class="fas fa-hand-holding-usd"></i>
                <span>Ø¬Ø²Ø¦ÛŒØ§Øª Ø¯ÙˆÙ†ÛŒØª</span>
            </h3>
            <button 
                onclick="closeDonationModal()"
                class="text-white/60 hover:text-white p-2 rounded-lg hover:bg-white/10 transition"
            >
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Content -->
        <div id="donationModalContent" class="p-5">
            <!-- Content will be loaded here -->
        </div>
        
    </div>
</div>

<!-- â•â•â• Modal for Reject Reason â•â•â• -->
<div id="rejectModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="glass rounded-2xl max-w-md w-full">
        
        <div class="p-5 border-b border-white/10">
            <h3 class="text-white font-bold text-lg">âŒ Ø±Ø¯ Ø¯ÙˆÙ†ÛŒØª</h3>
        </div>
        
        <div class="p-5">
            <form id="rejectForm">
                <input type="hidden" id="rejectDonationId" name="donation_id">
                
                <div class="mb-4">
                    <label class="block text-white/70 text-sm mb-2">Ø¯Ù„ÛŒÙ„ Ø±Ø¯ (Ø§Ø®ØªÛŒØ§Ø±ÛŒ)</label>
                    <textarea 
                        id="rejectReason"
                        name="reason"
                        rows="3"
                        placeholder="Ù…Ø«Ù„Ø§Ù‹: Ù¾Ø±Ø¯Ø§Ø®Øª Ù†Ø§Ù…ÙˆÙÙ‚ØŒ Ø§Ø·Ù„Ø§Ø¹Ø§Øª Ù†Ø§Ø¯Ø±Ø³Øª..."
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition resize-none"
                    ></textarea>
                </div>
                
                <div class="flex gap-3">
                    <button 
                        type="button" 
                        onclick="closeRejectModal()"
                        class="flex-1 bg-white/10 text-white px-4 py-2.5 rounded-lg hover:bg-white/20 transition"
                    >
                        Ø§Ù†ØµØ±Ø§Ù
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white px-4 py-2.5 rounded-lg transition"
                    >
                        Ø±Ø¯ Ø¯ÙˆÙ†ÛŒØª
                    </button>
                </div>
            </form>
        </div>
        
    </div>
</div>

<!-- â•â•â• JavaScript â•â•â• -->
<script>
// â•â•â• View Donation Details â•â•â•
async function viewDonation(donationId) {
    try {
        const response = await fetch(`/admin/api/donations/${donationId}`);
        const data = await response.json();
        
        if (data.success) {
            const d = data.data;
            
            const content = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-white/60 text-xs">Ø¢ÛŒØ¯ÛŒ Ø¯ÙˆÙ†ÛŒØª</label>
                            <div class="text-white font-mono">#${d.id}</div>
                        </div>
                        <div>
                            <label class="text-white/60 text-xs">Ø¢ÛŒØ¯ÛŒ Ú©Ø§Ø±Ø¨Ø±</label>
                            <div class="text-white font-mono">${d.user_id}</div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-white/60 text-xs">Ú©Ø§Ø±Ø¨Ø±</label>
                        <div class="text-white font-medium">${d.user_display_name || 'Ú©Ø§Ø±Ø¨Ø±'}</div>
                        ${d.username ? `<a href="https://t.me/${d.username}" target="_blank" class="text-blue-400 text-sm hover:underline">@${d.username}</a>` : ''}
                    </div>
                    
                    <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 text-center">
                        <div class="text-white/60 text-xs mb-1">Ù…Ø¨Ù„Øº Ø¯ÙˆÙ†ÛŒØª</div>
                        <div class="text-green-400 text-3xl font-bold">${d.amount_formatted || number_format(d.amount)} ØªÙˆÙ…Ø§Ù†</div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-white/60 text-xs">Ø¯Ø±Ú¯Ø§Ù‡</label>
                            <div class="text-white flex items-center gap-2">
                                <span class="text-xl">${d.gateway_icon || 'ðŸ’µ'}</span>
                                <span>${d.gateway || 'Ù†Ø§Ù…Ø´Ø®Øµ'}</span>
                            </div>
                        </div>
                        <div>
                            <label class="text-white/60 text-xs">ÙˆØ¶Ø¹ÛŒØª</label>
                            <div class="text-white">${d.status_icon} ${d.status_text}</div>
                        </div>
                    </div>
                    
                    ${d.ref_id ? `
                    <div>
                        <label class="text-white/60 text-xs">Ref ID</label>
                        <div class="text-white font-mono text-sm bg-white/5 rounded p-2">${d.ref_id}</div>
                    </div>
                    ` : ''}
                    
                    ${d.transaction_id ? `
                    <div>
                        <label class="text-white/60 text-xs">Transaction ID</label>
                        <div class="text-white font-mono text-sm bg-white/5 rounded p-2">${d.transaction_id}</div>
                    </div>
                    ` : ''}
                    
                    <div>
                        <label class="text-white/60 text-xs">ØªØ§Ø±ÛŒØ® Ø§ÛŒØ¬Ø§Ø¯</label>
                        <div class="text-white">${d.created_at}</div>
                        <div class="text-white/50 text-sm">${d.time_ago}</div>
                    </div>
                    
                    ${d.approved_at ? `
                    <div>
                        <label class="text-white/60 text-xs">ØªØ§Ø±ÛŒØ® ØªØ£ÛŒÛŒØ¯</label>
                        <div class="text-white">${d.approved_at}</div>
                    </div>
                    ` : ''}
                    
                    ${d.reject_reason ? `
                    <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-3">
                        <label class="text-red-300 text-xs">Ø¯Ù„ÛŒÙ„ Ø±Ø¯</label>
                        <div class="text-white text-sm mt-1">${d.reject_reason}</div>
                    </div>
                    ` : ''}
                    
                    <div class="flex gap-3 pt-4">
                        ${d.status === 'pending' ? `
                        <button onclick="approveDonation(${d.id}, true)" class="flex-1 bg-green-500/20 border border-green-500/50 text-green-300 px-4 py-2.5 rounded-lg hover:bg-green-500/30 transition">
                            <i class="fas fa-check"></i>
                            <span>ØªØ£ÛŒÛŒØ¯</span>
                        </button>
                        <button onclick="showRejectModal(${d.id})" class="flex-1 bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-2.5 rounded-lg hover:bg-red-500/30 transition">
                            <i class="fas fa-times"></i>
                            <span>Ø±Ø¯</span>
                        </button>
                        ` : ''}
                        <a href="/admin/chat.php?id=${d.user_id}" class="flex-1 bg-purple-500/20 border border-purple-500/50 text-purple-300 px-4 py-2.5 rounded-lg hover:bg-purple-500/30 transition text-center">
                            <i class="fas fa-comments"></i>
                            <span>Ú†Øª Ø¨Ø§ Ú©Ø§Ø±Ø¨Ø±</span>
                        </a>
                    </div>
                </div>
            `;
            
            document.getElementById('donationModalContent').innerHTML = content;
            document.getElementById('donationModal').classList.remove('hidden');
            document.getElementById('donationModal').classList.add('flex');
        } else {
            showToast(data.error || 'Ø®Ø·Ø§ Ø¯Ø± Ø¯Ø±ÛŒØ§ÙØª Ø§Ø·Ù„Ø§Ø¹Ø§Øª', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Close Modal â•â•â•
function closeDonationModal() {
    document.getElementById('donationModal').classList.add('hidden');
    document.getElementById('donationModal').classList.remove('flex');
}

// â•â•â• Approve Donation â•â•â•
async function approveDonation(donationId, fromModal = false) {
    if (!confirm('Ø¢ÛŒØ§ Ù…Ø·Ù…Ø¦Ù† Ù‡Ø³ØªÛŒØ¯ Ú©Ù‡ Ù…ÛŒâ€ŒØ®ÙˆØ§Ù‡ÛŒØ¯ Ø§ÛŒÙ† Ø¯ÙˆÙ†ÛŒØª Ø±Ø§ ØªØ£ÛŒÛŒØ¯ Ú©Ù†ÛŒØ¯ØŸ')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/api/donations/approve', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ donation_id: donationId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Ø¯ÙˆÙ†ÛŒØª ØªØ£ÛŒÛŒØ¯ Ø´Ø¯', 'success');
            
            if (fromModal) {
                closeDonationModal();
            }
            
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Show Reject Modal â•â•â•
function showRejectModal(donationId) {
    closeDonationModal();
    document.getElementById('rejectDonationId').value = donationId;
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectModal').classList.add('flex');
}

// â•â•â• Close Reject Modal â•â•â•
function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectModal').classList.remove('flex');
    document.getElementById('rejectForm').reset();
}

// â•â•â• Reject Donation â•â•â•
document.getElementById('rejectForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const donationId = document.getElementById('rejectDonationId').value;
    const reason = document.getElementById('rejectReason').value;
    
    try {
        const response = await fetch('/admin/api/donations/reject', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ donation_id: donationId, reason: reason })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Ø¯ÙˆÙ†ÛŒØª Ø±Ø¯ Ø´Ø¯', 'success');
            closeRejectModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
});

// â•â•â• Delete Donation â•â•â•
async function deleteDonation(donationId) {
    if (!confirm('Ø¢ÛŒØ§ Ù…Ø·Ù…Ø¦Ù† Ù‡Ø³ØªÛŒØ¯ Ú©Ù‡ Ù…ÛŒâ€ŒØ®ÙˆØ§Ù‡ÛŒØ¯ Ø§ÛŒÙ† Ø¯ÙˆÙ†ÛŒØª Ø±Ø§ Ø­Ø°Ù Ú©Ù†ÛŒØ¯ØŸ\n\nØ§ÛŒÙ† Ø¹Ù…Ù„ ØºÛŒØ±Ù‚Ø§Ø¨Ù„ Ø¨Ø§Ø²Ú¯Ø´Øª Ø§Ø³Øª!')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/api/donations/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ donation_id: donationId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Ø¯ÙˆÙ†ÛŒØª Ø­Ø°Ù Ø´Ø¯', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Export Donations â•â•â•
function exportDonations() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = `/admin/api/donations/export?${params.toString()}`;
    showToast('Ø¯Ø± Ø­Ø§Ù„ Ø¯Ø§Ù†Ù„ÙˆØ¯ ÙØ§ÛŒÙ„...', 'info');
}

// â•â•â• Show Financial Report â•â•â•
function showFinancialReport() {
    const from = prompt('ØªØ§Ø±ÛŒØ® Ø´Ø±ÙˆØ¹ (YYYY-MM-DD):', new Date().toISOString().split('T')[0]);
    if (!from) return;
    
    const to = prompt('ØªØ§Ø±ÛŒØ® Ù¾Ø§ÛŒØ§Ù† (YYYY-MM-DD):', new Date().toISOString().split('T')[0]);
    if (!to) return;
    
    window.location.href = `/admin/api/donations/financial-report?from=${from}&to=${to}`;
    showToast('Ø¯Ø± Ø­Ø§Ù„ Ø¯Ø§Ù†Ù„ÙˆØ¯ Ú¯Ø²Ø§Ø±Ø´...', 'info');
}

// â•â•â• Close Modals on Escape â•â•â•
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDonationModal();
        closeRejectModal();
    }
});

// â•â•â• Close Modals on Outside Click â•â•â•
document.getElementById('donationModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeDonationModal();
    }
});

document.getElementById('rejectModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>