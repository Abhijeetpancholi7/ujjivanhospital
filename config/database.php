<?php
declare(strict_types=1);

// Database configuration for SQL Server
$serverName = 'DESKTOP-KHCGP49\\SQLEXPRESS02';
$database   = 'ujjivan';
$username   = 'Abhijeetpancholi';
$password   = 'Monster7@12';

try {
    $dsn = "sqlsrv:Server=$serverName;Database=$database";
    
    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper function for escaping HTML output
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

// Helper function for cleaning input values
function clean_value($value) {
    if ($value === null) return '';
    return trim((string)$value);
}

// CSRF Functions
function csrf_token() {
    if (!isset($_SESSION)) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    if (!isset($_SESSION)) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// JSON response helper
function json_response($success, $message, $count = 0, $extra = []) {
    $response = array_merge([
        'success' => $success,
        'message' => $message,
        'count' => $count
    ], $extra);
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Start session for CSRF if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================================
// ADDITIONAL HELPER FUNCTIONS
// ============================================================================

/**
 * Get PDO instance
 * @return PDO
 */
function getPdo(): PDO {
    global $pdo;
    return $pdo;
}

/**
 * Send JSON response with proper headers
 * @param mixed $data Response data
 * @param int $statusCode HTTP status code
 */
function jsonResponse(mixed $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Read and parse JSON body from request
 * @return array Parsed JSON data
 */
function readJsonBody(): array {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!is_array($data)) {
        jsonResponse(['success' => false, 'message' => 'Invalid JSON provided.'], 400);
    }
    
    return $data;
}

/**
 * Normalize date string to YYYY-MM-DD format
 * @param string $date Date string in any format
 * @return string|null Normalized date or null if invalid
 */
function normalizeDate(string $date): ?string {
    $date = trim($date);
    if ($date === '') {
        return null;
    }
    
    // Try parsing the date
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return null;
    }
    
    return date('Y-m-d', $timestamp);
}

/**
 * Normalize time string to HH:MM format
 * @param string $time Time string in any format
 * @return string|null Normalized time or null if invalid
 */
function normalizeTime(string $time): ?string {
    $time = trim($time);
    if ($time === '') {
        return null;
    }
    
    // Try parsing time
    $timestamp = strtotime('2000-01-01 ' . $time);
    if ($timestamp === false) {
        return null;
    }
    
    return date('H:i', $timestamp);
}

/**
 * Validate email address
 * @param string $email Email to validate
 * @return bool
 */
function is_valid_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitize string for database storage
 * @param mixed $value Value to sanitize
 * @return string Sanitized value
 */
function sanitize(mixed $value): string {
    return trim((string)$value);
}

// Database table structure for SQL Server (run this once)
/*
CREATE TABLE indoor_records (
    id INT IDENTITY(1,1) PRIMARY KEY,
    sno NVARCHAR(50),
    yearly_no NVARCHAR(50),
    monthly_no NVARCHAR(50),
    admission_date DATE,
    admission_time TIME,
    employee_no NVARCHAR(100),
    patient_name NVARCHAR(200) NOT NULL,
    address NVARCHAR(MAX),
    age INT,
    sex NVARCHAR(20),
    diagnosis NVARCHAR(MAX),
    ent_pvt NVARCHAR(100),
    nonent NVARCHAR(100),
    dod DATE,
    staff_nurse NVARCHAR(200),
    doctor_name NVARCHAR(200),
    remarks NVARCHAR(MAX),
    created_at DATETIME2 DEFAULT GETDATE(),
    updated_at DATETIME2 DEFAULT GETDATE()
);

CREATE INDEX idx_admission_date ON indoor_records(admission_date);
CREATE INDEX idx_patient_name ON indoor_records(patient_name);
*/
?>