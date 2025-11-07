<?php
session_start();
if (!isset($_SESSION['emp_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
    exit;
}
?> 