<?php
require_once __DIR__ . '/../config.php';
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_role']);
session_destroy();
header('Location: login.php');
exit;
