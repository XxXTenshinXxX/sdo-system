<aside class="sidebar">
    <div>
        <div class="sidebar-brand">
            <img src="../../assets/images/SDO-Logo.png" alt="SDO Logo" class="sidebar-brand-logo">
            <div class="sidebar-brand-copy">
                <strong>School Division Office</strong>
                <span>REMITTANCE UNIT</span>
                <span>(PhilHealth)</span>
                <span>USER1 PORTAL</span>
            </div>
            <br>
        </div>

        <p class="sidebar-section-title">Main Menu</p>
        <nav class="menu">
            <a href="dashboard.php" class="<?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-table-columns"></i>
                <span>Dashboard</span>
            </a>
            <a href="es-shs.php" class="<?= ($activePage ?? '') === 'es-shs' ? 'active' : '' ?>">
                <i class="fa-solid fa-school"></i>
                <span>ES / SHS</span>
            </a>
        </nav>
    </div>

    <div class="sidebar-logout">
        <button type="button" class="sidebar-logout-btn" id="adminSidebarLogoutTrigger">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </button>
    </div>
</aside>

<div class="modal-overlay" id="adminLogoutConfirmModal" aria-hidden="true">
    <div class="modal-card confirm-logout-card">
        <div class="confirm-logout-hero">
            <div class="confirm-logout-icon">
                <i class="fa-solid fa-right-from-bracket"></i>
            </div>
            <div>
                <span class="confirm-logout-kicker">Logout Confirmation</span>
                <h3>Logout from User1 Dashboard?</h3>
                <p>Your session will end and you will be redirected to the login page.</p>
            </div>
        </div>
        <div class="confirm-logout-actions">
            <button type="button" class="btn btn-outline" id="adminLogoutCancelBtn">Cancel</button>
            <a href="../logout.php" class="btn btn-danger">
                <i class="fa-solid fa-right-from-bracket"></i>
                Confirm Logout
            </a>
        </div>
    </div>
</div>

<style>
    .modal-overlay {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(15, 23, 42, 0.56);
        backdrop-filter: blur(3px);
        z-index: 1000;
    }

    .modal-overlay.show {
        display: flex;
    }

    .modal-card {
        width: min(560px, calc(100vw - 32px));
        border-radius: 24px;
        box-shadow: 0 28px 60px rgba(15, 23, 42, 0.28);
        animation: adminLogoutModalIn 0.18s ease-out;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        padding: 11px 18px;
        border-radius: 14px;
        border: none;
        text-decoration: none;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, color 0.18s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn-outline {
        background: #ffffff;
        color: #0f172a;
        border: 1px solid #cbd5e1;
        box-shadow: 0 10px 20px rgba(148, 163, 184, 0.16);
    }

    .btn-outline:hover {
        background: #f8fafc;
    }

    .btn-danger {
        background: linear-gradient(135deg, #b91c1c, #dc2626);
        color: #ffffff;
        box-shadow: 0 14px 26px rgba(220, 38, 38, 0.22);
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #991b1b, #b91c1c);
    }

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

    .confirm-logout-card {
        max-width: 560px;
        padding: 0;
        overflow: hidden;
        border: 1px solid rgba(248, 113, 113, 0.18);
        background:
            radial-gradient(circle at top right, rgba(248, 113, 113, 0.12), transparent 32%),
            linear-gradient(180deg, #ffffff, #fff7f7);
    }

    .confirm-logout-hero {
        display: flex;
        gap: 18px;
        padding: 26px 24px 20px;
        border-bottom: 1px solid #fee2e2;
    }

    .confirm-logout-icon {
        width: 60px;
        height: 60px;
        border-radius: 20px;
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #b91c1c;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
        flex-shrink: 0;
    }

    .confirm-logout-kicker {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        background: #fee2e2;
        color: #b91c1c;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .confirm-logout-hero h3 {
        margin: 0 0 8px;
        font-size: 24px;
        line-height: 1.2;
        color: #0f172a;
    }

    .confirm-logout-hero p {
        margin: 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.6;
    }

    .confirm-logout-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 20px 24px 24px;
        flex-wrap: wrap;
        background: rgba(255, 255, 255, 0.72);
    }

    @keyframes adminLogoutModalIn {
        from {
            opacity: 0;
            transform: translateY(10px) scale(0.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @media (max-width: 640px) {
        .confirm-logout-hero {
            flex-direction: column;
        }

        .confirm-logout-actions {
            justify-content: stretch;
        }

        .confirm-logout-actions .btn {
            width: 100%;
        }
    }
</style>

<script>
    (function () {
        const logoutTrigger = document.getElementById('adminSidebarLogoutTrigger');
        const logoutModal = document.getElementById('adminLogoutConfirmModal');
        const logoutCancelBtn = document.getElementById('adminLogoutCancelBtn');

        if (!logoutTrigger || !logoutModal || !logoutCancelBtn) {
            return;
        }

        function openLogoutModal() {
            logoutModal.classList.add('show');
            logoutModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeLogoutModal() {
            logoutModal.classList.remove('show');
            logoutModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
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
