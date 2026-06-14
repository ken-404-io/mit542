<?php
/* Admin logout: clear the session and return to login. */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = array();
session_destroy();

header("Location: login.php");
exit();
