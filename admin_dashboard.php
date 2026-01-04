<?php
session_start();

// protect admin area
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin-login.html");
    exit();
}

$adminName = $_SESSION['admin_name'] ?? 'Admin';

// ---------- DB CONNECTION (HTML-safe, not using db.php) ----------
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "garderobe_db";

$totalProducts    = 0;
$totalUsers       = 0;
$newOrdersToday   = 0;  // will show 0 until you add orders table
$lowStockAlerts   = 0;  // placeholder – can be wired when you add stock column
$productSnapshot  = [];

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset("utf8mb4");

    // 1) Total products
    if ($result = $conn->query("SELECT COUNT(*) AS c FROM products")) {
        $row = $result->fetch_assoc();
        $totalProducts = (int)($row['c'] ?? 0);
        $result->free();
    }

    // 2) Registered users
    if ($result = $conn->query("SELECT COUNT(*) AS c FROM users")) {
        $row = $result->fetch_assoc();
        $totalUsers = (int)($row['c'] ?? 0);
        $result->free();
    }

    // 3) New orders today (only if you later create an 'orders' table)
    // This will just stay 0 if the table doesn't exist.
    try {
        $today = date('Y-m-d');
        if ($result = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE DATE(created_at) = '$today'")) {
            $row = $result->fetch_assoc();
            $newOrdersToday = (int)($row['c'] ?? 0);
            $result->free();
        }
    } catch (Throwable $e) {
        // ignore if orders table not present
        $newOrdersToday = 0;
    }

    // 4) Product snapshot: latest 4 products
    if ($result = $conn->query("SELECT name, category, price FROM products ORDER BY id DESC LIMIT 4")) {
        while ($row = $result->fetch_assoc()) {
            $productSnapshot[] = $row;
        }
        $result->free();
    }

    // 5) Low stock alerts – placeholder: if you later add a 'stock' column:
    // $lowStockAlerts = (int)$conn->query("SELECT COUNT(*) c FROM products WHERE stock < 5")->fetch_assoc()['c'];

} catch (Throwable $e) {
    // If DB fails, we just keep all stats as 0 / empty
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard – Shahz&Ahd</title>

  <!-- Same fonts & icons as main site -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="style.css">

  <style>
    /* --- Basic admin layout --- */
    body {
      margin: 0;
      font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: #f3f4f6;
      color: #111827;
    }

    .admin-layout {
      display: flex;
      min-height: 100vh;
    }

    /* --- Sidebar --- */
    .admin-sidebar {
      width: 240px;
      background: #111827;
      color: #e5e7eb;
      display: flex;
      flex-direction: column;
    }

    .admin-sidebar-header {
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid rgba(55, 65, 81, 0.8);
      display: flex;
      align-items: center;
      gap: 0.6rem;
    }

    .admin-sidebar-logo {
      width: 34px;
      height: 34px;
      border-radius: 999px;
      background: linear-gradient(135deg, #ef4444, #f97316);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 1rem;
    }

    .admin-sidebar-title {
      font-weight: 600;
      font-size: 1rem;
    }

    .admin-nav {
      list-style: none;
      padding: 0.75rem 0;
      margin: 0;
      flex: 1;
    }

    .admin-nav li {
      margin-bottom: 0.25rem;
    }

    .admin-nav a {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      padding: 0.6rem 1.5rem;
      color: #d1d5db;
      font-size: 0.95rem;
      text-decoration: none;
      transition: background 0.15s ease, color 0.15s ease, padding-left 0.15s ease;
    }

    .admin-nav a i {
      width: 18px;
      text-align: center;
    }

    .admin-nav a:hover {
      background: #1f2937;
      color: #fff;
      padding-left: 1.8rem;
    }

    .admin-nav a.active {
      background: #f97316;
      color: #fff;
    }

    .admin-sidebar-footer {
      padding: 0.75rem 1.5rem 1.2rem;
      border-top: 1px solid rgba(55, 65, 81, 0.8);
      font-size: 0.8rem;
      color: #9ca3af;
    }

    /* --- Main content --- */
    .admin-main {
      flex: 1;
      display: flex;
      flex-direction: column;
      padding: 1.5rem 2rem;
      gap: 1.5rem;
    }

    .admin-main-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
    }

    .admin-main-title {
      font-size: 1.5rem;
      font-weight: 600;
      margin: 0;
    }

    .admin-main-subtitle {
      margin: 0.15rem 0 0;
      font-size: 0.9rem;
      color: #6b7280;
    }

    .admin-user-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.4rem 0.75rem;
      background: #111827;
      color: #e5e7eb;
      border-radius: 999px;
      font-size: 0.85rem;
    }

    .admin-logout-btn {
      border: none;
      background: #ef4444;
      color: #fff;
      font-size: 0.9rem;
      border-radius: 999px;
      padding: 0.45rem 0.9rem;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      text-decoration: none;
    }

    .admin-logout-btn:hover {
      background: #dc2626;
    }

    /* --- Stats cards --- */
    .admin-stats-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 1rem;
    }

    .admin-stat-card {
      background: #ffffff;
      border-radius: 0.75rem;
      padding: 1rem 1.1rem;
      box-shadow: 0 1px 2px rgba(15, 23, 42, 0.12);
      display: flex;
      flex-direction: column;
      gap: 0.45rem;
    }

    .admin-stat-label {
      font-size: 0.8rem;
      color: #6b7280;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .admin-stat-value {
      font-size: 1.25rem;
      font-weight: 600;
    }

    .admin-stat-footer {
      font-size: 0.8rem;
      color: #10b981;
      display: flex;
      align-items: center;
      gap: 0.3rem;
    }

    /* --- Quick actions + table --- */
    .admin-two-column {
      display: grid;
      grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
      gap: 1.25rem;
      align-items: flex-start;
    }

    .admin-panel {
      background: #ffffff;
      border-radius: 0.75rem;
      padding: 1.1rem 1.25rem;
      box-shadow: 0 1px 2px rgba(15, 23, 42, 0.12);
    }

    .admin-panel-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.75rem;
    }

    .admin-panel-title {
      font-size: 1rem;
      font-weight: 600;
      margin: 0;
    }

    .admin-panel-subtitle {
      font-size: 0.8rem;
      color: #6b7280;
      margin: 0.1rem 0 0;
    }

    .admin-chip {
      font-size: 0.75rem;
      padding: 0.25rem 0.6rem;
      border-radius: 999px;
      background: #eff6ff;
      color: #1d4ed8;
    }

    .admin-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-top: 0.75rem;
    }

    .admin-action-btn {
      padding: 0.5rem 0.85rem;
      border-radius: 999px;
      border: 1px solid #e5e7eb;
      background: #f9fafb;
      font-size: 0.85rem;
      text-decoration: none;
      color: #111827;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
    }

    .admin-action-btn.primary {
      background: linear-gradient(135deg, #ef4444, #f97316);
      color: #fff;
      border-color: transparent;
    }

    .admin-action-btn:hover {
      background: #f3f4f6;
    }

    .admin-action-btn.primary:hover {
      filter: brightness(0.95);
    }

    table.admin-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.85rem;
      margin-top: 0.5rem;
    }

    table.admin-table thead {
      background: #f9fafb;
    }

    table.admin-table th,
    table.admin-table td {
      padding: 0.5rem 0.4rem;
      text-align: left;
      border-bottom: 1px solid #e5e7eb;
    }

    table.admin-table th {
      font-weight: 600;
      font-size: 0.8rem;
      color: #6b7280;
      text-transform: uppercase;
    }

    table.admin-table tr:last-child td {
      border-bottom: none;
    }

    table.admin-table .badge {
      font-size: 0.75rem;
      border-radius: 999px;
      padding: 0.2rem 0.55rem;
    }

    .badge-live {
      background: #ecfdf3;
      color: #15803d;
    }

    .badge-draft {
      background: #fef3c7;
      color: #92400e;
    }

    .badge-low {
      background: #fef2f2;
      color: #b91c1c;
    }

    @media (max-width: 1024px) {
      .admin-stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
      .admin-two-column {
        grid-template-columns: minmax(0, 1fr);
      }
      .admin-sidebar {
        display: none; /* hide sidebar on small screens for now */
      }
    }
  </style>
</head>
<body>
<div class="admin-layout">

  <!-- SIDEBAR -->
  <aside class="admin-sidebar">
    <div class="admin-sidebar-header">
      <div class="admin-sidebar-logo">
        <i class="fa-solid fa-bag-shopping"></i>
      </div>
      <div>
        <div class="admin-sidebar-title">Shahz&Ahd</div>
        <div style="font-size: 0.75rem; color:#9ca3af;">Admin Panel</div>
      </div>
    </div>

<ul class="admin-nav">
  <li><a href="admin_dashboard.php" class="active"><i class="fa-solid fa-gauge"></i>Dashboard</a></li>
  <li><a href="admin_products.php"><i class="fa-solid fa-shirt"></i>Products</a></li>
  <li><a href="admin_orders.php"><i class="fa-solid fa-receipt"></i>Orders</a></li>
  <li><a href="admin_customers.php"><i class="fa-regular fa-user"></i></a></li>
  <li><a href="admin_ml.php"><i class="fa-solid fa-wand-magic-sparkles"></i></a></li>
  <li><a href="admin_settings.php"><i class="fa-solid fa-gear"></i></a></li>
</ul>


    <div class="admin-sidebar-footer">
      Logged in as <strong><?php echo htmlspecialchars($adminName); ?></strong>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="admin-main">

    <header class="admin-main-header">
      <div>
        <h1 class="admin-main-title">Welcome back, <?php echo htmlspecialchars($adminName); ?> 👋</h1>
        <p class="admin-main-subtitle">Quick overview of your store. Use the shortcuts below to manage products and orders.</p>
      </div>
      <div style="display:flex; align-items:center; gap:0.6rem;">
        <div class="admin-user-pill">
          <i class="fa-regular fa-circle-user"></i>
          <span>Admin</span>
        </div>
        <a class="admin-logout-btn" href="backend/admin_logout.php">
          <i class="fa-solid fa-right-from-bracket"></i>
          <span>Logout</span>
        </a>
      </div>
    </header>

    <!-- STATS CARDS -->
    <section class="admin-stats-grid">
      <article class="admin-stat-card">
        <div class="admin-stat-label">Total Products</div>
        <div class="admin-stat-value"><?php echo $totalProducts; ?></div>
        <div class="admin-stat-footer">
          <i class="fa-solid fa-arrow-trend-up"></i>
          <span>Demo data – wired to products table (live now)</span>
        </div>
      </article>

      <article class="admin-stat-card">
        <div class="admin-stat-label">New Orders (Today)</div>
        <div class="admin-stat-value"><?php echo $newOrdersToday; ?></div>
        <div class="admin-stat-footer">
          <i class="fa-regular fa-clock"></i>
          <span>Orders module placeholder</span>
        </div>
      </article>

      <article class="admin-stat-card">
        <div class="admin-stat-label">Registered Users</div>
        <div class="admin-stat-value"><?php echo $totalUsers; ?></div>
        <div class="admin-stat-footer">
          <i class="fa-solid fa-users"></i>
          <span>From users table</span>
        </div>
      </article>

      <article class="admin-stat-card">
        <div class="admin-stat-label">Low Stock Alerts</div>
        <div class="admin-stat-value"><?php echo $lowStockAlerts ?: '–'; ?></div>
        <div class="admin-stat-footer">
          <i class="fa-solid fa-triangle-exclamation"></i>
          <span>Connect later to stock logic</span>
        </div>
      </article>
    </section>

    <!-- QUICK ACTIONS + PRODUCT SNAPSHOT -->
    <section class="admin-two-column">

      <!-- Quick actions -->
      <div class="admin-panel">
        <div class="admin-panel-header">
          <div>
            <h2 class="admin-panel-title">Quick Actions</h2>
            <p class="admin-panel-subtitle">Most common actions you’ll need while managing Shahz&Ahd.</p>
          </div>
          <span class="admin-chip"><i class="fa-solid fa-bolt"></i> Fast access</span>
        </div>

        <div class="admin-actions">
          <a class="admin-action-btn primary" href="admin_products.html">
            <i class="fa-solid fa-plus"></i>
            <span>Add / Manage Products</span>
          </a>
          <a class="admin-action-btn" href="admin_orders.html">
            <i class="fa-solid fa-receipt"></i>
            <span>View Orders</span>
          </a>
          <a class="admin-action-btn" href="#">
            <i class="fa-solid fa-user-group"></i>
            <span>View Customers</span>
          </a>
          <a class="admin-action-btn" href="#">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            <span>Train Recommendations</span>
          </a>
        </div>
      </div>

      <!-- Product overview table (live from DB) -->
      <div class="admin-panel">
        <div class="admin-panel-header">
          <div>
            <h2 class="admin-panel-title">Product Snapshot</h2>
            <p class="admin-panel-subtitle">Shows your latest products from the database.</p>
          </div>
        </div>

        <table class="admin-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Category</th>
              <th>Price</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!empty($productSnapshot)): ?>
            <?php foreach ($productSnapshot as $p): ?>
              <tr>
                <td><?php echo htmlspecialchars($p['name']); ?></td>
                <td><?php echo htmlspecialchars($p['category'] ?? '—'); ?></td>
                <td>$<?php echo number_format((float)($p['price'] ?? 0), 2); ?></td>
                <td><span class="badge badge-live">Live</span></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
              <tr>
                <td colspan="4">No products found yet. Add your first product from <strong>Products</strong> page.</td>
              </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

    </section>

  </main>
</div>
</body>
</html>
