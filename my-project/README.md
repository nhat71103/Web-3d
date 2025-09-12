# 🎨 Frontend React - Thế Giới Động Vật 3D

Giao diện người dùng của ứng dụng Thế Giới Động Vật 3D, được xây dựng bằng React 19 và Tailwind CSS.

## 🚀 Tính Năng Chính

- **🌍 Trang Chủ**: Hiển thị danh sách động vật với bộ lọc nâng cao
- **🔍 Tìm Kiếm**: Tìm kiếm động vật theo tên và loài
- **🎮 3D Viewer**: Xem model 3D động vật với Three.js
- **📱 Responsive**: Tương thích mọi thiết bị (desktop, tablet, mobile)
- **🎨 Modern UI**: Giao diện đẹp với Tailwind CSS
- **⚡ Real-time**: Cập nhật trạng thái real-time

## ⚡ Chạy Nhanh

### 1. Cài Đặt
```bash
npm install
```

### 2. Chạy Development
```bash
npm run dev
```
Truy cập: http://localhost:5173

### 3. Build Production
```bash
npm run build
```

## 🔧 Yêu Cầu Hệ Thống
- Node.js 18+
- npm hoặc yarn
- Backend PHP đang chạy (XAMPP)

## 📁 Cấu Trúc Dự Án

```
my-project/
├── src/
│   ├── components/          # React components
│   │   ├── AnimalCard.jsx  # Card hiển thị động vật
│   │   ├── HomePage.jsx    # Trang chủ
│   │   └── EndangeredAnimalsPage.jsx
│   ├── App.jsx            # Component chính
│   └── main.jsx          # Entry point
├── api/                   # API endpoints
├── public/               # Static files
└── dist/                 # Build output
```

## 🔧 Cấu Hình

### API Endpoint
Mặc định kết nối đến: `http://localhost/3d_web/animal-showcase/animals_api_simple.php`

### Cấu Hình Backend
Đảm bảo XAMPP đang chạy:
- Apache: Port 80
- MySQL: Port 3306
- Database: `animal_showcase`

## 🎮 Tính Năng Chính

### 🌍 Trang Chủ
- Hiển thị danh sách động vật với card đẹp
- Bộ lọc nâng cao: Tình trạng bảo tồn, sắp xếp tên, loại môi trường
- Tìm kiếm theo tên động vật
- Responsive design cho mọi thiết bị

### 🎮 3D Viewer
- Hiển thị model 3D động vật với Three.js
- Điều khiển camera: zoom, rotate, pan
- Loading animation khi tải model
- Fallback khi không có model 3D

### 🔍 Bộ Lọc Nâng Cao
- **Tình trạng bảo tồn**: 6 mức độ từ "Đã tuyệt chủng" đến "Ít quan ngại"
- **Sắp xếp tên**: A-Z, Z-A, Dài-Ngắn, Ngắn-Dài
- **Loại môi trường**: Rừng, Biển, Đảo, Đồng bằng, Trên không, Hồ, Sông

## 🐛 Xử Lý Lỗi

### Lỗi Thường Gặp
1. **CORS Error**: Đảm bảo XAMPP đang chạy
2. **3D Model không load**: Kiểm tra file .glb có tồn tại
3. **API không kết nối**: Kiểm tra URL backend

### Debug
- Mở F12 Developer Tools
- Kiểm tra Console tab để xem lỗi
- Kiểm tra Network tab để xem API calls

## 📚 Tài Liệu

- [React](https://react.dev/)
- [Tailwind CSS](https://tailwindcss.com/)
- [Three.js](https://threejs.org/)
- [Vite](https://vitejs.dev/)

---

⭐ **Thế Giới Động Vật 3D** - Khám phá thế giới động vật qua công nghệ 3D!
