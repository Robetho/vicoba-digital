<?php
// Ensure session is started for $_SESSION['login_id']
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Check if user is logged in as a borrower (type 2 or similar)
if (!isset($_SESSION['login_id']) || ($_SESSION['login_type'] ?? '') != 2) { // Adjust 'login_type' if your borrower type is different
    header("Location: u-login.php"); // Redirect to borrower login if not authenticated
    exit();
}

include 'db_connect.php'; // Include your database connection

?>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <large class="card-title">
                    <b>My Messages</b>
                    <button class="btn btn-primary btn-sm btn-block col-md-2 float-right" type="button" id="new_message"><i class="fa fa-plus"></i> New Message to Admin</button>
                </large>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="message-list">
                    <colgroup>
                        <col width="5%">
                        <col width="20%">
                        <col width="40%">
                        <col width="15%">
                        <col width="10%">
                        <col width="10%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Subject</th>
                            <th class="text-center">Message</th>
                            <th class="text-center">Date Sent</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $i = 1;
                            $borrower_id = $_SESSION['login_id']; // Assuming 'login_id' holds the borrower's ID
                            $qry = $conn->query("SELECT * FROM user_messages WHERE borrower_id = '$borrower_id' ORDER BY date_sent DESC");
                            while ($row = $qry->fetch_assoc()):
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td><?php echo strip_tags(substr($row['message_content'], 0, 100)); ?>...</td>
                            <td class="text-center"><?php echo date('M d, Y H:i A', strtotime($row['date_sent'])); ?></td>
                            <td class="text-center">
                                <?php if ($row['status'] == 0): ?>
                                    <span class="badge badge-warning">Unread</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Read</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-outline-info btn-sm view_message" type="button" data-id="<?php echo $row['id']; ?>"><i class="fa fa-eye"></i> View</button>
                                <button class="btn btn-outline-danger btn-sm delete_message" type="button" data-id="<?php echo $row['id']; ?>"><i class="fa fa-trash"></i></button>
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
        margin: unset;
    }
    td {
        vertical-align: middle !important;
    }
</style>

<script>
    $(document).ready(function() {
        $('#message-list').dataTable();

        $('#new_message').click(function() {
            uni_modal("New Message to Admin", "u-manage-message.php", 'mid-large');
        });

        $('.view_message').click(function() {
            uni_modal("View Message", "u-view-message.php?id=" + $(this).attr('data-id'), 'mid-large');
        });

        $('.delete_message').click(function() {
            _conf("Are you sure you want to delete this message?", "delete_message", [$(this).attr('data-id')]);
        });
    });

    function delete_message($id) {
        start_load();
        $.ajax({
            url: 'ajax.php?action=delete_user_message',
            method: 'POST',
            data: { id: $id },
            success: function(resp) {
                if (resp == 1) {
                    alert_toast("Message successfully deleted", 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    alert_toast("Failed to delete message", 'error');
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