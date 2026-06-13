<?php
/**
 * Loader для заблокованих IP-адрес
 * Завантажує заблоковані IP з БД та створює масив $spamip для order.php
 */

// Підключаємо автозавантажувач admin
require_once __DIR__ . '/admin/app/bootstrap.php';

use App\Models\BlockedIp;

try {
    $blockedIpModel = new BlockedIp();
    
    // Отримуємо активні заблоковані IP
    $spamip = $blockedIpModel->getActive();
    
    // Якщо немає жодного IP в БД, використовуємо дефолтні значення
    if (empty($spamip)) {
        $spamip = [
            '134.249.248.30',
            '2a03:2260:10:22:19c2:7b5e:5809:a0cc'
        ];
    }
    
} catch (\Throwable $e) {
    // При помилці використовуємо дефолтні значення
    $spamip = [
        '134.249.248.30',
        '2a03:2260:10:22:19c2:7b5e:5809:a0cc'
    ];
    
    // Можна логувати помилку
    error_log("BlockedIp loader error: " . $e->getMessage());
}
