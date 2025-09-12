<?php
// animals_new_fixed_clean.php - API sạch sẽ, không có output sớm
// KHÔNG có error_reporting hoặc echo ở đây!

class AnimalsNewAPIFixedClean {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
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

            // Xử lý media_data và thêm các trường tương thích ngược
            foreach ($animals as &$animal) {
                // Xử lý media_data
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
                
                // THÊM CÁC TRƯỜNG TƯƠNG THÍCH NGƯỢC (QUAN TRỌNG!)
                $animal['species'] = $animal['species_name'] ?? 'Không xác định';
                $animal['habitat_type'] = $animal['habitat_name'] ?? 'Chưa cập nhật';
                $animal['conservation_status'] = $animal['conservation_status_name'] ?? 'Chưa cập nhật';
                
                // Thêm trường image_path từ media nếu có
                $imageMedia = array_filter($animal['media'], function($m) { 
                    return $m['media_type'] === 'image'; 
                });
                if (!empty($imageMedia)) {
                    $animal['image_path'] = reset($imageMedia)['file_url'];
                } else {
                    $animal['image_path'] = null;
                }
                
                // Thêm trường model_path từ media nếu có
                $modelMedia = array_filter($animal['media'], function($m) { 
                    return $m['media_type'] === '3d_model'; 
                });
                if (!empty($modelMedia)) {
                    $animal['model_path'] = reset($modelMedia)['file_url'];
                } else {
                    $animal['model_path'] = null;
                }
                
                // Thêm trường status_3d (mặc định)
                $animal['status_3d'] = $animal['model_path'] ? 'completed' : 'pending';
            }

            return ['success' => true, 'data' => $animals];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
?>
