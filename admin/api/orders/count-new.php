<?php

require_once '../../app/bootstrap.php';

use App\Core\Auth;
use App\Models\OrderRecord;

header('Content-Type: application/json');

// Вимагаємо авторизації
Auth::requireAuth();

// Тільки для адміністраторів
if (!Auth::isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Access Denied']);
    exit();
}

$orderRecord = new OrderRecord();
$newOrdersCount = $orderRecord->getNewOrdersCount();

echo json_encode(['success' => true, 'count' => $newOrdersCount]);
exit();
