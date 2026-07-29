<?php
// backend/bootstrap.php

// CORS Headers
if (!headers_sent()) {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Inlined Database Connection Class (No config/ folder required)
if (!class_exists('Database')) {
    class Database {
        private $host = "localhost";
        private $db_name = "stremfi";
        private $username = "srikanthchowdary";
        private $password = "";
        public $conn;

        public function getConnection() {
            $this->conn = null;
            try {
                $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->exec("set names utf8mb4");
            } catch(PDOException $exception) {
                if (!headers_sent()) header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    "success" => false,
                    "message" => "Database connection error: " . $exception->getMessage()
                ]);
                exit;
            }
            return $this->conn;
        }
    }
}

if (!defined('JWT_SECRET')) {
    define('JWT_SECRET', 'stremfi_super_secret_key_123456');
}

if (!function_exists('base64UrlEncode')) {
    function base64UrlEncode($data) {
        return rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($data)), '=');
    }
}

if (!function_exists('base64UrlDecode')) {
    function base64UrlDecode($data) {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}

if (!function_exists('generateToken')) {
    function generateToken($userId, $role, $name) {
        $header = base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64UrlEncode(json_encode([
            'userId' => (int)$userId,
            'role' => $role,
            'name' => $name,
            'exp' => time() + (3600 * 24) // 24 hours
        ]));
        $rawSignature = hash_hmac('sha256', "$header.$payload", JWT_SECRET, true);
        $signature = base64UrlEncode($rawSignature);
        return "$header.$payload.$signature";
    }
}

if (!function_exists('verifyToken')) {
    function verifyToken() {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : '');
        
        if (empty($authHeader) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        }
        if (empty($authHeader) && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        if (preg_match('/Bearer\s(\S+)/i', trim($authHeader), $matches)) {
            $token = trim($matches[1]);
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return false;
            }
            list($header, $payload, $signature) = $parts;
            $rawSignature = hash_hmac('sha256', "$header.$payload", JWT_SECRET, true);
            $validSignature = base64UrlEncode($rawSignature);

            if (!hash_equals($signature, $validSignature)) {
                $altValidSig = base64_encode($rawSignature);
                $sigNorm = str_replace(['-', '_'], ['+', '/'], urldecode($signature));
                if (!hash_equals(rtrim($sigNorm, '='), rtrim($altValidSig, '='))) {
                    return false;
                }
            }
            
            $data = json_decode(base64UrlDecode($payload), true);
            if (!$data || !isset($data['exp']) || $data['exp'] < time()) {
                return false;
            }
            return $data;
        }
        return false;
    }
}

if (!function_exists('logActivity')) {
    function logActivity($db, $operatorId, $activity, $description = "") {
        try {
            $query = "INSERT INTO activity_logs (operator_id, activity, description, created_at) 
                      VALUES (:operator_id, :activity, :description, CURRENT_TIMESTAMP)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':operator_id', $operatorId);
            $stmt->bindParam(':activity', $activity);
            $stmt->bindParam(':description', $description);
            $stmt->execute();
        } catch (PDOException $e) {
            // Fail silently
        }
    }
}

if (!function_exists('requireAuth')) {
    function requireAuth() {
        $user = verifyToken();
        if (!$user) {
            if (!headers_sent()) header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Access denied. Invalid or expired token."]);
            exit;
        }
        return $user;
    }
}

// Get request body
if (!isset($input)) {
    $input = json_decode(file_get_contents("php://input"), true);
}

// Initialize DB Connection
if (!isset($db) || !$db) {
    $database = new Database();
    $db = $database->getConnection();
}
