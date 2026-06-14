<?php
/* =====================================================
   REPORTS  (admin: generate reports)
   A sales summary: revenue from paid orders, order counts by
   status, and the best-selling products.
   ===================================================== */
include("includes/auth.php");
include("includes/db.php");
include_once __DIR__ . "/../functions/order_helpers.php";   // statusBadge()

// Single-value query helper (returns 0 / '' on any failure).
function scalar($con, $sql, $default = 0) {
    if (!$con) { return $default; }
    $res = dbQuery($sql);
    if (!$res || mysqli_num_rows($res) === 0) { return $default; }
    $row = mysqli_fetch_row($res);
    return $row[0] === null ? $default : $row[0];
}

$total_orders  = (int)   scalar($con, "SELECT COUNT(*) FROM orders");
$paid_orders   = (int)   scalar($con, "SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'");
$total_revenue = (float) scalar($con, "SELECT SUM(order_total) FROM orders WHERE payment_status = 'paid'");
$avg_order     = $paid_orders > 0 ? $total_revenue / $paid_orders : 0;

$admin_title = "Reports";
$active_nav  = "reports";
include("includes/admin_header.php");
?>

<div class="page_head">
    <h2>Sales Reports</h2>
    <p>Revenue and order performance at a glance.</p>
</div>

<div class="stat_grid">
    <div class="stat_card">
        <div class="stat_icon">&#128176;</div>
        <div class="stat_meta">
            <div class="num">$ <?php echo number_format($total_revenue, 2); ?></div>
            <div class="label">Revenue (paid)</div>
        </div>
    </div>
    <div class="stat_card">
        <div class="stat_icon">&#128722;</div>
        <div class="stat_meta">
            <div class="num"><?php echo $total_orders; ?></div>
            <div class="label">Total Orders</div>
        </div>
    </div>
    <div class="stat_card">
        <div class="stat_icon">&#9989;</div>
        <div class="stat_meta">
            <div class="num"><?php echo $paid_orders; ?></div>
            <div class="label">Paid Orders</div>
        </div>
    </div>
    <div class="stat_card">
        <div class="stat_icon">&#128200;</div>
        <div class="stat_meta">
            <div class="num">$ <?php echo number_format($avg_order, 2); ?></div>
            <div class="label">Avg. Order Value</div>
        </div>
    </div>
</div>

<div class="card">
    <h3>Orders by Status</h3>
    <table class="admin_table">
        <thead><tr><th>Status</th><th>Orders</th></tr></thead>
        <tbody>
            <?php
            $by_status = $con ? dbQuery(
                "SELECT order_status, COUNT(*) AS c FROM orders GROUP BY order_status"
            ) : false;
            if ($by_status && mysqli_num_rows($by_status) > 0):
                while ($r = mysqli_fetch_assoc($by_status)): ?>
                    <tr>
                        <td><?php echo statusBadge($r['order_status']); ?></td>
                        <td><?php echo (int) $r['c']; ?></td>
                    </tr>
                <?php endwhile;
            else: ?>
                <tr><td colspan="2" class="hint">No orders yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h3>Top-Selling Products</h3>
    <table class="admin_table">
        <thead><tr><th>Product</th><th>Units Sold</th><th>Revenue</th></tr></thead>
        <tbody>
            <?php
            // Counts every order's items; revenue here is gross across all orders.
            $top = $con ? dbQuery(
                "SELECT product_title,
                        SUM(quantity) AS units,
                        SUM(subtotal) AS revenue
                   FROM order_items
                  GROUP BY product_title
                  ORDER BY units DESC
                  LIMIT 10"
            ) : false;
            if ($top && mysqli_num_rows($top) > 0):
                while ($r = mysqli_fetch_assoc($top)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['product_title']); ?></td>
                        <td><?php echo (int) $r['units']; ?></td>
                        <td>$ <?php echo number_format((float) $r['revenue'], 2); ?></td>
                    </tr>
                <?php endwhile;
            else: ?>
                <tr><td colspan="3" class="hint">No sales data yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include("includes/admin_footer.php"); ?>
