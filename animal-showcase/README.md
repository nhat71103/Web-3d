# 🐘 Backend PHP - Thế Giới Động Vật 3D

Backend API và Admin Panel của ứng dụng Thế Giới Động Vật 3D, được xây dựng bằng PHP và MySQL.

## 🚀 Tính Năng Chính

- **🌐 API RESTful**: Cung cấp dữ liệu cho frontend React
- **👨‍💼 Admin Panel**: Quản lý động vật, hình ảnh, model 3D
- **📁 File Upload**: Xử lý hình ảnh và model 3D (.glb)
- **🗄️ Database**: MySQL với cấu trúc tối ưu
- **🔗 CORS**: Hỗ trợ cross-origin requests
- **🎮 3D Viewer**: Hiển thị model 3D động vật

## ⚡ Chạy Nhanh

### 1. Khởi Động XAMPP
- Start Apache (Port 80)
- Start MySQL (Port 3306)

### 2. Truy Cập Admin Panel
```
http://localhost/3d_web/animal-showcase/admin.html
```

### 3. API Endpoint
```
http://localhost/3d_web/animal-showcase/animals_api_simple.php
```

## 🔧 Yêu Cầu Hệ Thống
- **XAMPP**: Apache + MySQL + PHP
- **PHP**: 7.4+
- **MySQL**: 5.7+
- **Database**: `animal_showcase`

## 📁 Cấu Trúc Dự Án

```
animal-showcase/
├── admin.html              # 👨‍💼 Admin Panel chính
├── 3d-viewer.html          # 🎮 3D Model Viewer
├── animals_api_simple.php  # 🌐 API chính cho frontend
├── add_animal.php          # ➕ Thêm động vật
├── update_animal.php       # ✏️ Sửa động vật
├── delete_animal.php       # 🗑️ Xóa động vật
├── manage_regions.php      # 🌍 Quản lý khu vực
├── api/                    # 📁 API endpoints
├── uploads/                # 📁 Files upload
│   ├── images/            # 🖼️ Hình ảnh động vật
│   └── models/            # 🎮 Model 3D (.glb)
└── README.md              # 📚 Tài liệu này
```

## 🗄️ Database

### Cấu Trúc Chính
- **`animals_new`**: Thông tin động vật chính
- **`animal_species`**: Phân loại loài
- **`conservation_statuses`**: Tình trạng bảo tồn
- **`regions`**: Khu vực sống
- **`animal_media`**: Hình ảnh và model 3D

## 🎮 Admin Panel

### Tính Năng Chính
- **➕ Thêm động vật**: Form thêm động vật mới với khu vực sống tự do
- **✏️ Sửa động vật**: Cập nhật thông tin động vật
- **🗑️ Xóa động vật**: Xóa động vật khỏi hệ thống
- **🖼️ Quản lý hình ảnh**: Upload và quản lý hình ảnh động vật
- **🎮 Quản lý model 3D**: Upload và quản lý model 3D (.glb)
- **🌍 Khu vực sống**: Nhập tự do khu vực sống (không cần chọn từ danh sách)

### Cách Sử Dụng
1. Truy cập: `http://localhost/3d_web/animal-showcase/admin.html`
2. Sử dụng form "Thêm Động Vật Mới" để thêm động vật
3. Nhập khu vực sống tự do (VD: "Hồ Hoàn Kiếm", "Rừng Cúc Phương")
4. Upload hình ảnh và model 3D
5. Sử dụng "Tự động thêm model 3D" để gán model cho động vật

## 🌐 API Endpoints

### Chính
- **GET** `animals_api_simple.php` - Lấy danh sách động vật cho frontend
- **POST** `add_animal.php` - Thêm động vật mới
- **POST** `update_animal.php` - Cập nhật động vật
- **POST** `delete_animal.php` - Xóa động vật

### 3D Viewer
- **GET** `3d-viewer.html` - Hiển thị model 3D động vật

## 🗄️ Database Schema

### Bảng Chính
- **`animals_new`**: Thông tin động vật (tên, mô tả, khu vực sống, tình trạng bảo tồn)
- **`animal_species`**: Phân loại loài (tên khoa học, họ, bộ, lớp)
- **`conservation_statuses`**: Tình trạng bảo tồn (6 mức độ)
- **`regions`**: Khu vực sống (tham khảo)
- **`animal_media`**: Hình ảnh và model 3D

## 🔧 Cấu Hình

### Database
```php
// api/config.php
$host = 'localhost';
$dbname = 'animal_showcase';
$username = 'root';
$password = '';
```

### CORS
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept');
```

## 🐛 Xử Lý Lỗi

### Lỗi Thường Gặp
1. **Database connection failed**: Kiểm tra XAMPP MySQL đang chạy
2. **CORS error**: Kiểm tra headers trong PHP files
3. **File upload failed**: Kiểm tra quyền ghi thư mục uploads/
4. **3D model không hiển thị**: Kiểm tra file .glb có tồn tại

### Debug
- Mở F12 Developer Tools
- Kiểm tra Console tab
- Kiểm tra Network tab cho API calls

## 📚 Tài Liệu

- [PHP](https://www.php.net/)
- [MySQL](https://dev.mysql.com/)
- [Three.js](https://threejs.org/)

---

⭐ **Thế Giới Động Vật 3D** - Backend mạnh mẽ cho ứng dụng 3D!