<?php
// API debug chi tiết media
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    require_once 'config.php';
    $db = Database::getInstance()->getConnection();
    
    $debug = [];
    
    // 1. Kiểm tra tất cả media trong database
    $stmt = $db->query("
        SELECT 
            am.*,
            an.name as animal_name
        FROM animal_media am
        LEFT JOIN animals_new an ON am.animal_id = an.id
        ORDER BY am.animal_id, am.media_type, am.is_primary DESC
    ");
    $debug['all_media'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Kiểm tra từng file có tồn tại không
    $debug['file_check'] = [];
    foreach ($debug['all_media'] as $media) {
        $file_path = __DIR__ . '/../' . $media['file_url'];
        $debug['file_check'][] = [
            'animal_id' => $media['animal_id'],
            'animal_name' => $media['animal_name'],
            'file_url' => $media['file_url'],
            'media_type' => $media['media_type'],
            'is_primary' => $media['is_primary'],
            'full_path' => $file_path,
            'exists' => file_exists($file_path),
            'size' => file_exists($file_path) ? filesize($file_path) : 'N/A',
            'readable' => file_exists($file_path) ? is_readable($file_path) : 'N/A'
        ];
    }
    
    // 3. Kiểm tra dữ liệu động vật
    $stmt = $db->query("
        SELECT 
            an.id,
            an.name,
            an.description,
            COUNT(am.id) as media_count,
            GROUP_CONCAT(am.media_type) as media_types
        FROM animals_new an
        LEFT JOIN animal_media am ON an.id = am.animal_id
        GROUP BY an.id
        ORDER BY an.id
    ");
    $debug['animals_summary'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. Test serve_media.php với một file cụ thể
    $test_file = 'uploads/images/animal_1_1756096904.png';
    $test_url = 'http://localhost/3d_web/animal-showcase/api/serve_media.php?file=' . urlencode($test_file);
    $debug['test_serve_media'] = [
        'test_file' => $test_file,
        'test_url' => $test_url,
        'file_exists' => file_exists(__DIR__ . '/../' . $test_file),
        'file_size' => file_exists(__DIR__ . '/../' . $test_file) ? filesize(__DIR__ . '/../' . $test_file) : 'N/A'
    ];
    
    echo json_encode([
        'success' => true,
        'debug_info' => $debug,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Debug error: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
