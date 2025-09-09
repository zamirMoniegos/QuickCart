<?php
session_start();
date_default_timezone_set('Asia/Manila');

// Include necessary files
require_once 'vendor/autoload.php';
require_once 'db_connect.php';
require_once 'mailer.php'; // Assumed to contain PHPMailer setup if not done here

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Your Google Client ID from the Google Cloud Console
$CLIENT_ID = '354395193613-0mpsdrfhkeh5ta56gvgiapulcgh9nek1.apps.googleusercontent.com';

// Check if the 'credential' POST variable is set (sent by Google Sign-In)
if (!isset($_POST['credential'])) {
    // A more user-friendly error handling than die()
    $_SESSION['login_error'] = 'Google credential not received. Please try again.';
    header('Location: login.php');
    exit;
}

$id_token = $_POST['credential'];

try {
    $client = new Google_Client(['client_id' => $CLIENT_ID]);
    $payload = $client->verifyIdToken($id_token);

    if ($payload) {
        // Token is valid, get user info
        $user_email = $payload['email'];
        $user_name = $payload['name'];
        $user_picture = $payload['picture'];

        // --- Check if user exists in the database ---
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // User doesn't exist, create a new one
            $insert_stmt = $conn->prepare("INSERT INTO users (username, email, name, picture) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssss", $user_email, $user_email, $user_name, $user_picture);
            $insert_stmt->execute();
            $user_id = $insert_stmt->insert_id;
            $insert_stmt->close();
        } else {
            // User exists, get their ID
            $user = $result->fetch_assoc();
            $user_id = $user['id'];
        }
        $stmt->close();

        // =======================================================
        // ▼▼▼         START OTP PROCESS FOR LOGIN           ▼▼▼
        // =======================================================
        
        // 1. Generate a secure 6-digit OTP
        $otp = random_int(100000, 999999);

        // 2. Prepare PHPMailer to send the OTP
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'zmoniegos@gmail.com'; // Your Gmail address
            $mail->Password   = 'uwtk cziy sxpy gmba';   // Your Google App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('QuickCart@gmail.com', 'QuickCart Security');
            $mail->addAddress($user_email, $user_name);
            
            // Email Content
            $mail->isHTML(true);
            $mail->Subject = 'Your QuickCart Verification Code';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                    <h2>Hi <b>" . htmlspecialchars($user_name) . "</b>,</h2>
                    <p>Your One-Time Password (OTP) to complete your login is:</p>
                    <p style='font-size: 24px; font-weight: bold; letter-spacing: 2px;'>" . $otp . "</p>
                    <p>This code will expire in 5 minutes.</p>
                    <p>If you did not request this, please ignore this email.</p>
                    <br>
                    <p>Thanks,<br>The QuickCart Team</p>
                </div>";

            // 3. Send the email
            $mail->send();

            // 4. If email sends successfully, set up the session for verification
            session_regenerate_id(true); // Regenerate session ID for security

            // Store OTP info and temporary user data
            $_SESSION['otp_code']       = $otp;
            $_SESSION['otp_expiry']     = time() + 300; // 5 minutes validity
            $_SESSION['email_temp']     = $user_email;
            $_SESSION['user_data_temp'] = [
                'id'      => $user_id,
                'name'    => $user_name,
                'picture' => $user_picture
            ];
            
            // Set the initial 60-second resend timer and reset attempt counters
            $_SESSION['timer_end'] = time() + 60;
            unset($_SESSION['otp_attempts']);
            unset($_SESSION['otp_cooldown_end']);

            // 5. Redirect to the OTP verification page
            header("Location: verify-otp.php");
            exit;

        } catch (Exception $e) {
            // Handle mailer failure
            $_SESSION['login_error'] = "Could not send verification email. Please try again later. Mailer Error: {$mail->ErrorInfo}";
            header('Location: login.php');
            exit;
        }

    } else {
        // Invalid Google ID token
        $_SESSION['login_error'] = 'Invalid Google session. Please sign in again.';
        header('Location: login.php');
        exit;
    }
} catch (Exception $e) {
    // Catch errors from Google_Client or other exceptions
    $_SESSION['login_error'] = 'An error occurred during Google authentication: ' . $e->getMessage();
    header('Location: login.php');
    exit;
}
?>