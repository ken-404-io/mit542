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

    // --- Image upload (stored in ../images so the storefront can show it) ---
    $product_image = $_FILES['product_image']['name'];
    $image_tmp     = $_FILES['product_image']['tmp_name'];
    $upload_dir    = __DIR__ . "/../images/";

    $errors = array();

    if ($product_cat <= 0)   { $errors[] = "Please choose a category."; }
    if ($product_brand <= 0) { $errors[] = "Please choose a brand."; }

    // Validate the uploaded file.
    if (empty($product_image) || $_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Please choose a product image.";
    } else {
        $allowed = array("jpg", "jpeg", "png", "gif", "webp");
        $ext = strtolower(pathinfo($product_image, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = "Image must be a JPG, PNG, GIF, or WEBP file.";
        } else {
            // Unique, safe filename so uploads never clash or break paths.
            $product_image = "prod_" . time() . "_" . mt_rand(1000, 9999) . "." . $ext;
        }
    }

    if (empty($errors)) {
        if (move_uploaded_file($image_tmp, $upload_dir . $product_image)) {

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
            $message = "Could not save the uploaded image. Check folder permissions.";
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
                        $run_cats = mysqli_query($con, "SELECT * FROM categories");
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
                        $run_brands = mysqli_query($con, "SELECT * FROM brands");
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
