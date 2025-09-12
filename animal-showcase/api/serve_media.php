<?php
// API serve file media để tránh vấn đề CORS
// TẮT HOÀN TOÀN error reporting để tránh HTML output
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);
set_time_limit(300);

// Tắt output buffering
if (ob_get_level()) ob_end_clean();

// Set headers NGAY LẬP TỨC
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

// Bảo mật: Cho phép truy cập file trong thư mục uploads và models
$file_path = str_replace('..', '', $file_path); // Ngăn chặn directory traversal

// Kiểm tra xem file path có chứa uploads hoặc models không
$is_allowed = (strpos($file_path, 'uploads') !== false || strpos($file_path, 'models') !== false);

// Thêm debug logging chi tiết
error_log("serve_media.php - File path: " . $file_path);
error_log("serve_media.php - Is allowed: " . ($is_allowed ? 'Yes' : 'No'));
error_log("serve_media.php - Full path: " . __DIR__ . '/../' . $file_path);
error_log("serve_media.php - File exists: " . (file_exists(__DIR__ . '/../' . $file_path) ? 'Yes' : 'No'));
error_log("serve_media.php - Current directory: " . __DIR__);

if (!$is_allowed) {
    error_log("serve_media.php - Access denied for: " . $file_path);
    http_response_code(403);
    echo 'Access denied for: ' . $file_path;
    exit;
}

// Đường dẫn đầy đủ đến file
$full_path = __DIR__ . '/../' . $file_path;

// Kiểm tra file có tồn tại không
if (!file_exists($full_path)) {
    error_log("serve_media.php - File not found: " . $full_path);
    
    // Thử tìm file với tên tương tự (case-insensitive)
    $dir = dirname($full_path);
    $filename = basename($full_path);
    $files = scandir($dir);
    
    error_log("serve_media.php - Searching in directory: " . $dir);
    error_log("serve_media.php - Looking for filename: " . $filename);
    error_log("serve_media.php - Available files: " . implode(', ', $files));
    
    $found_file = null;
    foreach ($files as $file) {
        error_log("serve_media.php - Comparing: '$file' vs '$filename'");
        if (strcasecmp($file, $filename) === 0) {
            $found_file = $file;
            error_log("serve_media.php - Found exact match: " . $found_file);
            break;
        }
    }
    
    // Nếu không tìm thấy exact match, thử tìm partial match
    if (!$found_file) {
        error_log("serve_media.php - No exact match found, trying partial match...");
        
        // Chuẩn hóa tên file để so sánh
        $normalizedFilename = strtolower($filename);
        $normalizedFilename = str_replace(['đ', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ'], 'o', $normalizedFilename);
        $normalizedFilename = str_replace(['à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ'], 'a', $normalizedFilename);
        $normalizedFilename = str_replace(['è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ'], 'e', $normalizedFilename);
        $normalizedFilename = str_replace(['ì', 'í', 'ị', 'ỉ', 'ĩ'], 'i', $normalizedFilename);
        $normalizedFilename = str_replace(['ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ'], 'u', $normalizedFilename);
        $normalizedFilename = str_replace(['ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ'], 'y', $normalizedFilename);
        $normalizedFilename = str_replace(['đ'], 'd', $normalizedFilename);
        $normalizedFilename = str_replace([' ', '_', '-'], '', $normalizedFilename);
        
        error_log("serve_media.php - Normalized filename: " . $normalizedFilename);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $normalizedFile = strtolower($file);
            $normalizedFile = str_replace(['đ', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ'], 'o', $normalizedFile);
            $normalizedFile = str_replace(['à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ'], 'a', $normalizedFile);
            $normalizedFile = str_replace(['è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ'], 'e', $normalizedFile);
            $normalizedFile = str_replace(['ì', 'í', 'ị', 'ỉ', 'ĩ'], 'i', $normalizedFile);
            $normalizedFile = str_replace(['ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ'], 'u', $normalizedFile);
            $normalizedFile = str_replace(['ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ'], 'y', $normalizedFile);
            $normalizedFile = str_replace(['đ'], 'd', $normalizedFile);
            $normalizedFile = str_replace([' ', '_', '-'], '', $normalizedFile);
            
            error_log("serve_media.php - Comparing normalized: '$normalizedFile' vs '$normalizedFilename'");
            
                    // Kiểm tra độ tương đồng
        $similarity = 0;
        similar_text($normalizedFile, $normalizedFilename, $similarity);
        
        error_log("serve_media.php - Similarity: $similarity% between '$normalizedFile' and '$normalizedFilename'");
        
        if (strpos($normalizedFile, $normalizedFilename) !== false || 
            strpos($normalizedFilename, $normalizedFile) !== false ||
            $similarity > 60) { // Giảm ngưỡng từ 70% xuống 60%
            $found_file = $file;
            error_log("serve_media.php - Found partial match: " . $found_file . " (similarity: $similarity%)");
            break;
        }
        }
    }
    
    if ($found_file) {
        $full_path = $dir . '/' . $found_file;
        error_log("serve_media.php - Using file: " . $full_path);
    } else {
        // Fallback: Thử tìm file với tên đơn giản hơn
        error_log("serve_media.php - No similar file found, trying fallback method...");
        
        // Tạo tên file đơn giản (chỉ giữ chữ cái và số)
        $simpleFilename = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($filename));
        error_log("serve_media.php - Simple filename: " . $simpleFilename);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $simpleFile = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($file));
            error_log("serve_media.php - Comparing simple: '$simpleFile' vs '$simpleFilename'");
            
            if (strpos($simpleFile, $simpleFilename) !== false || 
                strpos($simpleFilename, $simpleFile) !== false) {
                $found_file = $file;
                $full_path = $dir . '/' . $found_file;
                error_log("serve_media.php - Found fallback match: " . $found_file);
                break;
            }
        }
        
        if (!$found_file) {
            error_log("serve_media.php - No file found with any method");
            http_response_code(404);
            echo 'File not found: ' . $file_path;
            exit;
        }
    }
}

// Kiểm tra file có đọc được không
if (!is_readable($full_path)) {
    error_log("serve_media.php - File not readable: " . $full_path);
    http_response_code(403);
    echo 'File not readable: ' . $file_path;
    exit;
}

// Lấy thông tin file
$file_info = pathinfo($full_path);
$extension = strtolower($file_info['extension']);

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

// Set headers
header('Content-Type: ' . $content_type);
header('Content-Length: ' . filesize($full_path));
header('Cache-Control: public, max-age=31536000'); // Cache 1 năm

// Đọc và output file
try {
    // Sử dụng readfile thay vì file_get_contents để tránh memory issues
    if (readfile($full_path) === false) {
        error_log("serve_media.php - Failed to read file: " . $full_path);
        http_response_code(500);
        echo 'Failed to read file: ' . $file_path;
        exit;
    }
} catch (Exception $e) {
    error_log("serve_media.php - Exception reading file: " . $e->getMessage());
    http_response_code(500);
    echo 'Error reading file: ' . $e->getMessage();
    exit;
}
?>
