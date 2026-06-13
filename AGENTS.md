# AdminOrder Dashboard - AI Agent Memory

> Ключевые моменты проекта для AI ассистентов
> Дата создания: 16 мая 2026

---

## 📋 О проекте

**AdminOrder Dashboard** - административная панель для управления данными с архитектурой MVC на чистом PHP.

### Технологический стек
- **Backend**: PHP 8.x (чистый, без фреймворков)
- **База данных**: SQLite (`database/crm.db`)
- **Frontend**: Vanilla JavaScript, CSS
- **Архитектура**: MVC (Model-View-Controller)
- **Авторизация**: Session-based через `App\Core\Auth`
- **OAuth**: Google и GitHub (через `league/oauth2-client`)
- **Зависимости**: Composer для OAuth библиотек

---

## 🏗 Архитектура проекта

### Структура папок
```
admin/
├── app/                         # Ядро приложения
│   ├── bootstrap.php           # Инициализация, autoload, миграции
│   ├── PhpDebugHeaders.php     # Debug headers для разработки
│   ├── Config/
│   │   ├── Database.php        # Singleton PDO для SQLite
│   │   └── OAuth.php           # Конфигурация OAuth провайдеров
│   ├── Controllers/
│   │   ├── BaseController.php  # Базовый контроллер с render()
│   │   ├── AuthController.php  # Авторизация
│   │   ├── OAuthController.php # OAuth авторизация
│   │   └── UserController.php  # Управление пользователями
│   ├── Core/
│   │   ├── Auth.php           # Система авторизации
│   │   └── Lang.php           # Мультиязычность (ru, uk, en)
│   ├── Models/
│   │   └── User.php           # Модель пользователя
│   └── Views/                 # Шаблоны (MVC)
│       ├── layouts/
│       │   ├── main.php       # Главный layout (с sidebar)
│       │   └── auth.php       # Layout для login/register
│       └── pages/             # Вьюхи страниц
│           ├── dashboard/
│           ├── users/
│           └── auth/
├── oauth/                      # OAuth entry points
│   ├── google/
│   │   ├── index.php          # Redirect на Google
│   │   └── callback.php       # Callback от Google
│   └── github/
│       ├── index.php          # Redirect на GitHub
│       └── callback.php       # Callback от GitHub
├── css/
│   └── styles.css            # Единый файл стилей
├── js/
│   └── script.js             # Единый файл JavaScript
├── database/
│   └── crm.db               # SQLite база
├── vendor/                   # Composer зависимости
├── .env                      # OAuth credentials (не коммитить!)
├── .env.example              # Пример конфигурации
├── composer.json             # PHP зависимости
├── OAUTH-SETUP.md           # Документация OAuth
└── index.php                # Точка входа
```

### Ключевые концепции

#### 1. **Autoload через SPL**
```php
// bootstrap.php
spl_autoload_register(function ($class) {
    // App\Core\Auth -> app/Core/Auth.php
    $prefix = 'App\\';
    $baseDir = APP_PATH . '/';
    // ... автозагрузка по namespace
});
```

#### 2. **Singleton для базы данных**
```php
$db = Database::getInstance(); // Единое подключение
```

#### 3. **MVC Pattern**
- **Model**: Работа с данными (User, Settings, etc.)
- **View**: HTML шаблоны в `app/Views/`
- **Controller**: Логика обработки запросов

#### 4. **Layout система**
```php
// BaseController::render()
$this->render('users/index', [
    'users' => $users,
    'title' => 'Пользователи'
], 'main'); // layout: main или auth
```

#### 5. **Авторизация**
```php
// Проверка авторизации на каждой странице
Auth::requireAuth(); // redirect на login.php если не залогинен

// Получить данные пользователя
$user = Auth::user(); // ['id', 'email', 'name']
```

---

## 🔑 Ключевые файлы

### app/bootstrap.php
**Назначение**: Инициализация всего приложения
- Устанавливает `APP_ENV` (development/production)
- Подключает autoloader для классов
- Запускает миграции БД
- Инициализирует сессии и Lang
- Определяет helper-функции `__()` и `t()` для переводов

**Важно**: Подключается в начале каждого PHP файла!

### app/Config/Database.php
**Назначение**: Управление подключением к SQLite
- **Singleton pattern** - один экземпляр PDO
- Автосоздание папки `database/` если нет
- Методы `runMigrations()`, `executeSql()`
- PDO в режиме `FETCH_ASSOC`

### app/Core/Auth.php
**Назначение**: Система авторизации
**Методы**:
- `Auth::check()` - проверка авторизации
- `Auth::user()` - данные текущего пользователя
- `Auth::login($email, $password)` - вход
- `Auth::loginById($userId)` - вход по ID (для OAuth)
- `Auth::logout()` - выход
- `Auth::requireAuth()` - middleware для защищенных страниц

### app/Core/Lang.php
**Назначение**: Мультиязычность
- Поддержка языков: ru, uk, en
- Переключение через `?lang=ru`
- Сохранение выбора в cookie
- Helper-функции `__()` и `t()`

**Использование**:
```php
echo __('auth.welcome'); // Переведет ключ
echo t('users.total', ['count' => 5]); // С подстановкой
```

### app/Models/User.php
**Назначение**: Работа с пользователями
**Методы**:
- `User::findByEmail($email)`
- `User::findByEmailOrNickname($login)` - для авторизации
- `User::all()` - все пользователи
- `User::create($email, $nickname, $password, $name)`
- `User::updateProfile($id, $data)`
- `User::findByOAuth($provider, $oauthId)` - поиск OAuth пользователя
- `User::createFromOAuth($provider, $oauthId, $email, $name, $avatar)` - создание через OAuth
- `User::linkOAuth($userId, $provider, $oauthId, $avatar)` - привязка OAuth к существующему

---

## 🔐 OAuth Авторизация (Новое: 16.05.2026)

**AdminOrder Dashboard** теперь поддерживает OAuth авторизацию через **Google** и **GitHub**.

### Технологический стек OAuth
- **Библиотека**: `league/oauth2-client` (через Composer)
- **Провайдеры**: `league/oauth2-google`, `league/oauth2-github`
- **Конфигурация**: `.env` файл для credentials
- **Защита**: CSRF через state параметр

### Структура файлов

```
admin/
├── .env                        # OAuth credentials (не коммитить!)
├── .env.example                # Пример конфигурации
├── composer.json               # PHP зависимости
├── OAUTH-SETUP.md             # Подробная инструкция
├── OAUTH-QUICKSTART.md        # Быстрый старт за 5 минут
├── oauth/                      # OAuth entry points
│   ├── google/
│   │   ├── index.php          # Redirect на Google
│   │   └── callback.php       # Callback от Google
│   └── github/
│       ├── index.php          # Redirect на GitHub
│       └── callback.php       # Callback от GitHub
└── app/
    ├── Config/
    │   └── OAuth.php          # Конфигурация провайдеров
    └── Controllers/
        └── OAuthController.php # Обработка OAuth потоков
```

### Новые поля в таблице users

```sql
oauth_provider TEXT DEFAULT NULL  -- 'google' или 'github'
oauth_id TEXT DEFAULT NULL        -- ID от провайдера
avatar TEXT DEFAULT NULL          -- URL аватарки
```

Миграция выполняется автоматически через `Database::runMigrations()`.

### Ключевые классы OAuth

#### app/Config/OAuth.php
**Назначение**: Конфигурация OAuth провайдеров
- `OAuth::getGoogleConfig()` - настройки Google
- `OAuth::getGithubConfig()` - настройки GitHub
- `OAuth::isGoogleConfigured()` - проверка наличия Google credentials
- `OAuth::isGithubConfigured()` - проверка наличия GitHub credentials
- `OAuth::loadEnv()` - загрузка .env файла

#### app/Controllers/OAuthController.php
**Назначение**: Обработка OAuth авторизации
**Методы**:
- `googleRedirect()` - редирект на Google OAuth
- `googleCallback()` - обработка callback от Google
- `githubRedirect()` - редирект на GitHub OAuth
- `githubCallback()` - обработка callback от GitHub
- `handleOAuthLogin($provider, $oauthId, $email, $name, $avatar)` - единая логика логина

### Процесс OAuth авторизации

1. **Пользователь нажимает кнопку** (Google/GitHub) на `/admin/login.php`
2. **Redirect на провайдера** через `/admin/oauth/{provider}/`
3. **Авторизация на стороне провайдера** (Google/GitHub)
4. **Callback** на `/admin/oauth/{provider}/callback` с authorization code
5. **Обмен code на access token**
6. **Получение данных пользователя** (email, name, avatar)
7. **Создание или обновление пользователя** в БД
8. **Автоматический логин** через `Auth::loginById($userId)`
9. **Redirect** на `/admin/index.php`

### Безопасность OAuth

**CSRF защита**:
```php
// Генерация state при redirect
$_SESSION['oauth2state'] = $provider->getState();

// Валидация state при callback
if ($_GET['state'] !== $_SESSION['oauth2state']) {
    // Отклонить запит
}
```

**Особенности**:
- Для OAuth пользователей генерируется случайный пароль (они не логинятся через форму)
- Email валидируется провайдером
- Аватарки загружаются с CDN провайдеров (не хранятся локально)
- Если email уже существует - OAuth привязывается к существующему аккаунту

### UI интеграция

**Login форма** (`app/Views/pages/auth/login.php`):
```php
<?php if (\App\Config\OAuth::isGoogleConfigured()): ?>
<a href="/admin/oauth/google/" class="btn-social btn-google">
    <!-- SVG иконка Google -->
    <span>Google</span>
</a>
<?php endif; ?>
```

Кнопки отображаются **только если** соответствующий провайдер настроен в `.env`.

### Быстрый старт OAuth

См. **OAUTH-QUICKSTART.md** для инструкции за 5 минут.

**Минимальная конфигурация .env**:
```env
APP_URL=http://localhost:5000/admin

GOOGLE_CLIENT_ID=ваш_client_id
GOOGLE_CLIENT_SECRET=ваш_secret

GITHUB_CLIENT_ID=ваш_client_id
GITHUB_CLIENT_SECRET=ваш_secret
```

### Troubleshooting OAuth

**Кнопки не отображаются?**
→ Проверьте `.env` файл, убедитесь что credentials заполнены

**redirect_uri_mismatch?**
→ URL в `.env` должен точно совпадать с настроенным в Google Cloud Console / GitHub OAuth App

**Email не получается от GitHub?**
→ Сделайте email публичным в настройках GitHub или предоставьте scope `user:email`

---

## 🎯 Паттерны разработки

### 1. Создание новой страницы

**Шаг 1**: Создать контроллер (если нужно)
```php
// app/Controllers/ProductController.php
namespace App\Controllers;

class ProductController extends BaseController
{
    public function index()
    {
        $products = Product::all();
        
        $this->render('products/index', [
            'products' => $products,
            'title' => 'Продукты'
        ]);
    }
}
```

**Шаг 2**: Создать view
```php
// app/Views/pages/products/index.php
<div class="page-header">
    <h1><?= $title ?></h1>
</div>

<div class="products-grid">
    <?php foreach ($products as $product): ?>
        <div class="product-card">
            <h3><?= htmlspecialchars($product['name']) ?></h3>
        </div>
    <?php endforeach; ?>
</div>
```

**Шаг 3**: Создать файл в корне админки
```php
// products.php
<?php
require_once 'app/bootstrap.php';
Auth::requireAuth();

$controller = new \App\Controllers\ProductController();
$controller->index();
```

### 2. Добавление новой модели

```php
// app/Models/Product.php
namespace App\Models;

use App\Config\Database;

class Product
{
    public static function all(): array
    {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM products")->fetchAll();
    }
    
    public static function findById(int $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
```

### 3. Работа с формами и AJAX

**HTML форма**:
```html
<form id="userForm" method="POST">
    <input type="text" name="name" required>
    <button type="submit">Сохранить</button>
</form>
```

**JavaScript обработка**:
```javascript
document.getElementById('userForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const response = await fetch('api/users.php', {
        method: 'POST',
        body: formData
    });
    
    const result = await response.json();
    if (result.success) {
        // Успех
    }
});
```

**PHP API endpoint**:
```php
// api/users.php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    
    $success = User::create($name);
    
    echo json_encode(['success' => $success]);
}
```

---

## 🗄 База данных

### Структура таблицы users
```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    nickname TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    name TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME
);
```

### Миграции
Миграции запускаются автоматически в `bootstrap.php`:
```php
Database::runMigrations();
```

Создать миграцию: добавить метод в `Database.php`

---

## 🎨 Frontend

### Стили
- **Единый файл**: `css/styles.css`
- **CSS переменные** для темизации
- **Темная тема** по умолчанию
- Переключатель темы: `#themeToggle`

### JavaScript
- **Единый файл**: `js/script.js`
- Vanilla JS (без jQuery в основном коде)
- Модульная структура через классы

### UI Компоненты
- Sidebar с навигацией
- Модальные окна
- Уведомления (toast)
- Таблицы с сортировкой
- Формы с валидацией

---

## 🔐 Безопасность

### Обязательные практики:
1. **XSS защита**: 
   ```php
   <?= htmlspecialchars($user['name']) ?>
   ```

2. **SQL Injection защита**: 
   ```php
   $stmt->execute([':id' => $id]); // Prepared statements
   ```

3. **CSRF токены** (TODO): Добавить для форм

4. **Password hashing**:
   ```php
   password_hash($password, PASSWORD_DEFAULT)
   password_verify($input, $hash)
   ```

5. **Авторизация на каждой странице**:
   ```php
   // Для админ-страниц
   Auth::requireAdmin();
   
   // Для страниц доступных всем авторизованным
   Auth::requireAuth();
   ```

### Система ролей (добавлена 17.05.2026)
- **user** (пользователь) - доступ только к `/admin/profile.php`
- **admin** (администратор) - полный доступ ко всей админке
- После логина: админ → index.php, пользователь → profile.php
- Обычные пользователи НЕ МОГУТ заходить в админку (только профиль)
- См. `ROLES-SYSTEM.md` для деталей

---

## 🐛 Отладка

### PhpDebugHeaders
```php
// Включено в development режиме
$debug = $GLOBALS['debug'];
$debug->log('User data', $user);
$debug->measure('Query time');
```

### Режимы окружения
```php
// bootstrap.php
putenv('APP_ENV=development'); // или 'production'
```

**Development**: 
- Показывает ошибки
- Debug headers в ответах

**Production**:
- Скрывает ошибки
- Минимум информации

---

## 📝 TODO & Улучшения

### Высокий приоритет
- [ ] Добавить CSRF защиту для форм
- [ ] Реализовать роли пользователей (admin, user)
- [ ] Валидация данных на стороне сервера
- [ ] API endpoints с JWT токенами

### Средний приоритет
- [ ] Pagination для таблиц
- [ ] Поиск и фильтрация
- [ ] Export в CSV/Excel
- [ ] Логирование действий пользователей

### Низкий приоритет
- [ ] Email уведомления
- [ ] Двухфакторная авторизация
- [ ] Темная/светлая тема персистентность
- [ ] PWA поддержка

---

## 💡 Советы AI агенту

### При добавлении нового функционала:
1. **Всегда проверяй существующие паттерны** - не изобретай велосипед
2. **Используй namespace App\\** для всех классов
3. **Autoload работает автоматически** - просто создай файл в правильной папке
4. **Не забывай `require_once 'app/bootstrap.php'`** в точках входа
5. **Используй Singleton для Database** - `Database::getInstance()`
6. **Защищай страницы через `Auth::requireAuth()`**
7. **Всегда экранируй вывод** - `htmlspecialchars()`
8. **Используй prepared statements** для SQL

### При чтении кода:
- `app/bootstrap.php` - точка старта, там все инициализируется
- `app/Views/layouts/main.php` - базовая структура HTML страниц
- Контроллеры не эхоят HTML напрямую, только через `$this->render()`

### При рефакторинге:
- Старые файлы (login.php, index.php) могут содержать HTML - это нормально
- Постепенный переход на MVC - не ломай работающий код
- Layout система использует `ob_start()` для буферизации

---

## 📚 Полезные команды

### SQLite база
```bash
# Открыть базу
sqlite3 database/crm.db

# Показать таблицы
.tables

# Показать структуру
.schema users

# Выполнить запрос
SELECT * FROM users;
```

### Git (если используется)
```bash
# Игнорировать базу
echo "database/*.db" >> .gitignore
```

---

## � Известные проблемы и решения

### PHP 8.x Совместимость (Исправлено 16.05.2026)

**Проблема**: Deprecated warnings, TypeError и session errors при использовании PHP 8.x

**Симптомы**:
- `PHP Deprecated: Return type of App\Config\DebugPDO::beginTransaction()...`
- `PHP Warning: session_start(): Session cannot be started after headers have already been sent`
- `PHP Warning: Undefined array key "REQUEST_METHOD"`
- `TypeError: Database::connect(): Return value must be of type PDO, DebugPDO returned`

**Решение**:
1. ✅ Класс `DebugPDO` переделан с наследования на декоратор (композицию)
2. ✅ Добавлены правильные return types для всех методов PDO
3. ✅ Добавлены проверки `isset($_SERVER['REQUEST_METHOD'])` во всех entry points
4. ✅ Обновлены типы в `Database` на union types: `PDO|DebugPDO`

**Детали**: См. [admin/BUGFIX-PHP8.md](admin/BUGFIX-PHP8.md)

**Важно для разработчиков**:
- `DebugPDO` теперь **НЕ наследует** PDO, а оборачивает его (паттерн Decorator)
- Все методы проксируются к реальному PDO объекту
- Return types соответствуют PHP 8.x стандартам (union types: `PDO|DebugPDO`, `string|false`, `PDOStatement|false`)
- Класс `Database` использует union types для совместимости с обоими типами

### Рекомендации при добавлении новых классов

1. **Не наследуйте PDO напрямую** - используйте композицию
2. **Всегда проверяйте $_SERVER ключи** через `isset()` перед использованием
3. **Не выводите ничего до `require bootstrap.php`** - это может нарушить session_start()

---

## 🔗 Связанные файлы

- **Документация**: `admin/app/README.md`
- **Миграции**: `Database::runMigrations()` в `app/Config/Database.php`
- **Языки**: `admin/lang/{ru,uk,en}.php`
- **Конфигурация сервера**: `admin/fiveserver.config.js` (для Five Server)
- **Багфиксы**: `admin/BUGFIX-PHP8.md` - исправления для PHP 8.x

---

## 📞 Контакты проекта

**Проект**: AdminOrder Dashboard  
**Создан**: Май 2026  
**Версия**: 1.0.0  
**PHP**: 8.x+  
**База**: SQLite 3

---

*Этот документ автоматически обновляется при существенных изменениях архитектуры*
