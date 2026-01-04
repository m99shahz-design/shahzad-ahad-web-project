<?php
// backend/product_view.php
require_once __DIR__ . "/db.php";

// Read JSON body OR normal POST
$raw = file_get_contents("php://input");
$json = json_decode($raw, true);

$id = 0;
if (is_array($json) && isset($json["id"])) {
  $id = (int)$json["id"];
} elseif (isset($_POST["id"])) {
  $id = (int)$_POST["id"];
}

if ($id <= 0) {
  echo json_encode(["success" => false, "message" => "Invalid product id"]);
  exit;
}

// increment views
$stmt = $conn->prepare("UPDATE products SET views = views + 1 WHERE id = ?");
$stmt->bind_param("i", $id);
$ok = $stmt->execute();
$stmt->close();

echo json_encode(["success" => $ok]);
