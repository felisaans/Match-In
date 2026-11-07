<?php

require_once __DIR__ . '/SkillService.php';
require_once __DIR__ . '/PreferenceService.php';
require_once __DIR__ . '/EnrollmentService.php';
require_once __DIR__ . '/SystemLogService.php';

class MatchingService
{
    private PDO $db;
    private EnrollmentService $enrollmentService;
    private SystemLogService $logService;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->enrollmentService = new EnrollmentService($db);
        $this->logService = new SystemLogService($db);
    }

    public function getLastRecommendation(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT grr.final_score, grr.created_at,
                    u.id_user AS partner_id, u.name, u.student_id, (u.photo IS NOT NULL) AS has_photo
             FROM group_recommendation_result grr
             JOIN users u ON grr.partner_id = u.id_user
             WHERE grr.id_user = ? AND grr.ranking_position = 1
             ORDER BY grr.created_at DESC
             LIMIT 1'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getCriteria(): array
    {
        $stmt = $this->db->query(
            'SELECT id_criteria, criteria_name, criteria_code, weight FROM criteria ORDER BY criteria_code'
        );
        return $stmt->fetchAll();
    }

    public function buildMatchingData(int $userId, int $classId): array
    {
        if (!$this->isClassAccessibleToUser($userId, $classId)) {
            return ['success' => false, 'message' => 'Kamu belum terdaftar di kelas ini atau kelas tidak sesuai semester aktif.'];
        }

        $userPreference = $this->getUserPreference($userId, $classId);
        if (!$userPreference) {
            return ['success' => false, 'message' => 'Lengkapi preferensi untuk kelas ini dulu ya!'];
        }

        $classmates = $this->enrollmentService->getClassmates($userId, $classId);
        if (empty($classmates)) {
            return ['success' => false, 'message' => 'Belum ada teman sekelas lain. Coba kelas lain dulu!'];
        }

        $criteria = $this->getCriteria();
        $alternatives = [];
        $comparison = [];

        foreach ($classmates as $mate) {
            $mateId = (int) $mate['id_user'];
            $mateSkills = $this->getUserSkills($mateId);
            $matePreference = $this->getUserPreference($mateId, $classId);

            $scores = [
                'C1' => $this->calcSkillCompatibility($userPreference, $mateSkills),
                'C2' => $this->calcPreferenceCompatibility($userPreference, $mateSkills),
                'C3' => $this->calcWorkStyleCompatibility($userPreference, $matePreference),
                'C4' => $this->calcExperience($mateSkills, (int) $mate['semester']),
            ];

            $alternatives[] = [
                'id' => $mateId,
                'name' => $mate['name'],
                'npm' => $mate['student_id'],
                'major' => $mate['major'],
                'scores' => $scores,
            ];

            $comparison[] = [
                'id' => $mateId,
                'name' => $mate['name'],
                'npm' => $mate['student_id'],
                'major' => $mate['major'],
                'skills_summary' => SkillService::formatSkillsSummary($mateSkills),
                'work_style' => $matePreference
                    ? PreferenceService::workStyleLabel($matePreference['work_style'])
                    : 'Belum diisi',
                'has_photo' => (bool) $mate['has_photo'],
                'photo_url' => $mate['has_photo'] ? url('api/photo.php?id=' . $mateId) : null,
            ];
        }

        $classInfo = $this->getClassInfo($classId);
        $sawData = $this->calculateSawRanking($alternatives, $criteria);
        $this->saveRecommendationResults($userId, $classId, $sawData['ranking']);
        $this->logService->log(
            $userId,
            'calculate_saw',
            sprintf('Menghitung SAW untuk kelas %d, %d kandidat.', $classId, count($sawData['ranking']))
        );

        return [
            'success' => true,
            'class' => $classInfo,
            'criteria' => array_map(fn($c) => [
                'code' => $c['criteria_code'],
                'name' => $c['criteria_name'],
                'weight' => (float) $c['weight'],
            ], $criteria),
            'comparison' => $comparison,
            'matrix' => array_map(fn($alt) => [
                'id' => $alt['id'],
                'name' => $alt['name'],
                'npm' => $alt['npm'],
                'scores' => $alt['scores'],
            ], $alternatives),
            'normalization' => $sawData['normalization'],
            'weighted' => $sawData['weighted'],
            'ranking' => $sawData['ranking'],
            'top3' => $sawData['top3'],
        ];
    }

    private function getClassInfo(int $classId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.id_class, c.class_name, c.semester, co.course_name, co.lecturer
             FROM classes c
             JOIN courses co ON c.id_course = co.id_course
             WHERE c.id_class = ?'
        );
        $stmt->execute([$classId]);
        return $stmt->fetch() ?: null;
    }

    private function isClassAccessibleToUser(int $userId, int $classId): bool
    {
        $semester = $this->getUserSemester($userId);

        $stmt = $this->db->prepare(
            'SELECT 1
             FROM enrollments e
             JOIN classes c ON e.id_class = c.id_class
             WHERE e.id_user = ? AND e.id_class = ? AND c.semester = ?'
        );
        $stmt->execute([$userId, $classId, $semester]);
        return (bool) $stmt->fetchColumn();
    }

    private function getUserSemester(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT semester FROM users WHERE id_user = ?');
        $stmt->execute([$userId]);
        return (int) ($stmt->fetchColumn() ?: 1);
    }

    private function calculateSawRanking(array $alternatives, array $criteria): array
    {
        $maxValues = [];
        foreach ($criteria as $criteriaItem) {
            $code = $criteriaItem['criteria_code'];
            $scores = array_map(fn($alt) => $alt['scores'][$code] ?? 0, $alternatives);
            $scores[] = 0.0001;
            $maxValues[$code] = max($scores);
        }

        $normalizationRows = [];
        $weightedRows = [];
        $processed = [];

        foreach ($alternatives as $alt) {
            $normalizedScores = [];
            $weightedScores = [];
            $totalScore = 0.0;

            foreach ($criteria as $criteriaItem) {
                $code = $criteriaItem['criteria_code'];
                $weight = (float) $criteriaItem['weight'];
                $raw = $alt['scores'][$code] ?? 0;
                $normalized = $maxValues[$code] > 0 ? $raw / $maxValues[$code] : 0;
                $weighted = $normalized * $weight;

                $normalizedScores[$code] = round($normalized, 4);
                $weightedScores[$code] = round($weighted, 4);
                $totalScore += $weighted;
            }

            $normalizationRows[] = [
                'id' => $alt['id'],
                'name' => $alt['name'],
                'npm' => $alt['npm'],
                'normalized' => $normalizedScores,
            ];

            $weightedRows[] = [
                'id' => $alt['id'],
                'name' => $alt['name'],
                'npm' => $alt['npm'],
                'weighted' => $weightedScores,
                'totalScore' => round($totalScore, 4),
            ];

            $processed[] = array_merge($alt, [
                'normalized' => $normalizedScores,
                'weighted' => $weightedScores,
                'totalScore' => round($totalScore, 4),
                'percentage' => round($totalScore * 100, 2),
            ]);
        }

        usort($processed, fn($a, $b) => $b['totalScore'] <=> $a['totalScore']);

        foreach ($processed as $index => &$item) {
            $item['rank'] = $index + 1;
        }
        unset($item);

        return [
            'normalization' => [
                'maxValues' => array_map(fn($v) => round($v, 4), $maxValues),
                'rows' => $normalizationRows,
            ],
            'weighted' => $weightedRows,
            'ranking' => $processed,
            'top3' => array_slice($processed, 0, 3),
        ];
    }

    private function saveRecommendationResults(int $userId, int $classId, array $ranking): void
    {
        $delete = $this->db->prepare(
            'DELETE FROM group_recommendation_result WHERE id_user = ? AND id_class = ?'
        );
        $delete->execute([$userId, $classId]);

        $insert = $this->db->prepare(
            'INSERT INTO group_recommendation_result (id_user, id_class, partner_id, final_score, ranking_position)
             VALUES (?, ?, ?, ?, ?)'
        );
        foreach ($ranking as $item) {
            $insert->execute([
                $userId,
                $classId,
                $item['id'],
                $item['totalScore'],
                $item['rank'],
            ]);
        }
    }

    private function getUserSkills(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT so.skill_name, s.level
             FROM skills s
             JOIN skill_options so ON s.id_skill_option = so.id_skill_option
             WHERE s.id_user = ?'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    private function getUserPreference(int $userId, int $classId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT preferred_skill, work_style FROM preferences WHERE id_user = ? AND id_class = ?'
        );
        $stmt->execute([$userId, $classId]);
        $pref = $stmt->fetch();
        return $pref ?: null;
    }

    private function calcSkillCompatibility(array $userPref, array $mateSkills): float
    {
        if (empty($mateSkills)) {
            return 1.0;
        }

        $preferred = strtolower(trim($userPref['preferred_skill']));
        $relevantLevels = [];

        foreach ($mateSkills as $skill) {
            $skillName = strtolower(trim($skill['skill_name']));
            if ($this->skillsMatch($preferred, $skillName)) {
                $relevantLevels[] = SkillService::levelNumeric($skill['level']);
            }
        }

        if (empty($relevantLevels)) {
            $allLevels = array_map(fn($s) => SkillService::levelNumeric($s['level']), $mateSkills);
            return round(array_sum($allLevels) / count($allLevels), 2);
        }

        return round(array_sum($relevantLevels) / count($relevantLevels), 2);
    }

    private function calcPreferenceCompatibility(array $userPref, array $mateSkills): float
    {
        $preferred = strtolower(trim($userPref['preferred_skill']));
        $bestScore = 1.0;

        foreach ($mateSkills as $skill) {
            $skillName = strtolower(trim($skill['skill_name']));
            if ($preferred === $skillName) {
                return 3.0;
            }
            if ($this->skillsPartialMatch($preferred, $skillName)) {
                $bestScore = max($bestScore, 2.0);
            }
        }

        return $bestScore;
    }

    private function calcWorkStyleCompatibility(array $userPref, ?array $matePref): float
    {
        if (!$matePref) {
            return 2.0;
        }

        $userStyle = $userPref['work_style'];
        $mateStyle = $matePref['work_style'];

        if ($userStyle === $mateStyle) {
            return 3.0;
        }

        if ($userStyle === 'fleksibel' || $mateStyle === 'fleksibel') {
            return 2.0;
        }

        if (
            ($userStyle === 'tim' && $mateStyle === 'individu') ||
            ($userStyle === 'individu' && $mateStyle === 'tim')
        ) {
            return 1.0;
        }

        return 2.0;
    }

    private function calcExperience(array $mateSkills, int $semester): float
    {
        if (empty($mateSkills)) {
            return (float) max(1, $semester);
        }

        $score = 0;
        foreach ($mateSkills as $skill) {
            $score += match ($skill['level']) {
                'expert' => 3,
                'intermediate' => 2,
                default => 1,
            };
        }

        return (float) $score;
    }

    private function skillsMatch(string $preferred, string $skillName): bool
    {
        return $preferred === $skillName
            || str_contains($skillName, $preferred)
            || str_contains($preferred, $skillName);
    }

    private function skillsPartialMatch(string $preferred, string $skillName): bool
    {
        $preferredWords = preg_split('/[\s\/\-]+/', $preferred);
        $skillWords = preg_split('/[\s\/\-]+/', $skillName);

        foreach ($preferredWords as $pw) {
            if (strlen($pw) < 3) continue;
            foreach ($skillWords as $sw) {
                if (str_contains($sw, $pw) || str_contains($pw, $sw)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function updateSawValues(int $userId): void
    {
        $skills = $this->getUserSkills($userId);
        $user = $this->db->prepare('SELECT semester FROM users WHERE id_user = ?');
        $user->execute([$userId]);
        $userData = $user->fetch();

        $values = [
            'C1' => $this->averageSkillLevel($skills),
            'C2' => min(3.0, $this->averageSkillLevel($skills) + 0.5),
            'C3' => 2.0,
            'C4' => $this->calcExperience($skills, (int) ($userData['semester'] ?? 1)),
        ];

        $criteria = $this->getCriteria();
        foreach ($criteria as $c) {
            $code = $c['criteria_code'];
            $val = $values[$code] ?? 0;

            $stmt = $this->db->prepare(
                'INSERT INTO saw_values (id_user, id_criteria, value)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE value = VALUES(value)'
            );
            $stmt->execute([$userId, $c['id_criteria'], $val]);
        }
    }

    private function averageSkillLevel(array $skills): float
    {
        if (empty($skills)) return 1.0;
        $levels = array_map(fn($s) => SkillService::levelNumeric($s['level']), $skills);
        return round(array_sum($levels) / count($levels), 2);
    }
}
