<?php

class PreferenceService
{
    private PDO $db;

    private const VALID_STYLES = ['tim', 'individu', 'fleksibel'];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getByUserAndClass(int $userId, int $classId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM preferences WHERE id_user = ? AND id_class = ?'
        );
        $stmt->execute([$userId, $classId]);
        $pref = $stmt->fetch();
        return $pref ?: null;
    }

    public function getAllByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.class_name, co.course_name
             FROM preferences p
             JOIN classes c ON p.id_class = c.id_class
             JOIN courses co ON c.id_course = co.id_course
             WHERE p.id_user = ?
             ORDER BY co.course_name, c.class_name'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function save(int $userId, int $classId, string $preferredSkill, string $workStyle): array
    {
        $preferredSkill = trim($preferredSkill);
        if (empty($preferredSkill)) {
            return ['success' => false, 'message' => 'Skill yang dicari wajib diisi.'];
        }
        if (!in_array($workStyle, self::VALID_STYLES, true)) {
            return ['success' => false, 'message' => 'Gaya kerja tidak valid.'];
        }

        $existing = $this->getByUserAndClass($userId, $classId);
        if ($existing) {
            $stmt = $this->db->prepare(
                'UPDATE preferences SET preferred_skill = ?, work_style = ? WHERE id_preference = ? AND id_user = ?'
            );
            $stmt->execute([$preferredSkill, $workStyle, $existing['id_preference'], $userId]);
        } else {
            $stmt = $this->db->prepare(
                'INSERT INTO preferences (id_user, id_class, preferred_skill, work_style) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$userId, $classId, $preferredSkill, $workStyle]);
        }

        return ['success' => true, 'message' => 'Preferensi berhasil disimpan!'];
    }

    public static function workStyleLabel(string $style): string
    {
        return match ($style) {
            'tim' => 'Tim',
            'individu' => 'Individu',
            'fleksibel' => 'Fleksibel',
            default => $style,
        };
    }
}
