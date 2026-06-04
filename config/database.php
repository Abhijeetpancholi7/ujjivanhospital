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