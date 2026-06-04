<?php
declare(strict_types=1);
ob_start();

require_once __DIR__ . '/config/database.php';

function wants_json_response(): bool
{
    return strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
        || strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
}

try {
    $isJson = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
    $input = $isJson ? json_decode(file_get_contents('php://input'), true) : $_POST;
    $input = is_array($input) ? $input : [];

    if (!verify_csrf($input['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null))) {
        throw new InvalidArgumentException('Security token expired. Please refresh the page.');
    }

    $id = isset($input['id']) ? (int) $input['id'] : 0;
    if ($id <= 0) {
        throw new InvalidArgumentException('Record ID is required.');
    }

    $patientName = clean_value($input['patient_name'] ?? '');
    $employeeNo = clean_value($input['employee_no'] ?? '');
    $age = clean_value($input['age'] ?? '');

    if ($patientName === '' && $employeeNo === '') {
        throw new InvalidArgumentException('Patient name or employee number is required.');
    }

    if ($age !== '' && (!is_numeric($age) || (int) $age < 0 || (int) $age > 120)) {
        throw new InvalidArgumentException('Age must be between 0 and 120.');
    }

    $admissionDate = clean_value($input['admission_date'] ?? '');
    $dod = clean_value($input['dod'] ?? '');

    $stmt = $pdo->prepare("
        UPDATE indoor_records SET
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
            remarks = :remarks
        WHERE id = :id
    ");

    $stmt->execute([
        ':sno' => clean_value($input['sno'] ?? ''),
        ':yearly_no' => clean_value($input['yearly_no'] ?? ''),
        ':monthly_no' => clean_value($input['monthly_no'] ?? ''),
        ':admission_date' => $admissionDate === '' ? null : $admissionDate,
        ':admission_time' => clean_value($input['admission_time'] ?? ''),
        ':employee_no' => $employeeNo,
        ':patient_name' => $patientName,
        ':address' => clean_value($input['address'] ?? ''),
        ':age' => $age === '' ? null : (int) $age,
        ':sex' => clean_value($input['sex'] ?? ''),
        ':diagnosis' => clean_value($input['diagnosis'] ?? ''),
        ':ent_pvt' => clean_value($input['ent_pvt'] ?? ''),
        ':nonent' => clean_value($input['nonent'] ?? ''),
        ':dod' => $dod === '' ? null : $dod,
        ':staff_nurse' => clean_value($input['staff_nurse'] ?? ''),
        ':doctor_name' => clean_value($input['doctor_name'] ?? ''),
        ':remarks' => clean_value($input['remarks'] ?? ''),
        ':id' => $id,
    ]);

    if (wants_json_response()) {
        json_response(true, 'Record updated successfully.', 1, ['data' => ['id' => $id]]);
    }

    header('Location: indoor_edit.php?id=' . $id . '&updated=1');
    exit;
} catch (Throwable $exception) {
    if (wants_json_response()) {
        json_response(false, $exception->getMessage(), 0);
    }

    $idPart = isset($id) && $id > 0 ? '?id=' . $id . '&error=' : '?error=';
    header('Location: indoor_edit.php' . $idPart . urlencode($exception->getMessage()));
    exit;
}