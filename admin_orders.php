<?php
session_start();
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin-login.html");
    exit();
}
$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Orders – Shahz&Ahd Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    body{margin:0;font-family:'Inter',system-ui,sans-serif;background:#f3f4f6;color:#111827;}
    .admin-layout{display:flex;min-height:100vh;}
    .admin-sidebar{width:240px;background:#111827;color:#e5e7eb;display:flex;flex-direction:column;}
    .admin-sidebar-header{padding:1.25rem 1.5rem;border-bottom:1px solid rgba(55,65,81,.8);display:flex;gap:.6rem;align-items:center;}
    .admin-sidebar-logo{width:34px;height:34px;border-radius:999px;background:linear-gradient(135deg,#ef4444,#f97316);display:flex;align-items:center;justify-content:center;color:#fff;}
    .admin-sidebar-title{font-weight:600;font-size:1rem;}
    .admin-nav{list-style:none;padding:.75rem 0;margin:0;flex:1;}
    .admin-nav li{margin-bottom:.25rem;}
    .admin-nav a{display:flex;align-items:center;gap:.6rem;padding:.6rem 1.5rem;color:#d1d5db;font-size:.95rem;text-decoration:none;transition:.15s;}
    .admin-nav a i{width:18px;text-align:center;}
    .admin-nav a:hover{background:#1f2937;color:#fff;padding-left:1.8rem;}
    .admin-nav a.active{background:#f97316;color:#fff;}
    .admin-sidebar-footer{padding:.75rem 1.5rem 1.2rem;border-top:1px solid rgba(55,65,81,.8);font-size:.8rem;color:#9ca3af;}
    .admin-main{flex:1;padding:1.5rem 2rem;}
    .admin-main h1{margin-top:0;}
  </style>
</head>
<body>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <div class="admin-sidebar-header">
      <div class="admin-sidebar-logo"><i class="fa-solid fa-bag-shopping"></i></div>
      <div><div class="admin-sidebar-title">Shahz&Ahd</div><div style="font-size:.75rem;color:#9ca3af;">Admin Panel</div></div>
    </div>
    <ul class="admin-nav">
      <li><a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i>Dashboard</a></li>
      <li><a href="admin_products.php"><i class="fa-solid fa-shirt"></i>Products</a></li>
      <li><a href="admin_orders.php" class="active"><i class="fa-solid fa-receipt"></i>Orders</a></li>
      <li><a href="admin_customers.php"><i class="fa-regular fa-user"></i>Customers</a></li>
      <li><a href="admin_ml.php"><i class="fa-solid fa-wand-magic-sparkles"></i>ML & Recommendations</a></li>
      <li><a href="admin_settings.php"><i class="fa-solid fa-gear"></i>Settings</a></li>
    </ul>
    <div class="admin-sidebar-footer">Logged in as <strong><?php echo htmlspecialchars($adminName); ?></strong></div>
  </aside>

  <main class="admin-main">
    <h1>Orders</h1>
    <p>This is a placeholder page. When you add an <code>orders</code> table and endpoints, we can list and manage orders here.</p>
  </main>
</div>
</body>
</html>
