# PhoneStore Deployment Guide

Tài liệu này mô tả quy trình deploy PhoneStore lên production. Mục tiêu là deploy có kiểm soát, có bước kiểm tra, có rollback cơ bản và không làm gián đoạn dữ liệu vận hành.

## 1. Yêu cầu server

Khuyến nghị tối thiểu:

| Thành phần | Phiên bản / ghi chú |
|---|---|
| OS | Ubuntu 22.04 LTS hoặc tương đương |
| PHP | 8.2+ |
| Extensions | `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip` |
| Web server | Nginx hoặc Apache |
| Database | MySQL 8+ |
| Node.js | 20+ |
| Composer | 2.x |
| Queue | Laravel database queue hoặc Redis |
| Storage | Có backup cho database và `storage/app/public`; xem thêm [`docs/DATABASE_BACKUP_RUNBOOK.md`](docs/DATABASE_BACKUP_RUNBOOK.md), [`docs/UPLOAD_BACKUP_RUNBOOK.md`](docs/UPLOAD_BACKUP_RUNBOOK.md) |

## 2. Chuẩn bị code

Deploy từ nhánh ổn định đã được review:

```bash
git fetch origin
git checkout main
git pull origin main
```

Nếu deploy staging từ `develop`:

```bash
git checkout develop
git pull origin develop
```

## 3. Cấu hình `.env` production

Tạo file `.env` từ `.env.example`, sau đó cập nhật các giá trị production:

```bash
cp .env.example .env
php artisan key:generate
```

Checklist chi tiết theo từng nhóm biến nằm tại [`docs/PRODUCTION_ENV_CHECKLIST.md`](docs/PRODUCTION_ENV_CHECKLIST.md). Phải hoàn tất checklist này trước khi chạy migrate hoặc nhận đơn hàng thật.

Các biến bắt buộc cần kiểm tra:

```env
APP_NAME=PhoneStore
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=phone_store
DB_USERNAME=phone_store_user
DB_PASSWORD=change_me

CACHE_STORE=file
QUEUE_CONNECTION=database
SESSION_DRIVER=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=change_me
MAIL_PASSWORD=change_me
MAIL_FROM_ADDRESS=no-reply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

VNPAY_TMN_CODE=change_me
VNPAY_HASH_SECRET=change_me
VNPAY_URL=https://pay.vnpay.vn/vpcpay.html
VNPAY_RETURN_URL=/thanh-toan/vnpay-callback

MOMO_PARTNER_CODE=change_me
MOMO_ACCESS_KEY=change_me
MOMO_SECRET_KEY=change_me
MOMO_ENDPOINT=https://payment.momo.vn/v2/gateway/api/create
MOMO_RETURN_URL=/thanh-toan/momo-callback
```

Không commit `.env`, payment keys, mail password hoặc database password lên Git.

## 4. Cài dependencies

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

## 5. Quyền thư mục

```bash
chmod -R ug+rwx storage bootstrap/cache
php artisan storage:link
```

Nếu dùng user `www-data`:

```bash
chown -R www-data:www-data storage bootstrap/cache
```

## 6. Backup trước migrate

Quy trình migrate production chi tiết nằm tại [`docs/PRODUCTION_MIGRATION_RUNBOOK.md`](docs/PRODUCTION_MIGRATION_RUNBOOK.md). Trước khi deploy thật, dùng runbook đó làm checklist chính.

Luôn backup database trước khi chạy migration production:

```bash
mysqldump -u phone_store_user -p phone_store > backups/phone_store_$(date +%F_%H%M%S).sql
```

Nếu có upload ảnh sản phẩm/banner:

```bash
tar -czf backups/storage_public_$(date +%F_%H%M%S).tar.gz storage/app/public
```

## 7. Chạy migrate và optimize

Chỉ chạy bước này sau khi backup đã verify thành công và migration mới đã được phân loại rủi ro.

```bash
php artisan down
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan up
```

Nếu deploy lần đầu:

```bash
php artisan db:seed --force
```

Chỉ chạy seed production khi đã kiểm tra seeder không ghi đè dữ liệu thật.

## 8. Queue worker

Email xác nhận đơn hàng và email cập nhật trạng thái đang chạy qua queue. Production bắt buộc có queue worker. Runbook chi tiết nằm tại [`docs/QUEUE_WORKER_RUNBOOK.md`](docs/QUEUE_WORKER_RUNBOOK.md).

Chạy thử thủ công:

```bash
php artisan queue:work --tries=3 --timeout=90
```

Ví dụ Supervisor:

```ini
[program:phonestore-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/phone-store/artisan queue:work --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/phone-store/storage/logs/worker.log
stopwaitsecs=3600
```

Sau khi đổi code:

```bash
php artisan queue:restart
```

## 9. Scheduler

Production nên cấu hình Laravel Scheduler để sẵn sàng chạy tác vụ định kỳ. Runbook chi tiết nằm tại [`docs/SCHEDULER_RUNBOOK.md`](docs/SCHEDULER_RUNBOOK.md).

Cron mẫu:

```cron
* * * * * cd /var/www/phone-store && php artisan schedule:run >> storage/logs/scheduler.log 2>&1
```

Kiểm tra:

```bash
php artisan schedule:list
```

## 10. Nginx mẫu

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/phone-store/public;

    index index.php index.html;

    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Sau khi cấu hình SSL, buộc HTTPS bằng Nginx hoặc load balancer.

## 11. Smoke test sau deploy

Kiểm tra nhanh sau mỗi lần deploy:

```bash
php artisan about
php artisan route:list
php artisan queue:failed
```

Checklist trình duyệt:

| Luồng | Kỳ vọng |
|---|---|
| Trang chủ | Banner/sản phẩm hiển thị, không lỗi asset |
| Danh sách sản phẩm | Filter/sort hoạt động |
| Chi tiết sản phẩm | Variant, ảnh, related products hiển thị |
| Checkout COD | Tạo đơn, trừ kho, queue email |
| Checkout VNPAY sandbox/production | Redirect và callback thành công |
| Admin dashboard | Thống kê hiển thị, cache không lỗi |
| Admin hủy đơn | Hoàn kho đúng nếu đơn đã trừ tồn |

## 12. Rollback nhanh

Nếu lỗi sau deploy:

```bash
php artisan down
git checkout <previous_commit_or_tag>
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate:rollback --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan up
```

Chỉ rollback migration khi migration đó có `down()` an toàn và đã xác nhận không làm mất dữ liệu mới. Nếu migration ảnh hưởng dữ liệu vận hành, ưu tiên hotfix forward theo [`docs/PRODUCTION_MIGRATION_RUNBOOK.md`](docs/PRODUCTION_MIGRATION_RUNBOOK.md).

## 13. Giám sát vận hành

Theo dõi các điểm sau:

- `storage/logs/laravel.log`
- `storage/logs/worker.log`
- `php artisan queue:failed`
- Queue worker theo [`docs/QUEUE_WORKER_RUNBOOK.md`](docs/QUEUE_WORKER_RUNBOOK.md)
- Scheduler theo [`docs/SCHEDULER_RUNBOOK.md`](docs/SCHEDULER_RUNBOOK.md)
- Backup database theo [`docs/DATABASE_BACKUP_RUNBOOK.md`](docs/DATABASE_BACKUP_RUNBOOK.md)
- Backup uploaded files theo [`docs/UPLOAD_BACKUP_RUNBOOK.md`](docs/UPLOAD_BACKUP_RUNBOOK.md)
- Dung lượng disk cho upload và log
- Database slow query log nếu traffic tăng
- Tỷ lệ callback thanh toán lỗi
- Email queue bị tồn đọng

## 14. Quy tắc release

- `main` chỉ nhận code đã review và đã pass CI.
- Không deploy trực tiếp từ feature branch lên production.
- Trước deploy phải có backup DB.
- Sau deploy phải chạy smoke test.
- Mọi thay đổi payment, coupon, inventory, order phải có test liên quan trước khi merge.
