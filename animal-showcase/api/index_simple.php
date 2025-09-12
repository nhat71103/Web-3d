<?php
// index_simple.php - Phiên bản đơn giản để test
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Get the request path
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$path = '';

if (strpos($requestUri, '/3d_web/animal-showcase/api/') !== false) {
    $path = str_replace('/3d_web/animal-showcase/api/', '', $requestUri);
    $path = trim($path, '/');
} elseif (strpos($requestUri, '/animal-showcase/api/') !== false) {
    $path = str_replace('/animal-showcase/api/', '', $requestUri);
    $path = trim($path, '/');
}

echo "<!-- DEBUG: Request URI: $requestUri -->\n";
echo "<!-- DEBUG: Path: $path -->\n";

// Handle /animals endpoint
if ($path === 'animals' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header("Content-Type: application/json; charset=UTF-8");
    
    try {
        // Load required files
        require_once 'config.php';
        require_once 'animals_new_fixed.php';
        
        // Create API instance
        $api = new AnimalsNewAPIFixed();
        $result = $api->getAnimals();
        
        if ($result['success']) {
            // Return animals array directly
            echo json_encode($result['data']);
        } else {
            // Return empty array on error
            echo json_encode([]);
        }
        
    } catch (Exception $e) {
        error_log("Error in /animals endpoint: " . $e->getMessage());
        // Return empty array on error
        echo json_encode([]);
    }
    
    exit(0);
}

// Default response
header("Content-Type: application/json; charset=UTF-8");
echo json_encode(['error' => 'Endpoint not found']);
?>
