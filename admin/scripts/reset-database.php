<?php
/**
 * Скрипт для пересоздания базы данных с правильной структурой.
 * ВНИМАНИЕ: удаляет все существующие данные!
 */

$adminRoot = dirname(__DIR__);
$dbPath = $adminRoot . '/database/crm.db';

if (file_exists($dbPath)) {
    echo "Удаление старой базы данных...\n";
    unlink($dbPath);
}

echo "Создание новой базы данных...\n";

require_once $adminRoot . '/app/bootstrap.php';

use App\Config\Database;
use App\Models\User;

Database::initSchema();

echo "База данных создана!\n";

echo "Создание тестового пользователя...\n";

$email = 'admin@example.com';
$nickname = 'admin';
$password = 'admin123';
$name = 'Администратор';

if (User::create($email, $nickname, $password, $name)) {
    echo "✅ Тестовый пользователь создан:\n";
    echo "   Email: admin@example.com\n";
    echo "   Nickname: admin\n";
    echo "   Password: admin123\n";
} else {
    echo "❌ Ошибка создания пользователя\n";
}

echo "\nГотово! Теперь можно войти в систему.\n";
