<?php

$con = mysqli_connect("localhost", "root", "", "ecommerce_website");

function getCats() {
    global $con;
    $get_cats = "SELECT * FROM categories";
    $run_cats = mysqli_query($con, $get_cats);
    while ($row_cats = mysqli_fetch_array($run_cats)) {
        $cat_title = $row_cats['cat_title'];
        echo "<li><a href='#'>$cat_title</a></li>";
    }
}

function getBrands() {
    global $con;
    $get_brands = "SELECT * FROM brands";
    $run_brands = mysqli_query($con, $get_brands);
    while ($row_brands = mysqli_fetch_array($run_brands)) {
        $brand_title = $row_brands['brand_title'];
        echo "<li><a href='#'>$brand_title</a></li>";
    }
}

function getProducts() {
    global $con;
    $get_products = "SELECT * FROM products";
    $run_products = mysqli_query($con, $get_products);
    while ($row_products = mysqli_fetch_array($run_products)) {
        $product_title = $row_products['product_title'];
        $product_price = $row_products['product_price'];
        $product_image = $row_products['product_image'];
        echo "
        <div style='display:inline-block;width:200px;margin:10px;
        padding:10px;background:white;border:1px solid #ccc;
        text-align:center;vertical-align:top;'>
            <img src='images/$product_image' width='150' height='120'
                 style='object-fit:cover;'/>
            <h4>$product_title</h4>
            <p><b>Price: $$product_price</b></p>
        </div>";
    }
}

function getPro() {
    global $con;
    $get_pro = "SELECT * FROM products ORDER BY RAND() LIMIT 0,6";
    $run_pro = mysqli_query($con, $get_pro);
    while ($row_pro = mysqli_fetch_array($run_pro)) {
        $pro_id    = $row_pro['product_id'];
        $pro_cat   = $row_pro['product_cat'];
        $pro_brand = $row_pro['product_brand'];
        $pro_title = $row_pro['product_title'];
        $pro_price = $row_pro['product_price'];
        $pro_image = $row_pro['product_image'];

        echo "
        <div id='single_product'>
            <h3>$pro_title</h3>
            <img src='images/$pro_image' width='180' height='180' />
            <p><b>$ $pro_price</b></p>
            <a href='details.php?pro_id=$pro_id' style='float:left;'>Details</a>
            <a href='index.php?pro_id=$pro_id'>
                <button style='float:right'>Add to Cart</button>
            </a>
        </div>
        ";
    }
}

?>