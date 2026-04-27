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
$profileInitial = $isSuperAdminRole ? 'SA' : strtoupper(substr(trim($userRole), 0, 1));
$roleClass = $isSuperAdminRole || $isAdminLikeRole ? 'role-super-admin' : '';
$pageTitle = 'User3 Activity Log';
$activePage = '';

userActivityMarkCurrentUser();
userActivityLogPageVisit('user3 activity log');
$activityEntries = userActivityFetchAuditEntries(150);
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
                            <h3>System Activity Log</h3>
                            <div class="activity-log-actions">
                                <button type="button" class="activity-log-print-btn" id="printActivityLogBtn">
                                    <i class="fa-solid fa-print"></i>
                                    <span>Print</span>
                                </button>
                                <a href="dashboard.php">Back to Dashboard</a>
                            </div>
                        </div>

                        <table class="table" id="activityLogTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>When</th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>Page</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($activityEntries === []): ?>
                                    <tr>
                                        <td colspan="7">No system activity has been recorded yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($activityEntries as $index => $entry): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($entry['created_at_formatted'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($entry['user_label'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($entry['role_label'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($entry['action_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($entry['description'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($entry['page_path'] ?: 'Not available', ENT_QUOTES, 'UTF-8') ?></td>
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

    <style>
        .activity-log-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .activity-log-print-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            padding: 10px 16px;
            border: 1px solid #bfdbfe;
            border-radius: 14px;
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 14px 24px rgba(37, 99, 235, 0.18);
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }

        .activity-log-print-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 28px rgba(37, 99, 235, 0.22);
            filter: brightness(1.03);
        }

        .activity-log-print-btn i {
            font-size: 13px;
        }
    </style>

    <script>
        (function () {
            const printButton = document.getElementById('printActivityLogBtn');
            const activityLogTable = document.getElementById('activityLogTable');

            if (!printButton || !activityLogTable) {
                return;
            }

            printButton.addEventListener('click', function () {
                const printWindow = window.open('', '_blank', 'width=1200,height=800');
                if (!printWindow) {
                    return;
                }

                printWindow.document.write(`
                    <html>
                    <head>
                        <title>System Activity Log</title>
                        <style>
                            body { font-family: Arial, sans-serif; padding: 24px; color: #0f172a; }
                            h1 { margin: 0 0 8px; font-size: 24px; }
                            p { margin: 0 0 18px; color: #475569; }
                            table { width: 100%; border-collapse: collapse; }
                            th, td { border: 1px solid #cbd5e1; padding: 10px 12px; text-align: left; font-size: 12px; vertical-align: top; }
                            th { background: #eff6ff; }
                        </style>
                    </head>
                    <body>
                        <h1>System Activity Log</h1>
                        <p>Printed on ${new Date().toLocaleString()}</p>
                        ${activityLogTable.outerHTML}
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
            });
        }());
    </script>
</body>
</html>
