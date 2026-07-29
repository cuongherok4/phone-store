# Production Migration Runbook

Tài liệu này chuẩn hóa cách chạy migration PhoneStore trên staging/production. Mục tiêu là thay đổi database có kiểm soát, có backup, có bước xác minh và có phương án xử lý khi lỗi.

## 1. Nguyên tắc

| Quy tắc | Bắt buộc |
|---|:---:|
| Chỉ chạy migration từ branch/release đã pass CI và đã review | Có |
| Luôn backup database trước khi chạy `php artisan migrate --force` | Có |
| Không chạy seed production nếu seeder chưa được review là không ghi đè dữ liệu thật | Có |
| Không rollback migration có `dropColumn`, `drop`, đổi kiểu cột hoặc xóa dữ liệu khi chưa xác nhận an toàn | Có |
| Nếu lỗi sau deploy nhưng dữ liệu đã thay đổi, ưu tiên hotfix forward thay vì rollback mù | Có |
| Sau migrate phải kiểm tra route, queue, checkout, admin order và log | Có |

## 2. Phân loại migration trước khi chạy

Trước mỗi release, đọc danh sách migration mới:

```bash
php artisan migrate:status
git diff --name-only <previous_release>..HEAD -- database/migrations
```

| Loại thay đổi | Rủi ro | Cách xử lý |
|---|---|---|
| Tạo bảng mới | Thấp | Có thể chạy trong maintenance window ngắn |
| Thêm cột nullable hoặc có default | Thấp | Ưu tiên dạng backward-compatible |
| Thêm index thường | Trung bình | Chạy lúc traffic thấp nếu bảng lớn |
| Thêm unique index | Cao | Kiểm tra dữ liệu trùng trước migrate |
| Đổi kiểu cột bằng `change()` | Cao | Test trên staging với dữ liệu gần production |
| `dropColumn`, `drop`, xóa dữ liệu | Rất cao | Cần backup, approval riêng và kế hoạch rollback/fix-forward |
| Thêm foreign key/cascade | Cao | Kiểm tra dữ liệu mồ côi trước migrate |

Các nhóm migration hiện tại cần chú ý khi có dữ liệu thật:

| Nhóm | Lưu ý |
|---|---|
| Coupon/payment unique index | Kiểm tra dữ liệu trùng trước khi thêm unique |
| Social token column changes | Test staging nếu DB có user social login |
| Performance/search indexes | Có thể lock bảng nếu dữ liệu sản phẩm/đơn hàng lớn |
| Permission tables | Cần seed/sync role sau deploy nếu cấu hình role thay đổi |

## 3. Pre-check bắt buộc

Chạy trên server trước khi migrate:

```bash
git status --short
git rev-parse --short HEAD
php -v
php artisan about
php artisan config:clear
php artisan migrate:status
php artisan queue:failed
```

Kiểm tra cấu hình:

```bash
php artisan config:show app
php artisan config:show database
php artisan config:show queue
```

Checklist trước khi bật maintenance:

| Việc | Trạng thái |
|---|:---:|
| Release commit đúng với PR đã merge | ⬜ |
| CI trên branch release/develop/main đã xanh | ⬜ |
| `.env` production đã theo `docs/PRODUCTION_ENV_CHECKLIST.md` | ⬜ |
| Có quyền ghi vào `storage` và `bootstrap/cache` | ⬜ |
| Queue worker có thể restart sau deploy | ⬜ |
| Đã thông báo maintenance window nếu hệ thống có khách thật | ⬜ |

## 4. Backup gate

Tạo thư mục backup ngoài repo:

```bash
sudo mkdir -p /var/backups/phone-store
sudo chown "$USER":"$USER" /var/backups/phone-store
```

Backup database:

```bash
BACKUP_TIME=$(date +%F_%H%M%S)
mysqldump \
  --single-transaction \
  --routines \
  --triggers \
  -u "$DB_USERNAME" \
  -p "$DB_DATABASE" \
  > "/var/backups/phone-store/db_${BACKUP_TIME}.sql"
```

Nén backup:

```bash
gzip "/var/backups/phone-store/db_${BACKUP_TIME}.sql"
```

Kiểm tra backup không rỗng:

```bash
ls -lh "/var/backups/phone-store/db_${BACKUP_TIME}.sql.gz"
gzip -t "/var/backups/phone-store/db_${BACKUP_TIME}.sql.gz"
```

Nếu có upload ảnh đang dùng local disk:

```bash
tar -czf "/var/backups/phone-store/storage_public_${BACKUP_TIME}.tar.gz" storage/app/public
```

Không được chạy migrate nếu backup database không tạo được hoặc không verify được.

## 5. Chạy migration production

Đưa hệ thống vào maintenance mode:

```bash
php artisan down --render="errors::503" --retry=60
```

Cài dependency và build nếu deploy code mới:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Chạy migrate:

```bash
php artisan migrate --force
```

Cache lại cấu hình:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan queue:restart
```

Mở lại hệ thống:

```bash
php artisan up
```

## 6. Verify sau migrate

Kiểm tra Artisan:

```bash
php artisan migrate:status
php artisan route:list
php artisan queue:failed
php artisan about
```

Kiểm tra trình duyệt:

| Luồng | Kỳ vọng |
|---|---|
| Trang chủ | Load được banner/sản phẩm, asset không 404 |
| Danh sách sản phẩm | Filter/sort/search hoạt động |
| Chi tiết sản phẩm | Variant, ảnh, tồn kho hiển thị đúng |
| Giỏ hàng | Add/update/remove/select item hoạt động |
| Checkout COD | Tạo đơn, trừ kho, queue email |
| Thanh toán online | Redirect/callback đúng môi trường |
| Admin order | Xem đơn, cập nhật trạng thái, hủy đơn đúng |
| Báo cáo/kho | Không lỗi query hoặc permission |

Theo dõi log trong 15-30 phút đầu:

```bash
tail -f storage/logs/laravel.log
php artisan queue:failed
```

## 7. Rollback và fix-forward

Chỉ dùng `migrate:rollback` khi migration vừa chạy có `down()` an toàn và chưa có dữ liệu mới phụ thuộc vào schema mới.

Rollback code và migration:

```bash
php artisan down --retry=60
git checkout <previous_release_commit>
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate:rollback --force --step=1
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan up
```

Khôi phục database từ backup khi lỗi nghiêm trọng:

```bash
php artisan down --retry=60
gunzip -c /var/backups/phone-store/db_<timestamp>.sql.gz | mysql -u "$DB_USERNAME" -p "$DB_DATABASE"
php artisan config:cache
php artisan queue:restart
php artisan up
```

Ưu tiên fix-forward khi:

| Tình huống | Hành động |
|---|---|
| Migration đã thêm dữ liệu/cột mới và user đã phát sinh đơn hàng mới | Viết migration sửa tiếp, không restore DB cũ |
| Lỗi chỉ nằm ở code đọc/ghi schema mới | Hotfix code và redeploy |
| Unique index fail vì dữ liệu trùng | Dừng deploy, làm migration làm sạch dữ liệu có kiểm soát |
| Queue job lỗi sau schema change | Pause worker, hotfix job, chạy lại job failed sau khi kiểm tra |

## 8. Mẫu biên bản deploy

| Trường | Giá trị |
|---|---|
| Ngày giờ deploy | |
| Người thực hiện | |
| Branch/commit | |
| Backup DB | |
| Backup storage | |
| Migration mới | |
| Kết quả `php artisan migrate --force` | |
| Smoke test | |
| Sự cố phát sinh | |
| Quyết định rollback/fix-forward | |

Lưu biên bản deploy bên ngoài repo nếu có thông tin nhạy cảm như server path, database name thật hoặc tên người vận hành.
