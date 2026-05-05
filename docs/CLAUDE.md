# CLAUDE.md — Phone Store (Laravel)

> **Mục đích:** Tài liệu kỹ thuật toàn diện cho AI assistant (Claude) và developer.
> Đọc file này trước khi viết bất kỳ dòng code nào trong dự án.

---

## 1. TỔNG QUAN DỰ ÁN

| Thông tin | Chi tiết |
|-----------|----------|
| Tên dự án | Phone Store — Website bán điện thoại |
| Framework | Laravel 11 |
| Database | MySQL 8 — `phone_store_v2` |
| Frontend | Blade + Tailwind CSS + Alpine.js |
| Auth | Laravel Auth + Spatie Permission |
| Storage | Laravel Storage (public disk) + Intervention Image |
| PDF | barryvdh/laravel-dompdf |
| Search | Laravel Scout |
| Vai trò | `admin` / `customer` (ENUM trong bảng `users`) |
| Thư mục gốc | `D:\Project-khanh\phone-store` |

---

## 2. MÔI TRƯỜNG & CÀI ĐẶT

### Yêu cầu
- PHP >= 8.2
- Composer >= 2.x
- MySQL Workbench >= 8.0
- Node.js >= 18.x + npm

### Packages PHP
```bash
composer require spatie/laravel-permission
composer require intervention/image-laravel
composer require laravel/scout
composer require barryvdh/laravel-dompdf
```

### Packages Node
```bash
npm install -D tailwindcss @tailwindcss/vite alpinejs
```

### File .env quan trọng
```env
APP_NAME="Phone Store"
DB_CONNECTION=mysql
DB_DATABASE=phone_store_v2
DB_USERNAME=root
DB_PASSWORD=your_password
FILESYSTEM_DISK=public
```

### Khởi động dự án
```bash
php artisan storage:link        # link public storage
php artisan key:generate
npm run dev                     # hoặc npm run build
php artisan serve
```

---

## 3. DATABASE — 24 BẢNG

### 3.1 Sơ đồ quan hệ tổng quát

```
[users] ──1:N──► [user_addresses]
[users] ──1:1──► [carts] ──1:N──► [cart_items] ──N:1──► [product_variants]
[users] ──1:N──► [orders] ──1:N──► [order_items] ──N:1──► [product_variants]
[users] ──N:N──► [wishlists] ──► [products]
[users] ──1:N──► [reviews]
[users] ──1:N──► [notifications]

[brands] ──1:N──► [products] ◄──N:1── [categories] (self-join parent_id)
[products] ──1:N──► [product_variants]
[product_variants] ──N:N──► [attributes]  (qua variant_attributes + attribute_values)
[product_variants] ──1:N──► [variant_images]
[product_variants] ──N:N──► [warehouses]  (qua inventory)

[orders] ──1:N──► [payments]
[orders] ──1:N──► [order_status_histories]
[orders] ──N:1──► [coupons] ──1:N──► [coupon_usages]
[warehouses] ──1:N──► [inventory_logs]
```

### 3.2 Chi tiết từng bảng

| # | Bảng | Mô tả | Lưu ý quan trọng |
|---|------|-------|-----------------|
| 1 | `users` | Tài khoản người dùng | Soft delete (`deleted_at`), role ENUM |
| 2 | `user_addresses` | Nhiều địa chỉ giao hàng/user | `is_default`, snapshot vào orders |
| 3 | `brands` | Hãng điện thoại | `slug`, `is_active` |
| 4 | `categories` | Danh mục, hỗ trợ self-join | `parent_id` NULL = danh mục gốc |
| 5 | `products` | Sản phẩm — không có giá | Soft delete, giá ở `product_variants` |
| 6 | `attributes` | Loại thuộc tính (màu, RAM...) | `code` UNIQUE (vd: `color`, `ram`) |
| 7 | `attribute_values` | Giá trị thuộc tính | `color_hex` cho màu sắc |
| 8 | `product_variants` | SKU — 1 variant = 1 tổ hợp thuộc tính | Có `price`, `compare_price`, `cost_price` |
| 9 | `variant_attributes` | Pivot: variant ↔ attribute_value | PK (variant_id, attribute_id) |
| 10 | `variant_images` | Ảnh của từng variant | `is_primary`, `sort_order` |
| 11 | `warehouses` | Kho hàng | Có thể nhiều kho |
| 12 | `inventory` | Tồn kho theo variant × warehouse | CHECK `quantity >= 0` |
| 13 | `inventory_logs` | Lịch sử nhập/xuất kho | `change_type`: IMPORT/EXPORT/ADJUST |
| 14 | `coupons` | Mã giảm giá | `percent` hoặc `fixed`, có `max_uses_per_user` |
| 15 | `coupon_usages` | Track ai đã dùng coupon nào | FK đến orders (thêm sau bằng ALTER) |
| 16 | `orders` | Đơn hàng | Snapshot địa chỉ, có `subtotal/discount/shipping/total` |
| 17 | `order_items` | Chi tiết sản phẩm trong đơn | Snapshot `sku` và `name` lúc mua |
| 18 | `payments` | Lịch sử giao dịch thanh toán | COD/MOMO/VNPAY/ZALOPAY/BANKING/CREDIT_CARD |
| 19 | `order_status_histories` | Log mỗi lần đổi trạng thái đơn | `old_status` → `new_status` |
| 20 | `carts` | Giỏ hàng | Guest (`session_id`) và user (`user_id`) |
| 21 | `cart_items` | Sản phẩm trong giỏ | UNIQUE (cart_id, variant_id) |
| 22 | `wishlists` | Sản phẩm yêu thích | PK (user_id, product_id) |
| 23 | `reviews` | Đánh giá sản phẩm | UNIQUE (user_id, product_id); phải mua mới được review |
| 24 | `notifications` | Thông báo cho user | `type`: order_status / promotion / system |
| 25 | `suppliers` | Nhà cung cấp | `name`, `contact_person`, `email`, `phone` |

### 3.3 Enum quan trọng cần nhớ

```
users.role                → 'admin' | 'customer'
orders.status             → 'PENDING' | 'CONFIRMED' | 'SHIPPING' | 'COMPLETED' | 'CANCELLED'
orders.payment_status     → 'UNPAID' | 'PAID' | 'REFUNDED'
payments.method           → 'COD' | 'MOMO' | 'VNPAY' | 'ZALOPAY' | 'BANKING' | 'CREDIT_CARD'
payments.status           → 'PENDING' | 'SUCCESS' | 'FAILED' | 'REFUNDED'
inventory_logs.change_type→ 'IMPORT' | 'EXPORT' | 'ADJUST'
coupons.discount_type     → 'percent' | 'fixed'
```

---

## 4. CẤU TRÚC THƯ MỤC LARAVEL

```
phone-store/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── VariantController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── BrandController.php
│   │   │   │   ├── OrderController.php
│   │   │   │   ├── CouponController.php
│   │   │   │   ├── InventoryController.php
│   │   │   │   ├── WarehouseController.php
│   │   │   │   ├── ReviewController.php
│   │   │   │   ├── SupplierController.php
│   │   │   │   └── UserController.php
│   │   │   └── Customer/
│   │   │       ├── HomeController.php
│   │   │       ├── ProductController.php
│   │   │       ├── CartController.php
│   │   │       ├── CheckoutController.php
│   │   │       ├── OrderController.php
│   │   │       ├── WishlistController.php
│   │   │       ├── ReviewController.php
│   │   │       ├── ProfileController.php
│   │   │       ├── AddressController.php
│   │   │       └── NotificationController.php
│   │   ├── Middleware/
│   │   │   ├── AdminMiddleware.php
│   │   │   └── EnsureEmailVerified.php
│   │   └── Requests/
│   │       ├── Admin/
│   │       │   ├── StoreProductRequest.php
│   │       │   ├── StoreVariantRequest.php
│   │       │   ├── StoreOrderStatusRequest.php
│   │       │   └── StoreCouponRequest.php
│   │       │   └── SupplierRequest.php
│   │       └── Customer/
│   │           ├── CheckoutRequest.php
│   │           └── ReviewRequest.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── UserAddress.php
│   │   ├── Brand.php
│   │   ├── Category.php
│   │   ├── Product.php
│   │   ├── Attribute.php
│   │   ├── AttributeValue.php
│   │   ├── ProductVariant.php
│   │   ├── VariantAttribute.php
│   │   ├── VariantImage.php
│   │   ├── Warehouse.php
│   │   ├── Inventory.php
│   │   ├── InventoryLog.php
│   │   ├── Coupon.php
│   │   ├── CouponUsage.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── Payment.php
│   │   ├── OrderStatusHistory.php
│   │   ├── Cart.php
│   │   ├── CartItem.php
│   │   ├── Wishlist.php
│   │   ├── Review.php
│   │   ├── Supplier.php
│   │   └── Notification.php
│   ├── Services/
│   │   ├── CartService.php
│   │   ├── OrderService.php
│   │   ├── PaymentService.php
│   │   ├── CouponService.php
│   │   ├── InventoryService.php
│   │   └── NotificationService.php
│   ├── Observers/
│   │   ├── OrderObserver.php           ← tự tạo order_status_history
│   │   └── InventoryObserver.php       ← tự tạo inventory_log
│   └── Events/ + Listeners/
│       ├── OrderPlaced → SendOrderConfirmationEmail
│       └── OrderStatusChanged → SendOrderStatusNotification
├── database/
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── UserSeeder.php              ← tạo admin mặc định
│   │   ├── BrandSeeder.php
│   │   ├── CategorySeeder.php
│   │   ├── ProductSeeder.php
│   │   └── WarehouseSeeder.php
│   └── factories/
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── admin.blade.php         ← sidebar + topbar
│   │   │   └── app.blade.php           ← header + footer customer
│   │   ├── admin/
│   │   │   ├── dashboard/index.blade.php
│   │   │   ├── products/ (index, create, edit)
│   │   │   ├── variants/ (index, form)
│   │   │   ├── categories/ (index, form)
│   │   │   ├── brands/ (index, form)
│   │   │   ├── orders/ (index, show, invoice)
│   │   │   ├── coupons/ (index, form)
│   │   │   ├── inventory/ (index, import, logs)
│   │   │   ├── reviews/ (index)
│   │   │   └── users/ (index, show)
│   │   │   └── suppliers/ (index, form)
│   │   ├── customer/
│   │   │   ├── home/index.blade.php
│   │   │   ├── products/ (index, show)
│   │   │   ├── cart/index.blade.php
│   │   │   ├── checkout/index.blade.php
│   │   │   ├── orders/ (index, show)
│   │   │   ├── wishlist/index.blade.php
│   │   │   ├── profile/ (edit, addresses)
│   │   │   └── notifications/index.blade.php
│   │   ├── auth/ (login, register, forgot-password)
│   │   └── components/
│   │       ├── product-card.blade.php
│   │       ├── pagination.blade.php
│   │       ├── alert.blade.php
│   │       └── breadcrumb.blade.php
│   └── js/app.js
├── routes/
│   ├── web.php
│   └── admin.php
└── tests/
```

---

## 5. ELOQUENT MODELS — RELATIONSHIPS

### User
```php
hasMany(UserAddress::class)
hasOne(Cart::class)
hasMany(Order::class)
hasMany(Review::class)
hasMany(Notification::class)
belongsToMany(Product::class, 'wishlists')
```

### Product
```php
belongsTo(Brand::class)
belongsTo(Category::class)
hasMany(ProductVariant::class)
hasMany(Review::class)
belongsToMany(User::class, 'wishlists')
// Scopes: scopeActive(), scopeSearch($query, $keyword)
```

### ProductVariant
```php
belongsTo(Product::class)
hasMany(VariantImage::class)
hasMany(VariantAttribute::class)
belongsToMany(Warehouse::class, 'inventory')->withPivot('quantity')
hasMany(CartItem::class)
hasMany(OrderItem::class)
// Accessor: getPrimaryImageAttribute()
// Scope: scopeActive(), scopeInStock()
```

### Order
```php
belongsTo(User::class)
belongsTo(UserAddress::class, 'address_id')
belongsTo(Coupon::class)
hasMany(OrderItem::class)
hasMany(Payment::class)
hasMany(OrderStatusHistory::class)
```

### Cart
```php
belongsTo(User::class)
hasMany(CartItem::class)
// Accessor: getTotalAttribute(), getItemCountAttribute()
```

### Category
```php
belongsTo(Category::class, 'parent_id')   // cha
hasMany(Category::class, 'parent_id')     // con
hasMany(Product::class)
```

---

## 6. ROUTES

### routes/web.php (Public + Customer)
```
GET  /                              HomeController@index
GET  /san-pham                      Customer\ProductController@index
GET  /san-pham/{slug}               Customer\ProductController@show
GET  /thuong-hieu/{slug}            Customer\ProductController@byBrand
GET  /danh-muc/{slug}               Customer\ProductController@byCategory

// Auth
GET|POST /login
GET|POST /register
POST     /logout
GET|POST /quen-mat-khau

// Cart (guest + user)
GET    /gio-hang                    CartController@index
POST   /gio-hang/them               CartController@add
PUT    /gio-hang/{id}               CartController@update
DELETE /gio-hang/{id}               CartController@remove

// Authenticated
GET  /checkout                      CheckoutController@index
POST /checkout                      CheckoutController@store
POST /checkout/kiem-tra-coupon      CheckoutController@validateCoupon  (AJAX)

GET    /don-hang                    Customer\OrderController@index
GET    /don-hang/{id}               Customer\OrderController@show
POST   /don-hang/{id}/huy           Customer\OrderController@cancel

POST   /yeu-thich/{productId}       WishlistController@toggle
GET    /yeu-thich                   WishlistController@index

POST   /san-pham/{id}/danh-gia      Customer\ReviewController@store

GET    /tai-khoan                   ProfileController@edit
PUT    /tai-khoan                   ProfileController@update
GET    /tai-khoan/dia-chi           AddressController@index
POST   /tai-khoan/dia-chi           AddressController@store
PUT    /tai-khoan/dia-chi/{id}      AddressController@update
DELETE /tai-khoan/dia-chi/{id}      AddressController@destroy
POST   /tai-khoan/dia-chi/{id}/mac-dinh  AddressController@setDefault

GET    /thong-bao                   NotificationController@index
POST   /thong-bao/doc-het           NotificationController@markAllRead
POST   /thong-bao/{id}/doc          NotificationController@markRead
```

### routes/admin.php (prefix: /admin, middleware: admin)
```
GET  /admin                         Admin\DashboardController@index

// Sản phẩm
GET|POST         /admin/san-pham                    index + create + store
GET|PUT|DELETE   /admin/san-pham/{id}               show + edit + update + destroy
GET|POST         /admin/san-pham/{id}/bien-the       VariantController@index + store
PUT|DELETE       /admin/bien-the/{id}               VariantController@update + destroy
POST             /admin/bien-the/{id}/anh           VariantController@uploadImages

// Danh mục & Thương hiệu (Resource CRUD)
Resource /admin/danh-muc            Admin\CategoryController
Resource /admin/thuong-hieu         Admin\BrandController
Resource /admin/nha-cung-cap        Admin\SupplierController

// Đơn hàng
GET  /admin/don-hang                Admin\OrderController@index
GET  /admin/don-hang/{id}           Admin\OrderController@show
PUT  /admin/don-hang/{id}/trang-thai    Admin\OrderController@updateStatus
GET  /admin/don-hang/{id}/hoa-don  Admin\OrderController@printInvoice  (PDF)

// Kho hàng
GET  /admin/kho-hang                InventoryController@index
POST /admin/kho-hang/nhap           InventoryController@import
GET  /admin/kho-hang/log            InventoryController@logs

// Coupon
Resource /admin/coupon              Admin\CouponController

// Đánh giá
GET    /admin/danh-gia              Admin\ReviewController@index
PUT    /admin/danh-gia/{id}/duyet   Admin\ReviewController@approve
DELETE /admin/danh-gia/{id}         Admin\ReviewController@destroy

// Người dùng
GET  /admin/nguoi-dung              Admin\UserController@index
GET  /admin/nguoi-dung/{id}         Admin\UserController@show
PUT  /admin/nguoi-dung/{id}/trang-thai  Admin\UserController@toggleActive
```

---

## 7. SERVICES — BUSINESS LOGIC

### CartService
```
getOrCreateCart(Request $request): Cart
  → Nếu đã login: lấy cart theo user_id
  → Nếu guest: lấy cart theo session_id, tạo mới nếu chưa có

addItem(Cart $cart, int $variantId, int $qty): void
  → Kiểm tra variant tồn tại + is_active
  → Kiểm tra tồn kho đủ không
  → Nếu đã có trong giỏ: tăng quantity, else tạo mới
  → Throw exception nếu vượt tồn kho

updateItem(CartItem $item, int $qty): void
removeItem(CartItem $item): void

mergeGuestCart(Cart $guestCart, Cart $userCart): void
  → Gộp items, xoá guest cart sau khi merge (gọi khi user login)

getTotal(Cart $cart): float
validateStock(Cart $cart): array  → ['ok' => bool, 'errors' => [...]]
```

### OrderService
```
createFromCart(Cart $cart, array $data): Order
  Tham số $data: [address_id, coupon_code, payment_method, note]
  Các bước:
  1. validateStock() — throw nếu thiếu hàng
  2. Tính subtotal = sum(variant.price × qty)
  3. CouponService::validate() nếu có coupon
  4. Tính discount_amount, shipping_fee, total_price
  5. Snapshot địa chỉ từ user_addresses → orders
  6. Tạo Order record
  7. Tạo OrderItem records (snapshot sku, name, price)
  8. InventoryService::deduct() cho từng item
  9. Tạo CouponUsage nếu có coupon, tăng coupons.used_count
  10. Tạo Payment record (status PENDING)
  11. Tạo OrderStatusHistory (null → PENDING)
  12. Xoá cart_items + cart
  13. Fire event OrderPlaced

updateStatus(Order $order, string $newStatus, int $adminId, ?string $note): void
  → Validate transition hợp lệ (xem bảng transition bên dưới)
  → Cập nhật orders.status
  → Tạo OrderStatusHistory record
  → Fire event OrderStatusChanged

cancelOrder(Order $order, string $reason, int $userId): void
  → Chỉ cancel được khi PENDING hoặc CONFIRMED
  → Hoàn kho (InventoryService::import ADJUST)
  → Nếu payment_status = PAID → tạo refund record
  → Cập nhật status = CANCELLED, lưu cancelled_reason
```

**Bảng transition trạng thái đơn hàng:**
```
PENDING    → CONFIRMED | CANCELLED
CONFIRMED  → SHIPPING  | CANCELLED
SHIPPING   → COMPLETED
COMPLETED  → (không đổi được)
CANCELLED  → (không đổi được)
```

### CouponService
```
validate(string $code, int $userId, float $subtotal): array
  Trả về: ['valid' => bool, 'coupon' => Coupon|null, 'message' => string]
  Kiểm tra:
  1. Tìm coupon theo code, is_active = true
  2. Thời gian: start_at <= now <= expires_at
  3. used_count < max_uses (nếu max_uses không null)
  4. Số lần user dùng < max_uses_per_user
  5. subtotal >= min_order_value

calculate(Coupon $coupon, float $subtotal): float
  → percent: min(subtotal × value/100, max_discount_amount ?? PHP_FLOAT_MAX)
  → fixed:   min(value, subtotal)
```

### InventoryService
```
getStock(int $variantId): int
  → Tổng quantity tất cả kho của variant đó

deduct(int $variantId, int $qty, int $orderId): void
  → Trừ kho, tạo inventory_log (EXPORT, reference_type='order')

import(int $variantId, int $warehouseId, int $qty, string $note, int $adminId): void
  → Cộng kho, tạo inventory_log (IMPORT)

adjust(int $variantId, int $warehouseId, int $newQty, string $note, int $adminId): void
  → Set quantity mới, tạo inventory_log (ADJUST)
```

### NotificationService
```
send(int $userId, string $type, string $title, string $body, array $data = []): void
markRead(int $notificationId): void
markAllRead(int $userId): void
getUnreadCount(int $userId): int
```

---

## 8. TÍNH NĂNG — CHECKLIST ĐẦY ĐỦ

### Auth & Tài khoản
- [ ] Đăng ký, đăng nhập, đăng xuất
- [ ] Quên mật khẩu + reset qua email
- [ ] Xác thực email (`email_verified_at`)
- [ ] Middleware `AdminMiddleware` chặn non-admin vào `/admin`
- [ ] Trang cá nhân: sửa tên, số điện thoại, đổi avatar (upload + resize bằng Intervention Image)
- [ ] Quản lý địa chỉ: thêm / sửa / xoá / đặt mặc định

### Trang khách hàng
- [ ] Trang chủ: banner hero, sản phẩm nổi bật, danh sách thương hiệu
- [ ] Danh sách sản phẩm: lọc (hãng, danh mục, khoảng giá, thuộc tính), sắp xếp (giá, mới nhất), phân trang
- [ ] Chi tiết sản phẩm:
  - [ ] Chọn variant động (Alpine.js): chọn màu/dung lượng → cập nhật giá + ảnh + tồn kho
  - [ ] Thêm vào giỏ hàng
  - [ ] Toggle wishlist (AJAX)
  - [ ] Hiển thị đánh giá (rating trung bình + danh sách)
- [ ] Giỏ hàng: thêm / cập nhật số lượng / xoá / tính tổng tiền
- [ ] Checkout:
  - [ ] Chọn địa chỉ đã lưu hoặc nhập địa chỉ mới
  - [ ] Nhập mã giảm giá (AJAX validate realtime)
  - [ ] Chọn phương thức thanh toán (COD mặc định)
  - [ ] Hiển thị tóm tắt đơn hàng + tổng cộng
  - [ ] Đặt hàng → redirect trang cảm ơn
- [ ] Lịch sử đơn hàng: danh sách, lọc theo trạng thái
- [ ] Chi tiết đơn hàng: timeline trạng thái, danh sách sản phẩm, thông tin thanh toán
- [ ] Huỷ đơn hàng (chỉ PENDING/CONFIRMED)
- [ ] Wishlist: xem + xoá
- [ ] Đánh giá sản phẩm: chỉ sau khi mua, rating 1-5 sao, upload ảnh, mỗi user 1 review/sản phẩm
- [ ] Thông báo: danh sách, đánh dấu đã đọc, badge số thông báo chưa đọc

### Admin Panel
- [ ] Dashboard: tổng doanh thu, tổng đơn hàng, đơn hàng theo trạng thái, sản phẩm sắp hết hàng, biểu đồ doanh thu theo ngày/tháng
- [ ] Quản lý danh mục: CRUD, upload ảnh, sắp xếp thứ tự, phân cấp cha/con
- [ ] Quản lý thương hiệu: CRUD, upload logo
- [ ] Quản lý sản phẩm:
  - [ ] Danh sách + tìm kiếm + lọc theo danh mục/thương hiệu/trạng thái
  - [ ] Tạo sản phẩm: thông tin cơ bản, chọn danh mục, thương hiệu
  - [ ] Quản lý variants: thêm/sửa/xoá từng SKU (giá, thuộc tính, tồn kho ban đầu)
  - [ ] Upload ảnh variant: chọn ảnh chính, sắp xếp
  - [ ] Soft delete sản phẩm
- [ ] Quản lý kho:
  - [ ] Xem tồn kho tổng hợp theo variant × warehouse
  - [ ] Nhập hàng (IMPORT): chọn variant, kho, số lượng, ghi chú
  - [ ] Xem lịch sử inventory_logs: lọc theo variant/kho/loại
- [ ] Quản lý đơn hàng:
  - [ ] Danh sách + lọc theo trạng thái, ngày, tìm kiếm theo mã đơn
  - [ ] Chi tiết đơn: thông tin khách, địa chỉ, sản phẩm, timeline trạng thái, thông tin thanh toán
  - [ ] Cập nhật trạng thái (theo đúng luồng transition)
  - [ ] Huỷ đơn + nhập lý do
  - [ ] In hoá đơn PDF (dompdf)
- [ ] Quản lý coupon: CRUD, xem lượt đã dùng
- [ ] Quản lý đánh giá: danh sách chờ duyệt, duyệt/từ chối/xoá
- [ ] Quản lý người dùng: danh sách, xem chi tiết, khoá/mở tài khoản

### Hệ thống & Kỹ thuật
- [ ] Seeder: admin mặc định (email: admin@phonestore.vn / pass: password), kho mặc định, danh mục mẫu, thương hiệu mẫu
- [ ] Observer `OrderObserver`: tự tạo `order_status_history` khi `orders.status` thay đổi
- [ ] Event `OrderPlaced` → Listener `SendOrderConfirmationEmail`
- [ ] Event `OrderStatusChanged` → Listener `SendStatusNotification` (tạo notification + gửi email)
- [ ] Merge guest cart → user cart sau khi đăng nhập (LoginController)
- [ ] Policy `OrderPolicy`: customer chỉ xem/huỷ đơn của chính mình
- [ ] Policy `ReviewPolicy`: chỉ review sản phẩm đã mua
- [ ] Eager loading tránh N+1 query (with() ở tất cả các index)
- [ ] Form Request validation đầy đủ cho tất cả form

---

## 9. QUY TẮC CODE BẮT BUỘC

**Controllers — CHỈ làm 3 việc:**
```php
// 1. Nhận request (validate qua Form Request)
// 2. Gọi Service
// 3. Trả về response / redirect
public function store(CheckoutRequest $request)
{
    $order = $this->orderService->createFromCart(
        cart: CartService::getOrCreateCart($request),
        data: $request->validated()
    );
    return redirect()->route('orders.show', $order)->with('success', 'Đặt hàng thành công!');
}
```

**Không viết logic nghiệp vụ trong Controller.**
**Không viết query trực tiếp trong Controller — dùng Model scope hoặc Service.**

**Snapshot dữ liệu đơn hàng:**
- `order_items`: luôn lưu `sku`, `name`, `price` tại thời điểm mua → không join ngược về `product_variants` để lấy tên/giá
- `orders`: luôn lưu `shipping_name`, `shipping_phone`, `shipping_address` từ `user_addresses`

**Inventory:**
- Mọi thay đổi tồn kho phải qua `InventoryService` — tuyệt đối không update bảng `inventory` trực tiếp

**Coupon:**
- Luôn validate qua `CouponService::validate()` trước khi apply
- Chỉ tăng `used_count` và tạo `coupon_usages` sau khi order được tạo thành công (trong transaction)

**Database transaction:**
```php
// OrderService::createFromCart phải wrap trong DB::transaction()
DB::transaction(function () use (...) {
    // tạo order, items, trừ kho, coupon usage, payment...
});
```

---

## 10. THỨ TỰ TRIỂN KHAI

```
── Giai đoạn 1: Nền tảng ──────────────────────────────────────
  1. Cấu hình Laravel (.env, routes, middleware, service provider)
  2. Chạy phone_store_v2_updated.sql trong MySQL Workbench
  3. Tạo toàn bộ 24 Models + relationships + casts
  4. Seeders cơ bản (admin, warehouse, category, brand)
  5. Auth (đăng ký, đăng nhập, đăng xuất, quên mật khẩu)

── Giai đoạn 2: Admin CRUD ─────────────────────────────────────
  6. Layout admin (sidebar, topbar Tailwind)
  7. CRUD danh mục (kể cả cha/con)
  8. CRUD thương hiệu + upload logo
  9. CRUD sản phẩm + variants + upload ảnh
  10. Quản lý kho (nhập hàng, xem log)
  11. CRUD coupon

── Giai đoạn 3: Luồng mua hàng ─────────────────────────────────
  12. Layout customer (header, footer, menu danh mục)
  13. Trang chủ + danh sách sản phẩm + lọc
  14. Trang chi tiết sản phẩm + chọn variant (Alpine.js)
  15. Giỏ hàng (guest + user)
  16. Merge cart khi đăng nhập
  17. Checkout (địa chỉ, coupon AJAX, COD)
  18. Lịch sử đơn hàng + chi tiết + huỷ đơn

── Giai đoạn 4: Tính năng bổ sung ──────────────────────────────
  19. Wishlist
  20. Đánh giá sản phẩm + upload ảnh review
  21. Thông báo (bell icon + badge)
  22. Quản lý profile + địa chỉ giao hàng

── Giai đoạn 5: Admin nâng cao ─────────────────────────────────
  23. Dashboard thống kê + biểu đồ (Chart.js)
  24. Quản lý đơn hàng đầy đủ (timeline + cập nhật trạng thái)
  25. In hoá đơn PDF (dompdf)
  26. Quản lý đánh giá + người dùng

── Giai đoạn 6: Hoàn thiện ─────────────────────────────────────
  27. Gửi email (xác nhận đơn, đổi trạng thái) qua Queue
  28. Tối ưu query (eager loading, cache thống kê dashboard)
  29. Tích hợp thanh toán online (MOMO/VNPAY nếu cần)
  30. Viết Tests (Feature tests cho luồng checkout chính)
```

---

## 11. LƯU Ý ĐẶC BIỆT

> **KHÔNG dùng migrations** — Database đã khởi tạo bằng file SQL chạy trực tiếp trong MySQL Workbench 8.0.

**Variant & Attribute (phần phức tạp nhất):**
- Mỗi variant = 1 tổ hợp attribute_values (ví dụ: Đen + 128GB)
- Frontend cần Alpine.js để khi user chọn thuộc tính → tìm đúng variant_id → cập nhật giá/ảnh/tồn kho
- Lưu toàn bộ danh sách variants + attributes dưới dạng JSON trong thẻ `<script>` trên trang chi tiết sản phẩm

**Guest Cart:**
- Dùng `session()->getId()` làm `session_id`
- Khi user đăng nhập → gọi `CartService::mergeGuestCart()` trong `LoginController::authenticated()`

**Công thức tính tiền đơn hàng:**
```
subtotal      = sum(variant.price × quantity)   // tổng hàng chưa giảm
discount      = CouponService::calculate()       // 0 nếu không có coupon
shipping_fee  = (cố định hoặc theo vùng)
total_price   = subtotal - discount + shipping_fee
```

**Coupon percent có trần:**
```
discount = min(subtotal × percent/100, max_discount_amount)
```

**Soft delete Product:**
- Khi xoá product → variants CASCADE xoá theo
- `order_items.variant_id` sẽ SET NULL nhưng không mất dữ liệu vì đã snapshot `sku` + `name`

**Review:**
- UNIQUE (user_id, product_id): mỗi user chỉ review 1 lần/sản phẩm
- `order_item_id` phải hợp lệ (thuộc đơn hàng COMPLETED của chính user đó)
- `is_approved = false` mặc định, admin phải duyệt mới hiển thị
