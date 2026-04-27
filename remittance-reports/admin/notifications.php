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

$profileInitial = $isSuperAdminRole
    ? 'SA'
    : strtoupper(substr(trim($userRole), 0, 1));
$roleClass = $isSuperAdminRole ? 'role-super-admin' : '';

$pageTitle = 'All Notifications';
$activePage = 'notifications';

userActivityMarkCurrentUser();
$allNotifications = userActivityFetchNotifications($userRole, 100);
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
                        <h3>Notifications History</h3>
                        <p>A complete list of recent system alerts and remittance updates.</p>
                    </div>

                    <div class="panel">
                        <div class="notification-full-list">
                            <?php if ($allNotifications === []): ?>
                                <div class="empty-state" style="padding: 60px; text-align: center; color: #64748b;">
                                    <i class="fa-solid fa-bell-slash" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                                    <p>No notifications found yet.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($allNotifications as $notif): ?>
                                    <div class="notif-full-item" style="display: flex; gap: 16px; padding: 20px; border-bottom: 1px solid #f1f5f9; align-items: flex-start;">
                                        <div class="notif-full-icon" style="width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: #f8fafc; flex-shrink: 0;">
                                            <span class="notification-dot <?= htmlspecialchars($notif['dot_class'], ENT_QUOTES, 'UTF-8') ?>" style="position: static; margin: 0;"></span>
                                        </div>
                                        <div style="flex: 1;">
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                                <strong style="color: #0f172a; text-transform: uppercase; font-size: 13px; letter-spacing: 0.05em;"><?= htmlspecialchars($notif['type'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                <span style="color: #64748b; font-size: 12px;"><?= htmlspecialchars($notif['created_at_formatted'], ENT_QUOTES, 'UTF-8') ?></span>
                                            </div>
                                            <p style="margin: 0; color: #334155; line-height: 1.5; font-size: 14px;"><?= htmlspecialchars($notif['message'], ENT_QUOTES, 'UTF-8') ?></p>
                                            <small style="color: #94a3b8; font-size: 11px; margin-top: 6px; display: block;"><?= htmlspecialchars(date('M j, Y h:i A', strtotime($notif['created_at'])), ENT_QUOTES, 'UTF-8') ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>

            <?php include __DIR__ . '/includes/footer.php'; ?>
        </main>
    </div>

    <style>
        .notif-full-item:last-child {
            border-bottom: none;
        }
        .notif-full-item:hover {
            background-color: #f8fafc;
        }
        .notif-full-icon .notification-dot {
            width: 12px;
            height: 12px;
        }
        .notif-full-icon .notification-dot.is-success { background: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); }
        .notif-full-icon .notification-dot.is-danger { background: #ef4444; box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1); }
        .notif-full-icon .notification-dot.is-warn { background: #f59e0b; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1); }
        .notif-full-icon .notification-dot.is-info { background: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
    </style>
</body>
</html>
