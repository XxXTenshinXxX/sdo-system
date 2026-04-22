<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Select System</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        min-height: 100vh;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background: #3498db;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    .container {
        width: 100%;
        max-width: 980px;
        background: #ffffff;
        border: 1px solid #dbe4ea;
        border-radius: 28px;
        padding: 32px;
        box-shadow: 0 18px 38px rgba(10, 37, 64, 0.14);
        color: #1f2937;
    }

    .header {
        text-align: center;
        margin-bottom: 28px;
    }

    .header h1 {
        margin: 0 0 8px;
        font-size: 34px;
        letter-spacing: 0.4px;
    }

    .header p {
        margin: 0;
        font-size: 15px;
        color: #6b7280;
    }

    .cards {
        display: grid;
        grid-template-columns: repeat(2, minmax(240px, 1fr));
        gap: 22px;
        margin-bottom: 28px;
    }

    .card {
        display: block;
        text-decoration: none;
        background: #fff;
        color: #1f2937;
        border-radius: 22px;
        padding: 28px 24px;
        box-shadow: 0 18px 35px rgba(15, 23, 42, 0.16);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .card:hover {
        transform: translateY(-8px);
        box-shadow: 0 24px 40px rgba(15, 23, 42, 0.22);
    }

    .card-icon {
        width: 66px;
        height: 66px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        margin: 0 auto 18px;
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    }

    .card.leave .card-icon {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    }

    .card h2 {
        margin: 0 0 10px;
        font-size: 22px;
        text-align: center;
    }

    .card p {
        margin: 0;
        color: #4b5563;
        line-height: 1.55;
        font-size: 14px;
        text-align: center;
    }

    .actions {
        display: flex;
        justify-content: center;
    }

    .logout-btn {
        border: none;
        border-radius: 999px;
        padding: 12px 28px;
        font-size: 14px;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        cursor: pointer;
        box-shadow: 0 12px 24px rgba(220, 38, 38, 0.28);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .logout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 28px rgba(220, 38, 38, 0.34);
    }

    .modal {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, 0.45);
    }

    .modal.show {
        display: flex;
    }

    .modal-content {
        width: 100%;
        max-width: 420px;
        background: #fff;
        color: #111827;
        border-radius: 22px;
        padding: 28px;
        text-align: center;
        box-shadow: 0 24px 50px rgba(15, 23, 42, 0.24);
        animation: modalIn 0.2s ease;
    }

    .modal-content h3 {
        margin: 0 0 12px;
        font-size: 24px;
    }

    .modal-content p {
        margin: 0 0 24px;
        color: #4b5563;
        line-height: 1.5;
    }

    .modal-buttons {
        display: flex;
        justify-content: center;
        gap: 12px;
    }

    .cancel-btn,
    .confirm-btn {
        border: none;
        border-radius: 12px;
        padding: 11px 18px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
    }

    .cancel-btn {
        background: #e5e7eb;
        color: #111827;
    }

    .confirm-btn {
        background: #dc2626;
        color: #fff;
    }

    @keyframes modalIn {
        from {
            opacity: 0;
            transform: translateY(10px) scale(0.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @media (max-width: 700px) {
        .container {
            padding: 24px 18px;
        }

        .header h1 {
            font-size: 28px;
        }

        .cards {
            grid-template-columns: 1fr;
        }

        .modal-buttons {
            flex-direction: column;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Select Dashboard</h1>
            <p>Choose which system you want to open.</p>
        </div>

        <div class="cards">
            <a class="card" href="remittance-reports/admin/dashboard.php">
                <div class="card-icon"><i class="fa-solid fa-file-medical"></i></div>
                <h2>PhilHealth Remittance</h2>
                <p>Open the remittance reports dashboard for administration and monitoring.</p>
            </a>

            <a class="card leave" href="leave-monitoring/dashboard.php">
                <div class="card-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <h2>Leave Monitoring</h2>
                <p>Go to the leave monitoring system to manage records and employee leave activity.</p>
            </a>
        </div>

        <div class="actions">
            <button type="button" class="logout-btn" onclick="openLogoutModal()">Logout</button>
        </div>
    </div>

    <div id="logoutModal" class="modal" aria-hidden="true">
        <div class="modal-content">
            <h3>Confirm Logout</h3>
            <p>Are you sure you want to logout from your account?</p>
            <div class="modal-buttons">
                <button type="button" class="cancel-btn" onclick="closeLogoutModal()">Cancel</button>
                <form id="logoutForm" action="logout.php" method="POST">
                    <button type="submit" class="confirm-btn">Yes, Logout</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const logoutModal = document.getElementById('logoutModal');

        function openLogoutModal() {
            logoutModal.classList.add('show');
            logoutModal.setAttribute('aria-hidden', 'false');
        }

        function closeLogoutModal() {
            logoutModal.classList.remove('show');
            logoutModal.setAttribute('aria-hidden', 'true');
        }

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
    </script>
</body>
</html>
