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
    
    // Get animal ID from POST data
    $animal_id = intval($_POST['id'] ?? 0);
    
    if (!$animal_id) {
        echo json_encode([
            'success' => false,
            'error' => 'Animal ID not provided',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit(0);
    }
    
    // Delete animal (cascade will delete related media)
    $stmt = $db->prepare("DELETE FROM animals_new WHERE id = ?");
    $result = $stmt->execute([$animal_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Animal deleted successfully',
            'animal_id' => $animal_id,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to delete animal',
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
