<?php

class SystemLogService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function log(?int $userId, string $action, string $description): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO system_logs (id_user, action, description) VALUES (?, ?, ?)'
        );
        return $stmt->execute([$userId, $action, $description]);
    }

    public function getRecent(int $userId, int $limit = 5): array
    {
        $limit = max(1, min($limit, 20)); // guard rail, LIMIT can't be a bound param
        $stmt = $this->db->prepare(
            "SELECT action, description, created_at FROM system_logs
             WHERE id_user = ? ORDER BY created_at DESC LIMIT {$limit}"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
