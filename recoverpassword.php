<?php include("admin/header.php"); ?>
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
            <p class="login-subtitle">Enter the email you use for HRM, and we’ll help you create a new password.</p>
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label-form">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email" required>
                </div>
                <div class="d-flex justify-content-center">
                    <button type="submit" class="btn btn-login">Reset</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include "admin/footer.php" ?>