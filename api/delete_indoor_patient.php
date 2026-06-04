<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

try {
    // Only POST requests allowed
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        jsonResponse(['success' => false, 'message' => 'Only POST requests are allowed.']);
    }

    // Verify CSRF token
    $body = readJsonBody();
    $csrfToken = $body['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    
    if (!verify_csrf($csrfToken)) {
        http_response_code(403);
        jsonResponse(['success' => false, 'message' => 'Invalid security token.']);
    }

    $id = isset($body['id']) ? (int)$body['id'] : 0;

    if ($id <= 0) {
        http_response_code(400);
        jsonResponse(['success' => false, 'message' => 'Valid record ID is required.']);
    }

    // Check if record exists
    $checkStmt = getPdo()->prepare('SELECT id FROM indoor_records WHERE id = :id');
    $checkStmt->execute([':id' => $id]);
    
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        jsonResponse(['success' => false, 'message' => 'Record not found.']);
    }

    // Delete the record
    $stmt = getPdo()->prepare('DELETE FROM indoor_records WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        http_response_code(500);
        jsonResponse(['success' => false, 'message' => 'Failed to delete record.']);
    }

    jsonResponse(['success' => true, 'message' => 'Record deleted successfully.', 'id' => $id], 200);

} catch (Throwable $exception) {
    http_response_code(500);
    jsonResponse(['success' => false, 'message' => $exception->getMessage()], 500);
}
?>