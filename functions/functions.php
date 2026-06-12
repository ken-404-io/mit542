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

// Database connection.
$con = @mysqli_connect("localhost", "root", "", "ecommerce_website");

/* -----------------------------------------------------
   Sidebar: list all product categories.
   ----------------------------------------------------- */
function getCats() {
    global $con;
    $run = mysqli_query($con, "SELECT * FROM categories");
    if (!$run) {
        return;
    }
    while ($row = mysqli_fetch_array($run)) {
        $cat_id    = $row['cat_id'];
        $cat_title = $row['cat_title'];
        echo "<li><a href='results.php?cat=$cat_id'>$cat_title</a></li>";
    }
}

/* -----------------------------------------------------
   Sidebar: list all product brands.
   ----------------------------------------------------- */
function getBrands() {
    global $con;
    $run = mysqli_query($con, "SELECT * FROM brands");
    if (!$run) {
        return;
    }
    while ($row = mysqli_fetch_array($run)) {
        $brand_id    = $row['brand_id'];
        $brand_title = $row['brand_title'];
        echo "<li><a href='results.php?brand=$brand_id'>$brand_title</a></li>";
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
   ----------------------------------------------------- */
function getPro() {
    global $con;
    $run = mysqli_query($con, "SELECT * FROM products ORDER BY RAND() LIMIT 0,6");
    if (!$run || mysqli_num_rows($run) === 0) {
        echo "<p class='empty_state'>No products available yet.</p>";
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
    $run = mysqli_query($con, "SELECT * FROM products");
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

?>
