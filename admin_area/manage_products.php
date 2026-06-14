<?php
/* =====================================================
   MANAGE PRODUCTS  (admin: product catalog)
   Lists every product with quick edit / delete actions.
   ===================================================== */
include("includes/auth.php");
include("includes/db.php");

$message = "";
$message_type = "success";

// Handle delete (POST so it can't be triggered by a stray link).
if (isset($_POST['delete_product'])) {
    $del_id = (int) $_POST['product_id'];
    if ($del_id > 0 && $con) {
        $stmt = mysqli_prepare($con, "DELETE FROM products WHERE product_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $del_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Product deleted.";
        } else {
            $message = "Could not delete that product.";
            $message_type = "error";
        }
        mysqli_stmt_close($stmt);
    }
}

$admin_title = "Manage Products";
$active_nav  = "manage_products";
include("includes/admin_header.php");
?>

<div class="page_head">
    <h2>Manage Products</h2>
    <p>Edit, remove, or add products to your catalog.</p>
</div>

<?php if ($message): ?>
    <div class="alert alert_<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="card">
    <div class="form_actions" style="margin-top:0;">
        <a href="insert_product.php" class="btn btn_primary">&#43; Insert New Product</a>
    </div>

    <?php
    $run = $con ? dbQuery(
        "SELECT p.*, c.cat_title, b.brand_title
           FROM products p
           LEFT JOIN categories c ON c.cat_id = p.product_cat
           LEFT JOIN brands b ON b.brand_id = p.product_brand
          ORDER BY p.product_id DESC"
    ) : false;

    if (!$run || mysqli_num_rows($run) === 0): ?>
        <p class="empty_state">No products yet. Insert your first one above.</p>
    <?php else: ?>
        <table class="admin_table">
            <thead>
                <tr>
                    <th>Image</th><th>Title</th><th>Category</th>
                    <th>Brand</th><th>Price</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($run)):
                    $pid = (int) $row['product_id']; ?>
                <tr>
                    <td><img src="../images/<?php echo htmlspecialchars($row['product_image']); ?>"
                             alt="" class="admin_thumb" /></td>
                    <td><?php echo htmlspecialchars($row['product_title']); ?></td>
                    <td><?php echo htmlspecialchars($row['cat_title'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['brand_title'] ?? '—'); ?></td>
                    <td>$ <?php echo number_format((float) $row['product_price'], 2); ?></td>
                    <td class="admin_actions">
                        <a href="edit_product.php?id=<?php echo $pid; ?>"
                           class="btn btn_outline btn_sm">Edit</a>
                        <form method="post" action="manage_products.php"
                              onsubmit="return confirm('Delete this product?');"
                              style="display:inline;">
                            <input type="hidden" name="product_id" value="<?php echo $pid; ?>" />
                            <button type="submit" name="delete_product"
                                    class="btn btn_danger btn_sm">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include("includes/admin_footer.php"); ?>
