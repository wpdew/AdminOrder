# Промпт: Архитектура и структура проекта

## Контекст
AdminOrder Dashboard - MVC приложение на чистом PHP 8.x с SQLite базой данных.

## Архитектурные принципы

### 1. MVC Pattern
```
Model (app/Models/)     -> Работа с данными
View (app/Views/)       -> HTML шаблоны
Controller (app/Controllers/) -> Бизнес-логика
```

### 2. Namespace структура
```php
App\Models\User          -> app/Models/User.php
App\Controllers\Auth     -> app/Controllers/AuthController.php
App\Core\Auth           -> app/Core/Auth.php
```

### 3. Layout система
- `app/Views/layouts/main.php` - с sidebar и навигацией
- `app/Views/layouts/auth.php` - минималистичный для login/register
- `app/Views/pages/` - конкретные страницы

### 4. Точки входа
```
admin/index.php    -> Инициализирует контроллер -> render() -> layout + view
admin/users.php    -> Аналогично
admin/login.php    -> AuthController -> auth layout
```

## Ключевые файлы

### bootstrap.php
**Всегда первым подключается**
```php
require_once 'app/bootstrap.php';
```

Что делает:
- Autoload классов через SPL
- Запуск миграций БД
- Инициализация сессий
- Lang система

### BaseController
```php
namespace App\Controllers;

class BaseController
{
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        // Буферизация view
        // Подключение layout
        // Вывод HTML
    }
}
```

### Database Singleton
```php
$db = Database::getInstance(); // Всегда одно подключение
```

## Правила разработки

### ✅ Делай так:
```php
// 1. Prepared statements
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $id]);

// 2. Экранирование вывода
<?= htmlspecialchars($user['name']) ?>

// 3. Авторизация на защищенных страницах
Auth::requireAuth();

// 4. Использование render() в контроллерах
$this->render('users/index', ['users' => $users]);
```

### ❌ Не делай так:
```php
// ❌ SQL injection
$db->query("SELECT * FROM users WHERE id = $id");

// ❌ XSS уязвимость
<?= $user['name'] ?>

// ❌ Прямой echo HTML в контроллере
echo '<div>Users</div>';

// ❌ Дублирование кода авторизации
if (!isset($_SESSION['user_id'])) { ... }
```

## Добавление нового функционала

### Новая страница (пример: Products)

**1. Модель** (`app/Models/Product.php`):
```php
<?php
namespace App\Models;
use App\Config\Database;

class Product
{
    public static function all(): array
    {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM products ORDER BY id")->fetchAll();
    }
}
```

**2. Контроллер** (`app/Controllers/ProductController.php`):
```php
<?php
namespace App\Controllers;
use App\Models\Product;

class ProductController extends BaseController
{
    public function index()
    {
        $products = Product::all();
        
        $this->render('products/index', [
            'products' => $products,
            'title' => 'Продукты',
            'activeMenu' => 'products'
        ]);
    }
}
```

**3. View** (`app/Views/pages/products/index.php`):
```php
<div class="page-header">
    <h1><?= $title ?></h1>
</div>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Цена</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?= $product['id'] ?></td>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td><?= $product['price'] ?> грн</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
```

**4. Точка входа** (`admin/products.php`):
```php
<?php
require_once 'app/bootstrap.php';

Auth::requireAuth();

$controller = new \App\Controllers\ProductController();
$controller->index();
```

**5. Добавить в навигацию** (`app/Views/layouts/main.php`):
```html
<a href="products.php" class="nav-item <?= $activeMenu === 'products' ? 'active' : '' ?>">
    <span class="icon">📦</span>
    <span>Продукты</span>
</a>
```

## База данных

### Создание таблицы (миграция)
Добавить в `Database::runMigrations()`:

```php
// Таблица products
if (!in_array('products', $tables)) {
    $this->executeSql("
        CREATE TABLE products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
}
```

## Frontend интеграция

### AJAX запрос
```javascript
async function saveProduct(data) {
    const response = await fetch('api/products.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    });
    
    const result = await response.json();
    return result;
}
```

### API endpoint
```php
// api/products.php
<?php
require_once '../app/bootstrap.php';

header('Content-Type: application/json');
Auth::requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $success = Product::create($data);
    
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Продукт создан' : 'Ошибка'
    ]);
}
```

## Debugging

### Development режим
```php
// bootstrap.php
putenv('APP_ENV=development');

// Показывает все ошибки
// Debug headers в ответах
```

### PhpDebugHeaders
```php
$debug = $GLOBALS['debug'];
$debug->log('User data', $user);
$debug->measure('Query execution');
```

## Безопасность чеклист

- [ ] `Auth::requireAuth()` на каждой странице админки
- [ ] `htmlspecialchars()` для всех выводов переменных
- [ ] Prepared statements для всех SQL запросов
- [ ] `password_hash()` и `password_verify()` для паролей
- [ ] HTTPS в production (TODO: добавить в конфиг)
- [ ] CSRF токены для форм (TODO: реализовать)

## Полезные ссылки

- **AGENTS.md** - полная документация проекта
- **app/README.md** - документация приложения
- **prompts/** - коллекция промптов для разных задач
