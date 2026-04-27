<?php
require_once __DIR__ . '/../../../includes/user-activity.php';
userActivityMarkCurrentUser();
userActivityLogPageVisit('admin remittance page');
$currentProfile = isset($_SESSION['user_id']) ? userActivityFetchCurrentUser((int) $_SESSION['user_id']) : null;
$profileImage = trim((string) ($currentProfile['profile_picture'] ?? ''));
$profileImageUrl = $profileImage !== '' ? '../../' . ltrim($profileImage, '/') : '';
?>
<div class="navbar">
    <div class="brand">
        <div class="brand-badge"><i class="fa-solid fa-file-medical"></i></div>
        <div>
            <h1>Dashboard Overview</h1>
            <p>Monitor PhilHealth remittance access and module entry points.</p>
        </div>
    </div>

    <div class="nav-actions">
        <div class="nav-popover-wrap">
            <button type="button" class="nav-icon-button" id="notificationBellBtn" aria-label="Notifications" aria-expanded="false" aria-haspopup="true">
                <i class="fa-solid fa-bell"></i>
                <span class="nav-icon-badge"></span>
            </button>

            <div class="notification-popover" id="notificationPopover" hidden>
                <div class="notification-popover-head">
                    <div>
                        <strong>Notifications</strong>
                        <span>Recent remittance updates</span>
                    </div>
                </div>

                <div class="notification-list">
                    <div class="notification-item">
                        <span class="notification-dot is-info"></span>
                        <div>
                            <strong>PDF uploads are enabled</strong>
                            <p>You can now upload one or more PDF reports from ES / SHS and QES.</p>
                        </div>
                    </div>

                    <div class="notification-item">
                        <span class="notification-dot is-success"></span>
                        <div>
                            <strong>Employee drill-down is ready</strong>
                            <p>Click `View Employees` in the tables to inspect parsed employee rows.</p>
                        </div>
                    </div>

                    <div class="notification-item">
                        <span class="notification-dot is-warn"></span>
                        <div>
                            <strong>Backup dashboard synced</strong>
                            <p>User 3 pages continue mirroring the admin remittance views.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <span class="nav-chip <?= htmlspecialchars($roleClass ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars(ucwords($userRole ?? 'staff'), ENT_QUOTES, 'UTF-8') ?>
        </span>
        <div class="nav-popover-wrap">
            <button type="button" class="profile-button profile-trigger-button" id="profilePanelBtn" aria-label="Open profile menu" aria-expanded="false" aria-haspopup="true">
                <?php if ($profileImageUrl !== ''): ?>
                    <img src="<?= htmlspecialchars($profileImageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Profile picture" class="profile-avatar-image">
                <?php else: ?>
                    <?= htmlspecialchars($profileInitial ?? 'U', ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </button>

            <div class="profile-menu-popover" id="profilePopover" hidden>
                <div class="profile-menu-head">
                    <div class="profile-menu-avatar">
                        <?php if ($profileImageUrl !== ''): ?>
                            <img src="<?= htmlspecialchars($profileImageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Profile picture" class="profile-avatar-image">
                        <?php else: ?>
                            <?= htmlspecialchars($profileInitial ?? 'U', ENT_QUOTES, 'UTF-8') ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <strong><?= htmlspecialchars($currentProfile['display_name'] ?? ($userRole ?? 'User'), ENT_QUOTES, 'UTF-8') ?></strong>
                        <span><?= htmlspecialchars($currentProfile['email'] ?? ($_SESSION['email'] ?? 'No email available'), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>

                <div class="profile-menu-links">
                    <a href="profile.php" class="profile-menu-link">
                        <i class="fa-solid fa-id-badge"></i>
                        <div>
                            <strong>Profile</strong>
                            <span>Open your account details</span>
                        </div>
                    </a>
                    <a href="activity-log.php" class="profile-menu-link">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <div>
                            <strong>Activity Log</strong>
                            <span>View recent login and activity history</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const heartbeatUrl = '../activity-heartbeat.php';
        const bellButton = document.getElementById('notificationBellBtn');
        const popover = document.getElementById('notificationPopover');
        const profileButton = document.getElementById('profilePanelBtn');
        const profilePopover = document.getElementById('profilePopover');

        if (!bellButton || !popover) {
            return;
        }

        function closePopover() {
            popover.hidden = true;
            bellButton.setAttribute('aria-expanded', 'false');
        }

        function closeProfilePopover() {
            if (!profilePopover || !profileButton) {
                return;
            }

            profilePopover.hidden = true;
            profileButton.setAttribute('aria-expanded', 'false');
        }

        function openPopover() {
            popover.hidden = false;
            bellButton.setAttribute('aria-expanded', 'true');
            closeProfilePopover();
        }

        function openProfilePopover() {
            if (!profilePopover || !profileButton) {
                return;
            }

            profilePopover.hidden = false;
            profileButton.setAttribute('aria-expanded', 'true');
            closePopover();
        }

        bellButton.addEventListener('click', function (event) {
            event.stopPropagation();
            if (popover.hidden) {
                openPopover();
            } else {
                closePopover();
            }
        });

        popover.addEventListener('click', function (event) {
            event.stopPropagation();
        });

        if (profileButton && profilePopover) {
            profileButton.addEventListener('click', function (event) {
                event.stopPropagation();
                if (profilePopover.hidden) {
                    openProfilePopover();
                } else {
                    closeProfilePopover();
                }
            });

            profilePopover.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        }

        document.addEventListener('click', function () {
            closePopover();
            closeProfilePopover();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closePopover();
                closeProfilePopover();
            }
        });

        function sendHeartbeat() {
            fetch(heartbeatUrl, {
                method: 'POST',
                credentials: 'same-origin',
                cache: 'no-store',
                keepalive: true,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).catch(function () {
            });
        }

        sendHeartbeat();
        window.setInterval(sendHeartbeat, 30000);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                sendHeartbeat();
            }
        });
        window.addEventListener('focus', sendHeartbeat);
    }());
</script>
