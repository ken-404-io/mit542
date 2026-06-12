<?php
$page_title = "Contact Us - My Online Shop";
include("header.php");

$sent = isset($_POST['send_message']);
?>

<section class="section">
    <div class="section_head">
        <h2 class="section_title">Contact Us</h2>
        <p class="section_subtitle">We'd love to hear from you</p>
    </div>

    <div class="contact_layout">
        <div class="contact_info card">
            <h3>Get in Touch</h3>
            <p><strong>Address:</strong> 123 Market Street, Manila</p>
            <p><strong>Email:</strong> support@myonlineshop.test</p>
            <p><strong>Phone:</strong> (02) 8123 4567</p>
            <p><strong>Hours:</strong> Mon&ndash;Sat, 9:00 AM &ndash; 6:00 PM</p>
        </div>

        <div class="contact_form card">
            <?php if ($sent): ?>
                <p class="alert_success">
                    Thanks! Your message has been received.
                </p>
            <?php endif; ?>
            <form method="post" action="contact.php">
                <label>Your Name
                    <input type="text" name="name" required />
                </label>
                <label>Your Email
                    <input type="email" name="email" required />
                </label>
                <label>Message
                    <textarea name="message" rows="5" required></textarea>
                </label>
                <button type="submit" name="send_message"
                        class="btn btn_primary">Send Message</button>
            </form>
        </div>
    </div>
</section>

<?php include("footer.php"); ?>
