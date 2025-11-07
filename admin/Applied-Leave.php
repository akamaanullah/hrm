<?php include "header.php"?>

<?php include "top-bar.php"?>

<?php include "sidebar.php"?>

<!-- Add required CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">

<!-- Main Content -->
<main class="main-content">
    <div class="container-fluid px-3 px-lg-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Leave Applications</h1>
            <div id="exportButtonContainer"></div>
        </div>

        <!-- Filters Section -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="leaveFilterForm" class="row">
                    <div class="col-12 col-md-6 col-lg">
                        <label class="form-label">Employee ID</label>
                        <input type="text" class="form-control" id="empIdFilter" placeholder="Employee ID">
                    </div>
                    <div class="col-12 col-md-6 col-lg">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" id="nameFilter" placeholder="Employee Name">
                    </div>
                    <div class="col-12 col-md-6 col-lg">
                        <label class="form-label">Department</label>
                        <select class="form-select" id="departmentFilter">
                            <option value="">All Departments</option>
                            <?php
                            if (isset($pdo)) {
                                $dept_query = "SELECT dept_name FROM departments WHERE status = 'active'";
                                $dept_result = $pdo->query($dept_query);
                                if ($dept_result) {
                                    while ($dept = $dept_result->fetch()) {
                                        echo "<option value='" . htmlspecialchars($dept['dept_name']) . "'>" . htmlspecialchars($dept['dept_name']) . "</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg d-flex align-items-end">
                        <button type="reset" class="btn btn-primary w-100">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Leave Applications Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="appliedLeaveTable">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Employee Name</th>
                                <th>Department</th>
                                <th>Leave Type</th>
                                <th>From Date</th>
                                <th>To Date</th>
                                <th>Days</th>
                                <th>Reason</th>
                                <th>Applied On</th>
                                <th>Admin Comment</th>
                                <th>Status</th>
                                <th>Document</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="appliedLeaveTbody">
                            <!-- Dynamic rows will be loaded here by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Document View Modal -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="documentModalLabel">Document View</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="documentModalBody" style="text-align:center;">
        <!-- Document will be loaded here -->
      </div>
    </div>
  </div>
</div>

<!-- Leave Details Modal -->
<div class="modal fade" id="adminLeaveDetailsModal" tabindex="-1" aria-labelledby="adminLeaveDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="adminLeaveDetailsModalLabel">Leave Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="adminLeaveDetailsBody">
        <!-- Details will be filled by JS -->
      </div>
    </div>
  </div>
</div>

<!-- Edit Leave Modal -->
<div class="modal fade" id="editLeaveModal" tabindex="-1" aria-labelledby="editLeaveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editLeaveModalLabel">Edit Leave Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editLeaveForm">
          <input type="hidden" id="editLeaveId">
          <div class="mb-3">
            <label for="editStatus" class="form-label">Status</label>
            <select class="form-select" id="editStatus" required>
              <!-- <option value="pending">Pending</option> -->
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="editAdminComment" class="form-label">Admin Comment</label>
            <textarea class="form-control" id="editAdminComment" rows="3" placeholder="Enter admin comment..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveLeaveChanges">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<?php include "footer.php"?>
<script src="include/js/Applied-Leave.js"></script>