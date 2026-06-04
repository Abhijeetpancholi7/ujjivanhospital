<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$pageTitle = 'Edit Indoor Record';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$record = null;
$error = clean_value($_GET['error'] ?? '');
$updated = isset($_GET['updated']);

if ($id) {
    $stmt = $pdo->prepare('SELECT TOP 1 * FROM indoor_records WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $record = $stmt->fetch();
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="page-shell">
    <section class="hospital-card">
        <header class="hospital-header">
            <div class="ntpc-logo"><div>NTPC<br>DADRI</div></div>
            <div class="hospital-title">
                <h1>UJJIVAN HOSPITAL</h1>
                <h2>Edit Indoor Patient Record</h2>
            </div>
            <a class="btn-med btn-primary-med no-print" href="index.php"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
        </header>

        <div class="p-3 p-lg-4">
            <?php if ($updated): ?>
                <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Record updated successfully.</div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo e($error); ?></div>
            <?php endif; ?>

            <?php if (!$record): ?>
                <div class="alert alert-warning">Record not found.</div>
            <?php else: ?>
                <form class="edit-card p-3 p-lg-4" method="post" action="indoor_update_record.php" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                    <input type="hidden" name="id" value="<?php echo (int) $record['id']; ?>">

                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label fw-bold">S.No</label>
                            <input class="form-control" type="text" name="sno" value="<?php echo e($record['sno']); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Yearly No</label>
                            <input class="form-control" type="text" name="yearly_no" value="<?php echo e($record['yearly_no']); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Monthly No</label>
                            <input class="form-control" type="text" name="monthly_no" value="<?php echo e($record['monthly_no']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Admission Date</label>
                            <input class="form-control" type="date" name="admission_date" value="<?php echo e($record['admission_date']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Admission Time</label>
                            <input class="form-control" type="time" name="admission_time" value="<?php echo e($record['admission_time']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Employee No.</label>
                            <input class="form-control" type="text" name="employee_no" value="<?php echo e($record['employee_no']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Patient Name</label>
                            <input class="form-control" type="text" name="patient_name" value="<?php echo e($record['patient_name']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Age</label>
                            <input class="form-control" type="number" min="0" max="120" name="age" value="<?php echo e($record['age']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Sex</label>
                            <select class="form-select" name="sex">
                                <?php foreach (['', 'Male', 'Female', 'Other', 'Transgender'] as $sex): ?>
                                    <option value="<?php echo e($sex); ?>" <?php echo $record['sex'] === $sex ? 'selected' : ''; ?>><?php echo e($sex ?: 'Select'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">D.O.D.</label>
                            <input class="form-control" type="date" name="dod" value="<?php echo e($record['dod']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Staff Nurse</label>
                            <input class="form-control" type="text" name="staff_nurse" value="<?php echo e($record['staff_nurse']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Doctor Name</label>
                            <input class="form-control" type="text" name="doctor_name" value="<?php echo e($record['doctor_name']); ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Address</label>
                            <textarea class="form-control" name="address" rows="2"><?php echo e($record['address']); ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Diagnosis/Complaints</label>
                            <textarea class="form-control" name="diagnosis" rows="4"><?php echo e($record['diagnosis']); ?></textarea>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">ENT/PVT</label>
                            <input class="form-control" type="text" name="ent_pvt" value="<?php echo e($record['ent_pvt']); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">NONENT</label>
                            <input class="form-control" type="text" name="nonent" value="<?php echo e($record['nonent']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="4"><?php echo e($record['remarks']); ?></textarea>
                        </div>
                    </div>

                    <div class="toolbar mt-4">
                        <button class="btn-med btn-success-med" type="submit"><i class="fa-solid fa-floppy-disk"></i> Update Record</button>
                        <a class="btn-med btn-primary-med" href="indoor_print_records.php?id=<?php echo (int) $record['id']; ?>" target="_blank"><i class="fa-solid fa-print"></i> Print Individual</a>
                        <a class="btn-med btn-muted-med" href="index.php">Return to Index</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>