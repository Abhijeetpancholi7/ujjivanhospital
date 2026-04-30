<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        jsonResponse(['success' => false, 'message' => 'Only GET requests are allowed.'], 405);
    }

    $stmt = getPdo()->query('SELECT id, yearly_no, monthly_no, admission_date, admission_time, employee_no, patient_name, address, age, sex, diagnosis_complaints, ent_pvt, nonent, dod, staff_nurse, doctor_name, remarks FROM indoor_patient_register ORDER BY admission_date DESC, id DESC');
    $records = $stmt->fetchAll();

    foreach ($records as &$record) {
        $record['admission_time'] = $record['admission_time'] ? substr((string) $record['admission_time'], 0, 5) : '';
    }

    jsonResponse([
        'success' => true,
        'message' => 'Records loaded successfully.',
        'records' => $records,
    ]);
} catch (Throwable $exception) {
    jsonResponse(['success' => false, 'message' => $exception->getMessage()], 500);
}
