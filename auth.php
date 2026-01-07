<?php
session_start();
date_default_timezone_set('Europe/Bucharest'); // Set the time zone


header('Content-Type: application/json');

// 1. Import PHPMailer files
require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 2. Database Connection
$host = 'localhost';
$db   = ' '; 
$user = ' '; 
$pass = ' ';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET time_zone = '+02:00'"); 
} catch (PDOException $e) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

$action   = $_POST['action'] ?? '';
$email    = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$otp_in   = $_POST['otp'] ?? '';

// --- ACTIONS: Login / Register / Reset Request ---
if ($action == 'register' || $action == 'login' || $action == 'reset_request') {
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user_data = $stmt->fetch();

    if ($action == 'login' && (!$user_data || !password_verify($password, $user_data['password']))) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
        exit;
    }

    if ($action == 'reset_request' && !$user_data) {
        echo json_encode(['status' => 'error', 'message' => 'Email not found']);
        exit;
    }

    $otp = rand(100000, 999999);
    $expiry = date("Y-m-d H:i:s", strtotime('+10 minutes'));

    if ($action == 'register') {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password, otp_code, otp_expiry) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE otp_code=?, otp_expiry=?");
        $stmt->execute([$email, $hashed, $otp, $expiry, $otp, $expiry]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE email = ?");
        $stmt->execute([$otp, $expiry, $email]);
    }

    // --- SEND EMAIL ---
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = ' ';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'support@nanos.ro'; // if you need suport email me
        $mail->Password   = ' '; // email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
        $mail->Port       = 465; 
        
        $mail->setFrom(' ', 'Nanos Security');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Verification Code: $otp";
        $mail->Body = "
<div style='background-color: #020617; padding: 50px 15px; font-family: \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; margin: 0;'>
    <div style='max-width: 500px; margin: 0 auto; background-color: #0f172a; border-radius: 24px; padding: 40px; border: 1px solid #1e293b; text-align: center; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);'>
        
        <div style='margin-bottom: 30px;'>
            <div style='display: inline-block; width: 64px; height: 64px; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); border-radius: 18px; line-height: 64px; color: #ffffff; font-size: 30px; font-weight: 800; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);'>
                N
            </div>
            <h1 style='color: #ffffff; font-size: 26px; font-weight: 800; margin: 20px 0 10px 0; letter-spacing: -0.5px;'>Confirm Identity</h1>
            <div style='height: 2px; width: 40px; background: #6366f1; margin: 0 auto;'></div>
        </div>

        <p style='color: #94a3b8; font-size: 16px; line-height: 1.6; margin-bottom: 25px;'>
            A sign-in attempt to your <b>Nanos</b> account was made from a new device. Use the verification code below to authorize this session.
        </p>

        <div style='background: #1e293b; border-radius: 16px; padding: 30px; margin: 30px 0; border: 1px solid #334155; position: relative;'>
            <small style='color: #6366f1; text-transform: uppercase; letter-spacing: 2px; font-size: 11px; font-weight: 700;'>Verification Code</small>
            <div style='color: #ffffff; font-size: 48px; font-weight: 800; letter-spacing: 12px; margin-top: 10px; font-family: monospace;'>
                $otp
            </div>
        </div>

        <div style='background: rgba(245, 158, 11, 0.1); border-radius: 12px; padding: 15px; margin-bottom: 30px;'>
            <p style='color: #fbbf24; font-size: 13px; margin: 0; line-height: 1.5;'>
                <b>Note:</b> This code will expire in 10 minutes. If you did not request this, please change your password immediately.
            </p>
        </div>

        <p style='color: #475569; font-size: 13px;'>
            IP Address: " . $_SERVER['REMOTE_ADDR'] . "<br>
            Location: Bucharest, Romania
        </p>

        <hr style='border: 0; border-top: 1px solid #1e293b; margin: 40px 0;'>

        <p style='color: #334155; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;'>
            &copy; " . date('Y') . " Nanos Security Systems &bull; Automated Shield
        </p>
    </div>
</div>
";

        $mail->send();
        
        $_SESSION['temp_email'] = $email;

        $_SESSION['is_resetting'] = ($action == 'reset_request') ? true : false;
        
        echo json_encode(['status' => 'success', 'message' => 'OTP Sent']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Mailer Error: ' . $mail->ErrorInfo]);
    }

} elseif ($action == 'verify') {
    $email = $_SESSION['temp_email'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND otp_code = ? AND otp_expiry > NOW()");
    $stmt->execute([$email, $otp_in]);
    $user_data = $stmt->fetch();

    if ($user_data) {
        if (isset($_SESSION['is_resetting']) && $_SESSION['is_resetting'] === true) {
            echo json_encode(['status' => 'success', 'next' => 'password_reset']);
        } else {
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['logged_in'] = true;
            echo json_encode(['status' => 'success', 'next' => 'dashboard']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired code']);
    }

} elseif ($action == 'update_password') {
    $email = $_SESSION['temp_email'] ?? '';
    $new_hashed = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE users SET password = ?, otp_code = NULL WHERE email = ?");
    if ($stmt->execute([$new_hashed, $email])) {
        session_destroy();
        echo json_encode(['status' => 'success', 'message' => 'Password updated! Please login.']);
    }
}