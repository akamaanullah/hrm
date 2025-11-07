<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("config.php");
session_start();
$showForm = false;
$error = '';
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';
if ($email && $token) {
    // Token validate karo
    $sql = "SELECT * FROM reset_password WHERE email=? AND token=? AND expiry > NOW() AND used=0";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $token]);
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        $emp_id = $row['emp_id'];
        $showForm = true;
    } else {
        $error = "Invalid or expired reset link.";
    }
}
// Password reset logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['NewPassword'], $_POST['ConfirmPassword'], $_POST['email'], $_POST['token'])) {
    $email = $_POST['email'];
    $token = $_POST['token'];
    $newPassword = $_POST['NewPassword'];
    $confirmPassword = $_POST['ConfirmPassword'];
    if ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match!";
        $showForm = true;
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        // Update employee password
        $sql = "UPDATE employees SET password=? WHERE email=?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$hashedPassword, $email])) {
            // Token ko used=1 karo
            $upd = $pdo->prepare("UPDATE reset_password SET used=1 WHERE email=? AND token=?");
            $upd->execute([$email, $token]);
            // Get user role
            $sql = "SELECT role, emp_id FROM employees WHERE email=?";
            $stmt2 = $pdo->prepare($sql);
            $stmt2->execute([$email]);
            $user = $stmt2->fetch();
            $_SESSION['emp_id'] = $user['emp_id'];
            $_SESSION['emp_email'] = $email;
            $_SESSION['role'] = $user['role'];
            if ($user['role'] == 'admin') {
                echo "<script>alert('Password reset successfully! Redirecting to admin dashboard...'); window.location='admin/index.php';</script>";
            } else {
                echo "<script>alert('Password reset successfully! Redirecting to employee dashboard...'); window.location='user/userattendance.php';</script>";
            }
            exit;
        } else {
            $error = "Reset failed!";
            $showForm = false;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - HRM Dashboard</title>
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
    <div class="login-form">
        <div class="container">
            <div class="login-container">
                <!-- Logo -->
                <div class="login-logo">
                    <img src="assets/images/LOGO.png" alt="Logo">
                    <span>HRM Dashboard</span>
                </div>
                <div class="recover-logo text-center mt-3">
                    <span>Reset Password</span>
                </div>
                <!-- Login Form -->
                <p class="login-subtitle">Fill in the form below to reset your password.</p>
                <?php if ($error) { ?>
                    <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                <?php } ?>
                <?php if ($showForm) { ?>
                    <form action="" method="POST">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <div class="mb-3">
                            <label for="NewPassword" class="form-label-form">New Password</label>
                            <input type="password" class="form-control" id="NewPassword" name="NewPassword" placeholder="Enter New Password" required autocomplete="new-password">
                        </div>
                        <div class="mb-3">
                            <label for="ConfirmPassword" class="form-label-form">Confirm Password</label>
                            <input type="password" class="form-control" id="ConfirmPassword" name="ConfirmPassword" placeholder="Enter Confirm Password" required autocomplete="new-password">
                        </div>
                        <br>
                        <div class="d-flex justify-content-center">
                            <button type="submit" class="btn btn-login">Reset</button>
                        </div>
                    </form>
                <?php } ?>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>