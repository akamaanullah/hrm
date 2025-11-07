<?php include "header.php" ?>
<?php include "top-bar.php" ?>
<?php include "sidebar.php" ?>
<!-- Main Content -->
<main class="main-content">
    <div class="container-fluid px-3 px-lg-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Leave Requests</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaveTypeModal">
                <i class="fas fa-plus me-2"></i>Add Leave Type
            </button>
        </div>
        <!-- Search Bar -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="leaveRequestSearchForm" class="row">
                    <div class="col-12 col-md-6 col-lg">
                        <label class="form-label">Search by ID And Name</label>
                        <input type="text" class="form-control" id="employeeIdSearch" placeholder="Enter ID And Name">
                    </div>
                    <div class="col-12 col-md-6 col-lg">
                        <label class="form-label">Search by Department</label>
                        <select class="form-select" id="departmentSearch">
                            <option value="">All Departments</option>
                            <!-- Options will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg">
                        <label class="form-label">Search by Leave Type</label>
                        <select class="form-select" id="leaveTypeSearch">
                            <option value="">All Leave Types</option>
                            <!-- Options will be loaded dynamically -->
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
        <!-- Leave Requests Cards -->
        <div class="card" id="leaveRequestsListCard">
            <div class="card-body">
                <div class="row g-4" id="leaveRequestsContainer">
                    <!-- Dynamic cards will be loaded here by JS -->
                    <div class="col-md-6 col-lg-4 leave-card" ...>
                        <!-- card content -->
                    </div>
                </div>
            </div>
        </div>
        <!-- No Data Found Message -->
        <div id="noDataMessage" class="text-center py-5" style="display: none;">
            <div class="no-data-icon-container bg-light rounded-circle mx-auto mb-4" style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-calendar-check fa-3x" style="color:var(--topbar-bg-light);"></i>
            </div>
            <h4 class="fw-bold" style="color:var(--topbar-bg-light);">All Caught Up!</h4>
            <p class="text-muted">There are no leave requests at the moment.</p>
        </div>
    </div>
</main>
<!-- Toast Notification -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="leaveToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive"
        aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="leaveToastMsg">
                Leave accepted successfully!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
</div>
<!-- Add Leave Type Modal -->
<div class="modal fade" id="addLeaveTypeModal" tabindex="-1" aria-labelledby="addLeaveTypeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLeaveTypeModalLabel">Add New Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addLeaveTypeForm">
                    <div class="mb-3">
                        <label for="leaveTypeName" class="form-label">Leave Type Name</label>
                        <input type="text" class="form-control" id="leaveTypeName" name="leaveTypeName" required>
                    </div>
                </form>
                <hr>
                <!-- Existing Departments List -->
                <div class="mb-3">
                    <label class="form-label">Existing Leave Types</label>
                    <div class="dept-list">
                        <div id="existingLeaveTypesList">
                            <!-- Leave types will be loaded here dynamically -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveLeaveTypeBtn">Save Leave Type</button>
            </div>
        </div>
    </div>
</div>
<script src="include/js/leave-requests.js"></script>
<?php include "footer.php" ?>