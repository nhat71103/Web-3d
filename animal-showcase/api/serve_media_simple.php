<?php
// API serve file media ĐƠN GIẢN để test
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Lấy file path từ query parameter
$file_path = $_GET['file'] ?? '';
if (empty($file_path)) {
    http_response_code(400);
    echo 'File path is required';
    exit;
}

// Log để debug
error_log("serve_media_simple.php: Requested file: " . $file_path);

// Đường dẫn đầy đủ đến file
$full_path = __DIR__ . '/../' . $file_path;

// Log để debug
error_log("serve_media_simple.php: Full path: " . $full_path);

// Kiểm tra file có tồn tại không
if (!file_exists($full_path)) {
    error_log("serve_media_simple.php: File not found: " . $full_path);
    http_response_code(404);
    echo 'File not found: ' . $file_path;
    exit;
}

// Kiểm tra file có đọc được không
if (!is_readable($full_path)) {
    error_log("serve_media_simple.php: File not readable: " . $full_path);
    http_response_code(403);
    echo 'File not readable: ' . $file_path;
    exit;
}

// Lấy thông tin file
$file_info = pathinfo($full_path);
$extension = strtolower($file_info['extension']);

// Log để debug
error_log("serve_media_simple.php: File extension: " . $extension);
error_log("serve_media_simple.php: File size: " . filesize($full_path));

// Set content type dựa trên extension
$content_types = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'glb' => 'model/gltf-binary',
    'gltf' => 'model/gltf+json',
    'obj' => 'model/obj',
    'fbx' => 'model/fbx',
    'mp4' => 'video/mp4',
    'avi' => 'video/x-msvideo',
    'mov' => 'video/quicktime'
];

$content_type = $content_types[$extension] ?? 'application/octet-stream';

// Log để debug
error_log("serve_media_simple.php: Content type: " . $content_type);

// Set headers
header('Content-Type: ' . $content_type);
header('Content-Length: ' . filesize($full_path));
header('Cache-Control: public, max-age=31536000');

// Đọc và output file
readfile($full_path);
?>
