const totalDefaultRows = 2;

const fields = [
  { key: "yearly_no", type: "text", placeholder: "e.g., 2026-01" },
  { key: "monthly_no", type: "text", placeholder: "e.g., 12" },
  { key: "admission_date", type: "date", placeholder: "Date", required: true },
  { key: "admission_time", type: "time", placeholder: "Time" },
  { key: "employee_no", type: "text", placeholder: "Emp ID" },
  { key: "patient_name", type: "text", placeholder: "Patient name", required: true },
  { key: "address", type: "textarea", placeholder: "Full address..." },
  { key: "age", type: "number", placeholder: "Years", required: true, min: 0, max: 130 },
  { key: "sex", type: "select", placeholder: "Sex", required: true, options: ["", "M", "F", "O"] },
  { key: "diagnosis_complaints", type: "textarea", placeholder: "Diagnosis, symptoms...", required: true },
  { key: "ent_pvt", type: "text", placeholder: "ENT/PVT" },
  { key: "nonent", type: "text", placeholder: "NONENT" },
  { key: "dod", type: "date", placeholder: "D.O.D." },
  { key: "staff_nurse", type: "text", placeholder: "Staff nurse name" },
  { key: "doctor_name", type: "text", placeholder: "Dr. name" },
  { key: "remarks", type: "textarea", placeholder: "Notes..." }
];

const tableBody = document.getElementById("tableBody");
const recordsSection = document.getElementById("recordsSection");
const recordsTableBody = document.getElementById("recordsTableBody");
const filterForm = document.getElementById("filterForm");
const recordCount = document.getElementById("recordCount");
const saveStatus = document.getElementById("saveStatus");
const digitalClock = document.getElementById("digitalClock");
const toastElement = document.getElementById("appToast");
const toastMessage = document.getElementById("toastMessage");
const toast = bootstrap.Toast.getOrCreateInstance(toastElement, { delay: 3000 });

let savedRecords = [];

function createControl(field, value = "") {
  if (field.type === "textarea") {
    const textarea = document.createElement("textarea");
    textarea.rows = 1;
    textarea.placeholder = field.placeholder;
    textarea.value = value || "";
    textarea.dataset.field = field.key;
    return textarea;
  }

  if (field.type === "select") {
    const select = document.createElement("select");
    select.dataset.field = field.key;

    field.options.forEach((optionValue) => {
      const option = document.createElement("option");
      option.value = optionValue;
      option.textContent = optionValue || field.placeholder;
      select.appendChild(option);
    });

    select.value = value || "";
    return select;
  }

  const input = document.createElement("input");
  input.type = field.type;
  input.placeholder = field.placeholder;
  input.value = value || "";
  input.dataset.field = field.key;

  if (field.min !== undefined) {
    input.min = field.min;
  }

  if (field.max !== undefined) {
    input.max = field.max;
  }

  return input;
}

function createCell(field, value = "") {
  const td = document.createElement("td");
  td.appendChild(createControl(field, value));
  return td;
}

function createSerialCell() {
  const td = document.createElement("td");
  td.className = "serial-cell";
  td.textContent = "0";
  return td;
}

function createActionCell(row) {
  const td = document.createElement("td");
  td.className = "no-print";

  const wrapper = document.createElement("div");
  wrapper.className = "row-actions";

  const duplicateButton = document.createElement("button");
  duplicateButton.type = "button";
  duplicateButton.className = "icon-btn duplicate";
  duplicateButton.title = "Duplicate row";
  duplicateButton.innerHTML = '<i class="bi bi-copy"></i>';
  duplicateButton.addEventListener("click", () => duplicateRow(row));

  const deleteButton = document.createElement("button");
  deleteButton.type = "button";
  deleteButton.className = "icon-btn delete";
  deleteButton.title = "Delete row";
  deleteButton.innerHTML = '<i class="bi bi-trash3"></i>';
  deleteButton.addEventListener("click", () => deleteRow(row));

  wrapper.append(duplicateButton, deleteButton);
  td.appendChild(wrapper);

  return td;
}

function addRow(record = {}) {
  const tr = document.createElement("tr");
  tr.dataset.id = record.id || "";

  tr.appendChild(createSerialCell());

  fields.forEach((field) => {
    tr.appendChild(createCell(field, record[field.key] ?? ""));
  });

  tr.appendChild(createActionCell(tr));
  tableBody.appendChild(tr);
  updateSerialNumbers();
}

function duplicateRow(row) {
  const data = getRowData(row);
  delete data.id;
  addRow(data);
  showToast("Row duplicated. Save all rows to store it.", "success");
}

function isRowEmpty(row) {
  return fields.every((field) => {
    const control = row.querySelector(`[data-field="${field.key}"]`);
    return !control || control.value.trim() === "";
  });
}

function getRowData(row) {
  const data = { id: row.dataset.id ? Number(row.dataset.id) : null };

  fields.forEach((field) => {
    const control = row.querySelector(`[data-field="${field.key}"]`);
    data[field.key] = control ? control.value.trim() : "";
  });

  return data;
}

function collectRowsForSave() {
  const rows = Array.from(tableBody.querySelectorAll("tr"));
  const patients = [];

  rows.forEach((row, index) => {
    if (isRowEmpty(row)) {
      return;
    }

    const data = getRowData(row);
    const missingField = fields.find((field) => field.required && !String(data[field.key] || "").trim());

    if (missingField) {
      throw new Error(`Row ${index + 1}: ${missingField.placeholder} is required.`);
    }

    const age = Number(data.age);

    if (!Number.isInteger(age) || age < 0 || age > 130) {
      throw new Error(`Row ${index + 1}: Age must be between 0 and 130.`);
    }

    patients.push(data);
  });

  if (patients.length === 0) {
    throw new Error("No filled rows found to save.");
  }

  return patients;
}

async function saveRows(options = {}) {
  const printAfterSave = Boolean(options.printAfterSave);

  try {
    setStatus("Saving...");
    const patients = collectRowsForSave();
    const response = await fetch("api/save_indoor_patient.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ patients })
    });
    const result = await response.json();

    if (!response.ok || !result.success) {
      throw new Error(result.message || "Unable to save records.");
    }

    setStatus("Saved");
    showToast(result.message, "success");
    await refreshRecordCount();

    if (!recordsSection.classList.contains("d-none")) {
      await viewRecords(false);
    }

    if (printAfterSave) {
      setTimeout(() => window.print(), 350);
      return true;
    }

    initializeBlankRows();
    return true;
  } catch (error) {
    setStatus("Error");
    showToast(error.message, "error");
    return false;
  }
}

function getFilterParams() {
  const params = new URLSearchParams();
  const formData = new FormData(filterForm);

  for (const [key, value] of formData.entries()) {
    const trimmed = String(value).trim();

    if (trimmed !== "") {
      params.append(key, trimmed);
    }
  }

  return params;
}

async function fetchRecords(params = new URLSearchParams()) {
  const query = params.toString();
  const url = query ? `api/fetch_indoor_patients.php?${query}` : "api/fetch_indoor_patients.php";
  const response = await fetch(url, { cache: "no-store" });
  const result = await response.json();

  if (!response.ok || !result.success) {
    throw new Error(result.message || "Unable to load records.");
  }

  savedRecords = result.records;
  recordCount.textContent = result.total ?? savedRecords.length;
  return savedRecords;
}

async function refreshRecordCount() {
  try {
    await fetchRecords(new URLSearchParams());
  } catch (error) {
    recordCount.textContent = "0";
  }
}

async function viewRecords(scrollToSection = true) {
  try {
    recordsSection.classList.remove("d-none");
    setStatus("Loading...");
    renderRecordsLoading();

    const records = await fetchRecords(getFilterParams());
    renderRecordsTable(records);
    setStatus("Ready");

    if (scrollToSection) {
      recordsSection.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  } catch (error) {
    setStatus("Error");
    renderRecordsError(error.message);
    showToast(error.message, "error");
  }
}

function renderRecordsLoading() {
  recordsTableBody.innerHTML = '<tr><td colspan="11" class="text-center text-muted py-4">Loading saved records...</td></tr>';
}

function renderRecordsError(message) {
  recordsTableBody.innerHTML = `<tr><td colspan="11" class="text-center text-danger py-4">${escapeHtml(message)}</td></tr>`;
}

function renderRecordsTable(records) {
  if (records.length === 0) {
    recordsTableBody.innerHTML = '<tr><td colspan="11" class="text-center text-muted py-4">No saved records found.</td></tr>';
    return;
  }

  recordsTableBody.innerHTML = "";

  records.forEach((record) => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${escapeHtml(record.yearly_no || "")}</td>
      <td>${escapeHtml(record.monthly_no || "")}</td>
      <td>${formatDisplayDate(record.admission_date)}</td>
      <td>${escapeHtml(record.admission_time || "")}</td>
      <td>${escapeHtml(record.employee_no || "")}</td>
      <td class="fw-semibold">${escapeHtml(record.patient_name || "")}</td>
      <td>${escapeHtml(record.age || "")}</td>
      <td>${escapeHtml(record.sex || "")}</td>
      <td class="text-truncate-cell">${escapeHtml(record.diagnosis_complaints || "")}</td>
      <td>${escapeHtml(record.doctor_name || "")}</td>
      <td>
        <div class="record-actions">
          <button type="button" class="btn btn-sm btn-outline-primary" data-action="edit" data-id="${record.id}">
            <i class="bi bi-pencil-square"></i> Edit
          </button>
          <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${record.id}">
            <i class="bi bi-trash3"></i> Delete
          </button>
        </div>
      </td>
    `;

    recordsTableBody.appendChild(tr);
  });
}

function editRecord(recordId) {
  const record = savedRecords.find((item) => Number(item.id) === Number(recordId));

  if (!record) {
    showToast("Selected record was not found.", "error");
    return;
  }

  tableBody.innerHTML = "";
  addRow(record);
  setStatus("Editing");
  window.scrollTo({ top: 0, behavior: "smooth" });
  showToast("Record loaded for editing. Save All will update it.", "success");
}

async function deleteSavedRecord(recordId) {
  if (!confirm("Delete this saved patient record?")) {
    return;
  }

  try {
    setStatus("Deleting...");
    const response = await fetch("api/delete_indoor_patient.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: Number(recordId) })
    });
    const result = await response.json();

    if (!response.ok || !result.success) {
      throw new Error(result.message || "Unable to delete record.");
    }

    showToast(result.message, "success");
    await viewRecords(false);
    removeMainRowIfPresent(recordId);
    ensureAtLeastOneRow();
    setStatus("Ready");
  } catch (error) {
    setStatus("Error");
    showToast(error.message, "error");
  }
}

async function deleteRow(row) {
  const id = Number(row.dataset.id || 0);

  if (!id) {
    row.remove();
    updateSerialNumbers();
    showToast("Unsaved row removed.", "success");
    ensureAtLeastOneRow();
    return;
  }

  await deleteSavedRecord(id);
}

function removeMainRowIfPresent(recordId) {
  tableBody.querySelectorAll("tr").forEach((row) => {
    if (Number(row.dataset.id || 0) === Number(recordId)) {
      row.remove();
    }
  });
  updateSerialNumbers();
}

function resetFilters() {
  filterForm.reset();
  viewRecords(false);
}

function initializeBlankRows() {
  tableBody.innerHTML = "";

  for (let i = 0; i < totalDefaultRows; i += 1) {
    addRow();
  }

  updateSerialNumbers();
  setStatus("Ready");
}

function ensureAtLeastOneRow() {
  if (tableBody.querySelectorAll("tr").length === 0) {
    addRow();
  }
  updateSerialNumbers();
}

function updateSerialNumbers() {
  tableBody.querySelectorAll("tr").forEach((row, index) => {
    const serialCell = row.querySelector(".serial-cell");

    if (serialCell) {
      serialCell.textContent = String(index + 1);
    }
  });
}

function updateClock() {
  const now = new Date();
  const day = String(now.getDate()).padStart(2, "0");
  const month = String(now.getMonth() + 1).padStart(2, "0");
  const year = now.getFullYear();
  let hours = now.getHours();
  const minutes = String(now.getMinutes()).padStart(2, "0");
  const seconds = String(now.getSeconds()).padStart(2, "0");
  const meridiem = hours >= 12 ? "PM" : "AM";

  hours %= 12;
  hours = hours || 12;

  digitalClock.querySelector("span").textContent = `${day}-${month}-${year} ${String(hours).padStart(2, "0")}:${minutes}:${seconds} ${meridiem}`;
}

function formatDisplayDate(value) {
  if (!value) {
    return "";
  }

  const [year, month, day] = value.split("-");

  if (!year || !month || !day) {
    return escapeHtml(value);
  }

  return `${day}-${month}-${year}`;
}

function setStatus(message) {
  saveStatus.textContent = message;
}

function showToast(message, type = "success") {
  toastElement.classList.remove("success", "error");
  toastElement.classList.add(type);
  toastMessage.textContent = message;
  toast.show();
}

function escapeHtml(value) {
  return String(value)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

document.getElementById("addRowBtn").addEventListener("click", () => addRow());
document.getElementById("viewRowsBtn").addEventListener("click", () => viewRecords());
document.getElementById("saveRowsBtn").addEventListener("click", saveRows);
document.getElementById("savePrintBtn").addEventListener("click", () => saveRows({ printAfterSave: true }));
document.getElementById("printBtn").addEventListener("click", () => window.print());
document.getElementById("resetFiltersBtn").addEventListener("click", resetFilters);

filterForm.addEventListener("submit", (event) => {
  event.preventDefault();
  viewRecords(false);
});

recordsTableBody.addEventListener("click", (event) => {
  const button = event.target.closest("button[data-action]");

  if (!button) {
    return;
  }

  const recordId = button.dataset.id;

  if (button.dataset.action === "edit") {
    editRecord(recordId);
    return;
  }

  if (button.dataset.action === "delete") {
    deleteSavedRecord(recordId);
  }
});

document.addEventListener("keydown", (event) => {
  const key = event.key.toUpperCase();

  if (event.ctrlKey && event.shiftKey && key === "A") {
    event.preventDefault();
    addRow();
  }

  if (event.ctrlKey && event.shiftKey && key === "S") {
    event.preventDefault();
    saveRows();
  }
});

document.addEventListener("DOMContentLoaded", () => {
  initializeBlankRows();
  updateClock();
  setInterval(updateClock, 1000);
  refreshRecordCount();
});
