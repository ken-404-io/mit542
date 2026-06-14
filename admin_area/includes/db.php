<?php

// Database connection for the admin area. Settings come from environment
// variables (Docker) and fall back to the classic local XAMPP defaults.
$db_host = getenv("DB_HOST") ?: "localhost";
$db_user = getenv("DB_USER") ?: "root";
$db_pass = getenv("DB_PASS") ?: "";
$db_name = getenv("DB_NAME") ?: "ecommerce_website";
$con = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Run a query only when we hold a live connection. If the database is
// unreachable, mysqli_connect() returns false and passing that to
// mysqli_query() throws a fatal TypeError on PHP 8. Returning false instead
// lets callers handle the failure gracefully.
function dbQuery($sql) {
    global $con;
    if (!($con instanceof mysqli)) {
        return false;
    }
    return mysqli_query($con, $sql);
}

?>
