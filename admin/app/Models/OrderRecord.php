<?php

namespace App\Models;

use App\Config\Database;
use App\Config\DebugPDO;
use PDO;

/**
 * Модель замовлень з order.php
 */
class OrderRecord
{
    private PDO|DebugPDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Створити запис замовлення
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(" 
            INSERT INTO orders (
                customer_name,
                phone,
                product_id,
                product_title,
                product_price,
                quantity,
                total_sum,
                comment,
                payment,
                delivery,
                delivery_address,
                additional_1,
                additional_2,
                additional_3,
                additional_4,
                type_form,
                status,
                is_spam,
                client_ip,
                utm_source,
                utm_medium,
                utm_term,
                utm_content,
                utm_campaign,
                upsells_json
            ) VALUES (
                :customer_name,
                :phone,
                :product_id,
                :product_title,
                :product_price,
                :quantity,
                :total_sum,
                :comment,
                :payment,
                :delivery,
                :delivery_address,
                :additional_1,
                :additional_2,
                :additional_3,
                :additional_4,
                :type_form,
                :status,
                :is_spam,
                :client_ip,
                :utm_source,
                :utm_medium,
                :utm_term,
                :utm_content,
                :utm_campaign,
                :upsells_json
            )
        ");

        return $stmt->execute([
            ':customer_name' => (string)($data['customer_name'] ?? ''),
            ':phone' => (string)($data['phone'] ?? ''),
            ':product_id' => (string)($data['product_id'] ?? ''),
            ':product_title' => (string)($data['product_title'] ?? ''),
            ':product_price' => (float)($data['product_price'] ?? 0),
            ':quantity' => max(1, (int)($data['quantity'] ?? 1)),
            ':total_sum' => (float)($data['total_sum'] ?? 0),
            ':comment' => (string)($data['comment'] ?? ''),
            ':payment' => (string)($data['payment'] ?? ''),
            ':delivery' => (string)($data['delivery'] ?? ''),
            ':delivery_address' => (string)($data['delivery_address'] ?? ''),
            ':additional_1' => (string)($data['additional_1'] ?? ''),
            ':additional_2' => (string)($data['additional_2'] ?? ''),
            ':additional_3' => (string)($data['additional_3'] ?? ''),
            ':additional_4' => (string)($data['additional_4'] ?? ''),
            ':type_form' => (string)($data['type_form'] ?? ''),
            ':status' => (string)($data['status'] ?? 'new'),
            ':is_spam' => !empty($data['is_spam']) ? 1 : 0,
            ':client_ip' => (string)($data['client_ip'] ?? ''),
            ':utm_source' => (string)($data['utm_source'] ?? ''),
            ':utm_medium' => (string)($data['utm_medium'] ?? ''),
            ':utm_term' => (string)($data['utm_term'] ?? ''),
            ':utm_content' => (string)($data['utm_content'] ?? ''),
            ':utm_campaign' => (string)($data['utm_campaign'] ?? ''),
            ':upsells_json' => (string)($data['upsells_json'] ?? ''),
        ]);
    }

    /**
     * Отримати список замовлень
     */
    public function getAll(int $limit = 500): array
    {
        $limit = max(1, min(5000, $limit));
        $stmt = $this->db->query(" 
            SELECT * FROM orders
            ORDER BY created_at DESC, id DESC
            LIMIT {$limit}
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Отримати замовлення по ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Оновити замовлення з адмінки
     */
    public function updateById(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(" 
            UPDATE orders
            SET
                customer_name = :customer_name,
                phone = :phone,
                product_title = :product_title,
                product_price = :product_price,
                quantity = :quantity,
                total_sum = :total_sum,
                comment = :comment,
                payment = :payment,
                delivery = :delivery,
                delivery_address = :delivery_address,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        $price = (float)($data['product_price'] ?? 0);
        $quantity = max(1, (int)($data['quantity'] ?? 1));
        $total = isset($data['total_sum']) && $data['total_sum'] !== ''
            ? (float)$data['total_sum']
            : ($price * $quantity);

        return $stmt->execute([
            ':id' => $id,
            ':customer_name' => (string)($data['customer_name'] ?? ''),
            ':phone' => (string)($data['phone'] ?? ''),
            ':product_title' => (string)($data['product_title'] ?? ''),
            ':product_price' => $price,
            ':quantity' => $quantity,
            ':total_sum' => $total,
            ':comment' => (string)($data['comment'] ?? ''),
            ':payment' => (string)($data['payment'] ?? ''),
            ':delivery' => (string)($data['delivery'] ?? ''),
            ':delivery_address' => (string)($data['delivery_address'] ?? ''),
            ':status' => (string)($data['status'] ?? 'new'),
        ]);
    }

    /**
     * Отримати кількість нових замовлень (не спам)
     */
    public function getNewOrdersCount(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE status = 'new' AND is_spam = 0");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
}
