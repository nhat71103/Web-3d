<?php
// API debug để kiểm tra dữ liệu database
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    require_once 'config.php';
    $db = Database::getInstance()->getConnection();
    
    $debug = [];
    
    // 1. Kiểm tra bảng animals_new
    $stmt = $db->query("SELECT COUNT(*) as count FROM animals_new");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $debug['animals_new_count'] = $result['count'];
    
    // 2. Kiểm tra bảng animal_media
    $stmt = $db->query("SELECT COUNT(*) as count FROM animal_media");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $debug['animal_media_count'] = $result['count'];
    
    // 3. Kiểm tra bảng animal_species
    $stmt = $db->query("SELECT COUNT(*) as count FROM animal_species");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $debug['animal_species_count'] = $result['count'];
    
    // 4. Kiểm tra bảng habitats
    $stmt = $db->query("SELECT COUNT(*) as count FROM habitats");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $debug['habitats_count'] = $result['count'];
    
    // 5. Kiểm tra bảng conservation_statuses
    $stmt = $db->query("SELECT COUNT(*) as count FROM conservation_statuses");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $debug['conservation_statuses_count'] = $result['count'];
    
    // 6. Lấy mẫu dữ liệu từ animals_new
    $stmt = $db->query("SELECT * FROM animals_new LIMIT 3");
    $debug['sample_animals'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 7. Lấy mẫu dữ liệu từ animal_media
    $stmt = $db->query("SELECT * FROM animal_media LIMIT 5");
    $debug['sample_media'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 8. Kiểm tra JOIN
    $query = "
        SELECT 
            an.id,
            an.name,
            asp.species_name,
            h.name as habitat_name,
            cs.name as conservation_status_name
        FROM animals_new an
        LEFT JOIN animal_species asp ON an.species_id = asp.id
        LEFT JOIN habitats h ON an.habitat_id = h.id
        LEFT JOIN conservation_statuses cs ON an.conservation_status_id = cs.id
        LIMIT 3
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $debug['sample_join'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
