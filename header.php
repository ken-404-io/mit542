<?php
// Shared site header. Pages set $page_title before including this file.
include_once __DIR__ . "/functions/functions.php";

$page_title = isset($page_title) ? $page_title : "My Online Shop";
$count      = cartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $page_title; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
          rel="stylesheet" />
    <link rel="stylesheet" href="styles/style.css" media="all" />
</head>
<body>
    <div class="main_wrapper">

        <!-- ===== TOP BAR ===== -->
        <header class="topbar">
            <a href="index.php" class="brand">
                <img src="images/logo.gif" alt="My Online Shop" class="brand_logo" />
                <span class="brand_name">My Online Shop</span>
            </a>

            <nav class="menubar">
                <ul id="menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="index.php#products">All Products</a></li>
                    <li><a href="account.php">My Account</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li class="user_greeting">Hi, <?php echo htmlspecialchars(currentUserName()); ?></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="account.php#signup">Sign Up</a></li>
                    <?php endif; ?>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </nav>

            <div class="topbar_tools">
                <form class="search_form" method="get" action="results.php">
                    <input type="text" name="user_query"
                           placeholder="Search a product..." />
                    <button type="submit" name="search">Search</button>
                </form>
                <a href="cart.php" class="cart_pill">
                    <span class="cart_icon">&#128722;</span>
                    Cart <span class="cart_badge"><?php echo $count; ?></span>
                </a>
            </div>
        </header>

        <!-- ===== MAIN LAYOUT (sidebar + content) ===== -->
        <div class="layout">

            <aside id="sidebar">
                <div class="sidebar_block">
                    <h4>Categories</h4>
                    <ul><?php getCats(); ?></ul>
                </div>
                <div class="sidebar_block">
                    <h4>Brands</h4>
                    <ul><?php getBrands(); ?></ul>
                </div>
            </aside>

            <main class="content_wrapper">
