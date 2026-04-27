<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/includes/pdf-remittance-service.php';

$userRole = $_SESSION['role'] ?? 'staff';
$normalizedUserRole = strtolower(trim($userRole));
$profileInitial = $normalizedUserRole === 'super admin' ? 'SA' : strtoupper(substr(trim($userRole), 0, 1));
$roleClass = $normalizedUserRole === 'super admin' ? 'role-super-admin' : '';

$pageTitle = 'PhilHealth Remittance Dashboard';
$activePage = 'dashboard';

// Fetch Remittance Stats
$reports = remittanceFetchStoredReportSummaries('es-shs');
$totalReports = count($reports);
$totalEmployees = 0;
$latestPeriod = '-';
$latestUpload = '-';
$recentReports = array_slice($reports, 0, 6);

if ($reports !== []) {
    $latestHeader = $reports[0]['header'] ?? [];
    $latestPeriod = trim((string) ($latestHeader['applicable_period'] ?? '')) ?: '-';
    $latestUploadValue = trim((string) ($reports[0]['uploaded_at'] ?? ''));
    $latestUpload = $latestUploadValue !== '' ? date('M j, Y h:i A', strtotime($latestUploadValue) ?: time()) : '-';
}

foreach ($reports as $report) {
    $totalEmployees += (int) ($report['employee_count'] ?? 0);
}

// Keep User Count for small display if needed, but primary focus is remittances
$userCount = 0;
$userCountQuery = "SELECT COUNT(*) FROM users";
if ($res = mysqli_query($conn, $userCountQuery)) {
    $userCount = (int) mysqli_fetch_row($res)[0];
}

$hasCreatedAt = false;
$columnCheck = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'created_at'");
if ($columnCheck && mysqli_num_rows($columnCheck) > 0) {
    $hasCreatedAt = true;
}

$recentQuery = $hasCreatedAt
    ? "
        SELECT id, email, role, created_at
        FROM users
        ORDER BY created_at DESC, id DESC
        LIMIT 6
    "
    : "
        SELECT id, email, role, NULL AS created_at
        FROM users
        ORDER BY id DESC
        LIMIT 6
    ";

$recentResult = mysqli_query($conn, $recentQuery);
if ($recentResult) {
    $recentUsers = mysqli_fetch_all($recentResult, MYSQLI_ASSOC);
}

function formatRoleClass(string $role): string
{
    return match (strtolower(trim($role))) {
        'super admin' => 'approved',
        'admin' => 'review',
        'staff' => 'pending',
        default => 'review',
    };
}

function formatRoleLabel(string $role): string
{
    $role = trim($role);
    return $role !== '' ? ucwords($role) : 'Unassigned';
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
                            <div class="label">Uploaded Reports</div>
                            <div class="value"><?= number_format($totalReports) ?></div>
                            <div class="trend up">Total ES / SHS PDF reports available</div>
                        </div>

                        <div class="stat-card">
                            <div class="label">Employees Listed</div>
                            <div class="value"><?= number_format($totalEmployees) ?></div>
                            <div class="trend up">Total employee records processed</div>
                        </div>

                        <div class="stat-card">
                            <div class="label">Latest Period</div>
                            <div class="value" style="font-size: 24px;"><?= htmlspecialchars($latestPeriod, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="trend warn">Most recent remittance period</div>
                        </div>

                        <div class="stat-card">
                            <div class="label">Latest Upload</div>
                            <div class="value" style="font-size: 22px;"><?= htmlspecialchars($latestUpload, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="trend up">Last file upload timestamp</div>
                        </div>
                    </div>
                </section>

                <section class="section">
                    <div class="main-grid">
                        <div class="panel">
                            <div class="section-header">
                                <h3>Recent Users</h3>
                                <a href="page/users.php">Open User List</a>
                            </div>

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recentUsers === []): ?>
                                        <tr>
                                            <td colspan="4">No user records available yet.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentUsers as $index => $user): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars((string) ($user['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td>
                                                    <span class="status <?= htmlspecialchars(formatRoleClass((string) ($user['role'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                                                        <?= htmlspecialchars(formatRoleLabel((string) ($user['role'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars(
                                                        !empty($user['created_at']) ? date('M j, Y h:i A', strtotime((string) $user['created_at'])) : 'Not available',
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>
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
                                <a href="page/es-shs.php" class="quick-link">
                                    <i class="fa-solid fa-school"></i>
                                    <div>
                                        <strong>Open ES / SHS</strong>
                                        <div>Proceed to the ES and SHS remittance page.</div>
                                    </div>
                                </a>

                                <a href="page/qes.php" class="quick-link">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                    <div>
                                        <strong>Open QES</strong>
                                        <div>Review quarterly education support remittance records.</div>
                                    </div>
                                </a>

                                <a href="page/users.php" class="quick-link">
                                    <i class="fa-solid fa-users-gear"></i>
                                    <div>
                                        <strong>Manage Users</strong>
                                        <div>Check and maintain accounts with dashboard access.</div>
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
