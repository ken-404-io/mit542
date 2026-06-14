<?php
/* =====================================================
   EDIT PRODUCT  (admin: product catalog)
   Updates an existing product. The image is optional — keep
   the current one unless a new file is uploaded. Prepared
   statements throughout.
   ===================================================== */
include("includes/auth.php");
include("includes/db.php");

$message = "";
$message_type = "success";

$product_id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['product_id'] ?? 0);

// Update on submit.
if (isset($_POST['update_post']) && $con) {
    $product_title    = trim($_POST['product_title']);
    $product_cat      = (int) $_POST['product_cat'];
    $product_brand    = (int) $_POST['product_brand'];
    $product_price    = trim($_POST['product_price']);
    $product_desc     = trim($_POST['product_desc']);
    $product_keywords = trim($_POST['product_keywords']);
    $product_image    = $_POST['current_image'] ?? '';

    $errors = array();
    if ($product_title === '')   { $errors[] = "Title is required."; }
    if ($product_cat <= 0)       { $errors[] = "Please choose a category."; }
    if ($product_brand <= 0)     { $errors[] = "Please choose a brand."; }

    // Replace the image only when a new one is uploaded.
    if (!empty($_FILES['product_image']['name'])
        && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $upload_error = null;
        $stored = uploadProductImage($_FILES['product_image'], $upload_error);
        if ($stored === false) {
            $errors[] = $upload_error ?: "Could not save the uploaded image.";
        } else {
            $product_image = $stored;
        }
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare(
            $con,
            "UPDATE products SET product_cat=?, product_brand=?, product_title=?,
                    product_price=?, product_desc=?, product_image=?, product_keywords=?
              WHERE product_id=?"
        );
        mysqli_stmt_bind_param(
            $stmt, "iisdsssi",
            $product_cat, $product_brand, $product_title, $product_price,
            $product_desc, $product_image, $product_keywords, $product_id
        );
        if (mysqli_stmt_execute($stmt)) {
            $message = "Product updated successfully.";
        } else {
            $message = "Database error: could not update product.";
            $message_type = "error";
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = implode(" ", $errors);
        $message_type = "error";
    }
}

// Load the product to edit.
$product = null;
if ($product_id > 0 && $con) {
    $res = dbQuery("SELECT * FROM products WHERE product_id = '$product_id'");
    $product = $res ? mysqli_fetch_assoc($res) : null;
}

$admin_title = "Edit Product";
$active_nav  = "manage_products";
include("includes/admin_header.php");
?>

<div class="page_head">
    <h2>Edit Product</h2>
    <p>Update the details below and save your changes.</p>
</div>

<?php if ($message): ?>
    <div class="alert alert_<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if (!$product): ?>
    <div class="card"><p class="empty_state">
        Product not found. <a href="manage_products.php">Back to products</a>
    </p></div>
<?php else: ?>
<div class="card">
    <form action="edit_product.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="product_id" value="<?php echo (int) $product['product_id']; ?>" />
        <input type="hidden" name="current_image"
               value="<?php echo htmlspecialchars($product['product_image']); ?>" />
        <div class="form_grid">

            <div class="field">
                <label>Product Title</label>
                <input type="text" name="product_title" required
                       value="<?php echo htmlspecialchars($product['product_title']); ?>" />
            </div>

            <div class="field">
                <label>Price</label>
                <input type="text" name="product_price" required
                       value="<?php echo htmlspecialchars($product['product_price']); ?>" />
            </div>

            <div class="field">
                <label>Category</label>
                <select name="product_cat" required>
                    <?php
                    $run_cats = dbQuery("SELECT * FROM categories");
                    while ($run_cats && $c = mysqli_fetch_assoc($run_cats)):
                        $sel = ((int) $c['cat_id'] === (int) $product['product_cat']) ? 'selected' : '';
                    ?>
                        <option value="<?php echo (int) $c['cat_id']; ?>" <?php echo $sel; ?>>
                            <?php echo htmlspecialchars($c['cat_title']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="field">
                <label>Brand</label>
                <select name="product_brand" required>
                    <?php
                    $run_brands = dbQuery("SELECT * FROM brands");
                    while ($run_brands && $b = mysqli_fetch_assoc($run_brands)):
                        $sel = ((int) $b['brand_id'] === (int) $product['product_brand']) ? 'selected' : '';
                    ?>
                        <option value="<?php echo (int) $b['brand_id']; ?>" <?php echo $sel; ?>>
                            <?php echo htmlspecialchars($b['brand_title']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="field full">
                <label>Keywords</label>
                <input type="text" name="product_keywords"
                       value="<?php echo htmlspecialchars($product['product_keywords']); ?>" />
            </div>

            <div class="field full">
                <label>Description</label>
                <textarea name="product_desc"><?php echo htmlspecialchars($product['product_desc']); ?></textarea>
            </div>

            <div class="field full">
                <label>Current Image</label>
                <img src="<?php echo htmlspecialchars(productImageUrl($product['product_image'], '../images/')); ?>"
                     alt="" class="admin_thumb" style="display:block;margin-bottom:8px;" />
                <input type="file" name="product_image" accept="image/*" />
                <p class="hint">Leave empty to keep the current image.</p>
            </div>

            <div class="form_actions">
                <button type="submit" name="update_post" class="btn btn_primary">Save Changes</button>
                <a href="manage_products.php" class="btn btn_outline">Cancel</a>
            </div>

        </div>
    </form>
</div>
<?php endif; ?>

<?php include("includes/admin_footer.php"); ?>
