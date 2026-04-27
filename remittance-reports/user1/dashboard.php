<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../admin/includes/pdf-remittance-service.php';

$userRole = $_SESSION['role'] ?? 'user1';
$normalizedUserRole = strtolower(trim($userRole));
$profileInitial = strtoupper(substr(trim($userRole), 0, 1));
$roleClass = $normalizedUserRole === 'super admin' ? 'role-super-admin' : '';

$pageTitle = 'User1 Dashboard';
$activePage = 'dashboard';

$reports = remittanceFetchStoredReportSummaries('es-shs');
$totalReports = count($reports);
$totalEmployees = 0;
$latestPeriod = '-';
$latestUpload = '-';
$recentReports = array_slice($reports, 0, 5);

if ($reports !== []) {
    $latestHeader = $reports[0]['header'] ?? [];
    $latestPeriod = trim((string) ($latestHeader['applicable_period'] ?? '')) ?: '-';
    $latestUploadValue = trim((string) ($reports[0]['uploaded_at'] ?? ''));
    $latestUpload = $latestUploadValue !== '' ? date('M j, Y h:i A', strtotime($latestUploadValue) ?: time()) : '-';
}

foreach ($reports as $report) {
    $totalEmployees += (int) ($report['employee_count'] ?? 0);
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<body>
    <div class="layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="content">
            <?php include __DIR__ . '/includes/navbar.php'; ?>

            <div class="content-body">

                <section class="section">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="label">Uploaded Reports</div>
                            <div class="value"><?= number_format($totalReports) ?></div>
                            <div class="trend up">Available ES / SHS PDF reports in the system</div>
                        </div>

                        <div class="stat-card">
                            <div class="label">Employees Listed</div>
                            <div class="value"><?= number_format($totalEmployees) ?></div>
                            <div class="trend up">Combined employee rows across uploaded reports</div>
                        </div>

                        <div class="stat-card">
                            <div class="label">Latest Period</div>
                            <div class="value" style="font-size: 24px;"><?= htmlspecialchars($latestPeriod, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="trend warn">Most recent applicable period detected</div>
                        </div>

                        <div class="stat-card">
                            <div class="label">Latest Upload</div>
                            <div class="value" style="font-size: 22px;"><?= htmlspecialchars($latestUpload, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="trend up">Newest ES / SHS report upload timestamp</div>
                        </div>
                    </div>
                </section>

                <section class="section">
                    <div class="main-grid">
                        <div class="panel">
                            <div class="section-header">
                                <h3>Recent ES / SHS Reports</h3>
                                <a href="es-shs.php">Open ES / SHS</a>
                            </div>

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>PDF File</th>
                                        <th>Applicable Period</th>
                                        <th>Employees</th>
                                        <th>Uploaded By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recentReports === []): ?>
                                        <tr>
                                            <td colspan="4">No ES / SHS reports uploaded yet.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentReports as $report): ?>
                                            <?php $header = $report['header'] ?? []; ?>
                                            <tr>
                                                <td><?= htmlspecialchars((string) ($report['file_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string) ($header['applicable_period'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= number_format((int) ($report['employee_count'] ?? 0)) ?></td>
                                                <td><?= htmlspecialchars((string) ($report['uploaded_by'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="panel">
                            <div class="section-header">
                                <h3>Quick Actions</h3>
                            </div>

                            <div class="quick-links">
                                <a href="es-shs.php" class="quick-link">
                                    <i class="fa-solid fa-school"></i>
                                    <div>
                                        <strong>Open ES / SHS Module</strong>
                                        <div>View, upload, and manage ES / SHS remittance reports.</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <?php include __DIR__ . '/../admin/includes/footer.php'; ?>
        </main>
    </div>
</body>
</html>
