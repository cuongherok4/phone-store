# Scheduler Runbook

Tài liệu này chuẩn hóa cách bật Laravel Scheduler cho PhoneStore trên staging/production. Scheduler là nền tảng để chạy tác vụ định kỳ như dọn dữ liệu tạm, gửi báo cáo, kiểm tra đơn thanh toán treo hoặc health check vận hành.

## 1. Trạng thái hiện tại

Hiện tại hệ thống chưa có scheduled task nghiệp vụ đang chạy.

Kiểm tra:

```bash
php artisan schedule:list
```

Kết quả kỳ vọng hiện tại:

```text
No scheduled tasks have been defined.
```

Dù chưa có task nghiệp vụ, production vẫn nên cấu hình sẵn scheduler để khi thêm tác vụ định kỳ sau này không phải thay đổi hạ tầng.

## 2. Nguyên tắc

| Quy tắc | Bắt buộc |
|---|:---:|
| Chỉ có một cron entry gọi `php artisan schedule:run` mỗi phút | Có |
| Không tạo nhiều cron riêng lẻ cho từng artisan command | Có |
| Scheduled task nặng phải đẩy sang queue nếu có thể | Có |
| Task có nguy cơ chạy lâu phải dùng lock/overlap control | Có |
| Log scheduler phải được theo dõi cùng queue worker | Có |
| Sau deploy phải kiểm tra `schedule:list` và cron/systemd timer | Có |

## 3. Cron production

Mở crontab của user chạy app, thường là `www-data` hoặc user deploy:

```bash
sudo crontab -u www-data -e
```

Thêm một dòng duy nhất:

```cron
* * * * * cd /var/www/phone-store && php artisan schedule:run >> storage/logs/scheduler.log 2>&1
```

Kiểm tra cron:

```bash
sudo crontab -u www-data -l
grep CRON /var/log/syslog | tail -n 50
tail -n 100 /var/www/phone-store/storage/logs/scheduler.log
```

Nếu server không có `/var/log/syslog`, dùng:

```bash
journalctl -u cron -n 100 --no-pager
```

## 4. systemd timer thay cho cron

Nếu muốn quản lý bằng systemd, tạo service `/etc/systemd/system/phone-store-schedule.service`:

```ini
[Unit]
Description=Run PhoneStore Laravel Scheduler

[Service]
Type=oneshot
WorkingDirectory=/var/www/phone-store
ExecStart=/usr/bin/php artisan schedule:run
User=www-data
Group=www-data
StandardOutput=append:/var/www/phone-store/storage/logs/scheduler.log
StandardError=append:/var/www/phone-store/storage/logs/scheduler.log
```

Tạo timer `/etc/systemd/system/phone-store-schedule.timer`:

```ini
[Unit]
Description=Run PhoneStore Laravel Scheduler every minute

[Timer]
OnBootSec=60
OnUnitActiveSec=60
AccuracySec=1s
Unit=phone-store-schedule.service

[Install]
WantedBy=timers.target
```

Kích hoạt:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now phone-store-schedule.timer
sudo systemctl list-timers phone-store-schedule.timer
sudo systemctl status phone-store-schedule.timer
```

Không dùng đồng thời cron và systemd timer cho scheduler trên cùng server.

## 5. Thêm scheduled task mới

Laravel 12 có thể khai báo schedule trong `routes/console.php` bằng `Schedule` facade.

Ví dụ:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:prune-failed --hours=168')
    ->dailyAt('03:30')
    ->withoutOverlapping()
    ->onOneServer();
```

Checklist khi thêm task:

| Câu hỏi | Yêu cầu |
|---|---|
| Task có thay đổi dữ liệu thật không? | Phải có log và test staging |
| Task có thể chạy lâu không? | Dùng `withoutOverlapping()` |
| Task có thể chạy nhiều server không? | Dùng `onOneServer()` nếu dùng shared cache |
| Task có gửi mail/API không? | Cân nhắc dispatch job sang queue |
| Task thất bại có ảnh hưởng đơn hàng không? | Có cảnh báo/rollback/fix-forward |

## 6. Deploy checklist

| Việc | Lệnh |
|---|---|
| Xem danh sách task | `php artisan schedule:list` |
| Test một task cụ thể nếu có | `php artisan schedule:test` |
| Chạy scheduler thủ công | `php artisan schedule:run` |
| Kiểm tra cron | `sudo crontab -u www-data -l` |
| Kiểm tra timer nếu dùng systemd | `sudo systemctl list-timers phone-store-schedule.timer` |
| Theo dõi log | `tail -f storage/logs/scheduler.log` |

Sau deploy:

```bash
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan schedule:list
```

## 7. Giám sát hằng ngày

```bash
tail -n 100 storage/logs/scheduler.log
php artisan schedule:list
php artisan queue:failed
```

Ngưỡng cảnh báo đề xuất:

| Dấu hiệu | Hành động |
|---|---|
| `scheduler.log` không có dòng mới trong ngày | Kiểm tra cron/timer |
| Task chạy lặp nhiều lần trong một phút | Kiểm tra có cấu hình trùng cron và systemd timer |
| Task chạy quá lâu | Thêm `withoutOverlapping()`, chuyển phần nặng sang queue |
| Task fail liên tục | Tắt task trong release/hotfix, kiểm tra log và dữ liệu liên quan |
| Queue backlog tăng sau scheduler | Kiểm tra worker theo `docs/QUEUE_WORKER_RUNBOOK.md` |

## 8. Bàn giao khách hàng

| Nội dung | Cần bàn giao |
|---|---|
| Cơ chế chạy scheduler | Cron hoặc systemd timer |
| User chạy scheduler | `www-data` hoặc user deploy |
| App path | `/var/www/phone-store` |
| File log | `storage/logs/scheduler.log` |
| Lệnh kiểm tra task | `php artisan schedule:list` |
| Quy tắc thêm task mới | Dùng `routes/console.php`, log đầy đủ, tránh overlap |

Không ghi SSH key, server password hoặc secret `.env` vào tài liệu public trong repo.
