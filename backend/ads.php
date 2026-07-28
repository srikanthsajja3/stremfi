<?php
// backend/ads.php

require_once __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: Fetch Ads
if ($method === 'GET') {
    $stmt = $db->prepare("SELECT * FROM ads ORDER BY id DESC");
    $stmt->execute();
    $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($ads as &$ad) {
        $ad['id'] = (int)$ad['id'];
        $ad['is_active'] = (int)$ad['is_active'];
    }
    echo json_encode(["success" => true, "ads" => $ads]);
    exit;
}

// POST: Create Ad
if ($method === 'POST') {
    $user = requireAuth();

    $title = isset($input['title']) ? trim($input['title']) : '';
    $imageUrl = isset($input['image_url']) ? trim($input['image_url']) : (isset($input['imageUrl']) ? trim($input['imageUrl']) : '');
    $linkUrl = isset($input['link_url']) ? trim($input['link_url']) : (isset($input['linkUrl']) ? trim($input['linkUrl']) : '');
    $position = isset($input['position']) ? trim($input['position']) : 'banner';
    $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

    if (empty($title) || empty($imageUrl)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Title and image URL are required."]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO ads (title, image_url, link_url, position, is_active) VALUES (:title, :image_url, :link_url, :position, :is_active)");
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':image_url', $imageUrl);
    $stmt->bindParam(':link_url', $linkUrl);
    $stmt->bindParam(':position', $position);
    $stmt->bindParam(':is_active', $isActive);
    $stmt->execute();

    $newId = (int)$db->lastInsertId();

    echo json_encode(["success" => true, "message" => "Ad uploaded successfully.", "id" => $newId]);
    exit;
}

// PUT: Update Ad
if ($method === 'PUT') {
    $user = requireAuth();
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Ad ID is required."]);
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM ads WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Ad not found."]);
        exit;
    }

    $title = isset($input['title']) ? trim($input['title']) : $existing['title'];
    $imageUrl = isset($input['image_url']) ? trim($input['image_url']) : (isset($input['imageUrl']) ? trim($input['imageUrl']) : $existing['image_url']);
    $linkUrl = isset($input['link_url']) ? trim($input['link_url']) : $existing['link_url'];
    $position = isset($input['position']) ? trim($input['position']) : $existing['position'];
    $isActive = isset($input['is_active']) ? (int)$input['is_active'] : (int)$existing['is_active'];

    $updateStmt = $db->prepare("UPDATE ads SET title = :title, image_url = :image_url, link_url = :link_url, position = :position, is_active = :is_active WHERE id = :id");
    $updateStmt->bindParam(':title', $title);
    $updateStmt->bindParam(':image_url', $imageUrl);
    $updateStmt->bindParam(':link_url', $linkUrl);
    $updateStmt->bindParam(':position', $position);
    $updateStmt->bindParam(':is_active', $isActive);
    $updateStmt->bindParam(':id', $id);
    $updateStmt->execute();

    echo json_encode(["success" => true, "message" => "Ad updated successfully."]);
    exit;
}

// DELETE: Delete Ad
if ($method === 'DELETE') {
    $user = requireAuth();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($input['id']) ? (int)$input['id'] : 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Ad ID is required."]);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM ads WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Ad deleted successfully."]);
    exit;
}
