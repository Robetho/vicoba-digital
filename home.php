<?php
// Ensure db_connect.php is included before starting session if session relies on database connection
include 'db_connect.php';

// Start the session if it hasn't been started already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Security: Check if user is logged in
if (!isset($_SESSION['login_id'])) {
    header("location:login.php"); // Redirect to login page if not logged in
    exit();
}

// --- PHP Data Preparation for Charts ---

// 1. Data for Payments Over Time (e.g., monthly)
$monthly_payments_data = [];
// Assuming 'date_created' in 'payments' table stores the payment date
$payments_chart_query = $conn->query("
    SELECT
        DATE_FORMAT(date_created, '%Y-%m') as month,
        SUM(amount) as total_amount
    FROM payments
    GROUP BY month
    ORDER BY month ASC
");
while ($row = $payments_chart_query->fetch_assoc()) {
    $monthly_payments_data[$row['month']] = $row['total_amount'];
}

// Fill in missing months with 0 for a continuous chart
$current_month = date('Y-m-01');
$start_month_obj = new DateTime(empty($monthly_payments_data) ? $current_month : min(array_keys($monthly_payments_data)).'-01');
$end_month_obj = new DateTime($current_month);
$interval = DateInterval::createFromDateString('1 month');
$period = new DatePeriod($start_month_obj, $interval, $end_month_obj->modify('+1 month')); // Include current month

$chart_months = [];
$chart_payments_data = [];

foreach ($period as $dt) {
    $month_key = $dt->format('Y-m');
    $chart_months[] = $dt->format('M Y'); // e.g., Jan 2023
    $chart_payments_data[] = $monthly_payments_data[$month_key] ?? 0;
}


// 2. Data for Loan Status Distribution (Pie/Doughnut Chart)
$loan_status_data = [];
$loan_status_labels = [];
$loan_status_colors = []; // Define colors for each status

$loan_status_query = $conn->query("
    SELECT
        status,
        COUNT(*) as count
    FROM loan_list
    GROUP BY status
");

// Map status codes to user-friendly labels and colors
$status_map = [
    0 => ['label' => 'Submitted', 'color' => '#6c757d'], // Grey
    1 => ['label' => 'Confirmed', 'color' => '#ffc107'], // Yellow (Warning)
    2 => ['label' => 'Released', 'color' => '#17a2b8'],  // Cyan (Info)
    3 => ['label' => 'Completed', 'color' => '#28a745'], // Green (Success)
    4 => ['label' => 'Denied', 'color' => '#dc3545']    // Red (Danger)
];

while ($row = $loan_status_query->fetch_assoc()) {
    $status_code = $row['status'];
    if (isset($status_map[$status_code])) {
        $loan_status_labels[] = $status_map[$status_code]['label'];
        $loan_status_data[] = $row['count'];
        $loan_status_colors[] = $status_map[$status_code]['color'];
    }
}

?>

<style>
    /* Custom styles for the dashboard cards */
    .card-dashboard {
        border-radius: 0.75rem; /* Slightly rounded corners */
        overflow: hidden; /* Ensure content stays within borders */
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05); /* Subtle shadow */
    }
    .card-dashboard:hover {
        transform: translateY(-5px); /* Lift effect on hover */
        box-shadow: 0 8px 16px rgba(0,0,0,0.1); /* Enhanced shadow on hover */
    }
    .card-dashboard .card-body {
        padding: 1.5rem; /* Consistent padding */
    }
    .card-dashboard .card-footer {
        background-color: rgba(0, 0, 0, 0.1); /* Slightly darker footer for contrast */
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        padding: 0.75rem 1.5rem; /* Padding for footer */
    }
    .card-dashboard .text-lg {
        font-size: 2.25rem; /* Larger font for main numbers */
        font-weight: 700; /* Bolder numbers */
        line-height: 1.2;
    }
    .card-dashboard .small {
        font-size: 0.85rem; /* Smaller text for descriptions */
        opacity: 0.8; /* Slight transparency for secondary text */
    }
    .card-dashboard i.fa {
        font-size: 2.5rem; /* Larger icons */
        opacity: 0.6; /* Subtle icons */
    }
    .welcome-card {
        background-color: #f8f9fa; /* Light background for welcome card */
        border-left: 5px solid #007bff; /* Accent border */
    }
    .table-responsive {
        max-height: 350px; /* Limit height for scrollable tables */
        overflow-y: auto;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card welcome-card mt-3 mb-4">
                <div class="card-body">
                    <h4 class="mb-0">
                        <?php
                        // Safely access session variables
                        $userName = isset($_SESSION['login_name']) ? $_SESSION['login_name'] : 'Guest';
                        $loginType = isset($_SESSION['login_type']) ? $_SESSION['login_type'] : 0;

                        if ($loginType == 3) { // Assuming type 3 is 'Doctor' based on your previous code comment
                            $namePref = isset($_SESSION['login_name_pref']) ? ', ' . $_SESSION['login_name_pref'] : '';
                            echo "Welcome back Dr. " . htmlspecialchars($userName) . htmlspecialchars($namePref) . "!";
                        } else {
                            echo "Welcome back " . htmlspecialchars($userName) . "!";
                        }
                        ?>
                    </h4>
                    <p class="text-muted mt-2 mb-0">Here's a quick overview of your Vikoba Management System today.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row ml-2 mr-2">
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white card-dashboard">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mr-3">
                            <div class="text-white-75 small">Payments Today</div>
                            <div class="text-lg font-weight-bold">
                                <?php
                                $current_date = date("Y-m-d"); // Get current date
                                $payments_today_query = $conn->query("SELECT SUM(amount) AS total FROM payments WHERE DATE(date_created) = '$current_date'");
                                $payments_today = $payments_today_query->num_rows > 0 ? $payments_today_query->fetch_array()['total'] : 0;
                                echo number_format($payments_today, 2);
                                ?>
                            </div>
                        </div>
                        <i class="fa fa-money-bill-alt"></i> </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=payments">View Payments</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white card-dashboard">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mr-3">
                            <div class="text-white-75 small">Total Borrowers</div>
                            <div class="text-lg font-weight-bold">
                                <?php
                                $borrowers_query = $conn->query("SELECT * FROM borrowers");
                                echo $borrowers_query->num_rows;
                                ?>
                            </div>
                        </div>
                        <i class="fa fa-users"></i> </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=borrowers">View Borrowers</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-white card-dashboard">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mr-3">
                            <div class="text-white-75 small">Active Loans</div>
                            <div class="text-lg font-weight-bold">
                                <?php
                                $active_loans_query = $conn->query("SELECT * FROM loan_list WHERE status = 2"); // Assuming 2 means released/active
                                echo $active_loans_query->num_rows;
                                ?>
                            </div>
                        </div>
                        <i class="fa fa-hand-holding-usd"></i> </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=loans">View Loan List</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white card-dashboard">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mr-3">
                            <div class="text-white-75 small">Total Amount Due (Receivable)</div>
                            <div class="text-lg font-weight-bold">
                                <?php
                                // Total principal + interest from released loans
                                $total_loans_amount_query = $conn->query("SELECT SUM(l.amount + (l.amount * (p.interest_percentage / 100))) AS total FROM loan_list l INNER JOIN loan_plan p ON p.id = l.plan_id WHERE l.status = 2");
                                $total_loans_amount = $total_loans_amount_query->num_rows > 0 ? $total_loans_amount_query->fetch_array()['total'] : 0;

                                // Total payments collected from all loans (excluding penalties, as they are separate)
                                $total_payments_collected_query = $conn->query("SELECT SUM(amount) AS total FROM payments");
                                $total_payments_collected = $total_payments_collected_query->num_rows > 0 ? $total_payments_collected_query->fetch_array()['total'] : 0;

                                echo number_format($total_loans_amount - $total_payments_collected, 2);
                                ?>
                            </div>
                        </div>
                        <i class="fa fa-coins"></i> </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=loans">View Loan List</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-secondary text-white card-dashboard">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mr-3">
                            <div class="text-white-75 small">Total Principal Released</div>
                            <div class="text-lg font-weight-bold">
                                <?php
                                $total_principal_released_query = $conn->query("SELECT SUM(amount) AS total FROM loan_list WHERE status = 2");
                                $total_principal_released = $total_principal_released_query->num_rows > 0 ? $total_principal_released_query->fetch_array()['total'] : 0;
                                echo number_format($total_principal_released, 2);
                                ?>
                            </div>
                        </div>
                        <i class="fa fa-dollar-sign"></i> </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=loans">View Released Loans</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white card-dashboard">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mr-3">
                            <div class="text-white-75 small">Total Payments Collected</div>
                            <div class="text-lg font-weight-bold">
                                <?php
                                $total_payments_query = $conn->query("SELECT SUM(amount) AS total FROM payments");
                                $total_payments = $total_payments_query->num_rows > 0 ? $total_payments_query->fetch_array()['total'] : 0;
                                echo number_format($total_payments, 2);
                                ?>
                            </div>
                        </div>
                        <i class="fa fa-receipt"></i> </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=payments">View All Payments</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white card-dashboard">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mr-3">
                            <div class="text-white-75 small">Total Savings Collected</div>
                            <div class="text-lg font-weight-bold">
                                <?php
                                $total_savings_query = $conn->query("SELECT SUM(amount) AS total FROM savings");
                                $total_savings = $total_savings_query->num_rows > 0 ? $total_savings_query->fetch_array()['total'] : 0;
                                echo number_format($total_savings, 2);
                                ?>
                            </div>
                        </div>
                        <i class="fa fa-hand-holding-usd"></i> </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=savings_list">View All Savings</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-danger text-white card-dashboard">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mr-3">
                            <div class="text-white-75 small">Overdue Loans</div>
                            <div class="text-lg font-weight-bold">
                                <?php
                                // This query identifies loans where at least one payment schedule date has passed AND no payment has been recorded for that specific due date.
                                // It might need further refinement based on your exact overdue logic (e.g., partial payments, grace periods).
                                $overdue_loans_query = $conn->query("
                                    SELECT DISTINCT ls.loan_id
                                    FROM loan_schedules ls
                                    INNER JOIN loan_list ll ON ls.loan_id = ll.id
                                    LEFT JOIN payments p ON ls.loan_id = p.loan_id AND DATE(ls.date_due) = DATE(p.date_created)
                                    WHERE ls.date_due < CURDATE() AND ll.status = 2 AND p.id IS NULL
                                ");
                                echo $overdue_loans_query->num_rows;
                                ?>
                            </div>
                        </div>
                        <i class="fa fa-exclamation-triangle"></i> </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=loans&status=overdue">View Overdue Loans</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-dark text-white card-dashboard">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mr-3">
                            <div class="text-white-75 small">Total Loan Plans</div>
                            <div class="text-lg font-weight-bold">
                                <?php
                                $loan_plans_query = $conn->query("SELECT * FROM loan_plan");
                                echo $loan_plans_query->num_rows;
                                ?>
                            </div>
                        </div>
                        <i class="fa fa-clipboard-list"></i> </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=loan_plan">Manage Loan Plans</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white card-dashboard">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mr-3">
                            <div class="text-white-75 small">Total Loan Types</div>
                            <div class="text-lg font-weight-bold">
                                <?php
                                $loan_types_query = $conn->query("SELECT * FROM loan_types");
                                echo $loan_types_query->num_rows;
                                ?>
                            </div>
                        </div>
                        <i class="fa fa-tags"></i> </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=loan_type">Manage Loan Types</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

    </div>

    ---

    <div class="row ml-2 mr-2">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5>Monthly Payments Overview</h5>
                </div>
                <div class="card-body">
                    <canvas id="paymentsChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5>Loan Status Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="loanStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    ---

    <div class="row ml-2 mr-2">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5>Recent Payments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Payee</th>
                                    <th>Amount</th>
                                    <th>Penalty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $recent_payments_query = $conn->query("SELECT p.*, b.firstname, b.lastname FROM payments p JOIN loan_list ll ON p.loan_id = ll.id JOIN borrowers b ON ll.borrower_id = b.id ORDER BY p.date_created DESC LIMIT 5");
                                if ($recent_payments_query->num_rows > 0) {
                                    while ($row = $recent_payments_query->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . date("M d, Y H:i A", strtotime($row['date_created'])) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) . "</td>";
                                        echo "<td>" . number_format($row['amount'], 2) . "</td>";
                                        echo "<td>" . number_format($row['penalty_amount'], 2) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>No recent payments.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="chart.umd.min.js"></script>

<script>
    // PHP variables converted to JavaScript
    const chartMonths = <?php echo json_encode(array_values($chart_months)); ?>;
    const chartPaymentsData = <?php echo json_encode(array_values($chart_payments_data)); ?>;

    const loanStatusLabels = <?php echo json_encode($loan_status_labels); ?>;
    const loanStatusData = <?php echo json_encode($loan_status_data); ?>;
    const loanStatusColors = <?php echo json_encode($loan_status_colors); ?>;


    // --- Payments Chart (Bar Chart) ---
    const paymentsCtx = document.getElementById('paymentsChart').getContext('2d');
    new Chart(paymentsCtx, {
        type: 'bar', // Changed to bar for clarity
        data: {
            labels: chartMonths,
            datasets: [{
                label: 'Total Payments (Tsh)',
                data: chartPaymentsData,
                backgroundColor: 'rgba(0, 123, 255, 0.7)', // Blue
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Amount (Tsh)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Month'
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Monthly Payments Collected'
                }
            }
        }
    });

    // --- Loan Status Chart (Doughnut Chart) ---
    const loanStatusCtx = document.getElementById('loanStatusChart').getContext('2d');
    new Chart(loanStatusCtx, {
        type: 'doughnut',
        data: {
            labels: loanStatusLabels,
            datasets: [{
                label: 'Number of Loans',
                data: loanStatusData,
                backgroundColor: loanStatusColors,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right', // Place legend on the right for better readability
                },
                title: {
                    display: true,
                    text: 'Distribution of Loan Statuses'
                }
            }
        }
    });

</script>