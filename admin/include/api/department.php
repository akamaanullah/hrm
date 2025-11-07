<?php
require_once '../../../config.php';
header('Content-Type: application/json');

// Helper function to check if department exists
function departmentExists($pdo, $dept_name) {
    $check_stmt = $pdo->prepare("SELECT dept_id FROM departments WHERE LOWER(dept_name) = LOWER(?)");
    if (!$check_stmt) {
        throw new Exception("Prepare check failed: " . implode(', ', $pdo->errorInfo()));
    }
    
    $check_stmt->execute([$dept_name]);
    return $check_stmt->rowCount() > 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Add new department
    $dept_name = trim($input['dept_name'] ?? '');
    $manager = $input['manager'] ?? null;
    $dep_head = $input['dep_head'] ?? null;
    $status = $input['status'] ?? 'active';
    
    // Convert empty strings to null for foreign keys
    if ($manager === '' || $manager === 'null') {
        $manager = null;
    }
    if ($dep_head === '' || $dep_head === 'null') {
        $dep_head = null;
    }


    if (empty($dept_name)) {
        echo json_encode(['success' => false, 'message' => 'Department name is required']);
        exit;
    }

    // Manager and Head are optional - can be assigned later

    try {
        // Check if department exists
        if (departmentExists($pdo, $dept_name)) {
            echo json_encode(['success' => false, 'message' => 'This department name already exists']);
            exit;
        }

        // Insert new department
        $stmt = $pdo->prepare("INSERT INTO departments (dept_name, manager, dep_head, status) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . implode(', ', $pdo->errorInfo()));
        }

        if ($stmt->execute([$dept_name, $manager, $dep_head, $status])) {
            $dept_id = $pdo->lastInsertId();
            
            // Update employees table to assign department to manager and head
            if (!empty($manager)) {
                $update_manager = $pdo->prepare("UPDATE employees SET department = ? WHERE emp_id = ?");
                $update_manager->execute([$dept_id, $manager]);
            }
            
            if (!empty($dep_head)) {
                $update_head = $pdo->prepare("UPDATE employees SET department = ? WHERE emp_id = ?");
                $update_head->execute([$dept_id, $dep_head]);
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Department added successfully',
                'data' => [
                    'dept_id' => $dept_id,
                    'dept_name' => $dept_name,
                    'manager' => $manager,
                    'dep_head' => $dep_head,
                    'status' => $status
                ]
            ]);
            
            // Log the successful insert
            error_log("Department added successfully: {$dept_name} (ID: {$dept_id})");
        } else {
            throw new Exception("Insert failed: " . implode(', ', $stmt->errorInfo()));
        }
    } catch (Exception $e) {
        error_log("Error adding department: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Error adding department: ' . $e->getMessage()
        ]);
    }
    exit;
}

// GET request - fetch all departments or employees for dropdown
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if requesting employees list for department head dropdown
    if (isset($_GET['action']) && $_GET['action'] === 'employees') {
        try {
            // Check if requesting management employees only (for Department Manager dropdown)
            if (isset($_GET['type']) && $_GET['type'] === 'management') {
                $sql = "SELECT e.emp_id, e.first_name, e.middle_name, e.last_name, e.designation 
                        FROM employees e 
                        INNER JOIN departments d ON e.department_id = d.dept_id 
                        WHERE d.dept_name = 'Management' 
                        AND e.status = 'active' 
                        AND (e.role IS NULL OR e.role != 'admin') 
                        AND (e.is_deleted = 0 OR e.is_deleted IS NULL)
                        AND d.status = 'active'
                        ORDER BY e.first_name ASC";
            } else {
                // For Department Head dropdown - show all employees except admin and Management department
                $sql = "SELECT e.emp_id, e.first_name, e.middle_name, e.last_name, e.designation 
                        FROM employees e 
                        LEFT JOIN departments d ON e.department_id = d.dept_id 
                        WHERE e.status = 'active' 
                        AND (e.role IS NULL OR e.role != 'admin') 
                        AND (e.is_deleted = 0 OR e.is_deleted IS NULL)
                        AND (d.dept_name != 'Management' OR d.dept_name IS NULL)
                        ORDER BY e.first_name ASC";
            }
            $stmt = $pdo->query($sql);
            
            if (!$stmt) {
                throw new Exception("Query failed: " . implode(', ', $pdo->errorInfo()));
            }
            
            $employees = [];
            while ($row = $stmt->fetch()) {
                $employees[] = [
                    'emp_id' => $row['emp_id'],
                    'name' => trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']),
                    'designation' => $row['designation']
                ];
            }
            
            // Debug: Log employees being returned
            error_log("Department dropdown employees: " . json_encode($employees));
            
            echo json_encode(['success' => true, 'data' => $employees]);
        } catch (Exception $e) {
            error_log("Error fetching employees: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Error fetching employees: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    try {
        $sql = "SELECT d.dept_id, d.dept_name, d.manager, d.dep_head, d.status, d.created_at, d.updated_at, 
                e.first_name, e.middle_name, e.last_name,
                m.first_name as manager_first_name, m.middle_name as manager_middle_name, m.last_name as manager_last_name
                FROM departments d 
                LEFT JOIN employees e ON d.dep_head = e.emp_id AND e.status = 'active' AND (e.is_deleted = 0 OR e.is_deleted IS NULL)
                LEFT JOIN employees m ON d.manager = m.emp_id AND m.status = 'active' AND (m.is_deleted = 0 OR m.is_deleted IS NULL)
                ORDER BY d.created_at DESC";
        $stmt = $pdo->query($sql);
        
        if (!$stmt) {
            throw new Exception("Query failed: " . implode(', ', $pdo->errorInfo()));
        }
        
        $departments = [];
        while ($row = $stmt->fetch()) {
            $departments[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $departments]);
    } catch (Exception $e) {
        error_log("Error fetching departments: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Error fetching departments: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Update department
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $dept_id = $data['dept_id'];
        $dept_name = trim($data['dept_name']);
        $manager = $data['manager'] ?? null;
        $dep_head = $data['dep_head'] ?? null;
        $status = $data['status'] ?? null;
        
        // Convert empty strings to null for foreign keys
        if ($manager === '' || $manager === 'null') {
            $manager = null;
        }
        if ($dep_head === '' || $dep_head === 'null') {
            $dep_head = null;
        }

        // Check if new name exists for other departments
        $check_stmt = $pdo->prepare("SELECT dept_id FROM departments WHERE LOWER(dept_name) = LOWER(?) AND dept_id != ?");
        $check_stmt->execute([$dept_name, $dept_id]);
        
        if ($check_stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'This department name already exists']);
            exit;
        }

        // Build update query based on available fields
        $update_fields = ["dept_name = ?", "manager = ?", "dep_head = ?"]; 
        $types = "sss";
        $params = [$dept_name, $manager, $dep_head];

        if ($status !== null) {
            $update_fields[] = "status = ?";
            $types .= "s";
            $params[] = $status;
        }

        $sql = "UPDATE departments SET " . implode(", ", $update_fields) . " WHERE dept_id = ?";
        $types .= "i";
        $params[] = $dept_id;

        $stmt = $pdo->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . implode(', ', $pdo->errorInfo()));
        }

        if ($stmt->execute($params)) {
            // Update employees table to assign department to manager and head
            if (!empty($manager)) {
                $update_manager = $pdo->prepare("UPDATE employees SET department = ? WHERE emp_id = ?");
                $update_manager->execute([$dept_id, $manager]);
            }
            
            if (!empty($dep_head)) {
                $update_head = $pdo->prepare("UPDATE employees SET department = ? WHERE emp_id = ?");
                $update_head->execute([$dept_id, $dep_head]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Department updated successfully']);
        } else {
            throw new Exception("Update failed: " . implode(', ', $stmt->errorInfo()));
        }
    } catch (Exception $e) {
        error_log("Error updating department: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error updating department: ' . $e->getMessage()]);
    }
    exit;
}

// Delete department
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $dept_id = $_GET['dept_id'] ?? null;
        
        if (!$dept_id) {
            throw new Exception("Department ID is required");
        }

        // First check if department is being used by any employees
        $check_sql = "SELECT COUNT(*) as count FROM employees WHERE department_id = ?";
        $check_stmt = $pdo->prepare($check_sql);
        if (!$check_stmt) {
            throw new Exception("Prepare check failed: " . implode(', ', $pdo->errorInfo()));
        }

        $check_stmt->execute([$dept_id]);
        $row = $check_stmt->fetch();

        if ($row['count'] > 0) {
            throw new Exception("Cannot delete department as it is assigned to " . $row['count'] . " employee(s)");
        }

        // If no employees are using this department, proceed with deletion
        $sql = "DELETE FROM departments WHERE dept_id = ?";
        $stmt = $pdo->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . implode(', ', $pdo->errorInfo()));
        }

        if ($stmt->execute([$dept_id])) {
            echo json_encode(['success' => true, 'message' => 'Department deleted successfully']);
        } else {
            throw new Exception("Delete failed: " . implode(', ', $stmt->errorInfo()));
        }
    } catch (Exception $e) {
        error_log("Error deleting department: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>