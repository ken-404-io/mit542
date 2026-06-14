<?php

/* =====================================================
   CORE FUNCTIONS & DATABASE CONNECTION
   ===================================================== */

// Start a session so the shopping cart works across pages.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Use classic (non-throwing) mysqli error handling so a failed query
// degrades gracefully instead of throwing a fatal exception.
mysqli_report(MYSQLI_REPORT_OFF);

// Database connection. Settings come from environment variables (so the app
// runs in Docker against a separate DB container) and fall back to the classic
// local XAMPP defaults when they are not set.
$db_host = getenv("DB_HOST") ?: "localhost";
$db_user = getenv("DB_USER") ?: "root";
$db_pass = getenv("DB_PASS") ?: "";
$db_name = getenv("DB_NAME") ?: "ecommerce_website";
$con = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);

/* -----------------------------------------------------
   Run a query, but only when we actually hold a live
   connection. If the database is unreachable, mysqli_connect()
   returns false; passing that straight to mysqli_query() throws
   a fatal TypeError on PHP 8. Returning false here instead lets
   every caller fall back to its empty-state message and keeps
   the page rendering.
   ----------------------------------------------------- */
function dbQuery($sql) {
    global $con;
    if (!($con instanceof mysqli)) {
        return false;
    }
    return mysqli_query($con, $sql);
}

/* -----------------------------------------------------
   Sidebar: list all product categories.
   ----------------------------------------------------- */
function getCats() {
    global $con;
    $run = dbQuery("SELECT * FROM categories");
    if (!$run) {
        return;
    }
    while ($row = mysqli_fetch_array($run)) {
        $cat_id    = $row['cat_id'];
        $cat_title = $row['cat_title'];
        echo "<li><a href='index.php?cat=$cat_id'>$cat_title</a></li>";
    }
}

/* -----------------------------------------------------
   Sidebar: list all product brands.
   ----------------------------------------------------- */
function getBrands() {
    global $con;
    $run = dbQuery("SELECT * FROM brands");
    if (!$run) {
        return;
    }
    while ($row = mysqli_fetch_array($run)) {
        $brand_id    = $row['brand_id'];
        $brand_title = $row['brand_title'];
        echo "<li><a href='index.php?brand=$brand_id'>$brand_title</a></li>";
    }
}

/* -----------------------------------------------------
   Reusable product card markup (used everywhere a grid
   of products is shown) so the design stays consistent.
   ----------------------------------------------------- */
function renderProductCard($row) {
    $pro_id    = $row['product_id'];
    $pro_title = $row['product_title'];
    $pro_price = $row['product_price'];
    $pro_image = $row['product_image'];

    echo "
    <div class='product_card'>
        <a href='details.php?pro_id=$pro_id' class='product_thumb'>
            <img src='images/$pro_image' alt='$pro_title' />
        </a>
        <div class='product_body'>
            <h3 class='product_title'>$pro_title</h3>
            <p class='product_price'>\$ $pro_price</p>
            <div class='product_actions'>
                <a class='btn btn_outline'
                   href='details.php?pro_id=$pro_id'>Details</a>
                <a class='btn btn_primary'
                   href='cart.php?add=$pro_id'>Add to Cart</a>
            </div>
        </div>
    </div>";
}

/* -----------------------------------------------------
   Home page: show six random featured products.

   When the visitor picks a category or brand from the
   sidebar, this random feed steps aside so that
   getCatPro() / getBrandPro() own the content area.
   ----------------------------------------------------- */
function getPro() {
    global $con;
    if (isset($_GET['cat']) || isset($_GET['brand'])) {
        return;
    }
    $run = dbQuery("SELECT * FROM products ORDER BY RAND() LIMIT 0,6");
    if (!$run || mysqli_num_rows($run) === 0) {
        echo "<p class='empty_state'>No products available yet.</p>";
        return;
    }
    while ($row = mysqli_fetch_array($run)) {
        renderProductCard($row);
    }
}

/* -----------------------------------------------------
   Sidebar filter: show only the products that belong to
   the category chosen via index.php?cat=ID.
   ----------------------------------------------------- */
function getCatPro() {
    global $con;
    if (!isset($_GET['cat'])) {
        return;
    }
    $cat_id = (int) $_GET['cat'];
    $run = dbQuery("SELECT * FROM products WHERE product_cat='$cat_id'");
    if (!$run || mysqli_num_rows($run) === 0) {
        echo "<p class='empty_state'>
                There are no products in this category yet.
                <a href='index.php'>Back to home</a>
              </p>";
        return;
    }
    while ($row = mysqli_fetch_array($run)) {
        renderProductCard($row);
    }
}

/* -----------------------------------------------------
   Sidebar filter: show only the products that belong to
   the brand chosen via index.php?brand=ID.
   ----------------------------------------------------- */
function getBrandPro() {
    global $con;
    if (!isset($_GET['brand'])) {
        return;
    }
    $brand_id = (int) $_GET['brand'];
    $run = dbQuery("SELECT * FROM products WHERE product_brand='$brand_id'");
    if (!$run || mysqli_num_rows($run) === 0) {
        echo "<p class='empty_state'>
                There are no products for this brand yet.
                <a href='index.php'>Back to home</a>
              </p>";
        return;
    }
    while ($row = mysqli_fetch_array($run)) {
        renderProductCard($row);
    }
}

/* -----------------------------------------------------
   Show every product (used by listing pages).
   ----------------------------------------------------- */
function getProducts() {
    global $con;
    $run = dbQuery("SELECT * FROM products");
    if (!$run || mysqli_num_rows($run) === 0) {
        echo "<p class='empty_state'>No products available yet.</p>";
        return;
    }
    while ($row = mysqli_fetch_array($run)) {
        renderProductCard($row);
    }
}

/* -----------------------------------------------------
   Total number of items currently in the cart.
   ----------------------------------------------------- */
function cartCount() {
    if (!empty($_SESSION['cart'])) {
        return array_sum($_SESSION['cart']);
    }
    return 0;
}

/* -----------------------------------------------------
   Auth helpers: is a user signed in, and what is their
   display name? Set by google_callback.php / account.php.
   ----------------------------------------------------- */
function isLoggedIn() {
    return !empty($_SESSION['user_logged_in']);
}

function currentUserName() {
    if (!empty($_SESSION['user_name'])) {
        return $_SESSION['user_name'];
    }
    if (!empty($_SESSION['user_email'])) {
        return $_SESSION['user_email'];
    }
    return 'Account';
}

?>
