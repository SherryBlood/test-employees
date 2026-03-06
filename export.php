<?php
require 'vendor/autoload.php';
include 'db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

$selected_jobs = isset($_GET['jobs']) ? (array)$_GET['jobs'] : [];
$sort = $_GET['sort'] ?? 'newest';

switch ($sort) {
    case 'oldest':
        $order_query = "hire_date ASC";
        break;
    case 'salary_desc':
        $order_query = "salary DESC";
        break;
    case 'salary_asc':
        $order_query = "salary ASC";
        break;
    default:
        $order_query = "hire_date DESC";
        break;
}

$where_sql = "";
$params = [];
if (!empty($selected_jobs)) {
    $placeholders = str_repeat('?,', count($selected_jobs) - 1) . '?';
    $where_sql = " WHERE job_title IN ($placeholders) ";
    $params = $selected_jobs;
}

$sql = "SELECT first_name, last_name, email, phone_number, job_title, salary, hire_date FROM employee $where_sql ORDER BY $order_query";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$headers = ['First Name', 'Last Name', 'Email', 'Phone', 'Job Title', 'Salary', 'Hire Date'];
$sheet->fromArray($headers, NULL, 'A1');

if (!empty($employees)) {
    $rowNumber = 2;
    foreach ($employees as $employee) {
        $sheet->setCellValue('A' . $rowNumber, $employee['first_name']);
        $sheet->setCellValue('B' . $rowNumber, $employee['last_name']);
        $sheet->setCellValue('C' . $rowNumber, $employee['email']);

        $sheet->setCellValueExplicit(
            'D' . $rowNumber,
            $employee['phone_number'],
            DataType::TYPE_STRING
        );

        $sheet->setCellValue('E' . $rowNumber, $employee['job_title']);
        $sheet->setCellValue('F' . $rowNumber, $employee['salary']);
        $sheet->setCellValue('G' . $rowNumber, $employee['hire_date']);

        $rowNumber++;
    }
}

$sheet->getStyle('A1:G1')->getFont()->setBold(true);

foreach (range('A', 'G') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="employees_export.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
