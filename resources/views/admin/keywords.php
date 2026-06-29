<?php
/**
 * ============================================
 * Keywords Management - مدیریت کلمات کلیدی
 * ============================================
 * نسخه: 2.0.0
 * 
 * لیست، ایجاد، ویرایش و حذف کلمات کلیدی
 * تست تطابق، Import/Export
 */

// متغیرهای مورد نیاز از Controller:
// - $keywords (آرایه کلمات کلیدی)
// - $pagination (اطلاعات صفحه‌بندی)
// - $filters (فیلترهای فعلی)
// - $stats (آمار کلمات کلیدی)

$keywords = $keywords ?? [];
$pagination = $pagination ?? ['total' => 0, 'current_page' => 1, 'total_pages' => 1];
$filters = $filters ?? ['search' => '', 'active' => '', 'answer_type' => '', 'sort' => 'priority', 'order' => 'DESC'];
$stats = $stats ?? ['total' => 0, 'active' => 0, 'inactive' => 0, 'total_matches' => 0];

// CSRF Token
$csrfToken = $_SESSION['_csrf_token'] ?? '';
?>

<!-- ═══ آمار سریع ═══ -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">🔑</div>
        <div class="text-white/60 text-xs mb-1">کل کلمات</div>
        <div class="text-white text-2xl font-bold"><?= number_format($stats['total'] ?? 0) ?></div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">✅</div>
        <div class="text-white/60 text-xs mb-1">فعال</div>
        <div class="text-green-400 text-2xl font-bold"><?= number_format($stats['active'] ?? 0) ?></div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">⏸️</div>
        <div class="text-white/60 text-xs mb-1">غیرفعال</div>
        <div class="text-red-400 text-2xl font-bold"><?= number_format($stats['inactive'] ?? 0) ?></div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">🎯</div>
        <div class="text-white/60 text-xs mb-1">کل تطابق‌ها</div>
        <div class="text-purple-400 text-2xl font-bold"><?= number_format($stats['total_matches'] ?? 0) ?></div>
    </div>
</div>

<!-- ═══ دکمه‌های عملیات ═══ -->
<div class="flex flex-wrap gap-3 mb-6">
    <button 
        onclick="openKeywordModal()"
        class="bg-gradient-to-r from-purple-500 to-blue-500 text-white px-6 py-2.5 rounded-lg hover:opacity-90 transition flex items-center gap-2"
    >
        <i class="fas fa-plus"></i>
        <span>افزودن کلمه کلیدی</span>
    </button>
    
    <button 
        onclick="openTestModal()"
        class="bg-green-500/20 border border-green-500/50 text-green-300 px-6 py-2.5 rounded-lg hover:bg-green-500/30 transition flex items-center gap-2"
    >
        <i class="fas fa-vial"></i>
        <span>تست کلمه کلیدی</span>
    </button>
    
    <button 
        onclick="importKeywords()"
        class="bg-blue-500/20 border border-blue-500/50 text-blue-300 px-6 py-2.5 rounded-lg hover:bg-blue-500/30 transition flex items-center gap-2"
    >
        <i class="fas fa-upload"></i>
        <span>Import</span>
    </button>
    
    <button 
        onclick="exportKeywords()"
        class="bg-yellow-500/20 border border-yellow-500/50 text-yellow-300 px-6 py-2.5 rounded-lg hover:bg-yellow-500/30 transition flex items-center gap-2"
    >
        <i class="fas fa-download"></i>
        <span>Export</span>
    </button>
</div>

<!-- ═══ فیلترها و جستجو ═══ -->
<div class="glass rounded-2xl p-5 mb-6">
    <form method="GET" action="/admin/keywords.php" class="space-y-4">
        
        <!-- ردیف اول: جستجو -->
        <div>
            <label class="block text-white/70 text-sm mb-2">جستجو در کلمات کلیدی و پاسخ‌ها</label>
            <div class="relative">
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40">
                    <i class="fas fa-search"></i>
                </span>
                <input 
                    type="text" 
                    name="search" 
                    value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                    placeholder="جستجو..."
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 pr-10 pl-4 text-white placeholder-white/40 focus:border-purple-500 transition"
                >
            </div>
        </div>
        
        <!-- ردیف دوم: فیلترها -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- فیلتر وضعیت -->
            <div>
                <label class="block text-white/70 text-sm mb-2">وضعیت</label>
                <select 
                    name="active" 
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                >
                    <option value="" <?= ($filters['active'] ?? '') === '' ? 'selected' : '' ?>>همه</option>
                    <option value="1" <?= ($filters['active'] ?? '') === '1' ? 'selected' : '' ?>>✅ فعال</option>
                    <option value="0" <?= ($filters['active'] ?? '') === '0' ? 'selected' : '' ?>>⏸️ غیرفعال</option>
                </select>
            </div>
            
            <!-- فیلتر نوع پاسخ -->
            <div>
                <label class="block text-white/70 text-sm mb-2">نوع پاسخ</label>
                <select 
                    name="answer_type" 
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                >
                    <option value="" <?= empty($filters['answer_type']) ? 'selected' : '' ?>>همه</option>
                    <option value="text" <?= ($filters['answer_type'] ?? '') === 'text' ? 'selected' : '' ?>>💬 متنی</option>
                    <option value="photo" <?= ($filters['answer_type'] ?? '') === 'photo' ? 'selected' : '' ?>>🖼️ عکس</option>
                    <option value="video" <?= ($filters['answer_type'] ?? '') === 'video' ? 'selected' : '' ?>>🎥 ویدئو</option>
                    <option value="document" <?= ($filters['answer_type'] ?? '') === 'document' ? 'selected' : '' ?>>📄 فایل</option>
                    <option value="audio" <?= ($filters['answer_type'] ?? '') === 'audio' ? 'selected' : '' ?>>🎵 صدا</option>
                    <option value="voice" <?= ($filters['answer_type'] ?? '') === 'voice' ? 'selected' : '' ?>>🎤 ویس</option>
                    <option value="sticker" <?= ($filters['answer_type'] ?? '') === 'sticker' ? 'selected' : '' ?>>🎭 استیکر</option>
                </select>
            </div>
            
        </div>
        
        <!-- ردیف سوم: دکمه‌ها -->
        <div class="flex flex-wrap gap-3">
            <button 
                type="submit" 
                class="bg-gradient-to-r from-purple-500 to-blue-500 text-white px-6 py-2.5 rounded-lg hover:opacity-90 transition flex items-center gap-2"
            >
                <i class="fas fa-filter"></i>
                <span>اعمال فیلتر</span>
            </button>
            
            <a 
                href="/admin/keywords.php" 
                class="bg-white/10 text-white px-6 py-2.5 rounded-lg hover:bg-white/20 transition flex items-center gap-2"
            >
                <i class="fas fa-times"></i>
                <span>پاک کردن فیلترها</span>
            </a>
        </div>
        
    </form>
</div>

<!-- ═══ عملیات دسته‌جمعی ═══ -->
<?php if (!empty($keywords)): ?>
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
                onclick="bulkAction('activate')" 
                class="bg-green-500/20 border border-green-500/50 text-green-300 px-4 py-2 rounded-lg hover:bg-green-500/30 transition text-sm flex items-center gap-2"
            >
                <i class="fas fa-check"></i>
                <span>فعال کردن</span>
            </button>
            <button 
                onclick="bulkAction('deactivate')" 
                class="bg-yellow-500/20 border border-yellow-500/50 text-yellow-300 px-4 py-2 rounded-lg hover:bg-yellow-500/30 transition text-sm flex items-center gap-2"
            >
                <i class="fas fa-pause"></i>
                <span>غیرفعال کردن</span>
            </button>
            <button 
                onclick="bulkAction('delete')" 
                class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-2 rounded-lg hover:bg-red-500/30 transition text-sm flex items-center gap-2"
            >
                <i class="fas fa-trash"></i>
                <span>حذف</span>
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ═══ لیست کلمات کلیدی ═══ -->
<div class="glass rounded-2xl overflow-hidden">
    
    <?php if (empty($keywords)): ?>
    <!-- Empty State -->
    <div class="text-center py-16">
        <div class="text-6xl mb-4">🔑</div>
        <h3 class="text-white text-xl font-bold mb-2">هنوز کلمه کلیدی‌ای تعریف نشده</h3>
        <p class="text-white/50 text-sm mb-6">کلمات کلیدی به ربات کمک می‌کنن تا به صورت خودکار به پیام‌های کاربران پاسخ بده</p>
        <button 
            onclick="openKeywordModal()"
            class="inline-block bg-purple-500/20 border border-purple-500/50 text-purple-300 px-6 py-2.5 rounded-lg hover:bg-purple-500/30 transition"
        >
            افزودن اولین کلمه کلیدی
        </button>
    </div>
    <?php else: ?>
    
    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-white/5 border-b border-white/10">
                    <th class="text-right py-3 px-4 text-white/70 font-medium">
                        <input type="checkbox" class="keyword-checkbox" style="display: none;">
                    </th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">کلمه کلیدی</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden md:table-cell">پاسخ</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden lg:table-cell">نوع</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden lg:table-cell">
                        <a href="?sort=priority&order=<?= ($filters['sort'] ?? '') === 'priority' && ($filters['order'] ?? '') === 'ASC' ? 'DESC' : 'ASC' ?>&<?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="hover:text-white transition flex items-center gap-1">
                            <span>اولویت</span>
                            <?php if (($filters['sort'] ?? '') === 'priority'): ?>
                            <i class="fas fa-sort-<?= ($filters['order'] ?? '') === 'ASC' ? 'up' : 'down' ?> text-xs"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden md:table-cell">تطابق‌ها</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">وضعیت</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($keywords as $kw): ?>
                <tr class="border-b border-white/5 hover:bg-white/5 transition">
                    
                    <!-- Checkbox -->
                    <td class="py-3 px-4">
                        <input 
                            type="checkbox" 
                            value="<?= $kw['id'] ?>"
                            class="keyword-checkbox w-4 h-4 rounded border-white/20 bg-white/10 text-purple-500 focus:ring-purple-500"
                            onchange="updateSelectedCount()"
                        >
                    </td>
                    
                    <!-- Keyword -->
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <span class="text-lg"><?= $kw['type_icon'] ?? '💬' ?></span>
                            <div>
                                <div class="text-white font-medium">
                                    <?= htmlspecialchars($kw['keyword']) ?>
                                </div>
                                <?php if (!empty($kw['features'])): ?>
                                <div class="flex gap-1 mt-1">
                                    <?php foreach ($kw['features'] as $feature): ?>
                                    <span class="text-xs bg-white/10 text-white/60 px-1.5 py-0.5 rounded">
                                        <?= htmlspecialchars($feature) ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    
                    <!-- Answer -->
                    <td class="py-3 px-4 hidden md:table-cell">
                        <div class="text-white/70 text-sm truncate max-w-xs" title="<?= htmlspecialchars($kw['answer'] ?? '') ?>">
                            <?= htmlspecialchars($kw['answer_preview'] ?? $kw['answer'] ?? '-') ?>
                        </div>
                    </td>
                    
                    <!-- Type -->
                    <td class="py-3 px-4 hidden lg:table-cell">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-white/10 text-white">
                            <span><?= $kw['type_icon'] ?? '💬' ?></span>
                            <span><?= htmlspecialchars($kw['type_text'] ?? 'متنی') ?></span>
                        </span>
                    </td>
                    
                    <!-- Priority -->
                    <td class="py-3 px-4 hidden lg:table-cell">
                        <div class="text-white font-mono text-sm">
                            <?= number_format($kw['priority'] ?? 0) ?>
                        </div>
                    </td>
                    
                    <!-- Matches -->
                    <td class="py-3 px-4 hidden md:table-cell">
                        <div class="text-purple-400 font-bold">
                            <?= number_format($kw['match_count'] ?? 0) ?>
                        </div>
                        <?php if (!empty($kw['last_matched_ago'])): ?>
                        <div class="text-white/40 text-xs">
                            آخرین: <?= htmlspecialchars($kw['last_matched_ago']) ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    
                    <!-- Status -->
                    <td class="py-3 px-4">
                        <button 
                            onclick="toggleKeyword(<?= $kw['id'] ?>, <?= $kw['active'] ? 0 : 1 ?>)"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium cursor-pointer transition
                                <?= !empty($kw['active']) ? 'bg-green-500/20 text-green-300 hover:bg-green-500/30' : 'bg-gray-500/20 text-gray-300 hover:bg-gray-500/30' ?>"
                        >
                            <span><?= htmlspecialchars($kw['status_icon'] ?? '❓') ?></span>
                            <span><?= htmlspecialchars($kw['status_text'] ?? 'نامشخص') ?></span>
                        </button>
                    </td>
                    
                    <!-- Actions -->
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <button 
                                onclick="editKeyword(<?= $kw['id'] ?>)"
                                class="bg-blue-500/20 hover:bg-blue-500/30 text-blue-300 p-2 rounded-lg transition"
                                title="ویرایش"
                            >
                                <i class="fas fa-edit text-sm"></i>
                            </button>
                            
                            <button 
                                onclick="viewKeywordStats(<?= $kw['id'] ?>)"
                                class="bg-purple-500/20 hover:bg-purple-500/30 text-purple-300 p-2 rounded-lg transition"
                                title="آمار"
                            >
                                <i class="fas fa-chart-bar text-sm"></i>
                            </button>
                            
                            <button 
                                onclick="deleteKeyword(<?= $kw['id'] ?>)"
                                class="bg-red-500/20 hover:bg-red-500/30 text-red-300 p-2 rounded-lg transition"
                                title="حذف"
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
                نمایش <?= number_format($pagination['from'] ?? 0) ?> تا <?= number_format($pagination['to'] ?? 0) ?> از <?= number_format($pagination['total']) ?> کلمه کلیدی
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

<!-- ═══ Modal for Add/Edit Keyword ═══ -->
<div id="keywordModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="glass rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        
        <!-- Modal Header -->
        <div class="p-5 border-b border-white/10 flex items-center justify-between">
            <h3 class="text-white font-bold text-lg flex items-center gap-2" id="keywordModalTitle">
                <i class="fas fa-plus-circle"></i>
                <span>افزودن کلمه کلیدی</span>
            </h3>
            <button 
                onclick="closeKeywordModal()"
                class="text-white/60 hover:text-white p-2 rounded-lg hover:bg-white/10 transition"
            >
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Content -->
        <div class="p-5">
            <form id="keywordForm">
                <input type="hidden" id="keywordId" name="id">
                
                <!-- Keyword -->
                <div class="mb-4">
                    <label class="block text-white/70 text-sm mb-2">کلمه کلیدی <span class="text-red-400">*</span></label>
                    <input 
                        type="text" 
                        id="keywordInput"
                        name="keyword"
                        required
                        placeholder="مثلاً: قیمت، سلام، راهنما"
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition"
                    >
                    <p class="text-white/40 text-xs mt-1">کلمه یا عبارتی که می‌خواهید ربات به آن پاسخ بده</p>
                </div>
                
                <!-- Answer Type -->
                <div class="mb-4">
                    <label class="block text-white/70 text-sm mb-2">نوع پاسخ</label>
                    <select 
                        id="answerType"
                        name="answer_type"
                        onchange="toggleFileType()"
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                    >
                        <option value="text">💬 متنی</option>
                        <option value="photo">🖼️ عکس</option>
                        <option value="video">🎥 ویدئو</option>
                        <option value="document">📄 فایل</option>
                        <option value="audio">🎵 صدا</option>
                        <option value="voice">🎤 ویس</option>
                        <option value="sticker">🎭 استیکر</option>
                    </select>
                </div>
                
                <!-- Answer Text -->
                <div class="mb-4">
                    <label class="block text-white/70 text-sm mb-2">پاسخ <span class="text-red-400">*</span></label>
                    <textarea 
                        id="answerInput"
                        name="answer"
                        rows="4"
                        required
                        placeholder="پاسخ ربات به این کلمه کلیدی..."
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition resize-none"
                    ></textarea>
                    <p class="text-white/40 text-xs mt-1">می‌توانید از HTML برای فرمت‌دهی استفاده کنید</p>
                </div>
                
                <!-- File ID (for non-text types) -->
                <div class="mb-4 hidden" id="fileIdGroup">
                    <label class="block text-white/70 text-sm mb-2">File ID</label>
                    <input 
                        type="text" 
                        id="fileIdInput"
                        name="file_id"
                        placeholder="File ID از تلگرام..."
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition font-mono text-sm"
                        dir="ltr"
                    >
                    <p class="text-white/40 text-xs mt-1">File ID فایل، عکس، ویدئو و غیره از تلگرام</p>
                </div>
                
                <!-- Priority -->
                <div class="mb-4">
                    <label class="block text-white/70 text-sm mb-2">اولویت</label>
                    <input 
                        type="number" 
                        id="priorityInput"
                        name="priority"
                        value="0"
                        min="0"
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                    >
                    <p class="text-white/40 text-xs mt-1">کلمات با اولویت بالاتر زودتر بررسی می‌شن</p>
                </div>
                
                <!-- Options -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            id="exactMatchInput"
                            name="exact_match"
                            class="w-5 h-5 rounded border-white/20 bg-white/10 text-purple-500 focus:ring-purple-500"
                        >
                        <span class="text-white text-sm">تطابق دقیق</span>
                    </label>
                    
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            id="caseSensitiveInput"
                            name="case_sensitive"
                            class="w-5 h-5 rounded border-white/20 bg-white/10 text-purple-500 focus:ring-purple-500"
                        >
                        <span class="text-white text-sm">حساس به حروف</span>
                    </label>
                </div>
                
                <!-- Active -->
                <div class="mb-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            id="activeInput"
                            name="active"
                            checked
                            class="w-5 h-5 rounded border-white/20 bg-white/10 text-purple-500 focus:ring-purple-500"
                        >
                        <span class="text-white text-sm">فعال</span>
                    </label>
                </div>
                
                <!-- Buttons -->
                <div class="flex gap-3">
                    <button 
                        type="button" 
                        onclick="closeKeywordModal()"
                        class="flex-1 bg-white/10 text-white px-4 py-2.5 rounded-lg hover:bg-white/20 transition"
                    >
                        انصراف
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 bg-gradient-to-r from-purple-500 to-blue-500 text-white px-4 py-2.5 rounded-lg hover:opacity-90 transition"
                    >
                        ذخیره
                    </button>
                </div>
                
            </form>
        </div>
        
    </div>
</div>

<!-- ═══ Modal for Test Keyword ═══ -->
<div id="testModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="glass rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        
        <div class="p-5 border-b border-white/10 flex items-center justify-between">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                <i class="fas fa-vial"></i>
                <span>تست کلمه کلیدی</span>
            </h3>
            <button 
                onclick="closeTestModal()"
                class="text-white/60 hover:text-white p-2 rounded-lg hover:bg-white/10 transition"
            >
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="p-5">
            <div class="mb-4">
                <label class="block text-white/70 text-sm mb-2">متن برای تست</label>
                <textarea 
                    id="testInput"
                    rows="3"
                    placeholder="متنی که می‌خواهید تست کنید..."
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition resize-none"
                ></textarea>
            </div>
            
            <button 
                onclick="runTest()"
                class="w-full bg-gradient-to-r from-green-500 to-emerald-500 text-white px-4 py-2.5 rounded-lg hover:opacity-90 transition mb-4"
            >
                <i class="fas fa-play"></i>
                <span>اجرای تست</span>
            </button>
            
            <div id="testResults" class="hidden">
                <!-- Results will be loaded here -->
            </div>
        </div>
        
    </div>
</div>

<!-- ═══ Modal for Keyword Stats ═══ -->
<div id="statsModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="glass rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        
        <div class="p-5 border-b border-white/10 flex items-center justify-between">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                <i class="fas fa-chart-bar"></i>
                <span>آمار کلمه کلیدی</span>
            </h3>
            <button 
                onclick="closeStatsModal()"
                class="text-white/60 hover:text-white p-2 rounded-lg hover:bg-white/10 transition"
            >
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="statsModalContent" class="p-5">
            <!-- Stats will be loaded here -->
        </div>
        
    </div>
</div>

<!-- ═══ Hidden File Input for Import ═══ -->
<input type="file" id="importFile" accept=".json" style="display: none;" onchange="handleImport(event)">

<!-- ═══ JavaScript ═══ -->
<script>
// ═══ Toggle File Type ═══
function toggleFileType() {
    const type = document.getElementById('answerType').value;
    const fileIdGroup = document.getElementById('fileIdGroup');
    
    if (type === 'text') {
        fileIdGroup.classList.add('hidden');
    } else {
        fileIdGroup.classList.remove('hidden');
    }
}

// ═══ Open Keyword Modal ═══
function openKeywordModal(keywordId = null) {
    document.getElementById('keywordForm').reset();
    document.getElementById('keywordId').value = '';
    document.getElementById('activeInput').checked = true;
    document.getElementById('priorityInput').value = 0;
    
    if (keywordId) {
        document.getElementById('keywordModalTitle').innerHTML = '<i class="fas fa-edit"></i><span>ویرایش کلمه کلیدی</span>';
        loadKeywordData(keywordId);
    } else {
        document.getElementById('keywordModalTitle').innerHTML = '<i class="fas fa-plus-circle"></i><span>افزودن کلمه کلیدی</span>';
    }
    
    document.getElementById('keywordModal').classList.remove('hidden');
    document.getElementById('keywordModal').classList.add('flex');
}

// ═══ Close Keyword Modal ═══
function closeKeywordModal() {
    document.getElementById('keywordModal').classList.add('hidden');
    document.getElementById('keywordModal').classList.remove('flex');
}

// ═══ Load Keyword Data ═══
async function loadKeywordData(keywordId) {
    try {
        const response = await fetch(`/admin/api/keywords/${keywordId}`);
        const data = await response.json();
        
        if (data.success) {
            const kw = data.data;
            
            document.getElementById('keywordId').value = kw.id;
            document.getElementById('keywordInput').value = kw.keyword;
            document.getElementById('answerType').value = kw.answer_type || 'text';
            document.getElementById('answerInput').value = kw.answer;
            document.getElementById('fileIdInput').value = kw.file_id || '';
            document.getElementById('priorityInput').value = kw.priority || 0;
            document.getElementById('exactMatchInput').checked = kw.exact_match == 1;
            document.getElementById('caseSensitiveInput').checked = kw.case_sensitive == 1;
            document.getElementById('activeInput').checked = kw.active == 1;
            
            toggleFileType();
        } else {
            showToast(data.error || 'خطا در دریافت اطلاعات', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
}

// ═══ Edit Keyword ═══
function editKeyword(keywordId) {
    openKeywordModal(keywordId);
}

// ═══ Save Keyword ═══
document.getElementById('keywordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        id: formData.get('id'),
        keyword: formData.get('keyword'),
        answer_type: formData.get('answer_type'),
        answer: formData.get('answer'),
        file_id: formData.get('file_id'),
        priority: parseInt(formData.get('priority')) || 0,
        exact_match: formData.get('exact_match') ? 1 : 0,
        case_sensitive: formData.get('case_sensitive') ? 1 : 0,
        active: formData.get('active') ? 1 : 0
    };
    
    try {
        const url = data.id ? '/admin/api/keywords/update' : '/admin/api/keywords/create';
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(data.id ? 'کلمه کلیدی بروزرسانی شد' : 'کلمه کلیدی اضافه شد', 'success');
            closeKeywordModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.error || 'خطا', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
});

// ═══ Toggle Keyword ═══
async function toggleKeyword(keywordId, newValue) {
    try {
        const response = await fetch('/admin/api/keywords/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ id: keywordId, active: newValue })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(newValue ? 'کلمه کلیدی فعال شد' : 'کلمه کلیدی غیرفعال شد', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'خطا', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
}

// ═══ Delete Keyword ═══
async function deleteKeyword(keywordId) {
    if (!confirm('آیا مطمئن هستید که می‌خواهید این کلمه کلیدی را حذف کنید؟')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/api/keywords/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ id: keywordId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('کلمه کلیدی حذف شد', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'خطا', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
}

// ═══ Bulk Actions ═══
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.keyword-checkbox');
    const bulkActions = document.getElementById('bulkActions');
    
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    
    updateSelectedCount();
    
    if (bulkActions) {
        bulkActions.style.display = selectAll.checked || getSelectedCount() > 0 ? 'block' : 'none';
    }
}

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

function getSelectedCount() {
    return document.querySelectorAll('.keyword-checkbox:checked').length;
}

function getSelectedIds() {
    const checkboxes = document.querySelectorAll('.keyword-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

async function bulkAction(action) {
    const ids = getSelectedIds();
    
    if (ids.length === 0) {
        showToast('لطفاً حداقل یک کلمه کلیدی انتخاب کنید', 'warning');
        return;
    }
    
    const actionTexts = {
        'activate': 'فعال کردن',
        'deactivate': 'غیرفعال کردن',
        'delete': 'حذف'
    };
    
    if (!confirm(`آیا مطمئن هستید که می‌خواهید ${ids.length} کلمه کلیدی را ${actionTexts[action]} کنید؟`)) {
        return;
    }
    
    try {
        const response = await fetch('/admin/api/keywords/bulk', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ ids, action })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(`${data.affected} کلمه کلیدی تغییر کرد`, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'خطا', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
}

// ═══ Test Keyword ═══
function openTestModal() {
    document.getElementById('testInput').value = '';
    document.getElementById('testResults').classList.add('hidden');
    document.getElementById('testModal').classList.remove('hidden');
    document.getElementById('testModal').classList.add('flex');
}

function closeTestModal() {
    document.getElementById('testModal').classList.add('hidden');
    document.getElementById('testModal').classList.remove('flex');
}

async function runTest() {
    const text = document.getElementById('testInput').value.trim();
    
    if (!text) {
        showToast('لطفاً متنی وارد کنید', 'warning');
        return;
    }
    
    try {
        const response = await fetch('/admin/api/keywords/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ text })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const results = data.data;
            let html = '<div class="space-y-3">';
            
            if (results.matched) {
                html += `<div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4">
                    <div class="text-green-300 font-bold mb-2">✅ تطابق پیدا شد!</div>
                    <div class="text-white text-sm">تعداد تطابق‌ها: ${results.count}</div>
                </div>`;
                
                if (results.all_matches && results.all_matches.length > 0) {
                    html += '<div class="space-y-2 mt-4">';
                    results.all_matches.forEach(match => {
                        html += `<div class="bg-white/5 rounded-lg p-3">
                            <div class="text-white font-medium mb-1">${match.keyword}</div>
                            <div class="text-white/60 text-sm">${match.answer_preview || match.answer}</div>
                        </div>`;
                    });
                    html += '</div>';
                }
            } else {
                html += `<div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4">
                    <div class="text-red-300 font-bold">❌ هیچ تطابقی پیدا نشد</div>
                </div>`;
            }
            
            html += '</div>';
            
            document.getElementById('testResults').innerHTML = html;
            document.getElementById('testResults').classList.remove('hidden');
        } else {
            showToast(data.error || 'خطا', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
}

// ═══ View Keyword Stats ═══
async function viewKeywordStats(keywordId) {
    try {
        const response = await fetch(`/admin/api/keywords/stats/${keywordId}`);
        const data = await response.json();
        
        if (data.success) {
            const stats = data.data;
            
            let html = `
                <div class="space-y-4">
                    <div class="bg-white/5 rounded-lg p-4">
                        <div class="text-white/60 text-xs mb-1">کلمه کلیدی</div>
                        <div class="text-white font-bold text-lg">${stats.keyword.keyword}</div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-purple-500/10 border border-purple-500/30 rounded-lg p-4 text-center">
                            <div class="text-white/60 text-xs mb-1">تعداد تطابق‌ها</div>
                            <div class="text-purple-400 text-3xl font-bold">${stats.match_count}</div>
                        </div>
                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 text-center">
                            <div class="text-white/60 text-xs mb-1">آخرین تطابق</div>
                            <div class="text-blue-400 text-sm">${stats.last_match || 'هرگز'}</div>
                        </div>
                    </div>
                    
                    ${stats.daily_matches && stats.daily_matches.length > 0 ? `
                    <div>
                        <div class="text-white/60 text-sm mb-2">تطابق‌های 30 روز اخیر</div>
                        <div class="space-y-1">
                            ${stats.daily_matches.map(d => `
                                <div class="flex justify-between items-center bg-white/5 rounded p-2">
                                    <span class="text-white/70 text-sm">${d.date}</span>
                                    <span class="text-purple-400 font-bold">${d.count}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    ` : '<div class="text-white/50 text-center py-4">هنوز تطابقی ثبت نشده</div>'}
                </div>
            `;
            
            document.getElementById('statsModalContent').innerHTML = html;
            document.getElementById('statsModal').classList.remove('hidden');
            document.getElementById('statsModal').classList.add('flex');
        } else {
            showToast(data.error || 'خطا', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
}

function closeStatsModal() {
    document.getElementById('statsModal').classList.add('hidden');
    document.getElementById('statsModal').classList.remove('flex');
}

// ═══ Import/Export ═══
function importKeywords() {
    document.getElementById('importFile').click();
}

function handleImport(event) {
    const file = event.target.files[0];
    
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = async function(e) {
        try {
            const data = JSON.parse(e.target.result);
            
            if (!confirm(`آیا می‌خواهید ${data.keywords?.length || 0} کلمه کلیدی را وارد کنید؟`)) {
                return;
            }
            
            const response = await fetch('/admin/api/keywords/import', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: JSON.stringify({ keywords: data.keywords || data })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast(`${result.imported} کلمه کلیدی وارد شد`, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(result.error || 'خطا', 'error');
            }
        } catch (error) {
            showToast('فایل JSON نامعتبر است', 'error');
        }
    };
    
    reader.readAsText(file);
}

function exportKeywords() {
    window.location.href = '/admin/api/keywords/export';
    showToast('در حال دانلود فایل...', 'info');
}

// ═══ Close Modals on Escape ═══
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeKeywordModal();
        closeTestModal();
        closeStatsModal();
    }
});

// ═══ Close Modals on Outside Click ═══
['keywordModal', 'testModal', 'statsModal'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
            this.classList.remove('flex');
        }
    });
});
</script>