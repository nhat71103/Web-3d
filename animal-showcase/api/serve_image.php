<?php
// api/serve_image.php - Serve images publicly
header('Content-Type: image/jpeg');
header('Access-Control-Allow-Origin: *');

$imagePath = $_GET['path'] ?? '';
if (empty($imagePath)) {
    http_response_code(400);
    echo 'Image path required';
    exit;
}

// Security: only allow images from uploads/images directory
if (strpos($imagePath, 'uploads/images/') !== 0) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

$fullPath = '../' . $imagePath;
if (!file_exists($fullPath)) {
    http_response_code(404);
    echo 'Image not found';
    exit;
}

// Get image info
$imageInfo = getimagesize($fullPath);
if ($imageInfo === false) {
    http_response_code(400);
    echo 'Invalid image file';
    exit;
}

// Set correct content type
$mimeType = $imageInfo['mime'];
header('Content-Type: ' . $mimeType);

// Output image
readfile($fullPath);
?>
