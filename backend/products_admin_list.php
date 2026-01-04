<?php
// backend/products_admin_list.php
session_start();

header('Content-Type: application/json');

// Simple admin check
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Admin only"]);
    exit;
}

$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "garderobe_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "DB error"]);
    exit;
}
$conn->set_charset("utf8mb4");

// Get all products (latest first)
$sql = "SELECT id, name, category, price, old_price, image, views
        FROM products
        ORDER BY id DESC";

$result = $conn->query($sql);
$products = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $result->free();
}

echo json_encode([
    "success"  => true,
    "products" => $products
]);

$conn->close();
