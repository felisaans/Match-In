<?php

require_once __DIR__ . '/SystemLogService.php';

class SkillService
{
    private PDO $db;
    private SystemLogService $logService;

    private const VALID_LEVELS = ['basic', 'intermediate', 'expert'];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->logService = new SystemLogService($db);
    }

    /**
     * Seluruh katalog skill (untuk dropdown "Tambah Skill" & preferensi),
     * dikelompokkan per kategori.
     *
     * @return array<string, array<int, string>> [category => [id_skill_option => skill_name]]
     */
    public function getOptionsGrouped(): array
    {
        $stmt = $this->db->query(
            'SELECT id_skill_option, skill_name, category FROM skill_options ORDER BY category, skill_name'
        );

        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $category = $row['category'] ?: 'Lainnya';
            $grouped[$category][$row['id_skill_option']] = $row['skill_name'];
        }

        return $grouped;
    }

    /**
     * Skill milik seorang mahasiswa, sudah di-join ke katalog supaya
     * tetap punya kolom `skill_name` seperti sebelumnya.
     */
    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT s.id_skill, s.id_skill_option, so.skill_name, so.category, s.level
             FROM skills s
             JOIN skill_options so ON s.id_skill_option = so.id_skill_option
             WHERE s.id_user = ?
             ORDER BY so.skill_name'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function add(int $userId, int $skillOptionId, string $level): array
    {
        if (!in_array($level, self::VALID_LEVELS, true)) {
            return ['success' => false, 'message' => 'Level skill tidak valid.'];
        }

        $option = $this->db->prepare('SELECT skill_name FROM skill_options WHERE id_skill_option = ?');
        $option->execute([$skillOptionId]);
        $skillName = $option->fetchColumn();
        if (!$skillName) {
            return ['success' => false, 'message' => 'Pilihan skill tidak ditemukan.'];
        }

        $check = $this->db->prepare('SELECT id_skill FROM skills WHERE id_user = ? AND id_skill_option = ?');
        $check->execute([$userId, $skillOptionId]);
        if ($check->fetch()) {
            return ['success' => false, 'message' => 'Skill ini sudah kamu tambahkan.'];
        }

        $stmt = $this->db->prepare(
            'INSERT INTO skills (id_user, id_skill_option, level) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $skillOptionId, $level]);
        $this->logService->log($userId, 'add_skill', sprintf('Menambahkan skill %s (%s).', $skillName, $level));

        return ['success' => true, 'message' => 'Skill berhasil ditambahkan!'];
    }

    public function updateLevel(int $userId, int $skillId, string $level): array
    {
        if (!in_array($level, self::VALID_LEVELS, true)) {
            return ['success' => false, 'message' => 'Level skill tidak valid.'];
        }

        $stmt = $this->db->prepare(
            'UPDATE skills SET level = ? WHERE id_skill = ? AND id_user = ?'
        );
        $stmt->execute([$level, $skillId, $userId]);

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'Skill tidak ditemukan.'];
        }

        $this->logService->log($userId, 'update_skill', sprintf('Memperbarui level skill id %d menjadi %s.', $skillId, $level));
        return ['success' => true, 'message' => 'Skill berhasil diperbarui!'];
    }

    public function delete(int $userId, int $skillId): array
    {
        $stmt = $this->db->prepare('DELETE FROM skills WHERE id_skill = ? AND id_user = ?');
        $stmt->execute([$skillId, $userId]);

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'Skill tidak ditemukan.'];
        }

        $this->logService->log($userId, 'delete_skill', sprintf('Menghapus skill id %d.', $skillId));
        return ['success' => true, 'message' => 'Skill berhasil dihapus.'];
    }

    public static function levelLabel(string $level): string
    {
        return match ($level) {
            'basic' => 'Dasar',
            'intermediate' => 'Menengah',
            'expert' => 'Mahir',
            default => $level,
        };
    }

    public static function levelBadgeClass(string $level): string
    {
        return match ($level) {
            'basic' => 'badge-basic',
            'intermediate' => 'badge-intermediate',
            'expert' => 'badge-expert',
            default => 'badge',
        };
    }

    public static function levelNumeric(string $level): int
    {
        return match ($level) {
            'basic' => 1,
            'intermediate' => 2,
            'expert' => 3,
            default => 1,
        };
    }

    public static function formatSkillsSummary(array $skills): string
    {
        if (empty($skills)) {
            return 'Belum ada skill';
        }

        $parts = [];
        foreach ($skills as $skill) {
            $parts[] = $skill['skill_name'] . ' (' . self::levelLabel($skill['level']) . ')';
        }

        return implode(', ', $parts);
    }
}
