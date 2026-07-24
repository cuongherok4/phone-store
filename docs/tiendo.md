# 📱 PhoneStore - Theo Dõi Tiến Độ Hệ Thống

> **Cập nhật lần cuối:** 23/07/2026
> **Mục tiêu:** Xây dựng hệ thống thương mại điện tử bán điện thoại có thể triển khai thật, bàn giao cho khách hàng/doanh nghiệp sử dụng, có quy trình vận hành rõ ràng, dễ bảo trì và có khả năng mở rộng theo nhu cầu kinh doanh.

---

## 📊 Quy Ước Trạng Thái

| Màu | Trạng thái | Ý nghĩa |
|---|---|---|
| 🟢 `Done` | Đã hoàn thành | Đã hoàn thành và có thể dùng |
| 🟡 `Doing` | Đang hoàn thiện | Đang hoàn thiện / cần rà lại |
| 🔴 `Todo` | Chưa làm | Chưa làm |
| ⚪ `Hold` | Tạm hoãn | Tạm hoãn hoặc chỉ làm khi có nhu cầu triển khai |

---

## 📈 Tổng Quan Tiến Độ

| Giai đoạn | Nội dung | Trạng thái | Tiến độ |
|---|---|:---:|:---:|
| **GĐ 1** | Nền tảng hệ thống | 🟢 Done | **100%** |
| **GĐ 2** | Quản trị sản phẩm và danh mục | 🟢 Done | **100%** |
| **GĐ 3** | Luồng mua hàng cho khách | 🟢 Done | **100%** |
| **GĐ 4** | Tài khoản, đánh giá, yêu thích, thông báo | 🟢 Done | **100%** |
| **GĐ 5** | Quản trị vận hành shop | 🟢 Done | **100%** |
| **GĐ 6** | Thanh toán, hóa đơn, báo cáo | 🟡 Doing | **90%** |
| **GĐ 7** | Hiệu năng và tối ưu truy vấn | 🟡 Doing | **80%** |
| **GĐ 8** | Bảo mật và phân quyền | 🟡 Doing | **70%** |
| **GĐ 9** | Kiểm thử tự động | 🟡 Doing | **60%** |
| **GĐ 10** | CI/CD và triển khai thật | 🟡 Doing | **45%** |

### 💡 Đánh giá hiện tại
Hệ thống đã có đầy đủ khung chức năng của một website bán điện thoại thực tế. Phần cần ưu tiên tiếp theo là **bảo mật luồng tiền**, **kiểm thử**, **tối ưu độ trễ**, **tài liệu deploy** và **quy trình vận hành** sau bàn giao.

---

## 📝 Cách Đọc Mã Công Việc Và Thực Thi

### Cấu trúc mã công việc
```
<giai đoạn>.<số thứ tự>
```

**Ví dụ:**
- `6.1` = GĐ 6 - Thanh toán, hóa đơn và báo cáo - Thanh toán COD
- `8.7` = GĐ 8 - Bảo mật và phân quyền - Coupon validation đầy đủ
- `10.4` = GĐ 10 - CI/CD, deploy và bàn giao - Tài liệu deploy

### Quy trình thực thi khi có yêu cầu "thực hiện 6.1"

1. ✅ Đọc đúng dòng công việc `6.1` trong tài liệu này
2. ✅ Xác định giai đoạn/module bằng bảng bên dưới
3. ✅ Checkout từ `develop`
4. ✅ Tạo branch theo quy ước của giai đoạn
5. ✅ Rà code liên quan, thực hiện thay đổi, cập nhật test/docs nếu cần
6. ✅ Commit bằng tiếng Anh theo conventional commit
7. ✅ Push branch hoặc merge vào `develop` khi được yêu cầu

---

## 🔧 Bảng Module Và Branch Mặc Định

| Giai đoạn | Module | Branch mặc định | Commit type |
|---|---|---|---|
| **GĐ 1** | Nền tảng hệ thống | `chore/project-foundation` | `chore`, `refactor` |
| **GĐ 2** | Quản trị sản phẩm/danh mục | `feature/admin-catalog` | `feat`, `fix`, `refactor` |
| **GĐ 3** | Luồng mua hàng | `feature/customer-checkout` | `feat`, `fix`, `test` |
| **GĐ 4** | Tài khoản và tương tác khách | `feature/customer-account` | `feat`, `fix`, `test` |
| **GĐ 5** | Quản trị vận hành shop | `feature/admin-operations` | `feat`, `fix`, `refactor` |
| **GĐ 6** | Thanh toán, hóa đơn, báo cáo | `feature/payment-reporting` | `feat`, `fix`, `test` |
| **GĐ 7** | Hiệu năng và giảm độ trễ | `perf/performance-optimization` | `perf`, `refactor`, `test` |
| **GĐ 8** | Bảo mật và phân quyền | `fix/security-hardening` | `fix`, `feat`, `test` |
| **GĐ 9** | Kiểm thử tự động | `test/core-business-flows` | `test` |
| **GĐ 10** | CI/CD, deploy và bàn giao | `docs/deployment-operations` | `docs`, `ci`, `chore` |

---

## ⚡ Thứ Tự Ưu Tiên Khi Làm Tiếp

| Phase | Mục tiêu | Mã công việc | Branch |
|---|---|---|---|
| **Phase 1** | Siết bảo mật luồng tiền và dữ liệu user | `8.7`, `8.3`, `8.4`, `8.5`, `8.6`, `6.4` | `fix/security-hardening` |
| **Phase 2** | Bảo vệ nghiệp vụ bằng test | `9.1`, `9.2`, `9.3`, `9.5`, `9.6`, `9.7`, `9.8` | `test/core-business-flows` |
| **Phase 3** | Giảm độ trễ khi vận hành thật | `7.8`, `7.6`, `7.7`, `7.9`, `7.10` | `perf/performance-optimization` |
| **Phase 4** | Chuẩn hóa deploy và bàn giao | `10.4`, `10.5`, `10.6`, `10.7`, `10.8`, `10.9`, `10.10`, `10.12` | `docs/deployment-operations` |
| **Phase 5** | Release ổn định | `10.11` | `release/v1.0.0` |

---

## 🎯 Quy Ước Commit

| Loại việc | Format commit |
|---|---|
| Tính năng mới | `feat(scope): short description` |
| Sửa lỗi | `fix(scope): short description` |
| Tối ưu hiệu năng | `perf(scope): short description` |
| Refactor | `refactor(scope): short description` |
| Test | `test(scope): short description` |
| CI/CD | `ci(scope): short description` |
| Tài liệu | `docs(scope): short description` |
| Việc nền tảng | `chore(scope): short description` |

**Ví dụ:**
```bash
git commit -m "fix(coupon): enforce usage limits during checkout"
git commit -m "test(order): cover stock deduction for COD checkout"
git commit -m "docs(deploy): add production environment checklist"
```

---

## 1️⃣ Giai Đoạn 1 - Nền Tảng Hệ Thống

| # | Hạng mục | Trạng thái | Ghi chú |
|---|---|:---:|---|
| 1.1 | Laravel 12, PHP 8.2+, Composer, Vite | 🟢 | Nền tảng backend/frontend đã sẵn sàng |
| 1.2 | Cấu trúc route customer/admin riêng | 🟢 | `web.php`, `admin.php` |
| 1.3 | Service Layer cho nghiệp vụ chính | 🟢 | Cart, Order, Inventory, Payment, Review |
| 1.4 | MySQL schema cho hệ thống bán hàng | 🟢 | Có SQL mẫu và migrations |
| 1.5 | Models và relationships | 🟢 | Product, Variant, Order, Inventory, Coupon... |
| 1.6 | Seeder dữ liệu mẫu | 🟢 | Admin, brand, warehouse, product |
| 1.7 | Cấu hình môi trường `.env.example` | 🟢 | Cần mở rộng thêm production checklist |
| 1.8 | Git Flow: `main`, `develop` | 🟢 | `main` ổn định, `develop` tích hợp |

---

## 2️⃣ Giai Đoạn 2 - Quản Trị Sản Phẩm Và Danh Mục

| # | Hạng mục | Trạng thái | Ghi chú |
|---|---|:---:|---|
| 2.1 | Layout admin | 🟢 | Sidebar, topbar, giao diện quản trị |
| 2.2 | Quản lý thương hiệu | 🟢 | Thêm/sửa/xóa, logo, slug |
| 2.3 | Quản lý sản phẩm | 🟢 | CRUD, soft delete, thông số kỹ thuật |
| 2.4 | Quản lý biến thể sản phẩm | 🟢 | SKU, giá, giá so sánh, trạng thái |
| 2.5 | Thuộc tính biến thể | 🟢 | Màu, RAM/ROM hoặc thuộc tính mở rộng |
| 2.6 | Ảnh sản phẩm/biến thể | 🟢 | Ảnh chính, thứ tự hiển thị |
| 2.7 | Quản lý banner | 🟢 | Banner chính/phụ cho trang chủ |
| 2.8 | Dọn file Blade sai vị trí | 🟢 | Không để view trong Controller/Request |

---

## 3️⃣ Giai Đoạn 3 - Luồng Mua Hàng Cho Khách

| # | Hạng mục | Trạng thái | Ghi chú |
|---|---|:---:|---|
| 3.1 | Trang chủ | 🟢 | Banner, sản phẩm mới, sản phẩm nổi bật |
| 3.2 | Danh sách sản phẩm | 🟢 | Tìm kiếm, lọc thương hiệu, lọc giá, sắp xếp |
| 3.3 | Chi tiết sản phẩm | 🟢 | Gallery, chọn biến thể, giá, tồn kho |
| 3.4 | Giỏ hàng lưu DB | 🟢 | Hỗ trợ chọn từng item để checkout |
| 3.5 | Checkout | 🟢 | Địa chỉ, phí ship, giảm giá, phương thức thanh toán |
| 3.6 | Tạo đơn hàng | 🟢 | Dùng transaction, lưu order và order_items |
| 3.7 | Trừ kho khi đặt hàng | 🟢 | Dùng InventoryService, có log kho |
| 3.8 | Mua ngay | 🟢 | Đã sửa lưu đủ order item và trừ kho đúng service |
| 3.9 | Kiểm tra tồn kho trước thanh toán | 🟢 | Kiểm tra trong transaction, khóa dòng kho để tránh oversell khi nhiều checkout đồng thời |

---

## 4️⃣ Giai Đoạn 4 - Tài Khoản Và Tương Tác Khách Hàng

| # | Hạng mục | Trạng thái | Ghi chú |
|---|---|:---:|---|
| 4.1 | Đăng ký, đăng nhập, đăng xuất | 🟢 | Auth cơ bản |
| 4.2 | Đăng nhập Google | 🟢 | Laravel Socialite |
| 4.3 | Hồ sơ người dùng | 🟢 | Cập nhật thông tin, avatar |
| 4.4 | Sổ địa chỉ | 🟢 | Thêm/xóa/đặt mặc định |
| 4.5 | Lịch sử đơn hàng | 🟢 | Khách xem đơn đã mua |
| 4.6 | Hủy đơn | 🟢 | Khách chỉ hủy được đơn của chính mình, service kiểm tra owner trước khi cập nhật |
| 4.7 | Wishlist | 🟢 | Toggle AJAX, danh sách yêu thích |
| 4.8 | Đánh giá sản phẩm | 🟢 | Chỉ người đã mua được đánh giá |
| 4.9 | Thông báo | 🟢 | Danh sách, đánh dấu đã đọc |

---

## 5️⃣ Giai Đoạn 5 - Quản Trị Vận Hành Shop

| # | Hạng mục | Trạng thái | Ghi chú |
|---|---|:---:|---|
| 5.1 | Dashboard admin | 🟢 | Doanh thu, đơn hàng, tồn kho, biểu đồ |
| 5.2 | Quản lý đơn hàng | 🟢 | Lọc, xem chi tiết, cập nhật trạng thái |
| 5.3 | Hủy đơn và hoàn kho | 🟢 | Admin hủy đơn qua OrderService, hoàn kho nếu đã trừ tồn, log kho dùng `RETURN` |
| 5.4 | Quản lý kho nhiều warehouse | 🟢 | Nhập/xuất/điều chỉnh tồn kho |
| 5.5 | Lịch sử tồn kho | 🟢 | Ghi log thay đổi số lượng |
| 5.6 | Quản lý nhà cung cấp | 🟢 | Danh sách, thêm/sửa/xóa |
| 5.7 | Quản lý coupon | 🟢 | Có CouponService, validate giới hạn dùng, tính giảm giá server-side, ghi lịch sử sử dụng |
| 5.8 | Quản lý review | 🟢 | Duyệt/xóa review, sync rating sản phẩm |
| 5.9 | Quản lý người dùng | 🟢 | Danh sách, bật/tắt trạng thái |
| 5.10 | Cấu hình hệ thống | 🟢 | Settings cho thông tin shop/thanh toán |

---

## 6️⃣ Giai Đoạn 6 - Thanh Toán, Hóa Đơn Và Báo Cáo

| # | Hạng mục | Trạng thái | Ghi chú |
|---|---|:---:|---|
| 6.1 | Thanh toán COD | 🟢 | Luồng mặc định |
| 6.2 | Thanh toán VNPAY | 🟢 | Có callback, cần kiểm thử sandbox kỹ |
| 6.3 | Thanh toán MoMo | 🟢 | Có cấu hình payment |
| 6.4 | Xác nhận thanh toán online | 🟢 | Callback khóa dòng order trong transaction, chỉ trừ kho/log/gửi mail ở lần xác nhận đầu tiên |
| 6.5 | In hóa đơn PDF | 🟢 | Admin in hóa đơn |
| 6.6 | Export đơn hàng Excel | 🟢 | Phục vụ báo cáo vận hành |
| 6.7 | Export tồn kho Excel | 🟢 | Phục vụ kiểm kho |
| 6.8 | Email xác nhận đơn | 🟢 | Có Mailable, cần chuyển qua queue |
| 6.9 | Email cập nhật trạng thái | 🟢 | Có Mailable, cần chuyển qua queue |

---

## 7️⃣ Giai Đoạn 7 - Hiệu Năng Và Giảm Độ Trễ

| # | Hạng mục | Trạng thái | Ghi chú |
|---|---|:---:|---|
| 7.1 | Eager loading tránh N+1 | 🟢 | Đã áp dụng ở nhiều màn hình chính |
| 7.2 | Index cho truy vấn phổ biến | 🟢 | Có migration tối ưu index |
| 7.3 | Cache cột rating sản phẩm | 🟢 | `avg_rating`, `review_count` |
| 7.4 | Cache tổng tồn kho variant | 🟢 | `total_stock` |
| 7.5 | Bỏ `withAvg/withCount` ở listing | 🟢 | Listing dùng cột cache |
| 7.6 | Cache homepage | 🟢 | Cache banner, thương hiệu, sản phẩm mới/nổi bật; admin cập nhật dữ liệu sẽ tự xóa cache |
| 7.7 | Cache dashboard admin | 🟡 | Có cache một phần, cần chuẩn hóa TTL |
| 7.8 | Queue gửi mail | 🟢 | Order confirmation/status mail dùng queue để giảm độ trễ request |
| 7.9 | Tối ưu related products | 🔴 | Tránh `inRandomOrder()` trực tiếp khi dữ liệu lớn |
| 7.10 | Tối ưu tìm kiếm | 🔴 | Scout/Meilisearch hoặc fulltext tùy deploy |

---

## 8️⃣ Giai Đoạn 8 - Bảo Mật Và Phân Quyền

| # | Hạng mục | Trạng thái | Ghi chú |
|---|---|:---:|---|
| 8.1 | Middleware admin | 🟢 | Đã có bảo vệ route admin |
| 8.2 | Phân quyền theo role | 🟡 | Có Spatie, cần thống nhất dùng role/permission |
| 8.3 | Policy xem đơn hàng | 🟢 | Có `OrderPolicy::view`, khách chỉ xem đơn của mình, admin xem được mọi đơn |
| 8.4 | Policy hủy đơn hàng | 🟢 | Có `OrderPolicy::cancel`, chỉ chủ đơn/admin hủy được đơn ở trạng thái hợp lệ |
| 8.5 | Rate limit login | 🟢 | Giới hạn 5 lần sai/phút theo email + IP, đăng nhập đúng sẽ xóa bộ đếm |
| 8.6 | Rate limit checkout/coupon | 🟢 | Checkout 6 lần/phút, check coupon 20 lần/phút theo user/IP |
| 8.7 | Coupon validation đầy đủ | 🟢 | Active, thời gian, tổng lượt, lượt/user, đơn tối thiểu, tính giảm giá server-side, chống ghi trùng usage theo order |
| 8.8 | Idempotent payment callback | 🟢 | Đã có khóa order, payment record idempotent theo mã giao dịch và test service |
| 8.9 | Không commit secret | 🟢 | `.env` ignore, dùng `.env.example` |
| 8.10 | Kiểm tra file upload | 🔴 | Ràng buộc mime/size và lưu storage an toàn |

---

## 9️⃣ Giai Đoạn 9 - Kiểm Thử Tự Động

| # | Hạng mục | Trạng thái | Ghi chú |
|---|---|:---:|---|
| 9.1 | Xóa/đổi ExampleTest mặc định | 🔴 | Test hiện tại chưa có giá trị thật |
| 9.2 | Test InventoryService | 🟢 | Đã cover kiểm tra tồn kho, import, deduct, restore, adjust và rollback khi thiếu hàng |
| 9.3 | Test OrderService | 🟢 | Đã cover tạo đơn COD/online, trừ kho, hủy đơn, hoàn kho và xác nhận thanh toán idempotent |
| 9.4 | Test CouponService | 🟢 | Đã có test validate, calculate, record usage |
| 9.5 | Test Cart flow | 🔴 | Add/update/remove/select items |
| 9.6 | Test Checkout flow | 🟢 | Đã có feature test cho đặt đơn COD, chặn thiếu tồn kho và check coupon server-side |
| 9.7 | Test quyền xem đơn | 🔴 | Chống xem đơn người khác |
| 9.8 | CI chạy `php artisan test` | 🔴 | Bật sau khi schema test ổn định |

---

## 🔟 Giai Đoạn 10 - CI/CD, Deploy Và Bàn Giao

| # | Hạng mục | Trạng thái | Ghi chú |
|---|---|:---:|---|
| 10.1 | GitHub Actions CI | 🟢 | PHP check, route list, frontend build |
| 10.2 | Build frontend production | 🟢 | `npm run build` |
| 10.3 | Quy trình branch | 🟢 | `main`, `develop`, `feature/*`, `fix/*`, `hotfix/*` |
| 10.4 | Tài liệu deploy | 🔴 | Cần `DEPLOYMENT.md` |
| 10.5 | Checklist `.env` production | 🔴 | APP_ENV, APP_DEBUG, DB, MAIL, PAYMENT |
| 10.6 | Quy trình migrate production | 🔴 | Backup trước migrate, rollback khi lỗi |
| 10.7 | Queue worker | 🔴 | Cần hướng dẫn Supervisor/systemd |
| 10.8 | Scheduler | 🔴 | Cần cron cho Laravel schedule |
| 10.9 | Backup database | 🔴 | Dump DB theo ngày/tuần |
| 10.10 | Backup uploaded files | 🔴 | Storage/public uploads |
| 10.11 | Release từ `develop` sang `main` | 🔴 | Chỉ merge khi CI xanh và đã review |
| 10.12 | Tài liệu bàn giao khách hàng | 🔴 | Tài khoản admin, cách vận hành, cách backup |

---

## 🎯 Các Việc Ưu Tiên Tiếp Theo

### Phase 1: Bảo Mật Luồng Tiền (P0)

| Mã | Việc | Lý do |
|---|---|---|
| **8.7** | Tách `CouponService` và siết validation | Liên quan trực tiếp đến tiền/giảm giá |
| **8.3, 8.4** | Thêm policy cho order | Tránh lỗi bảo mật nghiêm trọng |
| **8.5, 8.6** | Thêm rate limit login/checkout/coupon | Chống spam và abuse |
| **6.4** | Xác nhận thanh toán online idempotent | Đã hoàn thành, tiếp tục kiểm thử sandbox gateway khi có credentials thật |

### Phase 2: Test Nghiệp Vụ Cốt Lõi (P0)

| Mã | Việc | Lý do |
|---|---|---|
| **9.2, 9.3, 9.6** | Viết test cho checkout và inventory | Bảo vệ luồng bán hàng cốt lõi |

### Phase 3: Hiệu Năng (P1)

| Mã | Việc | Lý do |
|---|---|---|
| **7.7** | Chuẩn hóa cache dashboard | Cải thiện tốc độ khi có traffic admin |
| **7.9** | Tối ưu related products | Giảm truy vấn nặng khi dữ liệu sản phẩm tăng |
| **7.10** | Tối ưu search | Cần khi dữ liệu sản phẩm tăng |

### Phase 4: Deploy (P1)

| Mã | Việc | Lý do |
|---|---|---|
| **10.4, 10.5** | Viết `DEPLOYMENT.md` và checklist `.env` | Cần cho hệ thống đem bán/bàn giao |
| **10.6, 10.9, 10.10** | Chuẩn hóa backup/rollback | Cần trước khi deploy thật |

---

## ✅ Tiêu Chuẩn Hoàn Thành Trước Khi Bán/Bàn Giao

Một chức năng chỉ được xem là hoàn thành khi:

- ✔️ Code ngắn gọn, dễ đọc, không lặp logic không cần thiết
- ✔️ Controller mỏng, nghiệp vụ chính nằm trong Service/Form Request
- ✔️ Có kiểm tra quyền truy cập với dữ liệu thuộc user
- ✔️ Có validate đầy đủ dữ liệu đầu vào
- ✔️ Không làm chậm request bằng tác vụ có thể chạy nền
- ✔️ Không có `dd()`, `dump()`, `var_dump()`, secret hoặc file local
- ✔️ Migration không tự xóa dữ liệu vận hành nếu chưa có backup
- ✔️ Có test cho luồng nghiệp vụ quan trọng
- ✔️ CI chạy xanh
- ✔️ Có hướng dẫn vận hành/deploy nếu ảnh hưởng production

---

## 🚀 Giá Trị Kỹ Thuật Nổi Bật

### Tóm tắt hệ thống

Xây dựng hệ thống thương mại điện tử bán điện thoại bằng **Laravel 12**, gồm:
- Storefront cho khách hàng
- Dashboard quản trị
- Quản lý kho nhiều chi nhánh
- Thanh toán online (VNPAY, MoMo)
- Hóa đơn PDF
- Export báo cáo
- CI/CD và tối ưu hiệu năng truy vấn

### Các điểm kỹ thuật chính

1. **Service Layer** cho giỏ hàng, đơn hàng, thanh toán, kho và báo cáo
2. **Transaction** cho tạo đơn và trừ kho trong DB
3. **Multi-warehouse** quản lý tồn kho với lịch sử import/export/adjust
4. **Payment Gateway** tích hợp VNPAY/MoMo với callback + xác nhận
5. **Performance** tối ưu: eager loading, composite index, cached columns
6. **Git Flow** với `develop`, CI GitHub Actions
7. **Production Roadmap** bảo mật, test, queue, cache, backup, rollback, deploy

---

## 📅 Nhật Ký Cập Nhật

| Ngày | Nội dung |
|---|---|
| 23/07/2026 | Tạo và push nhánh `develop` |
| 23/07/2026 | Thêm GitHub Actions CI cho PHP check và frontend build |
| 23/07/2026 | Thêm migration tối ưu index và cache columns |
| 23/07/2026 | Sửa lỗi lưu `subtotal` order item và trừ kho luồng mua ngay |
| 23/07/2026 | Viết lại tài liệu tiến độ theo hướng hệ thống thật có thể deploy/bàn giao |
| 23/07/2026 | Hoàn thành mục `3.9`: kiểm tra tồn kho trước thanh toán và chống oversell khi checkout đồng thời |
| 23/07/2026 | Hoàn thành mục `4.6`: kiểm tra quyền sở hữu khi khách hủy đơn hàng |
| 23/07/2026 | Hoàn thành mục `5.3`: admin hủy đơn dùng service chung và hoàn kho bằng log `RETURN` |
| 23/07/2026 | Hoàn thành mục `5.7`: tách CouponService, áp dụng coupon server-side và thêm test |
| 23/07/2026 | Siết thêm mục `8.7`: chống coupon usage trùng order, validate user/subtotal và bổ sung unique index |
| 23/07/2026 | Hoàn thành mục `8.3`: thêm OrderPolicy cho quyền xem đơn hàng |
| 23/07/2026 | Hoàn thành mục `8.4`: thêm policy hủy đơn hàng cho khách và admin |
| 23/07/2026 | Hoàn thành mục `8.5`: thêm rate limit login chống brute force |
| 23/07/2026 | Hoàn thành mục `8.6`: thêm rate limit cho checkout và check coupon |
| 23/07/2026 | Hoàn thành mục `6.4`: xác nhận thanh toán online idempotent, tránh trừ kho/log/mail trùng khi callback lặp |
| 23/07/2026 | Hoàn thành mục `7.6`: cache dữ liệu homepage và tự xóa cache khi admin cập nhật banner/sản phẩm/thương hiệu |
| 23/07/2026 | Hoàn thành mục `9.2`: bổ sung test InventoryService cho import/deduct/restore/adjust |
| 23/07/2026 | Hoàn thành mục `9.3`: bổ sung test OrderService cho COD/online checkout, cancel và stock deduction |
| 23/07/2026 | Hoàn thành mục `9.6`: bổ sung feature test cho checkout process, tồn kho và coupon |
| 24/07/2026 | Hoàn thành mục `7.8`: chuyển email xác nhận đơn/cập nhật trạng thái sang queue |

---

**Tài liệu này được dùng để theo dõi, quản lý và giao tiếp tiến độ phát triển hệ thống PhoneStore. Cập nhật thường xuyên khi có tiến triển.**
