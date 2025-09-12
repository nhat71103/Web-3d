<?php
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 0);

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Tắt tất cả output buffering
while (ob_get_level()) {
    ob_end_clean();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit(0);
}

try {
    require_once 'config.php';
    $pdo = Database::getInstance()->getConnection();
    
    $query = "
        SELECT
            an.id,
            an.name,
            an.description,
            an.population_count,
            an.created_at,
            an.updated_at,
            asp.species_name,
            asp.scientific_name,
            asp.family,
            asp.order_name,
            asp.class_name,
            h.name AS habitat_name,
            h.description AS habitat_description,
            h.icon AS habitat_icon,
            cs.name AS conservation_status_name,
            cs.description AS conservation_status_description,
            cs.color_code AS conservation_status_color,
            cs.priority AS conservation_status_priority,
            cd.iucn_status,
            cd.population_trend,
            cd.threats,
            cd.conservation_actions,
            cd.last_assessment_date,
            (SELECT GROUP_CONCAT(CONCAT(am.file_url, '||', am.media_type, '||', am.is_primary) SEPARATOR ';;')
             FROM animal_media am WHERE am.animal_id = an.id) AS media_data
        FROM animals_new an
        LEFT JOIN animal_species asp ON an.species_id = asp.id
        LEFT JOIN habitats h ON an.habitat_id = h.id
        LEFT JOIN conservation_statuses cs ON an.conservation_status_id = cs.id
        LEFT JOIN conservation_data cd ON an.id = cd.animal_id
        ORDER BY an.name ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($animals as &$animal) {
        $animal['media'] = [];
        if (!empty($animal['media_data'])) {
            $media_items = explode(';;', $animal['media_data']);
            foreach ($media_items as $item) {
                list($file_url, $media_type, $is_primary) = explode('||', $item);
                $animal['media'][] = [
                    'file_url' => $file_url,
                    'media_type' => $media_type,
                    'is_primary' => (bool)$is_primary
                ];
            }
        }
        unset($animal['media_data']);
        
        $animal['species'] = $animal['species_name'] ?? 'Không xác định';
        $animal['habitat_type'] = $animal['habitat_name'] ?? 'Chưa cập nhật';
        $animal['conservation_status'] = $animal['conservation_status_name'] ?? 'Chưa cập nhật';
        
        $imageMedia = array_filter($animal['media'], function($m) { 
            return $m['media_type'] === 'image'; 
        });
        if (!empty($imageMedia)) {
            $animal['image_path'] = reset($imageMedia)['file_url'];
        } else {
            $animal['image_path'] = null;
        }
        
        $modelMedia = array_filter($animal['media'], function($m) { 
            return $m['media_type'] === '3d_model'; 
        });
        if (!empty($modelMedia)) {
            $animal['model_path'] = reset($modelMedia)['file_url'];
        } else {
            $animal['model_path'] = null;
        }
        
        $animal['status_3d'] = $animal['model_path'] ? 'completed' : 'pending';
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
        'error' => 'Database error: ' . $e->getMessage(),
        'data' => [],
        'count' => 0,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
