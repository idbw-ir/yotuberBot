<?php
/**
 * ============================================
 * Donations Management - مدیریت دونیت‌ها
 * ============================================
 * نسخه: 2.0.0
 * 
 * لیست دونیت‌ها با فیلتر، جستجو و عملیات
 * از layout اصلی (admin.php) استفاده می‌کنه
 */

// متغیرهای مورد نیاز از Controller:
// - $donations (آرایه دونیت‌ها)
// - $pagination (اطلاعات صفحه‌بندی)
// - $filters (فیلترهای فعلی)
// - $stats (آمار دونیت‌ها)

$donations = $donations ?? [];
$pagination = $pagination ?? ['total' => 0, 'current_page' => 1, 'total_pages' => 1];
$filters = $filters ?? ['status' => '', 'gateway' => '', 'min_amount' => '', 'max_amount' => '', 'sort' => 'created_at', 'order' => 'DESC'];
$stats = $stats ?? ['total_amount' => 0, 'total_count' => 0, 'today_amount' => 0, 'month_amount' => 0, 'pending' => 0, 'average' => 0];

// CSRF Token
$csrfToken = $_SESSION['_csrf_token'] ?? '';
?>

<!-- ═══ آمار سریع ═══ -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">💰</div>
        <div class="text-white/60 text-xs mb-1">مجموع دونیت</div>
        <div class="text-green-400 text-xl font-bold"><?= number_format($stats['total_amount'] ?? 0) ?></div>
        <div class="text-white/40 text-xs">تومان</div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">💳</div>
        <div class="text-white/60 text-xs mb-1">تعداد کل</div>
        <div class="text-white text-xl font-bold"><?= number_format($stats['total_count'] ?? 0) ?></div>
        <div class="text-white/40 text-xs">دونیت</div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">📅</div>
        <div class="text-white/60 text-xs mb-1">امروز</div>
        <div class="text-purple-400 text-xl font-bold"><?= number_format($stats['today_amount'] ?? 0) ?></div>
        <div class="text-white/40 text-xs">تومان</div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">📊</div>
        <div class="text-white/60 text-xs mb-1">این ماه</div>
        <div class="text-blue-400 text-xl font-bold"><?= number_format($stats['month_amount'] ?? 0) ?></div>
        <div class="text-white/40 text-xs">تومان</div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">⏳</div>
        <div class="text-white/60 text-xs mb-1">در انتظار</div>
        <div class="text-yellow-400 text-xl font-bold"><?= number_format($stats['pending'] ?? 0) ?></div>
        <div class="text-white/40 text-xs">دونیت</div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">💎</div>
        <div class="text-white/60 text-xs mb-1">میانگین</div>
        <div class="text-white text-xl font-bold"><?= number_format($stats['average'] ?? 0) ?></div>
        <div class="text-white/40 text-xs">تومان</div>
    </div>
</div>

<!-- ═══ فیلترها و جستجو ═══ -->
<div class="glass rounded-2xl p-5 mb-6">
    <form method="GET" action="/admin/donations.php" class="space-y-4">
        
        <!-- ردیف اول: وضعیت و درگاه -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- فیلتر وضعیت -->
            <div>
                <label class="block text-white/70 text-sm mb-2">وضعیت</label>
                <select 
                    name="status" 
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                >
                    <option value="" <?= empty($filters['status']) ? 'selected' : '' ?>>همه</option>
                    <option value="success" <?= ($filters['status'] ?? '') === 'success' ? 'selected' : '' ?>>✅ موفق</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>⏳ در انتظار</option>
                    <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>❌ ناموفق</option>
                </select>
            </div>
            
            <!-- فیلتر درگاه -->
            <div>
                <label class="block text-white/70 text-sm mb-2">درگاه پرداخت</label>
                <select 
                    name="gateway" 
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
                >
                    <option value="" <?= empty($filters['gateway']) ? 'selected' : '' ?>>همه</option>
                    <option value="zarinpal" <?= ($filters['gateway'] ?? '') === 'zarinpal' ? 'selected' : '' ?>>💳 زرین‌پال</option>
                    <option value="idpay" <?= ($filters['gateway'] ?? '') === 'idpay' ? 'selected' : '' ?>>💰 IDPay</option>
                    <option value="nextpay" <?= ($filters['gateway'] ?? '') === 'nextpay' ? 'selected' : '' ?>>🏦 NextPay</option>
                    <option value="nowpayments" <?= ($filters['gateway'] ?? '') === 'nowpayments' ? 'selected' : '' ?>>₿ NowPayments</option>
                    <option value="manual" <?= ($filters['gateway'] ?? '') === 'manual' ? 'selected' : '' ?>>✋ دستی</option>
                </select>
            </div>
            
        </div>
        
        <!-- ردیف دوم: بازه مبلغ -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- حداقل مبلغ -->
            <div>
                <label class="block text-white/70 text-sm mb-2">حداقل مبلغ (تومان)</label>
                <input 
                    type="number" 
                    name="min_amount" 
                    value="<?= htmlspecialchars($filters['min_amount'] ?? '') ?>"
                    placeholder="مثلاً: 10000"
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition"
                    min="0"
                >
            </div>
            
            <!-- حداکثر مبلغ -->
            <div>
                <label class="block text-white/70 text-sm mb-2">حداکثر مبلغ (تومان)</label>
                <input 
                    type="number" 
                    name="max_amount" 
                    value="<?= htmlspecialchars($filters['max_amount'] ?? '') ?>"
                    placeholder="مثلاً: 1000000"
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition"
                    min="0"
                >
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
                href="/admin/donations.php" 
                class="bg-white/10 text-white px-6 py-2.5 rounded-lg hover:bg-white/20 transition flex items-center gap-2"
            >
                <i class="fas fa-times"></i>
                <span>پاک کردن فیلترها</span>
            </a>
            
            <button 
                type="button" 
                onclick="exportDonations()" 
                class="bg-green-500/20 border border-green-500/50 text-green-300 px-6 py-2.5 rounded-lg hover:bg-green-500/30 transition flex items-center gap-2"
            >
                <i class="fas fa-download"></i>
                <span>خروجی CSV</span>
            </button>
            
            <button 
                type="button" 
                onclick="showFinancialReport()" 
                class="bg-blue-500/20 border border-blue-500/50 text-blue-300 px-6 py-2.5 rounded-lg hover:bg-blue-500/30 transition flex items-center gap-2"
            >
                <i class="fas fa-file-invoice"></i>
                <span>گزارش مالی</span>
            </button>
        </div>
        
    </form>
</div>

<!-- ═══ لیست دونیت‌ها ═══ -->
<div class="glass rounded-2xl overflow-hidden">
    
    <?php if (empty($donations)): ?>
    <!-- Empty State -->
    <div class="text-center py-16">
        <div class="text-6xl mb-4">💸</div>
        <h3 class="text-white text-xl font-bold mb-2">هنوز دونیتی ثبت نشده</h3>
        <p class="text-white/50 text-sm mb-6">وقتی کاربران حمایت مالی کنن، اینجا نمایش داده می‌شن</p>
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
                    <th class="text-right py-3 px-4 text-white/70 font-medium">آیدی</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">کاربر</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">
                        <a href="?sort=amount&order=<?= ($filters['sort'] ?? '') === 'amount' && ($filters['order'] ?? '') === 'ASC' ? 'DESC' : 'ASC' ?>&<?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="hover:text-white transition flex items-center gap-1">
                            <span>مبلغ</span>
                            <?php if (($filters['sort'] ?? '') === 'amount'): ?>
                            <i class="fas fa-sort-<?= ($filters['order'] ?? '') === 'ASC' ? 'up' : 'down' ?> text-xs"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden md:table-cell">درگاه</th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">
                        <a href="?sort=status&order=<?= ($filters['sort'] ?? '') === 'status' && ($filters['order'] ?? '') === 'ASC' ? 'DESC' : 'ASC' ?>&<?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="hover:text-white transition flex items-center gap-1">
                            <span>وضعیت</span>
                            <?php if (($filters['sort'] ?? '') === 'status'): ?>
                            <i class="fas fa-sort-<?= ($filters['order'] ?? '') === 'ASC' ? 'up' : 'down' ?> text-xs"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium hidden lg:table-cell">
                        <a href="?sort=created_at&order=<?= ($filters['sort'] ?? '') === 'created_at' && ($filters['order'] ?? '') === 'ASC' ? 'DESC' : 'ASC' ?>&<?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="hover:text-white transition flex items-center gap-1">
                            <span>تاریخ</span>
                            <?php if (($filters['sort'] ?? '') === 'created_at'): ?>
                            <i class="fas fa-sort-<?= ($filters['order'] ?? '') === 'ASC' ? 'up' : 'down' ?> text-xs"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="text-right py-3 px-4 text-white/70 font-medium">عملیات</th>
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
                                    <?= htmlspecialchars($d['user_display_name'] ?? 'کاربر') ?>
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
                        <div class="text-white/40 text-xs">تومان</div>
                    </td>
                    
                    <!-- Gateway -->
                    <td class="py-3 px-4 hidden md:table-cell">
                        <div class="flex items-center gap-2">
                            <span class="text-xl"><?= htmlspecialchars($d['gateway_icon'] ?? '💵') ?></span>
                            <span class="text-white/70 text-xs"><?= htmlspecialchars($d['gateway'] ?? 'نامشخص') ?></span>
                        </div>
                    </td>
                    
                    <!-- Status -->
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium
                            <?= ($d['status'] ?? '') === 'success' ? 'bg-green-500/20 text-green-300' : 
                               (($d['status'] ?? '') === 'pending' ? 'bg-yellow-500/20 text-yellow-300' : 
                               'bg-red-500/20 text-red-300') ?>">
                            <span><?= htmlspecialchars($d['status_icon'] ?? '❓') ?></span>
                            <span><?= htmlspecialchars($d['status_text'] ?? 'نامشخص') ?></span>
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
                                title="مشاهده جزئیات"
                            >
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                            
                            <?php if (($d['status'] ?? '') === 'pending'): ?>
                            <button 
                                onclick="approveDonation(<?= $d['id'] ?>)"
                                class="bg-green-500/20 hover:bg-green-500/30 text-green-300 p-2 rounded-lg transition"
                                title="تأیید"
                            >
                                <i class="fas fa-check text-sm"></i>
                            </button>
                            <button 
                                onclick="rejectDonation(<?= $d['id'] ?>)"
                                class="bg-red-500/20 hover:bg-red-500/30 text-red-300 p-2 rounded-lg transition"
                                title="رد"
                            >
                                <i class="fas fa-times text-sm"></i>
                            </button>
                            <?php endif; ?>
                            
                            <button 
                                onclick="deleteDonation(<?= $d['id'] ?>)"
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
                نمایش <?= number_format($pagination['from'] ?? 0) ?> تا <?= number_format($pagination['to'] ?? 0) ?> از <?= number_format($pagination['total']) ?> دونیت
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

<!-- ═══ Modal for Donation Details ═══ -->
<div id="donationModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="glass rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        
        <!-- Modal Header -->
        <div class="p-5 border-b border-white/10 flex items-center justify-between">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                <i class="fas fa-hand-holding-usd"></i>
                <span>جزئیات دونیت</span>
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

<!-- ═══ Modal for Reject Reason ═══ -->
<div id="rejectModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="glass rounded-2xl max-w-md w-full">
        
        <div class="p-5 border-b border-white/10">
            <h3 class="text-white font-bold text-lg">❌ رد دونیت</h3>
        </div>
        
        <div class="p-5">
            <form id="rejectForm">
                <input type="hidden" id="rejectDonationId" name="donation_id">
                
                <div class="mb-4">
                    <label class="block text-white/70 text-sm mb-2">دلیل رد (اختیاری)</label>
                    <textarea 
                        id="rejectReason"
                        name="reason"
                        rows="3"
                        placeholder="مثلاً: پرداخت ناموفق، اطلاعات نادرست..."
                        class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition resize-none"
                    ></textarea>
                </div>
                
                <div class="flex gap-3">
                    <button 
                        type="button" 
                        onclick="closeRejectModal()"
                        class="flex-1 bg-white/10 text-white px-4 py-2.5 rounded-lg hover:bg-white/20 transition"
                    >
                        انصراف
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white px-4 py-2.5 rounded-lg transition"
                    >
                        رد دونیت
                    </button>
                </div>
            </form>
        </div>
        
    </div>
</div>

<!-- ═══ JavaScript ═══ -->
<script>
// ═══ View Donation Details ═══
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
                            <label class="text-white/60 text-xs">آیدی دونیت</label>
                            <div class="text-white font-mono">#${d.id}</div>
                        </div>
                        <div>
                            <label class="text-white/60 text-xs">آیدی کاربر</label>
                            <div class="text-white font-mono">${d.user_id}</div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-white/60 text-xs">کاربر</label>
                        <div class="text-white font-medium">${d.user_display_name || 'کاربر'}</div>
                        ${d.username ? `<a href="https://t.me/${d.username}" target="_blank" class="text-blue-400 text-sm hover:underline">@${d.username}</a>` : ''}
                    </div>
                    
                    <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 text-center">
                        <div class="text-white/60 text-xs mb-1">مبلغ دونیت</div>
                        <div class="text-green-400 text-3xl font-bold">${d.amount_formatted || number_format(d.amount)} تومان</div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-white/60 text-xs">درگاه</label>
                            <div class="text-white flex items-center gap-2">
                                <span class="text-xl">${d.gateway_icon || '💵'}</span>
                                <span>${d.gateway || 'نامشخص'}</span>
                            </div>
                        </div>
                        <div>
                            <label class="text-white/60 text-xs">وضعیت</label>
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
                        <label class="text-white/60 text-xs">تاریخ ایجاد</label>
                        <div class="text-white">${d.created_at}</div>
                        <div class="text-white/50 text-sm">${d.time_ago}</div>
                    </div>
                    
                    ${d.approved_at ? `
                    <div>
                        <label class="text-white/60 text-xs">تاریخ تأیید</label>
                        <div class="text-white">${d.approved_at}</div>
                    </div>
                    ` : ''}
                    
                    ${d.reject_reason ? `
                    <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-3">
                        <label class="text-red-300 text-xs">دلیل رد</label>
                        <div class="text-white text-sm mt-1">${d.reject_reason}</div>
                    </div>
                    ` : ''}
                    
                    <div class="flex gap-3 pt-4">
                        ${d.status === 'pending' ? `
                        <button onclick="approveDonation(${d.id}, true)" class="flex-1 bg-green-500/20 border border-green-500/50 text-green-300 px-4 py-2.5 rounded-lg hover:bg-green-500/30 transition">
                            <i class="fas fa-check"></i>
                            <span>تأیید</span>
                        </button>
                        <button onclick="showRejectModal(${d.id})" class="flex-1 bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-2.5 rounded-lg hover:bg-red-500/30 transition">
                            <i class="fas fa-times"></i>
                            <span>رد</span>
                        </button>
                        ` : ''}
                        <a href="/admin/chat.php?id=${d.user_id}" class="flex-1 bg-purple-500/20 border border-purple-500/50 text-purple-300 px-4 py-2.5 rounded-lg hover:bg-purple-500/30 transition text-center">
                            <i class="fas fa-comments"></i>
                            <span>چت با کاربر</span>
                        </a>
                    </div>
                </div>
            `;
            
            document.getElementById('donationModalContent').innerHTML = content;
            document.getElementById('donationModal').classList.remove('hidden');
            document.getElementById('donationModal').classList.add('flex');
        } else {
            showToast(data.error || 'خطا در دریافت اطلاعات', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
}

// ═══ Close Modal ═══
function closeDonationModal() {
    document.getElementById('donationModal').classList.add('hidden');
    document.getElementById('donationModal').classList.remove('flex');
}

// ═══ Approve Donation ═══
async function approveDonation(donationId, fromModal = false) {
    if (!confirm('آیا مطمئن هستید که می‌خواهید این دونیت را تأیید کنید؟')) {
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
            showToast('دونیت تأیید شد', 'success');
            
            if (fromModal) {
                closeDonationModal();
            }
            
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'خطا', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
}

// ═══ Show Reject Modal ═══
function showRejectModal(donationId) {
    closeDonationModal();
    document.getElementById('rejectDonationId').value = donationId;
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectModal').classList.add('flex');
}

// ═══ Close Reject Modal ═══
function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectModal').classList.remove('flex');
    document.getElementById('rejectForm').reset();
}

// ═══ Reject Donation ═══
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
            showToast('دونیت رد شد', 'success');
            closeRejectModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'خطا', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
});

// ═══ Delete Donation ═══
async function deleteDonation(donationId) {
    if (!confirm('آیا مطمئن هستید که می‌خواهید این دونیت را حذف کنید؟\n\nاین عمل غیرقابل بازگشت است!')) {
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
            showToast('دونیت حذف شد', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'خطا', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
}

// ═══ Export Donations ═══
function exportDonations() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = `/admin/api/donations/export?${params.toString()}`;
    showToast('در حال دانلود فایل...', 'info');
}

// ═══ Show Financial Report ═══
function showFinancialReport() {
    const from = prompt('تاریخ شروع (YYYY-MM-DD):', new Date().toISOString().split('T')[0]);
    if (!from) return;
    
    const to = prompt('تاریخ پایان (YYYY-MM-DD):', new Date().toISOString().split('T')[0]);
    if (!to) return;
    
    window.location.href = `/admin/api/donations/financial-report?from=${from}&to=${to}`;
    showToast('در حال دانلود گزارش...', 'info');
}

// ═══ Close Modals on Escape ═══
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDonationModal();
        closeRejectModal();
    }
});

// ═══ Close Modals on Outside Click ═══
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