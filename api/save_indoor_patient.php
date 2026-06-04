<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

try {
    // Only POST requests allowed
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        jsonResponse(['success' => false, 'message' => 'Only POST requests are allowed.']);
    }

    // Read and validate JSON body
    $body = readJsonBody();
    $records = $body['records'] ?? null;

    if (!is_array($records) || count($records) === 0) {
        http_response_code(400);
        jsonResponse(['success' => false, 'message' => 'Records array is required and cannot be empty.']);
    }

    // Verify CSRF token if provided
    $csrfToken = $body['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    if ($csrfToken && !verify_csrf($csrfToken)) {
        http_response_code(403);
        jsonResponse(['success' => false, 'message' => 'Invalid security token.']);
    }

    $pdo = getPdo();
    $pdo->beginTransaction();

    $insertSql = 'INSERT INTO indoor_records (
        sno, yearly_no, monthly_no, admission_date, admission_time,
        employee_no, patient_name, address, age, sex, diagnosis,
        ent_pvt, nonent, dod, staff_nurse, doctor_name, remarks
    ) VALUES (
        :sno, :yearly_no, :monthly_no, :admission_date, :admission_time,
        :employee_no, :patient_name, :address, :age, :sex, :diagnosis,
        :ent_pvt, :nonent, :dod, :staff_nurse, :doctor_name, :remarks
    )';

    $updateSql = 'UPDATE indoor_records SET
        sno = :sno,
        yearly_no = :yearly_no,
        monthly_no = :monthly_no,
        admission_date = :admission_date,
        admission_time = :admission_time,
        employee_no = :employee_no,
        patient_name = :patient_name,
        address = :address,
        age = :age,
        sex = :sex,
        diagnosis = :diagnosis,
        ent_pvt = :ent_pvt,
        nonent = :nonent,
        dod = :dod,
        staff_nurse = :staff_nurse,
        doctor_name = :doctor_name,
        remarks = :remarks,
        updated_at = GETDATE()
        WHERE id = :id';

    $insertStmt = $pdo->prepare($insertSql);
    $updateStmt = $pdo->prepare($updateSql);
    $savedIds = [];
    $savedCount = 0;

    foreach ($records as $index => $record) {
        if (!is_array($record)) {
            throw new InvalidArgumentException('Invalid record format at row ' . ($index + 1) . '.');
        }

        // Build and validate parameters
        $params = buildRecordParams($record, $index);
        $recordId = isset($record['id']) && (int)$record['id'] > 0 ? (int)$record['id'] : 0;

        if ($recordId > 0) {
            // Update existing record
            $params[':id'] = $recordId;
            $updateStmt->execute($params);
            $savedIds[] = $recordId;
        } else {
            // Insert new record
            $insertStmt->execute($params);
            $newId = (int)$pdo->lastInsertId();
            $savedIds[] = $newId;
        }

        $savedCount++;
    }

    $pdo->commit();

    jsonResponse([
        'success' => true,
        'message' => $savedCount . ' record(s) saved successfully.',
        'count' => $savedCount,
        'ids' => $savedIds
    ]);

} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    jsonResponse(['success' => false, 'message' => $exception->getMessage()]);
}

/**
 * Build and validate parameters for record insert/update
 * @param array $record Record data
 * @param int $index Row index (for error messages)
 * @return array Validated parameters
 */
function buildRecordParams(array $record, int $index): array
{
    // Normalize date fields
    $admissionDate = normalizeDate($record['admission_date'] ?? '');
    $dod = normalizeDate($record['dod'] ?? '');
    $admissionTime = normalizeTime($record['admission_time'] ?? '');

    // Extract and clean fields
    $patientName = sanitize($record['patient_name'] ?? '');
    $age = sanitize($record['age'] ?? '');
    $sex = sanitize($record['sex'] ?? '');
    $diagnosis = sanitize($record['diagnosis'] ?? '');

    // Validate required fields
    if ($admissionDate === null) {
        throw new InvalidArgumentException('Row ' . ($index + 1) . ': Admission date is required and must be a valid date.');
    }

    if ($patientName === '') {
        throw new InvalidArgumentException('Row ' . ($index + 1) . ': Patient name is required.');
    }

    if ($age === '') {
        throw new InvalidArgumentException('Row ' . ($index + 1) . ': Age is required.');
    }

    if ($sex === '') {
        throw new InvalidArgumentException('Row ' . ($index + 1) . ': Sex is required.');
    }

    if ($diagnosis === '') {
        throw new InvalidArgumentException('Row ' . ($index + 1) . ': Diagnosis is required.');
    }

    // Validate age is numeric and in valid range
    if (!ctype_digit($age) || (int)$age > 150) {
        throw new InvalidArgumentException('Row ' . ($index + 1) . ': Age must be a valid number between 0 and 150.');
    }

    return [
        ':sno' => sanitize($record['sno'] ?? ''),
        ':yearly_no' => sanitize($record['yearly_no'] ?? ''),
        ':monthly_no' => sanitize($record['monthly_no'] ?? ''),
        ':admission_date' => $admissionDate,
        ':admission_time' => $admissionTime,
        ':employee_no' => sanitize($record['employee_no'] ?? ''),
        ':patient_name' => $patientName,
        ':address' => sanitize($record['address'] ?? ''),
        ':age' => (int)$age,
        ':sex' => substr(strtoupper($sex), 0, 20),
        ':diagnosis' => $diagnosis,
        ':ent_pvt' => sanitize($record['ent_pvt'] ?? ''),
        ':nonent' => sanitize($record['nonent'] ?? ''),
        ':dod' => $dod,
        ':staff_nurse' => sanitize($record['staff_nurse'] ?? ''),
        ':doctor_name' => sanitize($record['doctor_name'] ?? ''),
        ':remarks' => sanitize($record['remarks'] ?? ''),
    ];
}
?>