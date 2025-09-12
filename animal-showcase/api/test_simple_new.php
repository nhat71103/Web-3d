<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Test đơn giản - không cần database
echo json_encode([
    'success' => true,
    'message' => 'API test thành công',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
