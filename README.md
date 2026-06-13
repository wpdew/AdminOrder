# AdminOrder Dashboard

Административная панель на чистом PHP (MVC) для управления пользователями, заказами, интеграциями и безопасностью.

## Что сейчас в системе

- Централизованный роутер: `admin/index.php` + маршруты вида `?route=...`
- Роли доступа: `admin` и `user`
- Авторизация по email/nickname + password, OAuth (Google/GitHub)
- Профиль пользователя (`?route=profile`) с сохранением языка интерфейса
- Управление пользователями (`?route=users`) с CRUD и сменой роли
- Реальная таблица заказов (`?route=table`) из БД с редактированием
- Фильтрация заказов по статусам (в т.ч. переход с карточек Dashboard)
- Настройки интеграций (`?route=settings`) в таблице `order_settings`
- Управление блокировками IP (`?route=blocked-ips`) + интеграция с loader
- Dashboard с актуальной статистикой по пользователям/заказам/IP/интеграциям
- Мультиязычность (`uk`, `en`, `ru`) с едиными словарями перевода

## Технологии

- PHP 8+
- SQLite (`admin/database/crm.db`)
- Vanilla JS + CSS
- jQuery/DataTables (для таблицы заказов)
- Composer + `league/oauth2-*`

## Структура проекта

```text
admin/
  app/
    Controllers/
    Models/
    Views/
    Config/
    Core/
    bootstrap.php
  api/
    users/
  oauth/
    google/
    github/
  css/
  js/
  database/
  index.php
```

## Быстрое разворачивание нового проекта

Ниже минимальный сценарий, чтобы поднять чистую копию и зайти в админку.

### 1. Требования

- PHP `>= 8.0`
- Composer
- Доступ на запись в `admin/database/`

### 2. Установить зависимости

```bash
cd admin
composer install
```

### 3. Опционально настроить OAuth

```bash
cp .env.example .env
```

Заполните в `.env`:

```env
APP_URL=http://localhost:5000/admin
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
```

Если OAuth не нужен, этот шаг можно пропустить.

### 4. Запустить локальный сервер из корня репозитория

```bash
php -S localhost:5000
```

### 4.1 Создать новую БД (если `crm.db` отсутствует)

База и таблицы создаются через bootstrap и миграции автоматически.

Команда инициализации из корня репозитория:

```bash
php -r "require 'admin/app/bootstrap.php'; echo 'DB initialized'.PHP_EOL;"
```

Проверка, что таблицы создались:

```bash
sqlite3 admin/database/crm.db ".tables"
```

### 5. Открыть админку

`http://localhost:5000/admin/`

### 6. Создать первый аккаунт

Перейдите на:

`http://localhost:5000/admin/?route=register`

При регистрации язык интерфейса нового пользователя по умолчанию: `en`.

### 7. Назначить админ-роль первому пользователю

Для новой БД назначьте роль вручную:

```bash
sqlite3 admin/database/crm.db "UPDATE users SET role='admin' WHERE id=1;"
```

После этого войдите под этим пользователем:

`http://localhost:5000/admin/?route=login`

## Важные маршруты

- `?route=login` - вход
- `?route=register` - регистрация
- `?route=profile` - профиль текущего пользователя
- `?route=users` - управление пользователями (admin)
- `?route=table` - заказы + фильтрация/редактирование (admin)
- `?route=settings` - интеграции и `order_settings` (admin)
- `?route=blocked-ips` - заблокированные IP (admin)
- `?route=access-denied` - страница отказа в доступе

## База данных и миграции

Миграции запускаются автоматически в `admin/app/bootstrap.php` через `Database::runMigrations()`.

Ключевые таблицы:

- `users`
- `orders`
- `order_settings`
- `blocked_ips`

## Язык интерфейса

- Поддержка: `uk`, `en`, `ru`
- Глобальное переключение: параметр `?lang=...`
- Персональный язык сохраняется в `users.interface_lang`
- При логине язык пользователя автоматически применяется к сессии
- Изменить язык можно в:
  - `?route=profile`
  - `?route=users` (при создании/редактировании пользователя)

## Полезные команды

Проверка синтаксиса PHP-файла:

```bash
php -l admin/app/Controllers/TableController.php
```

Быстро посмотреть таблицы SQLite:

```bash
sqlite3 admin/database/crm.db ".tables"
```

## Примечания

- Для новой БД роль `admin` назначается SQL-командой из quick-start.
- Для production рекомендуется отключить debug-поведение и настроить сервер (Nginx/Apache) с корректным document root и rewrite.
