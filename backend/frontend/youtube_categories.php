<?php
// backend/youtube_categories.php

require_once __DIR__ . '/../bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: Fetch all or single category
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $stmt = $db->prepare("SELECT c.id, c.actor_id, a.name as actor_name, c.name, c.image, c.category_order 
                              FROM youtube_categories c 
                              LEFT JOIN actors a ON c.actor_id = a.id 
                              WHERE c.id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $cat = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cat) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Category not found."]);
            exit;
        }
        $cat['id'] = (int)$cat['id'];
        $cat['actor_id'] = (int)$cat['actor_id'];
        $cat['category_order'] = (int)$cat['category_order'];
        echo json_encode(["success" => true, "category" => $cat]);
        exit;
    } else {
        $stmt = $db->prepare("SELECT c.id, c.actor_id, a.name as actor_name, c.name, c.image, c.category_order 
                              FROM youtube_categories c 
                              LEFT JOIN actors a ON c.actor_id = a.id 
                              ORDER BY c.category_order ASC, c.id DESC");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($categories as &$c) {
            $c['id'] = (int)$c['id'];
            $c['actor_id'] = (int)$c['actor_id'];
            $c['category_order'] = (int)$c['category_order'];
        }
        echo json_encode(["success" => true, "categories" => $categories]);
        exit;
    }
}

// POST: Create category
if ($method === 'POST') {
    $user = requireAuth();
    $actorId = isset($input['actor_id']) ? (int)$input['actor_id'] : 0;
    $name = isset($input['name']) ? trim($input['name']) : '';
    $image = isset($input['image']) ? trim($input['image']) : '';
    $categoryOrder = isset($input['category_order']) ? (int)$input['category_order'] : 1;

    if ($actorId <= 0 || empty($name)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "actor_id and category name are required."]);
        exit;
    }

    // Validation: Categories must be created under actors with is_category = 1
    $actorStmt = $db->prepare("SELECT is_category FROM actors WHERE id = :id LIMIT 1");
    $actorStmt->bindParam(':id', $actorId);
    $actorStmt->execute();
    $actor = $actorStmt->fetch(PDO::FETCH_ASSOC);
    if (!$actor) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Specified parent actor does not exist."]);
        exit;
    }
    if ((int)$actor['is_category'] !== 1) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Categories must be created under actors with is_category = 1."]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO youtube_categories (actor_id, name, image, category_order) VALUES (:actor_id, :name, :image, :category_order)");
    $stmt->bindParam(':actor_id', $actorId);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':image', $image);
    $stmt->bindParam(':category_order', $categoryOrder);
    $stmt->execute();

    $newId = (int)$db->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => "Category created successfully.",
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

    $stmt = $db->prepare("SELECT * FROM youtube_categories WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Category not found."]);
        exit;
    }

    $actorId = isset($input['actor_id']) ? (int)$input['actor_id'] : (int)$existing['actor_id'];
    $name = isset($input['name']) ? trim($input['name']) : $existing['name'];
    $image = isset($input['image']) ? trim($input['image']) : $existing['image'];
    $categoryOrder = isset($input['category_order']) ? (int)$input['category_order'] : (int)$existing['category_order'];

    // Validation: Categories must be created under actors with is_category = 1
    $actorStmt = $db->prepare("SELECT is_category FROM actors WHERE id = :id LIMIT 1");
    $actorStmt->bindParam(':id', $actorId);
    $actorStmt->execute();
    $actor = $actorStmt->fetch(PDO::FETCH_ASSOC);
    if (!$actor || (int)$actor['is_category'] !== 1) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Categories must be created under actors with is_category = 1."]);
        exit;
    }

    $updateStmt = $db->prepare("UPDATE youtube_categories SET actor_id = :actor_id, name = :name, image = :image, category_order = :category_order WHERE id = :id");
    $updateStmt->bindParam(':actor_id', $actorId);
    $updateStmt->bindParam(':name', $name);
    $updateStmt->bindParam(':image', $image);
    $updateStmt->bindParam(':category_order', $categoryOrder);
    $updateStmt->bindParam(':id', $id);
    $updateStmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Category updated successfully."
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

    $stmt = $db->prepare("DELETE FROM youtube_categories WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Category deleted successfully."
    ]);
    exit;
}
