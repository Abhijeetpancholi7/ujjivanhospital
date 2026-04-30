<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>UJJIVAN HOSPITAL - Indoor Patient Register</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <main class="register-sheet">
    <section class="top-strip" aria-label="Register header">
      <div class="ntpc-box">
        <div>NTPC</div>
        <div>NTPC</div>
      </div>

      <div class="hospital-name">
        <h1>UJJIVAN HOSPITAL</h1>
        <h2>NTPC DADRI</h2>
      </div>

      <div class="digital-clock no-print" id="digitalClock" aria-live="polite">
        <i class="bi bi-clock"></i>
        <span>--</span>
      </div>
    </section>

    <div class="register-title">INDOOR PATIENT REGISTER</div>

    <div class="toolbar-summary no-print">
      <span><i class="bi bi-database-check"></i> Saved records: <strong id="recordCount">0</strong></span>
      <span><i class="bi bi-clock-history"></i> Status: <strong id="saveStatus">Ready</strong></span>
    </div>

    <div class="table-container">
      <table class="register-table" id="registerTable">
        <thead>
          <tr>
            <th class="w-sno">S.No.</th>
            <th class="w-yearly">Yearly<br>No.</th>
            <th class="w-monthly">Monthly<br>No.</th>
            <th class="w-date">Date</th>
            <th class="w-time">Time</th>
            <th class="w-emp">Employee<br>No.</th>
            <th class="w-name">Name</th>
            <th class="w-address">Address</th>
            <th class="w-age">Age</th>
            <th class="w-sex">Sex</th>
            <th class="w-diagnosis">Diagnosis Complaints</th>
            <th class="w-ent">ENT/PVT</th>
            <th class="w-nonent">NONENT</th>
            <th class="w-dod">D.O.D.</th>
            <th class="w-staffnurse">Staff Nurse</th>
            <th class="w-dr">Dr. Name</th>
            <th class="w-remarks">Remarks</th>
            <th class="w-actions no-print">Action</th>
          </tr>
        </thead>
        <tbody id="tableBody"></tbody>
      </table>
    </div>

    <div class="button-container no-print">
      <button type="button" class="btn btn-primary" id="addRowBtn">
        <i class="bi bi-plus-circle"></i> Add Row
      </button>
      <button type="button" class="btn btn-info" id="viewRowsBtn">
        <i class="bi bi-eye"></i> View
      </button>
      <button type="button" class="btn btn-submit" id="saveRowsBtn">
        <i class="bi bi-save2"></i> Save All
      </button>
      <button type="button" class="btn btn-print-save" id="savePrintBtn">
        <span class="btn-emoji" aria-hidden="true">🖨️</span> Save &amp; Print
      </button>
      <button type="button" class="btn btn-success" id="printBtn">
        <i class="bi bi-printer"></i> Print
      </button>
    </div>

    <section class="records-section no-print d-none" id="recordsSection" aria-label="Saved records">
      <div class="records-header">
        <div>
          <h3>Saved Indoor Patient Records</h3>
          <p>Search, edit, or delete date-wise admission records.</p>
        </div>
      </div>

      <form class="filter-panel" id="filterForm">
        <div class="filter-field">
          <label for="filterDate">Date</label>
          <input type="date" class="form-control" id="filterDate" name="date">
        </div>
        <div class="filter-field">
          <label for="filterFromDate">From Date</label>
          <input type="date" class="form-control" id="filterFromDate" name="from_date">
        </div>
        <div class="filter-field">
          <label for="filterToDate">To Date</label>
          <input type="date" class="form-control" id="filterToDate" name="to_date">
        </div>
        <div class="filter-field">
          <label for="filterPatientName">Patient Name</label>
          <input type="text" class="form-control" id="filterPatientName" name="patient_name" placeholder="Patient name">
        </div>
        <div class="filter-field filter-field-wide">
          <label for="filterKeyword">Employee No / Data Search</label>
          <input type="text" class="form-control" id="filterKeyword" name="keyword" placeholder="Employee no, diagnosis, doctor, remarks...">
        </div>
        <div class="filter-actions">
          <button type="submit" class="btn btn-submit">
            <i class="bi bi-search"></i> Search
          </button>
          <button type="button" class="btn btn-secondary" id="resetFiltersBtn">
            <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
          </button>
        </div>
      </form>

      <div class="records-table-wrap">
        <table class="table table-sm table-hover align-middle records-table">
          <thead>
            <tr>
              <th>Yearly No.</th>
              <th>Monthly No.</th>
              <th>Date</th>
              <th>Time</th>
              <th>Employee No.</th>
              <th>Name</th>
              <th>Age</th>
              <th>Sex</th>
              <th>Diagnosis</th>
              <th>Dr. Name</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="recordsTableBody">
            <tr>
              <td colspan="11" class="text-center text-muted py-4">Click View to load saved records.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <div class="toast-container position-fixed top-0 end-0 p-3 no-print">
    <div id="appToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="toastMessage"></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>
