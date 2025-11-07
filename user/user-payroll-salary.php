<?php
session_start();
if (!isset($_SESSION['emp_id'])) {
    header('Location: login.php');
    exit;
}
require_once "../config.php";
?>
<?php include 'header.php'; ?>
<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>
<!-- Main Content -->
<main class="main-content">
    <div class="container-fluid px-3 px-lg-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Payroll Management</h1>
        </div>
        <!-- Payroll Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="payrollTable">
                        <thead>
                            <tr>
                                <th>Emp id</th>
                                <th>Name</th>
                                <th>Job Title</th>
                                <th>Basic Salary</th>
                                <th>Total Leave</th>
                                <th>Total Late</th>
                                <th>Total Half-day</th>
                                <th>Total Earnings</th>
                                <th>Total Deductions</th>
                                <th>Net Salary</th>
                                <th>Bank</th>
                                <th>Date of Payment</th>
                                <th>Payslip</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded by AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div> 
    </div>
</main>
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
                            <img src="../assets/images/LOGO.png" alt="Company Logo" style="max-height: 40px; margin-right: 15px;">
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
                                <td style="width: 20%; background-color: #f8f9fa;"><span style="color: #495057; font-weight: bold;">EMPLOYEE NAME</span></td>
                                <td style="width: 30%" id="empName"></td>
                                <td style="width: 20%; background-color: #f8f9fa;"><span style="color: #495057; font-weight: bold;">DATE OF JOINING</span></td>
                                <td style="width: 30%" id="joiningDate"></td>
                            </tr>
                            <tr>
                                <td style="background-color: #f8f9fa;"><span style="color: #495057; font-weight: bold;">DESIGNATION</span></td>
                                <td id="empDesignation"></td>
                                <td style="background-color: #f8f9fa;"><span style="color: #495057; font-weight: bold;">PAYMENT DATE</span></td>
                                <td id="payPeriod"></td>
                            </tr>
                            <tr>
                                <td style="background-color: #f8f9fa;"><span style="color: #495057; font-weight: bold;">DEPARTMENT</span></td>
                                <td id="empDepartment"></td>
                                <td style="background-color: #f8f9fa;"><span style="color: #495057; font-weight: bold;">BANK</span></td>
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
                    <div class="row mt-4">
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
<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="include/js/user-payroll-salary.js"></script>