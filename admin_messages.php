<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Ensure the logged-in user is an admin (type 1)
if (!isset($_SESSION['login_id']) || ($_SESSION['login_type'] ?? '') != 1) { // Adjust 'login_type' if your admin type is different
    header("Location: login.php"); // Redirect to login page if not authenticated as admin
    exit();
}

include 'db_connect.php'; // Include your database connection

?>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <large class="card-title">
                    <b>Messages from Borrowers</b>
                </large>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="admin-message-list">
                    <colgroup>
                        <col width="5%">
                        <col width="15%">
                        <col width="20%">
                        <col width="30%">
                        <col width="15%">
                        <col width="5%">
                        <col width="10%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Borrower</th>
                            <th class="text-center">Subject</th>
                            <th class="text-center">Message (Summary)</th>
                            <th class="text-center">Date Sent</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $i = 1;
                            // Select all messages from borrowers, join with borrower information
                            $qry = $conn->query("SELECT um.*, CONCAT(b.firstname, ' ', b.middlename, ' ', b.lastname) AS borrower_name FROM user_messages um INNER JOIN borrowers b ON b.id = um.borrower_id ORDER BY um.date_sent DESC");
                            while ($row = $qry->fetch_assoc()):
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['borrower_name']); ?></td>
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
                                <button class="btn btn-outline-info btn-sm view_user_message" type="button" data-id="<?php echo $row['id']; ?>"><i class="fa fa-eye"></i> View</button>
                                <button class="btn btn-outline-danger btn-sm delete_user_message_admin" type="button" data-id="<?php echo $row['id']; ?>"><i class="fa fa-trash"></i></button>
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
        $('#admin-message-list').dataTable();

        // Function to view message
        $('.view_user_message').click(function() {
            uni_modal("View Message", "admin_view_user_message.php?id=" + $(this).attr('data-id'), 'mid-large');
        });

        // Function to delete message (for admin)
        $('.delete_user_message_admin').click(function() {
            _conf("Are you sure you want to delete this message?", "delete_user_message_admin", [$(this).attr('data-id')]);
        });
    });

    function delete_user_message_admin($id) {
        start_load();
        $.ajax({
            url: 'ajax.php?action=delete_user_message_admin', // New action for admin
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