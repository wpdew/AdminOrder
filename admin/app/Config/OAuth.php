<?php

namespace App\Config;

/**
 * Конфігурація OAuth провайдерів
 */
class OAuth
{
    /**
     * Отримати конфігурацію для Google OAuth
     */
    public static function getGoogleConfig(): array
    {
        return [
            'clientId'     => self::getEnv('GOOGLE_CLIENT_ID'),
            'clientSecret' => self::getEnv('GOOGLE_CLIENT_SECRET'),
            'redirectUri'  => self::getEnv('GOOGLE_REDIRECT_URI', self::getDefaultRedirectUri('google')),
        ];
    }
    
    /**
     * Отримати конфігурацію для GitHub OAuth
     */
    public static function getGithubConfig(): array
    {
        return [
            'clientId'     => self::getEnv('GITHUB_CLIENT_ID'),
            'clientSecret' => self::getEnv('GITHUB_CLIENT_SECRET'),
            'redirectUri'  => self::getEnv('GITHUB_REDIRECT_URI', self::getDefaultRedirectUri('github')),
        ];
    }
    
    /**
     * Перевірити чи налаштований Google OAuth
     */
    public static function isGoogleConfigured(): bool
    {
        $config = self::getGoogleConfig();
        return !empty($config['clientId']) && !empty($config['clientSecret']);
    }
    
    /**
     * Перевірити чи налаштований GitHub OAuth
     */
    public static function isGithubConfigured(): bool
    {
        $config = self::getGithubConfig();
        return !empty($config['clientId']) && !empty($config['clientSecret']);
    }
    
    /**
     * Отримати значення змінної середовища
     */
    private static function getEnv(string $key, ?string $default = null): ?string
    {
        // Спочатку перевіряємо $_ENV
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        // Потім getenv()
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        // Повертаємо default
        return $default;
    }
    
    /**
     * Отримати URL для redirect за замовчуванням
     */
    private static function getDefaultRedirectUri(string $provider): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUri = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
        
        // Видаляємо подвійні слеші
        $baseUri = str_replace('//', '/', $baseUri . '/');
        
        return "{$protocol}://{$host}{$baseUri}oauth/{$provider}/callback";
    }
    
    /**
     * Завантажити змінні з .env файлу
     */
    public static function loadEnv(): void
    {
        $envFile = dirname(__DIR__, 2) . '/.env';
        
        if (!file_exists($envFile)) {
            return;
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Пропускаємо коментарі
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Парсимо KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Видаляємо лапки
                $value = trim($value, '"\'');
                
                // Підстановка змінних ${VAR}
                $value = preg_replace_callback('/\$\{([A-Z_]+)\}/', function($matches) {
                    return self::getEnv($matches[1], '');
                }, $value);
                
                // Зберігаємо в $_ENV
                if (!isset($_ENV[$key])) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }
}
