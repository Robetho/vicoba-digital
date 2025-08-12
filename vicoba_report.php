<?php include 'db_connect.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <large class="card-title">
                        <b>Vicoba Comprehensive Report</b>
                    </large>
                    <form id="filter-report-form" class="form-inline float-right" method="GET" action="">
                        <label for="month" class="mr-2">Month:</label>
                        <select name="month" id="month" class="form-control mr-3">
                            <option value="">All</option>
                            <?php
                            $months = [
                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                            ];
                            // Use current month if no filter applied for initial load, otherwise use GET value
                            $currentMonth = isset($_GET['month']) && $_GET['month'] != '' ? $_GET['month'] : date('n');
                            foreach ($months as $num => $name) {
                                $selected = (isset($_GET['month']) && $_GET['month'] == $num) ? 'selected' : '';
                                if (!isset($_GET['month']) && $num == date('n')) { // Default to current month if no filter set
                                    $selected = 'selected';
                                }
                                echo "<option value='{$num}' {$selected}>{$name}</option>";
                            }
                            ?>
                        </select>

                        <label for="year" class="mr-2">Year:</label>
                        <select name="year" id="year" class="form-control mr-3">
                            <option value="">All</option>
                            <?php
                            // Use current year if no filter applied for initial load, otherwise use GET value
                            $currentYear = isset($_GET['year']) && $_GET['year'] != '' ? $_GET['year'] : date('Y');
                            $startYear = 2020; // Anza miaka kutoka 2020 au mwaka wako Vicoba ilipoanza
                            $endYear = date('Y') + 5; // Miaka 5 mbele
                            for ($y = $startYear; $y <= $endYear; $y++) {
                                $selected = (isset($_GET['year']) && $_GET['year'] == $y) ? 'selected' : '';
                                if (!isset($_GET['year']) && $y == date('Y')) { // Default to current year if no filter set
                                    $selected = 'selected';
                                }
                                echo "<option value='{$y}' {$selected}>{$y}</option>";
                            }
                            ?>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm mr-2"><i class="fa fa-filter"></i> Filter</button>
                        <a href="#" id="export-btn" class="btn btn-success btn-sm"><i class="fa fa-file-excel"></i> Export Full Report</a>
                    </form>
                </div>
                <div class="card-body">
                    <h4>Overall Summary
                        <?php
                            $display_month = '';
                            if (isset($_GET['month']) && $_GET['month'] != '') {
                                $display_month = " for " . $months[$_GET['month']];
                            } else if (!isset($_GET['month'])) { // If no month is selected, default to current month
                                $display_month = " for " . $months[date('n')];
                            }

                            $display_year = '';
                            if (isset($_GET['year']) && $_GET['year'] != '') {
                                $display_year = " " . $_GET['year'];
                            } else if (!isset($_GET['year'])) { // If no year is selected, default to current year
                                $display_year = " " . date('Y');
                            }
                            echo $display_month . $display_year;
                        ?>
                    </h4>
                    <hr>
                    <div class="row">
                        <?php
                        // Filter variables from GET request
                        // If month/year not set, default to current month/year for filtering on initial load
                        $selected_month = isset($_GET['month']) && $_GET['month'] != '' ? $_GET['month'] : date('n');
                        $selected_year = isset($_GET['year']) && $_GET['year'] != '' ? $_GET['year'] : date('Y');

                        // Build date conditions for SQL queries
                        // Use AND for multiple conditions
                        // Note: These conditions are built for internal use in vicoba_report.php.
                        // The export file needs its own correctly aliased conditions.
                        $date_condition_loans = " AND YEAR(date_created) = '$selected_year'";
                        $date_condition_payments = " AND YEAR(date_created) = '$selected_year'";
                        $date_condition_savings = " AND YEAR(savings_date) = '$selected_year'";

                        if ($selected_month) {
                            $date_condition_loans .= " AND MONTH(date_created) = '$selected_month'";
                            $date_condition_payments .= " AND MONTH(date_created) = '$selected_month'";
                            $date_condition_savings .= " AND MONTH(savings_date) = '$selected_month'";
                        }


                        // Initialize all sums
                        $total_loans_disbursed = 0;
                        $total_payments_received = 0;
                        $total_penalties_collected = 0;
                        $total_savings_collected = 0;
                        $total_outstanding_balance = 0;

                        // Get total disbursed amount for released loans
                        // Only count loans released within the selected period
                        $disbursed_loans_qry = $conn->query("SELECT SUM(amount) AS total_disbursed FROM loan_list WHERE status IN (1, 2, 3) " . $date_condition_loans);
                        $total_disbursed_row = $disbursed_loans_qry->fetch_assoc();
                        $total_loans_disbursed = $total_disbursed_row['total_disbursed'] ?? 0;

                        // Get total payments received (amount - penalty_amount)
                        // Only count payments made within the selected period
                        $payments_qry = $conn->query("SELECT SUM(amount) AS total_amount_paid, SUM(penalty_amount) AS total_penalty_paid FROM payments WHERE 1=1 " . $date_condition_payments);
                        $payments_row = $payments_qry->fetch_assoc();
                        $total_payments_received = $payments_row['total_amount_paid'] ?? 0;
                        $total_penalties_collected = $payments_row['total_penalty_paid'] ?? 0;


                        // Get total savings
                        // Only count savings made within the selected period
                        $savings_qry = $conn->query("SELECT SUM(amount) AS total_savings FROM savings WHERE 1=1 " . $date_condition_savings);
                        $savings_row = $savings_qry->fetch_assoc();
                        $total_savings_collected = $savings_row['total_savings'] ?? 0;

                        // Get number of borrowers (usually not filtered by month/year as it's a total count)
                        $borrower_count_qry = $conn->query("SELECT COUNT(id) AS total_borrowers FROM borrowers");
                        $borrower_count_row = $borrower_count_qry->fetch_assoc();
                        $total_borrowers = $borrower_count_row['total_borrowers'] ?? 0;

                        // Get number of loans by status (filtered by date_created)
                        $loan_status_counts = [];
                        $loan_status_qry = $conn->query("SELECT status, COUNT(id) AS count FROM loan_list WHERE 1=1 " . $date_condition_loans . " GROUP BY status");
                        while($row = $loan_status_qry->fetch_assoc()){
                            $loan_status_counts[$row['status']] = $row['count'];
                        }
                        $num_loans_released = $loan_status_counts[2] ?? 0;
                        $num_loans_completed = $loan_status_counts[3] ?? 0;

                        // Calculate total expected payable for all released/completed loans to find outstanding balance
                        // Outstanding balance is typically calculated across ALL active loans, not just those created/paid in a specific month.
                        // If you need to filter this by loans released within the selected period, you'd need to add that condition.
                        // For now, it's global outstanding balance for all active loans.
                        $loan_details_for_outstanding_qry = $conn->query("
                            SELECT
                                ll.id AS loan_id,
                                ll.amount AS principal_amount,
                                lp.months,
                                lp.interest_percentage
                            FROM loan_list ll
                            JOIN loan_plan lp ON ll.plan_id = lp.id
                            WHERE ll.status IN (2, 3)
                        ");

                        $total_expected_payable_all_loans = 0;
                        while($loan = $loan_details_for_outstanding_qry->fetch_assoc()){
                            $monthly_calc = ($loan['principal_amount'] + ($loan['principal_amount'] * ($loan['interest_percentage']/100))) / $loan['months'];
                            $total_expected_payable_all_loans += ($monthly_calc * $loan['months']);
                        }

                        // Corrected: Explicitly specify table for 'amount' and 'penalty_amount'
                        $total_actual_payments_all_time = $conn->query("SELECT SUM(payments.amount - payments.penalty_amount) AS total_net_paid FROM payments JOIN loan_list ON payments.loan_id = loan_list.id WHERE loan_list.status IN (2,3)")->fetch_assoc()['total_net_paid'] ?? 0;

                        $total_outstanding_balance = $total_expected_payable_all_loans - $total_actual_payments_all_time;
                        ?>
                        <div class="col-md-3">
                            <div class="card text-white bg-primary mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Total Loans Disbursed <?php echo $display_month . $display_year; ?></h5>
                                    <p class="card-text h2"><?php echo number_format($total_loans_disbursed, 2); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-success mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Total Payments Received <?php echo $display_month . $display_year; ?></h5>
                                    <p class="card-text h2"><?php echo number_format($total_payments_received, 2); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-danger mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Total Penalties Collected <?php echo $display_month . $display_year; ?></h5>
                                    <p class="card-text h2"><?php echo number_format($total_penalties_collected, 2); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-info mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Total Savings Collected <?php echo $display_month . $display_year; ?></h5>
                                    <p class="card-text h2"><?php echo number_format($total_savings_collected, 2); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-secondary mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Outstanding Loan Balance (All Time)</h5>
                                    <p class="card-text h2"><?php echo number_format($total_outstanding_balance, 2); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-dark mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Total Borrowers</h5>
                                    <p class="card-text h2"><?php echo $total_borrowers; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-warning mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Loans Released <?php echo $display_month . $display_year; ?></h5>
                                    <p class="card-text h2"><?php echo $num_loans_released; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-success mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Loans Completed <?php echo $display_month . $display_year; ?></h5>
                                    <p class="card-text h2"><?php echo $num_loans_completed; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Optional: Add custom styles here if needed for the summary cards */
    .card-title {
        font-size: 1rem;
    }
    .card-text.h2 {
        font-size: 2.5rem;
    }
</style>

<script>
    document.getElementById('export-btn').addEventListener('click', function(e) {
        e.preventDefault();
        const month = document.getElementById('month').value;
        const year = document.getElementById('year').value;
        let url = 'export_vicoba_report_excel.php';
        const params = new URLSearchParams();
        // Append only if a specific month/year is selected (not 'All')
        if (month !== '') {
            params.append('month', month);
        }
        if (year !== '') {
            params.append('year', year);
        }
        if (params.toString()) {
            url += '?' + params.toString();
        }
        window.open(url, '_blank');
    });
</script>