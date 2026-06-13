<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class BlockedIp
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Отримати всі заблоковані IP
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM blocked_ips 
            ORDER BY created_at DESC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Отримати тільки активні заблоковані IP (для перевірки)
     */
    public function getActive(): array
    {
        $stmt = $this->db->query("
            SELECT ip_address FROM blocked_ips 
            WHERE is_active = 1
            ORDER BY id
        ");
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Отримати IP по ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM blocked_ips 
            WHERE id = :id
        ");
        
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    /**
     * Перевірити чи IP заблокований
     */
    public function isBlocked(string $ip): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM blocked_ips 
            WHERE ip_address = :ip AND is_active = 1
        ");
        
        $stmt->execute([':ip' => $ip]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Створити новий запис
     */
    public function create(array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO blocked_ips (ip_address, reason, is_active)
                VALUES (:ip_address, :reason, :is_active)
            ");
            
            return $stmt->execute([
                ':ip_address' => $data['ip_address'],
                ':reason' => $data['reason'] ?? '',
                ':is_active' => $data['is_active'] ?? 1
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Оновити IP
     */
    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE blocked_ips 
                SET ip_address = :ip_address,
                    reason = :reason,
                    is_active = :is_active,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            
            return $stmt->execute([
                ':id' => $id,
                ':ip_address' => $data['ip_address'],
                ':reason' => $data['reason'] ?? '',
                ':is_active' => $data['is_active'] ?? 1
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Видалити IP
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM blocked_ips WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Перемикач активності
     */
    public function toggleActive(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE blocked_ips 
            SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Масове додавання IP (з текстового списку)
     */
    public function bulkCreate(string $ipList, string $reason = ''): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $ipList)));
        $added = 0;
        $skipped = 0;
        $errors = [];

        foreach ($lines as $ip) {
            // Перевірка валідності IP
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                $errors[] = "Невалідний IP: $ip";
                $skipped++;
                continue;
            }

            if ($this->create(['ip_address' => $ip, 'reason' => $reason])) {
                $added++;
            } else {
                $skipped++;
                $errors[] = "Не вдалося додати IP: $ip (можливо вже існує)";
            }
        }

        return [
            'added' => $added,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }
}
