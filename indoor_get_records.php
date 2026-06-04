<?php
declare(strict_types=1);
ob_start();

require_once __DIR__ . '/config/database.php';

try {
    $dateFilter = clean_value($_GET['date'] ?? '');
    $startDate = clean_value($_GET['start_date'] ?? '');
    $endDate = clean_value($_GET['end_date'] ?? '');
    $search = clean_value($_GET['search'] ?? '');
    $exportCsv = ($_GET['export'] ?? '') === 'csv';
    $statsOnly = ($_GET['stats'] ?? '') === '1';

    $where = [];
    $params = [];

    if ($dateFilter !== '') {
        $where[] = 'admission_date = :date_filter';
        $params[':date_filter'] = $dateFilter;
    }

    if ($startDate !== '' && $endDate !== '') {
        $where[] = 'admission_date BETWEEN :start_date AND :end_date';
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;
    }

    if ($search !== '') {
        $where[] = '(patient_name LIKE :search OR employee_no LIKE :search OR diagnosis LIKE :search OR doctor_name LIKE :search)';
        $params[':search'] = '%' . $search . '%';
    }

    // Get stats
    $stats = [
        'today' => (int) $pdo->query("SELECT COUNT(*) FROM indoor_records WHERE admission_date = CURDATE()")->fetchColumn(),
        'month' => (int) $pdo->query("SELECT COUNT(*) FROM indoor_records WHERE YEAR(admission_date) = YEAR(CURDATE()) AND MONTH(admission_date) = MONTH(CURDATE())")->fetchColumn(),
        'total' => (int) $pdo->query("SELECT COUNT(*) FROM indoor_records")->fetchColumn(),
    ];

    // If only stats requested, return just stats
    if ($statsOnly) {
        json_response(true, 'Stats fetched successfully.', 0, [
            'data' => [
                'records' => [],
                'stats' => $stats,
            ],
        ]);
        exit;
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $stmt = $pdo->prepare("
        SELECT *
        FROM indoor_records
        {$whereSql}
        ORDER BY admission_date DESC, id DESC
    ");
    $stmt->execute($params);
    $records = $stmt->fetchAll();

    if ($exportCsv) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="indoor_records_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'ID', 'S.No', 'Yearly No', 'Monthly No', 'Admission Date', 'Admission Time',
            'Employee No', 'Patient Name', 'Address', 'Age', 'Sex', 'Diagnosis',
            'ENT/PVT', 'D.O.D.', 'Staff Nurse', 'Doctor Name', 'Remarks', 'Created At', 'Updated At'
        ]);

        foreach ($records as $record) {
            fputcsv($output, [
                $record['id'], $record['sno'], $record['yearly_no'], $record['monthly_no'],
                $record['admission_date'], $record['admission_time'], $record['employee_no'],
                $record['patient_name'], $record['address'], $record['age'], $record['sex'],
                $record['diagnosis'], $record['ent_pvt'], $record['dod'],
                $record['staff_nurse'], $record['doctor_name'], $record['remarks'],
                $record['created_at'], $record['updated_at']
            ]);
        }
        fclose($output);
        exit;
    }

    json_response(true, 'Records fetched successfully.', count($records), [
        'data' => [
            'records' => $records,
            'stats' => $stats,
        ],
    ]);
} catch (Throwable $exception) {
    json_response(false, $exception->getMessage(), 0, ['data' => ['records' => [], 'stats' => []]]);
}