<?php
// Disable error reporting to prevent HTML output
error_reporting(0);
ini_set('display_errors', 0);

include '../../../config.php'; // Yahan apne PDO connection ka path sahi karein

header('Content-Type: application/json');

// PDO connection already available from config.php
if (!isset($pdo)) {
    echo json_encode(['error' => 'Database connection failed', 'details' => 'PDO connection not available']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // If no JSON data, check for form data
    if (!$data) {
        $data = $_POST;
    }

    error_log("Received action: " . ($data['action'] ?? 'no action'));
    error_log("Received data: " . print_r($data, true));

    // Handle soft delete
    if (isset($data['action']) && $data['action'] === 'soft_delete' && isset($data['emp_id'])) {
        try {
            $stmt = $pdo->prepare("UPDATE employees SET is_deleted = 1 WHERE emp_id = :emp_id");
            $stmt->execute([':emp_id' => $data['emp_id']]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // Handle restore employee
    if (isset($data['action']) && $data['action'] === 'restore' && isset($data['emp_id'])) {
        try {
            $stmt = $pdo->prepare("UPDATE employees SET is_deleted = 0 WHERE emp_id = :emp_id");
            $stmt->execute([':emp_id' => $data['emp_id']]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // Handle permanent delete employee (from database)
    if (isset($data['action']) && $data['action'] === 'permanent_delete' && isset($data['emp_id'])) {
        try {
            // Delete employee permanently from database
            $stmt = $pdo->prepare("DELETE FROM employees WHERE emp_id = :emp_id");
            $stmt->execute([':emp_id' => $data['emp_id']]);
            
            // Also delete related records (optional - uncomment if needed)
            // $pdo->prepare("DELETE FROM attendance WHERE emp_id = :emp_id")->execute([':emp_id' => $data['emp_id']]);
            // $pdo->prepare("DELETE FROM leaves WHERE emp_id = :emp_id")->execute([':emp_id' => $data['emp_id']]);
            // $pdo->prepare("DELETE FROM payroll WHERE emp_id = :emp_id")->execute([':emp_id' => $data['emp_id']]);
            
            echo json_encode(['success' => true, 'message' => 'Employee permanently deleted from database']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit;
    }

    // Handle update_joining_full action (HR completing employee details)
    if (isset($data['action']) && $data['action'] === 'update_joining_full' && isset($data['emp_id'])) {
        $emp_id = $data['emp_id'];
        $first_name = $data['first_name'] ?? '';
        $middle_name = $data['middle_name'] ?? '';
        $last_name = $data['last_name'] ?? '';
        $gender = $data['gender'] ?? '';
        $date_of_birth = $data['date_of_birth'] ?? '';
        $phone = $data['phone'] ?? '';
        $email = $data['email'] ?? '';
        $address = $data['address'] ?? '';
        $cnic = $data['cnic'] ?? '';
        $religion = $data['religion'] ?? '';
        $marital_status = $data['marital_status'] ?? '';
        $emergency_contact = $data['emergency_contact'] ?? '';
        $emergency_relation = $data['emergency_relation'] ?? '';
        $designation = $data['position'] ?? '';
        $line_manager = $data['line_manager'] ?? '';
        $department = $data['department_id'] ?? '';
        $sub_department = $data['sub_department'] ?? '';
        $bank_name = $data['bank_name'] ?? '';
        $account_title = $data['account_title'] ?? '';
        $account_number = $data['account_number'] ?? '';
        $bank_branch = $data['bank_branch'] ?? '';
        $salary = $data['salary'] ?? '';
        $joining_date = $data['joining_date'] ?? '';
        $qualification_institution = $data['qualification_institution'] ?? '';
        $education_from_date = $data['education_from_date'] ?? '';
        $education_to_date = $data['education_to_date'] ?? '';
        $education_percentage = $data['education_percentage'] ?? '';
        $specialization = $data['specialization'] ?? '';
        $last_organization = $data['last_organization'] ?? '';
        $last_designation = $data['last_designation'] ?? '';
        $experience_from_date = $data['experience_from_date'] ?? '';
        $experience_to_date = $data['experience_to_date'] ?? '';
        $shift_id = $data['shift_id'] ?? null;
        $job_type = $data['job_type'] ?? '';
        $account_type = $data['account_type'] ?? '';
        $password = $data['password'] ?? '';

        // Validate gender field
        $valid_genders = ['Male', 'Female', 'Other'];
        error_log("Received gender value: '" . $gender . "'");
        if (!empty($gender) && !in_array($gender, $valid_genders)) {
            echo json_encode(['success' => false, 'message' => 'Invalid gender value. Must be Male, Female, or Other.']);
            exit;
        }
        // Set default gender if empty
        if (empty($gender)) {
            $gender = 'Male'; // Default value
        }
        
        // Email validation
        $email = trim($email);
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
            exit;
        }
        
        // Check if email already exists (excluding current employee)
        $email_check_sql = "SELECT emp_id FROM employees WHERE email = ? AND emp_id != ? LIMIT 1";
        $email_check_stmt = $pdo->prepare($email_check_sql);
        $email_check_stmt->execute([$email, $emp_id]);
        if ($email_check_stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'This email is already registered. Please use a different email address.']);
            exit;
        }

        try {
            $updateFields = [
                'first_name = :first_name',
                'middle_name = :middle_name',
                'last_name = :last_name',
                'gender = :gender',
                'date_of_birth = :date_of_birth',
                'phone = :phone',
                'email = :email',
                'address = :address',
                'cnic = :cnic',
                // 'religion = :religion',
                'marital_status = :marital_status',
                'emergency_contact = :emergency_contact',
                'emergency_relation = :emergency_relation',
                'designation = :designation',
                'line_manager = :line_manager',
                'department_id = :department',
                'sub_department = :sub_department',
                'bank_name = :bank_name',
                'account_title = :account_title',
                'account_number = :account_number',
                'bank_branch = :bank_branch',
                'salary = :salary',
                'joining_date = :joining_date',
                'qualification_institution = :qualification_institution',
                'education_from_date = :education_from_date',
                'education_to_date = :education_to_date',
                'education_percentage = :education_percentage',
                'specialization = :specialization',
                'last_organization = :last_organization',
                'last_designation = :last_designation',
                'experience_from_date = :experience_from_date',
                'experience_to_date = :experience_to_date',
                'shift_id = :shift_id',
                'job_type = :job_type',
                'account_type = :account_type',
                'status = :status'
            ];

            // Add password update if provided
            if (!empty($password)) {
                $updateFields[] = 'password = :password';
            }

            $sql = "UPDATE employees SET " . implode(', ', $updateFields) . " WHERE emp_id = :emp_id";
            $stmt = $pdo->prepare($sql);

            $params = [
                ':first_name' => $first_name,
                ':middle_name' => $middle_name,
                ':last_name' => $last_name,
                ':gender' => $gender,
                ':date_of_birth' => $date_of_birth,
                ':phone' => $phone,
                ':email' => $email,
                ':address' => $address,
                ':cnic' => $cnic,
                // ':religion' => $religion,
                ':marital_status' => $marital_status,
                ':emergency_contact' => $emergency_contact,
                ':emergency_relation' => $emergency_relation,
                ':designation' => $designation,
                ':line_manager' => $line_manager,
                ':department' => $department,
                ':sub_department' => $sub_department,
                ':bank_name' => $bank_name,
                ':account_title' => $account_title,
                ':account_number' => $account_number,
                ':bank_branch' => $bank_branch,
                ':salary' => $salary,
                ':joining_date' => $joining_date,
                ':qualification_institution' => $qualification_institution,
                ':education_from_date' => $education_from_date,
                ':education_to_date' => $education_to_date,
                ':education_percentage' => $education_percentage,
                ':specialization' => $specialization,
                ':last_organization' => $last_organization,
                ':last_designation' => $last_designation,
                ':experience_from_date' => $experience_from_date,
                ':experience_to_date' => $experience_to_date,
                ':shift_id' => $shift_id,
                ':job_type' => $job_type,
                ':account_type' => $account_type,
                ':status' => 'active',
                ':emp_id' => $emp_id
            ];

            // Add password parameter if provided
            if (!empty($password)) {
                $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $stmt->execute($params);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // Update employee
    if (isset($data['action']) && $data['action'] === 'update' && isset($data['emp_id'])) {
        $emp_id = $data['emp_id'];
        $first_name = $data['first_name'] ?? '';
        $middle_name = $data['middle_name'] ?? '';
        $last_name = $data['last_name'] ?? '';
        $gender = $data['gender'] ?? '';
        $date_of_birth = $data['date_of_birth'] ?? '';
        $phone = $data['phone'] ?? '';
        $email = $data['email'] ?? '';
        $address = $data['address'] ?? '';
        $cnic = $data['cnic'] ?? '';
        $religion = $data['religion'] ?? '';
        $marital_status = $data['marital_status'] ?? '';
        $emergency_contact = $data['emergency_contact'] ?? '';
        $emergency_relation = $data['emergency_relation'] ?? '';
        $designation = $data['position'] ?? '';
        $line_manager = $data['line_manager'] ?? '';
        $department = $data['department_id'] ?? '';
        $sub_department = $data['sub_department'] ?? '';
        $bank_name = $data['bank_name'] ?? '';
        $account_title = $data['account_title'] ?? '';
        $account_number = $data['account_number'] ?? '';
        $bank_branch = $data['bank_branch'] ?? '';
        $salary = $data['salary'] ?? '';
        $joining_date = $data['joining_date'] ?? '';
        $qualification_institution = $data['qualification_institution'] ?? '';
        $education_from_date = $data['education_from_date'] ?? '';
        $education_to_date = $data['education_to_date'] ?? '';
        $education_percentage = $data['education_percentage'] ?? '';
        $specialization = $data['specialization'] ?? '';
        $last_organization = $data['last_organization'] ?? '';
        $last_designation = $data['last_designation'] ?? '';
        $experience_from_date = $data['experience_from_date'] ?? '';
        $experience_to_date = $data['experience_to_date'] ?? '';
        $shift_id = $data['shift_id'] ?? null;
        $job_type = $data['job_type'] ?? '';
        $account_type = $data['account_type'] ?? '';

        // Set default gender if empty
        if (empty($gender)) {
            $gender = 'Male'; // Default value
        }
        
        // Email validation
        $email = trim($email);
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
            exit;
        }
        
        // Check if email already exists (excluding current employee)
        $email_check_sql = "SELECT emp_id FROM employees WHERE email = ? AND emp_id != ? LIMIT 1";
        $email_check_stmt = $pdo->prepare($email_check_sql);
        $email_check_stmt->execute([$email, $emp_id]);
        if ($email_check_stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'This email is already registered. Please use a different email address.']);
            exit;
        }
        
        // Check if password is provided
        $password = $data['password'] ?? '';
        $updatePassword = !empty($password);

        try {
            // Build UPDATE query dynamically based on whether password is provided
            $updateQuery = "UPDATE employees SET
                first_name = :first_name,
                middle_name = :middle_name,
                last_name = :last_name,
                gender = :gender,
                date_of_birth = :date_of_birth,
                phone = :phone,
                email = :email,
                address = :address,
                cnic = :cnic,
                -- religion = :religion,
                marital_status = :marital_status,
                emergency_contact = :emergency_contact,
                emergency_relation = :emergency_relation,
                designation = :designation,
                line_manager = :line_manager,
                department_id = :department,
                sub_department = :sub_department,
                bank_name = :bank_name,
                account_title = :account_title,
                account_number = :account_number,
                bank_branch = :bank_branch,
                salary = :salary,
                joining_date = :joining_date,
                qualification_institution = :qualification_institution,
                education_from_date = :education_from_date,
                education_to_date = :education_to_date,
                education_percentage = :education_percentage,
                specialization = :specialization,
                last_organization = :last_organization,
                last_designation = :last_designation,
                experience_from_date = :experience_from_date,
                experience_to_date = :experience_to_date,
                shift_id = :shift_id,
                job_type = :job_type,
                account_type = :account_type,
                status = 'active'";
            
            // Add password to query if provided
            if ($updatePassword) {
                $updateQuery .= ", password = :password";
            }
            
            $updateQuery .= " WHERE emp_id = :emp_id";
            
            $stmt = $pdo->prepare($updateQuery);
            
            // Build parameters array
            $params = [
                ':first_name' => $first_name,
                ':middle_name' => $middle_name,
                ':last_name' => $last_name,
                ':gender' => $gender,
                ':date_of_birth' => $date_of_birth,
                ':phone' => $phone,
                ':email' => $email,
                ':address' => $address,
                ':cnic' => $cnic,
                ':marital_status' => $marital_status,
                ':emergency_contact' => $emergency_contact,
                ':emergency_relation' => $emergency_relation,
                ':designation' => $designation,
                ':line_manager' => $line_manager,
                ':department' => $department,
                ':sub_department' => $sub_department,
                ':bank_name' => $bank_name,
                ':account_title' => $account_title,
                ':account_number' => $account_number,
                ':bank_branch' => $bank_branch,
                ':salary' => $salary,
                ':joining_date' => $joining_date,
                ':qualification_institution' => $qualification_institution,
                ':education_from_date' => $education_from_date,
                ':education_to_date' => $education_to_date,
                ':education_percentage' => $education_percentage,
                ':specialization' => $specialization,
                ':last_organization' => $last_organization,
                ':last_designation' => $last_designation,
                ':experience_from_date' => $experience_from_date,
                ':experience_to_date' => $experience_to_date,
                ':shift_id' => $shift_id,
                ':job_type' => $job_type,
                ':account_type' => $account_type,
                ':emp_id' => $emp_id
            ];
            
            // Add hashed password if provided
            if ($updatePassword) {
                $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            
            $stmt->execute($params);
            
            // Handle file uploads for edit employee
            $upload_dir = '../../../uploads/joining_documents/';
            $cv_attachment_path = null;
            $other_documents_path = null;
            
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
                        $cv_attachment_path = 'uploads/joining_documents/' . $cv_filename;
                        
                    }
                }
            }
            
            // Handle ID Card upload
            if (isset($_FILES['id_card_attachment']) && $_FILES['id_card_attachment']['error'] == 0) {
                $id_card_file = $_FILES['id_card_attachment'];
                $id_card_extension = strtolower(pathinfo($id_card_file['name'], PATHINFO_EXTENSION));
                $allowed_id_card_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
                
                if (in_array($id_card_extension, $allowed_id_card_extensions)) {
                    $id_card_filename = 'id_card_' . time() . '_' . $first_name . '.' . $id_card_extension;
                    $id_card_path = $upload_dir . $id_card_filename;
                    
                    if (move_uploaded_file($id_card_file['tmp_name'], $id_card_path)) {
                        $id_card_attachment_path = 'uploads/joining_documents/' . $id_card_filename;
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
                                $other_docs[] = 'uploads/joining_documents/' . $other_filename;
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
                                $other_docs[] = 'uploads/joining_documents/' . $other_filename;
                            }
                        }
                    }
                }
                
                if (!empty($other_docs)) {
                    $other_documents_path = json_encode($other_docs);
                    
                }
            }
            
            // Update employees table with file paths if new files uploaded
            if ($cv_attachment_path || $other_documents_path) {
                try {
                    $update_sql = "UPDATE employees SET ";
                    $update_params = [];
                    
                    if ($cv_attachment_path) {
                        $update_sql .= "cv_attachment = :cv_attachment";
                        $update_params[':cv_attachment'] = $cv_attachment_path;
                    }
                    
                    if (isset($id_card_attachment_path) && $id_card_attachment_path) {
                        if ($cv_attachment_path) {
                            $update_sql .= ", ";
                        }
                        $update_sql .= "id_card_attachment = :id_card_attachment";
                        $update_params[':id_card_attachment'] = $id_card_attachment_path;
                    }
                    
                    if ($other_documents_path) {
                        if ($cv_attachment_path || (isset($id_card_attachment_path) && $id_card_attachment_path)) {
                            $update_sql .= ", ";
                        }
                        $update_sql .= "other_documents = :other_documents";
                        $update_params[':other_documents'] = $other_documents_path;
                    }
                    
                    $update_sql .= " WHERE emp_id = :emp_id";
                    $update_params[':emp_id'] = $emp_id;
                    
                    $update_file_stmt = $pdo->prepare($update_sql);
                    $update_file_stmt->execute($update_params);
                } catch (Exception $e) {
                    error_log("Failed to update employees table with file paths: " . $e->getMessage());
                }
            }
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // Add new employee
    $first_name = $data['first_name'] ?? '';
    $middle_name = $data['middle_name'] ?? '';
    $last_name = $data['last_name'] ?? '';
    $gender = $data['gender'] ?? '';
    $date_of_birth = $data['date_of_birth'] ?? '';
    $phone = $data['phone'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $address = $data['address'] ?? '';
    $cnic = $data['cnic'] ?? '';
    $religion = $data['religion'] ?? '';
    $marital_status = $data['marital_status'] ?? '';
    $emergency_contact = $data['emergency_contact'] ?? '';
    $emergency_relation = $data['emergency_relation'] ?? '';
    $designation = $data['position'] ?? '';
    $line_manager = $data['line_manager'] ?? '';
    $department = $data['department_id'] ?? '';
    $sub_department = $data['sub_department'] ?? '';
    $bank_name = $data['bank_name'] ?? '';
    $account_title = $data['account_title'] ?? '';
    $account_number = $data['account_number'] ?? '';
    $account_type = $data['account_type'] ?? '';
    $bank_branch = $data['bank_branch'] ?? '';
    $salary = $data['salary'] ?? '';
    $joining_date = $data['joining_date'] ?? '';
    $qualification_institution = $data['qualification_institution'] ?? '';
    $education_from_date = $data['education_from_date'] ?? '';
    $education_to_date = $data['education_to_date'] ?? '';
    $education_percentage = $data['education_percentage'] ?? '';
    $specialization = $data['specialization'] ?? '';
    $last_organization = $data['last_organization'] ?? '';
    $last_designation = $data['last_designation'] ?? '';
    $experience_from_date = $data['experience_from_date'] ?? '';
    $experience_to_date = $data['experience_to_date'] ?? '';
    $shift_id = $data['shift_id'] ?? null;
    $job_type = $data['job_type'] ?? '';


    // Set default gender if empty
    if (empty($gender)) {
        $gender = 'Male'; // Default value
    }
    
    // Email validation for new employee
    $email = trim($email);
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }
    
    // Check if email already exists
    $email_check_sql = "SELECT emp_id FROM employees WHERE email = ? LIMIT 1";
    $email_check_stmt = $pdo->prepare($email_check_sql);
    $email_check_stmt->execute([$email]);
    if ($email_check_stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'This email is already registered. Please use a different email address.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO employees (
            first_name, middle_name, last_name, gender, date_of_birth, phone, email, password, address, cnic, religion, marital_status, emergency_contact, emergency_relation, designation, line_manager, department_id, sub_department, bank_name, account_title, account_number, account_type, bank_branch, salary, joining_date, qualification_institution, education_from_date, education_to_date, education_percentage, specialization, last_organization, last_designation, experience_from_date, experience_to_date, shift_id, job_type, cv_attachment, id_card_attachment, other_documents, status
        ) VALUES (
            :first_name, :middle_name, :last_name, :gender, :date_of_birth, :phone, :email, :password, :address, :cnic, :religion, :marital_status, :emergency_contact, :emergency_relation, :designation, :line_manager, :department, :sub_department, :bank_name, :account_title, :account_number, :account_type, :bank_branch, :salary, :joining_date, :qualification_institution, :education_from_date, :education_to_date, :education_percentage, :specialization, :last_organization, :last_designation, :experience_from_date, :experience_to_date, :shift_id, :job_type, :cv_attachment, :id_card_attachment, :other_documents, 'active'
        )");
        $stmt->execute([
            ':first_name' => $first_name,
            ':middle_name' => $middle_name,
            ':last_name' => $last_name,
            ':gender' => $gender,
            ':date_of_birth' => $date_of_birth,
            ':phone' => $phone,
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':address' => $address,
            ':cnic' => $cnic,
            ':religion' => $religion,
            ':marital_status' => $marital_status,
            ':emergency_contact' => $emergency_contact,
            ':emergency_relation' => $emergency_relation,
            ':designation' => $designation,
            ':line_manager' => $line_manager,
            ':department' => $department,
            ':sub_department' => $sub_department,
            ':bank_name' => $bank_name,
            ':account_title' => $account_title,
            ':account_number' => $account_number,
            ':account_type' => $account_type,
            ':bank_branch' => $bank_branch,
            ':salary' => $salary,
            ':joining_date' => $joining_date,
            ':qualification_institution' => $qualification_institution,
            ':education_from_date' => $education_from_date,
            ':education_to_date' => $education_to_date,
            ':education_percentage' => $education_percentage,
            ':specialization' => $specialization,
            ':last_organization' => $last_organization,
            ':last_designation' => $last_designation,
            ':experience_from_date' => $experience_from_date,
            ':experience_to_date' => $experience_to_date,
            ':shift_id' => $shift_id,
            ':job_type' => $job_type,
            ':cv_attachment' => $cv_attachment_path ?? null,
            ':id_card_attachment' => $id_card_attachment_path ?? null,
            ':other_documents' => $other_documents_path ?? null
        ]);
        
        // Get the inserted employee ID
        $emp_id = $pdo->lastInsertId();
        
        // Handle file uploads
        $uploaded_files = [];
        $upload_dir = '../../../uploads/joining_documents/';
        $cv_attachment_path = null;
        $other_documents_path = null;
        
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
                    $cv_attachment_path = 'uploads/joining_documents/' . $cv_filename; // Store for employees table
                }
            }
        }
        
        // Handle ID Card upload
        if (isset($_FILES['id_card_attachment']) && $_FILES['id_card_attachment']['error'] == 0) {
            $id_card_file = $_FILES['id_card_attachment'];
            $id_card_extension = strtolower(pathinfo($id_card_file['name'], PATHINFO_EXTENSION));
            $allowed_id_card_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
            
            if (in_array($id_card_extension, $allowed_id_card_extensions)) {
                $id_card_filename = 'id_card_' . time() . '_' . $first_name . '.' . $id_card_extension;
                $id_card_path = $upload_dir . $id_card_filename;
                
                if (move_uploaded_file($id_card_file['tmp_name'], $id_card_path)) {
                    $uploaded_files['id_card'] = $id_card_path;
                    $id_card_attachment_path = 'uploads/joining_documents/' . $id_card_filename; // Store for employees table
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
                                $other_docs[] = 'uploads/joining_documents/' . $other_filename;
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
                            $other_docs[] = 'uploads/joining_documents/' . $other_filename;
                        }
                    }
                }
            }
            
            if (!empty($other_docs)) {
                $uploaded_files['other_documents'] = $other_docs;
                $other_documents_path = json_encode($other_docs); // Store for employees table
                error_log("Other documents JSON: " . $other_documents_path);
            }
        }
        
        // Update employees table with file paths
        if ($cv_attachment_path || $other_documents_path) {
            try {
                $update_sql = "UPDATE employees SET ";
                $update_params = [];
                
                if ($cv_attachment_path) {
                    $update_sql .= "cv_attachment = :cv_attachment";
                    $update_params[':cv_attachment'] = $cv_attachment_path;
                }
                
                if (isset($id_card_attachment_path) && $id_card_attachment_path) {
                    if ($cv_attachment_path) {
                        $update_sql .= ", ";
                    }
                    $update_sql .= "id_card_attachment = :id_card_attachment";
                    $update_params[':id_card_attachment'] = $id_card_attachment_path;
                }
                
                if ($other_documents_path) {
                    if ($cv_attachment_path || (isset($id_card_attachment_path) && $id_card_attachment_path)) {
                        $update_sql .= ", ";
                    }
                    $update_sql .= "other_documents = :other_documents";
                    $update_params[':other_documents'] = $other_documents_path;
                }
                
                $update_sql .= " WHERE emp_id = :emp_id";
                $update_params[':emp_id'] = $emp_id;
                
                    $update_stmt = $pdo->prepare($update_sql);
                    $update_stmt->execute($update_params);
                    error_log("Update SQL: " . $update_sql);
                    error_log("Update Params: " . print_r($update_params, true));
            } catch (Exception $e) {
                error_log("Failed to update employees table with file paths: " . $e->getMessage());
            }
        }
        
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// GET request handling
try {
    $where = "WHERE (e.is_deleted = 0 OR e.is_deleted IS NULL) AND (e.status = 'active' OR e.status IS NULL) AND (e.role IS NULL OR e.role != 'admin')";
    if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
        $where = "WHERE e.is_deleted = 1 AND (e.role IS NULL OR e.role != 'admin')";
    }

    $sql = "SELECT 
        e.emp_id,
        e.first_name,
        e.middle_name,
        e.last_name,
        e.gender,
        e.date_of_birth,
        e.phone,
        e.email,
        e.address,
        e.cnic,
        e.religion,
        e.marital_status,
        e.emergency_contact,
        e.emergency_relation,
        e.designation as position,
        e.line_manager,
        e.department_id,
        d.dept_name as department,
        e.sub_department,
        e.bank_name,
        e.account_title,
        e.account_number,
        e.account_type,
        e.bank_branch,
        e.salary,
        e.joining_date,
        e.qualification_institution,
        e.education_from_date,
        e.education_to_date,
        e.education_percentage,
        e.specialization,
        e.last_organization,
        e.last_designation,
        e.experience_from_date,
        e.experience_to_date,
        e.job_type,
        e.created_at,
        e.updated_at,
        e.profile_img,
        e.cv_attachment,
        e.other_documents,
        e.id_card_attachment,
        e.shift_id,
        s.shift_name,
        s.start_time as shift_start_time,
        s.end_time as shift_end_time
    FROM employees e
    LEFT JOIN shifts s ON e.shift_id = s.id
    LEFT JOIN departments d ON e.department_id = d.dept_id
    $where
    ORDER BY e.emp_id DESC";

    $result = $pdo->query($sql);
    $employees = $result->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($employees);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database query failed', 'details' => $e->getMessage()]);
}
?>