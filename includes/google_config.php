<?php
/* =====================================================
   GOOGLE OAUTH 2.0 CONFIGURATION
   -----------------------------------------------------
   To enable "Sign up with Google":

   1. Go to https://console.cloud.google.com/apis/credentials
   2. Create an OAuth 2.0 Client ID (type: Web application).
   3. Add an Authorized redirect URI that EXACTLY matches the
      GOOGLE_REDIRECT_URI printed below (e.g.
      http://localhost/google_callback.php).
   4. Provide the Client ID and Secret in ONE of these ways:
        a) Set environment variables GOOGLE_CLIENT_ID and
           GOOGLE_CLIENT_SECRET (recommended), or
        b) Create includes/google_config.local.php that defines
           GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET (it is
           git-ignored so secrets never get committed).

   Never commit real credentials to the repository.
   ===================================================== */

// Load a git-ignored local override first, if present.
$local = __DIR__ . "/google_config.local.php";
if (file_exists($local)) {
    require_once $local;
}

if (!defined("GOOGLE_CLIENT_ID")) {
    define("GOOGLE_CLIENT_ID", getenv("GOOGLE_CLIENT_ID") ?: "YOUR_GOOGLE_CLIENT_ID");
}

if (!defined("GOOGLE_CLIENT_SECRET")) {
    define("GOOGLE_CLIENT_SECRET", getenv("GOOGLE_CLIENT_SECRET") ?: "YOUR_GOOGLE_CLIENT_SECRET");
}

// Work out the redirect URI from the current request unless one is
// explicitly provided. It MUST match a URI registered in Google Console.
if (!defined("GOOGLE_REDIRECT_URI")) {
    $env_redirect = getenv("GOOGLE_REDIRECT_URI");
    if ($env_redirect) {
        define("GOOGLE_REDIRECT_URI", $env_redirect);
    } else {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $base   = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        define("GOOGLE_REDIRECT_URI", $scheme . "://" . $host . $base . "/google_callback.php");
    }
}

// Google OAuth 2.0 endpoints.
define("GOOGLE_AUTH_URL",     "https://accounts.google.com/o/oauth2/v2/auth");
define("GOOGLE_TOKEN_URL",    "https://oauth2.googleapis.com/token");
define("GOOGLE_USERINFO_URL", "https://www.googleapis.com/oauth2/v3/userinfo");

// Resolve a CA bundle so cURL can verify Google's TLS certificate. On a
// default XAMPP/Windows PHP there is no CA bundle configured, which makes
// the token request fail and surfaces as "Could not obtain access token
// from Google". We fall back to the cacert.pem shipped next to this file.
// Order of preference: explicit env var, php.ini settings, bundled file.
if (!defined("GOOGLE_CAINFO")) {
    $cainfo = getenv("GOOGLE_CAINFO")
        ?: (ini_get("curl.cainfo")
        ?: ini_get("openssl.cafile"));
    if (!$cainfo || !is_file($cainfo)) {
        $bundled = __DIR__ . "/cacert.pem";
        $cainfo  = is_file($bundled) ? $bundled : "";
    }
    define("GOOGLE_CAINFO", $cainfo);
}

/* Apply TLS verification (using our CA bundle) to a cURL handle. Keeps
   verification ON so the client secret and tokens are never sent over an
   unverified connection. */
function googleCurlSSL($ch) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    if (GOOGLE_CAINFO !== "") {
        curl_setopt($ch, CURLOPT_CAINFO, GOOGLE_CAINFO);
    }
}

/* Returns true once real credentials have been configured. Leftover
   placeholders count as "not configured" so we show a clear setup message
   instead of sending junk to Google (which returns "invalid client"). */
function googleOAuthReady() {
    $placeholders = array(
        "",
        "YOUR_GOOGLE_CLIENT_ID",
        "YOUR_GOOGLE_CLIENT_SECRET",
        "PASTE_YOUR_CLIENT_ID_HERE",
        "PASTE_YOUR_CLIENT_SECRET_HERE",
    );
    return !in_array(GOOGLE_CLIENT_ID, $placeholders, true)
        && !in_array(GOOGLE_CLIENT_SECRET, $placeholders, true);
}
