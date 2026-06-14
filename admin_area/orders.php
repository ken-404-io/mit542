<?php
/* =====================================================
   ORDERS  (admin: process orders)
   Lists customer orders, lets the admin filter by status and
   advance an order's fulfilment status inline.
   ===================================================== */
include("includes/auth.php");
include("includes/db.php");
include_once __DIR__ . "/../functions/order_helpers.php";   // statusBadge(), orderStatuses()

$message = "";

// Update an order's status.
if (isset($_POST['update_status']) && $con) {
    $oid        = (int) $_POST['order_id'];
    $new_status = trim($_POST['order_status']);
    if ($oid > 0 && in_array($new_status, orderStatuses(), true)) {
        $stmt = mysqli_prepare($con, "UPDATE orders SET order_status = ? WHERE order_id = ?");
        mysqli_stmt_bind_param($stmt, "si", $new_status, $oid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $message = "Order #$oid updated to " . ucfirst($new_status) . ".";
    }
}

// Optional status filter from the tab strip.
$filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$valid_filter = in_array($filter, orderStatuses(), true);

$admin_title = "Orders";
$active_nav  = "orders";
include("includes/admin_header.php");
?>

<div class="page_head">
    <h2>Orders</h2>
    <p>Review customer orders and move them through fulfilment.</p>
</div>

<?php if ($message): ?>
    <div class="alert alert_success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="card">
    <div class="filter_tabs">
        <a href="orders.php" class="<?php echo $valid_filter ? '' : 'active'; ?>">All</a>
        <?php foreach (orderStatuses() as $s): ?>
            <a href="orders.php?status=<?php echo $s; ?>"
               class="<?php echo ($filter === $s) ? 'active' : ''; ?>"><?php echo ucfirst($s); ?></a>
        <?php endforeach; ?>
    </div>

    <?php
    if ($con) {
        if ($valid_filter) {
            $stmt = mysqli_prepare(
                $con,
                "SELECT * FROM orders WHERE order_status = ? ORDER BY order_id DESC"
            );
            mysqli_stmt_bind_param($stmt, "s", $filter);
            mysqli_stmt_execute($stmt);
            $run = mysqli_stmt_get_result($stmt);
        } else {
            $run = dbQuery("SELECT * FROM orders ORDER BY order_id DESC");
        }
    } else {
        $run = false;
    }

    if (!$run || mysqli_num_rows($run) === 0): ?>
        <p class="empty_state">No orders to show.</p>
    <?php else: ?>
        <table class="admin_table">
            <thead>
                <tr>
                    <th>#</th><th>Customer</th><th>Total</th><th>Payment</th>
                    <th>Status</th><th>Date</th><th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($o = mysqli_fetch_assoc($run)):
                    $oid = (int) $o['order_id']; ?>
                <tr>
                    <td><a href="order_view.php?id=<?php echo $oid; ?>">#<?php echo $oid; ?></a></td>
                    <td>
                        <?php echo htmlspecialchars($o['customer_name']); ?><br />
                        <span class="hint"><?php echo htmlspecialchars($o['customer_email']); ?></span>
                    </td>
                    <td>$ <?php echo number_format((float) $o['order_total'], 2); ?></td>
                    <td><?php echo statusBadge($o['payment_status']); ?></td>
                    <td><?php echo statusBadge($o['order_status']); ?></td>
                    <td class="hint"><?php echo htmlspecialchars($o['created_at']); ?></td>
                    <td>
                        <form method="post" action="orders.php<?php echo $valid_filter ? '?status=' . htmlspecialchars($filter) : ''; ?>"
                              class="status_form">
                            <input type="hidden" name="order_id" value="<?php echo $oid; ?>" />
                            <select name="order_status">
                                <?php foreach (orderStatuses() as $s):
                                    $sel = ($s === $o['order_status']) ? 'selected' : ''; ?>
                                    <option value="<?php echo $s; ?>" <?php echo $sel; ?>>
                                        <?php echo ucfirst($s); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_status"
                                    class="btn btn_primary btn_sm">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include("includes/admin_footer.php"); ?>
