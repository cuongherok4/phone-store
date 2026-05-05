# Hướng dẫn Setup Dự án PhoneStore (Laravel) 🚀

Tài liệu này hướng dẫn bạn cách đẩy dự án lên Git và thiết lập lại dự án trên một máy tính mới một cách chi tiết.

---

## Phần 1: Đẩy dự án lên Git (Từ máy hiện tại)

Nếu bạn chưa có Repository trên GitHub/GitLab, hãy tạo một repo mới trước.

1. **Khởi tạo Git (nếu chưa có):**
   ```bash
   git init
   ```

2. **Thêm các file vào Git:**
   ```bash
   git add .
   ```

3. **Commit mã nguồn:**
   ```bash
   git commit -m "Initial commit - PhoneStore Project"
   ```

4. **Kết nối với Repository từ xa:**
   *(Thay URL bên dưới bằng URL repo của bạn)*
   ```bash
   git remote add origin https://github.com/cuongherok4/phone-store.git
   ```

5. **Đẩy mã nguồn lên:**
   ```bash
   git push -u origin main
   ```

---

## Phần 2: Setup dự án trên máy mới

### 1. Yêu cầu hệ thống (Prerequisites)
Đảm bảo máy mới đã cài đặt:
- **PHP >= 8.1** (Khuyên dùng 8.2)
- **Composer**
- **MySQL**
- **Node.js & NPM**

### 2. Các bước thiết lập

#### Bước 1: Clone dự án
```bash
git clone https://github.com/cuongherok4/phone-store.git
cd phone-store
```

#### Bước 2: Cài đặt Dependencies
```bash
# Cài đặt thư viện PHP
composer install

# Cài đặt thư viện Javascript
npm install
```

#### Bước 3: Cấu hình môi trường (.env)
- Tạo file `.env` từ file mẫu:
  ```bash
  cp .env.example .env
  ```
- Mở file `.env` và cấu hình thông tin Database:
  ```env
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=phone_store
  DB_USERNAME=root
  DB_PASSWORD=
  ```

#### Bước 4: Khởi tạo Application Key
```bash
php artisan key:generate
```

#### Bước 5: Thiết lập Cơ sở dữ liệu
Bạn có 2 lựa chọn:

**Cách A: Import file SQL (Khuyên dùng để có dữ liệu mẫu)**
- Tạo database tên `phone_store` trong MySQL.
- Import file `docs/phone_store_v2_updated.sql` vào database vừa tạo.

**Cách B: Chạy Migration (Nếu muốn database trắng)**
```bash
php artisan migrate --seed
```

#### Bước 6: Tạo liên kết Storage
Để hiển thị được hình ảnh sản phẩm và banner:
```bash
php artisan storage:link
```

#### Bước 7: Build assets
```bash
npm run dev
# Hoặc chạy production build:
npm run build
```

#### Bước 8: Khởi chạy dự án
```bash
php artisan serve
```
Truy cập: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## Một số lưu ý quan trọng:
- **Tài khoản Admin mặc định:** Kiểm tra trong bảng `users` (thường là `admin@gmail.com` / `12345678`).
- **Thư mục Storage:** Đảm bảo thư mục `storage` và `bootstrap/cache` có quyền ghi (Writable).
- **VNPAY/MoMo:** Nếu chạy trên máy mới, hãy cập nhật lại các khóa API trong trang **Cấu hình** của Admin nếu cần thiết.

Chúc bạn thiết lập thành công!
