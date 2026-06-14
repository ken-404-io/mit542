<?php
$page_title = "My Online Shop - Home";
include("header.php");

// Are we browsing a sidebar filter, or showing the normal home page?
$is_browsing = isset($_GET['cat']) || isset($_GET['brand']);
?>

<?php if (!$is_browsing): ?>
<!-- ===== HERO / WELCOME BANNER (home page only) ===== -->
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
<?php endif; ?>

<!-- ===== PRODUCTS (featured, or filtered by category / brand) ===== -->
<section id="products" class="section">
    <div class="section_head">
        <?php if (isset($_GET['cat'])): ?>
            <h2 class="section_title">Browsing Category</h2>
            <p class="section_subtitle">Products in your selected category</p>
        <?php elseif (isset($_GET['brand'])): ?>
            <h2 class="section_title">Browsing Brand</h2>
            <p class="section_subtitle">Products from your selected brand</p>
        <?php else: ?>
            <h2 class="section_title">Featured Products</h2>
            <p class="section_subtitle">Hand-picked items just for you</p>
        <?php endif; ?>
    </div>
    <div id="products_box" class="product_grid">
        <?php
            getPro();        // random feed on the home page
            getCatPro();     // products for the chosen category
            getBrandPro();   // products for the chosen brand
        ?>
    </div>
</section>

<?php include("footer.php"); ?>
