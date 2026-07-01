<?php
/**
 * ============================================
 * Admin Profile - پروفایل ادمین
 * ============================================
 * نسخه: 2.0.0
 * 
 * مدیریت اطلاعات شخصی، تغییر رمز عبور
 * مشاهده Activity Log و Session های فعال
 */

// متغیرهای مورد نیاز از Controller:
// - $admin (اطلاعات ادمین فعلی)
// - $activityLog (لاگ فعالیت‌ها)
// - $activeSessions (Session های فعال)
// - $stats (آمار فعالیت)

$admin = $admin ?? [];
$activityLog = $activityLog ?? [];
$activeSessions = $activeSessions ?? [];
$stats = $stats ?? ['total_logins' => 0, 'total_changes' => 0, 'days_active' => 0];

// CSRF Token
$csrfToken = $_SESSION['_csrf_token'] ?? '';

// تب فعال
$activeTab = $_GET['tab'] ?? 'info';
?>

<!-- ═══ کارت اطلاعات ادمین ═══ -->
<div class="glass rounded-2xl p-6 mb-6">
    <div class="flex items-center gap-6">
        
        <!-- Avatar -->
        <div class="relative">
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white text-4xl font-bold shadow-2xl">
                <?= strtoupper(substr($admin['name'] ?? 'A', 0, 1)) ?>
            </div>
            <span class="absolute bottom-0 right-0 w-6 h-6 bg-green-500 rounded-full border-4 border-gray-900"></span>
        </div>
        
        <!-- Info -->
        <div class="flex-1">
            <h2 class="text-white text-2xl font-bold mb-1"><?= htmlspecialchars($admin['name'] ?? 'ادمین') ?></h2>
            <p class="text-white/60 text-sm mb-2">@<?= htmlspecialchars($admin['username'] ?? 'admin') ?></p>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-purple-500/20 text-purple-300">
                    <i class="fas fa-shield-alt"></i>
                    <span><?= htmlspecialchars($admin['role'] ?? 'admin') ?></span>
                </span>
                <span class="text-white/40 text-xs">
                    عضو از: <?= htmlspecialchars($admin['created_at'] ?? 'نامشخص') ?>
                </span>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="hidden md:flex gap-6">
            <div class="text-center">
                <div class="text-2xl font-bold text-white"><?= number_format($stats['total_logins'] ?? 0) ?></div>
                <div class="text-white/50 text-xs">ورود</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-white"><?= number_format($stats['total_changes'] ?? 0) ?></div>
                <div class="text-white/50 text-xs">تغییرات</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-white"><?= number_format($stats['days_active'] ?? 0) ?></div>
                <div class="text-white/50 text-xs">روز فعال</div>
            </div>
        </div>
        
    </div>
</div>

<!-- ═══ تب‌ها ═══ -->
<div class="glass rounded-2xl p-2 mb-6">
    <div class="flex gap-2 overflow-x-auto">
        <a 
            href="?tab=info"
            class="flex items-center gap-2 px-4 py-2.5 rounded-lg whitespace-nowrap transition
                <?= $activeTab === 'info' ? 'bg-gradient-to-r from-purple-500 to-blue-500 text-white' : 'bg-white/5 text-white/70 hover:bg-white/10' ?>"
        >
            <i class="fas fa-user"></i>
            <span class="text-sm font-medium">اطلاعات شخصی</span>
        </a>
        
        <a 
            href="?tab=password"
            class="flex items-center gap-2 px-4 py-2.5 rounded-lg whitespace-nowrap transition
                <?= $activeTab === 'password' ? 'bg-gradient-to-r from-purple-500 to-blue-500 text-white' : 'bg-white/5 text-white/70 hover:bg-white/10' ?>"
        >
            <i class="fas fa-lock"></i>
            <span class="text-sm font-medium">تغییر رمز عبور</span>
        </a>
        
        <a 
            href="?tab=sessions"
            class="flex items-center gap-2 px-4 py-2.5 rounded-lg whitespace-nowrap transition
                <?= $activeTab === 'sessions' ? 'bg-gradient-to-r from-purple-500 to-blue-500 text-white' : 'bg-white/5 text-white/70 hover:bg-white/10' ?>"
        >
            <i class="fas fa-desktop"></i>
            <span class="text-sm font-medium">Session ها</span>
        </a>
        
        <a 
            href="?tab=activity"
            class="flex items-center gap-2 px-4 py-2.5 rounded-lg whitespace-nowrap transition
                <?= $activeTab === 'activity' ? 'bg-gradient-to-r from-purple-500 to-blue-500 text-white' : 'bg-white/5 text-white/70 hover:bg-white/10' ?>"
        >
            <i class="fas fa-history"></i>
            <span class="text-sm font-medium">Activity Log</span>
        </a>
    </div>
</div>

<!-- ═══ محتوای تب‌ها ═══ -->
<?php if ($activeTab === 'password'): ?>
<!-- ═══ Change Password Tab ═══ -->
<div class="glass rounded-2xl p-6">
    <h3 class="text-white font-bold text-xl mb-6 flex items-center gap-2">
        <i class="fas fa-lock"></i>
        <span>تغییر رمز عبور</span>
    </h3>
    
    <form id="passwordForm" class="space-y-5 max-w-xl">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        
        <!-- Current Password -->
        <div>
            <label class="block text-white/70 text-sm mb-2">
                رمز عبور فعلی <span class="text-red-400">*</span>
            </label>
            <div class="relative">
                <input 
                    type="password" 
                    name="current_password"
                    required
                    autocomplete="current-password"
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 pr-11 pl-11 text-white placeholder-white/40 focus:border-purple-500 transition"
                    placeholder="رمز عبور فعلی"
                >
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40">
                    <i class="fas fa-lock"></i>
                </span>
                <button 
                    type="button"
                    onclick="togglePasswordVisibility(this)"
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/60 transition"
                >
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
        
        <!-- New Password -->
        <div>
            <label class="block text-white/70 text-sm mb-2">
                رمز عبور جدید <span class="text-red-400">*</span>
            </label>
            <div class="relative">
                <input 
                    type="password" 
                    name="new_password"
                    id="newPassword"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 pr-11 pl-11 text-white placeholder-white/40 focus:border-purple-500 transition"
                    placeholder="حداقل 8 کاراکتر"
                    oninput="checkPasswordStrength(this.value)"
                >
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40">
                    <i class="fas fa-key"></i>
                </span>
                <button 
                    type="button"
                    onclick="togglePasswordVisibility(this)"
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/60 transition"
                >
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            
            <!-- Password Strength -->
            <div id="passwordStrength" class="mt-2 hidden">
                <div class="flex gap-1 mb-1">
                    <div class="h-1 flex-1 rounded bg-white/10" id="strength1"></div>
                    <div class="h-1 flex-1 rounded bg-white/10" id="strength2"></div>
                    <div class="h-1 flex-1 rounded bg-white/10" id="strength3"></div>
                    <div class="h-1 flex-1 rounded bg-white/10" id="strength4"></div>
                </div>
                <div class="text-xs text-white/60" id="strengthText">ضعیف</div>
            </div>
        </div>
        
        <!-- Confirm Password -->
        <div>
            <label class="block text-white/70 text-sm mb-2">
                تکرار رمز عبور جدید <span class="text-red-400">*</span>
            </label>
            <div class="relative">
                <input 
                    type="password" 
                    name="confirm_password"
                    id="confirmPassword"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 pr-11 pl-11 text-white placeholder-white/40 focus:border-purple-500 transition"
                    placeholder="تکرار رمز عبور جدید"
                    oninput="checkPasswordMatch()"
                >
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40">
                    <i class="fas fa-check"></i>
                </span>
                <button 
                    type="button"
                    onclick="togglePasswordVisibility(this)"
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/60 transition"
                >
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <div id="passwordMatch" class="mt-1 text-xs hidden"></div>
        </div>
        
        <!-- Password Requirements -->
        <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4">
            <div class="text-blue-300 text-sm font-medium mb-2">
                <i class="fas fa-info-circle"></i>
                <span>الزامات رمز عبور:</span>
            </div>
            <ul class="text-white/60 text-xs space-y-1">
                <li id="req-length" class="flex items-center gap-2">
                    <i class="fas fa-circle text-white/30 text-[6px]"></i>
                    <span>حداقل 8 کاراکتر</span>
                </li>
                <li id="req-upper" class="flex items-center gap-2">
                    <i class="fas fa-circle text-white/30 text-[6px]"></i>
                    <span>حداقل یک حرف بزرگ</span>
                </li>
                <li id="req-lower" class="flex items-center gap-2">
                    <i class="fas fa-circle text-white/30 text-[6px]"></i>
                    <span>حداقل یک حرف کوچک</span>
                </li>
                <li id="req-number" class="flex items-center gap-2">
                    <i class="fas fa-circle text-white/30 text-[6px]"></i>
                    <span>حداقل یک عدد</span>
                </li>
            </ul>
        </div>
        
        <!-- Submit Button -->
        <button 
            type="submit"
            class="bg-gradient-to-r from-purple-500 to-blue-500 text-white px-6 py-3 rounded-lg hover:opacity-90 transition flex items-center gap-2 font-bold"
        >
            <i class="fas fa-save"></i>
            <span>تغییر رمز عبور</span>
        </button>
        
    </form>
</div>

<?php elseif ($activeTab === 'sessions'): ?>
<!-- ═══ Active Sessions Tab ═══ -->
<div class="glass rounded-2xl p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-white font-bold text-xl flex items-center gap-2">
            <i class="fas fa-desktop"></i>
            <span>Session های فعال</span>
        </h3>
        <button 
            onclick="logoutAllSessions()"
            class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-2 rounded-lg hover:bg-red-500/30 transition text-sm flex items-center gap-2"
        >
            <i class="fas fa-sign-out-alt"></i>
            <span>خروج از همه</span>
        </button>
    </div>
    
    <?php if (empty($activeSessions)): ?>
    <div class="text-center py-12">
        <div class="text-5xl mb-3">🔒</div>
        <p class="text-white/50 text-sm">هیچ Session فعالی یافت نشد</p>
    </div>
    <?php else: ?>
    <div class="space-y-3">
        <?php foreach ($activeSessions as $session): ?>
        <div class="bg-white/5 rounded-xl p-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white text-xl">
                    <?php
                    $device = $session['device'] ?? 'unknown';
                    if (strpos($device, 'mobile') !== false) echo '<i class="fas fa-mobile-alt"></i>';
                    elseif (strpos($device, 'tablet') !== false) echo '<i class="fas fa-tablet-alt"></i>';
                    else echo '<i class="fas fa-desktop"></i>';
                    ?>
                </div>
                <div>
                    <div class="text-white font-medium">
                        <?= htmlspecialchars($session['device'] ?? 'دستگاه ناشناخته') ?>
                        <?php if (!empty($session['is_current'])): ?>
                        <span class="text-xs bg-green-500/20 text-green-300 px-2 py-0.5 rounded-full mr-2">
                            فعلی
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="text-white/50 text-xs mt-1">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= htmlspecialchars($session['location'] ?? 'نامشخص') ?></span>
                        <span class="mx-2">•</span>
                        <i class="fas fa-clock"></i>
                        <span><?= htmlspecialchars($session['last_activity'] ?? 'نامشخص') ?></span>
                    </div>
                    <div class="text-white/40 text-xs mt-1 font-mono">
                        IP: <?= htmlspecialchars($session['ip_address'] ?? 'نامشخص') ?>
                    </div>
                </div>
            </div>
            
            <?php if (empty($session['is_current'])): ?>
            <button 
                onclick="terminateSession('<?= htmlspecialchars($session['id']) ?>')"
                class="bg-red-500/20 hover:bg-red-500/30 text-red-300 px-4 py-2 rounded-lg transition text-sm"
            >
                <i class="fas fa-times"></i>
                <span>پایان</span>
            </button>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php elseif ($activeTab === 'activity'): ?>
<!-- ═══ Activity Log Tab ═══ -->
<div class="glass rounded-2xl p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-white font-bold text-xl flex items-center gap-2">
            <i class="fas fa-history"></i>
            <span>Activity Log</span>
        </h3>
        <button 
            onclick="clearActivityLog()"
            class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-2 rounded-lg hover:bg-red-500/30 transition text-sm flex items-center gap-2"
        >
            <i class="fas fa-trash"></i>
            <span>پاک کردن</span>
        </button>
    </div>
    
    <?php if (empty($activityLog)): ?>
    <div class="text-center py-12">
        <div class="text-5xl mb-3">📝</div>
        <p class="text-white/50 text-sm">هیچ فعالیتی ثبت نشده</p>
    </div>
    <?php else: ?>
    <div class="space-y-3 max-h-[600px] overflow-y-auto">
        <?php foreach ($activityLog as $log): ?>
        <div class="bg-white/5 rounded-xl p-4">
            <div class="flex items-start justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-lg">
                        <?php
                        $action = $log['action'] ?? '';
                        if (strpos($action, 'login') !== false) echo '🔐';
                        elseif (strpos($action, 'logout') !== false) echo '🚪';
                        elseif (strpos($action, 'change') !== false) echo '🔧';
                        elseif (strpos($action, 'delete') !== false) echo '🗑️';
                        elseif (strpos($action, 'create') !== false) echo '➕';
                        else echo '📝';
                        ?>
                    </span>
                    <span class="text-white font-medium text-sm">
                        <?= htmlspecialchars($log['action'] ?? 'نامشخص') ?>
                    </span>
                </div>
                <div class="text-white/40 text-xs">
                    <?= htmlspecialchars($log['created_at'] ?? '') ?>
                </div>
            </div>
            
            <?php if (!empty($log['description'])): ?>
            <p class="text-white/60 text-sm mb-2">
                <?= htmlspecialchars($log['description']) ?>
            </p>
            <?php endif; ?>
            
            <div class="text-white/40 text-xs flex items-center gap-3">
                <span>
                    <i class="fas fa-map-marker-alt"></i>
                    <?= htmlspecialchars($log['ip_address'] ?? 'نامشخص') ?>
                </span>
                <span>
                    <i class="fas fa-laptop"></i>
                    <?= htmlspecialchars($log['user_agent'] ?? 'نامشخص') ?>
                </span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php else: ?>
<!-- ═══ Personal Info Tab ═══ -->
<div class="glass rounded-2xl p-6">
    <h3 class="text-white font-bold text-xl mb-6 flex items-center gap-2">
        <i class="fas fa-user"></i>
        <span>اطلاعات شخصی</span>
    </h3>
    
    <form id="profileForm" class="space-y-5 max-w-xl">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        
        <!-- Name -->
        <div>
            <label class="block text-white/70 text-sm mb-2">
                نام و نام خانوادگی <span class="text-red-400">*</span>
            </label>
            <input 
                type="text" 
                name="name"
                value="<?= htmlspecialchars($admin['name'] ?? '') ?>"
                required
                class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition"
                placeholder="نام خود را وارد کنید"
            >
        </div>
        
        <!-- Username -->
        <div>
            <label class="block text-white/70 text-sm mb-2">
                نام کاربری <span class="text-red-400">*</span>
            </label>
            <input 
                type="text" 
                name="username"
                value="<?= htmlspecialchars($admin['username'] ?? '') ?>"
                required
                readonly
                class="w-full bg-white/5 border border-white/10 rounded-lg py-2.5 px-4 text-white/50 cursor-not-allowed"
                dir="ltr"
            >
            <p class="text-white/40 text-xs mt-1">نام کاربری قابل تغییر نیست</p>
        </div>
        
        <!-- Email -->
        <div>
            <label class="block text-white/70 text-sm mb-2">
                ایمیل
            </label>
            <input 
                type="email" 
                name="email"
                value="<?= htmlspecialchars($admin['email'] ?? '') ?>"
                class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition"
                placeholder="example@domain.com"
                dir="ltr"
            >
        </div>
        
        <!-- Phone -->
        <div>
            <label class="block text-white/70 text-sm mb-2">
                شماره تماس
            </label>
            <input 
                type="tel" 
                name="phone"
                value="<?= htmlspecialchars($admin['phone'] ?? '') ?>"
                class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition"
                placeholder="09123456789"
                dir="ltr"
            >
        </div>
        
        <!-- Bio -->
        <div>
            <label class="block text-white/70 text-sm mb-2">
                بیوگرافی
            </label>
            <textarea 
                name="bio"
                rows="3"
                class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition resize-none"
                placeholder="چند خط درباره خودتان بنویسید..."
            ><?= htmlspecialchars($admin['bio'] ?? '') ?></textarea>
        </div>
        
        <!-- Timezone -->
        <div>
            <label class="block text-white/70 text-sm mb-2">
                منطقه زمانی
            </label>
            <select 
                name="timezone"
                class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
            >
                <option value="Asia/Tehran" <?= ($admin['timezone'] ?? '') === 'Asia/Tehran' ? 'selected' : '' ?>>
                    Asia/Tehran (تهران)
                </option>
                <option value="Asia/Dubai" <?= ($admin['timezone'] ?? '') === 'Asia/Dubai' ? 'selected' : '' ?>>
                    Asia/Dubai (دبی)
                </option>
                <option value="UTC" <?= ($admin['timezone'] ?? '') === 'UTC' ? 'selected' : '' ?>>
                    UTC
                </option>
            </select>
        </div>
        
        <!-- Submit Button -->
        <button 
            type="submit"
            class="bg-gradient-to-r from-purple-500 to-blue-500 text-white px-6 py-3 rounded-lg hover:opacity-90 transition flex items-center gap-2 font-bold"
        >
            <i class="fas fa-save"></i>
            <span>ذخیره تغییرات</span>
        </button>
        
    </form>
</div>
<?php endif; ?>

<!-- ═══ JavaScript ═══ -->
<script>
// ═══ Toggle Password Visibility ═══
function togglePasswordVisibility(button) {
    const input = button.parentElement.querySelector('input');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// ═══ Check Password Strength ═══
function checkPasswordStrength(password) {
    const strengthDiv = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('strengthText');
    const bars = [
        document.getElementById('strength1'),
        document.getElementById('strength2'),
        document.getElementById('strength3'),
        document.getElementById('strength4')
    ];
    
    if (!password) {
        strengthDiv.classList.add('hidden');
        return;
    }
    
    strengthDiv.classList.remove('hidden');
    
    let strength = 0;
    
    // Length check
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    
    // Complexity checks
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    // Update bars
    const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
    const texts = ['خیلی ضعیف', 'ضعیف', 'متوسط', 'قوی', 'خیلی قوی'];
    
    bars.forEach((bar, index) => {
        bar.className = 'h-1 flex-1 rounded';
        if (index < Math.ceil(strength / 1.5)) {
            bar.classList.add(colors[Math.min(Math.ceil(strength / 1.5) - 1, 3)]);
        } else {
            bar.classList.add('bg-white/10');
        }
    });
    
    strengthText.textContent = texts[Math.min(Math.ceil(strength / 1.5), 4)];
    strengthText.className = `text-xs ${colors[Math.min(Math.ceil(strength / 1.5) - 1, 3)].replace('bg-', 'text-')}`;
    
    // Update requirements
    updateRequirements(password);
}

// ═══ Update Password Requirements ═══
function updateRequirements(password) {
    const requirements = {
        'req-length': password.length >= 8,
        'req-upper': /[A-Z]/.test(password),
        'req-lower': /[a-z]/.test(password),
        'req-number': /[0-9]/.test(password)
    };
    
    for (const [id, met] of Object.entries(requirements)) {
        const element = document.getElementById(id);
        const icon = element.querySelector('i');
        
        if (met) {
            icon.classList.remove('fa-circle', 'text-white/30');
            icon.classList.add('fa-check-circle', 'text-green-400');
            element.classList.add('text-green-400');
            element.classList.remove('text-white/60');
        } else {
            icon.classList.remove('fa-check-circle', 'text-green-400');
            icon.classList.add('fa-circle', 'text-white/30');
            element.classList.remove('text-green-400');
            element.classList.add('text-white/60');
        }
    }
}

// ═══ Check Password Match ═══
function checkPasswordMatch() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const matchDiv = document.getElementById('passwordMatch');
    
    if (!confirmPassword) {
        matchDiv.classList.add('hidden');
        return;
    }
    
    matchDiv.classList.remove('hidden');
    
    if (newPassword === confirmPassword) {
        matchDiv.innerHTML = '<i class="fas fa-check-circle text-green-400"></i><span class="text-green-400">رمزهای عبور مطابقت دارند</span>';
    } else {
        matchDiv.innerHTML = '<i class="fas fa-times-circle text-red-400"></i><span class="text-red-400">رمزهای عبور مطابقت ندارند</span>';
    }
}

// ═══ Save Profile ═══
document.getElementById('profileForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('/admin/api/profile/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('اطلاعات با موفقیت بروزرسانی شد', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.error || 'خطا در بروزرسانی', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
});

// ═══ Change Password ═══
document.getElementById('passwordForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Validation
    if (data.new_password !== data.confirm_password) {
        showToast('رمزهای عبور مطابقت ندارند', 'error');
        return;
    }
    
    if (data.new_password.length < 8) {
        showToast('رمز عبور باید حداقل 8 کاراکتر باشد', 'error');
        return;
    }
    
    try {
        const response = await fetch('/admin/api/profile/change-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('رمز عبور با موفقیت تغییر کرد', 'success');
            setTimeout(() => {
                window.location.href = '/admin/login.php?success=password_changed';
            }, 2000);
        } else {
            showToast(result.error || 'خطا در تغییر رمز', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
});

// ═══ Terminate Session ═══
async function terminateSession(sessionId) {
    if (!confirm('آیا مطمئن هستید که می‌خواهید این Session را پایان دهید؟')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/api/profile/terminate-session', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({ session_id: sessionId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Session پایان یافت', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.error || 'خطا', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
}

// ═══ Logout All Sessions ═══
async function logoutAllSessions() {
    if (!confirm('آیا مطمئن هستید که می‌خواهید از همه Session ها خارج شوید؟\n\nشما باید دوباره وارد شوید!')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/api/profile/logout-all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('از همه Session ها خارج شدید', 'success');
            setTimeout(() => {
                window.location.href = '/admin/login.php';
            }, 2000);
        } else {
            showToast(result.error || 'خطا', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
}

// ═══ Clear Activity Log ═══
async function clearActivityLog() {
    if (!confirm('آیا مطمئن هستید که می‌خواهید تمام Activity Log را پاک کنید؟\n\nاین عمل غیرقابل بازگشت است!')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/api/profile/clear-activity-log', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Activity Log پاک شد', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.error || 'خطا', 'error');
        }
    } catch (error) {
        showToast('خطا در ارتباط با سرور', 'error');
    }
}
</script>