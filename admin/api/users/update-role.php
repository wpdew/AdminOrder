<?php
require_once '../../app/bootstrap.php';

use App\Core\Auth;
use App\Models\User;

header('Content-Type: application/json');

// Проверка авторизации и прав администратора
if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Требуется авторизация']);
    exit;
}

if (!Auth::isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Недостаточно прав']);
    exit;
}

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Метод не поддерживается']);
    exit;
}

$userId = intval($_POST['user_id'] ?? 0);
$role = trim($_POST['role'] ?? '');

// Валидация
if ($userId <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID пользователя не указан']);
    exit;
}

if (!in_array($role, ['user', 'admin'])) {
    echo json_encode(['success' => false, 'error' => 'Недопустимая роль']);
    exit;
}

// Нельзя изменить роль самому себе
if ($userId === Auth::id()) {
    echo json_encode(['success' => false, 'error' => 'Нельзя изменить свою собственную роль']);
    exit;
}

// Проверяем что пользователь существует
$user = User::findById($userId);
if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Пользователь не найден']);
    exit;
}

// Обновляем роль
$success = User::updateRole($userId, $role);

if ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Роль пользователя успешно обновлена',
        'user' => [
            'id' => $userId,
            'role' => $role
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка обновления роли']);
}
