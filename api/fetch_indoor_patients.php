<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

try {
    // Only GET requests allowed for fetching
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        jsonResponse(['success' => false, 'message' => 'Only GET requests are allowed.']);
    }

    $where = [];
    $params = [];

    // Filter by specific date
    $date = normalizeDate($_GET['date'] ?? '');
    if ($date !== null) {
        $where[] = 'CAST(admission_date AS DATE) = :date';
        $params[':date'] = $date;
    }

    // Filter by date range
    $fromDate = normalizeDate($_GET['from_date'] ?? '');
    if ($fromDate !== null) {
        $where[] = 'CAST(admission_date AS DATE) >= :from_date';
        $params[':from_date'] = $fromDate;
    }

    $toDate = normalizeDate($_GET['to_date'] ?? '');
    if ($toDate !== null) {
        $where[] = 'CAST(admission_date AS DATE) <= :to_date';
        $params[':to_date'] = $toDate;
    }

    // Filter by patient name
    $patientName = sanitize($_GET['patient_name'] ?? '');
    if ($patientName !== '') {
        $where[] = 'patient_name LIKE :patient_name';
        $params[':patient_name'] = '%' . $patientName . '%';
    }

    // Filter by keyword (searches multiple fields)
    $keyword = sanitize($_GET['keyword'] ?? '');
    if ($keyword !== '') {
        $where[] = '(
            employee_no LIKE :keyword OR 
            sno LIKE :keyword OR 
            yearly_no LIKE :keyword OR 
            monthly_no LIKE :keyword OR 
            address LIKE :keyword OR 
            diagnosis LIKE :keyword OR 
            ent_pvt LIKE :keyword OR 
            nonent LIKE :keyword OR 
            staff_nurse LIKE :keyword OR 
            doctor_name LIKE :keyword OR 
            remarks LIKE :keyword
        )';
        $params[':keyword'] = '%' . $keyword . '%';
    }

    // Build SQL query
    $sql = 'SELECT 
        id, sno, yearly_no, monthly_no, admission_date, admission_time, 
        employee_no, patient_name, address, age, sex, diagnosis, 
        ent_pvt, nonent, dod, staff_nurse, doctor_name, remarks,
        created_at, updated_at 
    FROM indoor_records';

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY admission_date DESC, id DESC';

    // Execute query
    $stmt = getPdo()->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll();

    // Format time fields (SQL Server TIME type)
    foreach ($records as &$record) {
        if ($record['admission_time']) {
            $record['admission_time'] = substr((string)$record['admission_time'], 0, 5);
        }
    }
    unset($record);

    jsonResponse([
        'success' => true,
        'message' => 'Records fetched successfully.',
        'total' => count($records),
        'records' => $records
    ]);

} catch (Throwable $exception) {
    http_response_code(500);
    jsonResponse(['success' => false, 'message' => $exception->getMessage()]);
}
?>