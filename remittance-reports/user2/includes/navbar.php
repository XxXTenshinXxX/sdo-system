<?php
require_once __DIR__ . '/../../../includes/user-activity.php';
userActivityMarkCurrentUser();
userActivityLogPageVisit('user2 remittance page');
$currentProfile = isset($_SESSION['user_id']) ? userActivityFetchCurrentUser((int) $_SESSION['user_id']) : null;
$profileImage = trim((string) ($currentProfile['profile_picture'] ?? ''));
$profileImageUrl = $profileImage !== '' ? '../../' . ltrim($profileImage, '/') : '';
?>
<div class="navbar">
    <div class="brand">
        <div class="brand-badge"><i class="fa-solid fa-file-invoice-dollar"></i></div>
        <div>
            <h1>User2 Dashboard</h1>
            <p>Focused access for QES PhilHealth remittance reports.</p>
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
                        <span>Recent QES remittance updates</span>
                    </div>
                </div>

                <div class="notification-list" id="notificationList">
                    <div class="notification-item">
                        <span class="notification-dot is-info"></span>
                        <div>
                            <p>Loading notifications...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <span class="nav-chip <?= htmlspecialchars($roleClass ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars(strtoupper($userRole ?? 'user2'), ENT_QUOTES, 'UTF-8') ?>
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
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const heartbeatUrl = '../activity-heartbeat.php';
        const notificationsUrl = '../notifications-fetch.php';
        const bellButton = document.getElementById('notificationBellBtn');
        const popover = document.getElementById('notificationPopover');
        const notificationList = document.getElementById('notificationList');
        const badge = bellButton ? bellButton.querySelector('.nav-icon-badge') : null;
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

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function renderNotifications(notifications) {
            if (!notificationList) return;
            if (notifications.length === 0) {
                notificationList.innerHTML = '<div class="notification-item" style="justify-content:center;padding:30px;color:#64748b;font-size:13px;">No recent notifications</div>';
                return;
            }

            notificationList.innerHTML = notifications.map(notif => `
                <div class="notification-item">
                    <span class="notification-dot ${notif.dot_class}"></span>
                    <div>
                        <strong>${escapeHtml(notif.type.toUpperCase())}</strong>
                        <p>${escapeHtml(notif.message)}</p>
                        <small style="color:#94a3b8;font-size:11px;margin-top:4px;display:block;">${escapeHtml(notif.created_at_formatted)}</small>
                    </div>
                </div>
            `).join('');
        }

        function refreshNotifications() {
            fetch(notificationsUrl, {
                cache: 'no-store',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        renderNotifications(data.notifications);
                        if (badge) {
                            if (data.notifications.length > 0) {
                                badge.textContent = data.notifications.length;
                                badge.style.display = 'flex';
                            } else {
                                badge.style.display = 'none';
                            }
                        }
                    }
                })
                .catch(() => {});
        }

        sendHeartbeat();
        refreshNotifications();

        window.setInterval(sendHeartbeat, 30000);
        window.setInterval(refreshNotifications, 15000);

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                sendHeartbeat();
                refreshNotifications();
            }
        });

        window.addEventListener('focus', function() {
            sendHeartbeat();
            refreshNotifications();
        });
    }());
</script>
