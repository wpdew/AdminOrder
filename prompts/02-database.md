# Промпт: Работа с базой данных

## Основная информация

**СУБД**: SQLite 3  
**Путь к базе**: `database/crm.db`  
**Подключение**: Singleton через `App\Config\Database`

## Использование

### Получение подключения
```php
use App\Config\Database;

$db = Database::getInstance(); // Всегда возвращает один экземпляр PDO
```

### Выполнение запросов

#### SELECT (безопасно)
```php
// Один результат
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(); // Возвращает массив или false

// Множественные результаты
$stmt = $db->prepare("SELECT * FROM users WHERE role = :role");
$stmt->execute([':role' => 'admin']);
$users = $stmt->fetchAll(); // Возвращает массив массивов
```

#### INSERT
```php
$stmt = $db->prepare("
    INSERT INTO users (email, password, name) 
    VALUES (:email, :password, :name)
");

$success = $stmt->execute([
    ':email' => $email,
    ':password' => password_hash($password, PASSWORD_DEFAULT),
    ':name' => $name
]);

$lastId = $db->lastInsertId(); // ID нового пользователя
```

#### UPDATE
```php
$stmt = $db->prepare("
    UPDATE users 
    SET name = :name, email = :email 
    WHERE id = :id
");

$stmt->execute([
    ':name' => $name,
    ':email' => $email,
    ':id' => $userId
]);

$affectedRows = $stmt->rowCount();
```

#### DELETE
```php
$stmt = $db->prepare("DELETE FROM users WHERE id = :id");
$stmt->execute([':id' => $userId]);
```

## Модели (Рекомендуемый подход)

### Структура модели
```php
<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Product
{
    /**
     * Получить все продукты
     */
    public static function all(): array
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM products ORDER BY id DESC");
        return $stmt->fetchAll();
    }
    
    /**
     * Найти продукт по ID
     */
    public static function findById(int $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        $product = $stmt->fetch();
        return $product ?: null;
    }
    
    /**
     * Создать новый продукт
     */
    public static function create(array $data): bool
    {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            INSERT INTO products (name, price, description) 
            VALUES (:name, :price, :description)
        ");
        
        return $stmt->execute([
            ':name' => $data['name'],
            ':price' => $data['price'],
            ':description' => $data['description'] ?? null
        ]);
    }
    
    /**
     * Обновить продукт
     */
    public static function update(int $id, array $data): bool
    {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            UPDATE products 
            SET name = :name, price = :price, description = :description 
            WHERE id = :id
        ");
        
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':price' => $data['price'],
            ':description' => $data['description'] ?? null
        ]);
    }
    
    /**
     * Удалить продукт
     */
    public static function delete(int $id): bool
    {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Поиск по названию
     */
    public static function search(string $query): array
    {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            SELECT * FROM products 
            WHERE name LIKE :query 
            ORDER BY name
        ");
        
        $stmt->execute([':query' => '%' . $query . '%']);
        return $stmt->fetchAll();
    }
    
    /**
     * Получить количество продуктов
     */
    public static function count(): int
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT COUNT(*) as total FROM products");
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
}
```

## Миграции

### Где находятся
В методе `Database::runMigrations()` в файле `app/Config/Database.php`

### Создание новой таблицы
```php
// Добавить в runMigrations():

// Таблица products
if (!in_array('products', $tables)) {
    $this->executeSql("
        CREATE TABLE products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            description TEXT,
            image_url TEXT,
            stock INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Таблиця products створена\n";
}
```

### Добавление столбца в существующую таблицу
```php
// Проверка наличия столбца
$columnsResult = $this->pdo->query("PRAGMA table_info(users)");
$columns = $columnsResult->fetchAll(PDO::FETCH_COLUMN, 1);

if (!in_array('phone', $columns)) {
    $this->executeSql("ALTER TABLE users ADD COLUMN phone TEXT");
    echo "✓ Колонка phone додана до таблиці users\n";
}
```

### Создание индексов
```php
// Индекс для ускорения поиска
$this->executeSql("CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)");
$this->executeSql("CREATE INDEX IF NOT EXISTS idx_products_name ON products(name)");
```

## Примеры типовых запросов

### Связанные данные (JOIN)
```php
// Получить заказы с информацией о пользователях
$stmt = $db->query("
    SELECT 
        orders.id,
        orders.total,
        orders.created_at,
        users.name as user_name,
        users.email as user_email
    FROM orders
    LEFT JOIN users ON orders.user_id = users.id
    ORDER BY orders.created_at DESC
");

$orders = $stmt->fetchAll();
```

### Группировка и агрегация
```php
// Статистика заказов по пользователям
$stmt = $db->query("
    SELECT 
        users.name,
        COUNT(orders.id) as total_orders,
        SUM(orders.total) as total_spent
    FROM users
    LEFT JOIN orders ON users.id = orders.user_id
    GROUP BY users.id
    HAVING total_orders > 0
    ORDER BY total_spent DESC
");

$stats = $stmt->fetchAll();
```

### Pagination
```php
// Страница 1, по 20 элементов
$page = 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$stmt = $db->prepare("
    SELECT * FROM products 
    ORDER BY created_at DESC 
    LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$products = $stmt->fetchAll();
```

### Транзакции
```php
try {
    $db->beginTransaction();
    
    // Создаем заказ
    $stmt = $db->prepare("INSERT INTO orders (user_id, total) VALUES (:user_id, :total)");
    $stmt->execute([':user_id' => $userId, ':total' => $total]);
    $orderId = $db->lastInsertId();
    
    // Добавляем товары
    $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (:order_id, :product_id, :quantity)");
    foreach ($items as $item) {
        $stmt->execute([
            ':order_id' => $orderId,
            ':product_id' => $item['product_id'],
            ':quantity' => $item['quantity']
        ]);
    }
    
    $db->commit();
    return $orderId;
    
} catch (Exception $e) {
    $db->rollBack();
    throw $e;
}
```

## SQLite команды (CLI)

### Открыть базу
```bash
cd admin/database
sqlite3 crm.db
```

### Полезные команды
```sql
-- Показать все таблицы
.tables

-- Показать структуру таблицы
.schema users

-- Показать все данные с заголовками
.headers on
.mode column
SELECT * FROM users;

-- Экспорт в CSV
.mode csv
.output users.csv
SELECT * FROM users;
.output stdout

-- Выход
.quit
```

## Правила безопасности

### ✅ Безопасно (Prepared Statements)
```php
$stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute([':email' => $email]);
```

### ❌ НЕ безопасно (SQL Injection)
```php
$db->query("SELECT * FROM users WHERE email = '$email'");
```

### Экранирование данных при выводе
```php
// В PHP шаблонах ВСЕГДА:
<?= htmlspecialchars($user['name']) ?>

// Или короткий alias (если определен):
<?= e($user['name']) ?>
```

## Типовые ошибки

### Ошибка: "database is locked"
**Причина**: SQLite не поддерживает множественные одновременные записи

**Решение**:
```php
// Включить WAL режим (Write-Ahead Logging)
$db->exec("PRAGMA journal_mode=WAL");
```

### Ошибка: "no such table"
**Причина**: Таблица не создана

**Решение**: Проверить выполнение миграций в `bootstrap.php`

### Ошибка: "UNIQUE constraint failed"
**Причина**: Попытка вставить дубликат в уникальное поле

**Решение**: Проверять существование перед вставкой
```php
$existing = User::findByEmail($email);
if ($existing) {
    return ['error' => 'Email уже используется'];
}
```

## Performance оптимизации

### Индексы
```sql
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_products_name ON products(name);
CREATE INDEX idx_orders_user_id ON orders(user_id);
```

### EXPLAIN для анализа запросов
```sql
EXPLAIN QUERY PLAN 
SELECT * FROM users WHERE email = 'test@test.com';
```

### Vacuum (очистка и оптимизация)
```sql
VACUUM;
```

## Backup базы данных

```bash
# Создать резервную копию
sqlite3 crm.db ".backup crm_backup.db"

# Или через командную строку
cp crm.db crm_backup_$(date +%Y%m%d).db
```

## Restore базы
```bash
# Восстановить из бэкапа
cp crm_backup.db crm.db
```
