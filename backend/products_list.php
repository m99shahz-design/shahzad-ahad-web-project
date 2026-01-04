<?php
// backend/products_list.php
require_once __DIR__ . "/db.php";

// Always return JSON from this API
header("Content-Type: application/json; charset=utf-8");

$mode = $_GET["mode"] ?? "all";

try {

  // ---------- POPULAR (Most Visited) ----------
  if ($mode === "popular") {

    // ✅ default changed to 4 (Top 4 products only)
    $limit = isset($_GET["limit"]) ? (int)$_GET["limit"] : 4;
    if ($limit <= 0 || $limit > 20) $limit = 4;

    $stmt = $conn->prepare("SELECT id, name, category, price, old_price, image, views
                            FROM products
                            ORDER BY views DESC, id DESC
                            LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    while ($row = $res->fetch_assoc()) $rows[] = $row;

    // fallback if empty
    if (count($rows) === 0) {
      // ✅ use same $limit here too (not hardcoded 8)
      $stmt2 = $conn->prepare("SELECT id, name, category, price, old_price, image, views
                               FROM products
                               ORDER BY id DESC
                               LIMIT ?");
      $stmt2->bind_param("i", $limit);
      $stmt2->execute();
      $res2 = $stmt2->get_result();

      while ($row2 = $res2->fetch_assoc()) $rows[] = $row2;
      $stmt2->close();
    }

    $stmt->close();
    echo json_encode(["success" => true, "data" => $rows]);
    exit;
  }

  // ---------- SINGLE (Product detail page) ----------
  if ($mode === "single") {
    $id = (int)($_GET["id"] ?? 0);

    if ($id <= 0) {
      echo json_encode(["success" => false, "message" => "Invalid id"]);
      exit;
    }

    $stmt = $conn->prepare("SELECT id, name, category, price, old_price, image, views
                            FROM products
                            WHERE id = ?
                            LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if (!$row) {
      echo json_encode(["success" => false, "message" => "Not found"]);
      exit;
    }

    echo json_encode(["success" => true, "data" => $row]);
    exit;
  }

  // ---------- ALL PRODUCTS (Shop page) ----------
  $res = $conn->query("SELECT id, name, category, price, old_price, image, views
                       FROM products
                       ORDER BY id DESC");

  $rows = [];
  while ($row = $res->fetch_assoc()) $rows[] = $row;

  echo json_encode(["success" => true, "data" => $rows]);
  exit;

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(["success" => false, "message" => "Server error"]);
  exit;
}
