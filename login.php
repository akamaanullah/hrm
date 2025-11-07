<?php
// Session security - Must be set before session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

session_start();

// Clear old session data
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 32400)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

// If already logged in, redirect based on role
if (isset($_SESSION['emp_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/index.php");
    } else if ($_SESSION['role'] == 'hr') {
        header("Location: hr/index.php");
    } else {
        header("Location: user/userdashboard.php");
    }
    exit();
}

require_once 'config.php';

// Check database connection
if (!$pdo) {
    die("Connection failed: Database connection not available");
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepared statement to prevent SQL injection
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch();

        // Check user status
        if ($user['status'] !== 'active') {
            $error = "Account is deactivated. Contact administrator.";
        } else if (password_verify($password, $user['password'])) {
            $_SESSION['emp_id'] = $user['emp_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['middle_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['middle_name'] = $user['middle_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();

            if ($user['role'] == 'admin') {
                header("Location: admin/index.php");
            } else if ($user['role'] == 'hr') {
                header("Location: hr/index.php");
            } else {
                header("Location: user/userdashboard.php");
            }
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Incorrect email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="description" content="HRM Portal - Human Resource Management System Login">
    <meta name="theme-color" content="#00bfa5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="HRM Portal">
    <meta name="mobile-web-app-capable" content="yes">
    <title>HRM PORTAL - Login</title>
    <link rel="icon" href="assets/images/LOGO.png" type="image/x-icon">
    <link rel="apple-touch-icon" href="assets/images/LOGO.png">
    <link rel="apple-touch-icon" sizes="192x192" href="assets/images/LOGO.png">
    <link rel="apple-touch-icon" sizes="512x512" href="assets/images/LOGO.png">
    <link rel="manifest" href="manifest.json">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            overflow: hidden;
        }

        .login-wrapper {
            display: flex;
            height: 100vh;
        }

        /* Left Column - Marketing/Information Section */
        .left-column {
            flex: 1;
            background: #00bfa5;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .left-column::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="3" fill="rgba(0,0,0,0.1)"/><circle cx="80" cy="40" r="2" fill="rgba(0,0,0,0.1)"/><circle cx="40" cy="80" r="4" fill="rgba(0,0,0,0.1)"/><circle cx="90" cy="90" r="2" fill="rgba(0,0,0,0.1)"/><circle cx="10" cy="60" r="3" fill="rgba(0,0,0,0.1)"/></svg>');
            opacity: 0.3;
        }

        .left-content {
            color: white;
            z-index: 1;
            position: relative;
            max-width: 500px;
        }

        .left-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .left-logo img {
            width: 90px;
            height: 90px;
            margin-right: 10px;
        }

        .left-headline {
            text-align: center;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .left-description {
            font-size: 1.1rem;
            margin-bottom: 3rem;
            opacity: 0.9;
        }

        .feature-cards {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 3rem;
        }

        .feature-card {
            background: rgb(0 0 0 / 10%);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 1rem;
            display: flex;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: #00a18b;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: white;
        }

        .feature-text h4 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
        }

        .feature-text p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .stats {
            display: flex;
            justify-content: space-around;
            width: 100%;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            display: block;
        }

        .stat-label {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        /* Right Column - Login Form */
        .right-column {
            flex: 1;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-form-container {
            width: 100%;
            max-width: 400px;
        }

        .user-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            padding: 10px;
        }

        .user-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 2rem;
        }

        .welcome-text h2 {
            color: #1f2937;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .welcome-text p {
            color: #6b7280;
            margin: 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            color: #374151;
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #00bfa5;
            box-shadow: 0 0 0 3px rgba(0, 191, 165, 0.1);
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            z-index: 10;
            pointer-events: none;
        }

        .input-with-icon {
            padding-left: 3rem;
        }

        .form-control:focus+.input-icon {
            color: #00bfa5;
        }

        .input-group:focus-within .input-icon {
            color: #00bfa5;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            z-index: 10;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-check-input {
            width: 1rem;
            height: 1rem;
        }

        .form-check-label {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .forgot-password {
            color: #00bfa5;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn-signin {
            width: 100%;
            background: linear-gradient(135deg, #00bfa5 0%, #02d6ba 100%);
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-signin:hover {
            transform: translateY(-1px);
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            background: white;
            padding: 0 1rem;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .social-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .social-btn {
            width: 50px;
            height: 50px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .social-btn:hover {
            border-color: #00bfa5;
        }

        .signup-text {
            text-align: center;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .signup-link {
            color: #00bfa5;
            text-decoration: none;
            font-weight: 500;
        }

        .signup-link:hover {
            text-decoration: underline;
        }

        .error {
            background: #fef2f2;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #fecaca;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
            }

            .left-column {
                display: none;
            }

            .right-column {
                flex: none;
                height: 100vh;
            }
        }
    </style>
</head>

<body>

    <div class="login-wrapper">
        <!-- Left Column - Marketing/Information Section -->
        <div class="left-column">
            <div class="left-content">
                <div class="left-logo">
                    <img src="assets/images/LOGO.png" alt="Logo">
                    <span>HRM Dashboard</span>
                </div>

                <h1 class="left-headline">Welcome to HRM</h1>
                <p class="left-description text-center">Streamline your workforce management with our comprehensive HR dashboard. Manage attendance, payroll, leaves, and more with ease.</p>

                <div class="feature-cards">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Attendance Management</h4>
                            <p>Track employee attendance & time records</p>
                        </div>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Payroll System</h4>
                            <p>Automated salary & payslip generation</p>
                        </div>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Leave Management</h4>
                            <p>Efficient leave requests & approvals</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Right Column - Login Form -->
        <div class="right-column">
            <div class="login-form-container">
                <div class="user-icon">
                    <img src="assets/images/LOGO.png" alt="Logo" style="width: 100px; height: 100px; object-fit: contain;">
                </div>

                <div class="welcome-text">
                    <h2>Welcome Back</h2>
                    <p>Sign in to your account to continue your journey</p>
                </div>

                <?php if (!empty($error)) { ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php } ?>

                <form action="" method="POST">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" class="form-control input-with-icon" id="email" name="username" placeholder="Enter your email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" class="form-control input-with-icon" id="password" name="password" placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Keep me signed in</label>
                        </div>

                    </div>

                    <button type="submit" class="btn-signin">
                        Sign In
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>




            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.classList.remove('fa-eye');
                toggleBtn.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleBtn.classList.remove('fa-eye-slash');
                toggleBtn.classList.add('fa-eye');
            }
        }
    </script>
    <!-- PWA Service Worker Registration -->
    <script src="assets/js/pwa-register.js"></script>
</body>

</html>