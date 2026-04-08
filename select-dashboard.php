<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Select System</title>

<style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background-color: #3498db;        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .container {
        text-align: center;
        color: white;
    }

    h2 {
        margin-bottom: 30px;
        font-size: 28px;
    }

    .cards {
        display: flex;
        gap: 30px;
        justify-content: center;
    }

    .card {
        background: white;
        color: #333;
        width: 220px;
        padding: 30px 20px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        cursor: pointer;
        transition: 0.3s;
    }

    .card:hover {
        transform: translateY(-10px) scale(1.05);
        box-shadow: 0 15px 35px rgba(0,0,0,0.3);
    }

    .icon {
        font-size: 40px;
        margin-bottom: 15px;
    }

    .title {
        font-size: 18px;
        font-weight: bold;
    }

    button {
        border: none;
        background: none;
        width: 100%;
        cursor: pointer;
    }
    .logout-btn {
        background: #ff4d4d;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        transition: 0.3s;
    }

    .logout-btn:hover {
        background: #e60000;
    }


</style>
</head>

<body>

<div class="container">
    <h2>Select Dashboard</h2>

    <form method="POST" action="redirect-system.php">
        <div class="cards">

            <button type="submit" name="system" value="philhealth">
                <div class="card">
                    <div class="icon">💊</div>
                    <div class="title">PhilHealth Remittance</div>
                </div>
            </button>

            <button type="submit" name="system" value="leave">
                <div class="card">
                    <div class="icon">📅</div>
                    <div class="title">Leave Monitoring</div>
                </div>
            </button>

        </div>
    </form>

    <form id="logoutForm" action="logout.php" method="POST">
        <button type="button" class="logout-btn" onclick="openLogoutModal()">
            Logout
        </button>
    </form>

    
</div>
    <script>
        function confirmLogout() {
            return confirm("Are you sure you want to logout?");
        }
</script>

    
</div>
</body>

<div id="logoutModal" class="modal">
    <div class="modal-content">
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to logout?</p>

        <div class="modal-buttons">
            <button class="cancel-btn" onclick="closeLogoutModal()">Cancel</button>
            <button class="confirm-btn" onclick="submitLogout()">Yes, Logout</button>
        </div>
    </div>
</html>