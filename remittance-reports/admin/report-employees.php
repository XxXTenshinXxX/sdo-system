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

$section = trim((string) ($_GET['section'] ?? ''));
$fileName = trim((string) ($_GET['file'] ?? ''));

if (!in_array($section, ['es-shs', 'qes'], true)) {
    header('Location: dashboard.php');
    exit;
}

try {
    $report = remittanceFetchSingleReport($section, $fileName);
} catch (Throwable $exception) {
    remittanceSetFlash($exception->getMessage(), 'error');
    header('Location: ' . ($section === 'es-shs' ? 'es-shs.php' : 'qes.php'));
    exit;
}

$header = $report['header'] ?? [];
$employees = $report['employees'] ?? [];

$searchTerm = trim((string) ($_GET['search'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? ''));
$allowedStatuses = ['A', 'NE'];
if ($statusFilter !== '' && !in_array(strtoupper($statusFilter), $allowedStatuses, true)) {
    $statusFilter = '';
}

$filteredEmployees = array_values(array_filter($employees, static function (array $employee) use ($searchTerm, $statusFilter): bool {
    if ($statusFilter !== '') {
        $employeeStatus = trim((string) ($employee['status'] ?? ''));
        if (strcasecmp($employeeStatus, $statusFilter) !== 0) {
            return false;
        }
    }

    if ($searchTerm === '') {
        return true;
    }

    $searchableFields = [
        (string) ($employee['row_no'] ?? ''),
        (string) ($employee['philhealth_no'] ?? ''),
        (string) ($employee['surname'] ?? ''),
        (string) ($employee['given_name'] ?? ''),
        (string) ($employee['middle_name'] ?? ''),
        (string) ($employee['ps'] ?? ''),
        (string) ($employee['es'] ?? ''),
        (string) ($employee['status'] ?? ''),
    ];

    $haystack = strtolower(implode(' ', $searchableFields));
    return str_contains($haystack, strtolower($searchTerm));
}));

$activeFilterCount = 0;
if ($searchTerm !== '') {
    $activeFilterCount++;
}
if ($statusFilter !== '') {
    $activeFilterCount++;
}
$hasFilteredResults = $searchTerm !== '' || $statusFilter !== '';
$pageTitle = remittanceSectionLabel($section) . ' Employees';
$activePage = $section;
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<body>
    <div class="layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="content">
            <?php include __DIR__ . '/includes/navbar.php'; ?>

            <div class="content-body">
                <section class="section">
                    <div class="panel">
                        <div class="section-header">
                            <h3>Report Details</h3>
                            <a href="<?= htmlspecialchars($section === 'es-shs' ? 'es-shs.php' : 'qes.php', ENT_QUOTES, 'UTF-8') ?>">Back to <?= htmlspecialchars(remittanceSectionLabel($section), ENT_QUOTES, 'UTF-8') ?></a>
                        </div>

                        <div class="report-detail-grid">
                            <div><strong>PDF File</strong><span><?= htmlspecialchars((string) ($report['file_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div><strong>Employer Name</strong><span><?= htmlspecialchars((string) ($header['employer_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div><strong>Group Name</strong><span><?= htmlspecialchars((string) ($header['group_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div><strong>PhilHealth Number</strong><span><?= htmlspecialchars((string) ($header['philhealth_number'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div><strong>Document Control No.</strong><span><?= htmlspecialchars((string) ($header['document_control_number'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div><strong>Report Type</strong><span><?= htmlspecialchars((string) ($header['report_type'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                        </div>
                    </div>
                </section>

                <section class="section">
                    <div class="panel">
                        <div class="section-header">
                            <h3>Employees</h3>
                        </div>

                        <form method="GET" class="employee-search-panel">
                            <input type="hidden" name="section" value="<?= htmlspecialchars($section, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="file" value="<?= htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') ?>">

                            

                            <div class="employee-search-grid">
                                <label class="search-field search-field-wide">
                                    <span>Search employee</span>
                                    <input
                                        type="search"
                                        id="employee-search-input"
                                        name="search"
                                        value="<?= htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8') ?>"
                                        placeholder="Type surname, given name, PhilHealth no., PS, ES, or status">
                                </label>

                                <label class="search-field">
                                    <span>Status</span>
                                    <select name="status" id="employee-status-filter">
                                        <option value="">All statuses</option>
                                        <?php foreach ($allowedStatuses as $statusOption): ?>
                                            <option value="<?= htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8') ?>" <?= strcasecmp($statusFilter, $statusOption) === 0 ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>

                            </div>
                        </form>

                        <div class="report-table-shell">
                            <table class="table employee-list-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>PhilHealth No.</th>
                                        <th>Surname</th>
                                        <th>Given Name</th>
                                        <th>Middle Name</th>
                                        <th>PS</th>
                                        <th>ES</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="employee-table-body">
                                    <?php if ($employees === []): ?>
                                        <tr>
                                            <td colspan="8" class="empty-state-cell">No employee rows were parsed from this PDF.</td>
                                        </tr>
                                    <?php else: ?>
                                        <tr id="employee-empty-state"<?= $filteredEmployees === [] ? '' : ' hidden' ?>>
                                            <td colspan="8" class="empty-state-cell">
                                                No employee records matched your current search and filters.
                                                <?php if ($hasFilteredResults): ?>
                                                    <a href="report-employees.php?section=<?= rawurlencode($section) ?>&amp;file=<?= rawurlencode($fileName) ?>">Clear filters</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php foreach ($employees as $employee): ?>
                                            <?php
                                            $employeeSearchIndex = strtolower(implode(' ', [
                                                (string) ($employee['row_no'] ?? ''),
                                                (string) ($employee['philhealth_no'] ?? ''),
                                                (string) ($employee['surname'] ?? ''),
                                                (string) ($employee['given_name'] ?? ''),
                                                (string) ($employee['middle_name'] ?? ''),
                                                (string) ($employee['ps'] ?? ''),
                                                (string) ($employee['es'] ?? ''),
                                                (string) ($employee['status'] ?? ''),
                                            ]));
                                            ?>
                                            <?php
                                            $employeeFullName = trim(implode(' ', array_filter([
                                                (string) ($employee['surname'] ?? ''),
                                                (string) ($employee['given_name'] ?? ''),
                                                (string) ($employee['middle_name'] ?? ''),
                                            ])));
                                            ?>
                                            <tr
                                                class="employee-data-row"
                                                data-search="<?= htmlspecialchars($employeeSearchIndex, ENT_QUOTES, 'UTF-8') ?>"
                                                data-status="<?= htmlspecialchars(strtolower(trim((string) ($employee['status'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>"
                                                data-philhealth-no="<?= htmlspecialchars((string) ($employee['philhealth_no'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                data-employee-name="<?= htmlspecialchars($employeeFullName !== '' ? $employeeFullName : '-', ENT_QUOTES, 'UTF-8') ?>"
                                                tabindex="0"
                                                role="button"
                                                aria-label="View contribution history for <?= htmlspecialchars($employeeFullName !== '' ? $employeeFullName : 'employee', ENT_QUOTES, 'UTF-8') ?>">
                                                <td><?= htmlspecialchars((string) ($employee['row_no'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string) ($employee['philhealth_no'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string) ($employee['surname'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string) ($employee['given_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string) ($employee['middle_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string) ($employee['ps'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string) ($employee['es'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string) ($employee['status'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>

            <div class="employee-history-modal" id="employee-history-modal" hidden>
                <div class="employee-history-backdrop" data-modal-close></div>
                <div class="employee-history-dialog" role="dialog" aria-modal="true" aria-labelledby="employee-history-title">
                    <div class="employee-history-header">
                        <div>
                            <span class="history-kicker">Contribution History</span>
                            <h3 id="employee-history-title">Employee contribution history</h3>
                            <p id="employee-history-subtitle">Select an employee row to view all contribution records.</p>
                        </div>
                        <div class="history-header-actions">
                            <button type="button" class="history-print-btn" id="employee-history-print" aria-label="Print contribution history">Print</button>
                            <button type="button" class="history-close-btn" id="employee-history-close" aria-label="Close contribution history">Close</button>
                        </div>
                    </div>

                    <div class="employee-history-body">
                        <div class="employee-history-summary">
                            <div><strong>Employee</strong><span id="employee-history-name">-</span></div>
                            <div><strong>PhilHealth No.</strong><span id="employee-history-philhealth">-</span></div>
                            <div><strong>Total Records</strong><span id="employee-history-total">0</span></div>
                        </div>

                        <div class="employee-history-table-shell">
                            <table class="table history-table">
                                <thead>
                                    <tr>
                                        <th>Applicable Period</th>
                                        <th>Date Posted</th>
                                        <th>Document Control No.</th>
                                        <th>PS</th>
                                        <th>ES</th>
                                    </tr>
                                </thead>
                                <tbody id="employee-history-body">
                                    <tr>
                                        <td colspan="5" class="empty-state-cell">Select an employee row to view contribution history.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php include __DIR__ . '/includes/footer.php'; ?>
        </main>
    </div>

    <style>
        .report-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .report-detail-grid div {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 14px 16px;
            border: 1px solid #dbe4f0;
            border-radius: 16px;
            background: #ffffff;
        }

        .report-detail-grid strong {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .report-detail-grid span {
            font-size: 14px;
            color: #0f172a;
            line-height: 1.5;
        }

        .report-table-shell {
            overflow-x: hidden;
            width: 100%;
        }

        .employee-search-panel {
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #dbe4f0;
            border-radius: 22px;
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.10), transparent 30%),
                linear-gradient(180deg, #f8fbff, #ffffff);
        }

        .employee-search-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 18px;
        }

        .search-kicker {
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

        .employee-search-header h4 {
            margin: 0 0 8px;
            font-size: 22px;
            color: #0f172a;
        }

        .employee-search-header p {
            margin: 0;
            color: #64748b;
            line-height: 1.6;
        }

        .search-summary-chip {
            min-width: 170px;
            padding: 14px 16px;
            border-radius: 18px;
            background: #ffffff;
            border: 1px solid #bfdbfe;
            box-shadow: 0 14px 24px rgba(37, 99, 235, 0.08);
            text-align: center;
        }

        .search-summary-chip strong {
            display: block;
            font-size: 28px;
            color: #1d4ed8;
            line-height: 1;
        }

        .search-summary-chip span {
            display: block;
            margin-top: 6px;
            font-size: 13px;
            color: #475569;
        }

        .employee-search-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) repeat(2, minmax(180px, 1fr));
            gap: 14px;
        }

        .search-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .search-field span {
            font-size: 13px;
            font-weight: 700;
            color: #334155;
        }

        .search-field input,
        .search-field select {
            min-height: 48px;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            padding: 12px 14px;
            font-size: 14px;
            color: #0f172a;
            background: #ffffff;
        }

        .search-field input:focus,
        .search-field select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .employee-search-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .search-secondary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .search-secondary-btn {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0f172a;
        }

        .search-helper-text {
            font-size: 13px;
            color: #475569;
        }

        .active-filter-note {
            display: inline-flex;
            align-items: center;
            min-height: 40px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 700;
        }

        .employee-list-table {
            width: 100%;
            table-layout: fixed;
        }

        .employee-list-table th,
        .employee-list-table td {
            padding: 14px 12px;
            vertical-align: middle;
            font-size: 13px;
            line-height: 1.4;
            overflow-wrap: anywhere;
            word-break: break-word;
            text-align: center;
        }

        .employee-list-table th:nth-child(1),
        .employee-list-table td:nth-child(1) {
            width: 7%;
        }

        .employee-list-table th:nth-child(2),
        .employee-list-table td:nth-child(2) {
            width: 17%;
        }

        .employee-list-table th:nth-child(3),
        .employee-list-table td:nth-child(3),
        .employee-list-table th:nth-child(4),
        .employee-list-table td:nth-child(4),
        .employee-list-table th:nth-child(5),
        .employee-list-table td:nth-child(5) {
            width: 17%;
        }

        .employee-list-table th:nth-child(6),
        .employee-list-table td:nth-child(6),
        .employee-list-table th:nth-child(7),
        .employee-list-table td:nth-child(7) {
            width: 8%;
        }

        .employee-list-table th:nth-child(8),
        .employee-list-table td:nth-child(8) {
            width: 9%;
        }

        .employee-data-row {
            cursor: pointer;
        }

        .employee-data-row:hover,
        .employee-data-row:focus-visible {
            background: #eff6ff;
            outline: none;
        }

        .employee-history-modal {
            position: fixed;
            inset: 0;
            z-index: 1200;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .employee-history-modal[hidden] {
            display: none;
        }

        .employee-history-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.56);
            backdrop-filter: blur(4px);
        }

        .employee-history-dialog {
            position: relative;
            width: min(1120px, 100%);
            max-height: min(88vh, 920px);
            overflow: hidden;
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.28);
        }

        .employee-history-header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
            padding: 24px 24px 18px;
            border-bottom: 1px solid #e2e8f0;
            background: linear-gradient(180deg, #f8fbff, #ffffff);
        }

        .history-kicker {
            display: inline-flex;
            margin-bottom: 10px;
            padding: 6px 10px;
            border-radius: 999px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .employee-history-header h3 {
            margin: 0 0 8px;
            font-size: 24px;
            color: #0f172a;
        }

        .employee-history-header p {
            margin: 0;
            color: #64748b;
            line-height: 1.5;
        }

        .history-close-btn {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0f172a;
            border-radius: 12px;
            min-height: 42px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .history-header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .history-print-btn {
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
            border-radius: 12px;
            min-height: 42px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .employee-history-body {
            padding: 24px;
            overflow: auto;
            max-height: calc(88vh - 110px);
        }

        .employee-history-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .employee-history-summary div {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 14px 16px;
            border: 1px solid #dbe4f0;
            border-radius: 16px;
            background: #f8fbff;
        }

        .employee-history-summary strong {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .employee-history-summary span {
            font-size: 14px;
            color: #0f172a;
            line-height: 1.5;
        }

        .employee-history-table-shell {
            overflow-x: auto;
        }

        .history-table {
            width: 100%;
            table-layout: fixed;
        }

        .history-table th,
        .history-table td {
            font-size: 12px;
            line-height: 1.4;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        @media (max-width: 1180px) {
            .employee-list-table th,
            .employee-list-table td {
                padding: 12px 8px;
                font-size: 12px;
            }

            .history-table th,
            .history-table td {
                padding: 12px 8px;
                font-size: 11px;
            }
        }

        @media (max-width: 860px) {
            .report-detail-grid {
                grid-template-columns: 1fr;
            }

            .employee-search-header,
            .employee-search-grid {
                grid-template-columns: 1fr;
                flex-direction: column;
            }

            .employee-history-summary {
                grid-template-columns: 1fr;
            }

            .history-header-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }

        @media (max-width: 640px) {
            .employee-search-panel {
                padding: 16px;
            }

            .employee-search-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .search-secondary-btn {
                width: 100%;
            }

            .employee-history-modal {
                padding: 12px;
            }

            .employee-history-header,
            .employee-history-body {
                padding: 18px;
            }

            .history-header-actions {
                flex-direction: column-reverse;
                align-items: stretch;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchForm = document.querySelector('.employee-search-panel');
            const searchInput = document.getElementById('employee-search-input');
            const statusFilter = document.getElementById('employee-status-filter');
            const employeeRows = Array.from(document.querySelectorAll('.employee-data-row'));
            const emptyStateRow = document.getElementById('employee-empty-state');
            const resultsCount = document.getElementById('employee-results-count');
            const activeFilterNote = document.getElementById('active-filter-note');
            const historyModal = document.getElementById('employee-history-modal');
            const historyCloseButton = document.getElementById('employee-history-close');
            const historyPrintButton = document.getElementById('employee-history-print');
            const historyName = document.getElementById('employee-history-name');
            const historyPhilhealth = document.getElementById('employee-history-philhealth');
            const historyTotal = document.getElementById('employee-history-total');
            const historySubtitle = document.getElementById('employee-history-subtitle');
            const historyBody = document.getElementById('employee-history-body');
            const historyEndpointBase = 'employee-history.php?section=<?= rawurlencode($section) ?>&philhealth_no=';
            let lastFocusedRow = null;

            if (!searchForm || !searchInput || !statusFilter) {
                return;
            }

            function updateFilterNote(searchValue, statusValue) {
                if (!activeFilterNote) {
                    return;
                }

                let activeCount = 0;
                if (searchValue !== '') {
                    activeCount++;
                }
                if (statusValue !== '') {
                    activeCount++;
                }

                activeFilterNote.textContent = activeCount + ' active filter' + (activeCount > 1 ? 's' : '');
                activeFilterNote.hidden = activeCount === 0;
            }

            function filterRows() {
                const searchValue = searchInput.value.trim().toLowerCase();
                const statusValue = statusFilter.value.trim().toLowerCase();
                let visibleCount = 0;

                employeeRows.forEach(function (row) {
                    const matchesSearch = searchValue === '' || (row.dataset.search || '').includes(searchValue);
                    const matchesStatus = statusValue === '' || (row.dataset.status || '') === statusValue;
                    const isVisible = matchesSearch && matchesStatus;

                    row.hidden = !isVisible;
                    if (isVisible) {
                        visibleCount++;
                    }
                });

                if (resultsCount) {
                    resultsCount.textContent = visibleCount.toLocaleString();
                }

                if (emptyStateRow) {
                    emptyStateRow.hidden = visibleCount !== 0;
                }

                updateFilterNote(searchValue, statusValue);
            }

            function closeHistoryModal() {
                if (!historyModal) {
                    return;
                }

                historyModal.hidden = true;
                document.body.style.overflow = '';

                if (lastFocusedRow) {
                    lastFocusedRow.focus();
                }
            }

            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function renderHistoryRows(historyEntries) {
                if (!historyBody) {
                    return;
                }

                if (!Array.isArray(historyEntries) || historyEntries.length === 0) {
                    historyBody.innerHTML = '<tr><td colspan="5" class="empty-state-cell">No contribution history found for this employee yet.</td></tr>';
                    return;
                }

                historyBody.innerHTML = historyEntries.map(function (entry) {
                    const applicablePeriod = entry.applicable_period || '-';
                    const datePosted = entry.date_received || entry.date_time_generated || entry.uploaded_at || '-';
                    const documentControlNumber = entry.document_control_number || '-';
                    const ps = entry.ps || '-';
                    const es = entry.es || '-';

                    return '<tr>' +
                        '<td>' + escapeHtml(applicablePeriod) + '</td>' +
                        '<td>' + escapeHtml(datePosted) + '</td>' +
                        '<td>' + escapeHtml(documentControlNumber) + '</td>' +
                        '<td>' + escapeHtml(ps) + '</td>' +
                        '<td>' + escapeHtml(es) + '</td>' +
                        '</tr>';
                }).join('');
            }

            function printHistoryTable() {
                if (!historyBody || !historyName || !historyPhilhealth || !historyTotal) {
                    return;
                }

                const printWindow = window.open('', '_blank', 'width=960,height=720');
                if (!printWindow) {
                    return;
                }

                const employeeNameValue = historyName.textContent || '-';
                const philhealthValue = historyPhilhealth.textContent || '-';
                const totalValue = historyTotal.textContent || '0';
                const tableRows = historyBody.innerHTML;

                printWindow.document.open();
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <title>Contribution History Print</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 24px; color: #111827; }
                            h1 { margin: 0 0 8px; font-size: 22px; }
                            p { margin: 4px 0; font-size: 13px; }
                            table { width: 100%; border-collapse: collapse; margin-top: 18px; }
                            th, td { border: 1px solid #cbd5e1; padding: 10px 8px; font-size: 12px; text-align: center; }
                            th { background: #eff6ff; }
                        </style>
                    </head>
                    <body>
                        <h1>Contribution History</h1>
                        <p><strong>Employee:</strong> ${escapeHtml(employeeNameValue)}</p>
                        <p><strong>PhilHealth No.:</strong> ${escapeHtml(philhealthValue)}</p>
                        <p><strong>Total Records:</strong> ${escapeHtml(totalValue)}</p>
                        <table>
                            <thead>
                                <tr>
                                    <th>Applicable Period</th>
                                    <th>Date Posted</th>
                                    <th>Document Control No.</th>
                                    <th>PS</th>
                                    <th>ES</th>
                                </tr>
                            </thead>
                            <tbody>${tableRows}</tbody>
                        </table>
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.onload = function () {
                    printWindow.focus();
                    printWindow.print();
                };
            }

            function setHistoryLoadingState(employeeName, philhealthNo) {
                if (historyName) {
                    historyName.textContent = employeeName;
                }
                if (historyPhilhealth) {
                    historyPhilhealth.textContent = philhealthNo || '-';
                }
                if (historyTotal) {
                    historyTotal.textContent = '...';
                }
                if (historySubtitle) {
                    historySubtitle.textContent = 'Loading contribution history from uploaded PDF reports...';
                }
                if (historyBody) {
                    historyBody.innerHTML = '<tr><td colspan="5" class="empty-state-cell">Loading contribution history...</td></tr>';
                }
            }

            async function openHistoryModal(row) {
                if (!historyModal || !row) {
                    return;
                }

                const philhealthNo = row.dataset.philhealthNo || '';
                const employeeName = row.dataset.employeeName || '-';
                setHistoryLoadingState(employeeName, philhealthNo);
                lastFocusedRow = row;
                historyModal.hidden = false;
                document.body.style.overflow = 'hidden';
                historyCloseButton.focus();

                try {
                    const response = await fetch(historyEndpointBase + encodeURIComponent(philhealthNo), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const payload = await response.json();
                    const historyEntries = Array.isArray(payload.history) ? payload.history : [];

                    if (historyTotal) {
                        historyTotal.textContent = historyEntries.length.toLocaleString();
                    }
                    if (historySubtitle) {
                        historySubtitle.textContent = 'Showing all available contribution records found in uploaded PDF reports for this employee.';
                    }
                    renderHistoryRows(historyEntries);
                } catch (error) {
                    if (historyTotal) {
                        historyTotal.textContent = '0';
                    }
                    if (historySubtitle) {
                        historySubtitle.textContent = 'Unable to load contribution history right now.';
                    }
                    if (historyBody) {
                        historyBody.innerHTML = '<tr><td colspan="5" class="empty-state-cell">Unable to load contribution history right now.</td></tr>';
                    }
                }
            }

            searchForm.addEventListener('submit', function (event) {
                event.preventDefault();
            });

            searchInput.addEventListener('input', function () {
                filterRows();
            });

            statusFilter.addEventListener('change', function () {
                filterRows();
            });

            employeeRows.forEach(function (row) {
                row.addEventListener('click', function () {
                    openHistoryModal(row);
                });

                row.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        openHistoryModal(row);
                    }
                });
            });

            if (historyCloseButton) {
                historyCloseButton.addEventListener('click', closeHistoryModal);
            }

            if (historyPrintButton) {
                historyPrintButton.addEventListener('click', printHistoryTable);
            }

            if (historyModal) {
                historyModal.addEventListener('click', function (event) {
                    if (event.target instanceof HTMLElement && event.target.hasAttribute('data-modal-close')) {
                        closeHistoryModal();
                    }
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && historyModal && !historyModal.hidden) {
                    closeHistoryModal();
                }
            });

            filterRows();
        });
    </script>
</body>
</html>
