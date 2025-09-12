<?php
// api/MeshyService.php - Meshy AI 3D Model Generation Service
require_once 'config.php';

class MeshyService {
    private $apiKey;
    private $db;
    
    public function __construct() {
        $this->apiKey = MESHY_API_KEY;
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Tạo 3D model (wrapper method cho API)
    public function generateModel($data) {
        try {
            // Debug logging
            error_log("MeshyService generateModel called with data: " . json_encode($data));
            
            if (isset($data['image_path']) || isset($data['image_url'])) {
                // Generate from image (local path or URL)
                $imagePath = $data['image_path'] ?? $data['image_url'];
                error_log("Generating from image: " . $imagePath);
                return $this->generateModelFromImage($imagePath);
            } else {
                // Generate from text description
                error_log("Generating from text description: " . ($data['description'] ?? 'N/A'));
                return $this->generateModelFromText($data['description'] ?? '');
            }
        } catch (Exception $e) {
            error_log("MeshyService generateModel error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Tạo 3D model từ ảnh
    private function generateModelFromImage($imagePath) {
        try {
            // Check if API key is valid
            if (empty($this->apiKey) || $this->apiKey === 'your_meshy_api_key_here') {
                return [
                    'success' => false,
                    'error' => 'Meshy API key chưa được cấu hình. Vui lòng cập nhật API key trong config.php'
                ];
            }
            
            // Log API key for debugging (only first few characters)
            $apiKeyPreview = substr($this->apiKey, 0, 10) . '...';
            error_log("Using Meshy API key: " . $apiKeyPreview);
            
            // Check if image file exists and get full path
            if (!filter_var($imagePath, FILTER_VALIDATE_URL)) {
                // It's a local path, check if file exists
                $fullImagePath = '../' . $imagePath;
                if (!file_exists($fullImagePath)) {
                    throw new Exception('Image file not found: ' . $fullImagePath);
                }
                error_log("Local image path: " . $fullImagePath);
                
                // Use direct file upload instead of URL
                $uploadResult = $this->uploadImageToMeshyStorage($fullImagePath);
                
                if (!$uploadResult['success']) {
                    return $uploadResult;
                }
                
                // Sau khi upload ảnh thành công, tạo 3D model
                $fileUrl = $uploadResult['data']['file_url'];
                
                $postData = json_encode([
                    'image_url' => $fileUrl,
                    'art_style' => 'realistic',
                    'negative_prompt' => '',
                    'target_polycount' => 10000
                ]);
                
                return $this->callMeshyAPI($postData, 'image-to-3d');
            } else {
                // It's already a full URL, use URL approach
                error_log("Using image URL for Meshy API: " . $imagePath);
                
                $postData = json_encode([
                    'image_url' => $imagePath,
                    'art_style' => 'realistic',
                    'negative_prompt' => '',
                    'target_polycount' => 10000
                ]);
                
                return $this->callMeshyAPI($postData, 'image-to-3d');
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Helper method để gọi Meshy API (multipart)
    private function callMeshyAPIMultipart($url, $method = 'POST', $postData = null, $isMultipart = false) {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30
            ]);
            
            if ($method === 'POST') {
                curl_setopt($curl, CURLOPT_POST, true);
                if ($postData) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
                }
            }
            
            $headers = ['Authorization: Bearer ' . $this->apiKey];
            if (!$isMultipart) {
                $headers[] = 'Content-Type: application/json';
            }
            
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            curl_close($curl);
            
            // Log response for debugging
            error_log("Meshy API Response - HTTP Code: " . $httpCode);
            error_log("Meshy API Response Body: " . $response);
            if (!empty($curlError)) {
                error_log("CURL Error: " . $curlError);
            }
            
            if ($httpCode !== 200 && $httpCode !== 202) {
                $errorMsg = 'Meshy API request failed. HTTP Code: ' . $httpCode;
                if ($response) {
                    $responseData = json_decode($response, true);
                    if (isset($responseData['error'])) {
                        $errorMsg .= '. Error: ' . $responseData['error'];
                    } else {
                        $errorMsg .= '. Response: ' . $response;
                    }
                }
                throw new Exception($errorMsg);
            }
            
            $result = json_decode($response, true);
            
            return [
                'success' => true,
                'data' => $result
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Helper method để gọi Meshy API (JSON)
    private function callMeshyAPI($postData, $endpoint) {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.meshy.ai/v1/' . $endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->apiKey,
                    'Content-Type: application/json'
                ],
                CURLOPT_TIMEOUT => 30
            ]);
            
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            curl_close($curl);
            
            // Log response for debugging
            error_log("Meshy API Response - HTTP Code: " . $httpCode);
            error_log("Meshy API Response Body: " . $response);
            if (!empty($curlError)) {
                error_log("CURL Error: " . $curlError);
            }
            
            if ($httpCode !== 200 && $httpCode !== 202) {
                $errorMsg = 'Meshy API request failed for ' . $endpoint . '. HTTP Code: ' . $httpCode;
                if ($response) {
                    $responseData = json_decode($response, true);
                    if (isset($responseData['error'])) {
                        $errorMsg .= '. Error: ' . $responseData['error'];
                    } else {
                        $errorMsg .= '. Response: ' . $response;
                    }
                }
                throw new Exception($errorMsg);
            }
            
            $result = json_decode($response, true);
            
            if (isset($result['result'])) {
                return [
                    'success' => true,
                    'data' => [
                        'result' => $result['result']
                    ],
                    'task_id' => $result['result'],
                    'status' => 'processing'
                ];
            } else {
                throw new Exception('Invalid response from Meshy API for ' . $endpoint);
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Upload ảnh trực tiếp lên Meshy API (sử dụng flow mới)
    private function uploadImageToMeshy($imagePath) {
        try {
            error_log("Uploading image to Meshy storage: " . $imagePath);
            
            // Upload ảnh lên Meshy storage trước
            $uploadResult = $this->uploadImageToMeshyStorage($imagePath);
            
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            
            $fileUrl = $uploadResult['data']['file_url'];
            
            // Sau đó tạo 3D model từ URL ảnh
            $postData = json_encode([
                'image_url' => $fileUrl,
                'art_style' => 'realistic',
                'negative_prompt' => '',
                'target_polycount' => 10000
            ]);
            
            error_log("Creating 3D model from uploaded image URL: " . $fileUrl);
            
            // Gọi Meshy API để tạo 3D model
            return $this->callMeshyAPI($postData, 'image-to-3d');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Tạo 3D model từ text
    private function generateModelFromText($prompt) {
        try {
            // Check if API key is valid
            if (empty($this->apiKey) || $this->apiKey === 'your_meshy_api_key_here') {
                return [
                    'success' => false,
                    'error' => 'Meshy API key chưa được cấu hình. Vui lòng cập nhật API key trong config.php'
                ];
            }
            
            $postData = json_encode([
                'prompt' => $prompt,
                'art_style' => 'realistic', // Default art style
                'negative_prompt' => '',
                'target_polycount' => 10000
            ]);
            
            // Sử dụng helper method
            return $this->callMeshyAPI($postData, 'text-to-3d');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Kiểm tra trạng thái 3D model (legacy method - giữ lại để tương thích)
    public function checkStatus($taskId) {
        return $this->checkTaskStatus($taskId);
    }

    // Kiểm tra trạng thái task 3D
    public function checkTaskStatus($taskId) {
        try {
            $url = MESHY_API_BASE . '/v1/tasks/' . $taskId;
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->apiKey
                ],
                CURLOPT_TIMEOUT => 30
            ]);
            
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            
            if ($httpCode !== 200 && $httpCode !== 202) {
                throw new Exception('Failed to check Meshy task status. HTTP Code: ' . $httpCode);
            }
            
            $result = json_decode($response, true);
            
            if (isset($result['result'])) {
                $status = $result['result']['status'];
                
                return [
                    'success' => true,
                    'status' => $status,
                    'data' => $result['result']
                ];
            } else {
                throw new Exception('Invalid response from Meshy API');
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Download 3D model (legacy method - giữ lại để tương thích)
    public function downloadModel($taskId) {
        // Redirect to new method
        return $this->downloadModelNew($taskId, null);
    }

    // Download 3D model với task ID và animal ID
    public function downloadModelNew($taskId, $animalId) {
        try {
            // Get task details first (sử dụng task ID thay vì model ID)
            $taskResult = $this->checkTaskStatus($taskId);
            if (!$taskResult['success']) {
                return $taskResult;
            }
            
            $taskData = $taskResult['data'];
            
            // Debug log
            error_log("Task data: " . json_encode($taskData));
            
            // Kiểm tra trạng thái task
            if ($taskData['status'] !== 'SUCCEEDED') {
                return [
                    'success' => false,
                    'error' => 'Task not completed yet. Status: ' . $taskData['status']
                ];
            }
            
            // Lấy URLs từ task data
            $modelUrl = $taskData['model_url'] ?? null;
            $thumbnailUrl = $taskData['thumbnail_url'] ?? null;
            
            if (!$modelUrl) {
                return [
                    'success' => false,
                    'error' => 'No model URL found'
                ];
            }
            
            // Create models directory if it doesn't exist
            $modelsDir = '../uploads/models/';
            if (!is_dir($modelsDir)) {
                mkdir($modelsDir, 0755, true);
            }
            
            // Download model file
            $modelFileName = 'animal_' . $animalId . '_' . time() . '.glb';
            $modelFilePath = $modelsDir . $modelFileName;
            
            $modelContent = file_get_contents($modelUrl);
            if ($modelContent === false) {
                throw new Exception('Failed to download model file');
            }
            
            if (file_put_contents($modelFilePath, $modelContent) === false) {
                throw new Exception('Failed to save model file');
            }
            
            // Get file size
            $fileSize = filesize($modelFilePath);
            
            // Update database with model information
            if ($animalId) {
                $this->updateAnimalModelInfo($animalId, $taskId, $modelUrl, $thumbnailUrl, $modelFilePath, $fileSize);
            }
            
            return [
                'success' => true,
                'model_path' => 'uploads/models/' . $modelFileName,
                'model_size' => $fileSize,
                'model_url' => $modelUrl,
                'thumbnail_url' => $thumbnailUrl
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Download model failed: ' . $e->getMessage()
            ];
        }
    }
    
    // Download và lưu model
    private function downloadAndSaveModel($modelUrl, $taskId) {
        $modelsDir = '../models/';
        if (!is_dir($modelsDir)) {
            mkdir($modelsDir, 0777, true);
        }
        
        $fileName = 'model_' . $taskId . '.glb';
        $filePath = $modelsDir . $fileName;
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $modelUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 300 // 5 minutes for large files
        ]);
        
        $modelData = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to download model file');
        }
        
        if (file_put_contents($filePath, $modelData) === false) {
            throw new Exception('Failed to save model file');
        }
        
        return $filePath;
    }
    
    // Cập nhật trạng thái động vật
    private function updateAnimalStatus($animalId, $status) {
        try {
            $stmt = $this->db->prepare("
                UPDATE animals 
                SET status_3d = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            
            $stmt->execute([$status, $animalId]);
        } catch (Exception $e) {
            error_log("Failed to update animal status: " . $e->getMessage());
        }
    }

    // Method để gọi từ admin - xử lý một model cụ thể
    public function processModel($animalId) {
        try {
            // Lấy thông tin animal
            $stmt = $this->db->prepare("
                SELECT id, name, meshy_task_id, image_path, status_3d
                FROM animals 
                WHERE id = ? AND meshy_task_id IS NOT NULL
            ");
            $stmt->execute([$animalId]);
            $animal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$animal) {
                return [
                    'success' => false,
                    'error' => 'Animal not found or no Meshy task ID'
                ];
            }
            
            error_log("Processing specific model: " . $animal['name']);
            
            // Kiểm tra trạng thái
            $statusResult = $this->checkStatus($animal['meshy_task_id']);
            
            if ($statusResult['success'] && $statusResult['status'] === 'completed') {
                // Download model
                $downloadResult = $this->downloadModel($animal['meshy_task_id']);
                
                if ($downloadResult['success']) {
                    // Cập nhật database với thông tin model hoàn chỉnh
                    $this->updateAnimalModelInfo($animal['id'], $animal['meshy_task_id'], $downloadResult['model_url'], null, $downloadResult['local_path'], 0);
                    
                    return [
                        'success' => true,
                        'message' => 'Model downloaded successfully',
                        'model_path' => $downloadResult['local_path']
                    ];
                } else {
                    return [
                        'success' => false,
                        'error' => 'Failed to download model: ' . $downloadResult['error']
                    ];
                }
            } elseif ($statusResult['success']) {
                return [
                    'success' => false,
                    'error' => 'Model not ready yet. Status: ' . $statusResult['status']
                ];
            } else {
                return $statusResult;
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Lấy Animal ID từ Task ID
    private function getAnimalIdByTaskId($taskId) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM animals WHERE meshy_task_id = ?");
            $stmt->execute([$taskId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['id'] : null;
        } catch (Exception $e) {
            error_log("Failed to get animal ID by task ID: " . $e->getMessage());
            return null;
        }
    }

    // Log Meshy request
    private function logMeshyRequest($requestData, $taskId, $responseData, $status) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO meshy_logs (task_id, request_data, response_data, status)
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $taskId,
                json_encode($requestData),
                json_encode($responseData),
                $status
            ]);
            
        } catch (Exception $e) {
            error_log("Failed to log Meshy request: " . $e->getMessage());
        }
    }

    // Lấy model ID từ task completed
    public function getCompletedModel($taskId) {
        try {
            $url = MESHY_API_BASE . '/v1/tasks/' . $taskId;
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->apiKey
                ],
                CURLOPT_TIMEOUT => 30
            ]);
            
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, INFO_HTTP_CODE);
            curl_close($curl);
            
            if ($httpCode !== 200) {
                throw new Exception('Failed to get task details. HTTP Code: ' . $httpCode);
            }
            
            $result = json_decode($response, true);
            
            if (!$result) {
                throw new Exception('Invalid JSON response from Meshy API');
            }
            
            if (isset($result['result'])) {
                $taskData = $result['result'];
                
                if ($taskData['status'] !== 'completed') {
                    return [
                        'success' => false,
                        'error' => 'Task not completed yet. Status: ' . $taskData['status']
                    ];
                }
                
                $modelId = $taskData['model_id'] ?? null;
                
                if (!$modelId) {
                    return [
                        'success' => false,
                        'error' => 'No model ID found in completed task'
                    ];
                }
                
                return [
                    'success' => true,
                    'data' => [
                        'model_id' => $modelId,
                        'task_data' => $taskData
                    ]
                ];
            } else {
                throw new Exception('Invalid response structure from Meshy API');
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Get completed model failed: ' . $e->getMessage()
            ];
        }
    }

    // Cập nhật thông tin model 3D vào database
    public function updateAnimalModelInfo($animalId, $modelId, $modelUrl, $thumbnailUrl, $modelFilePath, $fileSize) {
        try {
            $stmt = $this->db->prepare("
                UPDATE animals 
                SET meshy_model_id = ?,
                    meshy_model_url = ?,
                    meshy_thumbnail_url = ?,
                    model_file_path = ?,
                    model_file_size = ?,
                    model_created_at = NOW(),
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $modelId,
                $modelUrl,
                $thumbnailUrl,
                $modelFilePath,
                $fileSize,
                $animalId
            ]);
            
            return [
                'success' => true,
                'message' => 'Animal model info updated successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to update animal model info: ' . $e->getMessage()
            ];
        }
    }

    // Log operation vào meshy_logs
    public function logMeshyOperation($animalId, $taskId, $modelId, $requestData, $responseData, $status, $errorMessage = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO meshy_logs (animal_id, task_id, model_id, request_data, response_data, status, error_message)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $animalId,
                $taskId,
                $modelId,
                json_encode($requestData),
                json_encode($responseData),
                $status,
                $errorMessage
            ]);
            
            return [
                'success' => true,
                'message' => 'Operation logged successfully'
            ];
            
        } catch (Exception $e) {
            error_log("Failed to log Meshy operation: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to log operation: ' . $e->getMessage()
            ];
        }
    }

    // Lấy thông tin chi tiết của model
    public function getModelDetails($modelId) {
        try {
            $url = MESHY_API_BASE . '/v1/models/' . $modelId;
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->apiKey
                ],
                CURLOPT_TIMEOUT => 30
            ]);
            
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            
            if ($httpCode !== 200) {
                throw new Exception('Failed to get model details. HTTP Code: ' . $httpCode);
            }
            
            $result = json_decode($response, true);
            
            if (!$result) {
                throw new Exception('Invalid JSON response from Meshy API');
            }
            
            return [
                'success' => true,
                'data' => $result
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Get model details failed: ' . $e->getMessage()
            ];
        }
    }

    // Upload ảnh lên Meshy storage
    public function uploadImageToMeshyStorage($imagePath) {
        try {
            if (!file_exists($imagePath)) {
                throw new Exception('Image file not found: ' . $imagePath);
            }
            
            $url = MESHY_API_BASE . '/v1/files';
            
            $postData = [
                'file' => new CURLFile($imagePath)
            ];
            
            $result = $this->callMeshyAPIMultipart($url, 'POST', $postData, true);
            
            if (!$result['success']) {
                return $result;
            }
            
            $fileData = $result['data'];
            $fileUrl = $fileData['url'] ?? null;
            
            if (!$fileUrl) {
                throw new Exception('No file URL returned from Meshy');
            }
            
            return [
                'success' => true,
                'data' => [
                    'file_url' => $fileUrl,
                    'file_data' => $fileData
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Upload image failed: ' . $e->getMessage()
            ];
        }
    }
}
?>
