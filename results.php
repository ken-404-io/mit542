<?php
$page_title = "Search Results - My Online Shop";
include("header.php");

// Build the listing query from search, category or brand filters.
$heading = "All Products";
$where   = "";

if (isset($_GET['user_query']) && trim($_GET['user_query']) !== "") {
    $raw     = trim($_GET['user_query']);
    // Only escape against a live connection; without one mysqli_real_escape_string()
    // would throw a fatal TypeError. dbQuery() below already no-ops without a DB.
    $q       = ($con instanceof PDO) ? mysqli_real_escape_string($con, $raw) : addslashes($raw);
    $where   = "WHERE product_title ILIKE '%$q%'
                OR product_keywords ILIKE '%$q%'
                OR product_desc ILIKE '%$q%'";
    $heading = "Results for \"" . htmlspecialchars($_GET['user_query']) . "\"";
} elseif (isset($_GET['cat'])) {
    $cat     = (int) $_GET['cat'];
    $where   = "WHERE product_cat='$cat'";
    $heading = "Browsing Category";
} elseif (isset($_GET['brand'])) {
    $brand   = (int) $_GET['brand'];
    $where   = "WHERE product_brand='$brand'";
    $heading = "Browsing Brand";
}

$run = dbQuery("SELECT * FROM products $where");
?>

<section class="section">
    <div class="section_head">
        <h2 class="section_title"><?php echo $heading; ?></h2>
    </div>
    <div class="product_grid">
        <?php
        if ($run && mysqli_num_rows($run) > 0) {
            while ($row = mysqli_fetch_array($run)) {
                renderProductCard($row);
            }
        } else {
            echo "<p class='empty_state'>
                    No products matched your search.
                    <a href='index.php'>Back to home</a>
                  </p>";
        }
        ?>
    </div>
</section>

<?php include("footer.php"); ?>
