<?php
session_start();

$loginError = $_SESSION['login_error'] ?? '';
$oldEmail = $_SESSION['old_email'] ?? '';

unset($_SESSION['login_error'], $_SESSION['old_email']);
?>
<!DOCTYPE html>
<html lang="tl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="assets/images/SDO-Logo.png">

</head>
<body>
    <div class="login-card">
        <div class="logo">

            <img src="assets/images/SDO-Logo.png" alt="SDO Logo" class="login-logo">

            <h1>Login</h1>
            <p>Welcome back! Please login to continue.</p>
        </div>

        <div id="message" class="message <?= $loginError ? 'message-visible' : '' ?>">
            <?php if ($loginError): ?>
                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                <span><?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <form id="loginForm" method="POST" action="actions/login-process.php">
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" id="email" placeholder="Enter your email address" autocomplete="off" value="<?= htmlspecialchars($oldEmail, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

        <div class="input-group">
            <label>Password</label>
            <div class="password-wrapper" style="position: relative;">
                <input type="password" name="password" id="password" placeholder="Enter your password" required style="padding-right: 40px;">
                <i id="togglePassword" class="fa-solid fa-eye" 
                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
            </div>
        </div>

            <div class="options">
                <div class="forgot">
                    <a href="forgot-password.php">Forgot password?</a>
                </div>
            </div>

            <button type="submit" class="login-btn">Login</button>
            
        </form>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Toggle icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
