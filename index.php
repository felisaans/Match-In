<?php
session_start();
require_once __DIR__ . '/config/app.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . url('pages/dashboard.php'));
} else {
    header('Location: ' . url('auth/login.php'));
}
exit();
