<?php
// backend/orders_create.php
require_once __DIR__ . "/db.php";
header("Content-Type: application/json; charset=utf-8");

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data || !isset($data["items"]) || !is_array($data["items"]) || count($data["items"]) === 0) {
  echo json_encode(["success" => false, "message" => "Cart is empty"]);
  exit;
}

$customer_name = trim($data["customer_name"] ?? "Guest");
$customer_email = trim($data["customer_email"] ?? "");
$customer_phone = trim($data["customer_phone"] ?? "");
$customer_address = trim($data["customer_address"] ?? "");

$items = $data["items"];

$subtotal = 0;
foreach ($items as $it) {
  $price = floatval($it["price"] ?? 0);
  $qty = intval($it["qty"] ?? 1);
  if ($qty <= 0) $qty = 1;
  $subtotal += ($price * $qty);
}
$total = $subtotal;

$conn->begin_transaction();

try {
  // insert order
  $stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, customer_address, subtotal, total, status)
                          VALUES (?, ?, ?, ?, ?, ?, 'pending')");
  $stmt->bind_param("ssssdd", $customer_name, $customer_email, $customer_phone, $customer_address, $subtotal, $total);
  $stmt->execute();
  $orderId = $stmt->insert_id;
  $stmt->close();

  // insert items
  $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_price, qty, product_image)
                           VALUES (?, ?, ?, ?, ?, ?)");
  foreach ($items as $it) {
    $pid = isset($it["id"]) ? intval($it["id"]) : null;
    $name = trim($it["name"] ?? "Product");
    $price = floatval($it["price"] ?? 0);
    $qty = intval($it["qty"] ?? 1);
    if ($qty <= 0) $qty = 1;

    $img = $it["image"] ?? "";
    // if front-end sends "images/x.jpg", keep only filename
    $img = str_replace("images/", "", $img);

    $stmt2->bind_param("iisdis", $orderId, $pid, $name, $price, $qty, $img);
    $stmt2->execute();
  }
  $stmt2->close();

  $conn->commit();

  echo json_encode(["success" => true, "order_id" => $orderId]);
  exit;

} catch (Exception $e) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(["success" => false, "message" => "Server error"]);
  exit;
}
