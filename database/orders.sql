-- =====================================================
-- ORDERS SCHEMA
-- -----------------------------------------------------
-- Adds the tables the checkout / payment / order-tracking
-- flow reads from. `orders` is one row per placed order;
-- `order_items` is the line items that make up each order.
-- A snapshot of each product's title and price is stored on
-- the line item so historic orders stay accurate even after
-- the catalog changes. Import after schema.sql / users.sql:
--
--   mysql -h HOST -u USER -p DBNAME < database/orders.sql
-- =====================================================

CREATE TABLE IF NOT EXISTS orders (
    order_id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NULL,                       -- linked account (NULL = guest)
    customer_name    VARCHAR(255) NOT NULL,
    customer_email   VARCHAR(255) NOT NULL,
    customer_phone   VARCHAR(50)  NOT NULL DEFAULT '',
    customer_address TEXT,
    order_total      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_method   VARCHAR(50)  NOT NULL DEFAULT '',
    payment_status   VARCHAR(20)  NOT NULL DEFAULT 'unpaid',   -- unpaid | paid
    order_status     VARCHAR(20)  NOT NULL DEFAULT 'pending',  -- pending | processing | shipped | completed | cancelled
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items (
    item_id       INT AUTO_INCREMENT PRIMARY KEY,
    order_id      INT NOT NULL,
    product_id    INT NOT NULL,
    product_title VARCHAR(255) NOT NULL,
    unit_price    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    quantity      INT NOT NULL DEFAULT 1,
    subtotal      DECIMAL(10,2) NOT NULL DEFAULT 0.00
);
