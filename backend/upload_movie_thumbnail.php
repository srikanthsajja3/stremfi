<?php
// backend/upload_movie_thumbnail.php

require_once __DIR__ . '/bootstrap.php';

$user = requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No image file uploaded or error during upload."]);
    exit;
}

$targetDir = __DIR__ . '/images/movies/';
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
if (empty($fileExt)) {
    $fileExt = 'png';
}

$hash = substr(md5(uniqid(mt_rand(), true)), 0, 6);
$filename = 'movie_' . time() . '_' . $hash . '.' . $fileExt;
$targetFile = $targetDir . $filename;

if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
    $imageUrl = "/images/movies/$filename";
    echo json_encode([
        "success" => true,
        "message" => "Thumbnail uploaded successfully.",
        "image_url" => $imageUrl
    ]);
    exit;
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to save movie thumbnail."]);
    exit;
}
