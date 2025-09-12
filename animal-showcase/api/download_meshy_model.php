<?php
// api/download_meshy_model.php - Download 3D model từ Meshy AI
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';
require_once 'MeshyService.php';

try {
    // Kiểm tra method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }
    
    // Lấy dữ liệu từ request
    $input = json_decode(file_get_contents('php://input'), true);
    $animalId = $input['animal_id'] ?? null;
    $taskId = $input['task_id'] ?? null;
    
    if (!$animalId) {
        throw new Exception('Animal ID is required');
    }
    
    if (!$taskId) {
        throw new Exception('Task ID is required');
    }
    
    // Kết nối database
    $db = Database::getInstance()->getConnection();
    
    // Lấy thông tin động vật
    $stmt = $db->prepare("SELECT * FROM animals WHERE id = ?");
    $stmt->execute([$animalId]);
    $animal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$animal) {
        throw new Exception('Animal not found');
    }
    
    // Khởi tạo MeshyService
    $meshyService = new MeshyService();
    
    // Download model trong background
    $downloadResult = $meshyService->downloadModelNew($taskId, $animalId);
    
    if ($downloadResult['success']) {
        // Cập nhật database với thông tin model hoàn chỉnh
        $stmt = $db->prepare("
            UPDATE animals 
            SET model_path = ?, 
                status_3d = 'completed',
                meshy_task_id = ?,
                meshy_model_url = ?,
                meshy_thumbnail_url = ?,
                model_file_path = ?,
                model_file_size = ?,
                model_created_at = NOW(),
                updated_at = NOW() 
            WHERE id = ?
        ");
        
        $stmt->execute([
            $downloadResult['model_path'],
            $taskId,
            $downloadResult['model_url'],
            $downloadResult['thumbnail_url'],
            $downloadResult['model_path'],
            $downloadResult['model_size'],
            $animalId
        ]);
        
        // Log success
        $meshyService->logMeshyOperation(
            $animalId,
            $taskId, // Use task ID
            null, // No model ID
            'Direct model download',
            $downloadResult,
            'completed'
        );
        
        echo json_encode([
            'success' => true,
            'message' => '3D model downloaded and saved successfully',
            'model_path' => $downloadResult['model_path'],
            'model_url' => $downloadResult['model_url'],
            'thumbnail_url' => $downloadResult['thumbnail_url'],
            'file_size' => $downloadResult['model_size']
        ]);
        
    } else {
        // Cập nhật trạng thái thành failed
        $stmt = $db->prepare("UPDATE animals SET status_3d = 'failed', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$animalId]);
        
        throw new Exception('Failed to download model: ' . $downloadResult['error']);
    }
    
} catch (Exception $e) {
    error_log("Error in download_meshy_model.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
