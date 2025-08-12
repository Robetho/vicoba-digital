<?php
require 'vendor/autoload.php'; // Path to PhpSpreadsheet autoloader
include 'db_connect.php'; // Database connection

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

$spreadsheet = new Spreadsheet();

// --- Get Filter Parameters ---
$selected_month = isset($_GET['month']) && $_GET['month'] != '' ? intval($_GET['month']) : null;
$selected_year = isset($_GET['year']) && $_GET['year'] != '' ? intval($_GET['year']) : null;

// Build date conditions for SQL queries
// Crucial: Use aliases for date_created where ambiguity exists
$date_condition_loans = "";    // For loan_list (aliased as 'l' in queries)
$date_condition_payments = ""; // For payments (aliased as 'p' in queries)
$date_condition_savings = "";  // For savings (savings_date is unique)
$report_period_label = "";

if ($selected_year) {
    // Corrected: Explicitly use table aliases for date_created
    $date_condition_loans .= " AND YEAR(l.date_created) = '$selected_year'";
    $date_condition_payments .= " AND YEAR(p.date_created) = '$selected_year'";
    $date_condition_savings .= " AND YEAR(savings_date) = '$selected_year'";
    $report_period_label .= " " . $selected_year;
}
if ($selected_month) {
    $months_names = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ];
    // Corrected: Explicitly use table aliases for date_created
    $date_condition_loans .= " AND MONTH(l.date_created) = '$selected_month'";
    $date_condition_payments .= " AND MONTH(p.date_created) = '$selected_month'";
    $date_condition_savings .= " AND MONTH(savings_date) = '$selected_month'";
    $report_period_label = ($selected_month ? $months_names[$selected_month] : '') . $report_period_label;
}

if ($report_period_label) {
    $report_period_label = " for " . trim($report_period_label);
} else {
    $report_period_label = " (All Time)";
}


// --- General Styles ---
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

// This is the style that was intended for table headers
$subHeaderStyle = [
    'font' => [
        'bold' => true,
        'size' => 10, // Adjusted size for table headers
        'color' => ['argb' => 'FF000000'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFDDEBF7'], // Light Blue
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER, // Adjusted to center for table headers
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

$dataCellStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FFD9D9D9'],
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical' => Alignment::VERTICAL_TOP,
    ],
];

// --- Status Labels Mapping ---
$status_labels = [
    0 => 'For Approval',
    1 => 'Approved',
    2 => 'Released',
    3 => 'Completed',
    4 => 'Denied'
];

// --- Sheet 1: Overall Summary ---
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Overall Summary');

$sheet->mergeCells('A1:C1'); // Merges A1, B1, C1
$sheet->setCellValue('A1', 'VIKOBA MANAGEMENT SYSTEM - OVERALL SUMMARY REPORT' . $report_period_label);
$sheet->getStyle('A1')->applyFromArray($mainHeaderStyle); // Changed to mainHeader for bigger title
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// INCREASED WIDTH FOR MERGED HEADER (A1:C1)
$sheet->getColumnDimension('A')->setWidth(45); // Original 35, increase A to make merged cell wider
$sheet->getColumnDimension('B')->setWidth(30); // Original 25, slight increase
$sheet->getColumnDimension('C')->setWidth(15); // Added width for C to ensure merge looks good
// Ensure other columns after C don't interfere with main summary table
$sheet->getColumnDimension('D')->setWidth(1); // Set to minimal if not used after C


$currentRow = 3;

// Fetch summary data (filtered by date conditions)
$total_loans_disbursed = 0;
$total_payments_received = 0;
$total_penalties_collected = 0;
$total_savings_collected = 0;
$total_outstanding_balance = 0;

// Need to create a dummy loan list alias 'l' for the date condition here too
$disbursed_loans_qry = $conn->query("SELECT SUM(l.amount) AS total_disbursed FROM loan_list l WHERE l.status IN (1, 2, 3) " . $date_condition_loans);
$total_disbursed_row = $disbursed_loans_qry->fetch_assoc();
$total_loans_disbursed = $total_disbursed_row['total_disbursed'] ?? 0;

// Need to create a dummy payments alias 'p' for the date condition here too
$payments_qry = $conn->query("SELECT SUM(p.amount) AS total_amount_paid, SUM(p.penalty_amount) AS total_penalty_paid FROM payments p WHERE 1=1 " . $date_condition_payments);
$payments_row = $payments_qry->fetch_assoc();
$total_payments_received = $payments_row['total_amount_paid'] ?? 0;
$total_penalties_collected = $payments_row['total_penalty_paid'] ?? 0;

$savings_qry = $conn->query("SELECT SUM(amount) AS total_savings FROM savings WHERE 1=1 " . $date_condition_savings);
$savings_row = $savings_qry->fetch_assoc();
$total_savings_collected = $savings_row['total_savings'] ?? 0;

// Borrower count is usually overall, not filtered by month.
$borrower_count_qry = $conn->query("SELECT COUNT(id) AS total_borrowers FROM borrowers");
$borrower_count_row = $borrower_count_qry->fetch_assoc();
$total_borrowers = $borrower_count_row['total_borrowers'] ?? 0;

// For loan status counts, use alias 'l' for date_created
$loan_status_counts = [];
$loan_status_qry = $conn->query("SELECT l.status, COUNT(l.id) AS count FROM loan_list l WHERE 1=1 " . $date_condition_loans . " GROUP BY l.status");
while($row = $loan_status_qry->fetch_assoc()){
    $loan_status_counts[$row['status']] = $row['count'];
}
$num_loans_released = $loan_status_counts[2] ?? 0;
$num_loans_completed = $loan_status_counts[3] ?? 0;

// Outstanding Balance (calculated across all time for loans currently active)
$loan_details_for_outstanding_qry = $conn->query("
    SELECT
        ll.id AS loan_id,
        ll.amount AS principal_amount,
        lp.months,
        lp.interest_percentage
    FROM loan_list ll
    JOIN loan_plan lp ON ll.plan_id = lp.id
    WHERE ll.status IN (2, 3)
"); // No date condition here for universal outstanding balance

$total_expected_payable_all_loans = 0;
while($loan = $loan_details_for_outstanding_qry->fetch_assoc()){
    $monthly_calc = ($loan['principal_amount'] + ($loan['principal_amount'] * ($loan['interest_percentage']/100))) / $loan['months'];
    $total_expected_payable_all_loans += ($monthly_calc * $loan['months']);
}

// Corrected: Explicitly specify table for 'amount' and 'penalty_amount'
$total_actual_payments_all_time = $conn->query("SELECT SUM(payments.amount - payments.penalty_amount) AS total_net_paid FROM payments JOIN loan_list ON payments.loan_id = loan_list.id WHERE loan_list.status IN (2,3)")->fetch_assoc()['total_net_paid'] ?? 0;
$total_outstanding_balance = $total_expected_payable_all_loans - $total_actual_payments_all_time;


$summaryData = [
    ['Total Loans Disbursed' . $report_period_label, $total_loans_disbursed],
    ['Total Payments Received' . $report_period_label, $total_payments_received],
    ['Total Penalties Collected' . $report_period_label, $total_penalties_collected],
    ['Total Savings Collected' . $report_period_label, $total_savings_collected],
    ['Outstanding Loan Balance (All Time)', $total_outstanding_balance],
    ['Total Borrowers', $total_borrowers],
    ['Loans Released' . $report_period_label, $num_loans_released],
    ['Loans Completed' . $report_period_label, $num_loans_completed],
];

foreach ($summaryData as $data) {
    $sheet->setCellValue('A'.$currentRow, $data[0]);
    $sheet->setCellValue('B'.$currentRow, $data[1]);
    $sheet->getStyle('A'.$currentRow.':B'.$currentRow)->applyFromArray($dataCellStyle);
    $sheet->getStyle('A'.$currentRow)->getFont()->setBold(true);
    // Format amounts and counts appropriately
    if (is_numeric($data[1]) && strpos($data[0], 'Total ') === 0 && strpos($data[0], 'Borrowers') === false) { // Is an amount
         $sheet->getStyle('B'.$currentRow)->getNumberFormat()->setFormatCode('#,##0.00');
    } else { // Is a count or other text
         $sheet->getStyle('B'.$currentRow)->getNumberFormat()->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_GENERAL);
    }
    $currentRow++;
}

// These specific column widths were already here, keeping them.
// The main header width is now managed by the overall column widths above.


// --- Sheet 2: Loans Detailed Report ---
$spreadsheet->createSheet();
$spreadsheet->setActiveSheetIndex(1);
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Detailed Loans');

$sheet->setCellValue('A1', 'DETAILED LOANS REPORT' . $report_period_label);
$sheet->mergeCells('A1:M1'); // Merges A1 to M1
$sheet->getStyle('A1')->applyFromArray($mainHeaderStyle);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$currentRow = 3;
$headers = ['#', 'Borrower Name', 'Contact No.', 'Address', 'Ref No', 'Loan Type', 'Plan', 'Amount', 'Total Payable', 'Monthly Payable', 'Penalty Rate', 'Date Released', 'Status'];
$colWidths = [
    'A'=>5, 'B'=>35, 'C'=>25, 'D'=>45, 'E'=>20, 'F'=>25, 'G'=>30, 'H'=>18, 'I'=>20, 'J'=>20, 'K'=>18, 'L'=>22, 'M'=>18
]; // Increased widths for better readability, especially names and addresses

$colIndex = 0;
foreach ($headers as $header) {
    $col = chr(65 + $colIndex);
    $sheet->setCellValue($col . $currentRow, $header);
    $sheet->getColumnDimension($col)->setWidth($colWidths[$col] ?? 15);
    $colIndex++;
}
// FIX: Changed $headerStyle to $subHeaderStyle
$sheet->getStyle('A'.$currentRow.':M'.$currentRow)->applyFromArray($subHeaderStyle);
$currentRow++;

// Fetch data for detailed loans (filtered by date_created)
$loan_data_detailed = [];
$type_arr = [];
$plan_arr = [];

$type_qry = $conn->query("SELECT * FROM loan_types");
while($row = $type_qry->fetch_assoc()){
    $type_arr[$row['id']] = $row['type_name'];
}
$plan_qry = $conn->query("SELECT *, CONCAT(months,' month/s [ ',interest_percentage,'%, ',penalty_rate,' ]') AS plan_desc FROM loan_plan");
while($row = $plan_qry->fetch_assoc()){
    $plan_arr[$row['id']] = $row;
}

$qry = $conn->query("SELECT l.*, CONCAT(b.lastname,', ',b.firstname,' ',b.middlename) AS borrower_name, b.contact_no, b.address
    FROM loan_list l
    INNER JOIN borrowers b ON b.id = l.borrower_id
    WHERE 1=1 " . $date_condition_loans . "
    ORDER BY b.lastname, b.firstname, l.date_created ASC");

$loan_num = 1;
while($row = $qry->fetch_assoc()){
    $loan_type = $type_arr[$row['loan_type_id']] ?? 'N/A';
    $loan_plan_data = $plan_arr[$row['plan_id']] ?? null;

    $monthly_payment = 0;
    $total_payable_amount = 0;
    $penalty_rate = 0;

    if ($loan_plan_data) {
        $monthly_payment = ($row['amount'] + ($row['amount'] * ($loan_plan_data['interest_percentage']/100))) / $loan_plan_data['months'];
        $total_payable_amount = $monthly_payment * $loan_plan_data['months'];
        $penalty_rate = $loan_plan_data['penalty_rate'];
    }

    $sheet->setCellValue('A' . $currentRow, $loan_num++);
    $sheet->setCellValue('B' . $currentRow, htmlspecialchars($row['borrower_name']));
    $sheet->setCellValue('C' . $currentRow, htmlspecialchars($row['contact_no']));
    $sheet->setCellValue('D' . $currentRow, htmlspecialchars($row['address']));
    $sheet->setCellValue('E' . $currentRow, htmlspecialchars($row['ref_no']));
    $sheet->setCellValue('F' . $currentRow, $loan_type);
    $sheet->setCellValue('G' . $currentRow, htmlspecialchars($loan_plan_data['plan_desc'] ?? 'N/A'));
    $sheet->setCellValue('H' . $currentRow, $row['amount']);
    $sheet->setCellValue('I' . $currentRow, $total_payable_amount);
    $sheet->setCellValue('J' . $currentRow, $monthly_payment);
    $sheet->setCellValue('K' . $currentRow, $penalty_rate . '%');
    $sheet->setCellValue('L' . $currentRow, $row['date_released'] != '0000-00-00 00:00:00' ? date('Y-m-d', strtotime($row['date_released'])) : 'N/A');
    $sheet->setCellValue('M' . $currentRow, $status_labels[$row['status']] ?? 'Unknown');

    $sheet->getStyle('A'.$currentRow.':M'.$currentRow)->applyFromArray($dataCellStyle);
    $currentRow++;
}

$sheet->getStyle('H4:J'.($currentRow-1))->getNumberFormat()->setFormatCode('#,##0.00');


// --- Sheet 3: Payments Detailed Report ---
$spreadsheet->createSheet();
$spreadsheet->setActiveSheetIndex(2);
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Detailed Payments');

$sheet->setCellValue('A1', 'DETAILED PAYMENTS REPORT' . $report_period_label);
$sheet->mergeCells('A1:G1'); // Merges A1 to G1
$sheet->getStyle('A1')->applyFromArray($mainHeaderStyle);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$currentRow = 3;
$headers = ['#', 'Loan Ref No.', 'Borrower Name', 'Amount Paid', 'Penalty Paid', 'Overdue Status', 'Date Paid'];
$colWidths = [
    'A'=>5, 'B'=>20, 'C'=>35, 'D'=>18, 'E'=>18, 'F'=>18, 'G'=>22
]; // Increased widths for better readability

$colIndex = 0;
foreach ($headers as $header) {
    $col = chr(65 + $colIndex);
    $sheet->setCellValue($col . $currentRow, $header);
    $sheet->getColumnDimension($col)->setWidth($colWidths[$col] ?? 15);
    $colIndex++;
}
// FIX: Changed $headerStyle to $subHeaderStyle
$sheet->getStyle('A'.$currentRow.':G'.$currentRow)->applyFromArray($subHeaderStyle);
$currentRow++;

$payments_qry_detailed = $conn->query("
    SELECT p.*, ll.ref_no, CONCAT(b.lastname,', ',b.firstname,' ',b.middlename) AS borrower_name
    FROM payments p
    JOIN loan_list ll ON p.loan_id = ll.id
    JOIN borrowers b ON ll.borrower_id = b.id
    WHERE 1=1 " . $date_condition_payments . "
    ORDER BY p.date_created DESC
");

$payment_num = 1;
while($row = $payments_qry_detailed->fetch_assoc()){
    $overdue_status = ($row['overdue'] == 1) ? 'Yes' : 'No';

    $sheet->setCellValue('A' . $currentRow, $payment_num++);
    $sheet->setCellValue('B' . $currentRow, htmlspecialchars($row['ref_no']));
    $sheet->setCellValue('C' . $currentRow, htmlspecialchars($row['borrower_name']));
    $sheet->setCellValue('D' . $currentRow, $row['amount']);
    $sheet->setCellValue('E' . $currentRow, $row['penalty_amount']);
    $sheet->setCellValue('F' . $currentRow, $overdue_status);
    $sheet->setCellValue('G' . $currentRow, date('Y-m-d H:i:s', strtotime($row['date_created'])));

    $sheet->getStyle('A'.$currentRow.':G'.$currentRow)->applyFromArray($dataCellStyle);
    $currentRow++;
}

$sheet->getStyle('D4:E'.($currentRow-1))->getNumberFormat()->setFormatCode('#,##0.00');


// --- Sheet 4: Savings Detailed Report ---
$spreadsheet->createSheet();
$spreadsheet->setActiveSheetIndex(3);
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Detailed Savings');

$sheet->setCellValue('A1', 'DETAILED SAVINGS REPORT' . $report_period_label);
$sheet->mergeCells('A1:E1'); // Merges A1 to E1
$sheet->getStyle('A1')->applyFromArray($mainHeaderStyle);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$currentRow = 3;
$headers = ['#', 'Borrower Name', 'Amount', 'Savings Date', 'Notes'];
$colWidths = [
    'A'=>5, 'B'=>35, 'C'=>18, 'D'=>22, 'E'=>50
]; // Increased widths for better readability

$colIndex = 0;
foreach ($headers as $header) {
    $col = chr(65 + $colIndex);
    $sheet->setCellValue($col . $currentRow, $header);
    $sheet->getColumnDimension($col)->setWidth($colWidths[$col] ?? 15);
    $colIndex++;
}
// FIX: Changed $headerStyle to $subHeaderStyle
$sheet->getStyle('A'.$currentRow.':E'.$currentRow)->applyFromArray($subHeaderStyle);
$currentRow++;

$savings_qry_detailed = $conn->query("
    SELECT s.*, CONCAT(b.lastname,', ',b.firstname,' ',b.middlename) AS borrower_name
    FROM savings s
    JOIN borrowers b ON s.borrower_id = b.id
    WHERE 1=1 " . $date_condition_savings . "
    ORDER BY s.savings_date DESC
");

$saving_num = 1;
while($row = $savings_qry_detailed->fetch_assoc()){
    $sheet->setCellValue('A' . $currentRow, $saving_num++);
    $sheet->setCellValue('B' . $currentRow, htmlspecialchars($row['borrower_name']));
    $sheet->setCellValue('C' . $currentRow, $row['amount']);
    $sheet->setCellValue('D' . $currentRow, date('Y-m-d', strtotime($row['savings_date'])));
    $sheet->setCellValue('E' . $currentRow, htmlspecialchars($row['notes']));

    $sheet->getStyle('A'.$currentRow.':E'.$currentRow)->applyFromArray($dataCellStyle);
    $currentRow++;
}

$sheet->getStyle('C4:C'.($currentRow-1))->getNumberFormat()->setFormatCode('#,##0.00');


// --- Finalize and Output Excel File ---
$spreadsheet->setActiveSheetIndex(0); // Set first sheet as active on open

// Lock all sheets (optional, applies protection to all created sheets)
for ($i = 0; $i < $spreadsheet->getSheetCount(); $i++) {
    $spreadsheet->setActiveSheetIndex($i);
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->getProtection()->setSheet(true);
    $sheet->getProtection()->setPassword('vicobapass'); // Set a password for all sheets
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    $sheet->getStyle('A1:'.$highestColumn.$highestRow)->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
}


// Set headers for download
$filename = "Vicoba_Full_Report_" . str_replace(" ", "_", trim($report_period_label, " for ")) . "_" . date('Ymd_His') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

// Output the file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>