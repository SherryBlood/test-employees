<?php
session_start();
if (!isset($_SESSION['admin_id'])) exit('Access Denied');

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone_number'] ?? '');
    $salary     = trim($_POST['salary'] ?? '');
    $job        = trim($_POST['job_title'] ?? '');
    $hired      = trim($_POST['hire_date'] ?? '');

    if (empty($first_name) || empty($last_name) || empty($email) || empty($salary) || empty($job) || empty($hired)) {
        header("Location: index.php?error=empty");
        exit;
    }

    if (preg_match('/[0-9]/', $first_name) || preg_match('/[0-9]/', $last_name)) {
        header("Location: index.php?error=names");
        exit;
    }

    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    if (!empty($phone) && (strlen($cleanPhone) < 10 || strlen($cleanPhone) > 15)) {
        header("Location: index.php?error=phone");
        exit;
    }

    try {
        $sql = "INSERT INTO employee (first_name, last_name, email, phone_number, salary, job_title, hire_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$first_name, $last_name, $email, $phone, $salary, $job, $hired]);

        header("Location: index.php?success=1");
        exit;
    } catch (Exception $e) {
        header("Location: index.php?error=db");
        exit;
    }
}
