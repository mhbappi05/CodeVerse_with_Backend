<?php
session_start();

function checkAdminAuth() {
    if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
        header("Location: admin-login.php");
        exit();
    }
}
?>