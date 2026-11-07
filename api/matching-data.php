<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../services/MatchingService.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesi habis. Silakan masuk kembali ya!']);
    exit();
}

$classId = (int) ($_GET['id_class'] ?? 0);
if ($classId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID kelas tidak valid.']);
    exit();
}

$userId = (int) $_SESSION['user_id'];
$matchingService = new MatchingService($db);
$result = $matchingService->buildMatchingData($userId, $classId);

if (!$result['success']) {
    http_response_code(422);
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
