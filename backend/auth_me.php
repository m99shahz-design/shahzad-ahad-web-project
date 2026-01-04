<?php
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["logged_in" => false]);
    exit;
}

echo json_encode([
    "logged_in" => true,
    "id"        => $_SESSION['user_id'],
    "name"      => $_SESSION['user_name'],
    "role"      => $_SESSION['user_role']
]);
