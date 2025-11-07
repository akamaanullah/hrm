<?php include("admin/header.php"); ?>
<link rel="stylesheet" href="assets/css/style.css">
<div class="login-form">
    <div class="container">
        <div class="login-container">
            <!-- Logo -->
            <div class="login-logo">
                <img src="assets/images/LOGO.png" alt="Logo">
                <span>Register HRM Dashboard</span>
            </div>
            <!-- Registration Form -->
            <p class="login-subtitle">Enter your details below</p>
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="Username" class="form-label-form">Username</label>
                    <input type="text" class="form-control" id="Username" name="Username" placeholder="Username" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label-form">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email" required>
                </div>
                <div class="mb-3">
                    <label for="Password" class="form-label-form">Password</label>
                    <input type="password" class="form-control" id="Password" name="Password" placeholder="Enter Password" required>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label for="password" class="form-label-form mb-0">Confirm Password</label>
                    </div>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password" required>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" required>
                        <label class="form-check-label" for="remember">I agree to all Terms</label>
                    </div>
                </div>
                <div class="d-flex justify-content-center">
                    <button type="submit" class="btn btn-login">Sign Up</button>
                </div>
            </form>
            <p class="signup-text">
                Already have an account? <a href="login.php" class="signup-link">Sign In</a>
            </p>
        </div>
    </div>
</div>