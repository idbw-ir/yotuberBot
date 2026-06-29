<?php
/**
 * ============================================
 * کلاس مسیریابی (Router)
 * ============================================
 * مدیریت URL ها و Route ها
 * پشتیبانی از Parameters
 * پشتیبانی از Middleware
 */

namespace App\Core;

use Exception;

class Router {
    private static $instance = null;
    private $routes = [];
    private $middleware = [];
    private $currentRoute = null;
    private $basePath = '';
    
    // ──────────────────────────────────────
    // Constructor (Private - Singleton)
    // ──────────────────────────────────────
    private function __construct() {}
    
    // ──────────────────────────────────────
    // دریافت Instance (Singleton)
    // ──────────────────────────────────────
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // ──────────────────────────────────────
    // تنظیم Base Path
    // ──────────────────────────────────────
    public function setBasePath($path) {
        $this->basePath = rtrim($path, '/');
    }
    
    // ──────────────────────────────────────
    // ثبت Route - GET
    // ──────────────────────────────────────
    public function get($pattern, $handler, $middleware = []) {
        $this->addRoute('GET', $pattern, $handler, $middleware);
    }
    
    // ──────────────────────────────────────
    // ثبت Route - POST
    // ──────────────────────────────────────
    public function post($pattern, $handler, $middleware = []) {
        $this->addRoute('POST', $pattern, $handler, $middleware);
    }
    
    // ──────────────────────────────────────
    // ثبت Route - PUT
    // ──────────────────────────────────────
    public function put($pattern, $handler, $middleware = []) {
        $this->addRoute('PUT', $pattern, $handler, $middleware);
    }
    
    // ──────────────────────────────────────
    // ثبت Route - DELETE
    // ──────────────────────────────────────
    public function delete($pattern, $handler, $middleware = []) {
        $this->addRoute('DELETE', $pattern, $handler, $middleware);
    }
    
    // ──────────────────────────────────────
    // ثبت Route - ANY (همه متدها)
    // ──────────────────────────────────────
    public function any($pattern, $handler, $middleware = []) {
        $methods = ['GET', 'POST', 'PUT', 'DELETE'];
        foreach ($methods as $method) {
            $this->addRoute($method, $pattern, $handler, $middleware);
        }
    }
    
    // ──────────────────────────────────────
    // افزودن Route
    // ──────────────────────────────────────
    private function addRoute($method, $pattern, $handler, $middleware) {
        $pattern = $this->basePath . $pattern;
        
        // تبدیل {param} به regex
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'regex' => $regex,
            'handler' => $handler,
            'middleware' => (array)$middleware
        ];
    }
    
    // ──────────────────────────────────────
    // ثبت Middleware
    // ──────────────────────────────────────
    public function middleware($name, $callback) {
        $this->middleware[$name] = $callback;
    }
    
    // ──────────────────────────────────────
    // اجرای Router
    // ──────────────────────────────────────
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getCurrentUri();
        
        // جستجو برای Route مطابق
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['regex'], $uri, $matches)) {
                $this->currentRoute = $route;
                
                // استخراج Parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // اجرای Middleware ها
                if (!$this->runMiddleware($route['middleware'])) {
                    return;
                }
                
                // اجرای Handler
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }
        
        // Route یافت نشد - 404
        $this->notFound();
    }
    
    // ──────────────────────────────────────
    // دریافت URI فعلی
    // ──────────────────────────────────────
    private function getCurrentUri() {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';
        
        // حذف Base Path
        if ($this->basePath && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }
        
        return $uri ?: '/';
    }
    
    // ──────────────────────────────────────
    // اجرای Middleware ها
    // ──────────────────────────────────────
    private function runMiddleware($middlewareList) {
        foreach ($middlewareList as $middlewareName) {
            if (!isset($this->middleware[$middlewareName])) {
                throw new Exception("Middleware '{$middlewareName}' تعریف نشده است");
            }
            
            $result = call_user_func($this->middleware[$middlewareName]);
            
            if ($result === false) {
                return false;
            }
        }
        
        return true;
    }
    
    // ──────────────────────────────────────
    // اجرای Handler
    // ──────────────────────────────────────
    private function executeHandler($handler, $params) {
        if (is_callable($handler)) {
            // Closure
            call_user_func_array($handler, $params);
            
        } elseif (is_string($handler) && strpos($handler, '@') !== false) {
            // Controller@Method
            list($controller, $method) = explode('@', $handler);
            $controllerClass = "App\\Controllers\\{$controller}";
            
            if (!class_exists($controllerClass)) {
                throw new Exception("Controller '{$controllerClass}' یافت نشد");
            }
            
            $controllerInstance = new $controllerClass();
            
            if (!method_exists($controllerInstance, $method)) {
                throw new Exception("Method '{$method}' در Controller '{$controller}' وجود ندارد");
            }
            
            call_user_func_array([$controllerInstance, $method], $params);
            
        } elseif (is_array($handler) && count($handler) === 2) {
            // [Controller, Method]
            list($controller, $method) = $handler;
            
            if (is_string($controller)) {
                $controller = new $controller();
            }
            
            call_user_func_array([$controller, $method], $params);
            
        } else {
            throw new Exception("Handler نامعتبر است");
        }
    }
    
    // ──────────────────────────────────────
    // صفحه 404
    // ──────────────────────────────────────
    private function notFound() {
        http_response_code(404);
        
        if (file_exists(dirname(__DIR__, 2) . '/resources/views/errors/404.php')) {
            require dirname(__DIR__, 2) . '/resources/views/errors/404.php';
        } else {
            echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8">';
            echo '<title>404 - صفحه یافت نشد</title>';
            echo '<script src="https://cdn.tailwindcss.com"></script></head>';
            echo '<body class="bg-gray-900 min-h-screen flex items-center justify-center">';
            echo '<div class="text-center">';
            echo '<div class="text-6xl mb-4">🔍</div>';
            echo '<h1 class="text-3xl font-bold text-white mb-2">404</h1>';
            echo '<p class="text-gray-400 mb-6">صفحه مورد نظر یافت نشد</p>';
            echo '<a href="/" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">بازگشت به خانه</a>';
            echo '</div></body></html>';
        }
    }
    
    // ──────────────────────────────────────
    // دریافت Route فعلی
    // ──────────────────────────────────────
    public function getCurrentRoute() {
        return $this->currentRoute;
    }
    
    // ──────────────────────────────────────
    // تولید URL
    // ──────────────────────────────────────
    public function url($pattern, $params = []) {
        $url = $this->basePath . $pattern;
        
        foreach ($params as $key => $value) {
            $url = str_replace("{{$key}}", $value, $url);
        }
        
        return $url;
    }
    
    // ──────────────────────────────────────
    // Redirect
    // ──────────────────────────────────────
    public function redirect($url, $statusCode = 302) {
        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }
    
    // ──────────────────────────────────────
    // جلوگیری از Clone
    // ──────────────────────────────────────
    private function __clone() {}
    
    // ──────────────────────────────────────
    // جلوگیری از Unserialize
    // ──────────────────────────────────────
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}