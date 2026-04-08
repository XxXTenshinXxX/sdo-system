<!DOCTYPE html>
<html lang="tl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>
    <div class="login-card">
        <div class="logo">
            <h1>Login</h1>
            <p>Welcome back! Please login to continue.</p>
        </div>

        <form id="loginForm">
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" id="email" placeholder="example@email.com" autocomplete="off" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" id="password" placeholder="Enter your password" required>
            </div>

            <div class="options">
                <div class="forgot">
                    <a href="forgot-password.php">Forgot password?</a>
                </div>
            </div>

            <button type="submit" class="login-btn">Login</button>
            
            <div id="message" class="message"></div>
        </form>
    </div>

    <script>
        
    </script>
</body>
</html>