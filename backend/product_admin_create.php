<?php
// backend/product_admin_create.php
session_start();
header('Content-Type: application/json');

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

// Read fields
$name      = trim($_POST['name'] ?? '');
$category  = trim($_POST['category'] ?? '');
$price     = $_POST['price'] ?? '';
$old_price = $_POST['old_price'] ?? null;
$image     = trim($_POST['image'] ?? '');

if ($name === '' || $category === '' || $price === '') {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

$price = floatval($price);
if ($old_price !== null && $old_price !== '') {
    $old_price = floatval($old_price);
} else {
    $old_price = null;
}

$stmt = $conn->prepare(
    "INSERT INTO products (name, price, old_price, category, image, views)
     VALUES (?, ?, ?, ?, ?, 0)"
);
$stmt->bind_param(
    "sdsss",
    $name,
    $price,
    $old_price,
    $category,
    $image
);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "id"      => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Insert failed"
    ]);
}

$stmt->close();
$conn->close();
