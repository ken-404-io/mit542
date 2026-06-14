<?php
// Load DB + session and handle cart actions BEFORE any HTML is sent,
// so we can safely redirect.
include_once __DIR__ . "/functions/functions.php";

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Add an item.
if (isset($_GET['add'])) {
    $id = (int) $_GET['add'];
    if ($id > 0) {
        if (!isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] = 0;
        }
        $_SESSION['cart'][$id]++;
    }
    header("Location: cart.php");
    exit;
}

// Remove a single item line.
if (isset($_GET['remove'])) {
    $id = (int) $_GET['remove'];
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
    exit;
}

// Empty the whole cart.
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = array();
    header("Location: cart.php");
    exit;
}

$page_title = "Your Cart - My Online Shop";
include("header.php");
?>

<section class="section">
    <div class="section_head">
        <h2 class="section_title">Your Shopping Cart</h2>
    </div>

    <?php if (empty($_SESSION['cart'])): ?>
        <p class="empty_state">
            Your cart is empty.
            <a href="index.php">Start shopping</a>
        </p>
    <?php else: ?>
        <div class="cart_wrap">
            <table class="cart_table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grand_total = 0;
                    foreach ($_SESSION['cart'] as $id => $qty) {
                        $id  = (int) $id;
                        $run = dbQuery(
                            "SELECT * FROM products WHERE product_id='$id'");
                        if (!$run || mysqli_num_rows($run) === 0) {
                            continue;
                        }
                        $row      = mysqli_fetch_array($run);
                        $title    = $row['product_title'];
                        $price    = (float) $row['product_price'];
                        $image    = $row['product_image'];
                        $subtotal = $price * $qty;
                        $grand_total += $subtotal;

                        echo "
                        <tr>
                            <td class='cart_product'>
                                <img src='images/$image' alt='$title' />
                                <span>$title</span>
                            </td>
                            <td>\$ " . number_format($price, 2) . "</td>
                            <td>$qty</td>
                            <td>\$ " . number_format($subtotal, 2) . "</td>
                            <td>
                                <a class='cart_remove'
                                   href='cart.php?remove=$id'>Remove</a>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="cart_total_label">Grand Total</td>
                        <td colspan="2" class="cart_total_value">
                            $ <?php echo number_format($grand_total, 2); ?>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <div class="cart_buttons">
                <a class="btn btn_outline" href="index.php">
                    &larr; Continue Shopping
                </a>
                <a class="btn btn_ghost" href="cart.php?clear=1">Empty Cart</a>
                <a class="btn btn_primary" href="checkout.php">Proceed to Checkout</a>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php include("footer.php"); ?>
