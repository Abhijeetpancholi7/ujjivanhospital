<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
$pageTitle = 'Indoor Patient Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<main class="page-shell">
    <section class="hospital-card">
        <header class="hospital-header">
            <div class="ntpc-logo">
                <img src="assets/images/NTPC_Logo.svg.png" alt="NTPC Logo" class="logo-img" onerror="this.style.display='none'; this.parentNode.querySelector('.logo-fallback').style.display='flex';">
            </div>
            <div class="hospital-title">
                <h1>UJJIVAN HOSPITAL</h1>
                <h2>NTPC DADRI - INDOOR PATIENT REGISTER</h2>
            </div>
            <div class="clock-box">
                <i class="fa-regular fa-clock"></i>
                <span id="liveTime" class="time">--:--:--</span>
                <small id="liveDate">--</small>
            </div>
        </header>

        <div class="p-3 p-lg-4">
            <div class="stats-grid" id="statsGrid">
                <div class="stats-card"><div class="label">Today Admissions</div><div class="value" id="statToday">0</div></div>
                <div class="stats-card"><div class="label">This Month</div><div class="value" id="statMonth">0</div></div>
                <div class="stats-card"><div class="label">Total Records</div><div class="value" id="statTotal">0</div></div>
                <div class="stats-card"><div class="label">Rows in Draft</div><div class="value" id="rowCount">0</div></div>
            </div>

            <div class="table-zone">
                <table class="register-table" id="registerTable">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Yearly No.</th>
                            <th>Monthly No.</th>
                            <th>Admission Date</th>
                            <th>Admission Time</th>
                            <th>Employee No.</th>
                            <th>Patient Name</th>
                            <th>Address</th>
                            <th>Age</th>
                            <th>Sex</th>
                            <th>Diagnosis/Complaints</th>
                            <th>ENT/PVT</th>
                            <th>D.O.D.</th>
                            <th>Staff Nurse</th>
                            <th>Doctor Name</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>

            <div class="toolbar mt-3">
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn-med btn-primary-med" type="button" onclick="addNewRow()"><i class="fa-solid fa-plus"></i> Add Row</button>
                    <button class="btn-med btn-success-med" type="button" onclick="saveRows()"><i class="fa-solid fa-floppy-disk"></i> Save to Database</button>
                    <button class="btn-med btn-primary-med" type="button" onclick="openRecordsModal()"><i class="fa-solid fa-table-list"></i> View Records</button>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn-med btn-warning-med" type="button" onclick="resetDraft()"><i class="fa-solid fa-rotate-left"></i> Reset Table</button>
                    <button class="btn-med btn-muted-med" type="button" onclick="printRecords()"><i class="fa-solid fa-print"></i> Print Register</button>
                </div>
            </div>
        </div>
    </section>
</main>

<div class="modal fade records-modal" id="recordsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-database"></i> Indoor Patient Records</h5>
                <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="filter-bar">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <input class="form-control" type="date" id="filterDate" aria-label="Filter by date">
                        <input class="form-control" type="date" id="startDate" aria-label="Start date">
                        <input class="form-control" type="date" id="endDate" aria-label="End date">
                        <input class="form-control" type="search" id="searchInput" placeholder="Search patient, employee no, doctor">
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn-med btn-primary-med" type="button" onclick="loadRecords()"><i class="fa-solid fa-filter"></i> Apply</button>
                        <button class="btn-med btn-muted-med" type="button" onclick="clearFilters()">Clear</button>
                        <button class="btn-med btn-success-med" type="button" onclick="exportCsv()"><i class="fa-solid fa-file-csv"></i> Export CSV</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="recordsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Yearly No</th>
                                <th>Patient</th>
                                <th>Age/Sex</th>
                                <th>Diagnosis</th>
                                <th>Doctor</th>
                                <th>Employee No</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recordsTableBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-med btn-muted-med" type="button" data-bs-dismiss="modal">Close</button>
                <button class="btn-med btn-primary-med" type="button" onclick="printRecords()"><i class="fa-solid fa-print"></i> Print</button>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3 no-print">
    <div id="appToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-bold" id="toastMessage"></div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<div id="loadingOverlay">
    <div class="spinner-panel">
        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
        <span>Processing...</span>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const draftKey = 'indoor_register_draft_v2';
let recordsModal;
let recordsDataTable = null;

function esc(value) {
    return String(value ?? '').replace(/[&<>"']/g, ch => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[ch]));
}

function currentDate() {
    return new Date().toISOString().slice(0, 10);
}

function currentTime() {
    const now = new Date();
    return `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
}

function yearlyNumber(rowNumber) {
    const yr = new Date().getFullYear();
    return `${yr}/${String(rowNumber).padStart(4, '0')}`;
}

function monthlyNumber(rowNumber) {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const mon = months[new Date().getMonth()];
    return `${mon}-${String(rowNumber).padStart(3, '0')}`;
}

function updateClock() {
    const now = new Date();
    document.getElementById('liveTime').textContent = now.toLocaleTimeString('en-IN');
    document.getElementById('liveDate').textContent = now.toLocaleDateString('en-IN', {
        weekday: 'short', day: '2-digit', month: 'short', year: 'numeric'
    });
}

function showLoader(show) {
    document.getElementById('loadingOverlay').style.display = show ? 'grid' : 'none';
}

function showToast(message, success = true) {
    const toast = document.getElementById('appToast');
    toast.className = `toast align-items-center border-0 text-bg-${success ? 'success' : 'danger'}`;
    document.getElementById('toastMessage').textContent = message;
    bootstrap.Toast.getOrCreateInstance(toast, { delay: 3500 }).show();
}

function makeInput(type, placeholder, value = '') {
    return `<input type="${type}" placeholder="${esc(placeholder)}" value="${esc(value)}">`;
}

function makeTextarea(placeholder, value = '') {
    return `<textarea placeholder="${esc(placeholder)}">${esc(value)}</textarea>`;
}

function makeSelect(options, value = '') {
    return `<select>${options.map(option => {
        const label = option || 'Select';
        const selected = option === value ? 'selected' : '';
        return `<option value="${esc(option)}" ${selected}>${esc(label)}</option>`;
    }).join('')}</select>`;
}

function addNewRow(data = {}) {
    const tbody = document.getElementById('tableBody');
    const rowNo = tbody.children.length + 1;
    const tr = document.createElement('tr');

    tr.innerHTML = `
        <td>${makeInput('text', 'S.No', data.sno || rowNo)}</td>
        <td>${makeInput('text', 'Yearly No', data.yearly_no || yearlyNumber(rowNo))}</td>
        <td>${makeInput('text', 'Monthly No', data.monthly_no || monthlyNumber(rowNo))}</td>
        <td>${makeInput('date', '', data.admission_date || currentDate())}</td>
        <td>${makeInput('time', '', data.admission_time || currentTime())}</td>
        <td>${makeInput('text', 'Employee No', data.employee_no || '')}</td>
        <td>${makeInput('text', 'Patient Name', data.patient_name || '')}</td>
        <td>${makeTextarea('Complete address', data.address || '')}</td>
        <td>${makeInput('number', 'Age', data.age || '')}</td>
        <td>${makeSelect(['', 'Male', 'Female', 'Other', 'Transgender'], data.sex || '')}</td>
        <td>${makeTextarea('Diagnosis/Complaints', data.diagnosis || '')}</td>
        <td>${makeInput('text', 'ENT/PVT', data.ent_pvt || '')}</td>
        <td>${makeInput('date', '', data.dod || '')}</td>
        <td>${makeInput('text', 'Staff Nurse', data.staff_nurse || '')}</td>
        <td>${makeInput('text', 'Doctor Name', data.doctor_name || '')}</td>
        <td>${makeInput('text', 'Remarks', data.remarks || '')}</td>
        <td><button class="action-btn" type="button" onclick="deleteDraftRow(this)"><i class="fa-solid fa-trash"></i></button></td>
    `;
    tbody.appendChild(tr);
    updateRows();
    saveDraft();
}

function updateRows() {
    document.querySelectorAll('#tableBody tr').forEach((tr, index) => {
        const sno = tr.querySelector('td:first-child input');
        if (sno) sno.value = String(index + 1);
    });
    document.getElementById('rowCount').textContent = document.querySelectorAll('#tableBody tr').length;
}

function deleteDraftRow(button) {
    if (!confirm('Delete this draft row?')) return;
    button.closest('tr').remove();
    updateRows();
    saveDraft();
}

function collectRows() {
    return [...document.querySelectorAll('#tableBody tr')].map(row => {
        const cells = row.querySelectorAll('td');
        return {
            sno: cells[0].querySelector('input').value,
            yearly_no: cells[1].querySelector('input').value,
            monthly_no: cells[2].querySelector('input').value,
            admission_date: cells[3].querySelector('input').value,
            admission_time: cells[4].querySelector('input').value,
            employee_no: cells[5].querySelector('input').value,
            patient_name: cells[6].querySelector('input').value,
            address: cells[7].querySelector('textarea').value,
            age: cells[8].querySelector('input').value,
            sex: cells[9].querySelector('select').value,
            diagnosis: cells[10].querySelector('textarea').value,
            ent_pvt: cells[11].querySelector('input').value,
            dod: cells[12].querySelector('input').value,
            staff_nurse: cells[13].querySelector('input').value,
            doctor_name: cells[14].querySelector('input').value,
            remarks: cells[15].querySelector('input').value
        };
    });
}

function validateRows(rows) {
    for (const [index, row] of rows.entries()) {
        if (!String(row.patient_name).trim() && !String(row.employee_no).trim()) {
            return `Row ${index + 1}: patient name or employee number is required.`;
        }
        if (row.age !== '' && (!Number.isFinite(Number(row.age)) || Number(row.age) < 0 || Number(row.age) > 120)) {
            return `Row ${index + 1}: age must be between 0 and 120.`;
        }
    }
    return '';
}

function saveDraft() {
    localStorage.setItem(draftKey, JSON.stringify(collectRows()));
}

function loadDraft() {
    const saved = localStorage.getItem(draftKey);
    document.getElementById('tableBody').innerHTML = '';
    if (saved) {
        try {
            JSON.parse(saved).forEach(row => addNewRow(row));
        } catch (error) {
            localStorage.removeItem(draftKey);
        }
    }
    if (!document.querySelectorAll('#tableBody tr').length) {
        addNewRow();
        addNewRow();
    }
    updateRows();
}

function resetDraft() {
    if (!confirm('Clear all unsaved rows?')) return;
    localStorage.removeItem(draftKey);
    document.getElementById('tableBody').innerHTML = '';
    addNewRow();
    addNewRow();
    showToast('Draft table reset.');
}

async function saveRows() {
    const rows = collectRows().filter(row => Object.values(row).some(value => String(value).trim() !== ''));
    const validationError = validateRows(rows);
    if (!rows.length) return showToast('Please enter at least one record.', false);
    if (validationError) return showToast(validationError, false);

    showLoader(true);
    try {
        const response = await fetch('indoor_save_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify({ records: rows, csrf_token: csrfToken })
        });
        const result = await response.json();
        showToast(result.message || 'Save completed.', result.success);
        if (result.success) {
            localStorage.removeItem(draftKey);
            document.getElementById('tableBody').innerHTML = '';
            addNewRow();
            addNewRow();
            loadStats();
        }
    } catch (error) {
        showToast(`Save failed: ${error.message}`, false);
    } finally {
        showLoader(false);
    }
}

function recordParams() {
    const params = new URLSearchParams();
    ['filterDate', 'startDate', 'endDate', 'searchInput'].forEach(id => {
        const value = document.getElementById(id).value.trim();
        if (value) {
            params.set({
                filterDate: 'date',
                startDate: 'start_date',
                endDate: 'end_date',
                searchInput: 'search'
            }[id], value);
        }
    });
    return params;
}

async function loadRecords() {
    showLoader(true);
    try {
        const response = await fetch(`indoor_get_records.php?${recordParams().toString()}`);
        const result = await response.json();
        if (!result.success) return showToast(result.message || 'Unable to load records.', false);
        renderRecords(result.data.records || []);
        loadStats(result.data.stats || null);
    } catch (error) {
        showToast(`Load failed: ${error.message}`, false);
    } finally {
        showLoader(false);
    }
}

function renderRecords(records) {
    if (recordsDataTable) {
        recordsDataTable.destroy();
        recordsDataTable = null;
    }

    document.getElementById('recordsTableBody').innerHTML = records.map(record => `
        <tr>
            <td>${esc(record.id)}</td>
            <td>${esc(record.admission_date || '-')} ${esc(record.admission_time || '')}</td>
            <td>${esc(record.yearly_no || '-')}</td>
            <td><strong>${esc(record.patient_name || '-')}</strong></td>
            <td>${esc(record.age || '-')}/${esc(record.sex || '-')}</td>
            <td>${esc(String(record.diagnosis || '').slice(0, 80))}</td>
            <td>${esc(record.doctor_name || '-')}</td>
            <td>${esc(record.employee_no || '-')}</td>
            <td class="text-nowrap">
                <a class="btn btn-sm btn-primary" href="indoor_edit.php?id=${encodeURIComponent(record.id)}"><i class="fa-solid fa-pen"></i></a>
                <button class="btn btn-sm btn-danger" type="button" onclick="deleteRecord(${Number(record.id)})"><i class="fa-solid fa-trash"></i></button>
                <a class="btn btn-sm btn-secondary" href="indoor_print_records.php?id=${encodeURIComponent(record.id)}" target="_blank"><i class="fa-solid fa-print"></i></a>
            </td>
        </tr>
    `).join('');

    recordsDataTable = $('#recordsTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[0, 'desc']],
        destroy: true
    });
}

async function loadStats(stats = null) {
    if (!stats) {
        try {
            const response = await fetch('indoor_get_records.php?stats=1');
            const result = await response.json();
            stats = result.data.stats || {};
        } catch (error) {
            return;
        }
    }
    document.getElementById('statToday').textContent = stats.today || 0;
    document.getElementById('statMonth').textContent = stats.month || 0;
    document.getElementById('statTotal').textContent = stats.total || 0;
}

function openRecordsModal() {
    recordsModal.show();
    loadRecords();
}

function clearFilters() {
    ['filterDate', 'startDate', 'endDate', 'searchInput'].forEach(id => document.getElementById(id).value = '');
    loadRecords();
}

function exportCsv() {
    const params = recordParams();
    params.set('export', 'csv');
    window.location.href = `indoor_get_records.php?${params.toString()}`;
}

function printRecords() {
    const params = recordParams();
    window.open(`indoor_print_records.php${params.toString() ? `?${params.toString()}` : ''}`, '_blank');
}

async function deleteRecord(id) {
    if (!confirm('Delete this record permanently?')) return;
    showLoader(true);
    try {
        const response = await fetch('indoor_delete_record.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify({ id, csrf_token: csrfToken })
        });
        const result = await response.json();
        showToast(result.message || 'Delete completed.', result.success);
        if (result.success) {
            loadRecords();
            loadStats();
        }
    } catch (error) {
        showToast(`Delete failed: ${error.message}`, false);
    } finally {
        showLoader(false);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    recordsModal = new bootstrap.Modal(document.getElementById('recordsModal'));
    updateClock();
    setInterval(updateClock, 1000);
    loadDraft();
    loadStats();

    document.addEventListener('input', event => {
        if (event.target.closest('#tableBody')) saveDraft();
    });

    document.addEventListener('keydown', event => {
        if (event.ctrlKey && event.key.toLowerCase() === 's') {
            event.preventDefault();
            saveRows();
        }
        if (event.ctrlKey && event.shiftKey && event.key.toLowerCase() === 'a') {
            event.preventDefault();
            addNewRow();
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>