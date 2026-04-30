<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Only POST requests are allowed.'], 405);
    }

    $body = readJsonBody();
    $id = isset($body['id']) ? (int) $body['id'] : 0;

    if ($id <= 0) {
        jsonResponse(['success' => false, 'message' => 'Valid record id is required.'], 400);
    }

    $stmt = getPdo()->prepare('DELETE FROM indoor_patient_register WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        jsonResponse(['success' => false, 'message' => 'Record not found.'], 404);
    }

    jsonResponse(['success' => true, 'message' => 'Record deleted successfully.']);
} catch (Throwable $exception) {
    jsonResponse(['success' => false, 'message' => $exception->getMessage()], 500);
}
