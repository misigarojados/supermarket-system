<?php
session_start();

$host = 'localhost';
$dbname = 'supermarket_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function requireLogin()
{
    global $pdo;
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || $user['status'] !== 'active') {
        session_destroy();
        header('Location: login.php?error=account_disabled');
        exit();
    }
}

function requireAdmin()
{
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: dashboard.php');
        exit();
    }
}

function requireManager()
{
    requireLogin();
    if (!in_array($_SESSION['user_role'], ['admin', 'manager'])) {
        header('Location: dashboard.php');
        exit();
    }
}

function timeAgo($datetime)
{
    if (empty($datetime)) return 'Never';
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return $diff . ' seconds ago';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 2592000) return floor($diff / 86400) . ' days ago';
    return date('M d, Y', $time);
}
