<?php
// DEBUG LOGIN - REMOVE THIS FILE AFTER USE
// Creates a temporary admin session for local debugging only.
if (session_status() === PHP_SESSION_NONE) session_start();

// Minimal session values to satisfy `isLoggedIn()` and `hasRole()` checks
$_SESSION['user_id'] = 999999;
$_SESSION['user_name'] = 'Debug Admin';
$_SESSION['user_roles'] = ['super_admin'];
$_SESSION['primary_role'] = 'super_admin';
$_SESSION['user_type'] = 'admin';

// Redirect to dashboard
header('Location: dashboard.php');
exit;
