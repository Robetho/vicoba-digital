<?php
include 'db_connect.php';
session_start();

if (isset($_GET['id'])) {
    $qry = $conn->query("SELECT * FROM payments WHERE id=" . $_GET['id']);
    foreach ($qry->fetch_array() as $k => $val) {
        $$k = $val;
    }
}
$id = $_SESSION['login_id'];
?>

<div class="container-fluid">
    <div class="col-lg-12">
        <form id="u-manage-payment">
            <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="" class="control-label">Loan Reference No. </label>
                        <select name="loan_id" id="" class="custom-select browser-default select2">
                            <option value=""></option>
                            <?php
                            $id = $_SESSION['login_id'];

                            $loan_query = $conn->query("SELECT * FROM loan_list WHERE status = 2 AND borrower_id = '$id' ORDER BY ref_no ASC");

                            if ($loan_query && $loan_query->num_rows > 0) {
                                while ($row = $loan_query->fetch_assoc()) {
                            ?>
                                    <option value="<?php echo htmlspecialchars($row['id']); ?>"
                                        <?php echo isset($loan_id) && $loan_id == $row['id'] ? "selected" : ''; ?>>
                                        <?php echo htmlspecialchars($row['ref_no']); ?>
                                    </option>
                            <?php
                                }
                            } else {
                                echo '<option value="" disabled selected>No released loans available for payment</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row" id="fields">

            </div>
        </form>
    </div>
</div>

<script>
    $('[name="loan_id"]').change(function(){
        load_fields();
    });

    $('.select2').select2({
        placeholder: "Please select here",
        width: "100%"
    });

    function load_fields(){
        start_load();
        $.ajax({
            url: 'load_fields.php',
            method: "POST",
            data: {
                id: '<?php echo isset($id) ? $id : ""; ?>',
                loan_id: $('[name="loan_id"]').val()
            },
            success: function(resp){
                if(resp){
                    $('#fields').html(resp);
                    end_load();
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error (load_fields.php): " + status + " - " + error + "\nResponse: " + xhr.responseText);
                alert_toast("An error occurred while loading loan fields.", "error");
                end_load();
            }
        });
    }

    $('#u-manage-payment').submit(function(e){
        e.preventDefault();
        start_load();
        $.ajax({
            url: 'ajax.php?action=save_payment',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp){
                if(resp == 1){
                    alert_toast("Payment data successfully saved.", "success");
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                } else {
                    alert_toast("Failed to save payment. Please try again.", "error");
                    end_load();
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error (save_payment): " + status + " - " + error + "\nResponse: " + xhr.responseText);
                alert_toast("An error occurred. Please check the console for details.", "error");
                end_load();
            }
        });
    });

    $(document).ready(function(){
        if('<?php echo isset($_GET['id']) ?>' == 1 && $('[name="loan_id"]').val() != '') {
            load_fields();
        }
    });
</script>