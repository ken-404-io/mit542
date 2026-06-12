<?php
$page_title = "My Account - My Online Shop";
include("header.php");
?>

<section class="section">
    <div class="section_head">
        <h2 class="section_title">My Account</h2>
        <p class="section_subtitle">Sign in or create a new account</p>
    </div>

    <div class="account_layout">
        <!-- ===== LOGIN ===== -->
        <div class="card account_card" id="login">
            <h3>Sign In</h3>
            <form method="post" action="account.php">
                <label>Email
                    <input type="email" name="login_email" required />
                </label>
                <label>Password
                    <input type="password" name="login_pass" required />
                </label>
                <button type="submit" name="login"
                        class="btn btn_primary">Sign In</button>
            </form>
        </div>

        <!-- ===== SIGN UP ===== -->
        <div class="card account_card" id="signup">
            <h3>Create Account</h3>
            <form method="post" action="account.php">
                <label>Full Name
                    <input type="text" name="reg_name" required />
                </label>
                <label>Email
                    <input type="email" name="reg_email" required />
                </label>
                <label>Password
                    <input type="password" name="reg_pass" required />
                </label>
                <button type="submit" name="register"
                        class="btn btn_primary">Sign Up</button>
            </form>
        </div>
    </div>
</section>

<?php include("footer.php"); ?>
