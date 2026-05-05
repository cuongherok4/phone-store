# Phone Store — Hướng dẫn Setup Laravel
D:\Project-khanh
## Yêu cầu môi trường
- PHP >= 8.2
- Composer >= 2.x
- MySQL workbench>= 8.0
- Node.js >= 18.x + npm

---

## Bước 1 — Tạo project Laravel

```bash
composer create-project laravel/laravel phone-store
cd phone-store
D:\Project-khanh\phone-store> 
```

## Bước 2 — Cài packages cần thiết

```bash
composer require spatie/laravel-permission
composer require intervention/image-laravel
composer require laravel/scout
composer require barryvdh/laravel-dompdf

npm install -D tailwindcss @tailwindcss/vite alpinejs
```

## Bước 3 — Cấu hình .env

```env
APP_NAME="Phone Store"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=phone_store_v2
DB_USERNAME=root
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=

FILESYSTEM_DISK=public
```
## Bước 4 — Cấu hình .env
```
thực hiện chạy code mysql trong mysql workbench 8.0 CE
```
## Bước 5 — Build assets & chạy server
```
## Cấu trúc thư mục dự án

phone-store/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          ← Controllers cho admin panel
│   │   │   └── Customer/       ← Controllers cho khách hàng
│   │   ├── Middleware/
│   │   └── Requests/           ← Form Requests (validation)
│   ├── Models/                 ← Tất cả Eloquent Models
│   └── Services/               ← Business logic (CartService, OrderService...)
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── views/
│   │   ├── admin/              ← Giao diện admin
│   │   ├── customer/           ← Giao diện khách
│   │   └── components/         ← Blade components tái sử dụng
│   └── js/
├── routes/
│   ├── web.php
│   └── admin.php               ← Routes riêng cho admin
└── tests/
```