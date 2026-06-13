<?php

namespace App\Core;

/**
 * Клас для роботи з багатомовністю
 */
class Lang
{
    private static ?string $currentLang = null;
    private static array $translations = [];
    private static array $availableLangs = ['uk', 'en', 'ru'];
    
    /**
     * Ініціалізація мови
     */
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Визначаємо поточну мову
        if (isset($_GET['lang']) && in_array($_GET['lang'], self::$availableLangs)) {
            self::setLang($_GET['lang']);
        } elseif (isset($_SESSION['lang'])) {
            self::$currentLang = $_SESSION['lang'];
        } else {
            // Мова за замовчуванням
            self::$currentLang = 'uk';
        }
        
        // Завантажуємо переклади
        self::loadTranslations();
    }
    
    /**
     * Встановити поточну мову
     */
    public static function setLang(string $lang): void
    {
        if (in_array($lang, self::$availableLangs)) {
            self::$currentLang = $lang;
            $_SESSION['lang'] = $lang;
            self::loadTranslations();
        }
    }
    
    /**
     * Отримати поточну мову
     */
    public static function getCurrentLang(): string
    {
        return self::$currentLang ?? 'uk';
    }
    
    /**
     * Отримати список доступних мов
     */
    public static function getAvailableLangs(): array
    {
        return [
            'uk' => ['name' => 'Українська', 'flag' => '🇺🇦'],
            'en' => ['name' => 'English', 'flag' => '🇬🇧'],
            'ru' => ['name' => 'Русский', 'flag' => '🇷🇺']
        ];
    }
    
    /**
     * Завантажити переклади для поточної мови
     */
    private static function loadTranslations(): void
    {
        $langFile = ROOT_PATH . '/lang/' . self::$currentLang . '.php';
        
        if (file_exists($langFile)) {
            self::$translations = require $langFile;
        } else {
            self::$translations = [];
        }
    }
    
    /**
     * Отримати переклад за ключем
     */
    public static function get(string $key, array $replace = []): string
    {
        // Підтримка точкової нотації: 'auth.login'
        $keys = explode('.', $key);
        $value = self::$translations;
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $key; // Повертаємо ключ якщо переклад не знайдено
            }
        }
        
        // Заміна плейсхолдерів
        if (!empty($replace)) {
            foreach ($replace as $search => $replaceValue) {
                $value = str_replace(':' . $search, $replaceValue, $value);
            }
        }
        
        return $value;
    }
}

/**
 * Глобальна функція-хелпер для отримання перекладів
 */
function __($key, $replace = []) {
    return \App\Core\Lang::get($key, $replace);
}

/**
 * Альтернативна коротка функція
 */
function t($key, $replace = []) {
    return \App\Core\Lang::get($key, $replace);
}
