<?php
$page_title = "Product Details - My Online Shop";
include("header.php");
?>

<section class="section">
    <?php
    if (isset($_GET['pro_id'])) {
        $product_id = (int) $_GET['pro_id'];
        $get_pro = "SELECT * FROM products WHERE product_id='$product_id'";
        $run_pro = mysqli_query($con, $get_pro);

        if ($run_pro && mysqli_num_rows($run_pro) > 0) {
            while ($row_pro = mysqli_fetch_array($run_pro)) {
                $pro_id    = $row_pro['product_id'];
                $pro_title = $row_pro['product_title'];
                $pro_price = $row_pro['product_price'];
                $pro_image = $row_pro['product_image'];
                $pro_desc  = $row_pro['product_desc'];

                echo "
                <div class='product_detail'>
                    <div class='detail_image'>
                        <img src='images/$pro_image' alt='$pro_title' />
                    </div>
                    <div class='detail_info'>
                        <h2>$pro_title</h2>
                        <p class='detail_price'>\$ $pro_price</p>
                        <p class='detail_desc'>$pro_desc</p>
                        <div class='detail_buttons'>
                            <a class='btn btn_outline' href='index.php'>
                                &larr; Continue Shopping
                            </a>
                            <a class='btn btn_primary' href='cart.php?add=$pro_id'>
                                Add to Cart
                            </a>
                        </div>
                    </div>
                </div>";
            }
        } else {
            echo "<p class='empty_state'>
                    Sorry, that product could not be found.
                    <a href='index.php'>Return to shop</a>
                  </p>";
        }
    } else {
        echo "<p class='empty_state'>
                No product selected.
                <a href='index.php'>Browse our products</a>
              </p>";
    }
    ?>
</section>

<!-- ===== RELATED PRODUCTS ===== -->
<section class="section">
    <div class="section_head">
        <h2 class="section_title">You May Also Like</h2>
    </div>
    <div class="product_grid">
        <?php getPro(); ?>
    </div>
</section>

<?php include("footer.php"); ?>
