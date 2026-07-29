# Uploaded Files Backup Runbook

Tài liệu này chuẩn hóa backup file upload cho PhoneStore. Database backup chỉ giữ metadata; ảnh thật nằm trong `storage/app/public` hoặc storage S3-compatible nên phải có backup riêng.

## 1. Phạm vi cần backup

Ứng dụng đang lưu upload qua Laravel disk `public`, root mặc định là `storage/app/public`.

| Nhóm file | Thư mục | Nơi tham chiếu |
|---|---|---|
| Avatar khách hàng | `avatars/` | `users.avatar` |
| Logo thương hiệu | `brands/` | `brands.logo` |
| Banner trang chủ | `banners/` | `banners.image_url` |
| Ảnh review | `reviews/` | `reviews.images` |
| Ảnh biến thể sản phẩm | `variants/{variant_id}/` | `variant_images.image_url` |

Không backup các thư mục runtime như `storage/framework`, `storage/logs`, `bootstrap/cache` trong runbook này.

## 2. Mục tiêu vận hành

| Chỉ số | Mục tiêu |
|---|---|
| RPO | Tối đa mất 24 giờ upload với shop nhỏ, giảm xuống 1-6 giờ khi có nhiều ảnh sản phẩm/review |
| RTO | Khôi phục ảnh public trong 1 giờ sau khi xác nhận sự cố |
| Retention | Daily 14 ngày, weekly 8 tuần, monthly 6 tháng |
| Vị trí lưu | Tối thiểu 1 bản trên server và 1 bản ngoài server |
| Restore drill | Tối thiểu mỗi tháng 1 lần trên staging/local |

## 3. Nguyên tắc

| Quy tắc | Bắt buộc |
|---|:---:|
| Backup upload phải chạy gần thời điểm backup database | Có |
| Backup nằm ngoài repo và ngoài `public/` | Có |
| Không commit file upload backup lên Git | Có |
| File backup phải nén, verify được và đặt quyền đọc hạn chế | Có |
| Backup phải được copy ra ngoài server production | Có |
| Restore upload phải đi kèm kiểm tra link ảnh trong trình duyệt | Có |

## 4. Chuẩn bị server

Tạo thư mục backup:

```bash
sudo mkdir -p /var/backups/phone-store/uploads
sudo chown "$USER":"$USER" /var/backups/phone-store/uploads
chmod 700 /var/backups/phone-store/uploads
```

Kiểm tra storage link:

```bash
php artisan storage:link
ls -la public/storage
ls -la storage/app/public
```

## 5. Script backup upload local disk

Tạo file `/usr/local/bin/phone-store-upload-backup`:

```bash
#!/usr/bin/env bash
set -euo pipefail

APP_NAME="phone-store"
APP_DIR="/var/www/phone-store"
SOURCE_DIR="${APP_DIR}/storage/app/public"
BACKUP_DIR="/var/backups/phone-store/uploads"
DATE="$(date +%F_%H%M%S)"
BACKUP_FILE="${BACKUP_DIR}/${APP_NAME}_uploads_${DATE}.tar.gz"
LOG_FILE="${BACKUP_DIR}/backup.log"

mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR"

{
  echo "[$(date --iso-8601=seconds)] Starting uploaded files backup: ${BACKUP_FILE}"

  if [ ! -d "$SOURCE_DIR" ]; then
    echo "Source directory not found: ${SOURCE_DIR}"
    exit 1
  fi

  tar -czf "$BACKUP_FILE" -C "$SOURCE_DIR" .
  chmod 600 "$BACKUP_FILE"

  test -s "$BACKUP_FILE"
  tar -tzf "$BACKUP_FILE" > /dev/null

  find "$BACKUP_DIR" -name "${APP_NAME}_uploads_*.tar.gz" -mtime +14 -delete

  echo "[$(date --iso-8601=seconds)] Backup completed: ${BACKUP_FILE}"
} >> "$LOG_FILE" 2>&1
```

Cấp quyền chạy:

```bash
sudo chmod +x /usr/local/bin/phone-store-upload-backup
```

Chạy thử:

```bash
/usr/local/bin/phone-store-upload-backup
tail -n 50 /var/backups/phone-store/uploads/backup.log
ls -lh /var/backups/phone-store/uploads
```

## 6. Cron backup định kỳ

Nên chạy sau database backup 15-30 phút để metadata và file upload gần cùng thời điểm.

```cron
# PhoneStore uploaded files backup: daily at 02:45
45 2 * * * /usr/local/bin/phone-store-upload-backup
```

Khi hệ thống có nhiều upload trong ngày:

```cron
# PhoneStore uploaded files backup: every 6 hours
45 */6 * * * /usr/local/bin/phone-store-upload-backup
```

Kiểm tra cron:

```bash
crontab -l
grep CRON /var/log/syslog | tail -n 50
tail -n 50 /var/backups/phone-store/uploads/backup.log
```

## 7. Copy backup ra ngoài server

Ví dụ sync bằng `rclone`:

```bash
rclone copy /var/backups/phone-store/uploads remote:phone-store/uploads \
  --include "*.tar.gz" \
  --log-file /var/backups/phone-store/uploads/rclone.log \
  --log-level INFO
```

Cron sync:

```cron
# Sync uploaded files backup off-server
15 3 * * * rclone copy /var/backups/phone-store/uploads remote:phone-store/uploads --include "*.tar.gz" --log-file /var/backups/phone-store/uploads/rclone.log --log-level INFO
```

Nếu dùng S3-compatible disk trực tiếp cho upload, cấu hình lifecycle/versioning trên bucket và vẫn cần restore drill định kỳ.

## 8. Verify backup hằng ngày

| Kiểm tra | Lệnh |
|---|---|
| Có file backup mới trong 24 giờ | `find /var/backups/phone-store/uploads -name "*.tar.gz" -mtime -1 -ls` |
| File không rỗng | `ls -lh /var/backups/phone-store/uploads/*.tar.gz` |
| File tar hợp lệ | `tar -tzf /var/backups/phone-store/uploads/<file>.tar.gz > /dev/null` |
| Cron chạy thành công | `tail -n 50 /var/backups/phone-store/uploads/backup.log` |
| Đã sync off-server | Kiểm tra `rclone.log` hoặc dashboard provider |

## 9. Restore drill

Không restore thử đè lên production. Dùng staging/local:

```bash
mkdir -p /tmp/phone-store-upload-restore
tar -xzf /var/backups/phone-store/uploads/<backup_file>.tar.gz -C /tmp/phone-store-upload-restore
find /tmp/phone-store-upload-restore -maxdepth 2 -type f | head
```

Kiểm tra đủ nhóm thư mục:

```bash
ls -la /tmp/phone-store-upload-restore
```

Ghi lại kết quả:

| Ngày | File backup | Môi trường restore | Kết quả | Người kiểm tra |
|---|---|---|---|---|
| | | | | |

## 10. Restore production khi sự cố

Chỉ restore production khi đã xác nhận mất file upload hoặc cần quay lại snapshot cũ.

```bash
php artisan down --retry=60
mkdir -p storage/app/public
tar -xzf /var/backups/phone-store/uploads/<backup_file>.tar.gz -C storage/app/public
php artisan storage:link
php artisan config:cache
php artisan up
```

Nếu cần restore sạch trước khi bung backup:

```bash
php artisan down --retry=60
mv storage/app/public "storage/app/public_broken_$(date +%F_%H%M%S)"
mkdir -p storage/app/public
tar -xzf /var/backups/phone-store/uploads/<backup_file>.tar.gz -C storage/app/public
php artisan storage:link
php artisan up
```

Sau restore, kiểm tra trình duyệt:

| Luồng | Kỳ vọng |
|---|---|
| Trang chủ | Banner hiển thị |
| Danh sách/chi tiết sản phẩm | Ảnh variant hiển thị |
| Trang thương hiệu/admin brand | Logo hiển thị |
| Hồ sơ khách hàng | Avatar hiển thị |
| Review sản phẩm/đơn hàng | Ảnh review hiển thị |

## 11. Cảnh báo cần xử lý ngay

| Dấu hiệu | Hành động |
|---|---|
| Không có backup upload mới trong 24 giờ | Kiểm tra cron, quyền thư mục, disk |
| Backup nhỏ bất thường | Restore thử và so với số lượng file thực tế |
| `tar -tzf` lỗi | Tạo lại backup, kiểm tra disk/RAM |
| Ảnh 404 sau restore | Kiểm tra `php artisan storage:link`, quyền file, `APP_URL` |
| Sync off-server fail | Chạy lại sync và kiểm tra credential provider |

## 12. Bàn giao khách hàng

Khi bàn giao, ghi rõ:

| Nội dung | Cần bàn giao |
|---|---|
| Thư mục upload chính | `storage/app/public` |
| Public symlink | `public/storage` |
| Lịch backup upload | Daily hoặc mỗi 6 giờ |
| Nơi lưu ngoài server | S3-compatible bucket/VPS backup/off-site storage |
| Restore drill | Lịch kiểm tra hằng tháng |
| Người chịu trách nhiệm | Admin vận hành hoặc bên nhận bàn giao |

Không ghi secret S3, SSH key hoặc thông tin truy cập server trực tiếp vào tài liệu public trong repo.
