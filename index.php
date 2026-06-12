<!DOCTYPE html>
<?php include("functions/functions.php"); ?>
<html>
    <head>
        <title>My Online Shop</title>
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
                        <?php getPro(); ?>
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