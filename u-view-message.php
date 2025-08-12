<?php
// Ensure session is started for $_SESSION['login_id']
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

if (isset($_GET['id']) && $_GET['id'] > 0) {
    $id = $_GET['id'];
    $borrower_id = $_SESSION['login_id']; // Ensure only the owner can view their message
    $qry = $conn->query("SELECT * FROM user_messages WHERE id = $id AND borrower_id = $borrower_id");
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_array() as $k => $v) {
            $$k = $v;
        }
        // Mark message as read when viewed
        if ($status == 0) { // Only update if it's currently unread
            $conn->query("UPDATE user_messages SET status = 1 WHERE id = $id");
        }
    } else {
        echo "<p class='text-danger'>Message not found or you don't have permission to view it.</p>";
        // Ensure no further HTML is output if message is not found
        exit();
    }
} else {
    echo "<p class='text-danger'>Invalid message ID.</p>";
    exit(); // Ensure no further HTML is output for invalid ID
}
?>

<div class="container-fluid">
    <dl>
        <dt><b class="border-bottom border-primary">Subject</b></dt>
        <dd><?php echo ucwords(htmlspecialchars($subject)); ?></dd>
        <dt><b class="border-bottom border-primary">Message</b></dt>
        <dd><?php echo nl2br(htmlspecialchars($message_content)); ?></dd>
        <dt><b class="border-bottom border-primary">Date Sent</b></dt>
        <dd><?php echo date('M d, Y H:i A', strtotime($date_sent)); ?></dd>
        <dt><b class="border-bottom border-primary">Status</b></dt>
        <dd>
            <?php if ($status == 0): // Display original status before potential update ?>
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