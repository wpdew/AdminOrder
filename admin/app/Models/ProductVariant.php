<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Модель для роботи з варіантами продукту
 */
class ProductVariant
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Отримати всі варіанти
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM product_variants 
            ORDER BY sort_order ASC, id ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Отримати тільки активні варіанти
     */
    public function getActive(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM product_variants 
            WHERE is_active = 1 
            ORDER BY sort_order ASC, id ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Отримати варіант за ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM product_variants 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }
    
    /**
     * Створити новий варіант
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO product_variants (name, quantity, price_per_unit, sort_order, is_active) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['quantity'],
            $data['price_per_unit'],
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1
        ]);
    }
    
    /**
     * Оновити варіант
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE product_variants 
            SET name = ?, quantity = ?, price_per_unit = ?, sort_order = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['quantity'],
            $data['price_per_unit'],
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
            $id
        ]);
    }
    
    /**
     * Видалити варіант
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM product_variants WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Перемкнути активність варіанту
     */
    public function toggleActive(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE product_variants 
            SET is_active = NOT is_active, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }
}
