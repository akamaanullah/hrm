<?php
session_start();
include("config.php");
// PHPMailer include karein
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // Agar composer se install kiya hai
// Agar manually dala hai to: require 'PHPMailer/src/PHPMailer.php'; etc.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    // Emp_id nikaalo
    $empCheck = $pdo->prepare("SELECT emp_id FROM employees WHERE email=?");
    $empCheck->execute([$email]);
    if ($empCheck->rowCount() > 0) {
        $empRow = $empCheck->fetch();
        $emp_id = $empRow['emp_id'];
        // Purane tokens delete karo
        $del = $pdo->prepare("DELETE FROM reset_password WHERE emp_id=?");
        $del->execute([$emp_id]);
        // Naya token insert karo
        $sql = "INSERT INTO reset_password (emp_id, email, token, expiry, used, created_at) VALUES (?, ?, ?, ?, 0, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$emp_id, $email, $token, $expiry]);
        // PHPMailer se email bhejein
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'imuhammadzain01@gmail.com';
            $mail->Password = '*** **** **** ****'; // Your actual SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->setFrom('imuhammadzain01@gmail.com', 'HRM Dashboard');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Link - HRM Dashboard';
            $mail->Body = 'Click the following link to reset your password: <a href="' . $resetLink . '">' . $resetLink . '</a><br><br>This link will expire in 1 hour.';
            $mail->send();
            $msg = 'Reset link sent to your email. Please check your inbox.';
        } catch (Exception $e) {
            $msg = 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
        }
    } else {
        $msg = "Email not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRM Dashboard</title>
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Add required CSS and JS files -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
</head>
<body>
    <link rel="stylesheet" href="assets/css/style.css">
    <div class="login-form">
        <div class="container">
            <div class="login-container">
                <!-- Logo -->
                <div class="login-logo">
                    <img src="assets/images/LOGO.png" alt="Logo">
                    <span>HRM Dashboard</span>
                </div>
                <div class="recover-logo mt-3">
                    <span>Need help with your password?</span>
                </div>
                <!-- Login Form -->
                <p class="login-subtitle">Enter the email you use for HRM, and we'll help you create a new password.</p>
                <?php if (isset($msg)) { ?>
                    <div class="alert alert-info text-center"><?php echo $msg; ?></div>
                <?php } ?>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label-form">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email"
                            required>
                    </div>
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-login">Send Reset Link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include "user/footer.php"; ?>