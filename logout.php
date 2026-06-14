<?php
/* =====================================================
   Sign the current user out, then return to the home page.
   The shopping cart is left intact on purpose.
   ===================================================== */
include_once __DIR__ . "/functions/functions.php";   // starts the session

unset(
    $_SESSION['user_logged_in'],
    $_SESSION['user_email'],
    $_SESSION['user_name']
);
session_regenerate_id(true);

header("Location: index.php");
exit();
