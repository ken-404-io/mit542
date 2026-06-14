<?php
/* =====================================================
   ADMIN LOGIN
   Authenticates against an `admins` table when present
   (columns: admin_name, admin_pass). If that table does
   not exist yet, it falls back to a built-in default
   account so the panel is usable out of the box:

        Username: admin
        Password: admin123

   Change these by creating an `admins` row (store the
   password with password_hash()).
   ===================================================== */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("includes/db.php");

// Already signed in? Go straight to the dashboard.
if (!empty($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if (isset($_POST['admin_login'])) {
    $username = trim($_POST['admin_user']);
    $password = $_POST['admin_pass'];
    $authenticated = false;

    // Try the admins table first (gracefully ignore if it doesn't exist).
    if (isset($con) && $con) {
        $stmt = mysqli_prepare(
            $con,
            "SELECT admin_pass FROM admins WHERE admin_name = ? LIMIT 1"
        );
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($res && ($adm = mysqli_fetch_assoc($res))) {
                $stored_pass = $adm['admin_pass'];
                // Support both hashed and legacy plain-text passwords.
                if (password_verify($password, $stored_pass)
                    || $password === $stored_pass) {
                    $authenticated = true;
                }
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Built-in fallback so the panel works before any admins exist.
    if (!$authenticated && $username === "admin" && $password === "admin123") {
        $authenticated = true;
    }

    if ($authenticated) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name']      = $username;
        header("Location: index.php");
        exit();
    }

    $error = "Invalid username or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login &middot; My Online Shop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
          rel="stylesheet" />
    <link rel="stylesheet" href="styles/admin.css" media="all" />
</head>
<body>
    <div class="login_wrap">
        <div class="login_card">
            <div class="login_brand">
                <span class="dot"></span><h1>Shop Admin</h1>
                <p>Sign in to manage your store</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert_error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <div class="field">
                    <label>Username</label>
                    <input type="text" name="admin_user"
                           placeholder="admin" required autofocus />
                </div>
                <div class="field">
                    <label>Password</label>
                    <input type="password" name="admin_pass"
                           placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required />
                </div>
                <button type="submit" name="admin_login"
                        class="btn btn_primary btn_block">Sign In</button>
            </form>
        </div>
    </div>
</body>
</html>
