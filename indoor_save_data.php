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

    $records = $input['records'] ?? $input;
    if (!is_array($records)) {
        json_response(false, 'JSON must contain a records array.', 0);
    }

    $insertSql = "
        INSERT INTO indoor_records (
            sno, yearly_no, monthly_no, admission_date, admission_time,
            employee_no, patient_name, address, age, sex, diagnosis,
            ent_pvt, nonent, dod, staff_nurse, doctor_name, remarks
        ) VALUES (
            :sno, :yearly_no, :monthly_no, :admission_date, :admission_time,
            :employee_no, :patient_name, :address, :age, :sex, :diagnosis,
            :ent_pvt, :nonent, :dod, :staff_nurse, :doctor_name, :remarks
        )
    ";

    $stmt = $pdo->prepare($insertSql);
    $saved = 0;

    $pdo->beginTransaction();

    foreach ($records as $index => $record) {
        if (!is_array($record)) {
            continue;
        }

        $patientName = clean_value($record['patient_name'] ?? '');
        $employeeNo = clean_value($record['employee_no'] ?? '');
        $age = clean_value($record['age'] ?? '');

        if ($patientName === '' && $employeeNo === '') {
            throw new InvalidArgumentException('Row ' . ($index + 1) . ': patient name or employee number is required.');
        }

        if ($age !== '' && (!is_numeric($age) || (int) $age < 0 || (int) $age > 120)) {
            throw new InvalidArgumentException('Row ' . ($index + 1) . ': age must be between 0 and 120.');
        }

        $admissionDate = clean_value($record['admission_date'] ?? '');
        $dod = clean_value($record['dod'] ?? '');

        $stmt->execute([
            ':sno' => clean_value($record['sno'] ?? ''),
            ':yearly_no' => clean_value($record['yearly_no'] ?? ''),
            ':monthly_no' => clean_value($record['monthly_no'] ?? ''),
            ':admission_date' => $admissionDate === '' ? null : $admissionDate,
            ':admission_time' => clean_value($record['admission_time'] ?? ''),
            ':employee_no' => $employeeNo,
            ':patient_name' => $patientName,
            ':address' => clean_value($record['address'] ?? ''),
            ':age' => $age === '' ? null : (int) $age,
            ':sex' => clean_value($record['sex'] ?? ''),
            ':diagnosis' => clean_value($record['diagnosis'] ?? ''),
            ':ent_pvt' => clean_value($record['ent_pvt'] ?? ''),
            ':nonent' => clean_value($record['nonent'] ?? ''),
            ':dod' => $dod === '' ? null : $dod,
            ':staff_nurse' => clean_value($record['staff_nurse'] ?? ''),
            ':doctor_name' => clean_value($record['doctor_name'] ?? ''),
            ':remarks' => clean_value($record['remarks'] ?? ''),
        ]);

        $saved++;
    }

    if ($saved === 0) {
        throw new InvalidArgumentException('No valid records were submitted.');
    }

    $pdo->commit();
    json_response(true, "Successfully saved {$saved} record(s).", $saved, ['data' => ['saved' => $saved]]);
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(false, $exception->getMessage(), 0);
}