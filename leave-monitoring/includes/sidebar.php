<?php
$activePage = $activePage ?? '';
$esPages = ['es-active', 'es-inactivation', 'es-separation'];
$secPages = ['sec-active', 'sec-inactivation', 'sec-separation'];
$isEsOpen = in_array($activePage, $esPages, true);
$isSecOpen = in_array($activePage, $secPages, true);
?>
<aside class="sidebar">
    <div>
        <div class="sidebar-brand">
            <img src="../assets/images/SDO-Logo.png" alt="School Division Office Logo" class="sidebar-brand-logo">
            <div class="sidebar-brand-copy">
                <strong>School Division Office</strong>
                <span>Leave Monitoring System</span>
            </div>
            <br>
        </div>
        <p class="sidebar-section-title">Main Menu</p>
        <nav class="menu">
            <a href="dashboard.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Dashboard</a>
            <details class="menu-group" <?= $isEsOpen ? 'open' : '' ?>>
                <summary class="<?= $isEsOpen ? 'active' : '' ?>">
                    <span><i class="fa-solid fa-users"></i> ES</span>
                    <i class="fa-solid fa-chevron-down menu-caret"></i>
                </summary>
                <div class="submenu">
                    <a href="es-active.php" class="<?= $activePage === 'es-active' ? 'active' : '' ?>">Active</a>
                    <a href="es-inactivation.php" class="<?= $activePage === 'es-inactivation' ? 'active' : '' ?>">Inactivation</a>
                    <a href="es-separation.php" class="<?= $activePage === 'es-separation' ? 'active' : '' ?>">Separation</a>
                </div>
            </details>
            <details class="menu-group" <?= $isSecOpen ? 'open' : '' ?>>
                <summary class="<?= $isSecOpen ? 'active' : '' ?>">
                    <span><i class="fa-solid fa-building-columns"></i> SEC</span>
                    <i class="fa-solid fa-chevron-down menu-caret"></i>
                </summary>
                <div class="submenu">
                    <a href="sec-active.php" class="<?= $activePage === 'sec-active' ? 'active' : '' ?>">Active</a>
                    <a href="sec-inactivation.php" class="<?= $activePage === 'sec-inactivation' ? 'active' : '' ?>">Inactivation</a>
                    <a href="sec-separation.php" class="<?= $activePage === 'sec-separation' ? 'active' : '' ?>">Separation</a>
                </div>
            </details>
            <a href="../select-dashboard.php"><i class="fa-solid fa-arrow-left"></i> Back to Systems</a>
        </nav>
    </div>
    <div class="sidebar-logout">
        <button type="button" class="sidebar-logout-btn" id="sidebarLogoutTrigger">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </button>
    </div>
</aside>

<div class="modal-overlay" id="logoutConfirmModal" aria-hidden="true">
    <div class="modal-card confirm-delete-card">
        <div class="confirm-delete-hero">
            <div class="confirm-delete-icon">
                <i class="fa-solid fa-right-from-bracket"></i>
            </div>
            <div>
                <span class="confirm-delete-kicker">Logout Confirmation</span>
                <h3>Logout from Leave Monitoring?</h3>
                <p>Your current session will end and you will be returned to the login page.</p>
            </div>
        </div>
        <div class="confirm-delete-actions">
            <button type="button" class="btn btn-outline" id="logoutCancelBtn">Cancel</button>
            <a href="logout.php" class="btn btn-danger">
                <i class="fa-solid fa-right-from-bracket"></i>
                Confirm Logout
            </a>
        </div>
    </div>
</div>

<style>
    .sidebar-logout {
        margin-top: auto;
        padding-top: 12px;
        border-top: 1px solid rgba(148, 163, 184, 0.18);
    }

    .sidebar-logout-btn {
        width: 100%;
        border: 1px solid rgba(248, 113, 113, 0.18);
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(127, 29, 29, 0.92), rgba(185, 28, 28, 0.96));
        color: #fff7f7;
        padding: 14px 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 16px 26px rgba(127, 29, 29, 0.24);
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
    }

    .sidebar-logout-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 18px 28px rgba(127, 29, 29, 0.3);
        filter: brightness(1.04);
    }
</style>

<script>
    (function () {
        const logoutTrigger = document.getElementById('sidebarLogoutTrigger');
        const logoutModal = document.getElementById('logoutConfirmModal');
        const logoutCancelBtn = document.getElementById('logoutCancelBtn');

        if (!logoutTrigger || !logoutModal || !logoutCancelBtn) {
            return;
        }

        function openLogoutModal() {
            logoutModal.classList.add('show');
            logoutModal.setAttribute('aria-hidden', 'false');
        }

        function closeLogoutModal() {
            logoutModal.classList.remove('show');
            logoutModal.setAttribute('aria-hidden', 'true');
        }

        logoutTrigger.addEventListener('click', openLogoutModal);
        logoutCancelBtn.addEventListener('click', closeLogoutModal);

        logoutModal.addEventListener('click', function (event) {
            if (event.target === logoutModal) {
                closeLogoutModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && logoutModal.classList.contains('show')) {
                closeLogoutModal();
            }
        });
    }());
</script>
