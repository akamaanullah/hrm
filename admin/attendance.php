<?php include "header.php" ?>

<?php include "top-bar.php" ?>

<?php include "sidebar.php" ?>

<!-- Add required CSS and JS files -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker.min.css">



<!-- Main Content -->
<main class="main-content">
    <div class="container-fluid px-3 px-lg-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Attendance Management</h1>
            <div id="exportButtonContainer">
                <button id="autoAttendanceBtn" style="background:#11c9bb; color:#fff; border:none; border-radius:8px; font-weight:500; padding:8px 20px;">
                    <i class="fas fa-pen me-1"></i> Auto Attendance
                </button>
            </div>
        </div>
        <!-- Filters Section -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="attendanceFilterForm" class="row">
                    <!-- Employee ID Filter -->
                    <div class="col-12 col-md-6 col-lg">
                        <label class="form-label">Employee ID</label>
                        <input type="text" class="form-control" id="empIdFilter" placeholder="Enter Employee ID">
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
                            <!-- Departments will be loaded dynamically by JS -->
                        </select>
                    </div>
                    <!-- Status Filter -->
                    <div class="col-12 col-md-6 col-lg">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                            <option value="Half-day">Half-day</option>
                        </select>
                    </div>
                    <!-- Date Filter -->
                    <div class="col-12 col-md-6 col-lg">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" id="dateFilter" placeholder="Select Date">
                    </div>
                    <!-- Clear Filters Button -->
                    <div class="col-12 col-md-6 col-lg d-flex align-items-end">
                        <button type="reset" class="btn btn-primary w-100">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="attendanceTable">

                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Shift</th>
                                <th>Date</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Working Hours</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- dynamic data will be loaded here -->
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

    </div>
</main>

<!-- Monthly Attendance Report Modal -->
<div class="modal fade" id="monthlyReportModal" tabindex="-1" aria-labelledby="monthlyReportModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="monthlyReportModalLabel">Monthly Attendance Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Summary Cards + Calendar/Filter Side by Side -->
                <div class="row mb-3">
                    <div class="col-md-7 mb-3 mb-md-0">
                        <div id="attendanceSummaryArea"></div>
                    </div>
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Select Month</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="row g-2 align-items-end">
                                        <div class="col">
                                            <label class="form-label mb-1">Start Date</label>
                                            <input type="date" id="filterStartDate" class="form-control" min="" />
                                        </div>
                                        <div class="col">
                                            <label class="form-label mb-1">End Date</label>
                                            <input type="date" id="filterEndDate" class="form-control" min="" />
                                        </div>
                                        <div class="col-auto d-flex gap-2">
                                            <button class="btn btn-primary date-range-btn" id="applyDateRangeFilter">Filter</button>
                                            <button class="btn btn-secondary date-range-btn" id="clearDateRangeFilter">Clear Filter</button>
                                        </div>
                                    </div>
                                </div>
                                <div id="monthCalendar"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Attendance Table -->
                <div class="row">
                    <div class="col-12">
                        <div id="monthlyReportTable" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daily Attendance Detail Modal -->
<div class="modal fade" id="dailyDetailModal" tabindex="-1" aria-labelledby="dailyDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border: none; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.1);">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-clock me-2" style="font-size: 1.3rem; color: var(--button-color);"></i>
                    <h5 class="modal-title mb-0" id="dailyDetailModalLabel">Daily Attendance Detail</h5>
                </div>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <div id="dailyDetailContent"></div>
            </div>
        </div>
    </div>
</div>

<!-- Auto Attendance Modal -->
<div class="modal fade" id="autoAttendanceModal" tabindex="-1" aria-labelledby="autoAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#11c9bb; color:#fff;">
                <h5 class="modal-title" id="autoAttendanceModalLabel">Auto Attendance Filters</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="autoAttendanceFilterForm">
                    <div class="row mb-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small mb-1">Employee ID</label>
                            <input type="text" class="form-control" id="autoEmpId" placeholder="Employee ID">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small mb-1">Name</label>
                            <input type="text" class="form-control" id="autoEmpName" placeholder="Employee Name">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small mb-1">Department</label>
                            <select class="form-select" id="autoDepartment">
                                <option value="">All Departments</option>
                                <!-- Departments will be loaded dynamically by JS -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small mb-1">Date</label>
                            <input type="date" class="form-control" id="autoAttendanceDate">
                        </div>
                    </div>
                </form>
                <div class="mt-3">
                    <table class="table table-bordered table-sm" id="autoEmployeeTable">
                        <thead>
                            <tr>
                                <th style="width:40px;"><input type="checkbox" id="autoCheckAll"></th>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Department</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- JS se fill hoga -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="markAttendanceBtn" class="btn" style="background:#11c9bb; color:#fff; border:none; border-radius:8px; font-weight:500; padding:8px 24px;">Mark Attendance</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="include/js/attendance.js"></script>
<?php include "footer.php" ?>