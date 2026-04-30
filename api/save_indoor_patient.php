<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Only POST requests are allowed.'], 405);
    }

    $body = readJsonBody();
    $rows = $body['patients'] ?? null;

    if (!is_array($rows)) {
        jsonResponse(['success' => false, 'message' => 'Patients payload is required.'], 400);
    }

    $pdo = getPdo();
    $pdo->beginTransaction();

    $insertSql = 'INSERT INTO indoor_patient_register
        (yearly_no, monthly_no, admission_date, admission_time, employee_no, patient_name, address, age, sex, diagnosis_complaints, ent_pvt, nonent, dod, staff_nurse, doctor_name, remarks)
        VALUES
        (:yearly_no, :monthly_no, :admission_date, :admission_time, :employee_no, :patient_name, :address, :age, :sex, :diagnosis_complaints, :ent_pvt, :nonent, :dod, :staff_nurse, :doctor_name, :remarks)';

    $updateSql = 'UPDATE indoor_patient_register SET
        yearly_no = :yearly_no,
        monthly_no = :monthly_no,
        admission_date = :admission_date,
        admission_time = :admission_time,
        employee_no = :employee_no,
        patient_name = :patient_name,
        address = :address,
        age = :age,
        sex = :sex,
        diagnosis_complaints = :diagnosis_complaints,
        ent_pvt = :ent_pvt,
        nonent = :nonent,
        dod = :dod,
        staff_nurse = :staff_nurse,
        doctor_name = :doctor_name,
        remarks = :remarks
        WHERE id = :id';

    $insertStmt = $pdo->prepare($insertSql);
    $updateStmt = $pdo->prepare($updateSql);
    $savedIds = [];

    foreach ($rows as $index => $row) {
        if (!is_array($row)) {
            throw new InvalidArgumentException('Invalid row at position ' . ($index + 1) . '.');
        }

        $params = buildPatientParams($row, $index);
        $id = isset($row['id']) ? (int) $row['id'] : 0;

        if ($id > 0) {
            $updateStmt->execute($params + [':id' => $id]);
            $savedIds[] = $id;
        } else {
            $insertStmt->execute($params);
            $savedIds[] = (int) $pdo->lastInsertId();
        }
    }

    $pdo->commit();

    jsonResponse([
        'success' => true,
        'message' => count($savedIds) . ' record(s) saved successfully.',
        'ids' => $savedIds,
    ]);
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    jsonResponse(['success' => false, 'message' => $exception->getMessage()], 500);
}

function buildPatientParams(array $row, int $index): array
{
    $admissionDate = normalizeDate($row['admission_date'] ?? '');
    $dod = normalizeDate($row['dod'] ?? '');
    $admissionTime = normalizeTime($row['admission_time'] ?? '');
    $age = trim((string) ($row['age'] ?? ''));

    if ($admissionDate === null || trim((string) ($row['patient_name'] ?? '')) === '' || $age === '' || trim((string) ($row['sex'] ?? '')) === '' || trim((string) ($row['diagnosis_complaints'] ?? '')) === '') {
        throw new InvalidArgumentException('Required fields missing in row ' . ($index + 1) . '. Date, Patient Name, Age, Sex, and Diagnosis are required.');
    }

    if (!ctype_digit($age) || (int) $age > 130) {
        throw new InvalidArgumentException('Age must be a valid number between 0 and 130 in row ' . ($index + 1) . '.');
    }

    return [
        ':yearly_no' => trim((string) ($row['yearly_no'] ?? '')),
        ':monthly_no' => trim((string) ($row['monthly_no'] ?? '')),
        ':admission_date' => $admissionDate,
        ':admission_time' => $admissionTime,
        ':employee_no' => trim((string) ($row['employee_no'] ?? '')),
        ':patient_name' => trim((string) ($row['patient_name'] ?? '')),
        ':address' => trim((string) ($row['address'] ?? '')),
        ':age' => (int) $age,
        ':sex' => strtoupper(substr(trim((string) ($row['sex'] ?? '')), 0, 10)),
        ':diagnosis_complaints' => trim((string) ($row['diagnosis_complaints'] ?? '')),
        ':ent_pvt' => trim((string) ($row['ent_pvt'] ?? '')),
        ':nonent' => trim((string) ($row['nonent'] ?? '')),
        ':dod' => $dod,
        ':staff_nurse' => trim((string) ($row['staff_nurse'] ?? '')),
        ':doctor_name' => trim((string) ($row['doctor_name'] ?? '')),
        ':remarks' => trim((string) ($row['remarks'] ?? '')),
    ];
}
