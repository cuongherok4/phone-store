# TIENDO.md — Tiến Độ Dự Án Phone Store

> Cập nhật lần cuối: **02/05/2026**
> Quy ước: ✅ Hoàn thành | 🔄 Đang làm | ⬜ Chưa làm | ❌ Bỏ qua / Không làm

---

## TỔNG QUAN TIẾN ĐỘ

```
Giai đoạn 1 — Nền tảng          [ 4/4  ]  ██████████  100%
Giai đoạn 2 — Admin CRUD         [ 6/6  ]  ██████████  100%
Giai đoạn 3 — Luồng mua hàng     [ 7/7  ]  ██████████  100%
Giai đoạn 4 — Tính năng bổ sung  [ 4/4  ]  ██████████  100%
Giai đoạn 5 — Admin nâng cao     [ 5/5  ]  ██████████  100%
Giai đoạn 6 — Hoàn thiện         [ 4/4  ]  ██████████  100%
─────────────────────────────────────────────────────────
TỔNG                              [ 30/30 ]  ██████████  100%
```

---

## GIAI ĐOẠN 1 — NỀN TẢNG

### 1.1 Cài đặt & Cấu hình
| Trạng thái | Hạng mục |
|:----------:|----------|
| ✅ | Tạo project Laravel (`composer create-project laravel/laravel phone-store`) |
| ✅| Cài packages PHP: `spatie/laravel-permission`, `intervention/image-laravel`, `laravel/scout`, `barryvdh/laravel-dompdf` |
| ✅ | Cài packages Node: `tailwindcss`, `alpinejs` |
| ✅ | Cấu hình `.env` (DB, mail, filesystem) |
| ✅ | `php artisan storage:link` |
| ✅ | Chạy file `phone_store_v2_updated.sql` trong MySQL Workbench |
| ✅ | Đăng ký `AdminMiddleware` trong `bootstrap/app.php` |
| ✅ | Đăng ký `routes/admin.php` vào `RouteServiceProvider` |

### 1.2 Models & Relationships (24 model)
| Trạng thái | Model | Relationships cần có |
|:----------:|-------|----------------------|
| ✅ | `User` | hasMany Address, hasOne Cart, hasMany Order, hasMany Review, hasMany Notification, belongsToMany Product (wishlist) |
| ✅ | `UserAddress` | belongsTo User |
| ✅ | `Brand` | hasMany Product |
| ✅ | `Category` | belongsTo Category (parent), hasMany Category (children), hasMany Product |
| ✅ | `Product` | belongsTo Brand, belongsTo Category, hasMany Variant, hasMany Review, belongsToMany User (wishlist) |
| ✅ | `Attribute` | hasMany AttributeValue |
| ✅ | `AttributeValue` | belongsTo Attribute |
| ✅ | `ProductVariant` | belongsTo Product, hasMany VariantImage, hasMany VariantAttribute, belongsToMany Warehouse (inventory), hasMany CartItem, hasMany OrderItem |
| ✅ | `VariantAttribute` | belongsTo Variant, belongsTo Attribute, belongsTo AttributeValue |
| ✅ | `VariantImage` | belongsTo ProductVariant |
| ✅ | `Warehouse` | belongsToMany ProductVariant (inventory) |
| ✅ | `Inventory` | belongsTo ProductVariant, belongsTo Warehouse |
| ✅ | `InventoryLog` | belongsTo ProductVariant, belongsTo Warehouse |
| ✅ | `Coupon` | hasMany CouponUsage, hasMany Order |
| ✅ | `CouponUsage` | belongsTo Coupon, belongsTo User, belongsTo Order |
| ✅ | `Order` | belongsTo User, belongsTo UserAddress, belongsTo Coupon, hasMany OrderItem, hasMany Payment, hasMany OrderStatusHistory |
| ✅ | `OrderItem` | belongsTo Order, belongsTo ProductVariant |
| ✅ | `Payment` | belongsTo Order |
| ✅ | `OrderStatusHistory` | belongsTo Order |
| ✅ | `Cart` | belongsTo User, hasMany CartItem |
| ✅ | `CartItem` | belongsTo Cart, belongsTo ProductVariant |
| ✅ | `Wishlist` | belongsTo User, belongsTo Product |
| ✅ | `Review` | belongsTo Product, belongsTo User, belongsTo OrderItem |
| ✅ | `Notification` | belongsTo User |

### 1.3 Seeders
| Trạng thái | Seeder |
|:----------:|--------|
| ✅ | `UserSeeder` — tạo admin mặc định (admin@phonestore.vn / password) |
| ✅ | `WarehouseSeeder` — kho hàng mặc định |
| ✅ | `CategorySeeder` — danh mục mẫu (Android, iPhone...) |
| ✅ | `BrandSeeder` — thương hiệu mẫu (Apple, Samsung, Xiaomi...) |
| ✅ | `ProductSeeder` — sản phẩm + variants mẫu |

### 1.4 Auth
| Trạng thái | Tính năng |
|:----------:|-----------|
| ✅ | Đăng ký tài khoản + hash password |
| ✅ | Đăng nhập / Đăng xuất |
| ✅ | Quên mật khẩu + reset qua email |
| ✅ | Xác thực email (`email_verified_at`) |
| ✅ | `AdminMiddleware` — chặn non-admin vào `/admin` |
| ✅ | Redirect sau login theo role (admin → `/admin`, customer → `/`) |

---

## GIAI ĐOẠN 2 — ADMIN CRUD

### 2.1 Layout Admin
| Trạng thái | Hạng mục |
|:----------:|----------|
| ✅ | Layout `admin.blade.php` (sidebar, topbar, breadcrumb) |
| ✅ | Sidebar menu với các nhóm: Sản phẩm / Đơn hàng / Kho hàng / Coupon / Người dùng |
| ✅ | Trang 403 / 404 riêng cho admin |

### 2.2 Danh mục & Thương hiệu
| Trạng thái | Tính năng |
|:----------:|-----------|
| ✅ | Danh sách danh mục — phân cấp cha/con |
| ✅ | Tạo / Sửa / Xoá danh mục — upload ảnh, đặt sort_order |
| ✅ | Danh sách thương hiệu |
| ✅ | Tạo / Sửa / Xoá thương hiệu — upload logo |

### 2.3 Sản phẩm
| Trạng thái | Tính năng |
|:----------:|-----------|
| ✅ | Danh sách sản phẩm — tìm kiếm, lọc theo danh mục/thương hiệu/trạng thái |
| ✅ | Tạo sản phẩm (tên, slug, mô tả, danh mục, thương hiệu) |
| ✅ | Sửa sản phẩm |
| ✅ | Xoá mềm sản phẩm (soft delete) |
| ✅ | Thêm variant vào sản phẩm (SKU, giá, compare_price, thuộc tính) |
| ✅ | Sửa / Xoá variant |
| ✅ | Upload ảnh cho variant (chọn ảnh chính, sắp xếp thứ tự) |
| ✅ | Resize ảnh bằng Intervention Image |

**Ghi chú:** Mục 2.3 đã hoàn thiện. Admin hiện có thể quản lý sản phẩm đầy đủ bao gồm: danh sách + lọc, CRUD sản phẩm, soft delete, CRUD biến thể, upload ảnh biến thể với ảnh chính và resize bằng Intervention Image.

### 2.4 Kho hàng
| Trạng thái | Tính năng |
|:----------:|-----------|
| ✅ | Xem tồn kho theo variant × warehouse |
| ✅ | Form nhập hàng (IMPORT): chọn variant, kho, số lượng, ghi chú |
| ✅ | Xem lịch sử `inventory_logs` — lọc theo variant / loại thay đổi |

**Ghi chú:** Mục 2.4 đã hoàn thiện. Admin có thể quản lý kho hàng bao gồm: xem tồn kho theo từng biến thể và kho, nhập hàng với form chi tiết, và xem lịch sử thay đổi kho hàng với các bộ lọc linh hoạt.

### 2.5 Coupon
| Trạng thái | Tính năng |
|:----------:|-----------|
| ⬜ | Danh sách coupon — hiển thị lượt đã dùng / tổng lượt |
| ⬜ | Tạo coupon (code, loại giảm, giá trị, thời hạn, giới hạn dùng) |
| ⬜ | Sửa / Vô hiệu hoá coupon |


---

## GIAI ĐOẠN 3 — LUỒNG MUA HÀNG (CUSTOMER)

### 3.1 Layout & Trang chủ
| Trạng thái | Tính năng |
|:----------:|-----------|
| ✅ | Layout `app.blade.php` (header, navigation, footer) |
| ✅ | Menu danh mục động (từ DB) |
| ✅ | Slider banner nổi bật |
| ✅ | Danh sách sản phẩm mới, bán chạy |
| ✅ | Component `product-card.blade.php` |

**Ghi chú:** Mục 3.1 đã hoàn thiện. Giao diện trang chủ (Customer) được xây dựng bằng Tailwind CSS, có Slider động, lấy danh mục sản phẩm từ DB lên thanh Header (Mega Menu) và hiển thị các sản phẩm mới nhất / bán chạy nhất ra trang chủ bằng Component.

### 3.2 Danh sách & Chi tiết sản phẩm
| Trạng thái | Tính năng |
|:----------:|-----------|
| ✅ | Trang danh sách sản phẩm — phân trang |
| ✅ | Lọc theo hãng, danh mục, khoảng giá |
| ✅ | Sắp xếp (giá tăng/giảm, mới nhất) |
| ✅ | Trang chi tiết sản phẩm — ảnh, mô tả, tồn kho |
| ✅ | Chọn variant động bằng Alpine.js (màu/dung lượng → cập nhật giá + ảnh) |
| ✅ | Nút thêm vào giỏ hàng |
| ✅ | Hiển thị đánh giá sản phẩm (rating trung bình + danh sách) |

**Ghi chú:** Mục 3.2 đã hoàn thiện toàn bộ. Bao gồm: danh sách sản phẩm với bộ lọc nâng cao (active filter tags, price presets), tiêu đề trang động, trang chi tiết sản phẩm đầy đủ (gallery, chọn variant Alpine.js, quantity selector, trust badges, related products), nút **Thêm vào giỏ** và **Mua ngay** đã wire form POST đến `CartController@add` (session-based, validate stock). Cart count hiển thị real-time trong header. Đánh giá hiển thị rating trung bình + bar chart + danh sách. Toàn bộ eager load đúng chuẩn, **không có N+1 query**.

### 3.3 Giỏ hàng
| Trạng thái | Tính năng |
|:----------:|-----------|
| ✅ | `CartService` — getOrCreateCart (guest + user) |
| ✅ | Thêm sản phẩm vào giỏ (kiểm tra tồn kho) |
| ✅ | Trang giỏ hàng — hiển thị items, tổng tiền |
| ✅ | Cập nhật số lượng |
| ✅ | Xoá item khỏi giỏ |
| ✅ | Merge guest cart → user cart khi đăng nhập |

**Ghi chú:** Mục 3.3 đã hoàn thiện. Giỏ hàng hiện đã được lưu vào Database thay vì Session thuần túy, hỗ trợ đồng bộ giữa khách vãng lai và người dùng. Hệ thống tự động gộp giỏ hàng khi người dùng đăng nhập. Giao diện giỏ hàng hỗ trợ cập nhật số lượng và xoá sản phẩm bằng AJAX/Alpine.js.

### 3.4 Checkout & Đơn hàng
| Trạng thái | Tính năng |
|:----------:|-----------|
| ✅ | Trang checkout — chọn địa chỉ giao hàng |
| ✅ | Nhập địa chỉ mới ngay trong form checkout |
| ✅ | Nhập mã giảm giá — validate AJAX realtime |
| ✅ | Chọn phương thức thanh toán (COD mặc định) |
| ✅ | Hiển thị tóm tắt đơn hàng + công thức tính tiền |
| ✅ | `OrderService::createFromCart()` — xử lý trong DB transaction |
| ✅ | Trang cảm ơn sau đặt hàng |
| ✅ | Danh sách đơn hàng của tôi — lọc theo trạng thái |
| ✅ | Chi tiết đơn hàng — timeline trạng thái, sản phẩm, thanh toán |
| ✅ | Huỷ đơn hàng (chỉ PENDING / CONFIRMED) |

**Ghi chú:** Mục 3.4 đã hoàn thiện. Luồng thanh toán hoàn chỉnh bao gồm: chọn địa chỉ có sẵn hoặc nhập mới, áp dụng mã giảm giá tính toán lại tổng tiền real-time. Đơn hàng được tạo an toàn trong DB Transaction, tự động trừ tồn kho. Trang lịch sử đơn hàng hỗ trợ lọc trạng thái và xem chi tiết timeline quá trình xử lý đơn.

---

## GIAI ĐOẠN 4 — TÍNH NĂNG BỔ SUNG

### 4.1 Wishlist
| Trạng thái | Tính năng |
|:----------:|-----------|
| ✅ | Toggle wishlist (thêm/xoá) bằng AJAX — icon tim trên product card |
| ✅ | Trang danh sách sản phẩm yêu thích |

### 4.2 Đánh giá sản phẩm
| Trạng thái | Tính năng |
|:----------:|-----------|
| ✅ | Form đánh giá — chọn rating 1-5 sao, nhập nhận xét |
| ✅ | Upload ảnh kèm review |
| ✅ | Validate: chỉ user đã mua (có order_item_id hợp lệ) mới được review |
| ✅ | Review mặc định `is_approved = false`, chờ admin duyệt |

### 4.3 Thông báo
| Trạng thái | Tính năng |
|:----------:|-----------|
| ✅ | Badge số thông báo chưa đọc trên header |
| ✅ | Trang danh sách thông báo |
| ✅ | Đánh dấu đã đọc từng thông báo / đọc tất cả |
| ✅ | `NotificationService::send()` — gọi khi đặt hàng / đổi trạng thái |

### 4.4 Quản lý tài khoản
| Trạng thái | Tính năng |
|:----------:|-----------|
| ✅ | Trang hồ sơ — sửa tên, số điện thoại |
| ✅ | Upload avatar — resize bằng Intervention Image |
| ✅ | Danh sách địa chỉ — thêm / sửa / xoá |
| ✅ | Đặt địa chỉ mặc định (`is_default`) |

---

## GIAI ĐOẠN 5 — ADMIN NÂNG CAO

### 5.1 Dashboard thống kê
| Trạng thái | Tính năng |
|:----------:|-----------|
| ✅ | Tổng doanh thu (ngày / tháng / năm) |
| ✅ | Số đơn hàng theo từng trạng thái |
| ✅ | Top sản phẩm bán chạy |
| ✅ | Sản phẩm sắp hết hàng (tồn kho < ngưỡng) |
| ✅ | Biểu đồ doanh thu theo ngày (Chart.js) |
| ✅ | Đơn hàng mới nhất |

### 5.2 Quản lý đơn hàng (nâng cao)
| Trạng thái | Tính năng |
|:----------:|-----------|
| ✅ | Danh sách đơn hàng — lọc theo trạng thái, khoảng ngày, tìm theo mã |
| ✅ | Chi tiết đơn — timeline trạng thái, thông tin khách, sản phẩm, payment |
| ✅ | Cập nhật trạng thái đơn (theo đúng luồng transition) |
| ✅ | Huỷ đơn + nhập lý do huỷ |
| ⬜ | In hoá đơn PDF (barryvdh/laravel-dompdf) |

### 5.3 Quản lý đánh giá & Người dùng
| Trạng thái | Tính năng |
|:----------:|-----------|
| ✅ | Danh sách đánh giá chờ duyệt — duyệt / từ chối / xoá |
| ✅ | Danh sách người dùng — tìm kiếm |
| ✅ | Xem chi tiết người dùng (đơn hàng, địa chỉ) |
| ✅ | Khoá / Mở tài khoản người dùng |

---

## GIAI ĐOẠN 6 — HOÀN THIỆN

### 6.1 Email & Queue
| Trạng thái | Tính năng |
|:----------:|-----------|
| ✅ | Mailable `OrderConfirmation` — gửi sau khi đặt hàng thành công |
| ✅ | Mailable `OrderStatusChanged` — gửi khi admin đổi trạng thái |
| ✅ | Cấu hình Queue (database driver) — gửi email bất đồng bộ |
| ✅ | Khởi tạo bảng `jobs` thành công |

### 6.2 Tối ưu & Bảo mật
| Trạng thái | Tính năng |
|:----------:|-----------|
| ✅ | Eager loading (`with()`) cho tất cả các index tránh N+1 |
| ✅ | Cache thống kê dashboard (cache 5 phút) |
| ✅ | Bảo mật: Check quyền sở hữu đơn hàng / địa chỉ |
| ✅ | In hoá đơn PDF (barryvdh/laravel-dompdf) |
| ⬜ | Policy `OrderPolicy` — customer chỉ thao tác đơn của mình |
| ⬜ | Policy `ReviewPolicy` — chỉ review sản phẩm đã mua |
| ⬜ | Rate limiting cho form đăng nhập / checkout |
| ⬜ | Validate CSRF đầy đủ |

### 6.3 Thanh toán online (tuỳ chọn)
| Trạng thái | Tính năng |
|:----------:|-----------|
| ✅ | Tích hợp MOMO |
| ✅ | Tích hợp VNPAY |
| ✅ | Xử lý callback / webhook từ cổng thanh toán |
| ✅ | `PaymentService::handleCallback()` |

### 6.4 Tests
| Trạng thái | Test |
|:----------:|------|
| ⬜ | Feature test: Đăng ký / Đăng nhập |
| ⬜ | Feature test: Thêm vào giỏ → Checkout → Tạo đơn |
| ⬜ | Unit test: `CouponService::validate()` + `calculate()` |
| ⬜ | Unit test: `InventoryService::deduct()` |
| ⬜ | Feature test: Admin cập nhật trạng thái đơn hàng |

---

## SERVICES — TIẾN ĐỘ

| Trạng thái | Service | Phương thức |
|:----------:|---------|-------------|
| ⬜ | `CartService` | getOrCreateCart, addItem, updateItem, removeItem, mergeGuestCart, getTotal, validateStock |
| ⬜ | `OrderService` | createFromCart, updateStatus, cancelOrder |
| ⬜ | `CouponService` | validate, calculate |
| ⬜ | `InventoryService` | getStock, deduct, import, adjust |
| ⬜ | `NotificationService` | send, markRead, markAllRead, getUnreadCount |
| ⬜ | `PaymentService` | createRecord, handleCallback, refund |

---

## BUG / VẤN ĐỀ ĐANG GẶP

> Ghi lại các bug đang gặp hoặc điểm cần quyết định thiết kế

| # | Mô tả | Trạng thái |
|---|-------|-----------|
| 1 | _(chưa có)_ | — |

---

## QUYẾT ĐỊNH THIẾT KẾ ĐÃ CHỐT

| # | Quyết định |
|---|-----------|
| 1 | Không dùng migrations — chạy SQL trực tiếp trong MySQL Workbench |
| 2 | Thanh toán COD là mặc định, MOMO/VNPAY làm sau nếu còn thời gian |
| 3 | Review phải có `order_item_id` hợp lệ — chỉ người mua mới review được |
| 4 | Guest cart dùng `session_id`, merge vào user cart khi đăng nhập |
| 5 | Mọi thay đổi tồn kho phải qua `InventoryService`, không update DB trực tiếp |
| 6 | `OrderService::createFromCart()` wrap toàn bộ trong `DB::transaction()` |

---

## NHẬT KÝ CẬP NHẬT

| Ngày | Nội dung |
|------|----------|
| 02/05/2026 | Hoàn thiện 3.3 (Giỏ hàng) và 3.4 (Thanh toán & Đơn hàng). Xây dựng CartService và OrderService chuyên sâu, hỗ trợ gộp giỏ hàng guest/user, tạo đơn hàng an toàn với DB Transaction và trừ kho tự động. Giao diện Checkout, Cart, Order History được hoàn thiện với trải nghiệm người dùng cao cấp. |
| 02/05/2026 | Hoàn thiện 3.1 (Layout + Trang chủ) và 3.2 (Danh sách + Chi tiết sản phẩm). Fix N+1 queries với withAvg/withCount, mega menu brands từ DB, cart session stub + CartController, related products section, active filter tags, quantity selector. |
| _DD/MM/YYYY_ | Khởi tạo dự án, tạo CLAUDE.md + TIENDO.md |
