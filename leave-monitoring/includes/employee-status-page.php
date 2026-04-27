<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/employee-upload-service.php';
require_once __DIR__ . '/user-activity.php';

userActivityMarkCurrentUser();

$userRole = $_SESSION['role'] ?? 'staff';
$normalizedUserRole = strtolower(trim($userRole));
$profileInitial = $normalizedUserRole === 'super admin'
    ? 'SA'
    : strtoupper(substr(trim($userRole), 0, 1));
$roleClass = $normalizedUserRole === 'super admin' ? 'role-super-admin' : '';
$pageTitle = $pageTitle ?? 'Employee Status';
$activePage = $activePage ?? '';
$sectionTitle = $sectionTitle ?? 'Employee Records';
$sectionLabel = $sectionLabel ?? 'Employee Records';
$sectionDescription = $sectionDescription ?? 'Manage employee records, import spreadsheet data, and add new employees.';
$defaultGroup = $defaultGroup ?? '';
$defaultStatus = $defaultStatus ?? '';
[$uploadMessage, $uploadMessageType, $uploadMessageDetails] = handleEmployeeUpload($conn);
$records = fetchEmployeeUploads($conn, $defaultGroup, $defaultStatus);
?>
<?php include __DIR__ . '/header.php'; ?>
<body>
    <div class="layout">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="content">
            <?php include __DIR__ . '/navbar.php'; ?>

            <div class="content-body">
                <section class="toolbar-card">
                    <div class="toolbar">
                        <div>
                            <h2><?= htmlspecialchars($sectionTitle, ENT_QUOTES, 'UTF-8') ?></h2>
                            <p><?= htmlspecialchars($sectionDescription, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>

                        <div class="toolbar-actions">
                            <button type="button" class="btn btn-soft" onclick="openUploadModal()">
                                <i class="fa-solid fa-upload"></i>
                                Upload XLSX
                            </button>

                            <button type="button" class="btn btn-primary" onclick="openEmployeeModal()">
                                <i class="fa-solid fa-user-plus"></i>
                                Add Employee
                            </button>
                        </div>
                    </div>
                </section>

                <section class="section">
                    <div class="table-card">
                        <div class="section-header">
                            <div class="section-header-copy">
                                <h3><?= htmlspecialchars($sectionLabel, ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="section-subcopy">Search by employee no., name, birth details, or upload date.</p>
                            </div>
                            <div class="section-header-actions">
                                <label class="table-search-field" for="employeeTableSearch">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    <input type="search" id="employeeTableSearch" placeholder="Search employee records">
                                </label>
                                <button type="button" class="btn btn-outline btn-export-table" onclick="printEmployeeTable()">
                                    <i class="fa-solid fa-print"></i>
                                    Export Table
                                </button>
                                <button type="submit" form="bulkDeleteForm" class="btn btn-danger" id="bulkDeleteBtn" disabled>
                                    <i class="fa-solid fa-trash"></i>
                                    <span id="selectedCountText">Delete Selection (0)</span>
                                </button>
                            </div>
                        </div>

                        <?php if ($uploadMessage !== ''): ?>
                            <div class="alert-banner alert-<?= htmlspecialchars($uploadMessageType, ENT_QUOTES, 'UTF-8') ?>">
                                <div class="alert-banner-title"><?= htmlspecialchars($uploadMessage, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php if ($uploadMessageDetails !== []): ?>
                                    <div class="upload-result-list">
                                        <?php foreach ($uploadMessageDetails as $uploadDetail): ?>
                                            <div class="upload-result-item upload-result-item-<?= htmlspecialchars((string) ($uploadDetail['type'] ?? 'success'), ENT_QUOTES, 'UTF-8') ?>">
                                                <strong><?= htmlspecialchars((string) ($uploadDetail['file'] ?? 'Uploaded file'), ENT_QUOTES, 'UTF-8') ?></strong>
                                                <span><?= htmlspecialchars((string) ($uploadDetail['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAllRecords" class="table-checkbox" aria-label="Select all records">
                                    </th>
                                    <th>Employee No.</th>
                                    <th>Surname</th>
                                    <th>First Name</th>
                                    <th>M.I</th>
                                    <th>Date of Birth</th>
                                    <th>Place of Birth</th>
                                    <th>Uploaded</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="employeeTableBody">
                                <?php if ($records === []): ?>
                                    <tr>
                                        <td colspan="8" class="empty-state-cell">No uploaded employee records yet for this section.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($records as $record): ?>
                                        <?php $searchValue = trim(implode(' ', [
                                            (string) ($record['employee_no'] ?? ''),
                                            (string) ($record['surname'] ?? ''),
                                            (string) ($record['first_name'] ?? ''),
                                            (string) ($record['middle_initial'] ?? ''),
                                            (string) ($record['date_of_birth'] ?? ''),
                                            (string) ($record['place_of_birth'] ?? ''),
                                            date('F j, Y h:i A', strtotime((string) $record['created_at']))
                                        ])); ?>
                                        <tr class="employee-record-row" data-search="<?= htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8') ?>">
                                            <td>
                                                <input
                                                    type="checkbox"
                                                    class="table-checkbox record-select-checkbox"
                                                    value="<?= (int) $record['id'] ?>"
                                                    aria-label="Select <?= htmlspecialchars(trim($record['surname'] . ', ' . $record['first_name']), ENT_QUOTES, 'UTF-8') ?>"
                                                >
                                            </td>
                                            <td><?= htmlspecialchars($record['employee_no'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($record['surname'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($record['first_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($record['middle_initial'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($record['date_of_birth'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($record['place_of_birth'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <div><?= htmlspecialchars(date('F j, Y h:i A', strtotime((string) $record['created_at'])), ENT_QUOTES, 'UTF-8') ?></div>
                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                    <button
                                                        type="button"
                                                        class="table-btn edit edit-employee-btn action-icon-btn"
                                                        data-record-id="<?= (int) $record['id'] ?>"
                                                        data-employee-no="<?= htmlspecialchars($record['employee_no'], ENT_QUOTES, 'UTF-8') ?>"
                                                        data-surname="<?= htmlspecialchars($record['surname'], ENT_QUOTES, 'UTF-8') ?>"
                                                        data-first-name="<?= htmlspecialchars($record['first_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                        data-middle-initial="<?= htmlspecialchars($record['middle_initial'], ENT_QUOTES, 'UTF-8') ?>"
                                                        data-date-of-birth="<?= htmlspecialchars($record['date_of_birth'], ENT_QUOTES, 'UTF-8') ?>"
                                                        data-place-of-birth="<?= htmlspecialchars($record['place_of_birth'], ENT_QUOTES, 'UTF-8') ?>"
                                                        onclick="openEditEmployeeModal(this)"
                                                        title="Edit Employee"
                                                        aria-label="Edit Employee"
                                                    >
                                                        <i class="fa-solid fa-user-pen"></i>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="table-btn edit preview-btn action-icon-btn"
                                                        data-preview-id="preview-<?= (int) $record['id'] ?>"
                                                        data-name="<?= htmlspecialchars(trim($record['surname'] . ', ' . $record['first_name']), ENT_QUOTES, 'UTF-8') ?>"
                                                        onclick="openImageModal(this)"
                                                        title="View Form"
                                                        aria-label="View Form"
                                                    >
                                                        <i class="fa-solid fa-file-lines"></i>
                                                    </button>

                                                    <form method="POST" class="inline-action-form delete-record-form">
                                                        <input type="hidden" name="form_action" value="delete_employee_record">
                                                        <input type="hidden" name="record_id" value="<?= (int) $record['id'] ?>">
                                                        <button type="submit" class="table-btn delete action-icon-btn" title="Delete" aria-label="Delete">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>

                                                <template id="preview-<?= (int) $record['id'] ?>">
                                                    <?= renderUploadedEmployeePreview($conn, $record) ?>
                                                </template>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <tr id="employeeSearchEmptyRow" class="search-empty-row" hidden>
                                    <td colspan="9" class="empty-state-cell">No employee records match your search.</td>
                                </tr>
                            </tbody>
                        </table>

                        <form method="POST" id="bulkDeleteForm" class="bulk-delete-form">
                            <input type="hidden" name="form_action" value="bulk_delete_employee_records">
                            <div id="bulkDeleteInputs"></div>
                        </form>
                    </div>
                </section>
            </div>

            <?php include __DIR__ . '/footer.php'; ?>
        </main>
    </div>

    <div class="modal-overlay" id="uploadModal">
        <div class="modal-card">
            <h3>Upload XLSX File</h3>
            <p>Select the group, status, and one or more XLSX files to import employee data.</p>

            <form class="upload-form" id="uploadForm" action="#" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="form_action" value="upload_employee_record">
                <div class="form-field">
                    <label for="uploadGroup">Select Group</label>
                    <select id="uploadGroup" name="upload_group" required>
                        <option value="">Select group</option>
                        <option value="ES" <?= $defaultGroup === 'ES' ? 'selected' : '' ?>>ES</option>
                        <option value="SEC" <?= $defaultGroup === 'SEC' ? 'selected' : '' ?>>SEC</option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="uploadStatus">Select Status</label>
                    <select id="uploadStatus" name="upload_status" required>
                        <option value="">Select status</option>
                        <option value="Active" <?= $defaultStatus === 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Inactivation" <?= $defaultStatus === 'Inactivation' ? 'selected' : '' ?>>Inactivation</option>
                        <option value="Separation" <?= $defaultStatus === 'Separation' ? 'selected' : '' ?>>Separation</option>
                    </select>
                </div>

                <div class="file-upload-field">
                    <input class="file-input" id="leaveFileInput" type="file" name="employee_file[]" accept=".xlsx" multiple required>
                    <label class="file-picker" for="leaveFileInput">
                        <span class="file-picker-icon">
                            <i class="fa-solid fa-file-excel"></i>
                        </span>
                        <span class="file-picker-copy">
                            <strong>Choose XLSX Files</strong>
                            <span>Click to browse or drag and drop one or more Excel files here.</span>
                        </span>
                        <span class="file-picker-action">Browse</span>
                    </label>
                    <p class="file-selected-name" id="selectedFileName">No file selected</p>
                    <div class="upload-status-indicator" id="uploadStatusIndicator" aria-live="polite">
                        <span class="upload-status-dot"></span>
                        <span id="uploadStatusText">Waiting for file selection.</span>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closeUploadModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="uploadSubmitBtn">
                        <i class="fa-solid fa-file-arrow-up"></i>
                        Upload Files
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="imageModal">
        <div class="modal-card image-preview-card">
            <div class="image-preview-header">
                <div class="image-preview-copy">
                    <span class="image-preview-kicker">Uploaded Preview</span>
                    <h3 id="imagePreviewTitle">Uploaded Form</h3>
                    <p>Review the parsed spreadsheet layout in a cleaner document-style viewer.</p>
                </div>
                <div class="image-preview-actions">
                    <button type="button" class="btn btn-soft image-preview-print-btn" onclick="printImagePreview()">
                        <i class="fa-solid fa-print"></i>
                        Print
                    </button>
                    <button type="button" class="btn btn-primary image-preview-add-btn" onclick="openLeaveModal()">
                        <i class="fa-solid fa-plus"></i>
                        Add Leave
                    </button>
                    <button type="button" class="preview-close-btn" onclick="closeImageModal()" aria-label="Close preview">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
            <div class="image-preview-frame" id="imagePreviewContent">
                <p class="preview-empty">No preview loaded.</p>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="leaveModal">
        <div class="modal-card leave-modal-card">
            <div class="employee-modal-hero leave-modal-hero">
                <div class="employee-modal-badge">
                    <i class="fa-solid fa-calendar-plus"></i>
                </div>
                <div>
                    <h3 id="leaveModalTitle">Add Leave</h3>
                    <p id="leaveModalDescription">Enter leave details for the selected employee record.</p>
                </div>
            </div>

            <form class="employee-form leave-entry-form" id="leaveEntryForm" action="#" method="POST">
                <input type="hidden" id="leaveEditIndex" value="">
                <div class="employee-form-section">
                    <div class="employee-section-heading">
                        <span>Period Covered</span>
                        <small>Inclusive leave dates</small>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="leaveFromDate">From</label>
                            <input id="leaveFromDate" type="text" name="leave_from_date" placeholder="e.g. June 16, 2025 or 16/06/2025" required>
                        </div>
                        <div class="form-field">
                            <label for="leaveToDate">To</label>
                            <input id="leaveToDate" type="text" name="leave_to_date" placeholder="e.g. June 18, 2025 or 18/06/2025" required>
                        </div>
                    </div>
                </div>

                <div class="employee-form-section">
                    <div class="employee-section-heading">
                        <span>Leave Details</span>
                        <small>Classification and assignment information</small>
                    </div>

                    <div class="form-grid">
                        <div class="form-field form-field-full">
                            <label for="leaveReason">Reason</label>
                            <select id="leaveReason" name="leave_reason" required>
                                <option value="">Select reason</option>
                                <option value="Sick Leave w/out Pay">Sick Leave w/out Pay</option>
                                <option value="Sick Leave w/pay">Sick Leave w/pay</option>
                                <option value="Vacation Leave w/out Pay">Vacation Leave w/out Pay</option>
                                <option value="Vacation Leave w/pay">Vacation Leave w/pay</option>
                                <option value="Maternity Leave">Maternity Leave</option>
                                <option value="Study Leave">Study Leave</option>
                                <option value="Wellness Leave">Wellness Leave</option>
                                <option value="Special Privilege Leave">Special Privilege Leave</option>
                                <option value="Forced Leave">Forced Leave</option>
                                <option value="Others">Others (please specify:)</option>
                            </select>
                        </div>
                        <div class="form-field form-field-full" id="otherReasonField" style="display: none;">
                            <label for="leaveOtherReason">Other Reason</label>
                            <input id="leaveOtherReason" type="text" name="leave_other_reason" placeholder="Please specify the leave reason">
                        </div>
                        <div class="form-field form-field-full">
                            <label for="leaveStation">Station / Place of Assignment</label>
                            <input id="leaveStation" type="text" name="leave_station" placeholder="Enter station or place of assignment" required>
                        </div>
                    </div>
                </div>

                <div class="employee-form-section">
                    <div class="employee-section-heading">
                        <span>Leave Credits</span>
                        <small>Attendance and remarks details</small>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="absenceWithoutPay">Absence Without Pay</label>
                            <input id="absenceWithoutPay" type="text" name="absence_without_pay" placeholder="e.g. 1 day">
                        </div>
                        <div class="form-field">
                            <label for="absenceWithPay">Absence With Pay</label>
                            <input id="absenceWithPay" type="text" name="absence_with_pay" placeholder="e.g. 2 days">
                        </div>
                        <div class="form-field form-field-full">
                            <label for="leaveRemarks">Remarks</label>
                            <textarea id="leaveRemarks" name="leave_remarks" rows="3" placeholder="Enter remarks"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closeLeaveModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Leave
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="editEmployeeModal">
        <div class="modal-card employee-modal-card">
            <div class="employee-modal-hero">
                <div class="employee-modal-badge">
                    <i class="fa-solid fa-user-pen"></i>
                </div>
                <div>
                    <h3>Edit Employee</h3>
                    <p>Update the employee's basic personal information.</p>
                </div>
            </div>

            <form class="employee-form" action="#" method="POST">
                <input type="hidden" name="form_action" value="edit_employee_record">
                <input type="hidden" name="record_id" id="editEmployeeRecordId">
                <input type="hidden" name="employee_group" value="<?= htmlspecialchars($defaultGroup, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="employee_status" value="<?= htmlspecialchars($defaultStatus, ENT_QUOTES, 'UTF-8') ?>">
                <div class="employee-form-section">
                    <div class="employee-section-heading">
                        <span>Identity</span>
                        <small>Basic employee personal details</small>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="editEmployeeNo">Employee No.</label>
                            <input id="editEmployeeNo" type="text" name="employee_no" required>
                        </div>
                        <div class="form-field">
                            <label for="editEmployeeSurname">Surname</label>
                            <input id="editEmployeeSurname" type="text" name="employee_surname" required>
                        </div>
                        <div class="form-field">
                            <label for="editEmployeeFirstName">First Name</label>
                            <input id="editEmployeeFirstName" type="text" name="employee_first_name" required>
                        </div>
                        <div class="form-field">
                            <label for="editEmployeeMiddleInitial">Middle Initial (M.I)</label>
                            <input id="editEmployeeMiddleInitial" type="text" name="employee_middle_initial" maxlength="10">
                        </div>
                        <div class="form-field">
                            <label for="editEmployeeDateOfBirth">Date of Birth</label>
                            <input id="editEmployeeDateOfBirth" type="date" name="employee_date_of_birth" required>
                        </div>
                        <div class="form-field form-field-full">
                            <label for="editEmployeePlaceOfBirth">Place of Birth</label>
                            <input id="editEmployeePlaceOfBirth" type="text" name="employee_place_of_birth" required>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closeEditEmployeeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Update Employee
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="employeeModal">
        <div class="modal-card employee-modal-card">
            <div class="employee-modal-hero">
                <div class="employee-modal-badge">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <div>
                    <h3>Add Employee</h3>
                    <p>Enter the employee's basic personal information.</p>
                </div>
            </div>

            <form class="employee-form" action="#" method="POST">
                <input type="hidden" name="form_action" value="add_employee_record">
                <input type="hidden" name="employee_group" value="<?= htmlspecialchars($defaultGroup, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="employee_status" value="<?= htmlspecialchars($defaultStatus, ENT_QUOTES, 'UTF-8') ?>">
                <div class="employee-form-section">
                    <div class="employee-section-heading">
                        <span>Identity</span>
                        <small>Basic employee personal details</small>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="employeeNo">Employee No.</label>
                            <input id="employeeNo" type="text" name="employee_no" placeholder="Enter employee no." required>
                        </div>
                        <div class="form-field">
                            <label for="employeeSurname">Surname</label>
                            <input id="employeeSurname" type="text" name="employee_surname" placeholder="Enter surname" required>
                        </div>
                        <div class="form-field">
                            <label for="employeeFirstName">First Name</label>
                            <input id="employeeFirstName" type="text" name="employee_first_name" placeholder="Enter first name" required>
                        </div>
                        <div class="form-field">
                            <label for="employeeMiddleInitial">Middle Initial (M.I)</label>
                            <input id="employeeMiddleInitial" type="text" name="employee_middle_initial" placeholder="Enter middle initial" maxlength="10">
                        </div>
                        <div class="form-field">
                            <label for="employeeDateOfBirth">Date of Birth</label>
                            <input id="employeeDateOfBirth" type="date" name="employee_date_of_birth" required>
                        </div>
                        <div class="form-field form-field-full">
                            <label for="employeePlaceOfBirth">Place of Birth</label>
                            <input id="employeePlaceOfBirth" type="text" name="employee_place_of_birth" placeholder="Enter place of birth" required>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closeEmployeeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Employee
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="confirmDeleteModal">
        <div class="modal-card confirm-delete-card">
            <div class="confirm-delete-hero">
                <div class="confirm-delete-icon">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
                <div>
                    <span class="confirm-delete-kicker">Delete Record</span>
                    <h3>Delete this uploaded employee record?</h3>
                    <p>This action will permanently remove the employee entry and its uploaded XLSX file.</p>
                </div>
            </div>

            <div class="confirm-delete-actions">
                <button type="button" class="btn btn-outline" onclick="closeDeleteConfirmModal()">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fa-solid fa-trash"></i>
                    Delete Record
                </button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="confirmBulkDeleteModal">
        <div class="modal-card confirm-delete-card">
            <div class="confirm-delete-hero">
                <div class="confirm-delete-icon">
                    <i class="fa-solid fa-trash-arrow-up"></i>
                </div>
                <div>
                    <span class="confirm-delete-kicker">Bulk Delete</span>
                    <h3>Delete selected employee records?</h3>
                    <p>This will permanently remove all selected employee entries and their uploaded XLSX files.</p>
                </div>
            </div>

            <div class="confirm-delete-actions">
                <button type="button" class="btn btn-outline" onclick="closeBulkDeleteConfirmModal()">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmBulkDeleteBtn">
                    <i class="fa-solid fa-trash"></i>
                    Delete Selection
                </button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="confirmLeaveDeleteModal">
        <div class="modal-card confirm-delete-card">
            <div class="confirm-delete-hero">
                <div class="confirm-delete-icon">
                    <i class="fa-solid fa-calendar-xmark"></i>
                </div>
                <div>
                    <span class="confirm-delete-kicker">Delete Leave</span>
                    <h3>Delete this leave entry?</h3>
                    <p>This will remove the selected leave row from the current preview.</p>
                </div>
            </div>

            <div class="confirm-delete-actions">
                <button type="button" class="btn btn-outline" onclick="closeLeaveDeleteConfirmModal()">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmLeaveDeleteBtn">
                    <i class="fa-solid fa-trash"></i>
                    Delete Leave
                </button>
            </div>
        </div>
    </div>

    <script>
        const uploadModal = document.getElementById('uploadModal');
        const imageModal = document.getElementById('imageModal');
        const leaveModal = document.getElementById('leaveModal');
        const editEmployeeModal = document.getElementById('editEmployeeModal');
        const employeeModal = document.getElementById('employeeModal');
        const confirmDeleteModal = document.getElementById('confirmDeleteModal');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const confirmBulkDeleteModal = document.getElementById('confirmBulkDeleteModal');
        const confirmBulkDeleteBtn = document.getElementById('confirmBulkDeleteBtn');
        const confirmLeaveDeleteModal = document.getElementById('confirmLeaveDeleteModal');
        const confirmLeaveDeleteBtn = document.getElementById('confirmLeaveDeleteBtn');
        const tableCard = document.querySelector('.table-card');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');
        const bulkDeleteInputs = document.getElementById('bulkDeleteInputs');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const selectedCountText = document.getElementById('selectedCountText');
        const selectAllRecords = document.getElementById('selectAllRecords');
        const employeeTableSearch = document.getElementById('employeeTableSearch');
        const employeeTableBody = document.getElementById('employeeTableBody');
        const employeeSearchEmptyRow = document.getElementById('employeeSearchEmptyRow');
        const employeeTable = document.querySelector('.table-card .table');
        const uploadForm = document.getElementById('uploadForm');
        const uploadGroup = document.getElementById('uploadGroup');
        const uploadStatus = document.getElementById('uploadStatus');
        const leaveFileInput = document.getElementById('leaveFileInput');
        const uploadSubmitBtn = document.getElementById('uploadSubmitBtn');
        const uploadStatusIndicator = document.getElementById('uploadStatusIndicator');
        const uploadStatusText = document.getElementById('uploadStatusText');
        const leaveEntryForm = document.getElementById('leaveEntryForm');
        const leaveModalTitle = document.getElementById('leaveModalTitle');
        const leaveModalDescription = document.getElementById('leaveModalDescription');
        const leaveEditIndex = document.getElementById('leaveEditIndex');
        const leaveFromDate = document.getElementById('leaveFromDate');
        const leaveToDate = document.getElementById('leaveToDate');
        const leaveReason = document.getElementById('leaveReason');
        const otherReasonField = document.getElementById('otherReasonField');
        const leaveOtherReason = document.getElementById('leaveOtherReason');
        const leaveStation = document.getElementById('leaveStation');
        const absenceWithoutPay = document.getElementById('absenceWithoutPay');
        const absenceWithPay = document.getElementById('absenceWithPay');
        const leaveRemarks = document.getElementById('leaveRemarks');
        const selectedFileName = document.getElementById('selectedFileName');
        const imagePreviewContent = document.getElementById('imagePreviewContent');
        const imagePreviewTitle = document.getElementById('imagePreviewTitle');
        let alertBanner = document.querySelector('.alert-banner');
        const editEmployeeRecordId = document.getElementById('editEmployeeRecordId');
        const editEmployeeNo = document.getElementById('editEmployeeNo');
        const editEmployeeSurname = document.getElementById('editEmployeeSurname');
        const editEmployeeFirstName = document.getElementById('editEmployeeFirstName');
        const editEmployeeMiddleInitial = document.getElementById('editEmployeeMiddleInitial');
        const editEmployeeDateOfBirth = document.getElementById('editEmployeeDateOfBirth');
        const editEmployeePlaceOfBirth = document.getElementById('editEmployeePlaceOfBirth');
        const employeeRecordRows = Array.from(document.querySelectorAll('.employee-record-row'));
        const deleteRecordForms = Array.from(document.querySelectorAll('.delete-record-form'));
        const recordSelectCheckboxes = Array.from(document.querySelectorAll('.record-select-checkbox'));
        let pendingDeleteForm = null;
        let isBulkDeletePending = false;
        let pendingLeaveDeleteRow = null;

        function setUploadIndicatorState(state, message) {
            uploadStatusIndicator.classList.remove('is-idle', 'is-ready', 'is-uploading');
            uploadStatusIndicator.classList.add(state);
            uploadStatusText.textContent = message;
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderUploadAlert(message, type, details) {
            if (!tableCard) {
                return;
            }

            if (alertBanner) {
                alertBanner.remove();
            }

            const banner = document.createElement('div');
            banner.className = 'alert-banner alert-' + type;

            let detailsMarkup = '';
            if (Array.isArray(details) && details.length > 0) {
                detailsMarkup = '<div class="upload-result-list">' + details.map(function (detail) {
                    return '<div class="upload-result-item upload-result-item-' + escapeHtml(detail.type || 'success') + '">' +
                        '<strong>' + escapeHtml(detail.file || 'Uploaded file') + '</strong>' +
                        '<span>' + escapeHtml(detail.message || '') + '</span>' +
                    '</div>';
                }).join('') + '</div>';
            }

            banner.innerHTML = '<div class="alert-banner-title">' + escapeHtml(message) + '</div>' + detailsMarkup;
            const sectionHeader = tableCard.querySelector('.section-header');
            if (sectionHeader) {
                sectionHeader.insertAdjacentElement('afterend', banner);
            } else {
                tableCard.prepend(banner);
            }

            alertBanner = banner;
        }

        function openUploadModal() {
            uploadModal.classList.add('show');
        }

        function closeUploadModal() {
            uploadModal.classList.remove('show');
        }

        function openDeleteConfirmModal(form) {
            pendingDeleteForm = form;
            confirmDeleteModal.classList.add('show');
        }

        function closeDeleteConfirmModal() {
            confirmDeleteModal.classList.remove('show');
            pendingDeleteForm = null;
        }

        function openBulkDeleteConfirmModal() {
            isBulkDeletePending = true;
            confirmBulkDeleteModal.classList.add('show');
        }

        function closeBulkDeleteConfirmModal() {
            confirmBulkDeleteModal.classList.remove('show');
            isBulkDeletePending = false;
        }

        function openLeaveDeleteConfirmModal(row) {
            pendingLeaveDeleteRow = row;
            confirmLeaveDeleteModal.classList.add('show');
        }

        function closeLeaveDeleteConfirmModal() {
            confirmLeaveDeleteModal.classList.remove('show');
            pendingLeaveDeleteRow = null;
        }

        function openImageModal(button) {
            const previewTemplate = document.getElementById(button.dataset.previewId);
            imagePreviewContent.innerHTML = previewTemplate ? previewTemplate.innerHTML : '<p class="preview-empty">No preview available.</p>';
            imagePreviewTitle.textContent = button.dataset.name + ' Form';
            imageModal.classList.add('show');
        }

        function closeImageModal() {
            imageModal.classList.remove('show');
            imagePreviewContent.innerHTML = '<p class="preview-empty">No preview loaded.</p>';
        }

        function printImagePreview() {
            const previewMarkup = imagePreviewContent.innerHTML.trim();

            if (!previewMarkup || previewMarkup.includes('preview-empty')) {
                window.alert('No preview available to print.');
                return;
            }

            const printWindow = window.open('', '_blank', 'width=1200,height=900');
            if (!printWindow) {
                window.alert('Please allow pop-ups to print the report.');
                return;
            }

            const styleMarkup = Array.from(document.querySelectorAll('style, link[rel="stylesheet"]'))
                .map(function (node) {
                    return node.outerHTML;
                })
                .join('');
            const previewTitle = imagePreviewTitle.textContent || 'Uploaded Form';

            printWindow.document.open();
            printWindow.document.write(`<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${previewTitle}</title>
    ${styleMarkup}
    <style>
        body {
            margin: 0;
            padding: 24px;
            background: #ffffff;
            color: #0f172a;
        }

        .print-preview-shell {
            max-width: 1320px;
            margin: 0 auto;
        }

        .print-preview-shell .leave-table-preview-shell,
        .print-preview-shell .xlsx-preview-shell {
            box-shadow: none;
            border-radius: 0;
        }

        .print-preview-shell .no-print {
            display: none !important;
        }

        .print-preview-shell .leave-table-preview {
            table-layout: fixed;
        }

        .print-preview-shell .leave-table-preview th,
        .print-preview-shell .leave-table-preview td {
            word-break: normal;
            overflow-wrap: anywhere;
        }

        .print-preview-shell .leave-table-preview thead th {
            font-size: 11px;
            line-height: 1.2;
            padding: 8px 6px;
        }

        .print-preview-shell .leave-table-preview tbody td {
            font-size: 10.5px;
            line-height: 1.3;
            padding: 8px 6px;
            text-align: center;
            vertical-align: middle;
        }

        .print-preview-shell .leave-period-heading strong {
            font-size: 11px;
            line-height: 1.15;
        }

        .print-preview-shell .leave-period-heading span {
            font-size: 9px;
            line-height: 1.15;
        }

        .print-preview-shell .leave-table-preview th:nth-child(1),
        .print-preview-shell .leave-table-preview th:nth-child(2),
        .print-preview-shell .leave-table-preview td:nth-child(1),
        .print-preview-shell .leave-table-preview td:nth-child(2) {
            width: 11%;
        }

        .print-preview-shell .leave-table-preview th:nth-child(3),
        .print-preview-shell .leave-table-preview td:nth-child(3) {
            width: 12%;
        }

        .print-preview-shell .leave-table-preview th:nth-child(4),
        .print-preview-shell .leave-table-preview td:nth-child(4) {
            width: 21%;
        }

        .print-preview-shell .leave-table-preview th:nth-child(5),
        .print-preview-shell .leave-table-preview td:nth-child(5),
        .print-preview-shell .leave-table-preview th:nth-child(6),
        .print-preview-shell .leave-table-preview td:nth-child(6) {
            width: 16%;
        }

        .print-preview-shell .leave-table-preview th:nth-child(7),
        .print-preview-shell .leave-table-preview td:nth-child(7) {
            width: 13%;
        }

        @page {
            size: landscape;
            margin: 10mm;
        }

        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="print-preview-shell">${previewMarkup}</div>
    <script>
        window.addEventListener('load', function () {
            window.print();
            window.onafterprint = function () {
                window.close();
            };
        });
    <\/script>
</body>
</html>`);
            printWindow.document.close();
        }

        function openLeaveModal() {
            leaveEntryForm.reset();
            leaveEditIndex.value = '';
            leaveModalTitle.textContent = 'Add Leave';
            leaveModalDescription.textContent = 'Enter leave details for the selected employee record.';
            otherReasonField.style.display = 'none';
            leaveOtherReason.required = false;
            leaveModal.classList.add('show');
        }

        function closeLeaveModal() {
            leaveModal.classList.remove('show');
        }

        function openEditLeaveModal(button) {
            leaveEditIndex.value = button.dataset.leaveIndex || '';
            leaveFromDate.value = button.dataset.from || '';
            leaveToDate.value = button.dataset.to || '';
            leaveReason.value = button.dataset.reason || '';
            leaveStation.value = button.dataset.station || '';
            absenceWithoutPay.value = button.dataset.withoutPay || '';
            absenceWithPay.value = button.dataset.withPay || '';
            leaveRemarks.value = button.dataset.remarks || '';

            const isOther = leaveReason.value !== '' && !Array.from(leaveReason.options).some(function (option) {
                return option.value === leaveReason.value;
            });

            if (isOther) {
                leaveReason.value = 'Others';
                leaveOtherReason.value = button.dataset.reason || '';
                otherReasonField.style.display = 'flex';
                leaveOtherReason.required = true;
            } else {
                leaveOtherReason.value = '';
                otherReasonField.style.display = leaveReason.value === 'Others' ? 'flex' : 'none';
                leaveOtherReason.required = leaveReason.value === 'Others';
            }

            leaveModalTitle.textContent = 'Edit Leave';
            leaveModalDescription.textContent = 'Update the selected leave entry for this preview.';
            leaveModal.classList.add('show');
        }

        function deleteLeaveRow(button) {
            const row = button.closest('tr');
            if (!row) {
                return;
            }

            openLeaveDeleteConfirmModal(row);
        }

        function buildLeaveActionCell(index, leaveData) {
            return `<td class="leave-row-actions no-print">
                <div class="table-actions leave-preview-actions">
                    <button
                        type="button"
                        class="table-btn edit action-icon-btn"
                        onclick="openEditLeaveModal(this)"
                        title="Edit Leave"
                        aria-label="Edit Leave"
                        data-leave-index="${escapeHtml(index)}"
                        data-from="${escapeHtml(leaveData.from)}"
                        data-to="${escapeHtml(leaveData.to)}"
                        data-reason="${escapeHtml(leaveData.reason)}"
                        data-station="${escapeHtml(leaveData.station)}"
                        data-without-pay="${escapeHtml(leaveData.withoutPay)}"
                        data-with-pay="${escapeHtml(leaveData.withPay)}"
                        data-remarks="${escapeHtml(leaveData.remarks)}"
                    >
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button
                        type="button"
                        class="table-btn delete action-icon-btn"
                        onclick="deleteLeaveRow(this)"
                        title="Delete Leave"
                        aria-label="Delete Leave"
                        data-leave-index="${escapeHtml(index)}"
                    >
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </td>`;
        }

        function refreshLeaveRowActions() {
            const previewTableBody = imagePreviewContent.querySelector('.leave-table-preview tbody');
            if (!previewTableBody) {
                return;
            }

            Array.from(previewTableBody.querySelectorAll('tr')).forEach(function (row, index) {
                const editButton = row.querySelector('button[onclick="openEditLeaveModal(this)"]');
                const deleteButton = row.querySelector('button[onclick="deleteLeaveRow(this)"]');

                if (editButton) {
                    editButton.dataset.leaveIndex = String(index);
                }

                if (deleteButton) {
                    deleteButton.dataset.leaveIndex = String(index);
                }
            });
        }

        function openEditEmployeeModal(button) {
            editEmployeeRecordId.value = button.dataset.recordId || '';
            editEmployeeNo.value = button.dataset.employeeNo || '';
            editEmployeeSurname.value = button.dataset.surname || '';
            editEmployeeFirstName.value = button.dataset.firstName || '';
            editEmployeeMiddleInitial.value = button.dataset.middleInitial || '';
            editEmployeeDateOfBirth.value = button.dataset.dateOfBirth || '';
            editEmployeePlaceOfBirth.value = button.dataset.placeOfBirth || '';
            editEmployeeModal.classList.add('show');
        }

        function closeEditEmployeeModal() {
            editEmployeeModal.classList.remove('show');
        }

        function filterEmployeeTable() {
            if (!employeeTableSearch || !employeeTableBody) {
                return;
            }

            const query = employeeTableSearch.value.trim().toLowerCase();
            let visibleCount = 0;

            employeeRecordRows.forEach(function (row) {
                const haystack = (row.dataset.search || '').toLowerCase();
                const isMatch = query === '' || haystack.includes(query);
                row.hidden = !isMatch;

                if (isMatch) {
                    visibleCount++;
                }
            });

            if (employeeSearchEmptyRow) {
                employeeSearchEmptyRow.hidden = visibleCount > 0 || employeeRecordRows.length === 0;
            }

            syncSelectAllState();
        }

        function getVisibleSelectableCheckboxes() {
            return recordSelectCheckboxes.filter(function (checkbox) {
                const row = checkbox.closest('.employee-record-row');
                return row && !row.hidden;
            });
        }

        function updateBulkSelectionState() {
            const selectedCount = recordSelectCheckboxes.filter(function (checkbox) {
                return checkbox.checked;
            }).length;

            if (selectedCountText) {
                selectedCountText.textContent = 'Delete Selection (' + selectedCount + ')';
            }

            if (bulkDeleteBtn) {
                bulkDeleteBtn.disabled = selectedCount === 0;
            }

            syncSelectAllState();
        }

        function syncBulkDeleteInputs() {
            if (!bulkDeleteInputs) {
                return;
            }

            bulkDeleteInputs.innerHTML = '';
            recordSelectCheckboxes.forEach(function (checkbox) {
                if (!checkbox.checked) {
                    return;
                }

                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'record_ids[]';
                hiddenInput.value = checkbox.value;
                bulkDeleteInputs.appendChild(hiddenInput);
            });
        }

        function syncSelectAllState() {
            if (!selectAllRecords) {
                return;
            }

            const visibleCheckboxes = getVisibleSelectableCheckboxes();
            const checkedVisible = visibleCheckboxes.filter(function (checkbox) {
                return checkbox.checked;
            }).length;

            selectAllRecords.checked = visibleCheckboxes.length > 0 && checkedVisible === visibleCheckboxes.length;
            selectAllRecords.indeterminate = checkedVisible > 0 && checkedVisible < visibleCheckboxes.length;
        }

        function printEmployeeTable() {
            if (!employeeTable) {
                window.alert('No table available to print.');
                return;
            }

            const visibleRows = employeeRecordRows.filter(function (row) {
                return !row.hidden;
            });

            if (visibleRows.length === 0) {
                window.alert('No employee records match the current search.');
                return;
            }

            const tableHeaders = Array.from(employeeTable.querySelectorAll('thead th'))
                .slice(1, 8)
                .map(function (header) {
                    return '<th>' + header.innerHTML + '</th>';
                })
                .join('');

            const tableBody = visibleRows.map(function (row) {
                const cells = Array.from(row.querySelectorAll('td'))
                    .slice(1, 8)
                    .map(function (cell) {
                        return '<td>' + cell.innerHTML + '</td>';
                    })
                    .join('');

                return '<tr>' + cells + '</tr>';
            }).join('');

            const printWindow = window.open('', '_blank', 'width=1200,height=900');
            if (!printWindow) {
                window.alert('Please allow pop-ups to print the table.');
                return;
            }

            const styleMarkup = Array.from(document.querySelectorAll('style, link[rel="stylesheet"]'))
                .map(function (node) {
                    return node.outerHTML;
                })
                .join('');
            const printTitle = <?= json_encode($sectionLabel, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
            const printSubtitle = employeeTableSearch && employeeTableSearch.value.trim() !== ''
                ? 'Filtered by: ' + employeeTableSearch.value.trim()
                : 'Complete employee records list';

            printWindow.document.open();
            printWindow.document.write(`<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${printTitle}</title>
    ${styleMarkup}
    <style>
        body {
            margin: 0;
            padding: 24px;
            background: #ffffff;
            color: #0f172a;
            font-family: Arial, sans-serif;
        }

        .employee-table-print {
            max-width: 1280px;
            margin: 0 auto;
        }

        .employee-table-print-header {
            margin-bottom: 18px;
        }

        .employee-table-print-header h1 {
            margin: 0 0 6px;
            font-size: 24px;
            color: #0f172a;
        }

        .employee-table-print-header p {
            margin: 0;
            color: #475569;
            font-size: 13px;
        }

        .employee-table-print table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .employee-table-print th,
        .employee-table-print td {
            border: 1px solid #cbd5e1;
            padding: 10px 12px;
            font-size: 12px;
            line-height: 1.4;
            text-align: left;
            vertical-align: top;
            word-break: break-word;
        }

        .employee-table-print th {
            background: #eff6ff;
            color: #1e3a8a;
            font-weight: 800;
        }

        @page {
            size: landscape;
            margin: 10mm;
        }

        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="employee-table-print">
        <div class="employee-table-print-header">
            <h1>${printTitle}</h1>
            <p>${printSubtitle}</p>
        </div>
        <table>
            <thead>
                <tr>${tableHeaders}</tr>
            </thead>
            <tbody>${tableBody}</tbody>
        </table>
    </div>
    <script>
        window.addEventListener('load', function () {
            window.print();
            window.onafterprint = function () {
                window.close();
            };
        });
    <\/script>
</body>
</html>`);
            printWindow.document.close();
        }

        function formatLeaveDays(totalDays) {
            return totalDays === 1 ? '1 day' : totalDays + ' days';
        }

        function parseFlexibleLeaveDate(value) {
            const rawValue = value.trim();
            if (!rawValue) {
                return null;
            }

            const isoMatch = rawValue.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
            if (isoMatch) {
                const isoDate = new Date(Number(isoMatch[1]), Number(isoMatch[2]) - 1, Number(isoMatch[3]));
                return Number.isNaN(isoDate.getTime()) ? null : isoDate;
            }

            const slashMatch = rawValue.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
            if (slashMatch) {
                const slashDate = new Date(Number(slashMatch[3]), Number(slashMatch[2]) - 1, Number(slashMatch[1]));
                return Number.isNaN(slashDate.getTime()) ? null : slashDate;
            }

            const normalizedText = rawValue.replace(/\s+/g, ' ').replace(/-/g, ' ');
            const parsedTimestamp = Date.parse(normalizedText);
            if (!Number.isNaN(parsedTimestamp)) {
                const parsedDate = new Date(parsedTimestamp);
                return new Date(parsedDate.getFullYear(), parsedDate.getMonth(), parsedDate.getDate());
            }

            return null;
        }

        function calculateLeaveDays() {
            if (!leaveFromDate.value || !leaveToDate.value) {
                return 0;
            }

            const fromDate = parseFlexibleLeaveDate(leaveFromDate.value);
            const toDate = parseFlexibleLeaveDate(leaveToDate.value);

            if (!fromDate || !toDate) {
                return 0;
            }

            const diffInMs = toDate.getTime() - fromDate.getTime();

            if (Number.isNaN(diffInMs) || diffInMs < 0) {
                return 0;
            }

            return Math.floor(diffInMs / 86400000) + 1;
        }

        function updateLeaveAbsenceFields() {
            const totalDays = calculateLeaveDays();
            const reason = leaveReason.value;

            absenceWithoutPay.value = '';
            absenceWithPay.value = '';

            if (!reason || totalDays <= 0) {
                return;
            }

            const formattedDays = formatLeaveDays(totalDays);
            const normalizedReason = reason.toLowerCase();

            if (normalizedReason.includes('w/out pay')) {
                absenceWithoutPay.value = formattedDays;
                return;
            }

            if (
                normalizedReason.includes('w/pay') ||
                normalizedReason === 'maternity leave'.toLowerCase() ||
                normalizedReason === 'study leave'.toLowerCase() ||
                normalizedReason === 'wellness leave'.toLowerCase() ||
                normalizedReason === 'special privilege leave'.toLowerCase() ||
                normalizedReason === 'forced leave'.toLowerCase()
            ) {
                absenceWithPay.value = formattedDays;
            }
        }

        function openEmployeeModal() {
            employeeModal.classList.add('show');
        }

        function closeEmployeeModal() {
            employeeModal.classList.remove('show');
        }

        leaveFileInput.addEventListener('change', function () {
            if (leaveFileInput.files.length > 0) {
                const fileNames = Array.from(leaveFileInput.files).map(function (file) {
                    return file.name;
                });

                selectedFileName.textContent = leaveFileInput.files.length === 1
                    ? fileNames[0]
                    : leaveFileInput.files.length + ' files selected';
                selectedFileName.title = fileNames.join(', ');
                setUploadIndicatorState('is-ready', leaveFileInput.files.length + ' file(s) ready for upload.');
                return;
            }

            selectedFileName.textContent = 'No file selected';
            selectedFileName.title = '';
            setUploadIndicatorState('is-idle', 'Waiting for file selection.');
        });

        uploadForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const files = Array.from(leaveFileInput.files);
            if (files.length === 0) {
                setUploadIndicatorState('is-idle', 'Please select at least one XLSX file.');
                return;
            }

            if (!uploadGroup.value || !uploadStatus.value) {
                setUploadIndicatorState('is-idle', 'Please select the group and status before uploading.');
                return;
            }

            const batchSize = 10;
            const totalBatches = Math.ceil(files.length / batchSize);
            const allDetails = [];
            let totalSuccess = 0;
            let totalError = 0;

            uploadSubmitBtn.disabled = true;
            uploadGroup.disabled = true;
            uploadStatus.disabled = true;
            leaveFileInput.disabled = true;

            try {
                for (let batchIndex = 0; batchIndex < totalBatches; batchIndex++) {
                    const batchFiles = files.slice(batchIndex * batchSize, (batchIndex + 1) * batchSize);
                    const formData = new FormData();
                    formData.append('form_action', 'upload_employee_record');
                    formData.append('upload_group', uploadGroup.value);
                    formData.append('upload_status', uploadStatus.value);

                    batchFiles.forEach(function (file) {
                        formData.append('employee_file[]', file);
                    });

                    uploadSubmitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading ' + files.length + ' files';
                    setUploadIndicatorState(
                        'is-uploading',
                        'Uploading ' + files.length + ' file(s): batch ' + (batchIndex + 1) + ' of ' + totalBatches + ' (' + batchFiles.length + ' file(s) in this batch).'
                    );

                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    let payload = null;
                    try {
                        payload = await response.json();
                    } catch (jsonError) {
                        payload = null;
                    }

                    if (!response.ok || !payload) {
                        throw new Error('The server could not finish the batch upload.');
                    }

                    const batchDetails = Array.isArray(payload.details) ? payload.details : [];
                    batchDetails.forEach(function (detail) {
                        allDetails.push(detail);
                        if (detail.type === 'error') {
                            totalError++;
                            return;
                        }

                        totalSuccess++;
                    });
                }

                const finalType = totalSuccess > 0 && totalError > 0
                    ? 'warning'
                    : (totalSuccess > 0 ? 'success' : 'error');
                const finalMessage = totalSuccess > 0 && totalError > 0
                    ? totalSuccess + ' file(s) uploaded successfully and ' + totalError + ' file(s) failed.'
                    : (totalSuccess > 0
                        ? totalSuccess + ' XLSX file(s) uploaded and parsed successfully.'
                        : 'Upload failed for all selected XLSX files.');

                renderUploadAlert(finalMessage, finalType, allDetails);
                setUploadIndicatorState('is-ready', 'Upload complete. Refreshing the table...');
                closeUploadModal();
                window.setTimeout(function () {
                    const refreshUrl = new URL(window.location.href);
                    refreshUrl.searchParams.set('_refresh', String(Date.now()));
                    window.location.href = refreshUrl.toString();
                }, 1200);
            } catch (error) {
                renderUploadAlert(
                    error instanceof Error ? error.message : 'The upload could not be completed.',
                    'error',
                    []
                );
                setUploadIndicatorState('is-idle', 'Upload failed. Please try again.');
            } finally {
                uploadSubmitBtn.disabled = false;
                uploadGroup.disabled = false;
                uploadStatus.disabled = false;
                leaveFileInput.disabled = false;
                uploadSubmitBtn.innerHTML = '<i class="fa-solid fa-file-arrow-up"></i> Upload Files';
            }
        });

        deleteRecordForms.forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                openDeleteConfirmModal(form);
            });
        });

        if (selectAllRecords) {
            selectAllRecords.addEventListener('change', function () {
                const shouldCheck = selectAllRecords.checked;
                getVisibleSelectableCheckboxes().forEach(function (checkbox) {
                    checkbox.checked = shouldCheck;
                });
                updateBulkSelectionState();
            });
        }

        recordSelectCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                updateBulkSelectionState();
                syncBulkDeleteInputs();
            });
        });

        if (bulkDeleteForm) {
            bulkDeleteForm.addEventListener('submit', function (event) {
                const selectedCount = recordSelectCheckboxes.filter(function (checkbox) {
                    return checkbox.checked;
                }).length;

                if (selectedCount === 0) {
                    event.preventDefault();
                    return;
                }

                event.preventDefault();
                syncBulkDeleteInputs();
                openBulkDeleteConfirmModal();
            });
        }

        confirmDeleteBtn.addEventListener('click', function () {
            if (!pendingDeleteForm) {
                return;
            }

            confirmDeleteBtn.disabled = true;
            confirmDeleteBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Deleting...';
            pendingDeleteForm.submit();
        });

        confirmBulkDeleteBtn.addEventListener('click', function () {
            if (!isBulkDeletePending || !bulkDeleteForm) {
                return;
            }

            confirmBulkDeleteBtn.disabled = true;
            confirmBulkDeleteBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Deleting...';
            bulkDeleteForm.submit();
        });

        confirmLeaveDeleteBtn.addEventListener('click', function () {
            if (!pendingLeaveDeleteRow) {
                return;
            }

            pendingLeaveDeleteRow.remove();
            refreshLeaveRowActions();
            closeLeaveDeleteConfirmModal();
        });

        if (employeeTableSearch) {
            employeeTableSearch.addEventListener('input', filterEmployeeTable);
        }

        leaveReason.addEventListener('change', function () {
            const isOther = leaveReason.value === 'Others';
            otherReasonField.style.display = isOther ? 'flex' : 'none';
            leaveOtherReason.required = isOther;

            if (!isOther) {
                leaveOtherReason.value = '';
            }

            updateLeaveAbsenceFields();
        });

        leaveEntryForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const previewTableBody = imagePreviewContent.querySelector('.leave-table-preview tbody');
            if (!previewTableBody) {
                closeLeaveModal();
                return;
            }

            const leaveData = {
                from: leaveFromDate.value.trim(),
                to: leaveToDate.value.trim(),
                reason: leaveReason.value === 'Others' ? leaveOtherReason.value.trim() : leaveReason.value.trim(),
                station: leaveStation.value.trim(),
                withoutPay: absenceWithoutPay.value.trim(),
                withPay: absenceWithPay.value.trim(),
                remarks: leaveRemarks.value.trim()
            };

            const rowMarkup = `<tr>
                <td>${escapeHtml(leaveData.from)}</td>
                <td>${escapeHtml(leaveData.to)}</td>
                <td>${escapeHtml(leaveData.reason)}</td>
                <td>${escapeHtml(leaveData.station)}</td>
                <td>${escapeHtml(leaveData.withoutPay)}</td>
                <td>${escapeHtml(leaveData.withPay)}</td>
                <td>${escapeHtml(leaveData.remarks)}</td>
                ${buildLeaveActionCell(leaveEditIndex.value || previewTableBody.querySelectorAll('tr').length, leaveData)}
            </tr>`;

            const targetIndex = leaveEditIndex.value === '' ? -1 : Number(leaveEditIndex.value);
            if (targetIndex >= 0) {
                const targetRow = previewTableBody.querySelectorAll('tr')[targetIndex];
                if (targetRow) {
                    targetRow.outerHTML = rowMarkup;
                }
            } else {
                previewTableBody.insertAdjacentHTML('beforeend', rowMarkup);
            }

            refreshLeaveRowActions();
            closeLeaveModal();
        });

        leaveFromDate.addEventListener('change', updateLeaveAbsenceFields);
        leaveToDate.addEventListener('change', updateLeaveAbsenceFields);

        uploadModal.addEventListener('click', function (event) {
            if (event.target === uploadModal) {
                closeUploadModal();
            }
        });

        imageModal.addEventListener('click', function (event) {
            if (event.target === imageModal) {
                closeImageModal();
            }
        });

        leaveModal.addEventListener('click', function (event) {
            if (event.target === leaveModal) {
                closeLeaveModal();
            }
        });

        editEmployeeModal.addEventListener('click', function (event) {
            if (event.target === editEmployeeModal) {
                closeEditEmployeeModal();
            }
        });

        employeeModal.addEventListener('click', function (event) {
            if (event.target === employeeModal) {
                closeEmployeeModal();
            }
        });

        confirmDeleteModal.addEventListener('click', function (event) {
            if (event.target === confirmDeleteModal) {
                closeDeleteConfirmModal();
            }
        });

        confirmBulkDeleteModal.addEventListener('click', function (event) {
            if (event.target === confirmBulkDeleteModal) {
                closeBulkDeleteConfirmModal();
            }
        });

        confirmLeaveDeleteModal.addEventListener('click', function (event) {
            if (event.target === confirmLeaveDeleteModal) {
                closeLeaveDeleteConfirmModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && uploadModal.classList.contains('show')) {
                closeUploadModal();
            }

            if (event.key === 'Escape' && imageModal.classList.contains('show')) {
                closeImageModal();
            }

            if (event.key === 'Escape' && leaveModal.classList.contains('show')) {
                closeLeaveModal();
            }

            if (event.key === 'Escape' && editEmployeeModal.classList.contains('show')) {
                closeEditEmployeeModal();
            }

            if (event.key === 'Escape' && employeeModal.classList.contains('show')) {
                closeEmployeeModal();
            }

            if (event.key === 'Escape' && confirmDeleteModal.classList.contains('show')) {
                closeDeleteConfirmModal();
            }

            if (event.key === 'Escape' && confirmBulkDeleteModal.classList.contains('show')) {
                closeBulkDeleteConfirmModal();
            }

            if (event.key === 'Escape' && confirmLeaveDeleteModal.classList.contains('show')) {
                closeLeaveDeleteConfirmModal();
            }
        });

        if (alertBanner) {
            const alertHideDelay = alertBanner.querySelector('.upload-result-list') ? 8000 : 2500;

            window.setTimeout(function () {
                alertBanner.classList.add('is-hiding');

                window.setTimeout(function () {
                    alertBanner.remove();
                }, 400);
            }, alertHideDelay);
        }

        setUploadIndicatorState('is-idle', 'Waiting for file selection.');
        filterEmployeeTable();
        updateBulkSelectionState();
        syncBulkDeleteInputs();
    </script>
</body>
</html>
