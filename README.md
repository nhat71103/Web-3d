
# 🌍 Thế Giới Động Vật 3D - Animal Showcase 3D

Ứng dụng web hiển thị danh sách động vật với công nghệ 3D AI tiên tiến, được xây dựng bằng React (Frontend) và PHP (Backend).

## 🚀 Tính Năng Chính

- **Frontend React**: Giao diện người dùng hiện đại với Tailwind CSS
- **Backend PHP**: API RESTful với MySQL database
- **3D Models**: Hiển thị model 3D động vật bằng Three.js
- **AI Integration**: Tích hợp Meshy AI để tạo model 3D tự động
- **Admin Panel**: Quản lý động vật, upload hình ảnh, tạo model 3D
- **Responsive Design**: Tương thích mọi thiết bị

## 🏗️ Cấu Trúc Dự Án

```
3d_web/
├── my-project/          # Frontend React
│   ├── src/            # Mã nguồn React
│   ├── public/         # File tĩnh
│   └── package.json    # Dependencies
└── animal-showcase/    # Backend PHP
    ├── api/            # API endpoints
    ├── uploads/        # File uploads
    ├── models/         # 3D models
    └── admin.html      # Trang admin
```

## 🛠️ Cài Đặt

### Yêu Cầu Hệ Thống
- XAMPP (Apache + MySQL + PHP)
- Node.js 18+
- npm hoặc yarn

### Bước 1: Cài Đặt Backend
1. Khởi động XAMPP
2. Copy thư mục `animal-showcase` vào `htdocs`
3. Tạo database MySQL và import `database_schema.sql`
4. Cấu hình database trong `api/config.php`

### Bước 2: Cài Đặt Frontend
```bash
cd my-project
npm install
npm run dev
```

### Bước 3: Cấu Hình
- Sửa `src/config.js` để trỏ đến đúng backend URL
- Đảm bảo CORS được cấu hình đúng

## 🎯 Sử Dụng

### Frontend (React)
- **Trang chủ**: Hiển thị danh sách động vật
- **Tìm kiếm**: Lọc theo tên, loài, trạng thái 3D
- **Xem 3D**: Hiển thị model 3D động vật
- **Responsive**: Tương thích mobile và desktop

### Backend (PHP)
- **API RESTful**: CRUD operations cho động vật
- **File Upload**: Xử lý hình ảnh và model 3D
- **AI Integration**: Tích hợp Meshy AI
- **Database**: MySQL với cấu trúc tối ưu

### Admin Panel
- **Quản lý động vật**: Thêm, sửa, xóa
- **Upload hình ảnh**: Hỗ trợ nhiều định dạng
- **Tạo model 3D**: Tự động với AI
- **Theo dõi tiến trình**: Xem trạng thái xử lý

## 🔧 API Endpoints

### Animals
- `GET /api/animals` - Lấy danh sách động vật
- `GET /api/animals/{id}` - Lấy thông tin động vật
- `POST /api/animals` - Tạo động vật mới
- `PUT /api/animals/{id}` - Cập nhật động vật
- `DELETE /api/animals/{id}` - Xóa động vật

### 3D Models
- `POST /api/create-3d/{id}` - Tạo model 3D
- `GET /api/check-3d-status/{id}` - Kiểm tra trạng thái
- `GET /api/download-model/{id}` - Tải xuống model

## 🎨 Công Nghệ Sử Dụng

### Frontend
- **React 19**: UI framework
- **Tailwind CSS**: Styling
- **Three.js**: 3D graphics
- **Vite**: Build tool

### Backend
- **PHP 8+**: Server-side language
- **MySQL**: Database
- **PDO**: Database connection
- **REST API**: Architecture

### 3D & AI
- **Three.js**: 3D rendering
- **GLTF Loader**: Model loading
- **Meshy AI**: 3D generation
- **OrbitControls**: Camera control

## 📱 Responsive Design

- **Mobile First**: Thiết kế ưu tiên mobile
- **Grid System**: Layout linh hoạt
- **Touch Friendly**: Tương tác cảm ứng
- **Performance**: Tối ưu hóa tốc độ

## 🚀 Deployment

### Local Development
```bash
# Backend
# Sử dụng XAMPP

# Frontend
cd my-project
npm run dev
```

### Production Build
```bash
cd my-project
npm run build
```

## 🔍 Troubleshooting

### Lỗi Kết Nối Database
- Kiểm tra XAMPP đã khởi động
- Kiểm tra thông tin database trong `config.php`
- Đảm bảo MySQL service đang chạy

### Lỗi CORS
- Kiểm tra cấu hình CORS trong backend
- Đảm bảo frontend và backend cùng domain
- Kiểm tra headers trong request

### Lỗi 3D Model
- Kiểm tra file model có tồn tại
- Đảm bảo định dạng file được hỗ trợ
- Kiểm tra console browser để debug

## 📚 Tài Liệu Tham Khảo

- [React Documentation](https://react.dev/)
- [Three.js Documentation](https://threejs.org/docs/)
- [Tailwind CSS](https://tailwindcss.com/)
- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)

## 🤝 Đóng Góp

1. Fork dự án
2. Tạo feature branch
3. Commit changes
4. Push to branch
5. Tạo Pull Request

## 📄 License

Dự án này được phát hành dưới MIT License.

## 📞 Liên Hệ

- **Email**: info@example.com
- **Website**: https://example.com
- **GitHub**: https://github.com/username/project

---

⭐ Nếu dự án này hữu ích, hãy cho chúng tôi một star!
