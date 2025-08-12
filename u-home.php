<?php 
include 'db_connect.php'; 

// Start the session if it hasn't been started already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is logged in and is a borrower type
$borrower_id = isset($_SESSION['login_id']) ? $_SESSION['login_id'] : 0;

// Redirect if not logged in or not the correct user type
if ($borrower_id == 0) {
    header("location:login.php?status=missed_id"); 
    exit();
}

// Get database connection
//$conn = $crud->getDbConnection();

// --- NEW: Query for Unread Message Count ---
$unread_messages_count = 0;
if ($conn) {
    // This query counts bulk messages that do NOT have a corresponding entry in message_views for this borrower
    $unread_qry = $conn->query("
        SELECT COUNT(bm.id) AS unread_count
        FROM bulk_messages bm
        LEFT JOIN message_views mv ON bm.id = mv.message_id AND mv.borrower_id = '$borrower_id'
        WHERE mv.id IS NULL
    ");
    if ($unread_qry && $unread_qry->num_rows > 0) {
        $unread_messages_count = $unread_qry->fetch_assoc()['unread_count'];
    }
}
// --- END NEW ---
?>

<style>
    /* Custom styles for the dashboard cards (reused from admin for consistency) */
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
        background-color: #e9f7ef; /* Light green background for welcome card */
        border-left: 5px solid #28a745; /* Accent border */
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
                        $firstName = isset($_SESSION['login_firstname']) ? $_SESSION['login_firstname'] : '';
                        $lastName = isset($_SESSION['login_lastname']) ? $_SESSION['login_lastname'] : 'Member';
                        echo "Welcome back, " . htmlspecialchars($firstName) . " " . htmlspecialchars($lastName) . "!";
                        ?>
                    </h4>
                    <p class="text-muted mt-2 mb-0">Here's a quick overview of your Vikoba Management System account.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row ml-2 mr-2">
        <div class="col-md-4 mb-4">
            <div class="card bg-secondary text-white card-dashboard">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mr-3">
                            <div class="text-white-75 small">Your Active Loans</div>
                            <div class="text-lg font-weight-bold">
                                <?php
                                $loans_active_query = $conn->query("SELECT * FROM loan_list WHERE status = 2 AND borrower_id = '$borrower_id'");
                                echo $loans_active_query->num_rows;
                                ?>
                            </div>
                        </div>
                        <i class="fa fa-hand-holding-usd"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="u-index.php?page=u-loans">View All Loans</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card bg-primary text-white card-dashboard">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mr-3">
                            <div class="text-white-75 small">Your Total Savings</div>
                            <div class="text-lg font-weight-bold">
                                <?php
                                $savings_query = $conn->query("SELECT SUM(amount) AS total_savings FROM savings WHERE borrower_id = '$borrower_id'");
                                $savings_result = $savings_query->fetch_assoc();
                                $total_savings = $savings_result['total_savings'] ? $savings_result['total_savings'] : 0;
                                echo 'Tsh: ' . number_format($total_savings, 2);
                                ?>
                            </div>
                        </div>
                        <i class="fa fa-piggy-bank"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="u-index.php?page=u-saving">View Savings History</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card bg-info text-white card-dashboard">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mr-3">
                            <div class="text-white-75 small">Current Loan Balance</div>
                            <div class="text-lg font-weight-bold">
                                <?php
                                $total_loan_amount = 0;
                                $loans_owed_query = $conn->query("SELECT ll.id, ll.amount, lp.interest_percentage FROM loan_list ll JOIN loan_plan lp ON ll.plan_id = lp.id WHERE ll.borrower_id = '$borrower_id' AND ll.status = 2");
                                while($loan = $loans_owed_query->fetch_assoc()){
                                    $loan_principal_interest = $loan['amount'] + ($loan['amount'] * ($loan['interest_percentage'] / 100));
                                    
                                    // Calculate total payments for this specific loan
                                    $payments_for_this_loan_query = $conn->query("SELECT SUM(amount) AS paid_amount FROM payments WHERE loan_id = ".$loan['id']);
                                    $paid_amount = $payments_for_this_loan_query->fetch_assoc()['paid_amount'] ?? 0;

                                    $total_loan_amount += ($loan_principal_interest - $paid_amount);
                                }
                                echo 'Tsh: ' . number_format($total_loan_amount, 2);
                                ?>
                            </div>
                        </div>
                        <i class="fa fa-coins"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="u-index.php?page=u-loans">View Your Loans</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card bg-warning text-white card-dashboard">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mr-3">
                            <div class="text-white-75 small">Total Loans Requested</div>
                            <div class="text-lg font-weight-bold">
                                <?php
                                $loans_requested_query = $conn->query("SELECT * FROM loan_list WHERE borrower_id = '$borrower_id'");
                                echo $loans_requested_query->num_rows;
                                ?>
                            </div>
                        </div>
                        <i class="fa fa-file-invoice-dollar"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="u-index.php?page=u-loans">View Loan Applications</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card bg-danger text-white card-dashboard">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mr-3">
                            <div class="text-white-75 small">Next Payment Due</div>
                            <div class="text-lg font-weight-bold">
                                <?php
                                // This query finds the earliest upcoming scheduled payment date for any active loan for this borrower.
                                $next_scheduled_payment_date_q = $conn->query("
                                    SELECT ls.date_due 
                                    FROM loan_schedules ls 
                                    JOIN loan_list ll ON ls.loan_id = ll.id 
                                    WHERE ll.borrower_id = '$borrower_id' 
                                    AND ll.status = 2 
                                    AND ls.date_due >= CURDATE()
                                    ORDER BY ls.date_due ASC
                                    LIMIT 1
                                ");
                                
                                if ($next_scheduled_payment_date_q->num_rows > 0) {
                                    $next_due_date = $next_scheduled_payment_date_q->fetch_assoc()['date_due'];
                                    echo date("M d, Y", strtotime($next_due_date));
                                } else {
                                    echo "No upcoming payments";
                                }
                                ?>
                            </div>
                        </div>
                        <i class="fa fa-calendar-check"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="#">Loan Schedule</a>
                    <!-- <div class="small text-white"><i class="fas fa-angle-right"></i></div> -->
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card bg-success text-white card-dashboard">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mr-3">
                            <div class="text-white-75 small">Unread Messages</div>
                            <div class="text-lg font-weight-bold">
                                <?php echo $unread_messages_count; ?>
                            </div>
                        </div>
                        <i class="fa fa-bell"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="u-index.php?page=u-bulk_messages">View Messages</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        </div> 

    <div class="row ml-2 mr-2">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5>Recent Loan Activities</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Loan Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $recent_loans_query = $conn->query("SELECT ll.*, lt.type_name FROM loan_list ll JOIN loan_types lt ON ll.loan_type_id = lt.id WHERE ll.borrower_id = '$borrower_id' ORDER BY ll.date_created DESC LIMIT 5");
                                if ($recent_loans_query->num_rows > 0) {
                                    $status_map = [
                                        0 => 'Submitted', 
                                        1 => 'Confirmed', 
                                        2 => 'Released', 
                                        3 => 'Completed', 
                                        4 => 'Denied'
                                    ];
                                    while ($row = $recent_loans_query->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . date("M d, Y", strtotime($row['date_created'])) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['type_name']) . "</td>";
                                        echo "<td>" . number_format($row['amount'], 2) . "</td>";
                                        echo "<td><span class='badge ";
                                        if($row['status'] == 2) echo "badge-success";
                                        else if($row['status'] == 4) echo "badge-danger";
                                        else echo "badge-info"; // For pending/confirmed
                                        echo "'>" . ($status_map[$row['status']] ?? 'Unknown') . "</span></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>No recent loan activities.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5>Recent Savings Contributions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $recent_savings_query = $conn->query("SELECT * FROM savings WHERE borrower_id = '$borrower_id' ORDER BY savings_date DESC LIMIT 5");
                                if ($recent_savings_query->num_rows > 0) {
                                    while ($row = $recent_savings_query->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . date("M d, Y", strtotime($row['savings_date'])) . "</td>";
                                        echo "<td>" . number_format($row['amount'], 2) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['notes'] ?? 'N/A') . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3' class='text-center'>No recent savings contributions.</td></tr>";
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

<script>
    // Any specific JavaScript for this page can go here.
</script>