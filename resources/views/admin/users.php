<?php
/**
 * ============================================
 * Users Management - مدیریت کاربران
 * ============================================
 * نسخه: 2.0.0
 * 
 * لیست کاربران با فیلتر، جستجو و عملیات
 * از layout اصلی (admin.php) استفاده می‌کنه
 */

// متغیرهای مورد نیاز از Controller:
// - $users (آرایه کاربران)
// - $pagination (اطلاعات صفحه‌بندی)
// - $filters (فیلترهای فعلی)
// - $stats (آمار کاربران)

$users = $users ?? [];
$pagination = $pagination ?? ['total' => 0, 'current_page' => 1, 'total_pages' => 1];
$filters = $filters ?? ['search' => '', 'status' => '', 'sort' => 'joined_at', 'order' => 'DESC'];
$stats = $stats ?? ['total' => 0, 'vip' => 0, 'blocked' => 0, 'active' => 0];

// CSRF Token
$csrfToken = $_SESSION['_csrf_token'] ?? '';
?>

<!-- ═══ آمار سریع ═══ -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">👥</div>
        <div class="text-white/60 text-xs mb-1">کل کاربران</div>
        <div class="text-white text-2xl font-bold"><?= number_format($stats['total'] ?? 0) ?></div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">👑</div>
        <div class="text-white/60 text-xs mb-1">کاربران VIP</div>
        <div class="text-yellow-400 text-2xl font-bold"><?= number_format($stats['vip'] ?? 0) ?></div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">🚫</div>
        <div class="text-white/60 text-xs mb-1">بلاک شده</div>
        <div class="text-red-400 text-2xl font-bold"><?= number_format($stats['blocked'] ?? 0) ?></div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">🟢</div>
        <div class="text-white/60 text-xs mb-1">فعال (هفته)</div>
        <div class="text-green-400 text-2xl font-bold"><?= number_format($stats['active'] ?? 0) ?></div>
    </div>
</div>

<!-- ═══ فیلترها و جستجو ═══ -->
<div class="glass rounded-2xl p-5 mb-6">
    <form method="GET" action="/admin/users.php" class="space-y-4">
        
        <!-- ردیف اول: جستجو و فیلتر وضعیت -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <!-- جستجو -->
            <div class="md:col-span-2">
                <label class="block text-white/70 text-sm mb-2">جستجو</label>
                <div class="relative">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40">
                        <i class="fas fa-search"></i>
                    </span>
                    <input 
                        type="text" 
                        name="search" 
                        value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                        placeholder="جستجو بر اساس نام، یوزرنیم یا آیدی..."
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 pr-10 pl-4 text-white placeholder-white/40 focus:border-purple-500 transition"
                    >
                </div>
            </div>
            
            <!-- فیلتر وضعیت -->
            <div>
                <label class="block text-white/70 text-sm mb-2">وضعیت</label>
                <select 
                    name="status" 
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                >
                    <option value="" <?= empty($filters['status']) ? 'selected' : '' ?>>همه</option>
                    <option value="vip" <?= ($filters['status'] ?? '') === 'vip' ? 'selected' : '' ?>>👑 VIP</option>
                    <option value="blocked" <?= ($filters['status'] ?? '') === 'blocked' ? 'selected' : '' ?>>🚫 بلاک شده</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>🟢 فعال</option>
                    <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>⚪ غیرفعال</option>
                </select>
            </div>
            
        </div>
        
        <!-- ردیف دوم: دکمه‌ها -->
        <div class="flex flex-wrap gap-3">
            <button 
                type="submit" 
                class="bg-gradient-to-r from-purple-500 to-blue-500 text-white px-6 py-2.5 rounded-lg hover:opacity-90 transition flex items-center gap-2"
            >
                <i class="fas fa-filter"></i>
                <span>اعمال فیلتر</span>
            </button>
            
            <a 
                href="/admin/users.php" 
                class="bg-white/10 text-white px-6 py-2.5 rounded-lg hover:bg-white/20 transition flex items-center gap-2"
            >
                <i class="fas fa-times"></i>
                <span>پاک کردن فیلترها</span>
            </a>
            
            <button 
                type="button" 
                onclick="exportUsers()" 
                class="bg-green-500/20 border border-green-500/50 text-green-300 px-6 py-2.5 rounded-lg hover:bg-green-500/30 transition flex items-center gap-2"
            >
                <i class="fas fa-download"></i>
                <span>خروجی CSV</span>
            </button>
        </div>
        
    </form>
</div>

<!-- ═══ عملیات دسته‌جمعی ═══ -->
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
            <label for="selectAll" class="text-white text-sm cursor-pointer">انتخاب همه</label>
            <span class="text-white/50 text-sm">(<span id="selectedCount">0</span> انتخاب شده)</span>
        </div>
        
        <div class="flex gap-2">
            <button 
                onclick="bulkAction('make_vip')" 
                class="bg-yellow-500/20 border border-yellow-500/50 text-yellow-300 px-4 py-2 rounded-lg hover:bg-yellow-500/30 transition text-sm flex items-center gap-2"
            >
                <i class="fas fa-crown"></i>
                <span>VIP کردن</span>
            </button>
            <button 
                onclick="bulkAction('remove_vip')" 
                class="bg-white/10 text-white px-4 py-2 rounded-lg hover:bg-white/20 transition text-sm flex items-center gap-2"
            >
                <i class="fas fa-user-times"></i>
                <span>حذف VIP</span>
            </button>
            <button 
                onclick="bulkAction('block')" 
                class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-2 rounded-lg hover:bg-red-500/30 transition text-sm flex items-center gap-2"
            >
                <i class="fas fa-ban"></i>
                <span>بلاک</span>
            </button>
            <button 
                onclick="bulkAction('unblock')" 
                class="bg-green-500/20 border border-green-500/50 text-green-300 px-4 py-2 rounded-lg hover:bg-green-500/30 transition text-sm flex items-center gap-2"
            >
                <i class="fas fa-check"></i>
                <span>آن‌بلاک</span>
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ═══ جدول کاربران ═══ -->
<div class="glass rounded-2xl overflow-hidden">
    
    <?php if (empty($users)): ?>
    <!-- Empty State -->
    <div class="text-center py-16">
        <div class="text-6xl mb-4">👥</div>
        <h3 class="text-white text-xl font-bold mb-2">هنوز کاربری ثبت‌نام نکرده</h3>
        <p class="text-white/50 text-sm mb-6">وقتی کاربران ربات رو استارت کنن، اینجا نمایش داده می‌شن</p>
        <a href="/admin/" class="inline-block bg-purple-500/20 border border-purple-500/50 text-purple-300 px-6 py-2.5 rounded-lg hover:bg-purple-500/30 transition">
            بازگشت به داشبورد
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
                            <span>آیدی</span>
                            <?php if (($filters['sort'] ?? '') === 'id'): ?>
                            <i class="fas fa-sort-<?= ($filters['order'] ?? '') === 'ASC' ? 'up' : 'down' ?> text-xs"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">کاربر</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden md:table-cell">
                        <a href="?sort=joined_at&order=<?= ($filters['sort'] ?? '') === 'joined_at' && ($filters['order'] ?? '') === 'ASC' ? 'DESC' : 'ASC' ?>" class="hover:text-white transition flex items-center gap-1">
                            <span>تاریخ عضویت</span>
                            <?php if (($filters['sort'] ?? '') === 'joined_at'): ?>
                            <i class="fas fa-sort-<?= ($filters['order'] ?? '') === 'ASC' ? 'up' : 'down' ?> text-xs"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden lg:table-cell">آخرین بازدید</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden lg:table-cell">وضعیت</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">عملیات</th>
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
                                    <span><?= htmlspecialchars($user['display_name'] ?? 'کاربر') ?></span>
                                    <?php if (!empty($user['is_vip'])): ?>
                                    <span class="text-yellow-400" title="VIP">👑</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($user['username'])): ?>
                                <a href="https://t.me/<?= htmlspecialchars($user['username']) ?>" target="_blank" class="text-blue-400 text-xs hover:underline truncate block">
                                    @<?= htmlspecialchars($user['username']) ?>
                                </a>
                                <?php else: ?>
                                <span class="text-white/40 text-xs">بدون یوزرنیم</span>
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
                        <span class="text-white/40">هرگز</span>
                        <?php endif; ?>
                    </td>
                    
                    <!-- Status -->
                    <td class="py-3 px-4 hidden lg:table-cell">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium
                            <?= !empty($user['blocked']) ? 'bg-red-500/20 text-red-300' : 
                               (!empty($user['is_vip']) ? 'bg-yellow-500/20 text-yellow-300' : 
                               ($user['status_color'] === 'green' ? 'bg-green-500/20 text-green-300' : 
                               'bg-gray-500/20 text-gray-300')) ?>">
                            <?= htmlspecialchars($user['status_icon'] ?? '❓') ?>
                            <span><?= htmlspecialchars($user['status_text'] ?? 'نامشخص') ?></span>
                        </span>
                    </td>
                    
                    <!-- Actions -->
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <a 
                                href="/admin/chat.php?id=<?= $user['id'] ?>" 
                                class="bg-blue-500/20 hover:bg-blue-500/30 text-blue-300 p-2 rounded-lg transition"
                                title="چت با کاربر"
                            >
                                <i class="fas fa-comments text-sm"></i>
                            </a>
                            
                            <button 
                                onclick="toggleVip(<?= $user['id'] ?>, <?= $user['is_vip'] ? 0 : 1 ?>)"
                                class="<?= !empty($user['is_vip']) ? 'bg-yellow-500/20 hover:bg-yellow-500/30 text-yellow-300' : 'bg-white/10 hover:bg-white/20 text-white' ?> p-2 rounded-lg transition"
                                title="<?= !empty($user['is_vip']) ? 'حذف VIP' : 'VIP کردن' ?>"
                            >
                                <i class="fas fa-crown text-sm"></i>
                            </button>
                            
                            <button 
                                onclick="toggleBlock(<?= $user['id'] ?>, <?= $user['blocked'] ? 0 : 1 ?>)"
                                class="<?= !empty($user['blocked']) ? 'bg-green-500/20 hover:bg-green-500/30 text-green-300' : 'bg-red-500/20 hover:bg-red-500/30 text-red-300' ?> p-2 rounded-lg transition"
                                title="<?= !empty($user['blocked']) ? 'آن‌بلاک' : 'بلاک' ?>"
                            >
                                <i class="fas fa-<?= !empty($user['blocked']) ? 'check' : 'ban' ?> text-sm"></i>
                            </button>
                            
                            <button 
                                onclick="deleteUser(<?= $user['id'] ?>)"
                                class="bg-red-500/20 hover:bg-red-500/30 text-red-300 p-2 rounded-lg transition"
                                title="حذف کاربر"
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
    
    <!-- ═══ Pagination ═══ -->
    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="border-t border-white/10 p-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            
            <!-- Info -->
            <div class="text-white/60 text-sm">
                نمایش <?= number_format($pagination['from'] ?? 0) ?> تا <?= number_format($pagination['to'] ?? 0) ?> از <?= number_format($pagination['total']) ?> کاربر
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
                    <span class="hidden sm:inline">قبلی</span>
                </a>
                <?php else: ?>
                <span class="bg-white/5 text-white/30 px-3 py-2 rounded-lg flex items-center gap-1 cursor-not-allowed">
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="hidden sm:inline">قبلی</span>
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
                    <span class="hidden sm:inline">بعدی</span>
                    <i class="fas fa-chevron-left text-xs"></i>
                </a>
                <?php else: ?>
                <span class="bg-white/5 text-white/30 px-3 py-2 rounded-lg flex items-center gap-1 cursor-not-allowed">
                    <span class="hidden sm:inline">بعدی</span>
                    <i class="fas fa-chevron-left text-xs"></i>
                </span>
                <?php endif; ?>
                
            </div>
            
        </div>
    </div>
    <?php endif; ?>
    
    <?php endif; ?>
    
</div>

<!-- ═══ JavaScript ═══ -->
<script>
// ═══ Select All ═══
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

// ═══ Update Selected Count ═══
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

// ═══ Get Selected Count ═══
function getSelectedCount() {
    return document.querySelectorAll('.user-checkbox:checked').length;
}

// ═══ Get Selected IDs ═══
function getSelectedIds() {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// ═══ Bulk Action ═══
async function bulkAction(action) {
    const ids = getSelectedIds();
    
    if (ids.length === 0) {
        showToast('لطفاً حداقل یک کاربر انتخاب کنید', 'warning');
        return;
    }
    
    const actionTexts = {
        'make_vip': 'VIP کردن',
        'remove_vip': 'حذف VIP',
        'block': 'بلاک کردن',
        'unblock': 'آن‌بلاک کردن'
    };
    
    if (!confirm(`آیا مطمئن هستید که می‌خواهید ${ids.length} کاربر را ${actionTexts[action]} کنید؟`)) {
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
            showToast(`${data.affected} کاربر با موفقیت تغییر کرد`, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'خطا در عملیات', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
}

// ═══ Toggle VIP ═══
async function toggleVip(userId, newValue) {
    if (!confirm(`آیا مطمئن هستید؟`)) {
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
            showToast(newValue ? 'کاربر VIP شد' : 'VIP حذف شد', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'خطا', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
}

// ═══ Toggle Block ═══
async function toggleBlock(userId, newValue) {
    if (!confirm(`آیا مطمئن هستید؟`)) {
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
            showToast(newValue ? 'کاربر بلاک شد' : 'کاربر آن‌بلاک شد', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'خطا', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
}

// ═══ Delete User ═══
async function deleteUser(userId) {
    if (!confirm('آیا مطمئن هستید که می‌خواهید این کاربر را حذف کنید؟\n\nاین عمل غیرقابل بازگشت است!')) {
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
            showToast('کاربر حذف شد', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'خطا', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
}

// ═══ Export Users ═══
function exportUsers() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = `/admin/api/users/export?${params.toString()}`;
    showToast('در حال دانلود فایل...', 'info');
}
</script>