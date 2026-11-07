<?php

class EnrollmentService
{
    private PDO $db;
    private ?bool $courseSemesterExists = null;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    private function getUserSemester(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT semester FROM users WHERE id_user = ?');
        $stmt->execute([$userId]);
        return (int) ($stmt->fetchColumn() ?: 1);
    }

    private function courseSemesterTableExists(): bool
    {
        if ($this->courseSemesterExists !== null) {
            return $this->courseSemesterExists;
        }

        $stmt = $this->db->query("SHOW TABLES LIKE 'course_semester'");
        $this->courseSemesterExists = (bool) $stmt->fetchColumn();
        return $this->courseSemesterExists;
    }

    public function getUserClasses(int $userId): array
    {
        $semester = $this->getUserSemester($userId);

        if ($this->courseSemesterTableExists()) {
            $stmt = $this->db->prepare(
                'SELECT e.id_enrollment, e.id_class, c.class_name, c.semester,
                        co.id_course, co.course_name, co.lecturer
                 FROM enrollments e
                 JOIN classes c ON e.id_class = c.id_class
                 JOIN courses co ON c.id_course = co.id_course
                 JOIN course_semester cs ON co.id_course = cs.id_course AND cs.semester_number = ?
                 WHERE e.id_user = ? AND c.semester = ?
                 ORDER BY co.course_name, c.class_name'
            );
            $stmt->execute([$semester, $userId, $semester]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT e.id_enrollment, e.id_class, c.class_name, c.semester,
                        co.id_course, co.course_name, co.lecturer
                 FROM enrollments e
                 JOIN classes c ON e.id_class = c.id_class
                 JOIN courses co ON c.id_course = co.id_course
                 WHERE e.id_user = ? AND c.semester = ?
                 ORDER BY co.course_name, c.class_name'
            );
            $stmt->execute([$userId, $semester]);
        }

        return $stmt->fetchAll();
    }

    public function getAllClassesWithEnrollment(int $userId): array
    {
        $semester = $this->getUserSemester($userId);

        if ($this->courseSemesterTableExists()) {
            $stmt = $this->db->prepare(
                'SELECT c.id_class, c.class_name, c.semester,
                        co.id_course, co.course_name, co.lecturer,
                        CASE WHEN e.id_enrollment IS NOT NULL THEN 1 ELSE 0 END AS is_enrolled
                 FROM classes c
                 JOIN courses co ON c.id_course = co.id_course
                 JOIN course_semester cs ON co.id_course = cs.id_course AND cs.semester_number = ?
                 LEFT JOIN enrollments e ON e.id_class = c.id_class AND e.id_user = ?
                 WHERE c.semester = ?
                 ORDER BY co.course_name, c.class_name'
            );
            $stmt->execute([$semester, $userId, $semester]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT c.id_class, c.class_name, c.semester,
                        co.id_course, co.course_name, co.lecturer,
                        CASE WHEN e.id_enrollment IS NOT NULL THEN 1 ELSE 0 END AS is_enrolled
                 FROM classes c
                 JOIN courses co ON c.id_course = co.id_course
                 LEFT JOIN enrollments e ON e.id_class = c.id_class AND e.id_user = ?
                 WHERE c.semester = ?
                 ORDER BY co.course_name, c.class_name'
            );
            $stmt->execute([$userId, $semester]);
        }

        return $stmt->fetchAll();
    }

    public function enroll(int $userId, int $classId): array
    {
        $check = $this->db->prepare(
            'SELECT id_enrollment FROM enrollments WHERE id_user = ? AND id_class = ?'
        );
        $check->execute([$userId, $classId]);
        if ($check->fetch()) {
            return ['success' => false, 'message' => 'Kamu sudah terdaftar di kelas ini.'];
        }

        $semester = $this->getUserSemester($userId);
        if ($this->courseSemesterTableExists()) {
            $classCheck = $this->db->prepare(
                'SELECT c.id_class
                 FROM classes c
                 JOIN course_semester cs ON c.id_course = cs.id_course AND cs.semester_number = ?
                 WHERE c.id_class = ? AND c.semester = ?'
            );
            $classCheck->execute([$semester, $classId, $semester]);
        } else {
            $classCheck = $this->db->prepare(
                'SELECT id_class FROM classes WHERE id_class = ? AND semester = ?'
            );
            $classCheck->execute([$classId, $semester]);
        }

        if (!$classCheck->fetch()) {
            return ['success' => false, 'message' => 'Kelas tidak ditemukan atau tidak sesuai semester aktifmu.'];
        }

        $stmt = $this->db->prepare('INSERT INTO enrollments (id_user, id_class) VALUES (?, ?)');
        $stmt->execute([$userId, $classId]);

        return ['success' => true, 'message' => 'Berhasil mendaftar ke kelas!'];
    }

    public function unenroll(int $userId, int $classId): array
    {
        $stmt = $this->db->prepare('DELETE FROM enrollments WHERE id_user = ? AND id_class = ?');
        $stmt->execute([$userId, $classId]);

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'Kamu tidak terdaftar di kelas ini.'];
        }

        return ['success' => true, 'message' => 'Berhasil keluar dari kelas.'];
    }

    public function getClassmates(int $userId, int $classId): array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id_user, u.name, u.student_id, u.major, u.semester, (u.photo IS NOT NULL) AS has_photo
             FROM enrollments e
             JOIN users u ON e.id_user = u.id_user
             WHERE e.id_class = ? AND u.id_user != ?
             ORDER BY u.name'
        );
        $stmt->execute([$classId, $userId]);
        return $stmt->fetchAll();
    }
}
