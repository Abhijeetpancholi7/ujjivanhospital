<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Only POST requests are allowed.'], 405);
    }

    $row = readJsonBody();
    $id = isset($row['id']) ? (int) $row['id'] : 0;

    if ($id <= 0) {
        jsonResponse(['success' => false, 'message' => 'Valid record id is required.'], 400);
    }

    $admissionDate = normalizeDate($row['admission_date'] ?? '');
    $dod = normalizeDate($row['dod'] ?? '');
    $admissionTime = normalizeTime($row['admission_time'] ?? '');
    $age = trim((string) ($row['age'] ?? ''));

    if ($admissionDate === null || trim((string) ($row['patient_name'] ?? '')) === '' || $age === '' || trim((string) ($row['sex'] ?? '')) === '' || trim((string) ($row['diagnosis_complaints'] ?? '')) === '') {
        jsonResponse(['success' => false, 'message' => 'Date, Patient Name, Age, Sex, and Diagnosis are required.'], 422);
    }

    if (!ctype_digit($age) || (int) $age > 130) {
        jsonResponse(['success' => false, 'message' => 'Age must be a valid number between 0 and 130.'], 422);
    }

    $stmt = getPdo()->prepare('UPDATE indoor_patient_register SET
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
        WHERE id = :id');

    $stmt->execute([
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
        ':id' => $id,
    ]);

    jsonResponse(['success' => true, 'message' => 'Record updated successfully.', 'id' => $id]);
} catch (Throwable $exception) {
    jsonResponse(['success' => false, 'message' => $exception->getMessage()], 500);
}
