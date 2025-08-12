<?php include('db_connect.php');?>

<div class="container-fluid">
	
	<div class="col-lg-12">
		<div class="row">
			<!-- FORM Panel -->

			<!-- Table Panel -->
			<div class="col-md-12">
				<div class="card">
					<div class="card-body">
						<table class="table table-bordered table-hover">
							<thead>
								<tr>
									<th class="text-center">#</th>
									<th class="text-center">Plan</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$i = 1;
								$plan = $conn->query("SELECT * FROM loan_plan order by id asc");
								while($row=$plan->fetch_assoc()):
									$months = $row['months'];
									$months = $months / 12;
									if($months < 1){
										$months = $row['months']. " months";
									}else{
										$m = explode(".", $months);
										$months = $m[0] . " yrs.";
										if(isset($m[1])){
											$months .= " and ".number_format(12 * ($m[1] /100 ),0)."month/s";
										}
									}
								?>
								<tr>
									<td class="text-center"><?php echo $i++ ?></td>
									<td class="">
										 <p>Years/Month: <b><?php echo $months ?></b></p>
										 <p><small>Interest: <b><?php echo $row['interest_percentage']."%" ?></b></small></p>
										 <p><small>Over dure Penalty: <b><?php echo $row['penalty_rate']."%" ?></b></small></p>
									</td>
								</tr>
								<?php endwhile; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<!-- Table Panel -->
		</div>
	</div>	

</div>
<style>
	
	td{
		vertical-align: middle !important;
	}
	td p{
		margin: unset
	}
	img{
		max-width:100px;
		max-height: :150px;
	}
</style>
<script>
	function _reset(){
		$('#cimg').attr('src','');
		$('[name="id"]').val('');
		$('#manage-plan').get(0).reset();
	}
	
	$('#manage-plan').submit(function(e){
		e.preventDefault()
		start_load()
		$.ajax({
			url:'ajax.php?action=save_plan',
			data: new FormData($(this)[0]),
		    cache: false,
		    contentType: false,
		    processData: false,
		    method: 'POST',
		    type: 'POST',
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully added",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
				else if(resp==2){
					alert_toast("Data successfully updated",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
			}
		})
	})
	$('.edit_plan').click(function(){
		start_load()
		var plan = $('#manage-plan')
		plan.get(0).reset()
		plan.find("[name='id']").val($(this).attr('data-id'))
		plan.find("[name='months']").val($(this).attr('data-months'))
		plan.find("[name='interest_percentage']").val($(this).attr('data-interest_percentage'))
		plan.find("[name='penalty_reate']").val($(this).attr('data-penalty_reate'))
		end_load()
	})
	$('.delete_plan').click(function(){
		_conf("Are you sure to delete this Plan?","delete_plan",[$(this).attr('data-id')])
	})
	function displayImg(input,_this) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
        	$('#cimg').attr('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
    }
}
	function delete_plan($id){
		start_load()
		$.ajax({
			url:'ajax.php?action=delete_plan',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully deleted",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
			}
		})
	}
</script>