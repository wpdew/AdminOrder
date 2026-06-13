<?php

namespace App\Core;

use App\Models\User;

/**
 * Клас для управління авторизацією
 */
class Auth
{
    /**
     * Ініціалізація сесії
     */
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Перевірити чи користувач авторизований
     */
    public static function check(): bool
    {
        self::init();
        return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
    }
    
    /**
     * Отримати дані поточного користувача
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name'] ?? 'Користувач'
        ];
    }
    
    /**
     * Отримати ID поточного користувача
     */
    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Увійти в систему
     */
    public static function login(string $login, string $password): bool
    {
        $user = User::findByEmailOrNickname($login);
        
        if ($user && password_verify($password, $user['password'])) {
            self::init();
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['lang'] = $user['interface_lang'] ?? 'en';
            
            // Оновлюємо час останнього входу
            User::updateLastLogin($user['id']);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Увійти в систему за ID користувача (для OAuth)
     */
    public static function loginById(int $userId): bool
    {
        $user = User::findById($userId);
        
        if ($user) {
            self::init();
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['lang'] = $user['interface_lang'] ?? 'en';
            
            // Оновлюємо час останнього входу
            User::updateLastLogin($user['id']);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Вийти з системи
     */
    public static function logout(): void
    {
        self::init();
        
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }
        
        session_destroy();
    }
    
    /**
     * Перенаправити на сторінку входу якщо не авторизований
     */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            header('Location: /admin/?route=login');
            exit;
        }
    }
    
    /**
     * Перевірити чи є права адміністратора і перенаправити якщо немає
     */
    public static function requireAdmin(): void
    {
        self::requireAuth();
        
        if (!self::isAdmin()) {
            $_SESSION['flash_error'] = 'У вас немає прав доступу до цієї сторінки';
            header('Location: /admin/?route=access-denied');
            exit;
        }
    }
    
    /**
     * Перевірити чи поточний користувач є адміністратором
     */
    public static function isAdmin(): bool
    {
        if (!self::check()) {
            return false;
        }
        
        return User::isAdmin(self::id());
    }
    
    /**
     * Отримати роль поточного користувача
     */
    public static function role(): string
    {
        if (!self::check()) {
            return 'guest';
        }
        
        return User::getRole(self::id());
    }
    
    /**
     * Перенаправити на головну якщо вже авторизований
     */
    public static function requireGuest(): void
    {
        if (self::check()) {
            header('Location: /admin');
            exit;
        }
    }
}
