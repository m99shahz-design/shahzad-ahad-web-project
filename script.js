// ========= HELPER =========
function formToJSON(form) {
  const data = new FormData(form);
  const obj = {};
  data.forEach((value, key) => {
    obj[key] = value;
  });
  return obj;
}

function getQueryParam(name) {
  const params = new URLSearchParams(window.location.search);
  return params.get(name);
}

// ========= TRACK PRODUCT VIEWS (ML SYSTEM) =========
(function () {
  const path = window.location.pathname;
  if (!path.endsWith("product.html")) return;

  const id = getQueryParam("id");
  if (!id) return;

  fetch("backend/product_view.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id: parseInt(id, 10) }),
  }).catch(() => {});
})();

// ========= LOAD POPULAR PRODUCTS (ML RECOMMENDATION) =========
async function loadPopularProducts() {
  const container = document.getElementById("recommendedProducts");
  if (!container) return; // only on index.html

  try {
    const res = await fetch("backend/products_list.php?mode=popular");
    const data = await res.json();
    if (!data.success) return;

    container.innerHTML = "";

    data.data.forEach((p) => {
      const oldPrice = p.old_price
        ? `<span class="price-old">$${p.old_price}</span>`
        : "";

      container.innerHTML += `
        <article class="product-card">
          <div class="product-image">
            <a href="product.html?id=${p.id}">
              <img src="images/${p.image}" alt="${p.name}">
            </a>
          </div>
          <h3 class="product-name">
            <a href="product.html?id=${p.id}">${p.name}</a>
          </h3>
          <div class="product-price-row">
            <span class="price-new">$${p.price}</span>
            ${oldPrice}
          </div>
          <p class="product-category">${p.category || ""}</p>
          <button class="btn btn-primary small add-cart-btn">Add to cart</button>
        </article>
      `;
    });
  } catch (err) {
    console.log("Popular loading error", err);
  }
}

loadPopularProducts();

// ========= LOGIN (PHP BACKEND) =========
// ❗ Changed: no AJAX, no JSON, just show "Checking..." and let the form submit normally
const loginForm = document.getElementById("loginForm");
if (loginForm) {
  loginForm.addEventListener("submit", () => {
    const msg = document.getElementById("loginMessage");
    if (msg) {
      msg.style.color = "#555";
      msg.textContent = "Checking...";
    }
    // Do NOT preventDefault, do NOT use fetch.
    // The browser will submit the form to backend/auth_login.php
  });
}

// ✅ NOTE: REGISTER AJAX BLOCK REMOVED
// We now let the form submit normally to backend/auth_register.php
// and only keep the validation below.

// ========= SIGN UP REAL-TIME VALIDATION =========
const registerFormEl = document.getElementById("registerForm");
if (registerFormEl) {
  const usernameInput = document.getElementById("regUsername");
  const passInput = document.getElementById("regPassword");
  const confirmInput = document.getElementById("regConfirm");
  const robotCheckbox = document.getElementById("regNotRobot");
  const msg = document.getElementById("registerMessage");

  function setError(text) {
    if (!msg) return;
    msg.textContent = text;
    msg.style.color = "#e63946";
  }

  function clearError() {
    if (!msg) return;
    msg.textContent = "";
  }

  if (usernameInput) {
    usernameInput.addEventListener("input", () => {
      const v = usernameInput.value.trim().toLowerCase();
      if (v === "admin") {
        setError('Username "admin" is not allowed.');
      } else if (msg.textContent.includes("admin")) {
        clearError();
      }
    });
  }

  function checkPasswords() {
    if (!passInput || !confirmInput) return true;
    if (passInput.value && confirmInput.value && passInput.value !== confirmInput.value) {
      setError("Passwords do not match.");
      return false;
    }
    if (msg.textContent.includes("match")) {
      clearError();
    }
    return true;
  }

  if (passInput) passInput.addEventListener("input", checkPasswords);
  if (confirmInput) confirmInput.addEventListener("input", checkPasswords);

  registerFormEl.addEventListener("submit", (e) => {
    clearError();

    if (!usernameInput.value.trim()) {
      setError("Username is required.");
      e.preventDefault();
      return;
    }

    if (usernameInput.value.trim().toLowerCase() === "admin") {
      setError('Username "admin" is not allowed.');
      e.preventDefault();
      return;
    }

    if (!checkPasswords()) {
      e.preventDefault();
      return;
    }

    if (robotCheckbox && !robotCheckbox.checked) {
      setError('Please confirm "I\'m not a robot".');
      e.preventDefault();
      return;
    }
    // ✅ If everything is OK, we do NOT preventDefault
    // The form will submit normally to backend/auth_register.php
  });
}

// ========= LOGIN PASSWORD TOGGLE =========
const loginPasswordInput = document.getElementById("loginPassword");
const passwordToggleBtn = document.querySelector(".password-toggle");
if (loginPasswordInput && passwordToggleBtn) {
  passwordToggleBtn.addEventListener("click", () => {
    const type =
      loginPasswordInput.getAttribute("type") === "password" ? "text" : "password";
    loginPasswordInput.setAttribute("type", type);
    passwordToggleBtn.setAttribute(
      "aria-label",
      type === "password" ? "Show password" : "Hide password"
    );
  });
}

// ========= MOBILE NAV =========
const menuToggle = document.getElementById("menuToggle");
const mainNav = document.getElementById("mainNav");
if (menuToggle && mainNav) {
  menuToggle.addEventListener("click", () => {
    mainNav.classList.toggle("open");
  });
}

// ========= SEARCH BAR EXPAND + FILTER =========
const headerSearch = document.querySelector(".header-search");
const headerSearchToggle = document.querySelector(".header-search-toggle");
const headerSearchForm = document.querySelector(".header-search-form");
const headerSearchInput = headerSearchForm
  ? headerSearchForm.querySelector("input[type='search']")
  : null;

if (headerSearch && headerSearchToggle && headerSearchInput) {
  headerSearchToggle.addEventListener("click", () => {
    headerSearch.classList.toggle("open");
    if (headerSearch.classList.contains("open")) {
      headerSearchInput.focus();
    }
  });
}

function filterProducts(query) {
  const q = query.trim().toLowerCase();
  const cards = document.querySelectorAll(".product-card");

  cards.forEach((card) => {
    const name = card.querySelector(".product-name a")
      ? card.querySelector(".product-name a").textContent.toLowerCase()
      : "";
    const cat = card.querySelector(".product-category")
      ? card.querySelector(".product-category").textContent.toLowerCase()
      : "";

    card.style.display = !q || name.includes(q) || cat.includes(q) ? "" : "none";
  });

  const productsSection = document.getElementById("products");
  if (productsSection) {
    productsSection.scrollIntoView({ behavior: "smooth" });
  }
}

if (headerSearchForm && headerSearchInput) {
  headerSearchForm.addEventListener("submit", (e) => {
    e.preventDefault();
    filterProducts(headerSearchInput.value);
  });
}

// shop sidebar search
const shopSearchForm = document.querySelector(".shop-search");
const shopSearchInput = shopSearchForm
  ? shopSearchForm.querySelector("input")
  : null;

if (shopSearchForm && shopSearchInput) {
  shopSearchForm.addEventListener("submit", (e) => {
    e.preventDefault();
    filterProducts(shopSearchInput.value);
  });
}

// ========= BACK TO TOP =========
const backToTopBtn = document.getElementById("backToTop");
if (backToTopBtn) {
  backToTopBtn.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });
}

// ========= CART & WISHLIST (LOCALSTORAGE) =========
function loadCartItems() {
  try {
    const raw = localStorage.getItem("cartItems");
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

function saveCartItems(items) {
  localStorage.setItem("cartItems", JSON.stringify(items));
}

const cartCountEl = document.getElementById("cartCount");

function recalcCartCountAndBadge() {
  const items = loadCartItems();
  const count = items.length;
  if (cartCountEl) cartCountEl.textContent = count;
  localStorage.setItem("cartCount", String(count));
}

// initialize badge on load
recalcCartCountAndBadge();

function addItemToCart(item) {
  const items = loadCartItems();
  items.push({
    id: item.id || null,
    name: item.name || "Product",
    price: item.price || 0,
    image: item.image || "",
  });
  saveCartItems(items);
  recalcCartCountAndBadge();
}

// Add-to-cart from product cards (index/shop/recommended)
function setupCartButtonsOnCards() {
  const buttons = document.querySelectorAll(".add-cart-btn");

  buttons.forEach((btn) => {

    // ✅ SKIP product detail page button
    if (btn.id === "productAddCartBtn") return;

    // ✅ prevent double bind
    if (btn.dataset.bound === "1") return;
    btn.dataset.bound = "1";

    btn.addEventListener("click", () => {
      const card = btn.closest(".product-card");
      if (!card) return;

      const link = card.querySelector(".product-name a");
      const priceEl = card.querySelector(".price-new");
      const imgEl = card.querySelector(".product-image img");

      let id = null;
      if (link && link.getAttribute("href")) {
        try {
          const url = new URL(link.getAttribute("href"), window.location.href);
          id = url.searchParams.get("id");
        } catch {}
      }

      const name = link ? link.textContent.trim() : "Product";
      const priceText = priceEl ? priceEl.textContent.replace("$", "") : "0";
      const price = parseFloat(priceText) || 0;
      const image = imgEl ? imgEl.getAttribute("src") : "";

      addItemToCart({ id, name, price, image });
    });
  });

  // ============================
  // ❗FIX: This block below was DUPLICATED OUTSIDE the function and was breaking braces.
  // I am keeping it here as a comment so "nothing is removed", but it should not run.
  // ============================
  /*
  buttons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const card = btn.closest(".product-card");
      if (!card) return;

      const link = card.querySelector(".product-name a");
      const priceEl = card.querySelector(".price-new");
      const imgEl = card.querySelector(".product-image img");

      let id = null;
      if (link && link.getAttribute("href")) {
        try {
          const url = new URL(link.getAttribute("href"), window.location.href);
          id = url.searchParams.get("id");
        } catch (e) {}
      }

      const name = link ? link.textContent.trim() : "Product";
      const priceText = priceEl
        ? priceEl.textContent.replace("$", "").trim()
        : "0";
      const price = parseFloat(priceText) || 0;
      const image = imgEl ? imgEl.getAttribute("src") : "";

      addItemToCart({ id, name, price, image });
    });
  });
  */
}

setupCartButtonsOnCards();


// ===============================
// ✅ FIX: Add-to-cart from product detail page (product.html)
// The original code had productAddBtn used before declaration + stray lines.
// Wrapped safely without removing your logic.
// ===============================
(function () {
  const productAddBtn = document.getElementById("productAddCartBtn");
  if (!productAddBtn) return;

  if (productAddBtn.dataset.bound === "1") return;
  productAddBtn.dataset.bound = "1";

  productAddBtn.addEventListener("click", () => {
    const id = getQueryParam("id");
    const titleEl = document.getElementById("productTitleDetail");
    const priceEl = document.getElementById("productPriceNew");
    const imgEl = document.getElementById("productMainImage");
    const qtyInput = document.getElementById("qtyInput");

    const name = titleEl ? titleEl.textContent.trim() : "Product";
    const priceText = priceEl
      ? priceEl.textContent.replace("$", "").trim()
      : "0";
    const price = parseFloat(priceText) || 0;
    const image = imgEl ? imgEl.getAttribute("src") : "";
    const qty = qtyInput ? parseInt(qtyInput.value || "1", 10) || 1 : 1;

    for (let i = 0; i < qty; i++) {
      addItemToCart({ id, name, price, image });
    }

    alert("Added to cart.");
  });

  // Wishlist from product page (simple demo)
  const productWishlistBtn = document.getElementById("productWishlistBtn");
  if (productWishlistBtn) {
    productWishlistBtn.addEventListener("click", () => {
      alert("Added to wishlist (demo).");
    });
  }

  // ============================
  // ❗BROKEN ORIGINAL STRAY LINES (kept, but disabled so they don't crash)
  // ============================
  /*
  // Add-to-cart from product detail page
    if (productAddBtn.dataset.bound === "1") return;
    productAddBtn.dataset.bound = "1";

    if (!productAddBtn) return;
      if (productAddBtn.dataset.bound === "1") return;
    productAddBtn.dataset.bound = "1";
  */
})();

// Render cart page (cart.html)
(function () {
  const tbody = document.getElementById("cartTableBody");
  if (!tbody) return; // not on cart page

  const emptyMsg = document.getElementById("cartEmptyMessage");
  const subtotalEl = document.getElementById("cartSubtotal");
  const totalEl = document.getElementById("cartTotal");

  function renderCartPage() {
    const items = loadCartItems();
    tbody.innerHTML = "";

    if (!items.length) {
      if (emptyMsg) emptyMsg.style.display = "block";
      if (subtotalEl) subtotalEl.textContent = "$0.00";
      if (totalEl) totalEl.textContent = "$0.00";
      return;
    }

    if (emptyMsg) emptyMsg.style.display = "none";

    let subtotal = 0;

    items.forEach((item, index) => {
      const price = Number(item.price || 0);
      subtotal += price;

      const safeName = item.name || "Product";
      const imgSrc = item.image || "images/cap-1.jpg";

      const tr = document.createElement("tr");
      tr.dataset.index = String(index);
      tr.innerHTML = `
        <td>
          <div class="cart-product">
            <img src="${imgSrc}" alt="${safeName}">
            <span>${safeName}</span>
          </div>
        </td>
        <td>$${price.toFixed(2)}</td>
        <td>
          <button class="link-remove" type="button">Remove</button>
        </td>
      `;
      tbody.appendChild(tr);
    });

    if (subtotalEl) subtotalEl.textContent = "$" + subtotal.toFixed(2);
    if (totalEl) totalEl.textContent = "$" + subtotal.toFixed(2);
  }

  // remove item
  tbody.addEventListener("click", (e) => {
    const target = e.target;
    if (target.classList.contains("link-remove")) {
      const tr = target.closest("tr");
      if (!tr) return;
      const index = parseInt(tr.dataset.index || "-1", 10);
      if (index < 0) return;

      const items = loadCartItems();
      items.splice(index, 1);
      saveCartItems(items);
      recalcCartCountAndBadge();
      renderCartPage();
    }
  });

  renderCartPage();
})();


// ========= SHOP PAGE: LOAD ALL PRODUCTS FROM DB (NEW) =========
async function loadShopProducts() {
  const grid = document.getElementById("shopProductsGrid");
  if (!grid) return; // not on shop page

  // optional: simple loading text
  grid.innerHTML = "<p>Loading products...</p>";

  try {
    const res = await fetch("backend/products_list.php");
    const data = await res.json();
    if (!data.success || !Array.isArray(data.data)) {
      grid.innerHTML = "<p>No products found.</p>";
      return;
    }

    const products = data.data;
    if (!products.length) {
      grid.innerHTML = "<p>No products found.</p>";
      return;
    }

    grid.innerHTML = "";

    products.forEach((p) => {
      const oldPrice = p.old_price
        ? `<span class="price-old">$${p.old_price}</span>`
        : "";

      grid.innerHTML += `
        <article class="product-card">
          ${p.old_price ? '<span class="badge badge-sale">Sale</span>' : ""}
          <div class="product-image">
            <a href="product.html?id=${p.id}">
              <img src="images/${p.image}" alt="${p.name}">
            </a>
          </div>
          <h3 class="product-name">
            <a href="product.html?id=${p.id}">${p.name}</a>
          </h3>
          <div class="product-price-row">
            <span class="price-new">$${p.price}</span>
            ${oldPrice}
          </div>
          <p class="product-category">${p.category || ""}</p>
          <button class="btn btn-primary small add-cart-btn">Add to cart</button>
        </article>
      `;
    });

    // re-wire add-to-cart buttons on the new cards
    setupCartButtonsOnCards();
  } catch (err) {
    console.error("Shop products loading error", err);
    grid.innerHTML = "<p>Failed to load products.</p>";
  }
}

// call it once JS is loaded (works because script is at end of body)
loadShopProducts();


// ===============================
// NEW: GLOBAL ADD-TO-CART (works for dynamic cards too)
// ===============================
document.addEventListener("click", (e) => {
  const btn = e.target.closest(".add-cart-btn");
  if (!btn) return;

  // ✅ FIX: prevent double add (already handled elsewhere)
  if (btn.dataset.bound === "1") return;


  // prevent any default submit/redirect issues
  e.preventDefault();

  const card = btn.closest(".product-card");
  if (!card) return;

  const link = card.querySelector(".product-name a");
  const priceEl = card.querySelector(".price-new");
  const imgEl = card.querySelector(".product-image img");

  let id = null;
  if (link && link.getAttribute("href")) {
    try {
      const url = new URL(link.getAttribute("href"), window.location.href);
      id = url.searchParams.get("id");
    } catch {}
  }

  const name = link ? link.textContent.trim() : "Product";
  const priceText = priceEl ? priceEl.textContent.replace("$", "").trim() : "0";
  const price = parseFloat(priceText) || 0;
  const image = imgEl ? imgEl.getAttribute("src") : "";

  // uses your existing addItemToCart()
  addItemToCart({ id, name, price, image });

  alert("Added to cart.");
});


// ===============================
// NEW: After Popular products load, re-bind buttons (if needed)
// ===============================
(function () {
  const oldLoadPopular = window.loadPopularProducts;
  if (typeof oldLoadPopular !== "function") return;

  window.loadPopularProducts = async function () {
    await oldLoadPopular();
    // in case you still rely on setupCartButtonsOnCards()
    if (typeof setupCartButtonsOnCards === "function") {
      setupCartButtonsOnCards();
    }
  };
})();


// ===============================
// NEW: Load Product Details from DB on product.html
// ===============================
async function loadProductDetailFromDB() {
  const path = window.location.pathname;
  if (!path.endsWith("product.html")) return;

  const id = getQueryParam("id");
  if (!id) return;

  try {
    const res = await fetch(`backend/products_list.php?mode=single&id=${encodeURIComponent(id)}`);
    const data = await res.json();
    if (!data.success) return;

    const p = data.data;

    // These IDs must exist in your product.html
    const titleEl = document.getElementById("productTitleDetail");
    const priceEl = document.getElementById("productPriceNew");
    const catEl = document.getElementById("productCategoryDetail");
    const idEl = document.getElementById("productIdDetail");
    const imgEl = document.getElementById("productMainImage");

    if (titleEl) titleEl.textContent = p.name || "Product";
    if (priceEl) priceEl.textContent = `$${Number(p.price || 0).toFixed(2)}`;
    if (catEl) catEl.textContent = p.category || "-";
    if (idEl) idEl.textContent = p.id || "-";

    if (imgEl && p.image) {
      imgEl.src = `images/${p.image}`;
      imgEl.alt = p.name || "Product image";
    }

  } catch (err) {
    console.log("Product detail load error:", err);
  }
}

loadProductDetailFromDB();


// ===============================
// ✅ SHOP PAGE: LOAD FROM DB + FILTER + SORT + PRICE + PAGINATION
// (ADD THIS AT END OF script.js — do not remove old code)
// ===============================
(async function () {
  const shopPage = document.querySelector(".shop-page");
  const grid = document.querySelector(".shop-page .products-grid");
  if (!shopPage || !grid) return;

  const toolbarText = document.querySelector(".shop-page .shop-toolbar p");
  const sortSelect = document.querySelector(".shop-page .shop-toolbar select");
  const categoryLinks = document.querySelectorAll(".shop-sidebar .shop-list a");
  const priceRange = document.querySelector(".shop-sidebar input[type='range']");
  const paginationWrap = document.querySelector(".shop-pagination");

  let allProducts = [];
  let filtered = [];

  // state
  let activeCategory = "all";
  let searchQuery = "";
  let maxPrice = priceRange ? parseInt(priceRange.value || "200", 10) : 999999;
  let sortMode = "default";
  let currentPage = 1;
  const pageSize = 8;

  function escapeHTML(str) {
    return String(str ?? "").replace(/[&<>"']/g, (m) => ({
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    }[m]));
  }

  function normalizeCategory(c) {
    return String(c || "").trim().toLowerCase();
  }

  function normalizeMoney(v) {
    const n = parseFloat(String(v ?? "0"));
    return isNaN(n) ? 0 : n;
  }

  function applyAllFilters() {
    const q = searchQuery.trim().toLowerCase();

    filtered = allProducts.filter((p) => {
      const name = String(p.name || "").toLowerCase();
      const cat = String(p.category || "").toLowerCase();
      const price = normalizeMoney(p.price);

      const matchSearch = !q || name.includes(q) || cat.includes(q);
      const matchCategory = activeCategory === "all" || cat === activeCategory;
      const matchPrice = price <= maxPrice;

      return matchSearch && matchCategory && matchPrice;
    });

    // sorting
    if (sortMode === "price_low") {
      filtered.sort((a, b) => normalizeMoney(a.price) - normalizeMoney(b.price));
    } else if (sortMode === "price_high") {
      filtered.sort((a, b) => normalizeMoney(b.price) - normalizeMoney(a.price));
    } else if (sortMode === "latest") {
      filtered.sort((a, b) => (b.id || 0) - (a.id || 0));
    } else {
      // default: keep API order (already by id DESC)
    }

    // if current page out of range after filter
    const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
    if (currentPage > totalPages) currentPage = 1;
  }

  function renderPagination() {
    if (!paginationWrap) return;

    const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
    let html = "";

    // Prev
    html += `<a href="#" class="page-link" data-page="prev">« Prev</a>`;

    // Pages
    for (let i = 1; i <= totalPages; i++) {
      html += `<a href="#" class="page-link ${i === currentPage ? "active" : ""}" data-page="${i}">${i}</a>`;
    }

    // Next
    html += `<a href="#" class="page-link" data-page="next">Next »</a>`;

    paginationWrap.innerHTML = html;
  }

  function renderGrid() {
    const start = (currentPage - 1) * pageSize;
    const pageItems = filtered.slice(start, start + pageSize);

    grid.innerHTML = "";

    pageItems.forEach((p) => {
      const oldPrice = p.old_price && normalizeMoney(p.old_price) > 0
        ? `<span class="price-old">$${normalizeMoney(p.old_price).toFixed(2)}</span>`
        : "";

      const saleBadge = p.old_price && normalizeMoney(p.old_price) > normalizeMoney(p.price)
        ? `<span class="badge badge-sale">Sale</span>`
        : "";

      const imgName = p.image ? `images/${p.image}` : `images/cap-1.jpg`;

      grid.innerHTML += `
        <article class="product-card">
          ${saleBadge}
          <div class="product-image">
            <a href="product.html?id=${p.id}">
              <img src="${imgName}" alt="${escapeHTML(p.name)}" />
            </a>
          </div>
          <h3 class="product-name">
            <a href="product.html?id=${p.id}">${escapeHTML(p.name)}</a>
          </h3>
          <div class="product-price-row">
            <span class="price-new">$${normalizeMoney(p.price).toFixed(2)}</span>
            ${oldPrice}
          </div>
          <p class="product-category">${escapeHTML(p.category || "")}</p>
          <button class="btn btn-primary small add-cart-btn">Add to cart</button>
        </article>
      `;
    });

    // ✅ IMPORTANT: your cart buttons need re-binding after re-render
    if (typeof setupCartButtonsOnCards === "function") {
      setupCartButtonsOnCards();
    }

    // update toolbar text
    if (toolbarText) {
      const total = filtered.length;
      const from = total === 0 ? 0 : start + 1;
      const to = Math.min(start + pageSize, total);
      toolbarText.textContent = `Showing ${from}–${to} of ${total} products`;
    }

    renderPagination();
  }

  function updateEverything() {
    applyAllFilters();
    renderGrid();
  }

  // --------- Load products from DB ----------
  async function loadAllProductsFromDB() {
    try {
      const res = await fetch("backend/products_list.php?mode=all");
      const data = await res.json();

      // your API returns {success:true, data:[...]}
      if (!data || !data.success) {
        console.log("Shop products API failed", data);
        return;
      }

      allProducts = Array.isArray(data.data) ? data.data : [];
      // keep default order (id DESC already)
      filtered = allProducts.slice();
      updateEverything();
    } catch (err) {
      console.log("Shop load error", err);
    }
  }

  // --------- Events ----------
  // Categories
  categoryLinks.forEach((a) => {
    a.addEventListener("click", (e) => {
      e.preventDefault();
      const text = (a.textContent || "").trim().toLowerCase();

      // match your sidebar labels
      if (text === "all") activeCategory = "all";
      else if (text === "t-shirts") activeCategory = "t-shirts";
      else activeCategory = text;

      currentPage = 1;

      // UI active highlight (optional)
      categoryLinks.forEach((x) => x.classList.remove("active"));
      a.classList.add("active");

      updateEverything();
    });
  });

  // Sidebar search (reuse your existing input)
  const shopSearchForm = document.querySelector(".shop-search");
  const shopSearchInput = shopSearchForm ? shopSearchForm.querySelector("input") : null;

  if (shopSearchForm && shopSearchInput) {
    shopSearchForm.addEventListener("submit", (e) => {
      e.preventDefault();
      searchQuery = shopSearchInput.value || "";
      currentPage = 1;
      updateEverything();
    });
  }

  // Price range
  if (priceRange) {
    priceRange.addEventListener("input", () => {
      maxPrice = parseInt(priceRange.value || "200", 10);

      // optional: show live max on UI (if you want later)
      currentPage = 1;
      updateEverything();
    });
  }

  // Sorting dropdown
  if (sortSelect) {
    sortSelect.addEventListener("change", () => {
      const v = (sortSelect.value || "").toLowerCase();

      // map your dropdown text
      if (v.includes("low to high")) sortMode = "price_low";
      else if (v.includes("high to low")) sortMode = "price_high";
      else if (v.includes("latest")) sortMode = "latest";
      else sortMode = "default";

      currentPage = 1;
      updateEverything();
    });
  }

  // Pagination clicks
  if (paginationWrap) {
    paginationWrap.addEventListener("click", (e) => {
      const link = e.target.closest(".page-link");
      if (!link) return;
      e.preventDefault();

      const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
      const p = link.getAttribute("data-page");

      if (p === "prev") currentPage = Math.max(1, currentPage - 1);
      else if (p === "next") currentPage = Math.min(totalPages, currentPage + 1);
      else currentPage = parseInt(p || "1", 10) || 1;

      renderGrid();
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  // finally load
  await loadAllProductsFromDB();
})();

// ❗Your original message ended with: "})(); }"
// That extra "}" breaks JS. Keeping it as a comment so nothing is "removed":
// }


// ===============================
// ACCESSIBILITY SYSTEM
// ===============================
// ===============================
// ===============================
// Eye Comfort: Click-to-Enlarge Text
// ===============================
(function () {
  const toggle = document.getElementById("eyeComfortToggle");
  if (!toggle) return;

  let enabled = false;
  let lastFocused = null;

  function clearFocus() {
    if (lastFocused) {
      lastFocused.classList.remove("eyecomfort-focus");
      lastFocused = null;
    }
  }

  toggle.addEventListener("click", () => {
    enabled = !enabled;
    toggle.classList.toggle("active", enabled);
    toggle.setAttribute("aria-pressed", enabled ? "true" : "false");
    clearFocus();
  });

  document.addEventListener("click", (e) => {
    if (!enabled) return;

    // ignore clicks on button itself
    if (e.target === toggle || toggle.contains(e.target)) return;

    // only text elements
    const el = e.target.closest(
      "p, span, h1, h2, h3, h4, h5, h6, li, a, label"
    );

    if (!el) {
      clearFocus();
      return;
    }

    clearFocus();
    el.classList.add("eyecomfort-focus");
    lastFocused = el;
  });

  // ESC to exit mode
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && enabled) {
      enabled = false;
      toggle.classList.remove("active");
      toggle.setAttribute("aria-pressed", "false");
      clearFocus();
    }
  });
})();



// ===============================
// Eye Comfort: Click-to-Enlarge Text (AUTO-INJECT)
// Works without changing HTML/CSS
// ===============================
(function () {
  if (window.__eyeComfortInit) return;
  window.__eyeComfortInit = true;

  function injectStyles() {
    if (document.getElementById("eyecomfortStyles")) return;

    const style = document.createElement("style");
    style.id = "eyecomfortStyles";
    style.textContent = `
      .eyecomfort-btn {
        position: fixed;
        top: 50%;
        right: 18px;
        transform: translateY(-50%);
        width: 56px;
        height: 56px;
        border-radius: 50%;
        border: none;
        background: #ffffff;
        box-shadow: 0 12px 30px rgba(0,0,0,.25);
        cursor: pointer;
        font-size: 26px;
        z-index: 999999;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .eyecomfort-btn.active {
        background: #111;
        color: #fff;
      }

      .eyecomfort-focus {
        font-size: 1.6em !important;
        line-height: 1.6 !important;
        background: #fff9c4 !important;
        padding: 6px 8px !important;
        border-radius: 6px !important;
        transition: all .15s ease;
      }
    `;
    document.head.appendChild(style);
  }

  function createButton() {
    if (document.getElementById("eyeComfortToggle")) return;

    const btn = document.createElement("button");
    btn.id = "eyeComfortToggle";
    btn.className = "eyecomfort-btn";
    btn.type = "button";
    btn.setAttribute("aria-label", "Eye comfort mode");
    btn.setAttribute("aria-pressed", "false");
    btn.innerHTML = "👁";

    document.body.appendChild(btn);
  }

  function initLogic() {
    const toggle = document.getElementById("eyeComfortToggle");
    if (!toggle) return;

    let enabled = false;
    let lastFocused = null;

    function clearFocus() {
      if (lastFocused) {
        lastFocused.classList.remove("eyecomfort-focus");
        lastFocused = null;
      }
    }

    toggle.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      enabled = !enabled;
      toggle.classList.toggle("active", enabled);
      toggle.setAttribute("aria-pressed", enabled ? "true" : "false");
      clearFocus();
    });

    document.addEventListener(
      "click",
      (e) => {
        if (!enabled) return;
        if (e.target === toggle || toggle.contains(e.target)) return;

        const el = e.target.closest(
          "p, span, h1, h2, h3, h4, h5, h6, li, a, label, small, strong, em"
        );

        if (!el) {
          clearFocus();
          return;
        }

        clearFocus();
        el.classList.add("eyecomfort-focus");
        lastFocused = el;
      },
      true
    );

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && enabled) {
        enabled = false;
        toggle.classList.remove("active");
        toggle.setAttribute("aria-pressed", "false");
        clearFocus();
      }
    });
  }

  function boot() {
    injectStyles();
    createButton();
    initLogic();
    console.log("✅ EyeComfort loaded (middle-right)");
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();



// ========= COOKIE CONSENT =========


