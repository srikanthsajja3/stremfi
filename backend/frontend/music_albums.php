<?php
// backend/music_albums.php

require_once __DIR__ . '/../bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: Fetch music albums
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $stmt = $db->prepare("SELECT a.id, a.category_id, c.name as category_name, a.name, a.image_url, a.is_active, a.created_at 
                              FROM music_albums a 
                              LEFT JOIN music_categories c ON a.category_id = c.id 
                              WHERE a.id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $album = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$album) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Music album not found."]);
            exit;
        }
        $album['id'] = (int)$album['id'];
        $album['category_id'] = (int)$album['category_id'];
        $album['is_active'] = (int)$album['is_active'];
        echo json_encode(["success" => true, "album" => $album]);
        exit;
    } else {
        $stmt = $db->prepare("SELECT a.id, a.category_id, c.name as category_name, a.name, a.image_url, a.is_active, a.created_at 
                              FROM music_albums a 
                              LEFT JOIN music_categories c ON a.category_id = c.id 
                              ORDER BY a.id DESC");
        $stmt->execute();
        $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($albums as &$alb) {
            $alb['id'] = (int)$alb['id'];
            $alb['category_id'] = (int)$alb['category_id'];
            $alb['is_active'] = (int)$alb['is_active'];
        }
        echo json_encode(["success" => true, "albums" => $albums]);
        exit;
    }
}

// POST: Create album
if ($method === 'POST') {
    $user = requireAuth();
    $categoryId = isset($input['category_id']) ? (int)$input['category_id'] : 0;
    $name = isset($input['name']) ? trim($input['name']) : '';
    $imageUrl = isset($input['image_url']) ? trim($input['image_url']) : '';
    $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

    if ($categoryId <= 0 || empty($name)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "category_id and album name are required."]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO music_albums (category_id, name, image_url, is_active) VALUES (:category_id, :name, :image_url, :is_active)");
    $stmt->bindParam(':category_id', $categoryId);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':image_url', $imageUrl);
    $stmt->bindParam(':is_active', $isActive);
    $stmt->execute();

    $newId = (int)$db->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => "Music album created successfully.",
        "id" => $newId
    ]);
    exit;
}

// PUT: Update album
if ($method === 'PUT') {
    $user = requireAuth();
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid album ID is required."]);
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM music_albums WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Music album not found."]);
        exit;
    }

    $categoryId = isset($input['category_id']) ? (int)$input['category_id'] : (int)$existing['category_id'];
    $name = isset($input['name']) ? trim($input['name']) : $existing['name'];
    $imageUrl = isset($input['image_url']) ? trim($input['image_url']) : $existing['image_url'];
    $isActive = isset($input['is_active']) ? (int)$input['is_active'] : (int)$existing['is_active'];

    $updateStmt = $db->prepare("UPDATE music_albums SET category_id = :category_id, name = :name, image_url = :image_url, is_active = :is_active WHERE id = :id");
    $updateStmt->bindParam(':category_id', $categoryId);
    $updateStmt->bindParam(':name', $name);
    $updateStmt->bindParam(':image_url', $imageUrl);
    $updateStmt->bindParam(':is_active', $isActive);
    $updateStmt->bindParam(':id', $id);
    $updateStmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Music album updated successfully."
    ]);
    exit;
}

// DELETE: Delete album
if ($method === 'DELETE') {
    $user = requireAuth();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($input['id']) ? (int)$input['id'] : 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid album ID is required."]);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM music_albums WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Music album deleted successfully."
    ]);
    exit;
}
