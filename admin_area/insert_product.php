<?php
/* =====================================================
   INSERT PRODUCT  (redesigned + secured)
   - Modern admin theme
   - Prepared statements (no SQL injection)
   - Safe image upload into the storefront's images/ folder
   ===================================================== */
include("includes/auth.php");   // must be logged in
include("includes/db.php");

$message = "";   // feedback shown to the admin
$message_type = "success";

if (isset($_POST['insert_post'])) {

    // --- Text fields ---
    $product_title    = trim($_POST['product_title']);
    $product_cat      = (int) $_POST['product_cat'];
    $product_brand    = (int) $_POST['product_brand'];
    $product_price    = trim($_POST['product_price']);
    $product_desc     = trim($_POST['product_desc']);
    $product_keywords = trim($_POST['product_keywords']);

    $errors = array();

    if ($product_cat <= 0)   { $errors[] = "Please choose a category."; }
    if ($product_brand <= 0) { $errors[] = "Please choose a brand."; }

    // Upload the image (to Cloudinary when configured, else local images/).
    $product_image = "";
    $upload_error  = null;
    $stored = uploadProductImage($_FILES['product_image'] ?? array(), $upload_error);
    if ($stored === false) {
        $errors[] = $upload_error ?: "Please choose a valid product image.";
    } else {
        $product_image = $stored;
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare(
            $con,
            "INSERT INTO products
                (product_cat, product_brand, product_title, product_price,
                 product_desc, product_image, product_keywords)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt, "iissdss",
                $product_cat, $product_brand, $product_title, $product_price,
                $product_desc, $product_image, $product_keywords
            );

            if (mysqli_stmt_execute($stmt)) {
                $message = "Product \"" . htmlspecialchars($product_title) . "\" was inserted successfully.";
            } else {
                $message = "Database error: could not insert product.";
                $message_type = "error";
            }
            mysqli_stmt_close($stmt);
        } else {
            $message = "Database error: " . htmlspecialchars(mysqli_error($con));
            $message_type = "error";
        }
    } else {
        $message = implode(" ", $errors);
        $message_type = "error";
    }
}

$admin_title = "Insert Product";
$active_nav  = "insert_product";
include("includes/admin_header.php");
?>

<div class="page_head">
    <h2>Insert New Product</h2>
    <p>Add a product to your store. Fields marked with required must be filled.</p>
</div>

<?php if ($message): ?>
    <div class="alert alert_<?php echo $message_type; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="card">
    <form action="insert_product.php" method="post" enctype="multipart/form-data">
        <div class="form_grid">

            <div class="field">
                <label>Product Title</label>
                <input type="text" name="product_title"
                       placeholder="e.g. Wireless Headphones" required />
            </div>

            <div class="field">
                <label>Price</label>
                <input type="text" name="product_price"
                       placeholder="e.g. 49.99" required />
            </div>

            <div class="field">
                <label>Category</label>
                <select name="product_cat" required>
                    <option value="">Select a category</option>
                    <?php
                    if ($con) {
                        $run_cats = dbQuery("SELECT * FROM categories");
                        if ($run_cats) {
                            while ($row = mysqli_fetch_array($run_cats)) {
                                $id    = (int) $row['cat_id'];
                                $title = htmlspecialchars($row['cat_title']);
                                echo "<option value='$id'>$title</option>";
                            }
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="field">
                <label>Brand</label>
                <select name="product_brand" required>
                    <option value="">Select a brand</option>
                    <?php
                    if ($con) {
                        $run_brands = dbQuery("SELECT * FROM brands");
                        if ($run_brands) {
                            while ($row = mysqli_fetch_array($run_brands)) {
                                $id    = (int) $row['brand_id'];
                                $title = htmlspecialchars($row['brand_title']);
                                echo "<option value='$id'>$title</option>";
                            }
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="field full">
                <label>Keywords</label>
                <input type="text" name="product_keywords"
                       placeholder="comma, separated, search, terms" required />
                <p class="hint">Used to help customers find this product in search.</p>
            </div>

            <div class="field full">
                <label>Description</label>
                <textarea name="product_desc"
                          placeholder="Describe the product..." required></textarea>
            </div>

            <div class="field full">
                <label>Product Image</label>
                <div class="image_drop">
                    <input type="file" name="product_image" id="product_image"
                           accept="image/*" required />
                    <img id="image_preview" alt="Preview" />
                </div>
                <p class="hint">JPG, PNG, GIF, or WEBP.</p>
            </div>

            <div class="form_actions">
                <button type="submit" name="insert_post"
                        class="btn btn_primary">Insert Product</button>
                <a href="index.php" class="btn btn_outline">Cancel</a>
            </div>

        </div>
    </form>
</div>

<script>
    // Live image preview before upload.
    document.getElementById('product_image').addEventListener('change', function (e) {
        var file = e.target.files[0];
        var preview = document.getElementById('image_preview');
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    });
</script>

<?php include("includes/admin_footer.php"); ?>
