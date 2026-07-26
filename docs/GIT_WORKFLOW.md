# 🌿 Git Workflow — Phone Store

> Áp dụng **Git Flow** chuẩn hóa. Đọc kỹ trước khi bắt đầu code.

---

## 1. Cấu Trúc Nhánh

```
main           ← Production. Chỉ merge từ release/* hoặc hotfix/*
develop        ← Nhánh tích hợp. Luôn stable, deployable
feature/*      ← Tính năng mới (checkout từ develop)
fix/*          ← Bug nhỏ (checkout từ develop)
hotfix/*       ← Bug khẩn trên production (checkout từ main)
release/*      ← Chuẩn bị release (checkout từ develop)
```

**Quy tắc cứng:**
- ❌ Không push thẳng vào `main`
- ❌ Không push thẳng vào `develop` (trừ hotfix khẩn)
- ✅ Mọi thay đổi đều qua Pull Request

---

## 2. Commit Convention

Format: `type(scope): mô tả ngắn`

| Type | Khi nào dùng |
|------|-------------|
| `feat` | Thêm tính năng mới |
| `fix` | Sửa bug |
| `refactor` | Tái cấu trúc (không thêm tính năng, không fix bug) |
| `perf` | Cải thiện hiệu năng |
| `test` | Thêm/sửa test |
| `docs` | Cập nhật tài liệu |
| `chore` | Dependencies, build system |
| `ci` | CI/CD pipeline |

**Ví dụ thực tế:**
```bash
git commit -m "feat(coupon): add CouponService with max_uses validation"
git commit -m "fix(inventory): use RETURN type for order cancellation restore"
git commit -m "perf(product): cache product listing with Redis"
git commit -m "test(order): add unit tests for OrderService::createFromCart"
git commit -m "refactor(checkout): extract validation to CheckoutRequest"
git commit -m "docs: update tiendo.md progress"
```

---

## 3. Quy Trình Hàng Ngày

### Bắt đầu tính năng mới

```bash
# 1. Luôn checkout từ develop mới nhất
git checkout develop
git pull origin develop

# 2. Tạo feature branch
git checkout -b feature/coupon-service

# 3. Code, commit nhỏ thường xuyên
git add -p                    # Review từng thay đổi trước khi stage
git commit -m "feat(coupon): add CouponService skeleton"
git commit -m "feat(coupon): implement validate() method"
git commit -m "test(coupon): add unit tests for validation"

# 4. Push và mở Pull Request vào develop
git push origin feature/coupon-service
```

### Sau khi PR được merge

```bash
git checkout develop
git pull origin develop
git branch -d feature/coupon-service   # Xóa local branch
```

---

## 4. Release Process

```bash
# Tạo release branch từ develop
git checkout develop && git pull
git checkout -b release/v1.1.0

# Cập nhật version, CHANGELOG
# (chỉ fix bug nhỏ ở đây, không thêm feature)
git commit -m "chore: bump version to 1.1.0"
git commit -m "docs: update CHANGELOG for v1.1.0"

# Merge vào main và tag
git checkout main
git merge --no-ff release/v1.1.0
git tag -a v1.1.0 -m "Release v1.1.0 - Performance optimization"
git push origin main --tags

# Merge back vào develop (giữ sync)
git checkout develop
git merge --no-ff release/v1.1.0
git push origin develop

# Xóa release branch
git branch -d release/v1.1.0
git push origin --delete release/v1.1.0
```

---

## 5. Hotfix Process (Bug Khẩn Production)

```bash
# Từ main (không phải develop!)
git checkout main && git pull
git checkout -b hotfix/fix-vnpay-null-order

# Fix bug
git commit -m "fix(payment): handle null order in vnpay callback"

# Merge vào CẢ main VÀ develop
git checkout main
git merge --no-ff hotfix/fix-vnpay-null-order
git tag -a v1.1.1 -m "Hotfix: vnpay null order"
git push origin main --tags

git checkout develop
git merge --no-ff hotfix/fix-vnpay-null-order
git push origin develop

git branch -d hotfix/fix-vnpay-null-order
```

---

## 6. Lệnh Git Hay Dùng

| Lệnh | Ý nghĩa |
|------|---------|
| `git status` | Xem trạng thái |
| `git log --oneline --graph` | Xem lịch sử dạng graph |
| `git diff` | Xem chi tiết thay đổi |
| `git add -p` | Stage từng hunk (khuyến nghị) |
| `git stash` | Cất code đang làm dở |
| `git stash pop` | Lấy lại code đã cất |
| `git checkout -- .` | Hủy tất cả thay đổi chưa stage (cẩn thận!) |
| `git reset HEAD~1` | Undo commit cuối (giữ code) |
| `git rebase -i HEAD~3` | Squash 3 commit thành 1 trước khi push |

---

## 7. Tips & Best Practices

1. **Commit thường xuyên:** Chia nhỏ, mỗi commit 1 mục đích rõ ràng
2. **`git add -p`** thay vì `git add .` — review từng thay đổi
3. **Không commit `.env`** — đã có trong `.gitignore`
4. **Viết commit message tiếng Anh** — dễ đọc hơn trong log
5. **Squash trước khi merge** nếu có nhiều commit WIP: `git rebase -i`
6. **Pull trước khi Push** để tránh conflict không cần thiết
