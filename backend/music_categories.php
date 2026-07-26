<?php
// backend/music_categories.php

require_once __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: Fetch music categories
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $stmt = $db->prepare("SELECT id, name, image_url, has_album, is_active, created_at FROM music_categories WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $cat = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cat) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Music category not found."]);
            exit;
        }
        $cat['id'] = (int)$cat['id'];
        $cat['has_album'] = (int)$cat['has_album'];
        $cat['is_active'] = (int)$cat['is_active'];
        echo json_encode(["success" => true, "category" => $cat]);
        exit;
    } else {
        $stmt = $db->prepare("SELECT id, name, image_url, has_album, is_active, created_at FROM music_categories ORDER BY id DESC");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($categories as &$c) {
            $c['id'] = (int)$c['id'];
            $c['has_album'] = (int)$c['has_album'];
            $c['is_active'] = (int)$c['is_active'];
        }
        echo json_encode(["success" => true, "categories" => $categories]);
        exit;
    }
}

// POST: Create category
if ($method === 'POST') {
    $user = requireAuth();
    $name = isset($input['name']) ? trim($input['name']) : '';
    $imageUrl = isset($input['image_url']) ? trim($input['image_url']) : '';
    $hasAlbum = isset($input['has_album']) ? (int)$input['has_album'] : 0;
    $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

    if (empty($name)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Category name is required."]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO music_categories (name, image_url, has_album, is_active) VALUES (:name, :image_url, :has_album, :is_active)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':image_url', $imageUrl);
    $stmt->bindParam(':has_album', $hasAlbum);
    $stmt->bindParam(':is_active', $isActive);
    $stmt->execute();

    $newId = (int)$db->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => "Music category created successfully.",
        "id" => $newId
    ]);
    exit;
}

// PUT: Update category
if ($method === 'PUT') {
    $user = requireAuth();
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid category ID is required."]);
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM music_categories WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Music category not found."]);
        exit;
    }

    $name = isset($input['name']) ? trim($input['name']) : $existing['name'];
    $imageUrl = isset($input['image_url']) ? trim($input['image_url']) : $existing['image_url'];
    $hasAlbum = isset($input['has_album']) ? (int)$input['has_album'] : (int)$existing['has_album'];
    $isActive = isset($input['is_active']) ? (int)$input['is_active'] : (int)$existing['is_active'];

    $updateStmt = $db->prepare("UPDATE music_categories SET name = :name, image_url = :image_url, has_album = :has_album, is_active = :is_active WHERE id = :id");
    $updateStmt->bindParam(':name', $name);
    $updateStmt->bindParam(':image_url', $imageUrl);
    $updateStmt->bindParam(':has_album', $hasAlbum);
    $updateStmt->bindParam(':is_active', $isActive);
    $updateStmt->bindParam(':id', $id);
    $updateStmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Music category updated successfully."
    ]);
    exit;
}

// DELETE: Delete category
if ($method === 'DELETE') {
    $user = requireAuth();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($input['id']) ? (int)$input['id'] : 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid category ID is required."]);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM music_categories WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Music category deleted successfully."
    ]);
    exit;
}
