<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Ensure the logged-in user is an admin (type 1)
if (!isset($_SESSION['login_id']) || ($_SESSION['login_type'] ?? '') != 1) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

if (isset($_GET['id']) && $_GET['id'] > 0) {
    $id = $_GET['id'];
    // Select the message and borrower information
    $qry = $conn->query("SELECT um.*, CONCAT(b.firstname, ' ', b.middlename, ' ', b.lastname) AS borrower_name FROM user_messages um INNER JOIN borrowers b ON b.id = um.borrower_id WHERE um.id = $id");
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_array() as $k => $v) {
            $$k = $v;
        }
        // Mark message as read if it's currently unread
        if ($status == 0) {
            $conn->query("UPDATE user_messages SET status = 1 WHERE id = $id");
            // Refresh status variable to reflect the change
            $status = 1;
        }
    } else {
        echo "<p class='text-danger'>Message not found.</p>";
        exit();
    }
} else {
    echo "<p class='text-danger'>Invalid message ID.</p>";
    exit();
}
?>

<div class="container-fluid">
    <dl>
        <dt><b class="border-bottom border-primary">From Borrower</b></dt>
        <dd><?php echo ucwords(htmlspecialchars($borrower_name)); ?></dd>
        <dt><b class="border-bottom border-primary">Subject</b></dt>
        <dd><?php echo ucwords(htmlspecialchars($subject)); ?></dd>
        <dt><b class="border-bottom border-primary">Message</b></dt>
        <dd><?php echo nl2br(htmlspecialchars($message_content)); ?></dd>
        <dt><b class="border-bottom border-primary">Date Sent</b></dt>
        <dd><?php echo date('M d, Y H:i A', strtotime($date_sent)); ?></dd>
        <dt><b class="border-bottom border-primary">Status</b></dt>
        <dd>
            <?php if ($status == 0): ?>
                <span class="badge badge-warning">Unread</span>
            <?php else: ?>
                <span class="badge badge-success">Read</span>
            <?php endif; ?>
        </dd>
    </dl>
</div>
<div class="modal-footer display p-0 m-0">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>
<style>
    #uni_modal .modal-footer {
        display: none
    }

    #uni_modal .modal-footer.display {
        display: flex
    }
</style>