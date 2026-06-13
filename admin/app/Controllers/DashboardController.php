<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\BlockedIp;
use App\Models\OrderRecord;
use App\Models\Settings;
use App\Models\User;

/**
 * Контроллер главной страницы (Dashboard)
 */
class DashboardController extends BaseController
{
    /**
     * Главная страница админки
     */
    public function index(): void
    {
        Auth::requireAdmin();

        $settingsModel = new Settings();
        $showOrdersStats = $settingsModel->isInternalOrdersDbEnabled();

        $totalUsers = User::count();
        $users = User::all();
        $adminsCount = 0;
        foreach ($users as $user) {
            if (($user['role'] ?? 'user') === 'admin') {
                $adminsCount++;
            }
        }

        $orders = [];
        if ($showOrdersStats) {
            $orderModel = new OrderRecord();
            $orders = $orderModel->getAll(5000);
        }

        $totalOrders = count($orders);
        $newOrders = 0;
        $processingOrders = 0;
        $doneOrders = 0;
        $cancelledOrders = 0;
        $spamOrders = 0;
        $totalRevenue = 0.0;

        foreach ($orders as $order) {
            $status = trim((string)($order['status'] ?? 'new'));
            if ($status === 'new') {
                $newOrders++;
            } elseif ($status === 'processing') {
                $processingOrders++;
            } elseif ($status === 'done') {
                $doneOrders++;
            } elseif ($status === 'cancelled') {
                $cancelledOrders++;
            }

            if ((int)($order['is_spam'] ?? 0) === 1 || $status === 'spam') {
                $spamOrders++;
            }

            $totalRevenue += (float)($order['total_sum'] ?? 0);
        }

        $blockedIpModel = new BlockedIp();
        $allBlockedIps = $blockedIpModel->getAll();
        $activeBlockedIps = 0;
        foreach ($allBlockedIps as $blockedIp) {
            if ((int)($blockedIp['is_active'] ?? 0) === 1) {
                $activeBlockedIps++;
            }
        }

        $settingsRows = $settingsModel->getOrderSettings();
        $configuredGroups = [];
        foreach ($settingsRows as $setting) {
            $value = trim((string)($setting['setting_value'] ?? ''));
            $isActive = (int)($setting['is_active'] ?? 0) === 1;
            $group = (string)($setting['setting_group'] ?? 'general');

            if ($isActive && $value !== '') {
                $configuredGroups[$group] = true;
            }
        }

        $systemStats = [
            [
                'class' => 'critical',
                'icon' => '👥',
                'label' => __('dashboard_overview.users_label'),
                'value' => (string)$totalUsers,
                'trend' => __('dashboard_overview.users_trend', ['count' => (string)$adminsCount]),
                'trendClass' => 'trend-up',
                'meta' => __('dashboard_overview.users_meta'),
                'url' => '/admin/?route=users',
            ],
            [
                'class' => 'success',
                'icon' => '⛔',
                'label' => __('dashboard_overview.blocked_label'),
                'value' => (string)count($allBlockedIps),
                'trend' => __('dashboard_overview.blocked_trend', ['count' => (string)$activeBlockedIps]),
                'trendClass' => 'trend-up',
                'meta' => __('dashboard_overview.blocked_meta'),
                'url' => '/admin/?route=blocked-ips',
            ],
            [
                'class' => 'success',
                'icon' => '🔌',
                'label' => __('dashboard_overview.integrations_label'),
                'value' => (string)count($configuredGroups),
                'trend' => __('dashboard_overview.integrations_trend', ['count' => (string)count($configuredGroups)]),
                'trendClass' => 'trend-up',
                'meta' => __('dashboard_overview.integrations_meta'),
                'url' => '/admin/?route=settings',
            ],
        ];

        $orderSummaryStats = [
            [
                'class' => 'high',
                'icon' => '🧾',
                'label' => __('dashboard_overview.orders_label'),
                'value' => (string)$totalOrders,
                'trend' => __('dashboard_overview.orders_trend', ['count' => (string)$newOrders]),
                'trendClass' => 'trend-up',
                'meta' => __('dashboard_overview.orders_meta'),
                'url' => '/admin/?route=table',
            ],
            [
                'class' => 'low',
                'icon' => '💸',
                'label' => __('dashboard_overview.revenue_label'),
                'value' => number_format($totalRevenue, 0, '.', ' ') . ' ₴',
                'trend' => __('dashboard_overview.revenue_trend', ['count' => (string)$spamOrders]),
                'trendClass' => 'trend-down',
                'meta' => __('dashboard_overview.revenue_meta'),
                'url' => '/admin/?route=table',
            ],
        ];

        $orderStatusStats = [
            [
                'class' => 'high',
                'icon' => '🆕',
                'label' => __('dashboard_overview.status_new_label'),
                'value' => (string)$newOrders,
                'trend' => __('dashboard_overview.status_new_trend'),
                'trendClass' => 'trend-up',
                'meta' => __('dashboard_overview.status_new_meta'),
                'url' => '/admin/?route=table&status=new',
            ],
            [
                'class' => 'medium',
                'icon' => '⏳',
                'label' => __('dashboard_overview.status_processing_label'),
                'value' => (string)$processingOrders,
                'trend' => __('dashboard_overview.status_processing_trend'),
                'trendClass' => 'trend-up',
                'meta' => __('dashboard_overview.status_processing_meta'),
                'url' => '/admin/?route=table&status=processing',
            ],
            [
                'class' => 'success',
                'icon' => '✅',
                'label' => __('dashboard_overview.status_done_label'),
                'value' => (string)$doneOrders,
                'trend' => __('dashboard_overview.status_done_trend'),
                'trendClass' => 'trend-down',
                'meta' => __('dashboard_overview.status_done_meta'),
                'url' => '/admin/?route=table&status=done',
            ],
            [
                'class' => 'critical',
                'icon' => '🛑',
                'label' => __('dashboard_overview.status_cancelled_label'),
                'value' => (string)$cancelledOrders,
                'trend' => __('dashboard_overview.status_cancelled_trend'),
                'trendClass' => 'trend-up',
                'meta' => __('dashboard_overview.status_cancelled_meta'),
                'url' => '/admin/?route=table&status=cancelled',
            ],
            [
                'class' => 'medium',
                'icon' => '🚫',
                'label' => __('dashboard_overview.status_spam_label'),
                'value' => (string)$spamOrders,
                'trend' => __('dashboard_overview.status_spam_trend'),
                'trendClass' => 'trend-up',
                'meta' => __('dashboard_overview.status_spam_meta'),
                'url' => '/admin/?route=table&status=spam',
            ],
        ];
        
        // Рендерим view
        $this->render('dashboard/index', [
            'title' => __('dashboard.title'),
            'activeMenu' => 'dashboard',
            'totalUsers' => $totalUsers,
            'systemStats' => $systemStats,
            'showOrdersStats' => $showOrdersStats,
            'orderSummaryStats' => $orderSummaryStats,
            'orderStatusStats' => $orderStatusStats,
        ]);
    }
}
