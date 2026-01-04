<?php
// backend/db.php
$host = "localhost";
$user = "root";      // default XAMPP
$pass = "";          // default XAMPP
$dbname = "garderobe_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

$conn->set_charset("utf8mb4");

//session_start(); // session for auth
header("Content-Type: application/json");
