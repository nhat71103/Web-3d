<?php
// api/check_3d_status.php - API endpoint để kiểm tra trạng thái model 3D
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';
require_once 'MeshyService.php';

try {
    // Kiểm tra method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method not allowed');
    }
    
    // Lấy animal ID từ query parameter
    $animalId = $_GET['animal_id'] ?? null;
    
    if (!$animalId) {
        throw new Exception('Animal ID is required');
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
    
    if (!$animal['meshy_task_id']) {
        throw new Exception('No 3D model task found for this animal');
    }
    
    // Khởi tạo MeshyService
    $meshyService = new MeshyService();
    
                    // Kiểm tra trạng thái task
                $statusResult = $meshyService->checkTaskStatus($animal['meshy_task_id']);
    
    if (!$statusResult['success']) {
        throw new Exception('Failed to check status: ' . $statusResult['error']);
    }
    
    $status = $statusResult['status'];
    
    // Nếu task hoàn thành, download model
    if ($status === 'completed') {
        try {
                            // Lấy model ID từ task completed
                $modelResult = $meshyService->getCompletedModel($animal['meshy_task_id']);
                
                if (!$modelResult['success']) {
                    throw new Exception('Failed to get model ID: ' . $modelResult['error']);
                }
                
                $modelId = $modelResult['data']['model_id'];
                
                // Download model với model ID
                $downloadResult = $meshyService->downloadModelNew($modelId, $animal['id']);
            
            if ($downloadResult['success']) {
                // Cập nhật database với thông tin model đầy đủ
                $stmt = $db->prepare("
                    UPDATE animals 
                    SET model_path = ?, 
                        status_3d = 'completed',
                        meshy_model_id = ?,
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
                    $modelId,
                    $downloadResult['model_url'],
                    $downloadResult['thumbnail_url'],
                    $downloadResult['model_path'],
                    $downloadResult['model_size'],
                    $animalId
                ]);
                
                                            // Log success
                            $meshyService->logMeshyOperation(
                                $animalId,
                                $animal['meshy_task_id'],
                                $modelId,
                                'Status check - completed',
                                $downloadResult,
                                'completed'
                            );
                
                echo json_encode([
                    'success' => true,
                    'status' => 'completed',
                    'message' => '3D model completed and downloaded',
                    'model_path' => $downloadResult['model_path'],
                    'model_url' => $downloadResult['model_url'],
                    'thumbnail_url' => $downloadResult['thumbnail_url']
                ]);
            } else {
                // Cập nhật trạng thái thành failed
                $stmt = $db->prepare("UPDATE animals SET status_3d = 'failed', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$animalId]);
                
                echo json_encode([
                    'success' => false,
                    'status' => 'failed',
                    'error' => 'Failed to download model: ' . $downloadResult['error']
                ]);
            }
        } catch (Exception $e) {
            // Cập nhật trạng thái thành failed
            $stmt = $db->prepare("UPDATE animals SET status_3d = 'failed', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$animalId]);
            
            throw new Exception('Failed to download completed model: ' . $e->getMessage());
        }
    } else {
        // Trạng thái khác (pending, processing, failed)
        echo json_encode([
            'success' => true,
            'status' => $status,
            'message' => '3D model is ' . $status,
            'task_id' => $animal['meshy_task_id']
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error in check_3d_status.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
