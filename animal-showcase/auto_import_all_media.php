<?php
// auto_import_all_media.php - Auto import cả hình ảnh và model 3D dựa trên tên động vật
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
    $images_folder = 'uploads/images/';
    $models_folder = 'uploads/models/';
    
    // Kiểm tra folder có tồn tại không
    if (!is_dir($images_folder)) {
        throw new Exception("Folder images không tồn tại: " . $images_folder);
    }
    if (!is_dir($models_folder)) {
        throw new Exception("Folder models không tồn tại: " . $models_folder);
    }
    
    switch ($action) {
        case 'import_all':
            // Import cả hình ảnh và model 3D
            $result = importAllMedia($db, $images_folder, $models_folder);
            echo json_encode($result);
            break;
            
        case 'import_images':
            // Chỉ import hình ảnh
            $result = importImagesOnly($db, $images_folder);
            echo json_encode($result);
            break;
            
        case 'import_models':
            // Chỉ import model 3D
            $result = importModelsOnly($db, $models_folder);
            echo json_encode($result);
            break;
            
        case 'list_files':
            // Liệt kê tất cả files
            $result = listAllFiles($images_folder, $models_folder);
            echo json_encode($result);
            break;
            
        case 'debug_animals':
            // Debug: Hiển thị danh sách động vật trong database
            $result = debugAnimalsInDatabase($db);
            echo json_encode($result);
            break;
            
        case 'test_mapping':
            // Test mapping giữa file và database
            $result = testFileMapping($images_folder, $models_folder, $db);
            echo json_encode($result);
            break;
            
        case 'quick_import_ca_sau_xiem':
            // Quick import Cá sấu Xiêm
            $result = quickImportCaSauXiem($db, $images_folder, $models_folder);
            echo json_encode($result);
            break;
            
        case 'simple_import_ca_sau_xiem':
            // Simple import Cá sấu Xiêm như các con vật cũ
            $result = simpleImportCaSauXiem($db, $images_folder, $models_folder);
            echo json_encode($result);
            break;
            
        case 'auto_import_all_animals':
            // Auto import tất cả con vật có file tương ứng
            $result = autoImportAllAnimals($db, $images_folder, $models_folder);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode([
                'success' => false, 
                'error' => 'Action không hợp lệ. Sử dụng "import_all", "import_images", "import_models", "list_files", "debug_animals", "test_mapping", "quick_import_ca_sau_xiem", "simple_import_ca_sau_xiem" hoặc "auto_import_all_animals"'
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
 * Import cả hình ảnh và model 3D
 */
function importAllMedia($db, $images_folder, $models_folder) {
    $imported_images = 0;
    $imported_models = 0;
    $skipped_images = 0;
    $skipped_models = 0;
    $error_images = 0;
    $error_models = 0;
    $details = [];
    
    // 1. Import hình ảnh
    $details[] = "🖼️ === BẮT ĐẦU IMPORT HÌNH ẢNH ===";
    $image_result = importImagesOnly($db, $images_folder);
    $imported_images = $image_result['imported_count'];
    $skipped_images = $image_result['skipped_count'];
    $error_images = $image_result['error_count'];
    $details = array_merge($details, $image_result['details']);
    
    // 2. Import model 3D
    $details[] = "🎯 === BẮT ĐẦU IMPORT MODEL 3D ===";
    $model_result = importModelsOnly($db, $models_folder);
    $imported_models = $model_result['imported_count'];
    $skipped_models = $model_result['skipped_count'];
    $error_models = $model_result['error_count'];
    $details = array_merge($details, $model_result['details']);
    
    // 3. Tổng kết
    $total_imported = $imported_images + $imported_models;
    $total_skipped = $skipped_images + $skipped_models;
    $total_errors = $error_images + $error_models;
    
    $details[] = "📊 === TỔNG KẾT ===";
    $details[] = "✅ Hình ảnh: {$imported_images} imported, {$skipped_images} skipped, {$error_images} errors";
    $details[] = "✅ Model 3D: {$imported_models} imported, {$skipped_models} skipped, {$error_models} errors";
    $details[] = "🎯 TỔNG CỘNG: {$total_imported} imported, {$total_skipped} skipped, {$total_errors} errors";
    
    return [
        'success' => true,
        'message' => "Hoàn thành import tổng hợp! Tổng: {$total_imported} imported, {$total_skipped} skipped, {$total_errors} errors",
        'imported_images' => $imported_images,
        'imported_models' => $imported_models,
        'skipped_images' => $skipped_images,
        'skipped_models' => $skipped_models,
        'error_images' => $error_images,
        'error_models' => $error_models,
        'total_imported' => $total_imported,
        'total_skipped' => $total_skipped,
        'total_errors' => $total_errors,
        'details' => $details
    ];
}

/**
 * Import chỉ hình ảnh
 */
function importImagesOnly($db, $images_folder) {
    $imported_count = 0;
    $skipped_count = 0;
    $error_count = 0;
    $details = [];
    
    // Lấy danh sách file PNG/JPG trong folder
    $files = array_merge(
        glob($images_folder . '*.png'),
        glob($images_folder . '*.jpg'),
        glob($images_folder . '*.jpeg')
    );
    
    if (empty($files)) {
        return [
            'success' => true,
            'message' => 'Không tìm thấy file hình ảnh nào trong folder images',
            'imported_count' => 0,
            'skipped_count' => 0,
            'error_count' => 0,
            'details' => ['Không có file hình ảnh nào để import']
        ];
    }
    
    foreach ($files as $file_path) {
        try {
            $file_name = basename($file_path);
            $file_size = filesize($file_path);
            $relative_path = 'uploads/images/' . $file_name;
            
            // Tìm động vật tương ứng dựa trên tên file
            $animal_name = extractAnimalNameFromFileName($file_name);
            $animal = findAnimalByName($db, $animal_name);
            
            if (!$animal) {
                $details[] = "❌ Không tìm thấy động vật: '{$animal_name}' (từ file: '{$file_name}')";
                $skipped_count++;
                continue;
            }
            
            // Thêm thông tin debug
            $details[] = "🔍 Hình ảnh: '{$file_name}' → Tên nhận diện: '{$animal_name}' → Tìm thấy: '{$animal['name']}' (ID: {$animal['id']})";
            
            // Kiểm tra xem hình ảnh đã tồn tại chưa
            $existing_image = checkExistingMedia($db, $animal['id'], $relative_path, 'image');
            if ($existing_image) {
                $details[] = "⏭️ Đã tồn tại hình ảnh: {$animal_name} - {$file_name}";
                $skipped_count++;
                continue;
            }
            
            // Thêm hình ảnh vào database
            $success = addMediaToDatabase($db, $animal['id'], $relative_path, $file_name, $file_size, 'image', true);
            
            if ($success) {
                $details[] = "✅ Imported hình ảnh: {$animal_name} - {$file_name} (" . formatFileSize($file_size) . ")";
                $imported_count++;
            } else {
                $details[] = "❌ Lỗi import hình ảnh: {$animal_name} - {$file_name}";
                $error_count++;
            }
            
        } catch (Exception $e) {
            $details[] = "❌ Lỗi xử lý file hình ảnh {$file_name}: " . $e->getMessage();
            $error_count++;
        }
    }
    
    return [
        'success' => true,
        'message' => "Hoàn thành import hình ảnh! Imported: {$imported_count}, Skipped: {$skipped_count}, Errors: {$error_count}",
        'imported_count' => $imported_count,
        'skipped_count' => $skipped_count,
        'error_count' => $error_count,
        'details' => $details
    ];
}

/**
 * Import chỉ model 3D
 */
function importModelsOnly($db, $models_folder) {
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
            'details' => ['Không có file model 3D nào để import']
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
            $details[] = "🔍 Model 3D: '{$file_name}' → Tên nhận diện: '{$animal_name}' → Tìm thấy: '{$animal['name']}' (ID: {$animal['id']})";
            
            // Kiểm tra xem model đã tồn tại chưa
            $existing_model = checkExistingMedia($db, $animal['id'], $relative_path, '3d_model');
            if ($existing_model) {
                $details[] = "⏭️ Đã tồn tại model: {$animal_name} - {$file_name}";
                $skipped_count++;
                continue;
            }
            
            // Thêm model vào database
            $success = addMediaToDatabase($db, $animal['id'], $relative_path, $file_name, $file_size, '3d_model', false);
            
            if ($success) {
                $details[] = "✅ Imported model 3D: {$animal_name} - {$file_name} (" . formatFileSize($file_size) . ")";
                $imported_count++;
            } else {
                $details[] = "❌ Lỗi import model 3D: {$animal_name} - {$file_name}";
                $error_count++;
            }
            
        } catch (Exception $e) {
            $details[] = "❌ Lỗi xử lý file model {$file_name}: " . $e->getMessage();
            $error_count++;
        }
    }
    
    return [
        'success' => true,
        'message' => "Hoàn thành import model 3D! Imported: {$imported_count}, Skipped: {$skipped_count}, Errors: {$error_count}",
        'imported_count' => $imported_count,
        'skipped_count' => $skipped_count,
        'error_count' => $error_count,
        'details' => $details
    ];
}

/**
 * Liệt kê tất cả files
 */
function listAllFiles($images_folder, $models_folder) {
    $images = [];
    $models = [];
    
    // Lấy danh sách hình ảnh
    $image_files = array_merge(
        glob($images_folder . '*.png'),
        glob($images_folder . '*.jpg'),
        glob($images_folder . '*.jpeg')
    );
    
    foreach ($image_files as $file_path) {
        $file_name = basename($file_path);
        $file_size = filesize($file_path);
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $suggested_animal = extractAnimalNameFromFileName($file_name);
        
        $images[] = [
            'file_name' => $file_name,
            'file_size' => $file_size,
            'extension' => $extension,
            'suggested_animal' => $suggested_animal,
            'formatted_size' => formatFileSize($file_size)
        ];
    }
    
    // Lấy danh sách model 3D
    $model_files = glob($models_folder . '*.glb');
    
    foreach ($model_files as $file_path) {
        $file_name = basename($file_path);
        $file_size = filesize($file_path);
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
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
        'message' => 'Danh sách tất cả files',
        'images' => $images,
        'models' => $models,
        'image_count' => count($images),
        'model_count' => count($models),
        'total_count' => count($images) + count($models)
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
        'Rùa hoàn kiếm' => 'Rùa Hoàn Kiếm',  // Chú ý: Hoàn Kiếm viết hoa
        'Rùa Hoàn Kiếm' => 'Rùa Hoàn Kiếm'   // Trường hợp file đã viết hoa
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
 * Kiểm tra media đã tồn tại chưa
 */
function checkExistingMedia($db, $animal_id, $media_path, $media_type) {
    $stmt = $db->prepare("
        SELECT id FROM animal_media 
        WHERE animal_id = ? AND file_url = ? AND media_type = ?
    ");
    $stmt->execute([$animal_id, $media_path, $media_type]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Thêm media vào database
 */
function addMediaToDatabase($db, $animal_id, $media_path, $file_name, $file_size, $media_type, $is_primary = false) {
    try {
        $stmt = $db->prepare("
            INSERT INTO animal_media (animal_id, file_url, media_type, is_primary, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$animal_id, $media_path, $is_primary ? 1 : 0]);
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
 * Auto import tất cả con vật có file tương ứng
 */
function autoImportAllAnimals($db, $images_folder, $models_folder) {
    $details = [];
    $imported_count = 0;
    $skipped_count = 0;
    $error_count = 0;
    
    try {
        // Lấy tất cả con vật trong database
        $stmt = $db->prepare("SELECT id, name FROM animals_new ORDER BY name");
        $stmt->execute();
        $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $details[] = "🔍 Tìm thấy " . count($animals) . " con vật trong database";
        
        foreach ($animals as $animal) {
            $animal_id = $animal['id'];
            $animal_name = $animal['name'];
            $details[] = "--- Xử lý: {$animal_name} (ID: {$animal_id}) ---";
            
            $has_image = false;
            $has_model = false;
            
            // Tìm file hình ảnh tương ứng
            $image_files = array_merge(
                glob($images_folder . $animal_name . '.png'),
                glob($images_folder . $animal_name . '.jpg'),
                glob($images_folder . $animal_name . '.jpeg')
            );
            
            if (!empty($image_files)) {
                $image_file = basename($image_files[0]);
                $image_path = $images_folder . $image_file;
                $relative_path = 'uploads/images/' . $image_file;
                
                // Cập nhật image_path
                $stmt = $db->prepare("UPDATE animals_new SET image_path = ? WHERE id = ?");
                $success = $stmt->execute([$relative_path, $animal_id]);
                
                if ($success) {
                    $has_image = true;
                    $details[] = "✅ Imported hình ảnh: {$image_file}";
                } else {
                    $details[] = "❌ Lỗi import hình ảnh: {$image_file}";
                    $error_count++;
                }
            } else {
                $details[] = "⏭️ Không tìm thấy hình ảnh cho: {$animal_name}";
            }
            
            // Tìm file model 3D tương ứng
            $model_files = glob($models_folder . $animal_name . '.glb');
            
            if (!empty($model_files)) {
                $model_file = basename($model_files[0]);
                $model_path = $models_folder . $model_file;
                $relative_path = 'uploads/models/' . $model_file;
                $file_size = filesize($model_path);
                
                // Cập nhật model_path
                $stmt = $db->prepare("UPDATE animals_new SET model_path = ?, model_file_size = ?, status_3d = 'completed' WHERE id = ?");
                $success = $stmt->execute([$relative_path, $file_size, $animal_id]);
                
                if ($success) {
                    $has_model = true;
                    $details[] = "✅ Imported model 3D: {$model_file} (" . formatFileSize($file_size) . ")";
                } else {
                    $details[] = "❌ Lỗi import model 3D: {$model_file}";
                    $error_count++;
                }
            } else {
                $details[] = "⏭️ Không tìm thấy model 3D cho: {$animal_name}";
            }
            
            if ($has_image || $has_model) {
                $imported_count++;
            } else {
                $skipped_count++;
            }
        }
        
        return [
            'success' => true,
            'message' => "Hoàn thành auto import! Imported: {$imported_count}, Skipped: {$skipped_count}, Errors: {$error_count}",
            'imported_count' => $imported_count,
            'skipped_count' => $skipped_count,
            'error_count' => $error_count,
            'details' => $details
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Lỗi: ' . $e->getMessage()
        ];
    }
}

/**
 * Simple import Cá sấu Xiêm như các con vật cũ
 */
function simpleImportCaSauXiem($db, $images_folder, $models_folder) {
    $details = [];
    $image_status = 'Không tìm thấy';
    $model_status = 'Không tìm thấy';
    $animal_id = null;
    
    try {
        // Tìm động vật Cá sấu Xiêm
        $stmt = $db->prepare("SELECT id, name FROM animals_new WHERE name = ? OR name LIKE ?");
        $stmt->execute(['Cá sấu Xiêm', '%Cá sấu Xiêm%']);
        $animal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$animal) {
            return [
                'success' => false,
                'error' => 'Không tìm thấy động vật "Cá sấu Xiêm" trong database'
            ];
        }
        
        $animal_id = $animal['id'];
        $details[] = "✅ Tìm thấy động vật: {$animal['name']} (ID: {$animal_id})";
        
        // Import hình ảnh - cập nhật trực tiếp vào bảng animals_new
        $image_file = 'Cá sấu Xiêm.png';
        $image_path = $images_folder . $image_file;
        
        if (file_exists($image_path)) {
            $relative_path = 'uploads/images/' . $image_file;
            
            // Cập nhật trực tiếp vào bảng animals_new
            $stmt = $db->prepare("UPDATE animals_new SET image_path = ? WHERE id = ?");
            $success = $stmt->execute([$relative_path, $animal_id]);
            
            if ($success) {
                $image_status = 'Import thành công';
                $details[] = "✅ Updated image_path: {$relative_path}";
            } else {
                $image_status = 'Lỗi import';
                $details[] = "❌ Lỗi update image_path: {$relative_path}";
            }
        } else {
            $details[] = "❌ Không tìm thấy file hình ảnh: {$image_file}";
        }
        
        // Import model 3D - cập nhật trực tiếp vào bảng animals_new
        $model_file = 'Cá sấu Xiêm.glb';
        $model_path = $models_folder . $model_file;
        
        if (file_exists($model_path)) {
            $relative_path = 'uploads/models/' . $model_file;
            $file_size = filesize($model_path);
            
            // Cập nhật trực tiếp vào bảng animals_new
            $stmt = $db->prepare("UPDATE animals_new SET model_path = ?, model_file_size = ?, status_3d = 'completed' WHERE id = ?");
            $success = $stmt->execute([$relative_path, $file_size, $animal_id]);
            
            if ($success) {
                $model_status = 'Import thành công';
                $details[] = "✅ Updated model_path: {$relative_path} (Size: " . formatFileSize($file_size) . ")";
            } else {
                $model_status = 'Lỗi import';
                $details[] = "❌ Lỗi update model_path: {$relative_path}";
            }
        } else {
            $details[] = "❌ Không tìm thấy file model 3D: {$model_file}";
        }
        
        return [
            'success' => true,
            'message' => 'Hoàn thành simple import Cá sấu Xiêm (như các con vật cũ)',
            'animal_id' => $animal_id,
            'image_status' => $image_status,
            'model_status' => $model_status,
            'details' => $details
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Lỗi: ' . $e->getMessage()
        ];
    }
}

/**
 * Quick import Cá sấu Xiêm
 */
function quickImportCaSauXiem($db, $images_folder, $models_folder) {
    $details = [];
    $image_status = 'Không tìm thấy';
    $model_status = 'Không tìm thấy';
    $animal_id = null;
    
    try {
        // Tìm động vật Cá sấu Xiêm
        $stmt = $db->prepare("SELECT id, name FROM animals_new WHERE name = ? OR name LIKE ?");
        $stmt->execute(['Cá sấu Xiêm', '%Cá sấu Xiêm%']);
        $animal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$animal) {
            return [
                'success' => false,
                'error' => 'Không tìm thấy động vật "Cá sấu Xiêm" trong database'
            ];
        }
        
        $animal_id = $animal['id'];
        $details[] = "✅ Tìm thấy động vật: {$animal['name']} (ID: {$animal_id})";
        
        // Import hình ảnh
        $image_file = 'Cá sấu Xiêm.png';
        $image_path = $images_folder . $image_file;
        
        if (file_exists($image_path)) {
            $relative_path = 'uploads/images/' . $image_file;
            $file_size = filesize($image_path);
            
            // Kiểm tra đã tồn tại chưa
            $existing_image = checkExistingMedia($db, $animal_id, $relative_path, 'image');
            if ($existing_image) {
                $image_status = 'Đã tồn tại';
                $details[] = "⏭️ Hình ảnh đã tồn tại: {$image_file}";
            } else {
                // Thêm hình ảnh
                $success = addMediaToDatabase($db, $animal_id, $relative_path, $image_file, $file_size, 'image', true);
                if ($success) {
                    $image_status = 'Import thành công';
                    $details[] = "✅ Imported hình ảnh: {$image_file} (" . formatFileSize($file_size) . ")";
                } else {
                    $image_status = 'Lỗi import';
                    $details[] = "❌ Lỗi import hình ảnh: {$image_file}";
                }
            }
        } else {
            $details[] = "❌ Không tìm thấy file hình ảnh: {$image_file}";
        }
        
        // Import model 3D
        $model_file = 'Cá sấu Xiêm.glb';
        $model_path = $models_folder . $model_file;
        
        if (file_exists($model_path)) {
            $relative_path = 'uploads/models/' . $model_file;
            $file_size = filesize($model_path);
            
            // Kiểm tra đã tồn tại chưa
            $existing_model = checkExistingMedia($db, $animal_id, $relative_path, '3d_model');
            if ($existing_model) {
                $model_status = 'Đã tồn tại';
                $details[] = "⏭️ Model 3D đã tồn tại: {$model_file}";
            } else {
                // Thêm model 3D
                $success = addMediaToDatabase($db, $animal_id, $relative_path, $model_file, $file_size, '3d_model', false);
                if ($success) {
                    $model_status = 'Import thành công';
                    $details[] = "✅ Imported model 3D: {$model_file} (" . formatFileSize($file_size) . ")";
                } else {
                    $model_status = 'Lỗi import';
                    $details[] = "❌ Lỗi import model 3D: {$model_file}";
                }
            }
        } else {
            $details[] = "❌ Không tìm thấy file model 3D: {$model_file}";
        }
        
        return [
            'success' => true,
            'message' => 'Hoàn thành quick import Cá sấu Xiêm',
            'animal_id' => $animal_id,
            'image_status' => $image_status,
            'model_status' => $model_status,
            'details' => $details
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Lỗi: ' . $e->getMessage()
        ];
    }
}

/**
 * Test mapping giữa file và database
 */
function testFileMapping($images_folder, $models_folder, $db) {
    $results = [];
    
    // Lấy danh sách động vật trong database
    $stmt = $db->prepare("SELECT id, name FROM animals_new ORDER BY name");
    $stmt->execute();
    $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $animal_names = array_column($animals, 'name');
    
    // Test hình ảnh
    $image_files = array_merge(
        glob($images_folder . '*.png'),
        glob($images_folder . '*.jpg'),
        glob($images_folder . '*.jpeg')
    );
    
    $results['images'] = [];
    foreach ($image_files as $file_path) {
        $file_name = basename($file_path);
        $extracted_name = extractAnimalNameFromFileName($file_name);
        $found_animal = findAnimalByName($db, $extracted_name);
        
        $results['images'][] = [
            'file_name' => $file_name,
            'extracted_name' => $extracted_name,
            'found_animal' => $found_animal,
            'status' => $found_animal ? 'FOUND' : 'NOT_FOUND'
        ];
    }
    
    // Test model 3D
    $model_files = glob($models_folder . '*.glb');
    
    $results['models'] = [];
    foreach ($model_files as $file_path) {
        $file_name = basename($file_path);
        $extracted_name = extractAnimalNameFromFileName($file_name);
        $found_animal = findAnimalByName($db, $extracted_name);
        
        $results['models'][] = [
            'file_name' => $file_name,
            'extracted_name' => $extracted_name,
            'found_animal' => $found_animal,
            'status' => $found_animal ? 'FOUND' : 'NOT_FOUND'
        ];
    }
    
    return [
        'success' => true,
        'message' => 'Test mapping giữa file và database',
        'database_animals' => $animal_names,
        'results' => $results
    ];
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
