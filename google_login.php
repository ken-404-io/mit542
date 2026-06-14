<?php
/* =====================================================
   GOOGLE OAUTH - STEP 1: redirect the user to Google.
   ===================================================== */
include_once __DIR__ . "/functions/functions.php";   // starts the session
include_once __DIR__ . "/includes/google_config.php";

if (!googleOAuthReady()) {
    die("Google sign-in is not configured yet. See includes/google_config.php.");
}

// CSRF protection: random state we verify on the way back.
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = array(
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'state'         => $state,
    'access_type'   => 'online',
    'prompt'        => 'select_account',
);

header("Location: " . GOOGLE_AUTH_URL . "?" . http_build_query($params));
exit();
