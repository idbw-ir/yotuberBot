<?php
/**
 * ============================================
 * Dashboard Content - محتوای داشبورد
 * ============================================
 * نسخه: 2.0.0
 * 
 * این فایل محتوای اصلی صفحه داشبورد رو داره
 * از layout اصلی (admin.php) استفاده می‌کنه
 */

// این فایل از طریق include در public/admin/index.php استفاده می‌شه
// متغیرهای مورد نیاز از index.php ارسال می‌شن:
// - $stats
// - $cards
// - $donationChart
// - $userChart
// - $topDonors
// - $recentMessages
// - $recentDonations
// - $unreadCount

// مقادیر پیش‌فرض برای جلوگیری از خطا
$stats = $stats ?? ['users' => [], 'messages' => [], 'donations' => [], 'growth' => []];
$cards = $cards ?? [];
$donationChart = $donationChart ?? ['labels' => [], 'datasets' => []];
$userChart = $userChart ?? ['labels' => [], 'datasets' => []];
$topDonors = $topDonors ?? [];
$recentMessages = $recentMessages ?? [];
$recentDonations = $recentDonations ?? [];
$unreadCount = $unreadCount ?? 0;
?>

<!-- ═══ کارت‌های آماری ═══ -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <?php foreach ($cards as $card): ?>
    <div class="card-hover bg-gradient-to-br <?= $card['color'] ?> rounded-2xl p-5 shadow-lg transition-all duration-300 hover:shadow-2xl">
        <div class="flex justify-between items-start mb-3">
            <span class="text-4xl"><?= $card['icon'] ?></span>
            <?php if (isset($card['change'])): ?>
            <span class="text-xs bg-white/20 backdrop-blur-sm px-2.5 py-1 rounded-full font-medium">
                <?php if ($card['change'] >= 0): ?>
                    <i class="fas fa-arrow-up text-[10px]"></i>
                <?php else: ?>
                    <i class="fas fa-arrow-down text-[10px]"></i>
                <?php endif; ?>
                <?= abs($card['change']) ?>%
            </span>
            <?php endif; ?>
        </div>
        <div class="text-white/80 text-sm mb-1"><?= htmlspecialchars($card['title']) ?></div>
        <div class="text-white text-3xl font-bold mb-1"><?= htmlspecialchars($card['value']) ?></div>
        <div class="text-white/60 text-xs"><?= htmlspecialchars($card['subtitle'] ?? '') ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ═══ نمودارها ═══ -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    
    <!-- نمودار دونیت‌ها -->
    <div class="glass rounded-2xl p-5 shadow-lg">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-white font-bold text-lg flex items-center gap-2">
                    <span>💰</span>
                    <span>نمودار دونیت‌ها</span>
                </h3>
                <p class="text-white/50 text-xs mt-1">30 روز اخیر</p>
            </div>
            <div class="flex gap-2">
                <button onclick="updateDonationChart(7)" class="text-xs bg-white/10 hover:bg-white/20 text-white px-3 py-1.5 rounded-lg transition">7 روز</button>
                <button onclick="updateDonationChart(30)" class="text-xs bg-purple-500/30 text-white px-3 py-1.5 rounded-lg">30 روز</button>
                <button onclick="updateDonationChart(90)" class="text-xs bg-white/10 hover:bg-white/20 text-white px-3 py-1.5 rounded-lg">90 روز</button>
            </div>
        </div>
        <div class="relative" style="height: 280px;">
            <canvas id="donationChart"></canvas>
        </div>
    </div>
    
    <!-- نمودار کاربران -->
    <div class="glass rounded-2xl p-5 shadow-lg">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-white font-bold text-lg flex items-center gap-2">
                    <span>👥</span>
                    <span>رشد کاربران</span>
                </h3>
                <p class="text-white/50 text-xs mt-1">30 روز اخیر</p>
            </div>
            <div class="flex gap-2">
                <button onclick="updateUserChart(7)" class="text-xs bg-white/10 hover:bg-white/20 text-white px-3 py-1.5 rounded-lg transition">7 روز</button>
                <button onclick="updateUserChart(30)" class="text-xs bg-purple-500/30 text-white px-3 py-1.5 rounded-lg">30 روز</button>
                <button onclick="updateUserChart(90)" class="text-xs bg-white/10 hover:bg-white/20 text-white px-3 py-1.5 rounded-lg">90 روز</button>
            </div>
        </div>
        <div class="relative" style="height: 280px;">
            <canvas id="userChart"></canvas>
        </div>
    </div>
    
</div>

<!-- ═══ آمار سریع ═══ -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">🎯</div>
        <div class="text-white/60 text-xs mb-1">نرخ تبدیل</div>
        <div class="text-white text-xl font-bold">
            <?= isset($stats['donations']['total_count'], $stats['users']['total']) && $stats['users']['total'] > 0 
                ? round(($stats['donations']['total_count'] / $stats['users']['total']) * 100, 1) 
                : 0 ?>%
        </div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">💎</div>
        <div class="text-white/60 text-xs mb-1">میانگین دونیت</div>
        <div class="text-white text-xl font-bold">
            <?= isset($stats['donations']['average']) ? number_format($stats['donations']['average']) : 0 ?>
        </div>
        <div class="text-white/40 text-xs">تومان</div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">🔥</div>
        <div class="text-white/60 text-xs mb-1">کاربران فعال</div>
        <div class="text-white text-xl font-bold">
            <?= isset($stats['users']['active']) ? number_format($stats['users']['active']) : 0 ?>
        </div>
        <div class="text-white/40 text-xs">هفته اخیر</div>
    </div>
    <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">📊</div>
        <div class="text-white/60 text-xs mb-1">پیام امروز</div>
        <div class="text-white text-xl font-bold">
            <?= isset($stats['messages']['today']) ? number_format($stats['messages']['today']) : 0 ?>
        </div>
        <div class="text-white/40 text-xs">پیام</div>
    </div>
</div>

<!-- ═══ برترین‌ها و فعالیت‌های اخیر ═══ -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    
    <!-- برترین حامیان -->
    <div class="glass rounded-2xl p-5 shadow-lg">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                <span>🏆</span>
                <span>برترین حامیان</span>
            </h3>
            <a href="/admin/donations.php" class="text-blue-400 text-sm hover:text-blue-300 transition flex items-center gap-1">
                <span>مشاهده همه</span>
                <i class="fas fa-arrow-left text-xs"></i>
            </a>
        </div>
        
        <?php if (empty($topDonors)): ?>
        <div class="text-center py-12">
            <div class="text-5xl mb-3">🎁</div>
            <p class="text-white/50 text-sm">هنوز دونیتی ثبت نشده</p>
            <p class="text-white/30 text-xs mt-1">اولین دونیت به زودی ثبت می‌شه!</p>
        </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($topDonors as $i => $donor): ?>
            <div class="flex items-center justify-between bg-white/5 rounded-xl p-3 hover:bg-white/10 transition group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm
                        <?= $i === 0 ? 'bg-gradient-to-br from-yellow-400 to-orange-500' : 
                           ($i === 1 ? 'bg-gradient-to-br from-gray-300 to-gray-500' : 
                           ($i === 2 ? 'bg-gradient-to-br from-orange-600 to-orange-800' : 
                           'bg-gradient-to-br from-purple-500 to-blue-500')) ?>">
                        <?= $i + 1 ?>
                    </div>
                    <div>
                        <div class="text-white text-sm font-medium group-hover:text-purple-300 transition">
                            <?= htmlspecialchars($donor['display_name'] ?? $donor['first_name'] ?? 'کاربر') ?>
                        </div>
                        <div class="text-white/50 text-xs">
                            <?= number_format($donor['donation_count'] ?? 0) ?> دونیت
                        </div>
                    </div>
                </div>
                <div class="text-left">
                    <div class="text-green-400 font-bold text-sm">
                        <?= number_format($donor['total_amount'] ?? 0) ?>
                    </div>
                    <div class="text-white/40 text-xs">تومان</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- آخرین پیام‌ها -->
    <div class="glass rounded-2xl p-5 shadow-lg">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                <span>💬</span>
                <span>آخرین پیام‌ها</span>
                <?php if ($unreadCount > 0): ?>
                <span class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5">
                    <?= $unreadCount ?> جدید
                </span>
                <?php endif; ?>
            </h3>
            <a href="/admin/messages.php" class="text-blue-400 text-sm hover:text-blue-300 transition flex items-center gap-1">
                <span>مشاهده همه</span>
                <i class="fas fa-arrow-left text-xs"></i>
            </a>
        </div>
        
        <?php if (empty($recentMessages)): ?>
        <div class="text-center py-12">
            <div class="text-5xl mb-3">📭</div>
            <p class="text-white/50 text-sm">هنوز پیامی دریافت نشده</p>
            <p class="text-white/30 text-xs mt-1">پیام‌های کاربران اینجا نمایش داده می‌شن</p>
        </div>
        <?php else: ?>
        <div class="space-y-2 max-h-96 overflow-y-auto">
            <?php foreach ($recentMessages as $msg): ?>
            <a href="/admin/chat.php?id=<?= $msg['user_id'] ?>" class="block bg-white/5 rounded-xl p-3 hover:bg-white/10 transition group">
                <div class="flex justify-between items-start mb-1.5">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white text-xs font-bold">
                            <?= strtoupper(substr($msg['first_name'] ?? $msg['username'] ?? '?', 0, 1)) ?>
                        </div>
                        <span class="text-white text-sm font-medium group-hover:text-purple-300 transition">
                            <?= htmlspecialchars($msg['user_display_name'] ?? 'کاربر') ?>
                        </span>
                        <?php if (!empty($msg['is_vip'])): ?>
                        <span class="text-xs">👑</span>
                        <?php endif; ?>
                    </div>
                    <span class="text-white/40 text-xs"><?= htmlspecialchars($msg['time_ago'] ?? '') ?></span>
                </div>
                <p class="text-white/70 text-sm truncate pr-10">
                    <?= htmlspecialchars($msg['text_preview'] ?? $msg['text'] ?? '') ?>
                </p>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
</div>

<!-- ═══ آخرین دونیت‌ها ═══ -->
<div class="glass rounded-2xl p-5 shadow-lg mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-white font-bold text-lg flex items-center gap-2">
            <span>💳</span>
            <span>آخرین دونیت‌ها</span>
        </h3>
        <a href="/admin/donations.php" class="text-blue-400 text-sm hover:text-blue-300 transition flex items-center gap-1">
            <span>مشاهده همه</span>
            <i class="fas fa-arrow-left text-xs"></i>
        </a>
    </div>
    
    <?php if (empty($recentDonations)): ?>
    <div class="text-center py-12">
        <div class="text-5xl mb-3">💸</div>
        <p class="text-white/50 text-sm">هنوز دونیتی ثبت نشده</p>
        <p class="text-white/30 text-xs mt-1">دونیت‌های کاربران اینجا نمایش داده می‌شن</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-white/60 border-b border-white/10">
                    <th class="text-right py-3 px-3 font-medium">کاربر</th>
                    <th class="text-right py-3 px-3 font-medium">مبلغ</th>
                    <th class="text-right py-3 px-3 font-medium hidden md:table-cell">درگاه</th>
                    <th class="text-right py-3 px-3 font-medium hidden sm:table-cell">وضعیت</th>
                    <th class="text-right py-3 px-3 font-medium">زمان</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentDonations as $d): ?>
                <tr class="border-b border-white/5 hover:bg-white/5 transition">
                    <td class="py-3 px-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center text-white text-xs font-bold">
                                <?= strtoupper(substr($d['first_name'] ?? $d['username'] ?? '?', 0, 1)) ?>
                            </div>
                            <span class="text-white"><?= htmlspecialchars($d['user_display_name'] ?? 'کاربر') ?></span>
                        </div>
                    </td>
                    <td class="py-3 px-3">
                        <span class="text-green-400 font-bold">
                            <?= htmlspecialchars($d['amount_formatted'] ?? number_format($d['amount'] ?? 0)) ?>
                        </span>
                        <span class="text-white/50 text-xs">ت</span>
                    </td>
                    <td class="py-3 px-3 text-white/70 hidden md:table-cell">
                        <span class="text-lg"><?= htmlspecialchars($d['gateway_icon'] ?? '💵') ?></span>
                        <span class="text-xs"><?= htmlspecialchars($d['gateway'] ?? 'نامشخص') ?></span>
                    </td>
                    <td class="py-3 px-3 hidden sm:table-cell">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs
                            <?= ($d['status'] ?? '') === 'success' ? 'bg-green-500/20 text-green-300' : 
                               (($d['status'] ?? '') === 'pending' ? 'bg-yellow-500/20 text-yellow-300' : 
                               'bg-red-500/20 text-red-300') ?>">
                            <?= htmlspecialchars($d['status_icon'] ?? '❓') ?>
                            <span><?= htmlspecialchars($d['status_text'] ?? 'نامشخص') ?></span>
                        </span>
                    </td>
                    <td class="py-3 px-3 text-white/50 text-xs">
                        <?= htmlspecialchars($d['time_ago'] ?? '') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- ═══ Quick Actions ═══ -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <a href="/admin/users.php" class="glass rounded-xl p-4 text-center hover:bg-white/10 transition group">
        <div class="text-3xl mb-2 group-hover:scale-110 transition">👥</div>
        <div class="text-white text-sm font-medium">مدیریت کاربران</div>
    </a>
    <a href="/admin/broadcast.php" class="glass rounded-xl p-4 text-center hover:bg-white/10 transition group">
        <div class="text-3xl mb-2 group-hover:scale-110 transition">📢</div>
        <div class="text-white text-sm font-medium">ارسال دسته‌جمعی</div>
    </a>
    <a href="/admin/keywords.php" class="glass rounded-xl p-4 text-center hover:bg-white/10 transition group">
        <div class="text-3xl mb-2 group-hover:scale-110 transition">🔑</div>
        <div class="text-white text-sm font-medium">کلمات کلیدی</div>
    </a>
    <a href="/admin/settings.php" class="glass rounded-xl p-4 text-center hover:bg-white/10 transition group">
        <div class="text-3xl mb-2 group-hover:scale-110 transition">⚙️</div>
        <div class="text-white text-sm font-medium">تنظیمات</div>
    </a>
</div>

<!-- ═══ Chart.js Scripts ═══ -->
<script>
// ═══ نمودار دونیت‌ها ═══
let donationChartInstance = null;

function initDonationChart(data) {
    const ctx = document.getElementById('donationChart').getContext('2d');
    
    if (donationChartInstance) {
        donationChartInstance.destroy();
    }
    
    donationChartInstance = new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    labels: { 
                        color: '#fff', 
                        font: { family: 'Vazirmatn', size: 11 },
                        usePointStyle: true,
                        padding: 15
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleFont: { family: 'Vazirmatn' },
                    bodyFont: { family: 'Vazirmatn' },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('fa-IR').format(context.parsed.y);
                                if (context.datasetIndex === 0) label += ' تومان';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { 
                        color: 'rgba(255,255,255,0.6)',
                        font: { family: 'Vazirmatn', size: 10 },
                        callback: function(value) {
                            return new Intl.NumberFormat('fa-IR', { notation: 'compact' }).format(value);
                        }
                    },
                    grid: { color: 'rgba(255,255,255,0.05)' }
                },
                x: {
                    ticks: { 
                        color: 'rgba(255,255,255,0.6)',
                        font: { family: 'Vazirmatn', size: 10 },
                        maxRotation: 0
                    },
                    grid: { color: 'rgba(255,255,255,0.05)' }
                }
            }
        }
    });
}

// ═══ نمودار کاربران ═══
let userChartInstance = null;

function initUserChart(data) {
    const ctx = document.getElementById('userChart').getContext('2d');
    
    if (userChartInstance) {
        userChartInstance.destroy();
    }
    
    userChartInstance = new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    labels: { 
                        color: '#fff', 
                        font: { family: 'Vazirmatn', size: 11 },
                        usePointStyle: true,
                        padding: 15
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleFont: { family: 'Vazirmatn' },
                    bodyFont: { family: 'Vazirmatn' },
                    padding: 12,
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { 
                        color: 'rgba(255,255,255,0.6)',
                        font: { family: 'Vazirmatn', size: 10 },
                        stepSize: 1
                    },
                    grid: { color: 'rgba(255,255,255,0.05)' }
                },
                x: {
                    ticks: { 
                        color: 'rgba(255,255,255,0.6)',
                        font: { family: 'Vazirmatn', size: 10 },
                        maxRotation: 0
                    },
                    grid: { color: 'rgba(255,255,255,0.05)' }
                }
            }
        }
    });
}

// ═══ بروزرسانی نمودارها ═══
async function updateDonationChart(days) {
    try {
        const response = await fetch(`/admin/api/chart/donations?days=${days}`);
        const data = await response.json();
        
        if (data.success) {
            initDonationChart(data.data);
            showToast('نمودار بروزرسانی شد', 'success');
        }
    } catch (error) {
        showToast('خطا در بروزرسانی نمودار', 'error');
    }
}

async function updateUserChart(days) {
    try {
        const response = await fetch(`/admin/api/chart/users?days=${days}`);
        const data = await response.json();
        
        if (data.success) {
            initUserChart(data.data);
            showToast('نمودار بروزرسانی شد', 'success');
        }
    } catch (error) {
        showToast('خطا در بروزرسانی نمودار', 'error');
    }
}

// ═══ راه‌اندازی اولیه ═══
document.addEventListener('DOMContentLoaded', function() {
    // نمودار دونیت‌ها
    const donationData = <?= json_encode($donationChart) ?>;
    if (donationData && donationData.labels && donationData.labels.length > 0) {
        initDonationChart(donationData);
    }
    
    // نمودار کاربران
    const userData = <?= json_encode($userChart) ?>;
    if (userData && userData.labels && userData.labels.length > 0) {
        initUserChart(userData);
    }
    
    // Auto-refresh هر 5 دقیقه (اختیاری)
    // setInterval(() => location.reload(), 300000);
});
</script>