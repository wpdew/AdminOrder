<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\BlockedIp;
use App\Models\OrderRecord;
use App\Models\Settings;

/**
 * Контроллер для управління таблицями даних
 */
class TableController extends BaseController
{
    /**
     * Перевірка чи увімкнено внутрішню БД для замовлень
     */
    private function ensureInternalOrdersEnabled(): bool
    {
        $isEnabled = true;

        try {
            $isEnabled = (new Settings())->isInternalOrdersDbEnabled();
        } catch (\Throwable $e) {
            $isEnabled = true;
        }

        if (!$isEnabled) {
            $_SESSION['flash_warning'] = 'Внутренняя база заказов отключена в интеграциях';
            $this->redirect('/admin/?route=settings');
            return false;
        }

        return true;
    }

    /**
     * Сторінка таблиці финдингів безпеки
     */
    public function index(): void
    {
        // Вимагаємо прав адміністратора
        Auth::requireAdmin();
        if (!$this->ensureInternalOrdersEnabled()) {
            return;
        }
        $statusFilter = trim((string)($_GET['status'] ?? ''));
        $allowedStatuses = ['new', 'processing', 'done', 'cancelled', 'spam'];

        if ($statusFilter !== '' && !in_array($statusFilter, $allowedStatuses, true)) {
            $statusFilter = '';
        }

        $allOrders = (new OrderRecord())->getAll(1000);
        $orders = array_values(array_filter($allOrders, static function (array $order) use ($statusFilter): bool {
            if ($statusFilter === '') {
                return true;
            }

            $status = trim((string)($order['status'] ?? 'new'));

            if ($statusFilter === 'spam') {
                return $status === 'spam' || (int)($order['is_spam'] ?? 0) === 1;
            }

            return $status === $statusFilter;
        }));

        $statusCounts = [
            'all' => count($allOrders),
            'new' => 0,
            'processing' => 0,
            'done' => 0,
            'cancelled' => 0,
            'spam' => 0,
        ];

        foreach ($allOrders as $order) {
            $status = trim((string)($order['status'] ?? 'new'));
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }

            if ((int)($order['is_spam'] ?? 0) === 1 && $status !== 'spam') {
                $statusCounts['spam']++;
            }
        }
        
        // Рендеримо view
        $this->render('dashboard/table', [
            'title' => __('orders.title'),
            'activeMenu' => 'table',
            'orders' => $orders,
            'allOrdersCount' => $statusCounts['all'],
            'statusCounts' => $statusCounts,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Оновити замовлення з таблиці
     */
    public function update(): void
    {
        Auth::requireAdmin();
        if (!$this->ensureInternalOrdersEnabled()) {
            return;
        }

        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/?route=table');
            return;
        }

        if (($_POST['action'] ?? '') === 'create' && isset($_POST['ip_address'])) {
            $this->blockIpFromOrder();
            return;
        }

        $orderId = (int)($_POST['id'] ?? 0);
        if ($orderId <= 0) {
            $_SESSION['flash_error'] = __('orders.invalid_id');
            $this->redirect('/admin/?route=table');
            return;
        }

        $model = new OrderRecord();
        $updated = $model->updateById($orderId, [
            'customer_name' => trim((string)($_POST['customer_name'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')),
            'product_title' => trim((string)($_POST['product_title'] ?? '')),
            'product_price' => (string)($_POST['product_price'] ?? ''),
            'quantity' => (string)($_POST['quantity'] ?? ''),
            'total_sum' => (string)($_POST['total_sum'] ?? ''),
            'comment' => trim((string)($_POST['comment'] ?? '')),
            'payment' => trim((string)($_POST['payment'] ?? '')),
            'delivery' => trim((string)($_POST['delivery'] ?? '')),
            'delivery_address' => trim((string)($_POST['delivery_address'] ?? '')),
            'status' => trim((string)($_POST['status'] ?? 'new')),
        ]);

        if ($updated) {
            $_SESSION['flash_success'] = __('orders.update_success');
        } else {
            $_SESSION['flash_error'] = __('orders.update_error');
        }

        $redirectUrl = '/admin/?route=table';
        if (!empty($_POST['status_filter'])) {
            $redirectUrl .= '&status=' . urlencode((string)$_POST['status_filter']);
        }

        $this->redirect($redirectUrl);
    }

    private function blockIpFromOrder(): void
    {
        $ip = trim((string)($_POST['ip_address'] ?? ''));
        $reason = trim((string)($_POST['reason'] ?? ''));

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $_SESSION['flash_error'] = __('blocked_ips.invalid_ip_format');
            $this->redirectToTable();
            return;
        }

        $model = new BlockedIp();
        if ($model->create([
            'ip_address' => $ip,
            'reason' => $reason,
            'is_active' => 1,
        ])) {
            $_SESSION['flash_success'] = __('blocked_ips.ip_blocked');
        } else {
            $_SESSION['flash_error'] = __('blocked_ips.create_error');
        }

        $this->redirectToTable();
    }

    private function redirectToTable(): void
    {
        $redirectUrl = '/admin/?route=table';
        if (!empty($_POST['status_filter'])) {
            $redirectUrl .= '&status=' . urlencode((string)$_POST['status_filter']);
        }

        $this->redirect($redirectUrl);
    }
}
