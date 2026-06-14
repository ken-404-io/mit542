-- =====================================================
-- STOREFRONT SCHEMA + DATA  (PostgreSQL / Neon)
-- -----------------------------------------------------
-- Run this once in the Neon SQL Editor (or: psql "<connection-string>"
-- -f database/schema_postgres.sql). It creates every table the
-- storefront + admin panel use and seeds your catalog so the home
-- page, sidebar (Categories / Brands), search and admin all work.
--
-- Safe to re-run: it drops and recreates the tables.
-- =====================================================

DROP TABLE IF EXISTS order_items CASCADE;
DROP TABLE IF EXISTS orders      CASCADE;
DROP TABLE IF EXISTS products    CASCADE;
DROP TABLE IF EXISTS categories  CASCADE;
DROP TABLE IF EXISTS brands      CASCADE;
DROP TABLE IF EXISTS users       CASCADE;
DROP TABLE IF EXISTS admins      CASCADE;
DROP TABLE IF EXISTS cart        CASCADE;
DROP TABLE IF EXISTS customers   CASCADE;
DROP TABLE IF EXISTS payments    CASCADE;

-- ---- Categories ----
CREATE TABLE categories (
    cat_id    SERIAL PRIMARY KEY,
    cat_title TEXT NOT NULL
);

-- ---- Brands ----
CREATE TABLE brands (
    brand_id    SERIAL PRIMARY KEY,
    brand_title TEXT NOT NULL
);

-- ---- Products ----
CREATE TABLE products (
    product_id       SERIAL PRIMARY KEY,
    product_cat      INTEGER,
    product_brand    INTEGER,
    product_title    VARCHAR(255),
    product_price    NUMERIC(10,2) DEFAULT 0,
    product_desc     TEXT,
    product_image    TEXT,
    product_keywords TEXT
);

-- ---- Users (customer accounts) ----
CREATE TABLE users (
    user_id       SERIAL PRIMARY KEY,
    user_name     VARCHAR(255) NOT NULL,
    user_email    VARCHAR(255) NOT NULL UNIQUE,
    user_password VARCHAR(255) NOT NULL DEFAULT '',
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ---- Admins (admin panel logins; empty = use built-in admin/admin123) ----
CREATE TABLE admins (
    id         SERIAL PRIMARY KEY,
    admin_name VARCHAR(255),
    admin_pass VARCHAR(255)
);

-- ---- Orders ----
CREATE TABLE orders (
    order_id         SERIAL PRIMARY KEY,
    user_id          INTEGER,
    customer_name    VARCHAR(255) NOT NULL,
    customer_email   VARCHAR(255) NOT NULL,
    customer_phone   VARCHAR(50)  NOT NULL DEFAULT '',
    customer_address TEXT,
    order_total      NUMERIC(10,2) NOT NULL DEFAULT 0,
    payment_method   VARCHAR(50)  NOT NULL DEFAULT '',
    payment_status   VARCHAR(20)  NOT NULL DEFAULT 'unpaid',
    order_status     VARCHAR(20)  NOT NULL DEFAULT 'pending',
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ---- Order line items ----
CREATE TABLE order_items (
    item_id       SERIAL PRIMARY KEY,
    order_id      INTEGER NOT NULL,
    product_id    INTEGER NOT NULL,
    product_title VARCHAR(255) NOT NULL,
    unit_price    NUMERIC(10,2) NOT NULL DEFAULT 0,
    quantity      INTEGER NOT NULL DEFAULT 1,
    subtotal      NUMERIC(10,2) NOT NULL DEFAULT 0
);

-- ---- Empty structural tables carried over from the original MySQL
--      database for a complete migration (the app does not use them;
--      the cart lives in the PHP session and order/payment data is
--      stored inline on the orders table). ----
CREATE TABLE cart (
    id SERIAL PRIMARY KEY
);

CREATE TABLE customers (
    id SERIAL PRIMARY KEY
);

CREATE TABLE payments (
    id SERIAL PRIMARY KEY
);

-- =====================================================
-- SEED DATA  (your existing catalog)
-- =====================================================

INSERT INTO categories (cat_id, cat_title) VALUES
    (1, 'Laptops'),
    (2, 'Cameras'),
    (3, 'Mobiles'),
    (4, 'Computers');

INSERT INTO brands (brand_id, brand_title) VALUES
    (1, 'HP'),
    (2, 'DELL'),
    (3, 'LG'),
    (4, 'Samsung');

-- Products that referenced uploaded files not in the repo (products 3-5)
-- point to bundled images so the live storefront looks complete. Re-upload
-- real photos from the admin panel (they go to Cloudinary) to replace them.
INSERT INTO products
    (product_id, product_cat, product_brand, product_title, product_price,
     product_desc, product_image, product_keywords) VALUES
    (1, 1, 2, 'Dell Laptop', 49000,
     '14-inch Dell laptop with fast SSD storage and all-day battery.',
     'images.jpg', 'Dell Laptop'),
    (2, 3, 4, 'Samsung A50', 39000,
     'Samsung Galaxy A50 with AMOLED display and triple rear camera.',
     'pee.jpg', 'Samsung A50'),
    (3, 3, 3, 'LG Smart Phone', 21999,
     '5.7-inch LG smartphone with triple camera and long battery life.',
     'images.jpg', 'LG Smartphone'),
    (4, 2, 2, 'Camera', 13999,
     'High-resolution digital camera with 4K video recording.',
     'pee.jpg', 'camera'),
    (5, 2, 2, 'Camera', 13999,
     'High-resolution digital camera with 4K video recording.',
     'pee.jpg', 'camera');

INSERT INTO users (user_id, user_name, user_email, user_password) VALUES
    (1, 'Kitazon', 'kitazon83@gmail.com', ''),
    (2, 'Harold Sarmiento', 'harold.sarmiento179519@oed.com.ph', '');

INSERT INTO orders
    (order_id, user_id, customer_name, customer_email, customer_phone,
     customer_address, order_total, payment_method, payment_status,
     order_status, created_at) VALUES
    (1, 0, 'HAROLD SARMIENTO (Rod)', 'haroldsarmiento2515@gmail.com',
     '09764267307', '#092', 27998.00, 'card', 'paid', 'processing',
     '2026-06-14 16:13:03'),
    (2, 0, 'HAROLD SARMIENTO (Rod)', 'haroldsarmiento2515@gmail.com',
     '09764267307', '#092', 13999.00, 'cod', 'unpaid', 'pending',
     '2026-06-14 16:13:41'),
    (3, 2, 'Harold Sarmiento', 'harold.sarmiento179519@oed.com.ph',
     '000000000000', 'NONE', 70999.00, 'cod', 'unpaid', 'pending',
     '2026-06-14 16:27:48');

INSERT INTO order_items
    (item_id, order_id, product_id, product_title, unit_price, quantity, subtotal) VALUES
    (1, 1, 4, 'Camera', 13999.00, 2, 27998.00),
    (2, 2, 4, 'Camera', 13999.00, 1, 13999.00),
    (3, 3, 1, 'Dell Laptop', 49000.00, 1, 49000.00),
    (4, 3, 3, 'LG Smart Phone', 21999.00, 1, 21999.00);

-- =====================================================
-- Sync the SERIAL sequences past the manually-inserted ids so new
-- rows (sign-ups, orders, products) get fresh, non-colliding ids.
-- =====================================================
SELECT setval('categories_cat_id_seq',   (SELECT MAX(cat_id)   FROM categories));
SELECT setval('brands_brand_id_seq',      (SELECT MAX(brand_id) FROM brands));
SELECT setval('products_product_id_seq',  (SELECT MAX(product_id) FROM products));
SELECT setval('users_user_id_seq',        (SELECT MAX(user_id)  FROM users));
SELECT setval('orders_order_id_seq',      (SELECT MAX(order_id) FROM orders));
SELECT setval('order_items_item_id_seq',  (SELECT MAX(item_id)  FROM order_items));
