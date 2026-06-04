<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

try {
    // Only POST requests allowed
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        jsonResponse(['success' => false, 'message' => 'Only POST requests are allowed.']);
    }

    // Read JSON body
    $body = readJsonBody();

    // Verify CSRF token if provided
    $csrfToken = $body['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    if ($csrfToken && !verify_csrf($csrfToken)) {
        http_response_code(403);
        jsonResponse(['success' => false, 'message' => 'Invalid security token.']);
    }

    // Extract record ID
    $recordId = isset($body['id']) ? (int)$body['id'] : 0;

    if ($recordId <= 0) {
        http_response_code(400);
        jsonResponse(['success' => false, 'message' => 'Valid record ID is required.']);
    }

    // Normalize date fields
    $admissionDate = normalizeDate($body['admission_date'] ?? '');
    $dod = normalizeDate($body['dod'] ?? '');
    $admissionTime = normalizeTime($body['admission_time'] ?? '');

    // Extract and clean fields
    $patientName = sanitize($body['patient_name'] ?? '');
    $age = sanitize($body['age'] ?? '');
    $sex = sanitize($body['sex'] ?? '');
    $diagnosis = sanitize($body['diagnosis'] ?? '');

    // Validate required fields
    if ($admissionDate === null) {
        http_response_code(422);
        jsonResponse(['success' => false, 'message' => 'Admission date is required and must be a valid date.']);
    }

    if ($patientName === '') {
        http_response_code(422);
        jsonResponse(['success' => false, 'message' => 'Patient name is required.']);
    }

    if ($age === '') {
        http_response_code(422);
        jsonResponse(['success' => false, 'message' => 'Age is required.']);
    }

    if ($sex === '') {
        http_response_code(422);
        jsonResponse(['success' => false, 'message' => 'Sex is required.']);
    }

    if ($diagnosis === '') {
        http_response_code(422);
        jsonResponse(['success' => false, 'message' => 'Diagnosis is required.']);
    }

    // Validate age is numeric and in valid range
    if (!ctype_digit($age) || (int)$age > 150) {
        http_response_code(422);
        jsonResponse(['success' => false, 'message' => 'Age must be a valid number between 0 and 150.']);
    }

    // Check if record exists
    $checkStmt = getPdo()->prepare('SELECT id FROM indoor_records WHERE id = :id');
    $checkStmt->execute([':id' => $recordId]);
    
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        jsonResponse(['success' => false, 'message' => 'Record not found.']);
    }

    // Update the record
    $stmt = getPdo()->prepare('UPDATE indoor_records SET
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
        WHERE id = :id');

    $stmt->execute([
        ':sno' => sanitize($body['sno'] ?? ''),
        ':yearly_no' => sanitize($body['yearly_no'] ?? ''),
        ':monthly_no' => sanitize($body['monthly_no'] ?? ''),
        ':admission_date' => $admissionDate,
        ':admission_time' => $admissionTime,
        ':employee_no' => sanitize($body['employee_no'] ?? ''),
        ':patient_name' => $patientName,
        ':address' => sanitize($body['address'] ?? ''),
        ':age' => (int)$age,
        ':sex' => substr(strtoupper($sex), 0, 20),
        ':diagnosis' => $diagnosis,
        ':ent_pvt' => sanitize($body['ent_pvt'] ?? ''),
        ':nonent' => sanitize($body['nonent'] ?? ''),
        ':dod' => $dod,
        ':staff_nurse' => sanitize($body['staff_nurse'] ?? ''),
        ':doctor_name' => sanitize($body['doctor_name'] ?? ''),
        ':remarks' => sanitize($body['remarks'] ?? ''),
        ':id' => $recordId,
    ]);

    if ($stmt->rowCount() === 0) {
        http_response_code(500);
        jsonResponse(['success' => false, 'message' => 'Failed to update record.']);
    }

    jsonResponse([
        'success' => true,
        'message' => 'Record updated successfully.',
        'id' => $recordId
    ]);

} catch (Throwable $exception) {
    http_response_code(500);
    jsonResponse(['success' => false, 'message' => $exception->getMessage()]);
}
?>