<?php
/**
 * ============================================
 * Broadcast Management - Ù…Ø¯ÛŒØ±ÛŒØª Ø§Ø±Ø³Ø§Ù„ Ø¯Ø³ØªÙ‡â€ŒØ¬Ù…Ø¹ÛŒ
 * ============================================
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * 
 * Ø§ÛŒØ¬Ø§Ø¯ØŒ Ú©Ù†ØªØ±Ù„ Ùˆ Ù†Ø¸Ø§Ø±Øª Ø¨Ø± Ø§Ø±Ø³Ø§Ù„ Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§ÛŒ Ø¯Ø³ØªÙ‡â€ŒØ¬Ù…Ø¹ÛŒ
 * Ù¾Ø´ØªÛŒØ¨Ø§Ù†ÛŒ Ø§Ø² Ø§Ù†ÙˆØ§Ø¹ Ù…Ø­ØªÙˆØ§ Ùˆ Ú¯Ø±ÙˆÙ‡â€ŒÙ‡Ø§ÛŒ Ù‡Ø¯Ù
 */

// Ù…ØªØºÛŒØ±Ù‡Ø§ÛŒ Ù…ÙˆØ±Ø¯ Ù†ÛŒØ§Ø² Ø§Ø² Controller:
// - $broadcasts (Ù„ÛŒØ³Øª Broadcast Ù‡Ø§)
// - $pagination (Ø§Ø·Ù„Ø§Ø¹Ø§Øª ØµÙØ­Ù‡â€ŒØ¨Ù†Ø¯ÛŒ)
// - $stats (Ø¢Ù…Ø§Ø± Ú©Ù„ÛŒ)

$broadcasts = $broadcasts ?? [];
$pagination = $pagination ?? ['total' => 0, 'current_page' => 1, 'total_pages' => 1];
$stats = $stats ?? ['total_broadcasts' => 0, 'total_sent' => 0, 'total_failed' => 0, 'success_rate' => 0];

// CSRF Token
$csrfToken = $_SESSION['_csrf_token'] ?? '';
?>

<!-- â•â•â• Ø¢Ù…Ø§Ø± Ø³Ø±ÛŒØ¹ â•â•â• -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">ðŸ“¢</div>
        <div class="text-white/60 text-xs mb-1">Ú©Ù„ Broadcast Ù‡Ø§</div>
        <div class="text-white text-2xl font-bold"><?= number_format($stats['total_broadcasts'] ?? 0) ?></div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">âœ…</div>
        <div class="text-white/60 text-xs mb-1">Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§ÛŒ Ù…ÙˆÙÙ‚</div>
        <div class="text-green-400 text-2xl font-bold"><?= number_format($stats['total_sent'] ?? 0) ?></div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">âŒ</div>
        <div class="text-white/60 text-xs mb-1">Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§ÛŒ Ù†Ø§Ù…ÙˆÙÙ‚</div>
        <div class="text-red-400 text-2xl font-bold"><?= number_format($stats['total_failed'] ?? 0) ?></div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">ðŸ“Š</div>
        <div class="text-white/60 text-xs mb-1">Ù†Ø±Ø® Ù…ÙˆÙÙ‚ÛŒØª</div>
        <div class="text-purple-400 text-2xl font-bold"><?= number_format($stats['success_rate'] ?? 0, 1) ?>%</div>
    </div>
</div>

<!-- â•â•â• Ø¯Ú©Ù…Ù‡ Ø§ÛŒØ¬Ø§Ø¯ Broadcast â•â•â• -->
<div class="mb-6">
    <button 
        onclick="openCreateModal()"
        class="bg-gradient-to-r from-purple-500 to-blue-500 text-white px-6 py-3 rounded-lg hover:opacity-90 transition flex items-center gap-2 shadow-lg"
    >
        <i class="fas fa-plus-circle text-lg"></i>
        <span class="font-bold">Ø§ÛŒØ¬Ø§Ø¯ Broadcast Ø¬Ø¯ÛŒØ¯</span>
    </button>
</div>

<!-- â•â•â• Ù„ÛŒØ³Øª Broadcast Ù‡Ø§ â•â•â• -->
<div class="glass rounded-2xl overflow-hidden">
    
    <?php if (empty($broadcasts)): ?>
    <!-- Empty State -->
    <div class="text-center py-16">
        <div class="text-6xl mb-4">ðŸ“¢</div>
        <h3 class="text-white text-xl font-bold mb-2">Ù‡Ù†ÙˆØ² Broadcast Ø§ÛŒ Ø§ÛŒØ¬Ø§Ø¯ Ù†Ø´Ø¯Ù‡</h3>
        <p class="text-white/50 text-sm mb-6">Ø§ÙˆÙ„ÛŒÙ† Broadcast Ø®ÙˆØ¯ Ø±Ø§ Ø§ÛŒØ¬Ø§Ø¯ Ú©Ù†ÛŒØ¯ Ùˆ Ù¾ÛŒØ§Ù… Ø±Ø§ Ø¨Ù‡ Ú©Ø§Ø±Ø¨Ø±Ø§Ù† Ø§Ø±Ø³Ø§Ù„ Ú©Ù†ÛŒØ¯</p>
        <button 
            onclick="openCreateModal()"
            class="inline-block bg-purple-500/20 border border-purple-500/50 text-purple-300 px-6 py-2.5 rounded-lg hover:bg-purple-500/30 transition"
        >
            Ø§ÛŒØ¬Ø§Ø¯ Ø§ÙˆÙ„ÛŒÙ† Broadcast
        </button>
    </div>
    <?php else: ?>
    
    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-white/5 border-b border-white/10">
                    <th class="text-right py-3 px-4 text-white/70 font-medium">Ø¢ÛŒØ¯ÛŒ</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">Ø¹Ù†ÙˆØ§Ù†</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden md:table-cell">Ú¯Ø±ÙˆÙ‡ Ù‡Ø¯Ù</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden lg:table-cell">ØªØ¹Ø¯Ø§Ø¯ Ù‡Ø¯Ù</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">ÙˆØ¶Ø¹ÛŒØª</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden lg:table-cell">Ù¾ÛŒØ´Ø±ÙØª</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden md:table-cell">
                        <span>ØªØ§Ø±ÛŒØ®</span>
                    </th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">Ø¹Ù…Ù„ÛŒØ§Øª</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($broadcasts as $b): ?>
                <tr class="border-b border-white/5 hover:bg-white/5 transition">
                    
                    <!-- ID -->
                    <td class="py-3 px-4">
                        <code class="text-white/60 text-xs bg-white/10 px-2 py-1 rounded">
                            #<?= $b['id'] ?>
                        </code>
                    </td>
                    
                    <!-- Title -->
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <span class="text-lg"><?= $b['type_icon'] ?? 'ðŸ’¬' ?></span>
                            <div>
                                <div class="text-white font-medium">
                                    <?= htmlspecialchars($b['title'] ?? 'Ø¨Ø¯ÙˆÙ† Ø¹Ù†ÙˆØ§Ù†') ?>
                                </div>
                                <div class="text-white/50 text-xs truncate max-w-xs">
                                    <?= htmlspecialchars($b['content_preview'] ?? '') ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    
                    <!-- Target -->
                    <td class="py-3 px-4 hidden md:table-cell">
                        <span class="text-white/70 text-sm">
                            <?= htmlspecialchars($b['target_text'] ?? 'Ù†Ø§Ù…Ø´Ø®Øµ') ?>
                        </span>
                    </td>
                    
                    <!-- Target Count -->
                    <td class="py-3 px-4 hidden lg:table-cell">
                        <div class="text-white font-bold">
                            <?= number_format($b['target_count'] ?? 0) ?>
                        </div>
                        <div class="text-white/40 text-xs">Ú©Ø§Ø±Ø¨Ø±</div>
                    </td>
                    
                    <!-- Status -->
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium
                            <?= $b['status'] === 'completed' ? 'bg-green-500/20 text-green-300' : 
                               ($b['status'] === 'running' ? 'bg-blue-500/20 text-blue-300' : 
                               ($b['status'] === 'paused' ? 'bg-yellow-500/20 text-yellow-300' : 
                               ($b['status'] === 'cancelled' ? 'bg-red-500/20 text-red-300' : 
                               'bg-gray-500/20 text-gray-300'))) ?>">
                            <span><?= htmlspecialchars($b['status_icon'] ?? 'â“') ?></span>
                            <span><?= htmlspecialchars($b['status_text'] ?? 'Ù†Ø§Ù…Ø´Ø®Øµ') ?></span>
                        </span>
                    </td>
                    
                    <!-- Progress -->
                    <td class="py-3 px-4 hidden lg:table-cell">
                        <div class="w-32">
                            <div class="flex justify-between text-xs text-white/60 mb-1">
                                <span><?= number_format(($b['sent_count'] ?? 0) + ($b['failed_count'] ?? 0) + ($b['blocked_count'] ?? 0)) ?></span>
                                <span><?= number_format($b['target_count'] ?? 0) ?></span>
                            </div>
                            <div class="w-full bg-white/10 rounded-full h-2">
                                <div 
                                    class="bg-gradient-to-r from-purple-500 to-blue-500 h-2 rounded-full transition-all"
                                    style="width: <?= $b['progress_percent'] ?? 0 ?>%"
                                ></div>
                            </div>
                            <div class="text-white/40 text-xs mt-1">
                                <?= number_format($b['progress_percent'] ?? 0, 1) ?>%
                            </div>
                        </div>
                    </td>
                    
                    <!-- Date -->
                    <td class="py-3 px-4 text-white/60 text-xs hidden md:table-cell">
                        <div><?= htmlspecialchars($b['created_at'] ?? '-') ?></div>
                        <div class="text-white/40"><?= htmlspecialchars($b['created_ago'] ?? '') ?></div>
                    </td>
                    
                    <!-- Actions -->
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <button 
                                onclick="viewBroadcast(<?= $b['id'] ?>)"
                                class="bg-blue-500/20 hover:bg-blue-500/30 text-blue-300 p-2 rounded-lg transition"
                                title="Ù…Ø´Ø§Ù‡Ø¯Ù‡ Ø¬Ø²Ø¦ÛŒØ§Øª"
                            >
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                            
                            <?php if ($b['status'] === 'pending'): ?>
                            <button 
                                onclick="startBroadcast(<?= $b['id'] ?>)"
                                class="bg-green-500/20 hover:bg-green-500/30 text-green-300 p-2 rounded-lg transition"
                                title="Ø´Ø±ÙˆØ¹"
                            >
                                <i class="fas fa-play text-sm"></i>
                            </button>
                            <?php elseif ($b['status'] === 'running'): ?>
                            <button 
                                onclick="pauseBroadcast(<?= $b['id'] ?>)"
                                class="bg-yellow-500/20 hover:bg-yellow-500/30 text-yellow-300 p-2 rounded-lg transition"
                                title="ØªÙˆÙ‚Ù"
                            >
                                <i class="fas fa-pause text-sm"></i>
                            </button>
                            <?php elseif ($b['status'] === 'paused'): ?>
                            <button 
                                onclick="resumeBroadcast(<?= $b['id'] ?>)"
                                class="bg-green-500/20 hover:bg-green-500/30 text-green-300 p-2 rounded-lg transition"
                                title="Ø§Ø¯Ø§Ù…Ù‡"
                            >
                                <i class="fas fa-play text-sm"></i>
                            </button>
                            <?php endif; ?>
                            
                            <?php if (in_array($b['status'], ['pending', 'paused'])): ?>
                            <button 
                                onclick="cancelBroadcast(<?= $b['id'] ?>)"
                                class="bg-red-500/20 hover:bg-red-500/30 text-red-300 p-2 rounded-lg transition"
                                title="Ù„ØºÙˆ"
                            >
                                <i class="fas fa-stop text-sm"></i>
                            </button>
                            <?php endif; ?>
                            
                            <?php if (in_array($b['status'], ['completed', 'cancelled'])): ?>
                            <button 
                                onclick="duplicateBroadcast(<?= $b['id'] ?>)"
                                class="bg-purple-500/20 hover:bg-purple-500/30 text-purple-300 p-2 rounded-lg transition"
                                title="ØªÚ©Ø±Ø§Ø±"
                            >
                                <i class="fas fa-copy text-sm"></i>
                            </button>
                            
                            <button 
                                onclick="deleteBroadcast(<?= $b['id'] ?>)"
                                class="bg-red-500/20 hover:bg-red-500/30 text-red-300 p-2 rounded-lg transition"
                                title="Ø­Ø°Ù"
                            >
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                            <?php endif; ?>
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
            
            <div class="text-white/60 text-sm">
                Ù†Ù…Ø§ÛŒØ´ <?= number_format($pagination['from'] ?? 0) ?> ØªØ§ <?= number_format($pagination['to'] ?? 0) ?> Ø§Ø² <?= number_format($pagination['total']) ?> Broadcast
            </div>
            
            <div class="flex items-center gap-2">
                
                <?php if ($pagination['current_page'] > 1): ?>
                <a 
                    href="?page=<?= $pagination['current_page'] - 1 ?>"
                    class="bg-white/10 hover:bg-white/20 text-white px-3 py-2 rounded-lg transition flex items-center gap-1"
                >
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="hidden sm:inline">Ù‚Ø¨Ù„ÛŒ</span>
                </a>
                <?php endif; ?>
                
                <?php
                $startPage = max(1, $pagination['current_page'] - 2);
                $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                
                for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a 
                    href="?page=<?= $i ?>"
                    class="<?= $i === $pagination['current_page'] ? 'bg-purple-500 text-white' : 'bg-white/10 hover:bg-white/20 text-white' ?> px-3 py-2 rounded-lg transition"
                >
                    <?= $i ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <a 
                    href="?page=<?= $pagination['current_page'] + 1 ?>"
                    class="bg-white/10 hover:bg-white/20 text-white px-3 py-2 rounded-lg transition flex items-center gap-1"
                >
                    <span class="hidden sm:inline">Ø¨Ø¹Ø¯ÛŒ</span>
                    <i class="fas fa-chevron-left text-xs"></i>
                </a>
                <?php endif; ?>
                
            </div>
            
        </div>
    </div>
    <?php endif; ?>
    
    <?php endif; ?>
    
</div>

<!-- â•â•â• Modal for Create Broadcast â•â•â• -->
<div id="createModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="glass rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        
        <div class="p-5 border-b border-white/10 flex items-center justify-between">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                <i class="fas fa-bullhorn"></i>
                <span>Ø§ÛŒØ¬Ø§Ø¯ Broadcast Ø¬Ø¯ÛŒØ¯</span>
            </h3>
            <button 
                onclick="closeCreateModal()"
                class="text-white/60 hover:text-white p-2 rounded-lg hover:bg-white/10 transition"
            >
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="p-5">
            <form id="createForm">
                
                <!-- Title -->
                <div class="mb-4">
                    <label class="block text-white/70 text-sm mb-2">Ø¹Ù†ÙˆØ§Ù† <span class="text-red-400">*</span></label>
                    <input 
                        type="text" 
                        id="broadcastTitle"
                        name="title"
                        required
                        placeholder="Ù…Ø«Ù„Ø§Ù‹: ÙˆÛŒØ¯Ø¦ÙˆÛŒ Ø¬Ø¯ÛŒØ¯ØŒ ØªØ®ÙÛŒÙ ÙˆÛŒÚ˜Ù‡"
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition"
                    >
                </div>
                
                <!-- Content Type -->
                <div class="mb-4">
                    <label class="block text-white/70 text-sm mb-2">Ù†ÙˆØ¹ Ù…Ø­ØªÙˆØ§</label>
                    <select 
                        id="contentType"
                        name="content_type"
                        onchange="toggleFileIdField()"
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                    >
                        <option value="text">ðŸ’¬ Ù…ØªÙ†ÛŒ</option>
                        <option value="photo">ðŸ–¼ï¸ Ø¹Ú©Ø³</option>
                        <option value="video">ðŸŽ¥ ÙˆÛŒØ¯Ø¦Ùˆ</option>
                        <option value="document">ðŸ“„ ÙØ§ÛŒÙ„</option>
                        <option value="audio">ðŸŽµ ØµØ¯Ø§</option>
                        <option value="voice">ðŸŽ¤ ÙˆÛŒØ³</option>
                        <option value="sticker">ðŸŽ­ Ø§Ø³ØªÛŒÚ©Ø±</option>
                    </select>
                </div>
                
                <!-- Content -->
                <div class="mb-4">
                    <label class="block text-white/70 text-sm mb-2">Ù…Ø­ØªÙˆØ§ÛŒ Ù¾ÛŒØ§Ù… <span class="text-red-400">*</span></label>
                    <textarea 
                        id="broadcastContent"
                        name="content"
                        rows="6"
                        required
                        placeholder="Ù…ØªÙ† Ù¾ÛŒØ§Ù… Ø®ÙˆØ¯ Ø±Ø§ Ø¨Ù†ÙˆÛŒØ³ÛŒØ¯...&#10;&#10;Ù…ÛŒâ€ŒØªÙˆØ§Ù†ÛŒØ¯ Ø§Ø² Ù…ØªØºÛŒØ±Ù‡Ø§ÛŒ Ø²ÛŒØ± Ø§Ø³ØªÙØ§Ø¯Ù‡ Ú©Ù†ÛŒØ¯:&#10;{first_name} - Ù†Ø§Ù… Ú©Ø§Ø±Ø¨Ø±&#10;{username} - ÛŒÙˆØ²Ø±Ù†ÛŒÙ…&#10;{user_id} - Ø¢ÛŒØ¯ÛŒ Ú©Ø§Ø±Ø¨Ø±"
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition resize-none font-mono text-sm"
                    ></textarea>
                    <p class="text-white/40 text-xs mt-1">Ù…ÛŒâ€ŒØªÙˆØ§Ù†ÛŒØ¯ Ø§Ø² HTML Ùˆ Ù…ØªØºÛŒØ±Ù‡Ø§ÛŒ Ù‚Ø§Ù„Ø¨ Ø§Ø³ØªÙØ§Ø¯Ù‡ Ú©Ù†ÛŒØ¯</p>
                </div>
                
                <!-- File ID -->
                <div class="mb-4 hidden" id="fileIdGroup">
                    <label class="block text-white/70 text-sm mb-2">File ID</label>
                    <input 
                        type="text" 
                        id="fileIdInput"
                        name="file_id"
                        placeholder="File ID Ø§Ø² ØªÙ„Ú¯Ø±Ø§Ù…..."
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition font-mono text-sm"
                        dir="ltr"
                    >
                </div>
                
                <!-- Target -->
                <div class="mb-4">
                    <label class="block text-white/70 text-sm mb-2">Ú¯Ø±ÙˆÙ‡ Ù‡Ø¯Ù <span class="text-red-400">*</span></label>
                    <select 
                        id="targetGroup"
                        name="target"
                        onchange="toggleTargetOptions()"
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                    >
                        <option value="all">ðŸ‘¥ Ù‡Ù…Ù‡ Ú©Ø§Ø±Ø¨Ø±Ø§Ù†</option>
                        <option value="vip">ðŸ‘‘ Ú©Ø§Ø±Ø¨Ø±Ø§Ù† VIP</option>
                        <option value="non_vip">ðŸ‘¤ Ú©Ø§Ø±Ø¨Ø±Ø§Ù† Ø¹Ø§Ø¯ÛŒ</option>
                        <option value="active">ðŸŸ¢ Ú©Ø§Ø±Ø¨Ø±Ø§Ù† ÙØ¹Ø§Ù„</option>
                        <option value="inactive">âšª Ú©Ø§Ø±Ø¨Ø±Ø§Ù† ØºÛŒØ±ÙØ¹Ø§Ù„</option>
                        <option value="new">ðŸ†• Ú©Ø§Ø±Ø¨Ø±Ø§Ù† Ø¬Ø¯ÛŒØ¯</option>
                        <option value="donors">ðŸ’° Ø­Ø§Ù…ÛŒØ§Ù† Ù…Ø§Ù„ÛŒ</option>
                        <option value="non_donors">âŒ ØºÛŒØ± Ø­Ø§Ù…ÛŒ</option>
                        <option value="custom">ðŸŽ¯ Ú©Ø§Ø±Ø¨Ø±Ø§Ù† Ø®Ø§Øµ</option>
                    </select>
                </div>
                
                <!-- Target Options -->
                <div id="targetOptions" class="mb-4 hidden">
                    <!-- Dynamic options based on target -->
                </div>
                
                <!-- Target Count Preview -->
                <div class="mb-4 bg-purple-500/10 border border-purple-500/30 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-white/70 text-sm">ØªØ¹Ø¯Ø§Ø¯ Ú©Ø§Ø±Ø¨Ø±Ø§Ù† Ù‡Ø¯Ù:</span>
                        <span id="targetCount" class="text-purple-400 font-bold text-lg">-</span>
                    </div>
                    <button 
                        type="button"
                        onclick="updateTargetCount()"
                        class="mt-2 text-xs text-purple-300 hover:text-purple-200 transition"
                    >
                        <i class="fas fa-sync-alt"></i>
                        <span>Ø¨Ø±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ</span>
                    </button>
                </div>
                
                <!-- Delay -->
                <div class="mb-4">
                    <label class="block text-white/70 text-sm mb-2">ØªØ£Ø®ÛŒØ± Ø¨ÛŒÙ† Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§ (Ù…ÛŒÙ„ÛŒâ€ŒØ«Ø§Ù†ÛŒÙ‡)</label>
                    <input 
                        type="number" 
                        id="delayInput"
                        name="delay"
                        value="50"
                        min="0"
                        max="1000"
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                    >
                    <p class="text-white/40 text-xs mt-1">Ù¾ÛŒØ´Ù†Ù‡Ø§Ø¯: 50-100 Ù…ÛŒÙ„ÛŒâ€ŒØ«Ø§Ù†ÛŒÙ‡</p>
                </div>
                
                <!-- Preview Button -->
                <button 
                    type="button"
                    onclick="previewBroadcast()"
                    class="w-full bg-blue-500/20 border border-blue-500/50 text-blue-300 px-4 py-2.5 rounded-lg hover:bg-blue-500/30 transition mb-4"
                >
                    <i class="fas fa-eye"></i>
                    <span>Ù¾ÛŒØ´â€ŒÙ†Ù…Ø§ÛŒØ´ Ù¾ÛŒØ§Ù…</span>
                </button>
                
                <!-- Preview Area -->
                <div id="previewArea" class="mb-4 hidden">
                    <!-- Preview will be loaded here -->
                </div>
                
                <!-- Buttons -->
                <div class="flex gap-3">
                    <button 
                        type="button" 
                        onclick="closeCreateModal()"
                        class="flex-1 bg-white/10 text-white px-4 py-2.5 rounded-lg hover:bg-white/20 transition"
                    >
                        Ø§Ù†ØµØ±Ø§Ù
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 bg-gradient-to-r from-purple-500 to-blue-500 text-white px-4 py-2.5 rounded-lg hover:opacity-90 transition"
                    >
                        <i class="fas fa-save"></i>
                        <span>Ø°Ø®ÛŒØ±Ù‡ Ùˆ Ø§ÛŒØ¬Ø§Ø¯</span>
                    </button>
                </div>
                
            </form>
        </div>
        
    </div>
</div>

<!-- â•â•â• Modal for Broadcast Details â•â•â• -->
<div id="detailsModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="glass rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        
        <div class="p-5 border-b border-white/10 flex items-center justify-between">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                <i class="fas fa-info-circle"></i>
                <span>Ø¬Ø²Ø¦ÛŒØ§Øª Broadcast</span>
            </h3>
            <button 
                onclick="closeDetailsModal()"
                class="text-white/60 hover:text-white p-2 rounded-lg hover:bg-white/10 transition"
            >
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="detailsModalContent" class="p-5">
            <!-- Details will be loaded here -->
        </div>
        
    </div>
</div>

<!-- â•â•â• JavaScript â•â•â• -->
<script>
// â•â•â• Toggle File ID Field â•â•â•
function toggleFileIdField() {
    const type = document.getElementById('contentType').value;
    const fileIdGroup = document.getElementById('fileIdGroup');
    
    if (type === 'text') {
        fileIdGroup.classList.add('hidden');
    } else {
        fileIdGroup.classList.remove('hidden');
    }
}

// â•â•â• Toggle Target Options â•â•â•
function toggleTargetOptions() {
    const target = document.getElementById('targetGroup').value;
    const optionsDiv = document.getElementById('targetOptions');
    
    let html = '';
    
    switch (target) {
        case 'active':
            html = `
                <label class="block text-white/70 text-sm mb-2">ÙØ¹Ø§Ù„ Ø¯Ø± Ú†Ù†Ø¯ Ø±ÙˆØ² Ø§Ø®ÛŒØ±</label>
                <input 
                    type="number" 
                    name="active_days"
                    value="7"
                    min="1"
                    max="365"
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                >
            `;
            break;
            
        case 'inactive':
            html = `
                <label class="block text-white/70 text-sm mb-2">ØºÛŒØ±ÙØ¹Ø§Ù„ Ø¨ÛŒØ´ Ø§Ø² Ú†Ù†Ø¯ Ø±ÙˆØ²</label>
                <input 
                    type="number" 
                    name="inactive_days"
                    value="30"
                    min="1"
                    max="365"
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                >
            `;
            break;
            
        case 'new':
            html = `
                <label class="block text-white/70 text-sm mb-2">Ø¬Ø¯ÛŒØ¯ Ø¯Ø± Ú†Ù†Ø¯ Ø±ÙˆØ² Ø§Ø®ÛŒØ±</label>
                <input 
                    type="number" 
                    name="new_days"
                    value="7"
                    min="1"
                    max="365"
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                >
            `;
            break;
            
        case 'donors':
            html = `
                <label class="block text-white/70 text-sm mb-2">Ø­Ø¯Ø§Ù‚Ù„ Ù…Ø¨Ù„Øº Ø¯ÙˆÙ†ÛŒØª (ØªÙˆÙ…Ø§Ù†)</label>
                <input 
                    type="number" 
                    name="min_donation"
                    value="1"
                    min="1"
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                >
            `;
            break;
            
        case 'custom':
            html = `
                <label class="block text-white/70 text-sm mb-2">Ø¢ÛŒØ¯ÛŒ Ú©Ø§Ø±Ø¨Ø±Ø§Ù† (Ø¨Ø§ Ú©Ø§Ù…Ø§ Ø¬Ø¯Ø§ Ú©Ù†ÛŒØ¯)</label>
                <textarea 
                    name="user_ids"
                    rows="3"
                    placeholder="123456789, 987654321, 555555555"
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition resize-none font-mono text-sm"
                    dir="ltr"
                ></textarea>
            `;
            break;
    }
    
    if (html) {
        optionsDiv.innerHTML = html;
        optionsDiv.classList.remove('hidden');
    } else {
        optionsDiv.innerHTML = '';
        optionsDiv.classList.add('hidden');
    }
    
    updateTargetCount();
}

// â•â•â• Update Target Count â•â•â•
async function updateTargetCount() {
    const target = document.getElementById('targetGroup').value;
    const options = {};
    
    // Ø¬Ù…Ø¹â€ŒØ¢ÙˆØ±ÛŒ options
    const activeDays = document.querySelector('[name="active_days"]');
    const inactiveDays = document.querySelector('[name="inactive_days"]');
    const newDays = document.querySelector('[name="new_days"]');
    const minDonation = document.querySelector('[name="min_donation"]');
    const userIds = document.querySelector('[name="user_ids"]');
    
    if (activeDays) options.active_days = parseInt(activeDays.value);
    if (inactiveDays) options.inactive_days = parseInt(inactiveDays.value);
    if (newDays) options.new_days = parseInt(newDays.value);
    if (minDonation) options.min_donation = parseInt(minDonation.value);
    if (userIds) {
        options.user_ids = userIds.value.split(',').map(id => parseInt(id.trim())).filter(id => id > 0);
    }
    
    try {
        const response = await fetch('/admin/api/broadcast/count-target', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ target, options })
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('targetCount').textContent = new Intl.NumberFormat('fa-IR').format(data.count);
        } else {
            document.getElementById('targetCount').textContent = '-';
        }
    } catch (error) {
        document.getElementById('targetCount').textContent = '-';
    }
}

// â•â•â• Preview Broadcast â•â•â•
async function previewBroadcast() {
    const content = document.getElementById('broadcastContent').value;
    const target = document.getElementById('targetGroup').value;
    
    if (!content) {
        showToast('Ù„Ø·ÙØ§Ù‹ Ù…Ø­ØªÙˆØ§ÛŒ Ù¾ÛŒØ§Ù… Ø±Ø§ ÙˆØ§Ø±Ø¯ Ú©Ù†ÛŒØ¯', 'warning');
        return;
    }
    
    try {
        const response = await fetch('/admin/api/broadcast/preview', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ content, target })
        });
        
        const data = await response.json();
        
        if (data.success) {
            let html = '<div class="space-y-3">';
            html += '<div class="text-white/60 text-sm mb-2">Ù¾ÛŒØ´â€ŒÙ†Ù…Ø§ÛŒØ´ Ø¨Ø±Ø§ÛŒ 3 Ú©Ø§Ø±Ø¨Ø± Ù†Ù…ÙˆÙ†Ù‡:</div>';
            
            data.previews.forEach(preview => {
                html += `
                    <div class="bg-white/5 rounded-lg p-3">
                        <div class="text-white/60 text-xs mb-1">Ú©Ø§Ø±Ø¨Ø±: ${preview.user.first_name || preview.user.username || 'Ú©Ø§Ø±Ø¨Ø±'}</div>
                        <div class="text-white text-sm whitespace-pre-wrap">${preview.content}</div>
                    </div>
                `;
            });
            
            html += '</div>';
            
            document.getElementById('previewArea').innerHTML = html;
            document.getElementById('previewArea').classList.remove('hidden');
        } else {
            showToast(data.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Open Create Modal â•â•â•
function openCreateModal() {
    document.getElementById('createForm').reset();
    document.getElementById('fileIdGroup').classList.add('hidden');
    document.getElementById('targetOptions').classList.add('hidden');
    document.getElementById('previewArea').classList.add('hidden');
    document.getElementById('targetCount').textContent = '-';
    
    document.getElementById('createModal').classList.remove('hidden');
    document.getElementById('createModal').classList.add('flex');
    
    updateTargetCount();
}

// â•â•â• Close Create Modal â•â•â•
function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    document.getElementById('createModal').classList.remove('flex');
}

// â•â•â• Create Broadcast â•â•â•
document.getElementById('createForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        title: formData.get('title'),
        content: formData.get('content'),
        content_type: formData.get('content_type'),
        file_id: formData.get('file_id'),
        target: formData.get('target'),
        delay: parseInt(formData.get('delay')) || 50,
        target_options: {}
    };
    
    // Ø¬Ù…Ø¹â€ŒØ¢ÙˆØ±ÛŒ target options
    const activeDays = formData.get('active_days');
    const inactiveDays = formData.get('inactive_days');
    const newDays = formData.get('new_days');
    const minDonation = formData.get('min_donation');
    const userIds = formData.get('user_ids');
    
    if (activeDays) data.target_options.active_days = parseInt(activeDays);
    if (inactiveDays) data.target_options.inactive_days = parseInt(inactiveDays);
    if (newDays) data.target_options.new_days = parseInt(newDays);
    if (minDonation) data.target_options.min_donation = parseInt(minDonation);
    if (userIds) {
        data.target_options.user_ids = userIds.split(',').map(id => parseInt(id.trim())).filter(id => id > 0);
    }
    
    try {
        const response = await fetch('/admin/api/broadcast/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Broadcast Ø§ÛŒØ¬Ø§Ø¯ Ø´Ø¯', 'success');
            closeCreateModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
});

// â•â•â• View Broadcast Details â•â•â•
async function viewBroadcast(broadcastId) {
    try {
        const response = await fetch(`/admin/api/broadcast/${broadcastId}`);
        const data = await response.json();
        
        if (data.success) {
            const b = data.data;
            
            let html = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-white/60 text-xs">Ø¢ÛŒØ¯ÛŒ</label>
                            <div class="text-white font-mono">#${b.id}</div>
                        </div>
                        <div>
                            <label class="text-white/60 text-xs">ÙˆØ¶Ø¹ÛŒØª</label>
                            <div class="text-white">${b.status_icon} ${b.status_text}</div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-white/60 text-xs">Ø¹Ù†ÙˆØ§Ù†</label>
                        <div class="text-white font-bold text-lg">${b.title}</div>
                    </div>
                    
                    <div>
                        <label class="text-white/60 text-xs mb-2 block">Ù…Ø­ØªÙˆØ§ÛŒ Ù¾ÛŒØ§Ù…</label>
                        <div class="bg-white/5 rounded-lg p-4 text-white whitespace-pre-wrap">${b.content}</div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-white/60 text-xs">Ú¯Ø±ÙˆÙ‡ Ù‡Ø¯Ù</label>
                            <div class="text-white">${b.target_text}</div>
                        </div>
                        <div>
                            <label class="text-white/60 text-xs">ØªØ¹Ø¯Ø§Ø¯ Ù‡Ø¯Ù</label>
                            <div class="text-white font-bold">${new Intl.NumberFormat('fa-IR').format(b.target_count)}</div>
                        </div>
                    </div>
                    
                    <div class="bg-purple-500/10 border border-purple-500/30 rounded-lg p-4">
                        <div class="text-white/60 text-xs mb-2">Ù¾ÛŒØ´Ø±ÙØª</div>
                        <div class="w-full bg-white/10 rounded-full h-3 mb-2">
                            <div class="bg-gradient-to-r from-purple-500 to-blue-500 h-3 rounded-full" style="width: ${b.progress_percent}%"></div>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div>
                                <div class="text-green-400 font-bold">${new Intl.NumberFormat('fa-IR').format(b.sent_count || 0)}</div>
                                <div class="text-white/50 text-xs">Ù…ÙˆÙÙ‚</div>
                            </div>
                            <div>
                                <div class="text-red-400 font-bold">${new Intl.NumberFormat('fa-IR').format(b.failed_count || 0)}</div>
                                <div class="text-white/50 text-xs">Ù†Ø§Ù…ÙˆÙÙ‚</div>
                            </div>
                            <div>
                                <div class="text-yellow-400 font-bold">${new Intl.NumberFormat('fa-IR').format(b.blocked_count || 0)}</div>
                                <div class="text-white/50 text-xs">Ø¨Ù„Ø§Ú©</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-white/60 text-xs">ØªØ§Ø±ÛŒØ® Ø§ÛŒØ¬Ø§Ø¯</label>
                            <div class="text-white text-sm">${b.created_at}</div>
                        </div>
                        ${b.started_at ? `
                        <div>
                            <label class="text-white/60 text-xs">ØªØ§Ø±ÛŒØ® Ø´Ø±ÙˆØ¹</label>
                            <div class="text-white text-sm">${b.started_at}</div>
                        </div>
                        ` : ''}
                        ${b.completed_at ? `
                        <div>
                            <label class="text-white/60 text-xs">ØªØ§Ø±ÛŒØ® ØªÚ©Ù…ÛŒÙ„</label>
                            <div class="text-white text-sm">${b.completed_at}</div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
            
            document.getElementById('detailsModalContent').innerHTML = html;
            document.getElementById('detailsModal').classList.remove('hidden');
            document.getElementById('detailsModal').classList.add('flex');
        } else {
            showToast(data.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

function closeDetailsModal() {
    document.getElementById('detailsModal').classList.add('hidden');
    document.getElementById('detailsModal').classList.remove('flex');
}

// â•â•â• Start Broadcast â•â•â•
async function startBroadcast(broadcastId) {
    if (!confirm('Ø¢ÛŒØ§ Ù…Ø·Ù…Ø¦Ù† Ù‡Ø³ØªÛŒØ¯ Ú©Ù‡ Ù…ÛŒâ€ŒØ®ÙˆØ§Ù‡ÛŒØ¯ Ø§ÛŒÙ† Broadcast Ø±Ø§ Ø´Ø±ÙˆØ¹ Ú©Ù†ÛŒØ¯ØŸ')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/api/broadcast/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ broadcast_id: broadcastId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Broadcast Ø´Ø±ÙˆØ¹ Ø´Ø¯', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Pause Broadcast â•â•â•
async function pauseBroadcast(broadcastId) {
    if (!confirm('Ø¢ÛŒØ§ Ù…Ø·Ù…Ø¦Ù† Ù‡Ø³ØªÛŒØ¯ Ú©Ù‡ Ù…ÛŒâ€ŒØ®ÙˆØ§Ù‡ÛŒØ¯ Ø§ÛŒÙ† Broadcast Ø±Ø§ Ù…ØªÙˆÙ‚Ù Ú©Ù†ÛŒØ¯ØŸ')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/api/broadcast/pause', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ broadcast_id: broadcastId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Broadcast Ù…ØªÙˆÙ‚Ù Ø´Ø¯', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Resume Broadcast â•â•â•
async function resumeBroadcast(broadcastId) {
    if (!confirm('Ø¢ÛŒØ§ Ù…Ø·Ù…Ø¦Ù† Ù‡Ø³ØªÛŒØ¯ Ú©Ù‡ Ù…ÛŒâ€ŒØ®ÙˆØ§Ù‡ÛŒØ¯ Ø§ÛŒÙ† Broadcast Ø±Ø§ Ø§Ø¯Ø§Ù…Ù‡ Ø¯Ù‡ÛŒØ¯ØŸ')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/api/broadcast/resume', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ broadcast_id: broadcastId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Broadcast Ø§Ø¯Ø§Ù…Ù‡ ÛŒØ§ÙØª', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Cancel Broadcast â•â•â•
async function cancelBroadcast(broadcastId) {
    if (!confirm('Ø¢ÛŒØ§ Ù…Ø·Ù…Ø¦Ù† Ù‡Ø³ØªÛŒØ¯ Ú©Ù‡ Ù…ÛŒâ€ŒØ®ÙˆØ§Ù‡ÛŒØ¯ Ø§ÛŒÙ† Broadcast Ø±Ø§ Ù„ØºÙˆ Ú©Ù†ÛŒØ¯ØŸ\n\nØ§ÛŒÙ† Ø¹Ù…Ù„ ØºÛŒØ±Ù‚Ø§Ø¨Ù„ Ø¨Ø§Ø²Ú¯Ø´Øª Ø§Ø³Øª!')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/api/broadcast/cancel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ broadcast_id: broadcastId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Broadcast Ù„ØºÙˆ Ø´Ø¯', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Duplicate Broadcast â•â•â•
async function duplicateBroadcast(broadcastId) {
    const newTitle = prompt('Ø¹Ù†ÙˆØ§Ù† Ø¬Ø¯ÛŒØ¯ (Ø§Ø®ØªÛŒØ§Ø±ÛŒ):');
    
    try {
        const response = await fetch('/admin/api/broadcast/duplicate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ broadcast_id: broadcastId, new_title: newTitle })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Broadcast ØªÚ©Ø±Ø§Ø±ÛŒ Ø§ÛŒØ¬Ø§Ø¯ Ø´Ø¯', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Delete Broadcast â•â•â•
async function deleteBroadcast(broadcastId) {
    if (!confirm('Ø¢ÛŒØ§ Ù…Ø·Ù…Ø¦Ù† Ù‡Ø³ØªÛŒØ¯ Ú©Ù‡ Ù…ÛŒâ€ŒØ®ÙˆØ§Ù‡ÛŒØ¯ Ø§ÛŒÙ† Broadcast Ø±Ø§ Ø­Ø°Ù Ú©Ù†ÛŒØ¯ØŸ\n\nØ§ÛŒÙ† Ø¹Ù…Ù„ ØºÛŒØ±Ù‚Ø§Ø¨Ù„ Ø¨Ø§Ø²Ú¯Ø´Øª Ø§Ø³Øª!')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/api/broadcast/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ broadcast_id: broadcastId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Broadcast Ø­Ø°Ù Ø´Ø¯', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Close Modals on Escape â•â•â•
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateModal();
        closeDetailsModal();
    }
});

// â•â•â• Close Modals on Outside Click â•â•â•
['createModal', 'detailsModal'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
            this.classList.remove('flex');
        }
    });
});
</script>