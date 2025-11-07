<?php
session_start();
require_once "../config.php";
?>
<?php include "header.php"?>
<?php include "topbar.php"?>
<?php include "sidebar.php"?>
<?php
$emp_id = $_SESSION['emp_id'] ?? null;
if (!$emp_id) {
    echo "<script>alert('Please login first'); window.location.href='../login.php';</script>";
    exit;
}
// Sirf current user ke leaves fetch karo
$sql = "SELECT lr.*, lt.type_name FROM leave_requests lr LEFT JOIN leave_types lt ON lr.leave_type_id = lt.leave_type_id WHERE lr.emp_id = ? ORDER BY lr.created_at DESC";
$stmt = $pdo->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . implode(', ', $pdo->errorInfo()));
}
$stmt->execute([$emp_id]);
?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
<!-- Main Content -->
<main class="main-content">
    <div class="container-fluid px-3 px-lg-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Leave History</h1>
            <button class="btn btn-primary" id="applyLeaveBtn">Apply Leave</button>
        </div>
        <!-- Leave Applications Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="appliedLeaveTable">
                        <thead>
                            <tr>
                                <th>Emp ID</th>
                                <th>Name</th>
                                <th>Leave Type</th>
                                <th>From Date</th>
                                <th>To Date</th>
                                <th>Days</th>
                                <th>Reason</th>
                                <th>Applied On</th>
                                <th>Admin Comment</th>
                                <th>Document</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
<!-- Apply Leave Modal -->
<div class="modal fade" id="applyLeaveModal" tabindex="-1" aria-labelledby="applyLeaveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="applyLeaveModalLabel">Apply Leave</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="applyLeaveForm" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="leaveType" class="form-label">Leave Type</label>
            <select class="form-select" id="leaveType" name="leave_type_id" required>
              <option value="">Select Leave Type</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="startDate" class="form-label">From Date</label>
            <input type="date" class="form-control" id="startDate" name="start_date" required>
          </div>
          <div class="mb-3">
            <label for="endDate" class="form-label">To Date</label>
            <input type="date" class="form-control" id="endDate" name="end_date" required>
          </div>
          <div class="mb-3">
            <label for="reason" class="form-label">Reason</label>
            <textarea class="form-control" id="reason" name="reason" rows="2" required></textarea>
          </div>
          <div class="mb-3">
            <label for="document" class="form-label">Document (optional)</label>
            <input type="file" class="form-control" id="document" name="document">
          </div>
          <button type="submit" class="btn btn-primary">Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- Edit Leave Modal -->
<div class="modal fade" id="editLeaveModal" tabindex="-1" aria-labelledby="editLeaveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editLeaveModalLabel">Edit Leave</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editLeaveForm" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="editLeaveType" class="form-label">Leave Type</label>
            <select class="form-select" id="editLeaveType" name="leave_type_id">
              <option value="">Keep current leave type</option>
            </select>
            <small class="form-text text-muted">Leave empty to keep current leave type</small>
          </div>
          <div class="mb-3">
            <label for="editStartDate" class="form-label">From Date</label>
            <input type="date" class="form-control" id="editStartDate" name="start_date">
            <small class="form-text text-muted">Leave empty to keep current start date</small>
          </div>
          <div class="mb-3">
            <label for="editEndDate" class="form-label">To Date</label>
            <input type="date" class="form-control" id="editEndDate" name="end_date">
            <small class="form-text text-muted">Leave empty to keep current end date</small>
          </div>
          <div class="mb-3">
            <label for="editReason" class="form-label">Reason</label>
            <textarea class="form-control" id="editReason" name="reason" rows="2"></textarea>
            <small class="form-text text-muted">Leave empty to keep current reason</small>
          </div>
          <div class="mb-3">
            <label for="editDocument" class="form-label">Document (optional)</label>
            <input type="file" class="form-control" id="editDocument" name="document">
            <small class="form-text text-muted">Leave empty to keep existing document</small>
          </div>
          <button type="submit" class="btn btn-primary">Update Leave</button>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- Leave Details Modal -->
<div class="modal fade" id="leaveDetailsModal" tabindex="-1" aria-labelledby="leaveDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="leaveDetailsModalLabel">Leave Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="leaveDetailsBody">
        <!-- Details will be filled by JS -->
      </div>
    </div>
  </div>
</div>
<?php include "footer.php"?>
<script src="include/js/leave-history.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>