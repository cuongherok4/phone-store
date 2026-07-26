# Customer Handover Guide

Tài liệu này dùng khi bàn giao PhoneStore cho khách hàng hoặc đội vận hành. Mục tiêu là giúp người nhận biết hệ thống gồm những gì, cách vận hành hằng ngày, cách xử lý sự cố cơ bản và những thông tin nào phải lưu ngoài Git.

## 1. Phạm vi bàn giao

| Nhóm | Nội dung |
|---|---|
| Source code | Repository GitHub, branch `main` production và `develop` staging |
| Application | Laravel 12, PHP 8.2+, MySQL 8+, Vite/Tailwind/Alpine |
| Admin | Quản lý sản phẩm, biến thể, kho, đơn hàng, coupon, banner, brand, review, báo cáo |
| Customer | Storefront, tìm kiếm, giỏ hàng, checkout COD/online, wishlist, review, profile |
| Operations | Deploy, `.env`, migration, queue worker, scheduler, backup DB, backup upload |
| CI/CD | GitHub Actions chạy syntax, route list, `php artisan test`, frontend build |

## 2. Thông tin không lưu trong Git

Các thông tin sau phải bàn giao qua kênh an toàn như password manager hoặc secret manager, không ghi trực tiếp vào repo:

| Nhóm secret | Ví dụ |
|---|---|
| Server | SSH host, SSH user, SSH private key |
| Database | `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` |
| App | `APP_KEY`, production `.env` |
| Mail | SMTP username/password hoặc API key |
| Payment | VNPAY/MoMo merchant keys, hash secret |
| Storage | S3/R2/Wasabi access key, secret key, bucket |
| OAuth | Google/Facebook client secret |

## 3. Tài khoản bàn giao

Tạo tài khoản admin riêng cho khách hàng, không dùng chung tài khoản developer.

| Tài khoản | Email | Vai trò | Ghi chú |
|---|---|---|---|
| Owner/Admin | Điền khi bàn giao | `admin` | Đổi mật khẩu ngay sau khi nhận |
| Staff | Điền khi bàn giao | Theo phân quyền | Chỉ cấp quyền cần thiết |
| Customer test | Điền khi bàn giao | `customer` | Dùng để smoke test checkout |

Checklist tài khoản:

| Việc | Trạng thái |
|---|:---:|
| Admin đã đổi mật khẩu mặc định | ⬜ |
| Email admin thuộc khách hàng/đội vận hành | ⬜ |
| Không còn tài khoản developer có quyền admin nếu không cần hỗ trợ | ⬜ |
| Role/permission đã kiểm tra sau deploy | ⬜ |

## 4. Tài liệu vận hành liên quan

| Chủ đề | Tài liệu |
|---|---|
| Deploy production | [`DEPLOYMENT.md`](../DEPLOYMENT.md) |
| Checklist `.env` production | [`docs/PRODUCTION_ENV_CHECKLIST.md`](PRODUCTION_ENV_CHECKLIST.md) |
| Migration production | [`docs/PRODUCTION_MIGRATION_RUNBOOK.md`](PRODUCTION_MIGRATION_RUNBOOK.md) |
| Queue worker | [`docs/QUEUE_WORKER_RUNBOOK.md`](QUEUE_WORKER_RUNBOOK.md) |
| Scheduler | [`docs/SCHEDULER_RUNBOOK.md`](SCHEDULER_RUNBOOK.md) |
| Backup database | [`docs/DATABASE_BACKUP_RUNBOOK.md`](DATABASE_BACKUP_RUNBOOK.md) |
| Backup uploaded files | [`docs/UPLOAD_BACKUP_RUNBOOK.md`](UPLOAD_BACKUP_RUNBOOK.md) |
| Git workflow | [`docs/GIT_WORKFLOW.md`](GIT_WORKFLOW.md) |
| Tiến độ kỹ thuật | [`docs/tiendo.md`](tiendo.md) |

## 5. Checklist trước khi bàn giao production

| Nhóm | Điều kiện đạt |
|---|---|
| Code | Release đã merge vào `main`, CI xanh |
| `.env` | `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://...` |
| Database | Migration đã chạy, backup trước migrate đã verify |
| Storage | `php artisan storage:link`, ảnh sản phẩm/banner/avatar/review hiển thị |
| Queue | Worker chạy bằng Supervisor/systemd, `php artisan queue:failed` không có lỗi mới |
| Scheduler | Cron/systemd timer đã bật, `php artisan schedule:list` kiểm tra được |
| Mail | Gửi được email xác nhận đơn và email cập nhật trạng thái |
| Payment | COD hoạt động, VNPAY/MoMo đã test đúng môi trường |
| Backup | DB và upload có lịch backup, có bản off-server, có restore drill |
| Monitoring | Log app/worker/scheduler có người chịu trách nhiệm theo dõi |

## 6. Vận hành hằng ngày

| Việc | Tần suất | Lệnh/điểm kiểm tra |
|---|---|---|
| Kiểm tra log lỗi app | Hằng ngày | `tail -n 100 storage/logs/laravel.log` |
| Kiểm tra queue lỗi | Hằng ngày | `php artisan queue:failed` |
| Kiểm tra backup DB | Hằng ngày | Theo `docs/DATABASE_BACKUP_RUNBOOK.md` |
| Kiểm tra backup upload | Hằng ngày | Theo `docs/UPLOAD_BACKUP_RUNBOOK.md` |
| Kiểm tra dung lượng disk | Hằng tuần | `df -h` |
| Restore drill | Hằng tháng | Restore thử DB/upload trên staging/local |
| Review tài khoản admin | Hằng tháng | Xóa tài khoản không còn dùng |

## 7. Quy trình deploy ngắn gọn

Chỉ deploy release đã pass CI và đã được review.

```bash
git fetch origin
git checkout main
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan down --retry=60
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan queue:restart
php artisan up
```

Trước khi chạy `migrate --force`, luôn làm theo [`docs/PRODUCTION_MIGRATION_RUNBOOK.md`](PRODUCTION_MIGRATION_RUNBOOK.md).

## 8. Smoke test sau deploy

| Luồng | Kỳ vọng |
|---|---|
| Trang chủ | Banner/sản phẩm hiển thị, asset không 404 |
| Tìm kiếm sản phẩm | Search/filter/sort trả kết quả đúng |
| Chi tiết sản phẩm | Ảnh, variant, giá, tồn kho hiển thị đúng |
| Giỏ hàng | Add/update/remove/select item hoạt động |
| Checkout COD | Tạo đơn, trừ kho, queue email |
| Thanh toán online | Redirect/callback đúng môi trường |
| Admin đơn hàng | Xem đơn, cập nhật trạng thái, hủy đơn |
| Báo cáo | Export đơn hàng/tồn kho không lỗi |
| Upload ảnh | Banner/avatar/review/variant hiển thị sau upload |

## 9. Xử lý sự cố cơ bản

| Sự cố | Kiểm tra đầu tiên | Tài liệu liên quan |
|---|---|---|
| Website lỗi 500 | `storage/logs/laravel.log`, `.env`, `config:cache` | `DEPLOYMENT.md` |
| Checkout chậm | Queue worker, SMTP, payment gateway | `QUEUE_WORKER_RUNBOOK.md` |
| Email không gửi | `queue:failed`, mail provider, worker log | `QUEUE_WORKER_RUNBOOK.md` |
| Ảnh 404 | `storage:link`, `APP_URL`, quyền file | `UPLOAD_BACKUP_RUNBOOK.md` |
| Migrate lỗi | Backup, migration status, rollback/fix-forward | `PRODUCTION_MIGRATION_RUNBOOK.md` |
| Mất dữ liệu | Xác định thời điểm, restore DB/upload | `DATABASE_BACKUP_RUNBOOK.md`, `UPLOAD_BACKUP_RUNBOOK.md` |
| Callback thanh toán lỗi | Payment config, log, gateway dashboard | `DEPLOYMENT.md` |

## 10. Quy tắc bảo mật sau bàn giao

| Quy tắc | Trạng thái |
|---|:---:|
| Đổi toàn bộ mật khẩu mặc định | ⬜ |
| Thu hồi quyền developer nếu không còn bảo trì | ⬜ |
| Không gửi `.env` qua chat/email thường | ⬜ |
| Bật HTTPS trước khi nhận đơn thật | ⬜ |
| Không bật `APP_DEBUG=true` trên production | ⬜ |
| Kiểm tra log không chứa secret/payment key | ⬜ |
| Backup off-server được mã hóa hoặc giới hạn quyền truy cập | ⬜ |

## 11. Biên bản bàn giao

| Trường | Giá trị |
|---|---|
| Ngày bàn giao | |
| Bên bàn giao | |
| Bên nhận | |
| Repository | |
| Production URL | |
| Admin URL | |
| Release commit/tag | |
| CI status | |
| Backup DB gần nhất | |
| Backup upload gần nhất | |
| Người chịu trách nhiệm vận hành | |
| Kênh hỗ trợ/sự cố | |

Không điền secret trực tiếp vào biên bản public. Nếu cần lưu thông tin truy cập, dùng password manager và chỉ ghi tên vault/item trong biên bản.
