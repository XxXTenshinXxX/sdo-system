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
        <div class="profile-button"><?= htmlspecialchars($profileInitial ?? 'U', ENT_QUOTES, 'UTF-8') ?></div>
    </div>
</div>

<script>
    (function () {
        const bellButton = document.getElementById('notificationBellBtn');
        const popover = document.getElementById('notificationPopover');

        if (!bellButton || !popover) {
            return;
        }

        function closePopover() {
            popover.hidden = true;
            bellButton.setAttribute('aria-expanded', 'false');
        }

        function openPopover() {
            popover.hidden = false;
            bellButton.setAttribute('aria-expanded', 'true');
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

        document.addEventListener('click', function () {
            closePopover();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closePopover();
            }
        });
    }());
</script>
