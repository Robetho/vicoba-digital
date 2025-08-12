<?php
//session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL); // Report all errors for debugging

Class Action {
	private $db;

	public function __construct() {
		ob_start();
        include 'db_connect.php'; // Ensure db_connect.php is securely handling credentials
        $this->db = $conn;
	}

	function __destruct() {
	    if ($this->db) { // Check if connection exists before closing
	   $this->db->close();
	    }
		ob_end_flush();
	}

    /**
     * @return mysqli The database connection object.
     */
    public function getDbConnection() {
        return $this->db;
    }

	function login(){
    extract($_POST);
    // Admin/User login
    $qry = $this->db->query("SELECT * FROM users WHERE username = '{$username}' AND password = '{$password}' ");
    if($qry->num_rows > 0){
        foreach ($qry->fetch_array() as $key => $value) {
            if($key != 'password' && !is_numeric($key))
                $_SESSION['login_'.$key] = $value;
        }
        return 1; // Success for admin/user
    }

    // Borrower login
    $qry2 = $this->db->query("SELECT * FROM borrowers WHERE username = '{$username}' AND password = '".md5($password)."' ");
    if($qry2->num_rows > 0){
        foreach ($qry2->fetch_array() as $key => $value) {
            if($key != 'password' && !is_numeric($key))
                $_SESSION['login_'.$key] = $value;
        }
        return 2; // Success for borrower
    }
    return 3; // No matching user found
}

	function login2(){
		return 0; // Indicate failure if not implemented
	}

	function logout(){
		// Destroys session for admin/users
		session_destroy();
		// Ensure session variables are cleared
		$_SESSION = array();
		if (ini_get("session.use_cookies")) {
		    $params = session_get_cookie_params();
		    setcookie(session_name(), '', time() - 42000,
		        $params["path"], $params["domain"],
		        $params["secure"], $params["httponly"]
		    );
		}
		header("Location: login.php?message=logged_out");
		exit();
	}

	function logout2(){
		// Destroys session for borrowers
		session_destroy();
		// Ensure session variables are cleared
		$_SESSION = array();
		if (ini_get("session.use_cookies")) {
		    $params = session_get_cookie_params();
		    setcookie(session_name(), '', time() - 42000,
		        $params["path"], $params["domain"],
		        $params["secure"], $params["httponly"]
		    );
		}
		header("Location: u-login.php?message=logged_out");
		exit();
	}

	function save_user(){
		extract($_POST);
		$data = " name = '{$name}' ";
		$data .= ", username = '{$username}' ";
		// Consider hashing password using password_hash() for better security
		// $hashed_password = password_hash($password, PASSWORD_DEFAULT);
		// $data .= ", password = '{$hashed_password}' ";
		$data .= ", password = '{$password}' "; // Current implementation
		$data .= ", type = '{$type}' ";

		if(empty($id)){
			$save = $this->db->query("INSERT INTO users set ".$data);
		}else{
			$save = $this->db->query("UPDATE users set ".$data." where id = ".$id);
		}
		if($save){
			return 1;
		}
		return 0; // Return 0 on failure
	}

	function delete_user(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM users WHERE id = ".$id);
		if($delete)
			return 1;
		return 0;
	}

	function signup(){
		extract($_POST);
		$data = " name = '{$name}' ";
		$data .= ", contact = '{$contact}' ";
		$data .= ", address = '{$address}' ";
		$data .= ", username = '{$email}' ";
		$data .= ", password = '".md5($password)."' "; // Using md5 as per your original code
		$data .= ", type = 3"; // Assuming type 3 is for new signups
		
		$chk = $this->db->query("SELECT * FROM users WHERE username = '{$email}' ")->num_rows;
		if($chk > 0){
			return 2; // Username already exists
		}
		
		$save = $this->db->query("INSERT INTO users set ".$data);
		if($save){
			// Log in the new user immediately after successful signup
			$qry = $this->db->query("SELECT * FROM users WHERE username = '{$email}' AND password = '".md5($password)."' ");
			if($qry->num_rows > 0){
				foreach ($qry->fetch_array() as $key => $value) {
					if($key != 'password' && !is_numeric($key))
						$_SESSION['login_'.$key] = $value;
				}
			}
			return 1; // Signup successful
		}
		return 0; // Signup failed
	}

	function save_settings(){
		extract($_POST);
		$data = " name = '".$this->db->real_escape_string(str_replace("'","&#x2019;",$name))."' ";
		$data .= ", email = '{$this->db->real_escape_string($email)}' ";
		$data .= ", contact = '{$this->db->real_escape_string($contact)}' ";
		$data .= ", about_content = '".$this->db->real_escape_string(htmlentities(str_replace("'","&#x2019;",$about)))."' ";
		
		if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/img/'. $fname); // Ensure 'assets/img/' is correct path from root
			if($move){
				$data .= ", cover_img = '{$fname}' ";
			}
		}
		
		$chk = $this->db->query("SELECT * FROM system_settings");
		if($chk->num_rows > 0){
			$save = $this->db->query("UPDATE system_settings set ".$data);
		}else{
			$save = $this->db->query("INSERT INTO system_settings set ".$data);
		}
		
		if($save){
			// Update session settings
			$query = $this->db->query("SELECT * FROM system_settings LIMIT 1")->fetch_array();
			foreach ($query as $key => $value) {
				if(!is_numeric($key))
					$_SESSION['setting_'.$key] = $value;
			}
			return 1;
		}
		return 0;
	}

	function save_loan_type(){
		extract($_POST);
		$data = " type_name = '{$this->db->real_escape_string($type_name)}' ";
		$data .= " , description = '{$this->db->real_escape_string($description)}' ";
		if(empty($id)){
			$save = $this->db->query("INSERT INTO loan_types set ".$data);
		}else{
			$save = $this->db->query("UPDATE loan_types set ".$data." where id=".$id);
		}
		if($save)
			return 1;
		return 0;
	}

	function delete_loan_type(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM loan_types where id = ".$id);
		if($delete)
			return 1;
		return 0;
	}

	function save_plan(){
		extract($_POST);
		$data = " months = '{$months}' ";
		$data .= ", interest_percentage = '{$interest_percentage}' ";
		$data .= ", penalty_rate = '{$penalty_rate}' ";
		
		if(empty($id)){
			$save = $this->db->query("INSERT INTO loan_plan set ".$data);
		}else{
			$save = $this->db->query("UPDATE loan_plan set ".$data." where id=".$id);
		}
		if($save)
			return 1;
		return 0;
	}

	function delete_plan(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM loan_plan where id = ".$id);
		if($delete)
			return 1;
		return 0;
	}

	function save_borrower(){
		extract($_POST);
		$hashed_password = md5($password); // Still using MD5 as per your request, but consider password_hash
		$data = " lastname = '{$this->db->real_escape_string($lastname)}' ";
		$data .= ", firstname = '{$this->db->real_escape_string($firstname)}' ";
		$data .= ", middlename = '{$this->db->real_escape_string($middlename)}' ";
		$data .= ", username = '{$this->db->real_escape_string($username)}' ";
		$data .= ", password = '{$hashed_password}' ";
		$data .= ", address = '{$this->db->real_escape_string($address)}' ";
		$data .= ", contact_no = '{$this->db->real_escape_string($contact_no)}' ";
		$data .= ", email = '{$this->db->real_escape_string($email)}' ";
		$data .= ", tax_id = '{$this->db->real_escape_string($tax_id)}' ";
		
		if(empty($id)){
			$save = $this->db->query("INSERT INTO borrowers set ".$data);
		}else{
			$save = $this->db->query("UPDATE borrowers set ".$data." where id=".$id);
		}
		if($save)
			return 1;
		return 0;
	}

	function delete_borrower(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM borrowers where id = ".$id);
		if($delete)
			return 1;
		return 0;
	}

	function save_loan(){
		extract($_POST);
		$data = " borrower_id = {$borrower_id} ";
		$data .= " , loan_type_id = '{$loan_type_id}' ";
		$data .= " , plan_id = '{$plan_id}' ";
		$data .= " , amount = '{$amount}' ";
		$data .= " , purpose = '{$this->db->real_escape_string($purpose)}' ";
		
		if(isset($status)){
			$data .= " , status = '{$status}' ";
			if($status == 2){ // If loan is released/active
				$plan = $this->db->query("SELECT * FROM loan_plan where id = {$plan_id} ")->fetch_array();
				$sid = []; // To store schedule IDs
				for($i= 1; $i <= $plan['months']; $i++){
					$date = date("Y-m-d",strtotime(date("Y-m-d")." +{$i} months"));
					$chk = $this->db->query("SELECT * FROM loan_schedules WHERE loan_id = {$id} AND DATE(date_due) ='{$date}' ");
					if($chk->num_rows > 0){
						$ls_id = $chk->fetch_array()['id'];
						$this->db->query("UPDATE loan_schedules SET loan_id = {$id}, date_due ='{$date}' WHERE id = {$ls_id} ");
					}else{
						$this->db->query("INSERT INTO loan_schedules SET loan_id = {$id}, date_due ='{$date}' ");
						$ls_id = $this->db->insert_id;
					}
					$sid[] = $ls_id;
				}
				$sid_str = implode(",",$sid);
				if (!empty($sid_str)) { // Ensure $sid_str is not empty before deleting
					$this->db->query("DELETE FROM loan_schedules WHERE loan_id = {$id} AND id NOT IN ({$sid_str}) ");
				}
				$data .= " , date_released = '".date("Y-m-d H:i")."' ";
			} else { // If status is not 2 (e.g., denied, completed before release)
				$chk = $this->db->query("SELECT * FROM loan_schedules WHERE loan_id = {$id}")->num_rows;
				if($chk > 0){
					$this->db->query("DELETE FROM loan_schedules WHERE loan_id = {$id} ");
				}
			}
		}

		if(empty($id)){
			$ref_no = mt_rand(1,99999999);
			$i= 1;
			while($i== 1){
				$check = $this->db->query("SELECT * FROM loan_list WHERE ref_no = '{$ref_no}' ")->num_rows;
				if($check > 0){
					$ref_no = mt_rand(1,99999999);
				}else{
					$i = 0;
				}
			}
			$data .= " , ref_no = '{$ref_no}' ";
			$save = $this->db->query("INSERT INTO loan_list set ".$data);
		} else {
			$save = $this->db->query("UPDATE loan_list set ".$data." where id=".$id);
		}
		
		if($save)
			return 1;
		return 0;
	}

	function delete_loan(){
		extract($_POST);
		// Consider deleting related schedules and payments first for data integrity
		$this->db->query("DELETE FROM loan_schedules WHERE loan_id = ".$id);
		$this->db->query("DELETE FROM payments WHERE loan_id = ".$id);
		$delete = $this->db->query("DELETE FROM loan_list where id = ".$id);
		if($delete)
			return 1;
		return 0;
	}

	function save_payment(){
		extract($_POST);
		$data = " loan_id = {$loan_id} ";
		$data .= " , payee = '{$this->db->real_escape_string($payee)}' ";
		$data .= " , amount = '{$amount}' ";
		$data .= " , penalty_amount = '{$penalty_amount}' ";
		$data .= " , overdue = '{$overdue}' ";
		
		if(empty($id)){
			$save = $this->db->query("INSERT INTO payments set ".$data);
		}else{
			$save = $this->db->query("UPDATE payments set ".$data." where id = ".$id);
		}
		if($save)
			return 1;
		return 0;
	}

	function delete_payment(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM payments where id = ".$id);
		if($delete)
			return 1;
		return 0;
	}

	// --- NEW: User Message Functions ---
    function save_user_message(){
        extract($_POST);
        // Ensure borrower is logged in
        if (!isset($_SESSION['login_id'])) {
            return 0; // Not logged in
        }
        $borrower_id = $_SESSION['login_id'];

        if (empty($subject) || empty($message_content)) {
            return 2; // Subject or message cannot be empty
        }

        $subject = $this->db->real_escape_string($subject);
        $message_content = $this->db->real_escape_string($message_content);

        $data = "borrower_id = '{$borrower_id}' ";
        $data .= ", subject = '{$subject}' ";
        $data .= ", message_content = '{$message_content}' ";
        $data .= ", status = 0"; // Default to unread

        if(empty($id)){ // For new message
            $save = $this->db->query("INSERT INTO user_messages SET ".$data);
        } else { 
            return 0; // Or return an error if editing is not allowed for user-sent messages.
        }

        if($save){
            return 1; // Success
        }
        return 0; // Failure
    }

    function delete_user_message(){
        extract($_POST);
        // Ensure borrower is logged in and owns the message
        if (!isset($_SESSION['login_id'])) {
            return 0; // Not logged in
        }
        $borrower_id = $_SESSION['login_id'];

        // Securely delete only if the message belongs to the logged-in borrower
        $delete = $this->db->query("DELETE FROM user_messages WHERE id = {$id} AND borrower_id = {$borrower_id}");
        if($delete)
            return 1; // Success
        return 0; // Failure
    }

    function delete_user_message_admin(){ // Deletes message from admin's view
        extract($_POST);
        // Ensure admin is logged in
        if (!isset($_SESSION['login_id']) || ($_SESSION['login_type'] ?? '') != 1) {
            return 0; // Not admin
        }

        // Admin can delete any message
        $delete = $this->db->query("DELETE FROM user_messages WHERE id = {$id}");
        if($delete)
            return 1; // Success
        return 0; // Failure
    }

    // You might also want to add functions for 'get_loan_data' and 'pay' if they are used elsewhere
    // function get_loan_data(){...}
    // function pay(){...}

}