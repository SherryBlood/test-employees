<?php
session_start();
include 'db.php';

$login = trim($_POST['login'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($login) || empty($password)) {
    header("Location: login.php?error=empty");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM admin WHERE login = ?");
$stmt->execute([$login]);
$admin = $stmt->fetch();

if ($admin && password_verify($password, $admin['password_hash'])) {
    $_SESSION['admin_id'] = $admin['admin_id'];
    header("Location: index.php");
    exit;
} else {
    header("Location: login.php?error=1");
    exit;
}
