<?php
require 'vendor/autoload.php';
include 'db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        $sql = "INSERT INTO employee (first_name, last_name, email, phone_number, job_title, salary, hire_date) 
                VALUES (:fname, :lname, :email, :phone, :job, :salary, :hdate)
                ON DUPLICATE KEY UPDATE 
                    first_name = VALUES(first_name),
                    last_name = VALUES(last_name),
                    phone_number = VALUES(phone_number),
                    job_title = VALUES(job_title),
                    salary = VALUES(salary),
                    hire_date = VALUES(hire_date)";

        $stmt = $pdo->prepare($sql);

        for ($i = 1; $i < count($data); $i++) {
            $row = $data[$i];

            if (empty($row[0])) continue;

            $phone = (string)$row[3];

            $hire_date = $row[6];
            if (is_numeric($hire_date)) {
                $hire_date = Date::excelToDateTimeObject($hire_date)->format('Y-m-d');
            }

            $stmt->execute([
                ':fname'  => $row[0],
                ':lname'  => $row[1],
                ':email'  => $row[2],
                ':phone'  => $phone,
                ':job'    => $row[4],
                ':salary' => $row[5],
                ':hdate'  => $hire_date
            ]);
        }

        header("Location: index.php?import=success");
        exit;
    } catch (Exception $e) {
        die("Error loading file: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit;
}
