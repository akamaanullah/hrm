<?php include "header.php" ?>
<?php include "top-bar.php" ?>
<?php include "sidebar.php" ?>
<!-- Main Content -->
<main class="main-content">
  <div class="container-fluid px-3 px-lg-4">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mb-4">
      <h4 class="mb-0" style="color: #1f2937; font-size: 1.75rem;">Dashboard Overview</h4>
      <!-- <div style="font-size: 0.9rem; color: #6b7280;">
        <i class="fas fa-calendar-alt me-2"></i>
        <?php echo date('l, F j, Y'); ?>
      </div> -->
    </div>
    <!-- Quick Action Buttons -->
    <div class="row mb-4">
      <div class="col-12">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;">
          <a href="all-employee.php" class="btn" style="background: linear-gradient(135deg, #00bfa5, #02d6ba); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; transition: transform 0.2s ease; box-shadow: 0 2px 4px rgba(0, 191, 165, 0.3); text-decoration: none; display: inline-block;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0, 191, 165, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0, 191, 165, 0.3)'">
            <i class="fas fa-users me-2"></i>All Employees
          </a>
          <a href="leave-requests.php" class="btn" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; transition: transform 0.2s ease; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3); text-decoration: none; display: inline-block;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(16, 185, 129, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(16, 185, 129, 0.3)'">
            <i class="fas fa-calendar-times me-2"></i>Leave Requests
          </a>
          <a href="payroll.php" class="btn" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; transition: transform 0.2s ease; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3); text-decoration: none; display: inline-block;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(245, 158, 11, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(245, 158, 11, 0.3)'">
            <i class="fas fa-money-bill-wave me-2"></i>Payroll
          </a>
          <a href="admin-annoucement.php" class="btn" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; transition: transform 0.2s ease; box-shadow: 0 2px 4px rgba(139, 92, 246, 0.3); text-decoration: none; display: inline-block;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(139, 92, 246, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(139, 92, 246, 0.3)'">
            <i class="fas fa-bullhorn me-2"></i>Announcements
          </a>
        </div>
      </div>
    </div>
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="stats-card h-100 clickable-card" data-modal="totalEmployeesModal" style="background: white; border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 12px; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 15px -3px rgba(0, 0, 0, 0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)'">
          <div class="stats-icon" style="background: #e0f2fe; color: #0284c7; border-radius: 8px; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
            <i class="fas fa-users"></i>
          </div>
          <div class="stats-title" style="color: #6b7280; font-size: 0.9rem; font-weight: 500; margin-bottom: 0.5rem;">Total Employees</div>
          <div class="stats-value" id="totalEmployeesCount" style="color: #1f2937; font-size: 2rem; font-weight: 700;"></div>
          <div id="totalEmployeesTrend" style="color: #10b981; font-size: 0.85rem; font-weight: 600; margin-top: 0.5rem;">
            <i class="fas fa-arrow-up me-1"></i><span id="totalEmployeesChange">Loading...</span>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="stats-card h-100 clickable-card" data-modal="presentTodayModal" style="background: white; border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 12px; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 15px -3px rgba(0, 0, 0, 0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)'">
          <div class="stats-icon" style="background: #dcfce7; color: #16a34a; border-radius: 8px; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="stats-title" style="color: #6b7280; font-size: 0.9rem; font-weight: 500; margin-bottom: 0.5rem;">Present Today</div>
          <div class="stats-value" id="presentTodayCount" style="color: #1f2937; font-size: 2rem; font-weight: 700;"></div>
          <div id="presentTodayTrend" style="color: #10b981; font-size: 0.85rem; font-weight: 600; margin-top: 0.5rem;">
            <i class="fas fa-arrow-up me-1"></i><span id="presentTodayPercentage">Loading...</span>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="stats-card h-100 clickable-card" data-modal="onLeaveModal" style="background: white; border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 12px; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 15px -3px rgba(0, 0, 0, 0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)'">
          <div class="stats-icon" style="background: #fef3c7; color: #d97706; border-radius: 8px; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
            <i class="fas fa-user-clock"></i>
          </div>
          <div class="stats-title" style="color: #6b7280; font-size: 0.9rem; font-weight: 500; margin-bottom: 0.5rem;">On Leave</div>
          <div class="stats-value" id="onLeaveCount" style="color: #1f2937; font-size: 2rem; font-weight: 700;"></div>
          <div id="onLeaveTrend" style="color: #f59e0b; font-size: 0.85rem; font-weight: 600; margin-top: 0.5rem;">
            <i class="fas fa-minus me-1"></i><span id="onLeavePercentage">Loading...</span>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="stats-card h-100 clickable-card" data-modal="departmentsModal" style="background: white; border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 12px; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 15px -3px rgba(0, 0, 0, 0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)'">
          <div class="stats-icon" style="background: #e0f2fe; color: #0284c7; border-radius: 8px; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
            <i class="fas fa-building"></i>
          </div>
          <div class="stats-title" style="color: #6b7280; font-size: 0.9rem; font-weight: 500; margin-bottom: 0.5rem;">Departments</div>
          <div class="stats-value" id="departmentsCount" style="color: #1f2937; font-size: 2rem; font-weight: 700;"></div>
          <div id="departmentsTrend" style="color: #06b6d4; font-size: 0.85rem; font-weight: 600; margin-top: 0.5rem;">
            <i class="fas fa-arrow-up me-1"></i><span id="departmentsChange">Loading...</span>
          </div>
        </div>
      </div>
    </div>
    
    
    
    <!-- Weekly Attendance Overview (Full Width) -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="chart-card h-100" style="background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb; overflow: hidden;">
          <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb;">
            <h5 class="chart-title mb-0" style="font-weight: 700; color: #000000; font-size: 1.25rem;">
              <i class="fas fa-calendar-check me-2" style="color: #06b6d4;"></i>
              Weekly Attendance Overview
            </h5>
          </div>
          <div id="attendanceChart" style="min-height: 400px; padding: 0.5rem;"></div>
        </div>
      </div>
    </div>
    <!-- Job Type Employees Section -->
    <div class="row mb-4">
      <div class="col-12">
        <div style="text-align: center; margin-bottom: 2.5rem;">
          <h3 style="font-weight: 600; font-size: 2.2rem; margin: 1rem 0rem; color: #000000;">Employees by Job Type</h3>
          <p style="color: #6b7280; font-size: 1.1rem; margin: 0;">Overview of employees categorized by their employment type</p>
        </div>
      </div>
      <div class="col-md-4 col-12 mb-3">
        <div style="width: 90%; background: white; border-radius: 16px; box-shadow: 0px 1px 6px -1px rgba(0, 0, 0, 0.1); padding: 0.75rem; min-height: 320px;">
          <div style="width: 100%; display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
            <h5 style="font-weight: 700; color: #000000; font-size: 1.1rem; margin: 0;">
              <i class="fas fa-graduation-cap me-2" style="color: #00bfa5;"></i>
              Internship
            </h5>
            <span id="internshipCount" style="background: linear-gradient(135deg, #00bfa5, #02d6ba); color: white; padding: 0.4rem 0.8rem; border-radius: 15px; font-weight: 600; font-size: 0.8rem;">0</span>
          </div>
          <div id="internshipEmployeesList" style="max-height: 245px; overflow-y: auto;"></div>
        </div>
      </div>
      <div class="col-md-4 col-12 mb-3">
        <div style="width: 90%; background: white; border-radius: 16px; box-shadow: 0px 1px 6px -1px rgba(0, 0, 0, 0.1); padding: 0.75rem; min-height: 320px;">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
            <h5 style="font-weight: 700; color: #000000; font-size: 1.1rem; margin: 0;">
              <i class="fas fa-clock me-2" style="color: #00bfa5;"></i>
              Probation
            </h5>
            <span id="probationCount" style="background: linear-gradient(135deg, #00bfa5, #02d6ba); color: white; padding: 0.4rem 0.8rem; border-radius: 15px; font-weight: 600; font-size: 0.8rem;">0</span>
          </div>
          <div id="probationEmployeesList" style="max-height: 245px; overflow-y: auto;"></div>
        </div>
      </div>
      <div class="col-md-4 col-12 mb-3">
        <div style="width:90%; background: white; border-radius: 16px; box-shadow: 0px 1px 6px -1px rgba(0, 0, 0, 0.1); padding: 0.75rem; min-height: 320px;">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
            <h5 style="font-weight: 700; color: #000000; font-size: 1.1rem; margin: 0;">
              <i class="fas fa-user-tie me-2" style="color: #00bfa5;"></i>
              Permanent
            </h5>
            <span id="permanentCount" style="background: linear-gradient(135deg, #00bfa5, #02d6ba); color: white; padding: 0.4rem 0.8rem; border-radius: 15px; font-weight: 600; font-size: 0.8rem;">0</span>
          </div>
          <div id="permanentEmployeesList" style="max-height: 245px; overflow-y: auto;"></div>
        </div>
      </div>
    </div>
    <!-- Department Wise Employees Donut Chart + Summary (Reliable 2-Column Layout) -->
    <div class="row mb-4">
      <div class="col-12">
        <div style="text-align: center; margin-bottom: 2.5rem;">
          <h3 style="font-weight: 600; font-size: 2.2rem; margin: 1rem 0rem; color: #000000;">Department Wise Employees & Their Salaries</h3>
          <p style="color: #6b7280; font-size: 1.1rem; margin: 0;">Comprehensive overview of employee distribution and salary allocation across departments</p>
        </div>
      </div>
      <div class="col-md-6 col-12">
        <div style="background: white; border-radius: 16px; box-shadow: 0px 1px 6px -1px rgba(0, 0, 0, 0.1); padding: 1.5rem; min-height: 600px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
          <div id="deptEmpDonutChart" style="width: 100%; max-width: 450px; height: 350px; display: flex; align-items: center; justify-content: center;"></div>
        </div>
      </div>
      <div class="col-md-6 col-12">
        <div id="deptEmpSummaryBox" style="width: 100%; height: 600px; background: white; border-radius: 16px; box-shadow:0px 1px 6px -1px rgba(0, 0, 0, 0.1); padding: 2rem; overflow-y: auto;"></div>
      </div>
    </div>
    <!-- Salary Distribution Chart Row (2 columns) -->
    <div class="row mb-4 align-items-stretch">
      <div class="col-md-6">
        <div class="chart-card h-100" style="background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb; overflow: hidden;">
          <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb;">
            <h5 class="chart-title mb-0" style="font-weight: 700; color: #000000; font-size: 1.25rem;">
              <i class="fas fa-chart-bar me-2" style="color: #00bfa5;"></i>
              Monthly Salary Overview
            </h5>
          </div>
          <div id="salaryDeptChart" style="min-height: 350px; padding: 0.5rem;"></div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="chart-card h-100" style="background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb; overflow: hidden;">
          <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb;">
            <h5 class="chart-title mb-0" style="font-weight: 700; color: #000000; font-size: 1.25rem;">
              <i class="fas fa-user-group" style="color: #ec4899;"></i>
              Gender Distribution
            </h5>
          </div>
          <div id="genderDistributionContainer" style="padding: 0.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; min-height: 350px; align-items: center;">
            <div id="genderDonutChart" style="min-height: 350px; display: flex; align-items: center; justify-content: center;"></div>
            <div id="genderStatsBox" style="display: flex; flex-direction: column; justify-content: center; gap: 1rem;"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="row mb-4 align-items-stretch">
      <div class="col-md-6">
        <div class="chart-card h-100" style="background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb; overflow: hidden;">
          <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb;">
            <h5 class="chart-title mb-0" style="font-weight: 700; color: #000000; font-size: 1.25rem;">
              <i class="fas fa-chart-line me-2" style="color: #10b981;"></i>
              Employee Joining & Exit Trend
            </h5>
          </div>
          <div id="employeeLineChart" style="min-height: 350px; padding: 0.5rem;"></div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="chart-card h-100" style="background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb; overflow: hidden;">
          <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb;">
            <h5 class="chart-title mb-0" style="font-weight: 700; color: #000000; font-size: 1.25rem;">
              <i class="fas fa-calendar-times me-2" style="color: #f59e0b;"></i>
              Leave Requests Overview
            </h5>
          </div>
          <div id="leaveRequestsChart" style="min-height: 350px; padding: 0.5rem;"></div>
        </div>
      </div>
    </div>
  </div>
</main>
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
                        <img id="viewEmployeeProfileImg"
                            src="../assets/images/default-avatar.jpg"
                            class="profile-image" alt="User Avatar">
                    </div>
                </div>
                <div class="text-center mb-4">
                    <h4 class="mb-1 fw-bold" id="viewEmployeeFullName">Employee Name</h4>
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
                                                class="fas fa-person info-icon"></i> Gender</label>
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
                                                class="fas fa-id-card info-icon"></i> CNIC</label>
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
                                                class="fas fa-user-friends info-icon"></i> Emergency Contact Relation</label>
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
                                <div class="col-sm-12 mb-3">
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
                                                class="fas fa-user-tie info-icon"></i> Last Job Title</label>
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
                                        <label class="info-label" id="approvedLeavesLabel"><i class="fas fa-calendar-times info-icon"></i> Approved Leaves</label>
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
<!-- Total Employees Modal -->
<div class="modal fade" id="totalEmployeesModal" tabindex="-1" aria-labelledby="totalEmployeesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: #00bfa5; color: white; border-radius: 12px 12px 0 0;">
                <h5 class="modal-title" id="totalEmployeesModalLabel">
                    <i class="fas fa-users me-2"></i>All Employees
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filter Section -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="departmentFilter" class="form-label fw-semibold">
                            <i class="fas fa-filter me-1"></i>Filter by Department:
                        </label>
                        <select class="form-select" id="departmentFilter" onchange="filterEmployeesByDepartment()">
                            <option value="">All Departments</option>
                            <!-- Departments will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-primary w-100 " onclick="clearDepartmentFilter()">
                            <i class="fas fa-times me-1"></i>Clear Filter
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: #f8fafc;">
                            <tr>
                                <th></i>Name</th>
                                <th></i>Email</th>
                                <th></i>Department</th>
                            </tr>
                        </thead>
                        <tbody id="totalEmployeesTableBody">
                            <tr>
                                <td colspan="3" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading employees...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Present Today Modal -->
<div class="modal fade" id="presentTodayModal" tabindex="-1" aria-labelledby="presentTodayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: #00bfa5; color: white; border-radius: 12px 12px 0 0;">
                <h5 class="modal-title" id="presentTodayModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Present Today
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filter Section -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="presentDepartmentFilter" class="form-label fw-semibold">
                            <i class="fas fa-filter me-1"></i>Filter by Department:
                        </label>
                        <select class="form-select" id="presentDepartmentFilter" onchange="filterPresentEmployeesByDepartment()">
                            <option value="">All Departments</option>
                            <!-- Departments will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-primary w-100" onclick="clearPresentDepartmentFilter()">
                            <i class="fas fa-times me-1"></i>Clear Filter
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: #f8fafc;">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Check In</th>
                            </tr>
                        </thead>
                        <tbody id="presentTodayTableBody">
                            <tr>
                                <td colspan="4" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading present employees...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- On Leave Modal -->
<div class="modal fade" id="onLeaveModal" tabindex="-1" aria-labelledby="onLeaveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: #00bfa5; color: white; border-radius: 12px 12px 0 0;">
                <h5 class="modal-title" id="onLeaveModalLabel">
                    <i class="fas fa-user-clock me-2"></i>Employees on Leave
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filter Section -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="leaveDepartmentFilter" class="form-label fw-semibold">
                            <i class="fas fa-filter me-1"></i>Filter by Department:
                        </label>
                        <select class="form-select" id="leaveDepartmentFilter" onchange="filterLeaveEmployeesByDepartment()">
                            <option value="">All Departments</option>
                            <!-- Departments will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-primary w-100" onclick="clearLeaveDepartmentFilter()">
                            <i class="fas fa-times me-1"></i>Clear Filter
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: #f8fafc;">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Leave Period</th>
                                <th>Days</th>
                            </tr>
                        </thead>
                        <tbody id="onLeaveTableBody">
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading employees on leave...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Departments Modal -->
<div class="modal fade" id="departmentsModal" tabindex="-1" aria-labelledby="departmentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #00bfa5; color: white; border-radius: 12px 12px 0 0;">
                <h5 class="modal-title" id="departmentsModalLabel">
                    <i class="fas fa-building me-2"></i>All Departments
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: #f8fafc;">
                            <tr>
                                <th>Department Name</th>
                                <th>Employee Count</th>
                                <th>Department Manager</th>
                                <th>Department Head</th>
                            </tr>
                        </thead>
                        <tbody id="departmentsTableBody">
                            <tr>
                                <td colspan="4" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading departments...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "../admin/footer.php" ?>
<!-- Department Employees Modal -->
<div class="modal fade" id="departmentEmployeesModal" tabindex="-1" aria-labelledby="departmentEmployeesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #00bfa5; border-bottom: none;">
        <h5 class="modal-title text-white" id="departmentEmployeesModalLabel">
          <i class="fas fa-users me-2"></i>Department Employees
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="departmentEmployeesContent">
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading employees...</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="include/js/dashboard.js?v=<?php echo time(); ?>"></script>


