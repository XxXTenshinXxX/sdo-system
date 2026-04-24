<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$userRole = $_SESSION['role'] ?? 'staff';
$normalizedUserRole = strtolower(trim($userRole));
$profileInitial = $normalizedUserRole === 'super admin'
    ? 'SA'
    : strtoupper(substr(trim($userRole), 0, 1));
$roleClass = $normalizedUserRole === 'super admin' ? 'role-super-admin' : '';

$pageTitle = 'PhilHealth Remittance Dashboard';
$activePage = 'dashboard';

$stats = [
    'total_users' => 0,
    'admin_users' => 0,
    'staff_users' => 0,
    'super_admin_users' => 0,
];

$recentUsers = [];

$countQuery = "
    SELECT
        COUNT(*) AS total_users,
        SUM(CASE WHEN LOWER(TRIM(role)) = 'admin' THEN 1 ELSE 0 END) AS admin_users,
        SUM(CASE WHEN LOWER(TRIM(role)) = 'staff' THEN 1 ELSE 0 END) AS staff_users,
        SUM(CASE WHEN LOWER(TRIM(role)) = 'super admin' THEN 1 ELSE 0 END) AS super_admin_users
    FROM users
";
$countResult = mysqli_query($conn, $countQuery);
if ($countResult) {
    $stats = array_merge($stats, mysqli_fetch_assoc($countResult) ?: []);
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
                            <div class="label">Registered Users</div>
                            <div class="value"><?= number_format((int) ($stats['total_users'] ?? 0)) ?></div>
                            <div class="trend up">All accounts with access to the system</div>
                        </div>

                        <div class="stat-card">
                            <div class="label">Admin Accounts</div>
                            <div class="value"><?= number_format((int) ($stats['admin_users'] ?? 0)) ?></div>
                            <div class="trend up">Accounts assigned to admin operations</div>
                        </div>

                        <div class="stat-card">
                            <div class="label">Staff Accounts</div>
                            <div class="value"><?= number_format((int) ($stats['staff_users'] ?? 0)) ?></div>
                            <div class="trend up">Operational users for day-to-day tasks</div>
                        </div>

                        <div class="stat-card">
                            <div class="label">Super Admin</div>
                            <div class="value"><?= number_format((int) ($stats['super_admin_users'] ?? 0)) ?></div>
                            <div class="trend warn">High-level access currently configured</div>
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
                                        <th>ID</th>
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
                                        <?php foreach ($recentUsers as $user): ?>
                                            <tr>
                                                <td><?= number_format((int) ($user['id'] ?? 0)) ?></td>
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
