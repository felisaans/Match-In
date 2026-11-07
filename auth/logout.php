<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../config/database.php';

$auth = new AuthService($db);
$auth->logout();

header('Location: ' . url('auth/login.php'));
exit();
