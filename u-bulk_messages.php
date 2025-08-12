<?php 
// db_connect.php inapaswa kuwa included hapa au juu ya u-index.php
include('db_connect.php'); 

// Get the connection from your CRUD class
// Hii mstari nilikuwa nimeuweka comment, lakini ni muhimu sana
// Kama $conn tayari inapatikana kutoka db_connect.php, basi hii si lazima
// Lakini kama db_connect.php inafafanua $crud tu na sio $conn, basi utahitaji:
// $conn = $crud->getDbConnection(); 

// Start the session if it hasn't been started already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get the borrower ID from session (assuming this page is for borrowers)
$borrower_id = isset($_SESSION['login_id']) ? $_SESSION['login_id'] : 0;

// Redirect if not logged in or not the correct user type
if ($borrower_id == 0) {
    header("location:login.php?status=missed_id"); 
    exit();
}

// Kosa la "Call to a member function getDbConnection() on null" lilikuwa linatokana na hapa.
// Hakikisha $conn inapatikana hapa. Ikiwa db_connect.php inaunganisha database na kuweka $conn global,
// basi hauhitaji $crud->getDbConnection();
// Lakini kama db_connect.php inafafanua class ya Action na $crud object, basi unahitaji:
if (!isset($conn)) { // Angalia kama $conn haijafafanuliwa tayari
    if (isset($crud) && method_exists($crud, 'getDbConnection')) {
        $conn = $crud->getDbConnection(); // Pata connection kutoka $crud
    } else {
        // Handle error: $conn or $crud not properly initialized
        die("Database connection not available. Please check db_connect.php.");
    }
}


// Mark all current bulk messages as "viewed" by this borrower
if ($conn) {
    $bulk_messages_ids = [];
    $qry_all_messages = $conn->query("SELECT id FROM bulk_messages");
    if ($qry_all_messages) { // Check if query was successful
        while($row = $qry_all_messages->fetch_assoc()){
            $bulk_messages_ids[] = $row['id'];
        }
    
        if (!empty($bulk_messages_ids)) {
            foreach ($bulk_messages_ids as $message_id) {
                // Use INSERT IGNORE to avoid duplicate entries if a message is already marked as viewed
                $insert_view = $conn->query("INSERT IGNORE INTO message_views (message_id, borrower_id) VALUES ('$message_id', '$borrower_id')");
                // Optional: Add error logging if $insert_view is false
                // if (!$insert_view) { error_log("Failed to insert message view: " . $conn->error); }
            }
        }
    } else {
        // Handle error if bulk_messages table doesn't exist or query fails
        // error_log("Error querying bulk_messages: " . $conn->error);
    }
}
?>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <large class="card-title">
                    <b>Your Messages</b>
                    </large>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>Subject</th>
                            <th>Message Preview</th>
                            <th>Date Sent</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        // Rekebisha query ili kuangalia kama ujumbe umesomwa
                        $qry = $conn->query("
                            SELECT bm.*, mv.id AS view_id
                            FROM bulk_messages bm
                            LEFT JOIN message_views mv ON bm.id = mv.message_id AND mv.borrower_id = '$borrower_id'
                            ORDER BY bm.date_sent DESC
                        ");
                        if ($qry && $qry->num_rows > 0): // Check if query was successful and has rows
                            while ($row = $qry->fetch_assoc()):
                                $is_new = is_null($row['view_id']); // Check if view_id is NULL
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $i++; ?></td>
                            <td>
                                <b><?php echo ucwords(htmlspecialchars($row['subject'])); ?></b>
                                <?php if ($is_new): ?>
                                    <span class="badge badge-primary ml-2">NEW</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo nl2br(htmlspecialchars(substr($row['message_content'], 0, 200))); ?>...</td>
                            <td><?php echo date("M d, Y h:i A", strtotime($row['date_sent'])); ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary view_message" type="button" 
                                    data-id="<?php echo $row['id']; ?>" 
                                    data-subject="<?php echo htmlspecialchars($row['subject']); ?>"
                                    data-content="<?php echo htmlspecialchars($row['message_content']); ?>">
                                    View Full Message
                                </button>
                                </td>
                        </tr>
                        <?php endwhile; 
                        else: // If no messages found or query failed
                        ?>
                        <tr>
                            <td colspan="5" class="text-center">No messages available.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="message_viewer_modal" tabindex="-1" role="dialog" aria-labelledby="messageViewerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageViewerModalLabel">Message: <span id="modal_message_subject"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="modal_message_content" style="white-space: pre-wrap;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('.view_message').click(function(){
            var subject = $(this).data('subject');
            var content = $(this).data('content'); // Get the full content
            var message_id = $(this).data('id');

            $('#modal_message_subject').text(subject);
            $('#modal_message_content').text(content); // Set the full content
            $('#message_viewer_modal').modal('show');

            // Optional: You might want to remove the 'NEW' badge dynamically after viewing
            // However, the PHP logic handles marking as viewed on page load.
            // If you want instant feedback without page reload, you would need an AJAX call here
            // to update the `message_views` table for this specific message.
            // For now, the page reload (or next visit) will handle it.
        });

        // The "send_bulk_message" button was removed as it's admin functionality.
        // If it was meant to be here, please clarify its purpose for a borrower.
    });
</script>