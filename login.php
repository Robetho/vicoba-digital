<?php include('./header.php'); ?>
<?php include('./db_connect.php'); ?>
<?php
error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1);
ob_start(); // Start output buffering
session_start(); 
if(isset($_SESSION['login_id'])) {
    // Make sure 'login_id' is consistently used for both user types or adapt based on `type`.
    // If different types redirect to different pages based on `login_type` or similar.
    if(isset($_SESSION['login_type']) && $_SESSION['login_type'] == 2) { // Assuming type 2 is borrower
        header("location:u-index.php?page=u-home");
    } else { // Assuming other types are admin/chairman
        header("location:index.php?page=home");
    }
    exit(); // Always exit after a header redirect
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vikoba Digital Management System - Login</title>
    <style>
        /* General Resets and Body Styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e9ebee; /* Lighter background for a fresh feel */
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh; /* Use min-height to ensure it takes full height even with less content */
            padding: 20px; /* Add some padding for smaller screens */
        }

        /* Container for the entire layout */
        .container {
            display: flex;
            flex-direction: row;
            background: #ffffff;
            width: 100%;
            max-width: 1100px; /* Slightly reduced max-width for a cozier feel */
            min-height: 550px; /* Adjusted min-height */
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); /* More subtle, spread-out shadow */
            border-radius: 12px; /* Slightly more rounded corners */
            overflow: hidden;
        }

        /* Image Section Styling */
        .image-section {
            flex: 1;
            background: linear-gradient(rgba(75, 0, 130, 0.7), rgba(75, 0, 130, 0.7)), url('images/vicoba.jpg') center center / cover no-repeat; /* Dark overlay for text readability */
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            text-align: center;
            position: relative;
            padding: 30px;
        }

        .image-section h1 {
            font-size: 2.5em;
            margin-bottom: 15px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
        }

        .image-section p {
            font-size: 1.1em;
            line-height: 1.6;
            max-width: 400px;
        }

        /* Form and Info Sections Common Styling */
        .form-section, .info-section {
            flex: 1;
            padding: 40px; /* Increased padding for more breathing room */
            overflow-y: auto; /* Enable scrolling if content overflows */
        }

        /* Form Section Specific Styling */
        .form-section {
            background-color: #fcfcfc;
            display: flex;
            flex-direction: column;
            justify-content: center; /* Center content vertically within the form section */
        }

        .form-section h2 {
            color: #4B0082; /* Your original purple */
            margin-bottom: 30px; /* More space below heading */
            font-size: 2em;
            text-align: center;
        }

        .form-group {
            margin-bottom: 25px; /* More space between form groups */
        }

        .form-group label {
            display: block; /* Make labels take full width */
            margin-bottom: 8px; /* Space between label and input */
            font-weight: 600; /* Bolder labels */
            color: #555;
            font-size: 0.95em;
        }

        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 14px; /* Larger padding for easier interaction */
            font-size: 16px;
            border: 1px solid #dcdcdc; /* Lighter border */
            border-radius: 8px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus {
            border-color: #5F27CD; /* Accent color on focus */
            box-shadow: 0 0 0 3px rgba(95, 39, 205, 0.2); /* Soft glow on focus */
            outline: none; /* Remove default outline */
        }

        /* Login Button Styling */
        .login-button {
            width: 100%;
            padding: 15px;
            font-size: 1.1em;
            background-color: #5F27CD; /* New, richer purple */
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-top: 20px; /* Space above button */
        }

        .login-button:hover {
            background-color: #7a46e1; /* Lighter on hover */
            transform: translateY(-2px); /* Slight lift effect */
        }

        .login-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        /* Message Styling (Danger/Success) */
        .danger {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 12px;
            margin-bottom: 20px; /* More space for messages */
            border-radius: 8px;
            font-size: 0.9em;
            text-align: center;
        }

        .success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 0.9em;
            text-align: center;
        }

        /* Info Section Styling */
        .info-section {
            background-color: #f0f0f5; /* Slightly different background for distinction */
            border-left: 1px solid #e0e0e0;
        }

        .info-section h3 {
            color: #4B0082;
            margin-bottom: 15px;
            font-size: 1.5em;
            display: flex;
            align-items: center;
            gap: 10px; /* Space between icon and text */
        }

        .info-section p, .info-section ul {
            margin-bottom: 20px; /* More space between paragraphs/lists */
            font-size: 1em;
            line-height: 1.7; /* Improved readability */
            color: #444;
        }

        .info-section ul {
            list-style: none; /* Remove default bullets */
            padding-left: 0;
        }

        .info-section ul li {
            position: relative;
            padding-left: 25px; /* Space for custom bullet */
            margin-bottom: 10px;
        }

        .info-section ul li::before {
            content: '•'; /* Custom bullet point */
            color: #5F27CD; /* Accent color for bullets */
            font-size: 1.2em;
            position: absolute;
            left: 0;
            top: 0;
        }

        .info-section strong {
            color: #4B0082; /* Emphasize key roles */
        }

        /* Responsive Adjustments */
        @media (max-width: 900px) {
            .container {
                flex-direction: column;
                height: auto;
                min-height: auto; /* Reset min-height for mobile */
                width: 95%; /* Adjust width for smaller screens */
            }

            .image-section {
                height: 250px; /* Taller image section on smaller screens */
                order: -1; /* Move image section to the top on smaller screens */
                padding: 20px;
            }

            .image-section h1 {
                font-size: 2em;
            }

            .image-section p {
                font-size: 0.95em;
            }

            .form-section, .info-section {
                padding: 30px; /* Reduce padding on smaller screens */
            }

            .form-section h2 {
                margin-bottom: 20px;
                font-size: 1.8em;
            }

            .info-section h3 {
                font-size: 1.3em;
            }

            .info-section p, .info-section ul {
                font-size: 0.95em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="image-section">
            <div>
                <h1>Welcome to Vikoba Digital!</h1>
                <p>Your secure and efficient platform for managing community savings and loan groups. Empowering financial growth, together.</p>
            </div>
        </div>

        <div class="form-section">
            <h2>Login to Vikoba System</h2>
            <form id="login-form">
                <?php
                    if (isset($_GET['message']) && $_GET['message'] === 'logged_out') {
                        echo "<div class='success'>You have been successfully logged out.</div>"; // Changed to success for positive feedback
                    }
                    if (isset($_GET['status']) && $_GET['status'] === 'missed_id') {
                        echo "<div class='danger'>Please login first to access the system.</div>"; // More user-friendly message
                    }
                ?>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="login-button">Login</button>
            </form>
        </div>

        <div class="info-section">
            <h3>📌 About the Vikoba System</h3>
            <p>The **Vikoba Digital Management System** is a secure and easy-to-use platform designed to support community savings and loan groups (Vikoba) in managing their operations digitally. It replaces manual record-keeping with an efficient and transparent system that ensures accountability, accuracy, and real-time access to financial data.</p>
            <h3>🧑‍🤝‍🧑 System Users</h3>
            <ul>
                <li><strong>Chairman / Admin:</strong> Responsible for overseeing the entire group, including managing members, reviewing loan requests, approving repayments, and generating comprehensive reports.</li>
                <li><strong>Borrower / Member:</strong> Can easily request loans, make timely repayments, view their current loan balances, and monitor their contribution history with full transparency.</li>
            </ul>
        </div>
    </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> 
<script>
    $('#login-form').submit(function(e){
        e.preventDefault();
        var $button = $(this).find('.login-button'); // Reference the button
        $button.attr('disabled', true).text('Logging in...'); // Update button text

        // Remove existing messages
        $(this).find('.danger, .success').remove();

        $.ajax({
            url: 'ajax.php?action=login',
            method: 'POST',
            data: $(this).serialize(),
            error: function(err){
                console.log(err);
                $button.removeAttr('disabled').text('Login'); // Reset button on error
                $('#login-form').prepend('<div class="danger">An error occurred. Please try again.</div>'); // Generic error
            },
            success: function(resp){
                // Ensure resp is trimmed for accurate comparison
                resp = $.trim(resp); // <--- Add this line
                if(resp == 1){
                    location.href = 'index.php?page=home'; // Admin/User
                } else if(resp == 2){
                    location.href = 'u-index.php?page=u-home'; // Borrower
                } else if (resp == 3) { // This is for "No matching user found"
                    $('#login-form').prepend('<div class="danger">Incorrect username or password.</div>');
                    $button.removeAttr('disabled').text('Login'); // Reset button on incorrect credentials
                } else {
                    // Catch any unexpected responses from the server
                    $('#login-form').prepend('<div class="danger">An unexpected error occurred. Please try again later.</div>');
                    $button.removeAttr('disabled').text('Login');
                    console.log("Unexpected response:", resp); // Log unexpected responses for debugging
                }
            }
        });
    });
</script>
</body>
</html>