<?php
require_once '../../app/bootstrap.php';

use App\Core\Auth;
use App\Controllers\UserController;

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

// Обновление пользователя
$controller = new UserController();
$controller->update();
