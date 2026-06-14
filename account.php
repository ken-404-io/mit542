<?php
// Load DB + session and handle sign-in / registration BEFORE any HTML is
// sent, so we can validate and redirect cleanly.
include_once __DIR__ . "/functions/functions.php";

$auth_error = "";
$start_tab  = (isset($_GET['tab']) && $_GET['tab'] === 'signup') ? 'signup' : 'login';

/* ---- Create a new account with email + password ---- */
if (isset($_POST['register'])) {
    $start_tab = 'signup';
    $reg_name  = trim($_POST['reg_name'] ?? '');
    $reg_email = trim($_POST['reg_email'] ?? '');
    $reg_pass  = $_POST['reg_pass'] ?? '';

    if ($reg_name === '' || $reg_email === '' || $reg_pass === '') {
        $auth_error = "Please fill in every field.";
    } elseif (!filter_var($reg_email, FILTER_VALIDATE_EMAIL)) {
        $auth_error = "Please enter a valid email address.";
    } elseif (strlen($reg_pass) < 6) {
        $auth_error = "Your password must be at least 6 characters.";
    } elseif (!($con instanceof mysqli)) {
        $auth_error = "Accounts are unavailable right now. Please try again later.";
    } else {
        // Is the email already registered?
        $stmt = mysqli_prepare($con, "SELECT user_id FROM users WHERE user_email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $reg_email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $taken = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if ($taken) {
            $auth_error = "That email is already registered. Try signing in instead.";
        } else {
            $hash = password_hash($reg_pass, PASSWORD_DEFAULT);
            $ins  = mysqli_prepare(
                $con,
                "INSERT INTO users (user_name, user_email, user_password) VALUES (?, ?, ?)"
            );
            mysqli_stmt_bind_param($ins, "sss", $reg_name, $reg_email, $hash);
            if (mysqli_stmt_execute($ins)) {
                $new_id = (int) mysqli_insert_id($con);
                mysqli_stmt_close($ins);
                session_regenerate_id(true);
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id']        = $new_id;
                $_SESSION['user_email']     = $reg_email;
                $_SESSION['user_name']      = $reg_name;
                header("Location: account.php");
                exit;
            }
            mysqli_stmt_close($ins);
            $auth_error = "Sorry, we couldn't create your account. Please try again.";
        }
    }
}

/* ---- Sign in with email + password ---- */
if (isset($_POST['login'])) {
    $start_tab   = 'login';
    $login_email = trim($_POST['login_email'] ?? '');
    $login_pass  = $_POST['login_pass'] ?? '';

    if ($login_email === '' || $login_pass === '') {
        $auth_error = "Please enter your email and password.";
    } elseif (!($con instanceof mysqli)) {
        $auth_error = "Sign-in is unavailable right now. Please try again later.";
    } else {
        $stmt = mysqli_prepare(
            $con,
            "SELECT user_id, user_name, user_password FROM users WHERE user_email = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "s", $login_email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $uid, $uname, $uhash);
        $found = mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if (!$found) {
            $auth_error = "No account found with that email.";
        } elseif ($uhash === '') {
            // Account was created via Google and has no local password.
            $auth_error = "This account uses Google sign-in. Please use the Google button.";
        } elseif (!password_verify($login_pass, $uhash)) {
            $auth_error = "Incorrect password. Please try again.";
        } else {
            session_regenerate_id(true);
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id']        = (int) $uid;
            $_SESSION['user_email']     = $login_email;
            $_SESSION['user_name']      = $uname;
            header("Location: account.php");
            exit;
        }
    }
}

$page_title = "My Account - My Online Shop";
include("header.php");
?>

<section class="section">
    <div class="section_head">
        <h2 class="section_title">My Account</h2>
        <p class="section_subtitle">Sign in or create a new account</p>
    </div>

    <?php if (isLoggedIn()): ?>
    <!-- ===== SIGNED-IN VIEW ===== -->
    <div class="auth_layout">
        <aside class="auth_aside">
            <h2>Welcome back, <span><?php echo htmlspecialchars(currentUserName()); ?></span></h2>
            <p>You're signed in<?php echo !empty($_SESSION['user_email'])
                ? ' as ' . htmlspecialchars($_SESSION['user_email']) : ''; ?>.</p>
            <ul class="auth_perks">
                <li><span class="tick">&#10003;</span> Track every order in one place</li>
                <li><span class="tick">&#10003;</span> Saved wishlist &amp; favourites</li>
                <li><span class="tick">&#10003;</span> Faster, secure checkout</li>
            </ul>
        </aside>
        <div class="auth_panel">
            <h3>Your Account</h3>
            <p class="sub">Signed in as
                <strong><?php echo htmlspecialchars($_SESSION['user_email'] ?? currentUserName()); ?></strong>.</p>
            <a href="orders.php" class="btn btn_primary"
               style="display:inline-block;margin-top:10px;">My Orders</a>
            <a href="index.php" class="btn btn_outline"
               style="display:inline-block;margin-top:10px;margin-left:8px;">Continue shopping</a>
            <a href="logout.php" class="btn btn_ghost"
               style="display:inline-block;margin-top:10px;margin-left:8px;">Log out</a>
        </div>
    </div>
    <?php else: ?>

    <?php if (isset($_GET['checkout'])): ?>
        <p class="alert_success">Please sign in or create an account to complete your checkout.</p>
    <?php endif; ?>

    <div class="auth_layout">

        <!-- ===== WELCOME PANEL ===== -->
        <aside class="auth_aside">
            <h2>Welcome to <span>My Online Shop</span></h2>
            <p>Sign in to track your orders, save favourites, and check out faster.</p>
            <ul class="auth_perks">
                <li><span class="tick">&#10003;</span> Faster, secure checkout</li>
                <li><span class="tick">&#10003;</span> Track every order in one place</li>
                <li><span class="tick">&#10003;</span> Save products to your wishlist</li>
                <li><span class="tick">&#10003;</span> Member-only deals &amp; offers</li>
            </ul>
        </aside>

        <!-- ===== FORMS PANEL ===== -->
        <div class="auth_panel">
            <div class="auth_tabs">
                <div class="auth_tab" id="tab_login" onclick="showAuth('login')">Sign In</div>
                <div class="auth_tab" id="tab_signup" onclick="showAuth('signup')">Create Account</div>
            </div>

            <?php if ($auth_error): ?>
                <p class="alert_success" style="background:#fdecea;color:#c0392b;">
                    <?php echo htmlspecialchars($auth_error); ?>
                </p>
            <?php endif; ?>

            <!-- Sign In -->
            <form class="auth_form" id="form_login" method="post" action="account.php">
                <h3>Sign In</h3>
                <p class="sub">Enter your details to access your account.</p>
                <label>Email
                    <input type="email" name="login_email"
                           placeholder="you@example.com" required />
                </label>
                <label>Password
                    <input type="password" name="login_pass"
                           placeholder="Your password" required />
                </label>
                <button type="submit" name="login" class="btn btn_primary">Sign In</button>
                <p class="auth_switch">
                    New here? <a onclick="showAuth('signup')">Create an account</a>
                </p>
            </form>

            <!-- Create Account -->
            <form class="auth_form" id="form_signup" method="post" action="account.php">
                <h3>Create Account</h3>
                <p class="sub">It only takes a minute to get started.</p>

                <?php if (isset($_GET['oauth_error'])): ?>
                    <p class="alert_success" style="background:#fdecea;color:#c0392b;">
                        Google sign-in failed: <?php echo htmlspecialchars($_GET['oauth_error']); ?>
                    </p>
                <?php endif; ?>

                <a href="google_login.php" class="btn_google">
                    <img src="https://www.google.com/favicon.ico" alt="" />
                    Sign up with Google
                </a>
                <div class="auth_divider">or sign up with email</div>

                <label>Full Name
                    <input type="text" name="reg_name"
                           placeholder="Jane Doe" required />
                </label>
                <label>Email
                    <input type="email" name="reg_email"
                           placeholder="you@example.com" required />
                </label>
                <label>Password
                    <input type="password" name="reg_pass"
                           placeholder="Choose a strong password"
                           minlength="6" required />
                </label>
                <button type="submit" name="register" class="btn btn_primary">Sign Up</button>
                <p class="auth_switch">
                    Already a member? <a onclick="showAuth('login')">Sign in instead</a>
                </p>
            </form>
        </div>
    </div>

    <script>
    function showAuth(which) {
        var loginTab  = document.getElementById('tab_login');
        var signupTab = document.getElementById('tab_signup');
        var loginForm  = document.getElementById('form_login');
        var signupForm = document.getElementById('form_signup');

        var isSignup = which === 'signup';
        signupTab.classList.toggle('active', isSignup);
        loginTab.classList.toggle('active', !isSignup);
        signupForm.classList.toggle('active', isSignup);
        loginForm.classList.toggle('active', !isSignup);
    }

    // Open the right tab on load (supports #signup links from the menu).
    var startTab = '<?php echo $start_tab; ?>';
    if (location.hash === '#signup') { startTab = 'signup'; }
    showAuth(startTab);
    </script>
    <?php endif; ?>
</section>

<?php include("footer.php"); ?>
