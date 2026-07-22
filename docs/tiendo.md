# 📱 Phone Store — Theo Dõi Tiến Độ

> **Cập nhật lần cuối:** 23/07/2026
> **Quy ước:** ✅ Hoàn thành | 🔄 Đang làm | ⬜ Chưa làm | ❌ Bỏ qua

---

## 📊 Tổng Quan Tiến Độ

```
Giai đoạn 1 — Nền tảng & Setup          [✅ 100%]  ██████████
Giai đoạn 2 — Admin CRUD                 [✅ 100%]  ██████████
Giai đoạn 3 — Luồng mua hàng            [✅ 100%]  ██████████
Giai đoạn 4 — Tính năng bổ sung         [✅ 100%]  ██████████
Giai đoạn 5 — Admin nâng cao            [✅  95%]  █████████░
Giai đoạn 6 — Hoàn thiện & Thanh toán   [✅  90%]  █████████░
Giai đoạn 7 — Tối ưu (mới)              [⬜   0%]  ░░░░░░░░░░
─────────────────────────────────────────────────────────────
TỔNG                                     [~  84%]  ████████░░
```

---

## ✅ GIAI ĐOẠN 1 — NỀN TẢNG

| # | Hạng mục | Trạng thái |
|---|----------|:----------:|
| 1.1 | Tạo project Laravel 12, cài packages (spatie, intervention, scout, dompdf, excel) | ✅ |
| 1.2 | Cấu hình `.env` (DB, Mail, Storage, Payment) | ✅ |
| 1.3 | Import `phone_store_v2_updated.sql` vào MySQL | ✅ |
| 1.4 | 26 Eloquent Models + Relationships đầy đủ | ✅ |
| 1.5 | Seeders: User (admin), Warehouse, Brand, Product | ✅ |
| 1.6 | Auth: Register / Login / Logout / Forgot Password | ✅ |
| 1.7 | AdminMiddleware + route admin.php với prefix `/admin` | ✅ |
| 1.8 | Social Login (Socialite — Google) | ✅ |

---

## ✅ GIAI ĐOẠN 2 — ADMIN CRUD

| # | Hạng mục | Trạng thái |
|---|----------|:----------:|
| 2.1 | Layout admin (sidebar, topbar, breadcrumb) | ✅ |
| 2.2 | CRUD Thương hiệu (upload logo, slug) | ✅ |
| 2.3 | CRUD Danh mục (phân cấp cha/con, upload ảnh) | ✅ |
| 2.4 | CRUD Sản phẩm (soft delete, specifications) | ✅ |
| 2.5 | CRUD Biến thể (SKU, giá, compare_price, attributes) | ✅ |
| 2.6 | Upload ảnh variant (primary, sort_order, resize) | ✅ |
| 2.7 | Quản lý Nhà cung cấp | ✅ |
| 2.8 | Coupon: Danh sách, Tạo/Sửa/Xóa | ✅ |

---

## ✅ GIAI ĐOẠN 3 — LUỒNG MUA HÀNG (CUSTOMER)

| # | Hạng mục | Trạng thái |
|---|----------|:----------:|
| 3.1 | Layout customer (header mega menu, footer) | ✅ |
| 3.2 | Trang chủ: Banner slider, sản phẩm nổi bật | ✅ |
| 3.3 | Danh sách sản phẩm: filter, sort, phân trang | ✅ |
| 3.4 | Chi tiết sản phẩm: Alpine.js chọn variant, gallery | ✅ |
| 3.5 | CartService + Giỏ hàng (DB-based, merge guest) | ✅ |
| 3.6 | Checkout: chọn địa chỉ, coupon, phương thức | ✅ |
| 3.7 | OrderService: tạo đơn trong DB Transaction, trừ kho | ✅ |

---

## ✅ GIAI ĐOẠN 4 — TÍNH NĂNG BỔ SUNG

| # | Hạng mục | Trạng thái |
|---|----------|:----------:|
| 4.1 | Wishlist (toggle AJAX, trang danh sách) | ✅ |
| 4.2 | Review: form 5 sao, upload ảnh, validate đã mua | ✅ |
| 4.3 | Notification: badge, danh sách, đánh dấu đã đọc | ✅ |
| 4.4 | Profile: sửa thông tin, avatar, quản lý địa chỉ | ✅ |

---

## 🔄 GIAI ĐOẠN 5 — ADMIN NÂNG CAO

| # | Hạng mục | Trạng thái |
|---|----------|:----------:|
| 5.1 | Dashboard: doanh thu, đơn hàng, Chart.js | ✅ |
| 5.2 | Kho hàng: xem tồn kho, nhập hàng, lịch sử log | ✅ |
| 5.3 | Quản lý đơn hàng: lọc, cập nhật trạng thái, hủy | ✅ |
| 5.4 | Duyệt đánh giá + Quản lý người dùng | ✅ |
| 5.5 | Báo cáo & Export Excel | ✅ |
| 5.6 | In hóa đơn PDF | ✅ |
| 5.7 | Quản lý Banner | ✅ |
| 5.8 | Cấu hình hệ thống (Settings) | ✅ |

---

## 🔄 GIAI ĐOẠN 6 — HOÀN THIỆN

| # | Hạng mục | Trạng thái | Branch thực hiện | Lệnh Commit đề xuất |
|---|----------|:----------:|------------------|---------------------|
| 6.1 | Mail: OrderConfirmation + OrderStatusChanged | ✅ | - | - |
| 6.2 | Tích hợp VNPAY | ✅ | - | - |
| 6.3 | Tích hợp MoMo | ✅ | - | - |
| 6.4 | Eager loading chuẩn (không N+1) | ✅ | - | - |
| 6.5 | AI Consult (AIController) | ✅ | - | - |
| 6.6 | Policy: OrderPolicy (chỉ owner xem đơn) | ⬜ | `feature/order-policy` | `git commit -m "feat(auth): add OrderPolicy to restrict order viewing"` |
| 6.7 | Rate limiting cho login / checkout | ⬜ | `feature/rate-limit` | `git commit -m "feat(security): add throttle middleware for login and checkout"` |
| 6.8 | CouponService tách riêng + fix giới hạn dùng | ⬜ | `feature/coupon-service` | `git commit -m "feat(coupon): extract CouponService and add max_uses validation"` |

---

## 🔄 GIAI ĐOẠN 7 — TỐI ƯU (Xem chi tiết: OPTIMIZATION.md)

| # | Hạng mục | Trạng thái | Branch thực hiện | Lệnh Commit đề xuất |
|---|----------|:----------:|------------------|---------------------|
| 7.1 | Git Flow setup (develop, feature/*, hotfix/*) | ⬜ | `develop` | `git commit -m "chore: init develop branch and git flow"` |
| 7.2 | GitHub Actions CI/CD | ⬜ | `ci/github-actions` | `git commit -m "ci: add GitHub Actions workflow for linting and testing"` |
| 7.3 | Database performance indexes & Cache columns | ✅ | `perf/db-indexes` |
| 7.4 | Redis cache cho product listing | ⬜ | `perf/redis-cache` | `git commit -m "perf(product): implement Redis caching for product lists"` |
| 7.5 | Queue jobs: gửi email bất đồng bộ | ⬜ | `perf/queue-email` | `git commit -m "perf(mail): dispatch order emails to background queue"` |
| 7.7 | Form Requests cho Customer routes | ⬜ | `refactor/form-request` | `git commit -m "refactor(customer): move validation to Form Requests"` |
| 7.8 | Unit tests: OrderService, InventoryService... | ⬜ | `test/unit-services` | `git commit -m "test(services): add unit tests for core business logic"` |
| 7.9 | Feature tests: Checkout, Cart | ⬜ | `test/feature-cart` | `git commit -m "test(checkout): add feature tests for cart and checkout flow"` |

---

## 🐛 Bug / Vấn Đề Đang Theo Dõi

| # | Mô tả | Mức độ | Trạng thái | Branch fix | Lệnh Commit đề xuất |
|---|-------|:------:|:----------:|------------|---------------------|
| 1 | `checkCoupon()` thiếu kiểm tra giới hạn dùng | 🔴 High | ⬜ | `fix/coupon-limit` | `git commit -m "fix(coupon): enforce max_uses and dates in checkCoupon"` |
| 2 | `InventoryService::restore()` dùng type `IMPORT` thay vì `RETURN` | 🟡 Med | ⬜ | `fix/inventory-return` | `git commit -m "fix(inventory): change restore log type from IMPORT to RETURN"` |
| 3 | `OrderService::logStatus()` query `Order::find()` dư thừa | 🟢 Low | ⬜ | `fix/order-log-query` | `git commit -m "fix(order): remove redundant Order::find query in logStatus"` |
| 4 | Mail gửi sync trong request (nên dùng Queue) | 🟡 Med | ⬜ | *(Gộp chung 7.5)* | - |

---

## 📝 Nhật Ký Cập Nhật

| Ngày | Nội dung |
|------|----------|
| 23/07/2026 | Đã hoàn thành tối ưu Database (Mục 7.3) |
| 23/07/2026 | Bổ sung hướng dẫn Branch & Commit cho GĐ6, GĐ7 và Bug Fixes |
| 23/07/2026 | Tái cấu trúc toàn bộ docs, thêm OPTIMIZATION.md, GIT_WORKFLOW.md |
| 02/05/2026 | Hoàn thiện 3.3 (Cart) và 3.4 (Checkout/Order) |
| 02/05/2026 | Hoàn thiện 3.1 (Layout) và 3.2 (Product listing/detail) |
| DD/MM/2026 | Khởi tạo dự án |
