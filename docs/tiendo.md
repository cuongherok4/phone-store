Dưới đây là phiên bản **tiendo.md** đã được tôi viết lại, rõ ràng hơn, dễ nhìn hơn và dễ thực hiện hơn so với bản cũ.

---

# Phone Store — Laravel | Theo dõi tiến độ

> **Lưu file này tại:** `docs/tiendo.md`  
> Khi bắt đầu session mới, hãy paste nội dung file này để tôi có thể tiếp tục đúng tiến độ.

---

## Thông tin dự án

| Mục              | Chi tiết                                      |
|------------------|-----------------------------------------------|
| Framework        | Laravel 11 (PHP ≥ 8.2)                        |
| Database         | MySQL 8.0 — `phone_store_v2`                  |
| Frontend         | Tailwind CSS + AlpineJS + Vite                |
| Packages         | spatie/laravel-permission, intervention/image-laravel, laravel/scout, barryvdh/laravel-dompdf |
| Cấu trúc Routes  | `routes/web.php` + `routes/admin.php`         |

---

## Tiến độ tổng quan

**0 / 56 tasks hoàn thành (0%)**

```
[                                        ] 0%
```

**Cập nhật:** Chưa bắt đầu dự án

---

## Phase 1 — Nền tảng & Setup (8 tasks)

| STT | Task | Thành phần | Trạng thái | Ưu tiên |
|-----|------|------------|------------|--------|
| 1.1 | Cài đặt các packages cần thiết | Composer + npm | [ ] | ★★★ |
| 1.2 | Cấu hình file `.env` (DB, Mail, Storage) | Config | [ ] | ★★★ |
| 1.3 | Import database `phone_store_v2_updated.sql` | Database | [ ] | ★★★ |
| 1.4 | Viết toàn bộ **Migrations** từ file SQL (24 bảng) | Migration | [ ] | ★★★ |
| 1.5 | Tạo **Eloquent Models** + định nghĩa Relationships | Model | [ ] | ★★★ |
| 1.6 | Viết **Database Seeders** (Brand, Category, Product, Variant, Inventory...) | Seeder | [ ] | ★★ |
| 1.7 | Cấu hình **Tailwind CSS + AlpineJS + Vite** | Frontend | [ ] | ★★★ |
| 1.8 | Chạy `php artisan storage:link` và kiểm tra môi trường | Config | [ ] | ★★ |

**Trạng thái Phase:** ⬜ Chưa bắt đầu

---

## Phase 2 — Auth & Phân quyền (5 tasks)

| STT | Task | Thành phần | Trạng thái | Ưu tiên |
|-----|------|------------|------------|--------|
| 2.1 | Register, Login, Logout | Auth | [ ] | ★★★ |
| 2.2 | Forgot Password & Reset Password (Mailtrap) | Auth | [ ] | ★★ |
| 2.3 | Tạo Middleware (Admin + Customer) | Middleware | [ ] | ★★★ |
| 2.4 | Phân quyền route theo role (admin / customer) | Route | [ ] | ★★★ |
| 2.5 | Thiết kế giao diện Login / Register | View | [ ] | ★★ |

**Trạng thái Phase:** ⬜ Chưa bắt đầu

---

## Phase 3 — Admin Panel (15 tasks)

| STT | Task | Thành phần | Trạng thái | Ưu tiên |
|-----|------|------------|------------|--------|
| 3.1 | Dashboard (thống kê doanh thu, đơn hàng, tồn kho) | View | [ ] | ★★★ |
| 3.2 | CRUD Brands (upload logo) | CRUD | [ ] | ★★★ |
| 3.3 | CRUD Categories (hỗ trợ danh mục cha-con) | CRUD | [ ] | ★★★ |
| 3.4 | CRUD Products (thông tin cơ bản) | CRUD | [ ] | ★★★ |
| 3.5 | Quản lý Product Variants (SKU, giá, compare_price) | CRUD | [ ] | ★★★ |
| 3.6 | Upload & quản lý ảnh cho Variant | Media | [ ] | ★★★ |
| 3.7 | Quản lý Attributes & Attribute Values | CRUD | [ ] | ★★ |
| 3.8 | Quản lý Warehouses & Inventory | CRUD | [ ] | ★★ |
| 3.9 | Xem lịch sử Inventory Logs | View | [ ] | ★ |
| 3.10 | CRUD Coupons | CRUD | [ ] | ★★ |
| 3.11 | Quản lý Orders (xem danh sách, đổi trạng thái) | CRUD | [ ] | ★★★ |
| 3.12 | Order Status Histories | Model | [ ] | ★ |
| 3.13 | Quản lý Users (xem, khóa tài khoản) | CRUD | [ ] | ★★ |
| 3.14 | Duyệt Reviews | CRUD | [ ] | ★ |
| 3.15 | Quản lý Payments | View | [ ] | ★ |

**Trạng thái Phase:** ⬜ Chưa bắt đầu

---

## Phase 4 — Customer: Catalog (7 tasks)

| STT | Task | Thành phần | Trạng thái | Ưu tiên |
|-----|------|------------|------------|--------|
| 4.1 | Trang chủ (Banner, Sản phẩm nổi bật, Thương hiệu) | View | [ ] | ★★★ |
| 4.2 | Danh sách sản phẩm (filter, sort, phân trang) | View | [ ] | ★★★ |
| 4.3 | Trang chi tiết sản phẩm | View | [ ] | ★★★ |
| 4.4 | Chọn variant (màu sắc, dung lượng) bằng AlpineJS | Frontend | [ ] | ★★★ |
| 4.5 | Hiển thị Reviews & đánh giá trên trang sản phẩm | View | [ ] | ★★ |
| 4.6 | Wishlist (thêm/xóa sản phẩm yêu thích) | Feature | [ ] | ★★ |
| 4.7 | Trang Wishlist của user | View | [ ] | ★ |

**Trạng thái Phase:** ⬜ Chưa bắt đầu

---

## Phase 5 — Customer: Mua hàng (11 tasks)

| STT | Task | Thành phần | Trạng thái | Ưu tiên |
|-----|------|------------|------------|--------|
| 5.1 | CartService (thêm/xóa/sửa giỏ hàng - hỗ trợ guest) | Service | [ ] | ★★★ |
| 5.2 | Merge giỏ hàng guest vào user khi đăng nhập | Service | [ ] | ★★★ |
| 5.3 | Trang Giỏ hàng | View | [ ] | ★★★ |
| 5.4 | CouponService (validate, tính giảm giá, giới hạn sử dụng) | Service | [ ] | ★★★ |
| 5.5 | Trang Checkout (chọn địa chỉ, coupon, phương thức thanh toán) | View | [ ] | ★★★ |
| 5.6 | OrderService (tạo đơn hàng, snapshot dữ liệu) | Service | [ ] | ★★★ |
| 5.7 | InventoryService (trừ tồn kho + ghi log) | Service | [ ] | ★★★ |
| 5.8 | Thanh toán COD + tạo Payment record | Payment | [ ] | ★★★ |
| 5.9 | Chuẩn bị cấu trúc thanh toán VNPay / Momo | Payment | [ ] | ★★ |
| 5.10 | Gửi email xác nhận đơn hàng | Mail | [ ] | ★★ |
| 5.11 | Trang xác nhận đơn hàng thành công | View | [ ] | ★★ |

**Trạng thái Phase:** ⬜ Chưa bắt đầu

---

## Phase 6 — Customer: Tài khoản cá nhân (6 tasks)

| STT | Task | Thành phần | Trạng thái | Ưu tiên |
|-----|------|------------|------------|--------|
| 6.1 | Trang Profile (cập nhật thông tin, avatar) | View | [ ] | ★★ |
| 6.2 | Quản lý địa chỉ giao hàng (CRUD + set default) | CRUD | [ ] | ★★★ |
| 6.3 | Lịch sử đơn hàng (danh sách + chi tiết) | View | [ ] | ★★★ |
| 6.4 | Hủy đơn hàng (CANCELLED + hoàn tồn kho) | Feature | [ ] | ★★ |
| 6.5 | Viết Review sau khi mua hàng | Feature | [ ] | ★★ |
| 6.6 | Trang Notifications | View | [ ] | ★ |

**Trạng thái Phase:** ⬜ Chưa bắt đầu

---

## Phase 7 — Services & Business Logic (5 tasks)

| STT | Task | Thành phần | Trạng thái | Ưu tiên |
|-----|------|------------|------------|--------|
| 7.1 | Hoàn thiện CartService (edge cases) | Service | [ ] | ★★ |
| 7.2 | Hoàn thiện OrderService + xử lý transaction/rollback | Service | [ ] | ★★★ |
| 7.3 | Hoàn thiện CouponService + tracking coupon_usages | Service | [ ] | ★★ |
| 7.4 | InventoryService - Nhập kho thủ công từ Admin | Service | [ ] | ★★ |
| 7.5 | NotificationService (tự động tạo thông báo) | Service | [ ] | ★ |

**Trạng thái Phase:** ⬜ Chưa bắt đầu

---

## Phase 8 — Hoàn thiện & Deploy (7 tasks)

| STT | Task | Thành phần | Trạng thái | Ưu tiên |
|-----|------|------------|------------|--------|
| 8.1 | Export hóa đơn PDF (barryvdh/laravel-dompdf) | Feature | [ ] | ★★ |
| 8.2 | Tối ưu giao diện Tailwind (responsive) | UI/UX | [ ] | ★★★ |
| 8.3 | SEO (meta tags, slug tối ưu) | SEO | [ ] | ★★ |
| 8.4 | Hoàn thiện Validation (Form Requests) | Validation | [ ] | ★★★ |
| 8.5 | Viết một số Feature Test cơ bản | Testing | [ ] | ★ |
| 8.6 | Tối ưu query (eager loading, tránh N+1) | Performance | [ ] | ★★ |
| 8.7 | Production checklist & Deploy | Deploy | [ ] | ★ |

**Trạng thái Phase:** ⬜ Chưa bắt đầu

---

## Ghi chú & Quyết định kỹ thuật

- Chưa có ghi chú nào.

---

**Hướng dẫn sử dụng file này:**

- Khi hoàn thành một task → đổi `[ ]` thành `[x]`
- Khi phase có ít nhất 1 task hoàn thành → đổi icon phase thành `🔄 Đang làm`
- Khi hoàn thành toàn bộ task trong phase → đổi thành `✅ Hoàn thành`
- Cập nhật lại **Tiến độ tổng quan** ở đầu file sau mỗi lần cập nhật.

---

Bạn muốn tôi bắt đầu luôn **Phase 1** ngay bây giờ không?  
Hoặc bạn muốn chỉnh sửa thêm phần nào của file `tiendo.md` này trước khi bắt đầu làm?

Tôi sẵn sàng bắt đầu từ task 1.1 (cài packages) hoặc task 1.4 (viết migrations) tùy theo bạn muốn ưu tiên gì nhất.