<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Модель користувача
 */
class User
{
    /**
     * Дозволені мови інтерфейсу
     */
    public const ALLOWED_INTERFACE_LANGS = ['uk', 'en', 'ru'];

    /**
     * Нормалізувати код мови
     */
    private static function normalizeInterfaceLang(?string $lang): string
    {
        $lang = strtolower(trim((string)$lang));
        if (!in_array($lang, self::ALLOWED_INTERFACE_LANGS, true)) {
            return 'en';
        }

        return $lang;
    }

    /**
     * Знайти користувача за email
     */
    public static function findByEmail(string $email): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        
        $user = $stmt->fetch();
        return $user ?: null;
    }
    
    /**
     * Знайти користувача за nickname
     */
    public static function findByNickname(string $nickname): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE nickname = :nickname");
        $stmt->execute([':nickname' => $nickname]);
        
        $user = $stmt->fetch();
        return $user ?: null;
    }
    
    /**
     * Знайти користувача за email або nickname (для авторизації)
     */
    public static function findByEmailOrNickname(string $login): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :login OR nickname = :login");
        $stmt->execute([':login' => $login]);
        
        $user = $stmt->fetch();
        return $user ?: null;
    }
    
    /**
     * Знайти користувача за ID
     */
    public static function findById(int $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        $user = $stmt->fetch();
        return $user ?: null;
    }
    
    /**
     * Отримати всіх користувачів
     */
    public static function all(): array
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT id, email, nickname, name, role, interface_lang, oauth_provider, avatar, created_at, last_login FROM users ORDER BY id");
        return $stmt->fetchAll();
    }
    
    /**
     * Створити нового користувача
     */
    public static function create(
        string $email,
        string $nickname,
        string $password,
        string $name,
        string $role = 'user',
        string $interfaceLang = 'en'
    ): bool
    {
        $db = Database::getInstance();
        $interfaceLang = self::normalizeInterfaceLang($interfaceLang);
        
        // Перевірка чи існує користувач з таким email або nickname
        if (self::findByEmail($email) || self::findByNickname($nickname)) {
            return false;
        }
        
        $stmt = $db->prepare("
            INSERT INTO users (email, nickname, password, name, role, interface_lang) 
            VALUES (:email, :nickname, :password, :name, :role, :interface_lang)
        ");
        
        return $stmt->execute([
            ':email' => $email,
            ':nickname' => $nickname,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':name' => $name,
            ':role' => $role,
            ':interface_lang' => $interfaceLang,
        ]);
    }
    
    /**
     * Оновити пароль користувача
     */
    public static function updatePassword(string $email, string $newPassword): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET password = :password WHERE email = :email");
        
        return $stmt->execute([
            ':password' => password_hash($newPassword, PASSWORD_DEFAULT),
            ':email' => $email
        ]);
    }
    
    /**
     * Оновити час останнього входу
     */
    public static function updateLastLogin(int $userId): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
        
        return $stmt->execute([':id' => $userId]);
    }
    
    /**
     * Видалити користувача
     */
    public static function delete(string $email): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM users WHERE email = :email");
        
        return $stmt->execute([':email' => $email]);
    }
    
    /**
     * Видалити користувача за ID
     */
    public static function deleteById(int $id): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Оновити дані користувача
     */
    public static function update(
        int $id,
        string $email,
        string $nickname,
        string $name,
        ?string $password = null,
        string $interfaceLang = 'en'
    ): bool
    {
        $db = Database::getInstance();
        $interfaceLang = self::normalizeInterfaceLang($interfaceLang);
        
        // Якщо пароль переданий, оновлюємо і його
        if ($password !== null && !empty($password)) {
            $stmt = $db->prepare("
                UPDATE users 
                SET email = :email, nickname = :nickname, name = :name, password = :password, interface_lang = :interface_lang
                WHERE id = :id
            ");
            
            return $stmt->execute([
                ':id' => $id,
                ':email' => $email,
                ':nickname' => $nickname,
                ':name' => $name,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':interface_lang' => $interfaceLang,
            ]);
        } else {
            // Без зміни пароля
            $stmt = $db->prepare("
                UPDATE users 
                SET email = :email, nickname = :nickname, name = :name, interface_lang = :interface_lang
                WHERE id = :id
            ");
            
            return $stmt->execute([
                ':id' => $id,
                ':email' => $email,
                ':nickname' => $nickname,
                ':name' => $name,
                ':interface_lang' => $interfaceLang,
            ]);
        }
    }
    
    /**
     * Перевірити чи існує користувач з таким email
     */
    public static function exists(string $email): bool
    {
        return self::findByEmail($email) !== null;
    }
    
    /**
     * Получить количество пользователей
     */
    public static function count(): int
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT COUNT(*) as total FROM users");
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }
    
    /**
     * Обновить профиль пользователя (имя, email)
     */
    public static function updateProfile(int $id, array $data): bool
    {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            UPDATE users 
            SET name = :name, email = :email 
            WHERE id = :id
        ");
        
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':email' => $data['email']
        ]);
    }
    
    /**
     * Знайти користувача за OAuth provider та ID
     */
    public static function findByOAuth(string $provider, string $oauthId): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT * FROM users 
            WHERE oauth_provider = :provider AND oauth_id = :oauth_id
        ");
        $stmt->execute([
            ':provider' => $provider,
            ':oauth_id' => $oauthId
        ]);
        
        $user = $stmt->fetch();
        return $user ?: null;
    }
    
    /**
     * Створити користувача через OAuth
     */
    public static function createFromOAuth(
        string $provider, 
        string $oauthId, 
        string $email, 
        string $name, 
        ?string $avatar = null
    ): ?array {
        $db = Database::getInstance();
        
        try {
            // Генеруємо унікальний nickname
            $nickname = self::generateUniqueNickname($email, $name);
            
            // Для OAuth користувачів пароль не потрібен - генеруємо випадковий
            $randomPassword = bin2hex(random_bytes(16));
            
            $stmt = $db->prepare("
                INSERT INTO users (email, nickname, password, name, oauth_provider, oauth_id, avatar, role, interface_lang) 
                VALUES (:email, :nickname, :password, :name, :provider, :oauth_id, :avatar, :role, :interface_lang)
            ");
            
            $success = $stmt->execute([
                ':email' => $email,
                ':nickname' => $nickname,
                ':password' => password_hash($randomPassword, PASSWORD_DEFAULT),
                ':name' => $name,
                ':provider' => $provider,
                ':oauth_id' => $oauthId,
                ':avatar' => $avatar,
                ':role' => 'user', // По умолчанию обычный пользователь
                ':interface_lang' => 'en',
            ]);
            
            if ($success) {
                return self::findByOAuth($provider, $oauthId);
            }
            
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Оновити OAuth токен та аватар користувача
     */
    public static function updateOAuthData(int $userId, ?string $avatar = null): bool
    {
        $db = Database::getInstance();
        
        if ($avatar !== null) {
            $stmt = $db->prepare("UPDATE users SET avatar = :avatar WHERE id = :id");
            return $stmt->execute([
                ':id' => $userId,
                ':avatar' => $avatar
            ]);
        }
        
        return true;
    }
    
    /**
     * Прив'язати OAuth до існуючого користувача
     */
    public static function linkOAuth(int $userId, string $provider, string $oauthId, ?string $avatar = null): bool
    {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            UPDATE users 
            SET oauth_provider = :provider, oauth_id = :oauth_id, avatar = :avatar 
            WHERE id = :id
        ");
        
        return $stmt->execute([
            ':id' => $userId,
            ':provider' => $provider,
            ':oauth_id' => $oauthId,
            ':avatar' => $avatar
        ]);
    }
    
    /**
     * Генерувати унікальний nickname з email або імені
     */
    private static function generateUniqueNickname(string $email, string $name): string
    {
        // Спочатку пробуємо використати частину email
        $baseNickname = strpos($email, '@') !== false 
            ? substr($email, 0, strpos($email, '@')) 
            : strtolower(str_replace(' ', '', $name));
        
        // Очищаємо від небажаних символів
        $baseNickname = preg_replace('/[^a-z0-9_-]/', '', strtolower($baseNickname));
        
        $nickname = $baseNickname;
        $counter = 1;
        
        // Перевіряємо чи вільний nickname
        while (self::findByNickname($nickname) !== null) {
            $nickname = $baseNickname . $counter;
            $counter++;
        }
        
        return $nickname;
    }
    
    /**
     * Перевірити чи користувач є адміністратором
     */
    public static function isAdmin(int $userId): bool
    {
        $user = self::findById($userId);
        return $user && ($user['role'] ?? 'user') === 'admin';
    }
    
    /**
     * Оновити роль користувача
     */
    public static function updateRole(int $userId, string $role): bool
    {
        // Перевіряємо що роль валідна
        if (!in_array($role, ['user', 'admin'])) {
            return false;
        }
        
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET role = :role WHERE id = :id");
        
        return $stmt->execute([
            ':id' => $userId,
            ':role' => $role
        ]);
    }
    
    /**
     * Отримати роль користувача
     */
    public static function getRole(int $userId): string
    {
        $user = self::findById($userId);
        return $user['role'] ?? 'user';
    }
}
