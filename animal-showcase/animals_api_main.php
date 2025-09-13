<?php
// animals_api_main.php - API cho trang chủ sử dụng bảng animals cũ
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 0);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept');

while (ob_get_level()) {
    ob_end_clean();
}

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

try {
    require_once 'api/config.php';
    $db = Database::getInstance()->getConnection();
    
    // Query đơn giản cho trang chủ - sử dụng bảng animals cũ
    $query = "
        SELECT
            id,
            name,
            species,
            description,
            model_path,
            image_path,
            status_3d,
            meshy_model_url,
            meshy_thumbnail_url,
            model_file_path,
            model_file_size,
            created_at
        FROM animals
        ORDER BY name
    ";
    
    $stmt = $db->query($query);
    $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Thêm thông tin media cho mỗi động vật
    foreach ($animals as &$animal) {
        // Tạo mảng images và models từ dữ liệu cũ
        $animal['images'] = [];
        $animal['models'] = [];
        
        // Thêm image nếu có
        if (!empty($animal['image_path'])) {
            $image_url = $animal['image_path'];
            // Chuyển đổi đường dẫn tương đối thành đường dẫn qua serve_media.php
            if (strpos($image_url, 'http') !== 0) {
                $image_url = 'http://localhost/3d_web/animal-showcase/api/serve_media.php?file=' . urlencode($image_url);
            }
            
            $animal['images'][] = [
                'path' => $animal['image_path'],
                'url' => $image_url,
                'is_primary' => true
            ];
        }
        
        // Thêm 3D model nếu có
        if (!empty($animal['model_path'])) {
            $model_url = $animal['meshy_model_url'] ?: $animal['model_path'];
            // Chuyển đổi đường dẫn tương đối thành đường dẫn qua serve_media.php
            if (strpos($model_url, 'http') !== 0) {
                $model_url = 'http://localhost/3d_web/animal-showcase/api/serve_media.php?file=' . urlencode($model_url);
            }
            
            $animal['models'][] = [
                'path' => $animal['model_path'],
                'url' => $model_url,
                'is_primary' => true,
                'file_size' => $animal['model_file_size'],
                'status' => $animal['status_3d']
            ];
        }
        
        // Thêm thumbnail nếu có
        if (!empty($animal['meshy_thumbnail_url'])) {
            $animal['images'][] = [
                'path' => $animal['meshy_thumbnail_url'],
                'url' => $animal['meshy_thumbnail_url'],
                'is_primary' => false,
                'type' => 'thumbnail'
            ];
        }
        
        // Xóa các trường không cần thiết
        unset($animal['model_path'], $animal['image_path'], $animal['meshy_model_url'], $animal['meshy_thumbnail_url']);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $animals,
        'count' => count($animals),
        'timestamp' => date('Y-m-d H:i:s'),
        'source' => 'main_animals_table'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
