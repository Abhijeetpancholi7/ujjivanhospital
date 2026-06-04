<?php
declare(strict_types=1);
ob_start();

require_once __DIR__ . '/config/database.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        json_response(false, 'Invalid JSON request.', 0);
    }

    if (!verify_csrf($input['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null))) {
        json_response(false, 'Security token expired. Please refresh the page.', 0);
    }

    $id = isset($input['id']) ? (int) $input['id'] : 0;
    if ($id <= 0) {
        json_response(false, 'Record ID is required.', 0);
    }

    $stmt = $pdo->prepare('DELETE FROM indoor_records WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        json_response(false, 'Record not found or already deleted.', 0);
    }

    json_response(true, 'Record deleted successfully.', 1, ['data' => ['id' => $id]]);
} catch (Throwable $exception) {
    json_response(false, $exception->getMessage(), 0);
}