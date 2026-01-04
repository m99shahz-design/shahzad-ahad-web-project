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
  <title>Products – Shahz&Ahd Admin</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="style.css">

  <style>
    body {
      margin: 0;
      font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: #f3f4f6;
      color: #111827;
    }
    .admin-layout { display:flex; min-height:100vh; }

    .admin-sidebar {
      width: 240px;
      background: #111827;
      color: #e5e7eb;
      display:flex;
      flex-direction:column;
    }
    .admin-sidebar-header {
      padding:1.25rem 1.5rem;
      border-bottom:1px solid rgba(55,65,81,0.8);
      display:flex;
      align-items:center;
      gap:0.6rem;
    }
    .admin-sidebar-logo {
      width:34px;height:34px;border-radius:999px;
      background:linear-gradient(135deg,#ef4444,#f97316);
      display:flex;align-items:center;justify-content:center;
      color:#fff;font-size:1rem;
    }
    .admin-sidebar-title {font-weight:600;font-size:1rem;}

    .admin-nav {list-style:none;padding:0.75rem 0;margin:0;flex:1;}
    .admin-nav li {margin-bottom:0.25rem;}
    .admin-nav a {
      display:flex;align-items:center;gap:0.6rem;
      padding:0.6rem 1.5rem;
      color:#d1d5db;font-size:0.95rem;text-decoration:none;
      transition:background .15s ease,color .15s ease,padding-left .15s ease;
    }
    .admin-nav a i {width:18px;text-align:center;}
    .admin-nav a:hover {background:#1f2937;color:#fff;padding-left:1.8rem;}
    .admin-nav a.active {background:#f97316;color:#fff;}

    .admin-sidebar-footer {
      padding:0.75rem 1.5rem 1.2rem;
      border-top:1px solid rgba(55,65,81,0.8);
      font-size:0.8rem;color:#9ca3af;
    }

    .admin-main {
      flex:1;display:flex;flex-direction:column;
      padding:1.5rem 2rem;gap:1.5rem;
    }
    .admin-main-header {
      display:flex;justify-content:space-between;align-items:center;gap:1rem;
    }
    .admin-main-title {font-size:1.5rem;font-weight:600;margin:0;}
    .admin-main-subtitle {margin:0.15rem 0 0;font-size:0.9rem;color:#6b7280;}

    .admin-user-pill {
      display:inline-flex;align-items:center;gap:0.5rem;
      padding:0.4rem 0.75rem;
      background:#111827;color:#e5e7eb;border-radius:999px;
      font-size:0.85rem;
    }
    .admin-logout-btn {
      border:none;background:#ef4444;color:#fff;font-size:0.9rem;
      border-radius:999px;padding:0.45rem 0.9rem;cursor:pointer;
      display:inline-flex;align-items:center;gap:0.4rem;text-decoration:none;
    }
    .admin-logout-btn:hover {background:#dc2626;}

    .admin-panel {
      background:#fff;border-radius:0.75rem;
      padding:1.1rem 1.25rem;
      box-shadow:0 1px 2px rgba(15,23,42,0.12);
    }
    .admin-panel-header {
      display:flex;justify-content:space-between;align-items:center;
      margin-bottom:0.75rem;
    }
    .admin-panel-title {font-size:1rem;font-weight:600;margin:0;}
    .admin-panel-subtitle {font-size:0.8rem;color:#6b7280;margin:0.15rem 0 0;}

    .admin-chip {
      font-size:0.75rem;padding:0.25rem 0.6rem;
      border-radius:999px;background:#eff6ff;color:#1d4ed8;
    }

    .products-toolbar {
      display:flex;flex-wrap:wrap;gap:0.75rem;
      justify-content:space-between;align-items:center;
      margin-bottom:0.75rem;
    }

    .btn-primary-small {
      border:none;border-radius:999px;
      background:linear-gradient(135deg,#ef4444,#f97316);
      color:#fff;padding:0.5rem 0.95rem;
      font-size:0.85rem;display:inline-flex;align-items:center;gap:0.4rem;
      cursor:pointer;text-decoration:none;
    }
    .btn-primary-small:hover {filter:brightness(0.95);}

    .search-input {
      border-radius:999px;
      border:1px solid #e5e7eb;
      padding:0.4rem 0.8rem;
      font-size:0.85rem;
      min-width:220px;
    }

    table.admin-table {
      width:100%;border-collapse:collapse;
      font-size:0.85rem;margin-top:0.5rem;
    }
    table.admin-table thead {background:#f9fafb;}
    table.admin-table th,table.admin-table td {
      padding:0.5rem 0.4rem;text-align:left;
      border-bottom:1px solid #e5e7eb;
    }
    table.admin-table th {
      font-weight:600;font-size:0.8rem;color:#6b7280;text-transform:uppercase;
    }
    table.admin-table tr:last-child td {border-bottom:none;}

    .badge {
      font-size:0.75rem;border-radius:999px;
      padding:0.2rem 0.55rem;
    }
    .badge-live {background:#ecfdf3;color:#15803d;}
    .badge-sale {background:#fef3c7;color:#92400e;}

    .table-actions {
      display:flex;gap:0.35rem;
    }
    .btn-icon {
      border:none;border-radius:999px;
      padding:0.3rem 0.4rem;font-size:0.8rem;
      cursor:pointer;display:inline-flex;align-items:center;justify-content:center;
    }
    .btn-icon.edit {background:#e0f2fe;color:#1d4ed8;}
    .btn-icon.delete {background:#fee2e2;color:#b91c1c;}

    /* Add product form */
    .add-product-form {
      margin-top:0.75rem;
      border-top:1px solid #e5e7eb;
      padding-top:0.75rem;
      display:none;
      gap:0.75rem;
      flex-wrap:wrap;
      font-size:0.85rem;
    }
    .add-product-form .field {
      display:flex;flex-direction:column;gap:0.2rem;
      min-width:150px;flex:1;
    }
    .add-product-form input {
      border-radius:0.5rem;
      border:1px solid #e5e7eb;
      padding:0.35rem 0.6rem;
      font-size:0.85rem;
    }
    .add-product-form button {
      align-self:flex-start;
    }

    .form-message {
      font-size:0.8rem;margin-top:0.3rem;
    }
    .form-message.error {color:#b91c1c;}
    .form-message.success {color:#15803d;}

    @media (max-width:1024px){
      .admin-sidebar{display:none;}
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
        <div style="font-size:0.75rem;color:#9ca3af;">Admin Panel</div>
      </div>
    </div>

    <ul class="admin-nav">
      <li><a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i>Dashboard</a></li>
      <li><a href="admin_products.php" class="active"><i class="fa-solid fa-shirt"></i>Products</a></li>
      <li><a href="admin_orders.php"><i class="fa-solid fa-receipt"></i>Orders</a></li>
      <li><a href="admin_customers.php"><i class="fa-regular fa-user"></i>Customers</a></li>
      <li><a href="admin_ml.php"><i class="fa-solid fa-wand-magic-sparkles"></i>ML & Recommendations</a></li>
      <li><a href="admin_settings.php"><i class="fa-solid fa-gear"></i>Settings</a></li>
    </ul>

    <div class="admin-sidebar-footer">
      Logged in as <strong><?php echo htmlspecialchars($adminName); ?></strong>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="admin-main">

    <header class="admin-main-header">
      <div>
        <h1 class="admin-main-title">Products</h1>
        <p class="admin-main-subtitle">
          View, add and remove products. Changes are saved in the same database that powers your storefront.
        </p>
      </div>
      <div style="display:flex;align-items:center;gap:0.6rem;">
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

    <section class="admin-panel">
      <div class="admin-panel-header">
        <div>
          <h2 class="admin-panel-title">Product List</h2>
          <p class="admin-panel-subtitle">Pulled from backend/products_list.php</p>
        </div>
        <span class="admin-chip"><i class="fa-solid fa-database"></i> Live from DB</span>
      </div>

      <div class="products-toolbar">
        <div>
          <button type="button" id="toggleAddFormBtn" class="btn-primary-small">
            <i class="fa-solid fa-plus"></i> Add Product
          </button>
        </div>
        <div>
          <input id="searchInput" type="text" class="search-input" placeholder="Search by name or category...">
        </div>
      </div>

      <div id="messageArea" class="form-message"></div>

      <table class="admin-table" id="productsTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Product</th>
            <th>Category</th>
            <th>Price</th>
            <th>Old Price</th>
            <th>Views</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- filled by JS -->
        </tbody>
      </table>

      <!-- Add product form -->
      <form id="addProductForm" class="add-product-form">
        <div class="field">
          <label for="pName">Name</label>
          <input type="text" id="pName" name="name" required>
        </div>
        <div class="field">
          <label for="pCategory">Category</label>
          <input type="text" id="pCategory" name="category" required>
        </div>
        <div class="field">
          <label for="pPrice">Price</label>
          <input type="number" step="0.01" id="pPrice" name="price" required>
        </div>
        <div class="field">
          <label for="pOldPrice">Old Price (optional)</label>
          <input type="number" step="0.01" id="pOldPrice" name="old_price">
        </div>
        <div class="field">
          <label for="pImage">Image filename (e.g. tshirt-1.jpg)</label>
          <input type="text" id="pImage" name="image">
        </div>

        <button type="submit" class="btn-primary-small">
          <i class="fa-solid fa-floppy-disk"></i> Save Product
        </button>
      </form>
    </section>

  </main>
</div>

<script>
// ====== Helper: show message ======
function showMessage(msg, type = "success") {
  const area = document.getElementById("messageArea");
  area.textContent = msg;
  area.className = "form-message " + (type === "error" ? "error" : "success");
  if (!msg) return;
  setTimeout(() => {
    area.textContent = "";
    area.className = "form-message";
  }, 3000);
}

// ====== Load products from NEW admin endpoint ======
async function loadProducts() {
  const tbody = document.querySelector("#productsTable tbody");
  tbody.innerHTML = "<tr><td colspan='7'>Loading...</td></tr>";

  try {
    const res = await fetch("backend/products_admin_list.php");
    const data = await res.json();

    if (!data.success) {
      tbody.innerHTML = "<tr><td colspan='7'>Error: " + (data.message || "Cannot load products") + "</td></tr>";
      return;
    }

    let products = data.products || [];

    if (!products.length) {
      tbody.innerHTML = "<tr><td colspan='7'>No products found.</td></tr>";
      return;
    }

    const searchValue = document.getElementById("searchInput").value.toLowerCase();
    const filtered = products.filter(p => {
      const name = (p.name || "").toLowerCase();
      const cat  = (p.category || "").toLowerCase();
      return !searchValue || name.includes(searchValue) || cat.includes(searchValue);
    });

    if (!filtered.length) {
      tbody.innerHTML = "<tr><td colspan='7'>No products match your search.</td></tr>";
      return;
    }

    tbody.innerHTML = "";
    filtered.forEach(p => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${p.id}</td>
        <td>${p.name || ""}</td>
        <td>${p.category || ""}</td>
        <td>$${Number(p.price || 0).toFixed(2)}</td>
        <td>${p.old_price ? "$" + Number(p.old_price).toFixed(2) : "—"}</td>
        <td>${p.views ?? 0}</td>
        <td>
          <div class="table-actions">
            <button type="button" class="btn-icon edit" title="Edit (coming soon)">
              <i class="fa-regular fa-pen-to-square"></i>
            </button>
            <button type="button" class="btn-icon delete" data-id="${p.id}" title="Delete">
              <i class="fa-regular fa-trash-can"></i>
            </button>
          </div>
        </td>
      `;
      tbody.appendChild(tr);
    });

  } catch (err) {
    console.error(err);
    tbody.innerHTML = "<tr><td colspan='7'>Error loading products.</td></tr>";
  }
}

// ====== Delete product using NEW admin endpoint ======
async function deleteProduct(id) {
  if (!confirm("Delete product ID " + id + "?")) return;

  try {
    const formData = new FormData();
    formData.append("id", id);

    const res = await fetch("backend/product_admin_delete.php", {
      method: "POST",
      body: formData
    });
    const data = await res.json();

    if (data && data.success) {
      showMessage("Product deleted.");
    } else {
      showMessage(data.message || "Could not delete product.", "error");
    }
  } catch (err) {
    console.error(err);
    showMessage("Error deleting product.", "error");
  }
  loadProducts();
}

// ====== Add product using NEW admin endpoint ======
async function handleAddProduct(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);

  try {
    const res = await fetch("backend/product_admin_create.php", {
      method: "POST",
      body: formData
    });
    const data = await res.json();

    if (data && data.success) {
      showMessage("Product created successfully.");
      form.reset();
      loadProducts();
    } else {
      showMessage(data.message || "Could not create product.", "error");
    }
  } catch (err) {
    console.error(err);
    showMessage("Error creating product.", "error");
  }
}

// ====== Event listeners ======
document.addEventListener("DOMContentLoaded", () => {
  loadProducts();

  document.getElementById("searchInput").addEventListener("input", () => {
    loadProducts();
  });

  document.getElementById("productsTable").addEventListener("click", (e) => {
    const btn = e.target.closest("button.btn-icon.delete");
    if (btn) {
      const id = btn.getAttribute("data-id");
      deleteProduct(id);
    }
  });

  const addForm = document.getElementById("addProductForm");
  const toggleBtn = document.getElementById("toggleAddFormBtn");
  toggleBtn.addEventListener("click", () => {
    const visible = addForm.style.display === "flex";
    addForm.style.display = visible ? "none" : "flex";
  });

  addForm.addEventListener("submit", handleAddProduct);
});
</script>

</body>
</html>
