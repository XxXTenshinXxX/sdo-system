<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../includes/user-activity.php';

$userRole = $_SESSION['role'] ?? 'staff';
$normalizedUserRole = strtolower(trim($userRole));
$isSuperAdminRole = in_array($normalizedUserRole, ['super admin', 'super_admin', 'superadmin'], true);
$isAdminLikeRole = in_array($normalizedUserRole, ['admin', 'user3', 'admin backup', 'backup admin'], true);
$profileInitial = $isSuperAdminRole
    ? 'SA'
    : strtoupper(substr(trim($userRole), 0, 1));
$roleClass = $isSuperAdminRole || $isAdminLikeRole ? 'role-super-admin' : '';

$pageTitle = 'PhilHealth Remittance Users';
$activePage = 'users';

userActivityMarkCurrentUser();
$systemUsers = userActivityFetchUsersSnapshot();

$stats = [
    'total_users' => count($systemUsers),
    'active_users' => count(array_filter($systemUsers, static fn(array $user): bool => !empty($user['is_active']))),
    'inactive_users' => count(array_filter($systemUsers, static fn(array $user): bool => empty($user['is_active']))),
    'admin_like_users' => count(array_filter($systemUsers, static fn(array $user): bool => in_array($user['role'], ['Super Admin', 'Admin', 'Admin Backup'], true))),
];

function formatUsersDateTime(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return 'Not available';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return 'Not available';
    }

    return date('M j, Y h:i A', $timestamp);
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
                            <div class="value" id="usersTotalCount"><?= number_format($stats['total_users']) ?></div>
                            <div class="trend up">Accounts found in the `users` table</div>
                        </div>

                        <div class="stat-card">
                            <div class="label">Active Now</div>
                            <div class="value" id="usersActiveCount"><?= number_format($stats['active_users']) ?></div>
                            <div class="trend up">Receiving live heartbeat updates</div>
                        </div>

                        <div class="stat-card">
                            <div class="label">Inactive Users</div>
                            <div class="value" id="usersInactiveCount"><?= number_format($stats['inactive_users']) ?></div>
                            <div class="trend warn">No recent activity detected</div>
                        </div>

                        <div class="stat-card">
                            <div class="label">Admin Coverage</div>
                            <div class="value" id="usersAdminLikeCount"><?= number_format($stats['admin_like_users']) ?></div>
                            <div class="trend up">Super admin plus admin backup access</div>
                        </div>
                    </div>
                </section>

                <section class="section">
                    <div class="panel">
                        <div class="section-header">
                            <h3>System Users Table</h3>
                        </div>

                        <table class="table" id="systemUsersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Access</th>
                                    <th>Dashboard</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                </tr>
                            </thead>
                            <tbody id="systemUsersTableBody">
                                <?php if ($systemUsers === []): ?>
                                    <tr>
                                        <td colspan="8">No user records available yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($systemUsers as $index => $systemUser): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($systemUser['display_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($systemUser['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <span class="status <?= htmlspecialchars($systemUser['role_badge_class'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <?= htmlspecialchars($systemUser['role'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($systemUser['access'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($systemUser['dashboard'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <span class="status <?= htmlspecialchars($systemUser['status_badge_class'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <?= htmlspecialchars($systemUser['status_label'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars(formatUsersDateTime($systemUser['last_login_at']), ENT_QUOTES, 'UTF-8') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <?php include __DIR__ . '/includes/footer.php'; ?>
        </main>
    </div>

    <script>
        (function () {
            const statusEndpoint = '../activity-status.php';
            const tableBody = document.getElementById('systemUsersTableBody');
            const totalCount = document.getElementById('usersTotalCount');
            const activeCount = document.getElementById('usersActiveCount');
            const inactiveCount = document.getElementById('usersInactiveCount');
            const adminLikeCount = document.getElementById('usersAdminLikeCount');

            function escapeHtml(value) {
                return String(value ?? '').replace(/[&<>"']/g, function (character) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;'
                    }[character];
                });
            }

            function renderRows(users) {
                if (!tableBody) {
                    return;
                }

                if (!Array.isArray(users) || users.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="8">No user records available yet.</td></tr>';
                    return;
                }

                tableBody.innerHTML = users.map(function (user, index) {
                    return '<tr>'
                        + '<td>' + (index + 1) + '</td>'
                        + '<td>' + escapeHtml(user.display_name) + '</td>'
                        + '<td>' + escapeHtml(user.email) + '</td>'
                        + '<td><span class="status ' + escapeHtml(user.role_badge_class) + '">' + escapeHtml(user.role) + '</span></td>'
                        + '<td>' + escapeHtml(user.access) + '</td>'
                        + '<td>' + escapeHtml(user.dashboard) + '</td>'
                        + '<td><span class="status ' + escapeHtml(user.status_badge_class) + '">' + escapeHtml(user.status_label) + '</span></td>'
                        + '<td>' + escapeHtml(user.last_login_at_formatted || 'Not available') + '</td>'
                        + '</tr>';
                }).join('');
            }

            function updateStats(users) {
                const list = Array.isArray(users) ? users : [];
                const activeUsers = list.filter(function (user) {
                    return Boolean(user.is_active);
                }).length;
                const adminUsers = list.filter(function (user) {
                    return ['Super Admin', 'Admin', 'Admin Backup'].includes(user.role);
                }).length;

                if (totalCount) {
                    totalCount.textContent = list.length.toLocaleString();
                }
                if (activeCount) {
                    activeCount.textContent = activeUsers.toLocaleString();
                }
                if (inactiveCount) {
                    inactiveCount.textContent = (list.length - activeUsers).toLocaleString();
                }
                if (adminLikeCount) {
                    adminLikeCount.textContent = adminUsers.toLocaleString();
                }
            }

            function refreshUsers() {
                fetch(statusEndpoint, {
                    method: 'GET',
                    credentials: 'same-origin',
                    cache: 'no-store',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Unable to refresh user activity.');
                        }
                        return response.json();
                    })
                    .then(function (payload) {
                        const users = Array.isArray(payload.users) ? payload.users.map(function (user) {
                            return Object.assign({}, user, {
                                last_login_at_formatted: user.last_login_at ? new Date(user.last_login_at.replace(' ', 'T')).toLocaleString() : 'Not available'
                            });
                        }) : [];
                        updateStats(users);
                        renderRows(users);
                    })
                    .catch(function () {
                    });
            }

            window.setInterval(refreshUsers, 20000);
            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'visible') {
                    refreshUsers();
                }
            });
        }());
    </script>
</body>
</html>
