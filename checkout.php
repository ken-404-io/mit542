<?php
/* =====================================================
   CHECKOUT  (customer: place an order)
   Collects delivery details, turns the session cart into a
   real order + line items, then sends the customer to the
   payment step. Runs all logic before any HTML is sent so we
   can redirect cleanly.
   ===================================================== */
include_once __DIR__ . "/functions/functions.php";

// Must be signed in to place an order (so it can be tracked).
if (!isLoggedIn()) {
    header("Location: account.php?tab=login&checkout=1");
    exit;
}

// Nothing to check out?
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

/* ---- Build the order lines from the cart (priced from the DB) ---- */
$lines = array();
$grand_total = 0.00;
foreach ($_SESSION['cart'] as $id => $qty) {
    $id  = (int) $id;
    $qty = (int) $qty;
    if ($id <= 0 || $qty <= 0) {
        continue;
    }
    $run = dbQuery("SELECT * FROM products WHERE product_id='$id'");
    if (!$run || mysqli_num_rows($run) === 0) {
        continue;
    }
    $row      = mysqli_fetch_assoc($run);
    $price    = (float) $row['product_price'];
    $subtotal = $price * $qty;
    $grand_total += $subtotal;
    $lines[] = array(
        'product_id' => $id,
        'title'      => $row['product_title'],
        'price'      => $price,
        'qty'        => $qty,
        'subtotal'   => $subtotal,
    );
}

if (empty($lines)) {
    header("Location: cart.php");
    exit;
}

$error = "";

/* ---- Place the order ---- */
if (isset($_POST['place_order'])) {
    $name    = trim($_POST['customer_name'] ?? '');
    $email   = trim($_POST['customer_email'] ?? '');
    $phone   = trim($_POST['customer_phone'] ?? '');
    $address = trim($_POST['customer_address'] ?? '');
    $method  = trim($_POST['payment_method'] ?? '');

    $allowed_methods = array('card', 'paypal', 'cod');

    if ($name === '' || $email === '' || $address === '') {
        $error = "Please fill in your name, email and delivery address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!in_array($method, $allowed_methods, true)) {
        $error = "Please choose a payment method.";
    } elseif (!($con instanceof mysqli)) {
        $error = "We can't place orders right now. Please try again later.";
    } else {
        $user_id = currentUserId();
        $stmt = mysqli_prepare(
            $con,
            "INSERT INTO orders
                (user_id, customer_name, customer_email, customer_phone,
                 customer_address, order_total, payment_method, payment_status, order_status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'unpaid', 'pending')"
        );
        mysqli_stmt_bind_param(
            $stmt, "issssds",
            $user_id, $name, $email, $phone, $address, $grand_total, $method
        );

        if (mysqli_stmt_execute($stmt)) {
            $order_id = (int) mysqli_insert_id($con);
            mysqli_stmt_close($stmt);

            // Save each line item with a price/title snapshot.
            $item_stmt = mysqli_prepare(
                $con,
                "INSERT INTO order_items
                    (order_id, product_id, product_title, unit_price, quantity, subtotal)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            foreach ($lines as $line) {
                mysqli_stmt_bind_param(
                    $item_stmt, "iisdid",
                    $order_id, $line['product_id'], $line['title'],
                    $line['price'], $line['qty'], $line['subtotal']
                );
                mysqli_stmt_execute($item_stmt);
            }
            mysqli_stmt_close($item_stmt);

            // Cash on delivery skips the online payment step.
            $_SESSION['cart'] = array();
            if ($method === 'cod') {
                header("Location: orders.php?placed=" . $order_id);
            } else {
                header("Location: payment.php?order=" . $order_id);
            }
            exit;
        }
        mysqli_stmt_close($stmt);
        $error = "Sorry, we couldn't place your order. Please try again.";
    }
}

$page_title = "Checkout - My Online Shop";
include("header.php");
?>

<section class="section">
    <div class="section_head">
        <h2 class="section_title">Checkout</h2>
        <p class="section_subtitle">Confirm your details and place your order.</p>
    </div>

    <?php if ($error): ?>
        <p class="alert_success" style="background:#fdecea;color:#c0392b;">
            <?php echo htmlspecialchars($error); ?>
        </p>
    <?php endif; ?>

    <div class="checkout_layout">
        <!-- ===== DELIVERY + PAYMENT FORM ===== -->
        <form class="card checkout_form" method="post" action="checkout.php">
            <h3>Delivery details</h3>
            <div class="field">
                <label>Full name</label>
                <input type="text" name="customer_name" required
                       value="<?php echo htmlspecialchars($_POST['customer_name'] ?? currentUserName()); ?>" />
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="customer_email" required
                       value="<?php echo htmlspecialchars($_POST['customer_email'] ?? ($_SESSION['user_email'] ?? '')); ?>" />
            </div>
            <div class="field">
                <label>Phone <span class="hint">(optional)</span></label>
                <input type="text" name="customer_phone"
                       value="<?php echo htmlspecialchars($_POST['customer_phone'] ?? ''); ?>" />
            </div>
            <div class="field">
                <label>Delivery address</label>
                <textarea name="customer_address" required
                          placeholder="Street, city, postcode"><?php echo htmlspecialchars($_POST['customer_address'] ?? ''); ?></textarea>
            </div>

            <h3>Payment method</h3>
            <label class="pay_option">
                <input type="radio" name="payment_method" value="card" checked />
                Credit / Debit card
            </label>
            <label class="pay_option">
                <input type="radio" name="payment_method" value="paypal" />
                PayPal
            </label>
            <label class="pay_option">
                <input type="radio" name="payment_method" value="cod" />
                Cash on delivery
            </label>

            <div class="form_actions">
                <a href="cart.php" class="btn btn_outline">&larr; Back to cart</a>
                <button type="submit" name="place_order" class="btn btn_primary">Place Order</button>
            </div>
        </form>

        <!-- ===== ORDER SUMMARY ===== -->
        <aside class="card order_summary">
            <h3>Order summary</h3>
            <table class="cart_table">
                <tbody>
                    <?php foreach ($lines as $line): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($line['title']); ?>
                            &times; <?php echo (int) $line['qty']; ?></td>
                        <td style="text-align:right;">
                            $ <?php echo number_format($line['subtotal'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="cart_total_label">Grand Total</td>
                        <td class="cart_total_value" style="text-align:right;">
                            $ <?php echo number_format($grand_total, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </aside>
    </div>
</section>

<?php include("footer.php"); ?>
