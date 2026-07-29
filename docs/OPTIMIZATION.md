# 📐 Kế Hoạch Tối Ưu Hệ Thống Phone Store

> **Senior PHP Review** — Laravel 12, PHP 8.2  
> **Ngày lập:** 23/07/2026

---

## 🔍 Phân Tích Hiện Trạng

| Thành phần | Hiện trạng | Đánh giá |
|---|---|:---:|
| Framework | Laravel 12, PHP 8.2 | ✅ |
| Kiến trúc | MVC + Service Layer | ✅ |
| Auth | Custom + Socialite | ⚠️ |
| Payment | VNPAY + MoMo + Demo Gateway | ✅ |
| Inventory | Multi-warehouse, lockForUpdate | ✅ |
| Permission | `isAdmin()` custom (Spatie cài nhưng chưa dùng) | ❌ |
| Cache | Chưa dùng (chỉ cache dashboard) | ❌ |
| Queue | Cài nhưng mail vẫn gửi sync | ⚠️ |
| Testing | ~0% (1 ExampleTest trống) | ❌ |
| Git Workflow | Chưa có branching strategy | ⚠️ |

---

## 🚨 Vấn Đề Theo Mức Độ Ưu Tiên

### 🔴 CRITICAL — Xử lý ngay

| # | File | Vấn đề | Fix |
|---|------|---------|-----|
| 1 | `CheckoutController@checkCoupon` | Logic kiểm tra giới hạn coupon bỏ trống (`// ...`) — dễ abuse | ✅ Đã tách `CouponService` và validate server-side |
| 2 | `InventoryService::restore()` | `change_type = 'IMPORT'` khi hoàn kho — sai semantic | ✅ Đã đổi sang `'RETURN'` |
| 3 | Mail gửi sync | Request fail nếu SMTP chậm/lỗi | Chuyển sang `Queue::dispatch()` |

### 🟠 HIGH — Xử lý trong tuần

| # | File | Vấn đề | Fix |
|---|------|---------|-----|
| 4 | `OrderService::logStatus()` | Gọi `Order::find()` dư thừa khi đã có `$order` | Truyền `$order->status` trực tiếp |
| 5 | `AdminMiddleware` | Dùng `isAdmin()` custom — không nhất quán với Spatie đã cài | Unify về Spatie `hasRole()` |
| 6 | `CheckoutController::process()` | Validate inline, không dùng Form Request | Tách ra `CheckoutRequest` |
| 7 | Blade file trong `Controllers/Admin/` | `admin.blade.php`, `form.blade.php`, `index.blade.php` sai vị trí | Di chuyển vào `resources/views/` |

### 🟡 MEDIUM — Giai đoạn tối ưu

| # | Vấn đề | Fix |
|---|---------|-----|
| 8 | `Product::getMinPriceAttribute()` query mỗi lần access | Dùng `withMin()` ở query level |
| 9 | `Product::getAvgRatingAttribute()` query mỗi lần access | Dùng `withAvg()` ở query level |
| 10 | Related products dùng `inRandomOrder()` | Cache với seed theo ngày |
| 11 | Search dùng `LIKE '%keyword%'` | Kích hoạt Laravel Scout |
| 12 | Không có database indexes tối ưu | Thêm composite indexes |

---

## 📋 Kế Hoạch 6 Giai Đoạn (2 Tuần)

### ─── GIAI ĐOẠN 1: Git Setup (Ngày 1) ───

**Mục tiêu:** Chuẩn hóa workflow git trước khi làm bất cứ thứ gì khác.

```bash
# Tạo develop branch
git checkout -b develop
git push -u origin develop

# Từ đây mọi feature đều từ develop:
git checkout develop
git checkout -b feature/ten-tinh-nang
# ... code ...
git push origin feature/ten-tinh-nang
# → Mở Pull Request vào develop
```

**Files cần tạo:**
- `.github/workflows/ci.yml` — CI pipeline (lint + test)
- `.github/pull_request_template.md` — Template PR
- `.gitignore` — Thêm `/scratch/`, `/img/`

---

### ─── GIAI ĐOẠN 2: Security Fixes (Ngày 1-2) ───

**Branch:** `fix/security-critical`

**Checklist:**
- [x] `CouponService::validate()` — kiểm tra is_active, dates, max_uses, user đã dùng chưa
- [x] `CouponService::calculate()` — tính discount theo type (percent/fixed)
- [x] `CouponService::recordUsage()` — ghi `coupon_usages` sau khi order tạo thành công
- [x] `InventoryService::restore()` — đổi `IMPORT` → `RETURN`
- [ ] `OrderService::logStatus()` — bỏ `Order::find()` dư thừa
- [ ] Rate limiting: `throttle:10,1` cho login, `throttle:5,1` cho checkout

**Files mới:**
- `app/Services/CouponService.php`

**Files sửa:**
- `app/Services/InventoryService.php` (1 dòng)
- `app/Services/OrderService.php` (inject CouponService)
- `app/Http/Controllers/Customer/CheckoutController.php`

---

### ─── GIAI ĐOẠN 3: Code Cleanup (Ngày 2-4) ───

**Branch:** `refactor/code-structure`

**Checklist:**
- [ ] Di chuyển Blade file ra khỏi `Controllers/`
- [ ] Tạo `CheckoutRequest` (validation + coupon check)
- [ ] Tạo `ReviewRequest`, `ProfileRequest`, `AddressRequest`
- [ ] Xóa `create_attributes.php` ở root (nếu không cần)
- [ ] Đảm bảo `scratch/` trong `.gitignore`

**Cấu trúc Form Requests:**
```
app/Http/Requests/
├── Admin/
│   ├── BrandRequest.php         ✅ (có rồi)
│   ├── StoreProductRequest.php  ✅ (có rồi)
│   └── StoreVariantRequest.php  ✅ (có rồi)
└── Customer/                    ← Tạo mới
    ├── CheckoutRequest.php
    ├── ReviewRequest.php
    ├── ProfileRequest.php
    └── AddressRequest.php
```

---

### ─── GIAI ĐOẠN 4: Performance (Ngày 3-5) ───

**Branch:** `perf/database-cache`

#### 4.1 Database Indexes
```sql
-- Migration mới: add_performance_indexes
ALTER TABLE products     ADD INDEX idx_status_deleted (status, deleted_at);
ALTER TABLE products     ADD INDEX idx_brand_status (brand_id, status);
ALTER TABLE product_variants ADD INDEX idx_product_active (product_id, is_active);
ALTER TABLE product_variants ADD INDEX idx_price (price);
ALTER TABLE orders       ADD INDEX idx_user_status (user_id, status);
ALTER TABLE orders       ADD INDEX idx_created_at (created_at);
ALTER TABLE inventory_logs ADD INDEX idx_variant_created (variant_id, created_at);
```

#### 4.2 Cache Strategy
```php
// ProductCacheService
Cache::remember("product:{$id}", 1800, fn() => Product::with(...)->find($id));
Cache::remember("products:featured", 600, fn() => Product::active()->featured()->get());

// Invalidate khi admin sửa product
static::saved(fn() => Cache::forget("product:{$this->id}"));
```

#### 4.3 Queue Jobs
```php
// Thay sendOrderEmail() sync:
dispatch(new SendOrderConfirmationEmail($order));

// Job class:
class SendOrderConfirmationEmail implements ShouldQueue {
    public int $tries = 3;
    public function handle(): void { Mail::to(...)->send(...); }
    public function failed(): void { Log::error(...); }
}
```

---

### ─── GIAI ĐOẠN 5: Testing (Ngày 4-6) ───

**Branch:** `test/core-services`

#### Unit Tests (Ưu tiên)
```
tests/Unit/Services/
├── OrderServiceTest.php      ← createFromCart, cancelOrder, confirmPayment
├── InventoryServiceTest.php  ← deduct, restore, import
└── CouponServiceTest.php     ← validate, apply, recordUsage
```

#### Feature Tests
```
tests/Feature/
├── CheckoutTest.php   ← COD order, VNPAY redirect, callback
└── CartTest.php       ← add, update, remove, stock check
```

**Commands:**
```bash
php artisan test --coverage
php artisan test tests/Unit/Services/OrderServiceTest.php
```

---

### ─── GIAI ĐOẠN 6: CI/CD (Ngày 5-7) ───

**Branch:** `ci/github-actions`

**Pipeline `.github/workflows/ci.yml`:**
```yaml
name: CI
on: [push, pull_request]
jobs:
  lint:   ./vendor/bin/pint --test
  test:   php artisan test --coverage --min=70
  audit:  composer audit
```

---

## 📅 Timeline

| Ngày | Nhánh Git | Task |
|------|-----------|------|
| Ngày 1 | `ci/git-setup` | Git Flow, CI cơ bản, .gitignore |
| Ngày 2 | `fix/security-critical` | CouponService, InventoryService fix, rate limit |
| Ngày 3 | `refactor/code-structure` | Form Requests, cleanup |
| Ngày 4 | `perf/database-cache` | Indexes migration, ProductCacheService |
| Ngày 5 | `perf/database-cache` | Queue jobs email |
| Ngày 6 | `test/core-services` | Unit tests |
| Ngày 7 | `test/core-services` | Feature tests |
| Ngày 8-10 | `perf/scout-search` | Laravel Scout kích hoạt |
| Ngày 11-12 | `ci/github-actions` | CI/CD hoàn chỉnh |
| Ngày 13 | `release/v1.1.0` | Release prep, CHANGELOG |
| Ngày 14 | `main` | Merge release, tag v1.1.0 |

---

## ✅ Definition of Done

Mỗi task coi là **XONG** khi:

- [ ] `./vendor/bin/pint` — 0 warnings
- [ ] Tests liên quan pass
- [ ] Không có `dd()`, `var_dump()`, hardcode credentials
- [ ] PHPDoc cho public methods của Service
- [ ] CI pipeline xanh

---

## 🎯 Quick Wins (< 30 phút mỗi cái — Làm ngay)

1. **`InventoryService::restore()`** — Đã đổi `'IMPORT'` → `'RETURN'`
2. **`OrderService::logStatus()`** — Bỏ `Order::find()` thừa, dùng `$order->status`
3. **`.gitignore`** — Thêm `/scratch/` và `/img/`
4. **`CHANGELOG.md`** — Tạo file theo Keep a Changelog
5. **`checkCoupon()`** — Đã validate qua `CouponService`
