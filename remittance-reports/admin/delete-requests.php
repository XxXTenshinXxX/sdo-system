<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../includes/user-activity.php';
require_once __DIR__ . '/includes/pdf-remittance-service.php';

$userRole = $_SESSION['role'] ?? 'staff';
$normalizedUserRole = strtolower(trim($userRole));
$profileInitial = in_array($normalizedUserRole, ['super admin', 'super_admin', 'superadmin'], true)
    ? 'SA'
    : strtoupper(substr(trim($userRole), 0, 1));
$roleClass = in_array($normalizedUserRole, ['super admin', 'super_admin', 'superadmin'], true) ? 'role-super-admin' : '';
$pageTitle = 'Admin Delete Requests';
$activePage = 'delete-requests';
$requests = remittanceReadDeleteRequests();
usort($requests, static function (array $left, array $right): int {
    $leftTime = (string) ($left['requested_at'] ?? '');
    $rightTime = (string) ($right['requested_at'] ?? '');
    return strcmp($rightTime, $leftTime);
});

[$flashMessage, $flashType] = remittanceHandleDeleteRequestReview();
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
                            <h3>Delete Requests</h3>
                            <span><?= remittanceCountPendingDeleteRequests() ?> pending</span>
                        </div>

                        <?php if ($flashMessage !== ''): ?>
                            <div class="alert-banner alert-<?= htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8') ?>">
                                <div class="alert-banner-title"><?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="report-table-shell">
                            <table class="table report-summary-table">
                                <thead>
                                    <tr>
                                        <th>Report</th>
                                        <th>Section</th>
                                        <th>Requested By</th>
                                        <th>Uploaded By</th>
                                        <th>Requested At</th>
                                        <th>Status</th>
                                        <th>Reviewed By</th>
                                        <th class="action-cell">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($requests === []): ?>
                                        <tr>
                                            <td colspan="8" class="empty-state-cell">No delete requests found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($requests as $request): ?>
                                            <?php $isPending = (string) ($request['status'] ?? 'pending') === 'pending'; ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars((string) ($request['report_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong>
                                                    <div class="request-subtext"><?= htmlspecialchars((string) ($request['report_file'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                                </td>
                                                <td><?= htmlspecialchars((string) ($request['section_label'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars((string) ($request['requested_by'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong>
                                                    <div class="request-subtext"><?= htmlspecialchars((string) ($request['requested_by_role'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                                </td>
                                                <td><?= htmlspecialchars((string) ($request['uploaded_by'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string) ($request['requested_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td>
                                                    <span class="request-status-badge status-<?= htmlspecialchars((string) ($request['status'] ?? 'pending'), ENT_QUOTES, 'UTF-8') ?>">
                                                        <?= htmlspecialchars(remittanceDeleteRequestStatusLabel((string) ($request['status'] ?? 'pending')), ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars((string) ($request['reviewed_by'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                                    <?php if ((string) ($request['reviewed_at'] ?? '') !== ''): ?>
                                                        <div class="request-subtext"><?= htmlspecialchars((string) ($request['reviewed_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="action-cell">
                                                    <?php if ($isPending): ?>
                                                        <div class="request-action-group">
                                                            <form method="POST">
                                                                <input type="hidden" name="form_action" value="review_delete_request">
                                                                <input type="hidden" name="request_id" value="<?= htmlspecialchars((string) ($request['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                                <input type="hidden" name="decision" value="approve">
                                                                <button type="submit" class="request-approve-btn">Approve</button>
                                                            </form>
                                                            <form method="POST">
                                                                <input type="hidden" name="form_action" value="review_delete_request">
                                                                <input type="hidden" name="request_id" value="<?= htmlspecialchars((string) ($request['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                                <input type="hidden" name="decision" value="reject">
                                                                <button type="submit" class="request-reject-btn">Reject</button>
                                                            </form>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="request-subtext">Reviewed</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>

            <?php include __DIR__ . '/includes/footer.php'; ?>
        </main>
    </div>
</body>
</html>

<style>
    .request-subtext {
        margin-top: 4px;
        color: #64748b;
        font-size: 12px;
    }

    .request-status-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 700;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-approved {
        background: #dcfce7;
        color: #166534;
    }

    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .request-action-group {
        display: flex;
        gap: 8px;
        justify-content: center;
    }

    .request-action-group form {
        margin: 0;
    }

    .request-approve-btn,
    .request-reject-btn {
        border: none;
        border-radius: 12px;
        padding: 9px 14px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    .request-approve-btn {
        background: #166534;
        color: #ffffff;
    }

    .request-reject-btn {
        background: #991b1b;
        color: #ffffff;
    }
</style>
