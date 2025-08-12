<?php
include 'db_connect.php'; // Hakikisha unayo db_connect.php hapa

// Load PhpSpreadsheet library
require 'vendor/autoload.php'; // Hakikisha path hii ni sahihi kuelekea folder ya vendor
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set sheet title
$sheet->setTitle('Loans Report by Borrower');

// --- Define Headers and Styles ---
$headers = [
    'Borrower Details', 'Loan Ref No.', 'Loan Type', 'Plan', 'Amount',
    'Total Payable', 'Monthly Payable', 'Penalty Rate', 'Date Released', 'Status'
];

$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['argb' => 'FFFFFFFF'], // White text
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF4F81BD'], // Dark Blue
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
];

$borrowerHeaderStyle = [
    'font' => [
        'bold' => true,
        'color' => ['argb' => 'FF000000'], // Black text
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFEBF1DE'], // Light Green/Grey
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
    ],
];

$dataCellStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
    ],
];

// Set column widths
$sheet->getColumnDimension('A')->setWidth(35); // Borrower Details (Name, Contact, Address)
$sheet->getColumnDimension('B')->setWidth(18); // Loan Ref No.
$sheet->getColumnDimension('C')->setWidth(18); // Loan Type
$sheet->getColumnDimension('D')->setWidth(25); // Plan
$sheet->getColumnDimension('E')->setWidth(15); // Amount
$sheet->getColumnDimension('F')->setWidth(20); // Total Payable
$sheet->getColumnDimension('G')->setWidth(20); // Monthly Payable
$sheet->getColumnDimension('H')->setWidth(18); // Penalty Rate
$sheet->getColumnDimension('I')->setWidth(18); // Date Released
$sheet->getColumnDimension('J')->setWidth(15); // Status

// --- Fetch Data ---
$loan_data = [];
$type_arr = [];
$plan_arr = [];

// Fetch loan types
$type_qry = $conn->query("SELECT * FROM loan_types WHERE id IN (SELECT loan_type_id FROM loan_list)");
while($row = $type_qry->fetch_assoc()){
    $type_arr[$row['id']] = $row['type_name'];
}

// Fetch loan plans
$plan_qry = $conn->query("SELECT *, CONCAT(months,' month/s [ ',interest_percentage,'%, ',penalty_rate,' ]') AS plan FROM loan_plan WHERE id IN (SELECT plan_id FROM loan_list)");
while($row = $plan_qry->fetch_assoc()){
    $plan_arr[$row['id']] = $row;
}

// Fetch all loan data and organize by borrower
$qry = $conn->query("SELECT l.*, CONCAT(b.lastname,', ',b.firstname,' ',b.middlename) AS name, b.contact_no, b.address FROM loan_list l INNER JOIN borrowers b ON b.id = l.borrower_id ORDER BY b.lastname, b.firstname, l.id ASC");

while($row = $qry->fetch_assoc()){
    $borrower_id = $row['borrower_id'];

    if (!isset($loan_data[$borrower_id])) {
        $loan_data[$borrower_id] = [
            'name' => $row['name'],
            'contact_no' => $row['contact_no'],
            'address' => $row['address'],
            'loans' => []
        ];
    }

    $monthly = ($row['amount'] + ($row['amount'] * ($plan_arr[$row['plan_id']]['interest_percentage']/100))) / $plan_arr[$row['plan_id']]['months'];
    $total_payable = $monthly * $plan_arr[$row['plan_id']]['months'];

    $loan_data[$borrower_id]['loans'][] = [
        'ref_no' => $row['ref_no'],
        'loan_type' => $type_arr[$row['loan_type_id']],
        'plan' => $plan_arr[$row['plan_id']]['plan'],
        'amount' => $row['amount'],
        'total_payable' => $total_payable,
        'monthly_payable' => $monthly,
        'penalty_rate' => $plan_arr[$row['plan_id']]['penalty_rate'],
        'date_released' => ($row['date_released'] != '0000-00-00 00:00:00' ? date('Y-m-d', strtotime($row['date_released'])) : 'N/A'),
        'status' => $row['status']
    ];
}

// --- Write data to Excel ---
$currentRow = 1;

// Write main headers
$sheet->fromArray($headers, NULL, 'A'.$currentRow);
$sheet->getStyle('A'.$currentRow.':J'.$currentRow)->applyFromArray($headerStyle);
$currentRow++;

$statusMap = [
    0 => 'For Approval',
    1 => 'Approved',
    2 => 'Released',
    3 => 'Completed',
    4 => 'Denied'
];

foreach ($loan_data as $borrower) {
    // Borrower Header Row (merged across all columns for details)
    $sheet->mergeCells('A'.$currentRow.':J'.$currentRow);
    $sheet->setCellValue('A'.$currentRow, 'Borrower: ' . $borrower['name'] . ' | Contact: ' . $borrower['contact_no'] . ' | Address: ' . $borrower['address']);
    $sheet->getStyle('A'.$currentRow.':J'.$currentRow)->applyFromArray($borrowerHeaderStyle);
    $currentRow++;

    // Write each loan for this borrower
    foreach ($borrower['loans'] as $loan) {
        $sheet->setCellValue('A'.$currentRow, ''); // Empty cell for borrower column
        $sheet->setCellValue('B'.$currentRow, $loan['ref_no']);
        $sheet->setCellValue('C'.$currentRow, $loan['loan_type']);
        $sheet->setCellValue('D'.$currentRow, $loan['plan']);
        $sheet->setCellValue('E'.$currentRow, $loan['amount']);
        $sheet->setCellValue('F'.$currentRow, $loan['total_payable']);
        $sheet->setCellValue('G'.$currentRow, $loan['monthly_payable']);
        $sheet->setCellValue('H'.$currentRow, $loan['penalty_rate'] . '%');
        $sheet->setCellValue('I'.$currentRow, $loan['date_released']);
        $sheet->setCellValue('J'.$currentRow, $statusMap[$loan['status']]);

        // Apply data cell style
        $sheet->getStyle('A'.$currentRow.':J'.$currentRow)->applyFromArray($dataCellStyle);
        $currentRow++;
    }
    // Add an empty row for separation after each borrower's loans
    $currentRow++;
}

// Format numbers as currency where appropriate
$sheet->getStyle('E2:G'.($currentRow-1))->getNumberFormat()->setFormatCode('#,##0.00');

// Make cells read-only (optional)
$sheet->getProtection()->setSheet(true);
$sheet->getProtection()->setPassword('adminpass'); // Optional password
$sheet->getStyle('A1:J' . ($currentRow - 1))->getProtection()->setLocked(Protection::PROTECTION_PROTECTED); // Lock all cells with data

// --- Download as Excel ---
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Loans_Report_By_Borrower_' . date('Ymd_His') . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>