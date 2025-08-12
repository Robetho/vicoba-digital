<?php include 'db_connect.php'; ?>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <large class="card-title">
                    <b>Loan List</b>
                    <a href="export_loans_excel.php" class="btn btn-success btn-sm" target="_blank"><i class="fa fa-file-excel"></i> Export All Loans</a>
                </large>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="loan-list">
                    <colgroup>
                        <col width="5%">
                        <col width="20%">
                        <col width="25%">
                        <col width="20%">
                        <col width="10%">
                        <col width="20%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Borrower</th>
                            <th class="text-center">Loan Details</th>
                            <th class="text-center">Next Payment Details</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $i=1;
                            $type_arr = [];
                            $plan_arr = [];

                            // Fetch loan types
                            $type_qry = $conn->query("SELECT * FROM loan_types WHERE id IN (SELECT loan_type_id FROM loan_list)");
                            while($row = $type_qry->fetch_assoc()){
                                $type_arr[$row['id']] = $row['type_name'];
                            }

                            // Fetch loan plans
                            $plan_qry = $conn->query("SELECT *, CONCAT(months,' month/s [ ',interest_percentage,'%, ',penalty_rate,' ]') as plan FROM loan_plan WHERE id IN (SELECT plan_id FROM loan_list)");
                            while($row = $plan_qry->fetch_assoc()){
                                $plan_arr[$row['id']] = $row;
                            }

                            // Fetch all loan data
                            $qry = $conn->query("SELECT l.*, CONCAT(b.lastname,', ',b.firstname,' ',b.middlename) AS name, b.contact_no, b.address FROM loan_list l INNER JOIN borrowers b ON b.id = l.borrower_id ORDER BY id ASC");

                            while($row = $qry->fetch_assoc()):
                                $monthly = ($row['amount'] + ($row['amount'] * ($plan_arr[$row['plan_id']]['interest_percentage']/100))) / $plan_arr[$row['plan_id']]['months'];
                                $penalty = $monthly * ($plan_arr[$row['plan_id']]['penalty_rate']/100);

                                $payments_qry = $conn->query("SELECT * FROM payments WHERE loan_id =".$row['id']);
                                $paid_count = $payments_qry->num_rows;
                                $offset_str = $paid_count > 0 ? " OFFSET $paid_count " : "";

                                $next_due_date = null;
                                if ($row['status'] == 2) { // Only if released
                                    $next_schedule_qry = $conn->query("SELECT date_due FROM loan_schedules WHERE loan_id = '".$row['id']."' ORDER BY date(date_due) ASC LIMIT 1 $offset_str ");
                                    if ($next_schedule_qry && $next_schedule_qry->num_rows > 0) {
                                        $next_due_date = $next_schedule_qry->fetch_assoc()['date_due'];
                                    }
                                }

                                // Sum of actual paid amounts (excluding penalty from sum)
                                $sum_paid = 0;
                                $payments_qry_sum = $conn->query("SELECT amount, penalty_amount FROM payments WHERE loan_id =".$row['id']);
                                while($p = $payments_qry_sum->fetch_assoc()){
                                    $sum_paid += ($p['amount'] - $p['penalty_amount']);
                                }
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $i++ ?></td>
                            <td>
                                <p>Name :<b><?php echo htmlspecialchars($row['name']) ?></b></p>
                                <p><small>Contact # :<b><?php echo htmlspecialchars($row['contact_no']) ?></small></b></p>
                                <p><small>Address :<b><?php echo htmlspecialchars($row['address']) ?></small></b></p>
                            </td>
                            <td>
                                <p>Reference :<b><?php echo htmlspecialchars($row['ref_no']) ?></b></p>
                                <p><small>Loan type :<b><?php echo htmlspecialchars($type_arr[$row['loan_type_id']]) ?></small></b></p>
                                <p><small>Plan :<b><?php echo htmlspecialchars($plan_arr[$row['plan_id']]['plan']) ?></small></b></p>
                                <p><small>Amount :<b><?php echo number_format($row['amount'], 2) ?></small></b></p>
                                <p><small>Total Payable Amount :<b><?php echo number_format($monthly * $plan_arr[$row['plan_id']]['months'],2) ?></small></b></p>
                                <p><small>Monthly Payable Amount: <b><?php echo number_format($monthly,2) ?></small></b></p>
                                <p><small>Overdue Penalty Rate: <b><?php echo number_format($plan_arr[$row['plan_id']]['penalty_rate'], 2) ?>%</small></b></p>
                                <?php if($row['status'] == 2 || $row['status'] == 3): ?>
                                <p><small>Date Released: <b><?php echo date("M d, Y",strtotime($row['date_released'])) ?></small></b></p>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['status'] == 2 && !empty($next_due_date)): ?>
                                <p>Date: <b><?php echo date('M d, Y',strtotime($next_due_date)); ?></b></p>
                                <p><small>Monthly amount:<b><?php echo number_format($monthly,2) ?></b></small></p>
                                <?php
                                    // Calculate penalty for next payment if overdue
                                    $current_penalty_amount = 0;
                                    if (date('Ymd',strtotime($next_due_date)) < date("Ymd") ) {
                                        $current_penalty_amount = $penalty;
                                    }
                                ?>
                                <p><small>Penalty :<b><?php echo number_format($current_penalty_amount, 2); ?></b></small></p>
                                <p><small>Payable Amount :<b><?php echo number_format($monthly + $current_penalty_amount,2) ?></b></small></p>
                                <?php else: ?>
                                    N/a
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($row['status'] == 0): ?>
                                    <span class="badge badge-warning">For Approval</span>
                                <?php elseif($row['status'] == 1): ?>
                                    <span class="badge badge-info">Approved</span>
                                <?php elseif($row['status'] == 2): ?>
                                    <span class="badge badge-primary">Released</span>
                                <?php elseif($row['status'] == 3): ?>
                                    <span class="badge badge-success">Completed</span>
                                <?php elseif($row['status'] == 4): ?>
                                    <span class="badge badge-danger">Denied</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                    <button class="btn btn-outline-primary btn-sm edit_loan" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-outline-danger btn-sm delete_loan" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash"></i></button>
                                    <?php if($row['status'] == 2): ?>
                                    <div style="margin-top:5px;">
                                        <a href="export_borrower_loans.php?borrower_id=<?php echo $row['borrower_id'] ?>" target="_blank" class="btn btn-sm btn-success" style="width:100px;">
                                            <i class="fa fa-download"></i> Export
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<style>
    td p {
        margin:unset;
    }
    td img {
        width: 8vw;
        height: 12vh;
    }
    td{
        vertical-align: middle !important;
    }
</style>
<script>
    $('#loan-list').dataTable();
    $('#new_application').click(function(){
        uni_modal("New Loan Application","manage_loan.php",'mid-large');
    });
    $('.edit_loan').click(function(){
        uni_modal("Edit Loan","manage_loan.php?id="+$(this).attr('data-id'),'mid-large');
    });
    $('.delete_loan').click(function(){
        _conf("Are you sure to delete this data?","delete_loan",[$(this).attr('data-id')]);
    });
    function delete_loan($id){
        start_load();
        $.ajax({
            url:'ajax.php?action=delete_loan',
            method:'POST',
            data:{id:$id},
            success:function(resp){
                if(resp==1){
                    alert_toast("Loan successfully deleted",'success');
                    setTimeout(function(){
                        location.reload();
                    },1500);
                } else {
                    alert_toast("Failed to delete loan.",'error'); // Added basic error feedback
                    end_load();
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
                alert_toast("An error occurred during deletion.", 'error');
                end_load();
            }
        });
    }
</script>