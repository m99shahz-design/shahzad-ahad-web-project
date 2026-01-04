<?php
// backend/orders_update_status.php
require_once __DIR__ . "/db.php";
header("Content-Type: application/json; charset=utf-8");

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$id = intval($data["id"] ?? 0);
$status = $data["status"] ?? "";

$allowed = ["pending","paid","processing","shipped","delivered","cancelled"];

if ($id <= 0 || !in_array($status, $allowed, true)) {
  echo json_encode(["success" => false, "message" => "Invalid data"]);
  exit;
}

$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);
$stmt->execute();
$stmt->close();

echo json_encode(["success" => true]);
