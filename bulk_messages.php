<?php include('db_connect.php'); ?>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <large class="card-title">
                    <b>Sent Bulk Messages</b>
                    <button class="btn btn-info btn-block col-md-2 float-right mr-2" type="button" id="send_bulk_message"><i class="fa fa-envelope"></i> Send Bulk Message</button>
                </large>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="message-list">
                    <colgroup>
                        <col width="5%">
                        <col width="20%">
                        <col width="45%">
                        <col width="15%">
                        <col width="15%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Subject</th>
                            <th class="text-center">Message Content</th>
                            <th class="text-center">Date Sent</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        $qry = $conn->query("SELECT * FROM bulk_messages ORDER BY date_sent DESC");
                        while ($row = $qry->fetch_assoc()):
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $i++; ?></td>
                            <td><b><?php echo ucwords($row['subject']); ?></b></td>
                            <td><?php echo nl2br(htmlspecialchars(substr($row['message_content'], 0, 200))); ?>...</td>
                            <td><?php echo date("M d, Y h:i A", strtotime($row['date_sent'])); ?></td>
                            <td class="text-center">
                                <!-- <button class="btn btn-sm btn-outline-primary view_message" type="button" data-id="<?php echo $row['id']; ?>" data-subject="<?php echo htmlspecialchars($row['subject']); ?>">View Full Message</button> -->
                                <button class="btn btn-sm btn-outline-danger delete_message" type="button" data-id="<?php echo $row['id']; ?>">Delete</button>
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
    $(document).ready(function(){
        // Initialize DataTable
        $('#message-list').dataTable();

        // Handle View Full Message click - REKEBISHA HII SECTION
        $('.view_message').click(function(){
            var message_id = $(this).data('id');
            var subject = $(this).data('subject'); // Bado tunatumia subject hapa
            start_load(); // Show loading indicator

            $.ajax({
                url: 'ajax.php?action=get_single_bulk_message', // Tutaunda action hii mpya
                method: 'POST',
                data: {id: message_id},
                dataType: 'json', // Tunategemea jibu la JSON
                success: function(resp){
                    if(resp && resp.status == 'success'){
                        uni_modal(subject, '<div style="padding: 10px;">' + nl2br(resp.message_content) + '</div>', 'mid-large');
                    } else {
                        alert_toast("Failed to load message: " + (resp ? resp.message : "Unknown error"), 'error');
                    }
                    end_load(); // Hide loading indicator
                },
                error: function(xhr, status, error) {
                    alert_toast("AJAX Error: " + error + ". Response: " + xhr.responseText, 'error');
                    end_load(); // Hide loading indicator
                }
            });
        });

        // Handle Delete Message click
        $('.delete_message').click(function(){
            _conf("Are you sure to delete this message permanently?", "delete_message", [$(this).data('id')]);
        });
    });

    $('#send_bulk_message').click(function(){
        uni_modal("Send Bulk Message to Borrowers","manage_bulk_message.php",'mid-large');
    });

    // Function to delete message (will call ajax.php)
    function delete_message(id){
        start_load();
        $.ajax({
            url: 'ajax.php?action=delete_bulk_message',
            method: 'POST',
            data: {id: id},
            success: function(resp){
                if(resp == 1){ // Note: PHP should echo 1 for success
                    alert_toast("Message successfully deleted", 'success');
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                } else {
                    alert_toast("Error deleting message: " + resp, 'error'); // Display error from PHP if any
                }
                end_load();
            },
            error: function(xhr, status, error) {
                alert_toast("AJAX Error: " + error, 'error');
                end_load();
            }
        });
    }

    // Helper function for nl2br (if not globally available)
    function nl2br (str, is_xhtml) {
        if (typeof str === 'undefined' || str === null) {
            return '';
        }
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    }
</script>