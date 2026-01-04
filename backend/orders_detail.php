<?php
// backend/orders_detail.php
require_once __DIR__ . "/db.php";
header("Content-Type: application/json; charset=utf-8");

$id = intval($_GET["id"] ?? 0);
if ($id <= 0) {
  echo json_encode(["success" => false, "message" => "Invalid id"]);
  exit;
}

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
  echo json_encode(["success" => false, "message" => "Order not found"]);
  exit;
}

$stmt2 = $conn->prepare("SELECT product_id, product_name, product_price, qty, product_image
                         FROM order_items
                         WHERE order_id = ?
                         ORDER BY id ASC");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$res2 = $stmt2->get_result();

$items = [];
while ($row = $res2->fetch_assoc()) $items[] = $row;
$stmt2->close();

echo json_encode(["success" => true, "order" => $order, "items" => $items]);
