<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$userRole = $_SESSION['role'] ?? 'staff';
$normalizedUserRole = strtolower(trim($userRole));
$profileInitial = $normalizedUserRole === 'super admin'
    ? 'SA'
    : strtoupper(substr(trim($userRole), 0, 1));
$roleClass = $normalizedUserRole === 'super admin' ? 'role-super-admin' : '';
$pageTitle = 'Leave Monitoring Dashboard';
$activePage = 'dashboard';

$stats = [
    'total_records' => 0,
    'es_total' => 0,
    'sec_total' => 0,
    'active_total' => 0,
];
$recentLeaves = [];

$countQuery = "
    SELECT
        COUNT(*) AS total_records,
        SUM(CASE WHEN employee_group = 'ES' THEN 1 ELSE 0 END) AS es_total,
        SUM(CASE WHEN employee_group = 'SEC' THEN 1 ELSE 0 END) AS sec_total,
        SUM(CASE WHEN employee_status = 'Active' THEN 1 ELSE 0 END) AS active_total
    FROM leave_employee_uploads
";
$countResult = mysqli_query($conn, $countQuery);
if ($countResult) {
    $stats = array_merge($stats, mysqli_fetch_assoc($countResult) ?: []);
}

$recentQuery = "
    SELECT
        surname,
        first_name,
        middle_initial,
        employee_group,
        employee_status,
        created_at
    FROM leave_employee_uploads
    ORDER BY created_at DESC, id DESC
    LIMIT 6
";
$recentResult = mysqli_query($conn, $recentQuery);
if ($recentResult) {
    $recentLeaves = mysqli_fetch_all($recentResult, MYSQLI_ASSOC);
}

function formatDashboardEmployeeName(array $record): string
{
    $surname = trim((string) ($record['surname'] ?? ''));
    $firstName = trim((string) ($record['first_name'] ?? ''));
    $middleInitial = trim((string) ($record['middle_initial'] ?? ''));

    $name = $surname;
    if ($firstName !== '') {
        $name .= ($name !== '' ? ', ' : '') . $firstName;
    }
    if ($middleInitial !== '') {
        $name .= ' ' . $middleInitial;
    }

    return $name !== '' ? $name : 'Unknown Employee';
}

function formatDashboardStatusClass(string $status): string
{
    return match (strtolower(trim($status))) {
        'active' => 'approved',
        'inactivation' => 'review',
        'separation' => 'pending',
        default => 'review',
    };
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
                    <div class="section-header">
                        <h3>Overview</h3>
                        <a href="dashboard.php">Refresh Data</a>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="label">Total Employee Records</div>
                            <div class="value"><?= number_format((int) ($stats['total_records'] ?? 0)) ?></div>
                            <div class="trend up">All uploaded leave monitoring records</div>
                        </div>

                        <div class="stat-card">
                            <div class="label">ES Records</div>
                            <div class="value"><?= number_format((int) ($stats['es_total'] ?? 0)) ?></div>
                            <div class="trend up">Employee records under ES</div>
                        </div>

                        <div class="stat-card">
                            <div class="label">SEC Records</div>
                            <div class="value"><?= number_format((int) ($stats['sec_total'] ?? 0)) ?></div>
                            <div class="trend up">Employee records under SEC</div>
                        </div>

                        <div class="stat-card">
                            <div class="label">Active Records</div>
                            <div class="value"><?= number_format((int) ($stats['active_total'] ?? 0)) ?></div>
                            <div class="trend warn">Current records tagged as active</div>
                        </div>
                    </div>
                </section>

                <section class="section">
                    <div class="main-grid">
                        <div class="panel">
                            <div class="section-header">
                                <h3>Recent Leave</h3>
                                <a href="es-active.php">Open Records</a>
                            </div>

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Group</th>
                                        <th>Status</th>
                                        <th>Uploaded</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recentLeaves === []): ?>
                                        <tr>
                                            <td colspan="4">No recent leave records available yet.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentLeaves as $recentLeave): ?>
                                            <?php $statusValue = (string) ($recentLeave['employee_status'] ?? ''); ?>
                                            <tr>
                                                <td><?= htmlspecialchars(formatDashboardEmployeeName($recentLeave), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string) ($recentLeave['employee_group'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td>
                                                    <span class="status <?= htmlspecialchars(formatDashboardStatusClass($statusValue), ENT_QUOTES, 'UTF-8') ?>">
                                                        <?= htmlspecialchars($statusValue !== '' ? $statusValue : 'Unknown', ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars(date('M j, Y h:i A', strtotime((string) ($recentLeave['created_at'] ?? 'now'))), ENT_QUOTES, 'UTF-8') ?>
                                                </td>
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
                                <a href="es-active.php" class="quick-link">
                                    <i class="fa-solid fa-users"></i>
                                    <div>
                                        <strong>Open ES Active</strong>
                                        <div>Review and upload ES active employee forms</div>
                                    </div>
                                </a>

                                <a href="sec-active.php" class="quick-link">
                                    <i class="fa-solid fa-building-columns"></i>
                                    <div>
                                        <strong>Open SEC Active</strong>
                                        <div>Manage SEC active employee leave records</div>
                                    </div>
                                </a>

                                <a href="es-separation.php" class="quick-link">
                                    <i class="fa-solid fa-file-lines"></i>
                                    <div>
                                        <strong>Check Separation</strong>
                                        <div>View employee forms tagged for separation</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <?php include __DIR__ . '/includes/footer.php'; ?>
        </main>
    </div>
</body>
</html>
