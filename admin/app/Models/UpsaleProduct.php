<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Модель для роботи з upsale продуктами
 */
class UpsaleProduct
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Отримати всі продукти
     */
    public function getAll(bool $activeOnly = false): array
    {
        $query = "SELECT * FROM upsale_products";
        
        if ($activeOnly) {
            $query .= " WHERE is_active = 1";
        }
        
        $query .= " ORDER BY sort_order, id";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Отримати продукт за ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM upsale_products 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Створити новий продукт
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO upsale_products 
            (name, old_price, new_price, image, is_active, sort_order) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['name'],
            $data['old_price'],
            $data['new_price'],
            $data['image'],
            $data['is_active'] ?? 1,
            $data['sort_order'] ?? 0
        ]);
        
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Оновити продукт
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE upsale_products 
            SET name = ?, old_price = ?, new_price = ?, image = ?, 
                is_active = ?, sort_order = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['old_price'],
            $data['new_price'],
            $data['image'],
            $data['is_active'] ?? 1,
            $data['sort_order'] ?? 0,
            $id
        ]);
    }
    
    /**
     * Видалити продукт
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM upsale_products WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Перемикнути активність продукту
     */
    public function toggleActive(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE upsale_products 
            SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }
    
    /**
     * Оновити порядок сортування
     */
    public function updateSortOrder(int $id, int $sortOrder): bool
    {
        $stmt = $this->db->prepare("
            UPDATE upsale_products 
            SET sort_order = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$sortOrder, $id]);
    }
}
