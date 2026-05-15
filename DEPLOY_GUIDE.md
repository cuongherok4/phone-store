# Hướng Dẫn Deploy Chi Tiết Website Phone Store (Laravel 12)

Tài liệu này cung cấp lộ trình từng bước một để triển khai dự án lên một máy chủ VPS sạch (Ubuntu 22.04/24.04).

---

## Giai Đoạn 1: Thiết Lập Môi Trường (Dành cho Server mới)

Kết nối vào VPS của bạn qua Terminal: `ssh root@ip_cua_ban`

### 1.1 Cập nhật hệ thống
Đảm bảo các gói phần mềm ở phiên bản mới nhất:
```bash
sudo apt update && sudo apt upgrade -y
```

### 1.2 Cài đặt PHP 8.2 và các thư viện hỗ trợ
Laravel 12 yêu cầu tối thiểu PHP 8.2. Chúng ta sẽ dùng repository của Ondrej Surý:
```bash
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2-fpm php8.2-mysql php8.2-igbinary php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath php8.2-curl php8.2-gd php8.2-intl -y
```

### 1.3 Cài đặt Nginx & MySQL
```bash
sudo apt install nginx mysql-server -y
```

### 1.4 Cài đặt Composer (Trình quản lý thư viện PHP)
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

---

## Giai Đoạn 2: Đưa Mã Nguồn Lên Server

### 2.1 Cấu hình quyền thư mục web
```bash
sudo chown -R $USER:$USER /var/www
cd /var/www
```

### 2.2 Clone dự án từ GitHub
Thay link bằng link dự án của bạn:
```bash
git clone https://github.com/cuongherok4/phone-store.git
cd phone-store
```

### 2.3 Cài đặt thư viện PHP
```bash
composer install --no-dev --optimize-autoloader
```

### 2.4 Cài đặt Node.js và Build giao diện (Vite)
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
npm install
npm run build
```

---

## Giai Đoạn 3: Cấu Hình Cơ Sở Dữ Liệu

### 3.1 Tạo Database và Tài khoản truy cập
Truy cập vào MySQL bằng lệnh: `sudo mysql`
Sau đó chạy các lệnh SQL sau:
```sql
-- Tạo database
CREATE DATABASE phone_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tạo user mới và đặt mật khẩu
CREATE USER 'phone_user'@'localhost' IDENTIFIED BY 'MatKhauBaoMat123@';

-- Cấp quyền
GRANT ALL PRIVILEGES ON phone_store.* TO 'phone_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3.2 Cấu hình file .env
```bash
cp .env.example .env
nano .env
```
Dùng phím mũi tên để di chuyển và sửa các thông tin sau:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=http://yourdomain.com` (Thay bằng IP hoặc Domain của bạn)
- `DB_DATABASE=phone_store`
- `DB_USERNAME=phone_user`
- `DB_PASSWORD=MatKhauBaoMat123@`

*Nhấn `Ctrl + O`, `Enter` để lưu và `Ctrl + X` để thoát.*

### 3.3 Khởi tạo dữ liệu
```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
```

---

## Giai Đoạn 4: Cấu Hình Web Server (Nginx)

### 4.1 Tạo file cấu hình site
```bash
sudo nano /etc/nginx/sites-available/phone-store
```
Dán đoạn mã dưới đây vào (Nhớ sửa `yourdomain.com` thành IP hoặc tên miền của bạn):
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/phone-store/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 4.2 Kích hoạt cấu hình
```bash
sudo ln -s /etc/nginx/sites-available/phone-store /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## Giai Đoạn 5: Tối Ưu & Bảo Mật

### 5.1 Phân quyền thư mục (Rất quan trọng)
Để Laravel có thể ghi log và cache:
```bash
sudo chown -R www-data:www-data /var/www/phone-store/storage /var/www/phone-store/bootstrap/cache
sudo chmod -R 775 /var/www/phone-store/storage /var/www/phone-store/bootstrap/cache
```

### 5.2 Cài đặt SSL (HTTPS) - Miễn phí với Certbot
```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d yourdomain.com
```

### 5.3 Tăng tốc độ phản hồi (Cache)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---
**Lưu ý**: Nếu bạn thay đổi mã nguồn trên Git, chỉ cần vào server chạy:
`git pull` -> `npm run build` -> `php artisan migrate` -> `php artisan config:clear`.
