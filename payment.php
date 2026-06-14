<?php
/* =====================================================
   PAYMENT  (customer: pay for a placed order)
   A simulated payment step — no real money moves and no card
   details are stored. Marking the order paid moves it into the
   "processing" queue the admin works from. Logic runs before
   any HTML so it can redirect.
   ===================================================== */
include_once __DIR__ . "/functions/functions.php";

if (!isLoggedIn()) {
    header("Location: account.php?tab=login");
    exit;
}

$order_id = isset($_GET['order']) ? (int) $_GET['order'] : 0;
$user_id  = currentUserId();

// Load the order and make sure it belongs to the signed-in customer.
$order = null;
if ($order_id > 0 && ($con instanceof PDO)) {
    $stmt = mysqli_prepare(
        $con,
        "SELECT order_id, order_total, payment_status, payment_method
           FROM orders WHERE order_id = ? AND user_id = ? LIMIT 1"
    );
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $order = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
    }
}

if (!$order) {
    header("Location: orders.php");
    exit;
}

// Already paid? Nothing to do here.
if ($order['payment_status'] === 'paid') {
    header("Location: orders.php?paid=" . $order_id);
    exit;
}

$error = "";

if (isset($_POST['pay_now'])) {
    // Light sanity check on the demo card field; nothing is stored.
    $card = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
    if (strlen($card) < 12) {
        $error = "Please enter a valid card number (demo: any 12+ digits).";
    } else {
        $upd = mysqli_prepare(
            $con,
            "UPDATE orders
                SET payment_status = 'paid', order_status = 'processing'
              WHERE order_id = ? AND user_id = ?"
        );
        if ($upd) {
            mysqli_stmt_bind_param($upd, "ii", $order_id, $user_id);
            mysqli_stmt_execute($upd);
            mysqli_stmt_close($upd);
        }

        header("Location: orders.php?paid=" . $order_id);
        exit;
    }
}

$page_title = "Payment - My Online Shop";
include("header.php");
?>

<section class="section">
    <div class="section_head">
        <h2 class="section_title">Payment</h2>
        <p class="section_subtitle">Order #<?php echo (int) $order_id; ?></p>
    </div>

    <div class="card" style="max-width:480px;margin:0 auto;">
        <p class="pay_amount">
            Amount due: <strong>$ <?php echo number_format((float) $order['order_total'], 2); ?></strong>
        </p>
        <p class="hint">This is a demo checkout — no real payment is taken.</p>

        <?php if ($error): ?>
            <p class="alert_success" style="background:#fdecea;color:#c0392b;">
                <?php echo htmlspecialchars($error); ?>
            </p>
        <?php endif; ?>

        <form method="post" action="payment.php?order=<?php echo (int) $order_id; ?>">
            <div class="field">
                <label>Name on card</label>
                <input type="text" name="card_name" placeholder="Jane Doe" required />
            </div>
            <div class="field">
                <label>Card number</label>
                <input type="text" name="card_number"
                       placeholder="4242 4242 4242 4242" required />
            </div>
            <div class="form_actions">
                <a href="orders.php" class="btn btn_outline">Pay later</a>
                <button type="submit" name="pay_now" class="btn btn_primary">
                    Pay $ <?php echo number_format((float) $order['order_total'], 2); ?>
                </button>
            </div>
        </form>
    </div>
</section>

<?php include("footer.php"); ?>
