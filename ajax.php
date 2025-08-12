<?php
ob_start(); // Start output buffering
session_start(); // Ensure session is started at the very beginning

// Security check: Redirect to login if user is not authenticated
// This might need adjustment based on your specific authentication flow
// if (!isset($_SESSION['login_id'])) {
//     header("Location: login.php");
//     exit();
// }

$action = $_GET['action'] ?? ''; // Use null coalescing operator for safer access
include 'admin_class.php'; // Include your admin_class.php
$crud = new Action(); // Instantiate your Action class

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP; // Add SMTP to use SMTP::DEBUG_OFF

// --- IMPORTANT: Adjust this path to your PHPMailer installation ---
// If you used Composer, this is typically correct:
require_once(__DIR__ . '/vendor/autoload.php');


switch ($action) {
    case 'login':
        $login = $crud->login();
        echo $login;
        break;
    case 'login2': // Assuming this is for borrower login
        $login = $crud->login2();
        echo $login;
        break;
    case 'logout': // Admin/User logout
        $logout = $crud->logout();
        // No echo here, as header() redirect will handle it
        break;
    case 'logout2': // Borrower logout
        $logout = $crud->logout2();
        // No echo here, as header() redirect will handle it
        break;
    case 'save_user':
        $save = $crud->save_user();
        echo $save;
        break;
    case 'delete_user':
        $save = $crud->delete_user();
        echo $save;
        break;
    case 'signup':
        $save = $crud->signup();
        echo $save;
        break;
    case "save_settings":
        $save = $crud->save_settings();
        echo $save;
        break;
    case "save_loan_type":
        $save = $crud->save_loan_type();
        echo $save;
        break;
    case "delete_loan_type":
        $save = $crud->delete_loan_type();
        echo $save;
        break;
    case "save_plan":
        $save = $crud->save_plan();
        echo $save;
        break;
    case "delete_plan":
        $save = $crud->delete_plan();
        echo $save;
        break;
    case "save_borrower":
        $save = $crud->save_borrower();
        echo $save;
        break;
    case "delete_borrower":
        $save = $crud->delete_borrower();
        echo $save;
        break;
    case "save_loan":
        $save = $crud->save_loan();
        echo $save;
        break;
    case "delete_loan":
        $save = $crud->delete_loan();
        echo $save;
        break;
    case "save_payment":
        $save = $crud->save_payment();
        echo $save;
        break;
    case "delete_payment":
        $save = $crud->delete_payment();
        echo $save;
        break;

    // --- NEW: Send Bulk Message Action ---
    case 'send_bulk_message':
        // --- START DEBUGGING LINES (REMOVE IN PRODUCTION AFTER FIX) ---
        // error_reporting(E_ALL); // Report all PHP errors
        // ini_set('display_errors', 1); // Display errors directly in the browser
        // --- END DEBUGGING LINES ---

        $conn = $crud->getDbConnection();

        // Validate and sanitize input
        $subject = $conn->real_escape_string($_POST['subject'] ?? '');
        $message_content = $conn->real_escape_string($_POST['message'] ?? '');

        // Start database transaction
        $conn->begin_transaction();
        $success = true; // Assume success initially

        try {
            $save_msg_query = $conn->query("INSERT INTO bulk_messages (subject, message_content) VALUES ('$subject', '$message_content')");
            if (!$save_msg_query) {
                // If saving to DB fails, rollback and throw exception
                throw new Exception("Failed to save message to database: " . $conn->error);
            }

            // Fetch all borrowers' emails
            $borrowers_emails = [];
            $borrowers_query = $conn->query("SELECT email, firstname, lastname FROM borrowers WHERE email IS NOT NULL AND email != ''");
            if ($borrowers_query) {
                while ($row = $borrowers_query->fetch_assoc()) {
                    $borrowers_emails[] = $row;
                }
            } else {
                // If fetching emails fails, rollback and throw exception
                throw new Exception("Failed to fetch borrower emails: " . $conn->error);
            }

            if (empty($borrowers_emails)) {
                // No emails to send, but database saving was successful (if uncommented)
                $conn->commit();
                echo 1; // Indicate success
                exit();
            }

            // Initialize PHPMailer
            $mail = new PHPMailer(true); // Enable exceptions for debugging
            $mail->isSMTP();
            // --- SMTP DETAILS ---
            $mail->SMTPDebug = SMTP::DEBUG_OFF; // ZIMA DEBUGGING KATIKA PRODUCTION
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'robetholyando@gmail.com'; // Your Gmail address (MUST MATCH setFrom)
            $mail->Password   = 'bcqc sjms obow kqbi';       // Your Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use SMTPS for SSL (Port 465)
            $mail->Port       = 465;

            // --- CRITICAL FIX: setFrom() email MUST match the Username for authentication ---
            $mail->setFrom('robetholyando@gmail.com', 'Vikoba Management System'); // CORRECTED SENDER EMAIL
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $subject;

            // --- HAPA NDIYO TUNAJENGA BODY YA EMAIL KWA MUUNDO WA SIMS NIT ---
            $email_body_html = '
            <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 20px auto; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
                <div style="background-color: #556B8D; color: #ffffff; padding: 20px; text-align: center;">
                    <h2 style="margin: 0;">VIKOBA MANAGEMENT SYSTEM</h2>
                  
                </div>

                <div style="padding: 20px; background-color: #f9f9f9;">
                    <h3 style="color: #444; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 0;">' . htmlspecialchars($subject) . '</h3>
                    <p>Dear Borrower,</p>
                    <p>' . nl2br(htmlspecialchars($message_content)) . '</p>
                </div>

                <div style="border-top: 1px solid #ddd; margin: 0 20px;"></div>

                <div style="padding: 20px; font-size: 0.9em; color: #777;">
                    <p>Best regards,<br>
                    VIKOBA Management System Team<br>
                    Dar es Salaam, Tanzania</p>
                    <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">
                    <p style="text-align: center; font-size: 0.8em; color: #999;">
                         Email : <a href="mailto:robetholyando@gmail.com" style="color: #777; text-decoration: none;">robetholyando@gmail.com</a> | Website : <a href="#" style="color: #777; text-decoration: none;">yourdomain.com</a><br>
                        Designed and Developed by Eng. ROBETHO JOHN LYANDO.
                    </p>
                </div>
            </div>';

            $mail->Body    = $email_body_html;
            $mail->AltBody = strip_tags(str_replace('<br />', "\n", nl2br(htmlspecialchars($message_content)))); // Plain text alternative

            foreach ($borrowers_emails as $borrower) {
                try {
                    $mail->addAddress($borrower['email'], $borrower['firstname'] . ' ' . $borrower['lastname']);
                    $mail->send();
                    $mail->clearAddresses(); // Clear all addresses for the next iteration
                } catch (Exception $e) {
                    // Log the error for this specific email, but continue with others
                    error_log("Failed to send message to " . $borrower['email'] . ". Mailer Error: {$mail->ErrorInfo}");
                    // Do NOT throw an exception here, as it would stop the entire bulk send
                }
            }

            $conn->commit(); // Commit transaction if all database operations were successful
            echo 1; // Indicate overall success
        } catch (Exception $e) {
            $conn->rollback(); // Rollback transaction on any caught error
            error_log("Bulk message operation failed: " . $e->getMessage()); // Log the main error
            // --- DISPLAY THE ACTUAL ERROR IN THE BROWSER FOR DEBUGGING (ONLY FOR DEVELOPMENT) ---
            echo "Error: " . $e->getMessage(); // THIS WILL SHOW THE PHPMailer ERROR IN YOUR BROWSER
            // --- END DEBUGGING CHANGE ---
        }
        break;
    case 'get_single_bulk_message':
        // start_load(); // Remove this line if it's there - it's for JS, not PHP
        $conn = $crud->getDbConnection(); // Get connection
        $id = $conn->real_escape_string($_POST['id'] ?? 0); // Get ID from POST

        $response = ['status' => 'error', 'message' => 'Message not found.']; // Default response

        if ($id > 0) {
            $qry = $conn->query("SELECT subject, message_content FROM bulk_messages WHERE id = " . $id);
            if ($qry && $qry->num_rows > 0) {
                $row = $qry->fetch_assoc();
                $response['status'] = 'success';
                $response['subject'] = $row['subject'];
                $response['message_content'] = $row['message_content'];
            } else {
                $response['message'] = 'Message ID not found or query failed: ' . $conn->error;
            }
        } else {
            $response['message'] = 'Invalid message ID.';
        }

        // Output as JSON
        header('Content-Type: application/json');
        echo json_encode($response);
        // end_load(); // Remove this line if it's there
        break;
    case 'delete_bulk_message':
        $conn = $crud->getDbConnection(); // Get connection
        $id = $conn->real_escape_string($_POST['id'] ?? 0); // Get ID from POST

        try {
            $delete = $conn->query("DELETE FROM bulk_messages WHERE id = " . $id);
            if ($delete) {
                echo 1; // Success
            } else {
                throw new Exception("Failed to delete message from database: " . $conn->error);
            }
        } catch (Exception $e) {
            error_log("Error deleting bulk message: " . $e->getMessage());
            echo 0; // Failure
        }
        break;
    // END NEW: Delete Bulk Message Action

    case 'save_user_message':
        $save = $crud->save_user_message();
        echo $save;
        break;
    case 'delete_user_message':
        $delete = $crud->delete_user_message();
        echo $delete;
        break;
    // --- END NEW: User Message Actions ---
    case 'delete_user_message_admin': // Deletes message from admin's view
        $delete = $crud->delete_user_message_admin();
        echo $delete;
        break;
        
    default:
        // Handle undefined actions or log them
        error_log("Undefined AJAX action: " . $action);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}

ob_end_flush(); // End output buffering and send output
?>