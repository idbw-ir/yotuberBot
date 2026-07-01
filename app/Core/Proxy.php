<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Config;
use App\Core\Logger;

class Proxy {
    private static $instance = null;
    private $config;
    private $logger;

    private function __construct() {
        $this->config = Config::getInstance();
        $this->logger = Logger::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function isEnabled(): bool {
        return (bool)$this->config->get('proxy.enabled', false);
    }

    public function getType(): string {
        return $this->config->get('proxy.type', 'http');
    }

    public function getHost(): string {
        return $this->config->get('proxy.host', '');
    }

    public function getPort(): int {
        return (int)$this->config->get('proxy.port', 0);
    }

    public function getUsername(): string {
        return $this->config->get('proxy.username', '');
    }

    public function getPassword(): string {
        return $this->config->get('proxy.password', '');
    }

    public function getDns(): string {
        return $this->config->get('proxy.dns', '');
    }

    private function getCurlProxyType(): int {
        $map = [
            'http' => CURLPROXY_HTTP,
            'https' => CURLPROXY_HTTPS,
            'socks4' => CURLPROXY_SOCKS4,
            'socks5' => CURLPROXY_SOCKS5,
        ];
        return $map[$this->getType()] ?? CURLPROXY_HTTP;
    }

    public function applyToCurl($ch): void {
        if (!$this->isEnabled()) {
            return;
        }

        $host = $this->getHost();
        $port = $this->getPort();

        if (empty($host) || $port <= 0) {
            return;
        }

        curl_setopt($ch, CURLOPT_PROXY, $host);
        curl_setopt($ch, CURLOPT_PROXYPORT, $port);
        curl_setopt($ch, CURLOPT_PROXYTYPE, $this->getCurlProxyType());

        $username = $this->getUsername();
        $password = $this->getPassword();

        if (!empty($username)) {
            $auth = $username;
            if (!empty($password)) {
                $auth .= ":{$password}";
            }
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $auth);
        }

        $dns = $this->getDns();
        if (!empty($dns)) {
            curl_setopt($ch, CURLOPT_DNS_SERVERS, $dns);
        }

        $this->logger->debug('Proxy applied to curl request', [
            'type' => $this->getType(),
            'host' => $host,
            'port' => $port,
            'has_auth' => !empty($username),
            'dns' => $dns ?: 'default',
        ]);
    }

    public function test(string $host, int $port, string $type = 'http', string $username = '', string $password = '', string $dns = ''): array {
        $ch = curl_init('https://api.telegram.org/bot/test/getMe');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_PROXY => $host,
            CURLOPT_PROXYPORT => $port,
            CURLOPT_PROXYTYPE => (['http' => CURLPROXY_HTTP, 'https' => CURLPROXY_HTTPS, 'socks4' => CURLPROXY_SOCKS4, 'socks5' => CURLPROXY_SOCKS5])[$type] ?? CURLPROXY_HTTP,
        ]);

        if (!empty($username)) {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $username . (!empty($password) ? ":{$password}" : ''));
        }
        if (!empty($dns)) {
            curl_setopt($ch, CURLOPT_DNS_SERVERS, $dns);
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => "پروکسی خطا داد: {$error}"];
        }

        if ($httpCode === 200) {
            return ['success' => true, 'message' => 'پروکسی با موفقیت کار می‌کند'];
        }

        return ['success' => false, 'error' => "کد پاسخ غیرمنتظره: {$httpCode}"];
    }

    public function toArray(): array {
        return [
            'enabled' => $this->isEnabled(),
            'type' => $this->getType(),
            'host' => $this->getHost(),
            'port' => $this->getPort(),
            'username' => $this->getUsername(),
            'password' => $this->getPassword(),
            'dns' => $this->getDns(),
        ];
    }
}