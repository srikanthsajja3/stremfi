<?php
// backend/youtube_movies.php

require_once __DIR__ . '/../bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: Fetch all or single movie
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    try {
        if ($id > 0) {
            $stmt = $db->prepare("SELECT m.id, m.name, m.image, m.thumbnail, m.youtube_video_id, m.description, 
                                         m.actor_id, a.name as actor_name, 
                                         m.category_id, c.name as category_name, m.role 
                                  FROM youtube_movies m 
                                  LEFT JOIN actors a ON m.actor_id = a.id 
                                  LEFT JOIN youtube_categories c ON m.category_id = c.id 
                                  WHERE m.id = :id LIMIT 1");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $movie = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$movie) {
                http_response_code(404);
                echo json_encode(["success" => false, "message" => "Movie not found."]);
                exit;
            }
            $movie['id'] = (int)$movie['id'];
            $movie['actor_id'] = $movie['actor_id'] !== null ? (int)$movie['actor_id'] : null;
            $movie['category_id'] = $movie['category_id'] !== null ? (int)$movie['category_id'] : null;
            echo json_encode(["success" => true, "movie" => $movie]);
            exit;
        } else {
            $stmt = $db->prepare("SELECT m.id, m.name, m.image, m.thumbnail, m.youtube_video_id, m.description, 
                                         m.actor_id, a.name as actor_name, 
                                         m.category_id, c.name as category_name, m.role 
                                  FROM youtube_movies m 
                                  LEFT JOIN actors a ON m.actor_id = a.id 
                                  LEFT JOIN youtube_categories c ON m.category_id = c.id 
                                  ORDER BY m.id DESC");
            $stmt->execute();
            $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($movies as &$m) {
                $m['id'] = (int)$m['id'];
                $m['actor_id'] = $m['actor_id'] !== null ? (int)$m['actor_id'] : null;
                $m['category_id'] = $m['category_id'] !== null ? (int)$m['category_id'] : null;
            }
            echo json_encode(["success" => true, "movies" => $movies]);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => true, "movies" => []]);
        exit;
    }
}

// POST: Create movie
if ($method === 'POST') {
    $user = requireAuth();

    $name = isset($input['name']) ? trim($input['name']) : '';
    $image = isset($input['image']) ? trim($input['image']) : '';
    $thumbnail = isset($input['thumbnail']) ? trim($input['thumbnail']) : '';
    $youtubeVideoId = isset($input['youtube_video_id']) ? trim($input['youtube_video_id']) : '';
    $description = isset($input['description']) ? trim($input['description']) : '';
    $actorId = (isset($input['actor_id']) && $input['actor_id'] !== null && $input['actor_id'] !== '') ? (int)$input['actor_id'] : null;
    $categoryId = (isset($input['category_id']) && $input['category_id'] !== null && $input['category_id'] !== '') ? (int)$input['category_id'] : null;
    $role = (isset($input['role']) && $input['role'] !== null && $input['role'] !== '') ? trim($input['role']) : null;

    if (empty($name) || empty($youtubeVideoId)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Movie name and youtube_video_id are required."]);
        exit;
    }

    // Rule 1: Unique youtube_video_id validation
    $chkVideo = $db->prepare("SELECT id FROM youtube_movies WHERE youtube_video_id = :vid LIMIT 1");
    $chkVideo->bindParam(':vid', $youtubeVideoId);
    $chkVideo->execute();
    if ($chkVideo->fetch()) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Duplicate youtube_video_id values are not allowed."]);
        exit;
    }

    // Rule 2: Each movie can be mapped to either an Actor or a Category (not both, and not neither)
    $hasActor = ($actorId !== null && $actorId > 0);
    $hasCategory = ($categoryId !== null && $categoryId > 0);
    if (($hasActor && $hasCategory) || (!$hasActor && !$hasCategory)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Each movie can be mapped to either an Actor or a Category (not both)."]);
        exit;
    }

    // Rule 3: Actor validation ensures only actors with is_category = 0 can be assigned directly to a movie
    if ($hasActor) {
        $chkActor = $db->prepare("SELECT is_category FROM actors WHERE id = :id LIMIT 1");
        $chkActor->bindParam(':id', $actorId);
        $chkActor->execute();
        $actorData = $chkActor->fetch(PDO::FETCH_ASSOC);
        if (!$actorData) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Selected actor does not exist."]);
            exit;
        }
        if ((int)$actorData['is_category'] !== 0) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Actor validation ensures only actors with is_category = 0 can be assigned directly to a movie."]);
            exit;
        }
    }

    // Check category validity if category_id provided
    if ($hasCategory) {
        $chkCat = $db->prepare("SELECT id FROM youtube_categories WHERE id = :id LIMIT 1");
        $chkCat->bindParam(':id', $categoryId);
        $chkCat->execute();
        if (!$chkCat->fetch()) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Selected category does not exist."]);
            exit;
        }
    }

    $stmt = $db->prepare("INSERT INTO youtube_movies (name, image, thumbnail, youtube_video_id, description, actor_id, category_id, role) 
                          VALUES (:name, :image, :thumbnail, :youtube_video_id, :description, :actor_id, :category_id, :role)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':image', $image);
    $stmt->bindParam(':thumbnail', $thumbnail);
    $stmt->bindParam(':youtube_video_id', $youtubeVideoId);
    $stmt->bindParam(':description', $description);
    $stmt->bindValue(':actor_id', $hasActor ? $actorId : null, $hasActor ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':category_id', $hasCategory ? $categoryId : null, $hasCategory ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':role', $role, $role !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->execute();

    $newId = (int)$db->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => "Movie created successfully.",
        "id" => $newId
    ]);
    exit;
}

// PUT: Update movie
if ($method === 'PUT') {
    $user = requireAuth();
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid movie ID is required."]);
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM youtube_movies WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Movie not found."]);
        exit;
    }

    $name = isset($input['name']) ? trim($input['name']) : $existing['name'];
    $image = isset($input['image']) ? trim($input['image']) : $existing['image'];
    $thumbnail = isset($input['thumbnail']) ? trim($input['thumbnail']) : $existing['thumbnail'];
    $youtubeVideoId = isset($input['youtube_video_id']) ? trim($input['youtube_video_id']) : $existing['youtube_video_id'];
    $description = isset($input['description']) ? trim($input['description']) : $existing['description'];
    
    $actorId = array_key_exists('actor_id', $input) ? ($input['actor_id'] !== null && $input['actor_id'] !== '' ? (int)$input['actor_id'] : null) : ($existing['actor_id'] !== null ? (int)$existing['actor_id'] : null);
    $categoryId = array_key_exists('category_id', $input) ? ($input['category_id'] !== null && $input['category_id'] !== '' ? (int)$input['category_id'] : null) : ($existing['category_id'] !== null ? (int)$existing['category_id'] : null);
    $role = array_key_exists('role', $input) ? ($input['role'] !== null && $input['role'] !== '' ? trim($input['role']) : null) : $existing['role'];

    // Rule 1: Unique youtube_video_id check
    $chkVideo = $db->prepare("SELECT id FROM youtube_movies WHERE youtube_video_id = :vid AND id != :id LIMIT 1");
    $chkVideo->bindParam(':vid', $youtubeVideoId);
    $chkVideo->bindParam(':id', $id);
    $chkVideo->execute();
    if ($chkVideo->fetch()) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Duplicate youtube_video_id values are not allowed."]);
        exit;
    }

    // Rule 2: Either Actor or Category (not both, not neither)
    $hasActor = ($actorId !== null && $actorId > 0);
    $hasCategory = ($categoryId !== null && $categoryId > 0);
    if (($hasActor && $hasCategory) || (!$hasActor && !$hasCategory)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Each movie can be mapped to either an Actor or a Category (not both)."]);
        exit;
    }

    // Rule 3: Actor validation (is_category = 0)
    if ($hasActor) {
        $chkActor = $db->prepare("SELECT is_category FROM actors WHERE id = :id LIMIT 1");
        $chkActor->bindParam(':id', $actorId);
        $chkActor->execute();
        $actorData = $chkActor->fetch(PDO::FETCH_ASSOC);
        if (!$actorData || (int)$actorData['is_category'] !== 0) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Actor validation ensures only actors with is_category = 0 can be assigned directly to a movie."]);
            exit;
        }
    }

    $updateStmt = $db->prepare("UPDATE youtube_movies 
        SET name = :name, image = :image, thumbnail = :thumbnail, youtube_video_id = :youtube_video_id, 
            description = :description, actor_id = :actor_id, category_id = :category_id, role = :role 
        WHERE id = :id");
    $updateStmt->bindParam(':name', $name);
    $updateStmt->bindParam(':image', $image);
    $updateStmt->bindParam(':thumbnail', $thumbnail);
    $updateStmt->bindParam(':youtube_video_id', $youtubeVideoId);
    $updateStmt->bindParam(':description', $description);
    $updateStmt->bindValue(':actor_id', $hasActor ? $actorId : null, $hasActor ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $updateStmt->bindValue(':category_id', $hasCategory ? $categoryId : null, $hasCategory ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $updateStmt->bindValue(':role', $role, $role !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $updateStmt->bindParam(':id', $id);
    $updateStmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Movie updated successfully."
    ]);
    exit;
}

// DELETE: Delete movie
if ($method === 'DELETE') {
    $user = requireAuth();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($input['id']) ? (int)$input['id'] : 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid movie ID is required."]);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM youtube_movies WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Movie deleted successfully."
    ]);
    exit;
}
