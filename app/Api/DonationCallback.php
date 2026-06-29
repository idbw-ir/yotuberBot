<?php
/**
 * ============================================
 * کلاس مدیریت کال‌بک درگاه‌های پرداخت
 * ============================================
 * پردازش کال‌بک از درگاه‌های مختلف
 * اعتبارسنجی امضا و صحت پرداخت
 * بروزرسانی وضعیت دونیت
 * ارسال پیام تأیید به کاربر
 * بررسی VIP خودکار
 * لاگ تمام عملیات
 * امنیت بالا
 */

namespace App\Api;

use App\Core\Database;
use App\Core\Logger;
use App\Core\Config;
use App\Admin\Donations;
use App\Telegram\Bot;
use App\Helpers\Security;

class DonationCallback {
    private $db;
    private $logger;
    private $config;
    private $donations;
    private $bot;
    
    // ──────────────────────────────────────
    // Constructor
    // ──────────────────────────────────────
    public function __construct() {
        $this->db = Database::getInstance();
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
        $this->donations = Donations::getInstance();
        $this->bot = new Bot();
    }
    
    // ══════════════════════════════════════
    // پردازش اصلی کال‌بک
    // ══════════════════════════════════════
    
    /**
     * پردازش کال‌بک بر اساس درگاه
     */
    public function handle($gateway) {
        $this->logger->info('Donation callback received', [
            'gateway' => $gateway,
            'ip' => Security::getClientIp(),
            'method' => $_SERVER['REQUEST_METHOD']
        ]);
        
        try {
            switch (strtolower($gateway)) {
                case 'zarinpal':
                    return $this->handleZarinPal();
                    
                case 'idpay':
                    return $this->handleIDPay();
                    
                case 'nextpay':
                    return $this->handleNextPay();
                    
                case 'nowpayments':
                    return $this->handleNowPayments();
                    
                default:
                    $this->logger->error('Unknown payment gateway', ['gateway' => $gateway]);
                    return $this->sendResponse(false, 'درگاه پرداخت نامعتبر است');
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Donation callback error', [
                'gateway' => $gateway,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->sendResponse(false, 'خطا در پردازش پرداخت');
        }
    }
    
    // ══════════════════════════════════════
    // زرین‌پال (ZarinPal)
    // ══════════════════════════════════════
    
    /**
     * پردازش کال‌بک زرین‌پال
     */
    private function handleZarinPal() {
        $authority = $_GET['Authority'] ?? $_POST['Authority'] ?? null;
        $status = $_GET['Status'] ?? $_POST['Status'] ?? null;
        
        if (!$authority) {
            $this->logger->error('ZarinPal callback missing Authority');
            return $this->sendResponse(false, 'Authority یافت نشد');
        }
        
        // دریافت اطلاعات دونیت از دیتابیس
        $donation = $this->db->fetch(
            "SELECT * FROM donations WHERE ref_id = ? AND gateway = 'zarinpal'",
            [$authority]
        );
        
        if (!$donation) {
            $this->logger->error('ZarinPal donation not found', ['authority' => $authority]);
            return $this->sendResponse(false, 'دونیت یافت نشد');
        }
        
        // بررسی وضعیت
        if (strtolower($status) !== 'ok') {
            $this->logger->warning('ZarinPal payment not OK', [
                'authority' => $authority,
                'status' => $status
            ]);
            
            $this->donations->reject($donation['id'], 'پرداخت توسط کاربر لغو شد');
            
            return $this->redirectWithMessage('/payment/failed', 'پرداخت لغو شد');
        }
        
        // تأیید پرداخت از زرین‌پال
        $verification = $this->verifyZarinPal($authority, $donation['amount']);
        
        if (!$verification['success']) {
            $this->logger->error('ZarinPal verification failed', [
                'authority' => $authority,
                'error' => $verification['error']
            ]);
            
            $this->donations->reject($donation['id'], $verification['error']);
            
            return $this->redirectWithMessage('/payment/failed', 'تأیید پرداخت ناموفق بود');
        }
        
        // پردازش موفقیت
        return $this->processSuccess(
            $donation['id'],
            $donation['user_id'],
            $donation['amount'],
            $verification['ref_id']
        );
    }
    
    /**
     * تأیید پرداخت زرین‌پال
     */
    private function verifyZarinPal($authority, $amount) {
        $merchantId = $this->config->get('zarinpal_merchant_id');
        
        if (!$merchantId) {
            return ['success' => false, 'error' => 'Merchant ID تنظیم نشده'];
        }
        
        $data = [
            'merchant_id' => $merchantId,
            'authority' => $authority,
            'amount' => $amount
        ];
        
        $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/verify.json');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['data'])) {
            return ['success' => false, 'error' => 'پاسخ نامعتبر از زرین‌پال'];
        }
        
        $code = $result['data']['code'] ?? -1;
        
        if ($code === 100 || $code === 101) {
            return [
                'success' => true,
                'ref_id' => $result['data']['ref_id'] ?? null
            ];
        }
        
        return [
            'success' => false,
            'error' => "کد خطا: {$code} - " . ($result['data']['message'] ?? 'نامشخص')
        ];
    }
    
    // ══════════════════════════════════════
    // IDPay
    // ══════════════════════════════════════
    
    /**
     * پردازش کال‌بک IDPay
     */
    private function handleIDPay() {
        // دریافت داده‌ها از POST یا JSON
        $input = file_get_contents('php://input');
        $data = json_decode($input, true) ?: $_POST;
        
        $id = $data['id'] ?? null;
        $orderId = $data['order_id'] ?? null;
        $status = $data['status'] ?? null;
        $trackId = $data['track_id'] ?? null;
        $amount = $data['amount'] ?? null;
        
        if (!$id || !$orderId) {
            $this->logger->error('IDPay callback missing data', ['data' => $data]);
            return $this->sendResponse(false, 'داده‌های کال‌بک ناقص است');
        }
        
        // بررسی امضا (در صورت فعال بودن)
        if (!$this->verifyIDPaySignature($data)) {
            $this->logger->error('IDPay signature verification failed', ['data' => $data]);
            return $this->sendResponse(false, 'امضای کال‌بک نامعتبر است');
        }
        
        // دریافت اطلاعات دونیت
        $donation = $this->db->fetch(
            "SELECT * FROM donations WHERE ref_id = ? AND gateway = 'idpay'",
            [$id]
        );
        
        if (!$donation) {
            $this->logger->error('IDPay donation not found', ['id' => $id]);
            return $this->sendResponse(false, 'دونیت یافت نشد');
        }
        
        // بررسی وضعیت
        if ($status != 100 && $status != 101) {
            $this->logger->warning('IDPay payment not successful', [
                'id' => $id,
                'status' => $status
            ]);
            
            $this->donations->reject($donation['id'], "وضعیت پرداخت: {$status}");
            
            return $this->redirectWithMessage('/payment/failed', 'پرداخت موفق نبود');
        }
        
        // تأیید پرداخت از IDPay
        $verification = $this->verifyIDPay($id, $orderId);
        
        if (!$verification['success']) {
            $this->logger->error('IDPay verification failed', [
                'id' => $id,
                'error' => $verification['error']
            ]);
            
            $this->donations->reject($donation['id'], $verification['error']);
            
            return $this->redirectWithMessage('/payment/failed', 'تأیید پرداخت ناموفق بود');
        }
        
        // پردازش موفقیت
        return $this->processSuccess(
            $donation['id'],
            $donation['user_id'],
            $donation['amount'],
            $trackId
        );
    }
    
    /**
     * تأیید پرداخت IDPay
     */
    private function verifyIDPay($id, $orderId) {
        $apiKey = $this->config->get('idpay_api_key');
        
        if (!$apiKey) {
            return ['success' => false, 'error' => 'API Key تنظیم نشده'];
        }
        
        $data = [
            'id' => $id,
            'order_id' => $orderId
        ];
        
        $ch = curl_init('https://api.idpay.ir/v1.1/verify');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-KEY: ' . $apiKey,
                'X-SANDBOX: 0' // 1 برای تست
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if (!$result) {
            return ['success' => false, 'error' => 'پاسخ نامعتبر از IDPay'];
        }
        
        $status = $result['status'] ?? 0;
        
        if ($status === 100 || $status === 101) {
            return [
                'success' => true,
                'track_id' => $result['track_id'] ?? null
            ];
        }
        
        return [
            'success' => false,
            'error' => "کد خطا: {$status} - " . ($result['error_message'] ?? 'نامشخص')
        ];
    }
    
    /**
     * بررسی امضای IDPay
     */
    private function verifyIDPaySignature($data) {
        $signature = $this->config->get('idpay_signature');
        
        if (!$signature) {
            return true; // اگر signature تنظیم نشده، نادیده بگیر
        }
        
        $receivedSignature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
        
        if (empty($receivedSignature)) {
            return false;
        }
        
        // ساخت امضای مورد انتظار
        $expectedSignature = hash_hmac('sha256', json_encode($data), $signature);
        
        return hash_equals($expectedSignature, $receivedSignature);
    }
    
    // ══════════════════════════════════════
    // NextPay
    // ══════════════════════════════════════
    
    /**
     * پردازش کال‌بک NextPay
     */
    private function handleNextPay() {
        $orderId = $_GET['order_id'] ?? $_POST['order_id'] ?? null;
        $transactionId = $_GET['transaction_id'] ?? $_POST['transaction_id'] ?? null;
        $amount = $_GET['amount'] ?? $_POST['amount'] ?? null;
        $status = $_GET['status'] ?? $_POST['status'] ?? null;
        
        if (!$orderId || !$transactionId) {
            $this->logger->error('NextPay callback missing data');
            return $this->sendResponse(false, 'داده‌های کال‌بک ناقص است');
        }
        
        // دریافت اطلاعات دونیت
        $donation = $this->db->fetch(
            "SELECT * FROM donations WHERE ref_id = ? AND gateway = 'nextpay'",
            [$orderId]
        );
        
        if (!$donation) {
            $this->logger->error('NextPay donation not found', ['order_id' => $orderId]);
            return $this->sendResponse(false, 'دونیت یافت نشد');
        }
        
        // بررسی وضعیت
        if ($status != 0) {
            $this->logger->warning('NextPay payment not successful', [
                'order_id' => $orderId,
                'status' => $status
            ]);
            
            $this->donations->reject($donation['id'], "وضعیت پرداخت: {$status}");
            
            return $this->redirectWithMessage('/payment/failed', 'پرداخت موفق نبود');
        }
        
        // تأیید پرداخت از NextPay
        $verification = $this->verifyNextPay($orderId, $transactionId);
        
        if (!$verification['success']) {
            $this->logger->error('NextPay verification failed', [
                'order_id' => $orderId,
                'error' => $verification['error']
            ]);
            
            $this->donations->reject($donation['id'], $verification['error']);
            
            return $this->redirectWithMessage('/payment/failed', 'تأیید پرداخت ناموفق بود');
        }
        
        // پردازش موفقیت
        return $this->processSuccess(
            $donation['id'],
            $donation['user_id'],
            $donation['amount'],
            $transactionId
        );
    }
    
    /**
     * تأیید پرداخت NextPay
     */
    private function verifyNextPay($orderId, $transactionId) {
        $apiKey = $this->config->get('nextpay_api_key');
        
        if (!$apiKey) {
            return ['success' => false, 'error' => 'API Key تنظیم نشده'];
        }
        
        $data = [
            'api_key' => $apiKey,
            'order_id' => $orderId,
            'transaction_id' => $transactionId
        ];
        
        $ch = curl_init('https://nextpay.org/nx/gateway/verify');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if (!$result) {
            return ['success' => false, 'error' => 'پاسخ نامعتبر از NextPay'];
        }
        
        $code = $result['code'] ?? -1;
        
        if ($code === 0) {
            return [
                'success' => true,
                'card_holder' => $result['card_holder'] ?? null
            ];
        }
        
        return [
            'success' => false,
            'error' => "کد خطا: {$code}"
        ];
    }
    
    // ══════════════════════════════════════
    // NowPayments (Crypto)
    // ══════════════════════════════════════
    
    /**
     * پردازش کال‌بک NowPayments
     */
    private function handleNowPayments() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data) {
            $this->logger->error('NowPayments callback invalid JSON');
            return $this->sendResponse(false, 'داده نامعتبر');
        }
        
        // بررسی امضا
        if (!$this->verifyNowPaymentsSignature($input)) {
            $this->logger->error('NowPayments signature verification failed');
            return $this->sendResponse(false, 'امضای کال‌بک نامعتبر است');
        }
        
        $paymentId = $data['payment_id'] ?? null;
        $status = $data['payment_status'] ?? null;
        $orderId = $data['order_id'] ?? null;
        
        if (!$paymentId || !$orderId) {
            $this->logger->error('NowPayments callback missing data', ['data' => $data]);
            return $this->sendResponse(false, 'داده‌های کال‌بک ناقص است');
        }
        
        // دریافت اطلاعات دونیت
        $donation = $this->db->fetch(
            "SELECT * FROM donations WHERE ref_id = ? AND gateway = 'nowpayments'",
            [$orderId]
        );
        
        if (!$donation) {
            $this->logger->error('NowPayments donation not found', ['order_id' => $orderId]);
            return $this->sendResponse(false, 'دونیت یافت نشد');
        }
        
        // بررسی وضعیت
        if ($status !== 'finished') {
            $this->logger->warning('NowPayments payment not finished', [
                'payment_id' => $paymentId,
                'status' => $status
            ]);
            
            if ($status === 'failed' || $status === 'refunded' || $status === 'expired') {
                $this->donations->reject($donation['id'], "وضعیت: {$status}");
            }
            
            return $this->sendResponse(true, 'OK');
        }
        
        // پردازش موفقیت
        return $this->processSuccess(
            $donation['id'],
            $donation['user_id'],
            $donation['amount'],
            $paymentId
        );
    }
    
    /**
     * بررسی امضای NowPayments
     */
    private function verifyNowPaymentsSignature($payload) {
        $ipnSecret = $this->config->get('nowpayments_ipn_secret');
        
        if (!$ipnSecret) {
            return true; // اگر IPN Secret تنظیم نشده، نادیده بگیر
        }
        
        $signature = $_SERVER['HTTP_X_NOWPAYMENTS_SIG'] ?? '';
        
        if (empty($signature)) {
            return false;
        }
        
        $expectedSignature = hash_hmac('sha512', $payload, $ipnSecret);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    // ══════════════════════════════════════
    // پردازش موفقیت
    // ══════════════════════════════════════
    
    /**
     * پردازش پرداخت موفق
     */
    private function processSuccess($donationId, $userId, $amount, $transactionId) {
        // تأیید دونیت
        $result = $this->donations->approve($donationId, $transactionId);
        
        if (!$result['success']) {
            $this->logger->error('Failed to approve donation', [
                'donation_id' => $donationId,
                'error' => $result['error']
            ]);
            
            return $this->redirectWithMessage('/payment/failed', 'خطا در ثبت پرداخت');
        }
        
        // لاگ موفقیت
        $this->logger->info('Donation approved successfully', [
            'donation_id' => $donationId,
            'user_id' => $userId,
            'amount' => $amount,
            'transaction_id' => $transactionId
        ]);
        
        // ارسال پیام به ادمین
        $this->notifyAdmin($userId, $amount, $transactionId);
        
        // هدایت به صفحه موفقیت
        return $this->redirectWithMessage(
            '/payment/success',
            'پرداخت با موفقیت انجام شد',
            ['amount' => $amount]
        );
    }
    
    /**
     * ارسال نوتیفیکیشن به ادمین
     */
    private function notifyAdmin($userId, $amount, $transactionId) {
        try {
            $adminId = $this->config->telegram('admin_id');
            
            if (!$adminId) {
                return;
            }
            
            // دریافت اطلاعات کاربر
            $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            
            if (!$user) {
                return;
            }
            
            $userName = $user['first_name'] ?? $user['username'] ?? 'کاربر';
            $formattedAmount = number_format($amount);
            
            $message = "💰 <b>دونیت جدید دریافت شد!</b>\n\n" .
                      "👤 کاربر: {$userName}\n" .
                      "🆔 آیدی: {$userId}\n" .
                      "💵 مبلغ: {$formattedAmount} تومان\n" .
                      "🔑 Transaction ID: <code>{$transactionId}</code>\n\n" .
                      "✅ پرداخت با موفقیت تأیید شد";
            
            $this->bot->sendMessage($adminId, $message);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to notify admin', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    // ══════════════════════════════════════
    // متدهای کمکی
    // ══════════════════════════════════════
    
    /**
     * ارسال پاسخ JSON
     */
    private function sendResponse($success, $message) {
        http_response_code($success ? 200 : 400);
        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        
        exit;
    }
    
    /**
     * هدایت با پیام
     */
    private function redirectWithMessage($url, $message, $params = []) {
        $queryParams = http_build_query(array_merge(
            ['message' => $message],
            $params
        ));
        
        header("Location: {$url}?{$queryParams}");
        exit;
    }
    
    /**
     * دریافت اطلاعات دونیت
     */
    public function getDonationByRefId($refId, $gateway) {
        return $this->db->fetch(
            "SELECT * FROM donations WHERE ref_id = ? AND gateway = ?",
            [$refId, $gateway]
        );
    }
    
    /**
     * ایجاد دونیت جدید
     */
    public function createDonation($userId, $amount, $gateway, $refId = null) {
        return $this->donations->create($userId, $amount, $gateway, $refId);
    }
}