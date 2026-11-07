<?php

require_once __DIR__ . '/SystemLogService.php';

class AuthService
{
    private PDO $db;
    private SystemLogService $logService;

    public const VALID_MAJORS = ['Teknik Informatika', 'Ilmu Komputer', 'Teknik Komputer'];
    public const VALID_SEMESTERS = [2, 4, 6];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->logService = new SystemLogService($db);
    }

    public function register(array $data): array
    {
        $errors = $this->validateRegister($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $stmt = $this->db->prepare(
            'INSERT INTO users (name, student_id, email, password, major, semester)
             VALUES (?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            trim($data['name']),
            trim($data['student_id']),
            trim($data['email']),
            password_hash($data['password'], PASSWORD_DEFAULT),
            trim($data['major']),
            (int) $data['semester'],
        ]);

        return ['success' => true, 'message' => 'Yeay, registrasi berhasil! Yuk masuk ke akunmu.'];
    }

    public function login(string $email, string $password): array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([trim($email)]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Email atau kata sandi salah. Coba cek lagi ya!'];
        }

        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        $this->logService->log($user['id_user'], 'login', sprintf('User %s masuk ke sistem.', $user['email']));

        return ['success' => true, 'user' => $user];
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
    }

    private function validateRegister(array $data): array
    {
        $errors = [];

        if (empty(trim($data['name'] ?? ''))) {
            $errors[] = 'Nama lengkap wajib diisi.';
        }
        if (empty(trim($data['student_id'] ?? ''))) {
            $errors[] = 'NPM wajib diisi.';
        }
        if (empty(trim($data['email'] ?? '')) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email tidak valid.';
        }
        if (!in_array(trim($data['major'] ?? ''), self::VALID_MAJORS, true)) {
            $errors[] = 'MatchIn khusus untuk mahasiswa Teknik Informatika, Ilmu Komputer, atau Teknik Komputer.';
        }
        if (empty($data['password']) || strlen($data['password']) < 6) {
            $errors[] = 'Kata sandi minimal 6 karakter.';
        }
        if (($data['password'] ?? '') !== ($data['password_confirm'] ?? '')) {
            $errors[] = 'Konfirmasi kata sandi tidak cocok.';
        }

        $semester = (int) ($data['semester'] ?? 0);
        if (!in_array($semester, self::VALID_SEMESTERS, true)) {
            $errors[] = 'Semester aktif saat ini hanya 2, 4, atau 6.';
        }

        if ($this->emailExists($data['email'] ?? '')) {
            $errors[] = 'Email sudah terdaftar.';
        }
        if ($this->studentIdExists($data['student_id'] ?? '')) {
            $errors[] = 'NPM sudah terdaftar.';
        }

        return $errors;
    }

    private function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT id_user FROM users WHERE email = ?');
        $stmt->execute([trim($email)]);
        return (bool) $stmt->fetch();
    }

    private function studentIdExists(string $studentId): bool
    {
        $stmt = $this->db->prepare('SELECT id_user FROM users WHERE student_id = ?');
        $stmt->execute([trim($studentId)]);
        return (bool) $stmt->fetch();
    }
}
