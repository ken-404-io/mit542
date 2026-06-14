<?php
/* =====================================================
   ADMIN AUTH GUARD
   Include this at the very top of every protected admin
   page. If the visitor is not logged in as an admin they
   are bounced to the login screen.
   ===================================================== */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
