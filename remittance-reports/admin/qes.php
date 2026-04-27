<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/includes/pdf-remittance-service.php';

$userRole = $_SESSION['role'] ?? 'staff';
$normalizedUserRole = strtolower(trim($userRole));
$profileInitial = $normalizedUserRole === 'super admin'
    ? 'SA'
    : strtoupper(substr(trim($userRole), 0, 1));
$roleClass = $normalizedUserRole === 'super admin' ? 'role-super-admin' : '';

$pageTitle = 'QES Remittance Reports';
$activePage = 'qes';
$section = 'qes';

[$uploadMessage, $uploadMessageType] = remittanceHandlePdfUpload($section);
$reports = remittanceFetchStoredReportSummaries($section);
$availableYears = [];
foreach ($reports as $report) {
    $applicablePeriod = (string) (($report['header']['applicable_period'] ?? ''));
    if (preg_match('/(\d{4})/', $applicablePeriod, $matches)) {
        $availableYears[$matches[1]] = true;
    }
}
$availableYears = array_keys($availableYears);
rsort($availableYears, SORT_STRING);
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<body>
    <div class="layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="content">
            <?php include __DIR__ . '/includes/navbar.php'; ?>

            <div class="content-body">
                <section class="section">
                    <div class="panel report-upload-panel">
                        <div class="section-header report-upload-header">
                            <h3>Upload QES PDF</h3>
                            <button type="button" class="upload-action-btn" id="openUploadModalBtn">
                                Upload PDF
                            </button>
                        </div>

                        <?php if ($uploadMessage !== ''): ?>
                            <div class="alert-banner alert-<?= htmlspecialchars($uploadMessageType, ENT_QUOTES, 'UTF-8') ?>">
                                <div class="alert-banner-title"><?= htmlspecialchars($uploadMessage, ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="section">
                    <div class="panel report-table-panel">
                        <div class="section-header">
                            <h3>Uploaded QES Reports</h3>
                            <?php if ($reports !== []): ?>
                                <button type="button" class="bulk-delete-btn" id="openBulkDeleteBtn" disabled>
                                    <i class="fa-solid fa-trash-can"></i>
                                    Delete Selected
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="report-filter-bar">
                            <label class="report-filter-field report-filter-search">
                                <span>Search report</span>
                                <input type="search" id="reportSearchInput" placeholder="Search file, employer, group, PhilHealth no., or report type">
                            </label>

                            <label class="report-filter-field">
                                <span>Year</span>
                                <select id="reportYearFilter">
                                    <option value="">All years</option>
                                    <?php foreach ($availableYears as $yearOption): ?>
                                        <option value="<?= htmlspecialchars($yearOption, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($yearOption, ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>

                        <div class="report-table-shell">
                            <table class="table report-summary-table">
                                <thead>
                                    <tr>
                                        <th class="selection-cell">
                                            <input type="checkbox" id="selectAllReports" aria-label="Select all reports">
                                        </th>
                                        <th>PDF File</th>
                                        <th>Uploaded By</th>
                                        <th>Applicable Period</th>
                                        <th>Employer Name</th>
                                        <th>Group</th>
                                        <th>PhilHealth No.</th>
                                        <th>Report Type</th>
                                        <th class="employees-header-cell">Employees</th>
                                        <th class="action-cell">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($reports === []): ?>
                                        <tr>
                                            <td colspan="10" class="empty-state-cell">No uploaded QES PDF reports yet.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($reports as $index => $report): ?>
                                            <?php $header = $report['header'] ?? []; ?>
                                            <?php
                                            $storedFileName = (string) ($report['stored_file_name'] ?? '');
                                            $reportSearchIndex = strtolower(implode(' ', [
                                                (string) ($report['file_name'] ?? ''),
                                                (string) ($header['applicable_period'] ?? ''),
                                                (string) ($header['employer_name'] ?? ''),
                                                (string) ($header['group_name'] ?? ''),
                                                (string) ($header['philhealth_number'] ?? ''),
                                                (string) ($header['report_type'] ?? ''),
                                            ]));
                                            $reportYear = '';
                                            if (preg_match('/(\d{4})/', (string) ($header['applicable_period'] ?? ''), $matches)) {
                                                $reportYear = $matches[1];
                                            }
                                            $uploadedBy = trim((string) ($report['uploaded_by'] ?? 'Unknown uploader'));
                                            $uploadedAt = trim((string) ($report['uploaded_at'] ?? ''));
                                            $uploadedTime = $uploadedAt !== '' ? date('g:i A', strtotime($uploadedAt) ?: time()) : '-';
                                            ?>
                                            <tr>
                                                <td class="selection-cell">
                                                    <input type="checkbox" class="report-row-checkbox" form="bulkDeleteForm" name="report_files[]" value="<?= htmlspecialchars($storedFileName, ENT_QUOTES, 'UTF-8') ?>" aria-label="Select <?= htmlspecialchars((string) ($report['file_name'] ?? 'report'), ENT_QUOTES, 'UTF-8') ?>">
                                                </td>
                                                <td class="report-data-row report-file-cell" data-search="<?= htmlspecialchars($reportSearchIndex, ENT_QUOTES, 'UTF-8') ?>" data-year="<?= htmlspecialchars($reportYear, ENT_QUOTES, 'UTF-8') ?>">
                                                    <strong><?= htmlspecialchars((string) ($report['file_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong>
                                                </td>
                                                <td class="report-uploaded-by-cell">
                                                    <strong><?= htmlspecialchars($uploadedBy, ENT_QUOTES, 'UTF-8') ?></strong>
                                                    <span><?= htmlspecialchars($uploadedTime, ENT_QUOTES, 'UTF-8') ?></span>
                                                </td>
                                                <td><?= htmlspecialchars((string) ($header['applicable_period'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string) ($header['employer_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string) ($header['group_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string) ($header['philhealth_number'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string) ($header['report_type'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= number_format((int) ($report['employee_count'] ?? 0)) ?></td>
                                                <td class="action-cell">
                                                    <div class="report-action-group">
                                                        <a
                                                            class="table-toggle-btn report-link-btn report-icon-btn"
                                                            href="report-employees.php?section=qes&amp;file=<?= rawurlencode($storedFileName) ?>"
                                                            title="View employees"
                                                            aria-label="View employees">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </a>
                                                        <form method="POST" class="report-delete-form">
                                                            <input type="hidden" name="form_action" value="delete_pdf_report">
                                                            <input type="hidden" name="report_section" value="qes">
                                                            <input type="hidden" name="report_file" value="<?= htmlspecialchars($storedFileName, ENT_QUOTES, 'UTF-8') ?>">
                                                            <button type="button" class="report-delete-btn report-icon-btn report-delete-trigger" title="Delete report" aria-label="Delete report" data-report-file="<?= htmlspecialchars($storedFileName, ENT_QUOTES, 'UTF-8') ?>">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <form method="POST" id="bulkDeleteForm">
                            <input type="hidden" name="form_action" value="delete_selected_pdf_reports">
                            <input type="hidden" name="report_section" value="qes">
                        </form>
                    </div>
                </section>
            </div>

            <?php include __DIR__ . '/includes/footer.php'; ?>
        </main>
    </div>

    <div class="report-upload-modal" id="reportUploadModal" hidden>
        <div class="report-upload-backdrop" data-close-upload-modal></div>
        <div class="report-upload-dialog" role="dialog" aria-modal="true" aria-labelledby="uploadModalTitle">
            <div class="report-upload-dialog-head">
                <div>
                    <span class="upload-modal-kicker">PDF Upload</span>
                    <h3 id="uploadModalTitle">Upload QES Remittance PDF</h3>
                    <p>Select a PDF file to extract the report header and employee listing.</p>
                </div>
                <button type="button" class="upload-modal-close" data-close-upload-modal aria-label="Close upload modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" enctype="multipart/form-data" class="report-upload-form report-upload-form-modal">
                <input type="hidden" name="form_action" value="upload_pdf_report">
                <input type="hidden" name="report_section" value="qes">

                <label for="qesReportPdf" class="upload-dropzone" id="uploadDropzone">
                    <input type="file" id="qesReportPdf" name="report_pdf[]" accept=".pdf,application/pdf" class="upload-file-input" multiple required>
                    <span class="upload-dropzone-icon"><i class="fa-solid fa-file-pdf"></i></span>
                    <span class="upload-dropzone-title">Choose your remittance PDF files</span>
                    <span class="upload-dropzone-copy">You can select multiple PDF files in one upload. Click here to browse for files.</span>
                    <span class="upload-file-name" id="uploadFileName">No PDF selected yet.</span>
                </label>

                <div class="upload-status-indicator" id="uploadStatusIndicator">
                    <span class="upload-status-dot"></span>
                    <span id="uploadStatusText">Waiting for PDF selection.</span>
                </div>

                <div class="upload-modal-actions">
                    <button type="button" class="upload-secondary-btn" data-close-upload-modal>Cancel</button>
                    <button type="submit" class="upload-action-btn" id="uploadSubmitBtn">Upload PDF</button>
                </div>
            </form>
        </div>
    </div>

    <div class="report-delete-modal" id="reportDeleteModal" hidden>
        <div class="report-delete-backdrop" data-close-delete-modal></div>
        <div class="report-delete-dialog" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
            <div class="report-delete-dialog-head">
                <div>
                    <span class="delete-modal-kicker">Delete Report</span>
                    <h3 id="deleteModalTitle">Delete uploaded PDF report?</h3>
                    <p>This action will permanently remove the selected PDF report and its extracted metadata.</p>
                </div>
                <button type="button" class="delete-modal-close" data-close-delete-modal aria-label="Close delete modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="report-delete-dialog-body">
                <div class="delete-report-preview">
                    <strong>Selected file</strong>
                    <span id="deleteReportFileName">-</span>
                </div>

                <div class="delete-modal-actions">
                    <button type="button" class="upload-secondary-btn" data-close-delete-modal>Cancel</button>
                    <button type="button" class="delete-confirm-btn" id="confirmDeleteBtn">
                        <i class="fa-solid fa-trash"></i>
                        Delete Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="report-delete-modal" id="bulkDeleteModal" hidden>
        <div class="report-delete-backdrop" data-close-bulk-delete-modal></div>
        <div class="report-delete-dialog" role="dialog" aria-modal="true" aria-labelledby="bulkDeleteModalTitle">
            <div class="report-delete-dialog-head">
                <div>
                    <span class="delete-modal-kicker">Bulk Delete</span>
                    <h3 id="bulkDeleteModalTitle">Delete selected PDF reports?</h3>
                    <p>The selected reports and their extracted metadata will be permanently removed.</p>
                </div>
                <button type="button" class="delete-modal-close" data-close-bulk-delete-modal aria-label="Close bulk delete modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="report-delete-dialog-body">
                <div class="delete-report-preview">
                    <strong>Selected reports</strong>
                    <span id="bulkDeleteCount">0 reports selected</span>
                </div>

                <div class="delete-modal-actions">
                    <button type="button" class="upload-secondary-btn" data-close-bulk-delete-modal>Cancel</button>
                    <button type="button" class="delete-confirm-btn" id="confirmBulkDeleteBtn">
                        <i class="fa-solid fa-trash"></i>
                        Delete Selected
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .report-upload-header {
            align-items: center;
        }

        .upload-action-btn,
        .table-toggle-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            border: none;
            border-radius: 12px;
            padding: 10px 16px;
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 14px 24px rgba(37, 99, 235, 0.18);
        }

        .report-link-btn {
            text-decoration: none;
        }

        .report-filter-bar {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(180px, 240px);
            gap: 14px;
            margin-bottom: 18px;
        }

        .report-filter-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .report-filter-field span {
            font-size: 13px;
            font-weight: 700;
            color: #334155;
        }

        .report-filter-field input,
        .report-filter-field select {
            min-height: 46px;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            padding: 12px 14px;
            font-size: 14px;
            color: #0f172a;
            background: #ffffff;
        }

        .report-file-cell {
            text-align: left;
        }

        .report-file-cell strong {
            display: block;
            color: #0f172a;
            font-size: 14px;
            line-height: 1.5;
            word-break: break-word;
        }

        .report-uploaded-by-cell {
            text-align: left;
        }

        .report-uploaded-by-cell strong {
            display: block;
            color: #0f172a;
            font-size: 14px;
            line-height: 1.5;
            word-break: break-word;
        }

        .report-uploaded-by-cell span {
            display: block;
            margin-top: 4px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.4;
        }

        .bulk-delete-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            padding: 10px 16px;
            border: 1px solid #fecaca;
            border-radius: 12px;
            background: #fff1f2;
            color: #b91c1c;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .bulk-delete-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .upload-action-btn:hover,
        .table-toggle-btn:hover {
            transform: translateY(-1px);
        }

        .report-upload-modal[hidden] {
            display: none;
        }

        .report-delete-modal[hidden] {
            display: none;
        }

        .report-upload-modal {
            position: fixed;
            inset: 0;
            z-index: 1200;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .report-delete-modal {
            position: fixed;
            inset: 0;
            z-index: 1250;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .report-upload-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.58);
            backdrop-filter: blur(3px);
        }

        .report-delete-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.64);
            backdrop-filter: blur(4px);
        }

        .report-upload-dialog {
            position: relative;
            width: min(640px, calc(100vw - 32px));
            border-radius: 28px;
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.16), transparent 30%),
                linear-gradient(180deg, #ffffff, #f8fbff);
            border: 1px solid #dbeafe;
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.28);
            overflow: hidden;
        }

        .report-delete-dialog {
            position: relative;
            width: min(520px, calc(100vw - 32px));
            border-radius: 28px;
            background:
                radial-gradient(circle at top right, rgba(239, 68, 68, 0.14), transparent 32%),
                linear-gradient(180deg, #ffffff, #fff7f7);
            border: 1px solid #fecaca;
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.32);
            overflow: hidden;
        }

        .report-upload-dialog-head {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 24px 24px 18px;
            border-bottom: 1px solid #dbeafe;
        }

        .report-delete-dialog-head {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 24px 24px 18px;
            border-bottom: 1px solid #fecaca;
        }

        .upload-modal-kicker {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .delete-modal-kicker {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            background: #fee2e2;
            color: #b91c1c;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .report-upload-dialog-head h3 {
            margin: 0 0 8px;
            font-size: 24px;
            color: #0f172a;
        }

        .report-delete-dialog-head h3 {
            margin: 0 0 8px;
            font-size: 24px;
            color: #0f172a;
        }

        .report-upload-dialog-head p {
            margin: 0;
            color: #64748b;
            line-height: 1.6;
        }

        .report-delete-dialog-head p {
            margin: 0;
            color: #64748b;
            line-height: 1.6;
        }

        .upload-modal-close {
            width: 44px;
            height: 44px;
            border: 1px solid #dbeafe;
            border-radius: 14px;
            background: #ffffff;
            color: #1d4ed8;
            cursor: pointer;
            flex-shrink: 0;
        }

        .delete-modal-close {
            width: 44px;
            height: 44px;
            border: 1px solid #fecaca;
            border-radius: 14px;
            background: #ffffff;
            color: #dc2626;
            cursor: pointer;
            flex-shrink: 0;
        }

        .report-upload-form-modal {
            padding: 24px;
        }

        .report-delete-dialog-body {
            padding: 24px;
        }

        .upload-dropzone {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 28px 24px;
            border: 2px dashed #93c5fd;
            border-radius: 24px;
            background: linear-gradient(180deg, #eff6ff, #ffffff);
            text-align: center;
            cursor: pointer;
        }

        .upload-dropzone.is-ready {
            border-color: #2563eb;
            background: linear-gradient(180deg, #dbeafe, #eff6ff);
        }

        .upload-file-input {
            display: none;
        }

        .upload-dropzone-icon {
            width: 72px;
            height: 72px;
            border-radius: 22px;
            background: linear-gradient(135deg, #1d4ed8, #60a5fa);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            box-shadow: 0 16px 30px rgba(37, 99, 235, 0.22);
        }

        .upload-dropzone-title {
            font-size: 19px;
            font-weight: 800;
            color: #0f172a;
        }

        .upload-dropzone-copy {
            font-size: 14px;
            color: #64748b;
            max-width: 420px;
            line-height: 1.6;
        }

        .upload-file-name {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: #ffffff;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 700;
            border: 1px solid #bfdbfe;
        }

        .upload-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 18px;
        }

        .delete-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
        }

        .upload-status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 16px;
            padding: 12px 14px;
            border-radius: 14px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 700;
        }

        .upload-status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #93c5fd;
            box-shadow: 0 0 0 6px rgba(147, 197, 253, 0.18);
        }

        .upload-status-indicator.is-uploading .upload-status-dot {
            background: #2563eb;
            box-shadow: 0 0 0 6px rgba(37, 99, 235, 0.18);
            animation: uploadPulse 1s ease-in-out infinite;
        }

        .upload-status-indicator.is-ready .upload-status-dot {
            background: #16a34a;
            box-shadow: 0 0 0 6px rgba(22, 163, 74, 0.16);
        }

        .upload-secondary-btn {
            min-height: 44px;
            padding: 10px 16px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0f172a;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .delete-confirm-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            padding: 10px 16px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 14px 24px rgba(220, 38, 38, 0.18);
        }

        @keyframes uploadPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.18); opacity: 0.8; }
        }

        .report-table-shell,
        .employee-table-shell {
            overflow-x: hidden;
            width: 100%;
        }

        .selection-cell {
            width: 52px;
            text-align: center;
        }

        .selection-cell input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #1d4ed8;
            cursor: pointer;
        }

        .report-action-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
            justify-content: center;
            width: 100%;
        }

        .action-cell {
            width: 96px;
            text-align: center;
            vertical-align: middle;
        }

        .report-delete-form {
            margin: 0;
        }

        .report-delete-btn {
            min-height: 36px;
            border: none;
            border-radius: 10px;
            padding: 0;
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 14px 24px rgba(220, 38, 38, 0.18);
        }

        .report-icon-btn {
            width: 36px;
            min-width: 36px;
            height: 36px;
            padding: 0;
        }

        .report-icon-btn i {
            font-size: 13px;
        }

        .delete-report-preview {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 14px 16px;
            border: 1px solid #fecaca;
            border-radius: 16px;
            background: #fff1f2;
        }

        .delete-report-preview strong {
            font-size: 12px;
            color: #b91c1c;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .delete-report-preview span {
            color: #0f172a;
            line-height: 1.5;
            word-break: break-word;
        }

        .report-summary-table {
            width: 100%;
            table-layout: fixed;
        }

        .report-summary-table th,
        .report-summary-table td {
            padding: 14px 12px;
            vertical-align: middle;
            font-size: 13px;
            line-height: 1.4;
            overflow-wrap: anywhere;
            word-break: break-word;
            text-align: center;
        }

        .employees-header-cell {
            font-size: 11px;
            letter-spacing: 0.01em;
            white-space: nowrap;
        }

        .report-summary-table th:nth-child(1),
        .report-summary-table td:nth-child(1) {
            width: 5%;
            vertical-align: middle;
        }

        .report-summary-table th:nth-child(2),
        .report-summary-table td:nth-child(2) {
            width: 18%;
        }

        .report-summary-table th:nth-child(3),
        .report-summary-table td:nth-child(3) {
            width: 12%;
        }

        .report-summary-table th:nth-child(4),
        .report-summary-table td:nth-child(4) {
            width: 11%;
        }

        .report-summary-table th:nth-child(5),
        .report-summary-table td:nth-child(5) {
            width: 14%;
        }

        .report-summary-table th:nth-child(6),
        .report-summary-table td:nth-child(6) {
            width: 12%;
        }

        .report-summary-table th:nth-child(7),
        .report-summary-table td:nth-child(7) {
            width: 10%;
        }

        .report-summary-table th:nth-child(8),
        .report-summary-table td:nth-child(8) {
            width: 10%;
        }

        .report-summary-table th:nth-child(9),
        .report-summary-table td:nth-child(9) {
            width: 6%;
            vertical-align: middle;
        }

        .report-summary-table th:nth-child(10),
        .report-summary-table td:nth-child(10) {
            width: 8%;
            vertical-align: middle;
        }

        @media (max-width: 1180px) {
            .report-summary-table th,
            .report-summary-table td {
                padding: 12px 8px;
                font-size: 12px;
            }

            .report-file-cell strong,
            .report-uploaded-by-cell strong {
                font-size: 12px;
            }

            .report-file-cell span,
            .report-uploaded-by-cell span {
                font-size: 11px;
            }

            .action-cell {
                width: 84px;
            }

            .report-icon-btn {
                width: 32px;
                min-width: 32px;
                height: 32px;
                min-height: 32px;
            }
        }

        @media (max-width: 860px) {
            .report-detail-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .report-upload-dialog-head {
                flex-direction: column;
            }

            .upload-modal-actions {
                flex-direction: column-reverse;
            }

            .upload-modal-actions button,
            .delete-modal-actions button {
                width: 100%;
            }

            .delete-modal-actions {
                flex-direction: column-reverse;
            }
        }
    </style>

    <script>
        const uploadModal = document.getElementById('reportUploadModal');
        const deleteModal = document.getElementById('reportDeleteModal');
        const bulkDeleteModal = document.getElementById('bulkDeleteModal');
        const openUploadModalBtn = document.getElementById('openUploadModalBtn');
        const closeUploadModalButtons = document.querySelectorAll('[data-close-upload-modal]');
        const closeDeleteModalButtons = document.querySelectorAll('[data-close-delete-modal]');
        const closeBulkDeleteModalButtons = document.querySelectorAll('[data-close-bulk-delete-modal]');
        const deleteTriggers = document.querySelectorAll('.report-delete-trigger');
        const deleteReportFileName = document.getElementById('deleteReportFileName');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');
        const openBulkDeleteBtn = document.getElementById('openBulkDeleteBtn');
        const confirmBulkDeleteBtn = document.getElementById('confirmBulkDeleteBtn');
        const bulkDeleteCount = document.getElementById('bulkDeleteCount');
        const selectAllReports = document.getElementById('selectAllReports');
        const rowCheckboxes = document.querySelectorAll('.report-row-checkbox');
        const reportSearchInput = document.getElementById('reportSearchInput');
        const reportYearFilter = document.getElementById('reportYearFilter');
        const reportRows = Array.from(document.querySelectorAll('.report-summary-table tbody tr')).filter(function (row) {
            return row.querySelector('.report-data-row');
        });
        const uploadFileInput = document.getElementById('qesReportPdf');
        const uploadDropzone = document.getElementById('uploadDropzone');
        const uploadFileName = document.getElementById('uploadFileName');
        const uploadStatusIndicator = document.getElementById('uploadStatusIndicator');
        const uploadStatusText = document.getElementById('uploadStatusText');
        const uploadSubmitBtn = document.getElementById('uploadSubmitBtn');
        const uploadForm = document.querySelector('.report-upload-form-modal');
        let activeDeleteForm = null;

        function openUploadModal() {
            uploadModal.hidden = false;
            document.body.style.overflow = 'hidden';
        }

        function closeUploadModal() {
            uploadModal.hidden = true;
            document.body.style.overflow = '';
        }

        function openDeleteModal(form, fileName) {
            activeDeleteForm = form;
            deleteReportFileName.textContent = fileName || '-';
            deleteModal.hidden = false;
            document.body.style.overflow = 'hidden';
            confirmDeleteBtn.focus();
        }

        function closeDeleteModal() {
            deleteModal.hidden = true;
            document.body.style.overflow = '';
            activeDeleteForm = null;
        }

        function updateBulkDeleteState() {
            if (!openBulkDeleteBtn) {
                return;
            }

            const selectedCount = Array.from(rowCheckboxes).filter(function (checkbox) {
                return checkbox.checked;
            }).length;

            openBulkDeleteBtn.disabled = selectedCount === 0;
            if (bulkDeleteCount) {
                bulkDeleteCount.textContent = selectedCount + (selectedCount === 1 ? ' report selected' : ' reports selected');
            }

            if (selectAllReports) {
                selectAllReports.checked = selectedCount > 0 && selectedCount === rowCheckboxes.length;
                selectAllReports.indeterminate = selectedCount > 0 && selectedCount < rowCheckboxes.length;
            }
        }

        function filterReportRows() {
            const searchValue = reportSearchInput ? reportSearchInput.value.trim().toLowerCase() : '';
            const yearValue = reportYearFilter ? reportYearFilter.value.trim() : '';

            reportRows.forEach(function (row) {
                const searchableCell = row.querySelector('.report-data-row');
                if (!searchableCell) {
                    return;
                }

                const matchesSearch = searchValue === '' || (searchableCell.dataset.search || '').includes(searchValue);
                const matchesYear = yearValue === '' || (searchableCell.dataset.year || '') === yearValue;
                row.hidden = !(matchesSearch && matchesYear);
            });
        }

        function openBulkDeleteModal() {
            updateBulkDeleteState();
            bulkDeleteModal.hidden = false;
            document.body.style.overflow = 'hidden';
            confirmBulkDeleteBtn.focus();
        }

        function closeBulkDeleteModal() {
            bulkDeleteModal.hidden = true;
            document.body.style.overflow = '';
        }

        openUploadModalBtn.addEventListener('click', openUploadModal);
        closeUploadModalButtons.forEach(function (button) {
            button.addEventListener('click', closeUploadModal);
        });
        closeDeleteModalButtons.forEach(function (button) {
            button.addEventListener('click', closeDeleteModal);
        });
        closeBulkDeleteModalButtons.forEach(function (button) {
            button.addEventListener('click', closeBulkDeleteModal);
        });

        deleteTriggers.forEach(function (button) {
            button.addEventListener('click', function () {
                const form = button.closest('.report-delete-form');
                openDeleteModal(form, button.dataset.reportFile || '');
            });
        });

        confirmDeleteBtn.addEventListener('click', function () {
            if (activeDeleteForm) {
                activeDeleteForm.submit();
            }
        });

        if (selectAllReports) {
            selectAllReports.addEventListener('change', function () {
                rowCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = selectAllReports.checked;
                });
                updateBulkDeleteState();
            });
        }

        rowCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', updateBulkDeleteState);
        });

        if (reportSearchInput) {
            reportSearchInput.addEventListener('input', filterReportRows);
        }

        if (reportYearFilter) {
            reportYearFilter.addEventListener('change', filterReportRows);
        }

        if (openBulkDeleteBtn) {
            openBulkDeleteBtn.addEventListener('click', openBulkDeleteModal);
        }

        if (confirmBulkDeleteBtn) {
            confirmBulkDeleteBtn.addEventListener('click', function () {
                if (bulkDeleteForm) {
                    bulkDeleteForm.submit();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !uploadModal.hidden) {
                closeUploadModal();
            }
            if (event.key === 'Escape' && deleteModal && !deleteModal.hidden) {
                closeDeleteModal();
            }
            if (event.key === 'Escape' && bulkDeleteModal && !bulkDeleteModal.hidden) {
                closeBulkDeleteModal();
            }
        });

        uploadFileInput.addEventListener('change', function () {
            const files = uploadFileInput.files ? Array.from(uploadFileInput.files) : [];
            if (files.length > 0) {
                uploadFileName.textContent = files.length === 1 ? files[0].name : files.length + ' PDF files selected';
                uploadDropzone.classList.add('is-ready');
                uploadStatusIndicator.classList.remove('is-uploading');
                uploadStatusIndicator.classList.add('is-ready');
                uploadStatusText.textContent = files.length === 1 ? '1 file ready to upload.' : files.length + ' files ready to upload.';
            } else {
                uploadFileName.textContent = 'No PDF selected yet.';
                uploadDropzone.classList.remove('is-ready');
                uploadStatusIndicator.classList.remove('is-ready', 'is-uploading');
                uploadStatusText.textContent = 'Waiting for PDF selection.';
            }
        });

        uploadForm.addEventListener('submit', function () {
            const files = uploadFileInput.files ? Array.from(uploadFileInput.files) : [];
            uploadStatusIndicator.classList.remove('is-ready');
            uploadStatusIndicator.classList.add('is-uploading');
            uploadStatusText.textContent = 'Uploading ' + files.length + ' file(s), please wait...';
            uploadSubmitBtn.disabled = true;
            uploadSubmitBtn.textContent = 'Uploading...';
        });

        document.querySelectorAll('.alert-banner.alert-success').forEach(function (alert) {
            window.setTimeout(function () {
                alert.classList.add('is-hiding');
            }, 2600);
        });

        updateBulkDeleteState();
        filterReportRows();

    </script>
</body>
</html>
