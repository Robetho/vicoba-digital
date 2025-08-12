<?php 
include 'db_connect.php';

require 'vendor/autoload.php'; // Path to PhpSpreadsheet autoloader
include 'db_connect.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Protection;

if (!isset($_GET['borrower_id']) || empty($_GET['borrower_id'])) {
    die("Invalid borrower ID.");
}

$borrower_id = intval($_GET['borrower_id']);
$borrower = $conn->query("SELECT savings.*, borrowers.* FROM savings JOIN borrowers on borrowers.id = savings.borrower_id  WHERE savings.borrower_id = '$borrower_id' ORDER BY savings_date DESC");

$user_info = $conn->query("SELECT savings.*, borrowers.* FROM savings JOIN borrowers on borrowers.id = savings.borrower_id  WHERE savings.borrower_id = '$borrower_id' ORDER BY savings_date DESC");

if (!$borrower) {
    die("Borrower not found.");
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Loans');

// Set headers
$headers = ['Tax Id', 'Borrowe Name', 'Notes', 'Amount', 'Status', 'Date Released'];
$columnWidths = [15, 20, 50, 15, 20, 20];

foreach ($headers as $index => $header) {
    $col = chr(65 + $index); // A, B, C...
    $sheet->setCellValue($col . '1', $header);
    $sheet->getColumnDimension($col)->setWidth($columnWidths[$index]); // fixed width
    $sheet->getStyle($col . '1')->getFont()->setBold(true);
}

// Fetch and insert loan data
// $qry = $conn->query("SELECT l.*, lt.type_name, lp.months, lp.interest_percentage 
//     FROM loan_list l 
//     LEFT JOIN loan_types lt ON lt.id = l.loan_type_id 
//     LEFT JOIN loan_plan lp ON lp.id = l.plan_id 
//     WHERE l.borrower_id = $borrower_id");

// $status_labels = [
//     0 => 'For Approval',
//     1 => 'Approved',
//     2 => 'Released',
//     3 => 'Completed',
//     4 => 'Denied'
// ];

$rowNum = 2;
while ($row = $borrower->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowNum, $row['tax_id']);
    $sheet->setCellValue('B' . $rowNum, $row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname'] );
    $sheet->setCellValue('C' . $rowNum, $row['notes']);
    $sheet->setCellValue('D' . $rowNum, $row['amount']);
    $sheet->setCellValue('E' . $rowNum, 'active');
    $sheet->setCellValue('F' . $rowNum, $row['savings_date'] != '0000-00-00 00:00:00' ? date('Y-m-d', strtotime($row['savings_date'])) : 'N/A');
    $rowNum++;
}
$user = $user_info->fetch_assoc();
// Lock all cells
$sheet->getProtection()->setSheet(true); // Lock entire sheet
$sheet->getProtection()->setPassword('readonly'); // Set password
$sheet->getStyle('A1:F' . ($rowNum - 1))->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);

// Set headers for Excel download
$filename = "Savings-info-for-{$user['firstname']}-{$user['lastname']}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

// Output the file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
