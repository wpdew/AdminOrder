# Промпт: Авторизация и безопасность

## Система авторизации

### Класс Auth (App\Core\Auth)

**Основные методы:**

```php
use App\Core\Auth;

// Проверка авторизации
if (Auth::check()) {
    // Пользователь залогинен
}

// Получить данные текущего пользователя
$user = Auth::user(); // ['id' => 1, 'email' => '...', 'name' => '...']

// Получить ID текущего пользователя
$userId = Auth::id();

// Вход
$success = Auth::login($email, $password);

// Выход
Auth::logout();

// Middleware для защищенных страниц
Auth::requireAuth(); // Редирект на login.php если не авторизован
```

## Защита страниц админки

### Стандартный паттерн
```php
<?php
require_once 'app/bootstrap.php';

use App\Core\Auth;

// Обязательная авторизация
Auth::requireAuth();

// Далее код страницы...
```

### Пример для index.php
```php
<?php
require_once 'app/bootstrap.php';

use App\Core\Auth;
use App\Controllers\DashboardController;

Auth::requireAuth(); // Если не авторизован - редирект на login.php

$controller = new DashboardController();
$controller->index();
```

## Процесс авторизации

### 1. Страница входа (login.php)

```php
<?php
require_once 'app/bootstrap.php';

use App\Controllers\AuthController;

$controller = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Обработка формы
    $controller->login();
} else {
    // Показать форму
    $controller->showLogin();
}
```

### 2. AuthController::login()

```php
public function login()
{
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Валидация
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Заполните все поля';
        $this->redirect('/admin/login.php');
    }
    
    // Попытка входа
    if (Auth::login($email, $password)) {
        // Успех - редирект на главную
        $this->redirect('/admin/index.php');
    } else {
        // Ошибка - назад на форму
        $_SESSION['error'] = 'Неверный email или пароль';
        $this->redirect('/admin/login.php');
    }
}
```

### 3. Auth::login() внутри

```php
public static function login(string $login, string $password): bool
{
    // Поиск пользователя по email или nickname
    $user = User::findByEmailOrNickname($login);
    
    // Проверка пароля
    if ($user && password_verify($password, $user['password'])) {
        // Сохраняем в сессию
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        
        // Обновляем last_login
        User::updateLastLogin($user['id']);
        
        return true;
    }
    
    return false;
}
```

## Регистрация

### 1. Страница регистрации (register.php)

```php
<?php
require_once 'app/bootstrap.php';

use App\Controllers\AuthController;

$controller = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->register();
} else {
    $controller->showRegister();
}
```

### 2. AuthController::register()

```php
public function register()
{
    $email = $_POST['email'] ?? '';
    $nickname = $_POST['nickname'] ?? '';
    $password = $_POST['password'] ?? '';
    $name = $_POST['name'] ?? '';
    
    // Валидация
    if (empty($email) || empty($password) || empty($nickname)) {
        $_SESSION['error'] = 'Заполните все обязательные поля';
        $this->redirect('/admin/register.php');
    }
    
    // Проверка email формата
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Неверный формат email';
        $this->redirect('/admin/register.php');
    }
    
    // Проверка длины пароля
    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Пароль должен быть минимум 6 символов';
        $this->redirect('/admin/register.php');
    }
    
    // Создание пользователя
    if (User::create($email, $nickname, $password, $name)) {
        $_SESSION['success'] = 'Регистрация успешна! Войдите в систему';
        $this->redirect('/admin/login.php');
    } else {
        $_SESSION['error'] = 'Email или nickname уже используется';
        $this->redirect('/admin/register.php');
    }
}
```

## Безопасность паролей

### Хеширование при создании
```php
// В User::create()
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare("
    INSERT INTO users (email, password, ...) 
    VALUES (:email, :password, ...)
");

$stmt->execute([
    ':password' => $hashedPassword,
    // ...
]);
```

### Проверка при входе
```php
// В Auth::login()
if (password_verify($inputPassword, $user['password'])) {
    // Пароль верный
}
```

### Требования к паролю
```php
// Минимальная длина
if (strlen($password) < 8) {
    return 'Минимум 8 символов';
}

// Наличие цифр и букв
if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    return 'Пароль должен содержать буквы и цифры';
}

// Сложный пароль
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
    return 'Пароль должен содержать: заглавные, строчные буквы, цифры и спецсимволы';
}
```

## XSS защита

### Экранирование всех выводов
```php
<!-- ВСЕГДА в шаблонах: -->
<?= htmlspecialchars($user['name']) ?>
<?= htmlspecialchars($product['description']) ?>

<!-- Или короткий alias (создать в bootstrap.php): -->
<?php
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>

<!-- Использование: -->
<?= e($user['name']) ?>
```

### Очистка HTML (если нужно)
```php
// Удалить все теги
$clean = strip_tags($dirtyHtml);

// Разрешить только безопасные теги
$clean = strip_tags($dirtyHtml, '<p><br><strong><em>');

// Или использовать HTMLPurifier (установить через Composer)
```

## SQL Injection защита

### ✅ Правильно (Prepared Statements)
```php
$stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute([':email' => $email]);
```

### ❌ НЕПРАВИЛЬНО (уязвимо к SQL Injection)
```php
$query = "SELECT * FROM users WHERE email = '$email'";
$db->query($query);
```

### Примеры атак (что блокируется prepared statements)
```php
// Попытка SQL injection
$email = "admin@test.com' OR '1'='1";

// С prepared statements это безопасно - ищет точное совпадение строки
$stmt->execute([':email' => $email]); 
// WHERE email = "admin@test.com' OR '1'='1" - не найдет

// Без prepared statements (ОПАСНО)
// WHERE email = 'admin@test.com' OR '1'='1' - вернет всех пользователей!
```

## CSRF защита (TODO)

### Генерация токена
```php
// В bootstrap.php или Auth::init()
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
```

### Добавление в форму
```php
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    
    <!-- Остальные поля -->
    <button type="submit">Submit</button>
</form>
```

### Проверка в контроллере
```php
public function update()
{
    // Проверка CSRF токена
    $token = $_POST['csrf_token'] ?? '';
    
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        die('CSRF token mismatch');
    }
    
    // Обработка формы...
}
```

### Helper функция
```php
// В bootstrap.php
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function verify_csrf() {
    $token = $_POST['csrf_token'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}
```

## Session безопасность

### Настройки в bootstrap.php
```php
// Защита от session fixation
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1); // Только для HTTPS
    ini_set('session.use_strict_mode', 1);
    
    session_start();
    
    // Регенерация ID при входе
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}
```

### Тайм-аут сессии
```php
// В Auth::check() добавить:
public static function check(): bool
{
    self::init();
    
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Проверка тайм-аута (например, 2 часа)
    if (isset($_SESSION['last_activity'])) {
        $timeout = 2 * 60 * 60; // 2 часа в секундах
        
        if (time() - $_SESSION['last_activity'] > $timeout) {
            self::logout();
            return false;
        }
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}
```

## Валидация данных

### Email
```php
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception('Invalid email format');
}
```

### Длина строки
```php
if (strlen($name) < 2 || strlen($name) > 100) {
    throw new Exception('Name must be 2-100 characters');
}
```

### Числа
```php
$id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
if ($id === false) {
    throw new Exception('Invalid ID');
}
```

### URL
```php
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    throw new Exception('Invalid URL');
}
```

### Кастомная валидация
```php
class Validator
{
    public static function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = "Field $field is required";
            }
            
            if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "Field $field must be valid email";
            }
            
            if (preg_match('/min:(\d+)/', $rule, $matches)) {
                $min = (int)$matches[1];
                if (strlen($value) < $min) {
                    $errors[$field] = "Field $field must be at least $min characters";
                }
            }
        }
        
        return $errors;
    }
}

// Использование:
$errors = Validator::validate($_POST, [
    'email' => 'required|email',
    'password' => 'required|min:8',
    'name' => 'required|min:2'
]);

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    redirect('/form');
}
```

## Rate Limiting (защита от брутфорса)

### Простая реализация
```php
class RateLimiter
{
    public static function check(string $key, int $maxAttempts = 5, int $decayMinutes = 15): bool
    {
        $attempts = $_SESSION['rate_limit'][$key]['attempts'] ?? 0;
        $timestamp = $_SESSION['rate_limit'][$key]['timestamp'] ?? time();
        
        // Сброс если прошло время
        if (time() - $timestamp > $decayMinutes * 60) {
            $_SESSION['rate_limit'][$key] = [
                'attempts' => 1,
                'timestamp' => time()
            ];
            return true;
        }
        
        // Проверка лимита
        if ($attempts >= $maxAttempts) {
            return false;
        }
        
        // Увеличение счетчика
        $_SESSION['rate_limit'][$key]['attempts'] = $attempts + 1;
        return true;
    }
}

// Использование в AuthController::login()
$ip = $_SERVER['REMOTE_ADDR'];
if (!RateLimiter::check("login:$ip", 5, 15)) {
    $_SESSION['error'] = 'Слишком много попыток. Попробуйте через 15 минут';
    $this->redirect('/admin/login.php');
}
```

## Логирование безопасности

### Создать таблицу логов
```sql
CREATE TABLE security_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT NOT NULL,
    ip_address TEXT,
    user_agent TEXT,
    details TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### Logger класс
```php
class SecurityLogger
{
    public static function log(string $action, ?int $userId = null, array $details = []): void
    {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            INSERT INTO security_logs (user_id, action, ip_address, user_agent, details)
            VALUES (:user_id, :action, :ip, :user_agent, :details)
        ");
        
        $stmt->execute([
            ':user_id' => $userId,
            ':action' => $action,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ':details' => json_encode($details)
        ]);
    }
}

// Использование:
SecurityLogger::log('login_success', $userId);
SecurityLogger::log('login_failed', null, ['email' => $email]);
SecurityLogger::log('password_changed', $userId);
```

## Checklist безопасности

### Обязательно:
- [ ] `Auth::requireAuth()` на всех защищенных страницах
- [ ] `htmlspecialchars()` для всех выводов
- [ ] Prepared statements для всех SQL запросов
- [ ] `password_hash()` и `password_verify()` для паролей
- [ ] Валидация всех входных данных

### Рекомендуется:
- [ ] CSRF токены для форм
- [ ] Rate limiting для login
- [ ] HTTPS в production
- [ ] Session timeout
- [ ] Логирование критичных действий
- [ ] Двухфакторная аутентификация (2FA)

### Production окружение:
- [ ] `display_errors = Off` в php.ini
- [ ] `APP_ENV=production`
- [ ] Регулярный backup базы данных
- [ ] Мониторинг логов безопасности
- [ ] Обновление зависимостей
