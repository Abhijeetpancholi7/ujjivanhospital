<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$pageTitle = 'Print Indoor Register';
$recordId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$dateFilter = clean_value($_GET['date'] ?? '');
$startDate = clean_value($_GET['start_date'] ?? '');
$endDate = clean_value($_GET['end_date'] ?? '');

$where = [];
$params = [];

if ($recordId) {
    $where[] = 'id = :id';
    $params[':id'] = $recordId;
}

if ($dateFilter !== '') {
    $where[] = 'admission_date = :date_filter';
    $params[':date_filter'] = $dateFilter;
}

if ($startDate !== '' && $endDate !== '') {
    $where[] = 'admission_date BETWEEN :start_date AND :end_date';
    $params[':start_date'] = $startDate;
    $params[':end_date'] = $endDate;
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

$rangeLabel = 'All Records';
if ($recordId) {
    $rangeLabel = 'Record #' . $recordId;
} elseif ($dateFilter !== '') {
    $rangeLabel = $dateFilter;
} elseif ($startDate !== '' && $endDate !== '') {
    $rangeLabel = $startDate . ' to ' . $endDate;
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="page-shell">
    <div class="no-print mb-3 d-flex flex-wrap gap-2">
        <button class="btn-med btn-primary-med" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print / Save PDF</button>
            <a class="btn-med btn-muted-med" href="index.php">Dashboard</a>
    <section class="hospital-card p-3 p-lg-4">
        <div class="print-header text-center">
            <h1 class="fw-black" style="color: #2c3e50;">UJJIVAN HOSPITAL</h1>
            <h2 style="color: #16a085;">NTPC DADRI</h2>
            <h3>INDOOR PATIENT REGISTER</h3>
            <p>Printed on: <?php echo e(date('d/m/Y h:i A')); ?></p>
        </div>

        <div class="print-info d-flex justify-content-between gap-3 p-2 bg-light">
            <div><strong>Total Records:</strong> <?php echo count($records); ?></div>
            <div><strong>Selection:</strong> <?php echo e($rangeLabel); ?></div>
        </div>

        <div class="table-responsive">
            <table class="print-table">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Yearly No</th>
                        <th>Monthly No</th>
                        <th>Admission Date</th>
                        <th>Time</th>
                        <th>Emp No</th>
                        <th>Patient</th>
                        <th>Address</th>
                        <th>Age/Sex</th>
                        <th>Diagnosis</th>
                        <th>ENT/PVT</th>
                        <th>NONENT</th>
                        <th>D.O.D.</th>
                        <th>Staff Nurse</th>
                        <th>Doctor</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $index => $record): ?>
                        <tr>
                            <td><?php echo e($record['sno'] ?: (string) ($index + 1)); ?></td>
                            <td><?php echo e($record['yearly_no'] ?: '-'); ?></td>
                            <td><?php echo e($record['monthly_no'] ?: '-'); ?></td>
                            <td><?php echo e($record['admission_date'] ? date('d/m/Y', strtotime($record['admission_date'])) : '-'); ?></td>
                            <td><?php echo e($record['admission_time'] ?: '-'); ?></td>
                            <td><?php echo e($record['employee_no'] ?: '-'); ?></td>
                            <td><?php echo e($record['patient_name'] ?: '-'); ?></td>
                            <td><?php echo e($record['address'] ?: '-'); ?></td>
                            <td><?php echo e(($record['age'] ?: '-') . '/' . ($record['sex'] ?: '-')); ?></td>
                            <td><?php echo e($record['diagnosis'] ?: '-'); ?></td>
                            <td><?php echo e($record['ent_pvt'] ?: '-'); ?></td>
                            <td><?php echo e($record['nonent'] ?: '-'); ?></td>
                            <td><?php echo e($record['dod'] ? date('d/m/Y', strtotime($record['dod'])) : '-'); ?></td>
                            <td><?php echo e($record['staff_nurse'] ?: '-'); ?></td>
                            <td><?php echo e($record['doctor_name'] ?: '-'); ?></td>
                            <td><?php echo e($record['remarks'] ?: '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$records): ?>
                        <tr><td colspan="16" class="text-center">No records found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-4 small">
            <p class="mb-1">This is a computer-generated document. No signature required.</p>
            <p>UJJIVAN HOSPITAL - Indoor Department</p>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>