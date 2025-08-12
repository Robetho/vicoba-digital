<?php
// MUHIMU: db_connect.php inapaswa kuwa included mapema kabisa kwenye u-index.php
// ili $conn object iweze kupatikana hapa.
// Ondoa include 'db_connect.php'; hapa ikiwa tayari umeiiweka kwenye u-index.php
// Kwa sasa, tunadhania $conn inapatikana globally.

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$borrower_id = isset($_SESSION['login_id']) ? $_SESSION['login_id'] : 0;

// Debugging: Angalia borrower_id
// echo "Borrower ID: " . $borrower_id . "<br>";

$unread_messages_nav_count = 0;

if ($borrower_id > 0) {
  

    if (isset($conn) && $conn) {
        $unread_qry_nav = $conn->query("
            SELECT COUNT(bm.id) AS unread_count
            FROM bulk_messages bm
            LEFT JOIN message_views mv ON bm.id = mv.message_id AND mv.borrower_id = '$borrower_id'
            WHERE mv.id IS NULL
        ");
        if ($unread_qry_nav) {
            if ($unread_qry_nav->num_rows > 0) {
                $unread_messages_nav_count = $unread_qry_nav->fetch_assoc()['unread_count'];
            }
            // Debugging: Onyesha matokeo ya query
            // echo "Unread messages count from query: " . $unread_messages_nav_count . "<br>";
        } else {
            // Debugging: Onyesha error ya database
            // echo "Database Query Error: " . $conn->error . "<br>";
        }
    }
}
?>
<nav id="sidebar" class='mx-lt-5 bg-dark' >
		
		<div class="sidebar-list">

				<a href="u-index.php?page=u-home" class="nav-item nav-home"><span class='icon-field'><i class="fa fa-home"></i></span> Home</a>
				<a href="u-index.php?page=u-saving" class="nav-item nav-saving"><span class='icon-field'><i class="fa fa-file-invoice-dollar"></i></span> Saving</a>
				<a href="u-index.php?page=u-loans" class="nav-item nav-loans"><span class='icon-field'><i class="fa fa-file-invoice-dollar"></i></span> Loans</a>	
				
				<a href="u-index.php?page=u-payments" class="nav-item nav-payments"><span class='icon-field'><i class="fa fa-money-bill"></i></span> Payments</a>
				<a href="u-index.php?page=u-plan" class="nav-item nav-plan"><span class='icon-field'><i class="fa fa-list-alt"></i></span> Loan Plans</a>
				<a href="u-index.php?page=u-messages" class="nav-item nav-plan"><span class='icon-field'><i class="fas fa-envelope"></i></span> Message to Chairperson</a>	
				
				<a class="nav-link nav-u-bulk_messages" href="u-index.php?page=u-bulk_messages">
		 		<i class="fas fa-envelope"></i>
		 		<span>Messages</span>
		 		<?php if ($unread_messages_nav_count > 0): ?>
		 				<span class="badge badge-danger ml-1"><?php echo $unread_messages_nav_count; ?></span>
		 		<?php endif; ?>
		 		</a>
				<a href="u-index.php?page=u-loan-type" class="nav-item nav-loan-type"><span class='icon-field'><i class="fa fa-th-list"></i></span> Loan Types</a>		
		</div>

</nav>
<script>
	$('.nav-<?php echo isset($_GET['page']) ? $_GET['page'] : '' ?>').addClass('active')
</script>