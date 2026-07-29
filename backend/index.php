<?php
// backend/index.php

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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
                header('Content-Type: application/json');
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

// Token Helper functions
define('JWT_SECRET', 'stremfi_super_secret_key_123456');


function generateToken($userId, $role, $name) {
    $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64_encode(json_encode([
        'userId' => $userId,
        'role' => $role,
        'name' => $name,
        'exp' => time() + (3600 * 24) // 24 hours
    ]));
    $signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET, true);
    $signature = base64_encode($signature);
    return "$header.$payload.$signature";
}

function verifyToken() {
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    
    if (empty($authHeader) && isset($headers['authorization'])) {
        $authHeader = $headers['authorization'];
    }

    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        list($header, $payload, $signature) = $parts;
        $validSignature = base64_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
        
        if (!hash_equals($signature, $validSignature)) {
            return false;
        }
        
        $data = json_decode(base64_decode($payload), true);
        if ($data['exp'] < time()) {
            return false;
        }
        return $data;
    }
    return false;
}

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

// Parse request URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = preg_replace('/^.*backend\/index\.php/', '', $uri);
$uri = preg_replace('/^.*backend/', '', $uri);
$uri = rtrim($uri, '/');

// Get request body
$input = json_decode(file_get_contents("php://input"), true);

// Initialize DB Connection
$database = new Database();
$db = $database->getConnection();

// --- PUBLIC ROUTES ---

// Route: POST /auth/login
if ($uri === '/auth/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = isset($input['identity']) ? trim($input['identity']) : '';
    $password = isset($input['password']) ? trim($input['password']) : '';

    if (empty($identity) || empty($password)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Identity and password are required."]);
        exit;
    }

    $query = "SELECT id, name, role, email, mobile, password, wallet_balance, company_name, is_active 
              FROM operators 
              WHERE email = :identity OR mobile = :identity LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':identity', $identity);
    $stmt->execute();
    $operator = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($operator && ($password === $operator['password'] || password_verify($password, $operator['password']))) {
        if (!$operator['is_active']) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Account is inactive."]);
            exit;
        }

        $token = generateToken($operator['id'], $operator['role'], $operator['name']);
        logActivity($db, $operator['id'], "Login", "Operator successfully logged in.");

        unset($operator['password']);
        echo json_encode([
            "success" => true,
            "message" => "Authentication successful.",
            "token" => $token,
            "user" => $operator
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Invalid credentials."]);
    }
    exit;
}

// --- SECURED ROUTES ---
$user = verifyToken();
if (!$user) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Access denied. Invalid or expired token."]);
    exit;
}

$currentOperatorId = $user['userId'];
$currentOperatorRole = $user['role'];

// Route: GET /profile
if ($uri === '/profile' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT id, name, role, email, mobile, wallet_balance, company_name, address, city, state, pincode, profile_image 
              FROM operators WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $currentOperatorId);
    $stmt->execute();
    $operator = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "profile" => $operator]);
    exit;
}

// Route: GET /dashboard
if ($uri === '/dashboard' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    // Determine filters based on hierarchy
    if ($currentOperatorRole === 'super_admin') {
        // Super Admin sees everything
        $custCountQuery = "SELECT COUNT(*) as count FROM customers";
        $devCountQuery = "SELECT COUNT(*) as count FROM customer_devices WHERE status = 'ACTIVE'";
        $subCountQuery = "SELECT COUNT(*) as count FROM customer_subscriptions WHERE status = 'ACTIVE'";
        $activityQuery = "SELECT l.*, o.name as operator_name FROM activity_logs l LEFT JOIN operators o ON l.operator_id = o.id ORDER BY l.created_at DESC LIMIT 5";
    } elseif ($currentOperatorRole === 'admin') {
        // Admin sees direct customers AND customers managed by their child providers (operators)
        $custCountQuery = "SELECT COUNT(*) as count FROM customers c JOIN operators o ON c.operator_id = o.id WHERE o.parent_id = $currentOperatorId OR c.operator_id = $currentOperatorId";
        $devCountQuery = "SELECT COUNT(*) as count FROM customer_devices cd JOIN customers c ON cd.customer_id = c.id JOIN operators o ON c.operator_id = o.id WHERE o.parent_id = $currentOperatorId OR c.operator_id = $currentOperatorId AND cd.status = 'ACTIVE'";
        $subCountQuery = "SELECT COUNT(*) as count FROM customer_subscriptions cs JOIN customers c ON cs.customer_id = c.id JOIN operators o ON c.operator_id = o.id WHERE o.parent_id = $currentOperatorId OR c.operator_id = $currentOperatorId AND cs.status = 'ACTIVE'";
        $activityQuery = "SELECT l.*, o.name as operator_name FROM activity_logs l LEFT JOIN operators o ON l.operator_id = o.id WHERE l.operator_id = $currentOperatorId OR o.parent_id = $currentOperatorId ORDER BY l.created_at DESC LIMIT 5";
    } else {
        // Provider (operator) sees only their own customers/devices
        $custCountQuery = "SELECT COUNT(*) as count FROM customers WHERE operator_id = $currentOperatorId";
        $devCountQuery = "SELECT COUNT(*) as count FROM customer_devices cd JOIN customers c ON cd.customer_id = c.id WHERE c.operator_id = $currentOperatorId AND cd.status = 'ACTIVE'";
        $subCountQuery = "SELECT COUNT(*) as count FROM customer_subscriptions cs JOIN customers c ON cs.customer_id = c.id WHERE c.operator_id = $currentOperatorId AND cs.status = 'ACTIVE'";
        $activityQuery = "SELECT l.*, o.name as operator_name FROM activity_logs l LEFT JOIN operators o ON l.operator_id = o.id WHERE l.operator_id = $currentOperatorId ORDER BY l.created_at DESC LIMIT 5";
    }

    $stmt = $db->query($custCountQuery);
    $totalCustomers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $db->query($devCountQuery);
    $activeDevices = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $db->query($subCountQuery);
    $activeSubscriptions = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Operator Wallet balance
    $stmt = $db->prepare("SELECT wallet_balance FROM operators WHERE id = :id");
    $stmt->bindParam(':id', $currentOperatorId);
    $stmt->execute();
    $walletBalance = $stmt->fetch(PDO::FETCH_ASSOC)['wallet_balance'];

    $stmt = $db->query($activityQuery);
    $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "stats" => [
            "totalCustomers" => (int)$totalCustomers,
            "activeDevices" => (int)$activeDevices,
            "activeSubscriptions" => (int)$activeSubscriptions,
            "walletBalance" => (float)$walletBalance
        ],
        "recentActivity" => $recentActivity
    ]);
    exit;
}

// Route: /operators (GET, POST, PUT, DELETE) - Multi-tier management
if ($uri === '/operators') {
    // GET: List child operators
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($currentOperatorRole === 'super_admin') {
            // Super Admin gets all Admins and Operators, showing parent name
            $query = "SELECT o1.id, o1.parent_id, o1.role, o1.name, o1.mobile, o1.email, o1.wallet_balance, o1.company_name, o1.is_active, o1.created_at, o2.name as parent_name 
                      FROM operators o1 
                      LEFT JOIN operators o2 ON o1.parent_id = o2.id 
                      WHERE o1.role IN ('admin', 'operator') AND o1.id != :super_id
                      ORDER BY o1.id DESC";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':super_id', $currentOperatorId);
        } elseif ($currentOperatorRole === 'admin') {
            // Admin manages Providers (role operator)
            $query = "SELECT id, parent_id, role, name, mobile, email, wallet_balance, company_name, is_active, created_at 
                      FROM operators WHERE role = 'operator' AND parent_id = :parent_id ORDER BY id DESC";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':parent_id', $currentOperatorId);
        } else {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Unauthorized access."]);
            exit;
        }

        $stmt->execute();
        $operators = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "operators" => $operators]);
        exit;
    }

    // POST: Create Admin or Provider
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = isset($input['name']) ? trim($input['name']) : '';
        $mobile = isset($input['mobile']) ? trim($input['mobile']) : '';
        $email = isset($input['email']) ? trim($input['email']) : '';
        $password = isset($input['password']) ? trim($input['password']) : '';
        $company_name = isset($input['company_name']) ? trim($input['company_name']) : '';
        $wallet_balance = isset($input['wallet_balance']) ? (float)$input['wallet_balance'] : 0.00;

        if (empty($name) || empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Name, email, and password are required."]);
            exit;
        }

        // Determine target role and parent based on hierarchy
        if ($currentOperatorRole === 'super_admin') {
            $role = isset($input['role']) ? trim($input['role']) : 'admin';
            if ($role === 'operator') {
                $parent_id = isset($input['parent_id']) ? (int)$input['parent_id'] : $currentOperatorId;
            } else {
                $parent_id = $currentOperatorId;
            }
        } elseif ($currentOperatorRole === 'admin') {
            $role = 'operator'; // Admin creates operators
            $parent_id = $currentOperatorId;
        } else {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Unauthorized."]);
            exit;
        }

        try {
            $query = "INSERT INTO operators (parent_id, role, name, mobile, email, password, wallet_balance, company_name, is_active, created_at) 
                      VALUES (:parent_id, :role, :name, :mobile, :email, :password, :wallet_balance, :company_name, 1, CURRENT_TIMESTAMP)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':parent_id', $parent_id);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':mobile', $mobile);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':wallet_balance', $wallet_balance);
            $stmt->bindParam(':company_name', $company_name);
            $stmt->execute();

            logActivity($db, $currentOperatorId, "Create Operator", "Created operator $name ($role)");

            echo json_encode(["success" => true, "message" => "Operator created successfully."]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error creating operator: " . $e->getMessage()]);
        }
        exit;
    }

    // PUT: Update Admin or Provider
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $id = isset($input['id']) ? $input['id'] : null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ID is required."]);
            exit;
        }

        // Verification: ensure the target is indeed managed by the logged-in operator
        if ($currentOperatorRole !== 'super_admin') {
            $checkStmt = $db->prepare("SELECT parent_id FROM operators WHERE id = :id");
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            $target = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if (!$target || $target['parent_id'] != $currentOperatorId) {
                http_response_code(403);
                echo json_encode(["success" => false, "message" => "Unauthorized modification."]);
                exit;
            }
        }

        $name = isset($input['name']) ? trim($input['name']) : '';
        $mobile = isset($input['mobile']) ? trim($input['mobile']) : '';
        $email = isset($input['email']) ? trim($input['email']) : '';
        $company_name = isset($input['company_name']) ? trim($input['company_name']) : '';
        $is_active = isset($input['is_active']) ? (int)$input['is_active'] : 1;

        $passwordClause = "";
        $password = isset($input['password']) ? trim($input['password']) : '';
        if (!empty($password)) {
            $passwordClause = ", password = :password";
        }

        try {
            $query = "UPDATE operators 
                      SET name = :name, mobile = :mobile, email = :email, company_name = :company_name, is_active = :is_active $passwordClause 
                      WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':mobile', $mobile);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':company_name', $company_name);
            $stmt->bindParam(':is_active', $is_active);
            $stmt->bindParam(':id', $id);
            if (!empty($password)) {
                $stmt->bindParam(':password', $password);
            }
            $stmt->execute();

            logActivity($db, $currentOperatorId, "Update Operator", "Updated operator ID $id");

            echo json_encode(["success" => true, "message" => "Operator updated successfully."]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error updating operator: " . $e->getMessage()]);
        }
        exit;
    }

    // DELETE: Delete Operator
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ID is required."]);
            exit;
        }

        // Verification
        if ($currentOperatorRole !== 'super_admin') {
            $checkStmt = $db->prepare("SELECT parent_id FROM operators WHERE id = :id");
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            $target = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if (!$target || $target['parent_id'] != $currentOperatorId) {
                http_response_code(403);
                echo json_encode(["success" => false, "message" => "Unauthorized delete."]);
                exit;
            }
        }

        try {
            $stmt = $db->prepare("DELETE FROM operators WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            logActivity($db, $currentOperatorId, "Delete Operator", "Deleted operator ID $id");

            echo json_encode(["success" => true, "message" => "Operator deleted successfully."]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Cannot delete operator. Make sure there are no depending child tables."]);
        }
        exit;
    }
}

// Route: /customers (GET, POST, PUT, DELETE)
if ($uri === '/customers') {
    // GET: List customers
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($currentOperatorRole === 'super_admin') {
            $query = "SELECT c.*, o.name as operator_name 
                      FROM customers c 
                      LEFT JOIN operators o ON c.operator_id = o.id 
                      ORDER BY c.id DESC";
            $stmt = $db->prepare($query);
        } elseif ($currentOperatorRole === 'admin') {
            // Admin sees customers of their providers OR their direct customers
            $query = "SELECT c.*, o.name as operator_name 
                      FROM customers c 
                      LEFT JOIN operators o ON c.operator_id = o.id 
                      WHERE o.parent_id = :parent_id OR c.operator_id = :parent_id
                      ORDER BY c.id DESC";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':parent_id', $currentOperatorId);
        } else {
            // Provider sees only their own customers
            $query = "SELECT c.*, :op_name as operator_name 
                      FROM customers c 
                      WHERE c.operator_id = :operator_id 
                      ORDER BY c.id DESC";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':operator_id', $currentOperatorId);
            $stmt->bindValue(':op_name', $user['name']);
        }
        $stmt->execute();
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch subscriptions & devices for each customer
        foreach ($customers as &$customer) {
            $subStmt = $db->prepare("SELECT cs.*, p.plan_name, p.speed, p.data_limit 
                                     FROM customer_subscriptions cs 
                                     JOIN plans p ON cs.plan_id = p.id 
                                     WHERE cs.customer_id = :customer_id AND cs.status = 'ACTIVE' LIMIT 1");
            $subStmt->bindParam(':customer_id', $customer['id']);
            $subStmt->execute();
            $customer['active_subscription'] = $subStmt->fetch(PDO::FETCH_ASSOC) ?: null;

            $devStmt = $db->prepare("SELECT COUNT(*) as count FROM customer_devices WHERE customer_id = :customer_id");
            $devStmt->bindParam(':customer_id', $customer['id']);
            $devStmt->execute();
            $customer['device_count'] = (int)$devStmt->fetch(PDO::FETCH_ASSOC)['count'];
        }

        echo json_encode(["success" => true, "customers" => $customers]);
        exit;
    }

    // POST: Create customer
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $operator_id = isset($input['operator_id']) ? $input['operator_id'] : null;
        
        // If not specified, default to parent, but providers always assign to themselves
        if ($currentOperatorRole === 'operator') {
            $operator_id = $currentOperatorId;
        } elseif (!$operator_id) {
            $operator_id = $currentOperatorId;
        }

        $first_name = isset($input['first_name']) ? trim($input['first_name']) : '';
        $last_name = isset($input['last_name']) ? trim($input['last_name']) : '';
        $phone_number = isset($input['phone_number']) ? trim($input['phone_number']) : '';
        $password = isset($input['password']) ? trim($input['password']) : '';
        $customer_code = isset($input['customer_code']) ? trim($input['customer_code']) : '';
        $installation_address = isset($input['installation_address']) ? trim($input['installation_address']) : '';
        $notes = isset($input['notes']) ? trim($input['notes']) : '';
        $login_devices = isset($input['login_devices']) ? (int)$input['login_devices'] : 0;
        $Max_login_devices = isset($input['Max_login_devices']) ? (int)$input['Max_login_devices'] : 4;

        if (empty($first_name) || empty($phone_number) || empty($password)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "First name, phone number, and password are required."]);
            exit;
        }

        if (empty($customer_code)) {
            $customer_code = 'CUS' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        }

        try {
            $query = "INSERT INTO customers (operator_id, first_name, last_name, phone_number, password, customer_code, installation_address, notes, login_devices, Max_login_devices, created_at) 
                      VALUES (:operator_id, :first_name, :last_name, :phone_number, :password, :customer_code, :installation_address, :notes, :login_devices, :Max_login_devices, CURRENT_TIMESTAMP)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':operator_id', $operator_id);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':phone_number', $phone_number);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':customer_code', $customer_code);
            $stmt->bindParam(':installation_address', $installation_address);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':login_devices', $login_devices);
            $stmt->bindParam(':Max_login_devices', $Max_login_devices);
            $stmt->execute();
            $newCustomerId = $db->lastInsertId();

            logActivity($db, $currentOperatorId, "Create Customer", "Created customer $first_name ($customer_code)");

            echo json_encode(["success" => true, "message" => "Customer created successfully.", "id" => $newCustomerId]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error creating customer: " . $e->getMessage()]);
        }
        exit;
    }

    // PUT: Update customer
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $id = isset($input['id']) ? $input['id'] : null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Customer ID is required."]);
            exit;
        }

        // Verify hierarchy access
        if ($currentOperatorRole !== 'super_admin') {
            $checkStmt = $db->prepare("SELECT c.operator_id, o.parent_id FROM customers c JOIN operators o ON c.operator_id = o.id WHERE c.id = :id");
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            $cust = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$cust) {
                http_response_code(404);
                echo json_encode(["success" => false, "message" => "Customer not found."]);
                exit;
            }
            if ($currentOperatorRole === 'admin' && $cust['parent_id'] != $currentOperatorId && $cust['operator_id'] != $currentOperatorId) {
                http_response_code(403);
                echo json_encode(["success" => false, "message" => "Unauthorized."]);
                exit;
            }
            if ($currentOperatorRole === 'operator' && $cust['operator_id'] != $currentOperatorId) {
                http_response_code(403);
                echo json_encode(["success" => false, "message" => "Unauthorized."]);
                exit;
            }
        }

        $first_name = isset($input['first_name']) ? trim($input['first_name']) : '';
        $last_name = isset($input['last_name']) ? trim($input['last_name']) : '';
        $phone_number = isset($input['phone_number']) ? trim($input['phone_number']) : '';
        $customer_code = isset($input['customer_code']) ? trim($input['customer_code']) : '';
        $installation_address = isset($input['installation_address']) ? trim($input['installation_address']) : '';
        $notes = isset($input['notes']) ? trim($input['notes']) : '';
        $Max_login_devices = isset($input['Max_login_devices']) ? (int)$input['Max_login_devices'] : 4;
        
        $passwordClause = "";
        $password = isset($input['password']) ? trim($input['password']) : '';
        if (!empty($password)) {
            $passwordClause = ", password = :password";
        }

        try {
            $query = "UPDATE customers 
                      SET first_name = :first_name, last_name = :last_name, phone_number = :phone_number,
                          customer_code = :customer_code, installation_address = :installation_address, 
                          notes = :notes, Max_login_devices = :Max_login_devices $passwordClause 
                      WHERE id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':phone_number', $phone_number);
            $stmt->bindParam(':customer_code', $customer_code);
            $stmt->bindParam(':installation_address', $installation_address);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':Max_login_devices', $Max_login_devices);
            $stmt->bindParam(':id', $id);
            if (!empty($password)) {
                $stmt->bindParam(':password', $password);
            }
            $stmt->execute();

            logActivity($db, $currentOperatorId, "Update Customer", "Updated customer ID $id");

            echo json_encode(["success" => true, "message" => "Customer updated successfully."]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error updating customer: " . $e->getMessage()]);
        }
        exit;
    }

    // DELETE: Delete customer
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Customer ID is required."]);
            exit;
        }

        // Verify hierarchy access
        if ($currentOperatorRole !== 'super_admin') {
            $checkStmt = $db->prepare("SELECT c.operator_id, o.parent_id FROM customers c JOIN operators o ON c.operator_id = o.id WHERE c.id = :id");
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            $cust = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$cust) {
                http_response_code(404);
                echo json_encode(["success" => false, "message" => "Customer not found."]);
                exit;
            }
            if ($currentOperatorRole === 'admin' && $cust['parent_id'] != $currentOperatorId && $cust['operator_id'] != $currentOperatorId) {
                http_response_code(403);
                echo json_encode(["success" => false, "message" => "Unauthorized."]);
                exit;
            }
            if ($currentOperatorRole === 'operator' && $cust['operator_id'] != $currentOperatorId) {
                http_response_code(403);
                echo json_encode(["success" => false, "message" => "Unauthorized."]);
                exit;
            }
        }

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("DELETE FROM customer_devices WHERE customer_id = :customer_id");
            $stmt->bindParam(':customer_id', $id);
            $stmt->execute();

            $stmt = $db->prepare("DELETE FROM customer_subscriptions WHERE customer_id = :customer_id");
            $stmt->bindParam(':customer_id', $id);
            $stmt->execute();

            $stmt = $db->prepare("DELETE FROM customers WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $db->commit();
            logActivity($db, $currentOperatorId, "Delete Customer", "Deleted customer ID $id");

            echo json_encode(["success" => true, "message" => "Customer deleted successfully."]);
        } catch (PDOException $e) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error deleting customer: " . $e->getMessage()]);
        }
        exit;
    }
}

// Route: /devices (GET, POST, PUT)
if ($uri === '/devices') {
    // GET: List devices
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $customer_id = isset($_GET['customer_id']) ? $_GET['customer_id'] : null;
        if (!$customer_id) {
            if ($currentOperatorRole === 'super_admin') {
                $query = "SELECT cd.*, c.first_name, c.last_name, c.customer_code 
                          FROM customer_devices cd 
                          JOIN customers c ON cd.customer_id = c.id 
                          ORDER BY cd.id DESC";
                $stmt = $db->prepare($query);
            } elseif ($currentOperatorRole === 'admin') {
                $query = "SELECT cd.*, c.first_name, c.last_name, c.customer_code 
                          FROM customer_devices cd 
                          JOIN customers c ON cd.customer_id = c.id 
                          JOIN operators o ON c.operator_id = o.id
                          WHERE o.parent_id = :parent_id OR c.operator_id = :parent_id
                          ORDER BY cd.id DESC";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':parent_id', $currentOperatorId);
            } else {
                $query = "SELECT cd.*, c.first_name, c.last_name, c.customer_code 
                          FROM customer_devices cd 
                          JOIN customers c ON cd.customer_id = c.id 
                          WHERE c.operator_id = :operator_id 
                          ORDER BY cd.id DESC";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':operator_id', $currentOperatorId);
            }
        } else {
            $query = "SELECT cd.*, c.first_name, c.last_name, c.customer_code 
                      FROM customer_devices cd 
                      JOIN customers c ON cd.customer_id = c.id 
                      WHERE cd.customer_id = :customer_id 
                      ORDER BY cd.id DESC";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':customer_id', $customer_id);
        }

        $stmt->execute();
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "devices" => $devices]);
        exit;
    }

    // POST: Create/Register device
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $customer_id = isset($input['customer_id']) ? $input['customer_id'] : null;
        $device_name = isset($input['device_name']) ? trim($input['device_name']) : '';
        $device_uuid = isset($input['device_uuid']) ? trim($input['device_uuid']) : '';
        $mac_address = isset($input['mac_address']) ? trim($input['mac_address']) : '';
        $serial_number = isset($input['serial_number']) ? trim($input['serial_number']) : '';
        $android_id = isset($input['android_id']) ? trim($input['android_id']) : '';

        if (!$customer_id || empty($device_uuid)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Customer ID and Device UUID are required."]);
            exit;
        }

        // Verify hierarchy access
        if ($currentOperatorRole !== 'super_admin') {
            $checkStmt = $db->prepare("SELECT c.operator_id, o.parent_id FROM customers c JOIN operators o ON c.operator_id = o.id WHERE c.id = :id");
            $checkStmt->bindParam(':id', $customer_id);
            $checkStmt->execute();
            $cust = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if (!$cust) {
                http_response_code(404);
                echo json_encode(["success" => false, "message" => "Customer not found."]);
                exit;
            }
            if ($currentOperatorRole === 'admin' && $cust['parent_id'] != $currentOperatorId && $cust['operator_id'] != $currentOperatorId) {
                http_response_code(403);
                echo json_encode(["success" => false, "message" => "Unauthorized."]);
                exit;
            }
            if ($currentOperatorRole === 'operator' && $cust['operator_id'] != $currentOperatorId) {
                http_response_code(403);
                echo json_encode(["success" => false, "message" => "Unauthorized."]);
                exit;
            }
        }

        try {
            // Check device limits
            $limitStmt = $db->prepare("SELECT COUNT(*) as count FROM customer_devices WHERE customer_id = :customer_id");
            $limitStmt->bindParam(':customer_id', $customer_id);
            $limitStmt->execute();
            $deviceCount = $limitStmt->fetch(PDO::FETCH_ASSOC)['count'];

            $maxStmt = $db->prepare("SELECT Max_login_devices FROM customers WHERE id = :id");
            $maxStmt->bindParam(':id', $customer_id);
            $maxStmt->execute();
            $maxDevices = $maxStmt->fetch(PDO::FETCH_ASSOC)['Max_login_devices'];

            if ($deviceCount >= $maxDevices) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Device limit reached ($maxDevices devices max)."]);
                exit;
            }

            $query = "INSERT INTO customer_devices (customer_id, device_uuid, device_name, mac_address, serial_number, android_id, status, created_at) 
                      VALUES (:customer_id, :device_uuid, :device_name, :mac_address, :serial_number, :android_id, 'ACTIVE', CURRENT_TIMESTAMP)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->bindParam(':device_uuid', $device_uuid);
            $stmt->bindParam(':device_name', $device_name);
            $stmt->bindParam(':mac_address', $mac_address);
            $stmt->bindParam(':serial_number', $serial_number);
            $stmt->bindParam(':android_id', $android_id);
            $stmt->execute();

            logActivity($db, $currentOperatorId, "Register Device", "Registered device $device_name for customer ID $customer_id");

            echo json_encode(["success" => true, "message" => "Device registered successfully."]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error registering device: " . $e->getMessage()]);
        }
        exit;
    }

    // PUT: Update device status
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $id = isset($input['id']) ? $input['id'] : null;
        $status = isset($input['status']) ? trim($input['status']) : '';

        if (!$id || empty($status)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Device ID and status are required."]);
            exit;
        }

        // Verify hierarchy access
        if ($currentOperatorRole !== 'super_admin') {
            $checkStmt = $db->prepare("SELECT c.operator_id, o.parent_id FROM customer_devices cd JOIN customers c ON cd.customer_id = c.id JOIN operators o ON c.operator_id = o.id WHERE cd.id = :id");
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            $owner = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if (!$owner) {
                http_response_code(404);
                echo json_encode(["success" => false, "message" => "Device not found."]);
                exit;
            }
            if ($currentOperatorRole === 'admin' && $owner['parent_id'] != $currentOperatorId && $owner['operator_id'] != $currentOperatorId) {
                http_response_code(403);
                echo json_encode(["success" => false, "message" => "Unauthorized."]);
                exit;
            }
            if ($currentOperatorRole === 'operator' && $owner['operator_id'] != $currentOperatorId) {
                http_response_code(403);
                echo json_encode(["success" => false, "message" => "Unauthorized."]);
                exit;
            }
        }

        try {
            $stmt = $db->prepare("UPDATE customer_devices SET status = :status WHERE id = :id");
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            logActivity($db, $currentOperatorId, "Update Device Status", "Updated device ID $id status to $status");

            echo json_encode(["success" => true, "message" => "Device status updated to $status successfully."]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error updating device status: " . $e->getMessage()]);
        }
        exit;
    }
}

// Route: GET /plans
if ($uri === '/plans' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $db->query("SELECT p.*, pr.name as provider_name 
                        FROM plans p 
                        LEFT JOIN providers pr ON p.provider_id = pr.id 
                        WHERE p.is_active = 1");
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "plans" => $plans]);
    exit;
}

// Route: POST /recharge
if ($uri === '/recharge' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = isset($input['customer_id']) ? $input['customer_id'] : null;
    $plan_id = isset($input['plan_id']) ? $input['plan_id'] : null;
    $payment_mode = isset($input['payment_mode']) ? trim($input['payment_mode']) : 'WALLET';

    if (!$customer_id || !$plan_id) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Customer ID and Plan ID are required."]);
        exit;
    }

    try {
        $db->beginTransaction();

        $planStmt = $db->prepare("SELECT price, validity_days, plan_name FROM plans WHERE id = :id LIMIT 1");
        $planStmt->bindParam(':id', $plan_id);
        $planStmt->execute();
        $plan = $planStmt->fetch(PDO::FETCH_ASSOC);

        if (!$plan) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Plan not found."]);
            $db->rollBack();
            exit;
        }

        $price = (float)$plan['price'];
        $validity_days = (int)$plan['validity_days'];

        if ($payment_mode === 'WALLET') {
            $opStmt = $db->prepare("SELECT wallet_balance FROM operators WHERE id = :id FOR UPDATE");
            $opStmt->bindParam(':id', $currentOperatorId);
            $opStmt->execute();
            $operator = $opStmt->fetch(PDO::FETCH_ASSOC);

            $balance = (float)$operator['wallet_balance'];
            if ($balance < $price) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Insufficient wallet balance (Required: ₹$price, Current: ₹$balance)."]);
                $db->rollBack();
                exit;
            }

            $newBalance = $balance - $price;
            $updateOpStmt = $db->prepare("UPDATE operators SET wallet_balance = :new_balance WHERE id = :id");
            $updateOpStmt->bindParam(':new_balance', $newBalance);
            $updateOpStmt->bindParam(':id', $currentOperatorId);
            $updateOpStmt->execute();

            $txn_id = strtoupper(substr('TXN' . uniqid() . rand(10,99), 0, 16));
            
            $txnQuery = "INSERT INTO wallet_transactions (transaction_id, operator_id, transaction_type, amount, balance_before, balance_after, remarks, created_by, created_at) 
                         VALUES (:transaction_id, :operator_id, 'DEBIT', :amount, :balance_before, :balance_after, :remarks, :created_by, CURRENT_TIMESTAMP)";
            $txnStmt = $db->prepare($txnQuery);
            $txnStmt->bindParam(':transaction_id', $txn_id);
            $txnStmt->bindParam(':operator_id', $currentOperatorId);
            $txnStmt->bindParam(':amount', $price);
            $txnStmt->bindParam(':balance_before', $balance);
            $txnStmt->bindParam(':balance_after', $newBalance);
            $remarks = "Recharged Customer ID $customer_id for Plan: " . $plan['plan_name'];
            $txnStmt->bindParam(':remarks', $remarks);
            $txnStmt->bindParam(':created_by', $currentOperatorId);
            $txnStmt->execute();
        } else {
            $txn_id = strtoupper(substr('EXT' . uniqid() . rand(10,99), 0, 16));
        }

        $subStmt = $db->prepare("SELECT id, expiry_date FROM customer_subscriptions WHERE customer_id = :customer_id AND status = 'ACTIVE' LIMIT 1");
        $subStmt->bindParam(':customer_id', $customer_id);
        $subStmt->execute();
        $existingSub = $subStmt->fetch(PDO::FETCH_ASSOC);

        $activation_date = date('Y-m-d');
        if ($existingSub) {
            $expiry_timestamp = strtotime($existingSub['expiry_date']);
            $start_timestamp = ($expiry_timestamp > time()) ? $expiry_timestamp : time();
            $activation_date = date('Y-m-d', $start_timestamp);
            $expiry_date = date('Y-m-d', strtotime("+$validity_days days", $start_timestamp));
            
            $deactStmt = $db->prepare("UPDATE customer_subscriptions SET status = 'EXPIRED' WHERE customer_id = :customer_id");
            $deactStmt->bindParam(':customer_id', $customer_id);
            $deactStmt->execute();
        } else {
            $expiry_date = date('Y-m-d', strtotime("+$validity_days days"));
        }

        $newSubQuery = "INSERT INTO customer_subscriptions (customer_id, plan_id, activated_by, activation_date, expiry_date, status, auto_renew, created_at) 
                        VALUES (:customer_id, :plan_id, :activated_by, :activation_date, :expiry_date, 'ACTIVE', 0, CURRENT_TIMESTAMP)";
        $newSubStmt = $db->prepare($newSubQuery);
        $newSubStmt->bindParam(':customer_id', $customer_id);
        $newSubStmt->bindParam(':plan_id', $plan_id);
        $newSubStmt->bindParam(':activated_by', $currentOperatorId);
        $newSubStmt->bindParam(':activation_date', $activation_date);
        $newSubStmt->bindParam(':expiry_date', $expiry_date);
        $newSubStmt->execute();
        $newSubId = $db->lastInsertId();

        $histQuery = "INSERT INTO recharge_history (customer_id, subscription_id, plan_id, amount, payment_mode, recharge_type, recharged_by, recharge_date) 
                      VALUES (:customer_id, :subscription_id, :plan_id, :amount, :payment_mode, :recharge_type, :recharged_by, CURRENT_TIMESTAMP)";
        $histStmt = $db->prepare($histQuery);
        $histStmt->bindParam(':customer_id', $customer_id);
        $histStmt->bindParam(':subscription_id', $newSubId);
        $histStmt->bindParam(':plan_id', $plan_id);
        $histStmt->bindParam(':amount', $price);
        $histStmt->bindParam(':payment_mode', $payment_mode);
        $recharge_type = $existingSub ? 'RENEWAL' : 'NEW';
        $histStmt->bindParam(':recharge_type', $recharge_type);
        $histStmt->bindParam(':recharged_by', $currentOperatorId);
        $histStmt->execute();

        $db->commit();

        logActivity($db, $currentOperatorId, "Recharge", "Recharged Customer ID $customer_id with Plan: " . $plan['plan_name']);

        echo json_encode([
            "success" => true,
            "message" => "Recharge successful. Subscription activated until $expiry_date.",
            "transaction_id" => $txn_id,
            "expiry_date" => $expiry_date
        ]);

    } catch (PDOException $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error performing recharge: " . $e->getMessage()]);
    }
    exit;
}

// Route: POST /wallet/allocate - Allocate/Transfer wallet balance to sub-operators
if ($uri === '/wallet/allocate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_operator_id = isset($input['operator_id']) ? $input['operator_id'] : null;
    $amount = isset($input['amount']) ? (float)$input['amount'] : 0.00;

    if (!$target_operator_id || $amount <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Target operator ID and positive amount are required."]);
        exit;
    }

    try {
        $db->beginTransaction();

        // 1. Fetch current operator balance
        $parentStmt = $db->prepare("SELECT wallet_balance, name FROM operators WHERE id = :id FOR UPDATE");
        $parentStmt->bindParam(':id', $currentOperatorId);
        $parentStmt->execute();
        $parent = $parentStmt->fetch(PDO::FETCH_ASSOC);

        if (!$parent) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Operator profile not found."]);
            $db->rollBack();
            exit;
        }

        $parentBalance = (float)$parent['wallet_balance'];

        // 2. Fetch child operator balance and verify parent relationship
        $childStmt = $db->prepare("SELECT wallet_balance, name, parent_id FROM operators WHERE id = :id FOR UPDATE");
        $childStmt->bindParam(':id', $target_operator_id);
        $childStmt->execute();
        $child = $childStmt->fetch(PDO::FETCH_ASSOC);

        if (!$child) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Target operator not found."]);
            $db->rollBack();
            exit;
        }

        if ($currentOperatorRole !== 'super_admin' && $child['parent_id'] != $currentOperatorId) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "You are not authorized to allocate balance to this operator."]);
            $db->rollBack();
            exit;
        }

        $childBalance = (float)$child['wallet_balance'];

        // 3. Deduct from parent
        $newParentBalance = $parentBalance - $amount;
        $updParent = $db->prepare("UPDATE operators SET wallet_balance = :balance WHERE id = :id");
        $updParent->bindParam(':balance', $newParentBalance);
        $updParent->bindParam(':id', $currentOperatorId);
        $updParent->execute();

        // 4. Add to child
        $newChildBalance = $childBalance + $amount;
        $updChild = $db->prepare("UPDATE operators SET wallet_balance = :balance WHERE id = :id");
        $updChild->bindParam(':balance', $newChildBalance);
        $updChild->bindParam(':id', $target_operator_id);
        $updChild->execute();

        // 5. Create transaction log for parent (DEBIT)
        $txn_id_p = strtoupper(substr('TRF' . uniqid() . rand(10,99), 0, 16));
        $remarks_p = "Transferred ₹" . number_format($amount, 2) . " to operator " . $child['name'] . " (ID: $target_operator_id)";
        $txnPStmt = $db->prepare("INSERT INTO wallet_transactions (transaction_id, operator_id, transaction_type, amount, balance_before, balance_after, remarks, created_by, created_at) 
                                 VALUES (:txn_id, :op_id, 'DEBIT', :amount, :before, :after, :remarks, :by, CURRENT_TIMESTAMP)");
        $txnPStmt->bindParam(':txn_id', $txn_id_p);
        $txnPStmt->bindParam(':op_id', $currentOperatorId);
        $txnPStmt->bindParam(':amount', $amount);
        $txnPStmt->bindParam(':before', $parentBalance);
        $txnPStmt->bindParam(':after', $newParentBalance);
        $txnPStmt->bindParam(':remarks', $remarks_p);
        $txnPStmt->bindParam(':by', $currentOperatorId);
        $txnPStmt->execute();

        // 6. Create transaction log for child (CREDIT)
        $txn_id_c = strtoupper(substr('REC' . uniqid() . rand(10,99), 0, 16));
        $remarks_c = "Received ₹" . number_format($amount, 2) . " from parent " . $parent['name'];
        $txnCStmt = $db->prepare("INSERT INTO wallet_transactions (transaction_id, operator_id, transaction_type, amount, balance_before, balance_after, remarks, created_by, created_at) 
                                 VALUES (:txn_id, :op_id, 'CREDIT', :amount, :before, :after, :remarks, :by, CURRENT_TIMESTAMP)");
        $txnCStmt->bindParam(':txn_id', $txn_id_c);
        $txnCStmt->bindParam(':op_id', $target_operator_id);
        $txnCStmt->bindParam(':amount', $amount);
        $txnCStmt->bindParam(':before', $childBalance);
        $txnCStmt->bindParam(':after', $newChildBalance);
        $txnCStmt->bindParam(':remarks', $remarks_c);
        $txnCStmt->bindParam(':by', $currentOperatorId);
        $txnCStmt->execute();

        $db->commit();

        logActivity($db, $currentOperatorId, "Wallet Allocation", "Allocated ₹$amount to operator ID $target_operator_id (" . $child['name'] . ")");

        echo json_encode(["success" => true, "message" => "Transferred ₹" . number_format($amount, 2) . " successfully."]);

    } catch (PDOException $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error allocating wallet funds: " . $e->getMessage()]);
    }
    exit;
}

// Route: GET /wallet/transactions
if ($uri === '/wallet/transactions' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($currentOperatorRole === 'super_admin') {
        $query = "SELECT t.*, o.name as operator_name 
                  FROM wallet_transactions t 
                  LEFT JOIN operators o ON t.operator_id = o.id 
                  ORDER BY t.created_at DESC";
        $stmt = $db->prepare($query);
    } elseif ($currentOperatorRole === 'admin') {
        // Admin sees their own transactions and transactions of their providers
        $query = "SELECT t.*, o.name as operator_name 
                  FROM wallet_transactions t 
                  LEFT JOIN operators o ON t.operator_id = o.id 
                  WHERE t.operator_id = :parent_id OR o.parent_id = :parent_id
                  ORDER BY t.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':parent_id', $currentOperatorId);
    } else {
        // Provider sees only their own
        $query = "SELECT t.*, :op_name as operator_name 
                  FROM wallet_transactions t 
                  WHERE t.operator_id = :operator_id 
                  ORDER BY t.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':operator_id', $currentOperatorId);
        $stmt->bindValue(':op_name', $user['name']);
    }
    
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "transactions" => $transactions]);
    exit;
}

// Additional Route Handlers
if ($uri === '/app_versions') { require_once __DIR__ . '/app_versions.php'; exit; }
if ($uri === '/app_store') { require_once __DIR__ . '/app_store.php'; exit; }
if ($uri === '/actors') { require_once __DIR__ . '/actors.php'; exit; }
if ($uri === '/youtube_categories') { require_once __DIR__ . '/youtube_categories.php'; exit; }
if ($uri === '/youtube_movies') { require_once __DIR__ . '/youtube_movies.php'; exit; }
if ($uri === '/tv_channels') { require_once __DIR__ . '/tv_channels.php'; exit; }
if ($uri === '/music_categories') { require_once __DIR__ . '/music_categories.php'; exit; }
if ($uri === '/music_albums') { require_once __DIR__ . '/music_albums.php'; exit; }
if ($uri === '/music') { require_once __DIR__ . '/music.php'; exit; }
if ($uri === '/education_categories') { require_once __DIR__ . '/education_categories.php'; exit; }
if ($uri === '/education_subjects') { require_once __DIR__ . '/education_subjects.php'; exit; }
if ($uri === '/education_videos') { require_once __DIR__ . '/education_videos.php'; exit; }
if ($uri === '/upload_actor_image') { require_once __DIR__ . '/upload_actor_image.php'; exit; }
if ($uri === '/upload_banner') { require_once __DIR__ . '/upload_banner.php'; exit; }
if ($uri === '/upload_logo') { require_once __DIR__ . '/upload_logo.php'; exit; }
if ($uri === '/upload_movie_thumbnail') { require_once __DIR__ . '/upload_movie_thumbnail.php'; exit; }
if ($uri === '/ads') { require_once __DIR__ . '/ads.php'; exit; }
if ($uri === '/upload_ad_image') { require_once __DIR__ . '/upload_ad_image.php'; exit; }
if ($uri === '/ip_whitelist') { require_once __DIR__ . '/ip_whitelist.php'; exit; }

// Default 404 handler
http_response_code(404);
echo json_encode(["success" => false, "message" => "API endpoint not found."]);
exit;


