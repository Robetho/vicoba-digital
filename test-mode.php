
<?php include('./header.php'); ?>
<?php include('./db_connect.php'); ?>
<?php 
session_start();
if(isset($_SESSION['login_id']))
header("location:index.php?page=home");

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Vikoba Digital Management System</title>
  <style>
        * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f0f0f5;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .container {
      display: flex;
      flex-direction: row;
      background: #fff;
      width: 90%;
      max-width: 1200px;
      height: 600px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.2);
      border-radius: 10px;
      overflow: hidden;
    }

    .image-section {
      flex: 1;
      background: url('vikoba.jpg') center center / cover no-repeat;
      /*width: 30%;*/
    }

    .form-section, .info-section {
      flex: 1;
      padding: 40px 30px;
      overflow-y: auto;
    }

    .form-section {
      background-color: #f9f9f9;
      border-left: 1px solid #ddd;
      border-right: 1px solid #ddd;
    }

    .form-section h2 {
      color: #4B0082;
      margin-bottom: 20px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group input {
      width: 100%;
      padding: 12px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    .form-group input[type="submit"] {
      background-color: #4B0082;
      color: white;
      border: none;
      cursor: pointer;
      transition: 0.3s;
    }

    .form-group input[type="submit"]:hover {
      background-color: #6A0DAD;
    }

    .danger {
      color: red;
      margin-top: 10px;
      font-size: 0.9em;
    }

    .info-section h3 {
      color: #4B0082;
      margin-bottom: 10px;
    }

    .info-section p, .info-section ul {
      margin-bottom: 15px;
      font-size: 15px;
      line-height: 1.5;
      color: #333;
    }

    .info-section ul {
      padding-left: 20px;
    }

    @media (max-width: 900px) {
      .container {
        flex-direction: column;
        height: auto;
      }

      .image-section {
        height: 200px;
      }
    }

    .danger { padding: 10px; margin-bottom: 10px; border-radius: 5px; background: #f8d7da; color: #721c24; }
    .success { padding: 10px; margin-bottom: 10px; border-radius: 5px; color:#fff; background-color:rgba(38,185,154,0.88); }
  </style>
</head>
<body>
  <div class="container">
    <!-- Sehemu ya Picha -->
    <div class="image-section">
      <img src="images/vicoba.jpg" style="width: 100%; height: 80%; margin-top: 60px; margin-left: -6px;" alt="Vikoba group">
    </div>

    <!-- Sehemu ya Login -->
    <div class="form-section">
      <h2>Login to Vikoba System</h2>
      <form id="login-form">
        <?php
            if (isset($_GET['message']) && $_GET['message'] === 'logged_out') {
                echo "<div class='danger'>Logged out successfully.</div>";
            }
            if (isset($_GET['status']) && $_GET['status'] === 'missed_id') {
                echo "<div class='danger'>Please Login First</div>";
            }
        ?>
        <div class="form-group">
                <label for="username" class="control-label">Username</label>
                <input type="text" id="username" name="username" class="form-control">
              </div>
              <div class="form-group">
                <label for="password" class="control-label">Password</label>
                <input type="password" id="password" name="password" class="form-control">
              </div>
        <button class="btn-sm btn-block btn-wave col-md-4 btn-primary">Login</button>
      </form>
    </div>

    <!-- Sehemu ya Maelezo ya Mfumo -->
    <div class="info-section">
      <h3>📌 Mfumo wa Vikoba</h3>
      <p>Mfumo huu unasaidia kusimamia shughuli za VIKOBA kwa njia ya kidigitali.</p>
      <h3>👨‍💼 Chairman</h3>
      <p>Chairman anasimamia kikundi, anaweza kuona wanachama wote, kuidhinisha mikopo, na kuona taarifa za kifedha.</p>
      <h3>🧍‍♂️ Borrower</h3>
      <p>Borrower anaweza kuomba mkopo, kulipia deni, na kuona historia ya miamala yake.</p>
      <h3>✅ Faida za Mfumo</h3>
      <ul>
        <li>Usalama wa taarifa</li>
        <li>Urahisi wa kupata mikopo</li>
        <li>Taarifa sahihi kwa wakati</li>
      </ul>
    </div>
    <!-- <div class="footer">
      &copy; 2025 Vikoba Digital System | Empowering Savings Groups
    </div> -->
  </div>


  

<script>
	$('#login-form').submit(function(e){
		e.preventDefault()
		$('#login-form input[type="submit"]').attr('disabled',true).html('Logging in...');
		if($(this).find('.danger').length > 0 )
			$(this).find('.danger').remove();
		$.ajax({
			url:'ajax.php?action=login',
			method:'POST',
			data:$(this).serialize(),
			error:err=>{
				console.log(err)
		$('#login-form input[type="submit"]').removeAttr('disabled').html('Login');

			},
			success:function(resp){
				if(resp == 1){
					location.href ='index.php?page=home';
				}else if(resp == 2){
					location.href ='u-index.php?page=u-home';
				}else{
					$('#login-form').prepend('<div class="danger">Username or password is incorrect.</div>')
					$('#login-form input[type="submit"]').removeAttr('disabled').html('Login');
				}
			}
		})
	})
</script>	
</body>
</html>

