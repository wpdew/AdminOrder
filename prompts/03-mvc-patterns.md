# Промпт: MVC паттерны и layout система

## Концепция

MVC (Model-View-Controller) разделяет приложение на три слоя:
- **Model** - работа с данными
- **View** - отображение
- **Controller** - логика обработки запросов

## Структура файлов

```
admin/
├── app/
│   ├── Controllers/
│   │   ├── BaseController.php      # Базовый контроллер с render()
│   │   ├── DashboardController.php
│   │   ├── UserController.php
│   │   └── AuthController.php
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── main.php           # Layout с sidebar
│   │   │   └── auth.php           # Layout для login/register
│   │   └── pages/
│   │       ├── dashboard/
│   │       │   └── index.php
│   │       ├── users/
│   │       │   ├── index.php
│   │       │   └── profile.php
│   │       └── auth/
│   │           ├── login.php
│   │           └── register.php
│   └── Models/
│       ├── User.php
│       └── Settings.php
├── index.php                       # Точка входа -> DashboardController
└── users.php                       # Точка входа -> UserController
```

## BaseController

### Реализация
```php
<?php
namespace App\Controllers;

class BaseController
{
    /**
     * Рендер view с layout
     * 
     * @param string $view Путь к view (например 'users/index')
     * @param array $data Данные для передачи в view
     * @param string $layout Layout для использования ('main' или 'auth')
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        // Извлекаем переменные из массива
        extract($data);
        
        // Получаем контент view через буферизацию
        ob_start();
        require_once APP_PATH . "/Views/pages/{$view}.php";
        $content = ob_get_clean();
        
        // Подключаем layout
        require_once APP_PATH . "/Views/layouts/{$layout}.php";
    }
    
    /**
     * JSON ответ
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Редирект
     */
    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }
}
```

## Layout система

### Main Layout (для админки)
```php
<!-- app/Views/layouts/main.php -->
<?php
use App\Core\Auth;
$currentUser = Auth::user();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard' ?> - AdminOrder</title>
    <link rel="stylesheet" href="/admin/css/styles.css">
</head>
<body>
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
    
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="logo-icon">📊</div>
                <span>AdminOrder</span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="/admin/index.php" class="nav-item <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
                <span class="icon">🏠</span>
                <span>Dashboard</span>
            </a>
            <a href="/admin/users.php" class="nav-item <?= ($activeMenu ?? '') === 'users' ? 'active' : '' ?>">
                <span class="icon">👥</span>
                <span>Users</span>
            </a>
            <a href="/admin/profile.php" class="nav-item <?= ($activeMenu ?? '') === 'profile' ? 'active' : '' ?>">
                <span class="icon">⚙️</span>
                <span>Profile</span>
            </a>
            <a href="/admin/settings.php" class="nav-item <?= ($activeMenu ?? '') === 'settings' ? 'active' : '' ?>">
                <span class="icon">🔌</span>
                <span>Settings</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <button class="theme-toggle" id="themeToggle">🌙</button>
            <div class="user-profile">
                <div class="user-avatar"><?= strtoupper(substr($currentUser['name'], 0, 1)) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($currentUser['name']) ?></div>
                    <div class="user-email"><?= htmlspecialchars($currentUser['email']) ?></div>
                </div>
            </div>
        </div>
    </aside>
    
    <!-- Main Content -->
    <div class="container">
        <?= $content ?>
    </div>
    
    <script src="/admin/js/script.js"></script>
</body>
</html>
```

### Auth Layout (для login/register)
```php
<!-- app/Views/layouts/auth.php -->
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Auth' ?> - AdminOrder</title>
    <link rel="stylesheet" href="/admin/css/styles.css">
</head>
<body>
    <button class="theme-toggle" id="themeToggle">🌙</button>
    
    <div class="login-container">
        <?= $content ?>
    </div>
    
    <script src="/admin/js/script.js"></script>
</body>
</html>
```

## Контроллеры (примеры)

### DashboardController
```php
<?php
namespace App\Controllers;

use App\Models\User;

class DashboardController extends BaseController
{
    public function index()
    {
        // Получаем данные
        $totalUsers = User::count();
        $recentUsers = User::recent(5);
        
        // Рендерим view
        $this->render('dashboard/index', [
            'title' => 'Dashboard',
            'activeMenu' => 'dashboard',
            'totalUsers' => $totalUsers,
            'recentUsers' => $recentUsers
        ]);
    }
}
```

### UserController
```php
<?php
namespace App\Controllers;

use App\Models\User;
use App\Core\Auth;

class UserController extends BaseController
{
    /**
     * Список пользователей
     */
    public function index()
    {
        $users = User::all();
        
        $this->render('users/index', [
            'title' => 'Пользователи',
            'activeMenu' => 'users',
            'users' => $users
        ]);
    }
    
    /**
     * Профиль текущего пользователя
     */
    public function profile()
    {
        $userId = Auth::id();
        $user = User::findById($userId);
        
        $this->render('users/profile', [
            'title' => 'Профиль',
            'activeMenu' => 'profile',
            'user' => $user
        ]);
    }
    
    /**
     * Обновление профиля
     */
    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/profile.php');
        }
        
        $userId = Auth::id();
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
        ];
        
        // Валидация
        if (empty($data['name']) || empty($data['email'])) {
            $_SESSION['error'] = 'Заполните все поля';
            $this->redirect('/admin/profile.php');
        }
        
        // Обновление
        $success = User::updateProfile($userId, $data);
        
        if ($success) {
            $_SESSION['success'] = 'Профиль обновлен';
            $_SESSION['user_name'] = $data['name'];
            $_SESSION['user_email'] = $data['email'];
        } else {
            $_SESSION['error'] = 'Ошибка обновления';
        }
        
        $this->redirect('/admin/profile.php');
    }
    
    /**
     * API: получить пользователя по ID
     */
    public function getUser(int $id)
    {
        $user = User::findById($id);
        
        if (!$user) {
            $this->json(['error' => 'Пользователь не найден'], 404);
        }
        
        // Убираем пароль из ответа
        unset($user['password']);
        
        $this->json(['user' => $user]);
    }
}
```

### AuthController
```php
<?php
namespace App\Controllers;

use App\Core\Auth;

class AuthController extends BaseController
{
    /**
     * Страница входа
     */
    public function showLogin()
    {
        // Если уже залогинен, редирект
        if (Auth::check()) {
            $this->redirect('/admin/index.php');
        }
        
        $this->render('auth/login', [
            'title' => 'Вход',
            'error' => $_SESSION['error'] ?? null
        ], 'auth');
        
        unset($_SESSION['error']);
    }
    
    /**
     * Обработка входа
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/login.php');
        }
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (Auth::login($email, $password)) {
            $this->redirect('/admin/index.php');
        } else {
            $_SESSION['error'] = 'Неверный email или пароль';
            $this->redirect('/admin/login.php');
        }
    }
    
    /**
     * Выход
     */
    public function logout()
    {
        Auth::logout();
        $this->redirect('/admin/login.php');
    }
}
```

## Views (примеры)

### Dashboard View
```php
<!-- app/Views/pages/dashboard/index.php -->
<header>
    <div class="header-content">
        <div>
            <h1 class="section-title">Dashboard</h1>
            <p class="section-subtitle">Security posture overview</p>
        </div>
    </div>
</header>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-icon">👥</span>
            <span class="stat-label">Total Users</span>
        </div>
        <div class="stat-value"><?= $totalUsers ?></div>
    </div>
    
    <!-- Другие карточки -->
</div>

<div class="card">
    <h2>Recent Users</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Joined</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentUsers as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
```

### Users View
```php
<!-- app/Views/pages/users/index.php -->
<header>
    <div class="header-content">
        <h1 class="section-title"><?= $title ?></h1>
        <button class="btn btn-primary" onclick="showAddUserModal()">
            + Add User
        </button>
    </div>
</header>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Nickname</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['nickname']) ?></td>
                <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                <td>
                    <button class="btn-icon" onclick="editUser(<?= $user['id'] ?>)">✏️</button>
                    <button class="btn-icon" onclick="deleteUser(<?= $user['id'] ?>)">🗑️</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
```

### Login View
```php
<!-- app/Views/pages/auth/login.php -->
<div class="login-card fade-in">
    <div class="login-header">
        <h1>Добро пожаловать</h1>
        <p>Войдите в свой аккаунт для продолжения</p>
    </div>
    
    <?php if (!empty($error)): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="login.php" class="login-form">
        <div class="form-group">
            <label for="email">Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                placeholder="name@company.com"
                required
            >
        </div>
        
        <div class="form-group">
            <label for="password">Пароль</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required
            >
        </div>
        
        <button type="submit" class="btn-primary">
            <span>Войти</span>
            <span>→</span>
        </button>
    </form>
    
    <div class="signup-link">
        Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
    </div>
</div>
```

## Точки входа

### index.php
```php
<?php
require_once 'app/bootstrap.php';

use App\Core\Auth;
use App\Controllers\DashboardController;

Auth::requireAuth();

$controller = new DashboardController();
$controller->index();
```

### users.php
```php
<?php
require_once 'app/bootstrap.php';

use App\Core\Auth;
use App\Controllers\UserController;

Auth::requireAuth();

$controller = new UserController();
$controller->index();
```

### login.php
```php
<?php
require_once 'app/bootstrap.php';

use App\Controllers\AuthController;

$controller = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->login();
} else {
    $controller->showLogin();
}
```

## Передача данных в View

### Через массив $data
```php
$this->render('users/index', [
    'users' => $users,
    'title' => 'Пользователи',
    'totalUsers' => count($users),
    'activeMenu' => 'users'
]);
```

### Доступ в view через переменные
```php
<!-- В view автоматически доступны: -->
<?= $title ?>
<?= $totalUsers ?>
<?php foreach ($users as $user): ?>
    <?= $user['name'] ?>
<?php endforeach; ?>
```

## Flash сообщения

### Установка в контроллере
```php
$_SESSION['success'] = 'Пользователь создан';
$_SESSION['error'] = 'Ошибка валидации';
$this->redirect('/admin/users.php');
```

### Отображение в view
```php
<?php if (!empty($_SESSION['success'])): ?>
<div class="alert alert-success">
    <?= htmlspecialchars($_SESSION['success']) ?>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
<div class="alert alert-error">
    <?= htmlspecialchars($_SESSION['error']) ?>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>
```

## Лучшие практики

1. **Всегда используй `htmlspecialchars()`** при выводе данных пользователя
2. **Извлекай данные в контроллере**, не в view
3. **View должен содержать только HTML + минимум PHP** (loops, conditions)
4. **Бизнес-логику держи в контроллерах и моделях**
5. **Используй layout для избежания дублирования кода**
6. **Flash сообщения для информирования пользователя**
7. **`activeMenu` переменная для подсветки активного пункта меню**
