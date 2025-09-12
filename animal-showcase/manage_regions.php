<?php
/**
 * API quản lý regions (khu vực sống)
 */

error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 0);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept');

while (ob_get_level()) {
    ob_end_clean();
}

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

try {
    require_once 'api/config.php';
    $db = Database::getInstance()->getConnection();
    
    $action = $_POST['action'] ?? 'list';
    
    switch ($action) {
        case 'add':
            addRegion($db);
            break;
        case 'update':
            updateRegion($db);
            break;
        case 'delete':
            deleteRegion($db);
            break;
        case 'list':
        default:
            listRegions($db);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function addRegion($db) {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $icon = $_POST['icon'] ?? '🌍';
    
    if (empty($name)) {
        echo json_encode([
            'success' => false,
            'error' => 'Tên khu vực không được để trống',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        return;
    }
    
    // Kiểm tra tên khu vực đã tồn tại chưa
    $stmt = $db->prepare("SELECT id FROM regions WHERE name = ?");
    $stmt->execute([$name]);
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'error' => 'Khu vực này đã tồn tại',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        return;
    }
    
    // Thêm khu vực mới
    $stmt = $db->prepare("INSERT INTO regions (name, description, icon) VALUES (?, ?, ?)");
    $result = $stmt->execute([$name, $description, $icon]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Thêm khu vực thành công',
            'region_id' => $db->lastInsertId(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Không thể thêm khu vực',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}

function updateRegion($db) {
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $icon = $_POST['icon'] ?? '🌍';
    
    if (!$id || empty($name)) {
        echo json_encode([
            'success' => false,
            'error' => 'ID và tên khu vực không được để trống',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        return;
    }
    
    // Kiểm tra khu vực có tồn tại không
    $stmt = $db->prepare("SELECT id FROM regions WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'error' => 'Khu vực không tồn tại',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        return;
    }
    
    // Kiểm tra tên khu vực đã tồn tại chưa (trừ chính nó)
    $stmt = $db->prepare("SELECT id FROM regions WHERE name = ? AND id != ?");
    $stmt->execute([$name, $id]);
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'error' => 'Tên khu vực này đã tồn tại',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        return;
    }
    
    // Cập nhật khu vực
    $stmt = $db->prepare("UPDATE regions SET name = ?, description = ?, icon = ? WHERE id = ?");
    $result = $stmt->execute([$name, $description, $icon, $id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật khu vực thành công',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Không thể cập nhật khu vực',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}

function deleteRegion($db) {
    $id = $_POST['id'] ?? 0;
    
    if (!$id) {
        echo json_encode([
            'success' => false,
            'error' => 'ID khu vực không được để trống',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        return;
    }
    
    // Kiểm tra khu vực có đang được sử dụng không
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM animals_new WHERE region_id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Không thể xóa khu vực này vì đang có ' . $result['count'] . ' động vật sử dụng',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        return;
    }
    
    // Xóa khu vực
    $stmt = $db->prepare("DELETE FROM regions WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Xóa khu vực thành công',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Không thể xóa khu vực',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}

function listRegions($db) {
    $stmt = $db->prepare("SELECT * FROM regions ORDER BY name");
    $stmt->execute();
    $regions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $regions,
        'count' => count($regions),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
