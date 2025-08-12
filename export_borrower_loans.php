<?php
require 'vendor/autoload.php'; // Hakikisha path hii ni sahihi kuelekea folder ya vendor
include 'db_connect.php'; // Hakikisha unayo db_connect.php hapa

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

// --- 1. Validation ya Borrower ID ---
if (!isset($_GET['borrower_id']) || empty($_GET['borrower_id'])) {
    die("Invalid borrower ID provided.");
}

$borrower_id = intval($_GET['borrower_id']);

// Fetch borrower details
$borrower_qry = $conn->query("SELECT * FROM borrowers WHERE id = $borrower_id");
$borrower = $borrower_qry->fetch_assoc();

if (!$borrower) {
    die("Borrower not found with the provided ID.");
}

// --- 2. Initialize Spreadsheet ---
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Borrower Loans');

// --- 3. Define Styles ---
$mainHeaderStyle = [
    'font' => [
        'bold' => true,
        'size' => 14,
        'color' => ['argb' => 'FF333333'], // Dark Gray
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
];

$subHeaderStyle = [
    'font' => [
        'bold' => true,
        'size' => 10,
        'color' => ['argb' => 'FF000000'], // Black
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFF2F2F2'], // Light Gray
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FFC0C0C0'], // Light Gray border
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

$dataRowStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FFD9D9D9'], // Lighter Gray border
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical' => Alignment::VERTICAL_TOP,
    ],
];

// --- 4. Add Borrower Information Header ---
$sheet->mergeCells('A1:F1');
$sheet->setCellValue('A1', 'LOANS FOR: ' . htmlspecialchars(strtoupper($borrower['lastname'] . ', ' . $borrower['firstname'] . ' ' . $borrower['middlename'])));
$sheet->getStyle('A1')->applyFromArray($mainHeaderStyle);

$sheet->mergeCells('A2:F2');
$sheet->setCellValue('A2', 'Contact: ' . htmlspecialchars($borrower['contact_no']) . ' | Address: ' . htmlspecialchars($borrower['address']));
$sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$currentRow = 4; // Start headers from row 4

// --- 5. Set Column Headers and Widths ---
$headers = ['#', 'Ref No', 'Loan Type', 'Plan', 'Amount', 'Status', 'Date Released']; // Added # for serial number
$columnWidths = ['A' => 5, 'B' => 18, 'C' => 25, 'D' => 30, 'E' => 15, 'F' => 18, 'G' => 18]; // Adjusted widths for new columns

foreach ($headers as $index => $header) {
    $col = chr(65 + $index); // A, B, C...
    $sheet->setCellValue($col . $currentRow, $header);
    $sheet->getColumnDimension($col)->setWidth($columnWidths[$col]);
}
$sheet->getStyle('A'.$currentRow.':G'.$currentRow)->applyFromArray($subHeaderStyle);
$currentRow++; // Move to next row for data

// --- 6. Fetch and Insert Loan Data ---
$qry = $conn->query("SELECT l.*, lt.type_name, lp.months, lp.interest_percentage
    FROM loan_list l
    LEFT JOIN loan_types lt ON lt.id = l.loan_type_id
    LEFT JOIN loan_plan lp ON lp.id = l.plan_id
    WHERE l.borrower_id = $borrower_id
    ORDER BY l.date_created ASC"); // Order by creation date

$status_labels = [
    0 => 'For Approval',
    1 => 'Approved',
    2 => 'Released',
    3 => 'Completed',
    4 => 'Denied'
];

$loan_index = 1; // For serial number
while ($row = $qry->fetch_assoc()) {
    $status = $status_labels[$row['status']] ?? 'Unknown';
    $plan = htmlspecialchars($row['months']) . " month(s) [" . htmlspecialchars($row['interest_percentage']) . "%]";
    $date_released = ($row['date_released'] != '0000-00-00 00:00:00' ? date('Y-m-d', strtotime($row['date_released'])) : 'N/A');

    $sheet->setCellValue('A' . $currentRow, $loan_index++);
    $sheet->setCellValue('B' . $currentRow, htmlspecialchars($row['ref_no']));
    $sheet->setCellValue('C' . $currentRow, htmlspecialchars($row['type_name']));
    $sheet->setCellValue('D' . $currentRow, $plan);
    $sheet->setCellValue('E' . $currentRow, $row['amount']); // Amount will be formatted later
    $sheet->setCellValue('F' . $currentRow, $status);
    $sheet->setCellValue('G' . $currentRow, $date_released);

    // Apply general data cell style
    $sheet->getStyle('A'.$currentRow.':G'.$currentRow)->applyFromArray($dataRowStyle);
    $currentRow++;
}

// --- 7. Apply Number Formatting for Amount Column ---
$sheet->getStyle('E5:E' . ($currentRow - 1))->getNumberFormat()->setFormatCode('#,##0.00'); // Format Amount column

// --- 8. Set Read-Only Protection (Optional) ---
$sheet->getProtection()->setSheet(true); // Lock entire sheet
$sheet->getProtection()->setPassword('readonly'); // Set password
// Lock all cells with data, but ensure the password is set only once for the sheet.
$sheet->getStyle('A1:G' . ($currentRow - 1))->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);

// --- 9. Set Headers for Excel Download ---
$filename = "Loans_of_" . preg_replace('/[^A-Za-z0-9\-]/', '_', $borrower['lastname'] . '_' . $borrower['firstname']) . ".xlsx"; // Sanitize filename
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

// --- 10. Output the File ---
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>