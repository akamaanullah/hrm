<?php
include "header.php";
include "top-bar.php";
include "sidebar.php";
// Half-day data fetch karo attendance se aur payroll mein update karo
require_once "../config.php";
// Current month aur year
$current_month = date('m');
$current_year = date('Y');
// Attendance se half-day data fetch karo
$halfday_sql = "SELECT emp_id, COUNT(*) as halfday_count 
                FROM attendance 
                WHERE MONTH(check_in) = ? AND YEAR(check_in) = ? AND status = 'half-day' 
                GROUP BY emp_id";
$halfday_stmt = $pdo->prepare($halfday_sql);
$halfday_stmt->execute([$current_month, $current_year]);
// Har employee ke liye half-day data update karo
while ($row = $halfday_stmt->fetch()) {
    $emp_id = $row['emp_id'];
    $halfday_count = $row['halfday_count'];

    // Payroll table mein half_day_days update karo
    $update_sql = "UPDATE payroll SET half_day_days = ? WHERE emp_id = ? AND month = ? AND year = ?";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([$halfday_count, $emp_id, $current_month, $current_year]);
}
?>
<!-- Main Content -->
<main class="main-content">
    <div class="container-fluid px-3 px-lg-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Payroll Management</h1>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#autoPayslipModal">
                    <i class="fas fa-magic me-1"></i> Auto Generate Payslip
                </button>
                <div id="exportButtonContainer"></div>
            </div>
        </div>
        <!-- Filters Section -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="payrollFilterForm" class="row">
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
                    <!-- Designation Filter -->
                    <div class="col-12 col-md-6 col-lg">
                        <label class="form-label">Designation</label>
                        <input type="text" class="form-control" id="designationFilter" placeholder="Enter Designation">
                    </div>
                    <!-- Month Year Picker -->
                    <div class="col-12 col-md-6 col-lg">
                        <label class="form-label fw-semibold">Select Month & Year</label>
                        <div class="input-group">
                            <input type="text" class="form-control border-2" id="monthYearPicker" placeholder="Click to select month & year" readonly style="cursor: pointer;">
                            <button class="btn btn-primary" type="button" id="monthYearBtn" style="border-left: none;">
                                <i class="fas fa-calendar-alt"></i>
                            </button>
                        </div>
                        <!-- Hidden inputs for filter values -->
                        <input type="hidden" id="selectedMonth" value="">
                        <input type="hidden" id="selectedYear" value="">
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
        <!-- Payroll Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="payrollTable">
                        <thead>
                            <tr>
                                <th>Emp ID</th>
                                <th>Name</th>
                                <th>Job Title</th>
                                <th>Basic Salary</th>
                                <!-- <th>Fuel Allowance</th>
                                <th>House Rent Allowance</th>
                                <th>Utility Allowance</th>
                                <th>Mobile Allowance</th>
                                <th>Medical Allowance</th>
                                <th>Provident Fund</th>
                                <th>Professional Tax</th>
                                <th>Loan</th> -->
                                <th>Total Leaves</th>
                                <th>Total Late</th>
                                <th>Total Half-day</th>
                                <th>Total Earnings</th>
                                <th>Total deductions</th>
                                <th>Net Salary</th>
                                <th>Bank</th>
                                <th>Date of Payment</th>
                                <th>Payslip</th>
                                <th>Actions</th>
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
<!-- Edit Payroll Modal -->
<div class="modal" id="editPayrollModal" tabindex="-1" role="dialog" aria-labelledby="editPayrollModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPayrollModalLabel">Edit Payroll</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editPayrollForm">
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Employee ID</label>
                            <input type="text" class="form-control" id="editEmployeeId" readonly>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Select Month</label>
                            <select class="form-select" id="editMonth">
                                <option value="01">January</option>
                                <option value="02">February</option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Basic Salary</label>
                            <input type="number" class="form-control" id="editBasicSalary" readonly>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Fuel Allowance</label>
                            <input type="number" class="form-control" id="editFuelAllowance" readonly>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">House Rent Allowance</label>
                            <input type="number" class="form-control" id="editHouseRentAllowance" readonly>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Utility Allowance</label>
                            <input type="number" class="form-control" id="editUtilityAllowance" readonly>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Mobile Allowance</label>
                            <input type="number" class="form-control" id="editMobileAllowance" readonly>
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Provident Fund</label>
                            <input type="number" class="form-control" id="editProvidentFund">
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Loan</label>
                            <input type="number" class="form-control" id="editLoan">
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Total Leaves</label>
                            <input type="number" class="form-control" id="editTotalLeaves">
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Total Late</label>
                            <input type="number" class="form-control" id="editTotalLate">
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Total Half-day</label>
                            <input type="number" class="form-control" id="editTotalHalfday">
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Bank</label>
                            <select class="form-select" id="editBank">
                                <option value="">Select Bank</option>
                                <option value="HBL">HBL</option>
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
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Payment Date</label>
                            <input type="date" class="form-control" id="editPaymentDate">
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Professional Tax</label>
                            <input type="number" class="form-control" id="editProfessionalTax">
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Total Earnings</label>
                            <input type="number" class="form-control" id="editTotalEarnings" readonly>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Net Salary</label>
                            <input type="number" class="form-control" id="editNetSalary" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- View Payslip Modal -->
<div class="modal" id="viewPayslipModal" tabindex="-1" role="dialog" aria-labelledby="viewPayslipModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="payslipContent">
                <div class="text-end mb-3">
                    <button class="btn btn-primary" onclick="printPayslip()" aria-label="Print Payslip">
                        <i class="fas fa-print me-2" aria-hidden="true"></i>Print Payslip
                    </button>
                </div>
                <div class="payslip-container bg-white p-4">
                    <div class="text-center mb-4 border-bottom pb-3">
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <img src="../assets/images/LOGO.png" alt="Company Logo"
                                style="max-height: 40px; margin-right: 15px;">
                            <h3 class="mb-0 fw-bold">Richmond Tech Group</h3>
                        </div>
                        <h5 class="mb-1 fw-bold">PAYSLIP</h5>
                        <p class="mb-1 text-muted">Office # 14, Hillview Apt, Block-D North, Nazimabad</p>
                        <p class="mb-2 text-muted">Karachi, Pakistan</p>
                        <p class="mb-0 text-muted">Tel: +92-330-2764784</p>
                    </div>
                    <div class="employee-info mb-4">
                        <table class="table table-bordered payslip-table">
                            <tr>
                                <td style="width: 20%; background-color: #f8f9fa;"><span
                                        style="color: #495057; font-weight: bold;">EMPLOYEE NAME</span></td>
                                <td style="width: 30%" id="empName"></td>
                                <td style="width: 20%; background-color: #f8f9fa;"><span
                                        style="color: #495057; font-weight: bold;">DATE OF JOINING</span></td>
                                <td style="width: 30%" id="joiningDate"></td>
                            </tr>
                            <tr>
                                <td style="background-color: #f8f9fa;"><span
                                        style="color: #495057; font-weight: bold;">DESIGNATION</span></td>
                                <td id="empDesignation"></td>
                                <td style="background-color: #f8f9fa;"><span
                                        style="color: #495057; font-weight: bold;">PAYMENT DATE</span></td>
                                <td id="payPeriod"></td>
                            </tr>
                            <tr>
                                <td style="background-color: #f8f9fa;"><span
                                        style="color: #495057; font-weight: bold;">DEPARTMENT</span></td>
                                <td id="empDepartment"></td>
                                <td style="background-color: #f8f9fa;"><span
                                        style="color: #495057; font-weight: bold;">BANK</span></td>
                                <td id="empBank"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="salary-details">
                        <table class="table table-bordered payslip-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%"><span class="fw-bold">Earnings</span></th>
                                    <th style="width: 25%"><span class="fw-bold">Amount (PKR)</span></th>
                                    <th style="width: 25%"><span class="fw-bold">Deductions</span></th>
                                    <th style="width: 25%"><span class="fw-bold">Amount (PKR)</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Basic Salary</td>
                                    <td class="text-end" id="basicSalary"></td>
                                    <td>Provident Fund</td>
                                    <td class="text-end" id="providentFund"></td>
                                </tr>
                                <tr>
                                    <td>Fuel Allowance</td>
                                    <td class="text-end" id="fuelAllowance"></td>
                                    <td>Professional Tax</td>
                                    <td class="text-end" id="professionalTax"></td>
                                </tr>
                                <tr>
                                    <td>House Rent Allowance</td>
                                    <td class="text-end" id="houseRentAllowance"></td>
                                    <td>Loan</td>
                                    <td class="text-end" id="loan"></td>
                                </tr>
                                <tr>
                                    <td>Utility Allowance</td>
                                    <td class="text-end" id="utilityAllowance"></td>
                                    <td>Total Leaves</td>
                                    <td class="text-end" id="totalLeaves"></td>
                                </tr>
                                <tr>
                                    <td>Mobile Allowance</td>
                                    <td class="text-end" id="mobileAllowance"></td>
                                    <td>Total Late</td>
                                    <td class="text-end" id="totalLate"></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td>Total Half-day</td>
                                    <td class="text-end" id="totalHalfday"></td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td><strong>Total Earnings</strong></td>
                                    <td class="text-end"><strong id="totalEarnings"></strong></td>
                                    <td><strong>Total Deductions</strong></td>
                                    <td class="text-end"><strong id="totalDeductions"></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><strong>Total Earnings</strong></td>
                                    <td colspan="2" class="text-end"><strong id="grossEarnings"></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><strong>Total Deductions</strong></td>
                                    <td colspan="2" class="text-end"><strong id="totalDeduction"></strong></td>
                                </tr>
                                <tr class="table-primary">
                                    <td colspan="2"><strong>Net Salary</strong></td>
                                    <td colspan="2" class="text-end"><strong id="netSalary"></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="amount-in-words text-center p-3">
                                <h6 class="mb-2 text-muted">Amount: Rs. 180,833.00</h6>
                                <p class="mb-0">One Lac Eighty Thousand Eight Hundred and Thirty Three Rupees Only.</p>
                            </div>
                        </div>
                    </div>
                    <div class="row ">
                        <div class="col-12">
                            <div class="p-3">
                                <p class="mb-2 text-muted text-center">This is a system generated payslip</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- Auto Payslip Modal -->
<div class="modal fade" id="autoPayslipModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Auto Generate Payslip</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Filter Row -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="filterEmpId" placeholder="Filter by Emp ID">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="filterEmpName" placeholder="Filter by Name">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterDepartment">
                            <option value="">All Departments</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <!-- <label class="form-label fw-semibold">Select Month & Year</label> -->
                        <div class="input-group">
                            <input type="text" class="form-control border-2" id="payslipMonthYearPicker" placeholder="Click to select month & year" readonly style="cursor: pointer;">
                            <button class="btn btn-primary" type="button" id="payslipMonthYearBtn" style="border-left: none;">
                                <i class="fas fa-calendar-alt"></i>
                            </button>
                        </div>
                        <!-- Hidden inputs for selected values -->
                        <input type="hidden" id="selectedPayslipMonth" value="">
                        <input type="hidden" id="selectedPayslipYear" value="">
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 650px; overflow-y: auto;">
                    <table class="table table-bordered" id="autoPayslipEmployeeTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAllEmployees"></th>
                                <th>Emp ID</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Department</th>
                                <th>Salary</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <button class="btn btn-primary mt-3" id="generatePayslipBtn">Generate Payslip</button>
            </div>
        </div>
    </div>
</div>
<!-- Month Year Picker Modal -->
<div class="modal fade" id="monthYearPickerModal" tabindex="-1" aria-labelledby="monthYearPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h6 class="modal-title" id="monthYearPickerModalLabel">
                    <i class="fas fa-calendar-alt me-2"></i>Select Month & Year
                </h6>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Year Selection -->
                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-center">
                        <button class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" id="yearPrevBtn" style="width: 20px; height: 20px;">
                            <i class="fas fa-chevron-left" style="font-size: 10px;"></i>
                        </button>
                        <div class="mx-4">
                            <span class="fs-4 fw-bold text-dark" id="currentYear">2025</span>
                        </div>
                        <button class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" id="yearNextBtn" style="width: 20px; height: 20px;">
                            <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
                        </button>
                    </div>
                </div>
                <!-- Month Selection Grid -->
                <div>
                    <div class="row g-2">
                        <div class="col-4">
                            <button class="btn btn-light w-100 month-btn border-0 py-2 " data-month="01" style="border-radius: 3px;">
                                <small>JAN</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 month-btn border-0 py-2 " data-month="02" style="border-radius: 3px;">
                                <small>FEB</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 month-btn border-0 py-2 " data-month="03" style="border-radius: 3px;">
                                <small>MAR</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 month-btn border-0 py-2 " data-month="04" style="border-radius: 3px;">
                                <small>APR</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 month-btn border-0 py-2 " data-month="05" style="border-radius: 3px;">
                                <small>MAY</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 month-btn border-0 py-2 " data-month="06" style="border-radius: 3px;">
                                <small>JUN</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 month-btn border-0 py-2 " data-month="07" style="border-radius: 3px;">
                                <small>JUL</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 month-btn border-0 py-2 " data-month="08" style="border-radius: 3px;">
                                <small>AUG</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 month-btn border-0 py-2 " data-month="09" style="border-radius: 3px;">
                                <small>SEP</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 month-btn border-0 py-2 " data-month="10" style="border-radius: 3px;">
                                <small>OCT</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 month-btn border-0 py-2 " data-month="11" style="border-radius: 3px;">
                                <small>NOV</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 month-btn border-0 py-2 " data-month="12" style="border-radius: 3px;">
                                <small>DEC</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <!-- <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button> -->
                <button type="button" class="btn btn-primary" id="applyMonthYear">
                    <i class="fas fa-check me-1"></i>Apply Filter
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payslip Month Year Picker Modal -->
<div class="modal fade month-year-picker-modal" id="payslipMonthYearPickerModal" tabindex="-1" aria-labelledby="payslipMonthYearPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h6 class="modal-title text-dark" id="payslipMonthYearPickerModalLabel">
                    <i class="fas fa-calendar-alt me-2"></i>Select Month & Year
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Year Selection -->
                <div class="year-navigation">
                    <div class="d-flex align-items-center justify-content-center">
                        <button class="btn btn-outline-secondary year-nav-btn" id="payslipYearPrevBtn">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="mx-4">
                            <span class="year-display" id="payslipCurrentYear">2025</span>
                        </div>
                        <button class="btn btn-outline-secondary year-nav-btn" id="payslipYearNextBtn">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <!-- Month Selection Grid -->
                <div>
                    <div class="row g-2">
                        <div class="col-4">
                            <button class="btn btn-light w-100 payslip-month-btn border-0 py-2 " data-month="01" style="border-radius: 3px;">
                                <small>JAN</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 payslip-month-btn border-0 py-2 " data-month="02" style="border-radius: 3px;">
                                <small>FEB</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 payslip-month-btn border-0 py-2 " data-month="03" style="border-radius: 3px;">
                                <small>MAR</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 payslip-month-btn border-0 py-2 " data-month="04" style="border-radius: 3px;">
                                <small>APR</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 payslip-month-btn border-0 py-2 " data-month="05" style="border-radius: 3px;">
                                <small>MAY</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 payslip-month-btn border-0 py-2 " data-month="06" style="border-radius: 3px;">
                                <small>JUN</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 payslip-month-btn border-0 py-2 " data-month="07" style="border-radius: 3px;">
                                <small>JUL</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 payslip-month-btn border-0 py-2 " data-month="08" style="border-radius: 3px;">
                                <small>AUG</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 payslip-month-btn border-0 py-2 " data-month="09" style="border-radius: 3px;">
                                <small>SEP</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 payslip-month-btn border-0 py-2 " data-month="10" style="border-radius: 3px;">
                                <small>OCT</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 payslip-month-btn border-0 py-2 " data-month="11" style="border-radius: 3px;">
                                <small>NOV</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light w-100 payslip-month-btn border-0 py-2 " data-month="12" style="border-radius: 3px;">
                                <small>DEC</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-primary apply-btn" id="applyPayslipMonthYear">
                    <i class="fas fa-check me-1"></i>Apply Selection
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Toast Notification -->
<div id="payrollToast" class="toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3"
    role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 9999; min-width: 250px;">
    <div class="d-flex">
        <div class="toast-body" id="payrollToastMsg"></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
            aria-label="Close"></button>
    </div>
</div>
<?php include "footer.php" ?>
<script src="include/js/payroll.js"></script>
<script src="include/js/auto-payslip.js"></script>