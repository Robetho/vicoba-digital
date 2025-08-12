<?php include 'db_connect.php' ?>

<div class="container-fluid">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header">
				<large class="card-title">
					<b>New Saving </b>
					<a href="u-index.php?page=u-saving" class="btn btn-primary btn-sm btn-block col-md-2 float-right"><i class="fa fa-arrow"></i> Back To Saving</a>
				</large>
				
			</div>
			<div class="card-body">
				<form action="u-save.php" method="POST" id="manage-user">
					<div class="form-group">
						<label for="name">Amount</label>
						<input type="number" name="amount" step="1000" class="form-control" required>
					</div>
					<div class="form-group">
						<label for="username">Note</label>
						<textarea name="notes" id="notes" class="form-control" required></textarea>
					</div>
					<div class="form-group">
						<button class="btn btn-success btn-sm btn-block col-md-2 float-left" name="add-saving">Add Saving</button>
					</div>

				</form>
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