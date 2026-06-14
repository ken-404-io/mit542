<?php
/* =====================================================
   ORDER DETAIL  (admin: process a single order)
   Full view of one order: customer info, line items, and a
   control to change its fulfilment / payment status.
   ===================================================== */
include("includes/auth.php");
include("includes/db.php");
include_once __DIR__ . "/../functions/order_helpers.php";

$order_id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['order_id'] ?? 0);
$message = "";

if (isset($_POST['update_order']) && $con && $order_id > 0) {
    $new_status  = trim($_POST['order_status']);
    $new_payment = trim($_POST['payment_status']);
    if (in_array($new_status, orderStatuses(), true)
        && in_array($new_payment, array('unpaid', 'paid'), true)) {
        $stmt = mysqli_prepare(
            $con,
            "UPDATE orders SET order_status = ?, payment_status = ? WHERE order_id = ?"
        );
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssi", $new_status, $new_payment, $order_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $message = "Order updated.";
        }
    }
}

$order = null;
if ($order_id > 0 && $con) {
    $res = dbQuery("SELECT * FROM orders WHERE order_id = '$order_id'");
    $order = $res ? mysqli_fetch_assoc($res) : null;
}

$admin_title = "Order #" . $order_id;
$active_nav  = "orders";
include("includes/admin_header.php");
?>

<div class="page_head">
    <h2>Order #<?php echo (int) $order_id; ?></h2>
    <p><a href="orders.php">&larr; Back to orders</a></p>
</div>

<?php if ($message): ?>
    <div class="alert alert_success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if (!$order): ?>
    <div class="card"><p class="empty_state">Order not found.</p></div>
<?php else: ?>

<div class="card">
    <h3>Customer</h3>
    <p>
        <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br />
        <?php echo htmlspecialchars($order['customer_email']); ?><br />
        <?php if ($order['customer_phone'] !== ''): ?>
            <?php echo htmlspecialchars($order['customer_phone']); ?><br />
        <?php endif; ?>
        <?php echo nl2br(htmlspecialchars($order['customer_address'])); ?>
    </p>
    <p class="hint">Placed <?php echo htmlspecialchars($order['created_at']); ?>
        &middot; Payment method: <?php echo htmlspecialchars($order['payment_method']); ?></p>
</div>

<div class="card">
    <h3>Items</h3>
    <table class="admin_table">
        <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
        <tbody>
            <?php
            $items = dbQuery("SELECT * FROM order_items WHERE order_id = '$order_id'");
            while ($items && $it = mysqli_fetch_assoc($items)): ?>
            <tr>
                <td><?php echo htmlspecialchars($it['product_title']); ?></td>
                <td>$ <?php echo number_format((float) $it['unit_price'], 2); ?></td>
                <td><?php echo (int) $it['quantity']; ?></td>
                <td>$ <?php echo number_format((float) $it['subtotal'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="cart_total_label">Total</td>
                <td class="cart_total_value">$ <?php echo number_format((float) $order['order_total'], 2); ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="card">
    <h3>Update status</h3>
    <form method="post" action="order_view.php?id=<?php echo (int) $order_id; ?>">
        <input type="hidden" name="order_id" value="<?php echo (int) $order_id; ?>" />
        <div class="form_grid">
            <div class="field">
                <label>Fulfilment status</label>
                <select name="order_status">
                    <?php foreach (orderStatuses() as $s):
                        $sel = ($s === $order['order_status']) ? 'selected' : ''; ?>
                        <option value="<?php echo $s; ?>" <?php echo $sel; ?>><?php echo ucfirst($s); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Payment status</label>
                <select name="payment_status">
                    <?php foreach (array('unpaid', 'paid') as $s):
                        $sel = ($s === $order['payment_status']) ? 'selected' : ''; ?>
                        <option value="<?php echo $s; ?>" <?php echo $sel; ?>><?php echo ucfirst($s); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form_actions">
                <button type="submit" name="update_order" class="btn btn_primary">Save</button>
            </div>
        </div>
    </form>
</div>

<?php endif; ?>

<?php include("includes/admin_footer.php"); ?>
