<?php
// API đơn giản và ổn định cho động vật
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    require_once 'config.php';
    $db = Database::getInstance()->getConnection();
    
    // Lấy danh sách động vật với JOIN đơn giản
    $query = "
        SELECT 
            an.id,
            an.name,
            an.description,
            an.population_count,
            an.created_at,
            an.updated_at,
            
            COALESCE(asp.species_name, 'Không xác định') as species_name,
            COALESCE(asp.scientific_name, 'Không xác định') as scientific_name,
            COALESCE(asp.family, 'Không xác định') as family,
            COALESCE(asp.order_name, 'Không xác định') as order_name,
            COALESCE(asp.class_name, 'Không xác định') as class_name,
            
            COALESCE(h.name, 'Không xác định') as habitat_name,
            COALESCE(h.description, 'Không xác định') as habitat_description,
            COALESCE(h.icon, '🌍') as habitat_icon,
            
            COALESCE(cs.name, 'Không xác định') as conservation_status_name,
            COALESCE(cs.description, 'Không xác định') as conservation_status_description,
            COALESCE(cs.color_code, '#999999') as conservation_status_color,
            COALESCE(cs.priority, 999) as conservation_status_priority,
            
            COALESCE(cd.iucn_status, 'Không xác định') as iucn_status,
            COALESCE(cd.population_trend, 'Không xác định') as population_trend,
            COALESCE(cd.threats, 'Không xác định') as threats,
            COALESCE(cd.conservation_actions, 'Không xác định') as conservation_actions,
            COALESCE(cd.last_assessment_date, 'Không xác định') as last_assessment_date
        FROM animals_new an
        LEFT JOIN animal_species asp ON an.species_id = asp.id
        LEFT JOIN habitats h ON an.habitat_id = h.id
        LEFT JOIN conservation_statuses cs ON an.conservation_status_id = cs.id
        LEFT JOIN conservation_data cd ON an.id = cd.animal_id
        ORDER BY an.name ASC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Xử lý media cho mỗi động vật
    foreach ($animals as &$animal) {
        // Lấy media
        $media_query = "SELECT file_url, media_type, is_primary FROM animal_media WHERE animal_id = ? ORDER BY is_primary DESC, created_at ASC";
        $media_stmt = $db->prepare($media_query);
        $media_stmt->execute([$animal['id']]);
        $media = $media_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Chuyển đổi đường dẫn tương đối thành đường dẫn qua serve_media.php
        foreach ($media as &$media_item) {
            if (!empty($media_item['file_url'])) {
                // Nếu là đường dẫn tương đối, chuyển thành đường dẫn qua serve_media.php
                if (strpos($media_item['file_url'], 'http') !== 0) {
                    $media_item['file_url'] = 'http://localhost/3d_web/animal-showcase/api/serve_media.php?file=' . urlencode($media_item['file_url']);
                }
            }
        }
        
        $animal['media'] = $media ?: [];
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
        'message' => 'Database error: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
