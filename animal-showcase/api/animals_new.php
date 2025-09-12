<?php
// Bật hiển thị lỗi PHP để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    require_once 'config.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Config error: ' . $e->getMessage()]);
    exit;
}

class AnimalsNewAPI {
    private $db;
    
    public function __construct() {
        try {
            $this->db = Database::getInstance()->getConnection();
            if (!$this->db) {
                throw new Exception('Database connection object is null');
            }
        } catch (Exception $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    // Lấy danh sách động vật với đầy đủ thông tin từ các bảng liên quan
    public function getAnimals() {
        try {
            $query = "
                SELECT
                    an.id,
                    an.name,
                    an.description,
                    an.population_count,
                    an.created_at,
                    an.updated_at,
                    
                    asp.species_name,
                    asp.scientific_name,
                    asp.family,
                    asp.order_name,
                    asp.class_name,
                    
                    h.name AS habitat_name,
                    h.description AS habitat_description,
                    h.icon AS habitat_icon,
                    
                    cs.name AS conservation_status_name,
                    cs.description AS conservation_status_description,
                    cs.color_code AS conservation_status_color,
                    cs.priority AS conservation_status_priority,
                    
                    cd.iucn_status,
                    cd.population_trend,
                    cd.threats,
                    cd.conservation_actions,
                    cd.last_assessment_date,
                    
                    (SELECT GROUP_CONCAT(CONCAT(am.file_url, '||', am.media_type, '||', am.is_primary) SEPARATOR ';;')
                     FROM animal_media am WHERE am.animal_id = an.id) AS media_data
                FROM
                    animals_new an
                LEFT JOIN animal_species asp ON an.species_id = asp.id
                LEFT JOIN habitats h ON an.habitat_id = h.id
                LEFT JOIN conservation_statuses cs ON an.conservation_status_id = cs.id
                LEFT JOIN conservation_data cd ON an.id = cd.animal_id
                ORDER BY an.name ASC
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Xử lý media_data để trả về dạng mảng đối tượng
            foreach ($animals as &$animal) {
                $animal['media'] = [];
                if (!empty($animal['media_data'])) {
                    $media_items = explode(';;', $animal['media_data']);
                    foreach ($media_items as $item) {
                        list($file_url, $media_type, $is_primary) = explode('||', $item);
                        $animal['media'][] = [
                            'file_url' => $file_url,
                            'media_type' => $media_type,
                            'is_primary' => (bool)$is_primary
                        ];
                    }
                }
                unset($animal['media_data']); // Xóa trường tạm thời
                
                // Thêm các trường tương thích với trang admin cũ
                $animal['species'] = $animal['species_name'] ?? 'Không xác định';
                $animal['habitat_type'] = $animal['habitat_name'] ?? 'Chưa cập nhật';
                $animal['conservation_status'] = $animal['conservation_status_name'] ?? 'Chưa cập nhật';
                
                // Thêm trường image_path từ media nếu có
                $imageMedia = array_filter($animal['media'], function($m) { return $m['media_type'] === 'image'; });
                if (!empty($imageMedia)) {
                    $animal['image_path'] = reset($imageMedia)['file_url'];
                } else {
                    $animal['image_path'] = null;
                }
                
                // Thêm trường model_path từ media nếu có
                $modelMedia = array_filter($animal['media'], function($m) { return $m['media_type'] === '3d_model'; });
                if (!empty($modelMedia)) {
                    $animal['model_path'] = reset($modelMedia)['file_url'];
                } else {
                    $animal['model_path'] = null;
                }
            }

            return ['success' => true, 'data' => $animals];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Lấy thông tin chi tiết một động vật
    public function getAnimalById($id) {
        try {
            $query = "
                SELECT
                    an.*,
                    asp.species_name,
                    asp.scientific_name,
                    asp.family,
                    asp.order_name,
                    asp.class_name,
                    h.name AS habitat_name,
                    h.description AS habitat_description,
                    h.icon AS habitat_icon,
                    cs.name AS conservation_status_name,
                    cs.color_code AS conservation_status_color,
                    cs.priority AS conservation_status_priority,
                    cd.iucn_status,
                    cd.population_trend,
                    cd.threats,
                    cd.conservation_actions,
                    cd.last_assessment_date
                FROM animals_new an
                LEFT JOIN animal_species asp ON an.species_id = asp.id
                LEFT JOIN habitats h ON an.habitat_id = h.id
                LEFT JOIN conservation_statuses cs ON an.conservation_status_id = cs.id
                LEFT JOIN conservation_data cd ON an.id = cd.animal_id
                WHERE an.id = ?
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            $animal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$animal) {
                return ['success' => false, 'message' => 'Không tìm thấy động vật'];
            }
            
            // Lấy media của động vật
            $media_query = "SELECT * FROM animal_media WHERE animal_id = ? ORDER BY is_primary DESC, created_at ASC";
            $media_stmt = $this->db->prepare($media_query);
            $media_stmt->execute([$id]);
            $animal['media'] = $media_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return ['success' => true, 'data' => $animal];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Lấy danh sách loài
    public function getSpecies() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM animal_species ORDER BY species_name");
            $stmt->execute();
            $species = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return ['success' => true, 'data' => $species];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Lấy danh sách môi trường sống
    public function getHabitats() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM habitats ORDER BY name");
            $stmt->execute();
            $habitats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return ['success' => true, 'data' => $habitats];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Lấy danh sách tình trạng bảo tồn
    public function getConservationStatuses() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM conservation_statuses ORDER BY priority ASC");
            $stmt->execute();
            $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return ['success' => true, 'data' => $statuses];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Thêm động vật mới (cho Admin)
    public function addAnimal($data) {
        try {
            $this->db->beginTransaction();
            
            // Thêm vào bảng animals_new
            $stmt = $this->db->prepare("
                INSERT INTO animals_new (name, species_id, description, habitat_id, conservation_status_id, population_count)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['name'],
                $data['species_id'],
                $data['description'],
                $data['habitat_id'],
                $data['conservation_status_id'],
                $data['population_count']
            ]);
            
            $animal_id = $this->db->lastInsertId();
            
            // Thêm conservation_data nếu có
            if (!empty($data['iucn_status']) || !empty($data['threats'])) {
                $stmt = $this->db->prepare("
                    INSERT INTO conservation_data (animal_id, iucn_status, population_trend, threats, conservation_actions, last_assessment_date)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $animal_id,
                    $data['iucn_status'] ?? null,
                    $data['population_trend'] ?? 'unknown',
                    $data['threats'] ?? null,
                    $data['conservation_actions'] ?? null,
                    $data['last_assessment_date'] ?? null
                ]);
            }
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Thêm động vật thành công', 'animal_id' => $animal_id];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Cập nhật động vật (cho Admin)
    public function updateAnimal($id, $data) {
        try {
            $this->db->beginTransaction();
            
            // Cập nhật bảng animals_new
            $stmt = $this->db->prepare("
                UPDATE animals_new 
                SET name = ?, species_id = ?, description = ?, habitat_id = ?, 
                    conservation_status_id = ?, population_count = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([
                $data['name'],
                $data['species_id'],
                $data['description'],
                $data['habitat_id'],
                $data['conservation_status_id'],
                $data['population_count'],
                $id
            ]);
            
            // Cập nhật hoặc thêm conservation_data
            $stmt = $this->db->prepare("SELECT id FROM conservation_data WHERE animal_id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                // Cập nhật
                $stmt = $this->db->prepare("
                    UPDATE conservation_data 
                    SET iucn_status = ?, population_trend = ?, threats = ?, 
                        conservation_actions = ?, last_assessment_date = ?
                    WHERE animal_id = ?
                ");
                $stmt->execute([
                    $data['iucn_status'] ?? null,
                    $data['population_trend'] ?? 'unknown',
                    $data['threats'] ?? null,
                    $data['conservation_actions'] ?? null,
                    $data['last_assessment_date'] ?? null,
                    $id
                ]);
            } else {
                // Thêm mới
                $stmt = $this->db->prepare("
                    INSERT INTO conservation_data (animal_id, iucn_status, population_trend, threats, conservation_actions, last_assessment_date)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $id,
                    $data['iucn_status'] ?? null,
                    $data['population_trend'] ?? 'unknown',
                    $data['threats'] ?? null,
                    $data['conservation_actions'] ?? null,
                    $data['last_assessment_date'] ?? null
                ]);
            }
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Cập nhật động vật thành công'];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Xóa động vật (cho Admin)
    public function deleteAnimal($id) {
        try {
            $this->db->beginTransaction();
            
            // Xóa conservation_data trước (do foreign key)
            $stmt = $this->db->prepare("DELETE FROM conservation_data WHERE animal_id = ?");
            $stmt->execute([$id]);
            
            // Xóa animal_media
            $stmt = $this->db->prepare("DELETE FROM animal_media WHERE animal_id = ?");
            $stmt->execute([$id]);
            
            // Xóa động vật
            $stmt = $this->db->prepare("DELETE FROM animals_new WHERE id = ?");
            $stmt->execute([$id]);
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Xóa động vật thành công'];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}

// Xử lý request
try {
    $api = new AnimalsNewAPI();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'API initialization failed: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    exit(0);
}

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'animal':
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Thiếu ID động vật']);
                exit;
            }
            $result = $api->getAnimalById($id);
            echo json_encode($result);
            break;
            
        case 'species':
            $result = $api->getSpecies();
            echo json_encode($result);
            break;
            
        case 'habitats':
            $result = $api->getHabitats();
            echo json_encode($result);
            break;
            
        case 'conservation_statuses':
            $result = $api->getConservationStatuses();
            echo json_encode($result);
            break;
            
        default:
            // Lấy danh sách tất cả động vật
            $result = $api->getAnimals();
            echo json_encode($result);
    }
    
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $result = $api->addAnimal($input);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
    }
    
} elseif ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $_GET['id'] ?? 0;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Thiếu ID động vật']);
        exit;
    }
    
    $result = $api->updateAnimal($id, $input);
    echo json_encode($result);
    
} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Thiếu ID động vật']);
        exit;
    }
    
    $result = $api->deleteAnimal($id);
    echo json_encode($result);
    
} else {
    echo json_encode(['success' => false, 'message' => 'Method không được hỗ trợ']);
}
?>
