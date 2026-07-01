<?php
/**
 * ============================================
 * Live Chat - Ú†Øª Ø²Ù†Ø¯Ù‡ Ø¨Ø§ Ú©Ø§Ø±Ø¨Ø±Ø§Ù†
 * ============================================
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * 
 * Ø±Ø§Ø¨Ø· Ú©Ø§Ø±Ø¨Ø±ÛŒ Ú†Øª Ø²Ù†Ø¯Ù‡ Ø¨Ø§ Ú©Ø§Ø±Ø¨Ø±Ø§Ù†
 * Ø´Ø§Ù…Ù„ Ù„ÛŒØ³Øª Ú©Ø§Ø±Ø¨Ø±Ø§Ù†ØŒ Ù¾Ù†Ø¬Ø±Ù‡ Ú†Øª Ùˆ Ø§Ø±Ø³Ø§Ù„ Ù¾ÛŒØ§Ù…
 */

// Ù…ØªØºÛŒØ±Ù‡Ø§ÛŒ Ù…ÙˆØ±Ø¯ Ù†ÛŒØ§Ø² Ø§Ø² Controller:
// - $users (Ù„ÛŒØ³Øª Ú©Ø§Ø±Ø¨Ø±Ø§Ù† Ø¨Ø§ Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§ÛŒ Ø®ÙˆØ§Ù†Ø¯Ù‡ Ù†Ø´Ø¯Ù‡)
// - $currentUser (Ú©Ø§Ø±Ø¨Ø± Ø§Ù†ØªØ®Ø§Ø¨ Ø´Ø¯Ù‡)
// - $messages (Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§ÛŒ Ú©Ø§Ø±Ø¨Ø± ÙØ¹Ù„ÛŒ)
// - $userInfo (Ø§Ø·Ù„Ø§Ø¹Ø§Øª Ú©Ø§Ù…Ù„ Ú©Ø§Ø±Ø¨Ø±)

$users = $users ?? [];
$currentUser = $currentUser ?? null;
$messages = $messages ?? [];
$userInfo = $userInfo ?? [];

// CSRF Token
$csrfToken = $_SESSION['_csrf_token'] ?? '';

// Ø¢ÛŒØ¯ÛŒ Ú©Ø§Ø±Ø¨Ø± Ø§Ø² URL
$selectedUserId = $_GET['id'] ?? null;
?>

<div class="flex gap-4 h-[calc(100vh-180px)]">
    
    <!-- â•â•â• Ù„ÛŒØ³Øª Ú©Ø§Ø±Ø¨Ø±Ø§Ù† (Sidebar) â•â•â• -->
    <div class="w-80 glass rounded-2xl flex flex-col overflow-hidden flex-shrink-0">
        
        <!-- Header -->
        <div class="p-4 border-b border-white/10">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                <span>ðŸ’¬</span>
                <span>Ú†Øªâ€ŒÙ‡Ø§</span>
                <?php if (count($users) > 0): ?>
                <span class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5">
                    <?= count($users) ?>
                </span>
                <?php endif; ?>
            </h3>
        </div>
        
        <!-- Search -->
        <div class="p-3 border-b border-white/10">
            <div class="relative">
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40">
                    <i class="fas fa-search text-sm"></i>
                </span>
                <input 
                    type="text" 
                    id="userSearch"
                    placeholder="Ø¬Ø³ØªØ¬ÙˆÛŒ Ú©Ø§Ø±Ø¨Ø±..."
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2 pr-9 pl-3 text-white text-sm placeholder-white/40 focus:border-purple-500 transition"
                >
            </div>
        </div>
        
        <!-- Users List -->
        <div class="flex-1 overflow-y-auto" id="usersList">
            <?php if (empty($users)): ?>
            <div class="text-center py-12 px-4">
                <div class="text-5xl mb-3">ðŸ“­</div>
                <p class="text-white/50 text-sm">Ù¾ÛŒØ§Ù… Ø®ÙˆØ§Ù†Ø¯Ù‡ Ù†Ø´Ø¯Ù‡â€ŒØ§ÛŒ ÙˆØ¬ÙˆØ¯ Ù†Ø¯Ø§Ø±Ø¯</p>
            </div>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                <a 
                    href="/admin/chat.php?id=<?= $user['id'] ?>"
                    class="user-item block p-3 border-b border-white/5 hover:bg-white/10 transition <?= ($selectedUserId == $user['id']) ? 'bg-white/10 border-r-4 border-r-purple-500' : '' ?>"
                    data-name="<?= strtolower($user['first_name'] ?? '' . ' ' . $user['last_name'] ?? '' . ' ' . $user['username'] ?? '') ?>"
                >
                    <div class="flex items-start gap-3">
                        <!-- Avatar -->
                        <div class="relative flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white font-bold">
                                <?= strtoupper(substr($user['first_name'] ?? $user['username'] ?? '?', 0, 1)) ?>
                            </div>
                            <?php if (!empty($user['is_online'])): ?>
                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-gray-900"></span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-white font-medium text-sm truncate flex items-center gap-1">
                                    <?= htmlspecialchars($user['display_name'] ?? 'Ú©Ø§Ø±Ø¨Ø±') ?>
                                    <?php if (!empty($user['is_vip'])): ?>
                                    <span class="text-yellow-400 text-xs">ðŸ‘‘</span>
                                    <?php endif; ?>
                                </span>
                                <span class="text-white/40 text-xs flex-shrink-0">
                                    <?= htmlspecialchars($user['last_unread_ago'] ?? '') ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($user['last_message_preview'])): ?>
                            <p class="text-white/60 text-xs truncate mb-1">
                                <?= htmlspecialchars($user['last_message_preview']) ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($user['unread_count'])): ?>
                            <div class="flex items-center justify-between">
                                <span class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5 font-bold">
                                    <?= $user['unread_count'] ?> Ù¾ÛŒØ§Ù… Ø¬Ø¯ÛŒØ¯
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
    </div>
    
    <!-- â•â•â• Ù¾Ù†Ø¬Ø±Ù‡ Ú†Øª â•â•â• -->
    <div class="flex-1 glass rounded-2xl flex flex-col overflow-hidden">
        
        <?php if (!$currentUser): ?>
        <!-- Empty State -->
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center">
                <div class="text-7xl mb-4">ðŸ’¬</div>
                <h3 class="text-white text-xl font-bold mb-2">ÛŒÚ© Ú©Ø§Ø±Ø¨Ø± Ø±Ø§ Ø§Ù†ØªØ®Ø§Ø¨ Ú©Ù†ÛŒØ¯</h3>
                <p class="text-white/50 text-sm">Ø§Ø² Ù„ÛŒØ³Øª Ø³Ù…Øª Ø±Ø§Ø³ØªØŒ Ú©Ø§Ø±Ø¨Ø± Ù…ÙˆØ±Ø¯ Ù†Ø¸Ø± Ø±Ø§ Ø§Ù†ØªØ®Ø§Ø¨ Ú©Ù†ÛŒØ¯</p>
            </div>
        </div>
        <?php else: ?>
        
        <!-- Chat Header -->
        <div class="p-4 border-b border-white/10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <!-- Avatar -->
                <div class="relative">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white font-bold text-lg">
                        <?= strtoupper(substr($currentUser['first_name'] ?? $currentUser['username'] ?? '?', 0, 1)) ?>
                    </div>
                    <?php if (!empty($userInfo['is_online'])): ?>
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-gray-900"></span>
                    <?php endif; ?>
                </div>
                
                <!-- Info -->
                <div>
                    <div class="text-white font-bold flex items-center gap-2">
                        <span><?= htmlspecialchars($userInfo['display_name'] ?? 'Ú©Ø§Ø±Ø¨Ø±') ?></span>
                        <?php if (!empty($userInfo['is_vip'])): ?>
                        <span class="bg-yellow-500/20 text-yellow-300 text-xs px-2 py-0.5 rounded-full">VIP</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-white/50 text-xs flex items-center gap-2">
                        <?php if (!empty($userInfo['is_online'])): ?>
                        <span class="flex items-center gap-1 text-green-400">
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                            <span>Ø¢Ù†Ù„Ø§ÛŒÙ†</span>
                        </span>
                        <?php else: ?>
                        <span>Ø¢Ø®Ø±ÛŒÙ† Ø¨Ø§Ø²Ø¯ÛŒØ¯: <?= htmlspecialchars($userInfo['last_seen_ago'] ?? 'Ù†Ø§Ù…Ø´Ø®Øµ') ?></span>
                        <?php endif; ?>
                        <span>â€¢</span>
                        <span>ID: <?= $currentUser['id'] ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center gap-2">
                <button 
                    onclick="viewUserProfile(<?= $currentUser['id'] ?>)"
                    class="bg-white/10 hover:bg-white/20 text-white p-2 rounded-lg transition"
                    title="Ù¾Ø±ÙˆÙØ§ÛŒÙ„ Ú©Ø§Ø±Ø¨Ø±"
                >
                    <i class="fas fa-user-circle"></i>
                </button>
                <button 
                    onclick="exportChat(<?= $currentUser['id'] ?>)"
                    class="bg-white/10 hover:bg-white/20 text-white p-2 rounded-lg transition"
                    title="Ø®Ø±ÙˆØ¬ÛŒ Ú†Øª"
                >
                    <i class="fas fa-download"></i>
                </button>
                <button 
                    onclick="clearChat(<?= $currentUser['id'] ?>)"
                    class="bg-red-500/20 hover:bg-red-500/30 text-red-300 p-2 rounded-lg transition"
                    title="Ù¾Ø§Ú© Ú©Ø±Ø¯Ù† Ú†Øª"
                >
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        
        <!-- Messages Area -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messagesArea">
            <?php if (empty($messages)): ?>
            <div class="text-center py-12">
                <div class="text-5xl mb-3">ðŸ“</div>
                <p class="text-white/50 text-sm">Ù‡Ù†ÙˆØ² Ù¾ÛŒØ§Ù…ÛŒ Ø¯Ø± Ø§ÛŒÙ† Ú†Øª ÙˆØ¬ÙˆØ¯ Ù†Ø¯Ø§Ø±Ø¯</p>
                <p class="text-white/30 text-xs mt-1">Ø§ÙˆÙ„ÛŒÙ† Ù¾ÛŒØ§Ù… Ø±Ø§ Ø§Ø±Ø³Ø§Ù„ Ú©Ù†ÛŒØ¯!</p>
            </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                <div class="flex <?= $msg['direction'] === 'out' ? 'justify-start' : 'justify-end' ?> animate-fadeIn">
                    <div class="max-w-[70%] <?= $msg['direction'] === 'out' ? 'order-2' : '' ?>">
                        
                        <!-- Message Bubble -->
                        <div class="rounded-2xl px-4 py-3 <?= $msg['direction'] === 'out' ? 'bg-gradient-to-br from-purple-600 to-blue-600 text-white' : 'bg-white/10 text-white' ?>">
                            
                            <!-- Message Type Icon -->
                            <?php if ($msg['message_type'] !== 'text'): ?>
                            <div class="text-xs mb-1 opacity-70 flex items-center gap-1">
                                <span><?= $msg['type_icon'] ?? 'ðŸ“Ž' ?></span>
                                <span><?= htmlspecialchars($msg['message_type'] ?? 'ÙØ§ÛŒÙ„') ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Message Text -->
                            <?php if (!empty($msg['text'])): ?>
                            <div class="text-sm whitespace-pre-wrap break-words">
                                <?= nl2br(htmlspecialchars($msg['text'])) ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Time & Status -->
                            <div class="flex items-center justify-end gap-2 mt-2 text-xs opacity-70">
                                <span><?= htmlspecialchars($msg['time_ago'] ?? '') ?></span>
                                <?php if ($msg['direction'] === 'out'): ?>
                                <span title="<?= !empty($msg['is_read']) ? 'Ø®ÙˆØ§Ù†Ø¯Ù‡ Ø´Ø¯Ù‡' : 'Ø§Ø±Ø³Ø§Ù„ Ø´Ø¯Ù‡' ?>">
                                    <?= !empty($msg['is_read']) ? 'âœ“âœ“' : 'âœ“' ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            
                        </div>
                        
                        <!-- Note Indicator -->
                        <?php if ($msg['direction'] === 'note'): ?>
                        <div class="text-xs text-yellow-400 mt-1 flex items-center gap-1">
                            <i class="fas fa-sticky-note"></i>
                            <span>ÛŒØ§Ø¯Ø¯Ø§Ø´Øª</span>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Quick Replies -->
        <div class="border-t border-white/10 p-3">
            <div class="flex gap-2 overflow-x-auto pb-2">
                <button 
                    onclick="insertQuickReply('Ø³Ù„Ø§Ù…! Ú†Ø·ÙˆØ± Ù…ÛŒâ€ŒØªÙˆÙ†Ù… Ú©Ù…Ú©ØªÙˆÙ† Ú©Ù†Ù…ØŸ')"
                    class="bg-white/10 hover:bg-white/20 text-white text-xs px-3 py-1.5 rounded-lg whitespace-nowrap transition"
                >
                    ðŸ‘‹ Ø³Ù„Ø§Ù…
                </button>
                <button 
                    onclick="insertQuickReply('Ù…Ù…Ù†ÙˆÙ† Ø§Ø² Ù¾ÛŒØ§Ù… Ø´Ù…Ø§. Ù‡Ù…Ú©Ø§Ø±Ø§Ù† Ù…Ø§ Ø¯Ø± Ø§Ø³Ø±Ø¹ ÙˆÙ‚Øª Ù¾Ø§Ø³Ø® Ù…ÛŒâ€ŒØ¯Ù†.')"
                    class="bg-white/10 hover:bg-white/20 text-white text-xs px-3 py-1.5 rounded-lg whitespace-nowrap transition"
                >
                    â³ Ø¯Ø± Ø­Ø§Ù„ Ø¨Ø±Ø±Ø³ÛŒ
                </button>
                <button 
                    onclick="insertQuickReply('Ù…Ø´Ú©Ù„ Ø´Ù…Ø§ Ø­Ù„ Ø´Ø¯ØŸ Ø§Ú¯Ù‡ Ø³ÙˆØ§Ù„ Ø¯ÛŒÚ¯Ù‡â€ŒØ§ÛŒ Ø¯Ø§Ø±ÛŒØ¯ Ø¯Ø± Ø®Ø¯Ù…ØªÙ….')"
                    class="bg-white/10 hover:bg-white/20 text-white text-xs px-3 py-1.5 rounded-lg whitespace-nowrap transition"
                >
                    âœ… Ø­Ù„ Ø´Ø¯ØŸ
                </button>
                <button 
                    onclick="insertQuickReply('Ø¨Ø±Ø§ÛŒ Ø­Ù…Ø§ÛŒØª Ù…Ø§Ù„ÛŒ Ø§Ø² Ù„ÛŒÙ†Ú© Ø²ÛŒØ± Ø§Ø³ØªÙØ§Ø¯Ù‡ Ú©Ù†ÛŒØ¯:\nðŸ’° [Ù„ÛŒÙ†Ú© Ø­Ù…Ø§ÛŒØª]')"
                    class="bg-white/10 hover:bg-white/20 text-white text-xs px-3 py-1.5 rounded-lg whitespace-nowrap transition"
                >
                    ðŸ’° Ø­Ù…Ø§ÛŒØª
                </button>
            </div>
        </div>
        
        <!-- Input Area -->
        <div class="border-t border-white/10 p-4">
            <form id="chatForm" class="flex gap-3">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="user_id" value="<?= $currentUser['id'] ?>">
                
                <!-- Text Input -->
                <div class="flex-1 relative">
                    <textarea 
                        id="messageInput"
                        name="message"
                        rows="1"
                        placeholder="Ù¾ÛŒØ§Ù… Ø®ÙˆØ¯ Ø±Ø§ Ø¨Ù†ÙˆÛŒØ³ÛŒØ¯..."
                        class="w-full bg-white/10 border border-white/20 rounded-xl py-3 px-4 pr-12 text-white placeholder-white/40 focus:border-purple-500 transition resize-none"
                        style="min-height: 48px; max-height: 120px;"
                        onkeydown="handleKeyDown(event)"
                        oninput="autoResize(this)"
                    ></textarea>
                    
                    <!-- Emoji Button -->
                    <button 
                        type="button"
                        onclick="toggleEmojiPicker()"
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/60 transition"
                        title="Ø§ÛŒÙ…ÙˆØ¬ÛŒ"
                    >
                        <i class="far fa-smile text-xl"></i>
                    </button>
                </div>
                
                <!-- Send Button -->
                <button 
                    type="submit"
                    id="sendBtn"
                    class="bg-gradient-to-r from-purple-500 to-blue-500 hover:opacity-90 text-white px-6 rounded-xl transition flex items-center gap-2 flex-shrink-0"
                >
                    <i class="fas fa-paper-plane"></i>
                    <span class="hidden sm:inline">Ø§Ø±Ø³Ø§Ù„</span>
                </button>
            </form>
            
            <!-- Emoji Picker (Hidden by default) -->
            <div id="emojiPicker" class="hidden mt-3 bg-white/10 rounded-xl p-3">
                <div class="grid grid-cols-8 gap-2">
                    <?php
                    $emojis = ['ðŸ˜€', 'ðŸ˜ƒ', 'ðŸ˜„', 'ðŸ˜', 'ðŸ˜†', 'ðŸ˜…', 'ðŸ¤£', 'ðŸ˜‚', 'ðŸ™‚', 'ðŸ™ƒ', 'ðŸ˜‰', 'ðŸ˜Š', 'ðŸ˜‡', 'ðŸ¥°', 'ðŸ˜', 'ðŸ¤©', 'ðŸ˜˜', 'ðŸ˜—', 'ðŸ˜š', 'ðŸ˜™', 'ðŸ¥²', 'ðŸ˜‹', 'ðŸ˜›', 'ðŸ˜œ', 'ðŸ¤ª', 'ðŸ˜', 'ðŸ¤‘', 'ðŸ¤—', 'ðŸ¤­', 'ðŸ¤«', 'ðŸ¤”', 'ðŸ¤', 'ðŸ¤¨', 'ðŸ˜', 'ðŸ˜‘', 'ðŸ˜¶', 'ðŸ˜', 'ðŸ˜’', 'ðŸ™„', 'ðŸ˜¬', 'ðŸ¤¥', 'ðŸ˜Œ', 'ðŸ˜”', 'ðŸ˜ª', 'ðŸ¤¤', 'ðŸ˜´', 'ðŸ˜·', 'ðŸ¤’', 'ðŸ¤•', 'ðŸ¤¢', 'ðŸ¤®', 'ðŸ¥µ', 'ðŸ¥¶', 'ðŸ¥´', 'ðŸ˜µ', 'ðŸ¤¯', 'ðŸ¤ ', 'ðŸ¥³', 'ðŸ¥¸', 'ðŸ˜Ž', 'ðŸ¤“', 'ðŸ§', 'ðŸ˜•', 'ðŸ˜Ÿ', 'ðŸ™', 'â˜¹ï¸', 'ðŸ˜®', 'ðŸ˜¯', 'ðŸ˜²', 'ðŸ˜³', 'ðŸ¥º', 'ðŸ˜¦', 'ðŸ˜§', 'ðŸ˜¨', 'ðŸ˜°', 'ðŸ˜¥', 'ðŸ˜¢', 'ðŸ˜­', 'ðŸ˜±', 'ðŸ˜–', 'ðŸ˜£', 'ðŸ˜ž', 'ðŸ˜“', 'ðŸ˜©', 'ðŸ˜«', 'ðŸ¥±', 'ðŸ˜¤', 'ðŸ˜¡', 'ðŸ˜ ', 'ðŸ¤¬', 'ðŸ‘', 'ðŸ‘Ž', 'ðŸ‘', 'ðŸ™Œ', 'ðŸ¤', 'â¤ï¸', 'ðŸ’”', 'ðŸ’¯', 'âœ¨', 'ðŸ”¥', 'â­', 'ðŸŽ‰', 'ðŸŽŠ'];
                    foreach ($emojis as $emoji):
                    ?>
                    <button 
                        type="button"
                        onclick="insertEmoji('<?= $emoji ?>')"
                        class="text-2xl hover:bg-white/10 p-1 rounded transition"
                    >
                        <?= $emoji ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
        
    </div>
    
</div>

<!-- â•â•â• JavaScript â•â•â• -->
<script>
// â•â•â• Auto-scroll to bottom â•â•â•
function scrollToBottom() {
    const messagesArea = document.getElementById('messagesArea');
    if (messagesArea) {
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }
}

// â•â•â• Auto-resize textarea â•â•â•
function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
}

// â•â•â• Handle Enter key â•â•â•
function handleKeyDown(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        document.getElementById('chatForm').dispatchEvent(new Event('submit'));
    }
}

// â•â•â• Toggle Emoji Picker â•â•â•
function toggleEmojiPicker() {
    const picker = document.getElementById('emojiPicker');
    picker.classList.toggle('hidden');
}

// â•â•â• Insert Emoji â•â•â•
function insertEmoji(emoji) {
    const input = document.getElementById('messageInput');
    const start = input.selectionStart;
    const end = input.selectionEnd;
    const text = input.value;
    
    input.value = text.substring(0, start) + emoji + text.substring(end);
    input.focus();
    input.selectionStart = input.selectionEnd = start + emoji.length;
    
    toggleEmojiPicker();
}

// â•â•â• Insert Quick Reply â•â•â•
function insertQuickReply(text) {
    const input = document.getElementById('messageInput');
    input.value = text;
    input.focus();
    autoResize(input);
}

// â•â•â• Send Message â•â•â•
document.getElementById('chatForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const message = formData.get('message').trim();
    
    if (!message) {
        showToast('Ù„Ø·ÙØ§Ù‹ Ù¾ÛŒØ§Ù… Ø±Ø§ ÙˆØ§Ø±Ø¯ Ú©Ù†ÛŒØ¯', 'warning');
        return;
    }
    
    const sendBtn = document.getElementById('sendBtn');
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    try {
        const response = await fetch('/admin/api/chat/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({
                user_id: formData.get('user_id'),
                message: message
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Ø§Ø¶Ø§ÙÙ‡ Ú©Ø±Ø¯Ù† Ù¾ÛŒØ§Ù… Ø¨Ù‡ ØµÙØ­Ù‡
            appendMessage(data.message);
            
            // Ù¾Ø§Ú© Ú©Ø±Ø¯Ù† input
            document.getElementById('messageInput').value = '';
            autoResize(document.getElementById('messageInput'));
            
            showToast('Ù¾ÛŒØ§Ù… Ø§Ø±Ø³Ø§Ù„ Ø´Ø¯', 'success');
        } else {
            showToast(data.error || 'Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±Ø³Ø§Ù„ Ù¾ÛŒØ§Ù…', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    } finally {
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i><span class="hidden sm:inline">Ø§Ø±Ø³Ø§Ù„</span>';
    }
});

// â•â•â• Append Message â•â•â•
function appendMessage(msg) {
    const messagesArea = document.getElementById('messagesArea');
    
    // Ø­Ø°Ù empty state Ø§Ú¯Ø± ÙˆØ¬ÙˆØ¯ Ø¯Ø§Ø±Ù‡
    const emptyState = messagesArea.querySelector('.text-center.py-12');
    if (emptyState) {
        emptyState.remove();
    }
    
    const messageHtml = `
        <div class="flex justify-start animate-fadeIn">
            <div class="max-w-[70%]">
                <div class="rounded-2xl px-4 py-3 bg-gradient-to-br from-purple-600 to-blue-600 text-white">
                    <div class="text-sm whitespace-pre-wrap break-words">
                        ${msg.text.replace(/\n/g, '<br>')}
                    </div>
                    <div class="flex items-center justify-end gap-2 mt-2 text-xs opacity-70">
                        <span>Ø§Ù„Ø§Ù†</span>
                        <span>âœ“</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    messagesArea.insertAdjacentHTML('beforeend', messageHtml);
    scrollToBottom();
}

// â•â•â• Search Users â•â•â•
document.getElementById('userSearch')?.addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase();
    const users = document.querySelectorAll('.user-item');
    
    users.forEach(user => {
        const name = user.getAttribute('data-name');
        if (name.includes(query)) {
            user.style.display = 'block';
        } else {
            user.style.display = 'none';
        }
    });
});

// â•â•â• View User Profile â•â•â•
function viewUserProfile(userId) {
    window.open(`/admin/users.php?id=${userId}`, '_blank');
}

// â•â•â• Export Chat â•â•â•
function exportChat(userId) {
    if (!confirm('Ø¢ÛŒØ§ Ù…ÛŒâ€ŒØ®ÙˆØ§Ù‡ÛŒØ¯ Ú†Øª Ø±Ø§ Ø¨Ù‡ ÙØ±Ù…Øª Ù…ØªÙ†ÛŒ Ø¯Ø§Ù†Ù„ÙˆØ¯ Ú©Ù†ÛŒØ¯ØŸ')) {
        return;
    }
    window.location.href = `/admin/api/chat/export/${userId}`;
    showToast('Ø¯Ø± Ø­Ø§Ù„ Ø¯Ø§Ù†Ù„ÙˆØ¯...', 'info');
}

// â•â•â• Clear Chat â•â•â•
async function clearChat(userId) {
    if (!confirm('Ø¢ÛŒØ§ Ù…Ø·Ù…Ø¦Ù† Ù‡Ø³ØªÛŒØ¯ Ú©Ù‡ Ù…ÛŒâ€ŒØ®ÙˆØ§Ù‡ÛŒØ¯ ØªÙ…Ø§Ù… Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§ÛŒ Ø§ÛŒÙ† Ú†Øª Ø±Ø§ Ø­Ø°Ù Ú©Ù†ÛŒØ¯ØŸ\n\nØ§ÛŒÙ† Ø¹Ù…Ù„ ØºÛŒØ±Ù‚Ø§Ø¨Ù„ Ø¨Ø§Ø²Ú¯Ø´Øª Ø§Ø³Øª!')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/api/chat/clear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ user_id: userId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Ú†Øª Ù¾Ø§Ú© Ø´Ø¯', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Auto-refresh Messages (Ù‡Ø± 10 Ø«Ø§Ù†ÛŒÙ‡) â•â•â•
<?php if ($currentUser): ?>
let lastMessageId = <?= !empty($messages) ? end($messages)['id'] : 0 ?>;

async function checkNewMessages() {
    try {
        const response = await fetch(`/admin/api/chat/new-messages?user_id=<?= $currentUser['id'] ?>&after=${lastMessageId}`);
        const data = await response.json();
        
        if (data.success && data.messages && data.messages.length > 0) {
            data.messages.forEach(msg => {
                appendMessage(msg);
                lastMessageId = msg.id;
            });
        }
    } catch (error) {
        console.error('Error checking new messages:', error);
    }
}

// Ø¨Ø±Ø±Ø³ÛŒ Ù‡Ø± 10 Ø«Ø§Ù†ÛŒÙ‡
setInterval(checkNewMessages, 10000);
<?php endif; ?>

// â•â•â• Initialize â•â•â•
document.addEventListener('DOMContentLoaded', function() {
    scrollToBottom();
    
    // Focus on input
    const input = document.getElementById('messageInput');
    if (input) {
        input.focus();
    }
});
</script>