<?php
/* =====================================================
   MEDIA / IMAGE STORAGE  (Cloudinary)
   -----------------------------------------------------
   Render's filesystem is ephemeral, so product images
   uploaded from the admin panel are pushed to Cloudinary
   and the returned URL is stored in products.product_image.

   Configure with environment variables (unsigned upload):
     - CLOUDINARY_CLOUD_NAME
     - CLOUDINARY_UPLOAD_PRESET   (an "unsigned" preset)

   When Cloudinary is not configured, uploads fall back to
   the local images/ folder (fine for local development).
   ===================================================== */

// Is Cloudinary configured for unsigned uploads?
function cloudinaryConfigured() {
    return getenv('CLOUDINARY_CLOUD_NAME') && getenv('CLOUDINARY_UPLOAD_PRESET');
}

/* -----------------------------------------------------
   Resolve a stored image reference to a usable <img src>.
   - Full http(s) URLs (Cloudinary) are returned as-is.
   - Bare filenames are prefixed with the images base path.
   - Empty values fall back to the site logo.
   $base differs between the storefront ("images/") and the
   admin area ("../images/").
   ----------------------------------------------------- */
function productImageUrl($image, $base = 'images/') {
    $image = (string) $image;
    if ($image === '') {
        return $base . 'logo.gif';
    }
    if (preg_match('#^https?://#i', $image)) {
        return $image;
    }
    return $base . $image;
}

/* -----------------------------------------------------
   Handle a product image upload from $_FILES.
   Returns the value to store in product_image (a Cloudinary
   URL or a local filename), or false on failure. $error is
   populated with a human-readable reason when it returns false.
   ----------------------------------------------------- */
function uploadProductImage($file, &$error = null) {
    if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $error = "Please choose a product image.";
        return false;
    }

    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        $error = "Image must be a JPG, PNG, GIF, or WEBP file.";
        return false;
    }

    if (cloudinaryConfigured()) {
        $url = cloudinaryUpload($file['tmp_name']);
        if ($url) {
            return $url;
        }
        $error = "Could not upload the image to Cloudinary. Please try again.";
        return false;
    }

    // Local fallback (development only — not persistent on Render).
    $name = "prod_" . time() . "_" . mt_rand(1000, 9999) . "." . $ext;
    $dir  = __DIR__ . "/../images/";
    if (move_uploaded_file($file['tmp_name'], $dir . $name)) {
        return $name;
    }
    $error = "Could not save the uploaded image. Check folder permissions.";
    return false;
}

/* -----------------------------------------------------
   Upload a local temp file to Cloudinary via an unsigned
   preset. Returns the secure URL, or false on failure.
   ----------------------------------------------------- */
function cloudinaryUpload($tmpPath) {
    $cloud  = getenv('CLOUDINARY_CLOUD_NAME');
    $preset = getenv('CLOUDINARY_UPLOAD_PRESET');
    if (!$cloud || !$preset || !is_file($tmpPath)) {
        return false;
    }

    $endpoint = "https://api.cloudinary.com/v1_1/" . rawurlencode($cloud) . "/image/upload";
    $mime     = function_exists('mime_content_type')
        ? (mime_content_type($tmpPath) ?: 'application/octet-stream')
        : 'application/octet-stream';

    $post = array(
        'file'          => new CURLFile($tmpPath, $mime, 'upload'),
        'upload_preset' => $preset,
    );

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $post,
        CURLOPT_TIMEOUT        => 30,
    ));
    // Use the bundled CA bundle for reliable TLS (same one the app ships
    // for Google OAuth).
    $ca = __DIR__ . '/../includes/cacert.pem';
    if (is_file($ca)) {
        curl_setopt($ch, CURLOPT_CAINFO, $ca);
    }

    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false || $code !== 200) {
        return false;
    }
    $data = json_decode($resp, true);
    return $data['secure_url'] ?? false;
}
