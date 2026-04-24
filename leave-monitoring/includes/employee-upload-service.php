<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Shuchkin\SimpleXLSX;

function setEmployeeFlashMessage(string $message, string $type, array $details = []): void
{
    $_SESSION['employee_upload_flash'] = [
        'message' => $message,
        'type' => $type,
        'details' => $details,
    ];
}

function consumeEmployeeFlashMessage(): array
{
    $flash = $_SESSION['employee_upload_flash'] ?? ['message' => '', 'type' => '', 'details' => []];
    unset($_SESSION['employee_upload_flash']);

    return [
        (string) ($flash['message'] ?? ''),
        (string) ($flash['type'] ?? ''),
        is_array($flash['details'] ?? null) ? $flash['details'] : [],
    ];
}

function redirectToCurrentPage(bool $forceRefresh = false): never
{
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $target = $requestUri !== '' ? $requestUri : basename((string) ($_SERVER['PHP_SELF'] ?? ''));

    if (str_contains($target, '_refresh=')) {
        $target = (string) preg_replace('/([?&])_refresh=\d+&?/', '$1', $target);
        $target = rtrim($target, '?&');
    }

    if ($forceRefresh) {
        $separator = str_contains($target, '?') ? '&' : '?';
        $target .= $separator . '_refresh=' . time();
    }

    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Location: ' . $target);
    exit;
}

function isAjaxEmployeeUploadRequest(): bool
{
    $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    return $requestedWith === 'xmlhttprequest' || str_contains($accept, 'application/json');
}

function respondEmployeeUploadResult(string $message, string $type, array $details = []): never
{
    if (isAjaxEmployeeUploadRequest()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'message' => $message,
            'type' => $type,
            'details' => $details,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    setEmployeeFlashMessage($message, $type, $details);
    redirectToCurrentPage(true);
}

function ensureEmployeeUploadsTable(mysqli $conn): void
{
    $sql = <<<SQL
    CREATE TABLE IF NOT EXISTS leave_employee_uploads (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        employee_no VARCHAR(100) NOT NULL,
        surname VARCHAR(150) NOT NULL,
        first_name VARCHAR(150) NOT NULL,
        middle_initial VARCHAR(20) DEFAULT '',
        date_of_birth VARCHAR(100) DEFAULT '',
        place_of_birth VARCHAR(255) DEFAULT '',
        employee_group VARCHAR(20) NOT NULL,
        employee_status VARCHAR(30) NOT NULL,
        xlsx_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    SQL;

    $conn->query($sql);
}

function normalizeSheetValue(mixed $value): string
{
    return trim((string) $value);
}

function splitStoredXlsxPaths(string $value): array
{
    if (trim($value) === '') {
        return [];
    }

    $parts = preg_split('/\r\n|\r|\n|\s*\|\|\s*/', $value) ?: [];
    $paths = [];

    foreach ($parts as $part) {
        $path = trim((string) $part);
        if ($path !== '') {
            $paths[] = $path;
        }
    }

    return array_values(array_unique($paths));
}

function joinStoredXlsxPaths(array $paths): string
{
    $normalized = [];
    foreach ($paths as $path) {
        $path = trim((string) $path);
        if ($path !== '') {
            $normalized[] = $path;
        }
    }

    return implode("\n", array_values(array_unique($normalized)));
}

function findExistingEmployeeUpload(
    mysqli $conn,
    string $employeeNo,
    string $surname,
    string $firstName,
    string $middleInitial,
    string $dateOfBirth,
    string $placeOfBirth,
    string $group,
    string $status
): ?array {
    $stmt = $conn->prepare(
        'SELECT id, xlsx_path
         FROM leave_employee_uploads
         WHERE employee_no = ?
           AND surname = ?
           AND first_name = ?
           AND middle_initial = ?
           AND date_of_birth = ?
           AND place_of_birth = ?
           AND employee_group = ?
           AND employee_status = ?
         LIMIT 1'
    );

    if (!$stmt) {
        throw new RuntimeException('Failed to prepare the existing employee lookup query.');
    }

    $stmt->bind_param(
        'ssssssss',
        $employeeNo,
        $surname,
        $firstName,
        $middleInitial,
        $dateOfBirth,
        $placeOfBirth,
        $group,
        $status
    );

    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $record ?: null;
}

function upsertEmployeeUploadRecord(
    mysqli $conn,
    string $employeeNo,
    string $surname,
    string $firstName,
    string $middleInitial,
    string $dateOfBirth,
    string $placeOfBirth,
    string $group,
    string $status,
    string $xlsxPath = ''
): array {
    $existingRecord = findExistingEmployeeUpload(
        $conn,
        $employeeNo,
        $surname,
        $firstName,
        $middleInitial,
        $dateOfBirth,
        $placeOfBirth,
        $group,
        $status
    );

    if ($existingRecord) {
        $existingPaths = splitStoredXlsxPaths((string) ($existingRecord['xlsx_path'] ?? ''));
        if ($xlsxPath === '') {
            $xlsxPath = joinStoredXlsxPaths($existingPaths);
        } else {
            $existingPaths[] = $xlsxPath;
            $xlsxPath = joinStoredXlsxPaths($existingPaths);
        }

        $stmt = $conn->prepare(
            'UPDATE leave_employee_uploads
             SET employee_no = ?, surname = ?, first_name = ?, middle_initial = ?, date_of_birth = ?, place_of_birth = ?,
                 employee_group = ?, employee_status = ?, xlsx_path = ?
             WHERE id = ?'
        );

        if (!$stmt) {
            throw new RuntimeException('Failed to prepare the employee update query.');
        }

        $recordId = (int) $existingRecord['id'];
        $stmt->bind_param(
            'sssssssssi',
            $employeeNo,
            $surname,
            $firstName,
            $middleInitial,
            $dateOfBirth,
            $placeOfBirth,
            $group,
            $status,
            $xlsxPath,
            $recordId
        );

        if (!$stmt->execute()) {
            $errorMessage = $stmt->error !== '' ? $stmt->error : 'Unable to update the employee record.';
            $stmt->close();
            throw new RuntimeException($errorMessage);
        }

        $stmt->close();

        return [
            'action' => 'updated',
            'id' => $recordId,
            'previous_xlsx_path' => joinStoredXlsxPaths($existingPaths),
        ];
    }

    $stmt = $conn->prepare(
        'INSERT INTO leave_employee_uploads (
            employee_no, surname, first_name, middle_initial, date_of_birth, place_of_birth,
            employee_group, employee_status, xlsx_path
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    if (!$stmt) {
        throw new RuntimeException('Failed to prepare the upload record query.');
    }

    $stmt->bind_param(
        'sssssssss',
        $employeeNo,
        $surname,
        $firstName,
        $middleInitial,
        $dateOfBirth,
        $placeOfBirth,
        $group,
        $status,
        $xlsxPath
    );

    if (!$stmt->execute()) {
        $errorMessage = $stmt->error !== '' ? $stmt->error : 'Unable to save the employee record.';
        $stmt->close();
        throw new RuntimeException($errorMessage);
    }

    $recordId = $stmt->insert_id;
    $stmt->close();

    return [
        'action' => 'inserted',
        'id' => $recordId,
        'previous_xlsx_path' => '',
    ];
}

function normalizeSearchValue(string $value): string
{
    $value = strtolower($value);
    $value = str_replace(['.', ':', '(', ')', '/'], ' ', $value);
    $value = preg_replace('/\s+/', ' ', $value) ?? $value;
    return trim($value);
}

function isIgnoredCandidateLabel(string $value): bool
{
    $normalized = normalizeSearchValue($value);
    if ($normalized === '') {
        return true;
    }

    $ignoredLabels = [
        'surname',
        'first name',
        'm i',
        'm n',
        'middle initial',
        'initial',
        'date of birth',
        'place of birth',
        'birth',
        'name',
        'employee no',
        'if married woman',
        'give also full maiden name',
        'if married woman give also full maiden name',
    ];

    foreach ($ignoredLabels as $label) {
        if ($normalized === $label || str_contains($normalized, $label)) {
            return true;
        }
    }

    return false;
}

function findCellByNeedle(array $rows, string $needle): ?array
{
    $needle = normalizeSearchValue($needle);

    foreach ($rows as $rowIndex => $row) {
        foreach ($row as $colIndex => $value) {
            $current = normalizeSearchValue(normalizeSheetValue($value));
            if ($current !== '' && str_contains($current, $needle)) {
                return [
                    'row' => $rowIndex,
                    'col' => $colIndex,
                    'value' => normalizeSheetValue($value),
                ];
            }
        }
    }

    return null;
}

function findCellByNeedles(array $rows, array $needles): ?array
{
    foreach ($needles as $needle) {
        $cell = findCellByNeedle($rows, $needle);
        if ($cell !== null) {
            return $cell;
        }
    }

    return null;
}

function findExactCellByNeedles(array $rows, array $needles): ?array
{
    $normalizedNeedles = array_map('normalizeSearchValue', $needles);

    foreach ($rows as $rowIndex => $row) {
        foreach ($row as $colIndex => $value) {
            $current = normalizeSearchValue(normalizeSheetValue($value));
            if ($current !== '' && in_array($current, $normalizedNeedles, true)) {
                return [
                    'row' => $rowIndex,
                    'col' => $colIndex,
                    'value' => normalizeSheetValue($value),
                ];
            }
        }
    }

    return null;
}

function valueAbove(array $rows, ?array $cell): string
{
    if ($cell === null || $cell['row'] <= 0) {
        return '';
    }

    $value = normalizeSheetValue($rows[$cell['row'] - 1][$cell['col']] ?? '');
    return isIgnoredCandidateLabel($value) ? '' : $value;
}

function collectCandidateValues(array $rows, int $rowIndex, int $colIndex): array
{
    $candidates = [];
    $positions = [
        [$rowIndex - 1, $colIndex],
        [$rowIndex - 1, $colIndex - 1],
        [$rowIndex - 1, $colIndex + 1],
        [$rowIndex - 2, $colIndex],
        [$rowIndex, $colIndex - 1],
        [$rowIndex, $colIndex + 1],
        [$rowIndex - 2, $colIndex - 1],
        [$rowIndex - 2, $colIndex + 1],
    ];

    foreach ($positions as [$targetRow, $targetCol]) {
        if ($targetRow < 0 || $targetCol < 0) {
            continue;
        }

        $value = normalizeSheetValue($rows[$targetRow][$targetCol] ?? '');
        if ($value === '') {
            continue;
        }

        $candidates[] = $value;
    }

    return array_values(array_unique($candidates));
}

function extractLabeledValue(array $rows, ?array $cell): string
{
    if ($cell === null) {
        return '';
    }

    $candidates = collectCandidateValues($rows, (int) $cell['row'], (int) $cell['col']);

    foreach ($candidates as $candidate) {
        if (isIgnoredCandidateLabel($candidate)) {
            continue;
        }

        return $candidate;
    }

    return valueAbove($rows, $cell);
}

function sanitizeMiddleInitialValue(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    $normalized = normalizeSearchValue($value);
    if (
        $normalized === '' ||
        str_contains($normalized, 'if married woman') ||
        str_contains($normalized, 'full maiden name')
    ) {
        return '';
    }

    if (mb_strlen($value) > 10) {
        return '';
    }

    return $value;
}

function parseEmployeeFormXlsx(string $filePath): array
{
    $xlsx = SimpleXLSX::parse($filePath);

    if (!$xlsx) {
        throw new RuntimeException(SimpleXLSX::parseError() ?: 'Unable to parse the uploaded XLSX file.');
    }

    $rows = $xlsx->rows();

    $employeeNoCell = findCellByNeedles($rows, ['employee no', 'employee no:']);
    $surnameCell = findCellByNeedles($rows, ['(surname)', 'surname']);
    $firstNameCell = findCellByNeedles($rows, ['(first name)', 'first name']);
    $miCell = findCellByNeedles($rows, ['(m.i', '(m.i.)', '(m.n', '(m.n.)', 'middle initial', 'initial']);
    $dobCell = findCellByNeedles($rows, ['(date of birth)', 'date of birth']);
    $pobCell = findCellByNeedles($rows, ['(place of birth)', 'place of birth']);

    $employeeNo = '';
    if ($employeeNoCell !== null) {
        if (preg_match('/employee\s*no\.?\s*:\s*(.+)$/i', $employeeNoCell['value'], $matches)) {
            $employeeNo = trim($matches[1]);
        } else {
            $employeeNo = trim(str_ireplace(['employee no', ':'], '', $employeeNoCell['value']));
        }
    }

    $parsed = [
        'employee_no' => $employeeNo,
        'surname' => extractLabeledValue($rows, $surnameCell),
        'first_name' => extractLabeledValue($rows, $firstNameCell),
        'middle_initial' => sanitizeMiddleInitialValue(extractLabeledValue($rows, $miCell)),
        'date_of_birth' => extractLabeledValue($rows, $dobCell),
        'place_of_birth' => extractLabeledValue($rows, $pobCell),
    ];

    foreach ($parsed as $key => $value) {
        $parsed[$key] = trim($value);
    }

    if ($parsed['employee_no'] === '' || $parsed['surname'] === '' || $parsed['first_name'] === '') {
        throw new RuntimeException('The uploaded XLSX format was read, but the expected employee fields were not found.');
    }

    return $parsed;
}

function isMeaningfulLeaveTableRow(array $row): bool
{
    foreach ($row as $value) {
        $normalized = normalizeSearchValue(normalizeSheetValue($value));
        if ($normalized !== '' && $normalized !== 'x') {
            return true;
        }
    }

    return false;
}

function extractLeaveTableRows(array $rows): array
{
    $headerRowIndex = null;
    $fromToRowIndex = null;

    foreach ($rows as $rowIndex => $row) {
        $rowText = normalizeSearchValue(implode(' ', array_map('normalizeSheetValue', $row)));

        $hasMainHeaders =
            (str_contains($rowText, 'period covered') || str_contains($rowText, 'inclusive date')) &&
            str_contains($rowText, 'reason') &&
            str_contains($rowText, 'remarks');

        $hasFromToHeaders = str_contains($rowText, 'from') && str_contains($rowText, 'to');

        if ($hasMainHeaders) {
            $headerRowIndex = $rowIndex;
        }

        if ($hasFromToHeaders) {
            $fromToRowIndex = $rowIndex;
        }

        if ($headerRowIndex !== null && $fromToRowIndex !== null && abs($fromToRowIndex - $headerRowIndex) <= 2) {
            break;
        }
    }

    if ($headerRowIndex === null && $fromToRowIndex === null) {
        return [];
    }

    $dataRows = [];
    $startRowIndex = max($headerRowIndex ?? 0, $fromToRowIndex ?? 0) + 1;
    $emptyRowStreak = 0;

    for ($rowIndex = $startRowIndex; $rowIndex < count($rows); $rowIndex++) {
        $row = $rows[$rowIndex];

        if (!isMeaningfulLeaveTableRow($row)) {
            if ($dataRows !== []) {
                $emptyRowStreak++;

                // Some uploaded forms keep blank spacer rows between leave entries.
                if ($emptyRowStreak >= 2) {
                    break;
                }
                continue;
            }
            continue;
        }

        $emptyRowStreak = 0;

        $from = normalizeSheetValue($row[0] ?? '');
        $to = normalizeSheetValue($row[1] ?? '');
        $reason = normalizeSheetValue($row[2] ?? '');
        $station = normalizeSheetValue($row[3] ?? '');
        $withoutPay = normalizeSheetValue($row[4] ?? '');
        $withPay = normalizeSheetValue($row[5] ?? '');
        $remarks = normalizeSheetValue($row[6] ?? '');

        $joined = normalizeSearchValue(implode(' ', [$from, $to, $reason, $station, $withoutPay, $withPay, $remarks]));
        if (
            str_contains($joined, 'issued in compliance') ||
            str_contains($joined, 'purpose') ||
            str_contains($joined, 'certified correct') ||
            str_contains($joined, 'period covered') ||
            str_contains($joined, 'absence without pay') ||
            str_contains($joined, 'absence with pay')
        ) {
            break;
        }

        if ($from === 'x' && $to === 'x' && $reason === 'x' && $station === 'x' && $withoutPay === 'x' && $withPay === 'x' && $remarks === 'x') {
            continue;
        }

        $dataRows[] = [
            'from' => $from,
            'to' => $to,
            'reason' => $reason,
            'station' => $station,
            'without_pay' => $withoutPay,
            'with_pay' => $withPay,
            'remarks' => $remarks,
        ];
    }

    return $dataRows;
}

function extractFooterValue(array $rows, array $needles, callable $resolver): string
{
    $cell = findCellByNeedles($rows, $needles);
    if ($cell === null) {
        return '';
    }

    $value = $resolver($rows, $cell);
    return trim($value);
}

function valueRightOf(array $rows, ?array $cell): string
{
    if ($cell === null) {
        return '';
    }

    $value = normalizeSheetValue($rows[$cell['row']][$cell['col'] + 1] ?? '');
    return $value;
}

function firstMeaningfulValueBelow(array $rows, ?array $cell, int $maxOffset = 3): string
{
    if ($cell === null) {
        return '';
    }

    for ($offset = 1; $offset <= $maxOffset; $offset++) {
        $value = normalizeSheetValue($rows[$cell['row'] + $offset][$cell['col']] ?? '');
        if ($value !== '' && !isIgnoredCandidateLabel($value)) {
            return $value;
        }
    }

    return '';
}

function findNearbyMeaningfulValue(array $rows, int $rowIndex, int $colIndex, array $rowOffsets, array $colOffsets): string
{
    foreach ($rowOffsets as $rowOffset) {
        foreach ($colOffsets as $colOffset) {
            $targetRow = $rowIndex + $rowOffset;
            $targetCol = $colIndex + $colOffset;

            if ($targetRow < 0 || $targetCol < 0) {
                continue;
            }

            $value = normalizeSheetValue($rows[$targetRow][$targetCol] ?? '');
            if ($value !== '' && !isIgnoredCandidateLabel($value)) {
                return $value;
            }
        }
    }

    return '';
}

function extractFooterDate(array $rows): string
{
    $dateCell = findExactCellByNeedles($rows, ['(date)']);
    if ($dateCell !== null) {
        $directAbove = normalizeSheetValue($rows[$dateCell['row'] - 1][$dateCell['col']] ?? '');
        if ($directAbove !== '' && !isIgnoredCandidateLabel($directAbove)) {
            return $directAbove;
        }

        $nearby = findNearbyMeaningfulValue(
            $rows,
            (int) $dateCell['row'],
            (int) $dateCell['col'],
            [-1, -2, 0, 1],
            [0, -1, 1, -2, 2]
        );

        if ($nearby !== '') {
            return $nearby;
        }
    }

    $officerCell = findCellByNeedles($rows, ['administrative officer v']);
    if ($officerCell !== null) {
        $candidate = findNearbyMeaningfulValue(
            $rows,
            (int) $officerCell['row'],
            max(0, (int) $officerCell['col'] - 5),
            [-1, -2, -3],
            [0, 1, 2, 3]
        );

        if ($candidate !== '') {
            return $candidate;
        }
    }

    return '';
}

function formatFooterDate(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    $normalized = preg_replace('/\s+/', ' ', str_replace('/', '-', $value)) ?? $value;
    $timestamp = strtotime($normalized);

    if ($timestamp !== false) {
        return date('F j, Y', $timestamp);
    }

    if (preg_match('/^([A-Za-z]+)\s+(\d{1,2}),?\s+(\d{4})$/', $value, $matches)) {
        $month = ucfirst(strtolower($matches[1]));
        return sprintf('%s %d, %s', $month, (int) $matches[2], $matches[3]);
    }

    return $value;
}

function formatLeavePeriodValue(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    $timestamp = strtotime($value);
    if ($timestamp !== false) {
        return date('F j, Y', $timestamp);
    }

    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $matches)) {
        $timestamp = mktime(0, 0, 0, (int) $matches[2], (int) $matches[1], (int) $matches[3]);
        return date('F j, Y', $timestamp);
    }

    return $value;
}

function extractLeaveFooterDetails(array $rows): array
{
    $purpose = extractFooterValue($rows, ['purpose'], static function (array $rows, array $cell): string {
        return valueRightOf($rows, $cell);
    });

    $certifiedCorrect = extractFooterValue($rows, ['certified correct'], static function (array $rows, array $cell): string {
        $line1 = normalizeSheetValue($rows[$cell['row'] + 1][$cell['col']] ?? '');
        $line2 = normalizeSheetValue($rows[$cell['row'] + 2][$cell['col']] ?? '');
        return trim(implode("\n", array_filter([$line1, $line2], static fn (string $value): bool => $value !== '')));
    });

    $date = formatFooterDate(extractFooterDate($rows));

    $officerName = extractFooterValue($rows, ['administrative officer v'], static function (array $rows, array $cell): string {
        return valueAbove($rows, $cell);
    });

    $officerTitle = extractFooterValue($rows, ['administrative officer v'], static function (array $rows, array $cell): string {
        return normalizeSheetValue($rows[$cell['row']][$cell['col']] ?? '');
    });

    return [
        'purpose' => $purpose,
        'certified_correct' => $certifiedCorrect,
        'date' => $date,
        'officer_name' => $officerName,
        'officer_title' => $officerTitle,
    ];
}

function renderUploadedXlsxPreview(string $relativePath): string
{
    if ($relativePath === '') {
        return '<p class="preview-empty">No uploaded form preview available.</p>';
    }

    $fullPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
    if (!is_file($fullPath)) {
        return '<p class="preview-empty">The uploaded XLSX file could not be found.</p>';
    }

    $xlsx = SimpleXLSX::parse($fullPath);
    if (!$xlsx) {
        return '<p class="preview-empty">Unable to render the uploaded XLSX preview.</p>';
    }

    $rows = $xlsx->rows();
    if ($rows === []) {
        return '<p class="preview-empty">The uploaded XLSX file has no readable rows.</p>';
    }

    $leaveRows = extractLeaveTableRows($rows);
    if ($leaveRows === []) {
        return '<p class="preview-empty">The leave details table could not be found in the uploaded form.</p>';
    }

    $employeeDetails = parseEmployeeFormXlsx($fullPath);
    $footerDetails = extractLeaveFooterDetails($rows);

    $html = '<div class="leave-table-preview-shell">';
    $html .= '<div class="leave-form-top">';
    $html .= '<div class="leave-form-title"><strong>LEAVE MONITORING</strong><span>(To be Accomplished by the Employer)</span></div>';
    $html .= '<div class="leave-person-grid">';
    $html .= '<div class="leave-person-row">';
    $html .= '<div class="leave-person-label">NAME</div>';
    $html .= '<div class="leave-person-cell"><strong>' . htmlspecialchars($employeeDetails['surname'], ENT_QUOTES, 'UTF-8') . '</strong><span>(Surname)</span></div>';
    $html .= '<div class="leave-person-cell"><strong>' . htmlspecialchars($employeeDetails['first_name'], ENT_QUOTES, 'UTF-8') . '</strong><span>(First Name)</span></div>';
    $html .= '<div class="leave-person-cell"><strong>' . htmlspecialchars($employeeDetails['middle_initial'], ENT_QUOTES, 'UTF-8') . '</strong><span>(M.I.N.)</span></div>';
    $html .= '<div class="leave-person-note">(If married woman, give also full maiden name)</div>';
    $html .= '</div>';
    $html .= '<div class="leave-person-row leave-person-row-birth">';
    $html .= '<div class="leave-person-label">BIRTH</div>';
    $html .= '<div class="leave-person-cell leave-person-cell-birth"><strong>' . htmlspecialchars($employeeDetails['date_of_birth'], ENT_QUOTES, 'UTF-8') . '</strong><span>(Date of Birth)</span></div>';
    $html .= '<div class="leave-person-cell leave-person-cell-birth"><strong>' . htmlspecialchars($employeeDetails['place_of_birth'], ENT_QUOTES, 'UTF-8') . '</strong><span>(Place of Birth)</span></div>';
    $html .= '<div class="leave-person-note leave-person-note-birth">(Date herein should be checked from birth/baptismal certificate or some reliable documents.)</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<table class="leave-table-preview">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th colspan="2" class="leave-period-heading"><strong>Period Covered</strong><span>(Inclusive Date)</span></th>';
    $html .= '<th rowspan="2" class="leave-period-heading"><strong>Reason</strong></th>';
    $html .= '<th rowspan="2" class="leave-period-heading"><strong>Station / Place of Assignment</strong></th>';
    $html .= '<th rowspan="2" class="leave-period-heading"><strong>Absence Without Pay</strong></th>';
    $html .= '<th rowspan="2" class="leave-period-heading"><strong>Absence With Pay</strong></th>';
    $html .= '<th rowspan="2" class="leave-period-heading"><strong>Remarks</strong></th>';
    $html .= '<th rowspan="2" class="leave-period-heading leave-actions-column no-print"><strong>Actions</strong></th>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<th>From</th>';
    $html .= '<th>To</th>';
    $html .= '</tr></thead><tbody>';

    foreach ($leaveRows as $leaveIndex => $leaveRow) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars(formatLeavePeriodValue($leaveRow['from']), ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td>' . htmlspecialchars(formatLeavePeriodValue($leaveRow['to']), ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td>' . htmlspecialchars($leaveRow['reason'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td>' . htmlspecialchars($leaveRow['station'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td>' . htmlspecialchars($leaveRow['without_pay'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td>' . htmlspecialchars($leaveRow['with_pay'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td>' . htmlspecialchars($leaveRow['remarks'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td class="leave-row-actions no-print">';
        $html .= '<div class="table-actions leave-preview-actions">';
        $html .= '<button type="button" class="table-btn edit action-icon-btn" onclick="openEditLeaveModal(this)" title="Edit Leave" aria-label="Edit Leave"';
        $html .= ' data-leave-index="' . $leaveIndex . '"';
        $html .= ' data-from="' . htmlspecialchars($leaveRow['from'], ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' data-to="' . htmlspecialchars($leaveRow['to'], ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' data-reason="' . htmlspecialchars($leaveRow['reason'], ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' data-station="' . htmlspecialchars($leaveRow['station'], ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' data-without-pay="' . htmlspecialchars($leaveRow['without_pay'], ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' data-with-pay="' . htmlspecialchars($leaveRow['with_pay'], ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' data-remarks="' . htmlspecialchars($leaveRow['remarks'], ENT_QUOTES, 'UTF-8') . '"';
        $html .= '><i class="fa-solid fa-pen"></i></button>';
        $html .= '<button type="button" class="table-btn delete action-icon-btn" onclick="deleteLeaveRow(this)" title="Delete Leave" aria-label="Delete Leave"';
        $html .= ' data-leave-index="' . $leaveIndex . '"';
        $html .= '><i class="fa-solid fa-trash"></i></button>';
        $html .= '</div>';
        $html .= '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';
    $html .= '<div class="leave-footer-preview">';
    $html .= '<div class="leave-footer-row leave-footer-row-top">';
    $html .= '<div class="leave-footer-col leave-footer-purpose">';
    if ($footerDetails['purpose'] !== '') {
        $html .= '<div class="leave-inline-pair"><span class="leave-inline-label">Purpose:</span><span class="leave-inline-value">' . htmlspecialchars($footerDetails['purpose'], ENT_QUOTES, 'UTF-8') . '</span></div>';
    }
    $html .= '</div>';
    $html .= '<div class="leave-footer-col leave-footer-certify">';
    if ($footerDetails['certified_correct'] !== '') {
        $html .= '<div class="leave-certify-block"><strong>CERTIFIED CORRECT:</strong>';
        foreach (preg_split('/\r\n|\r|\n/', $footerDetails['certified_correct']) as $certifiedLine) {
            $certifiedLine = trim($certifiedLine);
            if ($certifiedLine === '') {
                continue;
            }

            $html .= '<span>' . htmlspecialchars($certifiedLine, ENT_QUOTES, 'UTF-8') . '</span>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';

    $html .= '<div class="leave-footer-row leave-footer-row-bottom">';
    $html .= '<div class="leave-footer-col leave-footer-date">';
    if ($footerDetails['date'] !== '') {
        $html .= '<div class="leave-date-block"><strong>' . htmlspecialchars($footerDetails['date'], ENT_QUOTES, 'UTF-8') . '</strong><span>(Date)</span></div>';
    }
    $html .= '</div>';
    $html .= '<div class="leave-footer-col leave-footer-signature">';
    if ($footerDetails['officer_name'] !== '' || $footerDetails['officer_title'] !== '') {
        $html .= '<div class="leave-signature-block">';
        if ($footerDetails['officer_name'] !== '') {
            $html .= '<strong>' . htmlspecialchars($footerDetails['officer_name'], ENT_QUOTES, 'UTF-8') . '</strong>';
        }
        if ($footerDetails['officer_title'] !== '') {
            $html .= '<span>' . htmlspecialchars($footerDetails['officer_title'], ENT_QUOTES, 'UTF-8') . '</span>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div></div>';
    return $html;
}

function findMatchingEmployeePreviewPaths(mysqli $conn, array $record): array
{
    $employeeNo = trim((string) ($record['employee_no'] ?? ''));
    $surname = trim((string) ($record['surname'] ?? ''));
    $firstName = trim((string) ($record['first_name'] ?? ''));
    $middleInitial = sanitizeMiddleInitialValue((string) ($record['middle_initial'] ?? ''));
    $group = trim((string) ($record['employee_group'] ?? ''));
    $status = trim((string) ($record['employee_status'] ?? ''));

    if ($employeeNo === '' || $surname === '' || $firstName === '' || $group === '' || $status === '') {
        return splitStoredXlsxPaths((string) ($record['xlsx_path'] ?? ''));
    }

    $stmt = $conn->prepare(
        'SELECT employee_no, surname, first_name, middle_initial, xlsx_path
         FROM leave_employee_uploads
         WHERE employee_group = ? AND employee_status = ? AND employee_no = ? AND surname = ? AND first_name = ?
         ORDER BY created_at ASC, id ASC'
    );

    if (!$stmt) {
        return splitStoredXlsxPaths((string) ($record['xlsx_path'] ?? ''));
    }

    $stmt->bind_param('sssss', $group, $status, $employeeNo, $surname, $firstName);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    $paths = [];
    foreach ($rows as $row) {
        $rowMiddleInitial = sanitizeMiddleInitialValue((string) ($row['middle_initial'] ?? ''));
        if ($rowMiddleInitial !== $middleInitial) {
            continue;
        }

        foreach (splitStoredXlsxPaths((string) ($row['xlsx_path'] ?? '')) as $path) {
            $paths[] = $path;
        }
    }

    if ($paths === []) {
        return splitStoredXlsxPaths((string) ($record['xlsx_path'] ?? ''));
    }

    return array_values(array_unique($paths));
}

function renderUploadedEmployeePreview(mysqli $conn, array $record): string
{
    $relativePaths = findMatchingEmployeePreviewPaths($conn, $record);
    if ($relativePaths === []) {
        return '<p class="preview-empty">No uploaded form preview available.</p>';
    }

    $employeeDetails = null;
    $footerDetails = [
        'purpose' => '',
        'certified_correct' => '',
        'date' => '',
        'officer_name' => '',
        'officer_title' => '',
    ];
    $leaveRows = [];

    foreach ($relativePaths as $relativePath) {
        $fullPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        if (!is_file($fullPath)) {
            continue;
        }

        $xlsx = SimpleXLSX::parse($fullPath);
        if (!$xlsx) {
            continue;
        }

        $rows = $xlsx->rows();
        if ($rows === []) {
            continue;
        }

        if ($employeeDetails === null) {
            try {
                $employeeDetails = parseEmployeeFormXlsx($fullPath);
            } catch (Throwable) {
                $employeeDetails = null;
            }
        }

        $leaveRows = array_merge($leaveRows, extractLeaveTableRows($rows));

        $currentFooterDetails = extractLeaveFooterDetails($rows);
        foreach ($footerDetails as $key => $value) {
            if ($footerDetails[$key] === '' && ($currentFooterDetails[$key] ?? '') !== '') {
                $footerDetails[$key] = (string) $currentFooterDetails[$key];
            }
        }
    }

    if ($employeeDetails === null) {
        return '<p class="preview-empty">Unable to render the uploaded XLSX preview.</p>';
    }

    if ($leaveRows === []) {
        return '<p class="preview-empty">The leave details table could not be found in the uploaded form.</p>';
    }

    $tempPath = trim((string) ($relativePaths[0] ?? ''));
    if ($tempPath === '') {
        return '<p class="preview-empty">No uploaded form preview available.</p>';
    }

    $html = '<div class="leave-table-preview-shell">';
    $html .= '<div class="leave-form-top">';
    $html .= '<div class="leave-form-title"><strong>LEAVE MONITORING</strong><span>(To be Accomplished by the Employer)</span></div>';
    $html .= '<div class="leave-person-grid">';
    $html .= '<div class="leave-person-row">';
    $html .= '<div class="leave-person-label">NAME</div>';
    $html .= '<div class="leave-person-cell"><strong>' . htmlspecialchars($employeeDetails['surname'], ENT_QUOTES, 'UTF-8') . '</strong><span>(Surname)</span></div>';
    $html .= '<div class="leave-person-cell"><strong>' . htmlspecialchars($employeeDetails['first_name'], ENT_QUOTES, 'UTF-8') . '</strong><span>(First Name)</span></div>';
    $html .= '<div class="leave-person-cell"><strong>' . htmlspecialchars($employeeDetails['middle_initial'], ENT_QUOTES, 'UTF-8') . '</strong><span>(M.I.N.)</span></div>';
    $html .= '<div class="leave-person-note">(If married woman, give also full maiden name)</div>';
    $html .= '</div>';
    $html .= '<div class="leave-person-row leave-person-row-birth">';
    $html .= '<div class="leave-person-label">BIRTH</div>';
    $html .= '<div class="leave-person-cell leave-person-cell-birth"><strong>' . htmlspecialchars($employeeDetails['date_of_birth'], ENT_QUOTES, 'UTF-8') . '</strong><span>(Date of Birth)</span></div>';
    $html .= '<div class="leave-person-cell leave-person-cell-birth"><strong>' . htmlspecialchars($employeeDetails['place_of_birth'], ENT_QUOTES, 'UTF-8') . '</strong><span>(Place of Birth)</span></div>';
    $html .= '<div class="leave-person-note leave-person-note-birth">(Date herein should be checked from birth/baptismal certificate or some reliable documents.)</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<table class="leave-table-preview">';
    $html .= '<thead><tr>';
    $html .= '<th colspan="2" class="leave-period-heading"><strong>Period Covered</strong><span>(Inclusive Date)</span></th>';
    $html .= '<th rowspan="2" class="leave-period-heading"><strong>Reason</strong></th>';
    $html .= '<th rowspan="2" class="leave-period-heading"><strong>Station / Place of Assignment</strong></th>';
    $html .= '<th rowspan="2" class="leave-period-heading"><strong>Absence Without Pay</strong></th>';
    $html .= '<th rowspan="2" class="leave-period-heading"><strong>Absence With Pay</strong></th>';
    $html .= '<th rowspan="2" class="leave-period-heading"><strong>Remarks</strong></th>';
    $html .= '<th rowspan="2" class="leave-period-heading leave-actions-column no-print"><strong>Actions</strong></th>';
    $html .= '</tr><tr><th>From</th><th>To</th></tr></thead><tbody>';

    foreach ($leaveRows as $leaveIndex => $leaveRow) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars(formatLeavePeriodValue($leaveRow['from']), ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td>' . htmlspecialchars(formatLeavePeriodValue($leaveRow['to']), ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td>' . htmlspecialchars($leaveRow['reason'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td>' . htmlspecialchars($leaveRow['station'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td>' . htmlspecialchars($leaveRow['without_pay'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td>' . htmlspecialchars($leaveRow['with_pay'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td>' . htmlspecialchars($leaveRow['remarks'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td class="leave-row-actions no-print"><div class="table-actions leave-preview-actions">';
        $html .= '<button type="button" class="table-btn edit action-icon-btn" onclick="openEditLeaveModal(this)" title="Edit Leave" aria-label="Edit Leave"';
        $html .= ' data-leave-index="' . $leaveIndex . '"';
        $html .= ' data-from="' . htmlspecialchars($leaveRow['from'], ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' data-to="' . htmlspecialchars($leaveRow['to'], ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' data-reason="' . htmlspecialchars($leaveRow['reason'], ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' data-station="' . htmlspecialchars($leaveRow['station'], ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' data-without-pay="' . htmlspecialchars($leaveRow['without_pay'], ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' data-with-pay="' . htmlspecialchars($leaveRow['with_pay'], ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' data-remarks="' . htmlspecialchars($leaveRow['remarks'], ENT_QUOTES, 'UTF-8') . '"';
        $html .= '><i class="fa-solid fa-pen"></i></button>';
        $html .= '<button type="button" class="table-btn delete action-icon-btn" onclick="deleteLeaveRow(this)" title="Delete Leave" aria-label="Delete Leave" data-leave-index="' . $leaveIndex . '"><i class="fa-solid fa-trash"></i></button>';
        $html .= '</div></td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';
    $html .= '<div class="leave-footer-preview">';
    $html .= '<div class="leave-footer-row leave-footer-row-top">';
    $html .= '<div class="leave-footer-col leave-footer-purpose">';
    if ($footerDetails['purpose'] !== '') {
        $html .= '<div class="leave-inline-pair"><span class="leave-inline-label">Purpose:</span><span class="leave-inline-value">' . htmlspecialchars($footerDetails['purpose'], ENT_QUOTES, 'UTF-8') . '</span></div>';
    }
    $html .= '</div><div class="leave-footer-col leave-footer-certify">';
    if ($footerDetails['certified_correct'] !== '') {
        $html .= '<div class="leave-certify-block"><strong>CERTIFIED CORRECT:</strong>';
        foreach (preg_split('/\r\n|\r|\n/', $footerDetails['certified_correct']) as $certifiedLine) {
            $certifiedLine = trim($certifiedLine);
            if ($certifiedLine === '') {
                continue;
            }
            $html .= '<span>' . htmlspecialchars($certifiedLine, ENT_QUOTES, 'UTF-8') . '</span>';
        }
        $html .= '</div>';
    }
    $html .= '</div></div><div class="leave-footer-row leave-footer-row-bottom"><div class="leave-footer-col leave-footer-date">';
    if ($footerDetails['date'] !== '') {
        $html .= '<div class="leave-date-block"><strong>' . htmlspecialchars($footerDetails['date'], ENT_QUOTES, 'UTF-8') . '</strong><span>(Date)</span></div>';
    }
    $html .= '</div><div class="leave-footer-col leave-footer-signature">';
    if ($footerDetails['officer_name'] !== '' || $footerDetails['officer_title'] !== '') {
        $html .= '<div class="leave-signature-block">';
        if ($footerDetails['officer_name'] !== '') {
            $html .= '<strong>' . htmlspecialchars($footerDetails['officer_name'], ENT_QUOTES, 'UTF-8') . '</strong>';
        }
        if ($footerDetails['officer_title'] !== '') {
            $html .= '<span>' . htmlspecialchars($footerDetails['officer_title'], ENT_QUOTES, 'UTF-8') . '</span>';
        }
        $html .= '</div>';
    }
    $html .= '</div></div></div></div>';

    return $html;
}

function moveUploadedFile(array $file, string $targetDirectory, array $allowedExtensions): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('One of the uploaded files could not be processed.');
    }

    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('Invalid file type uploaded.');
    }

    if (!is_dir($targetDirectory)) {
        mkdir($targetDirectory, 0777, true);
    }

    $safeName = bin2hex(random_bytes(10)) . '.' . $extension;
    $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Failed to save the uploaded file.');
    }

    return $safeName;
}

function buildEmployeeUploadFolder(string $group, string $status): string
{
    $normalizedGroup = strtoupper(trim($group));
    $normalizedStatus = strtoupper(trim($status));

    $allowedFolders = [
        'ES ACTIVE',
        'ES INACTIVATION',
        'ES SEPARATION',
        'SEC ACTIVE',
        'SEC INACTIVATION',
        'SEC SEPARATION',
    ];

    $folderName = $normalizedGroup . ' ' . $normalizedStatus;
    if (!in_array($folderName, $allowedFolders, true)) {
        throw new RuntimeException('Invalid upload folder configuration.');
    }

    return $folderName;
}

function normalizeUploadedFiles(array $files): array
{
    $names = $files['name'] ?? [];
    $tmpNames = $files['tmp_name'] ?? [];
    $errors = $files['error'] ?? [];
    $sizes = $files['size'] ?? [];
    $types = $files['type'] ?? [];

    if (!is_array($names)) {
        return $files === [] ? [] : [$files];
    }

    $normalized = [];
    foreach ($names as $index => $name) {
        $normalized[] = [
            'name' => $name,
            'type' => $types[$index] ?? '',
            'tmp_name' => $tmpNames[$index] ?? '',
            'error' => $errors[$index] ?? UPLOAD_ERR_NO_FILE,
            'size' => $sizes[$index] ?? 0,
        ];
    }

    return $normalized;
}

function processEmployeeUploadBatch(mysqli $conn, array $uploadedFiles, string $group, string $status): array
{
    if ($uploadedFiles === []) {
        return ['Please choose at least one XLSX file to upload.', 'error', []];
    }

    $successCount = 0;
    $errorCount = 0;
    $details = [];
    $uploadFolder = buildEmployeeUploadFolder($group, $status);
    $targetDirectory = dirname(__DIR__) . '/uploads/' . $uploadFolder;
    $relativeDirectory = 'uploads/' . $uploadFolder;

    foreach ($uploadedFiles as $file) {
        $originalName = trim((string) ($file['name'] ?? ''));
        if ($originalName === '') {
            continue;
        }

        $storedRelativePath = '';

        try {
            $xlsxFileName = moveUploadedFile(
                $file,
                $targetDirectory,
                ['xlsx']
            );

            $storedRelativePath = $relativeDirectory . '/' . $xlsxFileName;
            $parsed = parseEmployeeFormXlsx(dirname(__DIR__) . '/' . $storedRelativePath);
            $result = upsertEmployeeUploadRecord(
                $conn,
                $parsed['employee_no'],
                $parsed['surname'],
                $parsed['first_name'],
                $parsed['middle_initial'],
                $parsed['date_of_birth'],
                $parsed['place_of_birth'],
                $group,
                $status,
                $storedRelativePath
            );

            $successCount++;
            $details[] = [
                'type' => 'success',
                'file' => $originalName,
                'message' => ($result['action'] ?? 'inserted') === 'updated'
                    ? 'Existing employee record updated successfully.'
                    : 'Uploaded successfully.',
            ];
        } catch (Throwable $exception) {
            if ($storedRelativePath !== '') {
                deleteStoredFile($storedRelativePath);
            }

            $errorCount++;
            $details[] = [
                'type' => 'error',
                'file' => $originalName !== '' ? $originalName : 'Unnamed file',
                'message' => $exception->getMessage(),
            ];
        }
    }

    if ($successCount > 0 && $errorCount === 0) {
        return [
            $successCount === 1
                ? '1 XLSX file uploaded and parsed successfully.'
                : $successCount . ' XLSX files uploaded and parsed successfully.',
            'success',
            $details,
        ];
    }

    if ($successCount > 0 && $errorCount > 0) {
        return [
            $successCount . ' file(s) uploaded successfully and ' . $errorCount . ' file(s) failed.',
            'warning',
            $details,
        ];
    }

    return [
        $errorCount === 1
            ? 'Upload failed for 1 XLSX file.'
            : 'Upload failed for ' . $errorCount . ' XLSX files.',
        'error',
        $details,
    ];
}

function deleteStoredFile(string $relativePath): void
{
    $paths = splitStoredXlsxPaths($relativePath);
    foreach ($paths as $path) {
        $fullPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}

function handleEmployeeDelete(mysqli $conn): array
{
    ensureEmployeeUploadsTable($conn);

    $recordId = (int) ($_POST['record_id'] ?? 0);
    if ($recordId <= 0) {
        return ['Invalid employee record selected for deletion.', 'error'];
    }

    $stmt = $conn->prepare('SELECT xlsx_path FROM leave_employee_uploads WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return ['Unable to prepare the record lookup for deletion.', 'error'];
    }

    $stmt->bind_param('i', $recordId);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$record) {
        return ['The selected employee record was not found.', 'error'];
    }

    $deleteStmt = $conn->prepare('DELETE FROM leave_employee_uploads WHERE id = ?');
    if (!$deleteStmt) {
        return ['Unable to prepare the delete request.', 'error'];
    }

    $deleteStmt->bind_param('i', $recordId);
    $deleteStmt->execute();
    $deleteStmt->close();

    deleteStoredFile((string) ($record['xlsx_path'] ?? ''));

    return ['Employee record deleted successfully.', 'success'];
}

function handleBulkEmployeeDelete(mysqli $conn): array
{
    ensureEmployeeUploadsTable($conn);

    $recordIds = $_POST['record_ids'] ?? [];
    if (!is_array($recordIds) || $recordIds === []) {
        return ['Please select at least one employee record to delete.', 'error'];
    }

    $recordIds = array_values(array_unique(array_filter(array_map(
        static fn (mixed $value): int => (int) $value,
        $recordIds
    ), static fn (int $value): bool => $value > 0)));

    if ($recordIds === []) {
        return ['Please select at least one valid employee record to delete.', 'error'];
    }

    $placeholders = implode(',', array_fill(0, count($recordIds), '?'));
    $types = str_repeat('i', count($recordIds));

    $selectStmt = $conn->prepare("SELECT id, xlsx_path FROM leave_employee_uploads WHERE id IN ($placeholders)");
    if (!$selectStmt) {
        return ['Unable to prepare the selected record lookup.', 'error'];
    }

    $selectStmt->bind_param($types, ...$recordIds);
    $selectStmt->execute();
    $result = $selectStmt->get_result();
    $records = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $selectStmt->close();

    if ($records === []) {
        return ['The selected employee records were not found.', 'error'];
    }

    $deleteStmt = $conn->prepare("DELETE FROM leave_employee_uploads WHERE id IN ($placeholders)");
    if (!$deleteStmt) {
        return ['Unable to prepare the bulk delete request.', 'error'];
    }

    $deleteStmt->bind_param($types, ...$recordIds);
    $deleteStmt->execute();
    $deletedCount = $deleteStmt->affected_rows;
    $deleteStmt->close();

    foreach ($records as $record) {
        deleteStoredFile((string) ($record['xlsx_path'] ?? ''));
    }

    if ($deletedCount <= 0) {
        return ['No employee records were deleted.', 'error'];
    }

    return [
        $deletedCount === 1
            ? '1 employee record deleted successfully.'
            : $deletedCount . ' employee records deleted successfully.',
        'success'
    ];
}

function handleManualEmployeeCreate(mysqli $conn): array
{
    ensureEmployeeUploadsTable($conn);

    $employeeNo = trim((string) ($_POST['employee_no'] ?? ''));
    $surname = trim((string) ($_POST['employee_surname'] ?? ''));
    $firstName = trim((string) ($_POST['employee_first_name'] ?? ''));
    $middleInitial = trim((string) ($_POST['employee_middle_initial'] ?? ''));
    $dateOfBirth = trim((string) ($_POST['employee_date_of_birth'] ?? ''));
    $placeOfBirth = trim((string) ($_POST['employee_place_of_birth'] ?? ''));
    $group = strtoupper(trim((string) ($_POST['employee_group'] ?? '')));
    $status = trim((string) ($_POST['employee_status'] ?? ''));

    if ($employeeNo === '' || $surname === '' || $firstName === '' || $dateOfBirth === '' || $placeOfBirth === '') {
        return ['Please complete all required employee fields before saving.', 'error'];
    }

    if (!in_array($group, ['ES', 'SEC'], true)) {
        return ['Unable to determine the employee group for this record.', 'error'];
    }

    if (!in_array($status, ['Active', 'Inactivation', 'Separation'], true)) {
        return ['Unable to determine the employee status for this record.', 'error'];
    }

    try {
        $result = upsertEmployeeUploadRecord(
            $conn,
            $employeeNo,
            $surname,
            $firstName,
            $middleInitial,
            $dateOfBirth,
            $placeOfBirth,
            $group,
            $status
        );
    } catch (Throwable $exception) {
        return ['Failed to save employee record: ' . $exception->getMessage(), 'error'];
    }

    return [
        ($result['action'] ?? 'inserted') === 'updated'
            ? 'Existing employee record updated successfully.'
            : 'Employee record added successfully.',
        'success'
    ];
}

function handleEmployeeUpdate(mysqli $conn): array
{
    ensureEmployeeUploadsTable($conn);

    $recordId = (int) ($_POST['record_id'] ?? 0);
    $employeeNo = trim((string) ($_POST['employee_no'] ?? ''));
    $surname = trim((string) ($_POST['employee_surname'] ?? ''));
    $firstName = trim((string) ($_POST['employee_first_name'] ?? ''));
    $middleInitial = trim((string) ($_POST['employee_middle_initial'] ?? ''));
    $dateOfBirth = trim((string) ($_POST['employee_date_of_birth'] ?? ''));
    $placeOfBirth = trim((string) ($_POST['employee_place_of_birth'] ?? ''));
    $group = strtoupper(trim((string) ($_POST['employee_group'] ?? '')));
    $status = trim((string) ($_POST['employee_status'] ?? ''));

    if ($recordId <= 0) {
        return ['Invalid employee record selected for update.', 'error'];
    }

    if ($employeeNo === '' || $surname === '' || $firstName === '' || $dateOfBirth === '' || $placeOfBirth === '') {
        return ['Please complete all required employee fields before saving.', 'error'];
    }

    if (!in_array($group, ['ES', 'SEC'], true) || !in_array($status, ['Active', 'Inactivation', 'Separation'], true)) {
        return ['Unable to determine the employee section for this record.', 'error'];
    }

    $stmt = $conn->prepare(
        'UPDATE leave_employee_uploads
         SET employee_no = ?, surname = ?, first_name = ?, middle_initial = ?, date_of_birth = ?, place_of_birth = ?
         WHERE id = ? AND employee_group = ? AND employee_status = ?'
    );

    if (!$stmt) {
        return ['Failed to prepare the employee update query.', 'error'];
    }

    $stmt->bind_param(
        'ssssssiss',
        $employeeNo,
        $surname,
        $firstName,
        $middleInitial,
        $dateOfBirth,
        $placeOfBirth,
        $recordId,
        $group,
        $status
    );

    if (!$stmt->execute()) {
        $errorMessage = $stmt->error !== '' ? $stmt->error : 'Unable to update the employee record.';
        $stmt->close();
        return ['Failed to update employee record: ' . $errorMessage, 'error'];
    }

    if ($stmt->affected_rows < 0) {
        $stmt->close();
        return ['No employee record was updated.', 'error'];
    }

    $stmt->close();
    return ['Employee record updated successfully.', 'success'];
}

function handleEmployeeUpload(mysqli $conn): array
{
    ensureEmployeeUploadsTable($conn);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return consumeEmployeeFlashMessage();
    }

    $formAction = (string) ($_POST['form_action'] ?? '');
    if ($formAction === 'delete_employee_record') {
        [$message, $type] = handleEmployeeDelete($conn);
        setEmployeeFlashMessage($message, $type);
        redirectToCurrentPage(true);
    }

    if ($formAction === 'bulk_delete_employee_records') {
        [$message, $type] = handleBulkEmployeeDelete($conn);
        setEmployeeFlashMessage($message, $type);
        redirectToCurrentPage(true);
    }

    if ($formAction === 'add_employee_record') {
        [$message, $type] = handleManualEmployeeCreate($conn);
        setEmployeeFlashMessage($message, $type);
        redirectToCurrentPage(true);
    }

    if ($formAction === 'edit_employee_record') {
        [$message, $type] = handleEmployeeUpdate($conn);
        setEmployeeFlashMessage($message, $type);
        redirectToCurrentPage(true);
    }

    if ($formAction !== 'upload_employee_record') {
        return consumeEmployeeFlashMessage();
    }

    $group = strtoupper(trim((string) ($_POST['upload_group'] ?? '')));
    $status = trim((string) ($_POST['upload_status'] ?? ''));

    if (!in_array($group, ['ES', 'SEC'], true)) {
        setEmployeeFlashMessage('Please select a valid group before uploading.', 'error');
        redirectToCurrentPage(true);
    }

    if (!in_array($status, ['Active', 'Inactivation', 'Separation'], true)) {
        setEmployeeFlashMessage('Please select a valid status before uploading.', 'error');
        redirectToCurrentPage(true);
    }

    $uploadedFiles = normalizeUploadedFiles($_FILES['employee_file'] ?? []);
    [$message, $type, $details] = processEmployeeUploadBatch($conn, $uploadedFiles, $group, $status);
    respondEmployeeUploadResult($message, $type, $details);
}

function fetchEmployeeUploads(mysqli $conn, string $group, string $status): array
{
    ensureEmployeeUploadsTable($conn);

    $stmt = $conn->prepare(
        'SELECT id, employee_no, surname, first_name, middle_initial, date_of_birth, place_of_birth, xlsx_path, created_at
         FROM leave_employee_uploads
         WHERE employee_group = ? AND employee_status = ?
         ORDER BY surname ASC, first_name ASC, middle_initial ASC, employee_no ASC, id ASC'
    );

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('ss', $group, $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $records = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    foreach ($records as &$record) {
        $record['middle_initial'] = sanitizeMiddleInitialValue((string) ($record['middle_initial'] ?? ''));
    }
    unset($record);

    return $records;
}
