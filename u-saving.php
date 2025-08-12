<?php include 'db_connect.php' ?>

<div class="container-fluid">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header">
				<large class="card-title">
					<b>My Saving Lists</b>
					<a href="u-index.php?page=u-add-saving" class="btn btn-primary btn-sm btn-block col-md-2 float-right"><i class="fa fa-plus"></i> New Saving</a>
				</large>
				
			</div>
			<div class="card-body">
				<table class="table table-bordered" id="loan-list">
					<colgroup>
						<col width="3%">
						<col width="12%">
						<col width="15%">
						<col width="40%">
					</colgroup>
					<thead>
						<tr>
							<th class="text-center">#</th>
							<th class="text-center">Amount</th>
							<th class="text-center">Date</th>
							<th class="text-center">Note</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 1;
						$id = $_SESSION['login_id'];
							$qry = $conn->query("SELECT savings.*, borrowers.* FROM savings JOIN borrowers ON borrowers.id = savings.borrower_id WHERE savings.borrower_id = '$id' ORDER BY savings.savings_date DESC");
						while($row = $qry->fetch_assoc()): ?>
						  <tr>
						    <td><?= $i++ ?></td>
						    <td><?= number_format($row['amount'], 2) ?></td>
						    <td><?= $row['savings_date'] ?></td>
						    <td><?= $row['notes'] ?></td>
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
	$('#loan-list').dataTable()
	$('#new_payments').click(function(){
		uni_modal("New Payement","u-manage-payment.php",'mid-large')
	})
	$('.edit_payment').click(function(){
		uni_modal("Edit Payement","u-manage-payment.php?id="+$(this).attr('data-id'),'mid-large')
	})
	$('.delete_payment').click(function(){
		_conf("Are you sure to delete this data?","delete_payment",[$(this).attr('data-id')])
	})
function delete_payment($id){
		start_load()
		$.ajax({
			url:'ajax.php?action=delete_payment',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Payment successfully deleted",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
			}
		})
	}
</script>