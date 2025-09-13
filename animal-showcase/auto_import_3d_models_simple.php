<?php
// auto_import_3d_models_simple.php - Auto import 3D models từ folder uploads/models/
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit(0);
}

try {
    require_once 'api/config.php';
    $db = Database::getInstance()->getConnection();
    
    $action = $_POST['action'] ?? '';
    $models_folder = 'uploads/models/';
    
    // Kiểm tra folder models có tồn tại không
    if (!is_dir($models_folder)) {
        throw new Exception("Folder models không tồn tại: " . $models_folder);
    }
    
    switch ($action) {
        case 'scan':
            // Quét và import models
            $result = scanAndImportModels($db, $models_folder);
            echo json_encode($result);
            break;
            
        case 'list':
            // Liệt kê models trong folder
            $result = listModelsInFolder($models_folder);
            echo json_encode($result);
            break;
            
        case 'debug_animals':
            // Debug: Hiển thị danh sách động vật trong database
            $result = debugAnimalsInDatabase($db);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode([
                'success' => false, 
                'error' => 'Action không hợp lệ. Sử dụng "scan", "list" hoặc "debug_animals"'
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Quét và import models vào database
 */
function scanAndImportModels($db, $models_folder) {
    $imported_count = 0;
    $skipped_count = 0;
    $error_count = 0;
    $details = [];
    
    // Lấy danh sách file .glb trong folder
    $files = glob($models_folder . '*.glb');
    
    if (empty($files)) {
        return [
            'success' => true,
            'message' => 'Không tìm thấy file .glb nào trong folder models',
            'imported_count' => 0,
            'skipped_count' => 0,
            'error_count' => 0,
            'details' => []
        ];
    }
    
    foreach ($files as $file_path) {
        try {
            $file_name = basename($file_path);
            $file_size = filesize($file_path);
            $relative_path = 'uploads/models/' . $file_name;
            
            // Tìm động vật tương ứng dựa trên tên file
            $animal_name = extractAnimalNameFromFileName($file_name);
            $animal = findAnimalByName($db, $animal_name);
            
            if (!$animal) {
                $details[] = "❌ Không tìm thấy động vật: '{$animal_name}' (từ file: '{$file_name}')";
                $skipped_count++;
                continue;
            }
            
            // Thêm thông tin debug
            $details[] = "🔍 File: '{$file_name}' → Tên nhận diện: '{$animal_name}' → Tìm thấy: '{$animal['name']}' (ID: {$animal['id']})";
            
            // Kiểm tra xem model đã tồn tại chưa
            $existing_model = checkExistingModel($db, $animal['id'], $relative_path);
            if ($existing_model) {
                $details[] = "⏭️ Đã tồn tại: {$animal_name} - {$file_name}";
                $skipped_count++;
                continue;
            }
            
            // Thêm model vào database
            $success = addModelToDatabase($db, $animal['id'], $relative_path, $file_name, $file_size);
            
            if ($success) {
                $details[] = "✅ Imported: {$animal_name} - {$file_name} (" . formatFileSize($file_size) . ")";
                $imported_count++;
            } else {
                $details[] = "❌ Lỗi import: {$animal_name} - {$file_name}";
                $error_count++;
            }
            
        } catch (Exception $e) {
            $details[] = "❌ Lỗi xử lý file {$file_name}: " . $e->getMessage();
            $error_count++;
        }
    }
    
    return [
        'success' => true,
        'message' => "Hoàn thành! Imported: {$imported_count}, Skipped: {$skipped_count}, Errors: {$error_count}",
        'imported_count' => $imported_count,
        'skipped_count' => $skipped_count,
        'error_count' => $error_count,
        'details' => $details
    ];
}

/**
 * Liệt kê models trong folder
 */
function listModelsInFolder($models_folder) {
    $models = [];
    $files = glob($models_folder . '*.glb');
    
    foreach ($files as $file_path) {
        $file_name = basename($file_path);
        $file_size = filesize($file_path);
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        // Gợi ý tên động vật từ tên file
        $suggested_animal = extractAnimalNameFromFileName($file_name);
        
        $models[] = [
            'file_name' => $file_name,
            'file_size' => $file_size,
            'extension' => $extension,
            'suggested_animal' => $suggested_animal,
            'formatted_size' => formatFileSize($file_size)
        ];
    }
    
    return [
        'success' => true,
        'message' => 'Danh sách models trong folder',
        'models' => $models,
        'count' => count($models)
    ];
}

/**
 * Trích xuất tên động vật từ tên file
 */
function extractAnimalNameFromFileName($file_name) {
    // Loại bỏ extension
    $name = pathinfo($file_name, PATHINFO_FILENAME);
    
    // Chuyển đổi một số ký tự đặc biệt
    $name = str_replace(['_', '-'], ' ', $name);
    
    // Mapping chính xác từ tên file sang tên trong database
    $file_to_db_mapping = [
        // Cá
        'Cá ngừ vây vàng' => 'Cá ngừ vây vàng',
        'Cá rô đồng' => 'Cá rô đồng', 
        'Cá sấu Xiêm' => 'Cá sấu Xiêm',
        
        // Chim
        'Chim gõ kiến ngà' => 'Chim gõ kiến ngà',
        'Chim sáo đá' => 'Chim sáo đá',
        'Chim sẻ' => 'Chim sẻ',
        'Cò thìa' => 'Cò thìa',
        'Đại bàng biển bụng trắng' => 'Đại bàng biển bụng trắng',
        'Gà lôi lam mào trắng' => 'Gà lôi lam mào trắng',
        'Sếu đầu đỏ' => 'Sếu đầu đỏ',
        
        // Động vật có vú
        'Cầy vằn bắc' => 'Cầy vằn bắc',
        'Hổ Đông Dương' => 'Hổ Đông Dương',
        'Khỉ vàng' => 'Khỉ vàng',
        'Rái cá thường' => 'Rái cá thường',
        'Sao La' => 'Sao la',  // Chú ý: Sao la (không viết hoa chữ L)
        'Tê giác java' => 'Tê giác Java',  // Chú ý: Java viết hoa
        'Trâu rừng' => 'Trâu rừng',
        'Voi châu Á' => 'Voi châu Á',
        'Voọc chà vá chân xám' => 'Voọc chà vá chân xám',
        
        // Bò sát
        'Rùa hoàn kiếm' => 'Rùa Hoàn Kiếm'  // Chú ý: Hoàn Kiếm viết hoa
    ];
    
    // Tìm mapping chính xác trước
    if (isset($file_to_db_mapping[$name])) {
        return $file_to_db_mapping[$name];
    }
    
    // Nếu không tìm thấy mapping chính xác, thử tìm kiếm gần đúng
    foreach ($file_to_db_mapping as $file_name_key => $db_name) {
        if (stripos($name, $file_name_key) !== false || stripos($file_name_key, $name) !== false) {
            return $db_name;
        }
    }
    
    // Nếu vẫn không tìm thấy, trả về tên gốc đã được xử lý
    return trim($name);
}

/**
 * Tìm động vật theo tên
 */
function findAnimalByName($db, $animal_name) {
    // 1. Tìm kiếm chính xác trước
    $stmt = $db->prepare("SELECT id, name FROM animals_new WHERE name = ?");
    $stmt->execute([$animal_name]);
    $animal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($animal) {
        return $animal;
    }
    
    // 2. Tìm kiếm không phân biệt hoa thường
    $stmt = $db->prepare("SELECT id, name FROM animals_new WHERE LOWER(name) = LOWER(?)");
    $stmt->execute([$animal_name]);
    $animal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($animal) {
        return $animal;
    }
    
    // 3. Tìm kiếm gần đúng (chứa tên)
    $stmt = $db->prepare("SELECT id, name FROM animals_new WHERE name LIKE ?");
    $stmt->execute(["%{$animal_name}%"]);
    $animal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($animal) {
        return $animal;
    }
    
    // 4. Tìm kiếm ngược lại (tên động vật chứa tên file)
    $stmt = $db->prepare("SELECT id, name FROM animals_new WHERE ? LIKE CONCAT('%', name, '%')");
    $stmt->execute([$animal_name]);
    $animal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($animal) {
        return $animal;
    }
    
    // 5. Tìm kiếm theo từ khóa chính (bỏ qua các từ phụ)
    $keywords = explode(' ', $animal_name);
    foreach ($keywords as $keyword) {
        if (strlen($keyword) > 2) { // Chỉ tìm từ có độ dài > 2 ký tự
            $stmt = $db->prepare("SELECT id, name FROM animals_new WHERE name LIKE ?");
            $stmt->execute(["%{$keyword}%"]);
            $animal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($animal) {
                return $animal;
            }
        }
    }
    
    return null;
}

/**
 * Kiểm tra model đã tồn tại chưa
 */
function checkExistingModel($db, $animal_id, $model_path) {
    $stmt = $db->prepare("
        SELECT id FROM animal_media 
        WHERE animal_id = ? AND file_url = ? AND media_type = '3d_model'
    ");
    $stmt->execute([$animal_id, $model_path]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Thêm model vào database
 */
function addModelToDatabase($db, $animal_id, $model_path, $file_name, $file_size) {
    try {
        $stmt = $db->prepare("
            INSERT INTO animal_media (animal_id, file_url, media_type, is_primary, created_at) 
            VALUES (?, ?, '3d_model', 0, NOW())
        ");
        return $stmt->execute([$animal_id, $model_path]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Debug: Hiển thị danh sách động vật trong database
 */
function debugAnimalsInDatabase($db) {
    try {
        $stmt = $db->prepare("SELECT id, name FROM animals_new ORDER BY name");
        $stmt->execute();
        $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'message' => 'Danh sách động vật trong database',
            'animals' => $animals,
            'count' => count($animals)
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Lỗi khi lấy danh sách động vật: ' . $e->getMessage()
        ];
    }
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>
