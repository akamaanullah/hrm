<?php include 'header.php' ?>

<?php include 'top-bar.php' ?>

<?php include 'sidebar.php' ?>

<!-- Main Content -->
<main class="main-content">
    <div class="container-fluid px-3 px-lg-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">All Employees</h1>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#addDepartmentModal">
                    <i class="fas fa-plus me-2"></i>Add Depart
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                    <i class="fas fa-plus me-2"></i>Add Employee
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addShiftModal">
                    <i class="fas fa-plus me-2"></i>Add Shifts
                </button>
                <div id="exportButtonContainer"></div>
            </div>
        </div>
        <!-- Deleted/Active Employees Toggle Buttons -->
        <div class="mb-3 d-flex gap-2">
            <button type="button" class="btn btn-danger" id="showDeletedEmployeesBtn">
                <i class="fas fa-trash-alt me-2"></i>Show Deleted Employees
            </button>
            <button type="button" class="btn btn-secondary d-none" id="showActiveEmployeesBtn">
                <i class="fas fa-users me-2"></i>Show Active Employees
            </button>
        </div>
        <!-- Shifts Table UI -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">All Shifts</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Shift Name</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Grace Time</th>
                                <th>Halfday (Hours)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="shiftsTableBody">
                            <!-- Shifts yahan show hongi -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Filters Section -->
        <div class="card mb-4 mt-4">
            <div class="card-body">
                <form id="employeeFilterForm" class="row g-2">
                    <!-- Employee ID Filter -->
                    <div class="col-12 col-md-6 col-lg">
                        <label class="form-label">Employee ID</label>
                        <input type="text" class="form-control" id="employeeIdFilter" placeholder="Enter Employee ID">
                    </div>
                    <!-- Name Filter -->
                    <div class="col-12 col-md-6 col-lg">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" id="nameFilter" placeholder="Enter Name">
                    </div>
                    <!-- Department Filter -->
                    <div class="col-12 col-md-6 col-lg">
                        <label class="form-label">Department</label>
                        <select class="form-select" id="departmentFilter">
                            <option value="">All Departments</option>
                        </select>
                    </div>
                    <!-- Filter Buttons -->
                    <div class="col-12 col-md-6 col-lg d-flex align-items-end">
                        <button type="reset" class="btn btn-primary w-100 ">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Employees Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="employeeTable">
                        <thead>
                            <tr>
                                <th>Emp ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Phone</th>
                                <th>Job Title</th>
                                <th>Department</th>
                                <th>Shift</th>

                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Static rows removed. Data will be loaded dynamically by JS. -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editEmployeeForm">

                    <!-- Personal Information Section -->
                    <div class="mb-4">
                        <div class="section-header mb-4"
                            style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 8px; padding: 12px 16px; border-left: 4px solid #00bfa5;">
                            <h6 class="fw-bold mb-0" style="color: #2d3436; font-size: 1.1rem;">
                                <i class="fas fa-user me-2" style="color: #00bfa5;"></i>Personal Information
                            </h6>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editEmployeeFirstName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="editEmployeeMiddleName">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editEmployeeLastName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender</label>
                                <select class="form-select" id="editEmployeeGender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="editEmployeeDOB" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" id="editEmployeePhone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="editEmployeeEmail" required>
                                <small class="text-muted" id="editEmailFeedback"></small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" id="editEmployeeAddress" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ID card Number</label>
                                <input type="text" class="form-control" id="editEmployeeCNIC" required>
                            </div>
                            <!-- <div class="col-md-6 mb-3">
                                <label class="form-label">Religion</label>
                                <input type="text" class="form-control" id="editEmployeeReligion" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Marital Status</label>
                                <input type="text" class="form-control" id="editEmployeeMaritalStatus" required>
                            </div> -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Emergency Contact</label>
                                <input type="text" class="form-control" id="editEmployeeEmergencyContact" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Emergency Contact Relation</label>
                                <input type="text" class="form-control" id="editEmployeeEmergencyRelation" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <div style="position: relative;">
                                    <input type="password" class="form-control" id="editEmployeePassword" placeholder="Leave blank to keep current password" style="padding-right: 40px;">
                                    <button type="button" onclick="const pwd = document.getElementById('editEmployeePassword'); const icon = this.querySelector('i'); if(pwd.type === 'password') { pwd.type = 'text'; icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); } else { pwd.type = 'password'; icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6c757d; cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Leave blank to keep current password</small>
                            </div>
                        </div>
                    </div>

                    <!-- Job Information Section -->
                    <div class="mb-4">
                        <div class="section-header mb-4"
                            style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 8px; padding: 12px 16px; border-left: 4px solid #00bfa5;">
                            <h6 class="fw-bold mb-0" style="color: #2d3436; font-size: 1.1rem;">
                                <i class="fas fa-briefcase me-2" style="color: #00bfa5;"></i>Job Information
                            </h6>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Job Title</label>
                                <input type="text" class="form-control" id="editEmployeePosition" required>
                            </div>
                            <!-- <div class="col-md-6 mb-3">
                                <label class="form-label">Reporting Manager</label>
                                <input type="text" class="form-control" id="editEmployeeLineManager" required>
                            </div> -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Department</label>
                                <select class="form-select" id="editEmployeeDepartment" required>
                                    <option value="">Select Department</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sub Department</label>
                                <input type="text" class="form-control" id="editEmployeeSubDepartment">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Job Type</label>
                                <select class="form-select" id="editEmployeeJobType" required>
                                    <option value="">Select Job Type</option>
                                    <option value="Internship">Internship</option>
                                    <option value="Probation">Probation</option>
                                    <option value="Permanent">Permanent</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Shift Timing</label>
                                <select class="form-select" id="editEmployeeShiftTiming" required>
                                    <option value="">Select Shift</option>
                                    <!-- Options JS se aayenge -->
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Joining Date</label>
                                <input type="date" class="form-control" id="editEmployeeJoiningDate" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Salary</label>
                                <input type="number" class="form-control" id="editEmployeeSalary" required>
                            </div>
                        </div>
                    </div>

                    <!-- Bank Information Section -->
                    <div class="mb-4">
                        <div class="section-header mb-4"
                            style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 8px; padding: 12px 16px; border-left: 4px solid #00bfa5;">
                            <h6 class="fw-bold mb-0" style="color: #2d3436; font-size: 1.1rem;">
                                <i class="fas fa-university me-2" style="color: #00bfa5;"></i>Bank Information
                            </h6>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bank Name</label>
                                <select class="form-select" id="editEmployeeBankName" required>
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
                                <select class="form-select" id="editEmployeeAccountType" required>
                                    <option value="">Select Account Type</option>
                                    <option value="IBAN number">IBAN</option>
                                    <option value="IBFT number">IBFT</option>
                                    <option value="Mobile Banking">Mobile Banking</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Account Title</label>
                                <input type="text" class="form-control" id="editEmployeeAccountTitle" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Account Number</label>
                                <input type="text" class="form-control" id="editEmployeeAccountNumber" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bank Branch</label>
                                <input type="text" class="form-control" id="editEmployeeBankBranch" required>
                            </div>
                        </div>
                    </div>

                    <!-- Education & Experience Section -->
                    <div class="mb-4">
                        <div class="section-header mb-4"
                            style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 8px; padding: 12px 16px; border-left: 4px solid #00bfa5;">
                            <h6 class="fw-bold mb-0" style="color: #2d3436; font-size: 1.1rem;">
                                <i class="fas fa-graduation-cap me-2" style="color: #00bfa5;"></i>Education & Experience
                            </h6>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Qualification</label>
                                <input type="text" class="form-control" id="editEmployeeQualificationInstitution"
                                    required>
                            </div>
                            <!-- <div class="col-md-6 mb-3">
                                <label class="form-label">Education From Date</label>
                                <input type="date" class="form-control" id="editEmployeeEducationFromDate">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Education To Date</label>
                                <input type="date" class="form-control" id="editEmployeeEducationToDate">
                            </div> -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Degree / Certification</label>
                                <input type="text" class="form-control" id="editEmployeeEducationPercentage" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Professional Expertise</label>
                                <input type="text" class="form-control" id="editEmployeeSpecialization" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">College / University</label>
                                <input type="text" class="form-control" id="editEmployeeMaritalStatus" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Employer</label>
                                <input type="text" class="form-control" id="editEmployeeLastOrganization" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Designation</label>
                                <input type="text" class="form-control" id="editEmployeeLastDesignation" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Experience From Date</label>
                                <input type="date" class="form-control" id="editEmployeeExperienceFromDate">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Experience To Date</label>
                                <input type="date" class="form-control" id="editEmployeeExperienceToDate">
                            </div>
                        </div>
                    </div>

                    <!-- Document Attachments Section -->
                    <div class="mb-4">
                        <div class="section-header mb-4"
                            style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 8px; padding: 12px 16px; border-left: 4px solid #00bfa5;">
                            <h6 class="fw-bold mb-0" style="color: #2d3436; font-size: 1.1rem;">
                                <i class="fas fa-paperclip me-2" style="color: #00bfa5;"></i>Document Attachments
                            </h6>
                        </div>

                        <!-- Document Attachments Section -->
                        <div class="document-upload-section">

                            <div class="row g-4">

                                <!-- CV Attachment -->
                                <div class="col-md-6">
                                    <div class="upload-field">
                                        <label class="form-label fw-bold text-dark mb-3">Resume Attachment</label>
                                        <div class="file-input-wrapper">
                                            <input type="file" id="editEmployeeCVAttachment"
                                                class="form-control file-input" accept=".pdf,.doc,.docx">
                                            <div class="file-input-display">
                                                <button type="button" class="btn btn-outline-secondary file-choose-btn"
                                                    onclick="document.getElementById('editEmployeeCVAttachment').click()">
                                                    Choose file
                                                </button>
                                                <span class="file-name-display" id="editEmployeeCVDisplay">No file
                                                    chosen</span>
                                            </div>
                                        </div>
                                        <small class="text-muted mt-2 d-block">Upload Resume (PDF, DOC, DOCX
                                            only)</small>
                                    </div>
                                </div>

                                <!-- ID Card Attachment -->
                                <div class="col-md-6">
                                    <div class="upload-field">
                                        <label class="form-label fw-bold text-dark mb-3">ID Card Attachment</label>
                                        <div class="file-input-wrapper">
                                            <input type="file" id="editEmployeeIDCardAttachment"
                                                class="form-control file-input" accept=".jpg,.jpeg,.png,.pdf">
                                            <div class="file-input-display">
                                                <button type="button" class="btn btn-outline-secondary file-choose-btn"
                                                    onclick="document.getElementById('editEmployeeIDCardAttachment').click()">
                                                    Choose file
                                                </button>
                                                <span class="file-name-display" id="editEmployeeIDCardDisplay">No file
                                                    chosen</span>
                                            </div>
                                        </div>
                                        <small class="text-muted mt-2 d-block">Upload ID Card (JPG, PNG, PDF
                                            only)</small>
                                    </div>
                                </div>

                            </div>

                            <div class="row g-4">

                                <!-- Other Documents -->
                                <div class="col-md-6">
                                    <div class="upload-field">
                                        <label class="form-label fw-bold text-dark mb-3">Other Documents</label>
                                        <div class="file-input-wrapper">
                                            <input type="file" id="editEmployeeOtherDocuments"
                                                class="form-control file-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                                multiple>
                                            <div class="file-input-display">
                                                <button type="button" class="btn btn-outline-secondary file-choose-btn"
                                                    onclick="document.getElementById('editEmployeeOtherDocuments').click()">
                                                    Choose files
                                                </button>
                                                <span class="file-name-display" id="editEmployeeOtherDocsDisplay">No
                                                    file chosen</span>
                                            </div>
                                        </div>
                                        <small class="text-muted mt-2 d-block">Upload other relevant documents (PDF,
                                            DOC, DOCX, JPG, PNG)</small>
                                    </div>
                                </div>

                            </div>

                        </div>
                        
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="all-employee.php"><button type="submit" class="btn btn-primary" form="editEmployeeForm">Save
                        Changes</button></a>
            </div>
        </div>
    </div>
</div>

<!-- View Employee Modal -->
<div class="modal fade employee-profile-modal" id="viewEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="profile-cover">
                    <img src="../assets/images/LOGO.png" alt="Cover">
                </div>
                <button type="button" class="btn-close btn-close-white modal-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Profile Image Section -->
                <div class="profile-image-wrapper">
                    <div class="profile-image-container">
                        <img id="viewEmployeeProfileImg" src="../assets/images/default-avatar.jpg" class="profile-image"
                            alt="User Avatar">
                    </div>
                </div>

                <div class="text-center mb-4">
                    <h4 class="mb-1 fw-bold" id="viewEmployeeFullName">Syed Mahad Bukhari</h4>
                    <p class="text-muted mb-2" id="viewEmployeePosition">Job Title</p>
                </div>

                <!-- Information Sections -->
                <div class="row">
                    <!-- Personal Information -->
                    <div class="col-12">
                        <div class="info-section">
                            <h5 class="section-title"><span class="section-title-bar"></span>Personal Information</h5>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-user-friends info-icon"></i> Gender</label>
                                        <p class="info-value" id="viewEmployeeGender"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-calendar-alt info-icon"></i> Date of Birth</label>
                                        <p class="info-value" id="viewEmployeeDOB"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-id-card info-icon"></i> ID card Number</label>
                                        <p class="info-value" id="viewEmployeeCNIC"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-phone info-icon"></i> Phone</label>
                                        <p class="info-value" id="viewEmployeePhone"></p>
                                    </div>
                                </div>



                                <div class="col-sm-6">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-envelope info-icon"></i> Email</label>
                                        <p class="info-value" id="viewEmployeeEmail"></p>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-map-marker-alt info-icon"></i> Address</label>
                                        <p class="info-value" id="viewEmployeeAddress"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-phone-square-alt info-icon"></i> Emergency Contact</label>
                                        <p class="info-value" id="viewEmployeeEmergencyContact"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-phone info-icon"></i> Emergency Contact Relation</label>
                                        <p class="info-value" id="viewEmployeeEmergencyRelation"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Job Information -->
                    <div class="col-12">
                        <div class="info-section">
                            <h5 class="section-title"><span class="section-title-bar"></span>Job Information</h5>
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-building info-icon"></i> Department</label>
                                        <p class="info-value" id="viewEmployeeDepartment"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-sitemap info-icon"></i> Sub Department</label>
                                        <p class="info-value" id="viewEmployeeSubDepartment"></p>
                                    </div>
                                </div>
                                <!-- <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-user-tie info-icon"></i> Reporting Manager</label>
                                        <p class="info-value" id="viewEmployeeLineManager"></p>
                                    </div>
                                </div> -->
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-calendar-check info-icon"></i> Joining Date</label>
                                        <p class="info-value" id="viewEmployeeJoiningDate"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-clock info-icon"></i> Created Date</label>
                                        <p class="info-value" id="viewEmployeeCreatedAt"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-clock info-icon"></i> Timing</label>
                                        <p class="info-value" id="viewEmployeeTiming"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-briefcase info-icon"></i> Job Type</label>
                                        <p class="info-value" id="viewEmployeeJobType"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-money-bill-wave info-icon"></i> Salary</label>
                                        <p class="info-value" id="viewEmployeeSalary"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Bank Information -->
                    <div class="col-12">
                        <div class="info-section">
                            <h5 class="section-title"><span class="section-title-bar"></span>Bank Information</h5>
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-university info-icon"></i> Bank Name</label>
                                        <p class="info-value" id="viewEmployeeBankName"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-credit-card info-icon"></i> Bank Type</label>
                                        <p class="info-value" id="viewEmployeeAccountType"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-user info-icon"></i> Account Title</label>
                                        <p class="info-value" id="viewEmployeeAccountTitle"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-code-branch info-icon"></i> Bank Branch</label>
                                        <p class="info-value" id="viewEmployeeBankBranch"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-hashtag info-icon"></i> Account Number</label>
                                        <p class="info-value" id="viewEmployeeAccountNumber"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Education Information -->
                    <div class="col-12">
                        <div class="info-section">
                            <h5 class="section-title"><span class="section-title-bar"></span>Education Information</h5>
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-graduation-cap info-icon"></i> Qualification
                                        </label>
                                        <p class="info-value" id="viewEmployeeQualificationInstitution"></p>
                                    </div>
                                </div>
                                <!-- <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-calendar-day info-icon"></i> Education From Date</label>
                                        <p class="info-value" id="viewEmployeeEducationFromDate"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-calendar-day info-icon"></i> Education To Date</label>
                                        <p class="info-value" id="viewEmployeeEducationToDate"></p>
                                    </div>
                                </div> -->
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-book-open info-icon"></i> Degree / Certification</label>
                                        <p class="info-value" id="viewEmployeeEducationPercentage"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-building info-icon"></i> Professional Expertise</label>
                                        <p class="info-value" id="viewEmployeeSpecialization"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-building info-icon"></i> College / University</label>
                                        <p class="info-value" id="viewEmployeeMaritalStatus"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Experience Information -->
                    <div class="col-12">
                        <div class="info-section">
                            <h5 class="section-title"><span class="section-title-bar"></span>Experience Information</h5>
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-building info-icon"></i> Last Employer</label>
                                        <p class="info-value" id="viewEmployeeLastOrganization"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-user-tie info-icon"></i> Last Designation</label>
                                        <p class="info-value" id="viewEmployeeLastDesignation"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-calendar-check info-icon"></i> Experience From
                                            Date</label>
                                        <p class="info-value" id="viewEmployeeExperienceFromDate"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-calendar-check info-icon"></i> Experience To Date</label>
                                        <p class="info-value" id="viewEmployeeExperienceToDate"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Leave History -->
                    <div class="col-12">
                        <div class="info-section">
                            <h5 class="section-title"><span class="section-title-bar"></span>Leave History</h5>
                            <div class="row">
                                <div class="col-12">
                                    <div class="info-card">
                                        <label class="info-label" id="approvedLeavesLabel"><i
                                                class="fas fa-calendar-times info-icon"></i> Approved Leaves</label>
                                        <div id="viewEmployeeLeaveHistory" class="mt-2">
                                            <div class="text-center text-muted py-3">Loading leave history...</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Documents Section -->
                    <div class="col-12">
                        <div class="info-section">
                            <h5 class="section-title"><span class="section-title-bar"></span>Uploaded Documents</h5>
                            <div id="employeeDocumentsContainer">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading documents...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Progress Bar -->
                <div class="progress-bar-container mb-5">
                    <div class="step-indicators">
                        <div class="step-indicator">
                            1
                            <span class="step-title">Personal Info</span>
                        </div>
                        <div class="step-indicator">
                            2
                            <span class="step-title">Job & Bank</span>
                        </div>
                        <div class="step-indicator">
                            3
                            <span class="step-title">Education</span>
                        </div>
                        <div class="step-indicator">
                            4
                            <span class="step-title">Documents</span>
                        </div>
                    </div>
                </div>

                <form id="addEmployeeForm" class="step-form">
                    <!-- Step 1: Personal Information -->
                    <div class="form-step active">
                        <h6 class="fw-bold mb-3">Personal Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="newEmployeeFirstName" placeholder="First Name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="newEmployeeMiddleName" placeholder="Middle Name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="newEmployeeLastName" placeholder="Last Name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender</label>
                                <select class="form-select" id="newEmployeeGender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="newEmployeeDOB" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" id="newEmployeePhone" placeholder="Phone" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" id="newEmployeeAddress" placeholder="Address" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ID card Number</label>
                                <input type="text" class="form-control" id="newEmployeeCNIC" placeholder="ID card Number" required>
                            </div>
                            <!-- <div class="col-md-6 mb-3">
                                <label class="form-label">Religion</label>
                                <input type="text" class="form-control" id="newEmployeeReligion" required>
                            </div> -->
                            <!-- <div class="col-md-6 mb-3">
                                <label class="form-label">Marital Status</label>
                                <input type="text" class="form-control" id="newEmployeeMaritalStatus" required>
                            </div> -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Emergency Contact</label>
                                <input type="text" class="form-control" id="newEmployeeEmergencyContact" placeholder="Emergency Contact" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Emergency Contact Relation</label>
                                <input type="text" class="form-control" id="newEmployeeEmergencyRelation" placeholder="Emergency Contact Relation" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="newEmployeeEmail" placeholder="Email" required>
                                <small class="text-muted" id="addEmailFeedback"></small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <div style="position: relative;">
                                    <input type="password" class="form-control" id="newEmployeePassword" placeholder="Password" required autocomplete="new-password" style="padding-right: 40px;">
                                    <button type="button" onclick="const pwd = document.getElementById('newEmployeePassword'); const icon = this.querySelector('i'); if(pwd.type === 'password') { pwd.type = 'text'; icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); } else { pwd.type = 'password'; icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6c757d; cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Shift Timing</label>
                                <select class="form-select" id="newEmployeeShiftTiming" required>
                                    <!-- Options JS se aayenge -->
                                </select>
                            </div>
                        </div>
                        <div class="step-actions mt-4">
                            <div></div>
                            <button type="button" class="btn btn-primary next-step">Next</button>
                        </div>
                    </div>

                    <!-- Step 2: Job & Bank Details -->
                    <div class="form-step">
                        <h6 class="fw-bold mb-3">Job & Bank Details</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Job Title</label>
                                <input type="text" class="form-control" id="newEmployeePosition" placeholder="Job Title" required>
                            </div>
                            <!-- <div class="col-md-6 mb-3">
                                <label class="form-label">Reporting Manager</label>
                                <input type="text" class="form-control" id="newEmployeeLineManager" placeholder="Reporting Manager" required>
                            </div> -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Department</label>
                                <select class="form-select" id="newEmployeeDepartment" required>
                                    <!-- <option value="">Select Department</option> -->
                                    <option value="IT">IT</option>
                                    <option value="HR">HR</option>
                                    <option value="Finance">Finance</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Production">Production</option>
                                    <option value="Seo">Seo</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sub Department</label>
                                <input type="text" class="form-control" id="newEmployeeSubDepartment" placeholder="Sub Department">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bank Name</label>
                                <select class="form-select" id="newEmployeeBankName" required>
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
                                <select class="form-select" id="newEmployeeAccountType" required>
                                    <option value="">Select Account Type</option>
                                    <option value="IBAN number">IBAN</option>
                                    <option value="IBFT number">IBFT</option>
                                    <option value="Mobile Banking">Mobile Banking</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Account Title</label>
                                <input type="text" class="form-control" id="newEmployeeAccountTitle" placeholder="Account Title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Account Number</label>
                                <input type="text" class="form-control" id="newEmployeeAccountNumber" placeholder="Account Number" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bank Branch</label>
                                <input type="text" class="form-control" id="newEmployeeBankBranch" placeholder="Bank Branch" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Salary</label>
                                <input type="number" class="form-control" id="newEmployeeSalary" placeholder="Salary" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Job Type</label>
                                <select class="form-select" id="newEmployeeJobType" required>
                                    <option value="">Select Job Type</option>
                                    <option value="Internship">Internship</option>
                                    <option value="Probation">Probation</option>
                                    <option value="Permanent">Permanent</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Joining Date</label>
                                <input type="date" class="form-control" id="newEmployeeJoiningDate" required>
                            </div>

                        </div>
                        <div class="step-actions mt-4">
                            <button type="button" class="btn btn-secondary prev-step">Previous</button>
                            <button type="button" class="btn btn-primary next-step">Next</button>
                        </div>
                    </div>

                    <!-- Step 3: Education & Experience -->
                    <div class="form-step">
                        <h6 class="fw-bold mb-3">Education & Experience</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Qualification</label>
                                <input type="text" class="form-control" id="newEmployeeQualificationInstitution" placeholder="Qualification"
                                    required>
                            </div>
                            <!-- <div class="col-md-6 mb-3">
                                <label class="form-label">Education From Date</label>
                                <input type="date" class="form-control" id="newEmployeeEducationFromDate">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Education To Date</label>
                                <input type="date" class="form-control" id="newEmployeeEducationToDate">
                            </div> -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Degree / Certification</label>
                                <input type="text" class="form-control" id="newEmployeeEducationPercentage" placeholder="Degree / Certification" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Professional Expertise</label>
                                <input type="text" class="form-control" id="newEmployeeSpecialization" placeholder="Professional Expertise" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">College / University</label>
                                <input type="text" class="form-control" id="newEmployeeMaritalStatus" placeholder="College / University" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Employer</label>
                                <input type="text" class="form-control" id="newEmployeeLastOrganization" placeholder="Last Employer" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Job Title</label>
                                <input type="text" class="form-control" id="newEmployeeLastDesignation" placeholder="Last Job Title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Experience From Date</label>
                                <input type="date" class="form-control" id="newEmployeeExperienceFromDate">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Experience To Date</label>
                                <input type="date" class="form-control" id="newEmployeeExperienceToDate">
                            </div>
                        </div>
                        <div class="step-actions mt-4">
                            <button type="button" class="btn btn-secondary prev-step">Previous</button>
                            <button type="button" class="btn btn-primary next-step">Next</button>
                        </div>
                    </div>

                    <!-- Step 4: Document Attachments -->
                    <div class="form-step">
                        <h6 class="fw-bold mb-3">Document Attachments</h6>

                        <!-- Document Attachments Section -->
                        <div class="document-upload-section">
                            <div class="row g-4">
                                <!-- CV Attachment -->
                                <div class="col-md-6">
                                    <div class="upload-field">
                                        <label class="form-label fw-bold text-dark mb-3">Resume Attachment</label>
                                        <div class="file-input-wrapper">
                                            <input type="file" id="newEmployeeCVAttachment"
                                                class="form-control file-input" accept=".pdf,.doc,.docx" required>
                                            <div class="file-input-display">
                                                <button type="button" class="btn btn-outline-secondary file-choose-btn"
                                                    onclick="document.getElementById('newEmployeeCVAttachment').click()">
                                                    Choose file
                                                </button>
                                                <span class="file-name-display">No file chosen</span>
                                            </div>
                                        </div>
                                        <small class="text-muted mt-2 d-block">Upload Resume (PDF, DOC, DOCX
                                            only)</small>
                                    </div>
                                </div>

                                <!-- ID Card Attachment -->
                                <div class="col-md-6">
                                    <div class="upload-field">
                                        <label class="form-label fw-bold text-dark mb-3">ID Card Attachment <span class="text-danger">*</span></label>
                                        <div class="file-input-wrapper">
                                            <input type="file" id="newEmployeeIDCardAttachment"
                                                class="form-control file-input" accept=".jpg,.jpeg,.png,.pdf" required>
                                            <div class="file-input-display">
                                                <button type="button" class="btn btn-outline-secondary file-choose-btn"
                                                    onclick="document.getElementById('newEmployeeIDCardAttachment').click()">
                                                    Choose file
                                                </button>
                                                <span class="file-name-display">No file chosen</span>
                                            </div>
                                        </div>
                                        <small class="text-muted mt-2 d-block">Upload ID Card (JPG, PNG, PDF only)</small>
                                    </div>
                                </div>

                                <!-- Other Documents -->
                                <div class="col-md-6">
                                    <div class="upload-field">
                                        <label class="form-label fw-bold text-dark mb-3">Other Documents</label>
                                        <div class="file-input-wrapper">
                                            <input type="file" id="newEmployeeOtherDocuments"
                                                class="form-control file-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                                multiple>
                                            <div class="file-input-display">
                                                <button type="button" class="btn btn-outline-secondary file-choose-btn"
                                                    onclick="document.getElementById('newEmployeeOtherDocuments').click()">
                                                    Choose files
                                                </button>
                                                <span class="file-name-display">No file chosen</span>
                                            </div>
                                        </div>
                                        <small class="text-muted mt-2 d-block">Upload other relevant documents (PDF,
                                            DOC, DOCX, JPG, PNG)</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="step-actions mt-4">
                            <button type="button" class="btn btn-secondary prev-step">Previous</button>
                            <button type="submit" class="btn btn-primary">Save Employee</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Department Modal -->
<div class="modal fade " id="addDepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addDepartmentForm">
                    <div class="mb-4">
                        <label for="deptName" class="form-label">Department Name</label>
                        <input type="text" class="form-control" id="deptName" name="dept_name" maxlength="100" required>
                    </div>

                    <div class="mb-4">
                        <label for="deptManager" class="form-label">Department Manager</label>
                        <select class="form-select" id="deptManager" name="manager">
                            <option value="">Select Department Manager</option>
                        </select>
                        <div class="form-text">Select an employee to be the department manager (Optional - can be assigned later)</div>
                    </div>

                    <div class="mb-4">
                        <label for="deptHead" class="form-label">Department Head (Optional)</label>
                        <select class="form-select" id="deptHead" name="dep_head">
                            <option value="">Select Department Head</option>
                        </select>
                        <div class="form-text">Select an employee to be the department head</div>
                    </div>

                    <!-- Existing Departments List -->
                    <div class="mb-3">
                        <label class="form-label">Existing Departments</label>
                        <div id="existingDepartmentsList" style="max-height: 300px; overflow-y: auto;">
                            <div class="text-center text-muted py-3">Loading departments...</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveDepartmentBtn">Save Department</button>
            </div>
        </div>
    </div>
</div>


<!-- Add Shift Modal -->
<div class="modal fade" id="addShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Shift</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addShiftForm">
                    <div class="mb-3">
                        <label class="form-label">Shift Name</label>
                        <input type="text" class="form-control" id="shiftName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Time</label>
                        <input type="time" class="form-control" id="startTime" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Time</label>
                        <input type="time" class="form-control" id="endTime" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Grace Time (minutes)</label>
                        <input type="number" class="form-control" id="graceTime" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Halfday (Hours)</label>
                        <input type="number" class="form-control" id="halfdayHours" min="1" step="0.5" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="employeeToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive"
        aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="employeeToastMsg">
                Employee updated successfully!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
</div>


<?php include 'footer.php' ?>


<script>
$(document).ready(function() {
    // Delete department from dropdown
    $(document).on('click', '.btn-delete-dept', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const deptId = $(this).data('id');
        const deptName = $(this).data('name');

        // Direct API call without confirmation
        $.ajax({
            url: 'include/api/department.php?dept_id=' + deptId,
            type: 'DELETE',
            success: function(response) {
                if (response.success) {
                    showToast('Employee deleted successfully');
                    loadDepartments(); // Refresh the list
                } else {
                    showToast(response.message || 'Error deleting department', 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                let errorMessage = 'Server error! Please try again.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.message || errorMessage;
                } catch (e) {}
                showToast(errorMessage, 'danger');
            }
        });
    });

    // Toast function
    function showToast(message, type = 'success') {
        const toast = $('#employeeToast');
        const toastMessage = $('#employeeToastMsg');

        // Set message
        toastMessage.text(message);

        // Set background color based on type
        toast.removeClass('text-bg-success text-bg-danger');
        toast.addClass(type === 'success' ? 'text-bg-success' : 'text-bg-danger');

        // Show toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
    }

    // Load departments
    function loadDepartments() {
        $.ajax({
            url: 'include/api/department.php',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    displayDepartments(response.data);
                } else {
                    showToast('Error loading departments', 'danger');
                }
            },
            error: function() {
                showToast('Error loading departments', 'danger');
            }
        });
    }

    // Initial load
    loadDepartments();

    // Load departments on page load
    loadDepartments();

    // Save new department or update existing
    $('#saveDepartmentBtn').click(function() {
        const mode = $(this).data('mode');
        
        if (mode === 'update') {
            // Update existing department
            const deptId = $(this).data('dept-id');
            const managerValue = $('#deptManager').val();
            const headValue = $('#deptHead').val();
            
            const formData = {
                dept_id: deptId,
                dept_name: $('#deptName').val().trim(),
                manager: managerValue && managerValue !== '' ? managerValue : null,
                dep_head: headValue && headValue !== '' ? headValue : null,
                status: 'active'
            };

            if (!formData.dept_name) {
                showToast('Department name is required!', 'danger');
                return;
            }

            $.ajax({
                url: 'include/api/department.php',
                type: 'PUT',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        $('#addDepartmentModal').modal('hide');
                        $('#addDepartmentForm')[0].reset();
                        $('#saveDepartmentBtn').html('<i class="fas fa-plus"></i> Save Department');
                        $('#saveDepartmentBtn').removeData('mode dept-id');
                        loadDepartments();
                        showToast('Department updated successfully');
                    } else {
                        showToast(response.message || 'Error updating department', 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    showToast('Server error! Please try again.', 'danger');
                    console.error('Error:', error);
                }
            });
        } else {
            // Add new department
            const managerValue = $('#deptManager').val();
            const headValue = $('#deptHead').val();
            
            const formData = {
                dept_name: $('#deptName').val().trim(),
                manager: managerValue && managerValue !== '' ? managerValue : null,
                dep_head: headValue && headValue !== '' ? headValue : null,
                status: 'active'
            };

            if (!formData.dept_name) {
                showToast('Department name is required!', 'danger');
                return;
            }

            if (formData.dept_name.length > 100) {
                showToast('Department name cannot exceed 100 characters!', 'danger');
                return;
            }

            $.ajax({
                url: 'include/api/department.php',
                type: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        $('#addDepartmentModal').modal('hide');
                        $('#addDepartmentForm')[0].reset();
                        loadDepartments();
                        showToast('Department added successfully');
                    } else {
                        showToast(response.message || 'Error adding department', 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    showToast('Server error! Please try again.', 'danger');
                    console.error('Error:', error);
                }
            });
        }
    });

    // Load employees for department head and manager dropdowns
    function loadEmployeesForDropdown() {
        // Load all employees for Department Head dropdown
        $.ajax({
            url: 'include/api/department.php?action=employees',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    // Load employees for Department Head dropdown
                    const headSelect = $('#deptHead');
                    headSelect.empty();
                    headSelect.append('<option value="">Select Department Head</option>');
                    
                    response.data.forEach(function(employee) {
                        const option = `<option value="${employee.emp_id}">${employee.name} - ${employee.designation}</option>`;
                        headSelect.append(option);
                    });
                } else {
                    console.error('Error loading employees for department head:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading employees for department head:', error);
            }
        });

        // Load only Management employees for Department Manager dropdown
        $.ajax({
            url: 'include/api/department.php?action=employees&type=management',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    // Load employees for Department Manager dropdown
                    const managerSelect = $('#deptManager');
                    managerSelect.empty();
                    managerSelect.append('<option value="">Select Department Manager</option>');
                    
                    response.data.forEach(function(employee) {
                        const option = `<option value="${employee.emp_id}">${employee.name} - ${employee.designation}</option>`;
                        managerSelect.append(option);
                    });
                } else {
                    console.error('Error loading management employees:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading management employees:', error);
            }
        });
    }

    // Display departments
    function displayDepartments(departments) {
        const container = $('#existingDepartmentsList');
        
        if (departments.length === 0) {
            container.html('<div class="text-center text-muted py-3">No departments found</div>');
            return;
        }

        let html = '';
        departments.forEach(function(dept) {
            const depHeadName = dept.first_name && dept.last_name ? 
                `${dept.first_name} ${dept.middle_name || ''} ${dept.last_name}`.replace(/\s+/g, ' ').trim() : 'Not assigned';
            const managerName = dept.manager_first_name && dept.manager_last_name ? 
                `${dept.manager_first_name} ${dept.manager_middle_name || ''} ${dept.manager_last_name}`.replace(/\s+/g, ' ').trim() : 'Not assigned';
            html += `
                <div class="dept-item" style="cursor: pointer;" onclick="editDepartmentInModal(${dept.dept_id}, '${dept.dept_name}', ${dept.manager || 'null'}, ${dept.dep_head || 'null'})">
                    <div class="dept-info">
                        <div class="dept-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="dept-details">
                            <span class="dept-name">${dept.dept_name}</span>
                            <small class="text-muted d-block">Manager: ${managerName}</small>
                            <small class="text-muted d-block">Head: ${depHeadName}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-delete-dept" 
                            data-id="${dept.dept_id}" data-name="${dept.dept_name}"
                            title="Delete Department" onclick="event.stopPropagation();">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        });
        container.html(html);
    }

    // Load employees when modal opens
    $('#addDepartmentModal').on('show.bs.modal', function() {
        loadEmployeesForDropdown();
        loadDepartments();
    });


    // Edit department in existing modal
    window.editDepartmentInModal = function(deptId, deptName, managerId, headId) {
        // Fill the form with current values
        $('#deptName').val(deptName);
        $('#deptManager').val(managerId === 'null' ? '' : managerId);
        $('#deptHead').val(headId === 'null' ? '' : headId);
        
        // Change button to update mode
        $('#saveDepartmentBtn').html('<i class="fas fa-save"></i> Update Department');
        $('#saveDepartmentBtn').data('mode', 'update').data('dept-id', deptId);
        
        // Scroll to form
        $('.modal-body').scrollTop(0);
    };

    // Edit department
    $(document).on('click', '.edit-btn', function() {
        const deptId = $(this).data('id');
        $.ajax({
            url: `include/api/department.php?dept_id=${deptId}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const dept = response.data;
                    $('#edit_dept_id').val(dept.dept_id);
                    $('#edit_dept_name').val(dept.dept_name);
                    $('#edit_status').val(dept.status);
                    $('#editDepartmentModal').modal('show');
                }
            }
        });
    });

    // Update department
    $('#updateDepartment').click(function() {
        const formData = {
            dept_id: $('#edit_dept_id').val(),
            dept_name: $('#edit_dept_name').val().trim(),
            status: $('#edit_status').val()
        };

        if (!formData.dept_name) {
            showToast('Department name is required!', 'danger');
            return;
        }

        $.ajax({
            url: 'include/api/department.php',
            type: 'PUT',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    $('#editDepartmentModal').modal('hide');
                    loadDepartments();
                    showToast('Department updated successfully');
                } else {
                    showToast(response.message || 'Error updating department', 'danger');
                }
            },
            error: function(xhr, status, error) {
                showToast('Server error! Please try again.', 'danger');
                console.error('Error:', error);
            }
        });
    });

    // Delete department
    $(document).on('click', '.delete-btn', function() {
        const deptId = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to delete this employee!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `include/api/department.php?dept_id=${deptId}`,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            loadDepartments();
                            showToast('Employee deleted successfully');
                        } else {
                            showToast(response.message ||
                                'Error deleting department', 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showToast('Server error! Please try again.', 'danger');
                        console.error('Error:', error);
                    }
                });
            }
        });
    });
});


// Load employees for edit dropdown
function loadEmployeesForEditDropdown() {
    // Load all employees for Department Head dropdown
    $.ajax({
        url: 'include/api/department.php?action=employees',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                // Load employees for Department Head dropdown
                const headSelect = $('#editDeptHead');
                headSelect.empty();
                headSelect.append('<option value="">Select Department Head</option>');
                
                response.data.forEach(function(employee) {
                    const option = `<option value="${employee.emp_id}">${employee.name} - ${employee.designation}</option>`;
                    headSelect.append(option);
                });
                
                // Set selected value for department head
                const currentHead = $('#editDeptHead').data('current-head');
                if (currentHead) $('#editDeptHead').val(currentHead);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading employees for department head:', error);
        }
    });

    // Load only Management employees for Department Manager dropdown
    $.ajax({
        url: 'include/api/department.php?action=employees&type=management',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                // Load employees for Department Manager dropdown
                const managerSelect = $('#editDeptManager');
                managerSelect.empty();
                managerSelect.append('<option value="">Select Department Manager</option>');
                
                response.data.forEach(function(employee) {
                    const option = `<option value="${employee.emp_id}">${employee.name} - ${employee.designation}</option>`;
                    managerSelect.append(option);
                });
                
                // Set selected value for department manager
                const currentManager = $('#editDeptManager').data('current-manager');
                if (currentManager) $('#editDeptManager').val(currentManager);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading management employees:', error);
        }
    });
}

// Update Department
$('#updateDepartmentBtn').click(function() {
    const formData = {
        dept_id: $('#editDeptId').val(),
        dept_name: $('#editDeptName').val().trim(),
        manager: $('#editDeptManager').val() || null,
        dep_head: $('#editDeptHead').val() || null,
        status: 'active'
    };

    if (!formData.dept_name) {
        showToast('Department name is required!', 'danger');
        return;
    }

    $.ajax({
        url: 'include/api/department.php',
        type: 'PUT',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                $('#editDepartmentModal').modal('hide');
                loadDepartments();
                showToast('Department updated successfully');
            } else {
                showToast(response.message || 'Error updating department', 'danger');
            }
        },
        error: function(xhr, status, error) {
            showToast('Server error! Please try again.', 'danger');
            console.error('Error:', error);
        }
    });

});

</script>
<script src="include/js/employee.js"></script>