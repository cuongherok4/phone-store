# Hướng dẫn sử dụng Git & Làm việc nhóm (Teamwork)

Tài liệu này hướng dẫn các bước cơ bản để cập nhật code của bạn lên GitHub/GitLab và cách phối hợp khi làm việc chung với Team để tránh xung đột (conflict).

---

## 1. Quy trình cập nhật Code (Dành cho cá nhân)

Khi bạn đã sửa đổi code và muốn đẩy lên server:

### Bước 1: Kiểm tra các thay đổi
```bash
git status
```
Lệnh này giúp bạn xem những file nào đã bị thay đổi hoặc file mới tạo.

### Bước 2: Thêm các file vào hàng chờ (Stage)
* Thêm tất cả thay đổi:
  ```bash
  git add .
  ```
* Hoặc thêm từng file cụ thể:
  ```bash
  git add path/to/file.php
  ```

### Bước 3: Tạo ghi chú thay đổi (Commit)
*Lưu ý: Luôn viết ghi chú rõ ràng về những gì bạn đã làm.*
```bash
git commit -m "Tính năng: Thêm báo cáo Excel và thông tin hóa đơn"
```

### Bước 4: Đẩy code lên server (Push)
```bash
git push origin main
```
*(Thay `main` bằng tên nhánh bạn đang làm việc nếu cần).*

---

## 2. Quy trình làm việc nhóm (Tránh xung đột)

Khi làm việc nhóm, người khác có thể đã đẩy code mới lên trước bạn. Hãy tuân thủ quy trình này để dự án luôn ổn định:

### Bước 1: Luôn cập nhật code mới nhất từ Team trước khi làm
Trước khi bắt đầu code tính năng mới, hãy chạy:
```bash
git pull origin main
```

### Bước 2: Xử lý khi đẩy code (Quy trình chuẩn)
Nếu bạn đã code xong và chuẩn bị đẩy lên:
1. **Lưu (Commit)** code của bạn lại trước (xem phần 1).
2. **Kéo (Pull)** code mới nhất của Team về:
   ```bash
   git pull origin main
   ```
3. **Giải quyết xung đột (Nếu có):** 
   - Nếu Git báo có "Conflict", hãy mở các file đó lên.
   - Tìm đoạn `<<<<<<<< HEAD`, chọn giữ lại code của bạn, của Team, hoặc cả hai.
   - Sau khi sửa xong, chạy `git add .` và `git commit -m "Fix: Resolve merge conflict"`.
4. **Đẩy (Push)** code của mình lên:
   ```bash
   git push origin main
   ```

---

## 3. Một số lưu ý quan trọng

1. **Commit thường xuyên:** Đừng đợi code cả tuần mới commit một lần. Hãy chia nhỏ các tính năng để dễ quản lý.
2. **Lời nhắn Commit rõ ràng:** Sử dụng các tiền tố như `Tính năng:`, `Sửa lỗi:`, `Giao diện:`, `Refactor:` để Team dễ theo dõi.
3. **Không đẩy file cấu hình:** Tuyệt đối không `git add .env`. File này chứa mật khẩu database của máy bạn, không nên đưa lên Git. (Dự án đã có `.gitignore` để xử lý việc này).
4. **Nhánh (Branch):** Nếu làm tính năng lớn, hãy tạo nhánh riêng:
   ```bash
   git checkout -b feature/ten-tinh-nang
   ```
   Sau khi hoàn thành thì mới gộp (merge) vào nhánh chính.

---

## 4. Tóm tắt lệnh hay dùng

| Lệnh | Ý nghĩa |
|------|---------|
| `git status` | Xem trạng thái hiện tại |
| `git diff` | Xem chi tiết thay đổi trong code |
| `git log` | Xem lịch sử các lần commit |
| `git checkout .` | Hủy bỏ tất cả thay đổi chưa commit (Cẩn thận!) |
| `git stash` | Tạm cất code đang làm dở để pull code mới về |
| `git stash pop` | Lấy lại code đã cất sau khi pull xong |

---
*Chúc Team làm việc hiệu quả!*
