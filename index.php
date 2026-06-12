<?php
$page_title = "My Online Shop - Home";
include("header.php");
?>

<!-- ===== HERO / WELCOME BANNER ===== -->
<section class="hero">
    <div class="hero_text">
        <h1>Welcome to <span>My Online Shop</span></h1>
        <p>Discover great products at unbeatable prices, all in one place.</p>
        <a href="#products" class="btn btn_primary btn_lg">Shop Now</a>
    </div>
    <div class="hero_image">
        <img src="images/ad_banner.gif" alt="Featured offers" />
    </div>
</section>

<!-- ===== FEATURED PRODUCTS ===== -->
<section id="products" class="section">
    <div class="section_head">
        <h2 class="section_title">Featured Products</h2>
        <p class="section_subtitle">Hand-picked items just for you</p>
    </div>
    <div class="product_grid">
        <?php getPro(); ?>
    </div>
</section>

<?php include("footer.php"); ?>
