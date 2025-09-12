# 🚀 Hướng Dẫn Nhanh - Animal Showcase 3D

Hướng dẫn nhanh để chạy ứng dụng Thế Giới Động Vật 3D.

## ⚡ Chạy Nhanh (5 phút)

### 1. Khởi Động Backend
```bash
# Khởi động XAMPP
# Start Apache và MySQL
```

### 2. Chạy Frontend
```bash
cd my-project
npm install
npm run dev
```

### 3. Truy Cập
- **Frontend**: http://localhost:5173
- **Backend**: http://localhost/3d_web/animal-showcase
- **Admin**: http://localhost/3d_web/animal-showcase/admin.html

## 🔧 Cài Đặt Chi Tiết

### Backend Setup
1. **Database**: Tạo database `animal_showcase`
2. **Import Schema**: Chạy `database_schema.sql`
3. **Cấu hình**: Sửa `api/config.php`

### Frontend Setup
1. **Dependencies**: `npm install`
2. **Config**: Sửa `src/config.js`
3. **Dev Server**: `npm run dev`

## 🎯 Tính Năng Chính

- ✅ **Danh sách động vật** với hình ảnh
- ✅ **Model 3D** hiển thị bằng Three.js
- ✅ **Tìm kiếm và lọc** động vật
- ✅ **Admin panel** quản lý
- ✅ **AI integration** tạo model 3D

## 🐛 Troubleshooting

### Lỗi Kết Nối
- Kiểm tra XAMPP đã khởi động
- Kiểm tra database connection
- Kiểm tra CORS headers

### Lỗi 3D Model
- Kiểm tra file path
- Kiểm tra file format (.glb, .gltf)
- Kiểm tra console errors

## 📱 Test

### API Test
```bash
curl http://localhost/3d_web/animal-showcase/api/animals
```

### Frontend Test
- Mở http://localhost:5173
- Kiểm tra danh sách động vật
- Test tìm kiếm và lọc
- Xem model 3D

## 🔗 Links

- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost/3d_web/animal-showcase/api
- **Admin**: http://localhost/3d_web/animal-showcase/admin.html
- **Database**: phpMyAdmin (http://localhost/phpmyadmin)

---

🎉 **Chúc mừng!** Ứng dụng đã chạy thành công!
