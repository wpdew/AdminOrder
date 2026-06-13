<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Модель для роботи з характеристиками продукту
 */
class Specification
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Отримати всі характеристики
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM specifications 
            ORDER BY sort_order ASC, id ASC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Отримати тільки активні характеристики
     */
    public function getActive(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM specifications 
            WHERE is_active = 1
            ORDER BY sort_order ASC, id ASC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Отримати характеристику за ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM specifications WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    /**
     * Створити нову характеристику
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO specifications (label, value, sort_order, is_active)
            VALUES (:label, :value, :sort_order, :is_active)
        ");
        
        return $stmt->execute([
            ':label' => $data['label'],
            ':value' => $data['value'],
            ':sort_order' => $data['sort_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1,
        ]);
    }
    
    /**
     * Оновити характеристику
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE specifications 
            SET label = :label,
                value = :value,
                sort_order = :sort_order,
                is_active = :is_active,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        
        return $stmt->execute([
            ':id' => $id,
            ':label' => $data['label'],
            ':value' => $data['value'],
            ':sort_order' => $data['sort_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1,
        ]);
    }
    
    /**
     * Видалити характеристику
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM specifications WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Перемкнути статус активності характеристики
     */
    public function toggleActive(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE specifications 
            SET is_active = (CASE WHEN is_active = 1 THEN 0 ELSE 1 END),
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Оновити порядок сортування характеристик
     */
    public function updateSortOrder(array $items): bool
    {
        $this->db->beginTransaction();
        
        try {
            $stmt = $this->db->prepare("
                UPDATE specifications 
                SET sort_order = :sort_order,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            
            foreach ($items as $position => $id) {
                $stmt->execute([
                    ':id' => $id,
                    ':sort_order' => $position
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
