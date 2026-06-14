<?php
/* =====================================================
   USERS  (admin: monitor user accounts)
   Lists registered accounts with a snapshot of their order
   activity (orders placed and total amount spent on paid
   orders).
   ===================================================== */
include("includes/auth.php");
include("includes/db.php");

$admin_title = "Users";
$active_nav  = "users";
include("includes/admin_header.php");
?>

<div class="page_head">
    <h2>User Accounts</h2>
    <p>Registered customers and their order activity.</p>
</div>

<div class="card">
    <?php
    // LEFT JOIN so accounts with no orders still appear. Spend counts
    // only paid orders so the figure reflects real revenue per customer.
    $run = $con ? dbQuery(
        "SELECT u.user_id, u.user_name, u.user_email, u.created_at,
                COUNT(o.order_id) AS order_count,
                COALESCE(SUM(CASE WHEN o.payment_status = 'paid'
                                  THEN o.order_total ELSE 0 END), 0) AS total_spent
           FROM users u
           LEFT JOIN orders o ON o.user_id = u.user_id
          GROUP BY u.user_id, u.user_name, u.user_email, u.created_at
          ORDER BY u.user_id DESC"
    ) : false;

    if (!$run || mysqli_num_rows($run) === 0): ?>
        <p class="empty_state">No registered users yet.</p>
    <?php else: ?>
        <table class="admin_table">
            <thead>
                <tr>
                    <th>#</th><th>Name</th><th>Email</th>
                    <th>Orders</th><th>Total Spent</th><th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($u = mysqli_fetch_assoc($run)): ?>
                <tr>
                    <td><?php echo (int) $u['user_id']; ?></td>
                    <td><?php echo htmlspecialchars($u['user_name']); ?></td>
                    <td><?php echo htmlspecialchars($u['user_email']); ?></td>
                    <td><?php echo (int) $u['order_count']; ?></td>
                    <td>$ <?php echo number_format((float) $u['total_spent'], 2); ?></td>
                    <td class="hint"><?php echo htmlspecialchars($u['created_at']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include("includes/admin_footer.php"); ?>
