<?php
/**
 * ============================================
 * Live Chat - چت زنده با کاربران
 * ============================================
 * نسخه: 2.0.0
 * 
 * رابط کاربری چت زنده با کاربران
 * شامل لیست کاربران، پنجره چت و ارسال پیام
 */

// متغیرهای مورد نیاز از Controller:
// - $users (لیست کاربران با پیام‌های خوانده نشده)
// - $currentUser (کاربر انتخاب شده)
// - $messages (پیام‌های کاربر فعلی)
// - $userInfo (اطلاعات کامل کاربر)

$users = $users ?? [];
$currentUser = $currentUser ?? null;
$messages = $messages ?? [];
$userInfo = $userInfo ?? [];

// CSRF Token
$csrfToken = $_SESSION['_csrf_token'] ?? '';

// آیدی کاربر از URL
$selectedUserId = $_GET['id'] ?? null;
?>

<div class="flex gap-4 h-[calc(100vh-180px)]">
    
    <!-- ═══ لیست کاربران (Sidebar) ═══ -->
    <div class="w-80 glass rounded-2xl flex flex-col overflow-hidden flex-shrink-0">
        
        <!-- Header -->
        <div class="p-4 border-b border-white/10">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                <span>💬</span>
                <span>چت‌ها</span>
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
                    placeholder="جستجوی کاربر..."
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2 pr-9 pl-3 text-white text-sm placeholder-white/40 focus:border-purple-500 transition"
                >
            </div>
        </div>
        
        <!-- Users List -->
        <div class="flex-1 overflow-y-auto" id="usersList">
            <?php if (empty($users)): ?>
            <div class="text-center py-12 px-4">
                <div class="text-5xl mb-3">📭</div>
                <p class="text-white/50 text-sm">پیام خوانده نشده‌ای وجود ندارد</p>
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
                                    <?= htmlspecialchars($user['display_name'] ?? 'کاربر') ?>
                                    <?php if (!empty($user['is_vip'])): ?>
                                    <span class="text-yellow-400 text-xs">👑</span>
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
                                    <?= $user['unread_count'] ?> پیام جدید
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
    
    <!-- ═══ پنجره چت ═══ -->
    <div class="flex-1 glass rounded-2xl flex flex-col overflow-hidden">
        
        <?php if (!$currentUser): ?>
        <!-- Empty State -->
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center">
                <div class="text-7xl mb-4">💬</div>
                <h3 class="text-white text-xl font-bold mb-2">یک کاربر را انتخاب کنید</h3>
                <p class="text-white/50 text-sm">از لیست سمت راست، کاربر مورد نظر را انتخاب کنید</p>
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
                        <span><?= htmlspecialchars($userInfo['display_name'] ?? 'کاربر') ?></span>
                        <?php if (!empty($userInfo['is_vip'])): ?>
                        <span class="bg-yellow-500/20 text-yellow-300 text-xs px-2 py-0.5 rounded-full">VIP</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-white/50 text-xs flex items-center gap-2">
                        <?php if (!empty($userInfo['is_online'])): ?>
                        <span class="flex items-center gap-1 text-green-400">
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                            <span>آنلاین</span>
                        </span>
                        <?php else: ?>
                        <span>آخرین بازدید: <?= htmlspecialchars($userInfo['last_seen_ago'] ?? 'نامشخص') ?></span>
                        <?php endif; ?>
                        <span>•</span>
                        <span>ID: <?= $currentUser['id'] ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center gap-2">
                <button 
                    onclick="viewUserProfile(<?= $currentUser['id'] ?>)"
                    class="bg-white/10 hover:bg-white/20 text-white p-2 rounded-lg transition"
                    title="پروفایل کاربر"
                >
                    <i class="fas fa-user-circle"></i>
                </button>
                <button 
                    onclick="exportChat(<?= $currentUser['id'] ?>)"
                    class="bg-white/10 hover:bg-white/20 text-white p-2 rounded-lg transition"
                    title="خروجی چت"
                >
                    <i class="fas fa-download"></i>
                </button>
                <button 
                    onclick="clearChat(<?= $currentUser['id'] ?>)"
                    class="bg-red-500/20 hover:bg-red-500/30 text-red-300 p-2 rounded-lg transition"
                    title="پاک کردن چت"
                >
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        
        <!-- Messages Area -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messagesArea">
            <?php if (empty($messages)): ?>
            <div class="text-center py-12">
                <div class="text-5xl mb-3">📝</div>
                <p class="text-white/50 text-sm">هنوز پیامی در این چت وجود ندارد</p>
                <p class="text-white/30 text-xs mt-1">اولین پیام را ارسال کنید!</p>
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
                                <span><?= $msg['type_icon'] ?? '📎' ?></span>
                                <span><?= htmlspecialchars($msg['message_type'] ?? 'فایل') ?></span>
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
                                <span title="<?= !empty($msg['is_read']) ? 'خوانده شده' : 'ارسال شده' ?>">
                                    <?= !empty($msg['is_read']) ? '✓✓' : '✓' ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            
                        </div>
                        
                        <!-- Note Indicator -->
                        <?php if ($msg['direction'] === 'note'): ?>
                        <div class="text-xs text-yellow-400 mt-1 flex items-center gap-1">
                            <i class="fas fa-sticky-note"></i>
                            <span>یادداشت</span>
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
                    onclick="insertQuickReply('سلام! چطور می‌تونم کمکتون کنم؟')"
                    class="bg-white/10 hover:bg-white/20 text-white text-xs px-3 py-1.5 rounded-lg whitespace-nowrap transition"
                >
                    👋 سلام
                </button>
                <button 
                    onclick="insertQuickReply('ممنون از پیام شما. همکاران ما در اسرع وقت پاسخ می‌دن.')"
                    class="bg-white/10 hover:bg-white/20 text-white text-xs px-3 py-1.5 rounded-lg whitespace-nowrap transition"
                >
                    ⏳ در حال بررسی
                </button>
                <button 
                    onclick="insertQuickReply('مشکل شما حل شد؟ اگه سوال دیگه‌ای دارید در خدمتم.')"
                    class="bg-white/10 hover:bg-white/20 text-white text-xs px-3 py-1.5 rounded-lg whitespace-nowrap transition"
                >
                    ✅ حل شد؟
                </button>
                <button 
                    onclick="insertQuickReply('برای حمایت مالی از لینک زیر استفاده کنید:\n💰 [لینک حمایت]')"
                    class="bg-white/10 hover:bg-white/20 text-white text-xs px-3 py-1.5 rounded-lg whitespace-nowrap transition"
                >
                    💰 حمایت
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
                        placeholder="پیام خود را بنویسید..."
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
                        title="ایموجی"
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
                    <span class="hidden sm:inline">ارسال</span>
                </button>
            </form>
            
            <!-- Emoji Picker (Hidden by default) -->
            <div id="emojiPicker" class="hidden mt-3 bg-white/10 rounded-xl p-3">
                <div class="grid grid-cols-8 gap-2">
                    <?php
                    $emojis = ['😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '😚', '😙', '🥲', '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '🤥', '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🥵', '🥶', '🥴', '😵', '🤯', '🤠', '🥳', '🥸', '😎', '🤓', '🧐', '😕', '😟', '🙁', '☹️', '😮', '😯', '😲', '😳', '🥺', '😦', '😧', '😨', '😰', '😥', '😢', '😭', '😱', '😖', '😣', '😞', '😓', '😩', '😫', '🥱', '😤', '😡', '😠', '🤬', '👍', '👎', '👏', '🙌', '🤝', '❤️', '💔', '💯', '✨', '🔥', '⭐', '🎉', '🎊'];
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

<!-- ═══ JavaScript ═══ -->
<script>
// ═══ Auto-scroll to bottom ═══
function scrollToBottom() {
    const messagesArea = document.getElementById('messagesArea');
    if (messagesArea) {
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }
}

// ═══ Auto-resize textarea ═══
function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
}

// ═══ Handle Enter key ═══
function handleKeyDown(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        document.getElementById('chatForm').dispatchEvent(new Event('submit'));
    }
}

// ═══ Toggle Emoji Picker ═══
function toggleEmojiPicker() {
    const picker = document.getElementById('emojiPicker');
    picker.classList.toggle('hidden');
}

// ═══ Insert Emoji ═══
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

// ═══ Insert Quick Reply ═══
function insertQuickReply(text) {
    const input = document.getElementById('messageInput');
    input.value = text;
    input.focus();
    autoResize(input);
}

// ═══ Send Message ═══
document.getElementById('chatForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const message = formData.get('message').trim();
    
    if (!message) {
        showToast('لطفاً پیام را وارد کنید', 'warning');
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
            // اضافه کردن پیام به صفحه
            appendMessage(data.message);
            
            // پاک کردن input
            document.getElementById('messageInput').value = '';
            autoResize(document.getElementById('messageInput'));
            
            showToast('پیام ارسال شد', 'success');
        } else {
            showToast(data.error || 'خطا در ارسال پیام', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    } finally {
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i><span class="hidden sm:inline">ارسال</span>';
    }
});

// ═══ Append Message ═══
function appendMessage(msg) {
    const messagesArea = document.getElementById('messagesArea');
    
    // حذف empty state اگر وجود داره
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
                        <span>الان</span>
                        <span>✓</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    messagesArea.insertAdjacentHTML('beforeend', messageHtml);
    scrollToBottom();
}

// ═══ Search Users ═══
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

// ═══ View User Profile ═══
function viewUserProfile(userId) {
    window.open(`/admin/users.php?id=${userId}`, '_blank');
}

// ═══ Export Chat ═══
function exportChat(userId) {
    if (!confirm('آیا می‌خواهید چت را به فرمت متنی دانلود کنید؟')) {
        return;
    }
    window.location.href = `/admin/api/chat/export/${userId}`;
    showToast('در حال دانلود...', 'info');
}

// ═══ Clear Chat ═══
async function clearChat(userId) {
    if (!confirm('آیا مطمئن هستید که می‌خواهید تمام پیام‌های این چت را حذف کنید؟\n\nاین عمل غیرقابل بازگشت است!')) {
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
            showToast('چت پاک شد', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'خطا', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
}

// ═══ Auto-refresh Messages (هر 10 ثانیه) ═══
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

// بررسی هر 10 ثانیه
setInterval(checkNewMessages, 10000);
<?php endif; ?>

// ═══ Initialize ═══
document.addEventListener('DOMContentLoaded', function() {
    scrollToBottom();
    
    // Focus on input
    const input = document.getElementById('messageInput');
    if (input) {
        input.focus();
    }
});
</script>