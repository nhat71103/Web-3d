// src/config.js - Configuration file for the React app

// API Configuration
export const API_CONFIG = {
  // Backend PHP XAMPP thực tế của bạn
  BASE_URL: 'http://localhost/3d_web/animal-showcase', // Sử dụng localhost để tránh CORS issues
  API_PATH: '/api', // API endpoint
  UPLOAD_PATH: '/uploads', // Thư mục uploads
  MODELS_PATH: '/uploads/models' // Thư mục models 3D trong uploads
};

// Debug mode - set to true để xem log chi tiết
export const DEBUG_MODE = true;

// Full API URL - sửa lại để kết nối đúng với backend PHP
export const API_BASE = `${API_CONFIG.BASE_URL}`;

// Upload URLs
export const UPLOAD_BASE = `${API_CONFIG.BASE_URL}${API_CONFIG.UPLOAD_PATH}`;
export const MODELS_BASE = `${API_CONFIG.BASE_URL}${API_CONFIG.MODELS_PATH}`;

// App Configuration
export const APP_CONFIG = {
  name: 'Thế Giới Động Vật 3D',
  description: 'Khám phá thế giới động vật với công nghệ 3D AI tiên tiến',
  version: '1.0.0',
  author: 'Your Name'
};

// Default settings
export const DEFAULT_SETTINGS = {
  autoRefresh: true,
  refreshInterval: 30000, // 30 seconds
  maxImageSize: 10 * 1024 * 1024, // 10MB
  supportedImageTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']
};

// API Endpoints - cập nhật theo backend PHP thực tế
export const API_ENDPOINTS = {
  animals: '/animals', // GET /api/animals - lấy tất cả động vật từ database
  animal: '/animals', // GET /api/animals/{id} - lấy một động vật theo ID
  stats: '/stats', // GET /api/stats - thống kê
  upload: '/upload', // POST /api/upload - upload hình ảnh
  // Các endpoint cũ cho tạo model 3D đã được loại bỏ
  // Bây giờ chỉ cần lấy model từ CSDL
};

// CORS Configuration - đã được cấu hình đúng trong backend PHP
export const CORS_CONFIG = {
  mode: 'cors',
  credentials: 'omit', // Không gửi cookies để tránh CORS issues
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
};

// Fallback URLs - sử dụng khi backend chính không khả dụng
export const FALLBACK_CONFIG = {
  enabled: true,
  baseUrl: 'http://localhost:8080', // URL fallback
  timeout: 10000 // Timeout 10 giây cho XAMPP
};

// 3D Model Configuration
export const MODEL_3D_CONFIG = {
  supportedFormats: ['.glb', '.gltf', '.obj', '.fbx'],
  defaultViewer: 'threejs',
  autoRotate: true,
  enableControls: true,
  backgroundColor: '#f0f0f0'
};
