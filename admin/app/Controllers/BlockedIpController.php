<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\BlockedIp;

/**
 * Контролер керування заблокованими IP
 */
class BlockedIpController extends BaseController
{
    /**
     * Список заблокованих IP
     */
    public function index(): void
    {
        Auth::requireAdmin();

        $model = new BlockedIp();
        $ips = $model->getAll();

        $this->render('dashboard/blocked-ips', [
            'title' => 'Blocked IPs',
            'activeMenu' => 'blocked-ips',
            'ips' => $ips,
        ]);
    }

    /**
     * Обробка POST дій
     */
    public function handlePost(): void
    {
        Auth::requireAdmin();
        $redirectUrl = $this->getSafeRedirectUrl();

        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($redirectUrl);
            return;
        }

        $action = $_POST['action'] ?? '';
        $model = new BlockedIp();

        switch ($action) {
            case 'create':
                $ip = trim((string)($_POST['ip_address'] ?? ''));
                $reason = trim((string)($_POST['reason'] ?? ''));

                if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                    $_SESSION['flash_error'] = __('blocked_ips.invalid_ip_format');
                    break;
                }

                if ($model->create([
                    'ip_address' => $ip,
                    'reason' => $reason,
                    'is_active' => 1,
                ])) {
                    $_SESSION['flash_success'] = __('blocked_ips.ip_blocked');
                } else {
                    $_SESSION['flash_error'] = __('blocked_ips.create_error');
                }
                break;

            case 'bulk_add':
                $ipList = trim((string)($_POST['ip_list'] ?? ''));
                $reason = trim((string)($_POST['bulk_reason'] ?? ''));

                if ($ipList === '') {
                    $_SESSION['flash_warning'] = __('blocked_ips.no_ips_added');
                    break;
                }

                $result = $model->bulkCreate($ipList, $reason);
                if ($result['added'] > 0) {
                    $_SESSION['flash_success'] = __('blocked_ips.bulk_added') . ': ' . $result['added'] . ', ' . __('blocked_ips.bulk_skipped') . ': ' . $result['skipped'];
                } else {
                    $_SESSION['flash_warning'] = __('blocked_ips.no_ips_added');
                }
                break;

            case 'toggle':
                $id = (int)($_POST['id'] ?? 0);

                if ($id > 0 && $model->toggleActive($id)) {
                    $_SESSION['flash_success'] = __('blocked_ips.status_changed');
                } else {
                    $_SESSION['flash_error'] = __('blocked_ips.update_error');
                }
                break;

            case 'delete':
                $id = (int)($_POST['id'] ?? 0);

                if ($id > 0 && $model->delete($id)) {
                    $_SESSION['flash_success'] = __('blocked_ips.ip_deleted');
                } else {
                    $_SESSION['flash_error'] = __('blocked_ips.update_error');
                }
                break;

            default:
                $_SESSION['flash_warning'] = 'Unknown action';
                break;
        }

        $this->redirect($redirectUrl);
    }

    private function getSafeRedirectUrl(): string
    {
        $redirectUrl = trim((string)($_POST['redirect_to'] ?? ''));

        if ($redirectUrl !== '' && str_starts_with($redirectUrl, '/admin/')) {
            return $redirectUrl;
        }

        return '/admin/?route=blocked-ips';
    }
}
