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
<link rel="icon" type="image/png" href="assets/images/SDO-Logo.png">
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
        color: #1f2937;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    .container {
        width: 100%;
        max-width: 920px;
        background: #ffffff;
        border: 1px solid #cfe3f6;
        border-radius: 24px;
        padding: 32px 28px;
        box-shadow: 0 18px 38px rgba(52, 152, 219, 0.18);
    }

    .header {
        text-align: center;
        margin-bottom: 28px;
    }

    .header img {
        width: 78px;
        height: 78px;
        object-fit: contain;
        margin-bottom: 12px;
    }

    .header h1 {
        margin: 0 0 6px;
        font-size: 30px;
    }

    .header p {
        margin: 0;
        color: #6b7280;
        font-size: 15px;
    }

    .cards {
        display: grid;
        grid-template-columns: repeat(2, minmax(240px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .card {
        display: block;
        text-decoration: none;
        color: inherit;
        background: #f4faff;
        border: 1px solid #cfe3f6;
        border-radius: 20px;
        padding: 24px 22px;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .card:hover {
        transform: translateY(-4px);
        border-color: #3498db;
        box-shadow: 0 14px 24px rgba(52, 152, 219, 0.18);
    }

    .card-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 16px;
        background: #d6eaf8;
        color: #3498db;
    }

    .card.leave .card-icon {
        background: #d6eaf8;
        color: #3498db;
    }

    .card h2 {
        margin: 0 0 8px;
        font-size: 22px;
    }

    .card p {
        margin: 0;
        color: #4b5563;
        line-height: 1.55;
        font-size: 14px;
    }

    .actions {
        display: flex;
        justify-content: center;
    }

    .logout-btn {
        border: none;
        border-radius: 999px;
        padding: 12px 24px;
        font-size: 14px;
        font-weight: 700;
        color: #fff;
        background: #dc2626;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .logout-btn:hover {
        background: #b91c1c;
        transform: translateY(-1px);
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
        max-width: 400px;
        background: #fff;
        color: #111827;
        border-radius: 20px;
        padding: 26px;
        text-align: center;
        box-shadow: 0 24px 50px rgba(15, 23, 42, 0.2);
    }

    .modal-content h3 {
        margin: 0 0 12px;
        font-size: 22px;
    }

    .modal-content p {
        margin: 0 0 22px;
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

    @media (max-width: 700px) {
        .container {
            padding: 24px 18px;
        }

        .header h1 {
            font-size: 26px;
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
            <img src="assets/images/SDO-Logo.png" alt="School Division Office Logo">
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
