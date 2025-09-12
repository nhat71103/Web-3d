<?php
// API kiểm tra dữ liệu media
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    require_once 'config.php';
    $db = Database::getInstance()->getConnection();
    
    $debug = [];
    
    // 1. Kiểm tra bảng animal_media có tồn tại không
    $stmt = $db->query("SHOW TABLES LIKE 'animal_media'");
    $debug['table_exists'] = $stmt->rowCount() > 0;
    
    if ($debug['table_exists']) {
        // 2. Đếm số lượng media
        $stmt = $db->query("SELECT COUNT(*) as count FROM animal_media");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $debug['media_count'] = $result['count'];
        
        // 3. Lấy mẫu dữ liệu media
        $stmt = $db->query("SELECT * FROM animal_media LIMIT 5");
        $debug['sample_media'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 4. Kiểm tra JOIN với animals_new
        $query = "
            SELECT 
                am.id,
                am.animal_id,
                am.file_url,
                am.media_type,
                am.is_primary,
                an.name as animal_name
            FROM animal_media am
            LEFT JOIN animals_new an ON am.animal_id = an.id
            LIMIT 5
        ";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $debug['sample_join'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 5. Kiểm tra file có tồn tại không
        $stmt = $db->query("SELECT file_url FROM animal_media LIMIT 3");
        $media_files = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $debug['file_exists_check'] = [];
        foreach ($media_files as $media) {
            $file_path = __DIR__ . '/../' . $media['file_url'];
            $debug['file_exists_check'][] = [
                'file_url' => $media['file_url'],
                'full_path' => $file_path,
                'exists' => file_exists($file_path),
                'size' => file_exists($file_path) ? filesize($file_path) : 'N/A'
            ];
        }
    } else {
        $debug['error'] = 'Bảng animal_media không tồn tại';
    }
    
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
