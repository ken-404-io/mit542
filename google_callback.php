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
googleCurlSSL($ch);
$token_response = curl_exec($ch);
$token_http     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$token_errno    = curl_errno($ch);
$token_error    = curl_error($ch);
curl_close($ch);

// cURL never completed the request (network or TLS failure).
if ($token_response === false) {
    if (in_array($token_errno, array(60, 77, 35), true)) {
        oauthFail("Could not obtain access token from Google: TLS certificate "
            . "could not be verified (cURL #$token_errno). Point curl.cainfo in "
            . "php.ini at a cacert.pem (the repo ships one at includes/cacert.pem).");
    }
    oauthFail("Could not obtain access token from Google: " . $token_error);
}

$token_data = json_decode($token_response, true);

// Google answered but rejected the exchange — surface its actual reason
// (e.g. redirect_uri_mismatch, invalid_client, invalid_grant).
if ($token_http !== 200 || empty($token_data['access_token'])) {
    $reason = "";
    if (is_array($token_data)) {
        $reason = !empty($token_data['error_description'])
            ? $token_data['error_description']
            : (!empty($token_data['error']) ? $token_data['error'] : "");
    }
    if ($reason === "") {
        $reason = "HTTP $token_http";
    }
    oauthFail("Could not obtain access token from Google: " . $reason);
}

/* ---- Fetch the user's profile ---- */
$ch = curl_init(GOOGLE_USERINFO_URL);
curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => array("Authorization: Bearer " . $token_data['access_token']),
    CURLOPT_TIMEOUT        => 15,
));
googleCurlSSL($ch);
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
$user_id = 0;
if (isset($con) && $con) {
    $stmt = mysqli_prepare($con, "SELECT user_id FROM users WHERE user_email = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res && ($found = mysqli_fetch_assoc($res))) {
            $user_id = (int) $found['user_id'];
        }
        mysqli_stmt_close($stmt);

        if (!$user_id) {
            // New Google user: create a row with no local password.
            $ins = mysqli_prepare(
                $con,
                "INSERT INTO users (user_name, user_email, user_password)
                 VALUES (?, ?, '')"
            );
            if ($ins) {
                mysqli_stmt_bind_param($ins, "ss", $name, $email);
                mysqli_stmt_execute($ins);
                $user_id = (int) mysqli_insert_id($con);
                mysqli_stmt_close($ins);
            }
        }
    }
}

/* ---- Sign the user in ---- */
session_regenerate_id(true);
$_SESSION['user_logged_in'] = true;
$_SESSION['user_id']        = $user_id;
$_SESSION['user_email']     = $email;
$_SESSION['user_name']      = $name;

header("Location: index.php");
exit();
