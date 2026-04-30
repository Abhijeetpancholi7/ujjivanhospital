<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NAME = 'clinic';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

function getPdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function jsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function readJsonBody(): array
{
    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody ?: '', true);

    if (!is_array($data)) {
        jsonResponse(['success' => false, 'message' => 'Invalid JSON request body.'], 400);
    }

    return $data;
}

function normalizeDate(?string $value): ?string
{
    $value = trim((string) $value);

    if ($value === '') {
        return null;
    }

    $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y'];

    foreach ($formats as $format) {
        $date = DateTimeImmutable::createFromFormat($format, $value);
        $errors = DateTimeImmutable::getLastErrors();

        if ($date instanceof DateTimeImmutable && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
            return $date->format('Y-m-d');
        }
    }

    return null;
}

function normalizeTime(?string $value): ?string
{
    $value = trim((string) $value);

    if ($value === '') {
        return null;
    }

    $formats = ['H:i', 'H:i:s', 'h:i A', 'h:i a'];

    foreach ($formats as $format) {
        $time = DateTimeImmutable::createFromFormat($format, $value);
        $errors = DateTimeImmutable::getLastErrors();

        if ($time instanceof DateTimeImmutable && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
            return $time->format('H:i:s');
        }
    }

    return null;
}
