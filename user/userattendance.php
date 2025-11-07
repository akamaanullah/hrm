<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<?php include "header.php"?>
<?php include "topbar.php"?>
<?php include "sidebar.php"?>
<!-- Main Content -->
<main class="main-content">
<div class="container-fluid px-3 px-lg-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 attendance-header">
        <h1 class="h3 mb-0">My Attendance</h1>
        <!-- <div class="d-flex align-items-center gap-3"> -->
            <!-- Month Picker -->
            <!-- <div class="month-picker-container"> -->
                <!-- <label for="attendanceMonthPicker" class="form-label mb-1" style="font-size: 0.9rem; color: #666; font-weight: 500;">
                    <i class="fas fa-calendar-alt me-1"></i>Select Month
                </label> -->
                <!-- <input type="month" 
                       class="form-control" 
                       id="attendanceMonthPicker" 
                       style="min-width: 150px; border-radius: 8px; border: 1px solid #ddd; padding: 8px 12px; font-size: 0.9rem;">
            </div>
        </div> -->
        <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <label for="dateRange" style="font-size: 0.9rem; color: #636e72; font-weight: 500; margin: 0;">
                        <i class="fas fa-calendar-alt me-2"></i>Date Range:
                    </label>
                    <input type="date" id="startDate" style="padding: 0.4rem 0.6rem; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.85rem; color: #374151; background: white;">
                    <span style="color: #636e72; font-size: 0.85rem;">to</span>
                    <input type="date" id="endDate" style="padding: 0.4rem 0.6rem; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.85rem; color: #374151; background: white;">
                    <button id="applyDateFilter" style="background: linear-gradient(135deg, #00bfa5, #02d6ba); color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: transform 0.2s ease;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                        Apply
                    </button>
                    <button id="clearDateFilter" style="background: #6c757d; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: transform 0.2s ease;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                        Clear
                    </button>
                </div>
            </div>
    </div>
    <!-- Attendance Cards Grid -->
    <div class="attendance-grid" id="attendanceGrid">
        <!-- Attendance cards will be loaded here by JS -->
    </div>
    <!-- Bootstrap Attendance Details Modal -->
    <div class="modal fade" id="attendanceDetailsModal" tabindex="-1" aria-labelledby="attendanceDetailsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.1);">
          <div class="modal-header">
            <div class="d-flex align-items-center">
              <i class="fas fa-user-clock me-2" style="font-size: 1.3rem; color: var(--button-color);"></i>
              <h5 class="modal-title mb-0" id="attendanceDetailsModalLabel">Attendance Details</h5>
            </div>
            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="attendanceDetailsModalBody" style="padding: 1.5rem;">
            <!-- Details will be injected here by JS -->
          </div>
        </div>
      </div>
    </div>

    <!-- Break Records Modal -->
    <div class="modal fade" id="breakRecordsModal" tabindex="-1" aria-labelledby="breakRecordsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header" >
            <h5 class="modal-title" id="breakRecordsModalLabel">
              <i class="fa-solid fa-utensils me-2" style="color: var(--button-color);"></i>Break Records
            </h5>
            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="breakRecordsModalBody">
            <div class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2 text-muted">Loading break records...</p>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>
</main>
<!-- Toast Notification -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
  <div id="attendanceToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="attendanceToastMsg">
        Check in successful!
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
<?php include "footer.php"?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<?php
if (!isset($_SESSION['emp_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}
?>