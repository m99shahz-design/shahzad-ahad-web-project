<?php
// backend/orders_list.php
require_once __DIR__ . "/db.php";
header("Content-Type: application/json; charset=utf-8");

$res = $conn->query("SELECT id, customer_name, customer_email, customer_phone, total, status, created_at
                     FROM orders
                     ORDER BY id DESC");

$rows = [];
while ($row = $res->fetch_assoc()) $rows[] = $row;

echo json_encode(["success" => true, "data" => $rows]);
