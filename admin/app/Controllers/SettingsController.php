<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Settings;

/**
 * Контроллер страницы настроек
 */
class SettingsController extends BaseController
{
    /**
     * Показать страницу настроек интеграций
     */
    public function index(): void
    {
        Auth::requireAdmin();

        $settingsModel = new Settings();
        $definition = $settingsModel->getOrderSettingsDefinition();
        $settingsRows = $settingsModel->getOrderSettings();
        $settingsMap = [];

        foreach ($settingsRows as $row) {
            $settingsMap[$row['setting_key']] = $row;
        }

        $this->render('dashboard/settings', [
            'title' => __('nav.integrations'),
            'activeMenu' => 'settings',
            'definition' => $definition,
            'settingsMap' => $settingsMap,
        ]);
    }

    /**
     * Зберегти налаштування order.php
     */
    public function save(): void
    {
        Auth::requireAdmin();

        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/?route=settings');
            return;
        }

        $settings = $_POST['settings'] ?? [];
        $activeFlags = $_POST['active'] ?? [];

        if (!is_array($settings) || !is_array($activeFlags)) {
            $_SESSION['flash_error'] = __('order_settings.invalid_form');
            $this->redirect('/admin/?route=settings');
            return;
        }

        $settingsModel = new Settings();
        $saved = $settingsModel->updateOrderSettings($settings, $activeFlags);

        if ($saved) {
            $_SESSION['flash_success'] = __('order_settings.settings_saved');
        } else {
            $_SESSION['flash_error'] = __('order_settings.save_error');
        }

        $this->redirect('/admin/?route=settings');
    }
}
