<?php
session_start();

// remove only admin data (optional; session_destroy is also okay)
unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email'], $_SESSION['is_admin']);

session_destroy();

header("Location: ../admin-login.html");
exit;
