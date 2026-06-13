<?php
/**
 * Клас для завантаження налаштувань з БД
 * Використовується в order.php для отримання налаштувань
 */

// Підключаємо autoloader адмінки
require_once __DIR__ . '/admin/app/bootstrap.php';

use App\Models\Settings;

class OrderSettings {
    private static $settings = null;
    
    /**
     * Завантажити налаштування з БД
     */
    public static function load() {
        if (self::$settings === null) {
            try {
                $settingsModel = new Settings();
                $orderSettings = $settingsModel->getOrderSettingsArray();
                
                // Зберігаємо налаштування
                self::$settings = $orderSettings;
            } catch (\Throwable $e) {
                // Якщо помилка - використовуємо порожні налаштування
                self::$settings = [];
            }
        }
        
        return self::$settings;
    }
    
    /**
     * Отримати конкретне налаштування
     */
    public static function get($key, $default = '') {
        $settings = self::load();
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
    
    /**
     * Перевірити чи активна інтеграція
     */
    public static function isActive($key) {
        try {
            $settingsModel = new Settings();
            $setting = $settingsModel->getOrderSetting($key);
            
            return $setting && $setting['is_active'] == 1 && !empty($setting['setting_value']);
        } catch (\Throwable $e) {
            return false;
        }
    }
}

// Завантажуємо налаштування
$orderSettings = OrderSettings::load();

// Використовувати внутрішню БД для замовлень
$use_internal_orders_db = true;
try {
    $settingsModel = new Settings();
    $use_internal_orders_db = $settingsModel->isInternalOrdersDbEnabled();
} catch (\Throwable $e) {
    $use_internal_orders_db = true;
}

// Присвоюємо змінним значення з БД (якщо вони є, інакше залишаємо порожніми)
$tgtoken = OrderSettings::get('tg_token', '');
$tgchatid = OrderSettings::get('tg_chatid', '');
$tgchatforspamid = OrderSettings::get('tg_chatforspamid', '');

$crm_lp_token = OrderSettings::get('crm_lp_token', '');
$crm_lp_adress = OrderSettings::get('crm_lp_adress', 'http://_____________.lp-crm.biz');
$crm_lp_office = OrderSettings::get('crm_lp_office', '1');

$crm_salesdrive_token = OrderSettings::get('crm_salesdrive_token', '');
$crm_salesdrive_sources = OrderSettings::get('crm_salesdrive_sources', 'https://_______.salesdrive.me/handler/');

$crm_key_token = OrderSettings::get('crm_key_token', '');
$crm_key_sources = OrderSettings::get('crm_key_sources', '');
$crm_key_voronka = OrderSettings::get('crm_key_voronka', '');

$api_token_keep_crm = OrderSettings::get('api_token_keep_crm', '');

$token_magnetstore = OrderSettings::get('token_magnetstore', '');
$tenant_magnetstore = OrderSettings::get('tenant_magnetstore', '');

$crm_ebash_token = OrderSettings::get('crm_ebash_token', '');
$crm_ebash_adress = OrderSettings::get('crm_ebash_adress', '');
$crm_ebash_ofise = OrderSettings::get('crm_ebash_ofise', '1');

$googleURL = OrderSettings::get('google_url', '');

$email = OrderSettings::get('notification_email', '');
