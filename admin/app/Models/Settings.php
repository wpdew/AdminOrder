<?php

namespace App\Models;

use App\Config\Database;
use App\Config\DebugPDO;
use PDO;

/**
 * Модель для роботи з налаштуваннями
 */
class Settings
{
    private PDO|DebugPDO $db;

    /**
     * Дефолтна структура налаштувань order.php
     */
    private const ORDER_SETTINGS_DEFINITION = [
        'internal_storage' => [
            'title' => 'Internal Storage',
            'fields' => [
                'use_internal_orders_db' => [
                    'label' => 'Use internal database for orders',
                    'placeholder' => '1',
                    'default' => '1',
                ],
            ],
        ],
        'telegram' => [
            'title' => 'Telegram',
            'fields' => [
                'tg_token' => ['label' => 'Bot Token', 'placeholder' => '123456:ABCDEF...', 'default' => ''],
                'tg_chatid' => ['label' => 'Chat ID', 'placeholder' => '-1001234567890', 'default' => ''],
                'tg_chatforspamid' => ['label' => 'Spam Chat ID', 'placeholder' => '-1001234567890', 'default' => ''],
            ],
        ],
        'lp_crm' => [
            'title' => 'LP CRM',
            'fields' => [
                'crm_lp_token' => ['label' => 'API Token', 'placeholder' => 'LP CRM token', 'default' => ''],
                'crm_lp_adress' => ['label' => 'API Address', 'placeholder' => 'http://example.lp-crm.biz', 'default' => 'http://_____________.lp-crm.biz'],
                'crm_lp_office' => ['label' => 'Office', 'placeholder' => '1', 'default' => '1'],
            ],
        ],
        'salesdrive' => [
            'title' => 'SalesDrive',
            'fields' => [
                'crm_salesdrive_token' => ['label' => 'Form Token', 'placeholder' => 'SalesDrive form token', 'default' => ''],
                'crm_salesdrive_sources' => ['label' => 'Handler URL', 'placeholder' => 'https://project.salesdrive.me/handler/', 'default' => 'https://_______.salesdrive.me/handler/'],
            ],
        ],
        'key_crm' => [
            'title' => 'Key CRM',
            'fields' => [
                'crm_key_token' => ['label' => 'API Token', 'placeholder' => 'Key CRM token', 'default' => ''],
                'crm_key_sources' => ['label' => 'Source URL', 'placeholder' => 'https://api.keycrm.app/...', 'default' => ''],
                'crm_key_voronka' => ['label' => 'Pipeline', 'placeholder' => 'Pipeline/Stage ID', 'default' => ''],
            ],
        ],
        'keep_crm' => [
            'title' => 'Keep CRM',
            'fields' => [
                'api_token_keep_crm' => ['label' => 'API Token', 'placeholder' => 'Keep CRM token', 'default' => ''],
            ],
        ],
        'magnetstore' => [
            'title' => 'MagnetStore',
            'fields' => [
                'token_magnetstore' => ['label' => 'Token', 'placeholder' => 'MagnetStore token', 'default' => ''],
                'tenant_magnetstore' => ['label' => 'Tenant', 'placeholder' => 'magnetstore-tenant', 'default' => ''],
            ],
        ],
        'ebash' => [
            'title' => 'Ebash CRM',
            'fields' => [
                'crm_ebash_token' => ['label' => 'API Token', 'placeholder' => 'Ebash token', 'default' => ''],
                'crm_ebash_adress' => ['label' => 'API Address', 'placeholder' => 'https://ebash.example.com', 'default' => ''],
                'crm_ebash_ofise' => ['label' => 'Office', 'placeholder' => '1', 'default' => '1'],
            ],
        ],
        'google_sheets' => [
            'title' => 'Google Sheets',
            'fields' => [
                'google_url' => ['label' => 'Webhook URL', 'placeholder' => 'https://script.google.com/macros/s/.../exec', 'default' => ''],
            ],
        ],
        'email' => [
            'title' => 'Email Notifications',
            'fields' => [
                'notification_email' => ['label' => 'Email', 'placeholder' => 'orders@example.com', 'default' => ''],
            ],
        ],
    ];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureDefaultOrderSettings();
    }

    /**
     * Отримати опис полів для order_settings форми
     */
    public function getOrderSettingsDefinition(): array
    {
        return self::ORDER_SETTINGS_DEFINITION;
    }
    
    /**
     * Отримати всі налаштування лендінгу
     */
    public function getLandingSettings(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM landing_settings 
            ORDER BY setting_key
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Отримати конкретне налаштування лендінгу
     */
    public function getLandingSetting(string $key): ?string
    {
        $stmt = $this->db->prepare("
            SELECT setting_value FROM landing_settings 
            WHERE setting_key = ?
        ");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        
        return $result ? $result['setting_value'] : null;
    }
    
    /**
     * Оновити налаштування лендінгу
     */
    public function updateLandingSetting(string $key, string $value): bool
    {
        $stmt = $this->db->prepare("
            UPDATE landing_settings 
            SET setting_value = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE setting_key = ?
        ");
        return $stmt->execute([$value, $key]);
    }
    
    /**
     * Масове оновлення налаштувань лендінгу
     */
    public function updateLandingSettings(array $settings): bool
    {
        $this->db->beginTransaction();
        
        try {
            $stmt = $this->db->prepare("
                UPDATE landing_settings 
                SET setting_value = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE setting_key = ?
            ");
            
            foreach ($settings as $key => $value) {
                $stmt->execute([$value, $key]);
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Отримати всі налаштування order.php
     */
    public function getOrderSettings(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM order_settings 
            ORDER BY setting_key
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Отримати конкретне налаштування order.php
     */
    public function getOrderSetting(string $key): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM order_settings 
            WHERE setting_key = ?
        ");
        $stmt->execute([$key]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Оновити налаштування order.php
     */
    public function updateOrderSetting(string $key, string $value, bool $isActive = null): bool
    {
        if ($isActive !== null) {
            $stmt = $this->db->prepare("
                UPDATE order_settings 
                SET setting_value = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE setting_key = ?
            ");
            return $stmt->execute([$value, $isActive ? 1 : 0, $key]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE order_settings 
                SET setting_value = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE setting_key = ?
            ");
            return $stmt->execute([$value, $key]);
        }
    }
    
    /**
     * Масове оновлення налаштувань order.php
     */
    public function updateOrderSettings(array $settings, array $activeFlags = []): bool
    {
        $this->db->beginTransaction();
        
        try {
            $this->ensureDefaultOrderSettings();
            $allSettings = $this->getOrderSettings();
            
            foreach ($allSettings as $setting) {
                $key = $setting['setting_key'];
                $value = isset($settings[$key]) ? trim((string)$settings[$key]) : $setting['setting_value'];
                $isActive = isset($activeFlags[$key]) && (string)$activeFlags[$key] === '1' ? 1 : 0;
                
                $this->updateOrderSetting($key, $value, $isActive);
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Отримати налаштування order.php як асоціативний масив
     * Для використання в order.php
     */
    public function getOrderSettingsArray(): array
    {
        $stmt = $this->db->query(" 
            SELECT setting_key, setting_value
            FROM order_settings
            WHERE is_active = 1
            ORDER BY setting_key
        ");

        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }
        
        return $result;
    }
    
    /**
     * Отримати налаштування лендінгу як асоціативний масив
     */
    public function getLandingSettingsArray(): array
    {
        $settings = $this->getLandingSettings();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }
        
        return $result;
    }

    /**
     * Повернути плоский список ключів order_settings
     */
    public function getOrderSettingKeys(): array
    {
        $keys = [];

        foreach (self::ORDER_SETTINGS_DEFINITION as $group => $config) {
            foreach ($config['fields'] as $key => $fieldConfig) {
                $keys[] = [
                    'key' => $key,
                    'group' => $group,
                    'default' => $fieldConfig['default'] ?? '',
                ];
            }
        }

        return $keys;
    }

    /**
     * Гарантувати наявність дефолтних ключів у order_settings
     */
    public function ensureDefaultOrderSettings(): void
    {
        $stmt = $this->db->prepare(" 
            INSERT OR IGNORE INTO order_settings (setting_key, setting_value, setting_group, is_active)
            VALUES (?, ?, ?, 1)
        ");

        foreach ($this->getOrderSettingKeys() as $row) {
            $stmt->execute([$row['key'], $row['default'], $row['group']]);
        }
    }

    /**
     * Перевірити чи увімкнено внутрішню БД для замовлень
     */
    public function isInternalOrdersDbEnabled(): bool
    {
        $setting = $this->getOrderSetting('use_internal_orders_db');

        if (!$setting) {
            return true;
        }

        if ((int)($setting['is_active'] ?? 0) !== 1) {
            return false;
        }

        $value = strtolower(trim((string)($setting['setting_value'] ?? '')));
        if ($value === '') {
            return true;
        }

        return !in_array($value, ['0', 'false', 'off', 'no', 'disabled'], true);
    }
}
