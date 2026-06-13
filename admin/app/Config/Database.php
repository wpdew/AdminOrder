<?php

namespace App\Config;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Клас для роботи з базою даних SQLite
 */
class Database
{
    private static PDO|DebugPDO|null $instance = null;
    private string $dbPath;
    
    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->dbPath = dirname(__DIR__, 2) . '/database/crm.db';
    }
    
    /**
     * Отримати єдиний екземпляр підключення до БД (Singleton)
     */
    public static function getInstance(): PDO|DebugPDO
    {
        if (self::$instance === null) {
            $db = new self();
            self::$instance = $db->connect();
        }
        
        return self::$instance;
    }
    
    /**
     * Створити підключення до бази даних
     */
    private function connect(): PDO|DebugPDO
    {
        try {
            // Створюємо директорію для БД якщо її немає
            $dbDir = dirname($this->dbPath);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            
            $pdo = new PDO('sqlite:' . $this->dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Обгортаємо PDO для підрахунку запитів
            if (class_exists('PhpDebugHeaders') && isset($GLOBALS['debug'])) {
                return new DebugPDO($pdo, $GLOBALS['debug']);
            }
            
            return $pdo;
        } catch (PDOException $e) {
            die('Помилка підключення до бази даних: ' . $e->getMessage());
        }
    }
    
    /**
     * Ініціалізація структури бази даних
     */
    public static function initSchema(): void
    {
        $db = self::getInstance();
        
        // Таблиця користувачів
        $db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT UNIQUE NOT NULL,
                nickname TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                name TEXT NOT NULL,
                interface_lang TEXT DEFAULT 'en' NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_login DATETIME
            )
        ");
    }
    
    /**
     * Запустити міграції для оновлення структури БД
     */
    public static function runMigrations(): void
    {
        $db = self::getInstance();
        
        try {
            // Перевіряємо чи існує таблиця users
            $tableExists = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetch();
            
            if (!$tableExists) {
                // Таблиця не існує - створюємо з правильною структурою
                self::initSchema();
                // Додаємо OAuth поля після створення базової структури
                self::addOAuthColumns();
                self::addRoleColumn();
                self::addInterfaceLangColumn();
                self::createOrderSettingsTable();
                self::seedOrderSettingsDefaults();
                self::createBlockedIpsTable();
                self::seedDefaultBlockedIps();
                self::createOrdersTable();
                return;
            }
            
            // Перевіряємо чи існує колонка nickname
            $result = $db->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC);
            $hasNickname = false;
            $hasOAuthProvider = false;
            $hasOAuthId = false;
            $hasAvatar = false;
            
            foreach ($result as $column) {
                if ($column['name'] === 'nickname') {
                    $hasNickname = true;
                }
                if ($column['name'] === 'oauth_provider') {
                    $hasOAuthProvider = true;
                }
                if ($column['name'] === 'oauth_id') {
                    $hasOAuthId = true;
                }
                if ($column['name'] === 'avatar') {
                    $hasAvatar = true;
                }
            }
            
            // Якщо колонка nickname відсутня - додаємо її через пересоздание таблиці
            if (!$hasNickname) {
                $db->beginTransaction();
                
                // Створюємо тимчасову таблицю з новою структурою
                $db->exec("
                    CREATE TABLE users_new (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        email TEXT UNIQUE NOT NULL,
                        nickname TEXT UNIQUE NOT NULL,
                        password TEXT NOT NULL,
                        name TEXT NOT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        last_login DATETIME
                    )
                ");
                
                // Копіюємо дані зі старої таблиці, генеруючи nickname з email
                $db->exec("
                    INSERT INTO users_new (id, email, nickname, password, name, created_at, last_login)
                    SELECT 
                        id, 
                        email, 
                        CASE 
                            WHEN email LIKE '%@%' THEN SUBSTR(email, 1, INSTR(email, '@') - 1)
                            ELSE email
                        END as nickname,
                        password, 
                        name, 
                        created_at, 
                        last_login
                    FROM users
                ");
                
                // Видаляємо стару таблицю
                $db->exec("DROP TABLE users");
                
                // Перейменовуємо нову таблицю
                $db->exec("ALTER TABLE users_new RENAME TO users");
                
                $db->commit();
            }
            
            // Додаємо OAuth поля якщо їх немає
            if (!$hasOAuthProvider || !$hasOAuthId || !$hasAvatar) {
                self::addOAuthColumns();
            }
            
            // Додаємо поле role якщо його немає
            self::addRoleColumn();

            // Додаємо поле мови інтерфейсу якщо його немає
            self::addInterfaceLangColumn();

            // Створюємо таблицю order_settings та заповнюємо дефолтні ключі
            self::createOrderSettingsTable();
            self::seedOrderSettingsDefaults();

            // Створюємо таблицю blocked_ips та заповнюємо стартовими даними
            self::createBlockedIpsTable();
            self::seedDefaultBlockedIps();

            // Створюємо таблицю orders
            self::createOrdersTable();
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            // В разі помилки просто пропускаємо міграцію
            // Це не критично для роботи системи
        }
    }
    
    /**
     * Додати колонки для OAuth авторизації
     */
    private static function addOAuthColumns(): void
    {
        $db = self::getInstance();
        
        try {
            // Перевіряємо які поля вже існують
            $result = $db->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC);
            $columns = array_column($result, 'name');
            
            // Додаємо поля поетапно
            if (!in_array('oauth_provider', $columns)) {
                $db->exec("ALTER TABLE users ADD COLUMN oauth_provider TEXT DEFAULT NULL");
            }
            
            if (!in_array('oauth_id', $columns)) {
                $db->exec("ALTER TABLE users ADD COLUMN oauth_id TEXT DEFAULT NULL");
            }
            
            if (!in_array('avatar', $columns)) {
                $db->exec("ALTER TABLE users ADD COLUMN avatar TEXT DEFAULT NULL");
            }
            
            // Створюємо унікальний індекс для oauth_provider + oauth_id
            $db->exec("
                CREATE UNIQUE INDEX IF NOT EXISTS idx_oauth_provider_id 
                ON users(oauth_provider, oauth_id) 
                WHERE oauth_provider IS NOT NULL AND oauth_id IS NOT NULL
            ");
        } catch (\Exception $e) {
            // Ігноруємо помилки - поля можуть вже існувати
        }
    }
    
    /**
     * Додати колонку role для системи ролей
     */
    private static function addRoleColumn(): void
    {
        $db = self::getInstance();
        
        try {
            // Перевіряємо чи існує поле role
            $result = $db->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC);
            $columns = array_column($result, 'name');
            
            if (!in_array('role', $columns)) {
                // Додаємо поле role зі значенням за замовчуванням 'user'
                $db->exec("ALTER TABLE users ADD COLUMN role TEXT DEFAULT 'user' NOT NULL");
                
                // Оновлюємо першого користувача як адміна
                $db->exec("UPDATE users SET role = 'admin' WHERE id = 1");
            }
        } catch (\Exception $e) {
            // Ігноруємо помилки - поле може вже існувати
        }
    }

    /**
     * Додати колонку interface_lang для мови інтерфейсу
     */
    private static function addInterfaceLangColumn(): void
    {
        $db = self::getInstance();

        try {
            $result = $db->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC);
            $columns = array_column($result, 'name');

            if (!in_array('interface_lang', $columns, true)) {
                $db->exec("ALTER TABLE users ADD COLUMN interface_lang TEXT DEFAULT 'en' NOT NULL");
            }

            $db->exec("UPDATE users SET interface_lang = 'en' WHERE interface_lang IS NULL OR TRIM(interface_lang) = ''");
        } catch (\Exception $e) {
            // Ігноруємо помилки - поле може вже існувати
        }
    }

    /**
     * Створити таблицю налаштувань order.php
     */
    private static function createOrderSettingsTable(): void
    {
        $db = self::getInstance();

        $db->exec(" 
            CREATE TABLE IF NOT EXISTS order_settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                setting_key TEXT NOT NULL UNIQUE,
                setting_value TEXT DEFAULT '',
                setting_group TEXT DEFAULT 'general',
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $db->exec(" 
            CREATE INDEX IF NOT EXISTS idx_order_settings_group
            ON order_settings(setting_group)
        ");
    }

    /**
     * Заповнити order_settings дефолтними ключами
     */
    private static function seedOrderSettingsDefaults(): void
    {
        $db = self::getInstance();

        $defaults = [
            ['use_internal_orders_db', '1', 'internal_storage', 1],

            ['tg_token', '', 'telegram', 1],
            ['tg_chatid', '', 'telegram', 1],
            ['tg_chatforspamid', '', 'telegram', 1],

            ['crm_lp_token', '', 'lp_crm', 1],
            ['crm_lp_adress', 'http://_____________.lp-crm.biz', 'lp_crm', 1],
            ['crm_lp_office', '1', 'lp_crm', 1],

            ['crm_salesdrive_token', '', 'salesdrive', 1],
            ['crm_salesdrive_sources', 'https://_______.salesdrive.me/handler/', 'salesdrive', 1],

            ['crm_key_token', '', 'key_crm', 1],
            ['crm_key_sources', '', 'key_crm', 1],
            ['crm_key_voronka', '', 'key_crm', 1],

            ['api_token_keep_crm', '', 'keep_crm', 1],

            ['token_magnetstore', '', 'magnetstore', 1],
            ['tenant_magnetstore', '', 'magnetstore', 1],

            ['crm_ebash_token', '', 'ebash', 1],
            ['crm_ebash_adress', '', 'ebash', 1],
            ['crm_ebash_ofise', '1', 'ebash', 1],

            ['google_url', '', 'google_sheets', 1],
            ['notification_email', '', 'email', 1],
        ];

        $stmt = $db->prepare(" 
            INSERT OR IGNORE INTO order_settings (setting_key, setting_value, setting_group, is_active)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($defaults as $row) {
            $stmt->execute($row);
        }
    }

    /**
     * Створити таблицю заблокованих IP
     */
    private static function createBlockedIpsTable(): void
    {
        $db = self::getInstance();

        $db->exec(" 
            CREATE TABLE IF NOT EXISTS blocked_ips (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ip_address TEXT NOT NULL UNIQUE,
                reason TEXT DEFAULT '',
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $db->exec(" 
            CREATE INDEX IF NOT EXISTS idx_blocked_ips_active
            ON blocked_ips(is_active)
        ");
    }

    /**
     * Заповнити blocked_ips дефолтними IP
     */
    private static function seedDefaultBlockedIps(): void
    {
        $db = self::getInstance();

        $defaults = [
            ['134.249.248.30', 'Default blocked IP', 1],
            ['2a03:2260:10:22:19c2:7b5e:5809:a0cc', 'Default blocked IPv6', 1],
        ];

        $stmt = $db->prepare(" 
            INSERT OR IGNORE INTO blocked_ips (ip_address, reason, is_active)
            VALUES (?, ?, ?)
        ");

        foreach ($defaults as $row) {
            $stmt->execute($row);
        }
    }

    /**
     * Створити таблицю замовлень
     */
    private static function createOrdersTable(): void
    {
        $db = self::getInstance();

        $db->exec(" 
            CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_name TEXT DEFAULT '',
                phone TEXT DEFAULT '',
                product_id TEXT DEFAULT '',
                product_title TEXT DEFAULT '',
                product_price REAL DEFAULT 0,
                quantity INTEGER DEFAULT 1,
                total_sum REAL DEFAULT 0,
                comment TEXT DEFAULT '',
                payment TEXT DEFAULT '',
                delivery TEXT DEFAULT '',
                delivery_address TEXT DEFAULT '',
                additional_1 TEXT DEFAULT '',
                additional_2 TEXT DEFAULT '',
                additional_3 TEXT DEFAULT '',
                additional_4 TEXT DEFAULT '',
                type_form TEXT DEFAULT '',
                status TEXT DEFAULT 'new',
                is_spam INTEGER DEFAULT 0,
                client_ip TEXT DEFAULT '',
                utm_source TEXT DEFAULT '',
                utm_medium TEXT DEFAULT '',
                utm_term TEXT DEFAULT '',
                utm_content TEXT DEFAULT '',
                utm_campaign TEXT DEFAULT '',
                upsells_json TEXT DEFAULT '',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $db->exec(" 
            CREATE INDEX IF NOT EXISTS idx_orders_created_at
            ON orders(created_at DESC)
        ");

        $db->exec(" 
            CREATE INDEX IF NOT EXISTS idx_orders_status
            ON orders(status)
        ");
    }
    
    /**
     * Створити користувача за замовчуванням
     */
    public static function seedDefaultUser(): bool
    {
        $db = self::getInstance();
        
        // Перевірка чи є користувачі
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        
        if ($result['count'] == 0) {
            $stmt = $db->prepare("
                INSERT INTO users (email, nickname, password, name) 
                VALUES (:email, :nickname, :password, :name)
            ");
            
            return $stmt->execute([
                ':email' => 'admin@crm.com',
                ':nickname' => 'admin',
                ':password' => password_hash('admin123', PASSWORD_DEFAULT),
                ':name' => 'Адміністратор'
            ]);
        }
        
        return false;
    }
}

/**
 * Обгортка для PDO з автоматичним підрахунком запитів
 * Не наследует PDO напрямую, чтобы избежать проблем с PHP 8.x
 */
class DebugPDO
{
    private $pdo;
    private $debug;
    
    public function __construct(PDO $pdo, $debug)
    {
        $this->pdo = $pdo;
        $this->debug = $debug;
    }
    
    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        // Рахуємо SELECT запити
        if (stripos(trim($query), 'SELECT') === 0) {
            $this->debug->incrementQueryCount();
        }
        
        if ($fetchMode === null) {
            return $this->pdo->query($query);
        }
        return $this->pdo->query($query, $fetchMode, ...$fetchModeArgs);
    }
    
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        // Рахуємо SELECT запити при prepare
        if (stripos(trim($query), 'SELECT') === 0) {
            $this->debug->incrementQueryCount();
        }
        
        return $this->pdo->prepare($query, $options);
    }
    
    public function exec(string $query): int|false
    {
        return $this->pdo->exec($query);
    }
    
    // Проксимо всі інші методи PDO
    public function __call($method, $args)
    {
        return call_user_func_array([$this->pdo, $method], $args);
    }
    
    public function getAttribute(int $attribute): mixed
    {
        return $this->pdo->getAttribute($attribute);
    }
    
    public function setAttribute(int $attribute, mixed $value): bool
    {
        return $this->pdo->setAttribute($attribute, $value);
    }
    
    public function lastInsertId(?string $name = null): string|false
    {
        return $this->pdo->lastInsertId($name);
    }
    
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }
    
    public function commit(): bool
    {
        return $this->pdo->commit();
    }
    
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }
    
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }
}
