<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

$userRole = $_SESSION['role'] ?? 'staff';
$normalizedUserRole = strtolower(trim($userRole));
$profileInitial = $normalizedUserRole === 'super admin'
    ? 'SA'
    : strtoupper(substr(trim($userRole), 0, 1));
$roleClass = $normalizedUserRole === 'super admin' ? 'role-super-admin' : '';

$pageTitle = 'PhilHealth Remittance Users';
$activePage = 'users';

$systemUsers = [
    [
        'display_name' => 'Superadmin (You)',
        'role' => 'Super Admin',
        'access' => 'Full access to admin and backup dashboard',
        'dashboard' => 'Admin / User 3 Backup',
        'status' => 'Active',
    ],
    [
        'display_name' => 'User 1',
        'role' => 'Staff',
        'access' => 'ES / SHS access',
        'dashboard' => 'User 1',
        'status' => 'Active',
    ],
    [
        'display_name' => 'User 2',
        'role' => 'Staff',
        'access' => 'QES access',
        'dashboard' => 'User 2',
        'status' => 'Active',
    ],
    [
        'display_name' => 'User 3',
        'role' => 'Admin Backup',
        'access' => 'Backup copy of admin dashboard',
        'dashboard' => 'User 3 Backup',
        'status' => 'Active',
    ],
];

$stats = [
    'total_users' => count($systemUsers),
    'admin_like_users' => 2,
    'staff_users' => 2,
];

function formatUserBadgeClass(string $role): string
{
    return match (strtolower(trim($role))) {
        'super admin' => 'approved',
        'admin backup' => 'review',
        default => 'pending',
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
                        <h3>Users</h3>
                        <a href="users.php">Refresh List</a>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="label">Visible Accounts</div>
                            <div class="value"><?= number_format($stats['total_users']) ?></div>
                            <div class="trend up">Superadmin, User 1, User 2, and User 3</div>
                        </div>

                        <div class="stat-card">
                            <div class="label">Admin Coverage</div>
                            <div class="value"><?= number_format($stats['admin_like_users']) ?></div>
                            <div class="trend up">Primary admin plus backup dashboard</div>
                        </div>

                        <div class="stat-card">
                            <div class="label">Staff Accounts</div>
                            <div class="value"><?= number_format($stats['staff_users']) ?></div>
                            <div class="trend up">User 1 and User 2 operational access</div>
                        </div>

                        <div class="stat-card">
                            <div class="label">Backup Sync</div>
                            <div class="value">ON</div>
                            <div class="trend warn">`user3` mirrors this admin users page</div>
                        </div>
                    </div>
                </section>

                <section class="section">
                    <div class="panel">
                        <div class="section-header">
                            <h3>System Users Table</h3>
                        </div>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Access</th>
                                    <th>Dashboard</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($systemUsers as $index => $systemUser): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($systemUser['display_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <span class="status <?= htmlspecialchars(formatUserBadgeClass($systemUser['role']), ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($systemUser['role'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($systemUser['access'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($systemUser['dashboard'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($systemUser['status'], ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <?php include __DIR__ . '/includes/footer.php'; ?>
        </main>
    </div>
</body>
</html>
