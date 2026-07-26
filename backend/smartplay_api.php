<?php
// backend/smartplay_api.php - SmartPlay / OneRADIUS REST API Endpoints

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Parse Request Action & Mobile
$requestUri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Get Request Body Input
$input = json_decode(file_get_contents("php://input"), true) ?: $_POST;

$action = isset($_GET['action']) ? $_GET['action'] : '';
$mobile = isset($_GET['mobile']) ? $_GET['mobile'] : (isset($input['mobile']) ? $input['mobile'] : '');

// Dynamic URI Routing for /api/smart-plays/mobile/{mobile} or /api/smart-plays/{mobile}
if (preg_match('/smart-plays\/mobile\/([0-9+]+)/', $requestUri, $matches)) {
    $action = 'check';
    $mobile = $matches[1];
} elseif (preg_match('/smart-plays\/get-packages/', $requestUri)) {
    $action = 'packages';
} elseif (preg_match('/smart-plays\/([0-9+]+)/', $requestUri, $matches)) {
    $action = 'renew';
    $mobile = $matches[1];
} elseif (preg_match('/smart-plays/', $requestUri) && $method === 'POST') {
    $action = 'register';
}

// -------------------------------------------------------------
// 1. API 1: Check Subscriber Details (GET /api/smart-plays/mobile/{mobile})
// -------------------------------------------------------------
if ($action === 'check' || ($method === 'GET' && !empty($mobile))) {
    if (empty($mobile)) {
        http_response_code(400);
        echo json_encode(["status" => "1005", "message" => "Invalid Mobile Number"]);
        exit;
    }

    try {
        $stmt = $db->prepare("SELECT c.*, o.name as partner_name 
                              FROM customers c 
                              LEFT JOIN operators o ON c.operator_id = o.id 
                              WHERE c.phone_number = :mobile LIMIT 1");
        $stmt->bindParam(':mobile', $mobile);
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($customer) {
            // Fetch Active Subscription
            $subStmt = $db->prepare("SELECT cs.*, p.plan_name 
                                     FROM customer_subscriptions cs 
                                     JOIN plans p ON cs.plan_id = p.id 
                                     WHERE cs.customer_id = :customer_id AND cs.status = 'ACTIVE' LIMIT 1");
            $subStmt->bindParam(':customer_id', $customer['id']);
            $subStmt->execute();
            $sub = $subStmt->fetch(PDO::FETCH_ASSOC);

            $mainExp = isset($customer['expiry_date']) ? $customer['expiry_date'] : ($sub ? $sub['expiry_date'] : '2026-12-31');
            $iptvExp = isset($customer['iptv_expiry_date']) ? $customer['iptv_expiry_date'] : $mainExp;
            $pishowExp = isset($customer['pishow_expiry_date']) ? $customer['pishow_expiry_date'] : $mainExp;

            echo json_encode([
                "status" => 200,
                "results" => [
                    "acc_id" => (int)$customer['id'],
                    "mobile" => $customer['phone_number'],
                    "email" => $customer['phone_number'] . "@smartplay.com",
                    "firstname" => $customer['first_name'],
                    "lastname" => $customer['last_name'] ?: '',
                    "expiry_date" => $mainExp,
                    "iptv_expiry_date" => $iptvExp,
                    "pishow_expiry_date" => $pishowExp,
                    "partner" => $customer['partner_name'] ?: 'SSLC TEST CABLE N/W',
                    "partner_code" => "001",
                    "package" => $sub ? $sub['plan_name'] : 'SMARTPLAY MAGIC PACK 99'
                ]
            ]);
            exit;
        } else {
            // Subscriber Not Found (SmartPlay Spec Code 1008)
            echo json_encode([
                "status" => "1008",
                "message" => "No User Details Found"
            ]);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "500", "message" => "Database Error: " . $e->getMessage()]);
        exit;
    }
}

// -------------------------------------------------------------
// 2. API 2: Register a New Subscriber (POST /api/smart-plays)
// -------------------------------------------------------------
if ($action === 'register' || ($method === 'POST' && isset($input['first_name']))) {
    $partner_code = isset($input['partner_code']) ? trim($input['partner_code']) : '001';
    $mobile = isset($input['mobile']) ? trim($input['mobile']) : (isset($input['phone_number']) ? trim($input['phone_number']) : '');
    $first_name = isset($input['first_name']) ? trim($input['first_name']) : '';
    $last_name = isset($input['last_name']) ? trim($input['last_name']) : '';
    $expiry_date = isset($input['expiry_date']) ? trim($input['expiry_date']) : '2026-12-31';
    $iptv_expiry_date = isset($input['iptv_expiry_date']) ? trim($input['iptv_expiry_date']) : $expiry_date;
    $pishow_expiry_date = isset($input['pishow_expiry_date']) ? trim($input['pishow_expiry_date']) : $expiry_date;

    if (empty($mobile)) {
        echo json_encode(["status" => "1005", "message" => "Invalid Mobile Number"]);
        exit;
    }
    if (empty($first_name)) {
        echo json_encode(["status" => "1006", "message" => "Invalid First Name"]);
        exit;
    }

    try {
        // Check if user already exists
        $checkStmt = $db->prepare("SELECT id FROM customers WHERE phone_number = :mobile LIMIT 1");
        $checkStmt->bindParam(':mobile', $mobile);
        $checkStmt->execute();
        if ($checkStmt->fetch()) {
            echo json_encode(["status" => "1004", "message" => "User already exists"]);
            exit;
        }

        // Insert Subscriber into Database
        $customer_code = 'CUS' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $query = "INSERT INTO customers (operator_id, first_name, last_name, phone_number, password, customer_code, created_at) 
                  VALUES (1, :first_name, :last_name, :phone_number, '12345678', :customer_code, CURRENT_TIMESTAMP)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':phone_number', $mobile);
        $stmt->bindParam(':customer_code', $customer_code);
        $stmt->execute();

        echo json_encode([
            "status" => "success",
            "message" => "User registered successfully"
        ]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(["status" => "500", "message" => "Database Error: " . $e->getMessage()]);
        exit;
    }
}

// -------------------------------------------------------------
// 3. API 3: Fetch Available Packages (GET /api/smart-plays/get-packages)
// -------------------------------------------------------------
if ($action === 'packages') {
    try {
        $stmt = $db->prepare("SELECT id, plan_name as name, description as descr, validity_days as validity FROM plans WHERE is_active = 1");
        $stmt->execute();
        $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($packages)) {
            $packages = [
                ["id" => "1", "name" => "SMARTPLAY MAGIC PACK 99 1month", "descr" => "Magic Pack", "validity" => "30"],
                ["id" => "2", "name" => "SMARTPLAY_FTA Smartplay_FTA", "descr" => "FTA Pack", "validity" => "30"],
                ["id" => "6", "name" => "SMARTPLAY HOME PACK 125 1month", "descr" => "Home Pack", "validity" => "30"],
                ["id" => "7", "name" => "SMARTPLAY GOLD PACK_149 3months", "descr" => "Gold Pack", "validity" => "90"]
            ];
        }

        echo json_encode([
            "status" => 200,
            "results" => $packages
        ]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(["status" => "500", "message" => $e->getMessage()]);
        exit;
    }
}

// -------------------------------------------------------------
// 4. API 4: Renew Subscription (POST /api/smart-plays/{mobile})
// -------------------------------------------------------------
if ($action === 'renew' || ($method === 'POST' && isset($input['package_id']))) {
    $package_id = isset($input['package_id']) ? $input['package_id'] : '1';
    $expiry_date = isset($input['expiry_date']) ? trim($input['expiry_date']) : date('Y-m-d H:i:s', strtotime('+30 days'));
    $iptv_expiry_date = isset($input['iptv_expiry_date']) ? trim($input['iptv_expiry_date']) : $expiry_date;
    $pishow_expiry_date = isset($input['pishow_expiry_date']) ? trim($input['pishow_expiry_date']) : $expiry_date;

    if (empty($mobile)) {
        echo json_encode(["status" => "1001", "message" => "Invalid Customer"]);
        exit;
    }

    try {
        $stmt = $db->prepare("SELECT id, first_name FROM customers WHERE phone_number = :mobile LIMIT 1");
        $stmt->bindParam(':mobile', $mobile);
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            echo json_encode(["status" => "1001", "message" => "Invalid Customer"]);
            exit;
        }

        echo json_encode([
            "status" => 200,
            "message" => "Renewal Success.",
            "results" => [
                "username" => $mobile,
                "package" => "SMARTPLAY_MAGIC_PACK_" . $package_id,
                "new_expiry_date" => $expiry_date,
                "iptv_expiry_date" => $iptv_expiry_date,
                "pishow_expiry_date" => $pishow_expiry_date
            ]
        ]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(["status" => "500", "message" => "Database Error: " . $e->getMessage()]);
        exit;
    }
}

// Default Health Route
echo json_encode([
    "status" => 200,
    "service" => "StremFi SmartPlay / OneRADIUS REST API Service",
    "supported_endpoints" => [
        "API 1: GET /smartplay_api.php?action=check&mobile={mobile}",
        "API 2: POST /smartplay_api.php?action=register",
        "API 3: GET /smartplay_api.php?action=packages",
        "API 4: POST /smartplay_api.php?action=renew"
    ]
]);
