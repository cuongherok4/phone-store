# Queue Worker Runbook

Tài liệu này chuẩn hóa cách chạy queue worker cho PhoneStore trên staging/production. Queue đang xử lý email xác nhận đơn hàng và email cập nhật trạng thái đơn hàng, giúp checkout/admin update không bị chậm vì SMTP.

## 1. Phạm vi queue hiện tại

| Tác vụ | Nguồn gọi | Job/Mailable |
|---|---|---|
| Email xác nhận đơn hàng | Checkout thành công | `App\Mail\OrderConfirmation` |
| Email cập nhật trạng thái đơn | Admin cập nhật trạng thái | `App\Mail\OrderStatusChanged` |

Hai mailable này implement `ShouldQueue`, nên production bắt buộc có worker chạy nền khi `QUEUE_CONNECTION=database` hoặc `redis`.

## 2. Cấu hình `.env`

Khuyến nghị mặc định cho VPS nhỏ:

```env
QUEUE_CONNECTION=database
QUEUE_FAILED_DRIVER=database-uuids
DB_QUEUE=default
DB_QUEUE_RETRY_AFTER=90
```

Khi traffic tăng và có Redis:

```env
QUEUE_CONNECTION=redis
REDIS_QUEUE=default
REDIS_QUEUE_RETRY_AFTER=90
```

Không dùng `QUEUE_CONNECTION=sync` trên production vì request checkout/admin sẽ phải chờ gửi mail.

## 3. Pre-check

```bash
php artisan config:clear
php artisan migrate:status
php artisan queue:failed
php artisan about
```

Kiểm tra bảng queue đã có:

```bash
php artisan tinker
Schema::hasTable('jobs');
Schema::hasTable('failed_jobs');
```

Kiểm tra gửi mail queue thủ công:

```bash
php artisan queue:work --once --tries=3 --timeout=90
```

## 4. Chạy thử worker thủ công

Chạy trong thư mục app:

```bash
cd /var/www/phone-store
php artisan queue:work database --queue=default --sleep=3 --tries=3 --timeout=90 --max-time=3600
```

Nếu dùng Redis:

```bash
php artisan queue:work redis --queue=default --sleep=3 --tries=3 --timeout=90 --max-time=3600
```

Worker thủ công chỉ để test. Production cần Supervisor hoặc systemd để tự restart khi process chết.

## 5. Supervisor

Cài Supervisor:

```bash
sudo apt update
sudo apt install supervisor
```

Tạo file `/etc/supervisor/conf.d/phone-store-worker.conf`:

```ini
[program:phone-store-worker]
process_name=%(program_name)s_%(process_num)02d
directory=/var/www/phone-store
command=php artisan queue:work database --queue=default --sleep=3 --tries=3 --timeout=90 --max-time=3600
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

Nếu dùng Redis, đổi command thành:

```ini
command=php artisan queue:work redis --queue=default --sleep=3 --tries=3 --timeout=90 --max-time=3600
```

Nạp cấu hình:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start phone-store-worker:*
sudo supervisorctl status
```

Restart worker sau deploy:

```bash
php artisan queue:restart
sudo supervisorctl restart phone-store-worker:*
```

## 6. systemd

Nếu không dùng Supervisor, có thể dùng systemd.

Tạo file `/etc/systemd/system/phone-store-worker.service`:

```ini
[Unit]
Description=PhoneStore Laravel Queue Worker
After=network.target mysql.service

[Service]
Type=simple
WorkingDirectory=/var/www/phone-store
ExecStart=/usr/bin/php artisan queue:work database --queue=default --sleep=3 --tries=3 --timeout=90 --max-time=3600
Restart=always
RestartSec=5
User=www-data
Group=www-data
KillSignal=SIGTERM
TimeoutStopSec=3600
StandardOutput=append:/var/www/phone-store/storage/logs/worker.log
StandardError=append:/var/www/phone-store/storage/logs/worker.log

[Install]
WantedBy=multi-user.target
```

Kích hoạt:

```bash
sudo systemctl daemon-reload
sudo systemctl enable phone-store-worker
sudo systemctl start phone-store-worker
sudo systemctl status phone-store-worker
```

Restart sau deploy:

```bash
php artisan queue:restart
sudo systemctl restart phone-store-worker
```

## 7. Deploy checklist

| Việc | Lệnh |
|---|---|
| Clear config cũ | `php artisan config:clear` |
| Đảm bảo migration queue đã chạy | `php artisan migrate:status` |
| Cache config mới | `php artisan config:cache` |
| Restart worker để nhận code mới | `php artisan queue:restart` |
| Kiểm tra worker process | `sudo supervisorctl status` hoặc `sudo systemctl status phone-store-worker` |
| Kiểm tra job lỗi | `php artisan queue:failed` |
| Theo dõi log | `tail -f storage/logs/worker.log` |

## 8. Giám sát hằng ngày

```bash
php artisan queue:failed
tail -n 100 storage/logs/worker.log
```

Nếu dùng database queue, kiểm tra backlog:

```bash
php artisan tinker
DB::table('jobs')->count();
DB::table('failed_jobs')->count();
```

Ngưỡng cảnh báo đề xuất:

| Dấu hiệu | Hành động |
|---|---|
| `jobs` tăng liên tục | Kiểm tra SMTP, worker process, tăng `numprocs` nếu cần |
| Có `failed_jobs` mới | Xem exception, sửa nguyên nhân, retry có chọn lọc |
| Worker restart liên tục | Kiểm tra memory, permission, `.env`, log |
| Email khách không nhận | Kiểm tra mail provider, spam/bounce, queue failed |

## 9. Retry failed jobs

Xem danh sách job lỗi:

```bash
php artisan queue:failed
```

Retry một job:

```bash
php artisan queue:retry <uuid>
```

Retry tất cả chỉ khi đã chắc nguyên nhân đã được sửa:

```bash
php artisan queue:retry all
```

Xóa failed job sau khi đã xử lý:

```bash
php artisan queue:forget <uuid>
```

Không chạy `queue:flush` nếu chưa export/log lại lỗi cần điều tra.

## 10. Bàn giao khách hàng

| Nội dung | Cần bàn giao |
|---|---|
| Queue driver | `database` hoặc `redis` |
| Process manager | Supervisor hoặc systemd |
| File log | `storage/logs/worker.log` |
| Lệnh restart | `php artisan queue:restart` và restart service |
| Lệnh kiểm tra lỗi | `php artisan queue:failed` |
| Tác vụ đang queue | Email xác nhận đơn và email cập nhật trạng thái |

Không ghi SMTP password, Redis password hoặc quyền truy cập server trực tiếp vào tài liệu public trong repo.
