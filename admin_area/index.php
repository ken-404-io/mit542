<?php
/* =====================================================
   ADMIN DASHBOARD
   ===================================================== */
include("includes/auth.php");   // must be logged in
include("includes/db.php");

// Small helper: COUNT(*) for a table, safe if the table is missing.
function countRows($con, $table) {
    if (!$con) {
        return 0;
    }
    $res = mysqli_query($con, "SELECT COUNT(*) AS c FROM `$table`");
    if (!$res) {
        return 0;
    }
    $row = mysqli_fetch_assoc($res);
    return (int) $row['c'];
}

$total_products   = countRows($con, "products");
$total_categories = countRows($con, "categories");
$total_brands     = countRows($con, "brands");

$admin_title = "Dashboard";
$active_nav  = "dashboard";
include("includes/admin_header.php");
?>

<div class="page_head">
    <h2>Dashboard</h2>
    <p>Overview of your store at a glance.</p>
</div>

<div class="stat_grid">
    <div class="stat_card">
        <div class="stat_icon">&#128230;</div>
        <div class="stat_meta">
            <div class="num"><?php echo $total_products; ?></div>
            <div class="label">Products</div>
        </div>
    </div>
    <div class="stat_card">
        <div class="stat_icon">&#128193;</div>
        <div class="stat_meta">
            <div class="num"><?php echo $total_categories; ?></div>
            <div class="label">Categories</div>
        </div>
    </div>
    <div class="stat_card">
        <div class="stat_icon">&#127991;</div>
        <div class="stat_meta">
            <div class="num"><?php echo $total_brands; ?></div>
            <div class="label">Brands</div>
        </div>
    </div>
</div>

<div class="card">
    <h3>Quick Actions</h3>
    <div class="form_actions" style="margin-top:0;">
        <a href="insert_product.php" class="btn btn_primary">&#43; Insert New Product</a>
        <a href="../index.php" target="_blank" class="btn btn_outline">View Storefront</a>
    </div>
</div>

<?php include("includes/admin_footer.php"); ?>
