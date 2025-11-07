<?php 
include 'session_check.php'; 
include 'header.php'; 
include 'topbar.php'; 
include 'sidebar.php'; 
?>
<!-- Main Content -->
<main class="main-content">
    <div class="container-fluid px-3 px-lg-4">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mb-4">
            <h4 class="mb-0" style="color: #2d3436; font-size: 1.75rem;">My Dashboard</h4>
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
                </div>
            </div>
        </div>
        <!-- Quick Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stats-card h-100 clickable-card" id="todayStatusCard" style="background: white; border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 12px; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 15px -3px rgba(0, 0, 0, 0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)'">
                    <div class="stats-icon" style="background: #e0f2fe; color: #00bfa5; border-radius: 8px; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <div class="stats-title" style="color: #636e72; font-size: 0.9rem; font-weight: 500; margin-bottom: 0.5rem;">Today's Status</div>
                    <div id="todayStatusDetails" style="color: #00bfa5; font-size: 1rem; font-weight: 600; margin-top: 0.5rem;">
                        <span id="checkInTime">Check-in: --:--</span>
                    </div>
                    <div class="stats-value" id="todayStatus" style="color: #2d3436; font-size: 2rem; font-weight: 700;">--</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stats-card h-100 clickable-card" id="monthlyAttendanceCard" style="background: white; border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 12px; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 15px -3px rgba(0, 0, 0, 0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)'">
                    <div class="stats-icon" style="background: #dcfce7; color: #10b981; border-radius: 8px; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stats-title" style="color: #636e72; font-size: 0.9rem; font-weight: 500; margin-bottom: 0.5rem;">Monthly Attendance</div>
                    <div class="stats-value" id="monthAttendancePercent" style="color: #2d3436; font-size: 2rem; font-weight: 700;">--%</div>
                    <div id="monthAttendanceDetails" style="color: #10b981; font-size: 0.85rem; font-weight: 600; margin-top: 0.5rem;">
                        <span id="monthPresent">Present: 0</span> / <span id="monthTotal">Total: 0</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stats-card h-100 clickable-card" id="totalLeaveCard" style="background: white; border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 12px; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 15px -3px rgba(0, 0, 0, 0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)'">
                    <div class="stats-icon" style="background: #fef3c7; color: #f59e0b; border-radius: 8px; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <div class="stats-title" style="color: #636e72; font-size: 0.9rem; font-weight: 500; margin-bottom: 0.5rem;">Total Leave</div>
                    <div class="stats-value" id="leaveBalance" style="color: #2d3436; font-size: 2rem; font-weight: 700;">--</div>
                    <div id="leaveBalanceDetails" style="color: #f59e0b; font-size: 0.85rem; font-weight: 600; margin-top: 0.5rem;">
                        <span id="annualLeaves">Annual: 0</span> | <span id="sickLeaves">Sick: 0</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stats-card h-100 clickable-card" id="departmentCard" style="background: white; border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 12px; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 15px -3px rgba(0, 0, 0, 0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)'">
                    <div class="stats-icon" style="background: #e0f2fe; color: #00bfa5; border-radius: 8px; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stats-title" style="color: #636e72; font-size: 0.9rem; font-weight: 500; margin-bottom: 0.5rem;">Department</div>
                    <div class="stats-value" id="userDepartment" style="color: #2d3436; font-size: 1.5rem; font-weight: 700;">--</div>
                    <div id="departmentDetails" style="color: #00bfa5; font-size: 0.85rem; font-weight: 600; margin-top: 0.5rem;">
                        <span id="userDesignation">Job Title: --</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Quick Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;">
                    <a href="userattendance.php" class="btn" style="background: linear-gradient(135deg, #00bfa5, #02d6ba); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; transition: transform 0.2s ease; box-shadow: 0 2px 4px rgba(0, 191, 165, 0.3); text-decoration: none; display: inline-block;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0, 191, 165, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0, 191, 165, 0.3)'">
                        <i class="fas fa-clock me-2"></i>My Attendance
                    </a>
                    <a href="leave-history.php" class="btn" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; transition: transform 0.2s ease; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3); text-decoration: none; display: inline-block;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(16, 185, 129, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(16, 185, 129, 0.3)'">
                        <i class="fas fa-calendar-times me-2"></i>Leave History
                    </a>
                    <a href="user-payroll-salary.php" class="btn" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; transition: transform 0.2s ease; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3); text-decoration: none; display: inline-block;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(245, 158, 11, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(245, 158, 11, 0.3)'">
                        <i class="fas fa-money-bill-wave me-2"></i>Payroll
                    </a>
                    <a href="userannouncement.php" class="btn" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; transition: transform 0.2s ease; box-shadow: 0 2px 4px rgba(139, 92, 246, 0.3); text-decoration: none; display: inline-block;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(139, 92, 246, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(139, 92, 246, 0.3)'">
                        <i class="fas fa-bullhorn me-2"></i>Announcements
                    </a>
                </div>
            </div>
        </div>  
        <!-- Notifications Row -->
        <div class="row mb-4 align-items-stretch">
            <div class="col-12">
                <div class="chart-card h-100" style="background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb; overflow: hidden;">
                    <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; gap:12px;">
                        <h5 class="chart-title mb-0" style="font-weight: 700; color: #2d3436; font-size: 1.25rem; display:flex; align-items:center; gap:8px;">
                            <i class="fas fa-bell" style="color: #f59e0b;"></i>
                            Announcements
                        </h5>
                        <a href="userannouncement.php" style="background: var(--button-color); color:#fff; text-decoration:none; padding: 0.4rem 0.9rem; border-radius: 8px; font-weight:600; font-size:0.85rem;">View</a>
                    </div>
                    <div id="notificationsBox" style="padding: 1rem; max-height: 400px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
        <!-- Analytics Charts Row -->
        <div class="row mb-4 align-items-stretch">
            <div class="col-md-6">
                <div class="chart-card h-100" style="background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb; overflow: hidden;">
                    <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb;">
                        <h5 class="chart-title mb-0" style="font-weight: 700; color: #2d3436; font-size: 1.25rem;">
                            <i class="fas fa-chart-pie me-2" style="color: #00bfa5;"></i>
                            Monthly Attendance Overview
                        </h5>
                    </div>
                    <div class="row" style="padding: 0.5rem;">
                        <div class="col-12">
                            <div id="attendanceDonutChart" style="min-height: 250px; margin-bottom: 1rem;"></div>
                        </div>
                        <div class="col-12" id="attendanceStatsBox"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-card h-100" style="background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb; overflow: hidden;">
                    <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb;">
                        <h5 class="chart-title mb-0" style="font-weight: 700; color: #2d3436; font-size: 1.25rem;">
                            <i class="fas fa-chart-line me-2" style="color: #10b981;"></i>
                            Salary Trend
                        </h5>
                    </div>
                    <div id="salaryTrendChart" style="min-height: 350px; padding: 0.5rem;"></div>
                </div>
            </div>
        </div>
        <!-- Leave Analytics & Work Hours Row -->
        <div class="row mb-4 align-items-stretch">
            <div class="col-md-6">
                <div class="chart-card h-100" style="background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb; overflow: hidden;">
                    <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb;">
                        <h5 class="chart-title mb-0" style="font-weight: 700; color: #2d3436; font-size: 1.25rem;">
                            <i class="fas fa-calendar-times me-2" style="color: #f59e0b;"></i>
                            Leave Usage Analytics
                        </h5>
                    </div>
                    <div id="leaveAnalyticsChart" style="min-height: 350px; padding: 0.5rem;"></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-card h-100" style="background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb; overflow: hidden;">
                    <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb;">
                        <h5 class="chart-title mb-0" style="font-weight: 700; color: #2d3436; font-size: 1.25rem;">
                            <i class="fas fa-clock me-2" style="color: #8b5cf6;"></i>
                            Work Hours Analysis
                        </h5>
                    </div>
                    <div id="workHoursChart" style="min-height: 350px; padding: 0.5rem;"></div>
                </div>
            </div>
        </div>
    </div>
</main>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Chart.js for Charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Highcharts for Advanced Charts -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<!-- Dashboard JavaScript -->
<script src="include/js/dashboard.js"></script>
<?php include 'footer.php'; ?>