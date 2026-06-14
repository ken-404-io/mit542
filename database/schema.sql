-- =====================================================
-- STOREFRONT SCHEMA + SAMPLE DATA
-- -----------------------------------------------------
-- Creates the categories, brands and products tables the
-- storefront reads from, then seeds a little demo content so
-- the home page, sidebar and search show something out of the
-- box. Import this into your MySQL database, e.g.:
--
--   mysql -h HOST -u USER -p DBNAME < database/schema.sql
--
-- (Column names/types match exactly what the PHP code expects;
--  see functions/functions.php and admin_area/insert_product.php.)
-- =====================================================

CREATE TABLE IF NOT EXISTS categories (
    cat_id    INT AUTO_INCREMENT PRIMARY KEY,
    cat_title VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS brands (
    brand_id    INT AUTO_INCREMENT PRIMARY KEY,
    brand_title VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS products (
    product_id       INT AUTO_INCREMENT PRIMARY KEY,
    product_cat      INT NOT NULL,
    product_brand    INT NOT NULL,
    product_title    VARCHAR(255)   NOT NULL,
    product_price    DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    product_desc     TEXT,
    product_image    VARCHAR(255)   NOT NULL DEFAULT '',
    product_keywords VARCHAR(255)   NOT NULL DEFAULT ''
);

-- ---- Sample categories ----
INSERT INTO categories (cat_id, cat_title) VALUES
    (1, 'Electronics'),
    (2, 'Accessories'),
    (3, 'Home & Office');

-- ---- Sample brands ----
INSERT INTO brands (brand_id, brand_title) VALUES
    (1, 'Acme'),
    (2, 'Globex'),
    (3, 'Initech');

-- ---- Sample products ----
-- product_image values reference files that already exist in images/.
INSERT INTO products
    (product_cat, product_brand, product_title, product_price, product_desc, product_image, product_keywords)
VALUES
    (1, 1, 'Wireless Headphones', 49.99,
     'Comfortable over-ear wireless headphones with deep bass and long battery life.',
     'images.jpg', 'headphones, audio, wireless, music'),
    (1, 2, 'Smart Watch', 89.00,
     'Track your steps, heart rate and notifications from your wrist.',
     'images.jpg', 'watch, smart, fitness, wearable'),
    (2, 3, 'Laptop Backpack', 34.50,
     'Water-resistant backpack with a padded laptop compartment.',
     'pee.jpg', 'backpack, bag, laptop, travel'),
    (3, 1, 'Desk Lamp', 22.75,
     'Adjustable LED desk lamp with three brightness levels.',
     'pee.jpg', 'lamp, desk, light, office');
