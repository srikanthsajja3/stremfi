<?php
// backend/music.php

require_once __DIR__ . '/../bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: Fetch music tracks
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $stmt = $db->prepare("SELECT m.id, m.category_id, c.name as category_name, m.album_id, a.name as album_name, 
                                     m.name, m.image_url, m.type, m.stream_url, m.is_active, m.created_at 
                              FROM music m 
                              LEFT JOIN music_categories c ON m.category_id = c.id 
                              LEFT JOIN music_albums a ON m.album_id = a.id 
                              WHERE m.id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $track = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$track) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Music track not found."]);
            exit;
        }
        $track['id'] = (int)$track['id'];
        $track['category_id'] = (int)$track['category_id'];
        $track['album_id'] = $track['album_id'] !== null ? (int)$track['album_id'] : null;
        $track['is_active'] = (int)$track['is_active'];
        echo json_encode(["success" => true, "music" => $track]);
        exit;
    } else {
        $stmt = $db->prepare("SELECT m.id, m.category_id, c.name as category_name, m.album_id, a.name as album_name, 
                                     m.name, m.image_url, m.type, m.stream_url, m.is_active, m.created_at 
                              FROM music m 
                              LEFT JOIN music_categories c ON m.category_id = c.id 
                              LEFT JOIN music_albums a ON m.album_id = a.id 
                              ORDER BY m.id DESC");
        $stmt->execute();
        $tracks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tracks as &$t) {
            $t['id'] = (int)$t['id'];
            $t['category_id'] = (int)$t['category_id'];
            $t['album_id'] = $t['album_id'] !== null ? (int)$t['album_id'] : null;
            $t['is_active'] = (int)$t['is_active'];
        }
        echo json_encode(["success" => true, "music" => $tracks]);
        exit;
    }
}

// POST: Create music
if ($method === 'POST') {
    $user = requireAuth();
    $categoryId = isset($input['category_id']) ? (int)$input['category_id'] : 0;
    $albumId = (isset($input['album_id']) && $input['album_id'] !== null && $input['album_id'] !== '') ? (int)$input['album_id'] : null;
    $name = isset($input['name']) ? trim($input['name']) : '';
    $imageUrl = (isset($input['image_url']) && !empty($input['image_url'])) ? trim($input['image_url']) : null;
    $type = isset($input['type']) ? trim($input['type']) : 'PODCAST';
    $streamUrl = isset($input['stream_url']) ? trim($input['stream_url']) : '';
    $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

    if ($categoryId <= 0 || empty($name) || empty($streamUrl)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "category_id, name, and stream_url are required."]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO music (category_id, album_id, name, image_url, type, stream_url, is_active) 
                          VALUES (:category_id, :album_id, :name, :image_url, :type, :stream_url, :is_active)");
    $stmt->bindParam(':category_id', $categoryId);
    $stmt->bindValue(':album_id', $albumId, $albumId !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindParam(':name', $name);
    $stmt->bindValue(':image_url', $imageUrl, $imageUrl !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':stream_url', $streamUrl);
    $stmt->bindParam(':is_active', $isActive);
    $stmt->execute();

    $newId = (int)$db->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => "Music created successfully.",
        "id" => $newId
    ]);
    exit;
}

// PUT: Update music
if ($method === 'PUT') {
    $user = requireAuth();
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid music ID is required."]);
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM music WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Music track not found."]);
        exit;
    }

    $categoryId = isset($input['category_id']) ? (int)$input['category_id'] : (int)$existing['category_id'];
    $albumId = array_key_exists('album_id', $input) ? ($input['album_id'] !== null && $input['album_id'] !== '' ? (int)$input['album_id'] : null) : ($existing['album_id'] !== null ? (int)$existing['album_id'] : null);
    $name = isset($input['name']) ? trim($input['name']) : $existing['name'];
    $imageUrl = array_key_exists('image_url', $input) ? ($input['image_url'] !== null && $input['image_url'] !== '' ? trim($input['image_url']) : null) : $existing['image_url'];
    $type = isset($input['type']) ? trim($input['type']) : $existing['type'];
    $streamUrl = isset($input['stream_url']) ? trim($input['stream_url']) : $existing['stream_url'];
    $isActive = isset($input['is_active']) ? (int)$input['is_active'] : (int)$existing['is_active'];

    $updateStmt = $db->prepare("UPDATE music 
        SET category_id = :category_id, album_id = :album_id, name = :name, 
            image_url = :image_url, type = :type, stream_url = :stream_url, is_active = :is_active 
        WHERE id = :id");
    $updateStmt->bindParam(':category_id', $categoryId);
    $updateStmt->bindValue(':album_id', $albumId, $albumId !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $updateStmt->bindParam(':name', $name);
    $updateStmt->bindValue(':image_url', $imageUrl, $imageUrl !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $updateStmt->bindParam(':type', $type);
    $updateStmt->bindParam(':stream_url', $streamUrl);
    $updateStmt->bindParam(':is_active', $isActive);
    $updateStmt->bindParam(':id', $id);
    $updateStmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Music updated successfully."
    ]);
    exit;
}

// DELETE: Delete music
if ($method === 'DELETE') {
    $user = requireAuth();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($input['id']) ? (int)$input['id'] : 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid music ID is required."]);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM music WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Music deleted successfully."
    ]);
    exit;
}
