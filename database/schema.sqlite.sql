-- Youtuber Bot - Database Schema (SQLite/Bunny)
-- نسخه: 2.1.2 - سازگار با SQLite و Bunny Database (Turso)

PRAGMA foreign_keys = ON;

-- 1. Users
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT,
    first_name TEXT,
    last_name TEXT,
    phone TEXT,
    bio TEXT,
    language_code TEXT NOT NULL DEFAULT 'fa',
    is_vip INTEGER NOT NULL DEFAULT 0,
    vip_expires_at TEXT,
    blocked INTEGER NOT NULL DEFAULT 0,
    notes TEXT,
    total_donations REAL NOT NULL DEFAULT 0.00,
    donation_count INTEGER NOT NULL DEFAULT 0,
    message_count INTEGER NOT NULL DEFAULT 0,
    last_seen TEXT,
    last_seen_ip TEXT,
    joined_at TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at TEXT
);

CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_is_vip ON users(is_vip);
CREATE INDEX idx_users_blocked ON users(blocked);
CREATE INDEX idx_users_last_seen ON users(last_seen);
CREATE INDEX idx_users_joined_at ON users(joined_at);
CREATE INDEX idx_users_total_donations ON users(total_donations);

-- 2. Admins
DROP TABLE IF EXISTS admins;
CREATE TABLE admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    name TEXT NOT NULL DEFAULT '',
    email TEXT UNIQUE,
    phone TEXT,
    role TEXT NOT NULL DEFAULT 'admin' CHECK(role IN ('super_admin','admin','editor','moderator')),
    bio TEXT,
    timezone TEXT NOT NULL DEFAULT 'Asia/Tehran',
    avatar TEXT,
    active INTEGER NOT NULL DEFAULT 1,
    email_verified_at TEXT,
    remember_token TEXT UNIQUE,
    remember_expiry TEXT,
    last_login TEXT,
    last_ip TEXT,
    last_user_agent TEXT,
    login_count INTEGER NOT NULL DEFAULT 0,
    failed_login_attempts INTEGER NOT NULL DEFAULT 0,
    locked_until TEXT,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at TEXT
);

CREATE INDEX idx_admins_role ON admins(role);
CREATE INDEX idx_admins_active ON admins(active);
CREATE INDEX idx_admins_last_login ON admins(last_login);

-- 3. Messages
DROP TABLE IF EXISTS messages;
CREATE TABLE messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    admin_id INTEGER DEFAULT NULL REFERENCES admins(id) ON DELETE SET NULL,
    text TEXT,
    direction TEXT NOT NULL DEFAULT 'in' CHECK(direction IN ('in','out','note','ai')),
    message_type TEXT NOT NULL DEFAULT 'text' CHECK(message_type IN ('text','photo','video','document','audio','voice','location','contact','sticker','ai','note')),
    file_id TEXT,
    file_size INTEGER,
    file_name TEXT,
    mime_type TEXT,
    telegram_message_id INTEGER,
    is_read INTEGER NOT NULL DEFAULT 0,
    read_at TEXT,
    reply_to_message_id INTEGER,
    metadata TEXT,
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX idx_messages_user_id ON messages(user_id);
CREATE INDEX idx_messages_admin_id ON messages(admin_id);
CREATE INDEX idx_messages_direction ON messages(direction);
CREATE INDEX idx_messages_message_type ON messages(message_type);
CREATE INDEX idx_messages_is_read ON messages(is_read);
CREATE INDEX idx_messages_created_at ON messages(created_at);
CREATE INDEX idx_messages_user_created ON messages(user_id, created_at);

-- 4. Donations
DROP TABLE IF EXISTS donations;
CREATE TABLE donations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    amount REAL NOT NULL,
    currency TEXT NOT NULL DEFAULT 'IRT',
    gateway TEXT NOT NULL DEFAULT 'manual' CHECK(gateway IN ('zarinpal','idpay','nextpay','nowpayments','manual','crypto','other')),
    status TEXT NOT NULL DEFAULT 'pending' CHECK(status IN ('pending','success','failed','refunded','cancelled')),
    ref_id TEXT,
    transaction_id TEXT,
    authority TEXT,
    track_id TEXT,
    card_number TEXT,
    card_holder TEXT,
    description TEXT,
    reject_reason TEXT,
    payment_url TEXT,
    verify_data TEXT,
    approved_at TEXT,
    rejected_at TEXT,
    paid_at TEXT,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at TEXT
);

CREATE INDEX idx_donations_user_id ON donations(user_id);
CREATE INDEX idx_donations_status ON donations(status);
CREATE INDEX idx_donations_gateway ON donations(gateway);
CREATE INDEX idx_donations_ref_id ON donations(ref_id);
CREATE INDEX idx_donations_transaction_id ON donations(transaction_id);
CREATE INDEX idx_donations_created_at ON donations(created_at);
CREATE INDEX idx_donations_user_status ON donations(user_id, status);
CREATE INDEX idx_donations_amount ON donations(amount);

-- 5. Keywords
DROP TABLE IF EXISTS keywords;
CREATE TABLE keywords (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    keyword TEXT NOT NULL UNIQUE,
    answer TEXT NOT NULL,
    answer_type TEXT NOT NULL DEFAULT 'text' CHECK(answer_type IN ('text','photo','video','document','audio','voice','sticker')),
    file_id TEXT,
    priority INTEGER NOT NULL DEFAULT 0,
    active INTEGER NOT NULL DEFAULT 1,
    case_sensitive INTEGER NOT NULL DEFAULT 0,
    exact_match INTEGER NOT NULL DEFAULT 0,
    regex_mode INTEGER NOT NULL DEFAULT 0,
    match_count INTEGER NOT NULL DEFAULT 0,
    last_matched_at TEXT,
    created_by INTEGER DEFAULT NULL REFERENCES admins(id) ON DELETE SET NULL,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at TEXT
);

CREATE INDEX idx_keywords_active ON keywords(active);
CREATE INDEX idx_keywords_priority ON keywords(priority);
CREATE INDEX idx_keywords_match_count ON keywords(match_count);

-- 6. Keyword Matches
DROP TABLE IF EXISTS keyword_matches;
CREATE TABLE keyword_matches (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    keyword_id INTEGER NOT NULL REFERENCES keywords(id) ON DELETE CASCADE,
    user_id INTEGER DEFAULT NULL REFERENCES users(id) ON DELETE SET NULL,
    matched_text TEXT,
    matched_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX idx_keyword_matches_keyword_id ON keyword_matches(keyword_id);
CREATE INDEX idx_keyword_matches_user_id ON keyword_matches(user_id);
CREATE INDEX idx_keyword_matches_matched_at ON keyword_matches(matched_at);
CREATE INDEX idx_keyword_matches_keyword_date ON keyword_matches(keyword_id, matched_at);

-- 7. Settings
DROP TABLE IF EXISTS settings;
CREATE TABLE settings (
    key_name TEXT PRIMARY KEY,
    value TEXT,
    type TEXT NOT NULL DEFAULT 'string' CHECK(type IN ('string','integer','float','boolean','email','url','telegram_token','telegram_id','json','array','color')),
    category TEXT NOT NULL DEFAULT 'general',
    description TEXT,
    sort_order INTEGER NOT NULL DEFAULT 0,
    is_public INTEGER NOT NULL DEFAULT 0,
    is_sensitive INTEGER NOT NULL DEFAULT 0,
    validation_rules TEXT,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at TEXT
);

CREATE INDEX idx_settings_category ON settings(category);
CREATE INDEX idx_settings_sort_order ON settings(sort_order);

-- 8. Settings Log
DROP TABLE IF EXISTS settings_log;
CREATE TABLE settings_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    key_name TEXT NOT NULL,
    old_value TEXT,
    new_value TEXT,
    changed_by INTEGER DEFAULT NULL REFERENCES admins(id) ON DELETE SET NULL,
    changed_at TEXT NOT NULL DEFAULT (datetime('now')),
    ip_address TEXT,
    user_agent TEXT
);

CREATE INDEX idx_settings_log_key_name ON settings_log(key_name);
CREATE INDEX idx_settings_log_changed_by ON settings_log(changed_by);
CREATE INDEX idx_settings_log_changed_at ON settings_log(changed_at);

-- 9. Broadcasts
DROP TABLE IF EXISTS broadcasts;
CREATE TABLE broadcasts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    content_type TEXT NOT NULL DEFAULT 'text' CHECK(content_type IN ('text','photo','video','document','audio','voice','sticker')),
    file_id TEXT,
    target TEXT NOT NULL,
    target_options TEXT,
    target_count INTEGER NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'pending' CHECK(status IN ('pending','running','paused','completed','cancelled','scheduled')),
    delay INTEGER NOT NULL DEFAULT 50,
    sent_count INTEGER NOT NULL DEFAULT 0,
    failed_count INTEGER NOT NULL DEFAULT 0,
    blocked_count INTEGER NOT NULL DEFAULT 0,
    current_offset INTEGER NOT NULL DEFAULT 0,
    scheduled_at TEXT,
    started_at TEXT,
    completed_at TEXT,
    cancelled_at TEXT,
    created_by INTEGER DEFAULT NULL REFERENCES admins(id) ON DELETE SET NULL,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at TEXT
);

CREATE INDEX idx_broadcasts_status ON broadcasts(status);
CREATE INDEX idx_broadcasts_target ON broadcasts(target);
CREATE INDEX idx_broadcasts_created_at ON broadcasts(created_at);
CREATE INDEX idx_broadcasts_created_by ON broadcasts(created_by);

-- 10. Broadcast Recipients
DROP TABLE IF EXISTS broadcast_recipients;
CREATE TABLE broadcast_recipients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    broadcast_id INTEGER NOT NULL REFERENCES broadcasts(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    status TEXT NOT NULL DEFAULT 'pending' CHECK(status IN ('pending','success','failed','blocked')),
    error TEXT,
    sent_at TEXT,
    UNIQUE(broadcast_id, user_id)
);

CREATE INDEX idx_recipients_broadcast_id ON broadcast_recipients(broadcast_id);
CREATE INDEX idx_recipients_user_id ON broadcast_recipients(user_id);
CREATE INDEX idx_recipients_status ON broadcast_recipients(status);

-- 11. Chat Sessions
DROP TABLE IF EXISTS chat_sessions;
CREATE TABLE chat_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    admin_id INTEGER NOT NULL REFERENCES admins(id) ON DELETE CASCADE,
    status TEXT NOT NULL DEFAULT 'active' CHECK(status IN ('active','closed','transferred')),
    started_at TEXT NOT NULL DEFAULT (datetime('now')),
    ended_at TEXT,
    transferred_from INTEGER,
    notes TEXT
);

CREATE INDEX idx_chat_sessions_user_id ON chat_sessions(user_id);
CREATE INDEX idx_chat_sessions_admin_id ON chat_sessions(admin_id);
CREATE INDEX idx_chat_sessions_status ON chat_sessions(status);
CREATE INDEX idx_chat_sessions_started_at ON chat_sessions(started_at);

-- 12. API Tokens
DROP TABLE IF EXISTS api_tokens;
CREATE TABLE api_tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    token_hash TEXT NOT NULL UNIQUE,
    permissions TEXT,
    active INTEGER NOT NULL DEFAULT 1,
    expires_at TEXT,
    last_used_at TEXT,
    last_used_ip TEXT,
    last_used_user_agent TEXT,
    usage_count INTEGER NOT NULL DEFAULT 0,
    created_by INTEGER DEFAULT NULL REFERENCES admins(id) ON DELETE SET NULL,
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX idx_api_tokens_active ON api_tokens(active);
CREATE INDEX idx_api_tokens_expires_at ON api_tokens(expires_at);

-- 13. Activity Logs
DROP TABLE IF EXISTS activity_logs;
CREATE TABLE activity_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    admin_id INTEGER DEFAULT NULL REFERENCES admins(id) ON DELETE SET NULL,
    action TEXT NOT NULL,
    description TEXT,
    entity_type TEXT,
    entity_id INTEGER,
    old_values TEXT,
    new_values TEXT,
    ip_address TEXT,
    user_agent TEXT,
    metadata TEXT,
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX idx_activity_logs_admin_id ON activity_logs(admin_id);
CREATE INDEX idx_activity_logs_action ON activity_logs(action);
CREATE INDEX idx_activity_logs_entity ON activity_logs(entity_type, entity_id);
CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at);

-- 14. Admin Sessions
DROP TABLE IF EXISTS admin_sessions;
CREATE TABLE admin_sessions (
    id TEXT PRIMARY KEY,
    admin_id INTEGER DEFAULT NULL REFERENCES admins(id) ON DELETE CASCADE,
    ip_address TEXT,
    user_agent TEXT,
    device TEXT,
    location TEXT,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL,
    is_current INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX idx_admin_sessions_admin_id ON admin_sessions(admin_id);
CREATE INDEX idx_admin_sessions_last_activity ON admin_sessions(last_activity);

-- 15. Login Logs
DROP TABLE IF EXISTS login_logs;
CREATE TABLE login_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    admin_id INTEGER DEFAULT NULL REFERENCES admins(id) ON DELETE SET NULL,
    username TEXT,
    status TEXT NOT NULL CHECK(status IN ('success','failed','locked','rate_limited')),
    ip_address TEXT,
    user_agent TEXT,
    location TEXT,
    failure_reason TEXT,
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX idx_login_logs_admin_id ON login_logs(admin_id);
CREATE INDEX idx_login_logs_username ON login_logs(username);
CREATE INDEX idx_login_logs_status ON login_logs(status);
CREATE INDEX idx_login_logs_ip_address ON login_logs(ip_address);
CREATE INDEX idx_login_logs_created_at ON login_logs(created_at);

-- 16. Jobs
DROP TABLE IF EXISTS jobs;
CREATE TABLE jobs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    queue TEXT NOT NULL DEFAULT 'default',
    payload TEXT NOT NULL,
    attempts INTEGER NOT NULL DEFAULT 0,
    reserved_at INTEGER,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);

CREATE INDEX idx_jobs_queue ON jobs(queue);
CREATE INDEX idx_jobs_reserved_at ON jobs(reserved_at);
CREATE INDEX idx_jobs_available_at ON jobs(available_at);

-- 17. Failed Jobs
DROP TABLE IF EXISTS failed_jobs;
CREATE TABLE failed_jobs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at TEXT NOT NULL DEFAULT (datetime('now'))
);

-- Views
DROP VIEW IF EXISTS v_user_stats;
CREATE VIEW v_user_stats AS
SELECT
    COUNT(*) as total_users,
    SUM(CASE WHEN is_vip = 1 THEN 1 ELSE 0 END) as vip_users,
    SUM(CASE WHEN blocked = 1 THEN 1 ELSE 0 END) as blocked_users,
    SUM(CASE WHEN last_seen >= datetime('now', '-7 days') THEN 1 ELSE 0 END) as active_week,
    SUM(CASE WHEN date(joined_at) = date('now') THEN 1 ELSE 0 END) as new_today,
    SUM(total_donations) as total_revenue,
    SUM(donation_count) as total_donations_count
FROM users;

DROP VIEW IF EXISTS v_daily_donations;
CREATE VIEW v_daily_donations AS
SELECT
    date(created_at) as date,
    COUNT(*) as count,
    SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as total_amount,
    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count,
    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
    AVG(CASE WHEN status = 'success' THEN amount ELSE NULL END) as average_amount
FROM donations
GROUP BY date(created_at)
ORDER BY date DESC;

DROP VIEW IF EXISTS v_daily_messages;
CREATE VIEW v_daily_messages AS
SELECT
    date(created_at) as date,
    COUNT(*) as total,
    SUM(CASE WHEN direction = 'in' THEN 1 ELSE 0 END) as incoming,
    SUM(CASE WHEN direction = 'out' THEN 1 ELSE 0 END) as outgoing,
    SUM(CASE WHEN direction = 'ai' THEN 1 ELSE 0 END) as ai_messages,
    COUNT(DISTINCT user_id) as unique_users
FROM messages
GROUP BY date(created_at)
ORDER BY date DESC;

DROP VIEW IF EXISTS v_top_users;
CREATE VIEW v_top_users AS
SELECT
    u.id,
    u.username,
    u.first_name,
    u.last_name,
    u.total_donations,
    u.donation_count,
    u.message_count,
    u.last_seen,
    u.joined_at,
    u.first_name || ' ' || COALESCE(u.last_name, '') as full_name
FROM users u
WHERE u.blocked = 0
ORDER BY u.total_donations DESC;

-- Default admin (password: Admin@12345)
INSERT INTO admins (username, password_hash, name, email, role, active) VALUES
('admin', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/Le9Uyzg0yB.9l/O7G', 'مدیر سیستم', 'admin@example.com', 'super_admin', 1);

-- Default settings
INSERT INTO settings (key_name, value, type, category, description, sort_order) VALUES
('site_name', 'ربات یوتیوبر', 'string', 'general', 'نام سایت', 1),
('site_url', '', 'url', 'general', 'آدرس سایت', 2),
('timezone', 'Asia/Tehran', 'string', 'general', 'منطقه زمانی', 3),
('maintenance_mode', '0', 'boolean', 'general', 'حالت تعمیرات', 4),
('welcome_text', 'سلام {first_name} عزیز! 👋\n\nبه ربات ما خوش اومدی 🎬\n\nاز منوی زیر استفاده کن:', 'string', 'telegram', 'متن خوش‌آمدگویی', 10),
('welcome_photo', '', 'string', 'telegram', 'File ID عکس خوش‌آمدگویی', 11),
('donate_link', '', 'url', 'telegram', 'لینک درگاه حمایت', 12),
('donate_text', '💰 با حمایت مالی از ما، به تولید محتوای بهتر کمک می‌کنید!', 'string', 'telegram', 'متن صفحه حمایت', 13),
('youtube_url', '', 'url', 'telegram', 'لینک کانال یوتیوب', 14),
('telegram_channel', '', 'string', 'telegram', 'آیدی کانال تلگرام', 15),
('ai_enabled', '0', 'boolean', 'ai', 'فعال‌سازی هوش مصنوعی', 20),
('ai_provider', 'openai', 'string', 'ai', 'ارائه‌دهنده AI', 21),
('ai_api_key', '', 'string', 'ai', 'API Key هوش مصنوعی', 22),
('ai_model', 'gpt-4o-mini', 'string', 'ai', 'مدل AI', 23),
('ai_system_prompt', 'تو دستیار یک یوتیوبر فارسی‌زبان هستی. دوستانه و کوتاه جواب بده.', 'string', 'ai', 'System Prompt', 24),
('ai_max_tokens', '500', 'integer', 'ai', 'حداکثر توکن پاسخ', 25),
('ai_temperature', '0.7', 'float', 'ai', 'Temperature', 26),
('vip_threshold', '100000', 'integer', 'vip', 'آستانه VIP (تومان)', 30),
('vip_badge', '👑', 'string', 'vip', 'نشان VIP', 31),
('vip_duration_days', '30', 'integer', 'vip', 'مدت VIP (روز)', 32),
('admin_ip_whitelist', '[]', 'array', 'security', 'لیست IP های مجاز', 40),
('login_max_attempts', '5', 'integer', 'security', 'حداکثر تلاش ناموفق', 41),
('login_lockout_minutes', '15', 'integer', 'security', 'مدت قفل (دقیقه)', 42),
('session_timeout', '3600', 'integer', 'security', 'انقضای Session (ثانیه)', 43),
('csrf_enabled', '1', 'boolean', 'security', 'فعال‌سازی CSRF', 44),
('two_factor_enabled', '0', 'boolean', 'security', 'احراز هویت دو مرحله‌ای', 45),
('cache_ttl', '3600', 'integer', 'performance', 'TTL کش (ثانیه)', 50),
('broadcast_delay', '50', 'integer', 'performance', 'تأخیر Broadcast (ms)', 51),
('broadcast_batch_size', '30', 'integer', 'performance', 'اندازه Batch', 52),
('max_upload_size', '10485760', 'integer', 'performance', 'حداکثر حجم آپلود (bytes)', 53),
('notify_new_user', '1', 'boolean', 'notifications', 'اطلاع‌رسانی کاربر جدید', 60),
('notify_new_donation', '1', 'boolean', 'notifications', 'اطلاع‌رسانی دونیت جدید', 61),
('notify_failed_login', '1', 'boolean', 'notifications', 'اطلاع‌رسانی ورود ناموفق', 62),
('notify_blocked_user', '1', 'boolean', 'notifications', 'اطلاع‌رسانی کاربر بلاک شده', 63),

-- Proxy settings (for sanctions circumvention)
('proxy_enabled', '0', 'boolean', 'proxy', 'Enable Telegram proxy', 70),
('proxy_type', 'http', 'string', 'proxy', 'Proxy type (http/https/socks4/socks5)', 71),
('proxy_host', '', 'string', 'proxy', 'Proxy server address', 72),
('proxy_port', '0', 'integer', 'proxy', 'Proxy server port', 73),
('proxy_username', '', 'string', 'proxy', 'Proxy username', 74),
('proxy_password', '', 'string', 'proxy', 'Proxy password', 75),
('proxy_dns', '', 'string', 'proxy', 'Custom DNS (e.g. 178.22.122.100)', 76);

-- Default keywords
INSERT INTO keywords (keyword, answer, answer_type, priority, active) VALUES
('سلام', 'سلام {first_name} عزیز! 👋\n\nچطور می‌تونم کمکت کنم؟', 'text', 100, 1),
('درود', 'درود بر شما! 🌹\n\nخوش اومدید!', 'text', 99, 1),
('قیمت', 'برای اطلاع از قیمت‌ها به سایت ما مراجعه کنید:\n🌐 example.com', 'text', 90, 1),
('راهنما', '📖 <b>راهنمای استفاده</b>\n\n1️⃣ از منوی اصلی استفاده کنید\n2️⃣ برای حمایت: /donate\n3️⃣ برای تماس: /contact\n\nسوال دیگه‌ای دارید؟', 'text', 80, 1),
('حمایت', '💖 ممنون از توجه شما!\n\nبرای حمایت مالی از دکمه زیر استفاده کنید:', 'text', 70, 1),
('یوتیوب', '🎬 کانال یوتیوب ما:\n\nحتماً سابسکرایب کنید و زنگوله 🔔 رو بزنید!', 'text', 60, 1),
('vip', '👑 <b>باشگاه VIP</b>\n\nبا حمایت مالی، به جمع VIP ها بپیوندید و از مزایای ویژه بهره‌مند شوید!', 'text', 50, 1);
