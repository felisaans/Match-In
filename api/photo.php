<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../services/ProfileService.php';

// Hanya pengguna yang sudah login yang boleh melihat foto (mis. lewat halaman Cari Partner).
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit();
}

$targetId = (int) ($_GET['id'] ?? 0);
if ($targetId <= 0) {
    http_response_code(400);
    exit();
}

$profileService = new ProfileService($db);
$photo = $profileService->getPhoto($targetId);

if (!$photo) {
    http_response_code(404);
    exit();
}

header('Content-Type: ' . $photo['photo_mime']);
header('Cache-Control: private, max-age=300');
echo $photo['photo'];
