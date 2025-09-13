<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Kết nối database
    $host = 'localhost';
    $dbname = 'animal_showcase';
    $username = 'root';
    $password = '';
    
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Đường dẫn thư mục
    $images_folder = __DIR__ . '/uploads/images/';
    $models_folder = __DIR__ . '/uploads/models/';
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'map_images':
            $result = mapImagesToAnimals($db, $images_folder);
            echo json_encode($result);
            break;
            
        case 'map_models':
            $result = mapModelsToAnimals($db, $models_folder);
            echo json_encode($result);
            break;
            
        case 'map_all':
            $result = mapAllMedia($db, $images_folder, $models_folder);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Action không hợp lệ. Sử dụng "map_images", "map_models" hoặc "map_all"'
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Lỗi: ' . $e->getMessage()
    ]);
}

/**
 * Map hình ảnh từ thư mục vào database
 */
function mapImagesToAnimals($db, $images_folder) {
    $details = [];
    $mapped_count = 0;
    $skipped_count = 0;
    
    try {
        // Lấy tất cả con vật trong database
        $stmt = $db->prepare("SELECT id, name FROM animals_new ORDER BY name");
        $stmt->execute();
        $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $details[] = "🔍 Tìm thấy " . count($animals) . " con vật trong database";
        
        foreach ($animals as $animal) {
            $animal_id = $animal['id'];
            $animal_name = $animal['name'];
            
            // Xóa media cũ của con vật này
            $stmt = $db->prepare("DELETE FROM animal_media WHERE animal_id = ?");
            $stmt->execute([$animal_id]);
            
            // Tìm file hình ảnh tương ứng
            $image_files = array_merge(
                glob($images_folder . $animal_name . '.png'),
                glob($images_folder . $animal_name . '.jpg'),
                glob($images_folder . $animal_name . '.jpeg')
            );
            
            if (!empty($image_files)) {
                $image_file = basename($image_files[0]);
                $relative_path = 'uploads/images/' . $image_file;
                
                // Thêm vào bảng animal_media
                $stmt = $db->prepare("INSERT INTO animal_media (animal_id, media_type, file_path, file_url, is_primary, created_at) VALUES (?, 'image', ?, ?, 1, NOW())");
                $success = $stmt->execute([$animal_id, $relative_path, $relative_path]);
                
                if ($success) {
                    $mapped_count++;
                    $details[] = "✅ Mapped hình ảnh: {$animal_name} → {$image_file}";
                } else {
                    $details[] = "❌ Lỗi map hình ảnh: {$animal_name}";
                }
            } else {
                $skipped_count++;
                $details[] = "⏭️ Không tìm thấy hình ảnh cho: {$animal_name}";
            }
        }
        
        return [
            'success' => true,
            'message' => "Hoàn thành map hình ảnh! Mapped: {$mapped_count}, Skipped: {$skipped_count}",
            'mapped_count' => $mapped_count,
            'skipped_count' => $skipped_count,
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
 * Map model 3D từ thư mục vào database
 */
function mapModelsToAnimals($db, $models_folder) {
    $details = [];
    $mapped_count = 0;
    $skipped_count = 0;
    
    try {
        // Lấy tất cả con vật trong database
        $stmt = $db->prepare("SELECT id, name FROM animals_new ORDER BY name");
        $stmt->execute();
        $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $details[] = "🔍 Tìm thấy " . count($animals) . " con vật trong database";
        
        foreach ($animals as $animal) {
            $animal_id = $animal['id'];
            $animal_name = $animal['name'];
            
            // Xóa media cũ của con vật này
            $stmt = $db->prepare("DELETE FROM animal_media WHERE animal_id = ? AND media_type = '3d_model'");
            $stmt->execute([$animal_id]);
            
            // Tìm file model 3D tương ứng
            $model_files = glob($models_folder . $animal_name . '.glb');
            
            if (!empty($model_files)) {
                $model_file = basename($model_files[0]);
                $relative_path = 'uploads/models/' . $model_file;
                $file_size = filesize($model_files[0]);
                
                // Thêm vào bảng animal_media
                $stmt = $db->prepare("INSERT INTO animal_media (animal_id, media_type, file_path, file_url, file_size, is_primary, created_at) VALUES (?, '3d_model', ?, ?, ?, 0, NOW())");
                $success = $stmt->execute([$animal_id, $relative_path, $relative_path, $file_size]);
                
                if ($success) {
                    $mapped_count++;
                    $details[] = "✅ Mapped model 3D: {$animal_name} → {$model_file} (" . formatFileSize($file_size) . ")";
                } else {
                    $details[] = "❌ Lỗi map model 3D: {$animal_name}";
                }
            } else {
                $skipped_count++;
                $details[] = "⏭️ Không tìm thấy model 3D cho: {$animal_name}";
            }
        }
        
        return [
            'success' => true,
            'message' => "Hoàn thành map model 3D! Mapped: {$mapped_count}, Skipped: {$skipped_count}",
            'mapped_count' => $mapped_count,
            'skipped_count' => $skipped_count,
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
 * Map tất cả media (hình ảnh + model 3D)
 */
function mapAllMedia($db, $images_folder, $models_folder) {
    $details = [];
    $total_mapped = 0;
    $total_skipped = 0;
    
    try {
        // Map hình ảnh
        $image_result = mapImagesToAnimals($db, $images_folder);
        if ($image_result['success']) {
            $total_mapped += $image_result['mapped_count'];
            $total_skipped += $image_result['skipped_count'];
            $details = array_merge($details, $image_result['details']);
        }
        
        $details[] = "--- MAP MODEL 3D ---";
        
        // Map model 3D
        $model_result = mapModelsToAnimals($db, $models_folder);
        if ($model_result['success']) {
            $total_mapped += $model_result['mapped_count'];
            $total_skipped += $model_result['skipped_count'];
            $details = array_merge($details, $model_result['details']);
        }
        
        return [
            'success' => true,
            'message' => "Hoàn thành map tất cả media! Total Mapped: {$total_mapped}, Total Skipped: {$total_skipped}",
            'total_mapped' => $total_mapped,
            'total_skipped' => $total_skipped,
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
 * Format file size
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>
