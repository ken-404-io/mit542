<?php
$page_title = "My Account - My Online Shop";
include("header.php");

// Decide which tab opens first (Sign Up if ?tab=signup or #signup land).
$start_tab = (isset($_GET['tab']) && $_GET['tab'] === 'signup') ? 'signup' : 'login';
?>

<section class="section">
    <div class="section_head">
        <h2 class="section_title">My Account</h2>
        <p class="section_subtitle">Sign in or create a new account</p>
    </div>

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
</section>

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

<?php include("footer.php"); ?>
