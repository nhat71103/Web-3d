<?php
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
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'success' => false,
            'error' => 'Method not allowed',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit(0);
    }
    
    // Get animal ID from URL path
    $request_uri = $_SERVER['REQUEST_URI'];
    $path_parts = explode('/', trim($request_uri, '/'));
    $animal_id = null;
    
    // Find animal ID in path (look for edit/ID pattern)
    for ($i = 0; $i < count($path_parts) - 1; $i++) {
        if ($path_parts[$i] === 'edit' && isset($path_parts[$i + 1])) {
            $animal_id = intval($path_parts[$i + 1]);
            break;
        }
    }
    
    if (!$animal_id) {
        echo json_encode([
            'success' => false,
            'error' => 'Animal ID not provided',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit(0);
    }
    
    // Get form data
    $name = $_POST['name'] ?? '';
    $species = $_POST['species'] ?? '';
    $description = $_POST['description'] ?? '';
    $habitat_type = $_POST['habitat_type'] ?? '';
    $conservation_status = $_POST['conservation_status'] ?? '';
    $population_count = $_POST['population_count'] ?? '';
    
    if (empty($name)) {
        echo json_encode([
            'success' => false,
            'error' => 'Animal name is required',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit(0);
    }
    
    // Map habitat and conservation status to IDs
    $region_name = $_POST['region_name'] ?? 'Chưa xác định';
    $conservation_status_id = 1; // Default to first status
    
    // Region ID is already set from POST data
    
    // Get conservation status ID - map Vietnamese names to database
    $status_mapping = [
        'ít quan ngại' => 'Ít quan ngại',
        'gần bị đe dọa' => 'Gần bị đe dọa',
        'dễ bị tổn thương' => 'Dễ bị tổn thương',
        'nguy cấp' => 'Nguy cấp',
        'cực kỳ nguy cấp' => 'Cực kỳ nguy cấp',
        'đã tuyệt chủng' => 'Đã tuyệt chủng',
        'Least Concern' => 'Ít quan ngại',
        'Near Threatened' => 'Gần bị đe dọa',
        'Vulnerable' => 'Dễ bị tổn thương',
        'Endangered' => 'Nguy cấp',
        'Critically Endangered' => 'Cực kỳ nguy cấp',
        'Extinct' => 'Đã tuyệt chủng'
    ];
    
    $status_name = $status_mapping[$conservation_status] ?? $conservation_status;
    $stmt = $db->prepare("SELECT id FROM conservation_statuses WHERE name = ? LIMIT 1");
    $stmt->execute([$status_name]);
    $status = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($status) {
        $conservation_status_id = $status['id'];
    }
    
    // Get or create species
    $species_id = 1; // Default to first species
    $stmt = $db->prepare("SELECT id FROM animal_species WHERE species_name LIKE ? LIMIT 1");
    $stmt->execute(["%$species%"]);
    $species_data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($species_data) {
        $species_id = $species_data['id'];
    } else {
        // Create new species
        $stmt = $db->prepare("INSERT INTO animal_species (species_name, scientific_name) VALUES (?, ?)");
        $stmt->execute([$species, $species]);
        $species_id = $db->lastInsertId();
    }
    
    // Update animal
    $stmt = $db->prepare("
        UPDATE animals_new 
        SET name = ?, species_id = ?, description = ?, region_name = ?, 
            conservation_status_id = ?, population_count = ?
        WHERE id = ?
    ");
    
    $result = $stmt->execute([
        $name, $species_id, $description, $region_name, 
        $conservation_status_id, $population_count, $animal_id
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Animal updated successfully',
            'animal_id' => $animal_id,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to update animal',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
