<?php
require_once 'config.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $gender = $_POST['gender'];
    $date_of_birth = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
    $phone = $_POST['phone'];
    $email = trim($_POST['email']);
    $address = $_POST['address'];
    $position = $_POST['position'];
    // $department = $_POST['department']; // Removed - will be set by admin
    $bank_name = $_POST['bank_name'];
    $account_title = $_POST['account_title'];
    $account_number = $_POST['account_number'];
    $bank_branch = $_POST['bank_branch'];
    $education_from_date = !empty($_POST['education_from_date']) ? $_POST['education_from_date'] : null;
    $education_to_date = !empty($_POST['education_to_date']) ? $_POST['education_to_date'] : null;
    $education_percentage = $_POST['education_percentage'];
    $specialization = $_POST['specialization'];
    $last_organization = $_POST['last_organization'];
    $last_designation = $_POST['last_designation'];
    $experience_from_date = !empty($_POST['experience_from_date']) ? $_POST['experience_from_date'] : null;
    $experience_to_date = !empty($_POST['experience_to_date']) ? $_POST['experience_to_date'] : null;
    $joining_date = !empty($_POST['joining_date']) ? $_POST['joining_date'] : null;
    $cnic = $_POST['cnic'];
    // $religion = $_POST['religion'];
    $emergency_contact = $_POST['emergency_contact'];
    $emergency_relation = $_POST['emergency_relation'];
    $marital_status = $_POST['marital_status'];
    $qualification_institution = $_POST['qualification_institution'];
    $account_type = $_POST['account_type'];
    
    // Validations
    $valid = true;
    $error = '';
    
    // Email validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $valid = false;
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email already exists in database
        $email_check_sql = "SELECT emp_id FROM employees WHERE email = ? LIMIT 1";
        $email_check_stmt = $pdo->prepare($email_check_sql);
        $email_check_stmt->execute([$email]);
        if ($email_check_stmt->rowCount() > 0) {
            $valid = false;
            $error = 'This email is already registered. Please use a different email address.';
        }
    }
    
    // Account number validation
    if ($valid && $account_type == 'IBAN number' && strlen($account_number) != 24) {
        $valid = false;
        $error = 'IBAN must be exactly 24 characters.';
    } elseif ($valid && $account_type == 'IBFT number' && (strlen($account_number) < 10 || strlen($account_number) > 20)) {
        $valid = false;
        $error = 'IBFT Account Number must be 10 to 20 digits.';
    } elseif ($valid && $account_type == 'Mobile Banking' && strlen($account_number) != 11) {
        $valid = false;
        $error = 'Mobile Banking Number must be exactly 11 digits.';
    }
    
    if (!$valid) {
        $msg = "<div class='alert alert-danger mb-3'>$error</div>";
    } else {
        // Handle file uploads
        $uploaded_files = [];
        $upload_dir = 'uploads/joining_documents/';
        // Create upload directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        // Handle CV upload
        if (isset($_FILES['cv_attachment']) && $_FILES['cv_attachment']['error'] == 0) {
            $cv_file = $_FILES['cv_attachment'];
            $cv_extension = strtolower(pathinfo($cv_file['name'], PATHINFO_EXTENSION));
            $allowed_cv_extensions = ['pdf', 'doc', 'docx'];
            if (in_array($cv_extension, $allowed_cv_extensions)) {
                $cv_filename = 'cv_' . time() . '_' . $first_name . '.' . $cv_extension;
                $cv_path = $upload_dir . $cv_filename;

                if (move_uploaded_file($cv_file['tmp_name'], $cv_path)) {
                    $uploaded_files['cv'] = $cv_path;
                }
            }
        }
        // Handle Id Card upload
        if (isset($_FILES['id_card_attachment']) && $_FILES['id_card_attachment']['error'] == 0) {
            $id_card_file = $_FILES['id_card_attachment'];
            $id_card_extension = strtolower(pathinfo($id_card_file['name'], PATHINFO_EXTENSION));
            $allowed_id_card_extensions = ['pdf', 'jpg', 'jpeg', 'png'];

            if (in_array($id_card_extension, $allowed_id_card_extensions)) {
                $id_card_filename = 'id_card_' . time() . '_' . $first_name . '.' . $id_card_extension;
                $id_card_path = $upload_dir . $id_card_filename;

                if (move_uploaded_file($id_card_file['tmp_name'], $id_card_path)) {
                    $uploaded_files['id_card'] = $id_card_path;
                }
            }
        }
        // Handle other documents upload
        if (isset($_FILES['other_documents']) && !empty($_FILES['other_documents']['name'])) {
            $other_files = $_FILES['other_documents'];
            $allowed_other_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            $other_docs = [];
            // Check if it's a single file or multiple files
            if (is_array($other_files['name'])) {
                // Multiple files
                for ($i = 0; $i < count($other_files['name']); $i++) {
                    if ($other_files['error'][$i] == 0) {
                        $file_extension = strtolower(pathinfo($other_files['name'][$i], PATHINFO_EXTENSION));

                        if (in_array($file_extension, $allowed_other_extensions)) {
                            $other_filename = 'doc_' . time() . '_' . $i . '_' . $first_name . '.' . $file_extension;
                            $other_path = $upload_dir . $other_filename;

                            if (move_uploaded_file($other_files['tmp_name'][$i], $other_path)) {
                                $other_docs[] = $other_path;
                            }
                        }
                    }
                }
            } else {
                // Single file
                if ($other_files['error'] == 0) {
                    $file_extension = strtolower(pathinfo($other_files['name'], PATHINFO_EXTENSION));

                    if (in_array($file_extension, $allowed_other_extensions)) {
                        $other_filename = 'doc_' . time() . '_0_' . $first_name . '.' . $file_extension;
                        $other_path = $upload_dir . $other_filename;

                        if (move_uploaded_file($other_files['tmp_name'], $other_path)) {
                            $other_docs[] = $other_path;
                        }
                    }
                }
            }
            if (!empty($other_docs)) {
                $uploaded_files['other_documents'] = json_encode($other_docs);
            }
        }
        $sql = 'INSERT INTO employees (
        first_name, middle_name, last_name, gender, date_of_birth, phone, email, password, address, cnic, religion, marital_status, emergency_contact, emergency_relation, designation, line_manager, department, sub_department, bank_name, account_title, account_number, account_type, bank_branch, salary, joining_date, qualification_institution, education_from_date, education_to_date, education_percentage, specialization, last_organization, last_designation, experience_from_date, experience_to_date, shift_id, job_type, status, role, position
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        $stmt = $pdo->prepare($sql);
        if (!$stmt) {
            die('Prepare failed: ' . implode(', ', $pdo->errorInfo()));
        }
        if ($stmt->execute([
            $first_name,
            $middle_name,
            $last_name,
            $gender,
            $date_of_birth,
            $phone,
            $email,
            null,
            $address,
            $cnic,
            null,
            $marital_status,
            $emergency_contact,
            $emergency_relation,
            $position,
            null,
            null,
            null,
            $bank_name,
            $account_title,
            $account_number,
            $account_type,
            $bank_branch,
            0,
            $joining_date,
            $qualification_institution,
            $education_from_date,
            $education_to_date,
            $education_percentage,
            $specialization,
            $last_organization,
            $last_designation,
            $experience_from_date,
            $experience_to_date,
            null,
            null,
            'inactive',
            'user',
            $position
        ])) {
            // Get the inserted employee ID
            $emp_id = $pdo->lastInsertId();
            // Update employees table with file paths
            $cv_attachment_path = isset($uploaded_files['cv']) ? $uploaded_files['cv'] : null;
            $id_card_attachment_path = isset($uploaded_files['id_card']) ? $uploaded_files['id_card'] : null;
            $other_documents_path = isset($uploaded_files['other_documents']) ? $uploaded_files['other_documents'] : null;
            if ($cv_attachment_path || $id_card_attachment_path || $other_documents_path) {
                try {
                    $update_sql = "UPDATE employees SET ";
                    $update_params = [];
                    if ($cv_attachment_path) {
                        $update_sql .= "cv_attachment = :cv_attachment";
                        $update_params[':cv_attachment'] = $cv_attachment_path;
                    }
                    if ($id_card_attachment_path) {
                        if ($cv_attachment_path) {
                            $update_sql .= ", ";
                        }
                        $update_sql .= "id_card_attachment = :id_card_attachment";
                        $update_params[':id_card_attachment'] = $id_card_attachment_path;
                    }
                    if ($other_documents_path) {
                        if ($cv_attachment_path || $id_card_attachment_path) {
                            $update_sql .= ", ";
                        }
                        $update_sql .= "other_documents = :other_documents";
                        $update_params[':other_documents'] = $other_documents_path;
                    }
                    $update_sql .= " WHERE emp_id = :emp_id";
                    $update_params[':emp_id'] = $emp_id;
                    $update_stmt = $pdo->prepare($update_sql);
                    $update_stmt->execute($update_params);
                } catch (Exception $e) {
                    error_log("Failed to update employees table with file paths: " . $e->getMessage());
                }
            }
            // Save uploaded documents to database
            if (!empty($uploaded_files)) {
                try {
                    $doc_stmt = $pdo->prepare("INSERT INTO employee_documents (emp_id, document_type, file_path, original_filename) VALUES (?, ?, ?, ?)");
                    // Save CV if uploaded
                    if (isset($uploaded_files['cv'])) {
                        $cv_original_name = $_FILES['cv_attachment']['name'];
                        $doc_stmt->execute([$emp_id, 'cv', $uploaded_files['cv'], $cv_original_name]);
                    }
                    // Save Id Card if uploaded
                    if (isset($uploaded_files['id_card'])) {
                        $id_card_original_name = $_FILES['id_card_attachment']['name'];
                        $doc_stmt->execute([$emp_id, 'id_card', $uploaded_files['id_card'], $id_card_original_name]);
                    }
                    // Save other documents if uploaded
                    if (isset($uploaded_files['other_documents'])) {
                        $other_docs = json_decode($uploaded_files['other_documents'], true);
                        $other_files = $_FILES['other_documents'];
                        for ($i = 0; $i < count($other_docs); $i++) {
                            // Handle both single and multiple file scenarios
                            if (is_array($other_files['name'])) {
                                $original_name = $other_files['name'][$i];
                            } else {
                                $original_name = $other_files['name'];
                            }
                            $doc_stmt->execute([$emp_id, 'other', $other_docs[$i], $original_name]);
                        }
                    }
                } catch (Exception $e) {
                    // Document saving failed, but employee was created successfully
                    error_log("Document saving failed: " . $e->getMessage());
                }
            }
            $msg = "<div class='alert alert-success mb-3'>Form submitted successfully! Your application is under review. Admin will complete your profile soon.</div>";
            // Redirect to admin joining form page after 3 seconds
            echo "<script>
            setTimeout(function() {
                window.location.href = 'admin/joining-form.php';
            }, 3000);
        </script>";
        } else {
            $msg = "<div class='alert alert-danger mb-3'>Error: " . implode(', ', $stmt->errorInfo()) . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Joining Form</title>
    <link rel="icon" type="image/x-icon" href="assets/images/LOGO.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/LOGO.png">
    <link rel="apple-touch-icon" href="assets/images/LOGO.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .user-joining-form .card {
            border-radius: 15px;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.98);
            margin: 1rem 0rem;
            z-index: 10;
            position: relative;
            padding: 1.5rem;
        }
        .user-joining-form h3 {
            text-align: center;
            font-size: 2.3rem;
            font-family: "Open Sans", sans-serif !important;
            font-style: italic;
            text-transform: uppercase;
            color: black;
            font-weight: 700;
        }
        .user-joining-form .form-label {
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 0.8rem;
            font-family: "Poppins", sans-serif;
            letter-spacing: 0.5px;
        }
        .user-joining-form .form-control,
        .user-joining-form .form-select {
            border-radius: 5px;
            height: 40px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            font-family: "Poppins", sans-serif;
            padding: 7px 10px;
        }
        .user-joining-form .form-control:focus,
        .user-joining-form .form-select:focus {
            border-color: #00bfa5;
            box-shadow: 0 0 0 0.2rem rgb(0 191 165 / 14%);
            background: #fff;
            transform: translateY(-2px);
        }
        .user-joining-form .form-control:hover,
        .user-joining-form .form-select:hover {
            border-color: #00bfa5;
            background: #fff;
        }
        .user-joining-form .btn-primary {
            background: linear-gradient(135deg, #00bfa5 0%, #02d6ba 100%);
            border: none;
            border-radius: 8px;
            font-weight: 500;
            padding: 10px 20px;
            font-size: 1rem;
            font-family: "Poppins", sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            color: white;
        }
        .user-joining-form .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        .user-joining-form .btn-primary:hover::before {
            left: 100%;
        }
        .user-joining-form .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0px 3px 14px 0px rgb(0 191 165 / 52%);
            background: linear-gradient(135deg, #02d6ba 0%, #00bfa5 100%);
            color: white;
        }
        .user-joining-form .btn-primary:active {
            transform: translateY(-1px);
        }
        .user-joining-form .card-body {
            padding: 1rem 0rem;
        }
        /* Fix form alignment and spacing */
        .user-joining-form .form-control,
        .user-joining-form .form-select {
            margin-bottom: 0.5rem;
        }
        /* Section headers with Poppins font */
        .user-joining-form h5 {
            font-family: "Poppins", sans-serif;
        }
        .user-joining-form .row {
            margin-left: 0;
            margin-right: 0;
        }
        .user-joining-form .col-md-6 {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        /* Section headers alignment */
        .user-joining-form .d-flex.align-items-center {
            margin-left: 0.75rem;
            margin-right: 0.75rem;
        }
        /* Section Cards */
        .section-card {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }
        /* Alert Styling */
        .alert {
            border-radius: 12px;
            border: none;
            font-weight: 500;
        }
        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
        }
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
        }
        /* Responsive Design */
        @media (max-width: 767px) {
            .user-joining-form .card {
                margin-top: -80px;
                padding: 2rem 1rem 1.5rem 1rem;
            }
            .user-joining-form .card-body {
                padding: 2rem 1rem;
            }
            .user-joining-form .btn-primary {
                padding: 12px 30px;
                font-size: 1rem;
            }
            .user-joining-form .col-md-6 {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            .user-joining-form .d-flex.align-items-center {
                margin-left: 0.5rem;
                margin-right: 0.5rem;
            }
        }
        /* Animation for form fields */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .form-control,
        .form-select {
            animation: fadeInUp 0.6s ease forwards;
        }
        .col-md-6:nth-child(1) .form-control {
            animation-delay: 0.1s;
        }
        .col-md-6:nth-child(2) .form-control {
            animation-delay: 0.2s;
        }
        .col-md-6:nth-child(3) .form-control {
            animation-delay: 0.3s;
        }
        .col-md-6:nth-child(4) .form-control {
            animation-delay: 0.4s;
        }
        /* File Input Styling */
        .user-joining-form input[type="file"] {
            padding: 8px 12px;
            border: 2px dashed #00bfa5;
            background: rgba(0, 191, 165, 0.05);
            transition: all 0.3s ease;
        }
        .user-joining-form input[type="file"]:hover {
            border-color: #02d6ba;
            background: rgba(0, 191, 165, 0.1);
        }
        .user-joining-form input[type="file"]:focus {
            border-color: #00bfa5;
            box-shadow: 0 0 0 0.2rem rgb(0 191 165 / 14%);
            background: rgba(0, 191, 165, 0.1);
        }
        .user-joining-form .text-muted {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 5px;
            display: block;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const accountType = document.querySelector('[name="account_type"]');
            const accountNumber = document.querySelector('[name="account_number"]');
            if (accountType && accountNumber) {
                accountNumber.disabled = true;
                accountType.addEventListener('change', function() {
                    if (this.value === '') {
                        accountNumber.value = '';
                        accountNumber.disabled = true;
                    } else {
                        accountNumber.disabled = false;
                    }
                    accountNumber.removeAttribute('maxlength');
                    accountNumber.removeAttribute('minlength');
                    if (this.value === 'IBAN number') {
                        accountNumber.setAttribute('maxlength', '24');
                        accountNumber.setAttribute('minlength', '24');
                        accountNumber.placeholder = '24 digit IBAN';
                        accountNumber.oninput = function() {
                            this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
                        };
                    } else if (this.value === 'IBFT number') {
                        accountNumber.setAttribute('maxlength', '20');
                        accountNumber.setAttribute('minlength', '10');
                        accountNumber.placeholder = '10-20 digit IBFT';
                        accountNumber.oninput = function() {
                            this.value = this.value.replace(/\D/g, '');
                        };
                    } else if (this.value === 'Mobile Banking') {
                        accountNumber.setAttribute('maxlength', '11');
                        accountNumber.setAttribute('minlength', '11');
                        accountNumber.placeholder = '11 digit Mobile Number';
                        accountNumber.oninput = function() {
                            this.value = this.value.replace(/\D/g, '');
                        };
                    } else {
                        accountNumber.removeAttribute('maxlength');
                        accountNumber.removeAttribute('minlength');
                        accountNumber.placeholder = '';
                        accountNumber.oninput = null;
                    }
                });
                accountNumber.oninput = null;
            }
            // Phone & Emergency Contact: 11 digits only
            const phone = document.querySelector('[name="phone"]');
            const emergency = document.querySelector('[name="emergency_contact"]');
            [phone, emergency].forEach(function(field) {
                if (field) {
                    field.addEventListener('input', function() {
                        this.value = this.value.replace(/\D/g, '').slice(0, 11);
                    });
                }
            });
            // CNIC: 13 digits only
            const cnic = document.querySelector('[name="cnic"]');
            if (cnic) {
                cnic.addEventListener('input', function() {
                    this.value = this.value.replace(/\D/g, '').slice(0, 13);
                });
            }
            
            // Email validation - Real-time check
            const emailInput = document.getElementById('emailInput');
            const emailFeedback = document.getElementById('emailFeedback');
            let emailCheckTimeout;
            
            if (emailInput) {
                emailInput.addEventListener('input', function() {
                    const email = this.value.trim();
                    
                    // Clear previous timeout
                    clearTimeout(emailCheckTimeout);
                    
                    // Email format validation
                    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                    
                    if (email === '') {
                        emailFeedback.textContent = '';
                        emailFeedback.style.color = '';
                        this.style.borderColor = '';
                        return;
                    }
                    
                    if (!emailRegex.test(email)) {
                        emailFeedback.textContent = 'Invalid email format';
                        emailFeedback.style.color = '#dc3545';
                        this.style.borderColor = '#dc3545';
                        return;
                    }
                    
                    // Check if email already exists (with debounce)
                    emailCheckTimeout = setTimeout(function() {
                        fetch('check-email.php?email=' + encodeURIComponent(email))
                            .then(response => response.json())
                            .then(data => {
                                if (data.exists) {
                                    emailFeedback.textContent = 'This email is already registered';
                                    emailFeedback.style.color = '#dc3545';
                                    emailInput.style.borderColor = '#dc3545';
                                } else {
                                    emailFeedback.textContent = 'Email is available ✓';
                                    emailFeedback.style.color = '#28a745';
                                    emailInput.style.borderColor = '#28a745';
                                }
                            })
                            .catch(error => {
                                console.error('Email check error:', error);
                            });
                    }, 500); // 500ms debounce
                });
            }
            
            // Form validation on submit
            document.querySelector('form').addEventListener('submit', function(e) {
                let valid = true;
                let msg = '';
                
                // Email validation
                const email = document.getElementById('emailInput').value.trim();
                const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                
                if (!email || !emailRegex.test(email)) {
                    valid = false;
                    msg = 'Please enter a valid email address.';
                }
                
                // Account number validation
                if (valid) {
                    const accountType = document.querySelector('[name="account_type"]').value;
                    const accountNumber = document.querySelector('[name="account_number"]').value;
                    
                    if (accountType === 'IBAN number') {
                        if (accountNumber.length !== 24) {
                            valid = false;
                            msg = 'IBAN must be exactly 24 characters.';
                        }
                    } else if (accountType === 'IBFT number') {
                        if (accountNumber.length < 10 || accountNumber.length > 20) {
                            valid = false;
                            msg = 'IBFT Account Number must be 10 to 20 digits.';
                        }
                    } else if (accountType === 'Mobile Banking') {
                        if (accountNumber.length !== 11) {
                            valid = false;
                            msg = 'Mobile Banking Number must be exactly 11 digits.';
                        }
                    }
                }
                
                if (!valid) {
                    e.preventDefault();
                    alert(msg);
                }
            });
        });
    </script>
    <div class="user-joining-form">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-9">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h3 class="mb-4">Joining Form</h3>
                            <?php echo $msg; ?>
                            <form method="post" enctype="multipart/form-data">
                                <!-- Personal Information -->
                                <div class="d-flex align-items-center mb-3 mt-2">
                                    <span style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; margin-right: 10px; background: linear-gradient(135deg, #00bfa5, #02d6ba); border-radius: 8px;">
                                        <i class="fas fa-user" style="color: white; font-size: 16px;"></i>
                                    </span>
                                    <h5 class="mb-0 fw-bold text-dark">Personal Information</h5>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" class="form-control" required placeholder="First Name">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" name="middle_name" class="form-control" placeholder="Middle Name">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" name="last_name" class="form-control" required placeholder="Last Name">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" name="date_of_birth" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Gender</label>
                                        <select name="gender" class="form-select" required>
                                            <option value="">Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" class="form-control" required placeholder="03XX-XXXXXXX" maxlength="11" minlength="11" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" id="emailInput" class="form-control" required placeholder="you@email.com">
                                        <small class="text-muted" id="emailFeedback"></small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Address</label>
                                        <input type="text" name="address" class="form-control" required placeholder="Your Address" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Job Title</label>
                                        <input type="text" name="position" class="form-control" required placeholder="Your Job Title" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">ID card Number</label>
                                        <input type="text" name="cnic" class="form-control" required placeholder="XXXXX-XXXXXXX-X" maxlength="13" minlength="13" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Emergency Contact</label>
                                        <input type="text" name="emergency_contact" class="form-control" placeholder="03XX-XXXXXXX" maxlength="11" minlength="11" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Emergency Contact Relation</label>
                                        <input type="text" name="emergency_relation" class="form-control" placeholder="Father, Mother, Brother, Sister, Spouse Etc." required>
                                    </div>
                                </div>
                                <!-- Bank Details -->
                                <div class="d-flex align-items-center mb-3 mt-4">
                                    <span style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; margin-right: 10px; background: linear-gradient(135deg, #00bfa5, #02d6ba); border-radius: 8px;">
                                        <i class="fas fa-university" style="color: white; font-size: 16px;"></i>
                                    </span>
                                    <h5 class="mb-0 fw-bold text-dark">Bank Details</h5>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Bank Name</label>
                                        <select name="bank_name" class="form-select" required>
                                            <option value="">Select Bank</option>
                                            <option value="HBL">HBL</option>
                                            <option value="ALHabib">AL Habib</option>
                                            <option value="MCB">MCB</option>
                                            <option value="UBL">UBL</option>
                                            <option value="Meezan">Meezan</option>
                                            <option value="Allied">Allied</option>
                                            <option value="Bank Alfalah">Bank Alfalah</option>
                                            <option value="Askari">Askari</option>
                                            <option value="Faysal">Faysal</option>
                                            <option value="Habib Metro">Habib Metro</option>
                                            <option value="Bank Alfaha">Bank Alfaha</option>
                                            <option value="Soneri">Soneri</option>
                                            <option value="JS Bank">JS Bank</option>
                                            <option value="Bank Islami">Bank Islami</option>
                                            <option value="Standard Chartered">Standard Chartered</option>
                                            <option value="EasyPaisa">EasyPaisa</option>
                                            <option value="JazzCash">JazzCash</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Account Type</label>
                                        <select name="account_type" class="form-select" required>
                                            <option value="">Select Account Type</option>
                                            <option value="IBAN number" <?php if (isset($_POST['account_type']) && $_POST['account_type'] == 'IBAN number') echo 'selected'; ?>>IBAN</option>
                                            <option value="IBFT number" <?php if (isset($_POST['account_type']) && $_POST['account_type'] == 'IBFT number') echo 'selected'; ?>>IBFT</option>
                                            <option value="Mobile Banking" <?php if (isset($_POST['account_type']) && $_POST['account_type'] == 'Mobile Banking') echo 'selected'; ?>>Mobile Banking</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Account Title</label>
                                        <input type="text" name="account_title" class="form-control" placeholder="Account Title" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Account Number</label>
                                        <input type="text" name="account_number" class="form-control" placeholder="Account Number" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Bank Branch</label>
                                        <input type="text" name="bank_branch" class="form-control" placeholder="Bank Branch" required>
                                    </div>
                                </div>
                                <!-- Education -->
                                <div class="d-flex align-items-center mb-3 mt-4">
                                    <span style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; margin-right: 10px; background: linear-gradient(135deg, #00bfa5, #02d6ba); border-radius: 8px;">
                                        <i class="fas fa-graduation-cap" style="color: white; font-size: 16px;"></i>
                                    </span>
                                    <h5 class="mb-0 fw-bold text-dark">Education</h5>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Qualification</label>
                                        <input type="text" name="qualification_institution" class="form-control" placeholder="Qualification" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Degree / Certification</label>
                                        <input type="text" name="education_percentage" class="form-control" placeholder="Degree Or Certification" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Professional Expertise</label>
                                        <input type="text" name="specialization" class="form-control" placeholder="Professional Expertise" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">College / University</label>
                                        <input type="text" name="marital_status" class="form-control" placeholder="College / University" required>
                                    </div>
                                </div>
                                <!-- Experience -->
                                <div class="d-flex align-items-center mb-3 mt-4">
                                    <span style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; margin-right: 10px; background: linear-gradient(135deg, #00bfa5, #02d6ba); border-radius: 8px;">
                                        <i class="fas fa-briefcase" style="color: white; font-size: 16px;"></i>
                                    </span>
                                    <h5 class="mb-0 fw-bold text-dark">Experience</h5>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Last Employer</label>
                                        <input type="text" name="last_organization" class="form-control" placeholder="Last Employeer" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Last Designation</label>
                                        <input type="text" name="last_designation" class="form-control" placeholder="Last Designation" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Experience From Date</label>
                                        <input type="date" name="experience_from_date" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Experience To Date</label>
                                        <input type="date" name="experience_to_date" class="form-control">
                                    </div>
                                </div>
                                <!-- Document Attachments -->
                                <div class="d-flex align-items-center mb-3 mt-4">
                                    <span style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; margin-right: 10px; background: linear-gradient(135deg, #00bfa5, #02d6ba); border-radius: 8px;">
                                        <i class="fas fa-paperclip" style="color: white; font-size: 16px;"></i>
                                    </span>
                                    <h5 class="mb-0 fw-bold text-dark">Document Attachments</h5>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Resume Attachment</label>
                                        <input type="file" name="cv_attachment" class="form-control" accept=".pdf,.doc,.docx" required>
                                        <small class="text-muted">Upload your Resume (PDF, DOC, DOCX only)</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Id Card Attachment</label>
                                        <input type="file" name="id_card_attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                        <small class="text-muted">Upload your Id Card (PDF, JPG, PNG only)</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Other Documents Attachment</label>
                                        <input type="file" name="other_documents" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>
                                        <small class="text-muted">Upload other relevant documents Attachment (PDF, DOC, DOCX, JPG, PNG)</small>
                                    </div>
                                </div>
                                <div class="mt-4 text-center">
                                    <button type="submit" class="btn btn-primary px-4">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>