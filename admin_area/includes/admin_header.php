<?php
/* =====================================================
   SHARED ADMIN HEADER
   Protected pages include this AFTER auth.php. It renders
   the admin shell (brand, top bar, sidebar nav) and opens
   the .admin_content area. Each page sets $admin_title and
   optionally $active_nav before including this file.
   ===================================================== */

$admin_title = isset($admin_title) ? $admin_title : "Dashboard";
$active_nav  = isset($active_nav)  ? $active_nav  : "";
$admin_name  = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : "Admin";

function navActive($name, $current) {
    return $name === $current ? "active" : "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($admin_title); ?> &middot; Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
          rel="stylesheet" />
    <link rel="stylesheet" href="styles/admin.css" media="all" />
</head>
<body>
    <div class="admin_shell">

        <!-- ===== BRAND CORNER ===== -->
        <div class="admin_brand">
            <span class="dot"></span> Shop Admin
        </div>

        <!-- ===== TOP BAR ===== -->
        <header class="admin_topbar">
            <h1><?php echo htmlspecialchars($admin_title); ?></h1>
            <div class="admin_user">
                <span>Welcome, <span class="who"><?php echo htmlspecialchars($admin_name); ?></span></span>
                <a href="../index.php" class="btn btn_outline" target="_blank">View Site</a>
                <a href="logout.php" class="btn btn_primary">Logout</a>
            </div>
        </header>

        <!-- ===== SIDEBAR NAV ===== -->
        <nav class="admin_nav">
            <div class="nav_group_label">Manage</div>
            <a href="index.php" class="<?php echo navActive('dashboard', $active_nav); ?>">
                <span class="ico">&#9632;</span> Dashboard
            </a>
            <a href="manage_products.php" class="<?php echo navActive('manage_products', $active_nav); ?>">
                <span class="ico">&#128230;</span> Products
            </a>
            <a href="insert_product.php" class="<?php echo navActive('insert_product', $active_nav); ?>">
                <span class="ico">&#43;</span> Insert Product
            </a>
            <a href="orders.php" class="<?php echo navActive('orders', $active_nav); ?>">
                <span class="ico">&#128722;</span> Orders
            </a>
            <a href="users.php" class="<?php echo navActive('users', $active_nav); ?>">
                <span class="ico">&#128100;</span> Users
            </a>
            <a href="reports.php" class="<?php echo navActive('reports', $active_nav); ?>">
                <span class="ico">&#128202;</span> Reports
            </a>
            <div class="nav_group_label">Site</div>
            <a href="../index.php" target="_blank">
                <span class="ico">&#8599;</span> Storefront
            </a>
        </nav>

        <!-- ===== CONTENT ===== -->
        <main class="admin_content">
