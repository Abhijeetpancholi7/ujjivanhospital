<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        jsonResponse(['success' => false, 'message' => 'Only GET requests are allowed.'], 405);
    }

    $where = [];
    $params = [];

    $date = normalizeDate($_GET['date'] ?? '');
    $fromDate = normalizeDate($_GET['from_date'] ?? '');
    $toDate = normalizeDate($_GET['to_date'] ?? '');
    $patientName = trim((string) ($_GET['patient_name'] ?? ''));
    $keyword = trim((string) ($_GET['keyword'] ?? ''));

    if ($date !== null) {
        $where[] = 'admission_date = :date';
        $params[':date'] = $date;
    }

    if ($fromDate !== null) {
        $where[] = 'admission_date >= :from_date';
        $params[':from_date'] = $fromDate;
    }

    if ($toDate !== null) {
        $where[] = 'admission_date <= :to_date';
        $params[':to_date'] = $toDate;
    }

    if ($patientName !== '') {
        $where[] = 'patient_name LIKE :patient_name';
        $params[':patient_name'] = '%' . $patientName . '%';
    }

    if ($keyword !== '') {
        $where[] = '(employee_no LIKE :keyword OR yearly_no LIKE :keyword OR monthly_no LIKE :keyword OR address LIKE :keyword OR diagnosis_complaints LIKE :keyword OR ent_pvt LIKE :keyword OR nonent LIKE :keyword OR staff_nurse LIKE :keyword OR doctor_name LIKE :keyword OR remarks LIKE :keyword)';
        $params[':keyword'] = '%' . $keyword . '%';
    }

    $sql = 'SELECT id, yearly_no, monthly_no, admission_date, admission_time, employee_no, patient_name, address, age, sex, diagnosis_complaints, ent_pvt, nonent, dod, staff_nurse, doctor_name, remarks FROM indoor_patient_register';

    if ($where !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY admission_date DESC, id DESC';

    $stmt = getPdo()->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll();

    foreach ($records as &$record) {
        $record['admission_time'] = $record['admission_time'] ? substr((string) $record['admission_time'], 0, 5) : '';
    }
    unset($record);

    jsonResponse([
        'success' => true,
        'message' => 'Records loaded successfully.',
        'total' => count($records),
        'records' => $records,
    ]);
} catch (Throwable $exception) {
    jsonResponse(['success' => false, 'message' => $exception->getMessage()], 500);
}
