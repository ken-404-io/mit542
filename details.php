<!DOCTYPE html>
<?php include("functions/functions.php"); ?>
<html>
    <head>
        <title>Product Details</title>
        <link rel="stylesheet" href="styles/style.css" media="all" />
    </head>
    <body>
        <div class="main_wrapper">

            <!-- ===== MENUBAR STARTS HERE ===== -->
            <div class="menubar">
                <ul id="menu">
                    <li><a href="#">Home</a></li>
                    <li><a href="#">All Products</a></li>
                    <li><a href="#">My Account</a></li>
                    <li><a href="#">Sign Up</a></li>
                    <li><a href="#">Shopping Cart</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
                <div id="form">
                    <form method="get" action="results.php"
                          enctype="multipart/form-data">
                        <input type="text" name="user_query"
                               placeholder="Search a Product"/>
                        <input type="submit" name="search" value="search"/>
                    </form>
                </div>
            </div>
            <!-- ===== MENUBAR ENDS HERE ===== -->

            <!-- ===== SIDEBAR STARTS HERE ===== -->
            <div id="sidebar">
                <ul>
                    <li><b>Categories</b></li>
                    <?php getCats(); ?>
                </ul>
                <ul>
                    <li><b>Brands</b></li>
                    <?php getBrands(); ?>
                </ul>
            </div>
            <!-- ===== SIDEBAR ENDS HERE ===== -->

            <!-- ===== HEADER STARTS HERE ===== -->
            <div class="header_wrapper">
                <img id="logo" src="images/logo.gif" />
                <img id="banner" src="images/ad_banner.gif" />
            </div>
            <!-- ===== HEADER ENDS HERE ===== -->

            <!-- ===== CONTENT AREA STARTS HERE ===== -->
            <div class="content_wrapper">
                <div id="content_area">

                    <div id="shopping_cart">
                        <span style="float:right; font-size:18px;
                              padding:5px; line-height:40px;">
                            Welcome Guest!
                            <b style="color:yellow">Shopping Cart -</b>
                            Total items: Total Price:
                            <a href="cart.php" style="color:yellow">
                                Go to Cart
                            </a>
                        </span>
                    </div>

                    <div id="products_box">
                        <?php
                        if(isset($_GET['pro_id'])) {
                            $product_id = $_GET['pro_id'];
                            $get_pro = "SELECT * FROM products
                                        WHERE product_id='$product_id'";
                            $run_pro = mysqli_query($con, $get_pro);

                            if($run_pro && mysqli_num_rows($run_pro) > 0) {
                                while($row_pro = mysqli_fetch_array($run_pro)) {
                                    $pro_id    = $row_pro['product_id'];
                                    $pro_title = $row_pro['product_title'];
                                    $pro_price = $row_pro['product_price'];
                                    $pro_image = $row_pro['product_image'];
                                    $pro_desc  = $row_pro['product_desc'];

                                    echo "
                                    <div class='product_detail'>
                                        <div class='detail_image'>
                                            <img src='images/$pro_image'
                                                 alt='$pro_title' />
                                        </div>
                                        <div class='detail_info'>
                                            <h2>$pro_title</h2>
                                            <p class='detail_price'>
                                                $ $pro_price
                                            </p>
                                            <p class='detail_desc'>$pro_desc</p>
                                            <div class='detail_buttons'>
                                                <a class='btn_back'
                                                   href='index.php'>
                                                    &larr; Go Back
                                                </a>
                                                <a class='btn_cart'
                                                   href='index.php?pro_id=$pro_id'>
                                                    Add to Cart
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    ";
                                }
                            } else {
                                echo "<p class='not_found'>
                                        Sorry, that product could not be found.
                                        <a href='index.php'>Return to shop</a>
                                      </p>";
                            }
                        } else {
                            echo "<p class='not_found'>
                                    No product selected.
                                    <a href='index.php'>Browse our products</a>
                                  </p>";
                        }
                        ?>

                        <!-- ===== RELATED PRODUCTS ===== -->
                        <h3 class="related_heading">You May Also Like</h3>
                        <div class="related_box">
                            <?php getPro(); ?>
                        </div>
                    </div>

                </div>
            </div>
            <!-- ===== CONTENT AREA ENDS HERE ===== -->

            <!-- ===== FOOTER STARTS HERE ===== -->
            <div id="footer">
                <h2 style="text-align:center; padding-top:15px;">
                    &copy; 2016 by The Webmaster
                </h2>
            </div>
            <!-- ===== FOOTER ENDS HERE ===== -->

        </div>
    </body>
</html>