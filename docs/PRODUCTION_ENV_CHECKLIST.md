# Checklist `.env` Production

Tài liệu này dùng để chuẩn bị `.env` trước khi deploy PhoneStore lên staging hoặc production. File `.env` chứa secret vận hành nên chỉ lưu trên server/secret manager, không commit lên Git.

## 1. Nguyên tắc bắt buộc

| Quy tắc | Trạng thái |
|---|:---:|
| `.env` không được commit, gửi qua chat công khai hoặc lưu trong tài liệu bàn giao không mã hóa | ⬜ |
| Mỗi môi trường dùng một `APP_KEY`, database, mail account và payment key riêng | ⬜ |
| Production luôn đặt `APP_ENV=production` và `APP_DEBUG=false` | ⬜ |
| `APP_URL` phải là domain HTTPS thật, không dùng `localhost` | ⬜ |
| Sau khi đổi `.env` production phải chạy lại `php artisan config:cache` | ⬜ |
| Secret thanh toán/mail/database phải đổi ngay nếu từng bị lộ | ⬜ |

## 2. App và bảo mật

| Biến | Production khuyến nghị | Ghi chú |
|---|---|---|
| `APP_NAME` | `PhoneStore` hoặc tên shop/khách hàng | Hiển thị trong mail, log và tiêu đề hệ thống |
| `APP_ENV` | `production` | Không dùng `local` trên server thật |
| `APP_KEY` | Sinh bằng `php artisan key:generate --show` | Bắt buộc có trước khi nhận user thật |
| `APP_DEBUG` | `false` | Tránh lộ stack trace, SQL, path server |
| `APP_URL` | `https://domain-that.com` | Dùng cho asset, mail, callback và storage public |
| `APP_LOCALE` | `vi` | Phù hợp thị trường Việt Nam |
| `APP_MAINTENANCE_DRIVER` | `file` hoặc `cache` | Dùng `cache` nếu chạy nhiều server |

Kiểm tra nhanh:

```bash
php artisan about
php artisan config:show app
```

## 3. Database

| Biến | Production khuyến nghị | Ghi chú |
|---|---|---|
| `DB_CONNECTION` | `mysql` | MySQL 8+ hoặc MariaDB tương thích |
| `DB_HOST` | IP/private hostname của DB | Không public DB ra internet nếu không cần |
| `DB_PORT` | `3306` | Chỉ mở cho app server |
| `DB_DATABASE` | Tên DB riêng cho hệ thống | Không dùng chung với app khác |
| `DB_USERNAME` | User DB riêng | Không dùng `root` |
| `DB_PASSWORD` | Mật khẩu mạnh | Lưu trong secret manager nếu có |
| `DB_CHARSET` | `utf8mb4` | Hỗ trợ tiếng Việt và emoji |
| `DB_COLLATION` | `utf8mb4_unicode_ci` | Đồng bộ với migration hiện tại |

Kiểm tra nhanh:

```bash
php artisan migrate:status
php artisan db:show
```

## 4. Cache, session và queue

| Biến | Production khuyến nghị | Ghi chú |
|---|---|---|
| `CACHE_STORE` | `database` hoặc `redis` | Redis tốt hơn khi traffic tăng |
| `QUEUE_CONNECTION` | `database` hoặc `redis` | Không dùng `sync` vì email/order event sẽ làm chậm request |
| `QUEUE_FAILED_DRIVER` | `database-uuids` | Cần để truy vết job lỗi |
| `SESSION_DRIVER` | `database` hoặc `redis` | Không dùng `array` trên production |
| `SESSION_LIFETIME` | `120` | Có thể tăng cho admin nội bộ |
| `SESSION_ENCRYPT` | `true` nếu cần bảo mật cao | Cân nhắc chi phí CPU nhỏ |
| `SESSION_SECURE_COOKIE` | `true` | Bắt buộc khi chạy HTTPS |
| `SESSION_SAME_SITE` | `lax` | Phù hợp form checkout và CSRF |

Kiểm tra nhanh:

```bash
php artisan queue:failed
php artisan queue:work --once
```

## 5. Mail giao dịch

| Biến | Production khuyến nghị | Ghi chú |
|---|---|---|
| `MAIL_MAILER` | `smtp`, `ses`, `postmark` hoặc `resend` | Chọn nhà cung cấp có tracking/bounce |
| `MAIL_HOST` | SMTP host thật | Không dùng Mailtrap production |
| `MAIL_PORT` | `587` hoặc theo provider | Ưu tiên TLS |
| `MAIL_USERNAME` | Tài khoản gửi mail | Secret |
| `MAIL_PASSWORD` | Mật khẩu/API key | Secret |
| `MAIL_FROM_ADDRESS` | `no-reply@domain-that.com` | Nên trùng domain đã xác thực SPF/DKIM |
| `MAIL_FROM_NAME` | `"${APP_NAME}"` | Tên shop/brand |

Kiểm tra nhanh:

```bash
php artisan tinker
Mail::raw('Production mail test', fn ($m) => $m->to('admin@example.com')->subject('PhoneStore mail test'));
```

## 6. Thanh toán

### VNPAY

| Biến | Production khuyến nghị | Ghi chú |
|---|---|---|
| `VNPAY_TMN_CODE` | Mã merchant production | Secret |
| `VNPAY_HASH_SECRET` | Hash secret production | Secret, không dùng sandbox |
| `VNPAY_URL` | URL production từ VNPAY | Không để sandbox khi bán thật |
| `VNPAY_RETURN_URL` | `/thanh-toan/vnpay-callback` | Domain đầy đủ được dựng từ `APP_URL` |

### MoMo

| Biến | Production khuyến nghị | Ghi chú |
|---|---|---|
| `MOMO_PARTNER_CODE` | Partner code production | Secret |
| `MOMO_ACCESS_KEY` | Access key production | Secret |
| `MOMO_SECRET_KEY` | Secret key production | Secret |
| `MOMO_ENDPOINT` | Endpoint production từ MoMo | Không dùng test endpoint |
| `MOMO_RETURN_URL` | `/thanh-toan/momo-callback` | Cần khớp cấu hình merchant |

Trước khi bật production payment:

| Kiểm tra | Trạng thái |
|---|:---:|
| Callback domain dùng HTTPS và public được từ cổng thanh toán | ⬜ |
| Đơn thanh toán online chỉ trừ kho một lần khi callback lặp | ⬜ |
| Log thanh toán không ghi full secret/hash key | ⬜ |
| Có tài khoản test/sandbox riêng để kiểm tra trước release | ⬜ |

## 7. OAuth đăng nhập xã hội

| Biến | Production khuyến nghị | Ghi chú |
|---|---|---|
| `GOOGLE_CLIENT_ID` | Client ID production | Bỏ trống nếu chưa bật Google login |
| `GOOGLE_CLIENT_SECRET` | Client secret production | Secret |
| `GOOGLE_REDIRECT_URL` | `https://domain-that.com/auth/google/callback` | Phải khớp Google Console |
| `FACEBOOK_CLIENT_ID` | App ID production | Bỏ trống nếu chưa bật Facebook login |
| `FACEBOOK_CLIENT_SECRET` | App secret production | Secret |
| `FACEBOOK_REDIRECT_URL` | `https://domain-that.com/auth/facebook/callback` | Phải khớp Facebook App |

## 8. Storage upload

| Biến | Production khuyến nghị | Ghi chú |
|---|---|---|
| `FILESYSTEM_DISK` | `local` hoặc `s3` | `local` cần backup `storage/app/public` |
| `AWS_ACCESS_KEY_ID` | Chỉ điền nếu dùng S3 | Secret |
| `AWS_SECRET_ACCESS_KEY` | Chỉ điền nếu dùng S3 | Secret |
| `AWS_DEFAULT_REGION` | Region bucket | Ví dụ `ap-southeast-1` |
| `AWS_BUCKET` | Bucket upload | Không public write |
| `AWS_URL` | CDN/custom domain nếu có | Tùy provider |
| `AWS_ENDPOINT` | Endpoint S3-compatible nếu có | Dùng cho MinIO/Wasabi/R2 |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `false` hoặc theo provider | R2/MinIO có thể cần `true` |

Kiểm tra nhanh:

```bash
php artisan storage:link
```

## 9. Logging và giám sát

| Biến | Production khuyến nghị | Ghi chú |
|---|---|---|
| `LOG_CHANNEL` | `stack` | Có thể thêm Slack/Sentry sau |
| `LOG_STACK` | `daily` hoặc `single` | `daily` dễ rotate hơn |
| `LOG_LEVEL` | `warning` hoặc `error` | Không dùng `debug` production lâu dài |

Kiểm tra nhanh:

```bash
tail -f storage/logs/laravel.log
php artisan queue:failed
```

## 10. Checklist trước khi bàn giao

| Nhóm | Điều kiện đạt |
|---|---|
| App | `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://...` |
| Database | User DB riêng, không dùng `root`, backup trước migrate |
| Queue | Worker chạy nền và có lệnh restart sau deploy |
| Mail | Gửi được mail xác nhận đơn, SPF/DKIM đã cấu hình |
| Payment | VNPAY/MoMo dùng key đúng môi trường, callback HTTPS hoạt động |
| Storage | Upload ảnh hiển thị được sau `storage:link`, có kế hoạch backup |
| Security | Không có secret trong Git, log không lộ key, session cookie secure |
| CI | Branch release đã pass GitHub Actions |

Sau khi hoàn tất checklist:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```
