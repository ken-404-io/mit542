<?php
/* =====================================================
   MY ORDERS  (customer: track transactions)
   Lists the signed-in customer's orders with their payment
   and fulfilment status, plus the line items for each one.
   ===================================================== */
include_once __DIR__ . "/functions/functions.php";

if (!isLoggedIn()) {
    header("Location: account.php?tab=login");
    exit;
}

$user_id = currentUserId();

$page_title = "My Orders - My Online Shop";
include("header.php");

// Friendly confirmation banners after placing / paying.
$placed = isset($_GET['placed']) ? (int) $_GET['placed'] : 0;
$paid   = isset($_GET['paid'])   ? (int) $_GET['paid']   : 0;
?>

<section class="section">
    <div class="section_head">
        <h2 class="section_title">My Orders</h2>
        <p class="section_subtitle">Track your orders and payment status.</p>
    </div>

    <?php if ($placed): ?>
        <p class="alert_success">
            Order #<?php echo $placed; ?> placed successfully. Pay on delivery — thank you!
        </p>
    <?php elseif ($paid): ?>
        <p class="alert_success">
            Payment received for order #<?php echo $paid; ?>. We're getting it ready.
        </p>
    <?php endif; ?>

    <?php
    $run = false;
    if ($con instanceof mysqli) {
        $stmt = mysqli_prepare(
            $con,
            "SELECT order_id, order_total, payment_method, payment_status,
                    order_status, created_at
               FROM orders WHERE user_id = ? ORDER BY order_id DESC"
        );
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $run = mysqli_stmt_get_result($stmt);
        }
    }

    if (!$run || mysqli_num_rows($run) === 0):
    ?>
        <p class="empty_state">
            You haven't placed any orders yet.
            <a href="index.php">Start shopping</a>
        </p>
    <?php else: ?>
        <?php while ($order = mysqli_fetch_assoc($run)):
            $oid = (int) $order['order_id'];
        ?>
        <div class="card order_card">
            <div class="order_card_head">
                <div>
                    <strong>Order #<?php echo $oid; ?></strong>
                    <span class="hint"><?php echo htmlspecialchars($order['created_at']); ?></span>
                </div>
                <div class="order_card_status">
                    <?php echo statusBadge($order['order_status']); ?>
                    <?php echo statusBadge($order['payment_status']); ?>
                </div>
            </div>

            <table class="cart_table">
                <tbody>
                    <?php
                    $items = dbQuery(
                        "SELECT product_title, unit_price, quantity, subtotal
                           FROM order_items WHERE order_id = '$oid'"
                    );
                    while ($items && $it = mysqli_fetch_assoc($items)):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($it['product_title']); ?>
                            &times; <?php echo (int) $it['quantity']; ?></td>
                        <td style="text-align:right;">
                            $ <?php echo number_format((float) $it['subtotal'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="cart_total_label">Total</td>
                        <td class="cart_total_value" style="text-align:right;">
                            $ <?php echo number_format((float) $order['order_total'], 2); ?></td>
                    </tr>
                </tfoot>
            </table>

            <?php if ($order['payment_status'] === 'unpaid' && $order['payment_method'] !== 'cod'): ?>
                <div class="form_actions">
                    <a href="payment.php?order=<?php echo $oid; ?>"
                       class="btn btn_primary">Pay now</a>
                </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
</section>

<?php include("footer.php"); ?>
