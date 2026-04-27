<?php
require_once __DIR__ . '/../../../includes/user-activity.php';

final class RemittanceParseLimitException extends RuntimeException
{
}

if (function_exists('ini_set')) {
    @ini_set('memory_limit', '768M');
}

function remittanceParseIniSizeToBytes(string $value): int
{
    $trimmedValue = trim($value);
    if ($trimmedValue === '' || $trimmedValue === '-1') {
        return 0;
    }

    $unit = strtolower(substr($trimmedValue, -1));
    $size = (float) $trimmedValue;

    return match ($unit) {
        'g' => (int) ($size * 1024 * 1024 * 1024),
        'm' => (int) ($size * 1024 * 1024),
        'k' => (int) ($size * 1024),
        default => (int) $size,
    };
}

function remittanceSetFlash(string $message, string $type): void
{
    $_SESSION['remittance_upload_flash'] = [
        'message' => $message,
        'type' => $type,
    ];
}

function remittanceCurrentUploaderLabel(): string
{
    $role = trim((string) ($_SESSION['role'] ?? ''));
    if ($role !== '') {
        return $role;
    }

    $preferredKeys = ['full_name', 'name', 'username', 'user_name', 'email'];
    foreach ($preferredKeys as $key) {
        $value = trim((string) ($_SESSION[$key] ?? ''));
        if ($value !== '') {
            return $value;
        }
    }

    $userId = trim((string) ($_SESSION['user_id'] ?? ''));
    if ($role !== '' && $userId !== '') {
        return $role . ' #' . $userId;
    }

    if ($role !== '') {
        return $role;
    }

    if ($userId !== '') {
        return 'User #' . $userId;
    }

    return 'Unknown uploader';
}

function remittanceCurrentUserRole(): string
{
    return strtolower(trim((string) ($_SESSION['role'] ?? '')));
}

function remittanceCurrentUserId(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function remittanceCurrentUserLabel(): string
{
    $preferredKeys = ['full_name', 'name', 'username', 'user_name', 'email'];
    foreach ($preferredKeys as $key) {
        $value = trim((string) ($_SESSION[$key] ?? ''));
        if ($value !== '') {
            return $value;
        }
    }

    $role = trim((string) ($_SESSION['role'] ?? ''));
    $userId = remittanceCurrentUserId();
    if ($role !== '' && $userId > 0) {
        return $role . ' #' . $userId;
    }

    return $role !== '' ? $role : 'Unknown user';
}

function remittanceUserRequiresDeleteApproval(): bool
{
    return in_array(remittanceCurrentUserRole(), ['user1', 'user2'], true);
}

function remittanceConsumeFlash(): array
{
    $flash = $_SESSION['remittance_upload_flash'] ?? ['message' => '', 'type' => ''];
    unset($_SESSION['remittance_upload_flash']);

    return [
        (string) ($flash['message'] ?? ''),
        (string) ($flash['type'] ?? ''),
    ];
}

function remittanceRedirectToCurrentPage(): never
{
    $location = $_SERVER['REQUEST_URI'] ?? '';
    if ($location === '') {
        $location = 'dashboard.php';
    }

    header('Location: ' . $location);
    exit;
}

function remittanceSectionLabel(string $section): string
{
    return match ($section) {
        'es-shs' => 'ES / SHS',
        'qes' => 'QES',
        default => 'Unknown',
    };
}

function remittanceUploadsDirectory(string $section): string
{
    return dirname(__DIR__, 2) . '/uploads/' . $section;
}

function remittanceEnsureDirectory(string $directory): void
{
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
}

function remittanceSanitizeFileName(string $fileName): string
{
    $fileName = preg_replace('/[^A-Za-z0-9._-]+/', '-', $fileName) ?? 'report.pdf';
    $fileName = trim($fileName, '-.');

    return $fileName !== '' ? $fileName : 'report.pdf';
}

function remittanceDisplayFileName(string $fileName): string
{
    $baseName = basename(trim($fileName));
    if ($baseName === '') {
        return 'report.pdf';
    }

    $displayName = preg_replace('/^\d+_[a-f0-9]{8}_/i', '', $baseName) ?? $baseName;
    $displayName = trim($displayName);

    return $displayName !== '' ? $displayName : $baseName;
}

function remittanceNormalizeStoredReport(array $report, string $fallbackPdfPath): array
{
    $storedFileName = trim((string) ($report['stored_file_name'] ?? $report['file_name'] ?? basename($fallbackPdfPath)));
    if ($storedFileName === '') {
        $storedFileName = basename($fallbackPdfPath);
    }

    $report['stored_file_name'] = $storedFileName;
    $report['file_name'] = remittanceDisplayFileName((string) ($report['file_name'] ?? $storedFileName));

    return $report;
}

function remittanceDecodePdfText(string $rawText): string
{
    if ($rawText === '') {
        return '';
    }

    // Extremely long PDF string objects are usually layout/content blobs, not the
    // short labels and employee fields we need for remittance parsing.
    if (strlen($rawText) > 4096) {
        return '';
    }

    $text = $rawText;
    $text = str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $text);

    if (str_contains($text, '\\')) {
        $text = preg_replace('/\\\\[nrtbf]/', ' ', $text) ?? $text;
        $text = preg_replace('/\\\\[0-7]{1,3}/', '', $text) ?? $text;
    }

    $text = str_replace(["\r", "\n", "\t", "\f"], ' ', $text);
    while (str_contains($text, '  ')) {
        $text = str_replace('  ', ' ', $text);
    }

    return trim($text);
}

function remittanceExtractPdfStrings(string $pdfPath): array
{
    $tokens = [];
    remittanceStreamPdfTokens($pdfPath, static function (string $decoded) use (&$tokens): void {
        $tokens[] = $decoded;
    });

    return $tokens;
}

function remittanceOpenPdfStream(string $pdfPath)
{
    $handle = @fopen($pdfPath, 'rb');
    if ($handle === false) {
        throw new RuntimeException('Unable to read the uploaded PDF file.');
    }

    return $handle;
}

function remittanceStreamPdfTokens(string $pdfPath, callable $consumer): void
{
    $handle = remittanceOpenPdfStream($pdfPath);
    $tokens = [];
    $buffer = '';
    $chunkSize = 1024 * 1024;

    while (!feof($handle)) {
        $chunk = fread($handle, $chunkSize);
        if ($chunk === false) {
            fclose($handle);
            throw new RuntimeException('Unable to read the uploaded PDF file.');
        }

        if ($chunk === '') {
            continue;
        }

        $buffer .= $chunk;
        remittanceCollectPdfTokensFromBuffer($buffer, $tokens, false, $consumer);
    }

    fclose($handle);
    remittanceCollectPdfTokensFromBuffer($buffer, $tokens, true, $consumer);
}

function remittanceCollectPdfTokensFromBuffer(string &$buffer, array &$tokens, bool $flushAll = false, ?callable $consumer = null): void
{
    if ($buffer === '') {
        return;
    }

    if (!preg_match_all('/\((?:\\\\.|[^\\\\)])*\)/s', $buffer, $matches, PREG_OFFSET_CAPTURE)) {
        if ($flushAll || strlen($buffer) > 2097152) {
            $buffer = '';
        }
        return;
    }

    $lastProcessedOffset = 0;
    foreach ($matches[0] as [$match, $offset]) {
        $decoded = remittanceDecodePdfText(substr($match, 1, -1));
        if ($decoded !== '' && preg_match('/[A-Za-z0-9]/', $decoded)) {
            if ($consumer !== null) {
                $consumer($decoded);
            } else {
                $tokens[] = $decoded;
            }
        }

        $lastProcessedOffset = $offset + strlen($match);
    }

    if ($flushAll) {
        $buffer = '';
        return;
    }

    $buffer = (string) substr($buffer, $lastProcessedOffset);
}

function remittanceFindTokenValue(array $tokens, string $label): string
{
    $index = array_search($label, $tokens, true);
    if ($index === false) {
        return '';
    }

    return trim((string) ($tokens[$index + 1] ?? ''));
}

function remittanceParseEmployeeRows(array $tokens): array
{
    $startIndex = array_search('Status | Remarks', $tokens, true);
    if ($startIndex === false) {
        return [];
    }

    $employees = [];
    $index = $startIndex + 1;
    $totalTokens = count($tokens);

    while ($index + 7 < $totalTokens) {
        $rowNumber = trim($tokens[$index]);

        if (!preg_match('/^\d+$/', $rowNumber)) {
            $index++;
            continue;
        }

        $philHealthNo = trim((string) ($tokens[$index + 1] ?? ''));
        if (!preg_match('/^\d{9,}$/', $philHealthNo)) {
            $index++;
            continue;
        }

        $employees[] = [
            'row_no' => $rowNumber,
            'philhealth_no' => $philHealthNo,
            'surname' => trim((string) ($tokens[$index + 2] ?? '')),
            'given_name' => trim((string) ($tokens[$index + 3] ?? '')),
            'middle_name' => trim((string) ($tokens[$index + 4] ?? '')),
            'ps' => trim((string) ($tokens[$index + 5] ?? '')),
            'es' => trim((string) ($tokens[$index + 6] ?? '')),
            'status' => trim((string) ($tokens[$index + 7] ?? '')),
        ];

        $index += 8;
    }

    return $employees;
}

function remittanceParsePdfReport(string $pdfPath, string $section): array
{
    $maxEmployeeRows = 20000;
    $memoryLimitBytes = remittanceParseIniSizeToBytes((string) ini_get('memory_limit'));
    $memorySafetyThreshold = $memoryLimitBytes > 0
        ? (int) floor($memoryLimitBytes * 0.78)
        : 420 * 1024 * 1024;
    $headerLabels = [
        'SPA No. :' => 'spa_no',
        'Applicable Period :' => 'applicable_period',
        'Document Control Number :' => 'document_control_number',
        'Date/Time Generated :' => 'date_time_generated',
        'Date Received :' => 'date_received',
        'Report Type :' => 'report_type',
        'PhilHealth Number :' => 'philhealth_number',
        'Employer Name :' => 'employer_name',
        'Group Name :' => 'group_name',
        'Employer Address :' => 'employer_address',
        'Employer TIN :' => 'employer_tin',
        'Employer Type :' => 'employer_type',
        'No. of Employees :' => 'employees_reported',
    ];

    $header = array_fill_keys(array_values($headerLabels), '');
    $employees = [];
    $recentTokens = [];
    $employeeWindow = [];
    $inEmployeeSection = false;
    $foundReadableText = false;

    try {
        remittanceStreamPdfTokens($pdfPath, static function (string $token) use (&$header, $headerLabels, &$recentTokens, &$employees, &$employeeWindow, &$inEmployeeSection, &$foundReadableText, $maxEmployeeRows, $memorySafetyThreshold): void {
            $foundReadableText = true;

            $recentTokens[] = $token;
            if (count($recentTokens) > 2) {
                array_shift($recentTokens);
            }

            if (count($recentTokens) === 2 && isset($headerLabels[$recentTokens[0]]) && $header[$headerLabels[$recentTokens[0]]] === '') {
                $header[$headerLabels[$recentTokens[0]]] = trim($recentTokens[1]);
            }

            if ($token === 'Status | Remarks') {
                $inEmployeeSection = true;
                $employeeWindow = [];
                return;
            }

            if (!$inEmployeeSection) {
                return;
            }

            $employeeWindow[] = $token;
            if (count($employeeWindow) > 8) {
                array_shift($employeeWindow);
            }

            if (count($employeeWindow) < 8) {
                return;
            }

            $rowNumber = trim((string) $employeeWindow[0]);
            $philHealthNo = trim((string) $employeeWindow[1]);
            if (!preg_match('/^\d+$/', $rowNumber) || !preg_match('/^\d{9,}$/', $philHealthNo)) {
                return;
            }

            if (count($employees) >= $maxEmployeeRows) {
                throw new RemittanceParseLimitException('This PDF produced too many employee rows and may be too large or malformed to parse safely.');
            }

            if (memory_get_usage(true) >= $memorySafetyThreshold) {
                throw new RemittanceParseLimitException('This PDF is too large to parse safely within the current server memory limit.');
            }

            $employees[] = [
                'row_no' => $rowNumber,
                'philhealth_no' => $philHealthNo,
                'surname' => trim((string) $employeeWindow[2]),
                'given_name' => trim((string) $employeeWindow[3]),
                'middle_name' => trim((string) $employeeWindow[4]),
                'ps' => trim((string) $employeeWindow[5]),
                'es' => trim((string) $employeeWindow[6]),
                'status' => trim((string) $employeeWindow[7]),
            ];

            $employeeWindow = [];
        });
    } catch (RemittanceParseLimitException $exception) {
        throw new RuntimeException($exception->getMessage(), 0, $exception);
    }

    if (!$foundReadableText) {
        throw new RuntimeException('No readable text was found in the uploaded PDF.');
    }

    return [
        'section' => $section,
        'section_label' => remittanceSectionLabel($section),
        'stored_file_name' => basename($pdfPath),
        'file_name' => remittanceDisplayFileName(basename($pdfPath)),
        'header' => $header,
        'employees' => $employees,
        'employee_count' => count($employees),
        'uploaded_by' => remittanceCurrentUploaderLabel(),
        'uploaded_at' => date('Y-m-d H:i:s', filemtime($pdfPath) ?: time()),
    ];
}

function remittanceMetadataPath(string $pdfPath): string
{
    return preg_replace('/\.pdf$/i', '.json', $pdfPath) ?: ($pdfPath . '.json');
}

function remittanceWriteMetadata(string $pdfPath, array $data): void
{
    file_put_contents(remittanceMetadataPath($pdfPath), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function remittanceReportsIndexPath(string $section): string
{
    return remittanceUploadsDirectory($section) . '/.reports-index.json';
}

function remittanceDeleteRequestsPath(): string
{
    return dirname(__DIR__, 2) . '/uploads/.delete-requests.json';
}

function remittanceReadDeleteRequests(): array
{
    $path = remittanceDeleteRequestsPath();
    if (!is_file($path)) {
        return [];
    }

    $payload = json_decode((string) file_get_contents($path), true);
    return is_array($payload) ? array_values(array_filter($payload, 'is_array')) : [];
}

function remittanceWriteDeleteRequests(array $requests): void
{
    file_put_contents(remittanceDeleteRequestsPath(), json_encode(array_values($requests), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function remittanceDeleteRequestStatusLabel(string $status): string
{
    return match ($status) {
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        default => 'Pending',
    };
}

function remittanceCountPendingDeleteRequests(): int
{
    $count = 0;
    foreach (remittanceReadDeleteRequests() as $request) {
        if ((string) ($request['status'] ?? 'pending') === 'pending') {
            $count++;
        }
    }

    return $count;
}

function remittanceSummarizeReport(array $report, string $fallbackPdfPath = ''): array
{
    $normalized = remittanceNormalizeStoredReport($report, $fallbackPdfPath !== '' ? $fallbackPdfPath : ((string) ($report['stored_file_name'] ?? $report['file_name'] ?? 'report.pdf')));

    return [
        'section' => (string) ($normalized['section'] ?? ''),
        'section_label' => (string) ($normalized['section_label'] ?? ''),
        'stored_file_name' => (string) ($normalized['stored_file_name'] ?? ''),
        'file_name' => (string) ($normalized['file_name'] ?? ''),
        'header' => (array) ($normalized['header'] ?? []),
        'employee_count' => (int) ($normalized['employee_count'] ?? 0),
        'uploaded_by' => (string) ($normalized['uploaded_by'] ?? 'Unknown uploader'),
        'uploaded_at' => (string) ($normalized['uploaded_at'] ?? ''),
        'error' => (string) ($normalized['error'] ?? ''),
    ];
}

function remittanceReportSummarySignature(array $pdfFiles): string
{
    $parts = [];
    foreach ($pdfFiles as $pdfPath) {
        $parts[] = basename($pdfPath) . ':' . (string) (filemtime($pdfPath) ?: 0) . ':' . (string) (filesize($pdfPath) ?: 0);
    }

    return sha1(implode('|', $parts));
}

function remittanceSortReports(array &$reports): void
{
    usort($reports, static function (array $left, array $right): int {
        $leftPeriod = (string) (($left['header']['applicable_period'] ?? ''));
        $rightPeriod = (string) (($right['header']['applicable_period'] ?? ''));

        $extractPeriodParts = static function (string $period): array {
            if (preg_match('/(?P<month>\d{1,2})\D+(?P<year>\d{4})/', $period, $matches)) {
                return [
                    'year' => (int) $matches['year'],
                    'month' => (int) $matches['month'],
                ];
            }

            if (preg_match('/(?P<year>\d{4})/', $period, $matches)) {
                return [
                    'year' => (int) $matches['year'],
                    'month' => 0,
                ];
            }

            return [
                'year' => 0,
                'month' => 0,
            ];
        };

        $leftParts = $extractPeriodParts($leftPeriod);
        $rightParts = $extractPeriodParts($rightPeriod);

        if ($leftParts['year'] !== $rightParts['year']) {
            return $rightParts['year'] <=> $leftParts['year'];
        }

        if ($leftParts['month'] !== $rightParts['month']) {
            return $rightParts['month'] <=> $leftParts['month'];
        }

        return strcmp((string) ($right['uploaded_at'] ?? ''), (string) ($left['uploaded_at'] ?? ''));
    });
}

function remittanceWriteReportsIndex(string $section, array $pdfFiles, array $reports): void
{
    $payload = [
        'signature' => remittanceReportSummarySignature($pdfFiles),
        'generated_at' => date('Y-m-d H:i:s'),
        'reports' => array_values($reports),
    ];

    file_put_contents(remittanceReportsIndexPath($section), json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function remittanceFetchStoredReportSummaries(string $section): array
{
    $directory = remittanceUploadsDirectory($section);
    remittanceEnsureDirectory($directory);

    $pdfFiles = glob($directory . '/*.pdf') ?: [];
    rsort($pdfFiles);

    $indexPath = remittanceReportsIndexPath($section);
    $currentSignature = remittanceReportSummarySignature($pdfFiles);

    if (is_file($indexPath)) {
        $storedIndex = json_decode((string) file_get_contents($indexPath), true);
        if (
            is_array($storedIndex)
            && (string) ($storedIndex['signature'] ?? '') === $currentSignature
            && isset($storedIndex['reports'])
            && is_array($storedIndex['reports'])
        ) {
            return array_values(array_filter(array_map(
                static fn(array $report): array => remittanceSummarizeReport($report),
                $storedIndex['reports']
            ), 'is_array'));
        }
    }

    $reports = [];
    foreach ($pdfFiles as $pdfPath) {
        try {
            $reports[] = remittanceSummarizeReport(remittanceLoadReportFromPdf($pdfPath, $section), $pdfPath);
        } catch (Throwable $exception) {
            $reports[] = [
                'section' => $section,
                'section_label' => remittanceSectionLabel($section),
                'stored_file_name' => basename($pdfPath),
                'file_name' => remittanceDisplayFileName(basename($pdfPath)),
                'header' => [
                    'spa_no' => '',
                    'applicable_period' => '',
                    'document_control_number' => '',
                    'date_time_generated' => '',
                    'date_received' => '',
                    'report_type' => '',
                    'philhealth_number' => '',
                    'employer_name' => '',
                    'group_name' => '',
                    'employer_address' => '',
                    'employer_tin' => '',
                    'employer_type' => '',
                    'employees_reported' => '',
                ],
                'employee_count' => 0,
                'uploaded_by' => 'Unknown uploader',
                'uploaded_at' => date('Y-m-d H:i:s', filemtime($pdfPath) ?: time()),
                'error' => $exception->getMessage(),
            ];
        }
    }

    remittanceSortReports($reports);
    remittanceWriteReportsIndex($section, $pdfFiles, $reports);

    return $reports;
}

function remittanceFindStoredReportSummary(string $section, string $fileName): ?array
{
    $cleanFileName = basename(trim($fileName));
    if ($cleanFileName === '') {
        return null;
    }

    foreach (remittanceFetchStoredReportSummaries($section) as $report) {
        if ((string) ($report['stored_file_name'] ?? '') === $cleanFileName) {
            return $report;
        }
    }

    return null;
}

function remittanceLoadReportFromPdf(string $pdfPath, string $section): array
{
    $metadataPath = remittanceMetadataPath($pdfPath);
    $pdfModified = filemtime($pdfPath) ?: 0;
    $metadataModified = is_file($metadataPath) ? (filemtime($metadataPath) ?: 0) : 0;

    if ($metadataModified >= $pdfModified) {
        $stored = json_decode((string) file_get_contents($metadataPath), true);
        if (is_array($stored)) {
            return remittanceNormalizeStoredReport($stored, $pdfPath);
        }
    }

    $parsed = remittanceParsePdfReport($pdfPath, $section);
    remittanceWriteMetadata($pdfPath, $parsed);

    return remittanceNormalizeStoredReport($parsed, $pdfPath);
}

function remittanceFetchStoredReports(string $section): array
{
    $directory = remittanceUploadsDirectory($section);
    remittanceEnsureDirectory($directory);

    $pdfFiles = glob($directory . '/*.pdf') ?: [];
    rsort($pdfFiles);

    $reports = [];
    foreach ($pdfFiles as $pdfPath) {
        try {
            $reports[] = remittanceLoadReportFromPdf($pdfPath, $section);
        } catch (Throwable $exception) {
            $reports[] = [
                'section' => $section,
                'section_label' => remittanceSectionLabel($section),
                'stored_file_name' => basename($pdfPath),
                'file_name' => remittanceDisplayFileName(basename($pdfPath)),
                'header' => [
                    'spa_no' => '',
                    'applicable_period' => '',
                    'document_control_number' => '',
                    'date_time_generated' => '',
                    'date_received' => '',
                    'report_type' => '',
                    'philhealth_number' => '',
                    'employer_name' => '',
                    'group_name' => '',
                    'employer_address' => '',
                    'employer_tin' => '',
                    'employer_type' => '',
                    'employees_reported' => '',
                ],
                'employees' => [],
                'employee_count' => 0,
                'uploaded_by' => 'Unknown uploader',
                'uploaded_at' => date('Y-m-d H:i:s', filemtime($pdfPath) ?: time()),
                'error' => $exception->getMessage(),
            ];
        }
    }

    remittanceSortReports($reports);

    return $reports;
}

function remittanceFetchSingleReport(string $section, string $fileName): array
{
    $cleanFileName = basename(trim($fileName));
    if ($cleanFileName === '' || strtolower(pathinfo($cleanFileName, PATHINFO_EXTENSION)) !== 'pdf') {
        throw new RuntimeException('Invalid PDF report selected.');
    }

    $pdfPath = remittanceUploadsDirectory($section) . '/' . $cleanFileName;
    if (!is_file($pdfPath)) {
        throw new RuntimeException('The selected PDF report could not be found.');
    }

    return remittanceLoadReportFromPdf($pdfPath, $section);
}

function remittanceBuildContributionHistoryIndex(string $section, array $employees): array
{
    $targetPhilHealthNumbers = [];
    foreach ($employees as $employee) {
        $philHealthNo = trim((string) ($employee['philhealth_no'] ?? ''));
        if ($philHealthNo !== '') {
            $targetPhilHealthNumbers[$philHealthNo] = true;
        }
    }

    if ($targetPhilHealthNumbers === []) {
        return [];
    }

    $historyIndex = [];
    foreach (array_keys($targetPhilHealthNumbers) as $philHealthNo) {
        $historyIndex[$philHealthNo] = [];
    }

    $storedReports = remittanceFetchStoredReports($section);
    foreach ($storedReports as $storedReport) {
        $reportHeader = $storedReport['header'] ?? [];
        foreach (($storedReport['employees'] ?? []) as $historyEmployee) {
            $philHealthNo = trim((string) ($historyEmployee['philhealth_no'] ?? ''));
            if ($philHealthNo === '' || !isset($targetPhilHealthNumbers[$philHealthNo])) {
                continue;
            }

            $historyIndex[$philHealthNo][] = [
                'file_name' => (string) ($storedReport['file_name'] ?? ''),
                'applicable_period' => (string) ($reportHeader['applicable_period'] ?? ''),
                'date_received' => (string) ($reportHeader['date_received'] ?? ''),
                'date_time_generated' => (string) ($reportHeader['date_time_generated'] ?? ''),
                'document_control_number' => (string) ($reportHeader['document_control_number'] ?? ''),
                'report_type' => (string) ($reportHeader['report_type'] ?? ''),
                'uploaded_at' => (string) ($storedReport['uploaded_at'] ?? ''),
                'row_no' => (string) ($historyEmployee['row_no'] ?? ''),
                'philhealth_no' => $philHealthNo,
                'surname' => (string) ($historyEmployee['surname'] ?? ''),
                'given_name' => (string) ($historyEmployee['given_name'] ?? ''),
                'middle_name' => (string) ($historyEmployee['middle_name'] ?? ''),
                'ps' => (string) ($historyEmployee['ps'] ?? ''),
                'es' => (string) ($historyEmployee['es'] ?? ''),
                'status' => (string) ($historyEmployee['status'] ?? ''),
            ];
        }
    }

    foreach ($historyIndex as &$historyEntries) {
        usort($historyEntries, static function (array $left, array $right): int {
            return strcmp((string) ($right['uploaded_at'] ?? ''), (string) ($left['uploaded_at'] ?? ''));
        });
    }
    unset($historyEntries);

    return $historyIndex;
}

function remittanceFetchContributionHistory(string $section, string $philHealthNo): array
{
    $normalizedPhilHealthNo = trim($philHealthNo);
    if ($normalizedPhilHealthNo === '') {
        return [];
    }

    $directory = remittanceUploadsDirectory($section);
    remittanceEnsureDirectory($directory);

    $pdfFiles = glob($directory . '/*.pdf') ?: [];
    rsort($pdfFiles);

    $historyEntries = [];
    foreach ($pdfFiles as $pdfPath) {
        try {
            $storedReport = remittanceLoadReportFromPdf($pdfPath, $section);
        } catch (Throwable) {
            continue;
        }

        $reportHeader = $storedReport['header'] ?? [];
        foreach (($storedReport['employees'] ?? []) as $historyEmployee) {
            $employeePhilHealthNo = trim((string) ($historyEmployee['philhealth_no'] ?? ''));
            if ($employeePhilHealthNo !== $normalizedPhilHealthNo) {
                continue;
            }

            $historyEntries[] = [
                'file_name' => (string) ($storedReport['file_name'] ?? ''),
                'applicable_period' => (string) ($reportHeader['applicable_period'] ?? ''),
                'date_received' => (string) ($reportHeader['date_received'] ?? ''),
                'date_time_generated' => (string) ($reportHeader['date_time_generated'] ?? ''),
                'document_control_number' => (string) ($reportHeader['document_control_number'] ?? ''),
                'report_type' => (string) ($reportHeader['report_type'] ?? ''),
                'uploaded_at' => (string) ($storedReport['uploaded_at'] ?? ''),
                'row_no' => (string) ($historyEmployee['row_no'] ?? ''),
                'philhealth_no' => $employeePhilHealthNo,
                'surname' => (string) ($historyEmployee['surname'] ?? ''),
                'given_name' => (string) ($historyEmployee['given_name'] ?? ''),
                'middle_name' => (string) ($historyEmployee['middle_name'] ?? ''),
                'ps' => (string) ($historyEmployee['ps'] ?? ''),
                'es' => (string) ($historyEmployee['es'] ?? ''),
                'status' => (string) ($historyEmployee['status'] ?? ''),
            ];
        }
    }

    usort($historyEntries, static function (array $left, array $right): int {
        return strcmp((string) ($right['uploaded_at'] ?? ''), (string) ($left['uploaded_at'] ?? ''));
    });

    return $historyEntries;
}

function remittanceNormalizeUploadedFiles(array $files): array
{
    $names = $files['name'] ?? [];
    if (!is_array($names)) {
        return $files !== [] ? [$files] : [];
    }

    $normalized = [];
    foreach ($names as $index => $name) {
        $normalized[] = [
            'name' => $name,
            'type' => $files['type'][$index] ?? '',
            'tmp_name' => $files['tmp_name'][$index] ?? '',
            'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$index] ?? 0,
        ];
    }

    return $normalized;
}

function remittanceDeleteStoredReportFiles(string $section, string $fileName): bool
{
    $cleanFileName = basename(trim($fileName));
    if ($cleanFileName === '' || strtolower(pathinfo($cleanFileName, PATHINFO_EXTENSION)) !== 'pdf') {
        return false;
    }

    $pdfPath = remittanceUploadsDirectory($section) . '/' . $cleanFileName;
    if (!is_file($pdfPath)) {
        return false;
    }

    $metadataPath = remittanceMetadataPath($pdfPath);
    @unlink($pdfPath);
    if (is_file($metadataPath)) {
        @unlink($metadataPath);
    }

    return true;
}

function remittanceCreateDeleteRequests(string $section, array $fileNames): void
{
    $requests = remittanceReadDeleteRequests();
    $createdCount = 0;
    $skippedCount = 0;

    foreach ($fileNames as $fileName) {
        $cleanFileName = basename(trim((string) $fileName));
        if ($cleanFileName === '' || strtolower(pathinfo($cleanFileName, PATHINFO_EXTENSION)) !== 'pdf') {
            $skippedCount++;
            continue;
        }

        $report = remittanceFindStoredReportSummary($section, $cleanFileName);
        if ($report === null) {
            $skippedCount++;
            continue;
        }

        $hasPendingRequest = false;
        foreach ($requests as $existingRequest) {
            if (
                (string) ($existingRequest['status'] ?? 'pending') === 'pending'
                && (string) ($existingRequest['section'] ?? '') === $section
                && (string) ($existingRequest['report_file'] ?? '') === $cleanFileName
            ) {
                $hasPendingRequest = true;
                break;
            }
        }

        if ($hasPendingRequest) {
            $skippedCount++;
            continue;
        }

        $requests[] = [
            'id' => uniqid('delreq_', true),
            'section' => $section,
            'section_label' => remittanceSectionLabel($section),
            'report_file' => $cleanFileName,
            'report_name' => (string) ($report['file_name'] ?? remittanceDisplayFileName($cleanFileName)),
            'uploaded_by' => (string) ($report['uploaded_by'] ?? 'Unknown uploader'),
            'requested_by' => remittanceCurrentUserLabel(),
            'requested_by_role' => trim((string) ($_SESSION['role'] ?? '')),
            'requested_by_user_id' => remittanceCurrentUserId(),
            'requested_at' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'reviewed_by' => '',
            'reviewed_by_role' => '',
            'reviewed_by_user_id' => 0,
            'reviewed_at' => '',
        ];
        $createdCount++;
    }

    if ($createdCount > 0) {
        remittanceWriteDeleteRequests($requests);
        userActivityLogEvent('request_delete_report', 'Requested report deletion approval', [
            'section' => $section,
            'requested_count' => $createdCount,
        ]);
    }

    if ($createdCount === 0) {
        remittanceSetFlash('No new delete requests were created. The selected reports may already have pending requests.', 'warning');
        remittanceRedirectToCurrentPage();
    }

    $message = $createdCount === 1
        ? '1 delete request was sent for approval.'
        : $createdCount . ' delete requests were sent for approval.';

    if ($skippedCount > 0) {
        $message .= ' ' . $skippedCount . ' item(s) were skipped because they were invalid or already pending.';
    }

    remittanceSetFlash($message, 'success');
    remittanceRedirectToCurrentPage();
}

function remittanceDeleteStoredReport(string $section, string $fileName): void
{
    $cleanFileName = basename(trim($fileName));
    if ($cleanFileName === '' || strtolower(pathinfo($cleanFileName, PATHINFO_EXTENSION)) !== 'pdf') {
        remittanceSetFlash('Invalid PDF file selected for deletion.', 'error');
        remittanceRedirectToCurrentPage();
    }

    if (remittanceUserRequiresDeleteApproval()) {
        remittanceCreateDeleteRequests($section, [$cleanFileName]);
    }

    if (!remittanceDeleteStoredReportFiles($section, $cleanFileName)) {
        remittanceSetFlash('The selected PDF report could not be found.', 'error');
        remittanceRedirectToCurrentPage();
    }

    userActivityLogEvent('delete_report', 'Deleted a remittance report', [
        'section' => $section,
        'file_name' => $cleanFileName,
    ]);
    remittanceSetFlash('PDF report deleted successfully.', 'success');
    remittanceRedirectToCurrentPage();
}

function remittanceDeleteStoredReports(string $section, array $fileNames): void
{
    if (remittanceUserRequiresDeleteApproval()) {
        remittanceCreateDeleteRequests($section, $fileNames);
    }

    $deletedCount = 0;

    foreach ($fileNames as $fileName) {
        $cleanFileName = basename(trim((string) $fileName));
        if ($cleanFileName === '' || strtolower(pathinfo($cleanFileName, PATHINFO_EXTENSION)) !== 'pdf') {
            continue;
        }

        if (remittanceDeleteStoredReportFiles($section, $cleanFileName)) {
            $deletedCount++;
        }
    }

    if ($deletedCount === 0) {
        remittanceSetFlash('No valid PDF reports were selected for deletion.', 'error');
        remittanceRedirectToCurrentPage();
    }

    userActivityLogEvent('bulk_delete_reports', 'Deleted multiple remittance reports', [
        'section' => $section,
        'deleted_count' => $deletedCount,
    ]);
    remittanceSetFlash($deletedCount === 1 ? '1 PDF report deleted successfully.' : $deletedCount . ' PDF reports deleted successfully.', 'success');
    remittanceRedirectToCurrentPage();
}

function remittanceHandlePdfUpload(string $section): array
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return remittanceConsumeFlash();
    }

    if (($_POST['form_action'] ?? '') === 'delete_pdf_report' && ($_POST['report_section'] ?? '') === $section) {
        remittanceDeleteStoredReport($section, (string) ($_POST['report_file'] ?? ''));
    }

    if (($_POST['form_action'] ?? '') === 'delete_selected_pdf_reports' && ($_POST['report_section'] ?? '') === $section) {
        $selectedFiles = $_POST['report_files'] ?? [];
        remittanceDeleteStoredReports($section, is_array($selectedFiles) ? $selectedFiles : []);
    }

    if (($_POST['form_action'] ?? '') !== 'upload_pdf_report' || ($_POST['report_section'] ?? '') !== $section) {
        return remittanceConsumeFlash();
    }

    $files = remittanceNormalizeUploadedFiles($_FILES['report_pdf'] ?? []);
    if ($files === []) {
        remittanceSetFlash('Please choose at least one PDF file to upload.', 'error');
        remittanceRedirectToCurrentPage();
    }

    $directory = remittanceUploadsDirectory($section);
    remittanceEnsureDirectory($directory);

    $successCount = 0;
    $errorMessages = [];

    foreach ($files as $file) {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $errorMessages[] = 'One of the selected files could not be uploaded.';
            continue;
        }

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            $errorMessages[] = '"' . (string) ($file['name'] ?? 'Unknown file') . '" is not a PDF file.';
            continue;
        }

        $safeName = time() . '_' . bin2hex(random_bytes(4)) . '_' . remittanceSanitizeFileName((string) ($file['name'] ?? 'report.pdf'));
        $targetPath = $directory . '/' . $safeName;

        if (!move_uploaded_file((string) $file['tmp_name'], $targetPath)) {
            $errorMessages[] = 'Failed to save "' . (string) ($file['name'] ?? 'Unknown file') . '".';
            continue;
        }

        try {
            $parsed = remittanceParsePdfReport($targetPath, $section);
            remittanceWriteMetadata($targetPath, $parsed);
            userActivityLogEvent('upload_report', 'Uploaded a remittance report', [
                'section' => $section,
                'file_name' => (string) ($file['name'] ?? 'report.pdf'),
            ]);
            $successCount++;
        } catch (Throwable $exception) {
            @unlink($targetPath);
            $errorMessages[] = '"' . (string) ($file['name'] ?? 'Unknown file') . '" could not be parsed: ' . $exception->getMessage();
        }
    }

    if ($successCount > 0 && $errorMessages === []) {
        remittanceSetFlash($successCount === 1 ? '1 PDF uploaded successfully.' : $successCount . ' PDF files uploaded successfully.', 'success');
        remittanceRedirectToCurrentPage();
    }

    if ($successCount > 0) {
        remittanceSetFlash($successCount . ' file(s) uploaded successfully. ' . implode(' ', $errorMessages), 'warning');
        remittanceRedirectToCurrentPage();
    }

    remittanceSetFlash(implode(' ', $errorMessages) ?: 'No files were uploaded.', 'error');
    remittanceRedirectToCurrentPage();
}

function remittanceHandleDeleteRequestReview(): array
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || (string) ($_POST['form_action'] ?? '') !== 'review_delete_request') {
        return remittanceConsumeFlash();
    }

    $decision = strtolower(trim((string) ($_POST['decision'] ?? '')));
    $requestId = trim((string) ($_POST['request_id'] ?? ''));
    if ($requestId === '' || !in_array($decision, ['approve', 'reject'], true)) {
        remittanceSetFlash('Invalid delete request action.', 'error');
        remittanceRedirectToCurrentPage();
    }

    $requests = remittanceReadDeleteRequests();
    $foundIndex = null;
    foreach ($requests as $index => $request) {
        if ((string) ($request['id'] ?? '') === $requestId) {
            $foundIndex = $index;
            break;
        }
    }

    if ($foundIndex === null) {
        remittanceSetFlash('Delete request not found.', 'error');
        remittanceRedirectToCurrentPage();
    }

    $request = $requests[$foundIndex];
    if ((string) ($request['status'] ?? 'pending') !== 'pending') {
        remittanceSetFlash('This delete request was already reviewed.', 'warning');
        remittanceRedirectToCurrentPage();
    }

    $requests[$foundIndex]['status'] = $decision === 'approve' ? 'approved' : 'rejected';
    $requests[$foundIndex]['reviewed_by'] = remittanceCurrentUserLabel();
    $requests[$foundIndex]['reviewed_by_role'] = trim((string) ($_SESSION['role'] ?? ''));
    $requests[$foundIndex]['reviewed_by_user_id'] = remittanceCurrentUserId();
    $requests[$foundIndex]['reviewed_at'] = date('Y-m-d H:i:s');

    if ($decision === 'approve') {
        if (!remittanceDeleteStoredReportFiles((string) ($request['section'] ?? ''), (string) ($request['report_file'] ?? ''))) {
            remittanceSetFlash('The requested PDF report could not be found. The request was left pending.', 'error');
            remittanceRedirectToCurrentPage();
        }

        userActivityLogEvent('approve_delete_request', 'Approved report deletion request', [
            'section' => (string) ($request['section'] ?? ''),
            'file_name' => (string) ($request['report_file'] ?? ''),
            'requested_by' => (string) ($request['requested_by'] ?? ''),
        ]);
        remittanceWriteDeleteRequests($requests);
        remittanceSetFlash('Delete request approved and report deleted successfully.', 'success');
        remittanceRedirectToCurrentPage();
    }

    userActivityLogEvent('reject_delete_request', 'Rejected report deletion request', [
        'section' => (string) ($request['section'] ?? ''),
        'file_name' => (string) ($request['report_file'] ?? ''),
        'requested_by' => (string) ($request['requested_by'] ?? ''),
    ]);
    remittanceWriteDeleteRequests($requests);
    remittanceSetFlash('Delete request rejected successfully.', 'success');
    remittanceRedirectToCurrentPage();
}
