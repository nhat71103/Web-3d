<?php
// api/index.php - Main API endpoint
// CORS headers cơ bản để frontend React kết nối được

// KHÔNG hiển thị errors để tránh làm hỏng JSON response
error_reporting(0);
ini_set('display_errors', 0);

// Clear any output buffers
while (ob_get_level()) {
    ob_end_clean();
}

// Set CORS headers - QUAN TRỌNG!
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, Accept, Origin, X-Requested-With");
header("Access-Control-Max-Age: 86400");
// Content-Type sẽ được set sau khi xử lý endpoint

// Handle preflight OPTIONS request - QUAN TRỌNG!
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    // Set CORS headers for OPTIONS response
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, Accept, Origin, X-Requested-With");
    header("Access-Control-Max-Age: 86400");
    // OPTIONS response không cần Content-Type và không cần body
    exit(0);
}

// Handle HEAD requests (browsers sometimes send these)
if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
    http_response_code(200);
    header("Access-Control-Allow-Origin: *");
    // HEAD response không cần Content-Type
    exit(0);
}

// Get the request path from multiple sources
$path = $_GET['path'] ?? '';
if (empty($path)) {
    // Try to get path from REQUEST_URI
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    error_log("DEBUG: Full REQUEST_URI: " . $requestUri);
    
    // Handle different URL patterns
    if (strpos($requestUri, '/3d_web/animal-showcase/api/') !== false) {
        $path = str_replace('/3d_web/animal-showcase/api/', '', $requestUri);
        $path = trim($path, '/');
        error_log("DEBUG: Extracted path from /3d_web/animal-showcase/api/: " . $path);
    } elseif (strpos($requestUri, '/animal-showcase/api/') !== false) {
        $path = str_replace('/animal-showcase/api/', '', $requestUri);
        $path = trim($path, '/');
        error_log("DEBUG: Extracted path from /animal-showcase/api/: " . $path);
    } else {
        // Fallback: try to extract from any /api/ pattern
        if (strpos($requestUri, '/api/') !== false) {
            $path = substr($requestUri, strpos($requestUri, '/api/') + 5);
            $path = trim($path, '/');
            error_log("DEBUG: Extracted path from generic /api/: " . $path);
        }
    }
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Parse the path to determine the endpoint
$pathParts = explode('/', trim($path, '/'));
$endpoint = $pathParts[0] ?? '';

// Debug: Log the path extraction
error_log("Original REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
error_log("Extracted path: " . $path);
error_log("Request method: " . $method);
error_log("Path parts: " . json_encode($pathParts));
error_log("Endpoint: " . $endpoint);

try {
    // Include required files
    error_log("Loading required files...");
    require_once 'config.php';
    require_once 'AnimalController.php';
    require_once 'MeshyService.php';
    require_once 'animals_new.php'; // Added for the new endpoint
    error_log("Required files loaded successfully");
    
    // Initialize database and controllers
    error_log("Initializing database connection...");
    $db = Database::getInstance()->getConnection();
    if (!$db) {
        throw new Exception('Database connection failed - no connection object returned');
    }
    
    // Test database connection
    try {
        error_log("Testing database connection...");
        $db->query("SELECT 1");
        error_log("Database connection test successful");
        
        // Test if animals_new table exists (new schema)
        $stmt = $db->query("SHOW TABLES LIKE 'animals_new'");
        if ($stmt->rowCount() == 0) {
            error_log("Warning: Table 'animals_new' does not exist, but continuing...");
        } else {
            error_log("Table 'animals_new' exists");
        }
        
    } catch (Exception $e) {
        error_log("Database connection test failed: " . $e->getMessage());
        // Don't throw exception, just log warning
        error_log("Warning: Database test failed but continuing...");
    }
    
    error_log("Initializing controllers...");
    $controller = new AnimalController($db);
    $meshyService = new MeshyService();
    error_log("Controllers initialized successfully");
    
    // Handle API endpoints
    error_log("Handling endpoint: " . $endpoint);
    
    switch ($endpoint) {
        case 'animals':
            if ($method === 'GET') {
                // Sử dụng file animals_clean.php hoàn toàn mới
                include_once 'animals_clean.php';
                exit(0);
            } else {
                header("Content-Type: application/json; charset=UTF-8");
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit(0);
            }
            break;
            
        case 'check-database':
            if ($method === 'GET') {
                header("Content-Type: application/json; charset=UTF-8");
                include_once '../check_and_populate_database.php';
                exit(0);
            } else {
                header("Content-Type: application/json; charset=UTF-8");
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit(0);
            }
            break;
            
        case 'map-media':
            if ($method === 'GET') {
                header("Content-Type: application/json; charset=UTF-8");
                include_once '../auto_map_media_by_name.php';
                exit(0);
            } else {
                header("Content-Type: application/json; charset=UTF-8");
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit(0);
            }
            break;
            
        case 'animal':
            if ($method === 'GET') {
                header("Content-Type: application/json; charset=UTF-8");
                
                // Get animal ID from path
                $animalId = $pathParts[1] ?? null;
                if (!$animalId) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Animal ID is required']);
                    exit(0);
                }
                
                try {
                    // Use new database schema
                    $stmt = $db->prepare("
                        SELECT an.*, asp.species_name, h.name AS habitat_name, cs.name AS conservation_status_name
                        FROM animals_new an
                        LEFT JOIN animal_species asp ON an.species_id = asp.id
                        LEFT JOIN habitats h ON an.habitat_id = h.id
                        LEFT JOIN conservation_statuses cs ON an.conservation_status_id = cs.id
                        WHERE an.id = ?
                    ");
                    $stmt->execute([$animalId]);
                    $animal = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($animal) {
                        echo json_encode($animal);
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Animal not found']);
                    }
                    exit(0);
                    
                } catch (Exception $e) {
                    error_log("Error fetching animal: " . $e->getMessage());
                    http_response_code(500);
                    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
                    exit(0);
                }
            } else {
                header("Content-Type: application/json; charset=UTF-8");
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit(0);
            }
            break;
            
        case 'edit':
            if ($method === 'POST') {
                header("Content-Type: application/json; charset=UTF-8");
                
                // Get animal ID from path
                $animalId = $pathParts[1] ?? null;
                if (!$animalId) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Animal ID is required']);
                    exit(0);
                }
                
                // Get form data
                $name = $_POST['name'] ?? '';
                $species = $_POST['species'] ?? '';
                $description = $_POST['description'] ?? '';
                $habitat_type = $_POST['habitat_type'] ?? 'trên cạn';
                $conservation_status = $_POST['conservation_status'] ?? 'ít quan tâm';
                $population_count = $_POST['population_count'] ?? '';
                
                // Validate required fields
                if (empty($name) || empty($species)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Name and species are required']);
                    exit(0);
                }
                
                try {
                    // Update animal in database
                    $stmt = $db->prepare("
                        UPDATE animals 
                        SET name = ?, species = ?, description = ?, 
                            habitat_type = ?, conservation_status = ?, population_count = ?
                        WHERE id = ?
                    ");
                    
                    $result = $stmt->execute([
                        $name, $species, $description, 
                        $habitat_type, $conservation_status, $population_count, 
                        $animalId
                    ]);
                    
                    if ($result) {
                        echo json_encode(['success' => true, 'message' => 'Animal updated successfully']);
                    } else {
                        echo json_encode(['error' => 'Failed to update animal']);
                    }
                    exit(0);
                    
                } catch (Exception $e) {
                    error_log("Error updating animal: " . $e->getMessage());
                    http_response_code(500);
                    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
                    exit(0);
                }
            } else {
                header("Content-Type: application/json; charset=UTF-8");
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit(0);
            }
            break;
            
        case 'stats':
            if ($method === 'GET') {
                header("Content-Type: application/json; charset=UTF-8");
                $stats = $controller->getStats();
                echo json_encode($stats);
                exit(0); // QUAN TRỌNG: Thoát ngay sau khi trả về JSON
            } else {
                header("Content-Type: application/json; charset=UTF-8");
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit(0);
            }
            break;
            
        default:
            header("Content-Type: application/json; charset=UTF-8");
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            exit(0);
            break;
    }
    
} catch (Exception $e) {
    error_log("Fatal error during initialization: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Initialization failed: ' . $e->getMessage()]);
    exit;
}
?>