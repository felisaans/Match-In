<?php

class ProfileService
{
    private PDO $db;

    public const VALID_MAJORS = ['Teknik Informatika', 'Ilmu Komputer', 'Teknik Komputer'];
    public const VALID_SEMESTERS = [2, 4, 6];
    public const VALID_PHOTO_MIMES = ['image/jpeg', 'image/png', 'image/webp'];
    public const MAX_PHOTO_SIZE = 2 * 1024 * 1024; // 2MB

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getById(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT id_user, name, student_id, email, major, semester, (photo IS NOT NULL) AS has_photo FROM users WHERE id_user = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function update(int $userId, array $data): array
    {
        $errors = [];

        if (empty(trim($data['name'] ?? ''))) {
            $errors[] = 'Nama lengkap wajib diisi.';
        }
        if (!in_array(trim($data['major'] ?? ''), self::VALID_MAJORS, true)) {
            $errors[] = 'MatchIn khusus untuk mahasiswa Teknik Informatika, Ilmu Komputer, atau Teknik Komputer.';
        }

        $semester = (int) ($data['semester'] ?? 0);
        if (!in_array($semester, self::VALID_SEMESTERS, true)) {
            $errors[] = 'Semester aktif saat ini hanya 2, 4, atau 6.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $stmt = $this->db->prepare(
            'UPDATE users SET name = ?, major = ?, semester = ? WHERE id_user = ?'
        );
        $stmt->execute([
            trim($data['name']),
            trim($data['major']),
            $semester,
            $userId,
        ]);

        $_SESSION['user_name'] = trim($data['name']);

        return ['success' => true, 'message' => 'Profil berhasil diperbarui!'];
    }

    public function updatePhoto(int $userId, array $file): array
    {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => false, 'errors' => ['Pilih file foto terlebih dahulu.']];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'errors' => ['Gagal mengunggah foto. Coba lagi ya!']];
        }
        if ($file['size'] > self::MAX_PHOTO_SIZE) {
            return ['success' => false, 'errors' => ['Ukuran foto maksimal 2MB.']];
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, self::VALID_PHOTO_MIMES, true)) {
            return ['success' => false, 'errors' => ['Format foto harus JPG, PNG, atau WEBP.']];
        }

        $binary = file_get_contents($file['tmp_name']);
        if ($binary === false) {
            return ['success' => false, 'errors' => ['Gagal membaca file foto.']];
        }

        $stmt = $this->db->prepare('UPDATE users SET photo = ?, photo_mime = ? WHERE id_user = ?');
        $stmt->execute([$binary, $mime, $userId]);

        return ['success' => true, 'message' => 'Foto profil berhasil diperbarui!'];
    }

    public function getPhoto(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT photo, photo_mime FROM users WHERE id_user = ? AND photo IS NOT NULL');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getDashboardStats(int $userId): array
    {
        $skillCount = $this->db->prepare('SELECT COUNT(*) FROM skills WHERE id_user = ?');
        $skillCount->execute([$userId]);
        $skills = (int) $skillCount->fetchColumn();

        $classCount = $this->db->prepare('SELECT COUNT(*) FROM enrollments WHERE id_user = ?');
        $classCount->execute([$userId]);
        $classes = (int) $classCount->fetchColumn();

        $prefCount = $this->db->prepare('SELECT COUNT(*) FROM preferences WHERE id_user = ?');
        $prefCount->execute([$userId]);
        $preferences = (int) $prefCount->fetchColumn();

        return [
            'skills' => $skills,
            'classes' => $classes,
            'preferences' => $preferences,
            'profile_complete' => $skills > 0 && $preferences > 0,
        ];
    }
}
