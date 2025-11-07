<?php include "header.php" ?>

<?php include "top-bar.php" ?>

<?php include "sidebar.php" ?>

<style>
  .section-header {
    background: linear-gradient(135deg, #029480 0%, #05d9bc 100%);
    padding: 12px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(102, 234, 201, 0.3);
    margin-bottom: 20px;
    border-left: 4px solid #4CAF50;
  }

  .section-header h6 {
    font-weight: 600;
    margin: 0;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  }

  .form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 8px;
  }

  .form-control,
  .form-select {
    border: 2px solid #e9ecef;
    border-radius: 6px;
    padding: 10px 12px;
    transition: all 0.3s ease;
  }

  .form-control:focus,
  .form-select:focus {
    border-color: #005f52;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
  }

  .btn-primary {
    background: linear-gradient(135deg, #005f52 0%, #05d9bc 100%);
    border: none;
    border-radius: 8px;
    padding: 12px 30px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 234, 201, 0.3);
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 234, 201, 0.4);
  }

  .modal-content {
    border-radius: 15px;
    backdrop-filter: blur(10px);
    /* background: rgba(255, 255, 255, 0.98); */
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  }

  .modal-header {
    background: linear-gradient(135deg, #00bfa5 0%, #02d6ba 100%);
    color: white;
    border-radius: 15px 15px 0 0;
    border: none;
    padding: 1.5rem;
  }

  .modal-title {
    font-weight: 700;
    font-family: "Poppins", sans-serif;
    font-size: 1.5rem;
  }

  .btn-close {
    filter: invert(1);
  }

  .modal-body {
    padding: 2rem;
    font-family: "Poppins", sans-serif;
  }

  .modal-body .form-label {
    color: #2c3e50;
    margin-bottom: 8px;
    font-size: 0.9rem;
    font-family: "Poppins", sans-serif;
    letter-spacing: 0.5px;
    font-weight: 500;
  }

  .modal-body .form-control,
  .modal-body .form-select {
    border-radius: 8px;
    height: 45px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
    font-size: 0.95rem;
    font-family: "Poppins", sans-serif;
    padding: 10px 15px;
    margin-bottom: 0.5rem;
  }

  .modal-body .form-control:focus,
  .modal-body .form-select:focus {
    border-color: #00bfa5;
    box-shadow: 0 0 0 0.2rem rgb(0 191 165 / 14%);
    background: #fff;
    transform: translateY(-2px);
  }

  .modal-body .form-control:hover,
  .modal-body .form-select:hover {
    border-color: #00bfa5;
    background: #fff;
  }

  .modal-body .btn-primary {
    background: linear-gradient(135deg, #00bfa5 0%, #02d6ba 100%);
    border: none;
    border-radius: 8px;
    font-weight: 600;
    padding: 12px 30px;
    font-size: 1rem;
    font-family: "Poppins", sans-serif;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    color: white;
  }

  .modal-body .btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
  }

  .modal-body .btn-primary:hover::before {
    left: 100%;
  }

  .modal-body .btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0px 3px 14px 0px rgb(0 191 165 / 52%);
    background: linear-gradient(135deg, #02d6ba 0%, #00bfa5 100%);
    color: white;
  }

  .modal-body .btn-primary:active {
    transform: translateY(-1px);
  }

  .modal-body h5 {
    font-family: "Poppins", sans-serif;
    font-weight: 700;
  }

  /* .modal-body .d-flex.align-items-center {
    margin-bottom: 1.5rem;
  } */

  .modal-body .d-flex.align-items-center span {
    /* background: linear-gradient(135deg, #00bfa5, #02d6ba); */
    /* width: 32px; */
    border-radius: 8px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
  }

  .modal-body .d-flex.align-items-center span i {
    color: white;
    font-size: 16px;
  }

  /* Modern Employee Card Styles */
  .employee-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border: 1px solid #e5e7eb;
    overflow: hidden;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    height: 90%;
  }

  .employee-card:hover {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border-color: #00bfa5;
  }

  .employee-card-header {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 1.2rem 1.5rem;
    border-radius: 16px;
    text-align: center;
    position: relative;
    min-height: 140px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .employee-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00bfa5 0%, #02d6ba 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    position: relative;
    z-index: 1;
  }

  .employee-avatar i {
    font-size: 2.5rem;
    color: white;
  }

  .employee-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
    line-height: 1.2;
  }

  .employee-position {
    font-size: 0.9rem;
    color: #6b7280;
    font-weight: 500;
    margin-bottom: 0;
  }

  .employee-card-body {
    padding: 1.5rem;
  }

  .employee-info {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
  }

  .employee-info:last-child {
    margin-bottom: 0;
  }

  .employee-info i {
    width: 20px;
    color: #00bfa5;
    margin-right: 0.75rem;
    font-size: 0.9rem;
  }

  .employee-info span {
    color: #374151;
    font-weight: 500;
  }

  .employee-actions {
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
  }

  .view-profile-btn {
    width: 100%;
    background: linear-gradient(135deg, #00bfa5 0%, #02d6ba 100%);
    border: none;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
  }

  .view-profile-btn:hover {
    background: linear-gradient(135deg, #02d6ba 0%, #00bfa5 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0, 191, 165, 0.3);
    color: white;
  }

  .delete-employee-btn {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border: none;
    border-radius: 8px;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
  }

  .delete-employee-btn:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(239, 68, 68, 0.3);
    color: white;
  }

  .status-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #fef3c7;
    color: #d97706;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  /* Empty State */
  .no-employees-container {
    background: white;
    border-radius: 16px;
    padding: 4rem 2rem;
    text-align: center;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border: 2px dashed #e5e7eb;
  }

  .no-employees-icon {
    font-size: 4rem;
    color: #00bfa5;
    margin-bottom: 1.5rem;
    opacity: 0.7;
  }

  .no-employees-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
  }

  .no-employees-subtitle {
    color: #6b7280;
    font-size: 1rem;
    font-weight: 500;
  }
</style>

<?php
require_once "../config.php";
// New joining employees fetch karo (both inactive and active with incomplete profiles, excluding admin users)
$inactive_employees = [];
$sql = "SELECT *, created_at FROM employees WHERE status = 'inactive' AND (role = 'user' OR role IS NULL) ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
if ($stmt && $stmt->rowCount() > 0) {
  while ($row = $stmt->fetch()) {
    $inactive_employees[] = $row;
  }
}
// Departments fetch karo
$departments = [];
$deptResult = $pdo->query("SELECT dept_id, dept_name FROM departments WHERE status = 'active'");
if ($deptResult && $deptResult->rowCount() > 0) {
  while ($row = $deptResult->fetch()) {
    $departments[] = $row;
  }
}
// Shifts fetch karo
$shifts = [];
$shiftResult = $pdo->query("SELECT id, shift_name, start_time, end_time FROM shifts");
if ($shiftResult && $shiftResult->rowCount() > 0) {
  while ($row = $shiftResult->fetch()) {
    $shifts[] = $row;
  }
}
?>

<div class="main-content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">New Joining Employees</h1>
    <div id="exportButtonContainer"></div>
  </div>
  <div class="row g-4">
    <?php foreach ($inactive_employees as $emp): ?>
      <?php
      // Gender ke hisaab se icon set karo
      $iconClass = 'fas fa-user-circle'; // Default
      if (strtolower($emp['gender']) == 'female') {
        $iconClass = 'fas fa-female';
      } else if (strtolower($emp['gender']) == 'male') {
        $iconClass = 'fas fa-male';
      }
      $name = htmlspecialchars(trim(($emp['first_name'] ?? '') . ' ' . ($emp['middle_name'] ?? '') . ' ' . ($emp['last_name'] ?? '')));
      $position = htmlspecialchars($emp['position'] ?? '');
      $phone = htmlspecialchars($emp['phone'] ?? '');
      $email = htmlspecialchars($emp['email'] ?? '');
      ?>
      <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="employee-card" data-emp='<?php echo json_encode($emp); ?>'>
          <div class="status-badge">Inactive</div>

          <div class="employee-card-header">
            <div class="employee-avatar">
              <i class="<?php echo $iconClass; ?>"></i>
            </div>
            <div class="employee-name"><?php echo $name; ?></div>
            <div class="employee-position"><?php echo $position ?: 'Job Title not set'; ?></div>
          </div>

          <div class="employee-card-body">
            <?php if ($phone): ?>
              <div class="employee-info">
                <i class="fas fa-phone"></i>
                <span><?php echo $phone; ?></span>
              </div>
            <?php endif; ?>

            <?php if ($email): ?>
              <div class="employee-info">
                <i class="fas fa-envelope"></i>
                <span><?php echo $email; ?></span>
              </div>
            <?php endif; ?>

            <?php if ($emp['department'] || $emp['department_id']): ?>
              <div class="employee-info">
                <i class="fas fa-building"></i>
                <span>
                  <?php
                  $deptName = 'Unknown';
                  $deptId = $emp['department'] ?: $emp['department_id'];
                  foreach ($departments as $dept) {
                    if ($dept['dept_id'] == $deptId) {
                      $deptName = htmlspecialchars($dept['dept_name']);
                      break;
                    }
                  }
                  echo $deptName;
                  ?>
                </span>
              </div>
            <?php endif; ?>

            <?php if ($emp['joining_date']): ?>
              <div class="employee-info">
                <i class="fas fa-calendar-alt"></i>
                <span>Joining: <?php echo date('M d, Y', strtotime($emp['joining_date'])); ?></span>
              </div>
            <?php endif; ?>

            <?php if ($emp['created_at']): ?>
              <div class="employee-info">
                <i class="fas fa-clock"></i>
                <span>Created: <?php echo date('M d, Y', strtotime($emp['created_at'])); ?></span>
              </div>
            <?php endif; ?>

            <div class="employee-actions d-flex gap-2">
              <button class="view-profile-btn flex-grow-1">
                <i class="fas fa-eye"></i>
                View 
              </button>
              <button class="delete-employee-btn" data-emp-id="<?php echo $emp['emp_id']; ?>" data-emp-name="<?php echo $name; ?>" title="Delete Employee">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if (empty($inactive_employees)): ?>
      <div class="col-12">
        <div class="no-employees-container">
          <div class="no-employees-icon">
            <i class="fas fa-user-plus"></i>
          </div>
          <div class="no-employees-title">No New Joinings Found!</div>
          <div class="no-employees-subtitle">
            There are currently no records of any new employees joining the organization.
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editJoiningModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Complete Joining Form</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editJoiningForm">
          <input type="hidden" name="emp_id" id="edit_emp_id">

          <!-- Personal Information Section -->
          <div class="d-flex align-items-center mb-3 mt-2">
            <span style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; margin-right: 10px; background: linear-gradient(135deg, #00bfa5, #02d6ba); border-radius: 8px;">
              <i class="fas fa-user" style="color: white; font-size: 16px;"></i>
            </span>
            <h5 class="mb-0 fw-bold text-dark">Personal Information</h5>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">First Name <span class="text-danger">*</span></label>
              <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Middle Name</label>
              <input type="text" name="middle_name" id="edit_middle_name" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Last Name <span class="text-danger">*</span></label>
              <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Gender</label>
              <select name="gender" id="edit_gender" class="form-select" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Date of Birth</label>
              <input type="date" name="date_of_birth" id="edit_date_of_birth" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">ID card Number</label>
              <input type="text" name="cnic" id="edit_cnic" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Emergency Contact</label>
              <input type="text" name="emergency_contact" id="edit_emergency_contact" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Emergency Contact Relation</label>
              <input type="text" name="emergency_relation" id="edit_emergency_relation" class="form-control" placeholder="Father, Mother, Brother, etc." required>
            </div>
          </div>

          <!-- Contact Information Section -->
          <div class="d-flex align-items-center mb-3 mt-4">
            <span style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; margin-right: 10px; background: linear-gradient(135deg, #00bfa5, #02d6ba); border-radius: 8px;">
              <i class="fas fa-phone-alt" style="color: white; font-size: 16px;"></i>
            </span>
            <h5 class="mb-0 fw-bold text-dark">Contact Information</h5>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" id="edit_phone" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" name="email" id="edit_email" class="form-control" required>
              <small class="text-muted" id="emailFeedback"></small>
            </div>
            <div class="col-md-12 mb-3">
              <label class="form-label">Address</label>
              <input type="text" name="address" id="edit_address" class="form-control" required>
            </div>
          </div>

          <!-- Employment Information Section -->
          <div class="d-flex align-items-center mb-3 mt-4">
            <span style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; margin-right: 10px; background: linear-gradient(135deg, #00bfa5, #02d6ba); border-radius: 8px;">
              <i class="fas fa-briefcase" style="color: white; font-size: 16px;"></i>
            </span>
            <h5 class="mb-0 fw-bold text-dark">Employment Information</h5>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Job Title</label>
              <input type="text" name="position" id="edit_position" class="form-control" required>
            </div>
            <!-- <div class="col-md-6 mb-3">
              <label class="form-label">Reporting</label>
              <input type="text" name="line_manager" id="edit_line_manager" class="form-control" required>
            </div> -->
            <!-- <div class="col-md-6 mb-3">
              <label class="form-label">Sub Department</label>
              <input type="text" name="sub_department" id="edit_sub_department" class="form-control" required>
            </div> -->
            <!-- <div class="col-md-6 mb-3">
              <label class="form-label">Shift Timing</label>
              <select name="shift_id" id="edit_shift_id" class="form-select" required>
                <option value="">Select Shift</option>
                <?php
                foreach ($shifts as $shift):
                  $start = date("g:i A", strtotime($shift['start_time']));
                  $end = date("g:i A", strtotime($shift['end_time']));
                ?>
                  <option value="<?php echo $shift['id']; ?>">
                    <?php echo htmlspecialchars($shift['shift_name']) . " ($start - $end)"; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div> -->
            <!-- <div class="col-md-6 mb-3">
              <label class="form-label">Job Type</label>
              <select name="job_type" id="edit_job_type" class="form-select" required>
                <option value="">Select Job Type</option>
                <option value="Internship">Internship</option>
                <option value="Probation">Probation</option>
                <option value="Permanent">Permanent</option>
              </select>
            </div> -->
            
            <!-- <div class="col-md-6 mb-3">
              <label class="form-label">Salary</label>
              <input type="number" name="salary" id="edit_salary" class="form-control" required>
            </div> -->
          </div>

          <!-- Education Information Section -->
          <div class="d-flex align-items-center mb-3 mt-4">  
            <span style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; margin-right: 10px; background: linear-gradient(135deg, #00bfa5, #02d6ba); border-radius: 8px;">
              <i class="fas fa-graduation-cap" style="color: white; font-size: 16px;"></i>
            </span>
            <h5 class="mb-0 fw-bold text-dark">Education Information</h5>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Qualification</label>
              <input type="text" name="qualification_institution" id="edit_qualification_institution" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label"> Professional Expertise</label>
              <input type="text" name="specialization" id="edit_specialization" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Degree / Certification</label>
              <input type="text" name="education_percentage" id="edit_education_percentage" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">College / University</label>
              <input type="text" name="marital_status" id="edit_marital_status" class="form-control" required>
            </div>
          </div>

          <!-- Experience Information Section -->
          <div class="d-flex align-items-center mb-3 mt-4">
            <span style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; margin-right: 10px; background: linear-gradient(135deg, #00bfa5, #02d6ba); border-radius: 8px;">
              <i class="fas fa-briefcase" style="color: white; font-size: 16px;"></i>
            </span>
            <h5 class="mb-0 fw-bold text-dark">Experience Information</h5>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Last Employer</label>
              <input type="text" name="last_organization" id="edit_last_organization" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Last Designation</label>
              <input type="text" name="last_designation" id="edit_last_designation" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Experience From Date</label>
              <input type="date" name="experience_from_date" id="edit_experience_from_date" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Experience To Date</label>
              <input type="date" name="experience_to_date" id="edit_experience_to_date" class="form-control">
            </div>
          </div>

          <!-- Banking Information Section -->
          <div class="d-flex align-items-center mb-3 mt-4">
            <span style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; margin-right: 10px; background: linear-gradient(135deg, #00bfa5, #02d6ba); border-radius: 8px;">
              <i class="fas fa-university" style="color: white; font-size: 16px;"></i>
            </span>
            <h5 class="mb-0 fw-bold text-dark">Banking Information</h5>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Bank Name</label>
              <select name="bank_name" id="edit_bank_name" class="form-select" required>
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
            <div class="col-md-6 mb-3">
              <label class="form-label">Account Type</label>
              <select name="account_type" id="edit_account_type" class="form-select" required>
                <option value="">Select Account Type</option>
                <option value="IBAN number">IBAN</option>
                <option value="IBFT number">IBFT</option>
                <option value="Mobile Banking">Mobile Banking</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Account Title</label>
              <input type="text" name="account_title" id="edit_account_title" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Account Number</label>
              <input type="text" name="account_number" id="edit_account_number" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Bank Branch</label>
              <input type="text" name="bank_branch" id="edit_bank_branch" class="form-control" required>
            </div>
          </div>

          <!-- System Access Section -->
          <div class="d-flex align-items-center mb-3 mt-4">
            <span style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; margin-right: 10px; background: linear-gradient(135deg, #00bfa5, #02d6ba); border-radius: 8px;">
              <i class="fas fa-lock" style="color: white; font-size: 16px;"></i>
            </span>
            <h5 class="mb-0 fw-bold text-dark">Administrative Access</h5>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Department</label>
              <select name="department_id" id="edit_department_id" class="form-select" required>
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                  <option value="<?php echo $dept['dept_id']; ?>"><?php echo htmlspecialchars($dept['dept_name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Job Type</label>
              <select name="job_type" id="edit_job_type" class="form-select" required>
                <option value="">Select Job Type</option>
                <option value="Internship">Internship</option>
                <option value="Probation">Probation</option>
                <option value="Permanent">Permanent</option>
              </select>
            </div>
            </div>
            <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Sub Department</label>
              <input type="text" name="sub_department" id="edit_sub_department" class="form-control" placeholder="Sub Department">
            </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Salary</label>
            <input type="number" name="salary" id="edit_salary" class="form-control" required>
          </div>
          </div>
        <div class="row">
          <div class="col-md-6 mb-3">
              <label class="form-label">Joining Date</label>
              <input type="date" name="joining_date" id="edit_joining_date" class="form-control" required>
            </div>
          <div class="col-md-6 mb-3">
              <label class="form-label">Shift Timing</label>
              <select name="shift_id" id="edit_shift_id" class="form-select" required>
                <option value="">Select Shift</option>
                <?php
                foreach ($shifts as $shift):
                  $start = date("g:i A", strtotime($shift['start_time']));
                  $end = date("g:i A", strtotime($shift['end_time']));
                ?>
                  <option value="<?php echo $shift['id']; ?>">
                    <?php echo htmlspecialchars($shift['shift_name']) . " ($start - $end)"; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            </div>
            <div class="row">
          <div class="col-md-6 mb-3">
              <label class="form-label">Password</label>
              <div style="position: relative;">
                <input type="password" name="password" id="edit_password" class="form-control" placeholder="Password" required style="padding-right: 40px;">
                <button type="button" onclick="const pwd = document.getElementById('edit_password'); const icon = this.querySelector('i'); if(pwd.type === 'password') { pwd.type = 'text'; icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); } else { pwd.type = 'password'; icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6c757d; cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
           
            </div>
          <!-- Document Attachments -->
          <div class="d-flex align-items-center mb-3 mt-4">
            <span style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; margin-right: 10px; background: linear-gradient(135deg, #00bfa5, #02d6ba); border-radius: 8px;">
              <i class="fas fa-paperclip" style="color: white; font-size: 16px;"></i>
            </span>
            <h5 class="mb-0 fw-bold text-dark">Document Attachments</h5>
          </div>
          <!-- Display existing uploaded documents -->
          <div class="mb-3">
            <h6 class="text-muted mb-2">Uploaded Documents:</h6>
            <div id="cv_attachment_display" class="mb-2"></div>
            <div id="id_card_attachment_display" class="mb-2"></div>
            <div id="other_documents_display" class="mb-2"></div>
          </div>

          <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary px-4">
              <i class="fas fa-save me-2"></i>Save Changes
            </button>
          </div>
        </form>
        <div id="editFormMsg" class="mt-3"></div>
      </div>
    </div>
  </div>
</div>

<?php include "footer.php" ?>

<script>
  // Delete employee functionality
  $(document).on('click', '.delete-employee-btn', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const empId = $(this).data('emp-id');
    const empName = $(this).data('emp-name');
    const button = $(this);
    
    // SweetAlert confirmation
    Swal.fire({
      title: 'Are you sure?',
      html: `Do you want to <strong>permanently delete</strong> this employee?<br><strong>${empName}</strong>`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#6b7280',
      confirmButtonText: '<i class="fas fa-trash me-2"></i>Yes, Delete Permanently!',
      cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
      customClass: {
        confirmButton: 'btn btn-danger',
        cancelButton: 'btn btn-secondary'
      }
    }).then((result) => {
      if (result.isConfirmed) {
        // Show loading state
        button.html('<i class="fas fa-spinner fa-spin"></i> Deleting...').prop('disabled', true);
        
        // AJAX request to delete employee
        $.ajax({
          url: 'include/api/employee.php',
          type: 'POST',
          data: JSON.stringify({
            action: 'permanent_delete',
            emp_id: empId
          }),
          contentType: 'application/json',
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              // Success message
              Swal.fire({
                title: 'Deleted!',
                text: 'Employee has been permanently deleted from the HRM',
                icon: 'success',
                confirmButtonColor: '#00bfa5',
                confirmButtonText: 'OK'
              }).then(() => {
                // Remove card from UI and reload page
                location.reload();
              });
            } else {
              // Error message
              Swal.fire({
                title: 'Error!',
                text: response.message || 'Failed to delete employee.',
                icon: 'error',
                confirmButtonColor: '#ef4444'
              });
              // Reset button
              button.html('<i class="fas fa-trash"></i> Delete Employee').prop('disabled', false);
            }
          },
          error: function(xhr, status, error) {
            Swal.fire({
              title: 'Server Error!',
              text: 'An error occurred while deleting the employee. Please try again.',
              icon: 'error',
              confirmButtonColor: '#ef4444'
            });
            // Reset button
            button.html('<i class="fas fa-trash"></i> Delete Employee').prop('disabled', false);
          }
        });
      }
    });
  });

  // Card click pe modal open aur data fill
  $(document).on('click', '.employee-card, .view-profile-btn', function(e) {
    e.preventDefault();
    e.stopPropagation();

    // Get the employee data from the card
    var card = $(this).closest('.employee-card');
    var emp = card.data('emp');

    // Fill the modal with employee data
    $('#edit_emp_id').val(emp.emp_id);
    $('#edit_first_name').val(emp.first_name);
    $('#edit_middle_name').val(emp.middle_name);
    $('#edit_last_name').val(emp.last_name);
    $('#edit_gender').val(emp.gender);
    $('#edit_date_of_birth').val(emp.date_of_birth);
    $('#edit_cnic').val(emp.cnic);
    $('#edit_marital_status').val(emp.marital_status);
    $('#edit_emergency_contact').val(emp.emergency_contact);
    $('#edit_emergency_relation').val(emp.emergency_relation);
    $('#edit_phone').val(emp.phone);
    $('#edit_email').val(emp.email);
    $('#edit_address').val(emp.address);
    $('#edit_position').val(emp.position);
    $('#edit_line_manager').val(emp.line_manager);
    $('#edit_department_id').val(emp.department || emp.department_id);
    $('#edit_sub_department').val(emp.sub_department);
    $('#edit_shift_id').val(emp.shift_id);
    $('#edit_job_type').val(emp.job_type);
    $('#edit_joining_date').val(emp.joining_date);
    $('#edit_salary').val(emp.salary);
    $('#edit_qualification_institution').val(emp.qualification_institution);
    $('#edit_specialization').val(emp.specialization);
    $('#edit_education_percentage').val(emp.education_percentage);
    $('#edit_last_organization').val(emp.last_organization);
    $('#edit_last_designation').val(emp.last_designation);
    $('#edit_experience_from_date').val(emp.experience_from_date);
    $('#edit_experience_to_date').val(emp.experience_to_date);
    $('#edit_bank_name').val(emp.bank_name);
    $('#edit_account_type').val(emp.account_type);
    $('#edit_account_title').val(emp.account_title);
    $('#edit_account_number').val(emp.account_number);
    $('#edit_bank_branch').val(emp.bank_branch);
    $('#edit_password').val('');
    $('#editFormMsg').html('');
    
    // Store original email for validation
    if (typeof window !== 'undefined') {
      window.originalEmail = emp.email || '';
    }
    
    // Reset email feedback
    $('#emailFeedback').text('').css('color', '');
    $('#edit_email').css('borderColor', '');

    // Set current employee data for document loading
    window.currentEmployeeData = emp;
    
    // Load and display document attachments from employees table
    loadEmployeeDocuments(emp.emp_id);

    // Account number field ko enable karo agar account type hai
    if (emp.account_type) {
      $('#edit_account_number').prop('disabled', false);
    }

    // Show the modal
    var modal = new bootstrap.Modal(document.getElementById('editJoiningModal'));
    modal.show();
  });

  // Form submit pe update AJAX
  $('#editJoiningForm').on('submit', function(e) {
    e.preventDefault();
    
    // Email validation before submit
    const email = $('#edit_email').val().trim();
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    
    if (!email || !emailRegex.test(email)) {
      $('#editFormMsg').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Please enter a valid email address.</div>');
      return false;
    }
    
    // Check if email is different and already exists
    const emailFeedbackText = $('#emailFeedback').text();
    if (emailFeedbackText === 'This email is already registered') {
      $('#editFormMsg').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>This email is already registered. Please use a different email.</div>');
      return false;
    }

    // Show loading state
    var submitBtn = $(this).find('button[type="submit"]');
    var originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');
    submitBtn.prop('disabled', true);

    var formData = $(this).serialize() + '&action=update_joining_full';
    $.ajax({
      url: 'include/api/employee.php',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          $('#editFormMsg').html('<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Profile completed successfully! Employee is now active.</div>');
          setTimeout(function() {
            location.reload();
          }, 1500);
        } else {
          $('#editFormMsg').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>' + (response.message || 'Update failed') + '</div>');
        }
      },
      error: function() {
        $('#editFormMsg').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Server error! Please try again.</div>');
      },
      complete: function() {
        // Reset button state
        submitBtn.html(originalText);
        submitBtn.prop('disabled', false);
      }
    });
  });

  // Account type validation
  document.addEventListener('DOMContentLoaded', function() {
    // Email validation - Real-time check
    const emailInput = document.getElementById('edit_email');
    const emailFeedback = document.getElementById('emailFeedback');
    let emailCheckTimeout;
    let originalEmail = ''; // Store original email to skip validation
    
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
        
        // Skip check if email hasn't changed from original
        if (email === window.originalEmail) {
          emailFeedback.textContent = 'Current email (unchanged)';
          emailFeedback.style.color = '#6c757d';
          this.style.borderColor = '#6c757d';
          return;
        }
        
        // Check if email already exists (with debounce)
        emailCheckTimeout = setTimeout(function() {
          const currentEmpId = document.getElementById('edit_emp_id').value;
          fetch('../check-email.php?email=' + encodeURIComponent(email) + '&exclude_emp_id=' + currentEmpId)
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
    
    const accountType = document.getElementById('edit_account_type');
    const accountNumber = document.getElementById('edit_account_number');

    if (accountType && accountNumber) {
      // Initially enable if data exists
      if (accountType.value) {
        accountNumber.disabled = false;
      } else {
        accountNumber.disabled = true;
      }

      accountType.addEventListener('change', function() {
        if (this.value === '') {
          accountNumber.value = '';
          accountNumber.disabled = true;
        } else {
          accountNumber.disabled = false;
        }

        // Set validation rules based on account type
        accountNumber.removeAttribute('maxlength');
        accountNumber.removeAttribute('minlength');

        if (this.value === 'IBAN number') {
          accountNumber.setAttribute('maxlength', '24');
          accountNumber.setAttribute('minlength', '24');
          accountNumber.placeholder = '24 digit IBAN';
        } else if (this.value === 'IBFT number') {
          accountNumber.setAttribute('maxlength', '20');
          accountNumber.setAttribute('minlength', '10');
          accountNumber.placeholder = '10-20 digit IBFT';
        } else if (this.value === 'Mobile Banking') {
          accountNumber.setAttribute('maxlength', '11');
          accountNumber.setAttribute('minlength', '11');
          accountNumber.placeholder = '11 digit Mobile Number';
        } else {
          accountNumber.removeAttribute('maxlength');
          accountNumber.removeAttribute('minlength');
          accountNumber.placeholder = '';
        }
      });

      // Input validation
      accountNumber.addEventListener('input', function() {
        if (accountType.value === 'IBAN number') {
          // IBAN mein sirf uppercase letters aur numbers allow karo
          this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        } else {
          // Baaki account types ke liye sirf numeric
          this.value = this.value.replace(/\D/g, '');
        }
      });
    }
  });

  // Function to load employee documents from employees table
  function loadEmployeeDocuments(empId) {
    // Get employee data from the current employee object
    const emp = window.currentEmployeeData;
    
    if (emp) {
      // Display CV attachment if exists
      if (emp.cv_attachment && emp.cv_attachment.trim() !== '') {
        const fileName = emp.cv_attachment.split('/').pop();
        const fileExtension = fileName.split('.').pop().toLowerCase();
        let iconClass = 'fas fa-file';

        if (fileExtension === 'pdf') {
          iconClass = 'fas fa-file-pdf text-danger';
        } else if (['doc', 'docx'].includes(fileExtension)) {
          iconClass = 'fas fa-file-word text-primary';
        }

        // Fix file path for admin panel
        const filePath = emp.cv_attachment.startsWith('../') ? emp.cv_attachment : '../' + emp.cv_attachment;

        $('#cv_attachment_display').html(`
          <div class="d-flex align-items-center">
            <i class="${iconClass} me-2"></i>
            <span class="me-auto" style="cursor: pointer;" onclick="window.open('${filePath}', '_blank')" title="Click to open in new tab">${fileName}</span>
            <a href="${filePath}" download="${fileName}" class="btn btn-sm btn-outline-primary" title="Download file">
              <i class="fas fa-download"></i>
            </a>
          </div>
        `);
      } else {
        $('#cv_attachment_display').html('<span class="text-muted">No Resume uploaded</span>');
      }

      // Display Id Card attachment if exists
      if (emp.id_card_attachment && emp.id_card_attachment.trim() !== '') {
        const fileName = emp.id_card_attachment.split('/').pop();
        const fileExtension = fileName.split('.').pop().toLowerCase();
        let iconClass = 'fas fa-file';

        if (fileExtension === 'pdf') {
          iconClass = 'fas fa-file-pdf text-danger';
        } else if (['jpg', 'jpeg', 'png'].includes(fileExtension)) {
          iconClass = 'fas fa-file-image text-success';
        }

        // Fix file path for admin panel
        const filePath = emp.id_card_attachment.startsWith('../') ? emp.id_card_attachment : '../' + emp.id_card_attachment;

        $('#id_card_attachment_display').html(`
          <div class="d-flex align-items-center">
            <i class="${iconClass} me-2"></i>
            <span class="me-auto" style="cursor: pointer;" onclick="window.open('${filePath}', '_blank')" title="Click to open in new tab">${fileName}</span>
            <a href="${filePath}" download="${fileName}" class="btn btn-sm btn-outline-primary" title="Download file">
              <i class="fas fa-download"></i>
            </a>
          </div>
        `);
      } else {
        $('#id_card_attachment_display').html('<span class="text-muted">No Id Card uploaded</span>');
      }

      // Display other documents if exist
      if (emp.other_documents && emp.other_documents.trim() !== '') {
        try {
          const otherDocs = JSON.parse(emp.other_documents);
          if (Array.isArray(otherDocs) && otherDocs.length > 0) {
            let html = '';

            otherDocs.forEach((file) => {
              if (file.trim() !== '') {
                const fileName = file.split('/').pop();
                const fileExtension = fileName.split('.').pop().toLowerCase();
                let iconClass = 'fas fa-file';

                if (fileExtension === 'pdf') {
                  iconClass = 'fas fa-file-pdf text-danger';
                } else if (['doc', 'docx'].includes(fileExtension)) {
                  iconClass = 'fas fa-file-word text-primary';
                } else if (['jpg', 'jpeg', 'png'].includes(fileExtension)) {
                  iconClass = 'fas fa-file-image text-success';
                }

                // Fix file path for admin panel
                const filePath = file.startsWith('../') ? file : '../' + file;

                html += `
                  <div class="d-flex align-items-center mb-2">
                    <i class="${iconClass} me-2"></i>
                    <span class="me-auto" style="cursor: pointer;" onclick="window.open('${filePath}', '_blank')" title="Click to open in new tab">${fileName}</span>
                    <a href="${filePath}" download="${fileName}" class="btn btn-sm btn-outline-primary" title="Download file">
                      <i class="fas fa-download"></i>
                    </a>
                  </div>
                `;
              }
            });

            if (html) {
              $('#other_documents_display').html(html);
            } else {
              $('#other_documents_display').html('<span class="text-muted">No documents uploaded</span>');
            }
          } else {
            $('#other_documents_display').html('<span class="text-muted">No documents uploaded</span>');
          }
        } catch (e) {
          console.error('Error parsing other_documents:', e);
          $('#other_documents_display').html('<span class="text-muted">Error loading documents</span>');
        }
      } else {
        $('#other_documents_display').html('<span class="text-muted">No documents uploaded</span>');
      }
    } else {
      $('#cv_attachment_display').html('<span class="text-muted">Error loading employee data</span>');
      $('#id_card_attachment_display').html('<span class="text-muted">Error loading employee data</span>');
      $('#other_documents_display').html('<span class="text-muted">Error loading employee data</span>');
    }
  }

  // Get file icon based on file extension
  function getFileIcon(filename) {
    const extension = filename.split('.').pop().toLowerCase();

    switch (extension) {
      case 'pdf':
        return 'fas fa-file-pdf text-danger';
      case 'doc':
      case 'docx':
        return 'fas fa-file-word text-primary';
      case 'jpg':
      case 'jpeg':
      case 'png':
        return 'fas fa-file-image text-success';
      default:
        return 'fas fa-file text-secondary';
    }
  }

  // Function to display document attachments (legacy function - keeping for compatibility)
  function displayDocumentAttachments(cvAttachment, otherDocuments) {
    // CV Attachment Display
    const cvDisplay = $('#cv_attachment_display');
    if (cvAttachment && cvAttachment.trim() !== '') {
      const fileName = cvAttachment.split('/').pop();
      const fileExtension = fileName.split('.').pop().toLowerCase();
      let iconClass = 'fas fa-file';

      if (fileExtension === 'pdf') {
        iconClass = 'fas fa-file-pdf text-danger';
      } else if (['doc', 'docx'].includes(fileExtension)) {
        iconClass = 'fas fa-file-word text-primary';
      }

      // Fix file path for admin panel
      const filePath = cvAttachment.startsWith('../') ? cvAttachment : '../' + cvAttachment;

      cvDisplay.html(`
        <div class="d-flex align-items-center">
          <i class="${iconClass} me-2"></i>
          <span class="me-auto" style="cursor: pointer;" onclick="window.open('${filePath}', '_blank')" title="Click to open in new tab">${fileName}</span>
          <a href="${filePath}" download="${fileName}" class="btn btn-sm btn-outline-primary" title="Download file">
            <i class="fas fa-download"></i>
          </a>
        </div>
      `);
    } else {
      cvDisplay.html('<span class="text-muted">No Resume uploaded</span>');
    }

    // Other Documents Display
    const otherDocsDisplay = $('#other_documents_display');
    if (otherDocuments && otherDocuments.trim() !== '') {
      const files = otherDocuments.split(',');
      let html = '';

      files.forEach((file, index) => {
        if (file.trim() !== '') {
          const fileName = file.split('/').pop();
          const fileExtension = fileName.split('.').pop().toLowerCase();
          let iconClass = 'fas fa-file';

          if (fileExtension === 'pdf') {
            iconClass = 'fas fa-file-pdf text-danger';
          } else if (['doc', 'docx'].includes(fileExtension)) {
            iconClass = 'fas fa-file-word text-primary';
          } else if (['jpg', 'jpeg', 'png'].includes(fileExtension)) {
            iconClass = 'fas fa-file-image text-success';
          }

          // Fix file path for admin panel - main directory ke uploads folder
          const filePath = file.startsWith('uploads/') ? '../../' + file : '../../uploads/joining_documents/' + file.split('/').pop();

          html += `
            <div class="d-flex align-items-center mb-2">
              <i class="${iconClass} me-2"></i>
              <span class="me-auto" style="cursor: pointer;" onclick="window.open('${filePath}', '_blank')" title="Click to open in new tab">${fileName}</span>
              <a href="${filePath}" download="${fileName}" class="btn btn-sm btn-outline-primary" title="Download file">
                <i class="fas fa-download"></i>
              </a>
            </div>
          `;
        }
      });

      if (html) {
        otherDocsDisplay.html(html);
      } else {
        otherDocsDisplay.html('<span class="text-muted">No documents uploaded</span>');
      }
    } else {
      otherDocsDisplay.html('<span class="text-muted">No documents uploaded</span>');
    }
  }
</script>