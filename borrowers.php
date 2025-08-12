<?php include 'db_connect.php' ?>

<div class="container-fluid">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header">
				<large class="card-title">
					<b>Borrower List</b>
				</large>
				<button class="btn btn-primary btn-block col-md-2 float-right" type="button" id="new_borrower"><i class="fa fa-plus"></i> New Borrower</button>
				
				</div>
			<div class="card-body">
				<table class="table table-bordered" id="borrower-list">
					<colgroup>
						<col width="10%">
						<col width="35%">
						<col width="30%">
						<col width="15%">
						<col width="10%">
					</colgroup>
					<thead>
						<tr>
							<th class="text-center">#</th>
							<th class="text-center">Borrower</th>
							<th class="text-center">Current Loan</th>
							<th class="text-center">Next Payment Schedule</th>
							<th class="text-center">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 1;
							$qry = $conn->query("SELECT * FROM borrowers order by id desc");
							while($row = $qry->fetch_assoc()):

						 ?>
						 <tr>

						 	<td class="text-center"><?php echo $i++ ?></td>
						 	<td>
						 		<p>Name :<b><?php echo ucwords($row['lastname'].", ".$row['firstname'].' '.$row['middlename']) ?></b></p>
						 		<p><small>Address :<b><?php echo $row['address'] ?></small></b></p>
						 		<p><small>Contact # :<b><?php echo $row['contact_no'] ?></small></b></p>
						 		<p><small>Email :<b><?php echo $row['email'] ?></small></b></p>
						 		<p><small>Tax ID :<b><?php echo $row['tax_id'] ?></small></b></p>

						 	</td>
						 	<td class="">None</td>
						 	<td class="">N/A</td>
						 	<td class="text-center">
						 			<button class="btn btn-outline-primary btn-sm edit_borrower" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-edit"></i></button>
						 			<button class="btn btn-outline-danger btn-sm delete_borrower" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash"></i></button>
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
	// Initialize DataTable
	$('#borrower-list').dataTable();

	// Kazi ya kufungua modal mpya ya borrower
	$('#new_borrower').click(function(){
		uni_modal("New borrower","manage_borrower.php",'mid-large');
	});

	// Kazi ya kufungua modal ya kuhariri borrower
	$('.edit_borrower').click(function(){
		uni_modal("Edit borrower","manage_borrower.php?id="+$(this).attr('data-id'),'mid-large');
	});

	// Kazi ya kufuta borrower
	$('.delete_borrower').click(function(){
		_conf("Are you sure to delete this borrower?","delete_borrower",[$(this).attr('data-id')]);
	});

	// Kazi ya kufungua modal ya kutuma ujumbe mwingi kwa borrowers
	$('#send_bulk_message').click(function(){
		uni_modal("Send Bulk Message to Borrowers","manage_bulk_message.php",'mid-large');
	});

	// Kazi halisi ya kufuta borrower (inayotumwa na _conf)
	function delete_borrower($id){
		start_load(); // Anza kuonyesha upakiaji
		$.ajax({
			url:'ajax.php?action=delete_borrower', // Elekeza kwenye ajax.php na action ya kufuta
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Borrower successfully deleted",'success'); // Onyesha ujumbe wa mafanikio
					setTimeout(function(){
						location.reload(); // Pakia upya ukurasa baada ya muda
					},1500);
				} else {
					alert_toast("Failed to delete borrower. Please try again.",'error'); // Onyesha ujumbe wa kosa
					end_load(); // Maliza kuonyesha upakiaji
				}
			},
			error: function(xhr, status, error) {
                console.error("AJAX Error: ", status, error);
                alert_toast("An error occurred. Check console for details.",'error');
                end_load();
            }
		});
	}
</script>