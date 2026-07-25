# Database Backup Runbook

Tài liệu này chuẩn hóa backup database định kỳ cho PhoneStore. Mục tiêu là có bản sao dữ liệu đủ mới để khôi phục khi lỗi deploy, lỗi người vận hành, hỏng server hoặc mất dữ liệu.

## 1. Mục tiêu vận hành

| Chỉ số | Mục tiêu |
|---|---|
| RPO | Tối đa mất 24 giờ dữ liệu với shop nhỏ, giảm xuống 1-6 giờ khi có đơn đều |
| RTO | Khôi phục dịch vụ trong 1-2 giờ sau khi xác nhận sự cố DB |
| Retention | Daily 14 ngày, weekly 8 tuần, monthly 6 tháng |
| Vị trí lưu | Tối thiểu 1 bản trên server và 1 bản ngoài server |
| Kiểm tra restore | Tối thiểu mỗi tháng 1 lần trên staging/local |

## 2. Nguyên tắc

| Quy tắc | Bắt buộc |
|---|:---:|
| Backup nằm ngoài thư mục repo và ngoài `public/` | Có |
| File backup không được commit lên Git | Có |
| Backup phải nén và đặt quyền đọc hạn chế | Có |
| Sau backup phải verify file không rỗng và giải nén được | Có |
| Backup production phải được copy ra nơi lưu ngoài server | Có |
| Mỗi tháng phải restore thử ít nhất một bản backup | Có |

## 3. Chuẩn bị server

Tạo thư mục backup:

```bash
sudo mkdir -p /var/backups/phone-store/database
sudo chown "$USER":"$USER" /var/backups/phone-store/database
chmod 700 /var/backups/phone-store/database
```

Tạo file cấu hình MySQL client để tránh ghi password trực tiếp trong cron:

```bash
mkdir -p ~/.config/phone-store
chmod 700 ~/.config/phone-store
nano ~/.config/phone-store/mysql-backup.cnf
```

Nội dung mẫu:

```ini
[client]
user=phone_store_user
password=change_me
host=127.0.0.1
port=3306
```

Khóa quyền file:

```bash
chmod 600 ~/.config/phone-store/mysql-backup.cnf
```

## 4. Script backup database

Tạo file `/usr/local/bin/phone-store-db-backup`:

```bash
#!/usr/bin/env bash
set -euo pipefail

APP_NAME="phone-store"
DB_NAME="phone_store"
BACKUP_DIR="/var/backups/phone-store/database"
MYSQL_CNF="$HOME/.config/phone-store/mysql-backup.cnf"
DATE="$(date +%F_%H%M%S)"
BACKUP_FILE="${BACKUP_DIR}/${APP_NAME}_${DB_NAME}_${DATE}.sql"
LOG_FILE="${BACKUP_DIR}/backup.log"

mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR"

{
  echo "[$(date --iso-8601=seconds)] Starting database backup: ${BACKUP_FILE}.gz"

  mysqldump \
    --defaults-extra-file="$MYSQL_CNF" \
    --single-transaction \
    --quick \
    --routines \
    --triggers \
    --events \
    --set-gtid-purged=OFF \
    "$DB_NAME" > "$BACKUP_FILE"

  gzip "$BACKUP_FILE"
  chmod 600 "${BACKUP_FILE}.gz"

  test -s "${BACKUP_FILE}.gz"
  gzip -t "${BACKUP_FILE}.gz"

  find "$BACKUP_DIR" -name "${APP_NAME}_${DB_NAME}_*.sql.gz" -mtime +14 -delete

  echo "[$(date --iso-8601=seconds)] Backup completed: ${BACKUP_FILE}.gz"
} >> "$LOG_FILE" 2>&1
```

Cấp quyền chạy:

```bash
sudo chmod +x /usr/local/bin/phone-store-db-backup
```

Chạy thử:

```bash
/usr/local/bin/phone-store-db-backup
tail -n 50 /var/backups/phone-store/database/backup.log
ls -lh /var/backups/phone-store/database
```

## 5. Cron backup định kỳ

Mở cron:

```bash
crontab -e
```

Lịch đề xuất cho shop nhỏ:

```cron
# PhoneStore database backup: daily at 02:15
15 2 * * * /usr/local/bin/phone-store-db-backup
```

Khi hệ thống có đơn đều trong ngày, tăng tần suất:

```cron
# PhoneStore database backup: every 6 hours
15 */6 * * * /usr/local/bin/phone-store-db-backup
```

Kiểm tra cron:

```bash
crontab -l
grep CRON /var/log/syslog | tail -n 50
tail -n 50 /var/backups/phone-store/database/backup.log
```

## 6. Copy backup ra ngoài server

Tối thiểu cần một bản ngoài server. Có thể dùng S3-compatible storage, VPS backup volume hoặc một server nội bộ riêng.

Ví dụ sync bằng `rclone`:

```bash
rclone copy /var/backups/phone-store/database remote:phone-store/database \
  --include "*.sql.gz" \
  --log-file /var/backups/phone-store/database/rclone.log \
  --log-level INFO
```

Cron sync sau backup:

```cron
# Sync database backup off-server
45 2 * * * rclone copy /var/backups/phone-store/database remote:phone-store/database --include "*.sql.gz" --log-file /var/backups/phone-store/database/rclone.log --log-level INFO
```

Nếu chưa có object storage, vẫn phải tải backup ra máy quản trị sau mỗi lần deploy quan trọng.

## 7. Verify backup hằng ngày

Checklist kiểm tra nhanh:

| Kiểm tra | Lệnh |
|---|---|
| Có file backup mới trong 24 giờ | `find /var/backups/phone-store/database -name "*.sql.gz" -mtime -1 -ls` |
| File không rỗng | `ls -lh /var/backups/phone-store/database/*.sql.gz` |
| File nén hợp lệ | `gzip -t /var/backups/phone-store/database/<file>.sql.gz` |
| Cron chạy thành công | `tail -n 50 /var/backups/phone-store/database/backup.log` |
| Đã sync off-server | Kiểm tra log `rclone.log` hoặc dashboard provider |

## 8. Restore drill

Không restore trực tiếp lên production để kiểm thử. Dùng staging/local:

```bash
createdb phone_store_restore_test
```

Với MySQL:

```bash
mysql -u root -p -e "CREATE DATABASE phone_store_restore_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
gunzip -c /var/backups/phone-store/database/<backup_file>.sql.gz \
  | mysql -u root -p phone_store_restore_test
```

Kiểm tra dữ liệu:

```bash
mysql -u root -p -e "USE phone_store_restore_test; SHOW TABLES; SELECT COUNT(*) FROM orders;"
```

Ghi lại kết quả restore drill:

| Ngày | File backup | Môi trường restore | Kết quả | Người kiểm tra |
|---|---|---|---|---|
| | | | | |

## 9. Khôi phục production khi sự cố

Chỉ restore production khi đã xác nhận cần quay về bản backup và chấp nhận mất dữ liệu sau thời điểm backup.

```bash
php artisan down --retry=60
gunzip -c /var/backups/phone-store/database/<backup_file>.sql.gz \
  | mysql --defaults-extra-file="$HOME/.config/phone-store/mysql-backup.cnf" phone_store
php artisan config:cache
php artisan queue:restart
php artisan up
```

Sau restore:

```bash
php artisan migrate:status
php artisan route:list
php artisan queue:failed
```

Kiểm tra trình duyệt:

| Luồng | Kỳ vọng |
|---|---|
| Admin đăng nhập | Vào dashboard được |
| Danh sách đơn | Có dữ liệu đúng thời điểm backup |
| Chi tiết đơn | Không lỗi relation/order item |
| Kho hàng | Tồn kho khớp dữ liệu backup |
| Checkout test | Tạo đơn mới được sau restore |

## 10. Cảnh báo cần xử lý ngay

| Dấu hiệu | Hành động |
|---|---|
| Không có backup mới trong 24 giờ | Kiểm tra cron, disk, quyền thư mục |
| Backup size giảm bất thường | Restore thử ngay trên staging/local |
| `gzip -t` lỗi | Xóa bản lỗi sau khi có bản tốt, kiểm tra disk/RAM |
| Sync off-server fail | Chạy lại sync, kiểm tra credential provider |
| Disk backup gần đầy | Tăng retention cleanup hoặc chuyển backup ra storage ngoài |

## 11. Bàn giao khách hàng

Khi bàn giao hệ thống, ghi rõ:

| Nội dung | Cần bàn giao |
|---|---|
| Lịch backup | Daily hoặc mỗi 6 giờ |
| Nơi lưu backup | Server path và storage ngoài server |
| Retention | Daily/weekly/monthly |
| Người nhận cảnh báo | Email/Slack/nhóm vận hành |
| Quy trình restore | Link tài liệu này và người chịu trách nhiệm |

Không ghi database password, S3 secret hoặc thông tin truy cập server trực tiếp vào tài liệu public trong repo.
