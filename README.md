<h1 align="center">📱 PhoneStore</h1>

<p align="center">
  Hệ thống thương mại điện tử bán điện thoại — xây dựng bằng Laravel 12
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind">
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white" alt="Alpine.js">
</p>

---

## ✨ Tính Năng Nổi Bật

### 🛍️ Phía Khách Hàng
- **Danh mục & Tìm kiếm** — Lọc theo thương hiệu, khoảng giá, sắp xếp linh hoạt
- **Trang chi tiết sản phẩm** — Chọn variant (màu, RAM/ROM) bằng Alpine.js, cập nhật giá & ảnh realtime
- **Giỏ hàng thông minh** — Lưu DB, hỗ trợ guest, tự động merge khi đăng nhập
- **Checkout đầy đủ** — Chọn địa chỉ, mã giảm giá, nhiều phương thức thanh toán
- **Thanh toán online** — Tích hợp **VNPAY** & **MoMo**, xử lý callback & IPN
- **Wishlist** — Yêu thích sản phẩm, toggle bằng AJAX
- **Đánh giá** — Chỉ người đã mua mới được review, hỗ trợ upload ảnh
- **Tài khoản** — Quản lý hồ sơ, avatar, địa chỉ giao hàng, lịch sử đơn hàng
- **Social Login** — Đăng nhập bằng Google (Socialite)
- **AI Tư Vấn** — AIController tư vấn sản phẩm phù hợp

### ⚙️ Phía Quản Trị (Admin)
- **Dashboard** — Biểu đồ doanh thu (Chart.js), thống kê đơn hàng, sản phẩm sắp hết hàng
- **Quản lý sản phẩm** — CRUD đầy đủ, biến thể (variant), upload & resize ảnh
- **Kho hàng** — Multi-warehouse, nhập hàng, lịch sử thay đổi tồn kho
- **Đơn hàng** — Cập nhật trạng thái, huỷ đơn, in hóa đơn PDF
- **Coupon** — Tạo mã giảm giá theo % hoặc giá trị cố định
- **Báo cáo** — Export Excel đơn hàng & tồn kho
- **Duyệt đánh giá** — Approve/reject, quản lý người dùng

---

## 🏗️ Kiến Trúc Hệ Thống

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/        # 13 controllers quản trị
│   │   ├── Customer/     # 10 controllers khách hàng
│   │   └── Auth/         # Login, Register, Social
│   ├── Middleware/
│   │   └── AdminMiddleware.php
│   └── Requests/
│       └── Admin/        # Form Requests có validation
├── Models/               # 26 Eloquent Models
├── Services/             # Business Logic Layer
│   ├── OrderService.php       # Tạo đơn, xác nhận thanh toán, hủy đơn
│   ├── InventoryService.php   # Nhập/xuất/hoàn kho (multi-warehouse)
│   ├── CartService.php        # Giỏ hàng (DB-based)
│   ├── PaymentService.php     # VNPAY + MoMo integration
│   ├── ReviewService.php
│   ├── WishlistService.php
│   ├── NotificationService.php
│   └── Admin/
│       └── StatisticsService.php
├── Mail/                 # Mailable classes (OrderConfirmation...)
└── Exports/              # Excel export (Maatwebsite)

routes/
├── web.php               # Customer + Auth routes
└── admin.php             # Admin routes (prefix: /admin)
```

---

## 🗄️ Database

**26 bảng chính:**

```
users, user_addresses
products, product_variants, variant_images, variant_attributes
brands, attributes, attribute_values
orders, order_items, order_status_histories
carts, cart_items
inventory, inventory_logs, warehouses, suppliers
payments, coupons, coupon_usages
reviews, wishlists, notifications
banners, settings
```

---

## 🛠️ Tech Stack

| Thành phần | Công nghệ |
|---|---|
| Backend | Laravel 12, PHP 8.2+ |
| Database | MySQL 8.0 |
| Frontend | Tailwind CSS 3, Alpine.js 3, Vite |
| Auth | Laravel Auth + Socialite (Google) |
| Image | Intervention Image |
| PDF | barryvdh/laravel-dompdf |
| Excel | maatwebsite/excel |
| Search | Laravel Scout |
| Permission | spatie/laravel-permission |
| Payment | VNPAY, MoMo |
| Queue | Database driver |
| AI | AIController (custom) |

---

## 🚀 Cài Đặt & Chạy

### Yêu Cầu Hệ Thống
- PHP >= 8.2
- Composer
- MySQL 8.0+
- Node.js & NPM

### Các Bước Cài Đặt

**1. Clone project**
```bash
git clone https://github.com/cuongherok4/phone-store.git
cd phone-store
```

**2. Cài dependencies**
```bash
composer install
npm install
```

**3. Cấu hình môi trường**
```bash
cp .env.example .env
php artisan key:generate
```

Mở `.env` và cấu hình:
```env
APP_NAME=PhoneStore
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=phone_store
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=

# Google OAuth (tùy chọn)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

**4. Thiết lập database**

> **Cách A — Import SQL (Có sẵn dữ liệu mẫu):**
```bash
# Tạo database 'phone_store' trong MySQL, sau đó:
mysql -u root -p phone_store < docs/phone_store_v2_updated.sql
```

> **Cách B — Migration:**
```bash
php artisan migrate --seed
```

**5. Tạo storage link**
```bash
php artisan storage:link
```

**6. Chạy project**
```bash
# Chạy tất cả cùng lúc (server + queue + vite + logs)
composer dev

# Hoặc riêng lẻ:
php artisan serve
npm run dev
php artisan queue:listen
```

Truy cập: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 👤 Tài Khoản Mặc Định

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@phonestore.vn` | `password` |
| Customer | _(Tự đăng ký)_ | — |

> **Admin Panel:** [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin)

---

## 💳 Thanh Toán

| Phương thức | Trạng thái | Ghi chú |
|---|:---:|---|
| COD (Tiền mặt) | ✅ | Mặc định |
| VNPAY | ✅ | Cấu hình trong Admin > Cài đặt |
| MoMo | ✅ | Cấu hình trong `config/payment.php` |

Để kích hoạt VNPAY/MoMo, vào **Admin > Cài đặt hệ thống** và điền credentials từ sandbox/production.

---

## 🧪 Testing

```bash
# Chạy tất cả tests
composer test

# Chạy với coverage report
php artisan test --coverage

# Chạy riêng từng file
php artisan test tests/Unit/Services/OrderServiceTest.php
```

---

## 📂 Tài Liệu

| File | Nội dung |
|------|----------|
| [docs/TIENDO.md](docs/TIENDO.md) | Tiến độ dự án chi tiết |
| [docs/OPTIMIZATION.md](docs/OPTIMIZATION.md) | Kế hoạch tối ưu hệ thống |
| [docs/GIT_WORKFLOW.md](docs/GIT_WORKFLOW.md) | Git Flow & Commit Convention |
| [docs/setup.md](docs/setup.md) | Hướng dẫn cài đặt chi tiết |

---

## 🤝 Đóng Góp

1. Fork repository
2. Tạo feature branch: `git checkout -b feature/ten-tinh-nang`
3. Commit theo convention: `git commit -m "feat(scope): mô tả"`
4. Push: `git push origin feature/ten-tinh-nang`
5. Mở Pull Request vào nhánh `develop`

Xem chi tiết tại [docs/GIT_WORKFLOW.md](docs/GIT_WORKFLOW.md).

---

## 📄 License

Project này được phát triển cho mục đích học tập.
