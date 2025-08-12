<div class="container-fluid">
	<form action="" id="bulk-message-form">
		<div class="form-group">
			<label for="subject" class="control-label">Subject</label>
			<input type="text" class="form-control" name="subject" required>
		</div>
		<div class="form-group">
			<label for="message" class="control-label">Message</label>
			<textarea name="message" id="message" cols="30" rows="10" class="form-control" required></textarea>
		</div>
		<div class="form-group">
			<small class="text-muted">Note: This message will be sent to all borrowers with an email address.</small>
		</div>
	</form>
</div>
<script>
	$('#bulk-message-form').submit(function(e){
		e.preventDefault()
		start_load() // Assuming this function shows a loading indicator
		$.ajax({
			url:'ajax.php?action=send_bulk_message',
			method:'POST',
			data:$(this).serialize(),
			success:function(resp){
				if(resp == 1){
					alert_toast("Bulk message sent successfully!",'success') // Assuming this function shows a success toast
					setTimeout(function(){
						location.reload() // Reload the page after a short delay
					},1500)
				} else {
                    alert_toast("Failed to send bulk message. Please check server logs.",'error') // Assuming this function shows an error toast
                    end_load() // Assuming this function hides the loading indicator
                }
			},
            error: function(xhr, status, error) {
                console.error("AJAX Error: ", status, error);
                alert_toast("An error occurred during message sending.",'error');
                end_load();
            }
		})
	})
</script>