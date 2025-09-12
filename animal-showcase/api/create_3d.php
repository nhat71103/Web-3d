<?php
// api/create_3d.php - API endpoint để tạo model 3D từ Meshy
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
    
    if (!$animal['image_path']) {
        throw new Exception('Animal has no image to generate 3D model');
    }
    
    // Khởi tạo MeshyService
    $meshyService = new MeshyService();
    
    // Cập nhật trạng thái thành processing
    $stmt = $db->prepare("UPDATE animals SET status_3d = 'processing', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$animalId]);
    
    // Tạo model 3D từ ảnh
    $result = $meshyService->generateModel([
        'image_path' => $animal['image_path'],
        'description' => $animal['description']
    ]);
    
    if (!$result['success']) {
        // Cập nhật trạng thái thành failed
        $stmt = $db->prepare("UPDATE animals SET status_3d = 'failed', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$animalId]);
        
        throw new Exception('Failed to generate 3D model: ' . $result['error']);
    }
    
    // Lấy task ID từ response
    $taskId = $result['data']['result'] ?? null;
    
    if (!$taskId) {
        throw new Exception('No task ID received from Meshy');
    }
    
    // Cập nhật database với task ID
    $stmt = $db->prepare("
        UPDATE animals 
        SET meshy_task_id = ?, 
            status_3d = 'processing', 
            updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$taskId, $animalId]);
    
    // Log request
    $meshyService->logMeshyOperation(
        $animalId,
        $taskId,
        null,
        ['image_path' => $animal['image_path'], 'description' => $animal['description']],
        $result['data'],
        'processing'
    );
    
    echo json_encode([
        'success' => true,
        'message' => '3D model generation started successfully',
        'task_id' => $taskId,
        'status' => 'processing'
    ]);
    
} catch (Exception $e) {
    error_log("Error in create_3d.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
