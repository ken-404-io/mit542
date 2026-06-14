<?php
/* =====================================================
   GOOGLE OAUTH - STEP 2: handle Google's redirect back.
   Exchanges the code for a token, fetches the profile,
   finds-or-creates a local user, and signs them in.
   ===================================================== */
include_once __DIR__ . "/functions/functions.php";   // session + $con
include_once __DIR__ . "/includes/google_config.php";

function oauthFail($msg) {
    header("Location: account.php?tab=signup&oauth_error=" . urlencode($msg));
    exit();
}

if (!googleOAuthReady()) {
    oauthFail("Google sign-in is not configured.");
}

// Google reports an error (e.g. the user denied access).
if (isset($_GET['error'])) {
    oauthFail($_GET['error']);
}

// Verify the state to defend against CSRF.
if (empty($_GET['state']) || empty($_SESSION['oauth_state'])
    || !hash_equals($_SESSION['oauth_state'], $_GET['state'])) {
    oauthFail("Invalid state. Please try again.");
}
unset($_SESSION['oauth_state']);

if (empty($_GET['code'])) {
    oauthFail("No authorization code returned.");
}

/* ---- Exchange the authorization code for an access token ---- */
$token_fields = array(
    'code'          => $_GET['code'],
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code',
);

$ch = curl_init(GOOGLE_TOKEN_URL);
curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($token_fields),
    CURLOPT_TIMEOUT        => 15,
));
$token_response = curl_exec($ch);
$token_http     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($token_response === false || $token_http !== 200) {
    oauthFail("Could not obtain access token from Google.");
}

$token_data = json_decode($token_response, true);
if (empty($token_data['access_token'])) {
    oauthFail("Google did not return an access token.");
}

/* ---- Fetch the user's profile ---- */
$ch = curl_init(GOOGLE_USERINFO_URL);
curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => array("Authorization: Bearer " . $token_data['access_token']),
    CURLOPT_TIMEOUT        => 15,
));
$profile_response = curl_exec($ch);
$profile_http     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($profile_response === false || $profile_http !== 200) {
    oauthFail("Could not fetch your Google profile.");
}

$profile = json_decode($profile_response, true);
$email   = isset($profile['email']) ? $profile['email'] : '';
$name    = isset($profile['name'])  ? $profile['name']  : $email;

if (empty($email)) {
    oauthFail("Google did not share an email address.");
}

/* ---- Find or create the local user (only if a users table exists) ---- */
if (isset($con) && $con) {
    $stmt = mysqli_prepare($con, "SELECT user_id FROM users WHERE user_email = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $exists = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if (!$exists) {
            // New Google user: create a row with no local password.
            $ins = mysqli_prepare(
                $con,
                "INSERT INTO users (user_name, user_email, user_password)
                 VALUES (?, ?, '')"
            );
            if ($ins) {
                mysqli_stmt_bind_param($ins, "ss", $name, $email);
                mysqli_stmt_execute($ins);
                mysqli_stmt_close($ins);
            }
        }
    }
}

/* ---- Sign the user in ---- */
session_regenerate_id(true);
$_SESSION['user_logged_in'] = true;
$_SESSION['user_email']     = $email;
$_SESSION['user_name']      = $name;

header("Location: index.php");
exit();
