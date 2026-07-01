<?php
/**
 * ============================================
 * Admin Profile - Ù¾Ø±ÙˆÙØ§ÛŒÙ„ Ø§Ø¯Ù…ÛŒÙ†
 * ============================================
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * 
 * Ù…Ø¯ÛŒØ±ÛŒØª Ø§Ø·Ù„Ø§Ø¹Ø§Øª Ø´Ø®ØµÛŒØŒ ØªØºÛŒÛŒØ± Ø±Ù…Ø² Ø¹Ø¨ÙˆØ±
 * Ù…Ø´Ø§Ù‡Ø¯Ù‡ Activity Log Ùˆ Session Ù‡Ø§ÛŒ ÙØ¹Ø§Ù„
 */

// Ù…ØªØºÛŒØ±Ù‡Ø§ÛŒ Ù…ÙˆØ±Ø¯ Ù†ÛŒØ§Ø² Ø§Ø² Controller:
// - $admin (Ø§Ø·Ù„Ø§Ø¹Ø§Øª Ø§Ø¯Ù…ÛŒÙ† ÙØ¹Ù„ÛŒ)
// - $activityLog (Ù„Ø§Ú¯ ÙØ¹Ø§Ù„ÛŒØªâ€ŒÙ‡Ø§)
// - $activeSessions (Session Ù‡Ø§ÛŒ ÙØ¹Ø§Ù„)
// - $stats (Ø¢Ù…Ø§Ø± ÙØ¹Ø§Ù„ÛŒØª)

$admin = $admin ?? [];
$activityLog = $activityLog ?? [];
$activeSessions = $activeSessions ?? [];
$stats = $stats ?? ['total_logins' => 0, 'total_changes' => 0, 'days_active' => 0];

// CSRF Token
$csrfToken = $_SESSION['_csrf_token'] ?? '';

// ØªØ¨ ÙØ¹Ø§Ù„
$activeTab = $_GET['tab'] ?? 'info';
?>

<!-- â•â•â• Ú©Ø§Ø±Øª Ø§Ø·Ù„Ø§Ø¹Ø§Øª Ø§Ø¯Ù…ÛŒÙ† â•â•â• -->
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
            <h2 class="text-white text-2xl font-bold mb-1"><?= htmlspecialchars($admin['name'] ?? 'Ø§Ø¯Ù…ÛŒÙ†') ?></h2>
            <p class="text-white/60 text-sm mb-2">@<?= htmlspecialchars($admin['username'] ?? 'admin') ?></p>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-purple-500/20 text-purple-300">
                    <i class="fas fa-shield-alt"></i>
                    <span><?= htmlspecialchars($admin['role'] ?? 'admin') ?></span>
                </span>
                <span class="text-white/40 text-xs">
                    Ø¹Ø¶Ùˆ Ø§Ø²: <?= htmlspecialchars($admin['created_at'] ?? 'Ù†Ø§Ù…Ø´Ø®Øµ') ?>
                </span>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="hidden md:flex gap-6">
            <div class="text-center">
                <div class="text-2xl font-bold text-white"><?= number_format($stats['total_logins'] ?? 0) ?></div>
                <div class="text-white/50 text-xs">ÙˆØ±ÙˆØ¯</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-white"><?= number_format($stats['total_changes'] ?? 0) ?></div>
                <div class="text-white/50 text-xs">ØªØºÛŒÛŒØ±Ø§Øª</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-white"><?= number_format($stats['days_active'] ?? 0) ?></div>
                <div class="text-white/50 text-xs">Ø±ÙˆØ² ÙØ¹Ø§Ù„</div>
            </div>
        </div>
        
    </div>
</div>

<!-- â•â•â• ØªØ¨â€ŒÙ‡Ø§ â•â•â• -->
<div class="glass rounded-2xl p-2 mb-6">
    <div class="flex gap-2 overflow-x-auto">
        <a 
            href="?tab=info"
            class="flex items-center gap-2 px-4 py-2.5 rounded-lg whitespace-nowrap transition
                <?= $activeTab === 'info' ? 'bg-gradient-to-r from-purple-500 to-blue-500 text-white' : 'bg-white/5 text-white/70 hover:bg-white/10' ?>"
        >
            <i class="fas fa-user"></i>
            <span class="text-sm font-medium">Ø§Ø·Ù„Ø§Ø¹Ø§Øª Ø´Ø®ØµÛŒ</span>
        </a>
        
        <a 
            href="?tab=password"
            class="flex items-center gap-2 px-4 py-2.5 rounded-lg whitespace-nowrap transition
                <?= $activeTab === 'password' ? 'bg-gradient-to-r from-purple-500 to-blue-500 text-white' : 'bg-white/5 text-white/70 hover:bg-white/10' ?>"
        >
            <i class="fas fa-lock"></i>
            <span class="text-sm font-medium">ØªØºÛŒÛŒØ± Ø±Ù…Ø² Ø¹Ø¨ÙˆØ±</span>
        </a>
        
        <a 
            href="?tab=sessions"
            class="flex items-center gap-2 px-4 py-2.5 rounded-lg whitespace-nowrap transition
                <?= $activeTab === 'sessions' ? 'bg-gradient-to-r from-purple-500 to-blue-500 text-white' : 'bg-white/5 text-white/70 hover:bg-white/10' ?>"
        >
            <i class="fas fa-desktop"></i>
            <span class="text-sm font-medium">Session Ù‡Ø§</span>
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

<!-- â•â•â• Ù…Ø­ØªÙˆØ§ÛŒ ØªØ¨â€ŒÙ‡Ø§ â•â•â• -->
<?php if ($activeTab === 'password'): ?>
<!-- â•â•â• Change Password Tab â•â•â• -->
<div class="glass rounded-2xl p-6">
    <h3 class="text-white font-bold text-xl mb-6 flex items-center gap-2">
        <i class="fas fa-lock"></i>
        <span>ØªØºÛŒÛŒØ± Ø±Ù…Ø² Ø¹Ø¨ÙˆØ±</span>
    </h3>
    
    <form id="passwordForm" class="space-y-5 max-w-xl">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        
        <!-- Current Password -->
        <div>
            <label class="block text-white/70 text-sm mb-2">
                Ø±Ù…Ø² Ø¹Ø¨ÙˆØ± ÙØ¹Ù„ÛŒ <span class="text-red-400">*</span>
            </label>
            <div class="relative">
                <input 
                    type="password" 
                    name="current_password"
                    required
                    autocomplete="current-password"
                    class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 pr-11 pl-11 text-white placeholder-white/40 focus:border-purple-500 transition"
                    placeholder="Ø±Ù…Ø² Ø¹Ø¨ÙˆØ± ÙØ¹Ù„ÛŒ"
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
                Ø±Ù…Ø² Ø¹Ø¨ÙˆØ± Ø¬Ø¯ÛŒØ¯ <span class="text-red-400">*</span>
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
                    placeholder="Ø­Ø¯Ø§Ù‚Ù„ 8 Ú©Ø§Ø±Ø§Ú©ØªØ±"
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
                <div class="text-xs text-white/60" id="strengthText">Ø¶Ø¹ÛŒÙ</div>
            </div>
        </div>
        
        <!-- Confirm Password -->
        <div>
            <label class="block text-white/70 text-sm mb-2">
                ØªÚ©Ø±Ø§Ø± Ø±Ù…Ø² Ø¹Ø¨ÙˆØ± Ø¬Ø¯ÛŒØ¯ <span class="text-red-400">*</span>
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
                    placeholder="ØªÚ©Ø±Ø§Ø± Ø±Ù…Ø² Ø¹Ø¨ÙˆØ± Ø¬Ø¯ÛŒØ¯"
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
                <span>Ø§Ù„Ø²Ø§Ù…Ø§Øª Ø±Ù…Ø² Ø¹Ø¨ÙˆØ±:</span>
            </div>
            <ul class="text-white/60 text-xs space-y-1">
                <li id="req-length" class="flex items-center gap-2">
                    <i class="fas fa-circle text-white/30 text-[6px]"></i>
                    <span>Ø­Ø¯Ø§Ù‚Ù„ 8 Ú©Ø§Ø±Ø§Ú©ØªØ±</span>
                </li>
                <li id="req-upper" class="flex items-center gap-2">
                    <i class="fas fa-circle text-white/30 text-[6px]"></i>
                    <span>Ø­Ø¯Ø§Ù‚Ù„ ÛŒÚ© Ø­Ø±Ù Ø¨Ø²Ø±Ú¯</span>
                </li>
                <li id="req-lower" class="flex items-center gap-2">
                    <i class="fas fa-circle text-white/30 text-[6px]"></i>
                    <span>Ø­Ø¯Ø§Ù‚Ù„ ÛŒÚ© Ø­Ø±Ù Ú©ÙˆÚ†Ú©</span>
                </li>
                <li id="req-number" class="flex items-center gap-2">
                    <i class="fas fa-circle text-white/30 text-[6px]"></i>
                    <span>Ø­Ø¯Ø§Ù‚Ù„ ÛŒÚ© Ø¹Ø¯Ø¯</span>
                </li>
            </ul>
        </div>
        
        <!-- Submit Button -->
        <button 
            type="submit"
            class="bg-gradient-to-r from-purple-500 to-blue-500 text-white px-6 py-3 rounded-lg hover:opacity-90 transition flex items-center gap-2 font-bold"
        >
            <i class="fas fa-save"></i>
            <span>ØªØºÛŒÛŒØ± Ø±Ù…Ø² Ø¹Ø¨ÙˆØ±</span>
        </button>
        
    </form>
</div>

<?php elseif ($activeTab === 'sessions'): ?>
<!-- â•â•â• Active Sessions Tab â•â•â• -->
<div class="glass rounded-2xl p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-white font-bold text-xl flex items-center gap-2">
            <i class="fas fa-desktop"></i>
            <span>Session Ù‡Ø§ÛŒ ÙØ¹Ø§Ù„</span>
        </h3>
        <button 
            onclick="logoutAllSessions()"
            class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-2 rounded-lg hover:bg-red-500/30 transition text-sm flex items-center gap-2"
        >
            <i class="fas fa-sign-out-alt"></i>
            <span>Ø®Ø±ÙˆØ¬ Ø§Ø² Ù‡Ù…Ù‡</span>
        </button>
    </div>
    
    <?php if (empty($activeSessions)): ?>
    <div class="text-center py-12">
        <div class="text-5xl mb-3">ðŸ”’</div>
        <p class="text-white/50 text-sm">Ù‡ÛŒÚ† Session ÙØ¹Ø§Ù„ÛŒ ÛŒØ§ÙØª Ù†Ø´Ø¯</p>
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
                        <?= htmlspecialchars($session['device'] ?? 'Ø¯Ø³ØªÚ¯Ø§Ù‡ Ù†Ø§Ø´Ù†Ø§Ø®ØªÙ‡') ?>
                        <?php if (!empty($session['is_current'])): ?>
                        <span class="text-xs bg-green-500/20 text-green-300 px-2 py-0.5 rounded-full mr-2">
                            ÙØ¹Ù„ÛŒ
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="text-white/50 text-xs mt-1">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= htmlspecialchars($session['location'] ?? 'Ù†Ø§Ù…Ø´Ø®Øµ') ?></span>
                        <span class="mx-2">â€¢</span>
                        <i class="fas fa-clock"></i>
                        <span><?= htmlspecialchars($session['last_activity'] ?? 'Ù†Ø§Ù…Ø´Ø®Øµ') ?></span>
                    </div>
                    <div class="text-white/40 text-xs mt-1 font-mono">
                        IP: <?= htmlspecialchars($session['ip_address'] ?? 'Ù†Ø§Ù…Ø´Ø®Øµ') ?>
                    </div>
                </div>
            </div>
            
            <?php if (empty($session['is_current'])): ?>
            <button 
                onclick="terminateSession('<?= htmlspecialchars($session['id']) ?>')"
                class="bg-red-500/20 hover:bg-red-500/30 text-red-300 px-4 py-2 rounded-lg transition text-sm"
            >
                <i class="fas fa-times"></i>
                <span>Ù¾Ø§ÛŒØ§Ù†</span>
            </button>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php elseif ($activeTab === 'activity'): ?>
<!-- â•â•â• Activity Log Tab â•â•â• -->
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
            <span>Ù¾Ø§Ú© Ú©Ø±Ø¯Ù†</span>
        </button>
    </div>
    
    <?php if (empty($activityLog)): ?>
    <div class="text-center py-12">
        <div class="text-5xl mb-3">ðŸ“</div>
        <p class="text-white/50 text-sm">Ù‡ÛŒÚ† ÙØ¹Ø§Ù„ÛŒØªÛŒ Ø«Ø¨Øª Ù†Ø´Ø¯Ù‡</p>
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
                        if (strpos($action, 'login') !== false) echo 'ðŸ”';
                        elseif (strpos($action, 'logout') !== false) echo 'ðŸšª';
                        elseif (strpos($action, 'change') !== false) echo 'ðŸ”§';
                        elseif (strpos($action, 'delete') !== false) echo 'ðŸ—‘ï¸';
                        elseif (strpos($action, 'create') !== false) echo 'âž•';
                        else echo 'ðŸ“';
                        ?>
                    </span>
                    <span class="text-white font-medium text-sm">
                        <?= htmlspecialchars($log['action'] ?? 'Ù†Ø§Ù…Ø´Ø®Øµ') ?>
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
                    <?= htmlspecialchars($log['ip_address'] ?? 'Ù†Ø§Ù…Ø´Ø®Øµ') ?>
                </span>
                <span>
                    <i class="fas fa-laptop"></i>
                    <?= htmlspecialchars($log['user_agent'] ?? 'Ù†Ø§Ù…Ø´Ø®Øµ') ?>
                </span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php else: ?>
<!-- â•â•â• Personal Info Tab â•â•â• -->
<div class="glass rounded-2xl p-6">
    <h3 class="text-white font-bold text-xl mb-6 flex items-center gap-2">
        <i class="fas fa-user"></i>
        <span>Ø§Ø·Ù„Ø§Ø¹Ø§Øª Ø´Ø®ØµÛŒ</span>
    </h3>
    
    <form id="profileForm" class="space-y-5 max-w-xl">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        
        <!-- Name -->
        <div>
            <label class="block text-white/70 text-sm mb-2">
                Ù†Ø§Ù… Ùˆ Ù†Ø§Ù… Ø®Ø§Ù†ÙˆØ§Ø¯Ú¯ÛŒ <span class="text-red-400">*</span>
            </label>
            <input 
                type="text" 
                name="name"
                value="<?= htmlspecialchars($admin['name'] ?? '') ?>"
                required
                class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition"
                placeholder="Ù†Ø§Ù… Ø®ÙˆØ¯ Ø±Ø§ ÙˆØ§Ø±Ø¯ Ú©Ù†ÛŒØ¯"
            >
        </div>
        
        <!-- Username -->
        <div>
            <label class="block text-white/70 text-sm mb-2">
                Ù†Ø§Ù… Ú©Ø§Ø±Ø¨Ø±ÛŒ <span class="text-red-400">*</span>
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
            <p class="text-white/40 text-xs mt-1">Ù†Ø§Ù… Ú©Ø§Ø±Ø¨Ø±ÛŒ Ù‚Ø§Ø¨Ù„ ØªØºÛŒÛŒØ± Ù†ÛŒØ³Øª</p>
        </div>
        
        <!-- Email -->
        <div>
            <label class="block text-white/70 text-sm mb-2">
                Ø§ÛŒÙ…ÛŒÙ„
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
                Ø´Ù…Ø§Ø±Ù‡ ØªÙ…Ø§Ø³
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
                Ø¨ÛŒÙˆÚ¯Ø±Ø§ÙÛŒ
            </label>
            <textarea 
                name="bio"
                rows="3"
                class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white placeholder-white/40 focus:border-purple-500 transition resize-none"
                placeholder="Ú†Ù†Ø¯ Ø®Ø· Ø¯Ø±Ø¨Ø§Ø±Ù‡ Ø®ÙˆØ¯ØªØ§Ù† Ø¨Ù†ÙˆÛŒØ³ÛŒØ¯..."
            ><?= htmlspecialchars($admin['bio'] ?? '') ?></textarea>
        </div>
        
        <!-- Timezone -->
        <div>
            <label class="block text-white/70 text-sm mb-2">
                Ù…Ù†Ø·Ù‚Ù‡ Ø²Ù…Ø§Ù†ÛŒ
            </label>
            <select 
                name="timezone"
                class="w-full bg-white/10 border border-white/20 rounded-lg py-2.5 px-4 text-white focus:border-purple-500 transition"
            >
                <option value="Asia/Tehran" <?= ($admin['timezone'] ?? '') === 'Asia/Tehran' ? 'selected' : '' ?>>
                    Asia/Tehran (ØªÙ‡Ø±Ø§Ù†)
                </option>
                <option value="Asia/Dubai" <?= ($admin['timezone'] ?? '') === 'Asia/Dubai' ? 'selected' : '' ?>>
                    Asia/Dubai (Ø¯Ø¨ÛŒ)
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
            <span>Ø°Ø®ÛŒØ±Ù‡ ØªØºÛŒÛŒØ±Ø§Øª</span>
        </button>
        
    </form>
</div>
<?php endif; ?>

<!-- â•â•â• JavaScript â•â•â• -->
<script>
// â•â•â• Toggle Password Visibility â•â•â•
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

// â•â•â• Check Password Strength â•â•â•
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
    const texts = ['Ø®ÛŒÙ„ÛŒ Ø¶Ø¹ÛŒÙ', 'Ø¶Ø¹ÛŒÙ', 'Ù…ØªÙˆØ³Ø·', 'Ù‚ÙˆÛŒ', 'Ø®ÛŒÙ„ÛŒ Ù‚ÙˆÛŒ'];
    
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

// â•â•â• Update Password Requirements â•â•â•
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

// â•â•â• Check Password Match â•â•â•
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
        matchDiv.innerHTML = '<i class="fas fa-check-circle text-green-400"></i><span class="text-green-400">Ø±Ù…Ø²Ù‡Ø§ÛŒ Ø¹Ø¨ÙˆØ± Ù…Ø·Ø§Ø¨Ù‚Øª Ø¯Ø§Ø±Ù†Ø¯</span>';
    } else {
        matchDiv.innerHTML = '<i class="fas fa-times-circle text-red-400"></i><span class="text-red-400">Ø±Ù…Ø²Ù‡Ø§ÛŒ Ø¹Ø¨ÙˆØ± Ù…Ø·Ø§Ø¨Ù‚Øª Ù†Ø¯Ø§Ø±Ù†Ø¯</span>';
    }
}

// â•â•â• Save Profile â•â•â•
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
            showToast('Ø§Ø·Ù„Ø§Ø¹Ø§Øª Ø¨Ø§ Ù…ÙˆÙÙ‚ÛŒØª Ø¨Ø±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ Ø´Ø¯', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.error || 'Ø®Ø·Ø§ Ø¯Ø± Ø¨Ø±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
});

// â•â•â• Change Password â•â•â•
document.getElementById('passwordForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Validation
    if (data.new_password !== data.confirm_password) {
        showToast('Ø±Ù…Ø²Ù‡Ø§ÛŒ Ø¹Ø¨ÙˆØ± Ù…Ø·Ø§Ø¨Ù‚Øª Ù†Ø¯Ø§Ø±Ù†Ø¯', 'error');
        return;
    }
    
    if (data.new_password.length < 8) {
        showToast('Ø±Ù…Ø² Ø¹Ø¨ÙˆØ± Ø¨Ø§ÛŒØ¯ Ø­Ø¯Ø§Ù‚Ù„ 8 Ú©Ø§Ø±Ø§Ú©ØªØ± Ø¨Ø§Ø´Ø¯', 'error');
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
            showToast('Ø±Ù…Ø² Ø¹Ø¨ÙˆØ± Ø¨Ø§ Ù…ÙˆÙÙ‚ÛŒØª ØªØºÛŒÛŒØ± Ú©Ø±Ø¯', 'success');
            setTimeout(() => {
                window.location.href = '/admin/login.php?success=password_changed';
            }, 2000);
        } else {
            showToast(result.error || 'Ø®Ø·Ø§ Ø¯Ø± ØªØºÛŒÛŒØ± Ø±Ù…Ø²', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
});

// â•â•â• Terminate Session â•â•â•
async function terminateSession(sessionId) {
    if (!confirm('Ø¢ÛŒØ§ Ù…Ø·Ù…Ø¦Ù† Ù‡Ø³ØªÛŒØ¯ Ú©Ù‡ Ù…ÛŒâ€ŒØ®ÙˆØ§Ù‡ÛŒØ¯ Ø§ÛŒÙ† Session Ø±Ø§ Ù¾Ø§ÛŒØ§Ù† Ø¯Ù‡ÛŒØ¯ØŸ')) {
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
            showToast('Session Ù¾Ø§ÛŒØ§Ù† ÛŒØ§ÙØª', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Logout All Sessions â•â•â•
async function logoutAllSessions() {
    if (!confirm('Ø¢ÛŒØ§ Ù…Ø·Ù…Ø¦Ù† Ù‡Ø³ØªÛŒØ¯ Ú©Ù‡ Ù…ÛŒâ€ŒØ®ÙˆØ§Ù‡ÛŒØ¯ Ø§Ø² Ù‡Ù…Ù‡ Session Ù‡Ø§ Ø®Ø§Ø±Ø¬ Ø´ÙˆÛŒØ¯ØŸ\n\nØ´Ù…Ø§ Ø¨Ø§ÛŒØ¯ Ø¯ÙˆØ¨Ø§Ø±Ù‡ ÙˆØ§Ø±Ø¯ Ø´ÙˆÛŒØ¯!')) {
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
            showToast('Ø§Ø² Ù‡Ù…Ù‡ Session Ù‡Ø§ Ø®Ø§Ø±Ø¬ Ø´Ø¯ÛŒØ¯', 'success');
            setTimeout(() => {
                window.location.href = '/admin/login.php';
            }, 2000);
        } else {
            showToast(result.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}

// â•â•â• Clear Activity Log â•â•â•
async function clearActivityLog() {
    if (!confirm('Ø¢ÛŒØ§ Ù…Ø·Ù…Ø¦Ù† Ù‡Ø³ØªÛŒØ¯ Ú©Ù‡ Ù…ÛŒâ€ŒØ®ÙˆØ§Ù‡ÛŒØ¯ ØªÙ…Ø§Ù… Activity Log Ø±Ø§ Ù¾Ø§Ú© Ú©Ù†ÛŒØ¯ØŸ\n\nØ§ÛŒÙ† Ø¹Ù…Ù„ ØºÛŒØ±Ù‚Ø§Ø¨Ù„ Ø¨Ø§Ø²Ú¯Ø´Øª Ø§Ø³Øª!')) {
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
            showToast('Activity Log Ù¾Ø§Ú© Ø´Ø¯', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.error || 'Ø®Ø·Ø§', 'error');
        }
    } catch (error) {
        showToast('Ø®Ø·Ø§ Ø¯Ø± Ø§Ø±ØªØ¨Ø§Ø· Ø¨Ø§ Ø³Ø±ÙˆØ±', 'error');
    }
}
</script>