<?php
// api/AnimalController.php
require_once 'config.php';

class AnimalController {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Lấy tất cả động vật
    public function getAllAnimals() {
        try {
            error_log("getAllAnimals: Starting to fetch animals...");
            
            // Check if table exists
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'animals'");
            if ($stmt->rowCount() == 0) {
                error_log("Table 'animals' does not exist");
                return ['error' => 'Table animals does not exist'];
            }
            error_log("Table 'animals' exists, proceeding with query...");
            
            $stmt = $this->pdo->query("SELECT * FROM animals ORDER BY created_at DESC");
            $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Log for debugging
            error_log("Retrieved " . count($animals) . " animals from database");
            error_log("First animal data: " . json_encode($animals[0] ?? 'No animals'));
            
            return $animals;
        } catch (PDOException $e) {
            error_log("Database error in getAllAnimals: " . $e->getMessage());
            error_log("Error code: " . $e->getCode());
            return ['error' => 'Database error: ' . $e->getMessage()];
        } catch (Exception $e) {
            error_log("General error in getAllAnimals: " . $e->getMessage());
            return ['error' => 'General error: ' . $e->getMessage()];
        }
    }
    
    // Lấy một động vật theo ID
    public function getAnimalById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM animals WHERE id = ?");
            $stmt->execute([$id]);
            $animal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$animal) {
                return ['error' => 'Animal not found with ID: ' . $id];
            }
            
            return $animal;
        } catch (PDOException $e) {
            error_log("Database error in getAnimalById: " . $e->getMessage());
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Tạo động vật mới
    public function createAnimal($data) {
        try {
            // Validate required fields
            if (empty($data['name']) || empty($data['species'])) {
                return ['error' => 'Name and species are required'];
            }
            
            $stmt = $this->pdo->prepare("
                INSERT INTO animals (name, species, description, status_3d, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['name'],
                $data['species'],
                $data['description'] ?? '',
                $data['status_3d'] ?? 'pending'
            ]);
            
            $newId = $this->pdo->lastInsertId();
            error_log("Created new animal with ID: " . $newId);
            
            return [
                'id' => $newId, 
                'success' => true,
                'message' => 'Animal created successfully'
            ];
        } catch (PDOException $e) {
            error_log("Database error in createAnimal: " . $e->getMessage());
            return ['error' => 'Failed to create animal: ' . $e->getMessage()];
        }
    }
    
    // Cập nhật động vật
    public function updateAnimal($id, $data) {
        try {
            error_log("updateAnimal called with ID: " . $id);
            error_log("updateAnimal data: " . json_encode($data));
            
            // Validate required fields
            if (empty($data['name']) || empty($data['species'])) {
                error_log("Validation failed: missing name or species");
                return ['error' => 'Name and species are required'];
            }
            
            $stmt = $this->pdo->prepare("
                UPDATE animals 
                SET name = ?, species = ?, description = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $params = [
                $data['name'],
                $data['species'],
                $data['description'] ?? '',
                $id
            ];
            
            error_log("SQL params: " . json_encode($params));
            
            $stmt->execute($params);
            
            if ($stmt->rowCount() > 0) {
                error_log("Successfully updated animal with ID: " . $id);
                return ['success' => true, 'message' => 'Animal updated successfully'];
            } else {
                error_log("No rows affected for animal ID: " . $id);
                return ['error' => 'Animal not found with ID: ' . $id];
            }
        } catch (PDOException $e) {
            error_log("Database error in updateAnimal: " . $e->getMessage());
            return ['error' => 'Failed to update animal: ' . $e->getMessage()];
        }
    }
    
    // Xóa động vật
    public function deleteAnimal($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM animals WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                error_log("Deleted animal with ID: " . $id);
                return ['success' => true, 'message' => 'Animal deleted successfully'];
            } else {
                return ['error' => 'Animal not found with ID: ' . $id];
            }
        } catch (PDOException $e) {
            error_log("Database error in deleteAnimal: " . $e->getMessage());
            return ['error' => 'Failed to delete animal: ' . $e->getMessage()];
        }
    }
    
    // Upload ảnh cho động vật
    public function uploadImage($imageFile, $animalId) {
        try {
            // Kiểm tra file
            if (!isset($imageFile['tmp_name']) || !is_uploaded_file($imageFile['tmp_name'])) {
                return ['error' => 'Invalid file upload'];
            }
            
            // Kiểm tra kích thước file (max 10MB)
            if ($imageFile['size'] > 10 * 1024 * 1024) {
                return ['error' => 'File size too large. Maximum 10MB allowed.'];
            }
            
            // Kiểm tra loại file
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!in_array($imageFile['type'], $allowedTypes)) {
                return ['error' => 'Invalid file type. Only JPEG, PNG, and WebP allowed.'];
            }
            
            // Tạo thư mục uploads nếu chưa có
            $uploadDir = '../uploads/images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Tạo tên file duy nhất
            $fileName = 'animal_' . $animalId . '_' . time() . '.' . pathinfo($imageFile['name'], PATHINFO_EXTENSION);
            $filePath = $uploadDir . $fileName;
            
            // Di chuyển file
            if (move_uploaded_file($imageFile['tmp_name'], $filePath)) {
                // Cập nhật database
                $stmt = $this->pdo->prepare("UPDATE animals SET image_path = ? WHERE id = ?");
                $stmt->execute(['uploads/images/' . $fileName, $animalId]);
                
                error_log("Image uploaded successfully for animal ID: " . $animalId);
                
                return [
                    'success' => true,
                    'file_path' => 'uploads/images/' . $fileName,
                    'message' => 'Image uploaded successfully'
                ];
            } else {
                return ['error' => 'Failed to move uploaded file'];
            }
        } catch (Exception $e) {
            error_log("Upload error: " . $e->getMessage());
            return ['error' => 'Upload error: ' . $e->getMessage()];
        }
    }
    
    // Lấy tất cả loài động vật
    public function getAllSpecies() {
        try {
            $stmt = $this->pdo->query("SELECT DISTINCT species FROM animals ORDER BY species");
            $species = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $species;
        } catch (PDOException $e) {
            error_log("Database error in getAllSpecies: " . $e->getMessage());
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Lấy thống kê
    public function getStats() {
        try {
            error_log("getStats: Starting to fetch statistics...");
            
            // Check if table exists
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'animals'");
            if ($stmt->rowCount() == 0) {
                error_log("Table 'animals' does not exist in getStats");
                return ['error' => 'Table animals does not exist'];
            }
            error_log("Table 'animals' exists, proceeding with stats queries...");
            
            $stats = [];
            
            // Tổng số động vật
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM animals");
            $stats['total_animals'] = $stmt->fetchColumn();
            error_log("Total animals count: " . $stats['total_animals']);
            
            // Số động vật đang xử lý 3D
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM animals WHERE status_3d = 'processing'");
            $stats['processing_3d'] = $stats['total_animals'] > 0 ? $stmt->fetchColumn() : 0;
            error_log("Processing 3D count: " . $stats['processing_3d']);
            
            // Số động vật hoàn thành 3D
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM animals WHERE status_3d = 'completed'");
            $stats['completed_3d'] = $stats['total_animals'] > 0 ? $stmt->fetchColumn() : 0;
            error_log("Completed 3D count: " . $stats['completed_3d']);
            
            // Số loài động vật
            $stmt = $this->pdo->query("SELECT COUNT(DISTINCT species) FROM animals");
            $stats['total_species'] = $stats['total_animals'] > 0 ? $stmt->fetchColumn() : 0;
            error_log("Total species count: " . $stats['total_species']);
            
            error_log("Final stats: " . json_encode($stats));
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Database error in getStats: " . $e->getMessage());
            error_log("Error code: " . $e->getCode());
            return ['error' => 'Database error: ' . $e->getMessage()];
        } catch (Exception $e) {
            error_log("General error in getStats: " . $e->getMessage());
            return ['error' => 'General error: ' . $e->getMessage()];
        }
    }

    // Tạo 3D model cho động vật
    public function create3DModel($animalId) {
        try {
            // Kiểm tra động vật có tồn tại không
            $stmt = $this->pdo->prepare("SELECT * FROM animals WHERE id = ?");
            $stmt->execute([$animalId]);
            $animal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$animal) {
                return ['error' => 'Animal not found with ID: ' . $animalId];
            }
            
            // Kiểm tra xem đã có model 3D chưa
            if ($animal['status_3d'] === 'completed' && $animal['model_path']) {
                return ['error' => '3D model already exists for this animal'];
            }
            
            // Kiểm tra xem có ảnh không
            if (empty($animal['image_path'])) {
                return ['error' => 'Animal must have an image to create 3D model'];
            }
            
            // Gọi MeshyService để tạo model
            require_once 'MeshyService.php';
            $meshyService = new MeshyService();
            
            // Tạo model từ ảnh
            $result = $meshyService->generateModel([
                'image_path' => $animal['image_path']
            ]);
            
            if ($result['success']) {
                // Cập nhật trạng thái và task ID
                $stmt = $this->pdo->prepare("
                    UPDATE animals 
                    SET status_3d = 'processing', 
                        meshy_task_id = ?, 
                        updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$result['task_id'], $animalId]);
                
                return [
                    'success' => true,
                    'message' => '3D model generation started',
                    'task_id' => $result['task_id']
                ];
            } else {
                return [
                    'error' => 'Failed to start 3D model generation: ' . $result['error']
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error creating 3D model: " . $e->getMessage());
            return ['error' => 'Error creating 3D model: ' . $e->getMessage()];
        }
    }

    // Kiểm tra trạng thái 3D model
    public function check3DStatus($animalId) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM animals WHERE id = ?");
            $stmt->execute([$animalId]);
            $animal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$animal) {
                return ['error' => 'Animal not found with ID: ' . $animalId];
            }
            
            if (empty($animal['meshy_task_id'])) {
                return ['error' => 'No 3D model task found for this animal'];
            }
            
            // Gọi MeshyService để kiểm tra trạng thái
            require_once 'MeshyService.php';
            $meshyService = new MeshyService();
            
            $result = $meshyService->checkStatus($animal['meshy_task_id']);
            
            if ($result['success']) {
                // Nếu hoàn thành, download model
                if ($result['status'] === 'completed') {
                    $downloadResult = $meshyService->downloadModelNew($animal['meshy_task_id'], $animal['id']);
                    if ($downloadResult['success']) {
                        return [
                            'success' => true,
                            'status' => 'completed',
                            'model_path' => $downloadResult['model_path'],
                            'message' => '3D model completed and downloaded'
                        ];
                    }
                }
                
                return [
                    'success' => true,
                    'status' => $result['status'],
                    'message' => '3D model status: ' . $result['status']
                ];
            } else {
                return $result;
            }
            
        } catch (Exception $e) {
            error_log("Error checking 3D status: " . $e->getMessage());
            return ['error' => 'Error checking 3D status: ' . $e->getMessage()];
        }
    }
}
?>