-- =============================================================
-- DATABASE: phone_store_v2 (UPDATED v4)
-- Các thay đổi so với bản gốc:
--   + Thêm bảng: suppliers, payments, user_addresses, wishlists, coupon_usages,
--                order_status_histories, notifications
--   ~ Sửa products: thêm specifications (JSON)
--   ~ Sửa orders: thêm coupon_id, address_id, subtotal, payment_method
--   ~ Sửa coupons: thêm max_uses_per_user, start_at, CHECK used_count
--   ~ Sửa inventory: CHECK quantity >= 0, NOT NULL
--   ~ Sửa order_items: price/quantity NOT NULL
--   ~ Sửa cart_items: UNIQUE (cart_id, variant_id)
--   ~ Sửa reviews: UNIQUE (user_id, product_id)
--   ~ Sửa inventory_logs: thêm supplier_id, import_price, reference_type rõ ràng hơn
--   ~ Sửa variant_images: thêm created_at
--   + Thêm index: order_items, cart_items
-- =============================================================

CREATE DATABASE IF NOT EXISTS phone_store_v2
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE phone_store_v2;

-- =========================
-- 1. USERS
-- =========================
CREATE TABLE users (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    name            VARCHAR(255),
    email           VARCHAR(255) UNIQUE,
    password        VARCHAR(255),
    phone           VARCHAR(20),
    avatar          VARCHAR(255),
    role            ENUM('admin','customer') DEFAULT 'customer',
    email_verified_at TIMESTAMP NULL,
    remember_token  VARCHAR(100),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP NULL
);

-- =========================
-- 2. USER ADDRESSES       ← MỚI: lưu nhiều địa chỉ per user
-- =========================
CREATE TABLE user_addresses (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id         BIGINT NOT NULL,
    receiver_name   VARCHAR(255) NOT NULL,
    receiver_phone  VARCHAR(20)  NOT NULL,
    province        VARCHAR(100),
    district        VARCHAR(100),
    ward            VARCHAR(100),
    address_detail  TEXT,
    is_default      BOOLEAN DEFAULT FALSE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- 3. SUPPLIERS             ← MỚI: quản lý nhà cung cấp
-- =========================
CREATE TABLE suppliers (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name            VARCHAR(255) NOT NULL,
    contact_person  VARCHAR(255),
    email           VARCHAR(255),
    phone           VARCHAR(255),
    address         TEXT,
    is_active       BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL
);

-- =========================
-- 4. BRANDS
-- =========================
CREATE TABLE brands (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(255) NOT NULL,
    slug        VARCHAR(255) UNIQUE,
    logo        VARCHAR(255),
    is_active   BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =========================
-- 5. PRODUCTS
-- =========================
CREATE TABLE products (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    name            VARCHAR(255) NOT NULL,
    slug            VARCHAR(255) UNIQUE,
    brand_id        BIGINT,
    description     TEXT,
    short_desc      VARCHAR(500),
    status          TINYINT DEFAULT 1 COMMENT '1=active, 0=inactive',
    specifications  JSON NULL,  -- ← MỚI
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP NULL,
    FOREIGN KEY (brand_id)    REFERENCES brands(id)     ON DELETE SET NULL
);

-- =========================
-- 6. ATTRIBUTES
-- =========================
CREATE TABLE attributes (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    code        VARCHAR(50) UNIQUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- 7. ATTRIBUTE VALUES
-- =========================
CREATE TABLE attribute_values (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    attribute_id    BIGINT NOT NULL,
    value           VARCHAR(100),
    numeric_value   INT,
    color_hex       VARCHAR(10) COMMENT 'dùng nếu attribute là màu sắc',
    sort_order      INT DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attribute_id) REFERENCES attributes(id) ON DELETE CASCADE
);

-- =========================
-- 8. PRODUCT VARIANTS (SKU)
-- =========================
CREATE TABLE product_variants (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    product_id      BIGINT NOT NULL,
    sku             VARCHAR(100) UNIQUE,
    price           DECIMAL(12,2) NOT NULL,
    compare_price   DECIMAL(12,2)  COMMENT 'giá gốc/gạch ngang',
    cost_price      DECIMAL(12,2)  COMMENT 'giá vốn nội bộ',
    is_active       BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- =========================
-- 9. VARIANT ATTRIBUTES
-- =========================
CREATE TABLE variant_attributes (
    variant_id          BIGINT,
    attribute_id        BIGINT,
    attribute_value_id  BIGINT,
    PRIMARY KEY (variant_id, attribute_id),
    FOREIGN KEY (variant_id)         REFERENCES product_variants(id)  ON DELETE CASCADE,
    FOREIGN KEY (attribute_id)       REFERENCES attributes(id),
    FOREIGN KEY (attribute_value_id) REFERENCES attribute_values(id)
);

-- =========================
-- 10. VARIANT IMAGES
-- =========================
CREATE TABLE variant_images (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    variant_id  BIGINT,
    image_url   VARCHAR(255),
    is_primary  BOOLEAN DEFAULT FALSE,
    sort_order  INT DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE
);

-- =========================
-- 11. WAREHOUSES
-- =========================
CREATE TABLE warehouses (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(255),
    location    VARCHAR(255),
    is_active   BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- 12. INVENTORY
-- =========================
CREATE TABLE inventory (
    variant_id      BIGINT,
    warehouse_id    BIGINT,
    quantity        INT NOT NULL DEFAULT 0 CHECK (quantity >= 0),
    PRIMARY KEY (variant_id, warehouse_id),
    FOREIGN KEY (variant_id)   REFERENCES product_variants(id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id)
);

-- =========================
-- 13. INVENTORY LOGS
-- =========================
CREATE TABLE inventory_logs (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    variant_id      BIGINT,
    warehouse_id    BIGINT,
    supplier_id     BIGINT UNSIGNED NULL, -- ← MỚI
    change_type     ENUM('IMPORT','EXPORT','ADJUST') NOT NULL,
    quantity_change INT NOT NULL,
    import_price    DECIMAL(15,2) NULL, -- ← MỚI
    quantity_before INT COMMENT 'tồn kho trước khi thay đổi',
    quantity_after  INT COMMENT 'tồn kho sau khi thay đổi',
    reference_type  VARCHAR(50)  COMMENT 'order / purchase_order / manual',
    reference_id    BIGINT       COMMENT 'id của order hoặc PO tương ứng',
    note            TEXT,
    created_by      BIGINT COMMENT 'user_id thực hiện',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (variant_id)   REFERENCES product_variants(id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (supplier_id)  REFERENCES suppliers(id) ON DELETE SET NULL -- ← MỚI
);

-- =========================
-- 14. COUPONS
-- =========================
CREATE TABLE coupons (
    id                  BIGINT PRIMARY KEY AUTO_INCREMENT,
    code                VARCHAR(50) UNIQUE,
    description         VARCHAR(255),
    discount_type       ENUM('percent','fixed') NOT NULL,
    discount_value      DECIMAL(10,2) NOT NULL,
    max_discount_amount DECIMAL(12,2) COMMENT 'giới hạn số tiền giảm tối đa (dùng với percent)',
    min_order_value     DECIMAL(12,2) DEFAULT 0,
    max_uses            INT  COMMENT 'tổng lượt dùng tối đa',
    max_uses_per_user   INT DEFAULT 1 COMMENT 'mỗi user dùng tối đa bao nhiêu lần',
    used_count          INT DEFAULT 0,
    start_at            TIMESTAMP NULL,
    expires_at          TIMESTAMP NULL,
    is_active           BOOLEAN DEFAULT TRUE,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Trigger thay thế CHECK cross-column: used_count <= max_uses
DELIMITER $$

CREATE TRIGGER trg_coupons_insert_check
BEFORE INSERT ON coupons
FOR EACH ROW
BEGIN
    IF NEW.max_uses IS NOT NULL AND NEW.used_count > NEW.max_uses THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'used_count cannot exceed max_uses';
    END IF;
END$$

CREATE TRIGGER trg_coupons_update_check
BEFORE UPDATE ON coupons
FOR EACH ROW
BEGIN
    IF NEW.max_uses IS NOT NULL AND NEW.used_count > NEW.max_uses THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'used_count cannot exceed max_uses';
    END IF;
END$$

DELIMITER ;

-- =========================
-- 15. COUPON USAGES
-- =========================
CREATE TABLE coupon_usages (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    coupon_id   BIGINT NOT NULL,
    user_id     BIGINT NOT NULL,
    order_id    BIGINT,
    used_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id),
    FOREIGN KEY (user_id)   REFERENCES users(id)
);

-- =========================
-- 16. ORDERS
-- =========================
CREATE TABLE orders (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id         BIGINT,
    address_id      BIGINT NULL COMMENT 'snapshot địa chỉ lúc đặt hàng',
    coupon_id       BIGINT NULL,

    subtotal        DECIMAL(12,2) COMMENT 'tổng tiền hàng trước giảm',
    discount_amount DECIMAL(12,2) DEFAULT 0,
    shipping_fee    DECIMAL(12,2) DEFAULT 0,
    total_price     DECIMAL(12,2) COMMENT 'thực thu = subtotal - discount + shipping',

    status          ENUM('PENDING','CONFIRMED','SHIPPING','COMPLETED','CANCELLED') DEFAULT 'PENDING',
    payment_status  ENUM('UNPAID','PAID','REFUNDED') DEFAULT 'UNPAID',
    payment_method  VARCHAR(255) NULL, -- ← MỚI

    -- snapshot địa chỉ (giữ lại để không bị mất khi user xóa địa chỉ)
    shipping_name   VARCHAR(255),
    shipping_phone  VARCHAR(20),
    shipping_address TEXT,

    note            TEXT,
    cancelled_reason TEXT,

    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)   REFERENCES users(id),
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL,
    FOREIGN KEY (address_id) REFERENCES user_addresses(id) ON DELETE SET NULL
);

-- Thêm FK coupon_usages → orders (sau khi orders đã tồn tại)
ALTER TABLE coupon_usages
    ADD CONSTRAINT fk_coupon_usages_order
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL;

-- =========================
-- 17. ORDER ITEMS
-- =========================
CREATE TABLE order_items (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_id    BIGINT,
    variant_id  BIGINT,
    sku         VARCHAR(100) COMMENT 'snapshot SKU lúc mua',
    name        VARCHAR(255) COMMENT 'snapshot tên SP lúc mua',
    price       DECIMAL(12,2) NOT NULL,
    quantity    INT NOT NULL DEFAULT 1,
    FOREIGN KEY (order_id)  REFERENCES orders(id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
);

-- =========================
-- 18. PAYMENTS             ← MỚI: lưu thông tin thanh toán
-- =========================
CREATE TABLE payments (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_id        BIGINT NOT NULL,
    method          ENUM('COD','MOMO','VNPAY','ZALOPAY','BANKING','CREDIT_CARD') NOT NULL,
    transaction_id  VARCHAR(255) COMMENT 'mã giao dịch từ cổng thanh toán',
    amount          DECIMAL(12,2) NOT NULL,
    status          ENUM('PENDING','SUCCESS','FAILED','REFUNDED') DEFAULT 'PENDING',
    gateway_response TEXT COMMENT 'raw response từ cổng (JSON)',
    paid_at         TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- =========================
-- 19. ORDER STATUS HISTORY ← MỚI: log mỗi lần đổi trạng thái đơn
-- =========================
CREATE TABLE order_status_histories (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_id    BIGINT NOT NULL,
    old_status  VARCHAR(50),
    new_status  VARCHAR(50) NOT NULL,
    note        TEXT,
    changed_by  BIGINT COMMENT 'user_id (admin hoặc system)',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- =========================
-- 20. CARTS
-- =========================
CREATE TABLE carts (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id     BIGINT NULL,
    session_id  VARCHAR(255) NULL COMMENT 'cho guest chưa đăng nhập',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- 21. CART ITEMS
-- =========================
CREATE TABLE cart_items (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    cart_id     BIGINT,
    variant_id  BIGINT,
    quantity    INT DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cart_variant (cart_id, variant_id),
    FOREIGN KEY (cart_id)   REFERENCES carts(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE
);

-- =========================
-- 22. WISHLISTS            ← MỚI: sản phẩm yêu thích
-- =========================
CREATE TABLE wishlists (
    user_id     BIGINT,
    product_id  BIGINT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, product_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- =========================
-- 23. REVIEWS
-- =========================
CREATE TABLE reviews (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    product_id      BIGINT,
    user_id         BIGINT,
    order_item_id   BIGINT NULL COMMENT 'chỉ ai đã mua mới review được',
    rating          TINYINT CHECK (rating BETWEEN 1 AND 5),
    comment         TEXT,
    images          JSON COMMENT 'mảng URL ảnh đính kèm review',
    is_approved     BOOLEAN DEFAULT FALSE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_review_user_product (user_id, product_id),
    FOREIGN KEY (product_id)    REFERENCES products(id)    ON DELETE CASCADE,
    FOREIGN KEY (user_id)       REFERENCES users(id)       ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE SET NULL
);

-- =========================
-- 24. NOTIFICATIONS        ← MỚI: thông báo cho user
-- =========================
CREATE TABLE notifications (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id     BIGINT NOT NULL,
    type        VARCHAR(100) COMMENT 'order_status / promotion / system',
    title       VARCHAR(255),
    body        TEXT,
    data        JSON COMMENT 'payload tuỳ loại thông báo (vd: order_id)',
    is_read     BOOLEAN DEFAULT FALSE,
    read_at     TIMESTAMP NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================================
-- INDEXES
-- =============================================================

-- Products
CREATE INDEX idx_products_brand          ON products(brand_id);
CREATE INDEX idx_products_status         ON products(status, deleted_at);

-- Variants
CREATE INDEX idx_variant_price           ON product_variants(price);
CREATE INDEX idx_variant_product         ON product_variants(product_id);

-- Variant attributes
CREATE INDEX idx_variant_attr_value      ON variant_attributes(attribute_value_id);

-- Inventory
CREATE INDEX idx_inventory_variant       ON inventory(variant_id);

-- Orders
CREATE INDEX idx_orders_user_status      ON orders(user_id, status);
CREATE INDEX idx_orders_created          ON orders(created_at);

-- Payments
CREATE INDEX idx_payments_order          ON payments(order_id);
CREATE INDEX idx_payments_transaction    ON payments(transaction_id);

-- Cart
CREATE INDEX idx_cart_session            ON carts(session_id);
CREATE INDEX idx_cart_user               ON carts(user_id);

-- Reviews
CREATE INDEX idx_reviews_product         ON reviews(product_id, is_approved);

-- Notifications
CREATE INDEX idx_notifications_user      ON notifications(user_id, is_read);

-- Coupon usages
CREATE INDEX idx_coupon_usage_user       ON coupon_usages(coupon_id, user_id);

-- Wishlist
CREATE INDEX idx_wishlist_user           ON wishlists(user_id);

-- Order items
CREATE INDEX idx_order_items_order       ON order_items(order_id);

-- Cart items
CREATE INDEX idx_cart_items_cart         ON cart_items(cart_id);

-- =========================
-- 25. SETTINGS
-- =========================
CREATE TABLE settings (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    `key`       VARCHAR(100) UNIQUE NOT NULL,
    `value`     TEXT NULL,
    `group`     VARCHAR(50) DEFAULT 'general',
    description VARCHAR(255) NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =========================
-- 26. BANNERS
-- =========================
CREATE TABLE banners (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    title       VARCHAR(255) NULL,
    image_url   VARCHAR(255) NOT NULL,
    link_url    VARCHAR(255) NULL,
    type        ENUM('MAIN', 'SECONDARY') DEFAULT 'MAIN',
    sort_order  INT DEFAULT 0,
    is_active   BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert initial settings
INSERT INTO settings (`key`, `value`, `group`, `description`) VALUES
('store_phone', '0123.456.789', 'store', 'Số điện thoại hiển thị trên header'),
('vnpay_tmn_code', '', 'vnpay', 'Mã định danh website tại hệ thống VNPAY'),
('vnpay_hash_secret', '', 'vnpay', 'Chuỗi bí mật dùng để tạo mã hash từ VNPAY'),
('vnpay_url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html', 'vnpay', 'URL cổng thanh toán VNPAY');
