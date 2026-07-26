<?php
// backend/upload_logo.php

require_once __DIR__ . '/bootstrap.php';

$user = requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
    exit;
}

$operatorId = isset($_POST['operator_id']) ? (int)$_POST['operator_id'] : $user['userId'];

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No image uploaded or upload error occurred."]);
    exit;
}

$targetDir = __DIR__ . '/images/logos/';
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
$allowedExts = ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif'];
if (!in_array($fileExt, $allowedExts)) {
    $fileExt = 'png';
}

$filename = 'logo_' . time() . '_' . mt_rand(1000, 9999) . '.' . $fileExt;
$targetFile = $targetDir . $filename;

if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost:8000';
    $logoUrl = "$protocol://$host/images/logos/$filename";

    // Update operator record if operatorId provided
    if ($operatorId > 0) {
        $stmt = $db->prepare("UPDATE operators SET profile_image = :image WHERE id = :id");
        $stmt->bindParam(':image', $logoUrl);
        $stmt->bindParam(':id', $operatorId);
        $stmt->execute();
    }

    echo json_encode([
        "success" => true,
        "message" => "Logo uploaded successfully.",
        "logo_url" => $logoUrl
    ]);
    exit;
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to save uploaded image."]);
    exit;
}
