<?php
// Ensure session is started for $_SESSION['login_id']
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// You might not need db_connect here if this file is only accessed via uni_modal
// and ajax.php handles the DB interaction. But if you were to load data, it would be needed.
// include 'db_connect.php';

// No direct data fetching needed here for a new message form,
// but for an 'edit' scenario, you'd fetch based on ID.
// This form is primarily for 'new' messages.
$id = $_GET['id'] ?? null; // To support potential future editing, though less likely for user-to-admin messages.
$subject = '';
$message_content = '';

// If this modal could be used to edit a *sent* message (less common for user-to-admin),
// you'd add fetch logic here:
// if ($id) {
//    $borrower_id = $_SESSION['login_id'];
//    $qry = $conn->query("SELECT * FROM user_messages WHERE id = $id AND borrower_id = $borrower_id");
//    if ($qry->num_rows > 0) {
//        $data = $qry->fetch_assoc();
//        $subject = $data['subject'];
//        $message_content = $data['message_content'];
//    }
// }
?>

<div class="container-fluid">
    <form action="" id="manage-user-message">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        <div id="msg"></div>
        <div class="form-group">
            <label for="subject" class="control-label">Subject</label>
            <input type="text" class="form-control" name="subject" required value="<?php echo htmlspecialchars($subject); ?>">
        </div>
        <div class="form-group">
            <label for="message_content" class="control-label">Message</label>
            <textarea name="message_content" id="message_content" cols="30" rows="10" class="form-control" required><?php echo htmlspecialchars($message_content); ?></textarea>
        </div>
    </form>
</div>
<script>
    $('#manage-user-message').submit(function(e){
        e.preventDefault();
        start_load();
        $('#msg').html('');
        $.ajax({
            url:'ajax.php?action=save_user_message',
            data: new FormData($(this)[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            type: 'POST',
            success:function(resp){
                if(resp == 1){
                    alert_toast("Message sent successfully.",'success');
                    setTimeout(function(){
                        location.reload();
                    },1500);
                } else if(resp == 2){
                    $('#msg').html('<div class="alert alert-danger">Subject and message are required.</div>');
                    end_load();
                } else {
                    $('#msg').html('<div class="alert alert-danger">Error sending message. Please try again.</div>');
                    end_load();
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error + "\nResponse: " + xhr.responseText);
                $('#msg').html('<div class="alert alert-danger">An unexpected error occurred. Check console for details.</div>');
                end_load();
            }
        });
    });
</script>