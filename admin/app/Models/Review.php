<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Модель для роботи з відгуками
 */
class Review
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Отримати всі відгуки
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM reviews 
            ORDER BY created_at DESC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Отримати тільки активні відгуки
     */
    public function getActive(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM reviews 
            WHERE is_active = 1
            ORDER BY created_at DESC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Отримати відгук за ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM reviews WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    /**
     * Створити новий відгук
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO reviews (author, city, text, rating, likes, dislikes, time_ago, is_active, photo)
            VALUES (:author, :city, :text, :rating, :likes, :dislikes, :time_ago, :is_active, :photo)
        ");
        
        return $stmt->execute([
            ':author' => $data['author'],
            ':city' => $data['city'] ?? '',
            ':text' => $data['text'],
            ':rating' => $data['rating'] ?? 5,
            ':likes' => $data['likes'] ?? 0,
            ':dislikes' => $data['dislikes'] ?? 0,
            ':time_ago' => $data['time_ago'] ?? '1 день тому',
            ':is_active' => $data['is_active'] ?? 1,
            ':photo' => $data['photo'] ?? null,
        ]);
    }
    
    /**
     * Оновити відгук
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE reviews 
            SET author = :author,
                city = :city,
                text = :text,
                rating = :rating,
                likes = :likes,
                dislikes = :dislikes,
                time_ago = :time_ago,
                is_active = :is_active,
                photo = :photo,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        
        return $stmt->execute([
            ':id' => $id,
            ':author' => $data['author'],
            ':city' => $data['city'] ?? '',
            ':text' => $data['text'],
            ':rating' => $data['rating'] ?? 5,
            ':likes' => $data['likes'] ?? 0,
            ':dislikes' => $data['dislikes'] ?? 0,
            ':time_ago' => $data['time_ago'] ?? '1 день тому',
            ':is_active' => $data['is_active'] ?? 1,
            ':photo' => $data['photo'] ?? null,
        ]);
    }
    
    /**
     * Видалити відгук
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM reviews WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Перемкнути статус активності
     */
    public function toggleActive(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE reviews 
            SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Отримати статистику відгуків
     */
    public function getStats(): array
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                AVG(rating) as avg_rating
            FROM reviews
        ");
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
