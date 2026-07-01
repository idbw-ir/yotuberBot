<?php
/**
 * ============================================
 * Routes Configuration - تنظیمات مسیرها
 * ============================================
 * نسخه: 2.0.0
 * 
 * تعریف تمام Route های پروژه شامل:
 * - Public Routes (صفحات عمومی)
 * - Admin Routes (پنل مدیریت)
 * - API Routes (API endpoints)
 * - Webhook Routes (تلگرام)
 */

// دریافت Router Instance
$router = \App\Core\Router::getInstance();

// ──────────────────────────────────────
// 1. تعریف Middleware ها
// ──────────────────────────────────────

/**
 * Middleware احراز هویت ادمین
 */
$router->middleware('auth', function() {
    $auth = \App\Admin\Auth::getInstance();
    
    if (!$auth->check()) {
        // ذخیره URL فعلی برای بازگشت
        $session = \App\Core\Session::getInstance();
        $session->set('intended_url', $_SERVER['REQUEST_URI']);
        
        header('Location: /admin/login.php');
        exit;
    }
    
    return true;
});

/**
 * Middleware بررسی ادمین بودن
 */
$router->middleware('admin', function() {
    $auth = \App\Admin\Auth::getInstance();
    
    if (!$auth->isAdmin()) {
        http_response_code(403);
        die('شما دسترسی به این بخش را ندارید');
    }
    
    return true;
});

/**
 * Middleware بررسی Super Admin
 */
$router->middleware('super_admin', function() {
    $auth = \App\Admin\Auth::getInstance();
    
    if (!$auth->isSuperAdmin()) {
        http_response_code(403);
        die('شما دسترسی به این بخش را ندارید');
    }
    
    return true;
});

/**
 * Middleware CSRF Protection
 */
$router->middleware('csrf', function() {
    if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'])) {
        $session = \App\Core\Session::getInstance();
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        if (!$token || !$session->verifyCsrfToken($token)) {
            http_response_code(403);
            die('خطای امنیتی: توکن CSRF نامعتبر است');
        }
    }
    
    return true;
});

/**
 * Middleware API Authentication
 */
$router->middleware('api_auth', function() {
    $api = new \App\Api\StatisticsApi();
    
    if (!$api->authenticate()) {
        exit;
    }
    
    return true;
});

/**
 * Middleware Rate Limiting
 */
$router->middleware('rate_limit', function() {
    $ip = \App\Helpers\Security::getClientIp();
    $result = \App\Helpers\Security::checkRateLimit("route_{$ip}", 60, 60);
    
    if (!$result['allowed']) {
        http_response_code(429);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => 'محدودیت نرخ درخواست. لطفاً بعداً تلاش کنید.',
            'retry_after' => $result['reset_at'] - time()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    return true;
});

// ──────────────────────────────────────
// 2. Public Routes (صفحات عمومی)
// ──────────────────────────────────────

// صفحه اصلی
$router->get('/', function() {
    require PUBLIC_PATH . '/pages/home.php';
});

// درباره ما
$router->get('/about', function() {
    require PUBLIC_PATH . '/pages/about.php';
});

// تماس با ما
$router->get('/contact', function() {
    require PUBLIC_PATH . '/pages/contact.php';
});

// قوانین و مقررات
$router->get('/terms', function() {
    require PUBLIC_PATH . '/pages/terms.php';
});

// حریم خصوصی
$router->get('/privacy', function() {
    require PUBLIC_PATH . '/pages/privacy.php';
});

// ──────────────────────────────────────
// 3. Webhook Routes (تلگرام)
// ──────────────────────────────────────

// Webhook تلگرام
$router->post('/webhook', function() {
    require PUBLIC_PATH . '/webhook.php';
});

$router->post('/webhook.php', function() {
    require PUBLIC_PATH . '/webhook.php';
});

// ──────────────────────────────────────
// 4. Admin Routes (پنل مدیریت)
// ──────────────────────────────────────

// لاگین (بدون Middleware)
$router->get('/admin/login', function() {
    require PUBLIC_PATH . '/admin/login.php';
});

$router->post('/admin/login', function() {
    require PUBLIC_PATH . '/admin/login.php';
}, ['csrf']);

// خروج
$router->get('/admin/logout', function() {
    require PUBLIC_PATH . '/admin/logout.php';
});

$router->post('/admin/logout', function() {
    require PUBLIC_PATH . '/admin/logout.php';
}, ['csrf']);

// داشبورد
$router->get('/admin', function() {
    require PUBLIC_PATH . '/admin/index.php';
}, ['auth']);

$router->get('/admin/', function() {
    require PUBLIC_PATH . '/admin/index.php';
}, ['auth']);

// کاربران
$router->get('/admin/users', function() {
    require PUBLIC_PATH . '/admin/users.php';
}, ['auth']);

$router->get('/admin/users.php', function() {
    require PUBLIC_PATH . '/admin/users.php';
}, ['auth']);

// چت زنده
$router->get('/admin/chat', function() {
    require PUBLIC_PATH . '/admin/chat.php';
}, ['auth']);

$router->get('/admin/chat.php', function() {
    require PUBLIC_PATH . '/admin/chat.php';
}, ['auth']);

// پیام‌ها
$router->get('/admin/messages', function() {
    require PUBLIC_PATH . '/admin/messages.php';
}, ['auth']);

$router->get('/admin/messages.php', function() {
    require PUBLIC_PATH . '/admin/messages.php';
}, ['auth']);

// دونیت‌ها
$router->get('/admin/donations', function() {
    require PUBLIC_PATH . '/admin/donations.php';
}, ['auth']);

$router->get('/admin/donations.php', function() {
    require PUBLIC_PATH . '/admin/donations.php';
}, ['auth']);

// کلمات کلیدی
$router->get('/admin/keywords', function() {
    require PUBLIC_PATH . '/admin/keywords.php';
}, ['auth']);

$router->get('/admin/keywords.php', function() {
    require PUBLIC_PATH . '/admin/keywords.php';
}, ['auth']);

// ارسال دسته‌جمعی
$router->get('/admin/broadcast', function() {
    require PUBLIC_PATH . '/admin/broadcast.php';
}, ['auth']);

$router->get('/admin/broadcast.php', function() {
    require PUBLIC_PATH . '/admin/broadcast.php';
}, ['auth']);

// آمار و گزارشات
$router->get('/admin/statistics', function() {
    require PUBLIC_PATH . '/admin/statistics.php';
}, ['auth']);

$router->get('/admin/statistics.php', function() {
    require PUBLIC_PATH . '/admin/statistics.php';
}, ['auth']);

// تنظیمات
$router->get('/admin/settings', function() {
    require PUBLIC_PATH . '/admin/settings.php';
}, ['auth']);

$router->get('/admin/settings.php', function() {
    require PUBLIC_PATH . '/admin/settings.php';
}, ['auth']);

// پروفایل
$router->get('/admin/profile', function() {
    require PUBLIC_PATH . '/admin/profile.php';
}, ['auth']);

$router->get('/admin/profile.php', function() {
    require PUBLIC_PATH . '/admin/profile.php';
}, ['auth']);

// ──────────────────────────────────────
// 5. Admin API Routes
// ──────────────────────────────────────

// Users API
$router->get('/admin/api/users/{id}', function($id) {
    require BASE_PATH . '/app/Admin/Api/UsersApi.php';
    $api = new \App\Admin\Api\UsersApi();
    $api->show($id);
}, ['auth']);

$router->post('/admin/api/users/create', function() {
    require BASE_PATH . '/app/Admin/Api/UsersApi.php';
    $api = new \App\Admin\Api\UsersApi();
    $api->create();
}, ['auth', 'csrf']);

$router->post('/admin/api/users/update', function() {
    require BASE_PATH . '/app/Admin/Api/UsersApi.php';
    $api = new \App\Admin\Api\UsersApi();
    $api->update();
}, ['auth', 'csrf']);

$router->post('/admin/api/users/delete', function() {
    require BASE_PATH . '/app/Admin/Api/UsersApi.php';
    $api = new \App\Admin\Api\UsersApi();
    $api->delete();
}, ['auth', 'csrf']);

$router->post('/admin/api/users/vip', function() {
    require BASE_PATH . '/app/Admin/Api/UsersApi.php';
    $api = new \App\Admin\Api\UsersApi();
    $api->toggleVip();
}, ['auth', 'csrf']);

$router->post('/admin/api/users/block', function() {
    require BASE_PATH . '/app/Admin/Api/UsersApi.php';
    $api = new \App\Admin\Api\UsersApi();
    $api->toggleBlock();
}, ['auth', 'csrf']);

$router->post('/admin/api/users/bulk', function() {
    require BASE_PATH . '/app/Admin/Api/UsersApi.php';
    $api = new \App\Admin\Api\UsersApi();
    $api->bulkAction();
}, ['auth', 'csrf']);

$router->get('/admin/api/users/export', function() {
    require BASE_PATH . '/app/Admin/Api/UsersApi.php';
    $api = new \App\Admin\Api\UsersApi();
    $api->export();
}, ['auth']);

// Messages API
$router->get('/admin/api/messages/{id}', function($id) {
    require BASE_PATH . '/app/Admin/Api/MessagesApi.php';
    $api = new \App\Admin\Api\MessagesApi();
    $api->show($id);
}, ['auth']);

$router->post('/admin/api/messages/delete', function() {
    require BASE_PATH . '/app/Admin/Api/MessagesApi.php';
    $api = new \App\Admin\Api\MessagesApi();
    $api->delete();
}, ['auth', 'csrf']);

$router->get('/admin/api/messages/export', function() {
    require BASE_PATH . '/app/Admin/Api/MessagesApi.php';
    $api = new \App\Admin\Api\MessagesApi();
    $api->export();
}, ['auth']);

// Chat API
$router->post('/admin/api/chat/send', function() {
    require BASE_PATH . '/app/Admin/Api/ChatApi.php';
    $api = new \App\Admin\Api\ChatApi();
    $api->send();
}, ['auth', 'csrf']);

$router->get('/admin/api/chat/new-messages', function() {
    require BASE_PATH . '/app/Admin/Api/ChatApi.php';
    $api = new \App\Admin\Api\ChatApi();
    $api->getNewMessages();
}, ['auth']);

$router->post('/admin/api/chat/clear', function() {
    require BASE_PATH . '/app/Admin/Api/ChatApi.php';
    $api = new \App\Admin\Api\ChatApi();
    $api->clear();
}, ['auth', 'csrf']);

$router->get('/admin/api/chat/export/{userId}', function($userId) {
    require BASE_PATH . '/app/Admin/Api/ChatApi.php';
    $api = new \App\Admin\Api\ChatApi();
    $api->export($userId);
}, ['auth']);

// Donations API
$router->get('/admin/api/donations/{id}', function($id) {
    require BASE_PATH . '/app/Admin/Api/DonationsApi.php';
    $api = new \App\Admin\Api\DonationsApi();
    $api->show($id);
}, ['auth']);

$router->post('/admin/api/donations/approve', function() {
    require BASE_PATH . '/app/Admin/Api/DonationsApi.php';
    $api = new \App\Admin\Api\DonationsApi();
    $api->approve();
}, ['auth', 'csrf']);

$router->post('/admin/api/donations/reject', function() {
    require BASE_PATH . '/app/Admin/Api/DonationsApi.php';
    $api = new \App\Admin\Api\DonationsApi();
    $api->reject();
}, ['auth', 'csrf']);

$router->post('/admin/api/donations/delete', function() {
    require BASE_PATH . '/app/Admin/Api/DonationsApi.php';
    $api = new \App\Admin\Api\DonationsApi();
    $api->delete();
}, ['auth', 'csrf']);

$router->get('/admin/api/donations/export', function() {
    require BASE_PATH . '/app/Admin/Api/DonationsApi.php';
    $api = new \App\Admin\Api\DonationsApi();
    $api->export();
}, ['auth']);

$router->get('/admin/api/donations/financial-report', function() {
    require BASE_PATH . '/app/Admin/Api/DonationsApi.php';
    $api = new \App\Admin\Api\DonationsApi();
    $api->financialReport();
}, ['auth']);

// Keywords API
$router->get('/admin/api/keywords/{id}', function($id) {
    require BASE_PATH . '/app/Admin/Api/KeywordsApi.php';
    $api = new \App\Admin\Api\KeywordsApi();
    $api->show($id);
}, ['auth']);

$router->post('/admin/api/keywords/create', function() {
    require BASE_PATH . '/app/Admin/Api/KeywordsApi.php';
    $api = new \App\Admin\Api\KeywordsApi();
    $api->create();
}, ['auth', 'csrf']);

$router->post('/admin/api/keywords/update', function() {
    require BASE_PATH . '/app/Admin/Api/KeywordsApi.php';
    $api = new \App\Admin\Api\KeywordsApi();
    $api->update();
}, ['auth', 'csrf']);

$router->post('/admin/api/keywords/delete', function() {
    require BASE_PATH . '/app/Admin/Api/KeywordsApi.php';
    $api = new \App\Admin\Api\KeywordsApi();
    $api->delete();
}, ['auth', 'csrf']);

$router->post('/admin/api/keywords/toggle', function() {
    require BASE_PATH . '/app/Admin/Api/KeywordsApi.php';
    $api = new \App\Admin\Api\KeywordsApi();
    $api->toggle();
}, ['auth', 'csrf']);

$router->post('/admin/api/keywords/bulk', function() {
    require BASE_PATH . '/app/Admin/Api/KeywordsApi.php';
    $api = new \App\Admin\Api\KeywordsApi();
    $api->bulkAction();
}, ['auth', 'csrf']);

$router->post('/admin/api/keywords/test', function() {
    require BASE_PATH . '/app/Admin/Api/KeywordsApi.php';
    $api = new \App\Admin\Api\KeywordsApi();
    $api->test();
}, ['auth', 'csrf']);

$router->get('/admin/api/keywords/stats/{id}', function($id) {
    require BASE_PATH . '/app/Admin/Api/KeywordsApi.php';
    $api = new \App\Admin\Api\KeywordsApi();
    $api->stats($id);
}, ['auth']);

$router->post('/admin/api/keywords/import', function() {
    require BASE_PATH . '/app/Admin/Api/KeywordsApi.php';
    $api = new \App\Admin\Api\KeywordsApi();
    $api->import();
}, ['auth', 'csrf']);

$router->get('/admin/api/keywords/export', function() {
    require BASE_PATH . '/app/Admin/Api/KeywordsApi.php';
    $api = new \App\Admin\Api\KeywordsApi();
    $api->export();
}, ['auth']);

// Broadcast API
$router->post('/admin/api/broadcast/create', function() {
    require BASE_PATH . '/app/Admin/Api/BroadcastApi.php';
    $api = new \App\Admin\Api\BroadcastApi();
    $api->create();
}, ['auth', 'csrf']);

$router->get('/admin/api/broadcast/{id}', function($id) {
    require BASE_PATH . '/app/Admin/Api/BroadcastApi.php';
    $api = new \App\Admin\Api\BroadcastApi();
    $api->show($id);
}, ['auth']);

$router->post('/admin/api/broadcast/start', function() {
    require BASE_PATH . '/app/Admin/Api/BroadcastApi.php';
    $api = new \App\Admin\Api\BroadcastApi();
    $api->start();
}, ['auth', 'csrf']);

$router->post('/admin/api/broadcast/pause', function() {
    require BASE_PATH . '/app/Admin/Api/BroadcastApi.php';
    $api = new \App\Admin\Api\BroadcastApi();
    $api->pause();
}, ['auth', 'csrf']);

$router->post('/admin/api/broadcast/resume', function() {
    require BASE_PATH . '/app/Admin/Api/BroadcastApi.php';
    $api = new \App\Admin\Api\BroadcastApi();
    $api->resume();
}, ['auth', 'csrf']);

$router->post('/admin/api/broadcast/cancel', function() {
    require BASE_PATH . '/app/Admin/Api/BroadcastApi.php';
    $api = new \App\Admin\Api\BroadcastApi();
    $api->cancel();
}, ['auth', 'csrf']);

$router->post('/admin/api/broadcast/delete', function() {
    require BASE_PATH . '/app/Admin/Api/BroadcastApi.php';
    $api = new \App\Admin\Api\BroadcastApi();
    $api->delete();
}, ['auth', 'csrf']);

$router->post('/admin/api/broadcast/duplicate', function() {
    require BASE_PATH . '/app/Admin/Api/BroadcastApi.php';
    $api = new \App\Admin\Api\BroadcastApi();
    $api->duplicate();
}, ['auth', 'csrf']);

$router->post('/admin/api/broadcast/count-target', function() {
    require BASE_PATH . '/app/Admin/Api/BroadcastApi.php';
    $api = new \App\Admin\Api\BroadcastApi();
    $api->countTarget();
}, ['auth', 'csrf']);

$router->post('/admin/api/broadcast/preview', function() {
    require BASE_PATH . '/app/Admin/Api/BroadcastApi.php';
    $api = new \App\Admin\Api\BroadcastApi();
    $api->preview();
}, ['auth', 'csrf']);

// Settings API
$router->post('/admin/api/settings/save', function() {
    require BASE_PATH . '/app/Admin/Api/SettingsApi.php';
    $api = new \App\Admin\Api\SettingsApi();
    $api->save();
}, ['auth', 'csrf']);

$router->post('/admin/api/settings/reset', function() {
    require BASE_PATH . '/app/Admin/Api/SettingsApi.php';
    $api = new \App\Admin\Api\SettingsApi();
    $api->reset();
}, ['auth', 'csrf']);

$router->post('/admin/api/settings/reset-category', function() {
    require BASE_PATH . '/app/Admin/Api/SettingsApi.php';
    $api = new \App\Admin\Api\SettingsApi();
    $api->resetCategory();
}, ['auth', 'csrf']);

$router->post('/admin/api/settings/backup', function() {
    require BASE_PATH . '/app/Admin/Api/SettingsApi.php';
    $api = new \App\Admin\Api\SettingsApi();
    $api->backup();
}, ['auth', 'csrf']);

$router->get('/admin/api/settings/backup/download', function() {
    require BASE_PATH . '/app/Admin/Api/SettingsApi.php';
    $api = new \App\Admin\Api\SettingsApi();
    $api->downloadBackup();
}, ['auth']);

$router->post('/admin/api/settings/restore', function() {
    require BASE_PATH . '/app/Admin/Api/SettingsApi.php';
    $api = new \App\Admin\Api\SettingsApi();
    $api->restore();
}, ['auth', 'csrf']);

$router->post('/admin/api/settings/backup/delete', function() {
    require BASE_PATH . '/app/Admin/Api/SettingsApi.php';
    $api = new \App\Admin\Api\SettingsApi();
    $api->deleteBackup();
}, ['auth', 'csrf']);

$router->post('/admin/api/settings/clear-log', function() {
    require BASE_PATH . '/app/Admin/Api/SettingsApi.php';
    $api = new \App\Admin\Api\SettingsApi();
    $api->clearLog();
}, ['auth', 'csrf']);

// Profile API
$router->post('/admin/api/profile/update', function() {
    require BASE_PATH . '/app/Admin/Api/ProfileApi.php';
    $api = new \App\Admin\Api\ProfileApi();
    $api->update();
}, ['auth', 'csrf']);

$router->post('/admin/api/profile/change-password', function() {
    require BASE_PATH . '/app/Admin/Api/ProfileApi.php';
    $api = new \App\Admin\Api\ProfileApi();
    $api->changePassword();
}, ['auth', 'csrf']);

$router->post('/admin/api/profile/terminate-session', function() {
    require BASE_PATH . '/app/Admin/Api/ProfileApi.php';
    $api = new \App\Admin\Api\ProfileApi();
    $api->terminateSession();
}, ['auth', 'csrf']);

$router->post('/admin/api/profile/logout-all', function() {
    require BASE_PATH . '/app/Admin/Api/ProfileApi.php';
    $api = new \App\Admin\Api\ProfileApi();
    $api->logoutAll();
}, ['auth', 'csrf']);

$router->post('/admin/api/profile/clear-activity-log', function() {
    require BASE_PATH . '/app/Admin/Api/ProfileApi.php';
    $api = new \App\Admin\Api\ProfileApi();
    $api->clearActivityLog();
}, ['auth', 'csrf']);

// Chart API
$router->get('/admin/api/chart/users', function() {
    require BASE_PATH . '/app/Admin/Api/ChartApi.php';
    $api = new \App\Admin\Api\ChartApi();
    $api->users();
}, ['auth']);

$router->get('/admin/api/chart/donations', function() {
    require BASE_PATH . '/app/Admin/Api/ChartApi.php';
    $api = new \App\Admin\Api\ChartApi();
    $api->donations();
}, ['auth']);

$router->get('/admin/api/chart/messages', function() {
    require BASE_PATH . '/app/Admin/Api/ChartApi.php';
    $api = new \App\Admin\Api\ChartApi();
    $api->messages();
}, ['auth']);

// ──────────────────────────────────────
// 6. Public API Routes
// ──────────────────────────────────────

// Statistics API (با API Token)
$router->any('/api/statistics/{endpoint}', function($endpoint) {
    require BASE_PATH . '/app/Api/StatisticsApi.php';
    $api = new \App\Api\StatisticsApi();
    $api->route($endpoint);
}, ['api_auth', 'rate_limit']);

// Donation Callback
$router->any('/api/donation/callback/{gateway}', function($gateway) {
    require BASE_PATH . '/app/Api/DonationCallback.php';
    $callback = new \App\Api\DonationCallback();
    $callback->handle($gateway);
});

// ──────────────────────────────────────
// 7. Payment Routes
// ──────────────────────────────────────

// صفحه موفقیت پرداخت
$router->get('/payment/success', function() {
    require PUBLIC_PATH . '/payment/success.php';
});

// صفحه شکست پرداخت
$router->get('/payment/failed', function() {
    require PUBLIC_PATH . '/payment/failed.php';
});

// ──────────────────────────────────────
// 8. Error Routes
// ──────────────────────────────────────

// 404 Not Found (handled by Router)
// 500 Error (handled by bootstrap.php)

// ──────────────────────────────────────
// 9. Helper Function برای تولید URL
// ──────────────────────────────────────

if (!function_exists('route')) {
    /**
     * تولید URL برای Route
     */
    function route($path, $params = []) {
        $router = \App\Core\Router::getInstance();
        return $router->url($path, $params);
    }
}

// ──────────────────────────────────────
// 10. پایان تعریف Route ها
// ──────────────────────────────────────

// لاگ تعداد Route ها (فقط در Debug Mode)
if (config('app.debug') && php_sapi_name() !== 'cli') {
    try {
        $logger = \App\Core\Logger::getInstance();
        $logger->debug('Routes loaded', [
            'count' => 'Multiple routes registered'
        ]);
    } catch (Exception $e) {
        // نادیده بگیر
    }
}