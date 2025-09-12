<?php
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 0);

// Force no cache
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

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
    
    $query = "
        SELECT
            an.id,
            an.name,
            s.species_name,
            s.scientific_name,
            an.description,
            an.region_name,
            cs.name as conservation_status_name,
            an.population_count,
            an.created_at
        FROM animals_new an
        LEFT JOIN animal_species s ON an.species_id = s.id
        LEFT JOIN conservation_statuses cs ON an.conservation_status_id = cs.id
        ORDER BY an.name
    ";
    
    $stmt = $db->query($query);
    $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Lấy media cho mỗi động vật
    foreach ($animals as &$animal) {
        $media_query = "
            SELECT media_type, file_path, file_url, is_primary
            FROM animal_media
            WHERE animal_id = ?
            ORDER BY is_primary DESC, created_at ASC
        ";
        
        $media_stmt = $db->prepare($media_query);
        $media_stmt->execute([$animal['id']]);
        $media = $media_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Tách images và models
        $animal['images'] = [];
        $animal['models'] = [];
        
        foreach ($media as $item) {
            if ($item['media_type'] === 'image') {
                $animal['images'][] = [
                    'url' => $item['file_url'],
                    'path' => $item['file_path'],
                    'is_primary' => (bool)$item['is_primary']
                ];
            } elseif ($item['media_type'] === '3d_model') {
                $animal['models'][] = [
                    'url' => $item['file_url'],
                    'path' => $item['file_path'],
                    'is_primary' => (bool)$item['is_primary']
                ];
            }
        }
        
        // Kiểm tra có 3D model hay không - chỉ cần kiểm tra media_type
        $has3DModel = false;
        foreach ($media as $m) {
            if ($m['media_type'] === '3d_model') {
                $has3DModel = true;
                break;
            }
        }
        $animal['has3DModel'] = $has3DModel;
        
        // Backward compatibility
        $animal['species'] = $animal['species_name'];
        $animal['habitat_type'] = $animal['region_name'];
        $animal['habitat_name'] = $animal['region_name']; // For backward compatibility
        $animal['conservation_status'] = $animal['conservation_status_name'];
        
        // Primary image and model for backward compatibility
        $primary_image = array_filter($animal['images'], function($img) { return $img['is_primary']; });
        $primary_model = array_filter($animal['models'], function($model) { return $model['is_primary']; });
        
        $animal['image_path'] = !empty($primary_image) ? reset($primary_image)['url'] : null;
        $animal['model_path'] = !empty($primary_model) ? reset($primary_model)['url'] : null;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $animals,
        'count' => count($animals),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>