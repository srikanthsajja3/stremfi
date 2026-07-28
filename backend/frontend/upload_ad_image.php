<?php
// backend/upload_ad_image.php

require_once __DIR__ . '/../bootstrap.php';
$user = requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No valid image uploaded."]);
    exit;
}

$uploadDir = __DIR__ . '/images/ads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
if (!in_array($fileExt, $allowed)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid image format. Allowed: jpg, jpeg, png, webp, gif."]);
    exit;
}

$fileName = 'ad_' . time() . '_' . rand(1000, 9999) . '.' . $fileExt;
$targetPath = $uploadDir . $fileName;

if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $imageUrl = "$protocol://$host/images/ads/$fileName";

    echo json_encode([
        "success" => true,
        "message" => "Ad image uploaded successfully.",
        "image_url" => $imageUrl
    ]);
    exit;
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to save uploaded image."]);
    exit;
}
