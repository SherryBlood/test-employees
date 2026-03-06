<?php
session_start();
if (!isset($_SESSION['admin_id'])) exit('Access Denied');
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = $_POST['employee_id'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone_number'] ?? '');
    $salary     = trim($_POST['salary'] ?? '');
    $job        = trim($_POST['job_title'] ?? '');
    $hired      = trim($_POST['hire_date'] ?? '');

    if (empty($id) || empty($first_name) || empty($last_name) || empty($email) || empty($salary)) {
        header("Location: index.php?error=empty");
        exit;
    }

    try {
        $sql = "UPDATE employee SET 
                first_name = ?, last_name = ?, email = ?, 
                phone_number = ?, salary = ?, job_title = ?, hire_date = ? 
                WHERE employee_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$first_name, $last_name, $email, $phone, $salary, $job, $hired, $id]);

        header("Location: index.php?success=updated");
        exit;
    } catch (Exception $e) {
        header("Location: index.php?error=db");
        exit;
    }
}
