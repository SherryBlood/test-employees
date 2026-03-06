<?php
session_start();
if (!isset($_SESSION['admin_id'])) exit('Access Denied');
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ids'])) {
    $ids = json_decode($_POST['ids']);

    if (!empty($ids)) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "DELETE FROM employee WHERE employee_id IN ($placeholders)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids);

        echo json_encode(['success' => true]);
        exit;
    }
}
echo json_encode(['success' => false]);
