<?php
// backend/setup_db.php

$host = "localhost";
$username = "srikanthchowdary";
$password = ""; // Default empty password for local development
$db_name = "stremfi";
$sql_file = dirname(__DIR__) . "/stremfi_complete.sql";

echo "=== StremFi Database Setup Tool ===\n";

if (!file_exists($sql_file)) {
    die("Error: SQL backup file not found at: $sql_file\n");
}

try {
    // 1. Connect to MySQL server (without selecting DB)
    echo "Connecting to MySQL server at $host...\n";
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Create database
    echo "Creating database '$db_name' if not exists...\n";
    $conn->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "Database created or verified.\n";

    // 3. Connect to the specific database
    $conn->exec("USE `$db_name`");
    echo "Using database '$db_name'.\n";

    // 4. Read and execute the SQL file
    echo "Reading SQL backup file...\n";
    $sql = file_get_contents($sql_file);

    echo "Executing database import...\n";
    // We execute the dump file. Since PDO exec() doesn't handle multiple queries with delimiters well in all setups,
    // we will run it directly. If it fails due to multi-query restrictions, we notify the user.
    $conn->exec($sql);
    echo "Success: Database imported successfully!\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    echo "Make sure your MySQL server is running and the credentials in this script are correct.\n";
    exit(1);
}
