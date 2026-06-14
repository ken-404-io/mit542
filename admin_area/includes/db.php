<?php

// Database connection for the admin area. Settings come from environment
// variables (Docker) and fall back to the classic local XAMPP defaults.
$db_host = getenv("DB_HOST") ?: "localhost";
$db_user = getenv("DB_USER") ?: "root";
$db_pass = getenv("DB_PASS") ?: "";
$db_name = getenv("DB_NAME") ?: "ecommerce_website";
$con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

?>
