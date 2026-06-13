<?php

/**
 * Bootstrap файл для ініціалізації додатку
 */

// Встановлюємо режим розробки development
putenv('APP_ENV=development'); // або 'development' для розробки production

// Визначаємо константи шляхів
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');

// Підключаємо Composer autoload якщо існує
$composerAutoload = ROOT_PATH . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// Підключаємо Debug Headers
require_once APP_PATH . '/PhpDebugHeaders.php';
$GLOBALS['debug'] = PhpDebugHeaders::init();

// Автозавантаження класів
spl_autoload_register(function ($class) {
    // Перетворюємо namespace у шлях до файлу
    // App\Core\Auth -> app/Core/Auth.php
    $prefix = 'App\\';
    $baseDir = APP_PATH . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Завантажуємо змінні середовища з .env
\App\Config\OAuth::loadEnv();

// Запускаємо міграції для оновлення структури БД
try {
    \App\Config\Database::runMigrations();
} catch (Exception $e) {
    // Міграції не критичні, продовжуємо роботу
}

// Ініціалізуємо сесію
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ініціалізуємо систему багатомовності
\App\Core\Lang::init();

// Глобальні helper-функції для перекладів
if (!function_exists('__')) {
    /**
     * Отримати переклад
     * @param string $key Ключ перекладу (наприклад, 'auth.login')
     * @param array $replace Масив для заміни плейсхолдерів
     * @return string Перекладений текст
     */
    function __($key, $replace = []) {
        return \App\Core\Lang::get($key, $replace);
    }
}

if (!function_exists('t')) {
    /**
     * Alias для __()
     * @param string $key Ключ перекладу
     * @param array $replace Масив для заміни плейсхолдерів
     * @return string Перекладений текст
     */
    function t($key, $replace = []) {
        return \App\Core\Lang::get($key, $replace);
    }
}
