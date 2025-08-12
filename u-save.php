<?php
include 'db_connect.php';
session_start();
if (isset($_POST['add-saving'])) {
	$id = $_SESSION['login_id'];
	$borrower_id = $id;
	$amount = $_POST['amount'];
	$notes = $_POST['notes'];

	$conn->query("INSERT INTO savings (borrower_id, amount, notes) VALUES ('$borrower_id', '$amount', '$notes')");

	header("Location: u-index.php?page=u-saving");
}


?>