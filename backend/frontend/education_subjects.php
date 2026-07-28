<?php
// backend/education_subjects.php

require_once __DIR__ . '/../bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: Fetch education subjects
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $stmt = $db->prepare("SELECT s.id, s.category_id, c.name as category_name, s.name, s.image_url, s.is_active, s.created_at 
                              FROM education_subjects s 
                              LEFT JOIN education_categories c ON s.category_id = c.id 
                              WHERE s.id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $sub = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$sub) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Education subject not found."]);
            exit;
        }
        $sub['id'] = (int)$sub['id'];
        $sub['category_id'] = (int)$sub['category_id'];
        $sub['is_active'] = (int)$sub['is_active'];
        echo json_encode(["success" => true, "subject" => $sub]);
        exit;
    } else {
        $stmt = $db->prepare("SELECT s.id, s.category_id, c.name as category_name, s.name, s.image_url, s.is_active, s.created_at 
                              FROM education_subjects s 
                              LEFT JOIN education_categories c ON s.category_id = c.id 
                              ORDER BY s.id DESC");
        $stmt->execute();
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($subjects as &$s) {
            $s['id'] = (int)$s['id'];
            $s['category_id'] = (int)$s['category_id'];
            $s['is_active'] = (int)$s['is_active'];
        }
        echo json_encode(["success" => true, "subjects" => $subjects]);
        exit;
    }
}

// POST: Create subject
if ($method === 'POST') {
    $user = requireAuth();
    $categoryId = isset($input['category_id']) ? (int)$input['category_id'] : 0;
    $name = isset($input['name']) ? trim($input['name']) : '';
    $imageUrl = isset($input['image_url']) ? trim($input['image_url']) : '';
    $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

    if ($categoryId <= 0 || empty($name)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "category_id and subject name are required."]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO education_subjects (category_id, name, image_url, is_active) VALUES (:category_id, :name, :image_url, :is_active)");
    $stmt->bindParam(':category_id', $categoryId);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':image_url', $imageUrl);
    $stmt->bindParam(':is_active', $isActive);
    $stmt->execute();

    $newId = (int)$db->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => "Education subject created successfully.",
        "id" => $newId
    ]);
    exit;
}

// PUT: Update subject
if ($method === 'PUT') {
    $user = requireAuth();
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid subject ID is required."]);
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM education_subjects WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Education subject not found."]);
        exit;
    }

    $categoryId = isset($input['category_id']) ? (int)$input['category_id'] : (int)$existing['category_id'];
    $name = isset($input['name']) ? trim($input['name']) : $existing['name'];
    $imageUrl = isset($input['image_url']) ? trim($input['image_url']) : $existing['image_url'];
    $isActive = isset($input['is_active']) ? (int)$input['is_active'] : (int)$existing['is_active'];

    $updateStmt = $db->prepare("UPDATE education_subjects SET category_id = :category_id, name = :name, image_url = :image_url, is_active = :is_active WHERE id = :id");
    $updateStmt->bindParam(':category_id', $categoryId);
    $updateStmt->bindParam(':name', $name);
    $updateStmt->bindParam(':image_url', $imageUrl);
    $updateStmt->bindParam(':is_active', $isActive);
    $updateStmt->bindParam(':id', $id);
    $updateStmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Education subject updated successfully."
    ]);
    exit;
}

// DELETE: Delete subject
if ($method === 'DELETE') {
    $user = requireAuth();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($input['id']) ? (int)$input['id'] : 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid subject ID is required."]);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM education_subjects WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Education subject deleted successfully."
    ]);
    exit;
}
